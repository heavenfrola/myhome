<?PHP

	require_once $engine_dir.'/_engine/include/common.lib.php';
	require_once $engine_dir.'/_engine/include/file.lib.php';

	require_once $engine_dir.'/_engine/card.nicepay/lib/nicepay/web/NicePayWEB.php';
	require_once $engine_dir.'/_engine/card.nicepay/lib/nicepay/core/Constants.php';
	require_once $engine_dir.'/_engine/card.nicepay/lib/nicepay/web/NicePayHttpServletRequestWrapper.php';

	$authResultCode          = $_REQUEST['AuthResultCode'];
	$authResultMsg           = $_REQUEST['AuthResultMsg'];

	makePGLog($ono, 'nicepay start', print_r($_REQUEST, true));

	if($authResultCode == '0000') {
		makeFullDir('_data/nicepay_log');

	    $nicepayWEB         = new NicePayWEB();
	    $httpRequestWrapper = new NicePayHttpServletRequestWrapper($_REQUEST);
	    $_REQUEST           = $httpRequestWrapper->getHttpRequestMap();
	    $payMethod          = $_REQUEST['PayMethod'];

		// Request
		$nicepayWEB->setParam('NICEPAY_LOG_HOME', $root_dir.'/_data/nicepay_log');
		$nicepayWEB->setParam('APP_LOG', '1');
		$nicepayWEB->setParam('EVENT_LOG','1');
		$nicepayWEB->setParam('DEBUG_MODE','0');
		$nicepayWEB->setParam('EncFlag', 'S');
		$nicepayWEB->setParam('SERVICE_MODE', 'PY0');
		$nicepayWEB->setParam('Currency', 'KRW');
		$nicepayWEB->setParam('PayMethod', $payMethod);
		$nicepayWEB->setParam('LicenseKey', $cfg['nicepay_licenseKey']);
		$nicepayWEB->setParam('CHARSET', 'UTF8');

		// Return
		$responseDTO    = $nicepayWEB->doService($_REQUEST);

		$resultCode     = $responseDTO->getParameter('ResultCode');     // 결과코드 (정상 결과코드:3001)
		$resultMsg      = addslashes($responseDTO->getParameterUTF('ResultMsg'));   // 결과메시지
		$authDate       = $responseDTO->getParameter('AuthDate');       // 승인일시 (YYMMDDHH24mmss)
		$authCode       = $responseDTO->getParameter('AuthCode');       // 승인번호
		$buyerName      = addslashes($responseDTO->getParameterUTF('BuyerName'));   // 구매자명
		$mallUserID     = $responseDTO->getParameter('MallUserID');     // 회원사고객ID
		$goodsName      = addslashes($responseDTO->getParameterUTF('GoodsName'));   // 상품명
		$mallUserID     = $responseDTO->getParameter('MallUserID');     // 회원사ID
		$mid            = $responseDTO->getParameter('MID');            // 상점ID
		$tid            = $responseDTO->getParameter('TID');            // 거래ID
		$moid           = $responseDTO->getParameter('Moid');           // 주문번호
		$amt            = $responseDTO->getParameter('Amt');            // 금액
		$cardQuota      = $responseDTO->getParameter('CardQuota');      // 카드 할부개월 (00:일시불,02:2개월)
		$cardCode       = $responseDTO->getParameter('CardCode');       // 결제카드사코드
		$cardName       = addslashes($responseDTO->getParameterUTF('CardName'));    // 결제카드사명
		$bankCode       = $responseDTO->getParameter('BankCode');       // 은행코드
		$bankName       = addslashes($responseDTO->getParameterUTF('BankName'));    // 은행명
		$rcptType       = $responseDTO->getParameter('RcptType');       // 현금 영수증 타입 (0:발행되지않음,1:소득공제,2:지출증빙)
		$rcptAuthCode   = $responseDTO->getParameter('RcptAuthCode');   // 현금영수증 승인번호
		$carrier        = $responseDTO->getParameter('Carrier');        // 이통사구분
		$dstAddr        = $responseDTO->getParameter('DstAddr');        // 휴대폰번호
		$vbankBankCode  = $responseDTO->getParameter('VbankBankCode');  // 가상계좌은행코드
		$vbankBankName  = addslashes($responseDTO->getParameterUTF('VbankBankName'));  // 가상계좌은행명
		$vbankNum       = $responseDTO->getParameter('VbankNum');       // 가상계좌번호
		$vbankExpDate   = $responseDTO->getParameter('VbankExpDate');   // 가상계좌입금예정일

	    $paySuccess = false;
		if($payMethod == "CARD"){
			if($resultCode == "3001") $paySuccess = true;               // 신용카드(정상 결과코드:3001)
		}else if($payMethod == "BANK"){
			if($resultCode == "4000") $paySuccess = true;               // 계좌이체(정상 결과코드:4000)
		}else if($payMethod == "CELLPHONE"){
			if($resultCode == "A000") $paySuccess = true;               // 휴대폰(정상 결과코드:A000)
		}else if($payMethod == "VBANK"){
			if($resultCode == "4100") $paySuccess = true;               // 가상계좌(정상 결과코드:4100)
		}else if($payMethod == "SSG_BANK"){
			if($resultCode == "0000") $paySuccess = true;               // SSG은행계좌(정상 결과코드:0000)
		}

		// DB 저장
		$ono = addslashes($_REQUEST['Moid']);
		$ord = $pdo->assoc("select pay_type from {$tbl['order']} where ono='$moid'");
		$card_tbl = ($ord['pay_type'] == 4) ? $tbl['vbank'] : $tbl['card'];

		//승인 된 이후 재전송 데이터가 넘어올 때 제어
		$card = $pdo->assoc("select * from $card_tbl where wm_ono='$ono'");
		if($card['stat'] == '2' || $card['stat'] == '3') {
			makePGLog($ono, 'nicepay return');
			exit("OK\n");
		}

		if($paySuccess == true) {
			if($ord['pay_type'] == 4) {
				$cq = ", bankname='$vbankBankName', account='$vbankNum', bank_code='$vbankBankCode'";
			} else if($ord['pay_type'] == 5) {
				$cq = ", card_cd='$bankCode', card_name='$bankName', app_time='$authDate', app_no='$authCode'";
			} else {
				$cq = ", card_cd='$cardCode', card_name='$cardName', app_time='$authDate', app_no='$authCode', quota='$cardQuota'";
			}

			$pdo->query("update $card_tbl set stat='2', res_cd='$resultCode', res_msg='$resultMsg', ordr_idxx='$moid', tno='$tid', good_mny='$amt', use_pay_method='$payMethod' $cq where `wm_ono`='$ono'");
			$card_pay_ok = true;

			makePGLog($ono, 'nicepay success', print_r($responseDTO, true));
			include_once $engine_dir."/_engine/order/order2.exe.php";
		} else {
			$ErrorMsg      = addslashes($responseDTO->getParameterUTF('ErrorMsg'));

			$pdo->query("update wm_order set stat=31 where ono='$moid'");
			$pdo->query("update wm_order_product set stat=31 where ono='$moid'");
			$pdo->query("update $card_tbl set stat='3', res_cd='$resultCode', res_msg='$resultMsg' where wm_ono='$ono'");

			makePGLog($ono, 'nicepay faild', print_r($responseDTO, true));

			javac("
				if(typeof parent.nicepayClose == 'function') {
					parent.nicepayClose('".php2java($ErrorMsg)."');
				} else {
					window.alert('".php2java($ErrorMsg)."');
					location.href = '/shop/order.php';
				}
			");
		}
		exit("OK\n");
	} else {
		$ono = addslashes($_REQUEST['Moid']);
		$ostat = $pdo->row("select `stat` from `wm_order` where `ono` = '$ono'");
		if($ostat != 11) {
			msg('', $root_url);
			exit;
		} else {
			$pdo->query("update wm_order set stat=31 where ono='$ono'");
			$pdo->query("update wm_order_product set stat=31 where ono='$ono'");
			$pdo->query("update $card_tbl set stat='3', res_cd='$resultCode', res_msg='$resultMsg' where wm_ono='$ono'");

			makePGLog($ono, 'nicepay faild 2', print_r($_REQUEST, true));

			javac("
				if(typeof parent.nicepayClose == 'function') {
					parent.nicepayClose('".php2java($_REQUEST['AuthResultMsg'])."');
				} else {
					window.alert('".php2java($_REQUEST['AuthResultMsg'])."');
					location.href = '/shop/order.php';
				}
			");
		}
	}

?>