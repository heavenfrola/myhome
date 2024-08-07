<?PHP

	use Wing\API\Naver\CheckoutApi4;
    use Wing\API\Naver\CommerceAPI;
	use Wing\API\Kakao\KakaoTalkStore;

	/* +----------------------------------------------------------------------------------------------+
	' |  주문상세 상품리스트
	' +----------------------------------------------------------------------------------------------+*/

	if($_GET['soprd']) {
		$soprd = explode('@', preg_replace('/^@|@#/', '', $_GET['soprd']));
	}

	$_prd_stats = $_prev_set_idx = array();
	$_npay_claim_types = $_npay_hold_types = array();
	$cprd_field = '';
	if($cfg['use_erp_storage'] == 'Y') {
		$cprd_field .= ", storage_no";
	}
	if($cfg['use_prd_dlvprc'] == 'Y') {
		$cprd_field .= ", delivery_set";
	}

	// 주문메모 파싱
	function parseOrderProductStat($res) {
		global $_cache_admin_name, $_order_stat, $_order_color;

		$data = $res->current();
        $res->next();
		if(!$data['no']) return false;

		if($data['old_stat']) $data['old_stat'] = '신규등록';
		$data['ori_stat'] = sprintf("<span style='color:%s'>%s</span>", $_order_color[$data['ori_stat']], $_order_stat[$data['ori_stat']]);
		$data['stat'] = sprintf("<span style='color:%s'>%s</span>", $_order_color[$data['stat']], $_order_stat[$data['stat']]);

		if($data['admin_id']) {
			$data['admin_id'] = $_cache_admin_name[$data['admin_id']];
		} else if($data['member_id']) {
			$data['is_member'] = 'Y';
			$data['admin_id'] = "<a href='#' onclick=\"viewMember('','$data[member_id]'); return false;\">$data[member_id]</a>";
		}
		return $data;
	}

	$_sum_prd_dlv_prc_cnt = 0; // 개별배송 대상 상품 수

    // 스마트 스토어 주문 정보 수집
    if ($data['smartstore'] == 'Y') {
        $op = $pdo->iterator("select smartstore_ono from {$tbl['order_product']} where ono=?", array(
            $ono
        ));
        $productOrderIds = array();
        foreach ($op as $p) {
            if ($p['smartstore_ono']) {
                $productOrderIds[] = $p['smartstore_ono'];
            }
        }
        $CommerceAPI = new CommerceAPI();
        $ret = $CommerceAPI->ordersQuery($productOrderIds);
        $productOrders = array();
        foreach ($ret->data as $productOrder) {
            $productOrders[$productOrder->productOrder->productOrderId] = $productOrder;
        }
        unset($op, $productOrderIds);
    }

