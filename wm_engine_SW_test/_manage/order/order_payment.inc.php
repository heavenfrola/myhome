<?php if (count($payments) > 0) { ?>
<tr>
	<th scope="row">결제내역</th>
	<td colspan="2">
		<table class="tbl_inner line full">
			<caption class="hidden">결제내역</caption>
			<colgroup>
				<col style="width:80px">
				<col>
				<col>
				<col style="width:110px">
				<col style="width:110px">
				<col style="width:110px">
				<col style="width:80px">
			</colgroup>
			<thead>
				<tr>
					<th scope="row">종류</th>
					<th scope="row">사유</th>
					<th scope="row">금액</th>
					<th scope="row">결제방법</th>
					<th scope="row">처리일시</th>
					<th scope="row">처리자</th>
					<th scope="row">상태</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($payments as $val) { ?>
				<tr>
					<td rowspan="2">
						<strong><?=$_order_payment_type[$val['type']]?></strong>
					</td>
					<td rowspan="2">
						<?=stripslashes($val['reason'])?>
					</td>
					<td class="right">
						<ul>
							<li><?=parsePrice($val['amount'], true)?> <?=$cfg['currency_type']?></li>
							<?php if ($val['emoney_prc'] > 0) { ?>
							<li>+ 예치금 사용 : <strong class="p_color3"><?=parsePrice($val['emoney_prc'])?></strong> <?=$cfg['currency_type']?></li>
							<?php } ?>
							<?php if ($val['milage_prc'] > 0) { ?>
							<li>+ 적립금 사용 : <strong class="p_color4"><?=parsePrice($val['milage_prc'])?></strong> <?=$cfg['currency_type']?></li>
							<?php } ?>
							<?php if ($val['repay_emoney'] > 0) { ?>
							<li>- 예치금 복구 : <strong class="p_color3"><?=parsePrice($val['repay_emoney'])?></strong> <?=$cfg['currency_type']?></li>
							<?php } ?>
							<?php if ($val['repay_milage'] > 0) { ?>
							<li>- 적립금 복구 : <strong class="p_color4"><?=parsePrice($val['repay_milage'])?></strong> <?=$cfg['currency_type']?></li>
							<?php } ?>
							<?php if ($val['type'] == 1 && $val['cpn_no']) { ?>
							<li>쿠폰 반환</li>
							<?php } ?>
						</ul>
					</td>
					<td>
						<?php if ($val['pay_type']) { ?>
							<?=nl2br($_pay_type[$val['pay_type']])?>
						<?php } else if($val['amount'] == 0) { ?>

						<?php } else { ?>
							입금전 변경
						<?php } ?>
					</td>
					<td>
						<?=date('y-m-d H:i', $val['reg_date'])?>
					</td>
					<td rowspan="2"><?=$val['reg_id']?></td>
					<td rowspan="2">
						<?php if ($val['stat'] == 1 && $admin['level'] < 4) { ?>
						<span class="box_btn_s blue"><input type="button" value="완료" onclick="orderPaymentComplete(<?=$val['no']?>)"></span>
						<?php } else { ?>
						<?=$_order_payment_stat[$val['stat']]?>
						<?php } ?>
					</td>
				</tr>
				<tr>
					<td colspan="3" class="left">
						<?php if ($val['pay_type'] == 2) { ?>
						입금 계좌정보 : <?=$val['bank']?> <?=$val['bank_account']?> <?=$val['bank_name']?>
						<?php } ?>
						<div class="explain"><?=nl2br(stripslashes($val['comment']))?></div>
						<?php if ($val['account_refund']['no']) { ?>
						<div class="explain">
							이 <?=$_order_payment_type[$val['type']]?> 작업으로 인한 입점업체 정산 환불이 <strong class="p_color4"><?=($val['account_refund']['account_idx'] > 0) ? '정산등록' : '정산등록 전'?></strong>상태 입니다.
							<?php if ($val['account_refund']['account_idx'] == 0) { ?>
							<span class="box_btn_s2"><input type="button" onclick="accountRefundDel(<?=$val['account_refund']['no']?>);" value="<?=($val['account_refund']['del_yn'] == 'Y') ? '복구' : '숨김'?>" /></span>
							<?php } ?>
						</div>
						<?php } ?>
					</td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</td>
</tr>
<?php } ?>