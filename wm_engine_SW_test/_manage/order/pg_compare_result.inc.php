<?PHP

	$res = json_decode($_POST['result']);

	function parseResult(&$res) {
		global $tbl, $pdo;

		list($key, $data) = each($res);
		if(is_object($data) == false) return false;

		$data->status_str = ($data->status == 'ok') ? '<strong class="p_color">확인</strong>' : '<span class="p_color2">오류</span>';
		if($data->status == 'ok') {
			$ord = $pdo->assoc("select stat, stat2, date1, buyer_name, pay_prc from $tbl[order] where ono='$data->ono'");
			$data->date2 = date('Y-m-d H:i', $ord['date1']);
			$data->pay_prc = parsePrice($ord['pay_prc'], true);
			$data->buyer_name = stripslashes($ord['buyer_name']);
			$data->stat = $ord['stat'];
            if (strpos($ord['stat2'], '@11@') > -1) {
                $data->stat = '11';
            }

			if($data->stat != '11') {
				$data->status_str = '처리완료';
			}
		}

		return $data;
	}
?>
<table class="tbl_col">
	<caption>PG승인결과 대사</caption>
	<colgroup>
		<col style="width:140px;">
		<col style="width:140px;">
		<col style="width:120px;">
		<col style="width:120px;">
		<col style="width:100px;">
		<col>
		<col style="width:140px;">
	</colgroup>
	<thead>
		<tr>
			<th>주문번호</th>
			<th>주문일시</th>
			<th>주문자명</th>
			<th>결제금액</th>
			<th>확인결과</th>
			<th>비고</th>
			<th>입금처리</th>
		</tr>
	</thead>
	<tbody>
		<?while($data = parseResult($res)) {?>
		<tr>
			<td><a href="#" onclick="viewOrder('<?=$data->ono?>')"><?=$data->ono?></a></td>
			<td><?=$data->date2?></td>
			<td><?=$data->buyer_name?></td>
			<td><?=$data->pay_prc?></td>
			<td class="status_<?=$data->ono?>"><?=$data->status_str?></td>
			<td class="left explain"><?=$data->message?></td>
			<td class="btn_<?=$data->ono?>">
				<?if($data->stat == '11') {?>
				<span class="box_btn_s blue"><input type="button" value="<?=$_order_stat[11]?> 해제" onclick="recovery('<?=$data->ono?>')"></span>
				<?}?>
			</td>
		</tr>
		<?}?>
	</tbody>
</table>
<script type="text/javascript">
	function recovery(ono) {
		if(confirm('선택한 <?=$_order_stat[11]?> 주문을 <?=$_order_stat[11]?>상태로로 변경하시겠습니까?') == true) {
			$.post('?', {'body':'order@order_prd_dlv.exe', 'exec':'recovery', 'stat':'2', 'ono':ono}, function() {
				$('.status_'+ono).html('처리완료');
				$('.btn_'+ono).html('');
			});
		}
	}
</script>