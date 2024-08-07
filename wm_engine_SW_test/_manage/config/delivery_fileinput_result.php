<?PHP

	$no_qcheck = true;

	// 처리 결과
	$res = json_decode($_POST['result'], true);
	$total = $res['total'];
	$success = $res['success'];
	$faild = $total-$success;

	$add_name = ($cfg['adddlv_type'] == 2) ? "배송지별칭" : "지역명";

	// 검색
	$search = (string)$_POST['search'];
	$search_rows = 0;
	$active[$search] = 'active';
	if($search) {
		foreach($res['datas'] as $key => $val) {
			foreach($val as $key2 => $val2) {
				if($search == 'success' && $val2['msg'] != 'OK') {
					unset($res['datas'][$key][$key2]);
				}
				if($search == 'faild' && $val2['msg'] == 'OK') {
					unset($res['datas'][$key][$key2]);
				}
			}
		}
	}


	function parseOrderProductResult(&$_res, $area, $idx) {
		global $tbl, $_dlv_names, $res, $fd;

		$csv = current($_res); next($_res);

		if(is_array($csv) == false) return false;
		if($fd['opno'] > 0) {
			$opno = $csv['data'][$fd['opno']];
		}

		$data = array();
		$data['rowspan'] = count($res['datas'][$area]);

		$data['area'] = $csv['name'];
		$data['idx'] = $idx;
		$data['result'] = ($csv['msg'] == 'OK') ? '성공' : '<span class="p_color2">실패</span>';
		$data['status'] = ($csv['msg'] == 'OK') ? 'success' : 'faild';
		$data['msg'] = $data['msg2'] = $csv['msg'];

        $fp = fopen('php://temp', 'r+');
        fputcsv($fp, $csv['data']);
        rewind($fp);
        $data['csv'] = fgets($fp);
        fclose($fp);

		if($data['msg2'] == 'OK') $data['msg2'] = '';

		return $data;
	}

?>
<div class="box_title first">
	<h2 class="title">지역별 추가배송비 일괄처리 결과</h2>
</div>
<style>
	.tbl_col {width:100%;border:1px solid #c9c9c9;/* border-bottom:1px solid #c9c9c9; */background:#fff;word-wrap:break-word;word-break:break-all;}
	.box_tab {position:relative;clear:both;height:54px;margin-top:35px;background:#f9f9f9;border:1px solid #c9c9c9;border-bottom: 0;font-size:16px;font-weight:bold;line-height:54px;letter-spacing:-1px;}
</style>
<form id="resultFrm" method="POST" action="./index.php" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="<?=$body?>.exe">
	<input type="hidden" name="exec" value="download">
	<input type="hidden" name="search" value="">
	<input type="hidden" name="result" value="<?=htmlspecialchars($_POST['result'])?>">

	<div class="box_tab" style="margin-top:0;">
		<ul>
			<li><a href="#" onclick="viewDeliveryStatus(''); return false;" class="<?=$active['']?>">전체<span><?=number_format($total)?></span></a></li>
			<li><a href="#" onclick="viewDeliveryStatus('success'); return false;" class="<?=$active['success']?>">성공<span><?=number_format($success)?></a></li>
			<li><a href="#" onclick="viewDeliveryStatus('faild'); return false;" class="<?=$active['faild']?>">실패<span><?=number_format($faild)?></a></li>
		</ul>
		<div class="btns">
			<span class="box_btn_s icon excel btt"><input type="button" onclick="excelDown();" value="업로드 실패 배송지 다운"></span>
			<div class="process"></div>
		</div>
	</div>

	<table class="tbl_col">
		<caption class="hidden">지역별 추가배송비 일괄처리 결과</caption>
		<colgroup>
			<col style="width:100px;">
			<col style="width:100px;">
			<col style="width:100px;">
			<col>
		</colgroup>
		<thead>
			<tr>
				<th><?=$add_name?></th>
				<th>성공여부</th>
				<th>실패사유</th>
			</tr>
		</thead>
		<?foreach($res['datas'] as $area => $val) { $idx = 0;?>
		<?while($data = parseOrderProductResult($val, $area, $idx)) {?>
		<tr class="status status_<?=$data['status']?>">
			<td><?=$data['area']?></td>
			<td>
				<?=$data['result']?>
				<?php if($data['msg'] != 'OK') { ?>
				<input type="hidden" class="csv" name="csv[]" value="<?=inputText($data['csv'])?>">
				<?php } ?>
			</td>
			<td class="left explain"><?=$data['msg2']?></td>
		</tr>
		<?$idx++;$search_rows++;}?>
		<?}?>
		<?if($search_rows == 0) {?>
		<tr>
			<td colspan="8" class="center">검색된 내역이 없습니다.</td>
		</tr>
		<?}?>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="button" value="확인" onclick="location.href='?body=config@delivery'"></span>
	</div>
</form>
<script type="text/javascript">
var f = document.getElementById('resultFrm');
function viewDeliveryStatus(n) {
	$('.csv').prop('disabled', true);

	f.body.value = 'config@delivery_fileinput_result';
	f.search.value = n;
	f.target = '_self';
	f.submit();
}

function excelDown() {
	f.result.disabled = true;
	f.body.value = 'config@delivery_fileinput_result.exe';
	f.target = hid_frame;
	f.submit();

	f.result.disabled = false;
}
</script>