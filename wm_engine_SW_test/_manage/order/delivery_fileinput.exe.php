<?PHP

	use Wing\API\Naver\CheckoutApi4;
	use Wing\API\Naver\CommerceAPI;
	use Wing\API\Kakao\KakaoTalkStore;
	use Wing\API\Kakao\KakaoTalkPay;

	$no_qcheck = true;
	set_time_limit(0);
	ini_set('memory_limit', '-1');

	include_once $engine_dir."/_engine/sms/sms_module.php";
	include_once $engine_dir."/_engine/include/shop.lib.php";
	include_once $engine_dir.'/_engine/include/wingPos.lib.php';
	include_once $engine_dir.'/_engine/include/classes/SpreadsheetExcelReader.class.php';

	$file_type = $_POST['file_type'];

	if ($file_type == "xls" && getExt($_FILES['excel']['name']) != "xls") {
		msg('xls 형식의 파일만 업로드 가능합니다.');
	}
	if ($file_type == "csv" && getExt($_FILES['excel']['name']) != "csv") {
		msg('csv 형식의 파일만 업로드 가능합니다.');
	}

	if ($file_type == "xls") {
		$excel = new Spreadsheet_Excel_Reader();
		$excel->setUTFEncoder('mb');
		$excel->setOutputEncoding(_BASE_CHARSET_);
		$excel->read($_FILES['excel']['tmp_name']);

		if ($excel->sheets[0]['numRows'] < 2) msg('처리 가능한 데이터가 없습니다.');
	} else {
	    $file = fopen($_FILES['excel']['tmp_name'], 'r');
	}

	$result_data = array();
	function makeCSVLog($ono, $data, $msg) {
		global $result_data, $total;

		$ono = mb_convert_encoding($ono, _BASE_CHARSET_, array('euc-kr', 'utf-8'));
		if($ono == '주문번호') {
			$total--;
			return;
		}

		javac("parent.$('.process').html('<strong>$ono</strong> 주문서 처리 중...');");
		ob_flush();
		flush();

		$data = array_map(function($str) {
			return mb_convert_encoding($str, _BASE_CHARSET_, array('euc-kr', 'utf-8'));
		}, $data);

		$result_data[$ono][] = array(
			'data' => $data,
			'msg' => $msg
		);
	}

	if($cfg['opmk_api']) {
		include $engine_dir.'/_engine/api/'.$cfg['opmk_api'].'/'.$cfg['opmk_api'].'.class.php';
		include $engine_dir.'/_engine/api/'.$cfg['opmk_api'].'/'.$cfg['opmk_api'].'Order.class.php';

		$apiname = $cfg['opmk_api'].'Order';
		$openmarket = new $apiname();
	}

	$stat = numberOnly($_POST['stat']);
	$code_ignore = ($_POST['code_ignore'] == 'Y') ? 'Y' : 'N';
	$ignore_hold = ($_POST['ignore_hold'] == 'Y') ? 'Y' : 'N';

	$ord_input_fd_selected=(!$cfg['ord_input_fd_selected']) ? "no,ono,dlv_no,dlv_code" : $cfg['ord_input_fd_selected'];
	$fd_arr=explode(",",$ord_input_fd_selected);
	$fd_num=array();
	$count = ($file_type == "csv") ? "0" : "1";
	for($ii=0; $ii<count($fd_arr); $ii++){
		$fd_num[$fd_arr[$ii]]=$ii+$count;
	}

	$w = '';
	if($ignore_hold != 'Y') { // 지연배송상품 처리
		$w .= " and dlv_hold != 'Y'";
		$stock_where .= " and dlv_hold != 'Y'";
	}
	if($cfg['use_partner_delivery'] == 'Y' && $stat == 4) { // 업체별 배송 사용시
		if(!$admin['partner_no']) {
			$w .= " and (partner_no='0' or dlv_type=1)";
		} else {
			$w .= " and partner_no='{$admin['partner_no']}'";
		}
	}

	ob_end_clean();
	$datas = array();
	$total = $success = 0;
	$dlv_title = array();
	$sms_send = $cash_receipt = array();
	if ($file_type == "xls") {
		for($i = 1; $i <= $excel->sheets[0]['numRows']; $i++) {
			$datas[] = $excel->sheets[0]['cells'][$i];
		}
	} elseif ($file_type == "csv") {
		while($tmp = fgetcsv ($file, 2048, ',')) {
			$datas[] = $tmp;
		}
	}
	foreach($datas as $t) {
		$total++;
		$wp = '';
		$_ono = trim($t[$fd_num['ono']]);
		$_dlv_code = trim($t[$fd_num['dlv_code']]);
		$_dlv_name = trim($t[$fd_num['dlv_no']]);
		$_dlv_name = mb_convert_encoding($_dlv_name, _BASE_CHARSET_, array('euc-kr', 'utf8'));

		if(preg_match('/[a-z0-9-]/i', $_ono) == false) {
			makeCSVLog($_ono, $t, '잘못된 주문번호');
			continue;
		}

		$_opno = null;

		if(!$_ono) {
			makeCSVLog($_ono, $t, '주문번호 미입력');
			continue;
		}
		if(!$_dlv_name) {
			makeCSVLog($_ono, $t, '택배사명 미입력');
			continue;
		}
		if(!trim(str_replace('-', '', $_dlv_code))) {
			makeCSVLog($_ono, $t, '송장번호 미입력');
			continue;
		}

		if($fd_num['opno'] > 0) {
			$_opno = $t[$fd_num['opno']];
			if(!$_opno) {
				makeCSVLog($_ono, $t, '주문상품번호 미입력');
				continue;
			}
			$wp .= " and no='$_opno'";
		}
        if($code_ignore != 'Y') {
            $wp .= " and (dlv_code='' or dlv_code is null)";
        }

		$ord = $pdo->assoc("select * from `$tbl[order]` where `ono`='$_ono'");

		$where = ($admin['partner_no'] == 0) ? "partner_no in ('0', '')" : "partner_no='$admin[partner_no]'";
		if(!$ord['no']) {
			makeCSVLog($_ono, $t, '주문번호 없음/배송보류/이미 처리 된 주문');
			continue;
		}
		$dlv_no = $pdo->row("select `no` from `$tbl[delivery_url]` where `name`='$_dlv_name' and $where");
		if(!$dlv_no) {
			makeCSVLog($_ono, $t, '택배사명 매칭 오류');
			continue;
		}

		$asql = ''; // 체크아웃 처리된 주문을 제외
		if($ord['checkout'] == 'Y') {
			if($stat < $ord['stat']) { // 네이버페이상품은 이전상태로 변경불가
				$asql .= " and no != '$opData[no]'";
				makeCSVLog($_ono, $t, '네이버페이 주문 이전 상태로 변경 불가');
				continue;
			}

			if(!isset($checkout)) $checkout = new CheckoutApi4();

			$checkout_process=0;
			$res = $pdo->iterator("select * from $tbl[order_product] where ono='$_ono' and checkout_ono!='' and stat < 4 $w $wp");
            foreach ($res as $opData) {
				$checkout_result=$checkout->api('GetProductOrderInfoList', $opData['checkout_ono']);
				$checkout_stat=$checkout->getStat($checkout_result[0]);

				if($stat == $checkout_stat) {
					makeCSVLog($_ono, $t, '변경전/후의 주문 상태 동일');
					continue;
				}

				if($stat == 4) {
					$checkout->delivery($opData['checkout_ono'], $_dlv_code, $checkout->getDlvCode($_dlv_name));
				}
				else $checkout->api('PlaceProductOrder', $opData['checkout_ono']);

				if(!$checkout->error) $checkout_process++;
				else $asql .= " and no != '$opData[no]'";

				$dlv_title[$_ono][] = strip_tags(stripslashes($data['opData']));
			}
            if ($res->rowCount() == 0) {
				makeCSVLog($_ono, $t, '기처리/배송보류/타입점사 주문');
				continue;
            }
			if(empty($checkout_process)) {
				makeCSVLog($_ono, $t, '[네이버페이 오류] '.$checkout->error);
				continue;
			}
		} else if($ord['smartstore'] == 'Y') {
			if($stat < $ord['stat']) {
				makeCSVLog($_ono, $t, '스마트스토어 주문 이전 상태로 변경 불가');
				continue;
			}

            if (!isset($CommerceAPI)) {
                $CommerceAPI = new CommerceAPI();
            }

			$smartstore_process=0;
			$res = $pdo->iterator("select * from $tbl[order_product] where ono='$_ono' and smartstore_ono!='' and stat < 4 $w $wp");
            foreach ($res as $opData) {
                $smartstore_stat = $CommerceAPI->getCurrentStat($opData['smartstore_ono']);

				if($stat == $smartstore_stat) {
					makeCSVLog($_ono, $t, '스마트스토어 주문상태 동일');
					continue;
				}

				if($stat == 4) {
                    $ret = $CommerceAPI->ordersDispatch(
                        $opData['smartstore_ono'],
                        $dlv_no,
                        $_dlv_code
                    );
				}
				else {
                    $ret = $CommerceAPI->ordersConfirm($opData['smartstore_ono']);
                }
                if (!$ret->data || !count($ret->data->successProductOrderIds)) {
                    $asql .= " and no != '$opData[no]'";
                } else {
                    $smartstore_process++;
                }

				$dlv_title[$_ono][] = strip_tags(stripslashes($opData['name']));
			}
            if ($res->rowCount() == 0) {
				makeCSVLog($_ono, $t, '기처리/배송보류/타입점사 주문');
				continue;
            }
			if(empty($smartstore_process)) {
				makeCSVLog($_ono, $t, '[스마트스토어 오류] '.$checkout->error);
				continue;
			}
		} else  if($ord['talkstore'] == 'Y') {
			if($stat < $ord['stat']) {
				$asql .= " and no != '$opData[no]'";
				makeCSVLog($_ono, $t, '카카오톡스토어 주문 이전 상태로 변경 불가');
				continue;
			}

			if(is_object($kts) == false) {
				$kts = new KakaoTalkStore();
			}

			$kts_process=0;
			$res = $pdo->iterator("select talkstore_ono, name from $tbl[order_product] where ono='$_ono' and talkstore_ono!='' and stat < 4 $w $wp");
            foreach ($res as $opData) {
				switch($stat) {
					case '3' :
						$kts->setShippingWait($opData['talkstore_ono']);
						break;
					case '4' :
						$kts->setShipping($_dlv_name, $_dlv_code, $opData['talkstore_ono']);
						break;
				}
				if($ret == 'OK') {
					$kts_process++;
				}

				$dlv_title[$_ono][] = strip_tags(stripslashes($opData['name']));
			}
            if ($res->rowCount() == 0) {
				makeCSVLog($_ono, $t, '기처리/배송보류/타입점사 주문');
				continue;
            }
			if($kts_process < 1) {
				makeCSVLog($_ono, $t, '[카카오톡스토어 오류] '.$ret);
				continue;
			}
        } else if ($ord['external_order'] == 'talkpay') {
            $taklbuy_process = 0;
			$talkpay_res = $pdo->iterator("select external_id, name from $tbl[order_product] where ono='$_ono' and stat < 4 $w $wp");
            foreach ($talkpay_res as $tData) {
                 if (is_object($talkpay) == false) {
                      $talkpay = new KakaoTalkPay($scfg);
                 }
                 if ($stat == '3') {
                     $ret = $talkpay->confirm($tData['external_id'], $dlv_no, $_dlv_code);
                 } else if ($stat == '4') {
                     $ret = $talkpay->delivery($tData['external_id'], $dlv_no, $_dlv_code);
                 }

                 if ($ret == 'OK') {
                     $taklbuy_process++;
                 }
            }
            if ($res->rowCount() == 0) {
				makeCSVLog($_ono, $t, '기처리/배송보류/타입점사 주문');
				continue;
            }
			if($taklbuy_process < 1) {
				makeCSVLog($_ono, $t, '[카카오페이구매 오류] '.$ret);
				continue;
			}
		} else {
			$res = $pdo->iterator("select name from $tbl[order_product] where ono='$_ono' and stat < 4 $w $wp");
            foreach ($res as $opData) {
				$dlv_title[$_ono][] = strip_tags(stripslashes($opData['name']));
			}
		}
        $pdo->ping();

		$r = $pdo->query("update `$tbl[order]` set `dlv_code`='$_dlv_code', `dlv_no`='$dlv_no' where `no`='$ord[no]'");

		if($r){
			escDlvRegist($ord, $dlv_no, $_dlv_code);

			if(is_object($openmarket)) {
				$_dlv_pno = $pdo->row("select group_concat(no) from $tbl[order_product] where ono='$_ono' and openmarket_ono!='' $asql and stat < 4 $w $wp");
				if($_dlv_pno && $openmarket->setDeilvery($_dlv_pno, $dlv_no, $_dlv_code) == false) {
					makeCSVLog($_ono, $t, '오픈마켓/ERP 연동 실패');
					continue;
				}
			}

	    	if($stat > 0) {
                 $opres = $pdo->iterator("select no, stat from {$tbl['order_product']} where ono='$_ono' $asql and stat < 4 $w $wp");
                 foreach ($opres as $prd) {
                     orderStock($_ono, $prd['stat'], $stat, $prd['no']);
                 }
             }

            $stat_q2 = '';
            if ($stat) $stat_q2 = ", `stat`=$stat";
			$r = $pdo->query("update $tbl[order_product] set dlv_code='$_dlv_code', dlv_no='$dlv_no' $stat_q2 where ono='$_ono' $asql and stat < 4 $w $wp");
			if($r && $pdo->lastRowCount() > 0) {
				$nstat = ordChgPart($_ono);
				if($nstat != $ord['stat']) ordStatLogw($_ono, $stat);
				$success++;

				if($ord['sms'] == 'Y') { // 주문 sms 발송
					$sms_send[$_ono] = array(
						'stat' => $stat,
						'dlv_no' => $dlv_no,
						'dlv_code' => $_dlv_code
					);
				}

				if($stat && $stat != $ord['stat']){ // 현금영수증 발급
					$cash_receipt[$_ono] = array(
						'stat' =>$stat,
						'ord' => $ord,
					);
				}

				makeCSVLog($_ono, $t, 'OK');
			} else {
				$sql_error = $pdo->getError();
				if($sql_error) {
					makeCSVLog($_ono, $t, $sql_error);
				} else {
					makeCSVLog($_ono, $t, '처리 불가 주문(이미 처리 됨/배송 보류/다른 입점사 주문)');
				}
			}
		}
	}

	foreach($cash_receipt as $ono => $data) {
		cashReceiptAuto($data['ord'], $data['stat']);
        $pdo->ping();
	}

	foreach($sms_send as $_ono => $data) {
		$stat = $data['stat'];
		$dlv_no = $data['dlv_no'];
		$dlv_code = $data['dlv_code'];
		$ord = $pdo->assoc("select stat, buyer_cell, pay_prc, buyer_name, title, addressee_addr1, addressee_addr2 from $tbl[order] where ono='$_ono'");

		if(($stat == 4 && $ord['stat'] != 4) || setSmsHistory($_ono, ($stat+1))) {
            unset($sms_replace);
			$sms_replace['ono'] = $_ono;
			$sms_replace['buyer_name'] = $ord['buyer_name'];
			$sms_replace['pay_prc'] = number_format($ord['pay_prc']);

			if($stat == 4) {
				$sms_case = ($ord['stat'] == 4) ? 5 : 14;
				if($dlv_code){
					$sms_replace['dlv_code'] = $dlv_code;
					$dlv_data = $pdo->assoc("select `name`, `url` from `$tbl[delivery_url]` where `no` = '$dlv_no'");
					$sms_replace['dlv_name'] = $dlv_data['name'];
					$dlv_link = str_replace('{송장번호}', $dlv_code, $dlv_data['url']);
					$sms_replace['dlv_link'] = $dlv_link;
					$sms_replace['title'] = (count($dlv_title[$_ono]) > 1) ? $dlv_title[$_ono][0].' 외 '.(count($dlv_title[$_ono])-1).'건' : $dlv_title[$_ono][0];
					$sms_replace['address'] = stripslashes($ord['addressee_addr1'].' '.$ord['addressee_addr2']);
					if($sms_case == 5) $sms_replace['title'] = strip_tags(stripslashes($ord['title']));

					SMS_send_case($sms_case,$ord['buyer_cell']);
				}
			} elseif($stat == 3) {
				SMS_send_case(4, $ord['buyer_cell']);
			}
		}
	}

	@unlink($_FILES['excel']['tmp_name']);

	$result = json_encode(array(
		'total' => $total,
		'success' => $success,
		'datas' => $result_data
	));

?>
<form id="resultFrm" method="post" action="?body=order@delivery_fileinput_result" target="_parent">
	<input type="hidden" name="result" value="<?=htmlspecialchars($result)?>">
	<input type="hidden" name="file_type" value="<?=$file_type?>">
</form>
<script type="text/javascript">
document.querySelector('#resultFrm').submit();
</script>