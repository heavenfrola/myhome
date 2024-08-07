<?PHP

	if($_POST['exec'] == 'process') {
		$complex_no = numberOnly($_POST['complex_no']);
		$data = preg_replace("/[^0-9,]/", "", $_POST['data']);
		$data = explode(',', trim($data, ','));

		if(count($data) < 1) exit;

		$sort = 1;
		foreach($data as $no) {
			$r = $pdo->query("update $tbl[order_product] set dlv_hold_order='$sort' where complex_no='$complex_no' and dlv_hold='Y' and no='$no'");
			if($r) $sort++;
		}
		exit;
	}

	$opno = numberOnly($_GET['opno']);
	$oprd = $pdo->assoc("select ono, pno, complex_no from $tbl[order_product] where no='$opno'");

	$res = $pdo->iterator("
		select
			o.ono, o.buyer_name, o.date1, o.date2, o.member_no, o.member_id, o.addressee_addr1,
			p.no, p.stat, p.buy_ea
		from $tbl[order] o inner join $tbl[order_product] p using(ono)
		where p.complex_no='$oprd[complex_no]' and p.dlv_hold='Y' and p.stat between 1 and 3
		order by dlv_hold_order asc
	");

	function parseHold($res) {
		global $_order_stat, $_order_color;

		$data = $res->current();
        $res->next();
		if($data == false) return false;

		$data = array_map('stripslashes', $data);
		$data['date1_s'] = date('y-m-d H:i', $data['date1']);
		$data['date2_s'] = ($data['date2']) ? date('y-m-d H:i', $data['date2']) : '-';
		$data['stat_s'] = $_order_stat[$data['stat']];
		$data['stat_c'] = $_order_color[$data['stat']];

		$GLOBALS['idx']++;

		return $data;
	}

?>
<div class='dialogLayout' style="padding: 0 10px;">
	<input type="hidden" name="holdOrderComplexNo" value="<?=$oprd['complex_no']?>">
	<table class="tbl_inner line full" style="border-top: solid 1px #e8e8e8;">
		<thead>
			<tr>
				<th>순위</th>
				<th>주문번호</th>
				<th>주문자명</th>
				<th>회원아이디</th>
				<th>입금일시</th>
				<th>배송주소</th>
				<th>주문수량</th>
				<th>상태</th>
				<th>순서</th>
			</tr>
		</thead>
		<tbody>
			<?while($data = parseHold($res)) {?>
			<tr>
				<td>
					<?=$idx?>
					<input type="hidden" class="holdOrderNo" value="<?=$data['no']?>">
				</td>
				<td><a href="#" onclick="viewOrder('<?=$data['ono']?>#'); return false;"><?=$data['ono']?></a></td>
				<td><?=$data['buyer_name']?></td>
				<td><a href="#" onclick="viewMember('<?=$data['member_no']?>', '<?=$data['member_id']?>'); return false;"><?=$data['member_id']?></a></td>
				<td><?=$data['date2_s']?></td>
				<td><?=$data['addressee_addr1']?></td>
				<td><?=$data['buy_ea']?></td>
				<td><span style="color:<?=$data['stat_c']?>"><?=$data['stat_s']?></span></td>
				<td>
					<span class="box_btn_s"><input type="button" value="▲" onclick="holdOrderChg(this, -1);"></span>
					<span class="box_btn_s"><input type="button" value="▼" onclick="holdOrderChg(this, +1);"></span>
				</td>
			</tr>
			<?}?>
		</tbody>
	</table>
	<div class="box_bottom inner">
		<span class="box_btn blue"><input type="button" value="확인" onclick="holdOrderSave(this)"></span>
		<span class="box_btn"><input type="button" value="닫기" onclick="holdOrderClose();"></span>
	</div>
</div>