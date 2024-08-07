<?PHP

	$no_qcheck = true;

	// 처리 결과
	$res = json_decode($_POST['result'], true);
	$file_type = $_POST['file_type'];
	$total = $res['total'];
	$success = $res['success'];
	$faild = $total-$success;
	$count = ($file_type == "csv") ? "0" : "1";

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

	// 주문 상품별 csv
	$fd = explode(',', $cfg['ord_input_fd_selected']);
	foreach($fd as $key => $val) {
		$fd[$val] = $key+$count;
	}

	// 입점사명 캐시
	$_dlv_names = array();
	$dres = $pdo->iterator("select no, name from {$tbl['delivery_url']}");
    foreach ($dres as $data) {
		$_dlv_names[$data['no']] = stripslashes($data['name']);
	}

	function parseOrderProductResult(&$_res, $ono, $idx) {
		global $tbl, $_dlv_names, $res, $fd, $pdo;

		$csv = current($_res); next($_res);

		if(is_array($csv) == false) return false;
		if($fd['opno'] > 0) {
			$opno = $csv['data'][$fd['opno']];
		}

		if($opno > 0) {
			$ord = $pdo->assoc("select o.addressee_name, o.addressee_addr1, op.r_name, op.r_addr1, op.name as title from {$tbl['order']} o inner join {$tbl['order_product']} op using(ono) where ono='$ono' and op.no='$opno'");
		} else {
			$ord = $pdo->assoc("select o.addressee_name, o.addressee_addr1, o.title from {$tbl['order']} o where ono='$ono'");
		}

		$data = array();
		$data['rowspan'] = count($res['datas'][$ono]);

		$data['ono'] = $ono;
		$data['buyer_name'] = ($ord['r_name']) ? stripslashes($ord['r_name']) : stripslashes($ord['addressee_name']);
		$data['addressee_addr1'] = ($ord['r_addr1']) ? stripslashes($ord['r_addr1']) : stripslashes($ord['addressee_addr1']);
		$data['title'] = stripslashes($ord['title']);
		$data['dlv_no'] = $csv['data'][$fd['dlv_no']];
		$data['dlv_code'] = $csv['data'][$fd['dlv_code']];
		$data['idx'] = $idx;
		$data['result'] = ($csv['msg'] == 'OK') ? '성공' : '<span class="p_color2">실패</span>';
		$data['status'] = ($csv['msg'] == 'OK') ? 'success' : 'faild';
		$data['msg'] = $data['msg2'] = $csv['msg'];
		$data['csv'] = implode(',', $csv['data']);

		if($data['msg2'] == 'OK') $data['msg2'] = '';

		return $data;
	}

?>
<div class="box_title first">
	<h2 class="title">주문일괄배송처리 결과</h2>
</div>
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
			<span class="box_btn_s icon excel btt"><input type="button" onclick="excelDown();" value="실패주문서 엑셀다운로드"></span>
			<div class="process"></div>
		</div>
	</div>

	<table class="tbl_col">
		<caption class="hidden">주문일괄배송처리 결과</caption>
		<colgroup>
			<col style="width:150px;">
			<col style="width:120px;">
			<col>
			<col>
			<col style="width:100px;">
			<col style="width:100px;">
			<col style="width:100px;">
			<col>
		</colgroup>
		<thead>
			<tr>
				<th>주문번호</th>
				<th>수령자명</th>
				<th>상품명</th>
				<th>배송지</th>
				<th>배송업체</th>
				<th>송장번호</th>
				<th>성공여부</th>
				<th>실패사유</th>
			</tr>
		</thead>
		<?foreach($res['datas'] as $ono => $ord) { $idx = 0;?>
		<?while($data = parseOrderProductResult($ord, $ono, $idx)) {?>
		<tr class="status status_<?=$data['status']?>">
			<?if($data['idx'] == 0) {?>
			<td rowspan="<?=$data['rowspan']?>"><a href="#" onclick="viewOrder('<?=$data['ono']?>'); return false;"><strong><?=$data['ono']?></strong></a></td>
			<?}?>
			<td><?=$data['buyer_name']?></td>
			<td class="left"><?=$data['title']?></td>
			<td class="left"><?=$data['addressee_addr1']?></td>
			<td><?=$data['dlv_no']?></td>
			<td><?=$data['dlv_code']?></td>
			<td>
				<?=$data['result']?>
				<?if($data['msg'] != 'OK') {?>
				<input type="hidden" class="csv" name="csv[]" value="<?=$data['csv']?>">
				<?}?>
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
</form>
<script type="text/javascript">
var f = document.getElementById('resultFrm');
function viewDeliveryStatus(n) {
	$('.csv').prop('disabled', true);

	f.body.value = 'order@delivery_fileinput_result';
	f.search.value = n;
	f.target = '_self';
	f.submit();
}

function excelDown() {
	f.result.disabled = true;
	f.body.value = 'order@delivery_fileinput_result.exe';
	f.target = hid_frame;
	f.submit();

	f.result.disabled = false;
}
</script>