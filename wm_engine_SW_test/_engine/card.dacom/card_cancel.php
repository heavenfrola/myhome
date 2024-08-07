<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  데이콤 신용카드 취소
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir.'/_engine/include/common.lib.php';

    $is_mobile = $pdo->row("select mobile from $tbl[order] where ono='$card[wm_ono]'");
	$_card_test = ($is_mobile != 'N') ? $cfg['card_mobile_test'] : $cfg['card_test'];
	$_card_test_code = ($_card_test != "N") ? ':7080' : '';

	$pg_id = ($is_mobile != 'N') ? $cfg['card_mobile_dacom_id'] : $cfg['card_dacom_id'];
    if ($_card_test == 'Y') {
        $pg_id = 't'.$pg_id;
    }
	$pg_key = ($is_mobile != 'N') ? $cfg['card_mobile_dacom_key'] : $cfg['card_dacom_key'];
	$hashdata = md5($pg_id.$card['wm_ono'].$pg_key);

	$args  = 'ret_url=reload';
    if (in_array($_SERVER['SERVER_ADDR'], array("121.254.156.134", "121.254.156.137", "121.254.159.17", "121.254.156.139"))) {
        //토스의 https 접근 불가 이슈로 예외처리
	    $args .= '&note_url='.urlencode(str_replace('https://', 'http://', $manage_url).'/main/exec.php?exec_file=card.dacom/card_cancel.exe.php&urlfix=Y');
    } else {
    	$args .= '&note_url='.urlencode($manage_url.'/main/exec.php?exec_file=card.dacom/card_cancel.exe.php&urlfix=Y');
    }
	$args .= '&hashdata='.$hashdata;
	$args .= '&tid='.$card['tno'];
	$args .= '&mid='.$pg_id;
	$args .= '&oid='.$card['wm_ono'];

	$ret = comm('http://pg.dacom.net'.$_card_test_code.'/common/cancel.jsp', $args);
	$ret = trim(strip_tags($ret));
	$ret = preg_replace("/[^']*'([^']+)'.*/s", "$1", $ret);
	$ret = mb_convert_encoding($ret, _BASE_CHARSET_, array('euckr', 'utf8'));

    // 주문서 처리와 함께 카드 취소
    if (isset($duel_card_cancel) == true && $duel_card_cancel == true) {
        $card_cancel_result = ($ret == 'reload') ? 'success' : $ret;
        return;
    }

	if($ret == 'reload') msg('거래 취소가 완료되었습니다', 'reload', 'parent');
	else {
        // 실패 로그
        $pdo->query("
            insert into {$tbl['card_cc_log']} 
                (cno, stat, ono, price, tno, res_cd, res_msg, admin_id, admin_no, ip, reg_date)
            values 
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ", array(
                $card['no'], '1', $card['wm_ono'], $price, $card['tno'], '', addslashes($ret), $admin['admin_id'], $admin['no'], $_SERVER['REMOTE_ADDR'], $now)
        );
		msg($ret, 'reload', 'parent');
	}

	exit;

?>