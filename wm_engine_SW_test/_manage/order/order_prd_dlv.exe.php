<?PHP

	use Wing\API\Naver\CheckoutApi4;
    use Wing\API\Naver\CommerceAPI;
	use Wing\API\Kakao\KakaoTalkStore;
	use Wing\API\Kakao\KakaoTalkPay;

	printAjaxHeader();

	include_once $engine_dir.'/_engine/include/milage.lib.php';
	Include_once $engine_dir.'/_engine/include/shop2.lib.php';

	extract($_POST);
	$ono = addslashes(trim($_POST['ono']));
	$stat = numberOnly($_POST['stat']);
	$exec = $_POST['exec'];
	$_pno = (isset($_POST['pno'])) ? implode(',', $_POST['pno']) : null;

	$stats = array();
	$_total_prc = 0;
	$is_free_delivery = false;

	$ord = $pdo->assoc("select * from $tbl[order] where ono='$ono'");

	if($exec == 'recovery') {
		$stat = numberOnly($_POST['stat']);
		$exec = 'process';
		if($stat == 1) {
			$exec2 = 'recovery';
			$last_log = $pdo->assoc("select * from $tbl[order_payment] where ono='$ono' order by no desc limit 1");
			if($last_log['type'] != 1) {
				msg('정상적인 자동취소 주문서가 아닙니다.');
			}
			$_pno = str_replace('@', ',', trim($last_log['pno2'], '@'));
			$cpn_no = $last_log['cpn_no'];
			$milage_prc = $last_log['repay_milage'];
			$emoney_prc = $last_log['repay_emoney'];
			if($last_log['dlv_prc'] < 0) $_POST['dlv_prc'] = abs($last_log['dlv_prc']);

			if($cpn_no > 0) {
				$check = $pdo->assoc("select no, ono from $tbl[coupon_download] where no='$cpn_no'");
				if(!$check['no']) msg('복구할 주문서에 사용된 쿠폰이 삭제되어 복구할 수 없습니다.');
				if($check['ono']) msg('복구할 주문서에 사용된 쿠폰이 다른 주문서에 사용되어 복구할 수 없습니다.');
			}

			$recover_prdcpn = array();
			if($ord['sale7'] > 0) {
				$pres = $pdo->iterator("select no, prdcpn_no from $tbl[order_product] where ono='$ono' and sale7>0");
                foreach ($pres as $pdata) {
					if($pdo->row("select count(*) from $tbl[coupon_download] where no in ($pdata[prdcpn_no]) and ono=''") == 0) {
						msg('복구할 주문서에 사용된 상품별쿠폰이 삭제되었거나 다른 주문서에 사용되어 복구할 수 없습니다.');
					}
					$recover_prdcpn[$pdata['no']] = $pdata['prdcpn_no'];
				}
			}

			$amember = $pdo->assoc("select * from $tbl[member] where member_id='$ord[member_id]' and no='$ord[member_no]'");
			if($milage_prc > 0 && $amember['milage'] < $milage_prc) {
				msg('고객의 잔여 적립금 부족으로 주문서를 복구할 수 없습니다.');
			}
			if($emoney_prc > 0 && $amember['emoney'] < $emoney_prc) {
				msg('고객의 잔여 예치금 부족으로 주문서를 복구할 수 없습니다.');
			}
		} else if($stat == 2) {
			$exec2 = 'redoPay';
			$_pno = $pdo->row("select group_concat(no) from {$tbl['order_product']} where ono='$ono' and stat in (11, 31)");
			if($ord['member_no']) {
				$amember = $pdo->assoc("select * from {$tbl['member']} where member_id='{$ord['member_id']}' and no='{$ord['member_no']}'");
				$milage_prc = $ord['milage_prc'];
				$emoney_prc = $ord['emoney_prc'];
				if($milage_prc > 0 && $amember['milage'] < $milage_prc) {
					msg('고객의 잔여 적립금 부족으로 주문서를 복구할 수 없습니다.');
				}
				if($emoney_prc > 0 && $amember['emoney'] < $emoney_prc) {
					msg('고객의 잔여 예치금 부족으로 주문서를 복구할 수 없습니다.');
				}
			}
		}
	}

	$account_completed = '';
	$total_prd_dlv_prc = $repay_prd_dlv_prc = $prd_dlv_prc_recover = 0;
	$res = $pdo->iterator("select op.*, p.free_delivery from $tbl[order_product] op inner join $tbl[product] p on op.pno=p.no where op.ono='$ono' and op.no in ($_pno)");
    foreach ($res as $data) {

        if ($stat == -1 && $data['checkout_ono']) {
            //변경하려는 상태가 네이버페이 거래면서 '재고확인완료'인 경우
            if ($data['stat'] != 20) {
                //재고확인중 상태가 아니라면 처리하지 않는다
                continue;
            } else {
                //현재 네이버 페이의 상태로 적용한다.
                if(!isset($checkout)) $checkout = new CheckoutApi4();
                $checkout_result = $checkout->api('GetProductOrderInfoList', $data['checkout_ono']);
                $stat = $checkout->getStat($checkout_result[0]);
                if($checkout->error) {
                    msg(php2java('['.$data['name'].']네이버페이 에러 : '.$checkout->error));
                }
            }
        }

        if ($stat == -1 && $data['smartstore_ono']) {
            //변경하려는 상태가 스마트스토어 거래면서 '재고확인완료'인 경우
            if ($data['stat'] != 20) {
                //재고확인중 상태가 아니라면 처리하지 않는다
                continue;
            } else {
                //현재 스마트스토어의 상태로 적용한다.
                if (!isset($CommerceAPI)) {
                    $CommerceAPI = new CommerceAPI();
                }
                $stat = $CommerceAPI->getCurrentStat($data['smartstore_ono']);
                if (!$stat) {
                    msg('스마스스토어 주문 상품 정보를 가져올 수 없습니다.');
                }
            }
        }

		$stat_type = ($data['stat'] > 10 && $data['stat']%2 == 1) ? 2 : 1;
		$stats[$stat_type]++;

		if($data['account_idx'] > 0 && $data['stat'] == 5) {
			$account_completed .= '- '.stripslashes($data['name'])."\n";
		}

		$_total_prc += ($data['total_prc']-getOrderTotalSalePrc($data));
		$ono = $data['ono'];

		if($data['free_delivery'] == 'Y') {
			$is_free_delivery = true;
		}

		if($data['dlv_code']) {
			$invoice_no_exist = true;
		}

		// 입점사 개별 배송처리
		if($cfg['use_partner_shop'] == 'Y' && $cfg['use_partner_delivery'] == 'Y') {
			$partner_exchange = $data['partner_no'];
		}

		if($cfg['use_prd_dlvprc'] == 'Y') { // 개별 배송비
			$total_prd_dlv_prc += $data['prd_dlv_prc'];
			$repay_prd_dlv_prc += $data['repay_prd_dlv_prc'];

			if($data['stat'] > 10 && $stat < 10 && $data['prd_dlv_prc'] > 0) {
				$prd_dlv_prc_recover++;
			}
		}
	}

	if($prd_dlv_prc_recover > 0 && $prd_dlv_prc_recover != count($pno)) {
		msg('개별 배송비가 부여된 상품은 다른 상품과 함께 상태 복구할 수 없습니다.');
	}

	if(empty($account_completed) == false) {
		msg("정산이 완료된 주문은 반품처리만 가능합니다.\\n".php2java(trim($account_completed)));
	}

	// 입점사별 배송 정책 적용
	if(isset($partner_exchange) == true) {
		setPartnerDlvConfig($partner_exchange);
	}

	if($stats[1] > 0 && $stats[2] > 0) msg('취소상품과 일반상품을 같이 변경할수 없습니다.');

	$res = $pdo->iterator("select * from $tbl[order_product] where no in ($_pno)");

	// 실처리
	if($exec == 'process') {
        startOrderLog($ono, 'order_prd_dlv.exe.php'); // 주문 로그 작성

		if($cfg['opmk_api']) {
			include $engine_dir.'/_engine/api/'.$cfg['opmk_api'].'/'.$cfg['opmk_api'].'.class.php';
			include $engine_dir.'/_engine/api/'.$cfg['opmk_api'].'/'.$cfg['opmk_api'].'Order.class.php';

			$apiname = $cfg['opmk_api'].'Order';
			$openmarket = new $apiname();
		}

		$cnt = 0;
		$sum_total_milage = 0;
		$dlv_no = numberOnly(trim($_POST['dlv_no']));
		$dlv_code = addSlashes(trim($_POST['dlv_code']));
		$dlv_prc = numberOnly($_POST['dlv_prc']);
		$add_dlv_prc = numberOnly($_POST['add_dlv_prc']);
		$restore_prd_dlv_prc = numberOnly($_POST['restore_prd_dlv_prc']); // 개별 배송비 복구
		$dlv_name = $pdo->row("select name from $tbl[delivery_url] where no='$dlv_no' order by sort asc");
		$_title = array();

		if($cfg['invoice_essential'] == 'Y' && $dlv_no && !$dlv_code) {
			msg('송장번호를 입력해주세요.');
		}

		if($stats[2] > 0 && $_total_prc != 0) {
			$pay_type = $_POST['input_pay_type'];
			if($pay_type == 2) {
				if(!$input_bank) msg('입금 은행을 선택해 주세요.');
				$account = $pdo->assoc("select * from $tbl[bank_account] where no='$input_bank'");
				$bank = $account['bank'];
				$bank_account = $account['account'];
				$bank_name = $account['owner'];
			}
		}

        foreach ($res as $data) {
			if($data['stat'] == $stat) continue;

			$asql  = '';
			if($dlv_no && $dlv_code) $asql .= ", dlv_no='$dlv_no', dlv_code='$dlv_code'";
			if($data['stat'] > 10 && $data['repay_prc']) $asql .= ", repay_prc='0'";
			if($data['stat'] > 10 && $data['repay_milage']) $asql .= ", repay_milage='0'";

			if($data['checkout_ono'] && ($stat == 3 || $stat == 4)) { // 네이버 페이 배송처리
				if(!$_POST['dlv_no']) {
					$dlv_no = $data['dlv_no'];
					$dlv_code = $data['dlv_code'];
				}
				if(!isset($checkout)) $checkout = new CheckoutApi4();

				$checkout_result = $checkout->api('GetProductOrderInfoList', $data['checkout_ono']);
				$checkout_stat = $checkout->getStat($checkout_result[0]);
				if($checkout->error) {
					msg(php2java('네이버페이 에러 : '.$checkout->error));
				}

				if($stat == $checkout_stat) continue;

				if($stat == 4) {
					if(!$dlv_no) {
						msg('배송처리할 택배사를 선택해주세요.');
					}
					$checkout->delivery($data['checkout_ono'], $dlv_code, $checkout->getDlvCode($dlv_name)); // 배송중
					if($checkout->error) {
						msg(php2java('네이버페이 에러 : '.$checkout->error));
					}
				}
				if($stat == 3) {
					$checkout->api('PlaceProductOrder', $data['checkout_ono']); // 상품준비중
					if($checkout->error) {
						msg(php2java('네이버페이 에러 : '.$checkout->error));
					}
				}
			}

			// 스마트스토어 배송처리
			if($data['smartstore_ono'] && ($stat == 3 || $stat == 4)) {
				if(!$_POST['dlv_no']) {
					$dlv_no = $data['dlv_no'];
					$dlv_code = $data['dlv_code'];
				}
				if (!isset($CommerceAPI)) {
                    $CommerceAPI = new CommerceAPI();
                }
                $smartstore_stat = $CommerceAPI->getCurrentStat($data['smartstore_ono']);
				if($stat == $smartstore_stat) continue;

				if ($stat == 4) {
					if (!$_REQUEST['dlv_no']) {
						msg('배송처리할 택배사를 선택해주세요.');
					}
                    if (!$_REQUEST['dlv_code']) {
                        msg('송장번호를 입력해주세요.');
                    }

                    $ret = $CommerceAPI->ordersDispatch(
                        $data['smartstore_ono'],
                        $_REQUEST['dlv_no'],
                        $_REQUEST['dlv_code']
                    );
					$fp = fopen('_data/smartstore4.txt', 'a+');
					fwrite($fp, print_r($ret, true));
					fclose($fp);
                    if (!count($ret->data->successProductOrderInfos)) {
                        if ($ret->message) {
                            msg(php2java($ret->message));
                        } else if (count($ret->data->failProductOrderInfos)) {
                            msg(php2java($ret->data->failProductOrderInfos[0]->message));
                        }
                    }
				} else if ($stat == 3) {
                    $ret = $CommerceAPI->ordersConfirm($data['smartstore_ono']);
					$fp = fopen('_data/smartstore3.txt', 'a+');
					fwrite($fp, print_r($ret, true));
					fclose($fp);
                    if (!count($ret->data->successProductOrderInfos)) {
                        if ($ret->message) {
                            msg(php2java($ret->message));
                        } else if (count($ret->data->failProductOrderInfos)) {
                            msg(php2java($ret->data->failProductOrderInfos[0]->message));
                        }
                    }
				}
			}

			if($data['talkstore_ono'] && ($stat == 3 || $stat == 4)) { // 카카오톡 스토어 배송처리
				if(is_object($kts) == false) {
					$kts = new KakaoTalkStore();
				}

				if($stat == 3 || ($stat == 4 && $data['stat'] == 2)) {
					$ret = $kts->setShippingWait($data['talkstore_ono']);
					if($ret != 'OK') {
						$ret = json_decode($ret);
						msg(php2java($ret->extras->error_message));
					}
				}
				if($stat == 4) {
					if(!$_POST['dlv_no']) {
						$dlv_name = $pdo->row("select name from $tbl[delivery_url] where no='$data[dlv_no]'");
						$dlv_code = $data['dlv_code'];
					}
					$ret = $kts->setShipping($dlv_name, $dlv_code, $data['talkstore_ono']);
					if($ret != 'OK') {
						$ret = json_decode($ret);
						msg(php2java($ret->extras->error_message));
					}
				}
			}

            // 카카오페이 구매
            if($ord['external_order'] == 'talkpay' && $data['external_id'] && ($stat == 3 || $stat == 4)) { // 카카오페이 구매
                $talkpay = new KakaoTalkPay($scfg);
                if ($stat == 3) {
                    $ret = $talkpay->confirm($data['external_id'], $_POST['dlv_no'], $_POST['dlv_code']);
                } else if ($stat == 4) {
                    $ret = $talkpay->delivery($data['external_id'], $_POST['dlv_no'], $_POST['dlv_code']);
                }
                if ($ret != 'OK') {
                    msg(php2java("- 카카오페이구매\n".$ret));
                }
            }

			if($data['openmarket_ono'] && is_object($openmarket) && $dlv_no && $dlv_code) {
				if($openmarket->setDeilvery($data['no'], $dlv_no, $dlv_code) == false) {
					alert("오픈마켓 송장등록이 실패되었습니다.");
					continue;
				}
			}

			if($data['complex_no'] > 0 && !is_object($erpListener)) {
				$stockErr = orderStock($data['ono'], $data['stat'], $stat, $data['no']);
                if ($stockErr) {
                    //재고 오류시 변경하지 않음
                    continue;
                }
			}
			if($cfg['use_prd_dlvprc'] == 'Y' && $restore_prd_dlv_prc > 0) {
				$asql .= ", repay_prd_dlv_prc=0";
			}
			$pdo->query("update $tbl[order_product] set stat='$stat', repay_date=0 $asql where no='$data[no]'");
			if($dlv_no && $dlv_code) {
				$pdo->query("update $tbl[order] set dlv_no='$dlv_no', dlv_code='$dlv_code' where ono='$data[ono]'");
				$ord['dlv_code'] = $dlv_code;
				$ord['dlv_no'] = $dlv_no;
			}

			if($ord['milage_down'] == 'Y' && $stat == 5) $sum_total_milage += $data['total_milage']; // 이미 적립금 지급상태에서 부분 배송완료시
			$_title[] = stripslashes($data['name']);
			$cnt++;
		}

		if($cnt == 0) {
			msg('변경된 내역이 없습니다.');
		}

		$sum_repay_milage = $pdo->row("select sum(total_milage) from $tbl[order_product] where ono='$ono' and stat > 11");
		$pdo->query("update $tbl[order] set repay_milage='$sum_repay_milage' where ono='$ono'");

		if($stats[2] > 0 && $_total_prc != 0) {
			if($cpn_no > 0) { // 사용 쿠폰 복구
				$pdo->query("update $tbl[coupon_download] set ono='$ono', use_date='$last_log[reg_date]' where no='$cpn_no'");
			}
			if(is_array($recover_prdcpn) == true && count($recover_prdcpn) > 0) {
				foreach($recover_prdcpn as $_pno => $_prdcpn_no) {
					$pdo->query("update $tbl[coupon_download] set ono='$ono', use_date='$last_log[reg_date]', cart_no='$_pno' where no in ($_prdcpn_no)");
				}
			}
			if($milage_prc > 0 || $emoney_prc > 0) { // 사용 적립금 및 예치금 복구
				if($milage_prc > 0) {
					$_total_prc -= $milage_prc;
					$pdo->query("update $tbl[order] set milage_recharge='N', milage_recharge_date=0, milage_prc='$milage_prc' where ono='$ono'");
					ctrlMilage('-', 12, $milage_prc, $amember, '주문서 복구');
				}
				if($emoney_prc > 0) {
					$_total_prc -= $emoney_prc;
					$pdo->query("update $tbl[order] set emoney_recharge='N', emoney_recharge_date=0, emoney_prc='$emoney_prc' where ono='$ono'");
					ctrlEmoney('-', 12, $emoney_prc, $amember, '주문서 복구');
				}
			}

			if($exec2 == 'redoPay') {
				$payment_no = $pdo->row("select no from {$tbl['order_payment']} where ono='$ono' and type='0'");
				$pdo->query("update $tbl[order_payment] set stat=2, confirm_id='$admin[admin_id]', confirm_date='$now' where no='$payment_no'");
			} else {
				$payment_no = createPayment(array(
					'ono' => $ono,
					'pno' => $_POST['pno'],
					'type' => 2,
					'pay_type' => $pay_type,
					'amount' => ($_total_prc+$add_dlv_prc+$dlv_prc+$restore_prd_dlv_prc),
					'reason' => $_POST['reason'],
					'bank' => $bank,
					'bank_account' => $bank_account,
					'bank_name' => $bank_name,
					'dlv_prc' => ($dlv_prc+$restore_prd_dlv_prc),
					'add_dlv_prc' => $add_dlv_prc,
					'cno' => $cpn_no,
					'milage_prc' => $milage_prc,
					'emoney_prc' => $emoney_prc,
					'reason' => '취소 주문서 복구',
				));

				if(isset($partner_exchange) == true) { // 입점사 배송비 정산 테이블 갱신
					if(($dlv_prc+$restore_prd_dlv_prc) != 0) {
						$pdo->query("update {$tbl['order_dlv_prc']} set dlv_prc=(dlv_prc+$dlv_prc+$restore_prd_dlv_prc) where ono='$ono' and partner_no='$partner_exchange'");
					}
				}

				ordStatLogw($ono, 100, null, null,
						array(
							'payment_no' => $payment_no,
							'pno' => $_POST['pno'],
							'content' => "선택 $_order_stat[$stat]"
						)
				);
			}
		}

		$nstat = ordChgPart($ono);
		if($ord['stat'] != $nstat) {
			ordStatLogw($ono, $nstat);

			// 이메일 발송
			if($nstat == 4 || $nstat == 5) {
				$mail_case = $nstat-1;
				$data = $ord;
				$dlv = getDlvUrl($ord);
				include $engine_dir.'/_engine/include/mail.lib.php';
				sendMailContent($mail_case, $ord['buyer_name'], $ord['buyer_email']);
			}
		}

		// 현금영수증 재계산 - 위치변경 order 상태 바뀐후 진행이 맞는듯?
		chgCashReceipt($ono);

		if(($ord['pay_type'] == 4 || $ord['pay_type'] == 17) && $dlv_no && $dlv_code) {
			escDlvRegist($ord, $dlv_no, $dlv_code);
		}

		// 적립금 지급
		if($cfg['milage_use'] == 1) {
			$nstat2 = explode('@', trim($pdo->row("select stat2 from $tbl[order] where ono='$ord[ono]'"), '@'));
			foreach($nstat2 as $key => $val) {
				if($val > 5 && !in_array($val, array(12, 14, 16, 18))) unset($nstat2[$key]);
			}
			$nstat2 = array_unique($nstat2);
			if(($ord['stat'] == 5 || $nstat == 5) && $ord['stat'] != $nstat) {
				reloadOrderMilage($ord['ono']);
				$data = $pdo->assoc("select * from $tbl[order] where ono='$ord[ono]'");
				$asql = '';
				$ext = $nstat;
				orderMilageChg($ord['total_milage']-$ord['repay_milage']);
				if($asql) {
					$pdo->query("update $tbl[order] set ono='$data[ono]' $asql where ono='$data[ono]'");
					$asql = '';
				}
			} elseif($sum_total_milage > 0 && count($nstat2) == 1) {
				if(!$amember) $amember = $pdo->assoc("select * from $tbl[member] where member_id='$ord[member_id]' and no='$ord[member_no]'");
				if($amember['no']) {
					ctrlMilage('+', 0, $sum_total_milage, $amember, $ord['title'], '', $admin['admin_id']);
				}
				$total_milage = $pdo->row("select sum(total_milage) from $tbl[order_product] where ono='$ord[ono]' and stat=5");
				reloadOrderMilage($ord['ono']);
			}
		}

		// 배송처리 문자 발송
		$ord = $pdo->assoc("select * from $tbl[order] where ono='$ono'");
		if($ord['sms'] == "Y") {
			include_once $engine_dir.'/_engine/sms/sms_module.php';
			$sms_replace['ono'] = $ono;
			$sms_replace['buyer_name'] = $ord['buyer_name'];
			$sms_replace['pay_prc'] = number_format($ord['pay_prc']);
			$sms_replace['address'] = stripslashes($ord['addressee_addr1'].' '.$ord['addressee_addr2']);
			$sms_case = $nstat+1;
			if($stat == 4 && preg_match('/@4@/', $ord['stat2']) && $ord['stat'] < 4) { // 부분발송
				$sms_case = 14;
			}
			if ($sms_case == 14 || setSmsHistory($ono, $sms_case)) {
				if($sms_case == 4 || $sms_case == 5 || $sms_case == 14) {
					if($sms_case == 4 || $dlv_code || $ord['dlv_code']) {
						$sms_replace['dlv_code'] = ($dlv_code) ? $dlv_code : $ord['dlv_code'];
						$dlv_no = ($dlv_no) ? $dlv_no : $ord['dlv_no'];
						$dlv_data = $pdo->assoc("select name, url from $tbl[delivery_url] where no='$dlv_no'");
						$sms_replace['dlv_name'] = stripslashes($dlv_data['name']);
						$dlv_link = str_replace('{송장번호}', $dlv_code, $dlv_data['url']);
						$sms_replace['dlv_link'] = $dlv_link;

						$_title = (count($_title) > 1) ? $_title[0].' 외 '.(count($_title)-1).'건' : $_title[0];
						$sms_replace['title'] = strip_tags(stripslashes($_title));
						SMS_send_case($sms_case, $ord['buyer_cell']);
					}
				} else {
					SMS_send_case($sms_case, $ord['buyer_cell']);
				}
			}
		}

		if($ord['member_no'] > 0) {
			$finish_ono = ($stat == 5) ? $ono : null;
			setMemOrd($ord['member_no'], 1, $finish_ono);
		}

		makeOrderLog($ono, "order_prd_dlv.exe.php");

		if(is_object($erpListener)) {
			$erpListener->setOrder($ono);
		}

		msg('', 'reload', 'parent');
	}

	// 택배사 정보
	if($stat > 2) {
		$psql = '';
		if($cfg['use_partner_shop'] == 'Y') {
			$psql = ($admin['level'] == 4) ? " and partner_no='$admin[partner_no]'" : " and partner_no in (0, '')";
		}
		$dres = $pdo->iterator("select * from $tbl[delivery_url] where 1 $psql ORDER BY sort ASC");
        foreach ($dres as $dlvdata) {
			$dlv_url[$dlvdata['no']] = stripslashes($dlvdata['name']);
		}
	}
	$dlv_no_default = ($ord['checkout'] == 'Y' || $ord['smartstore'] == 'Y') ? '택배사 선택' : '기존정보 유지';
	if($dlv_no_default == '기존정보 유지' && $invoice_no_exist == false) $dlv_no_default = null;
	if($cfg['invoice_essential'] == 'Y' && $invoice_no_exist != true) $dlv_no_default = null;

	// 입금계좌 정보
	if($stats[2] > 0) {
		$mall_banks = array();
		$res2 = $pdo->iterator("select * from $tbl[bank_account] order by sort asc");
        foreach ($res2 as $bank) {
			$mall_banks[$bank['no']] .= stripslashes(trim($bank['bank'].' '.$bank['account'].' '.$bank['owner']));
		}
	}

	// 취소로 배송비 0원 된 후 복구 시 삭제된 배송비 재생성
	if($cfg['delivery_type'] == 3 && $ord['dlv_prc'] == 0) {
		if($is_free_delivery != true) {
			$free_cnt = $pdo->row("select count(*) from $tbl[order_product] op inner join $tbl[product] p on op.pno=p.no where op.ono='$ord[ono]' and op.stat < 11 and p.free_delivery='Y'");
			if($free_cnt > 0) {
				$is_free_delivery = true;
			}
		}

		// 도서산간 배송비 발생 여부 확인
		$dlv_prc_local = getAddPrcd($ord['addressee_addr1']);

		if($is_free_delivery != true) { // 현재 정상 상품 및 정상으로 변경할 상품 중 무료배송 상품이 있을 경우 배송비 추가 하지 않음
			$ssql = '';
			if($cfg['delivery_base'] == 2) {
                $ssql = '-' . getOrderSalesField('', '-');
			}

			$dlv_prc = 0;
			$new_pay_prc = $pdo->row("select sum(total_prc $ssql) from $tbl[order_product] where ono='$ord[ono]' and stat < 11");
			$new_pay_prc += $_total_prc;
			if($cfg['delivery_free_limit'] > $new_pay_prc) {
				$use_add_dlv_prc = true;
				$dlv_prc = $cfg['delivery_fee'];
			}
			$dlv_prc += $dlv_prc_local;

		}

		if($dlv_prc_local > 0 && $cfg['free_delivery_area'] == 'Y' && $use_add_dlv_prc != true) { // 무료배송 상품이 있을 경우에도 지역별 추가배송비 부과
			$use_add_dlv_prc = true;
			$dlv_prc = $dlv_prc_local;
		}
	}

	ob_start();

