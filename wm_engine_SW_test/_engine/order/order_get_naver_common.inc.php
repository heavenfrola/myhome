<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' | 네이버페이 스마트스토어 공통 처리 모듈
	' +----------------------------------------------------------------------------------------------+*/

    if ($scfg->comp('use_fb_npay', 'Y') == true && $scfg->comp('use_fb_conversion', 'Y') == true) {
        require_once __ENGINE_DIR__.'/_engine/include/facebook.lib.php'; // 페이스북 구매전환 API
    }

    if($cfg['use_partner_delivery'] == 'Y') { // 배송비 정산 테이블 생성
        if(!isTable($tbl['order_dlv_prc'])) {
            include_once $engine_dir.'/_config/tbl_schema.php';
            $pdo->query($tbl_schema['order_dlv_prc']);
        }
    }

	$_repay_stat_array = array('12','13','15','17');
	$idx = 0;
	while(1) {
		if($_REQUEST['order_no']) { // 주문번호로 수동 조회
			$_REQUEST['force_check'] = true;
			$order_no = addslashes($_REQUEST['order_no']);
			$res = $pdo->iterator("select ono, {$svc_name}_ono from $tbl[order_product] where ono='$order_no' and {$svc_name}_ono!=''");
            foreach ($res as $data) {
				$result[] = array(
					'ono' => $data['ono'],
					'cono' => $data[$svc_name.'_ono']
				);
			}
		} else {
			$result = $naver_api->api('GetChangedProductOrderList', $_REQUEST['o1'], $_REQUEST['o2'], $_REQUEST['o3'], $_REQUEST['checkout_id']);
			if($naver_api->error) {
				exit;
			}
		}
		$totcnt = count($result);

		if($MoreDataTimeFrom) {
			$_REQUEST['o1'] = $MoreDataTimeFrom;
			$MoreDataTimeFrom2 = $MoreDataTimeFrom;
		} else {
			$MoreDataTimeFrom2 = "";
		}

		$newcnt = $upcnt = 0;
		$onos = $last_changed = $changed = array();
		$_dlv_prc = array();
		if(is_array($result)) {
			foreach($result as $key => $val) {
				$idx++;
				// Order
				if(is_array($val)) {
					$order_no = $val['ono'];
					$cono = $val['cono'];
				} else {
					$order_no = simplexmlToString($val->OrderID[0]);
					$cono = $val->ProductOrderID[0];
					$order_last = strtotime($val->LastChangedDate[0]);
					$date2 = $val->PaymentDate[0] ? strtotime($val->PaymentDate[0]) : 0;
				}
				$_total_prc = 0;
				$mobile = 'N';

				$ord = $pdo->assoc("select * from $tbl[order] where ono='$order_no'");

				if(!$_REQUEST['force_check'] && $ord[$svc_name.'_last'] >= $order_last) {
					$is_cono = $pdo->row("select count(*) from $tbl[order_product] where {$svc_name}_ono='$cono'"); // 누락상품 체크
					if($is_cono > 0) {
						continue;
					}
				}

				// Order Product
				$pdata = $naver_api->api('GetProductOrderInfoList', $cono);
				if($naver_api->error) {
					$pdata = $naver_api->api('GetProductOrderInfoList', $cono);
					if($naver_api->error) {
						continue;
					}
				}
                if ($ord && $pdata[0]->OrderID != $ord['ono']) {
                    continue;
                }

				//$onos[] = $order_no; 실제 처리된 주문번호만 배열에 담게 아래로 이동 2019-03-27 by zzojae
				$last_changed[$order_no] = $order_last;
				$updates = array();

				$stat2 = array();
				$prd_prc = $naverMilage = 0;

				foreach($pdata as $val2) {
					if($val2->MallID[0] != $_REQUEST[$svc_name.'_id']) exit;
					$date1 = strtotime($val2->OrderDate[0]);
					$member_id = $val2->OrdererID[0];
					$buyer_name = $val2->OrdererName[0];
					$buyer_cell = $val2->OrdererTel1[0];
					$buyer_phone = $val2->OrdererTel2[0] ? $val2->OrdererTel1[0] : $buyer_cell;
					$pay_type = $naver_api->getPaytype($val2->PaymentMeans[0]);
					$SellingCode = addslashes($val2->SellingCode[0]);
					$stat = $naver_api->getStat($val2);
					$stat2[] = $stat;

					if($date2 == 0) {
						$date2 = $val2->PaymentDate[0] ? strtotime($val2->PaymentDate[0]) : 0;
					}

					$cono = $val2->ProductOrderID[0];
					$pno = ($svc_name == 'checkout') ? $val2->ProductID[0] : $val2->SellerProductCode[0];
					$pname = addslashes($val2->ProductName[0]);
					$buy_ea = $val2->Quantity[0];
					$option_prc = $val2->OptionPrice[0];
					$sell_prc = $val2->UnitPrice[0]+$option_prc;
					$total_prc = $sell_prc * $buy_ea;
					if($val2->TotalPaymentAmount[0]) {
						$total_prc = $val2->TotalPaymentAmount[0];
						$sell_prc = $total_prc/$buy_ea;
					} else 	if($val2->TotalProductAmount[0]) {
						$total_prc = $val2->TotalProductAmount[0];
						$sell_prc = $total_prc/$buy_ea;
					}
					$pay_prc = $val2->GeneralPaymentAmount[0]+$val2->PayLaterPaymentAmount[0];
					$naverMilage = $val2->NaverMileagePaymentAmount[0];
					if((int)$val2->ChargeAmountPaymentAmount[0] > 0) {
						$naverMilage+=(int)$val2->ChargeAmountPaymentAmount[0];
					}

					// 배송비 / 업체별 배송비
					$dlv_where = '';
					$partner_no = 0;
					$current_dlv_prc = $val2->DeliveryFeeAmount[0];
					if($val2->ShippingFeeType[0] == '착불') {
						$current_dlv_prc = 0;
					}

                    // 반품 배송비
                    $ClaimDeliveryFeeDemandAmount = (int) $val2->ClaimDeliveryFeeDemandAmount[0];
                    if ($ClaimDeliveryFeeDemandAmount > 0) {
                        $current_dlv_prc = $ClaimDeliveryFeeDemandAmount;
                    }

					if($val2->SectionDeliveryFee[0] > 0) $current_dlv_prc += $val2->SectionDeliveryFee[0];
					if($cfg['npay_ver'] == '2') { // 입점몰 사용시
                        if ($cfg['use_partner_shop'] == 'Y') {
    						$prd = $pdo->assoc("select partner_no, dlv_type from $tbl[product] where no='$pno'");
                            $dlv_partner_no = ($prd['dlv_type'] == '1' || $cfg['use_partner_delivery'] != 'Y') ? '0' : $prd['partner_no'];
                            $partner_no = $prd['partner_no'];
                            $dlv_where = " and partner_no='$dlv_partner_no'";
                        } else {
                            $dlv_partner_no = '0';
                        }
                        if(isset($_dlv_prc[$order_no][$dlv_partner_no]) == false || $_dlv_prc[$order_no][$dlv_partner_no] < $current_dlv_prc) {
                            $_dlv_prc[$order_no]['total'] += $current_dlv_prc;
                            $_dlv_prc[$order_no][$dlv_partner_no] = $current_dlv_prc;

                            $dlv_prc = $_dlv_prc[$order_no]['total'];
                        }
					} else {
						$dlv_prc = $current_dlv_prc;
					}

					$date4 = $val2->SendDate[0] ? strtotime($val2->SendDate[0]) : 0;
					$date5 = $val2->DeliveredDate[0] ? strtotime($val2->DeliveredDate[0]) : 0;
                    $date6 = $val2->DecisionDate[0] ? strtotime($val2->DecisionDate[0]) : 0;
                    if (!$date5 && $date6) $date5 = $date6;
					if($ord['date4'] && $date4 == 0) $date4 = $ord['date4'];
					if($ord['date5'] && $date5 == 0) $date5 = $ord['date5'];
					if($ord['date6'] && $date6 == 0) $date6 = $ord['date6'];
					$member_id = $val2->MallMemberID[0];
					$dlv_hold = 'N';
					if($val2->PayLocationType[0] == 'MOBILE') $mobile = 'Y';

					$addressee_addr1 = addslashes($val2->BaseAddress[0]);
					$addressee_addr2 = addslashes($val2->DetailedAddress[0]);
					$addressee_zip = $val2->ZipCode[0];
					$addressee_name = addslashes($val2->Name[0]);
					$addressee_cell = addslashes($val2->Tel1[0]);
					$addressee_phone = ($val2->Tel2[0]) ? addslashes($val2->Tel2[0]) : $addressee_cell;
					$ShippingMemo  = addslashes($val2->ShippingMemo[0]);
					$ShippingMemo .= "\n".addslashes($val2->OrderExtraData[0]);
					$ShippingMemo = trim($ShippingMemo);

					$free_delivery = $val2->ShippingFeeType[0] == '무료' ? 'Y' : 'N';

					$GiftReceivingStatus = $val2->GiftReceivingStatus[0]; //선물하기 상태값

					// 택배사 정보, 송장번호
					$dlv_no = $naver_api->getDlvPrv($val2->DeliveryCompany[0]);
					$dlv_no = $pdo->row("select no from $tbl[delivery_url] where name='$dlv_no' $dlv_where");
					$dlv_code = $val2->TrackingNumber[0];
                    if ($val2->ReDeliveryTrackingNumber[0]) {
                        $dlv_code = $val2->ReDeliveryTrackingNumber[0];
                    }

					// 옵션코드
					$prd = $pdo->assoc("select * from $tbl[product] where no='$pno'");
					$option_idx = $complex_no = $opts = '';
					$option_tmp = explode('+', $val2->OptionManageCode[0]);
					if(preg_match('/^[0-9]+$/', $option_tmp[0])) {
						$complex_no = numberOnly($option_tmp[0]);
						$option_idx = '';
						$opts = $pdo->row("select opts from erp_complex_option where complex_no='$complex_no'");
						$opts = str_replace('_', ',', trim($opts, '_'));
					} else if($svc_name == 'smartstore' && $val2->SellerCustomCode1[0]) {
						$complex_no = numberOnly($val2->SellerCustomCode1[0]);
					} else if(preg_match('/^[0-9]+$/', $val2->OptionCode[0])) {
						$complex_no = numberOnly($val2->OptionCode[0]);
						$option_idx = '';
						$opts = $pdo->row("select opts from erp_complex_option where complex_no='$complex_no'");
						$opts = str_replace('_', ',', trim($opts, '_'));
					}
					if(!$complex_no && preg_match('/\/([0-9]+)$/', $val2->OptionManageCode[0], $_tmp)) {
						$complex_no = $_tmp[1];
					}

					if(!$complex_no) { // 옵션 없는 재고관리 상품
						$MerchantProductId = simplexmlToString($val2->MerchantProductId[0]);
						if(preg_match('/^[0-9]+$/', $MerchantProductId) && $MerchantProductId > 0) {
							$complex_no = $MerchantProductId;
						} else if($val2->SellerCustomCode1[0]) {
							$complex_no = $val2->SellerCustomCode1[0];
						}
					}

                    // MerchantCustomCode
                    $custom1 = (empty($val2->MerchantCustomCode1) == false) ? json_decode($val2->MerchantCustomCode1) : array();
                    $custom2 = (empty($val2->MerchantCustomCode2) == false) ? json_decode($val2->MerchantCustomCode2) : array();

					$option = addslashes($val2->ProductOption[0]);
					// 주문상품 저장
					$oprd = $pdo->assoc("select no, stat, dlv_hold, dlv_code, r_zip from $tbl[order_product] where {$svc_name}_ono='$cono'");
					if($oprd['no'] > 0) {
						$erp_auto_input = 'Y';
						$ono = $order_no;
						$err = orderStock($order_no, $oprd['stat'], $stat, $oprd['no']);
						if($err == 20 && $stat <= 2) $stat = 20;

						$upcnt++;
						$pdo->query("update $tbl[order_product] set stat='$stat', dlv_code='$dlv_code', dlv_no='$dlv_no', r_addr1='$addressee_addr1', r_addr2='$addressee_addr2', r_zip='$addressee_zip', r_name='$addressee_name', r_phone='$addressee_phone', r_cell='$addressee_cell' where {$svc_name}_ono='$cono'");
						if($pdo->lastRowCount() > 0) $changed[$order_no]++;
						$GLOBALS['prevent_resolve'] = false;
						if($oprd['dlv_hold'] == 'Y') $GLOBALS['prevent_resolve'] = true;

                        if($cfg['use_partner_delivery'] == 'Y') {
                            $pdo->query("
                                update {$tbl['order_dlv_prc']} set dlv_prc='{$_dlv_prc[$order_no][$partner_no]}'
                                where ono='$order_no' and partner_no='$partner_no'
                            ");
                        }
					} else {
                        $pasql1 = $pasql2 = '';
						if($cfg['use_partner_shop'] == 'Y') {
							$fee_prc = getPercentage($total_prc, $prd['partner_rate']);
							$pasql1 .= ", partner_no, fee_rate, fee_prc, dlv_type";
							$pasql2 .= ", '$partner_no', '$prd[partner_rate]', '$fee_prc', '$prd[dlv_type]'";
						}
                        if (isset($custom1->set_idx) == true && isset($custom1->set_pno) == true) { // 세트상품
                            $pasql1 .= ", set_idx, set_pno";
                            $pasql2 .= ", '$custom1->set_idx', '$custom1->set_pno'";
                        }

						$newcnt++;
						$pdo->query(trim("
							INSERT INTO {$tbl['order_product']} (pno, ono, name, sell_prc, buy_ea, total_prc, `option`, option_prc, complex_no, option_idx, stat, dlv_code, dlv_hold, dlv_no, {$svc_name}_ono $pasql1)
							SELECT '$pno', '$order_no', '$pname', '$sell_prc', '$buy_ea', '$total_prc', '$option', '$option_prc', '$complex_no', '$option_idx', '$stat', '$dlv_code', '$dlv_hold', '$dlv_no', '$cono' $pasql2
							FROM dual
							WHERE NOT EXISTS (SELECT * FROM {$tbl['order_product']} WHERE checkout_ono='$cono')
						"));
						$changed[$order_no]++;

						// 업체별 배송비 정산 테이블
						if($cfg['use_partner_delivery'] == 'Y') {
							if(!$pdo->row("select count(*) from $tbl[order_dlv_prc] where ono='$order_no' and partner_no='$partner_no'")) {
								$pdo->query("insert into $tbl[order_dlv_prc] (ono, partner_no, first_prc, dlv_prc) values ('$order_no', '$partner_no', '{$_dlv_prc[$order_no][$partner_no]}', '{$_dlv_prc[$order_no][$partner_no]}')");
							} else { // 반품 배송비 발생 등으로 인한 업체별 배송비 변경
                                $pdo->query("
                                    update {$tbl['order_dlv_prc']} set dlv_prc='{$_dlv_prc[$order_no][$partner_no]}'
                                    where ono='$order_no' and partner_no='$partner_no'
                                ");
                            }
						}
					}

					$repay_oprd_qry = $repay_ord_qry = $gift_receive = "";
					if(in_array($stat,$_repay_stat_array)) { //취환반교 상태일때는 repay_prc,repay_date 값 저장
						$repay_oprd_qry = ", repay_prc=total_prc, repay_date='$now'";
						$repay_ord_qry = ", repay_date='$now'";
					} else {
						$repay_oprd_qry = ", repay_prc=0, repay_date='0'";
						$repay_ord_qry = ", repay_date='0'";
                    }
					if($GiftReceivingStatus) { //선물하기에 의한 주문이면 옵션 업데이트
						$gift_receive = ", option='$option', complex_no='$complex_no', option_idx='$option_idx'";
					}
					$updates[] = "update $tbl[order_product] set stat='$stat' $repay_oprd_qry $gift_receive where {$svc_name}_ono='$cono'";
					if($stat > 11) $pdo->query("update `$tbl[order]` set `ext_date`='$now' $repay_ord_qry where `ono`='$order_no'");
				}

				foreach($updates as $val) { // stat 은 재고 처리 후 변경
					$pdo->query($val);
				}
				unset($updates);
				// 주문서 저장
				if($ord['no']) {
					if($pay_type) {
						$pdo->query("update $tbl[order] set pay_type='$pay_type', date2='$date2', date4='$date4', date5='$date5', dlv_no='$dlv_no', dlv_code='$dlv_code', dlv_memo='$ShippingMemo', dlv_prc='$dlv_prc', mobile='$mobile', addressee_addr1='$addressee_addr1', addressee_addr2='$addressee_addr2', addressee_zip='$addressee_zip', addressee_name='$addressee_name', addressee_phone='$addressee_phone', addressee_cell='$addressee_cell', pay_prc='$pay_prc' where no='$ord[no]'");
					}
				} else {
					if($pay_type) {
						$pdo->query("insert into $tbl[order] (ono, date1, date2, date4, date5, member_no, member_id, buyer_name, buyer_phone, buyer_cell, pay_type, {$svc_name}, total_prc, prd_prc, dlv_prc, pay_prc, addressee_addr1, addressee_addr2, addressee_zip, addressee_name, addressee_phone, addressee_cell, free_delivery, dlv_no, dlv_code, dlv_memo, mobile, conversion) values ('$order_no', '$date1', '$date2', '$date4', '$date5', '$member_no', '$member_id', '$buyer_name', '$buyer_phone', '$buyer_cell', '$pay_type', 'Y', '$total_prc', '$prd_prc', '$dlv_prc', '$pay_prc', '$addressee_addr1', '$addressee_addr2', '$addressee_zip', '$addressee_name', '$addressee_phone', '$addressee_cell', '$free_delivery', '$dlv_no', '$dlv_code', '$ShippingMemo', '$mobile', '$SellingCode')");
						$insert_ono[$order_no] = true;
						if($pdo->getError()) {
							$pdo->query("delete from $tbl[order] where `ono` = '$order_no'");
							$pdo->query("delete from $tbl[order_product] where `ono` = '$order_no'");
						}
					}
				}
				$pdo->query("update {$tbl['order']} set point_use='$naverMilage' where ono='$order_no'");
				$onos[] = $order_no;
			}

			// 주문서별 총 결제금액 및 상태값 정리
			$onos = array_unique($onos);
			foreach($onos as $key => $val) {
				$oprd = $pdo->assoc("select sum(total_prc) as prd_prc, sum(if(stat in(1,2,3,4,5,12,14,16), total_prc, 0)) as pay_prc, group_concat(stat) as stat from $tbl[order_product] where ono='$val'");

				$ord = $pdo->assoc("select stat, pay_type, dlv_prc, point_use, pay_prc from $tbl[order] where ono='$val'");

				$stat = '';
				$prd_prc = $oprd['prd_prc'];
				$repay_prc = $prd_prc - $oprd['pay_prc']; //취소처리 금액 구하기
				$dlv_prc = ($prd_prc == $repay_prc) ? $ord['pay_prc'] : $ord['dlv_prc']; // 전체환불일 경우 결제금액 = 환불 배송비를 포함한 배송비
				if($ord['dlv_prc'] > 0 && $dlv_prc == 0) $repay_prc += $ord['dlv_prc']; //배송비가 있는 주문이 전체 취소 됐을 경우 취소금액에 배송비금약 더하기
				$ono = $val;

				// 신규 인입 상품의 재고 처리
				if($insert_ono[$val] == true) {
					$opres = $pdo->iterator("select no, stat from {$tbl['order_product']} where ono='$val'");
                    foreach ($opres as $opdata) {
						$erp_auto_input = 'Y';
						orderStock($val, 0.99, $opdata['stat'], $opdata['no']);
					}

                    $title = addslashes(makeOrderTitle($ono));
                    $pdo->query("update {$tbl['order']} set title=? where ono=?", array(
                        $title, $ono
                    ));
				}

				$order_last = $last_changed[$val];
				$pdo->query("update $tbl[order] set prd_prc='$prd_prc', total_prc='$prd_prc'+'{$ord['dlv_prc']}', dlv_prc='$dlv_prc', repay_prc='$repay_prc', {$svc_name}_last='$order_last' where ono='$val'");
				$stat = ordChgPart($val, false);
				if($stat != $ord['stat']) {
					ordStatLogw($val, $stat, 'Y');
					unset($changed[$val]);
				}
				if(isset($changed[$val]) == true) {
					if(is_object($erpListener)) {
						$erpListener->setOrder($ono);
					}
				}
				ordChgHold($val);

				if($ord['pay_type'] == 'C') {
					$pdo->query("update $tbl[order] set naver_cash='$pay_prc' where ono='$val'");
				}

				// 상태변경 문자 및 메일 발송
				if($insert_ono[$val] == true) {
					$ord = $pdo->assoc("select * from `$tbl[order]` where ono='$val'");

                    // 페이스북 픽셀 구매전환
                    if ($scfg->comp('use_fb_npay', 'Y') == true && $scfg->comp('use_fb_conversion', 'Y') == true) {
                        fbPurchase($ord);
                    }

					include_once $engine_dir."/_engine/sms/sms_module.php";
					$sms_replace['ono'] = $val;
					$sms_replace['title'] = $ord['title'];
					$sms_replace['buyer_name'] = stripslashes($ord['buyer_name']);
					$sms_replace['pay_prc'] = parsePrice($ord['pay_prc']);
					$sms_replace['pay_type'] = $_pay_type[$ord['pay_type']];
					$sms_replace['address'] = stripslashes($ord['addressee_addr1'].' '.$ord['addressee_addr2']);
					if($pay_type == 4) {
						$sms_replace['account'] = '-';
					} elseif ($pay_type == 2) {
						$sms_replace['account'] = '-';
					} else {
						$sms_replace['account'] = '결제완료';
					}
					SMS_send_case(12);

					if(strchr($cfg['email_checked'], '@2') && strchr($cfg['email_checked'], '@0')) {
						$mail_case = 2;
						$admin_email = ($cfg['email_admin']) ? $cfg['email_admin'] : $cfg['admin_email'];

						include $engine_dir.'/_engine/include/mail.lib.php';
					}
				}
			}
		}
		if(!$MoreDataTimeFrom2) break;
	}

?>