<?PHP

	$ii = 0;
	$check_pno = numberOnly($_REQUEST['check_pno']);
	if(is_array($check_pno) && count($check_pno)>0) {
		include_once $engine_dir."/_engine/include/shop.lib.php";
		foreach($check_pno as $key=>$val) {
			$data['member_id'] = "비회원";
			$data = get_info($tbl['order'], "no", $val);
			if(!$data['no']) {
				continue;
			}

			$pay_type = $_pay_type[$data['pay_type']];
			if($data['pay_type'] == 2 || $data['pay_type'] == 4) {
				$pay_type .= " ($data[bank])";
				if($data['bank_name']) {
					$pay_type.=" <u>입금자명 : ".stripslashes($data['bank_name'])."</u>";
				}
				if($data['pay_type'] == 4) {
					$card = get_info($tbl['vbank'], "wm_ono", $ono);
					$pay_type .= " (KCP 거래 번호 : $card[tno])";
				}
			}
			if($data['milage_prc'] > 0 && $data['pay_type'] != 3) $pay_type .= " ( <b>".parsePrice($data['pay_prc'],2)." ".$cfg['currency_type']."</b> ) + 적립금 ( <b>".parsePrice($data['milage_prc'],2)." ".$cfg['currency_type']."</b> )";
			$tmsg = ($data['stat']>10) ? "※ ".$_order_stat[$data['stat']]." 주문입니다" : "";

			$cprd_field = '';
			if($cfg['use_erp_storage'] == 'Y') {
				$cprd_field .= ", storage_no";
			}

			$total_cancel = 0;

            // 주문메모 옵션
            $order_memo_sql = '';
            if ($cfg['order_memo_print'] == 'Y') {
                if ($admin['level'] == 4) {
                    $order_memo_sql = " and admin_no='{$admin['no']}'";
                    $data['memo_cnt'] = $pdo->row("
                        select count(*) from {$tbl['order_memo']} where ono='{$data['ono']}' and type=1 $order_memo_sql
                    ");
                }
            }

            $_prev_set_idx = array();

?>
<style type="text/css" title="">
body {background:#fff;}
</style>
<div class="order_print">
	<?if($tmsg) {?>
	<h1><strong class="p_color2"><?=$tmsg?></strong></h1>
	<?}?>
	<h2>○ 주문상품</h2>
	<table class="tbl_mini full">
		<colgroup>
			<col style="width:60px">
			<col>
			<col style="width:60px">
			<col style="width:100px">
			<col style="width:100px">
			<col style="width:100px">
		</colgroup>
		<thead>
			<tr>
				<th scope="row">번호</th>
				<th scope="row">제품명</th>
				<?if($cfg['prdcode_print'] == 'Y') {?>
				<th scope="row">상품코드</th>
				<?}?>
				<th scope="row">수량</th>
				<th scope="row">상품가격</th>
				<th scope="row">총적립금</th>
				<th scope="row">소계</th>
			</tr>
		</thead>
		<tbody>
			<?php
				$sql = "select * from `{$tbl['order_product']}` p where `ono`='{$data['ono']}' order by `no`";
				$res = $pdo->iterator($sql);
				$idx = 1;
				$total = array();

				$split_big = "/ ";
				$split_small = ":";

                foreach ($res as $prd) {
					if($admin['level'] == 4 && $prd['partner_no'] != $admin['partner_no']) {
						continue;
					}

					$total['total_milage'] += $prd['total_milage'];
					$total['total_prc'] += ($prd['total_prc'] * $prd['buy_ea']);

					$prd['name'] = strip_tags(stripslashes($prd['name']));
					$prd['total_milage'] = parsePrice($prd['total_milage'],2);

					if($prd['price_no']) {
						$price = get_info($tbl['product_price'], "no", $prd['price_no']);
						$prd['sell_prc'] += $price['price'];
						$prd['name'] .= "[".$price['name']."]";
					}
					// 옵션이 있을 경우
					if($prd['option']) {
						$prd['option_str'] = str_replace("<split_big>",$split_big,$prd['option']);
						$prd['option_str'] = str_replace("<split_small>",$split_small,$prd['option_str']);
						$prd['option_str'] = $opt_deco1.$prd['option_str'].$opt_deco2;
					}

					$cprd = $pdo->assoc("select code $cprd_field from $tbl[product] where no='$prd[pno]'");
					if($cfg['order_storage_print'] == 'Y' && $cprd['storage_no'] > 0) {
						$storage = $pdo->assoc("select big, mid, small, depth4 from $tbl[erp_storage] where no='$cprd[storage_no]'");
						$storage_name = getStorageLocation($storage);
					}

					if ($cfg['repay_part'] == "Y" && $prd['stat'] > 5) {
						$data['total_prc'] -= $prd['total_prc'];
						$total_cancel += $prd['total_prc'];
						$prd['buy_ea'] = substr($_order_stat[$prd['stat']], 0, 6);
						$prd['name'] = "<s>{$prd['name']}</s>";
					}

                    if ($prd['set_idx'] && in_array($prd['set_idx'], $_prev_set_idx) == false) { // 세트 메인 상품 출력
                        $sprd = $pdo->assoc("select no, name from {$tbl['product']} where no='{$prd['set_pno']}'");
                        $set_cnt = $pdo->row("select count(*) from {$tbl['order_product']} where ono=? and set_idx=?", array(
                            $data['ono'], $prd['set_idx']
                        ));
                        $set_count = 1;
                    }

                    $is_set_prd = ($prd['set_idx']) ? true : false;

			?>
            <?php if($is_set_prd == true && in_array($prd['set_idx'], $_prev_set_idx) == false) { ?>
            <tr>
                <td><?=$idx?></td>
                <td class="left"><strong><?=strip_tags(stripslashes($sprd['name']))?></strong></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <?php $idx++; } ?>
			<tr>
				<td>
                    <?php
                        if($is_set_prd == true) $idx--;
                        else echo $idx;
                    ?>
                </td>
				<td class="left">
                    <?php
                        if($is_set_prd == true) {
                            echo ($set_cnt == $set_count) ? '└' : '├';
                            $set_count++;
                        }
                    ?>
					<b><?=$prd['name']?></b> <?=$prd['option_str']?>
					<?if($cprd['storage_no'] > 0) {?>
					<div style="margin: 2px 0; color: #666;"><?=$storage_name?></div>
					<?}?>
				</td>
				<? if($cfg['prdcode_print'] == 'Y') { ?>
				<td><?=$cprd['code']?></td>
				<? } ?>
				<td><?=$prd['buy_ea']?></td>
				<td><?=parsePrice($prd['sell_prc'],2)?></td>
				<td><?=$prd['total_milage']?></td>
				<td><?=parsePrice($prd['total_prc'],2)?></td>
			</tr>
			<?php

				$idx++;

                if (in_array($prd['set_idx'], $_prev_set_idx) == false) {
                    $_prev_set_idx[] = $prd['set_idx'];
                }
				}

				$cart_sum_price = $total['total_prc'];
				deliveryPrc();
				$sale1=$data['sale1'];
				$sale2=$data['sale2'];
				$sale3=$data['sale3'];
				totalOrderPrice();

				if (str_replace("@", "", $data['order_gift'])) {
					$_ord_gift = explode("@", $data['order_gift']);
			?>
			<tr>
				<td>사은품</td>
				<td colspan="5" class="left">
				<?php
                foreach ($_ord_gift as $val) {
                    echo $pdo->row("select name from {$tbl['product_gift']} where no='$val'");
                }
				?>
				</td>
			</tr>
			<?}?>
			<tr>
				<td colspan="10" class="left">
				상품 합계 : <b><?=parsePrice($data['prd_prc'],2)?> 원</b> + 배송비 : <?=parsePrice($data['dlv_prc'],2)?> <?=$cfg['currency_type']?><br>
				<?if($data['extra1']>0){?>+ 부가세 : <b><?=parsePrice($data['extra1'],2)?> <?=$cfg['currency_type']?></b><br><?}?>
				<?if($data['sale1']>0){?>- 패키지할인 : <b><?=parsePrice($data['sale1'],2)?> <?=$cfg['currency_type']?></b> <?}?>
				<?if($data['sale2']>0){?>- 이벤트할인 : <b><?=parsePrice($data['sale2'],2)?> <?=$cfg['currency_type']?></b> <?}?>
				<?if($data['sale3']>0){?>- 타임세일 : <b><?=parsePrice($data['sale3'],2)?> <?=$cfg['currency_type']?></b> <?}?>
				<?if($data['sale4']>0){?>- 우수회원할인 : <b><?=parsePrice($data['sale4'],2)?> <?=$cfg['currency_type']?></b> <?}?>
				<?if($data['sale5']>0){?>- 쿠폰할인 : <b><?=parsePrice($data['sale5'],2)?> <?=$cfg['currency_type']?></b> <?}?>
				<?if($total_cancel>0){?>- 주문취소 : <b><?=parsePrice($total_cancel,2)?> <?=$cfg['currency_type']?></b> <?}?>
				<br>
				= 총결제액 : <b><?=parsePrice($data['pay_prc'],2)?> <?=$cfg['currency_type']?></b>
				</td>
			</tr>
		</tbody>
	</table>

	<h2>○ 주문정보</h2>
	<table class="tbl_mini full">
		<colgroup>
			<col style="width:15%">
			<col style="width:35%">
			<col style="width:15%">
			<col style="width:35%">
		</colgroup>
		<tr>
			<th scope="row">주문일시</th>
			<td class="left"><?=date("Y/m/d g:i A", $data['date1'])?></td>
			<th scope="row">주문번호</th>
			<td class="left"><?=$data['ono']?></td>
		</tr>
		<tr>
			<th scope="row">결제방법</th>
			<td colspan="3" class="left"><?=$pay_type?></td>
		</tr>
		<?php
			if($data['pay_type'] == "1" || $data['pay_type'] == "5") { // 신용카드 또는 계좌이체
				$card = get_info($tbl['card'], "wm_ono", $data['ono']);
				if($card['no']) {
					if($data['pay_type'] == "1") {
						if($card['quota'] == "00") $card['quota_str'] = "일시불";
						else $card['quota_str'] = $card['quota']."개월";
					}
					else {
						$card['quota_str'] = "계좌이체";
						if($card['quota'] == "Y") $card['quota_str'] .= " (에스크로)";
					}
		?>
		<tr>
			<th scope="row">카드 정보</th>
			<td colspan="3" class="left">
				<?=$card['card_name']?> (<?=$card['quota_str']?>, 거래 번호 : <?=$card['tno']?>)
			</td>
		</tr>
		<?
				}
			}
		?>
	</table>

	<h2>○ 주문자</h2>
	<table class="tbl_mini full">
		<colgroup>
			<col style="width:15%">
			<col style="width:35%">
			<col style="width:15%">
			<col style="width:35%">
		</colgroup>
		<tr>
			<th scope="row">이름</th>
			<td class="left"><?=$data['buyer_name']?>(<?=$data['member_id']?>) <?if($cfg['blacklist_print'] == 'Y') { echo blackIconPrint('', $data);} ?></td>
			<?if($data['member_no'] > 0 && $cfg['member_level_print'] == 'Y') {?>
			<th scope="row">회원등급</th>
			<td class="left">
			<?
				$member_group = $pdo->assoc("select `level` from `$tbl[member]` where no = '$data[member_no]'");
				$group_name = $pdo->row("select `name` from `$tbl[member_group]` where no = '$member_group[level]'");
				echo $group_name;
			?>
			</td>
			<?} else {?>
			<td colspan="2"></td>
			<?}?>
		</tr>
		<tr>
			<th scope="row">전화번호</th>
			<td class="left"><?=$data['buyer_phone']?></td>
			<th scope="row">휴대폰</th>
			<td class="left"><?=$data['buyer_cell']?></td>
		</tr>
		<tr>
			<th scope="row">이메일</th>
			<td colspan="3" class="left"><?=$data['buyer_email']?></td>
		</tr>
		<?if($cfg['member_memo_print'] == 'Y' && $admin['level'] < 4) {?>
		<tr>
			<th scope="row">회원메모</th>
			<td colspan="3">
				<table class="tbl_mini full">
					<colgroup>
						<col style="width:75px">
						<col>
						<col style="width:70px">
					</colgroup>
					<tr>
						<th scope="col">작성일</th>
						<th scope="col">내용</th>
						<th scope="col">작성자</th>
					</tr>
					<?php
						$mm1 = $pdo->iterator("select * from `$tbl[order_memo]` where `ono`= '{$data['member_id']}' and type = 2 order by `no` asc");
                        foreach ($mm1 as $m1data) {
						$m1data['date'] = date("y/m/d H:i", $m1data['reg_date']);
						$m1data['content'] = nl2br(stripslashes($m1data['content']));
					?>
					<tr>
						<td style="font-size: 11px"><?=$m1data['date']?></td>
						<td class="left"><?=$m1data['content']?></td>
						<td style="font-size: 11px"><?=$m1data['admin_id']?></td>
					</tr>
					<?}?>
				</table>
			</td>
		</tr>
		<?}?>
	</table>

	<h2>○ 배송지</h2>
	<table class="tbl_mini full">
		<colgroup>
			<col style="width:15%">
			<col style="width:35%">
			<col style="width:15%">
			<col style="width:35%">
		</colgroup>
		<tr>
			<th scope="row">이름</th>
			<td class="left"><?=$data['addressee_name']?></td>
			<th scope="row">연락처</th>
			<td class="left"><?=$data['addressee_phone']?>&nbsp;&nbsp;&nbsp;<?=$data['addressee_cell']?></td>
		</tr>
		<? if($data['nations']) { ?>
		<tr>
			<th scope="row">배송국가</th>
			<td colspan="3" class="left"><?=getCountryNameFromCode($data['nations'])?></td>
		</tr>
		<? } ?>
		<tr>
			<th scope="row">주소</th>
			<td colspan="3" class="left">[<?=$data['addressee_zip']?>] <?=$data['addressee_addr1']?> <?=$data['addressee_addr2']?></td>
		</tr>
		<? if($data['delivery_com']) { ?>
		<tr>
			<th scope="row">배송업체</th>
			<td colspan="3" class="left"><?=getDeliveryNameFromNo($data['delivery_com'])?></td>
		</tr>
		<? } ?>
		<tr>
			<th scope="row">주문메세지</th>
			<td colspan="3" class="left"><?=stripslashes(nl2br($data['dlv_memo']))?>&nbsp;</td>
		</tr>
		<?if($cfg['order_memo_print'] != 'N' && $data['memo_cnt'] > 0) {?>
		<tr>
			<th scope="row">관리자메모</th>
			<td colspan="3" class="left">
				<table class="tbl_mini full">
					<colgroup>
						<col style="width:120px">
						<col>
						<col style="width:80px">
					</colgroup>
					<tr>
						<th scope="col">작성일</th>
						<th scope="col">내용</th>
						<th scope="col">작성자</th>
					</tr>
					<?php
						$mm = $pdo->iterator("select * from `{$tbl['order_memo']}` where `ono` = '{$data['ono']}' and type = 1 $order_memo_sql order by `no` asc");
                        foreach ($mm as $mdata) {
						$mdata['date'] = date("y/m/d H:i", $mdata['reg_date']);
						$mdata['content'] = nl2br(stripslashes($mdata['content']));
					?>
					<tr>
						<td style="font-size: 11px"><?=$mdata['date']?></td>
						<td class="left"><?=$mdata['content']?></td>
						<td style="font-size: 11px"><?=$mdata['admin_id']?></td>
					</tr>
					<?}?>
				</table>
			</td>
		</tr>
		<?}?>
	</table>

	<?php
		$pdo->query("update $tbl[order] set print=print+1 where ono='$data[ono]'");

		if($cfg['auto_stat3'] == "Y" && $data['stat'] == "2" && $data['checkout'] != 'Y'&& $data['smartstore'] != 'Y') {
			include_once $engine_dir.'/_engine/include/wingPos.lib.php';

			$stock_pno = null;
			$prd_part = null;

			if($cfg['n_smart_store'] == 'Y') $asql = " and smartstore_ono=''";
			else $asql = '';

			if($admin['level'] == 4) {
				$prd_part = " and partner_no='$admin[partner_no]'";
				$stock_pno = $pdo->row("select group_concat(no) from $tbl[order_product] where ono='$data[ono]' and stat=2 and checkout_ono='' $asql $prd_part");
				if(!$stock_pno) $stock_pno = '0';
				$stock_pno = explode(',', $stock_pno);
			}

			orderStock($data['ono'], 2, 3, $stock_pno);

			$pdo->query("update $tbl[order_product] set stat='3' where ono='$data[ono]' and stat=2 and checkout_ono='' $asql $prd_part");
			$nstat = ordChgPart($data['ono']);
			if($nstat == 3) {
				ordStatLogw($data['ono'], 3);
			}
			unset($asql);
		}

		$ii++;

		if($ii<count($check_pno)) {
	?>
	<table style="page-break-before: always;height:0px" border=0 cellspacing=0 cellpadding=0>
		<tr>
			<td></td>
		</tr>
	</table>
	<?
		}
			}
		}
		if($ii==0) {
	?>
	<div class="empty">
		<p><strong>출력할 주문이 없습니다!</strong></p>
		<span class="box_btn gray"><a href="javascript:" onClick="window.close()">창닫기</a></span>
	</div>
	<?
			exit();
		}
	?>
</div>
<script type="text/javascript">
	window.onload=new function(){
		if (confirm('<?=$ii?>건의 주문을 인쇄하시겠습니까?'))
		{
			$('iframe').remove();
			window.print();
		}
	}
</script>