?>
<input type="hidden" name="total_prc" value="<?=$_total_prc?>">
<table class="tbl_row">
	<caption><?=$_order_stat[$stat]?> 처리</caption>
	<colgroup>
		<col style="width:15%;">
		<col>
	</colgroup>
	<tbody>
		<?php foreach ($res as $data) {?>
		<tr>
			<th scope="row"><?=stripslashes($data['name'])?></th>
			<td>
				<?=parsePrice($data['total_prc']-getOrderTotalSalePrc($data))?> 원
			</td>
		</tr>
		<?}?>
		<?if($stats[2] > 0) {?>
		<?if($ord['dlv_prc'] > 0 && $total_prd_dlv_prc == 0) {?>
		<tr>
			<th>무료배송전환</th>
			<td>
				<label>
					<input type="checkbox" name="dlv_prc" value="<?=($cfg['delivery_fee']*-1)?>" onclick="rollBackDlvPrc(this)">
					기본 배송비 <input type="text" name="dlv_prc_tmp" class="input right" size="10" value="<?=$cfg['delivery_fee']?>" onchange="rollBackDlvPrc(this)" onkeyup="rollBackDlvPrc(this)"> <?=$cfg['currency_type']?> 차감
					<ul class="list_msg">
						<li>교환/반품 배송비는 차감되지 않습니다.</li>
					</ul>
				</label>
			</td>
		</tr>
		<?} else if($repay_prd_dlv_prc > 0) {?>
		<tr>
			<th>배송비</th>
			<td>
				<label>
					<input type="checkbox" name="restore_prd_dlv_prc" value="<?=$repay_prd_dlv_prc?>" <?=checked(in_array($stat, array(1, 2, 3)), true)?>>
					<strong><?=parsePrice($total_prd_dlv_prc, true)?></strong> <?=$cfg['currency_type']?>의 개별 배송비를 복구하여 결제금액에 재합산합니다.
				</label>
			</td>
		</tr>
		<?} else if($use_add_dlv_prc == true && $total_prd_dlv_prc == 0) {?>
		<tr>
			<th>배송비</th>
			<td>
				<label>
					<input type="checkbox" name="dlv_prc" value="<?=$dlv_prc?>" onclick="rollBackDlvPrc(this)" checked>
					<strong class="p_color2">배송비(<?=number_format($dlv_prc)?><?=$cfg['currency_type']?>)</strong> 를 추가합니다.
				</label>
				<?if($dlv_prc_local) {?>
				<ul class="list_msg">
					<li>도서산간 배송비 <?=number_format($dlv_prc_local)?> <?=$cfg['currency_type']?> 발생지역입니다.</li>
					<?if($cfg['free_delivery_area'] == 'Y') {?>
					<li><a href="?body=config@delivery" target="_blank">배송설정</a>에 의해 무료배송 대상일 때에도 도서산간 배송비는 부과됩니다.</li>
					<?}?>
				</ul>
				<?}?>
			</td>
		</tr>
		<?}?>
		<tr>
			<th scope="row">총 입금액</th>
			<td>
				<span class="total_input_prc"><?=number_format($_total_prc+$dlv_prc)?></span> 원
			</td>
		</tr>
		<tr>
			<th scope="row">입금방법</th>
			<td>
				<select name="input_pay_type" onchange="showInputMethod(this)">
					<option value="2"><?=$_pay_type[2]?></option>
					<option value="0">개인결제창</option>
				</select>
			</td>
		</tr>
		<tr class="input_method bank">
			<th scope="row">차액 입금계좌</th>
			<td><?=selectArray($mall_banks, 'input_bank', 0)?></td>
		</tr>
		<?}?>
		<?if($stat > 2 && !($stat == 3 && $ord['checkout'] == 'Y') && !($stat == 3 && $ord['talkstore'] == 'Y') && !($stat == 3 && $ord['smartstore'] == 'Y')) {?>
		<tr>
			<th>택배사</th>
			<td>
				<?=selectArray($dlv_url, 'dlv_no', false, $dlv_no_default)?>
				<?if($cfg['use_dooson'] == 'Y') {?>
				<ul class="list_msg">
					<li>두손 ERP 연동시 모든 배송처리는 ERP에서만 처리 가능합니다.</li>
					<li>윙에서 배송 처리 및 송장 입력시 배송시스템에 연동되지 않습니다. 배송처리는 ERP 시스템을 이용해 주세요.</li>
				</ul>
				<?}?>
			</td>
		</tr>
		<tr>
			<th>송장번호</th>
			<td><input type="text" name="dlv_code" class="input" size="20" onkeyup="if(event.keyCode == 13) jsPrdStatSet(this.form); return false;"></td>
		</tr>
		<?}?>
	</tbody>
</table>

<div class="box_bottom">
	<span class="box_btn blue"><input type="button" value="확인" onclick="jsPrdStatSet(this.form);"></span>
	<span class="box_btn gray"><input type="button" value="닫기" onclick="layTgl(repayDetail);"></span>
</div>
<?

	$script = php2java(ob_get_clean());

?>
<script type="text/javascript">
parent.prd_stat_refresh = 0;
parent.prdStat = <?=$stat?>;
parent.$('#repayDetail', parent.document).html("<?=$script?>").show();

var chg_dlv_no = function() {
	parent.$('input[name=dlv_code]').prop('disabled', (parent.$('select[name=dlv_no]').val() == '') ? true : false);
}
chg_dlv_no();
parent.$('select[name=dlv_no]').change(chg_dlv_no);
</script>