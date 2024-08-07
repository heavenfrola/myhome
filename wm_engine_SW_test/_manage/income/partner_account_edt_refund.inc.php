<?PHP

	// 대상 데이터 조회
	$res = $pdo->iterator("select a.*, b.corporate_name from {$tbl['order_account_refund']} a inner join {$tbl['partner_shop']} b on a.partner_no=b.no where a.account_idx='$account_idx' order by a.no desc");

	function parseData($res) {
        $data = $res->current();
        $res->next();
		if($data == false) return false;

		$data['corporate_name'] = stripslashes($data['corporate_name']);
		$data['refund_prc'] = $data['total_prc']-$data['dlv_prc']-$data['fee_prc']+$data['cpn_fee_m'];
		$data['reg_date_str'] = date('Y-m-d H:i', strtotime($data['reg_date']));

		return $data;
	}

	$partner_name = stripslashes($pdo->row("select corporate_name from {$tbl['partner_shop']} where no='{$account['partner_no']}'"));

	if($admin['level'] == 4) {
		$back_url = './?body=order@account_list';
	} else if($account_idx > 0) {
		$back_url = './?body=income@partner_account';
	} else {
		$back_url = './?body=income@partner_account_reg';
	}
	foreach($_GET as $key => $val) {
		if($key == 'body') continue;
		if($key == 'partner_no') continue;

		$back_url .= "&$key=".urlencode($val);
	}

?>
<div class="box_title first">
	<?=$partner_name?> 정산 환불 정보
</div>
<table class="tbl_col tbl_stat">
	<thead>
		<tr>
			<th>주문번호</th>
			<th>업체명</th>
			<th>취소상품금액</th>
			<th>추가배송비</th>
			<th>배송비 정산 환불</th>
			<th>입점수수료</th>
			<th>쿠폰본사부담</th>
			<th>환불요청금액</th>
			<th>상세내역</th>
			<th>반품처리자</th>
			<th>반품일시</th>
		</tr>
	</thead>
	<tbody>
		<?while($data = parseData($res)) {?>
		<tr>
			<td><a href='#' onclick="viewOrder('<?=$data['ono']?>'); return false;"><strong><?=$data['ono']?></strong></a></td>
			<td class="left"><?=$data['corporate_name']?></td>
			<td><?=parsePrice($data['prd_prc'], true)?></td>
			<td><?=parsePrice($data['dlv_prc'], true)?></td>
			<td><?=parsePrice($data['dlv_prc_return'], true)?></td>
			<td><?=parsePrice($data['fee_prc'], true)?></td>
			<td><?=parsePrice($data['cpn_fee_m'], true)?></td>
			<td><?=parsePrice($data['refund_prc'], true)?></td>
			<td><a href="#" onclick="cdetail.open('ono=<?=$data['ono']?>&payment_no=<?=$data['payment_no']?>'); return false;" class="p_color4">[보기]</a></td>
			<td><?=$data['admin_id']?></td>
			<td><?=$data['reg_date_str']?></td>
		</tr>
		<?}?>
	</tbody>
</table>
<div class="box_bottom left">
	<span class="explain">환불 요청금액 = 취소상품금액 - 추가배송비 + 배송비 정산환불 - 입점수수료 + 쿠폰본사부담</span>
</div>
<div class="box_bottom">
	<span class="box_btn"><input type="button" value="뒤로" onclick="location.href='<?=$back_url?>'"></span>
</div>
<script type="text/javascript">
// 주문서 상세보기
var cdetail = new layerWindow('order@order_cancel_info.exe');
</script>