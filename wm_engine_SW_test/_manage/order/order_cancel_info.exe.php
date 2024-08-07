<?PHP

	printAjaxheader();

	$ono = addslashes($_GET['ono']);
	$pno = addslashes($_GET['pno']);
	$pno2 = addslashes($_GET['pno2']);
	$payment_no = numberOnly($_GET['payment_no']);

	if($payment_no > 0) $w .= " and no='$payment_no'";
	else if($pno2) $w .= "and pno2 like '%@$pno2@%'";
	else $w .= "and pno like '%@$pno@%'";

	$data = $pdo->assoc("select * from $tbl[order_payment] where ono='$ono' and type>0 $w order by no desc limit 1");

	if($data['cpn_no'] > 0) {
		$repay_cpn = stripslashes($pdo->row("select name from $tbl[coupon_download] where no='$data[cpn_no]'"));
	} else {
		$repay_cpn = '없음';
	}

	$afield = '';
	if($admin['level'] == 4) {
		$afield = ", p.partner_no";
	}
	if($cfg['ts_use'] == 'Y') {
		$afield .= ", o.sale3";
	}

	$oprds = $oprds2 = array();
	for($i = 1; $i <= 2; $i++) {
		$suffix = ($i == 1) ? '2' : '';
		$pno = trim(str_replace('@', ',', preg_replace('/^@|@$/', '', $data['pno'.$suffix])));
		if(!$pno) continue;
		$res = $pdo->iterator("
			select
				o.no, o.name, total_prc, sale2, sale4, sale5, sale6, buy_ea,
				p.updir, p.upfile3, o.stat $afield
				from $tbl[order_product] o inner join $tbl[product] p on o.pno=p.no
			where ono='$ono' and o.no in ($pno)
		");
        foreach ($res as $prd) {
			if($admin['level'] == 4 && $prd['partner_no'] != $admin['partner_no']) { // 파트너 열람권한
				alert('열람 권한이 없습니다.');
				return;
			}
			$prd['name'] = stripslashes($prd['name']);
			$prd['total_sale'] = $prd['sale2']+$prd['sale3']+$prd['sale4']+$prd['sale5']+$prd['sale6'];
			$prd['prc'] = $prd['total_prc']-$prd['total_sale'];
			if($i == 1) $total_prd_prc += $prd['prc'];
			if($i == 2) $total_new_prc += $prd['prc'];

			${'oprds'.$suffix}[] = $prd;
		}
		$img_url = getFileDir('_data/product');
	}

	$_pay_type[0] = '미입금 주문';

	$_mng_name = getMngNameCache();

?>
<div id="popupContent" class="popupContent layerPop" style="width:600px;">
	<div id="header" class="popup_hd_line">
		<h1 id="logo"><img src="<?=$engine_url?>/_manage/image/wisa.gif" alt="WISA."></h1>
		<div id="mngTab_pop">상세 취소정보</div>
	</div>
	<div id="popupContentArea">
		<?for($i = 1; $i <= 2; $i++) { $suffix = ($i == 1) ? '2' : ''; $title = ($i == 1) ? '취소' : '교환배송'; if(count(${'oprds'.$suffix}) < 1) continue;?>
		<table class="tbl_col">
			<caption class="hidden">상세 취소정보</caption>
			<colgroup>
				<col>
				<col span="5" style="width:10%">
			</colgroup>
			<thead>
				<tr>
					<th scope="col"><?=$title?> 상품</th>
					<th scope="col">구매수량</th>
					<th scope="col">상품금액</th>
					<th scope="col">총할인액</th>
					<th scope="col">소계</th>
					<th scope="col">현재상태</th>
				</tr>
			</thead>
			<tbody>
				<?foreach(${'oprds'.$suffix} as $prd) {?>
				<tr>
					<td class="left">
						<div class="box_setup">
							<div class="thumb"><img src="<?=$img_url?>/<?=$prd['updir']?>/<?=$prd['upfile3']?>" style="height:30px;"></div>
							<p class="title"><?=$prd['name']?></p>
						</div>
					</td>
					<td><?=parsePrice($prd['buy_ea'],true)?></td>
					<td><?=parsePrice($prd['total_prc'],true)?> <?=$cfg['currency_type']?></td>
					<td><?=parsePrice($prd['total_sale'],true)?> <?=$cfg['currency_type']?></td>
					<td><?=parsePrice($prd['prc'],true)?> <?=$cfg['currency_type']?></td>
					<td><?=$_order_stat[$prd['stat']]?></td>
				</tr>
				<?}?>
			</tbody>
		</table>
		<?}?>
		<table class="tbl_row">
			<caption class="hidden">상태</caption>
			<colgroup>
				<col style="width:25%">
				<col>
			</colgroup>
			<tr>
				<th scope="row">처리상태</th>
				<td><?=$_order_payment_stat[$data['stat']]?></td>
			</tr>
			<?if($data['reason']) {?>
			<tr>
				<th scope="row">사유</th>
				<td>
					<?=stripslashes($data['reason'])?>
					<div><?=nl2br(stripslashes($data['comment']))?></div>
				</td>
			</tr>
			<?}?>
			<?if($total_prd_prc > 0) {?>
			<tr>
				<th scope="row">총 취소 상품 금액</th>
				<td><?=parsePrice($total_prd_prc,true)?> <?=$cfg['currency_type']?></td>
			</tr>
			<?}?>
			<?if($total_new_prc > 0) {?>
			<tr>
				<th scope="row">총 추가 상품 금액</th>
				<td><?=parsePrice($total_new_prc,true)?> <?=$cfg['currency_type']?></td>
			</tr>
			<?}?>
			<?if($data['dlv_prc'] > 0) {?>
			<tr>
				<th scope="row">기본 배송비</th>
				<td><?=parsePrice($data['dlv_prc'],true)?> <?=$cfg['currency_type']?></td>
			</tr>
			<?}?>
			<?if($data['add_dlv_prc'] != 0) {?>
			<tr>
				<th scope="row">주문 상품 금액별<br>배송비 변경</th>
				<td><?=parsePrice($data['add_dlv_prc'],true)?> <?=$cfg['currency_type']?></td>
			</tr>
			<?}?>
			<?if($data['ex_dlv_prc'] != 0) {?>
			<tr>
				<th scope="row">교환/반품/추가 배송비</th>
				<td><?=$_order_payment_dlv[$data['ex_dlv_type']]?> <?=number_format($data['ex_dlv_prc'])?> <?=$cfg['currency_type']?></td>
			</tr>
			<?}?>
			<?if($data['repay_emoney'] > 0) {?>
			<tr>
				<th scope="row">사용예치금 환불</th>
				<td><?=parsePrice($data['repay_emoney'],true)?> <?=$cfg['currency_type']?></td>
			</tr>
			<?}?>
			<?if($data['repay_milage'] > 0) {?>
			<tr>
				<th scope="row">사용적립금 환불</th>
				<td><?=parsePrice($data['repay_milage'],true)?> <?=$cfg['currency_type']?></td>
			</tr>
			<?}?>
			<?if($repay_cpn > 0) {?>
			<tr>
				<th scope="row">사용쿠폰 복구</th>
				<td><?=$repay_cpn?></td>
			</tr>
			<?}?>
			<?if($data['pay_type'] > 0) {?>
			<tr>
				<th scope="row">총 <?=$_order_payment_type[$data['type']]?> 금액</th>
				<td><?=parsePrice($data['amount'],true)?> <?=$cfg['currency_type']?></td>
			</tr>
			<tr>
				<th scope="row"><?=$_order_payment_type[$data['type']]?> 방법</th>
				<td><?=$_pay_type[$data['pay_type']]?></td>
			</tr>
			<?}?>
			<?if($data['bank']) {?>
			<tr>
				<th scope="row">환불/입금 계좌</th>
				<td>
					<?=$data['bank']?>
					<?=$data['bank_account']?>
					<?=$data['bank_name']?>
				</td>
			</tr>
			<?}?>
			<tr>
				<th scope="row">등록</th>
				<td><?=date('Y-m-d H:i', $data['reg_date'])?> | <?=$_mng_name[$data['reg_id']]?></td>
			</tr>
			<?if($data['confirm_date'] > 0) {?>
			<tr>
				<th scope="row">승인/완료</th>
				<td><?=date('Y-m-d H:i', $data['confirm_date'])?> | <?=$_mng_name[$data['confirm_id']]?></td>
			</tr>
			<?}?>
		</table>
		<div class="pop_bottom">
			<span class="box_btn_s gray"><input type="button" value="닫기" onclick="cdetail.close();"></span>
		</div>
	</div>
</div>