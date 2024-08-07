<?PHP

	$no_qcheck = true;

	// 처리 결과
	$res = json_decode(rawurldecode($_POST['result']), true);
	$total = $res['total'];
	$success = $res['success'];
	$faild = $total-$success;

	// 검색
	$search = (string)$_POST['search'];
	$search_rows = 0;
	$active[$search] = 'active';
	if($search) {
		foreach($res['datas'] as $key => $val) {
			foreach($val as $key2 => $val2) {
				if($search == 'success' && $val2 != 'OK') {
					unset($res['datas'][$key][$key2]);
				}
				if($search == 'faild' && $val2 == 'OK') {
					unset($res['datas'][$key][$key2]);
				}
			}
		}
	}

	// 입점사명 캐시
	$_dlv_names = array();
	$dres = $pdo->query("select no, name from {$tbl['delivery_url']}");
    foreach ($dres as $data) {
		$_dlv_names[$data['no']] = stripslashes($data['name']);
	}

	function parseOrderProductResult(&$_res, $ono, $idx) {
		global $tbl, $pdo, $res, $_order_stat, $_order_color;

		$result_message = current($_res);
        $opno = key($_res);
        next($_res);

		if($result_message == false) return false;

		if($opno > 0) {
            $sfield = '';
            if ($cfg['use_set_product'] == 'Y') {
                $sfield .= ", set_pno";
            }
			$oprd = $pdo->assoc("select name, stat $sfield from {$tbl['order_product']} where no='$opno'");
		} else {
			$oprd = $pdo->assoc("select stat from {$tbl['order']} where ono='$ono'");
        }

		$data = array();
		$data['rowspan'] = count($res['datas'][$ono]);

        $data['idx'] = $idx;
		$data['ono'] = $ono;
        $data['name'] = stripslashes($oprd['name']);
        if ($oprd['set_pno'] > 0) {
            $setname = $pdo->row("select name from {$tbl['product']} where no=?", array($oprd['set_pno']));
            if ($setname) {
                $data['name'] .= "<div class='explain'><span class=\"set_label\">SET</span> $setname</div>";
            }
        }
        $data['stat'] = "<span style='color:{$_order_color[$oprd['stat']]}'>{$_order_stat[$oprd['stat']]}</span>";
        $data['result'] = ($result_message == 'OK') ? '성공' : '<span class="p_color2">실패</span>';
		$data['msg'] = ($result_message == 'OK') ? '' : $result_message;

		return $data;
	}

?>
<div class="box_title first">
	<h2 class="title">주문일괄상태변경 결과</h2>
</div>
<form id="resultFrm" method="POST" action="./index.php" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="<?=$body?>.exe">
	<input type="hidden" name="search" value="">
	<input type="hidden" name="result" value="<?=htmlspecialchars($_POST['result'])?>">

	<div class="box_tab" style="margin-top:0;">
		<ul>
			<li><a href="#" onclick="viewDeliveryStatus(''); return false;" class="<?=$active['']?>">전체<span><?=number_format($total)?></span></a></li>
			<li><a href="#" onclick="viewDeliveryStatus('success'); return false;" class="<?=$active['success']?>">성공<span><?=number_format($success)?></a></li>
			<li><a href="#" onclick="viewDeliveryStatus('faild'); return false;" class="<?=$active['faild']?>">실패<span><?=number_format($faild)?></a></li>
		</ul>
	</div>

	<table class="tbl_col">
		<caption class="hidden">주문일괄배송처리 결과</caption>
		<colgroup>
			<col style="width:150px;">
			<col>
			<col style="width:100px;">
			<col style="width:100px;">
			<col>
		</colgroup>
		<thead>
			<tr>
				<th>주문번호</th>
				<th>주문상품</th>
				<th>주문상태</th>
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
			<td class="left"><?=$data['name']?></td>
            <td><?=$data['stat']?></td>
			<td>
				<?=$data['result']?>
			</td>
			<td class="left explain"><?=$data['msg']?></td>
		</tr>
		<?$idx++;$search_rows++;}?>
		<?}?>
		<?if($search_rows == 0) {?>
		<tr>
			<td colspan="5" class="center">검색된 내역이 없습니다.</td>
		</tr>
		<?}?>
	</table>
</form>
<script type="text/javascript">
var f = document.getElementById('resultFrm');
function viewDeliveryStatus(n) {
	$('.csv').prop('disabled', true);

	f.body.value = 'order@order_multi_result';
	f.search.value = n;
	f.target = '_self';
	f.submit();
}
</script>