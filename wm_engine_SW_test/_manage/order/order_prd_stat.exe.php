<?PHP

	use Wing\API\Naver\CheckoutApi4;
    use Wing\API\Naver\CommerceAPI;
	use Wing\API\Kakao\KakaoTalkStore;
	use Wing\API\Kakao\KakaoTalkPay;

	printAjaxHeader();
	checkBasic();

	$checkout = new CheckoutApi4();

	if(getSmartStoreState() == true) {
		$CommerceAPI = new CommerceAPI();
	}

	if($cfg['repay_part'] != 'Y') msg('주문 부분상태 변경 설정이 되어있지 않습니다.');

	$ono = addslashes(trim($_POST['ono']));
	$exec = $_POST['exec'];
	$repay_no = numberOnly($_POST['repay_no']);
	$_repay_no = implode(',', $repay_no);
	$stat = numberOnly($_POST['stat']);
	$ex_dlv_prc = numberOnly($_POST['ex_dlv_prc']);
	$ostat = numberOnly($_POST['ostat']);

	$pno = numberOnly($_POST['pno']);
	$_pno = implode(',', $pno);

    if ($stat == '132') { // (카카오페이구매) 취소 불가 후 배송 처리
        $repay_no = $pno;
        $_repay_no = $_pno;
    }

	$ord = $pdo->assoc("select * from $tbl[order] where ono='$ono'");
	if($ord['member_no'] > 0) {
		$amember = $pdo->assoc("select * from $tbl[member] where no='$ord[member_no]'");
	}

	if($cfg['use_prd_dlvprc'] == 'Y') {
		$cnt1 = $pdo->row("select count(*) from {$tbl['order_product']} where ono='$ono' and no in (".($exec == 'process' ? $_repay_no : $_pno).") and prd_dlv_prc=0 and prd_type!=3");
		$cnt2 = $pdo->row("select count(*) from {$tbl['order_product']} where ono='$ono' and no in (".($exec == 'process' ? $_repay_no : $_pno).") and prd_dlv_prc>0 and prd_type!=3");
		if($cnt2 > 0 && $cnt1 > 0) {
			msg('개별 배송비가 부여된 상품은 다른 상품과 함께 처리할 수 없습니다.');
		}
	}

	// 변경할 수 있는 상태 제한
	if($ord['checkout'] != 'Y' && $ord['smartstore'] != 'Y' && $ord['external_order'] != 'talkpay') {
		foreach($repay_no as $val) {
			$pstat = $pdo->row("select stat from $tbl[order_product] where no='$val'");
			if(($stat != 12 && $stat != 13 && $stat != 18) && ($pstat == 1 || $pstat == 12)) {
				msg($_order_stat[1].'상태인 주문은 취소/교환만 가능합니다.');
			}
			if(($stat != 14 && $stat != 15 && $stat != 18) && ($pstat == 2 || $pstat == 14)) {
				if($ord['talkstore'] != 'Y' || ($prd['talkstore_ono'] && $stat != 401)) {
					msg($_order_stat[3].'상태인 주문은 환불/교환만 가능합니다.');
				}
			}
			if(($stat != 14 && $stat != 15 && $stat != 18) && ($pstat == 3 || $pstat == 14)) {
				if($ord['talkstore'] != 'Y' || ($prd['talkstore_ono'] && $stat != 401)) {
					msg($_order_stat[3].'상태인 주문은 환불/교환만 가능합니다.');
				}
			}
			if(($stat != 16 && $stat != 17 && $stat != 18) && ($pstat == 4 || $pstat == 5 || $pstat == 6 || $pstat == 16)) {
				msg($_order_stat[4].'상태인 상품은 반품/교환만 가능합니다.');
			}
		}
	}

	if($exec == "process"){
        startOrderLog($ono, 'order_prd_stat.exe.php'); // 주문 로그 작성

		include_once $engine_dir."/_engine/include/milage.lib.php";
		include_once $engine_dir.'/_engine/include/wingPos.lib.php';

		$repay_prc = numberOnly($_POST['repay_prc'], true);
		$repay_milage = numberOnly($_POST['repay_milage'], true);
		$repay_member_milage = numberOnly($_POST['repay_member_milage'], true);
		$repay_dlv_prc = numberOnly($_POST['repay_dlv_prc'], true);
		$repay_prd_dlv_prc = numberOnly($_POST['repay_prd_dlv_prc']); // 개별 배송비 환불

		if(count($repay_no) < 1 || empty($stat) == true) msg('처리할 상품이 없습니다.');

		if($_POST['dlv_prc_no_return'] == 'Y') { // 환불 교환 배송비 받지 않음
			unset($_POST['repay_dlv_prc'], $repay_dlv_prc);
		}

		if($_POST['add_dlv_type'] == 1) { // 주문 취소로 인한 추가 배송비 받지 않음
			$_POST['dlv_prc'] = 0;
		}

		if($ord['checkout'] != 'Y' && $ord['smartstore'] != 'Y' && ($stat == 13 || $stat == 15 || $stat == 17 || $stat == 19)) {
			if($_POST['total_repay_prc'] < 0) msg('취소/환불/반품 금액은 0 '.$cfg['currency_type'].' 보다 작게 입력하실 수 없습니다.');

			$emoney_repay = numberOnly($_POST['emoney_repay'], true);
			$milage_repay = numberOnly($_POST['milage_repay'], true);
			$total_repay_prc = numberOnly($_POST['total_repay_prc'], true);
			$pay_type = numberOnly($_POST['pay_type']);
			$bank = addslashes(trim($_POST['bank']));
			$bank_account = addslashes(trim($_POST['bank_account']));
			$bank_name = addslashes(trim($_POST['bank_name']));
			$cpn_no = numberOnly($_POST['cpn_no']);
			$repay_prdcpn_no = numberOnly($_POST['repay_prdcpn_no']);

			if($total_repay_prc == 0 && $milage_repay) $pay_type = 3;
			if($total_repay_prc == 0 && $emoney_repay) $pay_type = 6;

			if($emoney_repay > 0 && $emoney_repay > $ord['emoney_prc']) msg('주문의 잔여 예치금보다 복구 예치금이 더 많습니다.');
			if($milage_repay > 0 && $milage_repay > $ord['milage_prc']) msg('주문의 잔여 적립금보다 복구 적립금이 더 많습니다.');
			if($pay_type == 2 && $total_repay_prc > 0) {
				if(!$bank) msg('환불 은행을 선택해 주세요.');
				if(!$bank_account) msg('환불 계좌번호를 입력해 주세요.');
				if(!$bank_name) msg('환불계좌의 예금주명을 입력해 주세요.');
			}
			if($stat > 13 && !$pay_type && $total_repay_prc > 0) msg('환불방법을 선택해 주세요.');
			if(!$_POST['reason']) msg('사유를 선택해 주세요.');
		}

		$_title = '';
		$add_dlv_prc = ($_POST['add_dlv_type'] == 2) ? numberOnly($_POST['add_dlv_prc'], true): 0;
		$repay_cnt = count($repay_no);
		$process = 0;
		$repay_sale5 = 0;
		$sum_repay_milage = 0;
		$cpn_recalc = ($_POST['cpn_recalc'] > 0) ? numberOnly($_POST['cpn_recalc']) : 0;
		$process_partner_no = 0;

        if (
            $stat == 13
            || $stat == 15
            || $stat == 17
        ) {
            // 전체상품 쿠폰 반환 체크 (사용(만료) 후 다시 다운로드 가능 쿠폰 대상)
            if ($cpn_no) {
                $dup_cpn_rst = coupon_dup_check($cpn_no);
                if ($dup_cpn_rst['cpn_name']) {
                    if (!$is_counsel) msg('[' . $dup_cpn_rst['cpn_name'][0] . '] 반환할 전체상품 쿠폰을 고객이 이미 보유하고 있습니다.');
                    $cpn_no = '';
                }
            }
            // 사용자 취소/자동취소 시 개별쿠폰 $repay_prdcpn_no 값을 전달하지 않아 wm_order_product 조회해서 처리.
            if (
                $is_counsel
                && !empty($_repay_no)
            ) {
                $repay_prdcpn_no = "";
                $repay_prdcpn_no = $pdo->row("select group_concat(prdcpn_no) as cpnno from $tbl[order_product] where ono='$ono' and no in ($_repay_no) and sale7 > 0 and stat < 10 group by ono");
            }
            //개별상품 쿠폰 반환 체크 (사용(만료) 후 다시 다운로드 가능 쿠폰 대상)
            if ($repay_prdcpn_no) {
                if (is_array($repay_prdcpn_no) == true && count($repay_prdcpn_no) > 0) {
                    $repay_prdcpn_no = implode(',', $repay_prdcpn_no);
                }
                $dup_prdcpn_rst = coupon_dup_check($repay_prdcpn_no, true);
                if (!$is_counsel && $dup_prdcpn_rst['cpn_name']) msg('[' . implode(',', $dup_prdcpn_rst['cpn_name']) . '] 반환할 개별상품 쿠폰을 고객이 이미 보유하고 있습니다.');
                $repay_prdcpn_no = $dup_prdcpn_rst['rtn_cpn'];
            }
        }

        // 남은 상품에 쿠폰할인 재분배
		if($cpn_recalc > 0) {
			$_remain_prc = 0;
			$_res = $pdo->iterator("select * from $tbl[order_product] where ono='$ono' and no not in ($_repay_no) and sale5 > 0 and stat < 10");
            foreach ($_res as $_prd) {
				$_prc[$_prd['no']] = $_prd['total_prc']-getOrderTotalSalePrc($_prd);
				$_remain_prc += $_prc[$_prd['no']];
			}
			if($cpn_recalc > $_remain_prc) {
				msg(sprintf('남은 상품금액(%s%s)이 쿠폰 할인금액보다 작으므로 재분배가 불가능합니다.', number_format($_remain_prc), $cfg['currency_type']));
			}
			$_cpn_recalc = $cpn_recalc;
			$i = 0;
			foreach($_prc as $_no => $_calc_prc) {
				$i++;
				if(count($_prc) == $i) {
					$_spt = $_cpn_recalc;
				} else {
					$_spt = getPercentage($cpn_recalc, ($_calc_prc/$_remain_prc)*100, 1);
				}
				$_cpn_recalc -= $_spt;

				$pdo->query("update $tbl[order_product] set sale5=sale5+'$_spt' where no='$_no'");
			}
		}

		// 처리할 주문 데이터 확인
		for($ii=0; $ii < $repay_cnt; $ii++) {
			$prd = $pdo->assoc("select * from $tbl[order_product] where no='$repay_no[$ii]'");
			if(!$prd['no']) msg('상품정보가 없습니다.');
			if($data['repay_prc'] > 0) {
				if($stat == 17 && $data['stat'] != 16) {
					alert('반품 처리중인 상품이 아닙니다.');
					continue;
				}
			}
			if($stat == $prd['stat']) continue;

            if ($cpn_recalc > 0) { // 쿠폰 할인금액 재분배시 환불 금액 변경
                $repay_prc[$ii] += $prd['sale5'];
    			$pdo->query("update $tbl[order_product] set sale5=0 where no in ($_repay_no)");
            }

			if($ord['checkout'] == 'Y') { // 네이버페이 처리
				if (isset($extrenal_process_cnt) == false) {
                    $extrenal_process_cnt = 0;
                }
				include 'order_checkout_cancel.exe.php';
				if($checkout->error) {
					alert("[네이버페이 메시지] $prd[checkout_ono]\\n\\n".php2java($checkout->error));
					continue;
				} else {
				    $extrenal_process_cnt++;
				}
				if($stat == $prd['stat']) {
					$process++;
					continue;
				}
			}

            if ($ord['external_order'] == 'talkpay') { // 카카오페이 구매 처리
				if (isset($extrenal_process_cnt) == false) {
                    $extrenal_process_cnt = 0;
                }
                include 'order_talkpay_cancel.exe.php';

                if ($ret !== true) {
                    $ret = str_replace("'", '"', $ret);
                    msg(php2java($ret), 'reload', 'parent');
                }
                $extrenal_process_cnt++;
                if($stat == $prd['stat']) {
                    $process++;
                    continue;
                }
            }

			if($ord['smartstore'] == 'Y') { // 스마트스토어 처리
				if (isset($extrenal_process_cnt) == false) {
                    $extrenal_process_cnt = 0;
                }
				include 'order_smartstore_cancel.exe.php';

				if($CommerceAPI->getError()) {
                    alert(php2java(sprintf(
                        "- 스마트스토어 (%s)\n\n%s",
                        $prd['smartstore_ono'],
                        $CommerceAPI->getError()
                    )));
					continue;
				}else{
					$extrenal_process_cnt++;
				}

				if($stat == $prd['stat']) {
					$process++;
					continue;
				}
			}

			if($prd['talkstore_ono']) { // 카카오톡 스토어 처리
				if(is_object($kts) == false) {
					$kts = new KakaoTalkStore();
				}

				if($stat == 15) {
					$ret = $kts->orderCancel($prd['talkstore_ono']);
					if($ret != 'OK') {
						$ret = json_decode($ret);
						alert(php2java($ret->extras->error_message));
						continue;
					}
				} elseif ($stat == 401) {
					$stat = $prd['stat'];
					$deliveryExpectedAt = date('Y-m-d 18:00:00', strtotime($_POST['deliveryExpectedAt']));
					$delayCausationCode = $_POST['delayCausationCode'];
					$delayCausation = trim($_POST['delayCausation']);
					$orderId = $prd['talkstore_ono'];

					if(!$delayCausation) msg('배송지연 상세 사유를 입력해 주세요.');

					$ret = $kts->setDeliveryDealy($delayCausation, $delayCausationCode, $deliveryExpectedAt, array($orderId));
					if($ret != 'OK') {
						$ret = json_decode($ret);
						msg($ret->extras->error_message);
					}
					$process++;
				} else {
					msg('카카오톡 스토어 주문은 품절 취소만 가능합니다.');
				}
			}

			$process++;

			// 재고 처리 (ERP 이용 시 주문 처리 후 ERP로 부터 최종 재고를 받으므로 불필요
			if(!is_object($erpListener)) {
				$prevent_resolve = ($prd['dlv_hold'] == 'Y') ? true : false; // 보류인 상품은 취소해도 다른 상품을 보류해제 하지 않음
				orderStock($prd['ono'], $prd['stat'], $stat, $prd['no']);
			}

			if($cpn_recalc == 0 && $prd['sale5'] > 0) { // % 쿠폰 할인 금액 제외
				$repay_sale5 += $prd['sale5'];
			}
			$add_q = '';
			$add_q = ($amember['no']) ? " , `repay_milage`='$repay_milage[$ii]'+'$repay_member_milage[$ii]'" : '';

			if($stat < 10) { // 네이버페이 주문상태 복구 되는 경우
				$add_q .= ", `repay_prc`='0', `repay_date`='0'";
			} else {
				$add_q .= ", `repay_prc`='$repay_prc[$ii]', `repay_date`='$now'";
			}

			if($repay_prd_dlv_prc > 0) { // 개별 배송비 환불
				$add_q .= ', repay_prd_dlv_prc=prd_dlv_prc';
			}
			$r = $pdo->query("update `$tbl[order_product]` set ostat=stat, `stat`='$stat' $add_q where `no`='$prd[no]'");
			if($r) {
				if($stat != 13 && $amember['no'] && ($repay_milage[$ii]+$repay_member_milage[$ii]) > 0) {
					if($ord['milage_down'] == 'Y' && $prd['stat'] == 5 && $prd['repay_milage'] < 1) { // 지금 적립금 환수
						//ctrlMilage('-', 12, ($repay_milage[$ii]+$repay_member_milage[$ii]), $amember, $prd[name], '', $admin[admin_id]);
						//reloadOrderMilage($ord['ono']);
						$sum_repay_milage += $repay_milage[$ii]+$repay_member_milage[$ii];
					}
				}
			}
			$_title=($_title) ? $_title."/".$prd['name'] : $prd['name'];
			if(!$_title2) $_title2 = $prd['name'];

			if($cfg['use_partner_shop'] == 'Y' && $cfg['use_partner_delivery'] == 'Y' && $prd['dlv_type'] != '1') { // 입점사 개별 배송처리
				$partner_exchange = $prd['partner_no'];
			}

			if($prd['account_idx'] > 0) { // 기 정산등록 여부 확인
				$account_registed = true;
				$process_partner_no = $prd['partner_no'];
			}
		}
		if($repay_cnt > 1) {
			$_title2 = $_title2.' 外 '.($repay_cnt-1);
		}
		javac("
			var prdFrm = parent.document.getElementById('prdFrm');
			if(prdFrm) {
				prdFrm.exec.value = '';
			}
		");

		if($process < 1) msg('처리 된 주문이 없습니다.');
		if(!$r){
			if($extrenal_process_cnt){
				msg('', 'reload', 'parent');
			}else{
				msg('업데이트 오류.');
			}
		}

		$oadd_q = '';
		$_repay_prc = $pdo->row("select sum(`repay_prc`) from `$tbl[order_product]` where `ono`='$ono'");
		if($amember['no']) {
			$_repay_milage = $pdo->row("select sum(`repay_milage`) from `$tbl[order_product]` where `ono`='$ono'");
			$oadd_q = " , `repay_milage`='$_repay_milage'";
		}
		if ($stat != 18 && $stat != 12 && $stat != 14 && $stat != 16) {
			$pdo->query("update `$tbl[order]` set `repay_prc`='$total_repay_prc', `repay_date`='$now', sale5=sale5-'$repay_sale5' $oadd_q where `ono`='$ono'");
		}

		if($amember['no']){
			if($total_give_milage > 0) ctrlMilage('+', 13, $total_give_milage, $amember, $_title, "", $admin['admin_id']);
		}

		if($ord['checkout'] != 'Y' && $ord['smartstore'] != 'Y' && ($stat == 13 || $stat == 15 || $stat == 17 || $stat == 19)) {
			include_once $engine_dir.'/_engine/include/milage.lib.php';

			if($emoney_repay > 0) {
				ctrlEmoney('+', 8, $emoney_repay, $amember, $_title2, false, $admin['admin_id'], $ono);
				$pdo->query("update $tbl[order] set emoney_prc=emoney_prc-$emoney_repay where ono='$ono'");
			}
			if($milage_repay > 0) {
				ctrlMilage('+', 8, $milage_repay, $amember, $_title2, false, $admin['admin_id'], $ono);
				$pdo->query("update $tbl[order] set milage_prc=milage_prc-$milage_repay where ono='$ono'");
			}
			if($pay_type == 3) {
				ctrlMilage('+', 3, $total_repay_prc, $amember, "[$ono] 환불 ".implode(',', $repay_no), false, $admin['admin_id'], $ono);
			}
			if($pay_type == 6) {
				ctrlEmoney('+', 3, $total_repay_prc, $amember, "[$ono] 환불 ".implode(',', $repay_no), false, $admin['admin_id'], $ono);
			}

			if($_POST['cpn_free_dlv']) { // 전체취소시 무료배송쿠폰 사용에 의한 배송비, 총 주문금액 삭제
				$cpn_free_dlv = numberOnly($_POST['cpn_free_dlv'], true);
				$pdo->query("update $tbl[order] set dlv_prc=dlv_prc-'$cpn_free_dlv', total_prc=total_prc-'$cpn_free_dlv' where ono='$ono'");
			}

			// 개별상품 쿠폰 반환
			if ($is_counsel && !is_array($repay_prdcpn_no)) {
				$pdo->query("update $tbl[coupon_download] set ono='', use_date=0, cart_no=0 where ono = '$ono'");
			} else if(is_array($repay_prdcpn_no) == true && count($repay_prdcpn_no) > 0) {
				$repay_prdcpn_no = implode(',', $repay_prdcpn_no);
				$pdo->query("update $tbl[coupon_download] set ono='', use_date=0, cart_no=0 where no in ($repay_prdcpn_no)");
			}

			$payment_no = createPayment(array(
				'ono' => $ono,
				'pno2' => $repay_no,
				'pay_type' => $_POST['pay_type'],
				'amount' => -($total_repay_prc),
				'reason' => $_POST['reason'],
				'comment' => addslashes($_POST['comment']),
				'bank' => $bank,
				'bank_account' => $bank_account,
				'bank_name' => $bank_name,
				'ex_dlv_prc' => $ex_dlv_prc,
				'ex_dlv_type' => $POST['ex_dlv_type'],
				'dlv_prc' => (($repay_dlv_prc+$repay_prd_dlv_prc)*-1)+$add_dlv_prc,
				'repay_emoney' => $emoney_repay,
				'repay_milage' => $milage_repay,
				'cpn_type' => ($cpn_no > 0) ? 'cancel' : null,
				'cpn_no' => $cpn_no,
				'copytomemo' => $_POST['copytomemo']
			), $stat);

			// 정산 등록후 취소
			if(isset($account_registed) == true) {
				if(isTable($tbl['order_account_refund']) == false) {
					include_once $engine_dir.'/_config/tbl_schema.php';
					$pdo->query($tbl_schema['order_account_refund']);
				}

				$cpn_fd = (fieldExist($tbl['order_product'], 'sale7')) ? '+sale7' : '';

				$account_repay_no = implode(',', $repay_no);
				$account_repay_dlv_prc = $add_dlv_prc+$ex_dlv_prc; // 환불 배송비
				$account_repay_prd_prc = $total_repay_prc+$account_repay_dlv_prc-$repay_dlv_prc; // 환불 상품금액
				$account_repay_prc = $total_repay_prc; // 총 환불금액
				$account_repay_fee_prc = $pdo->row("select sum(fee_prc) from $tbl[order_product] where no in ($account_repay_no)");
				$account_repay_cpn_fee_m = $pdo->row("select sum(sale5{$cpn_fd}-cpn_fee) from $tbl[order_product] where no in ($account_repay_no)");
				$account_repay_cpn_fee = $pdo->row("select sum(cpn_fee) from $tbl[order_product] where no in ($account_repay_no)");
				$account_repay_dlv_return_prc = $repay_dlv_prc;

				$pdo->query("
					insert into {$tbl['order_account_refund']} (ono, payment_no, partner_no, prd_prc, dlv_prc, dlv_prc_return, total_prc, fee_prc, cpn_fee_m, cpn_fee, admin_id, reg_date, account_idx)
					values ('$ono', '$payment_no', '$process_partner_no', '$account_repay_prd_prc', '$account_repay_dlv_prc', '$account_repay_dlv_return_prc', '$account_repay_prc', '$account_repay_fee_prc', '$account_repay_cpn_fee_m', '$account_repay_cpn_fee', '{$admin['admin_id']}', now(), 0)
				");
			}
		}

		if(isset($partner_exchange) == true) { // 입점사 배송비 정산 테이블 갱신
			$change_dlv_prc = (($repay_dlv_prc+$repay_prd_dlv_prc)*-1)+$add_dlv_prc+$ex_dlv_prc;
			if($change_dlv_prc != 0) {
				$pdo->query("update {$tbl['order_dlv_prc']} set dlv_prc=dlv_prc+$change_dlv_prc where ono='$ono' and partner_no='$partner_exchange'");
			}
		}

		ordStatLogw($ono, 100, null, null,
				array(
					'payment_no' => $payment_no,
					'pno' => $repay_no,
					'content' => "선택 $_order_stat[$stat]"
				)
		);

		// 현금영수증 재계산
		chgCashReceipt($ono);

		$nstat = ordChgPart($ono); //부분 주문상태 저장
		if($cfg['milage_use'] == 1 && $amember['no']) {
			// 적립금 차감
			if($sum_repay_milage > 0) {
				ctrlMilage('-', 12, $sum_repay_milage, $amember, $prd['name'], '', $admin[admin_id]);
			}
			reloadOrderMilage($ord['ono']);

			// 전체 배송완료 일때 적립급 적립
			if($ord['milage_down'] != 'Y' && $ord['stat'] != 5 && $nstat == 5) {
				$sum_total_milage = $pdo->row("select sum(total_milage) from $tbl[order_product] where ono='$ord[ono]' and stat=5");
				$sum_repay_milage = $pdo->row("select sum(total_milage) from $tbl[order_product] where ono='$ord[ono]' and stat>10");
				ctrlMilage('+', 0, $sum_total_milage, $amember, $prd['name'], '', $admin['admin_id']);
				$pdo->query("update $tbl[order] set `milage_down`='Y',`milage_down_date`='$now', repay_milage='$sum_repay_milage' where ono='$ord[ono]'");
			}
		}

		// 관련 ERP 재고 다시 파악
		if(is_object($erpListener)) {
			$tpno = $pdo->row("select group_concat(distinct complex_no) from $tbl[order_product] where ono='$ono' and complex_no > 0");
			if($tpno) {
				$res = $pdo->iterator("select sku from erp_complex_option where complex_no in ($tpno)");
                foreach ($res as $data) {
					$erpListener->getStock($data['sku']);
				}
			}
		}

		if($ord['member_no'] > 0) {
			setMemOrd($ord['member_no'], 1);
		}

		if($stat == 15 || $stat == 17) {
			include_once $engine_dir.'/_engine/sms/sms_module.php';
			$case = ($stat == 15) ? 29 : 30;
			if($ord['sms'] == 'Y') {
				$_repay_no = implode(',', $repay_no);
				$_tmp = $pdo->assoc("select name, (count(*)-1) as cnt from {$tbl['order_product']} where no in ($_repay_no)");
				$_title = strip_tags($_tmp['name']);
				if($_tmp['cnt'] > 0) $_title .= ' 외 '.$_tmp['cnt'].'건';

				$sms_replace['buyer_name'] = $ord['buyer_name'];
				$sms_replace['ono'] = $ord['ono'];
				$sms_replace['title'] = $_title;
				$sms_replace['pay_prc'] = parsePrice($total_repay_prc, true);
				SMS_send_case($case, $ord['buyer_cell']);
			}
		}

		makeOrderLog($ono, "order_prd_stat.exe.php");

		if($is_counsel == true) { // 마이페이지에서 호출
			return;
		}

		msg('', 'reload', 'parent');
	}

	if(count($pno) < 1) msg('처리하실 상품을 선택하세요.');
	if(!$stat || $stat < 11) msg('변경 불가능한 상태입니다.');
	if($ord['checkout'] != 'Y' && $ord['smartstore'] != 'Y' && $ord['external_order'] != 'talkpay' && $ostat > 10 && (($ostat == 18 || $ostat == 24) && ($stat != 24 && $stat != 25))) msg('일반상태의 주문서가 아닙니다.');

	$_repay_part_stat = array('' => '일반', 12=>'취소요청', 13 => '취소', 14=>'환불요청', 15 => '환불', 16=>'반품요청', 17 => '반품', 18=>'교환요청', 19=> '교환', 24=>'교환요청승인', 25=>'교환수거완료', 26=>'교환재발송', 27=>'반품거부', 171=>'반품보류', 172=>'반품보류해제', 28=>'교환거부', 191=>'교환보류', 192=>'교환보류해제', 401 => '배송지연');
	$title = $_repay_part_stat[$stat];

	$pname = array();
	$script = "<table class='tbl_row'>\
	<caption>{$title}처리</caption>\
	<colgroup>\
		<col style='width:17%'>\
	</colgroup>";

	$calc_qry = '';
	if($cfg['ts_use'] == 'Y') {
		$calc_qry .= "+sale3";
	}

	$input_s = "onclick='this.select();' onkeypress='numCk();'";
	$total_repay_prc = $total_repay_milage = $total_sale5 = $total_prd_dlv_prc = 0;
	for($ii = 0; $ii < count($pno); $ii++) {
		$prd = $pdo->assoc("select * from $tbl[order_product] where no='$pno[$ii]'");
		if($ord['checkout'] != 'Y' && $ord['smartstore'] != 'Y' && $ord['external_order'] != 'talkpay') {
			$pstat = $prd['stat'];
			if(($stat != 12 && $stat != 13 && $stat != 18) && ($pstat == 1 || $pstat == 12)) {
				msg($_order_stat[1].'상태인 주문은 취소/교환만 가능합니다.');
			}
			if(($stat != 14 && $stat != 15 && $stat != 18) && ($pstat == 2 || $pstat == 14)) {
				if(!$prd['talkstore_ono'] || ($prd['talkstore_ono'] && $stat != 401)) {
					msg($_order_stat[2].'상태인 주문은 환불/교환만 가능합니다.');
				}
			}
			if(($stat != 14 && $stat != 15 && $stat != 18) && ($pstat == 3 || $pstat == 14)) {
				if(!$prd['talkstore_ono'] || ($prd['talkstore_ono'] && $stat != 401)) {
					msg($_order_stat[3].'상태인 주문은 환불/교환만 가능합니다.');
				}
			}
			if(($stat != 16 && $stat != 17 && $stat != 18) && ($pstat == 4 || $pstat == 5 || $pstat == 6 || $pstat == 16)) {
				msg($_order_stat[4].'상태인 주문은 반품/교환만 가능합니다.');
			}
			if($prd['account_idx'] > 0) {
				$account_registed = true;
			}
		}

		$prd['name'] = addslashes($prd['name']);
		$_prdname = strip_tags(trim($prd['name']));
		$_prdoption = addslashes(str_replace('<split_small>', ' : ', str_replace('<split_big>', ' / ', trim($prd['option']))));

		if($prd['checkout_ono']|| $prd['smartstore_ono']) {
			//
		} else {
			if(($prd['stat']==4 || $prd['stat'] == 5|| $prd['stat'] == 6) && ($stat==12 || $stat==13 || $stat==14 || $stat==15)) {
				msg($_order_stat[4]."상태인 주문은 취소 및 환불 처리하실수 없습니다.\\n반품/교환 처리만 가능합니다.");
			}
		}

		if(isset($account_registed) == true) {
			$script .= "
			<tr>
				<td colspan='2' class='p_color2'>
					입점사 정산 등록이 완료된 주문서입니다. 주문 취소 시 입점사로부터 정산금액이 회수됩니다.<br>
					<strong>선택한 상품 반품 후 일반 상태로 변경할 수 없습니다.</strong>
				</td>
			</tr>
			";
		}

		$cancel_prc = $prd['total_prc']-getOrderTotalSalePrc($prd);
		if($prd['repay_prc'] > 0) $cancel_prc = $prd['repay_prc'];
		if($prd['checkout_ono'] > 0 || $ord['external_order'] == 'talkpay') {
			$script .= "
				<tr>
					<th scope='row'>
						$prd[checkout_ono] {$prd['external_id']}
						<input type=hidden name='repay_no[]' value='$prd[no]'>
					</th>
					<td>$prd[name]</td>
				</tr>
			";
			$script .= "<tr>\
				<th scope='row' rowspan='$_prd_rowspan' style='width:auto;'><input type=hidden name='repay_no[]' value='$prd[no]'>&nbsp;$_prdname&nbsp;<div>$_prdoption</div></th>";
			$script .= "<td>".$title."금액 : ".parsePrice($prd['total_prc'], true)." ".$cfg['currency_type']."<input type='hidden' name='repay_prc[]' value='$cancel_prc' class='autorepayprc'></td>";
		} else if($prd['smartstore_ono'] > 0) {
			$script .= "
				<tr>
					<th scope='row'>
						$prd[smartstore_ono]
						<input type=hidden name='repay_no[]' value='$prd[no]'>
					</th>
					<td>$prd[name]</td>
				</tr>
			";
		} else {
			$_prd_rowspan = ($amember) ? 2 : 1;
			if($stat%2 == 1) {
				$script .= "<tr>\
					<th scope='row' rowspan='$_prd_rowspan' style='width:auto;'><input type=hidden name='repay_no[]' value='$prd[no]'>&nbsp;$_prdname&nbsp;<div>$_prdoption</div></th>";
				if($prd['checkout_ono'] || $prd['talkstore_ono'] || $prd['smartstore_ono']) {
					$script .= "<td>".$title."금액 : ".parsePrice($prd['total_prc'], true)." ".$cfg['currency_type']."<input type='hidden' name='repay_prc[]' value='$cancel_prc' class='autorepayprc'></td>";
				} else {
					$script .= "<td>".$title."금액입력 : <input type=text name='repay_prc[]' value='$cancel_prc' class='input right autorepayprc' size=10 $input_s> ".$cfg['currency_type']."</td>";
				}
				$script .= "</tr>";
			} else {
				$script .= "
				<tr>
					<th rowspan='$_prd_rowspan'><input type=hidden name='repay_no[]' value='$prd[no]'>&nbsp;$_prdname&nbsp;<div>$_prdoption</div></th>
					<td>$title 금액 : <strong class='number'>".parsePrice($prd['total_prc'], true)."</strong> ".$cfg['currency_type']."</td>
				</tr>
				";
			}

			if($amember){
				$mlt=($stat == 13) ? "취소" : "반환";
				if($stat%2 == 1) {
					$script .= "
					<tr>
						<td>
							{$mlt}상품적립금: <input type=text name='repay_milage[]' value='".($prd['total_milage']-$prd['member_milage'])."' class='input right' size=5> ".$cfg['currency_type'].",
							{$mlt}회원적립금: <input type=text name='repay_member_milage[]' value='$prd[member_milage]' class='input right' size=5> ".$cfg['currency_type']."
						</td>
					</tr>
					";
				} else {
					$script .= "
					<tr>
						<td>
							{$mlt}상품적립금: <strong class='number'>".parsePrice($prd['total_milage']-$prd['member_milage'], true)."</strong> $cfg[currency_type] ,
							{$mlt}회원적립금: <strong class='number'>".parsePrice($prd['member_milage'], true)."</strong> $cfg[currency_type]
							<input type='hidden' name='repay_milage[]' value='".($prd['total_milage']-$prd['member_milage'])."'>
							<input type='hidden' name='repay_member_milage[]' value='{$prd['member_milage']}'>
						</td>
					</tr>
					";
				}

			}
		}
		$script .= "<input type='hidden' name='repay_prc_org[]' value='$cancel_prc'>";
		$script .= "<input type='hidden' name='repay_sale5[]' value='$prd[sale5]'>";

		$total_repay_prc += $cancel_prc;
		$total_repay_milage += $prd[total_milage];

		if($prd['sale5'] > 0) { // 총 쿠폰 할인 금액
			$total_sale5 += $prd['sale5'];
		}

		if($prd['prdcpn_no']) { // 사용 상품별 쿠폰
			$repay_prdcpn_no[] = $prd['prdcpn_no'];
		}

		if($cfg['use_partner_shop'] == 'Y' && $cfg['use_partner_delivery'] == 'Y' && $prd['dlv_type'] != '1') { // 업체배송 대상
			$partner_exchange = $prd['partner_no'];
		}

		if($cfg['use_prd_dlvprc'] == 'Y') { // 총 개별 배송비
			$total_prd_dlv_prc += $prd['prd_dlv_prc'];
		}

        // 세트 부분 취소 금지를 위한 데이터 정리
        if ($prd['set_idx']) {
            if (isset($_set_check) == false) {
                $_set_check = array();
            }
            $_set_check[$prd['set_idx']]++;
        }
	}

    // 세트 부분 취소 금지
    if (isset($_set_check) == true && count($_set_check) > 0) {
        foreach ($_set_check as $_set_idx => $ea) {
            $cnt = $pdo->row("select count(*) from {$tbl['order_product']} where ono='$ono' and set_idx='$_set_idx' and (stat < 10 or stat in (12, 14, 16, 18))");
            if ($cnt != $ea) {
                msg('세트 상품은 부분 취소/환불/반품하실 수 없습니다.');
            }
        }
    }

	// 입점사별 배송 정책 적용
	if(isset($partner_exchange) == true) {
		setPartnerDlvConfig($partner_exchange);
	}
	$ptn_sql = ($cfg['use_partner_shop'] == 'Y' && $cfg['use_partner_delivery'] == 'Y') ? " and partner_no='$partner_exchange'" : '';
    $ptn_sql2 = ($cfg['use_partner_shop'] == 'Y' && $cfg['use_partner_delivery'] == 'Y') ? " and op.partner_no='$partner_exchange'" : '';
	$no_prd_dlv_prc_qry = ($cfg['use_prd_dlvprc'] == 'Y') ? ' and prd_dlv_prc=0' : '';

	if(!$prd['checkout_ono'] && !$prd['smartstore_ono'] && ($stat == 13 || $stat == 15 || $stat == 17)) {
		if($prd['prd_dlv_prc'] == 0 || $prd['prd_dlv_prc'] == null) {
			$all_cancel = (count($pno) == $pdo->row("select count(*) from $tbl[order_product] where ono='$ono' and stat in (1,2,3,4,5,12,14,16,18) $no_prd_dlv_prc_qry"));
			$all_cancel_partner = (count($pno) == $pdo->row("select count(*) from {$tbl['order_product']} where ono='$ono' and stat in (1,2,3,4,5,12,14,16,18) $ptn_sql $no_prd_dlv_prc_qry"));

			// 취소로 배송비 발생 시
			if($cfg['use_partner_shop'] == 'Y' && $cfg['use_partner_delivery'] == 'Y') {
				$order_dlv_prc = $pdo->row("select dlv_prc from {$tbl['order_dlv_prc']} where ono='$ono' $ptn_sql");
			} else {
				$order_dlv_prc = $ord['dlv_prc'];
			}
			if($cfg['use_prd_dlvprc'] == 'Y') { // 환불 배송비에서 개별 배송비 제외
				$order_dlv_prc -= $pdo->row("select sum(prd_dlv_prc) from {$tbl['order_product']} where ono='$ono' and stat < 10 $ptn_sql");
			}
            $order_dlv_prc -= ($ord['sale2_dlv']+$ord['sale4_dlv']);
		}

		$script .= "</table><br><table class='tbl_row'><col style='width:17%'><caption>환불 설정</caption>";

		if($all_cancel == false && ($stat == 13 || $stat == 15) && $cfg['delivery_type'] == 3 && $order_dlv_prc == 0 && $total_prd_dlv_prc == 0) {
			$ssql = getOrderSalesField(null, '+');
			$base_prc = $pdo->row("select sum(total_prc)-sum($ssql) from {$tbl['order_product']} where ono='$ono' and no not in ($_pno) and repay_prc=0 $ptn_sql $no_prd_dlv_prc_qry"); // 취소 상품을 제외한 총 상품금액
			$dlv_prc = ($cfg['delivery_free_limit'] > $base_prc) ? $cfg['delivery_fee'] : 0; // 취소로 인해 발생하는 배송비
			$is_free_dlv = $pdo->row("
				select
					count(*)
				from
					{$tbl['order_product']} op inner join {$tbl['product']} p on op.pno=p.no
				where
					op.ono='$ono'
					and op.no not in ($_pno)
					and repay_prc=0 and free_delivery='Y'
					$ptn_sql2 $no_prd_dlv_prc_qry
			"); // 남은 상품 내 무료 배송 상품 존재 확인

			if($is_free_dlv == 0 && $dlv_prc > 0) {
				$script .= "
				<tr>
					<th scope='row'>추가배송비</th>
					<td>
						<div class='desc3' style='font-size:12px; font-weight: bold;'>선택한 상품을 취소할 경우 추가 배송비가 발생됩니다.</div>
						<p><label><input type='radio' name='add_dlv_type' value='1' class='autorepayprc'> 받지 않습니다.</label></p>
						$ex_dlv_option
						<p><label><input type='radio' name='add_dlv_type' value='2' class='autorepayprc' checked> 배송비에 추가합니다.</label></p>
						<input type='text' name='add_dlv_prc' value='$cfg[delivery_fee]' class='input right autorepayprc' size='10'> ".$cfg['currency_type']."
					</td>
				</tr>
				";
			}
			$total_repay_prc += $dlv_prc;
		}

		// 개별 배송비 환불
		if($cfg['use_prd_dlvprc'] == 'Y' && $total_prd_dlv_prc > 0) {
			$checked = ($stat == 13 || $stat == 15) ? 'checked' : '';
			$script .= "
			<tr>
				<th scope='row'>개별 배송비</th>
				<td>
					<label><input type='checkbox' name='repay_prd_dlv_prc' class='autorepayprc' value='$total_prd_dlv_prc' $checked> 개별 배송비 <strong>".parsePrice($total_prd_dlv_prc, true)."</strong> 원을 환불합니다.</label>
				</td>
			</tr>
			";
		}

		if($stat != 13 && $stat != 15) {
			if(!$cfg['delivery_fee']) $cfg['delivery_fee'] = 0;
			$_ex_dlv_prc = ($total_prd_dlv_prc > 0) ? $total_prd_dlv_prc : $cfg['delivery_fee'];
			$script .= "
				<tr>
					<th scope='row'>교환/반품 배송비 $tmp</th>
					<td>
						<label><input type='radio' name='ex_dlv_type' value='1' onclick='setExDlv(0);'> 없음</label>
						<label><input type='radio' name='ex_dlv_type' value='2' onclick='setExDlv($_ex_dlv_prc);' checked> 편도</label>
						<label><input type='radio' name='ex_dlv_type' value='3' onclick='setExDlv($_ex_dlv_prc*2);'> 왕복</label>
						<input type='text' name='ex_dlv_prc' value='$_ex_dlv_prc' class='input right autorepayprc' size='10'> ".$cfg['currency_type']."
						<span class='desc1'>(주문배송비에 합산됩니다.)</span>
					</td>
				</tr>
			";
		}

		if($ord['emoney_prc'] > 0) {
			$emoney_repay_default = ($all_cancel == true) ? $ord['emoney_prc'] : 0;
			$script .= "
				<tr>
					<th scope='row'>사용예치금 복구</th>
					<td>
						예치금 <input type='text' name='emoney_repay' class='input right autorepayprc' size='10' value='$emoney_repay_default'> ".$cfg['currency_type']."
						(사용 예치금 ".parsePrice($ord['emoney_prc'])." ".$cfg['currency_type'].")
					</td>
				</tr>
			";
		} else {
			$script .= "<tr><th scope='row'>사용예치금복구</th><td>현 주문서의 잔여 예치금이 없습니다.</td></tr>";
		}

		if($ord['milage_prc'] > 0) {
			$milage_repay_default = ($all_cancel == true) ? $ord['milage_prc'] : 0;
			$script .= "
				<tr>
					<th scope='row'>사용적립금 복구</th>
					<td>
						적립금 <input type='text' name='milage_repay' class='input right autorepayprc' size='10' value='$milage_repay_default'> ".$cfg['currency_type']."
						(사용 적립금 ".parsePrice($ord['milage_prc'])." ".$cfg['currency_type'].")
					</td>
				</tr>
			";
		} else {
			$script .= "<tr><th scope='row'>사용적립금 환불</th><td>현 주문서의 잔여 적립금이 없습니다.</td></tr>";
		}

		// 무료배송 쿠폰 사용여부 확인
		if($all_cancel == true) {
			$ocpn = $pdo->assoc("select * from $tbl[coupon_download] where ono='$ono'");
			if($ord['prd_nums']) {
				$cpns = explode('@', trim($ord['prd_nums'], '@'));
				if(count($cpns) > 0) $ucw .= "  or no in (".implode(',', $cpns).")";
			}
			if($ocpn['no'] || $ucw) {
				$mcouponRes = $pdo->iterator("select stype from $tbl[coupon_download] where no='$ocpn[no]' $ucw");
                foreach ($mcouponRes as $cpn_tmp) {
					if($cpn_tmp['stype'] == 3) {
						$cpn_free_dlv = $pdo->row("select dlv_prc from $tbl[order_payment] where ono='$ono' and type=0");
						$ord['dlv_prc'] -= $cpn_free_dlv;

						$script .= "
						<tr>
							<th scope='row'></th>
							<td><input type='hidden' name='cpn_free_dlv' value='$cpn_free_dlv'></td>
						</tr>
						";
						break;
					}
				}
			}
		}
		if($order_dlv_prc > 0 && ($all_cancel == true || $all_cancel_partner == true)) {
			if(($stat == 13 || $stat == 15) && preg_match('/@(17|19)@/', $ord['stat2']) == false) {
				$script .= "
				<tr>
					<th scope='row'>전체배송비 취소</th>
					<td>
						주문서내의 모든 상품이 취소되므로 남은 배송비 <span class='desc3'><strong>".parsePrice($order_dlv_prc)."</strong></span> ".$cfg['currency_type']." 도 같이 취소/환불됩니다.
						<input type='hidden' name='repay_dlv_prc' class='autorepayprc' value='".($order_dlv_prc)."'>
					</td>
				</tr>
				";
			} else if($stat == 17) {
				if($cfg['use_partner_shop'] == 'Y' && $cfg['use_partner_delivery'] == 'Y') {
					$first_dlv_prc = $pdo->row("select first_prc from {$tbl['order_dlv_prc']} where ono='$ono' $ptn_sql");
				} else {
					$first_dlv_prc = $pdo->row("select dlv_prc from {$tbl['order_payment']} where ono='$ono' and type=0 order by no asc limit 1");
				}
				$script .= "
				<tr>
					<th scope='row'>사용완료 배송비</th>
					<td>
						<input type='hidden' name='repay_dlv_prc' class='autorepayprc' value='".($first_dlv_prc)."' />
						<label>
							<input type='checkbox' name='dlv_prc_no_return' value='Y' checked onclick='autoRepayPrc()' />
							이미 사용된 배송비 <span class='desc3'><strong>".parsePrice($first_dlv_prc, true)."</strong></span> ".$cfg['currency_type']."은 환불되지 않습니다.
						</label>
					</td>
				</tr>
				";
			}
		}

		$script .= "
			<tr>
				<th scope='row' rowspan='2'>총 {$title} 금액</th>
				<td>
					<strong class='repay_calc'>0</strong> ".$cfg['currency_type']."
					(
					상품취소금액 <span class='repay_prc1'></span>
					+ 기본배송비 <span class='repay_prc3'></span>
					<span class='repay_prc4_area' style='display:none;'>+ 개별 배송비 <span class='repay_prc4'></span></span>
					- 추가배송비 <span class='repay_prc2'></span>
					- 복구예치금 <span class='repay_prce'></span>
					- 복구적립금 <span class='repay_prcm'></span>
					)
				</td>
			</tr>
			<tr>
				<td>
					<span class='desc3'>실 {$title}금액</span> <input type='text' class='repay_calc input right' name='total_repay_prc' size='10'> ".$cfg['currency_type']."
				</td>
			</tr>
		";

		if($stat != 13) {
			$bank_codes['gsshop'] = 'GS SHOP';
			$bank_codes['ssgmall'] = '신세계몰';
			$bank_codes['storefarm'] = '스토어팜';
			$bank_codes['naverpay'] = '네이버페이';
			if (isTable($tbl['bank_customer'])) {
				foreach($bank_codes as $key=>$val) {
					$chk_no = $pdo->row("select no from $tbl[bank_customer] where code='$key'");
					if($chk_no) {
						$pdo->query("delete from $tbl[bank_customer] where no='$chk_no'");
					}
				}
				$res = $pdo->iterator("select * from $tbl[bank_customer] order by no");
				foreach ($res as $data) {
					$bank_codes[$data['code']] = $data['bank'];
				}
			}

            $add_repay_type = '';
			if($ord['member_no'] > 0 && $ord['pay_type'] != '25' && $ord['pay_type'] != '27') {
                if ($scfg->comp('emoney_use', 'Y')) { // 예치금 사용일 때만 예치금으로 환불 가능하도록
                    $add_repay_type .= "<label class='p_cursor'><input type='radio' name='pay_type' value='6' class='repay_pay_method' onclick='showRepayMethod()'> $_pay_type[6]</label>&nbsp;&nbsp;";
                }
                if ($scfg->comp('milage_use', '1')) { // 적립금 사용일 때만 적립금으로 환불 가능하도록
                    $add_repay_type .= "<label class='p_cursor'><input type='radio' name='pay_type' value='3' class='repay_pay_method' onclick='showRepayMethod()'> $_pay_type[3]</label>&nbsp;&nbsp;";
                }
			}

			$script .= "
				<tr class='repay_method' style='display:none;'>
					<th scope='row' rowspan='2'>환불방법</th>
					<td>
						<ul class='list_info'>
							<li>환불방법은 예치금, 적립금, 무통장 입금, 신용카드(간편결제), 가상계좌, 휴대폰 중 선택할 수 있습니다.</li>
							<li>신용카드(간편결제), 휴대폰 환불 시 카드 정보 내 PG사 결제취소 버튼을 통해 환불을 진행해주시기 바랍니다.(연동 예정)</li>
							<li>휴대폰의 경우 부분환불을 지원하지 않습니다.</li>
							<li>무통장 입금 환불 시 직접 고객 은행정보로 환불을 진행해주셔야 합니다.</li>
						</ul>
						<div>
							$add_repay_type
							<label class='p_cursor p_color'><input type='radio' name='pay_type' value='2' class='repay_pay_method' onclick='showRepayMethod()'> $_pay_type[2]</label>&nbsp;&nbsp;
							<label class='p_cursor p_color4'><input type='radio' name='pay_type' value='1' class='repay_pay_method' onclick='showRepayMethod()'> $_pay_type[1](간편결제)</label>&nbsp;&nbsp;
							<label class='p_cursor p_color4'><input type='radio' name='pay_type' value='4' class='repay_pay_method' onclick='showRepayMethod()'> $_pay_type[4]</label>&nbsp;&nbsp;
							<label class='p_cursor p_color4'><input type='radio' name='pay_type' value='7' class='repay_pay_method' onclick='showRepayMethod()'> $_pay_type[7]</label>
						</div>
					</td>
				</tr>
				<tr>
					<td>
						<div class='repay_method bank' style='display:none;'>
							<span class='box_btn_s blue'><input type='button' value='입금은행 추가' onclick='add_banks(); return false;'></span>
							".selectArray($bank_codes, 'bank', 1, '::입금은행 선택::')."
							<input type='text' name='bank_account' class='input' size='20' placeholder='계좌번호'>
							<input type='text' name='bank_name' class='input' size='10' placeholder='예금주'>
						</div>
					</td>
				</tr>
			";
		}

		$cpn_no = $pdo->row("select no from $tbl[coupon_download] where ono='$ono' and stype!=5");
		if($cpn_no > 0) {
			$cpn = $pdo->assoc("select name, cno, sale_type from $tbl[coupon_download] where no='$cpn_no'");
			$cpn['name'] = stripslashes($cpn['name']);
			$script .= "
				<tr>
					<th scope='row'>전체상품 쿠폰 반환</th>
					<td>
						<label><input type='checkbox' name='cpn_no' value='$cpn_no' class='autorepayprc'> $cpn[name]</label>
						<a href='#' onclick='cpndetail.open(\"no=$cpn[cno]?>&readOnly=true\"); return false;' class='info_square'>상세정보</a>
						<div class='list_info tp'>
							<p>사용한 쿠폰을 고객에게 반환합니다. (취소금액 확인을 위해 주문서상에는 할인된 가격이 계속 표시됩니다.)</p>
						</div>
					</td>
				</tr>
			";
			if($all_cancel == false && $total_sale5 > 0 && $cpn['sale_type'] == 'm') {
				$script .= "
					<tr>
						<th scope='row'>전체상품 쿠폰 재분배</th>
						<td>
							<label><input type='checkbox' name='cpn_recalc' class='autorepayprc' value='$total_sale5' checked> 부분 취소/환불/반품에 따른 '$cfg[currency]'단위 전체상품 쿠폰의 할인금액 ".number_format($total_sale5)."$cfg[currency]을 재분배합니다.</label>
							<ul class='list_info tp'>
								<li>상품금액에 따라 비율 분배되며, 쿠폰 할인 제외대상 상품에는 분배 되지 않습니다.</li>
								<li class='warning'>쿠폰 할인금액 재분배에 따른 쿠폰 할인금액만큼 상품별 취소/환불/반품 금액이 증가됩니다.</li>
							</ul>
						</td>
					</tr>
				";
			}
		}

		if(count($repay_prdcpn_no) > 0) {
			$repay_prdcpn_no = implode(',', $repay_prdcpn_no);
			$tmp = '';
			$cpnres = $pdo->iterator("select no, cno, name from $tbl[coupon_download] where no in ($repay_prdcpn_no)");
            foreach ($cpnres as $cpndata) {
				$cpndata['name'] = stripslashes($cpndata['name']);
				$tmp .= "<li>
					<label><input type='checkbox' name='repay_prdcpn_no[]' value='$cpndata[no]'> $cpndata[name]</label>
					<a href='#' onclick='cpndetail.open(\"no=$cpndata[cno]?>&readOnly=true\"); return false;' class='info_square'>상세정보</a>
				</li>";
			}
			$script .= "
				<tr>
					<th scope='row'>$_cpn_stype[5] 반환</th>
					<td>
						<ul>$tmp</ul>
						<div class='list_info tp'>
							<p>사용한 쿠폰을 고객에게 반환합니다. (취소금액 확인을 위해 주문서상에는 할인된 가격이 계속 표시됩니다.)</p>
						</div>
					</td>
				</tr>
			";
			unset($tmp);
		}

		$reasons = '';
		$rres = $pdo->iterator("select reason from $tbl[claim_reasons] order by sort asc");
        foreach ($rres as $rdata) {
			$rdata['reason'] = stripslashes($rdata['reason']);
			$reasons .= "<option>$rdata[reason]</option>";
		}

		$script .= "
			<tr>
				<th scope='row'>{$title}사유</th>
				<td>
					<select name='reason'>
						<option value=''>:: 사유를 선택해주세요 ::</option>
						$reasons
					</select>
					<label><input type='checkbox' name='copytomemo' value='Y'> 입력한 상세 사유를 메모에도 등록</label>
					<p style='margin-top: 5px;'><textarea class='txta' name='comment' cols='80' rows='5'></textarea></p>
				</td>
			</tr>
		";

		if($prd['talkstore_ono'] && $stat == 15) {
		$script .= "
			<tr>
				<td colspan='2'>
					<ul class='list_msg'>
						<li>고객이 카카오톡에서 최종 취소승인을 해야 환불이 완료됩니다.</li>
						<li>별도로 승인을 하지 않는 경우 판매자 품절취소요청 +6일, 구매자 배송취소요청 +5일 경과시 자동취소됩니다.</li>
					</ul>

				</td>
			</tr>
		";
		}
	}

	if($prd['checkout_ono']) {
		if(in_array($stat, array(13, 24, 16, 26, 27, 28, 99, 100, 171, 191, 401))) {
			$checkoutprd = new CheckoutApi4();
			$nprd = $checkout->api('GetProductOrderInfoList', $prd['checkout_ono']);
			$npay_claim = $checkoutprd->getClaimInfo($nprd);

			if(in_array($stat, array(16, 17, 26))) { // 택배사 선택박스(공통)
				$nvcw = '';
				if($cfg['use_partner_delivery'] == 'Y') {
					$nvcw = " and partner_no='$admin[partner_no]'";
				}
				$res = $pdo->iterator("select * from $tbl[delivery_url] where 1 $nvcw order by sort asc");
                foreach ($res as $dlvdata) {
					$val = $checkout->getDlvCode($dlvdata['name']);
					if(!$val) continue;
					$dlv_providers .= "<option value='$val'>$dlvdata[name]</option>";
				}
			}

			$script .= "</table><br><table class='tbl_row'><col width='15%'><caption>네이버페이 추가정보</caption>";
			if($stat == 26) {
				$script .= "\
				<tr>\
					<th scope='row' style='width:auto;'>반품처리 택배사</th>\
					<td><select name='ckout1'>$dlv_providers</select></td>\
				</tr>\
				<tr>\
					<th scope='row' style='width:auto;'>반품시 송장번호</th>\
					<td><input type='text' name='ckout2' value='' class='input' size='15' /></td>\
				</tr>";
			}

			if($stat == 16) {
				$script .= "
					<tr>
						<th scope='row'><strong>반품사유</strong></th>
						<td>
							<select name='ckout3'>
								<option value='INTENT_CHANGED'>구매 의사 취소</option>
								<option value='COLOR_AND_SIZE'>색상 및 사이즈 변경</option>
								<option value='WRONG_ORDER'>다른 상품 잘못 주문</option>
								<option value='PRODUCT_UNSATISFIED'>서비스 및 상품 불만족</option>
								<option value='DELAYED_DELIVERY'>배송 지연</option>
								<option value='SOLD_OUT'>상품 품절</option>
								<option value='DROPPED_DELIVERY'>배송 누락</option>
								<option value='BROKEN'>상품 파손</option>
								<option value='INCORRECT_INFO'>상품 정보 상이</option>
								<option value='WRONG_DELIVERY'>오배송</option>
								<option value='WRONG_OPTION'>색상 등이 다른 상품을 잘못 배송</option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope='row'><strong>수거 방법</strong></th>
						<td>
							<select name='ckout4'>
								<option value='RETURN_INDIVIDUAL'>직접 반송</option>
							</select>
						</td>
					</tr>
					<tr class='collect_dlv'>
						<th scope='row'>택배사</th>
						<td><select name='ckout1'>$dlv_providers</select></td>\
					</tr>
					<tr class='collect_dlv'>
						<th scope='row'>송장번호</th>
						<td><input type='text' name='ckout2' class='input' size='20'></td>
					</tr>
				";
			}

			if($stat == 99) {
				$res = $pdo->query("select * from $tbl[delivery_url] where partner_no='$admin[partner_no]' order by sort asc");
                foreach ($res as $dlvdata) {
					$val = $checkout->getDlvCode($dlvdata['name']);
					if(!$val) continue;
					$dlv_providers .= "<option value='$val'>$dlvdata[name]</option>";
				}
				$script .= "\
				<tr>\
					<th scope='row' style='width:auto;'>택배사</th>\
					<td><select name='ckout2'>$dlv_providers</select></td>\
				</tr>\
				<tr>\
					<th scope='row' style='width:auto;'>송장번호</th>\
					<td><input type='text' name='ckout1' value='' class='input' size='15' /></td>\
				</tr>";
			}

			if($stat == 100) {
				$script .= "\
				<tr>\
					<th scope='row' style='width:auto;'>배송예정일</th>\
					<td><input type='text' name='ckout1' value='' class='input' size='15' /> 예:2012-02-21</td>\
				</tr>\
				<tr>\
					<th scope='row' style='width:auto;'>배송지연사유</th>\
					<td><input type='text' name='ckout2' value='' class='input' size='15' /></td>\
				</tr>";
			}

			if($stat == 13) {
				//CANCEL_REQUEST
				if($npay_claim['ClaimStatus'] == 'CANCEL_REQUESTED' || $npay_claim['ClaimStatus'] == 'CANCEL_REQUEST' || $npay_claim['ClaimStatus'] == 'CANCELING') {
					$cancel_reason = $npay_claim['ClaimReason'];
				} else {
					$cancel_reason = "
						<select name='ckout1'>
							<option value=''>:: 취소사유 ::</option>
							<option value='SOLD_OUT'>상품 품절</option>
							<option value='DELAYED_DELIVERY'>배송 지연</option>
							<option value='PRODUCT_UNSATISFIED'>서비스 및 상품 불만족</option>
							<option value='INTENT_CHANGED'>구매 의사 취소</option>
							<option value='COLOR_AND_SIZE'>색상 및 사이즈 변경</option>
							<option value='WRONG_ORDER'>다른 상품 잘못 주문</option>
							<option value='INCORRECT_INFO'>상품 정보 상이</option>
						</select>
					";
				}

				$script .= "
				<tr>
					<th scope='row' style='width:auto;'>취소사유</th>
					<td>$cancel_reason</td>
				</tr>";
			}

			if($stat == '24') {
				$script .= "\
				<tr>\
					<th scope='row' style='width:auto;'>기타비용청구액</th>\
					<td>\
						<input type='text' name='ckout1' value='0' class='input' size='15' />\
						<div class='desc1'>환불/반품에 의해 추가 입금을 받아야 할 경우 입력해 주세요.</div>\
					</td>\
				</tr>";
			}

			if($stat == '27') {
				$script .= "\
				<tr>\
					<th scope='row' style='width:auto;'>반품 거부 사유</th>\
					<td>\
						<input type='text' name='ckout1' value='' class='input input_full'>\
					</td>\
				</tr>";
			}

			if($stat == '171') {
				$tmp = $checkout->getWithHoldReturnReason();
				$withholdReturnReason = '';
				foreach($tmp as $key => $val) {
					$withholdReturnReason .= "<option value='$key'>$val</option>";
				}
				$script .= "
				<tr>
					<th scope='row' style='width:auto;'>반품 보류 사유</th>
					<td>
						<select name='ckout1'>$withholdReturnReason</select>
					</td>
				</tr>
				<tr>
					<th scope='row' style='width:auto;'>상세 사유</th>
					<td>
						<input type='text' name='ckout2' class='input input_full'>
					</td>
				</tr>
				<tr>
					<th scope='row' style='width:auto;'>기타 반품 비용</th>
					<td>
						<input type='text' name='ckout3' class='input' value='0' size='10'>
						<span class='explain'>없을 경우 입력하지 않아도 됩니다.</span>
					</td>
				</tr>
				";
			}

			if($stat == '28') {
				$script .= "\
				<tr>\
					<th scope='row' style='width:auto;'>교환 거부 사유</th>\
					<td>\
						<input type='text' name='ckout1' value='' class='input input_full'>\
					</td>\
				</tr>";
			}

			if($stat == '191') {
				$tmp = $checkout->getWithholdExchangeReason();
				$WithholdExchangeReason = '';
				foreach($tmp as $key => $val) {
					$WithholdExchangeReason .= "<option value='$key'>$val</option>";
				}
				$script .= "
				<tr>
					<th scope='row' style='width:auto;'>교환 보류 사유</th>
					<td>
						<select name='ckout1'>$WithholdExchangeReason</select>
					</td>
				</tr>
				<tr>
					<th scope='row' style='width:auto;'>상세 사유</th>
					<td>
						<input type='text' name='ckout2' class='input input_full'>
					</td>
				</tr>
				<tr>
					<th scope='row' style='width:auto;'>기타 교환 비용</th>
					<td>
						<input type='text' name='ckout3' class='input' value='0' size='10'>
						<span class='explain'>없을 경우 입력하지 않아도 됩니다.</span>
					</td>
				</tr>
				";
			}

			if($stat == '401') { // 발송지연
				$tmp = $checkout->getDelayedDispatchReason();
				$DelayedDispatchReason = '';
				foreach($tmp as $key => $val) {
					$DelayedDispatchReason .= "<option value='$key'>$val</option>";
				}
				$script .= "
				<tr>
					<th scope='row' style='width:auto;'>발송 기한</th>
					<td>
						<input type='text' name='ckout1' class='input datepicker' value='".date('Y-m-d', strtotime('+3 days'))."' size='10'> 일
						<span class='explain'>(최대 3개월 이내로 입력 가능합니다.)</span>
					</td>
				</tr>
				<tr>
					<th scope='row' style='width:auto;'>지연 사유</th>
					<td><select name='ckout3'>$DelayedDispatchReason</select></td>
				</tr>
				<tr>
					<th scope='row' style='width:auto;'>지연 상세 사유</th>
					<td>
						<input type='text' name='ckout2' value='' class='input input_full'>
					</td>
				</tr>
				";
			}
		}
	}

    // talkpay
    if ($ord['external_order'] == 'talkpay') {
        if (is_object($talkpay) == false) {
            $talkpay = new KakaoTalkPay($scfg);
        }

        if ($stat == '15') {
            $script .= "
            <tr>
                <th scope='row' style='width:auto;'>취소 사유<br>(카카오페이구매)</th>
                <td>
                    <select name='requestType'>
                        <option value='INTENT_CHANGED'>변심에 의한 상품 취소</option>
                        <option value='WRONG_ORDER'>다른 옵션이나 상품을 잘못 주문함</option>
                        <option value='DELAYED_DELIVERY'>배송지연</option>
                        <option value='BROKEN'>상품 파손 또는 불량</option>
                        <option value='WRONG_DELIVERY'>다른 상품 오배송 또는 구성품 누락</option>
                        <option value='INCORRECT_INFO'>상품정보와 다름</option>
                        <option value='SOLD_OUT'>품절로 인한 배송 불가</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td colspan='2' class='p_color2'>
                     상세 {$title}사유입력 시 카카오페이구매에도 동시에 전송됩니다.
                </td>
            </tr>
            ";
        }

        if($stat == '401') { // 발송지연
            $tmp = $talkpay->getDelayReasonType();
            $reasonType = '';
            foreach($tmp as $key => $val) {
                $reasonType .= "<option value='$key'>$val</option>";
            }
            $script .= "
            <tr>
                <th scope='row' style='width:auto;'>배송 예정일</th>
                <td>
                    <input type='text' name='estimatedDeliveryDate' class='input datepicker' value='".date('Y-m-d', strtotime('+3 days'))."' size='10'> 일
                    <span class=\"explain\">(결제 완료일로부터 1개월 이내로 입력 가능합니다.)</span>
                </td>
            </tr>
            <tr>
                <th scope='row' style='width:auto;'>배송지연 사유</th>
                <td><select name='reasonType'>$reasonType</select></td>
            </tr>
            <tr>
                <th scope='row' style='width:auto;'>배송지연 메시지</th>
                <td>
                    <input type='text' name='message' value='' class='input input_full'>
                </td>
            </tr>
            ";
        }

        if ($stat == '132' || $stat == '26') {
            $dlv_url = $talkpay->getLogisticsSelect($prd['partner_no']);

            $script .= "
            <tr>
                <th>배송 방법</th>
                <td>
                    <select name='deliveryMethod'>
                        <option value='LOGISTICS'>택배</option>
                        <option value='QUICK'>퀵서비스</option>
                        <option value='DIRECT'>직접전달</option>
                        <option value='VISIT'>방문수령</option>
                        <option value='NONE'>배송없음</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th>택배사</th>
                <td>
                    $dlv_url
                </td>
            </tr>
            <tr>
                <th>송장번호</th>
                <td><input type='text' name='dlv_code' class='input' size='20' onkeyup='if(event.keyCode == 13) jsPrdStatSet(this.form); return false;'></td>
            </tr>
            ";
        }

        if ($stat == '16' || $stat == '18') {
            $dlv_url = $talkpay->getLogisticsSelect($prd['partner_no']);

            $script .= "
            <tr>
                <th>수거 방법</th>
                <td>
                    <select name='collectMethodType'>
                        <option value='DELIVERY_ALREADY'>반송정보 직접입력</option>
                        <option value='DELIVERY_BY_CHECKOUT'>자동 수거 요청</option>
                        <option value='DELIVERY_INDIVIDUAL'>반송정보 미입력</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th>반품 요청 사유</th>
                <td>
                    <select name='requestType'>
                        <option value='DELAYED_DELIVERY'>배송지연</option>
                        <option value='BROKEN'>상품 파손 또는 불량</option>
                        <option value='WRONG_DELIVERY'>다른 상품 오배송 또는 구성품 누락</option>
                        <option value='INCORRECT_INFO'>상품정보와 다름</option>
                        <option value='SOLD_OUT'>품절로 인한 배송 불가</option>
                    </select>
                    <ul class='list_info'>
                        <li>귀책사유는 판매자 귀책으로 제한되며 반품 배송비 지불방식은 환불금액에서 차감됩니다.</li>
                    </ul>
                </td>
            </tr>
            <tr>
                <th>상세 사유</th>
                <td>
                    <input type='text' name='requestReason' class='input input_full'>
                </td>
            </tr>
            <tr>
                <th>택배사</th>
                <td>
                    $dlv_url
                </td>
            </tr>
            <tr>
                <th>송장번호</th>
                <td><input type='text' name='dlv_code' class='input' size='20' onkeyup='if(event.keyCode == 13) jsPrdStatSet(this.form); return false;'></td>
            </tr>
            ";
        }

        if ($stat == '171' || $stat == '191') {
            $script .= "
            <tr>
                <th>보류 사유</th>
                <td>
                    <input type='text' name='holdbackReason' class='input input_full'>
                </td>
            </tr>
            <tr>
                <th>추가비용</th>
                <td>
                    <input type='text' name='extraFee' class='input' size='10'> 원
                    <ul class='list_info'>
                        <li>요청 사유가 구매자 귀책인 경우에 한해 설정 가능합니다.</li>
                    </ul>
                </td>
            </tr>
            ";
        }

        if ($stat == '27' || $stat == '28') {
            $script .= "
            <tr>
                <th>거부 사유</th>
                <td>
                    <input type='text' name='reason' class='input input_full'>
                </td>
            </tr>
            ";
        }
    }

	//스마트스토어
	if($prd['smartstore_ono']) {
		if(in_array($stat, array(13, 24, 16, 26, 27, 28, 99, 100, 171, 191, 401))) {
            $nprd = $CommerceAPI->ordersQuery($prd['smartstore_ono']);
            $claim_type = $nprd->productOrder->claimType;
            $claim_status = $nprd->productOrder->claimStatus;

			if(in_array($stat, array(16, 17, 26))) { // 택배사 선택박스(공통)
				$nvcw = '';
				if($cfg['use_partner_delivery'] == 'Y') {
					$nvcw = " and partner_no='$admin[partner_no]'";
				}
                $dlv_providers = '<option value="">:: 택배사 :: </option>';
				$res = $pdo->iterator("select no, name from $tbl[delivery_url] where 1 $nvcw order by sort asc");
                foreach ($res as $dlvdata) {
					$dlv_providers .= "<option value='{$dlvdata['no']}'>{$dlvdata[name]}</option>";
				}
			}

			$script .= "</table><br><table class='tbl_row'><col width='15%'><caption>스마트스토어 추가정보</caption>";
			if($stat == 26) {
				$script .= "\
				<tr>\
					<th scope='row' style='width:auto;'>반품처리 택배사</th>\
					<td><select name='reDeliveryCompany'>$dlv_providers</select></td>\
				</tr>\
				<tr>\
					<th scope='row' style='width:auto;'>반품시 송장번호</th>\
					<td><input type='text' name='reDeliveryTrackingNumber' value='' class='input' size='15' /></td>\
				</tr>";
			}

			if($stat == 16) {
				$script .= "
					<tr>
						<th scope='row'><strong>반품사유</strong></th>
						<td>
							<select name='returnReason'>
								<option value='INTENT_CHANGED'>구매 의사 취소</option>
								<option value='COLOR_AND_SIZE'>색상 및 사이즈 변경</option>
								<option value='WRONG_ORDER'>다른 상품 잘못 주문</option>
								<option value='PRODUCT_UNSATISFIED'>서비스 및 상품 불만족</option>
								<option value='DELAYED_DELIVERY'>배송 지연</option>
								<option value='SOLD_OUT'>상품 품절</option>
								<option value='DROPPED_DELIVERY'>배송 누락</option>
								<option value='BROKEN'>상품 파손</option>
								<option value='INCORRECT_INFO'>상품 정보 상이</option>
								<option value='WRONG_DELIVERY'>오배송</option>
								<option value='WRONG_OPTION'>색상 등이 다른 상품을 잘못 배송</option>
							</select>
						</td>
					</tr>
					<tr class='collect_dlv'>
						<th scope='row'>택배사</th>
						<td><select name='collectDeliveryCompany'>$dlv_providers</select></td>\
					</tr>
					<tr class='collect_dlv'>
						<th scope='row'>송장번호</th>
						<td><input type='text' name='collectTrackingNumber' class='input' size='20'></td>
					</tr>
				";
			}

			if($stat == 100) {
				$script .= "\
				<tr>\
					<th scope='row' style='width:auto;'>배송예정일</th>\
					<td><input type='text' name='ckout1' value='' class='input' size='15' /> 예:2012-02-21</td>\
				</tr>\
				<tr>\
					<th scope='row' style='width:auto;'>배송지연사유</th>\
					<td><input type='text' name='ckout2' value='' class='input' size='15' /></td>\
				</tr>";
			}

			if($stat == 13) {
				//CANCEL_REQUEST
				if($claim_status == 'CANCEL_REQUESTED' || $claim_status == 'CANCEL_REQUEST' || $claim_status == 'CANCELING') {
					$cancel_reason = '';
				} else {
					$cancel_reason = "
						<select name='cancelReason'>
							<option value=''>:: 취소사유 ::</option>
							<option value='SOLD_OUT'>상품 품절</option>
							<option value='DELAYED_DELIVERY'>배송 지연</option>
							<option value='PRODUCT_UNSATISFIED'>서비스 및 상품 불만족</option>
							<option value='INTENT_CHANGED'>구매 의사 취소</option>
							<option value='COLOR_AND_SIZE'>색상 및 사이즈 변경</option>
							<option value='WRONG_ORDER'>다른 상품 잘못 주문</option>
							<option value='INCORRECT_INFO'>상품 정보 상이</option>
						</select>
					";
				}

				$script .= "
				<tr>
					<th scope='row' style='width:auto;'>취소사유</th>
					<td>$cancel_reason</td>
				</tr>";
			}

			if($stat == '24') {
				$script .= "\
				<tr>\
					<th scope='row' style='width:auto;'>기타비용청구액</th>\
					<td>\
						<input type='text' name='ckout1' value='0' class='input' size='15' />\
						<div class='desc1'>환불/반품에 의해 추가 입금을 받아야 할 경우 입력해 주세요.</div>\
					</td>\
				</tr>";
			}

			if($stat == '27') {
				$script .= "\
				<tr>\
					<th scope='row' style='width:auto;'>반품 거부 사유</th>\
					<td>\
						<input type='text' name='rejectReturnReason' value='' class='input input_full'>\
					</td>\
				</tr>";
			}

			if($stat == '171') {
				$script .= "
				<tr>
					<th scope='row' style='width:auto;'><strong>반품 보류 사유</strong></th>
					<td>
						<select name='holdbackClassType'>
						    <option value='ETC'>기타 사유</option>
                            <option value='RETURN_DELIVERYFEE'>반품 배송비 청구</option>	
                            <option value='EXTRAFEEE'>추가 비용 청구</option>
                            <option value='RETURN_DELIVERYFEE_AND_EXTRAFEEE'>반품 배송비 + 추가 비용 청구</option>	
                            <option value='RETURN_PRODUCT_NOT_DELIVERED'>반품 상품 미입고</option>	
                            <option value='EXCHANGE_DELIVERYFEE'>교환 배송비 청구</option>
                            <option value='EXCHANGE_EXTRAFEE'>추가 교환 비용 청구</option>
                            <option value='EXCHANGE_PRODUCT_READY'>교환 상품 준비 중</option>
                            <option value='EXCHANGE_PRODUCT_NOT_DELIVERED'>교환 상품 미입고</option>	
                            <option value='EXCHANGE_HOLDBACK'>교환 구매 확정 보류</option>
                            <option value='SELLER_CONFIRM_NEED'>판매자 확인 필요</option>
                            <option value='PURCHASER_CONFIRM_NEED'>구매자 확인 필요</option>	
                            <option value='SELLER_REMIT'>판매자 직접 송금</option>	
                            <option value='ETC2'>기타</option>
                        </select>
					</td>
				</tr>
				<tr>
					<th scope='row' style='width:auto;'><strong>상세 사유</strong></th>
					<td>
						<input type='text' name='holdbackReturnDetailReason' class='input input_full'>
					</td>
				</tr>
				<tr>
					<th scope='row' style='width:auto;'>기타 반품 비용</th>
					<td>
						<input type='text' name='extraReturnFeeAmount' class='input' value='0' size='10'>
						<span class='explain'>없을 경우 입력하지 않아도 됩니다.</span>
					</td>
				</tr>
				";
			}

			if($stat == '28') {
				$script .= "\
				<tr>\
					<th scope='row' style='width:auto;'>교환 거부 사유</th>\
					<td>\
						<input type='text' name='rejectExchangeReason' value='' class='input input_full'>\
					</td>\
				</tr>";
			}

			if($stat == '191') {
				$script .= "
				<tr>
					<th scope='row' style='width:auto;'><strong>교환 보류 사유</strong></th>
					<td>
						<select name='holdbackClassType'>
                            <option value='RETURN_DELIVERYFEE'>반품 배송비 청구</option>	
                            <option value='EXTRAFEEE'>추가 비용 청구</option>	
                            <option value='RETURN_DELIVERYFEE_AND_EXTRAFEEE'>반품 배송비 + 추가 비용 청구</option>	
                            <option value='RETURN_PRODUCT_NOT_DELIVERED'>반품 상품 미입고</option>
                            <option value='ETC'>기타 사유</option>
                            <option value='EXCHANGE_DELIVERYFEE'>교환 배송비 청구</option>	
                            <option value='EXCHANGE_EXTRAFEE'>추가 교환 비용 청구</option>
                            <option value='EXCHANGE_PRODUCT_READY'>교환 상품 준비 중</option>
                            <option value='EXCHANGE_PRODUCT_NOT_DELIVERED'>교환 상품 미입고</option>
                            <option value='EXCHANGE_HOLDBACK'>교환 구매 확정 보류</option>
                            <option value='SELLER_CONFIRM_NEED'>판매자 확인 필요</option>
                            <option value='PURCHASER_CONFIRM_NEED'>구매자 확인 필요</option>
                            <option value='SELLER_REMIT'>판매자 직접 송금</option>
                            <option value='ETC2'>기타</option>
                        </select>
					</td>
				</tr>
				<tr>
					<th scope='row' style='width:auto;'><strong>상세 사유</strong></th>
					<td>
						<input type='text' name='holdbackExchangeDetailReason' class='input input_full'>
					</td>
				</tr>
				<tr>
					<th scope='row' style='width:auto;'>기타 교환 비용</th>
					<td>
						<input type='text' name='extraExchangeFeeAmount' class='input' value='0' size='10'>
						<span class='explain'>없을 경우 입력하지 않아도 됩니다.</span>
					</td>
				</tr>
				";
			}

			if($stat == '401') { // 발송지연
				$script .= "
				<tr>
					<th scope='row' style='width:auto;'>발송 기한</th>
					<td>
						<input 
                            type='text' 
                            name='dispatchDueDate' 
                            class='input datepicker' 
                            value='".date('Y-m-d', strtotime('+3 days'))."' size='10'
                        > 일
					</td>
				</tr>
				<tr>
					<th scope='row' style='width:auto;'>지연 사유</th>
					<td>
                        <select name='delayedDispatchReason'>
                            <option value='PRODUCT_PREPARE'>상품 준비 중</option>	
                            <option value='CUSTOMER_REQUEST'>고객 요청</option>
                            <option value='CUSTOM_BUILD'>주문 제작</option>
                            <option value='RESERVED_DISPATCH'>예약 발송</option>
                            <option value='OVERSEA_DELIVERY'>해외 배송</option>
                            <option value='ETC'>기타</option>
                        </select>
                    </td>
				</tr>
				<tr>
					<th scope='row' style='width:auto;'>지연 상세 사유</th>
					<td>
						<input type='text' name='dispatchDelayedDetailedReason' value='' class='input input_full'>
					</td>
				</tr>
				";
			}
		}
	}

    //카카오페이구매
	if($prd['talkstore_ono']) {
        if(is_object($kts) == false) {
            $kts = new KakaoTalkStore();
        }
		if ($stat == '401') {
			$delayCausationCode = selectArray(
				$kts->getDeliveryCausationCode(),
				'delayCausationCode',
				false
			);

			$script .= "
			<tr>
				<th scope='row' style='width:auto;'>발송예정일</th>
				<td>
					<input type='text' name='deliveryExpectedAt' class='input datepicker' value='".date('Y-m-d', strtotime('+3 days'))."' size='10'> 일 18시
					<ul class='list_msg'>
						<li>배송요청일로부터 30일 이내로만 설정할수 있습니다.</li>
						<li>설정한 배송예정일이 경과한 후에도 배송처리를 하지 않은 경우, 구매자 취소요청 즉시 취소/환불이 가능한 상태로 변경됩니다.</li>
					</ul>
				</td>
			</tr>
			<tr>
				<th scope='row' style='width:auto;'>지연 사유</th>
				<td>$delayCausationCode</td>
			</tr>
			<tr>
				<th scope='row' style='width:auto;'><strong>지연 상세 사유</strong></th>
				<td>
					<input type='text' name='delayCausation' value='' class='input input_full'>
				</td>
			</tr>
			<tr>
				<td colspan='2'><span class='explain'>배송지연 처리 후 주문상태가 <strong>'{$_order_stat[3]}'</strong>으로 변경됩니다.</span></td>
			</tr>
			";
		}
	}

	$script .= "
			<tr>
				<td colspan='2' align=center><span id='stpBtn' class='box_btn blue'><input type=button value='".$title."처리' onclick='jsPrdStatSet(this.form);' /></span>
				<span class='box_btn gray'><input type=button value='닫기' onclick='layTgl(repayDetail);' /></span></td>
			</tr>
		</table>
		</div>
	";

?>
<script type="text/javascript">
parent.prd_stat_refresh = 0;
obj=parent.document.getElementById("repayDetail");
parent.prdStat=<?=$stat?>;
if(obj) {
	obj.innerHTML="<?=php2java($script);?>";
	obj.style.display="block";

	parent.autoRepayPrc();
	parent.$('.autorepayprc').bind({
		'focus' : function() { parent.autoRepayPrc(); },
		'keyup' : function() { parent.autoRepayPrc(); },
		'change' : function() { parent.autoRepayPrc(); }
	});
	parent.$('.repay_calc').change(function() {
		parent.showRepayMethod();
	});
	parent.setDatepicker();

	var cpn_process_chk = parent.$('input[name=cpn_recalc], input[name=cpn_no]');
	cpn_process_chk.click(function() {
		cpn_process_chk.not(this).attrprop('checked', false);
	});
}
</script>