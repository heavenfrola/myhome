<?PHP

	printAjaxHeader();

	$ono = addslashes(trim($_POST['ono']));
	$stat = numberOnly($_POST['stat']);
	$ext = $_POST['ext'];
	$type = numberOnly($_POST['type']);
	$repay_method = numberOnly($_POST['repay_method']);

	$ord = $pdo->assoc("select * from `$tbl[order]` where `ono` = '$ono'");

	if($stat == "2") {
		if($ext == 1) {
			$dlv_prc = numberOnly($_POST['dlv_prc']);
			if(!$dlv_prc || $repay_method == 1) $dlv_prc = 0;
			if($repay_method == 2) $dlv_prc = 0;
			if($ord['prd_dlv_prc'] > 0) $dlv_prc += $ord['prd_dlv_prc']; // 개별 배송비
			$dlv_minus = $ord['dlv_prc']-$dlv_prc;
			$payment_type = $dlv_minus > 0 ? 1 : 2;

			if($dlv_minus == 0) msg('변경할 배송비가 현재 배송비와 같습니다.');
			if($dlv_minus < 0 && $ord['stat'] > 1) msg('이미 입금이 완료된 주문서이므로 배송비를 추가할 수 없습니다.');
		} else { // 개별 배송비
			$update_qry = array();
			$dlv_minus = 0;
			foreach($_POST['prd_dlv_prc'] as $opno => $prc) {
				if($repay_method == '1') $prc = 0;
				$update_qry[] = "update {$tbl['order_product']} set prd_dlv_prc='$prc' where no='$opno'";

				$prc = parsePrice($prc);
				$prc_org = parsePrice($_POST['prd_dlv_prc_org'][$opno]);
				$dlv_minus += ($prc_org-$prc);
				$_tot_prd_dlv_prc += $prc;

				if($prc > $prc_org && $ord['stat'] > 1) msg('이미 입금이 완료된 주문서이므로 배송비를 추가할 수 없습니다.');
			}
		}

		if(!$ord['member_no'] && $type == 1 && $dlv_minus > 0) msg('비회원 주문이므로 적립금으로 환불할수 없습니다.');
		if(!$ord['member_no'] && $type == 2 && $dlv_minus > 0) msg('비회원 주문이므로 예치금으로 환불할수 없습니다.');

		if(is_array($update_qry) == true) {
			foreach($update_qry as $_qry) {
				$pdo->query($_qry);
			}
		}

		$pdo->query("update `$tbl[order]` set `dlv_prc`='$dlv_prc', `total_prc`=`total_prc`-$dlv_minus, `pay_prc`=`pay_prc`-'$dlv_minus' where `ono`='$ono'");

		if($type == 1 && $dlv_minus > 0) {
			$amember  = get_info($tbl[member], "no", "$ord[member_no]");
			ctrlMilage("+", 3, $dlv_minus, $amember,  "[$ono] 배송비 환불", "", $admin['admin_id'], $ono);
			ordStatLogw($ono,81, "", $dlv_minus);

			$pay_type = 3;
			$repay_milage = $dlv_minus;
		} else if($type == 2 && $dlv_minus > 0) {
			$amember  = get_info($tbl[member], "no", "$ord[member_no]");
			ctrlEmoney("+", 3, $dlv_minus, $amember,  $ono." 배송비 환불", "", $admin['admin_id'], $ono);
			ordStatLogw($ono,81, "", $dlv_minus);

			$pay_type = 6;
			$repay_emoney = $dlv_minus;
		} else {
			ordStatLogw($ono, 80, "", $dlv_prc);
		}

		createPayment(array(
			'ono' => $ono,
			'pay_type' => $pay_type,
			'type' => $payment_type,
			'amount' => ($dlv_minus)*-1,
			'repay_milage' => $repay_milage,
			'repay_emoney' => $repay_emoney,
			'dlv_prc' => ($dlv_minus)*-1,
			'reason' => '배송비 수정',
			'comment' => $_POST['comment'],
			'copytomemo' => $_POST['copytomemo']
		));

		msg("배송비가 수정되었습니다", "reload" ,"parent");
		return;
	}

	if($ext == '2') {
		$pres = $pdo->iterator("
			select op.no, op.name, op.option, op.prd_dlv_prc, op.stat
			from {$tbl['order_product']} op inner join {$tbl['product']} p on op.pno=p.no
			where op.ono='$ono' and op.repay_prd_dlv_prc=0 and p.delivery_set>0
			order by op.stat asc, op.no asc
		");
	}

	ob_start();
?>
<div>
	<ul class='list_msg'>
		<li>
			현재 책정된 기본 배송비는 <span class="p_color"><?=number_format($cfg['delivery_fee'])?></span> 원이며,
			이 주문서의 배송비는 <span class='p_color'><?=number_format($ord['dlv_prc'])?></span> 원 입니다.
		</li>
	</ul>
	<table class="tbl_row">
		<caption class="hidden">배송비 변경</caption>
		<colgroup>
			<col style="width:20%;">
		</colgroup>
		<?if($ext == '1') {?>
		<tr>
			<th scope="row">배송비 수정방법</th>
			<td>
				<label class="p_cursor"><input type="radio" name="repay_method" value="1" checked> 배송비 전체 차감</label><br>
				<label class="p_cursor"><input type="radio" name="repay_method" value="3"> 배송비 수정</label> -> <input type="text" name="dlv_prc" value="<?=parsePrice($ord['dlv_prc']-$ord['prd_dlv_prc'])?>" class="input" size="6"> 원
				<span class="explain">(배송비가 증가되는 경우는 입금이전에만 가능합니다)</span>
			</td>
		</tr>
		<?} elseif($ext == '2') {?>
		<tr>
			<th scope="row">개별 배송비 수정방법</th>
			<td>
				<label class="p_cursor"><input type="radio" name="repay_method" value="1" checked> 개별 배송비 전체 차감</label><br>
				<label class="p_cursor"><input type="radio" name="repay_method" value="2" checked> 배송비 수정</label>
				<table class="tbl_inner line full">
					<caption class="hidden">배송비 변경 대상</caption>
					<colgroup>
						<col />
						<col />
						<col style="width: 120px;">
					</colgroup>
					<thead>
						<tr>
							<th scope="row">상품명</th>
							<th scope="row">옵션</th>
							<th scope="row">배송비</th>
							<th scope="row">상태</th>
						</tr>
					</thead>
					<?php foreach ($pres as $pdata) {?>
					<tr>
						<td class="left"><?=stripslashes($pdata['name'])?></td>
						<td class="left"><?=parseOrderOption($pdata['option'])?></td>
						<td>
							<input type="hidden" name="prd_dlv_prc_org[<?=$pdata['no']?>]" value="<?=parsePrice($pdata['prd_dlv_prc'])?>">
							<input type="text"
								name="prd_dlv_prc[<?=$pdata['no']?>]"
								class="input right"
								size="6"
								value="<?=parsePrice($pdata['prd_dlv_prc'])?>"
								onfocus="this.select()"
							>
							<?=$cfg['currency_type']?>
						</td>
						<td><?=$_order_stat[$pdata['stat']]?></td>
					</tr>
					<?}?>
				</table>
			</td>
		</tr>
		<?}?>
		<tr>
			<th scope="row">차감시 환불방법</th>
			<td>
				<label class="p_cursor"><input type="radio" name="type" value="2" checked> 고객 예치금으로 환불</label>
				<label class="p_cursor"><input type="radio" name="type" value="1"> 고객 적립금으로 환불</label>
				<label class="p_cursor"><input type="radio" name="type" value="3"> 환불 없음</label>
			</td>
		</tr>
		<tr>
			<th scope='row'>상세사유</th>
			<td>
				<label><input type='checkbox' name='copytomemo' value='Y'> 입력한 상세 사유를 메모에도 등록</label>
				<p style='margin-top: 5px;'><textarea class='txta' name='comment' cols='80' rows='5'></textarea></p>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="button" value="승인" onclick="ordExecFromPrdbox('dlv_repay', 2, <?=$ext?>);"></span>
		<span class="box_btn gray"><input type="button" value="닫기" onclick="layTgl(repayDetail);"></span>
	</div>
</div>
<?
	$script = php2java(ob_get_contents());
	ob_end_clean();
?>
<script type="text/javascript">
	parent.prdStat="<?=$stat?>";
	var obj = parent.document.getElementById("repayDetail");
	if(obj){
		obj.innerHTML="<?=$script?>";
		obj.style.display="block";
	} else {
		window.alert('배송비 변경은 부분취소/환불 기능이 활성화 되어있어야 사용가능합니다\n해당 설정은 쇼핑몰관리-운영정보-주문설정에서 가능합니다');
	}
</script>