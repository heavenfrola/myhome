<?PHP

	if($data['pay_type']) {
		$pay_type_icn = "<img src='$engine_url/_manage/image/icon/pay{$data['pay_type']}.gif'>";
		if(($data['checkout'] == 'Y' || $data['smartstore'] == 'Y') && ($data['pay_type'] == '24' || $data['pay_type'] == '26')) {
			$pay_type_icn = str_replace('pay24.gif', 'pay24n.gif', $pay_type_icn);
			$pay_type_icn = str_replace('pay26.gif', 'pay26n.gif', $pay_type_icn);
		}
		if($data['pay_type'] != 3) {
			if($data['milage_prc'] > 0 || ($data['checkout'] == 'Y' && $data['point_use'] > 0)) $pay_type_icn .= " <img src='$engine_url/_manage/image/icon/pay+.gif'>";
		}
		if($data['pay_type'] != 6) {
			if($data['emoney_prc'] > 0) $pay_type_icn .= " <img src='$engine_url/_manage/image/icon/pay+6.gif'>";
		}
		if($total_sale5 > 0) {
			$pay_type_icn .= " <img src='$engine_url/_manage/image/icon/pay+c.gif'>";
		}
	}

	if($admin['level'] == 4) {
		include $engine_dir.'/_partner/order/order_view_prc.inc.php';
		return;
	}

	// 총 할인 금액 계산
	foreach($_order_sales as $fn => $fv) {
		$total_sale_prc += ${'total_'.$fn};
	}
	$total_sale_prc += $cpn_free_dlv;
	$total_sale_prc += $data['tax'];
	$total_sale_prc += $data['milage_prc'];
	$total_sale_prc += $data['emoney_prc'];
	$total_sale_prc += $data['sale2_dlv'];
	$total_sale_prc += $data['sale4_dlv'];

