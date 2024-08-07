<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' | INILite 결제 취소
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";


	// 라이브러리 인클루드 *
	require $engine_dir."/_engine/card.inicis/INILite/libs/INILiteLib.php";


	// INILite 클래스의 인스턴스 생성 *
	$inipay=new INILite;

	// 취소 정보 설정
	$inipay->m_inipayHome				= $engine_dir."/_engine/card.inicis/INILite";		//상점 수정 필요
	$inipay->m_inipayLogHome			= $root_dir."/_data/INILite";						//상점 수정 필요
	$inipay->m_key						= $cfg['card_inicis_key'];							//상점 수정 필요
	$inipay->m_ssl						= "false";											//ssl지원하면 true로 셋팅해 주세요.
	$inipay->m_type						= "cancel";											// 고정
	$inipay->m_log						= "true";											// true로 설정하면 로그가 생성됨(적극권장)
	$inipay->m_debug					= "true";											// 로그모드("true"로 설정하면 상세로그가 생성됨. 적극권장)
	$inipay->m_mid						= $mid;												// 상점아이디
	$inipay->m_tid						= $tid;												// 취소할 거래의 거래아이디
	$inipay->m_cancelMsg				= $msg;												// 취소사유

	$card=get_info($tbl[card], "no", $cno);

	$currency="won";
	if($_POST['price']) {
		$confirm_price					= $card['wm_price'] - $price;
		$inipay->m_type					= "repay";						// 부분취소 : repay
		$inipay->m_subPgIp				= "INIphpRPAY";					// 고정 (절대 수정 불가)
		$inipay->m_pgId					= $mid;							// 상점아이디
		$inipay->m_oldtid				= $tid;							//  원거래번호
		$inipay->m_price				= $price;						// 취소할금액
		$inipay->m_confirm_price		= $confirm_price;				// 승인금액 - 취소할금액
		$inipay->m_uip					= getenv("REMOTE_ADDR"); 		// 고정 (절대 수정 불가)
		$inipay->m_currency				= $currency;					// 화폐단위
		$inipay->m_encrypted			= $encrypted;					// 암호문
	} else {
		// 부분전액취소
		$sum_cancel_price=$pdo->row("select sum(`price`) from `{$tbl['card_cc_log']}` where `ono`='{$card['wm_ono']}' and `stat`=2");
		if($sum_cancel_price > 0) {
			$inipay->m_type				= "repay";						// 부분취소 : repay
			$inipay->m_subPgIp			= "INIphpRPAY";					// 고정 (절대 수정 불가)
			$inipay->m_pgId				= $mid;							// 상점아이디
			$inipay->m_oldtid			= $tid;							//  원거래번호
			$inipay->m_price			= $card['wm_price'];			// 취소할금액
			$inipay->m_confirm_price	= 0;							// 승인금액 - 취소할금액
			$inipay->m_uip				= getenv("REMOTE_ADDR"); 		// 고정 (절대 수정 불가)
			$inipay->m_currency			= $currency;					// 화폐단위
			$inipay->m_encrypted		= $encrypted;					// 암호문
		}
	}


	// 취소 요청
	$inipay->startAction();


	// 취소 결과
	$stat = 1;
	if($inipay->m_resultCode == "00") {
		if(!$price || $confirm_price == 0) $pdo->query("update `$tbl[card]` set `stat`='3' where `no`='$card[no]'");
		else $pdo->query("update `$tbl[card]` set `wm_price`='$confirm_price' where `no`='$card[no]'");

		$msg="거래취소성공!";
		$stat=2;

		if($price > 0 && $inipay->m_tid) $pdo->query("update $tbl[card] set env_info='$inipay->m_tid' where no='$card[no]'"); // 부분취소시 최종 TID 업데이트
	} else {
		$msg="거래취소실패! (".$inipay->m_resultCode." : ".addslashes($inipay->m_resultMsg).")";
	}

	$pdo->query("
		insert into `$tbl[card_cc_log]` (`cno`, `stat`, `ono`, `tno`, `price`, `res_cd`, `res_msg`, `admin_id`, `admin_no`, `ip`, `reg_date`)
		values ('$card[no]', '$stat', '$card[wm_ono]', '$card[tno]', '$price', '".$inipay->m_resultCode."', '".$inipay->m_resultMsg."', '$admin[admin_id]', '$admin[no]', '$_SERVER[REMOTE_ADDR]', '$now')
	");

	msg($msg, "reload", "parent");

?>