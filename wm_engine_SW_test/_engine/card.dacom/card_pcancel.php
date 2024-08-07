<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  U+ 결제 부분취소
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir.'/_engine/include/common.lib.php';

	if(!$admin['no']) exit('처리 권한이 없습니다.');

	$card = $pdo->assoc("select * from `$tbl[card]` where `no` = '$cno'");
	if(!$card['wm_ono']) msg('존재하지 않는 카드결제 코드입니다.');
	$ord = $pdo->assoc("select * from `$tbl[order]` where `ono`='$card[wm_ono]'");

	$card_dacom_id = ($ord['mobile'] == 'Y' || $ord['mobile'] == 'A')?$cfg['card_mobile_dacom_id']:$cfg['card_dacom_id'];
	$card_dacom_key = ($ord['mobile'] == 'Y' || $ord['mobile'] == 'A')?$cfg['card_mobile_dacom_key']:$cfg['card_dacom_key'];

	$ono = $card['wm_ono'];
	$hashdata = md5($card_dacom_id.$card['wm_ono'].$card_dacom_key);

	$manage_url = 'http://'.$_SERVER['HTTP_HOST'];

	switch($ord['pay_type']) {
		case '1' : $pay_method = "SC0010"; break; // 카드결제
		case "4" : $pay_method = "SC0040"; break; // 가상계좌 에스크로
		case "5" : $pay_method = "SC0030"; break; // 실시간 계좌이체
		case "7" : $pay_method = "SC0060"; break; // 휴대폰 결제
	}

	$args  = 'mid='.$card_dacom_id;
	$args .= '&oid='.$card['wm_ono'];
	$args .= '&tid='.$card['tno'];
	$args .= '&hashdata='.$hashdata;
	$args .= '&ret_url='.urlencode("$manage_url/main/exec.php?exec_file=card.dacom/card_pcancel.exe.php&mode=ret&urlfix=Y");
	$args .= '&note_url='.urlencode("$manage_url/main/exec.php?exec_file=card.dacom/card_pcancel.exe.php&admno={$admin['no']}&urlfix=Y");
	$args .= '&amount='.$card['good_mny'];
	$args .= '&rev_amount='.$price;
	$args .= '&paytype='.$pay_method;
	$args .= '&esc_good_code_list=';
	$args .= '&cancel_reason='.urlencode('admin cancel');

	$ret = comm('http://pg.dacom.net/common/partialCancel.jsp', $args);

	$dom = new DomDocument('1.0', 'UTF-8');
	@$dom->loadHTML("<meta charset='utf-8'>".mb_convert_encoding($ret, 'UTF-8', 'EUC-KR'));
	$xpath = new DomXpath($dom);

    if (is_object($xpath) == true) {
        $resp_code = $xpath->query("//input[@name='respcode']")->item(0)->getAttribute('value');
        $resp_msg = $xpath->query("//input[@name='respmsg']")->item(0)->getAttribute('value');
    }

    // 주문서 처리와 함께 카드 취소
    if (isset($duel_card_cancel) == true && $duel_card_cancel == true) {
        $card_cancel_result = ($ret == 'reload') ? 'success' : $ret;
        return;
    }

	if($resp_code == '0000') {
		msg('취소성공', 'reload', 'parent');
	} else {
		msg(php2java("거래취소실패 ($resp_msg)"));
	}

?>