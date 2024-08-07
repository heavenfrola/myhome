<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  결제 방식에 따라 필요한 PG 종류 선택
	' +----------------------------------------------------------------------------------------------+*/

	if($mobile_browser == 'mobile' && $_SESSION['browser_type'] != 'mobile' && $cfg['mobile_pg_use'] == 'Y') {
		$mobile_pg_use = 'Y';
	}
    if ($ord['browser_type'] == 'mobile') { // mypage 취소
		$mobile_pg_use = 'Y';
    }

	if($_SESSION['browser_type'] == 'mobile' || $mobile_pg_use == 'Y') {
		if($cfg['pg_mobile_version']) $pg_version = $cfg['pg_mobile_version']."/";
		$card_pg=$cfg['card_mobile_pg'];
	} else {
		if($cfg['card_pg'] != 'dacom' && $cfg['card_pg'] != 'inicis') $cfg['pg_version'] = '';
		if($cfg['pg_version']) $pg_version = $cfg['pg_version']."/";

		$card_pg=$cfg['card_pg'];
	}

	// 다날 휴대폰결제
	if($pay_type == 7 && $cfg['mobile_danal'] == 'Y') {
		$card_pg = "danal";
		$pg_version="";
	}
	// 알리페이 결제
	if($pay_type == 10 && $cfg['use_alipay'] == 'Y') {
		$card_pg = "alipay";
		$pg_version="";
	}
	// 카카오페이 결제
	if($pay_type == 12 && $cfg['use_kakaopay'] == 'Y') {
		$card_pg = "kakao";
		if($cfg['kakao_version'] == 'new') {
			$pg_version = $cfg['kakao_version']."/";
		}else {
			$pg_version = "";
		}
	}
	// Paypal 결제
	if($pay_type == 13 && $cfg['use_paypal'] == 'Y') {
		$card_pg = "paypal";
		$pg_version="";
	}
	// Cyrexpay 결제
	if($pay_type == 14 && $cfg['use_cyrexpay'] == 'Y') {
		$card_pg = "cyrexpay";
		$pg_version="";
	}
	// eContext 결제
	if($pay_type == 15 && $cfg['use_econtext'] == 'Y') {
		$card_pg = "eximbay";
		$pg_version="";
	}
	// Exim-paypal 결제
	if($pay_type == 16 && $cfg['use_paypal_c'] == 'Y') {
		$card_pg = "eximbay";
		$pg_version="";
	}
	// payco 결제
	if($pay_type == 17 && $cfg['use_payco'] == 'Y') {
		$card_pg = "payco";
		$pg_version="";
	}
	if($pay_type == 18 && $cfg['use_wechat'] == 'Y') {
		$card_pg = "eximbay";
		$pg_version="";
	}
	if($pay_type == 19 && $cfg['use_alipay_e'] == 'Y') {
		$card_pg = "eximbay";
		$pg_version="";
	}
	if($pay_type == 20 && $cfg['use_exim'] == 'Y') {
		$card_pg = "eximbay";
		$pg_version="";
	}
	if($pay_type == 21) { // Paynow
		$use_paynow = true;
	}
	// 토스계좌결제
	if($pay_type == 22 && ($cfg['use_tosspayment'] == 'Y' || $cfg['use_tosscard'] == 'Y')) {
		$card_pg = "tosspayment";
		$pg_version="";
	}

	// 네이버페이 간편결제
	if($pay_type == '25' && $scfg->comp('use_nsp', 'Y') == true) {
		$card_pg = 'naverSimplePay';
		$pg_version = '';
	}

	// 네이버페이 정기결제
	if($pay_type == '27' && $scfg->comp('use_nsp_sbscr', 'Y') == true) {
		$card_pg = 'naverSimplePay';
		$pg_version = '';
	}

    // 삼성페이 결제
    if($pay_type == '28' && $scfg->comp('use_samsungpay', 'Y') == true) {
        $card_pg = 'samsungpay';
        $pg_version = '';
    }

?>