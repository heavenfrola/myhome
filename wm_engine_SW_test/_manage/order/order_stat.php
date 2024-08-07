<?PHP

	use Wing\API\Naver\CheckoutApi4;
    use Wing\API\Naver\CommerceAPI;
	use Wing\API\Kakao\KakaoTalkPay;
	use Wing\API\Kakao\KakaoTalkStore;

    // 주문 로그 작성
    if ($data['ono']) {
        startOrderLog($data['ono'], 'order_stat.exe.php');
    }

	if($_POST['ext']) $ext = numberOnly($_POST['ext']);
	if($_POST['ext1']) $ext1 = numberOnly($_POST['ext1']);
	if($_POST['ext2']) $ext2 = numberOnly($_POST['ext2']);
	if($_POST['mode']) $mode = $_POST['mode'];
    $except_hold = (isset($_POST['except_hold']) == true) ? true : false; // 배송보류 주문 제외

	$oprd_qry = '';
	if((isset($from_receive) == false || $from_receive != 'Y') && $auto_finish != 'Y') {
		if($admin['level'] == 4) {
			$oprd_qry .= " and partner_no='$admin[partner_no]' and stat>1";
		}
		if($cfg['use_partner_delivery'] == 'Y' && $ext == '4') { // 업체별 배송 사용시
			if(!$admin['partner_no']) {
				$oprd_qry .= " and (partner_no='0' or dlv_type=1)";
			} else {
				$oprd_qry .= " and partner_no='{$admin['partner_no']}'";
			}
		}
	}

	if($ext1 > 0) {
		$oprd_qry .= " and stat='$ext1'";
	}
	if($data['repay_date'] > 0) {
        $oprd_qry .= " and `repay_date`=0";
    }

	if($data['checkout'] == 'Y') {
		if($ext == 5) {
			$ems = '네이버 페이 주문건은 배송완료로 변경 하실 수 없습니다.';
			$checkout_total = 1;
            if (function_exists('makeChanageResult') == true) makeChanageResult($data['ono'], 0, $ems);

			return;
		}
		if($ext < $data['stat']) {
			$ems = '네이버 페이 주문건은 이전상태로 복구 하실 수 없습니다.';
			$checkout_total=1;
            if (function_exists('makeChanageResult') == true)  makeChanageResult($data['ono'], 0, $ems);

			return;
		}
	}

	if($data['smartstore'] == 'Y') {
		if($ext == 5) {
			$ems = '스마트스토어 주문건은 배송완료로 변경 하실 수 없습니다.';
			$smartstore_total = 1;
            if (function_exists('makeChanageResult') == true)  makeChanageResult($data['ono'], 0, $ems);

			return;
		}
		if($ext < $data['stat']) {
			$ems = '스마트스토어 주문건은 이전상태로 복구 하실 수 없습니다.';
			$smartstore_total=1;
            if (function_exists('makeChanageResult') == true)  makeChanageResult($data['ono'], 0, $ems);

			return;
		}
	}

    // 바코드 안전 배송
	if($mode == 'erp_delivery') {
        if ($data['stat'] == 5) msg('이미 배송이 완료된 주문입니다');
        if ($data['stat'] > 10) msg('취소 상태의 주문입니다. 상태 변경 후 다시 시도해 주세요');

    	$oprd_qry .= " and dlv_code!=''";
    	$oprd_qry .= " and dlv_hold!='Y' and stat in (2,3)";
    }

	if($data['stat'] == $ext && !$ext2) {
		msg("변경 전후의 상태가 같습니다");
	}

	if($data['stat'] >= 13) {
		$_mstat=$pdo->row("select min(`stat`) from `$tbl[order_product]` where `ono`='$data[ono]'");
		if($_mstat >= 13 && $data[stat2] && $data[repay_date]) {
            if (function_exists('makeChanageResult') == true)  makeChanageResult($data['ono'], 0, '부분 취소/환불등의 처리가 된 경우에는 변경이 불가능합니다.');
            return;
        }
	}

	$asql="";

	$checkout_total = 0;
	$caql = '';

	// 네이버 페이 주문
	if($data['checkout'] == 'Y') {
		if(!isset($checkout)) $checkout = new CheckoutApi4();

		$checkout_process=0;
		$sql="select * from `$tbl[order_product]` where `ono`='$data[ono]' and `checkout_ono` <> '' and `stat` in (2,3) $oprd_qry";
		$res = $pdo->iterator($sql);
        foreach ($res as $opData) {
            if ($except_hold == true && $opData['dlv_hold'] == 'Y') {
                if (function_exists('makeChanageResult') == true)  makeChanageResult($data['ono'], $opData['no'], '배송보류 상태의 주문상품입니다.');
                continue;
            }
            if($ext == $opData['stat']) {
                if (function_exists('makeChanageResult') == true)  makeChanageResult($data['ono'], $opData['no'], '이미 변경 된 주문상품입니다.');
                continue;
            }

			$checkout_result=$checkout->api('GetProductOrderInfoList', $opData['checkout_ono']);
			$checkout_stat=$checkout->getStat($checkout_result[0]);

			if($ext <= $checkout_stat) continue;

			$checkout_dlv_no=$checkout->getDlvCode($pdo->row("select `name` from `$tbl[delivery_url]` where `no`='$opData[dlv_no]'"));
			if($ext == 4) {
				$checkout->delivery($opData['checkout_ono'], $opData['dlv_code'], $checkout_dlv_no);
			}
			else {
				$checkout->api('PlaceProductOrder', $opData['checkout_ono']);
			}

			if(!$checkout->error) {
                if (function_exists('makeChanageResult') == true)  makeChanageResult($data['ono'], $opData['no'], 'OK');
                $checkout_process++;
            } else {
                if (function_exists('makeChanageResult') == true)  makeChanageResult($data['ono'], $opData['no'], $checkout->error);
            }
		}

		if(empty($checkout_process)) {
			$checkout_total=1;
			if(empty($order_multi) && $ext == 4) {
                if (function_exists('makeChanageResult') == true)  makeChanageResult($data['ono'], 0, '송장번호 누락이거나 잘못된 송장번호입니다.');
            }
			return;
		}
	}

    // 카카오톡스토어 주문
    if($data['talkstore'] == 'Y' && ($ext == 3 || $ext == 4)) { // 카카오톡 스토어 배송처리
        if(is_object($kts) == false) {
            $kts = new KakaoTalkStore();
        }

		$res = $pdo->iterator("select * from {$tbl['order_product']} where ono='{$data['ono']}' and stat in (2,3) $oprd_qry");
        foreach ($res as $opData) {
            if ($ext != $opData['stat'] && $except_hold == true && $opData['dlv_hold'] == 'Y') {
                if (function_exists('makeChanageResult') == true)  makeChanageResult($data['ono'], $opData['no'], '배송보류 상태의 주문상품입니다.');
                continue;
            }
            if($ext == 3) {
                $ret = $kts->setShippingWait($opData['talkstore_ono']);
                if($ret != 'OK') {
                    $ret = json_decode($ret);
                    if (function_exists('makeChanageResult') == true) makeChanageResult($data['ono'], $opData['no'], $ret->extras->error_message);
                }
            }
            if($ext == 4) {
                $dlv_name = $pdo->row("select name from $tbl[delivery_url] where no='{$opData['dlv_no']}'");
                $ret = $kts->setShipping($dlv_name, $opData['dlv_code'], $data['talkstore_ono']);
                if($ret != 'OK') {
                    $ret = json_decode($ret);
                    if (function_exists('makeChanageResult') == true) makeChanageResult($data['ono'], $opData['no'], $ret->extras->error_message);
                }
            }
        }
    }

    if ($data['external_order'] == 'talkpay') {
        $talkpay = new KakaoTalkPay($scfg);

		$res = $pdo->iterator("select * from {$tbl['order_product']} where ono='{$data['ono']}' and stat in (2,3) $oprd_qry");
        foreach ($res as $opData) {
            if ($opData['stat'] == 2 && $ext == 3) {
                if ($except_hold == true && $opData['dlv_hold'] == 'Y') {
                    if (function_exists('makeChanageResult') == true)  makeChanageResult($data['ono'], $opData['no'], '배송보류 상태의 주문상품입니다.');
                    continue;
                }
                $ret = $talkpay->confirm($opData['external_id']);
                if ($ret != 'OK') {
                    if (function_exists('makeChanageResult') == true) makeChanageResult($data['ono'], $opData['no'], $ret);
                    $oprd_qry .= " and no!='{$opData['no']}'";
                }
            } else {
                if (function_exists('makeChanageResult') == true) makeChanageResult($data['ono'], $opData['no'], '카카오 페이구매 주문은 '.$_order_stat[3].'상태로만 변경가능합니다.');
                $oprd_qry .= " and no!='{$opData['no']}'";
                continue;
            }
        }
    }

	// 스마트스토어 주문
	if($data['smartstore'] == 'Y') {
		if(getSmartStoreState() == true) {
            if (!isset($CommerceAPI)) {
                $CommerceAPI = new CommerceAPI();
            }
		}

		$smartstore_process=0;
		$sql="select * from `$tbl[order_product]` where `ono`='$data[ono]' and `smartstore_ono` <> '' and `stat` in (2,3) $oprd_qry";
		$res = $pdo->iterator($sql);
		foreach ($res as $opData) {
            if($ext == $opData['stat']) {
                if (function_exists('makeChanageResult') == true)  makeChanageResult($data['ono'], $opData['no'], '이미 변경 된 주문상품입니다.');
                continue;
            }

            if ($except_hold == true && $opData['dlv_hold'] == 'Y') {
                if (function_exists('makeChanageResult') == true)  makeChanageResult($data['ono'], $opData['no'], '배송보류 상태의 주문상품입니다.');
                continue;
            }

            $smartstore_stat = $CommerceAPI->getCurrentStat($opData['smartstore_ono']);
			if($ext <= $smartstore_stat) continue;

			if($ext == 4) {
                $ret = $CommerceAPI->ordersDispatch(
                    $data['smartstore_ono'],
                    $opData['dlv_no'],
                    $opData['dlv_code']
                );
			}
			else {
                $CommerceAPI->ordersConfirm($opData['smartstore_ono']);
			}
            $smartstore_error = $CommerceAPI->getError();

			if(!$smartstore_error) {
                if (function_exists('makeChanageResult') == true)  makeChanageResult($data['ono'], $opData['no'], 'OK');
                $smartstore_process++;
            } else {
                if (function_exists('makeChanageResult') == true)  makeChanageResult($data['ono'], $opData['no'], $smartstore_error);
            }
		}

		if(empty($smartstore_process)) {
			$smartstore_total=1;
			if(empty($order_multi) && $ext == 4) {
                if (function_exists('makeChanageResult') == true)  makeChanageResult($data['ono'], 0, '송장번호 누락이거나 잘못된 송장번호입니다.');
            }
			return;
		}
	}

    // 재고 체크
	include_once $engine_dir.'/_engine/include/wingPos.lib.php';
	if($data['stat2']) {
		$xstat = explode('@', preg_replace('/^@|@$/', '', $data['stat2']));
		$xstat = min($xstat);
	} else {
		$xstat = $data['stat'];
	}

    $pdo->query('START TRANSACTION;');
	$_title = $pdo->row("select name from {$tbl['order_product']} where ono='{$data['ono']}' $oprd_qry order by no asc limit 1");
    $opRes = $pdo->iterator("select no, stat, name, dlv_hold from {$tbl['order_product']} where ono='{$data['ono']}' $oprd_qry");
    foreach ($opRes as $opData) {
        if($ext == $opData['stat']) {
            if (function_exists('makeChanageResult') == true)  makeChanageResult($data['ono'], $opData['no'], '이미 변경 된 주문상품입니다.');
            continue;
        }

        if ($except_hold == true && $opData['dlv_hold'] == 'Y') {
            if (function_exists('makeChanageResult') == true)  makeChanageResult($data['ono'], $opData['no'], '배송보류 상태의 주문상품입니다.');
            continue;
        }

        ob_start();
    	if(orderStock($data['ono'], $opData['stat'], $ext, $opData['no'])) {
            ob_end_clean();

            if (function_exists('makeChanageResult') == true)  makeChanageResult($data['ono'], $opData['no'], '재고가 부족합니다.');
            alert($data['ono']."\\n- ".$opData['name']."\\n재고가 부족합니다.");
            continue;
        }

        $pdo->query("update {$tbl['order_product']} set stat='$ext' where ono='{$data['ono']}' and no='{$opData['no']}'");

        if($pdo->lastRowCount() > 0) {
            if (function_exists('makeChanageResult') == true)  makeChanageResult($data['ono'], $opData['no'], 'OK');
        } else {
            if (function_exists('makeChanageResult') == true)  makeChanageResult($data['ono'], $opData['no'], '상태가 변경되지 않았습니다.');
        }
    }
    $pdo->query('COMMIT;');

	$_stat = ordChgPart($data['ono']);
    if ($_stat != $data['stat']) {
        // 발송 완료이거나 발송 완료 취소 시 지급 적립금 변경
        if($_stat == '5' || $data['stat'] == '5') {
            orderMilageChg();
        }
    }
	if($from_receive == "Y") $asql .= ", `receive`='Y', `receive_date`='$now'"; // 수취확인
	$pdo->query("update `$tbl[order]` set `stat`='$_stat' $asql where `ono`='$data[ono]'");
    $new_stat2 = $pdo->row("select stat2 from {$tbl['order']} where ono='{$data['ono']}'");
	if($data['stat2'] != $new_stat2) {
		ordStatLogw($data['ono'], $_stat);
        if(isset($oii) == false) $oii = 0;
        $oii++;
	}

	// 회원 누적구매액 재계산
	$finish_ono = ($ext == 5) ? $data['ono'] : null;
	setMemOrd($data[member_no],1, $finish_ono);

	// 현금영수증 자동 발급
	cashReceiptAuto($data, $ext);

	$ems=" 상태변경을 완료하였습니다";

	// 에스크로 배송등록
	escDlvRegist($data, $data['dlv_no'], $data['dlv_code']);

	// 각종 SMS
	if($data[sms]=="Y" && ($ext==2 || $ext==3 || $ext==4 || $ext==5) && !$from_receive) {
		if($ext == 4 && $pdo->row("select count(*) from {$tbl['order_product']} where ono='{$data['ono']}' and stat < 4") > 0) { // 부분발송
			$sms_case = 14;
		} else {
			$sms_case=$ext+1;
		}
		if($sms_case == 14 || setSmsHistory($data['ono'], $ext+1)) {
			include_once $engine_dir."/_engine/sms/sms_module.php";
			$sms_replace['ono'] = $data['ono'];
			$sms_replace['buyer_name'] = $data['buyer_name'];
			$sms_replace['pay_prc'] = number_format($data['pay_prc']);
            $sms_replace['title'] = stripslashes($data['title']);
            $sms_replace['address'] = stripslashes($data['addressee_addr1'].' '.$data['addressee_addr2']);
            if($sms_case == 14) {
                $sms_replace['title'] = $_title.' 외 '.($prd_cnt-1).'건';
            }
			if($ext == 4 || $ext == 14){ // 배송출고 송장번호 알림
				if($data['dlv_code']){
					$sms_replace['dlv_code'] = $data['dlv_code'];
					$dlv_data = $pdo->assoc("select `name`, `url` from {$tbl['delivery_url']} where `no` = '{$data['dlv_no']}'");
					$sms_replace['dlv_name'] = $dlv_data['name'];
					$dlv_link = str_replace('{송장번호}', $data['dlv_code'], $dlv_data['url']);
					$sms_replace['dlv_link'] = $dlv_link;

					if(SMS_send_case($sms_case,$data['buyer_cell'])) {
						$ems.="\\n\\n SMS전송을 완료하였습니다          \\n";
					}
				}
			}else{
				if(SMS_send_case($sms_case,$data['buyer_cell'])) {
					$ems.="\\n\\n SMS전송을 완료하였습니다          \\n";
				}
			}
		}
	}


	// 배송완료 메일
	if($ext == '5' && $data['mail_send'] == 'Y' && preg_match("/4/",$cfg['email_checked'])) {
		$ord=$data;
		$mail_case = 4;
		include $engine_dir.'/_engine/include/mail.lib.php';
		sendMailContent($mail_case, $member_name, $to_mail);
	}

	if($mode == 'erp_delivery') $ems = '';

?>