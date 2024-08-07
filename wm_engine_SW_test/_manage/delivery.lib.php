<?PHP

	use Wing\API\Naver\CheckoutApi4;
    use Wing\API\Naver\CommerceAPI;
    use Wing\API\Kakao\KakaoTalkPay;

	include_once $engine_dir."/_engine/sms/sms_module.php";
	include_once $engine_dir.'/_engine/include/wingPos.lib.php';

	if(getSmartStoreState() == true) {
        global $CommerceAPI;

        if (!isset($CommerceAPI)) {
            $CommerceAPI = new CommerceAPI();
        }
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  ext : 변경할 상태 (0/3/4)
	' |  ono : 변경할 주문번호
	' |  pno : 배송처리할 주문상품번호(콤마구분 or array)
	' |  dlv_no : 배송업체 (한글/코드0
	' |  dlv_code : 송장번호
	' |  dlv_date : 배송일자
	' |  w : 특정상품 제외 검색조건 (dlv_hold 등 처리)
	' +----------------------------------------------------------------------------------------------+*/
	function orderDelivery($ext, $ono, $pno, $dlv_no, $dlv_code, $dlv_date = null, $w = '') {
		global $cfg, $tbl, $engine_dir, $root_dir, $now, $checkout, $sms_case_admin, $sms_replace, $CommerceAPI, $pdo;

		// default values
		if(!$dlv_date) $dlv_date = $now;
		if(preg_match('/[^0-9]/', $dlv_no)) {
            $wqry = '';
            if ($cfg['use_partner_shop'] == 'Y') {
                $wqry = " and (partner_no='' or partner_no=0)";
            }
			$dlv_name = $dlv_no;
			$dlv_no = $pdo->row("select no from $tbl[delivery_url] where name='$dlv_no' $wqry");
		} else {
			$dlv_name = $pdo->row("select name from $tbl[delivery_url] where no='$dlv_no'");
		}
		if(is_array($pno)) $pno = implode(',', $pno);

		// check values
		if(!$dlv_no) return '택배사코드가 없거나 등록되지 않았습니다.';
		if(!$dlv_code) return '송장번호가 없습니다.';
		if(!$ono) return '주문번호가 없습니다.';
		if($pno && preg_match('/^[0-9][0-9,]*[0-9]?$/', $pno) == false) return '주문상품번호에 오류가 있습니다.';

		$ord = $pdo->assoc("select * from $tbl[order] where ono='$ono'");
		if(!$ord['no']) return '존재하지 않는 주문번호입니다.';

		$cnt = 0;
		$dlv_title = array();
		if($pno) $w .= " and no in ($pno)";
        if($ext) $w .= " and stat != ".$ext;
        $checkout_error_arr = array(); //네이버체크아웃 에러 데이터 (order_product.no를 취합)
		$res = $pdo->iterator("select * from $tbl[order_product] where ono='$ono' $w");
        foreach ($res as $data) {
			// wingpos
			orderStock($ono, $ord['stat'], $ext, $data['no']);

			// Naver checkout
			ob_start();
			if($data['checkout_ono']) {
				if(!isset($checkout) && !is_object($checkout)) $checkout = new CheckoutApi4();
				$checkout_result = $checkout->api('GetProductOrderInfoList', $data['checkout_ono']);
				if($checkout_result[0]->ProductOrderStatus[0] != 'DELIVERING') {
					if($ext == 3) $checkout->api('PlaceProductOrder', $data['checkout_ono']);
					else if($ext == 4) {
						$checkout_dlv_no = $checkout->getDlvCode($dlv_name);
						$checkout->delivery($data['checkout_ono'], $dlv_code, $checkout_dlv_no);
					}
				}
                if($checkout->error) $checkout_error_arr[] = $data['no']; //연동실패한 order_product.no 추가
			}

			// SmartStore
			if($data['smartstore_ono']) {
                $stat = $CommerceAPI->getCurrentStat($data['smartstore_ono']);
                if ($stat < 4) {
                    $ret = $CommerceAPI->ordersDispatch(
                        $data['smartstore_ono'],
                        $dlv_no,
                        $dlv_code
                    );
                    if ($CommerceAPI->getError()) {
                        $checkout_error_arr[] = $data['no'];
                    }
                }
			}

            // 카카오페이구매
            if ($ord['external_order'] == 'talkpay') {
                if (is_object($talkpay) == false) {
                    $talkpay = new KakaoTalkPay($GLOBALS['scfg']);
                }
                if ($ext == 3) {
                    $talkpay->confirm($data['external_id'], $dlv_no, $dlv_code);
                } else if ($ext == 4) {
                    $talkpay->delivery($data['external_id'], $dlv_no, $dlv_code);
                }
            }
			ob_end_clean();

			if($ext > 0) $p_asql = ", stat='$ext'";
			if( (!$ext || $ext != $data['stat'])
                && !in_array($data['no'], $checkout_error_arr)
            ) {
				$pdo->query("update $tbl[order_product] set dlv_no='$dlv_no', dlv_code='$dlv_code' $p_asql where stat in (2,3) and no='$data[no]'");
				if($pdo->lastRowCount() > 0) {
					$cnt++;
					$dlv_title[$ono][] = stripslashes($data['name']);
				}
			}
		}

        if(count($checkout_error_arr) > 0) return count($checkout_error_arr).'건의 주문상품이 변경되지 않았습니다. 재전송해주시기 바랍니다.';

		if($cnt < 1) return 'DUPLICATED';

		// inicis escrow
		escDlvRegist($ord, $dlv_no, $dlv_code);

		// update order
		$asql = "dlv_no='$dlv_no', dlv_code='$dlv_code'";
		if($ext == 4) $asql .= ",date4='$dlv_date'";
		if(!$ord['date3']) $asql .= ", date3='$dlv_date'";

		ordChgPart($ono);
		$result = $pdo->query("update `$tbl[order]` set $asql where `ono`='$ono'");
		if($result) {
			$data = $ord;
			ordStatLogw($ono, $ext, 'Y');

			// Naver milage
			if($ext == 4 && $cfg['milage_api_id'] && $cfg['milage_api_key'] && $ord['naver_milage_use'] == 'Y') {
				include_once $engine_dir.'/_engine/include/naverMilage.class.php';
				if(!isset($naverMilage)) $naverMilage=new naverMilage();
				$naverMilage->changeStatus($ono, $ext);
			}
		}

		// send email
		$dlv=getDlvUrl(array('dlv_no'=>$dlv_no, 'dlv_code'=>$dlv_code));
		$mail_case = 3;

		if($ext > 0 && $ord['mail_send'] == 'Y' && preg_match('/@'.$mail_case.'/', $cfg['email_checked'])) {
			$data = $ord;
			include $engine_dir."/_engine/include/mail.lib.php";
			sendMailContent($mail_case, $ord['buyer_name'], $data['buyer_email']);
		}

		// send sms
		if($ord['sms'] == 'Y') {
			$sms_replace['ono'] = $ord['ono'];
			$sms_replace['buyer_name'] = $ord['buyer_name'];
			$sms_replace['dlv_code'] = $dlv_code;
			$sms_replace['dlv_name']=$dlv_name;
			$sms_replace['title'] = (count($dlv_title[$ord['ono']]) > 1) ? $dlv_title[$ord['ono']][0].' 외 1건' : $ord['title'];
			$sms_replace['address'] = stripslashes($ord['addressee_addr1'].' '.$ord['addressee_addr2']);

			$dlv_url = $pdo->row("select url from $tbl[delivery_url] where no='$dlv_no'");
			$dlv_link = str_replace('{송장번호}', $dlv_code, $dlv_url);
			$sms_replace['dlv_link'] = $dlv_link;

			if($ext == 4) {
				if($pdo->row("select count(*) from $tbl[order_product] where ono='$ono' and stat between 1 and 3") > 0) { // part delivery
					SMS_send_case(14, $ord['buyer_cell']);
				} else {
					if(setSmsHistory($ono, 5)) {
						SMS_send_case(5, $ord['buyer_cell']);
					}
				}
			} else {
				if(setSmsHistory($ono, 4)) {
					SMS_send_case(4, $ord['buyer_cell']);
				}
			}
		}

		return 'OK';
	}
?>