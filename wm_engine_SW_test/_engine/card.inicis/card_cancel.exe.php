<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  이니시스 결제 취소
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	require($engine_dir."/_engine/card.inicis/INIpay41Lib.php");

	// INIpay41 클래스의 인스턴스 생성 *
	$inipay = new INIpay41;


	// 취소 정보 설정 *
	$inipay->m_inipayHome = file_exists($root_dir.'/_data/INIpay41') ? $root_dir.'/_data/INIpay41' : $root_dir.'/INIpay41';
	$inipay->m_type = "cancel"; // 고정
	$inipay->m_subPgIp = "203.238.3.10"; // 고정
	$inipay->m_keyPw = $cfg['card_key_password']; // 키패스워드(상점아이디에 따라 변경)
	$inipay->m_debug = "true"; // 로그모드("true"로 설정하면 상세로그가 생성됨.)
	$inipay->m_mid = $_REQUEST['mid']; // 상점아이디
	$inipay->m_tid = $_REQUEST['tid']; // 취소할 거래의 거래아이디
	$inipay->m_cancelMsg = $msg; // 취소사유
	$inipay->m_uip = getenv("REMOTE_ADDR"); // 고정

	$mid = $_REQUEST['mid'];
	$tid =  $_REQUEST['tid'];
	$price = $_REQUEST['price'];

	$card = get_info($tbl[card], "no", $cno);

	if($_POST['price']) {
		$confirm_price				= $card['wm_price'] - $price;
		$inipay->m_type				= 'repay';
		$inipay->m_subPgIp			= 'INIpayRPAY';
		$inipay->m_pgId				= $mid;
		$inipay->m_oldTid			= $tid;
		$inipay->m_price			= $price;
		$inipay->m_confirm_price	= $confirm_price;

	} else {
		$sum_cancel_price=$pdo->row("select sum(price) from {$tbl['card_cc_log']} where ono='{$card['wm_ono']}' and stat=2");

		if($sum_cancel_price > 0) { #부분전액취소
			$inipay->m_type				= 'repay';
			$inipay->m_subPgIp			= 'INIpayRPAY';
			$inipay->m_pgId				= $mid;
			$inipay->m_oldTid			= $tid;
			$inipay->m_price			= $card['wm_price'];
			$inipay->m_confirm_price	= 0;

		}
	}

	// 취소요청
	$inipay->startAction();
	$inipay->m_resultMsg = mb_convert_encoding($inipay->m_resultMsg, _BASE_CHARSET_, 'euc-kr');

	$res_cd=trim($res_cd);
	$stat=1;
	if($inipay->m_resultCode == "00"){
		if(!$price || $confirm_price == 0) {
			$pdo->query("update {$tbl['card']} set stat='3' where no='{$card['no']}'");
		}
		else $pdo->query("update {$tbl['card']} set wm_price='$confirm_price' where no='{$card['no']}'");
		$msg="거래취소성공!";
		$stat=2;

		if($price > 0 && $inipay->m_tid) { // 부분취소시 tid 업데이트 by zardsama
			$pdo->query("update {$tbl['card']} set env_info='$inipay->m_tid' where no='{$card['no']}'");
		}
	}else{
		$msg="거래취소실패! (".$inipay->m_resultCode." : ".addslashes($inipay->m_resultMsg).")";
	}
	$pdo->query("
		insert into {$tbl['card_cc_log']} (cno, stat, ono, tno, price, res_cd, res_msg, admin_id, admin_no, ip, reg_date)
		values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
	", array(
        $card['no'],
        $stat,
        $card['wm_ono'],
        $card['tno'],
        $price,
        $inipay->m_resultCode,
        $inipay->m_resultMsg,
        $admin['admin_id'],
        $admin['no'],
        $_SERVER['REMOTE_ADDR'],
        $now
    ));



	msg($msg, "reload", "parent");

?>