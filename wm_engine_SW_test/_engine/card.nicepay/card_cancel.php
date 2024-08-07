<?PHP

	if(!$admin['no'] && !$auto_cancel_stat) exit('permission denied');

	require_once $engine_dir.'/_engine/card.nicepay/lib/nicepay/web/NicePayWEB.php';
	require_once $engine_dir.'/_engine/card.nicepay/lib/nicepay/core/Constants.php';
	require_once $engine_dir.'/_engine/card.nicepay/lib/nicepay/web/NicePayHttpServletRequestWrapper.php';

	$ord = $pdo->assoc("select pay_type, mobile from {$tbl['order']} where ono='{$card['wm_ono']}'");
	$mid = ($ord['mobile'] == 'N') ? $cfg['nicepay_mid'] : $cfg['nicepay_m_mid'];
	$pwd = ($ord['mobile'] == 'N') ? $cfg['nicepay_pwd'] : $cfg['nicepay_m_pwd'];

	if($card['pg_version'] == 'autobill') {
		$mid = $cfg['card_auto_nicepay_mid'];
		$pwd = $cfg['card_auto_nicepay_pwd'];
	}

	if(empty($pwd) == true) {
		msg('거래 취소 비밀번호가 설정되어있지 않습니다.');
	}

    if ($price == $card['wm_price']) {
        $price = 0;
    }

    //기존 취소금액 계산(없을경우 전체취소처리)
    $sum_cancel_price = $pdo->row("select sum(price) from {$tbl['card_cc_log']} where ono='{$card['wm_ono']}' and stat=2");

	$_REQUEST = array(
		'MID' => $mid,
		'TID' => $card['tno'],
		'CancelAmt' => ($price > 0) ? $price : parsePrice($card['wm_price']),
		'CancelMsg' => '관리자 취소',
		'CancelPwd' => $pwd,
        'PartialCancelCode' => ($sum_cancel_price > 0 || $price > 0) ? '1' : '0'
	);

	$httpRequestWrapper = new NicePayHttpServletRequestWrapper($_REQUEST);
	$_REQUEST = $httpRequestWrapper->getHttpRequestMap();
	$nicepayWEB = new NicePayWEB();

	$nicepayWEB->setParam('NICEPAY_LOG_HOME', $root_dir.'/_data/nicepay_log');
	$nicepayWEB->setParam("APP_LOG","1");                           // 이벤트로그 모드 설정(0: DISABLE, 1: ENABLE)
	$nicepayWEB->setParam("EVENT_LOG","1");                         // 어플리케이션로그 모드 설정(0: DISABLE, 1: ENABLE)
	$nicepayWEB->setParam("EncFlag","S");                           // 암호화플래그 설정(N: 평문, S:암호화)
	$nicepayWEB->setParam("SERVICE_MODE", "CL0");                   // 서비스모드 설정(결제 서비스 : PY0 , 취소 서비스 : CL0)
	$nicepayWEB->setParam("CHARSET", "UTF8");                       // 인코딩

	// 취소 결과 필드
	$responseDTO = $nicepayWEB->doService($_REQUEST);
	$resultCode  = trim($responseDTO->getParameter("ResultCode"));  // 결과코드 (취소성공: 2001, 취소성공(LGU 계좌이체):2211)
	$resultMsg   = trim($responseDTO->getParameterUTF("ResultMsg"));// 결과메시지
	$cancelAmt   = $responseDTO->getParameter("CancelAmt");         // 취소금액
	$cancelDate  = $responseDTO->getParameter("CancelDate");        // 취소일
	$cancelTime  = $responseDTO->getParameter("CancelTime");        // 취소시간
	$cancelNum   = $responseDTO->getParameter("CancelNum");         // 취소번호
	$payMethod   = $responseDTO->getParameter("PayMethod");         // 취소 결제수단
	$mid         = $responseDTO->getParameter("MID");               // 상점 ID
	$tid         = $responseDTO->getParameter("TID");               // 거래아이디 TID

	$rev_amount	 = $card['wm_price']-$cancelAmt;

	$stat = 1;
	if($resultCode == '2001' || $resultCode == '2211') {
		$stat = 2;
		$cstat = ($rev_amount == 0 || $price == 0) ? 3 : 2;
		$pdo->query("update {$tbl['card']} set stat='$cstat', wm_price='$rev_amount' where no='$cno'");
	}

	$pdo->query("
		insert into {$tbl['card_cc_log']} (cno, stat, ono, price, tno, res_cd, res_msg, admin_id, admin_no, ip, reg_date)
		values ('$cno', '$stat', '{$card['wm_ono']}', '$cancelAmt', '{$card['tno']}', '$resultCode', '$resultMsg', '{$admin['admin_id']}', '{$admin['no']}', '{$_SERVER['REMOTE_ADDR']}', '$now')
	");

    // 주문서 처리와 함께 카드 취소
    if (isset($duel_card_cancel) == true && $duel_card_cancel == true) {
        $card_cancel_result = ($stat == 2) ? 'success' : $resultMsg;
        return;
    }

	msg(php2java($resultMsg), 'reload', 'parent');

?>