?>
<div class="priceinfo">
	<div class="list">
		<div class="frame">
			<table>
				<thead>
					<tr>
						<th><span>총주문액</span></th>
						<th class="sale"><span>할인금액</span></th>
						<th><span>적립금</span></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							<ul>
								<li>└ 상품가격합계 <span><?=parsePrice($prd_prc_sum, true)?> <?=$cfg['currency_type']?></span></li>
								<li>
									└ 배송비
									<?php if ($data['checkout'] != 'Y' && $data['smartstore'] != 'Y' && $data['external_order'] != 'talkpay' && $admin['level'] < 4) { ?><a href="#" onClick="ordExecFromPrdbox('dlv_repay', 1, 1); return false;" class="p_color">변경</a><?php } ?>
									<span><?=parsePrice($data['dlv_prc']-$data['prd_dlv_prc'], true)?> <?=$cfg['currency_type']?></span>
								</li>
								<?php if ($cfg['use_prd_dlvprc'] == 'Y' && $_sum_prd_dlv_prc_cnt > 0) { ?>
								<li>
									└ 개별배송비
									<a href="#" onClick="ordExecFromPrdbox('dlv_repay', 1, 2); return false;" class="p_color">변경</a>
									<span><?=parsePrice($data['prd_dlv_prc'], true)?>  <?=$cfg['currency_type']?></span>
								</li>
								<?php } ?>
								<!--
								<?if($total_ex_dlv > 0) {?>
								<li>
									└ 교환/반품 배송비 <span><?=parsePrice($total_ex_dlv, true)?> <?=$cfg['currency_type']?></span>
								</li>
								<?}?>
								<?if($total_add_dlv > 0) {?>
								<li>
									└ 주문상품금액 배송비 추가 <span><?=parsePrice($total_add_dlv, true)?> <?=$cfg['currency_type']?></span>
								</li>
								<?}?>
								<?if($total_add_dlv < 0) {?>
								<li>
									└ 주문상품금액 배송비 차감 <span>(-) <?=parsePrice(abs($total_add_dlv), true)?> <?=$cfg['currency_type']?></span>
								</li>
								<?}?>
								-->
							</ul>
						</td>
						<td class="sale">
							<ul>
								<?php if ($cpn_free_dlv > 0) { ?>
								<li>
									└ 무료배송쿠폰 <span>(-) <?=parsePrice($cpn_free_dlv, true)?> <?=$cfg['currency_type']?></span>
								</li>
								<?php } ?>
								<?php if ((int)$data['tax']) { ?>
								<li>
									└ + 관세 <span>(+) <?=parsePrice($data['tax'], true)?> <?=$cfg['currency_type']?></span>
								</li>
								<?php } ?>
								<li>
									└ 사용 적립금 <span>(-) <?=parsePrice($data['milage_prc'], true)?> <?=$cfg['currency_type']?></span>
								</li>
								<li>
									└ 사용 예치금 <span>(-) <?=parsePrice($data['emoney_prc'], true)?> <?=$cfg['currency_type']?></span>
								</li>
								<?php foreach ($_order_sales as $fn => $fv) { if(${'total_'.$fn} != 0) { ?>
								<li>
									└ <?=$fv?> <span>(<?=($data[$fn] > 0) ? '-' : '+'?>) <?=parsePrice(abs(${'total_'.$fn}), true)?> <?=$cfg['currency_type']?></span>
								</li>
								<?php }} ?>
								<?php if ($data['sale2_dlv'] > 0) { ?>
								<li>
									└ 무료배송 이벤트 <span>(-) <?=parsePrice($data['sale2_dlv'], true)?> <?=$cfg['currency_type']?></span>
								</li>
								<?php } ?>
								<?php if ($data['sale4_dlv'] > 0) { ?>
								<li>
									└ 회원 무료배송 <span>(-) <?=parsePrice($data['sale4_dlv'], true)?> <?=$cfg['currency_type']?></span>
								</li>
								<?php } ?>
							</ul>
						</td>
						<td>
							<ul>
								<li>
									└ 구매<?=$cfg['milage_name']?> <span><?=parsePrice($data['total_milage']-$data['member_milage']-$total_event_milage, true)?> <?=$cfg['currency_type']?></span>
								</li>
								<?php if ($data['total_milage'] > 0 && $data['member_milage']) { ?>
								<li>
									└ 회원 적립금 <span><?=parsePrice($data['member_milage'], true)?> <?=$cfg['currency_type']?></span>
								</li>
								<?php } ?>
								<?php if ($total_event_milage > 0) { ?>
								<li>
									└ 이벤트 적립금 <span><?=parsePrice($total_event_milage, true)?> <?=$cfg['currency_type']?></span>
								</li>
								<?php } ?>
							</ul>
						</td>
					</tr>
					<tr>
						<td class="last"><b><?=parsePrice($prd_prc_sum+$data['dlv_prc'], true)?></b> <?=$cfg['currency_type']?></td>
						<td class="last sale">(-) <b><?=parsePrice($total_sale_prc, true)?></b> <?=$cfg['currency_type']?></td>
						<td class="last"><b><?=parsePrice($data['total_milage'], true)?></b> <?=$cfg['currency_type']?> (<?=$milage_down?>)</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
	<div class="total">
		<ul>
			<li>총주문액<span><?=parsePrice($prd_prc_sum+$data['dlv_prc'], true)?> 원</span></li>
			<li>할인금액<span>(-) <?=parsePrice($total_sale_prc, true)?> 원</span></li>
			<?php if (($data['checkout'] == 'Y' || $data['smartstore'] == 'Y') && $data['point_use'] > 0) { ?>
			<li>
				NPAY포인트<span>(-) <?=parsePrice($data['point_use'], true)?> <?=$cfg['currency_type']?></span>
			</li>
			<?php } ?>
			<?php if ($data['sale5'] > 0 && $cpn['stype'] == 3) { ?>
			<li>무료배송 쿠폰<span>(-) <?=parsePrice($data['sale5'], true)?> 원</span></li>
			<?php } ?>
		</ul>
	</div>
	<div class="totalpay">
		실결제금액<span><?=$pay_type_icn?> <strong><?=parsePrice($data['pay_prc'], true)?></strong> <?=$cfg['currency_type']?></span>
	</div>
</div>
<div style="clear:both;"></div>