?>
<?php foreach ($_repay_part_stat as $key => $val) { $hide = ($key == 0) ? '' : 'none'; unset($_prev_partner_no); ?>
<table id="edt_layer_<?=$key?>" class="tbl_col" style="display:<?=$hide?>; border-top:0; table-layout:auto;">
	<caption class="hidden">주문상세 상품리스트</caption>
	<colgroup>
		<col style="width: 35px;">
		<col style="width: 35px;">
		<col>
		<col style="width: 35px;">
		<col style="width: 70px;">
		<col style="width: 115px;">
		<col style="width: 70px;">
		<?php if ($cfg['use_prd_dlvprc'] == 'Y') { ?>
		<col style="width: 70px;">
		<?php } ?>
		<col style="width: 70px;">
		<col style="width: 100px;">
		<col style="width: 70px;">
	</colgroup>
	<thead>
		<tr>
			<th scope="col">
				<?php if ($is_multi_partner < 2) { ?>
				<input type="checkbox" id="prd_check_all_0" class="check_pno_<?=$key?> grp_check grp_check_0_<?=$key?>" onclick="checkAll($('.check_pno_<?=$key?>'), this.checked)" data-partner_no='0' data-key="<?=$key?>">
				<?php } ?>
			</th>
			<th scope="col">번호</th>
			<th scope="col">제품명</th>
			<th scope="col">수량</th>
			<th scope="col">상품가격</th>
			<th scope="col">할인적용</th>
			<th scope="col">총적립금</th>
			<?php if ($cfg['use_prd_dlvprc'] == 'Y') { ?>
			<th scope="col">개별배송비</th>
			<?php } ?>
			<th scope="col">소계</th>
			<th scope="col">주문상태</th>
			<th scope="col">속성</th>
		</tr>
	</thead>
	<tbody>
		<?PHP

			$idx = 1;
			$total = array();
			$is_postpone = 0;
			$pnm = ($key == 0) ? 'prdStat' : 'prdStat'.$key;

			$_sum_buy_ea = 0;
			$_sum_prd_prc = 0;
			$_sum_sale_prc = 0;
			$_sum_milage = 0;
			$_sum_total_prc = 0;
			$_sum_prd_dlv_prc = 0; // 개별 배송비 합계
			$_prev_set_idx[$key] = array(); // 세트 메인 출력을 위한 캐쉬

            if (isset(${$pnm}) == false) ${$pnm} = array();
			for($ii=0; $ii < count(${$pnm}); $ii++) {
				$prd = $pdo->assoc("select * from $tbl[order_product] where no='".${$pnm}[$ii]."'");

                $prd['naverpay'] = ($prd['checkout_ono'] || $prd['smartstore_ono']) ? 'Y' : 'N';

				$partner_name = getPartnerName($prd['partner_no']);
				if(!$partner_name) $partner_name = $cfg['company_mall_name'];

				// 타 입점사 상품 처리
				if($admin['partner_no'] > 0 && $prd['partner_no'] != $admin['partner_no']) {
					$prd = array(
						'name' => '타사상품',
						'stat' => $prd['stat'],
					);
					$partner_name = '타사 주문 상품';
				}

				// 본사 배송 입점사 상품 배열
				$prd['partner_no2'] = ($prd['dlv_type'] == 1) ? '0' : $prd['partner_no'];

				// 요약 합계 정보
				if($key == 0) {
					$prd_prc_sum += ($prd['sell_prc']*$prd['buy_ea']);

					foreach($_order_sales as $fn => $fv) {
						${'total_'.$fn} += $prd[$fn];
					}
				}
				$_prd_stats[] = $prd['stat'];
				$total['total_milage'] += $prd['total_milage'];
				$total['total_prc'] += ($prd['total_prc']*$prd['buy_ea']);
				$total_event_milage += $prd['event_milage'];

				// 옵션명
				$prd['option_str'] = parseOrderOption($prd['option'], ', ', ':');

				$prd['etc'] = stripslashes($prd['etc']);

				// 상품 이미지
				$cprd = $pdo->assoc("select no, hash, wm_sc, stat, updir, upfile3, w3, h3 $cprd_field from $tbl[product] where no='$prd[pno]'");
				$cprd = shortCut($cprd);
				$img = prdImg(3, $cprd, 50, 50);

				// 창고 정보
				if($cprd['storage_no'] > 0) {
					$storage = $pdo->assoc("select big, mid, small, depth4 from $tbl[erp_storage] where no='$cprd[storage_no]'");
					$storage_name = getStorageLocation($storage);
				}

				if(is_array($soprd)) {
					$prd['soprd'] = in_array($prd['no'], $soprd) ? 'checked' : '';
					if($prd['soprd'] == 'checked') $soprd_ck++;
				}

				// 윙Pos
				if($prd['complex_no'] > 0) {
					$wingpos = $pdo->assoc("select curr_stock(complex_no) as stock, force_soldout from erp_complex_option where complex_no='$prd[complex_no]'");
					$erp_qty = number_format($wingpos['stock']);
					$erp_qty = ($wingpos['force_soldout'] == 'N') ? '무제한' : $erp_qty.' ea';
				}

				if($prd['repay_prc'] > 0) $prd['repay_prc'] += $prd['prd_dlv_prc'];
				$prd['prd_total_sale_prc'] = $prd['repay_prc']+getOrderTotalSalePrc($prd)+$prd['saled'];

				// 배송지 주소 변경내역
				if(isTable($tbl['order_addr_log'])) {
					$opaddr_cnt = $pdo->row("select count(*) from $tbl[order_addr_log] where ono='$ono' and opno='$prd[no]'");
				}

				// 주문상품 속성
				$oprd_attr1 = ($prd['dlv_hold'] != 'Y') ? 'hidden' : ''; // 배송보류
				$oprd_attr2 = ($opaddr_cnt == 0) ? 'hidden' : ''; // 배송지 변경

				// 배송 속성
				if($prd['dlv_hold'] == 'Y' && $prd['stat'] < 11) $is_postpone++;
				if($prd['dlv_no'] > 0) {
					$_temp = getDlvUrl($prd);
					$prd['dlv_name'] = $_temp['name'];
					$prd['dlv_url'] = $_temp['url'];
				}

				// 네이버페이 클레임 정보
				$npay_claim = array();
				if($prd['checkout_ono']) {
					if(!$checkout) {
						$checkout = new CheckoutApi4(true);
					}
					$nprd = $checkout->api('GetProductOrderInfoList', $prd['checkout_ono']);
					$npay_claim = $checkout->getClaimInfo($nprd[0]);
					if($npay_claim['ClaimType']) {
						$_npay_claim_types[] = $npay_claim['ClaimType'];
					}

					if(in_array($prd['stat'], array(12, 16, 22, 23, 18, 24, 25))) {
						include 'npay_stat_desc.txt.php';
					}

					if($npay_claim['HoldbackStatus'] == 'HOLDBACK') {
						$_npay_hold_types[] = $npay_claim['ClaimType'];
					}

					if($nprd[0]->ShippingMemo) {
						$prd['r_message'] = $nprd[0]->ShippingMemo->__toString();
					}
				}

                // 카카오톡바이 배송지연 표시
                if ($data['external_order'] == 'talkpay') {
                    $exprd = $external_data[$prd['external_id']];
                    if (is_object($exprd) == true && $exprd->notifiedDelayDelivery == true) {
                        $prd['DelayDelivery'] = true;
                    }
                }

				//스마트스토어
				if($prd['smartstore_ono']) {
                    $store_product = $productOrders[$prd['smartstore_ono']];
                    if (is_object($store_product)) {
                        // 클레임 타입
                        if ($store_product->productOrder->claimType) {
                            $_npay_claim_types[] = $store_product->productOrder->claimType;
                        }

                        if (in_array($prd['stat'], array(12, 16, 22, 23, 18, 24, 25))) {
                            include_once 'smartstore_stat_desc.txt.php';
                        }

                        if ($store_product->productOrder->shippingMemo) {
                            $prd['r_message'] = $store_product->productOrder->shippingMemo;
                        }

                        // 네이버페이 호환 레이어
                        $nprd[0] = array();
                        if ($store_product->productOrder->delayedDispatchReason) {
                            $nprd[0]['ShippingDueDate'] = date('Y-m-d', strtotime($store_product->productOrder->shippingDueDate));
                            $nprd[0]['DelayedDispatchReason'] = $store_product->productOrder->delayedDispatchReason;
                            $nprd[0]['DelayedDispatchDetailedReason'] = $store_product->productOrder->delayedDispatchDetailedReason;
                        }
                        $nprd[0] = json_decode(json_encode($nprd[0]));

                        if ($store_product->productOrder->claimType) {
                            $claimType = strtolower($store_product->productOrder->claimType);
                            $npay_claim = array(
                                'ClaimType' => $store_product->productOrder->claimType,
                                'ClaimTypeName' => $CommerceAPI->claimTypeName($store_product->productOrder->claimType),
                                'ClaimReason' => $CommerceAPI->claimReason($store_product),
                                'ClaimdetailReason' => $store_product->{$claimType}->{$claimType . 'DetailedReason'},
                                'ClaimDeliveryFeeDemandAmount' => $store_product->{$claimType}->claimDeliveryFeeDemandAmount,
                            );
                        }
                        if ($store_product->return->holdbackStatus == 'HOLDBACK') {
                            $npay_claim['HoldbackReason'] = $store_product->return->holdbackDetailedReason;
                        }
                    }
				}

				// 카카오톡 스토어
				if($prd['talkstore_ono']) {
					if(is_object($kts) == false) {
						$kts = new KakaoTalkStore();
					}
					$kprd = $kts->getOrder($prd['talkstore_ono']);
				}

				if($key == 0 && $cprd['delivery_set'] > 0) {
					$_sum_prd_dlv_prc_cnt++;
				}

				// 주문상품 변경내역
				$oplog_res = $pdo->iterator("select * from $tbl[order_product_log] where ono='$ono' and opno='$prd[no]' order by no asc");
                if ($oplog_res) {
    				$oplog_cnt = $oplog_res->rowCount();
                }

				$partner_grp_no = ($is_multi_partner > 1) ? $prd['partner_no2'] : 0;
				if($_prev_partner_no !== $prd['partner_no2']) {
					$_is_before_delivery = $pdo->row("select count(*) from {$tbl['order_product']} where ono='$ono' and partner_no='$partner_grp_no' and stat between 1 and 3");
				}

				if($prd['set_idx'] && in_array($prd['set_idx'], $_prev_set_idx[$key]) == false) { // 세트 메인 상품 출력
					$sprd = $pdo->assoc("select no, hash, name, stat, updir, upfile3, w3, h3 from {$tbl['product']} where no='{$prd['set_pno']}'");
					$sprd = shortCut($sprd);
					$simg = prdImg(3, $sprd, 50, 50);
				}
				$is_set_prd = ($prd['set_idx']) ? true : false;

		?>
		<?php if ($is_multi_partner > 1 && $_prev_partner_no !== $prd['partner_no2']) { ?>
		<tr>
			<td>
				<?php if ($admin['partner_no'] == 0 || $prd['partner_no'] == $admin['partner_no']) { ?>
				<input type="checkbox" id="prd_check_all_<?=$prd['partner_no']?>" data-partner_no="<?=$prd['partner_no']?>" data-key="<?=$key?>" class="grp_check grp_check_<?=$prd['partner_no']?>_<?=$key?>">
				<?php } ?>
			</td>
			<td colspan="10" class="left">
				<label for="prd_check_all_<?=$prd['partner_no']?>"><strong class="p_color"><?=$partner_name?></strong></label>
				<?php if ($key == 0 && $_is_before_delivery > 0 && $data['checkout'] != 'Y' && $data['talkstore'] != 'Y' && $data['smartstore'] != 'Y' && !$data['external_order'] && $admin['level'] < 4 && $is_multi_partner > 1) { ?>
				<span class="box_btn_s icon copy2" style="margin-left:10px;" onclick='orderAddprd(null, <?=$prd['partner_no']?>)'><input type='button' value='상품추가'></a></span>
				<?php } ?>
			</td>
		</tr>
		<?php } ?>

		<?php if ($is_set_prd == true && in_array($prd['set_idx'], $_prev_set_idx[$key]) == false) { ?>
		<!-- 세트 메인 시작 -->
		<tr>
			<td>
				<input
					type="checkbox"
					data-partner_no="<?=$partner_grp_no?>"
					data-key="<?=$key?>"
					data-delivery-set="<?=$cprd['delivery_set']?>"
					class="check_pno_<?=$key?> list_check set_main_<?=$key?>_<?=$prd['set_idx']?>"
				>
				</td>
			<td><?=$idx?></td>
			<td class="left">
				<div class="box_setup order_stat btn_none">
					<div class="thumb">
						<?php if ($sprd['upfile3']) { ?>
						<a href="<?=$root_url?>/shop/detail.php?pno=<?=$sprd['hash']?>" target="_blank"><img src="<?=$simg[0]?>" <?=$simg[1]?>></a>
						<?php } ?>
					</div>
					<div style="margin-left:60px;">
						<p class="title">
							<a href="./?body=product@set_register&pno=<?=$sprd['no']?>" target="_blank"><?=strip_tags(stripslashes($sprd['name']))?></a>
						</p>

					</div>
				</div>
			</td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
		</tr>
		<!-- 세트 메인 끝 -->
		<?php $idx++; } ?>

		<tr class="<?=($is_set_prd == true) ? 'set' : '';?>">
			<td>
				<?php if ($prd['no'] && ($prd['account_idx'] == 0 || in_array($prd['stat'], array(1, 2, 3, 4, 5, 16)) == true)) { ?>
				<input
					type="checkbox"
					name="pno[]"
					value="<?=$prd['no']?>"
					data-partner_no="<?=$partner_grp_no?>"
					data-key="<?=$key?>"
					data-delivery-set="<?=$cprd['delivery_set']?>"
                    data-stat="<?=$prd['stat']?>"
                    data-naverpay="<?=$prd['naverpay']?>"
					class='check_pno_<?=$key?> list_check <?php if ($prd['set_idx']) { ?>set_sub set_sub_<?=$key?>_<?=$prd['set_idx']?><?php } ?>'
				>
				<?php } ?>
			</td>
			<td>
				<?PHP
					if($is_set_prd == true) $idx--;
					else echo $idx;
				?>
			</td>
			<td class="left">
				<div class="box_setup order_stat btn_none">
					<div class="thumb">
						<?php if ($cprd['upfile3']) { ?>
						<a href="<?=$root_url?>/shop/detail.php?pno=<?=$cprd['hash']?>" target="_blank"><img src="<?=$img[0]?>" <?=$img[1]?>></a>
						<?php } ?>
					</div>
					<div style="margin-left:<?=($is_set_prd == true) ? '75px' : '60px';?>">
						<p class="title">
							<?php if ($prd['prd_type'] == 3) { ?>
							<img src="<?=$engine_url?>/_manage/image/icon/ic_gift.gif" alt="사은품">
							<?php } ?>
							<?php if ($prd['pno'] > 0) { ?>
							<a href="./?body=product@product_register&pno=<?=$prd['pno']?>" target="_blank"><?=strip_tags(stripslashes($prd['name']))?></a>
							<?php } else { ?>
							<span><?=$prd['name']?></span>
							<?php } ?>
						</p>
						<p class="cstr">
							<?=$prd['option_str']?><?php if ($cprd['stat']=='3'){?>&nbsp;<span class="p_color2">[품절]</span><?php } ?>
							<?php if ($prd['etc']) { ?>
							<div><?=$prd['etc']?></div>
							<?php } ?>
						</p>
						<?php if ($prd['partner_no'] > 0) { ?>
						<p class="p_color4" style="margin:2px 0;"><strong><?=$partner_name?></strong></p>
						<?php } ?>
						<?php if ($prd['checkout_ono']) { ?>
						<p>네이버 페이 주문상품코드 : <strong><?=$prd['checkout_ono']?></strong></p>
						<?php } ?>
						<?php if ($prd['external_id']) { ?>
						<p>카카오 페이구매 주문상품ID : <strong><?=$prd['external_id']?></strong></p>
						<?php } ?>
						<?php if ($prd['smartstore_ono']) { ?>
						<p>스마트스토어 상품주문번호 : <strong><?=$prd['smartstore_ono']?></strong></p>
						<?php } ?>
						<?php if ($prd['talkstore_ono']) { ?>
						<p style="white-space:nowrap;">톡스토어 주문번호 : <strong><?=$prd['talkstore_ono']?></strong></p>
						<?php } ?>
						<?php if ($prd['complex_no'] > 0) { ?>
						<p class="explain">
							현재고 : <?=$erp_qty?> <a href="#" onclick="viewStockDetail(<?=$prd['complex_no']?>); return false;" class="p_color3">[재고상세]</a>
						</p>
						<?php } ?>
						<?php if ($cprd['storage_no'] > 0) { ?>
						<p class="explain">
							<?=$storage_name?>
						</p>
						<?php } ?>
						<?php if ($prd['ex_pno'] && $prd['ex_pno'] != 'add') { ?>
						<p class='p_color explain'><?=$_exchange_before_stat[$prd['ex_type']]?></p>
						<?php } ?>
						<?php if ($prd['ex_pno'] == 'add') { ?>
						<p class='p_color explain'>추가 주문 상품</p>
						<?php } ?>
						<?php if ($prd['dlv_code']) { ?>
						<p class="explain">
							<span><?=$prd['dlv_name']?> :</span>
							<a href="#" onclick="window.open('<?=$prd['dlv_url']?>', 'dlv', 'status=no, width=800px'); return false;" class="p_color3"><?=$prd['dlv_code']?></a>
						</p>
						<?php } ?>
						<?php if ($npay_claim['ClaimReason']) { ?>
						<div class="claim" onclick="toggle_claim(<?=$prd['no']?>, '')">
							<p class="title">클레임 및 보류사유 <span>?</span></p>
							<div class="claim_detail claim_detail_<?=$prd['no']?>">
								<dl>
									<dt><?=$npay_claim['ClaimTypeName']?> 사유 : <?=$npay_claim['ClaimReason']?></dt>
									<dd>
										<?php if ($npay_claim['ClaimdetailReason']) { // 상세 사유?>
										└ <?=$npay_claim['ClaimdetailReason']?>
										<?php } ?>
										<?php if ($npay_claim['ClaimDeliveryFeeDemandAmount']) { // 생성된 반품교환 배송비?>
											<br>└ <?=$npay_claim['ClaimTypeName']?> 추가 배송비 :
											<?=number_format($npay_claim['ClaimDeliveryFeeDemandAmount'])?> 원
											<?=$npay_claim['ClaimDeliveryFeePayMethod']?>
										</span>
										<?php } ?>
									</dd>
								</dl>
								<?php if ($npay_claim['HoldbackReason']) { ?>
								<dl>
									<dt>보류 사유 : <?=$npay_claim['HoldbackReason']?></dt>
									<?php if($npay_claim['HoldbackDetailReason']) { ?>
									<dd>
										└ <?=$npay_claim['HoldbackDetailReason']?>
									</dd>
									<?php } ?>
								</dl>
								<?php } ?>
								<?php if ($npay_claim['RefundStandbyReason']) { ?>
								<dl>
									<dt>환불 대기 상태 : <?=$npay_claim['RefundStandbyStatus']?></dt>
									<dd>
										└ <?=$npay_claim['RefundStandbyReason']?>
									</dd>
								</dl>
								<?php } ?>
							</div>
						</div>
						<?php } ?>

						<?php if ($nprd[0]->EtcFeePayMethod) { ?>
						<p class="claim">
							<span class="title">기타비용 청구액 : </span>
							<strong><?=number_format($nprd[0]->EtcFeeDemandAmount->__toString())?>원</strong>
							<?=$nprd[0]->EtcFeePayMethod?>
						</p>
						<?php } ?>

						<?php if ($nprd[0]->DelayedDispatchReason) { ?>
						<div class="claim" onclick="toggle_claim(<?=$idx?>,'2')">
							<p class="title">지연발송예정일 <span>?</span></p>
							<div class="claim_detail claim_detail2_<?=$idx?>">
							<strong><?=$nprd[0]->ShippingDueDate?></strong>
							<?php if($prd['checkout_ono']){ ?>
								<span class="explain">(<?=$checkout->getDelayedDispatchReason($nprd[0]->DelayedDispatchReason->__toString())?>)</span>
							<?php } else if($prd['smartstore_ono']) { ?>
								<span class="explain">(<?=$CommerceAPI->delayedDispatchReason($store_product)?>)</span>
							<?php } ?>
							<?php if ($nprd[0]->DelayedDispatchDetailedReason) { ?>
							<div class="explain">└ <?=$nprd[0]->DelayedDispatchDetailedReason?></div>
							<?php } ?>
						</p>
						<?php } ?>
					</div>
				</div>
			</td>
			<td><?=number_format($prd['buy_ea'])?></td>
			<td><?=parsePrice($prd['sell_prc'], true)?></td>
			<td class="right" style='white-space:nowrap;'>
				<?php foreach ($_order_sales as $fn => $fv) {?>
				<?php if ($prd[$fn] != 0) { ?><div class="p_color explain"><?=$fv?> : <?=parsePrice(-($prd[$fn]), true)?></div><?php } ?>
				<?php } ?>
			</td>
			<td>
				<?=parsePrice($prd['total_milage']-$prd['repay_milage'], true)?>
				<?php if ($prd['repay_milage'] > 0 && $cfg['repay_part']=="Y") echo "<br>(-".parsePrice($prd['repay_milage'], true).")"; ?>
			</td>
			<?php if ($cfg['use_prd_dlvprc'] == 'Y') { ?>
			<td>
				<?=parsePrice($prd['prd_dlv_prc']-$prd['repay_prd_dlv_prc'], true)?>
				<?php if ($prd['repay_prd_dlv_prc'] > 0) echo "<br>(-".parsePrice($prd['prd_dlv_prc'], true).")"; ?>
			</td>
			<?php } ?>
			<td><?=parsePrice($prd['total_prc']-$prd['prd_total_sale_prc']+$prd['prd_dlv_prc'], 2)?><?php if ($prd['repay_prc'] > 0 && $cfg['repay_part'] == 'Y') echo "<br>(-".parsePrice($prd['repay_prc'], true).")"; ?></td>
			<td>
				<?=getOrdStat($prd)?>
				<?php if ($prd['repay_date'] > 0 && $cfg['repay_part'] == 'Y'){?><div class='explain'>(<?=date("y/m/d H:i", $prd['repay_date'])?>)<?php } ?>
				<?php if ($prd['npay_stat_desc']) { // 네이버페이 상태 설명?>
				<div class="exchg_stat">
					<input type="button" value="상태안내 &gt" onclick="showNpayStatDesc(<?=$prd['no']?>);" class="btn">
					<div class="npay_stat_desc layer_view blue npay_stat_desc_<?=$prd['no']?>"><?=$prd['npay_stat_desc']?></div>
				</div>
				<?php } ?>
				<?php if ($oplog_cnt > 0) {?>
				<p class="explain">
					<a href="#" onclick="$('.order_product_log_<?=$prd['no']?>').toggle(); return false;" style="color:#000;">
						[변경내역 <strong style="text-decoration:underline; color: #000;"><?=number_format($oplog_cnt)?></strong>건]
					</a>
				</p>
				<?php } ?>
			</td>
			<td>
				<div class="prd_hold_btn_<?=$prd['no']?> <?=$oprd_attr1?>"><a href="#" class="p_color3" onclick="setHoldOrder('<?=$prd['no']?>', this); return false;">배송보류</a></div>
				<div><a href="#" onclick="jsAddr(<?=$prd['no']?>); return false;" class="prd_addr_btn_<?=$prd['no']?> p_color <?=$oprd_attr2?>">배송지변경</a></div>
				<?php if (!$prd['checkout_ono'] && !$prd['smartstore_ono'] && in_array($prd['stat'], array(13, 15, 17, 19))) { ?>
					<span class='box_btn_s2 gray'><input type='button' value='취소정보' onclick='cdetail.open("ono=<?=$ono?>&pno2=<?=$prd['no']?>")'></span>
				<?php } ?>
				<?php if ($npay_claim['HoldbackReason']) { ?>
				<div class="explain p_color5"><?=$npay_claim['ClaimTypeName']?>보류</div>
				<?php } ?>
				<?php if ($npay_claim['RefundStandbyReason']) { ?>
				<div class="explain p_color5">환불대기</div>
				<?php } ?>
				<?php if ($prd['account_idx'] > 0) { ?>
				<div class="explain p_color4">업체정산등록</div>
				<?php } ?>
                <?php if ($prd['DelayDelivery'] == true) { ?>
                <div class="explain p_color5">배송지연</div>
                <?php } ?>
			</td>
		</tr>
		<?php if ($oplog_cnt >0) { ?>
		<tr class="order_product_log_<?=$prd['no']?> nh" style="display:none;">
			<td colspan="11" style="padding:10px;">
				<table class="tbl_inner full">
					<caption class="hidden">변경내역</caption>
					<colgroup>
						<col style="width:100px;">
						<col style="width:100px;">
						<col>
						<col style="width:200px;">
						<col style="width:150px;">
					</colgroup>
					<thead>
						<tr>
							<th scope="row">이전상태</th>
							<th scope="row">변경상태</th>
							<th scope="row">송장번호</th>
							<th scope="row">처리자</th>
							<th scope="row">처리일시</th>
						</tr>
					</thead>
					<tbody>
						<?php while ($oplog = parseOrderProductStat($oplog_res)) { ?>
						<tr>
							<td><?=$oplog['ori_stat']?></td>
							<td><?=$oplog['stat']?></td>
							<td><?=$oplog['dlv_code']?></td>
							<td>
								<?php if ($oplog['is_member'] == 'Y') { ?>
								<img src="<?=$engine_url?>/_manage/image/order/icon_order_member.png" >
								<?php } ?>
								<?=$oplog['admin_id']?>
							</td>
							<td><?=date('Y-m-d H:i', $oplog['reg_date'])?></td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
				<div style="padding-top:10px;">
					<span class="box_btn"><input type="button" value="닫기" onclick="$('.order_product_log_<?=$prd['no']?>').toggle();"></span>
				</div>
			</td>
		</tr>
		<?php } ?>
		<?php if ($prd['r_message'] && $prd['r_message'] != $data['dlv_memo']) { ?>
		<tr>
			<td colspan="10" class="left" style="background:#fafafa;">└ <span class="explain"><?=$prd['r_message']?></span></td>
		</tr>
		<?php } ?>
		<?php

			$idx++;
			$_sum_buy_ea += $prd['buy_ea'];
			$_sum_prd_prc += ($prd['sell_prc']*$prd['buy_ea']);;
			$_sum_sale_prc += -(getOrderTotalSalePrc($prd));
			$_sum_milage += ($prd['total_milage']-$prd['repay_milage']);
			$_sum_total_prc += ($prd['total_prc']-$prd['prd_total_sale_prc']+$prd['prd_dlv_prc']);
			$_sum_prd_dlv_prc += $prd['prd_dlv_prc'];
			$_prev_partner_no = $prd['partner_no2'];
			if (in_array($prd['set_idx'], $_prev_set_idx[$key]) == false) $_prev_set_idx[$key][] = $prd['set_idx'];
			}

			if($key == 0) {
				$data['order_gift'] = preg_replace('/@?_[0-9]+/', '', $data['order_gift']); // erp 연동상품 제외
				if(str_replace("@", "", $data['order_gift'])){
					$_ord_gift = explode("@", $data['order_gift']);
		?>
		<tr>
			<td></td>
			<?php if ($cfg['repay_part'] == "Y" && !$key) { ?>
			<td>--</td>
			<?php } ?>
			<td colspan="10" class="left gift">
				<ul>
					<?php
						for($ii=0; $ii<sizeof($_ord_gift); $ii++){
							if($_ord_gift[$ii]){
								$gift = get_info($tbl['product_gift'], "no", $_ord_gift[$ii]);
								$gift_url = ($gift['no'] && $gift['delete'] != "Y") ? "./?body=promotion@product_gift_register&gno=$gift[no]\" target=\"_blank" : "javascript:;\" onclick=\"alert('삭제된 사은품입니다');";
					?>
					<li>
						<div class="box_setup">
							<div class="thumb"><img src="<?=$root_url?>/<?=$gift['updir']?>/<?=$gift['upfile']?>" style="width:50px; height:50px;"></div>
							<p class="title">
								<img src="<?=$engine_url?>/_manage/image/icon/ic_gift.gif" alt="사은품">
								<a href="<?=$gift_url?>"><?=$gift['name']?></a>
							</p>
							<span class="box_btn_s gray btnp"><input type="button" value="삭제" onClick="orderDeletegift('<?=$data['ono']?>', '<?=$gift['no']?>');"></span>
						</div>
					</li>
					<?php
							}
						}
					?>
				</ul>
			</td>
		</tr>
		<?php
				}
			}
			$cart_sum_price=$total['total_prc'];
			$sale1=$data['sale1'];
			$sale2=$data['sale2'];
			$sale3=$data['sale3'];

		?>
	</tbody>
	<?php if ($_sum_buy_ea > 0) { ?>
	<tfoot>
		<tr>
			<td colspan="3">합계</td>
			<td><?=number_format($_sum_buy_ea)?></td>
			<td><?=parsePrice($_sum_prd_prc, true)?></td>
			<td><?=parsePrice($_sum_sale_prc, true)?></td>
			<td><?=parsePrice($_sum_milage, true)?></td>
			<?php if ($cfg['use_prd_dlvprc'] == "Y") { ?> <td><?=parsePrice($_sum_prd_dlv_prc, true)?></td><?php } ?>
			<td><?=parsePrice($_sum_total_prc, true)?></td>
			<td></td>
			<td></td>
		</tr>
	</tfoot>
	<?php } ?>
</table>
<?php }

	$_prd_stats = array_unique($_prd_stats);
	$_npay_claim_types = array_unique($_npay_claim_types);
	$_npay_hold_types = array_unique($_npay_hold_types);

?>
<?php if ($soprd_ck > 0) { ?>
	<input type='text' name='soprd_ck' value='<?=$soprd_ck?>'>
	<script type='text/javascript'>
		$(function() {
			jsPrdStat('sp');
		});
	</script>
<?php } ?>
<script type="text/javascript">
	function toggle_claim(idx, idxx){
		$('.claim_detail').not('.claim_detail'+idxx+'_'+idx).hide();

		var detail = $('.claim_detail'+idxx+'_'+idx);
		if (detail.css('display') == 'none'){
			detail.show();
		} else {
			detail.hide();
		}
	}

	function showNpayStatDesc(s) {
		$('.npay_stat_desc').not('.npay_stat_desc_'+s).hide();
		$('.npay_stat_desc_'+s).toggle();
	}

	var list_check = $(':checkbox.list_check');
	var grp_check = $(':checkbox.grp_check');
	list_check.on('change', function() {
        $('#repayDetail').slideUp(100, function() {
            this.innerHTML = '';
        });

		var partner_no = $(this).data('partner_no');
		var key = $(this).data('key');
		list_check.each(function() {
			if(partner_no != $(this).data('partner_no') || key != $(this).data('key')) {
				this.checked = false;
			}
		});
		checkboxCleanup();
	});
	grp_check.change(function() {
		var partner_no = $(this).data('partner_no');
		var key = $(this).data('key');
		var _this = this;
		list_check.each(function() {
			if(partner_no == $(this).data('partner_no') && key == $(this).data('key')) {
				this.checked = _this.checked;
			} else {
				if(_this.checked == true) this.checked = false;
			}
		});
		checkboxCleanup();
	});
	function checkboxCleanup() {
		grp_check.each(function() {
			var partner_no = $(this).data('partner_no');
			var key = $(this).data('key');
			var group_lists = list_check.filter(function() {
				return ($(this).data('partner_no') == partner_no && $(this).data('key') == key);
			})
			this.checked = (group_lists.length == group_lists.filter(':checked').length) ? true : false;
		});
	}

	<?php foreach($_prev_set_idx as $key => $val) {foreach($val as $set_idx) { ?>
	new chainCheckbox($('.set_main_<?=$key?>_<?=$set_idx?>'), $('.set_sub_<?=$key?>_<?=$set_idx?>'));
	<?php }} ?>
</script>