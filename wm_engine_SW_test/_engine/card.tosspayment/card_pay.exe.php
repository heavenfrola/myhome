<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  토스계좌결제 인증 완료후 결제 승인 및 완료 페이지 리다이렉트
	' +----------------------------------------------------------------------------------------------+*/
	include_once $engine_dir."/_engine/include/common.lib.php";

	if($cfg['use_tosscard'] == 'Y') {
		$cfg['tosspayment_api_key'] = $cfg['tossc_liveApiKey'];
	}

	// 인앱 결제여부
	$inAppYn = ($_SESSION['is_wisaapp'] == true) ? 'Y' : 'N';

	// 주문 채널
	if($_SESSION['browser_type'] == 'mobile' || $inAppYn == 'Y') {
		$orderChannel = 'MOBILE';
	} else {
		$orderChannel = 'PC';
	}

	//결제 승인 내역 확인
	$ono = @$_GET['orderNo'];
	if($ono == '' || @$_GET['status'] != 'PAY_APPROVED'){
		if($orderChannel == 'MOBILE'){
			msg('결제인증에 실패하였습니다.(1) 다시 시도해 주세요.', 'back');
		}else{
			javac("opener.parent.$('#order1').show()");
			msg('결제인증에 실패하였습니다.(1)', 'close');
		}
		exit;
	}
	// 이미 결제된건인지 확인
	$card_detail = $pdo->assoc("select * from `$tbl[card]` where `wm_ono`='$ono' and `stat` = 2 order by `no` desc limit 1");
	if($card_detail['no'] > 0){
		if($orderChannel == 'MOBILE'){
			msg('이미 완료된 결제입니다.', 'back');
		}else{
			javac("opener.parent.$('#order1').show()");
			msg('이미 완료된 결제입니다.', 'close');
		}
		exit;
	}

	//결제 승인내역 상세 조회
	function json_encode_han($arr) { // PHP 5.3 이하에서 (JSON_UNESCAPED_UNICODE 사용 불가)
		array_walk_recursive($arr, function (&$item, $key) { if (is_string($item)) $item = mb_encode_numericentity($item, array (0x80, 0xffff, 0, 0xffff), 'UTF-8'); });
		return mb_decode_numericentity(json_encode($arr), array (0x80, 0xffff, 0, 0xffff), 'UTF-8');
	}

	$json = json_encode_han(array(
		'orderNo'			=> $ono,
		'apiKey'			=> $cfg['tosspayment_api_key'],
	));
	$ch = curl_init('https://pay.toss.im/api/v1/status');
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/json',
		'Content-Length: ' . strlen($json))
	);
	$result = curl_exec($ch);
	curl_close($ch);
	$json = json_decode($result);

	$toss_amount = @$json->amount;
	$toss_status = @$json->payStatus;
	$toss_paytoken = @$json->payToken;
	$toss_product_desc = @$json->productDesc;


	// 주문 정보
	$ord = $pdo->assoc("select * from `$tbl[order]` where `ono`='$ono' and `stat` = 11 order by `no` desc limit 1");

	// 주문금액 일치 확인
	if($toss_amount != $ord['pay_prc']){
		if($orderChannel == 'MOBILE'){
			msg('결제인증에 실패하였습니다.(2)', 'back');
		}else{
			javac("opener.parent.$('#order1').show()");
			msg('결제인증에 실패하였습니다.(2)', 'close');
		}
		exit;
	}


	// 토스 결제 승인 전송
	$json = json_encode_han(array(
		'orderNo'			=> $ono,
		'payToken'			=> $toss_paytoken,
		'amount'			=> floor($ord['pay_prc']),
		'apiKey'			=> $cfg['tosspayment_api_key'],
	));
	$ch = curl_init('https://pay.toss.im/api/v1/execute');
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/json',
		'Content-Length: ' . strlen($json))
	);
	$result = curl_exec($ch);
	curl_close($ch);
	$json = json_decode($result);

	$resultCode = $json->code; 								// 결과코드 (정상 :0 , 그 외 에러)
	$resultMsg = $json->msg;						   		// 결과메시지(실패인경우)
	$authDate = $json->approvalTime;			   			// 승인일시 YYYY-MM--DD HH:mm:ss
	$paySuccess = false;									// 결제 성공 여부

	$app_time = preg_replace("/([-\s:])/",'',$authDate);
	if($resultMsg == ''){ $resultMsg = '결제성공'; }

	if($resultCode == "0") $paySuccess = true;				// 결과코드 (정상 :0 , 그 외 에러)

	if($paySuccess) {
	   // 결제 성공시 DB처리 하세요.
		$pdo->query("update `$tbl[card]` set `stat`='2' ,`app_time`='$app_time' ,`res_cd`='$resultCode' ,`res_msg`='$resultMsg' ,`ordr_idxx`='$ono' ,`tno`='$toss_paytoken' ,`good_mny`='$toss_amount' ,`use_pay_method`='TOSS'  where `wm_ono`='$ono'");

		makePGLog($ono, 'tosspayment success');

		$tosspayment_pay = 'Y';
		$card_pay_ok=true;
	}else{
	    // 결제 실패시 DB처리 하세요.
		$pdo->query("update `$tbl[card]` set `app_time`='$app_time' ,`res_cd`='$resultCode' ,`res_msg`='$resultMsg' ,`ordr_idxx`='$ono' ,`tno`='$toss_paytoken' ,`good_mny`='$toss_amount' ,`use_pay_method`='TOSS'  where `wm_ono`='$ono'");

		// 카드결제 실패 처리
		$pdo->query("update `$tbl[order]` set `stat`=31 where `ono`='$ono' and stat != 2");
		$pdo->query("update `$tbl[order_product]` set `stat`=31 where `ono`='$ono' and stat != 2");

		makePGLog($ono, 'tosspayment failed');

		if($orderChannel == 'MOBILE'){
			msg('결제가 실패하였습니다 : '.$resultCode.' ('.$resultMsg.')', 'back');
		}else{
			javac("opener.parent.$('#order1').show()");
			msg('결제가 실패하였습니다 : '.$resultCode.' ('.$resultMsg.')', 'close');
		}
		exit;
	}

	include_once $engine_dir."/_engine/order/order2.exe.php";
?>