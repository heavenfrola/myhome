<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  INIAPI 취소/부분취소
	' +----------------------------------------------------------------------------------------------+*/

	$apiKey = ($pdo->row("select pay_type from {$tbl['order']} where ono='{$card['wm_ono']}'") == '4') ?  $cfg['iniweb_escrow_apikey'] : $cfg['iniweb_basic_apikey'];
	switch($cfg['inicis_GID']) {
		case 'HOSTwisaG1' : $apiKey = 'dGXgJphqzegTL0VB'; break;
		case 'HOSTwisaG2' : $apiKey = 'BxT3k7VWQZUfC38K'; break;
		case 'HOSTwisaG3' : $apiKey = 'CHfsqkNAzisKvukV'; break;
	}

	if(empty($apiKey) == true) { // API키 없을 경우 key파일을 이용 취소(구버전)
        $_POST['mid'] = $cfg['card_web_id'];
        $_POST['tid'] = $card['tno'];
        $_POST['cno'] = $card['no'];
        $_POST['price'] = $price;

        require_once __ENGINE_DIR__.'/_engine/card.inicis/INIweb/card_cancel.exe.php';
        return;
    }

	$type = (empty($price) == true) ? 'Refund' : 'PartialRefund';
    if ((int) $price == (int) $card['wm_price']) { // 결제금액과 취소금액이 같고 첫 결제일 경우 무조건 전체 취소
        if ($pdo->row("select count(*) from {$tbl['card_cc_log']} where ono='{$card['wm_ono']}' and stat=2") == 0) {
            $type = 'Refund';
        }
    }
	$timestamp = date('YmdHis');

	$param = array(
		'type' => $type,
		'paymethod' => $card['use_pay_method'],
		'timestamp' => $timestamp,
		'clientIp' => $_SERVER['SERVER_ADDR'],
		'mid' => $cfg['card_web_id'],
		'tid' => $card['tno'],
		'msg' => 'admin cancel',
	);

	// 부분취소
	if($type == 'PartialRefund') {
		$param['price'] = $price;
		$param['confirmPrice'] = $card['wm_price']-$price;
		$param['hashData'] = hash('sha512', $apiKey.$type.$card['use_pay_method'].$timestamp.$_SERVER['SERVER_ADDR'].$cfg['card_web_id'].$card['tno'].$price.$param['confirmPrice']);
	} else {
		$param['hashData'] = hash('sha512', $apiKey.$type.$card['use_pay_method'].$timestamp.$_SERVER['SERVER_ADDR'].$cfg['card_web_id'].$card['tno']);
	}

	$r = comm('https://iniapi.inicis.com/api/v1/refund', http_build_query($param));
	$r = json_decode($r);

	if($r->resultCode == '00') {
		$stat = 2;
		if($type == 'Refund' || ($type == 'PartialRefund' && $param['confirmPrice'] == 0)) {
			$pdo->query("update {$tbl['card']} set stat='3' where no='{$card['no']}'");
		} else {
			$pdo->query("update {$tbl['card']} set wm_price='{$param['confirmPrice']}' where no='{$card['no']}'");
		}
	} else {
		$stat = 1;
	}
	$resultMsg = addslashes($r->resultMsg);

	$pdo->query("
		insert into {$tbl['card_cc_log']} (cno, stat, ono, tno, price, res_cd, res_msg, admin_id, admin_no, ip, reg_date)
		values ('{$card['no']}', '$stat', '{$card['wm_ono']}', '{$card['tno']}', '$price', '$r->resultCode', '$resultMsg', '{$admin['admin_id']}', '{$admin['no']}', '{$_SERVER['REMOTE_ADDR']}', '$now')
	");

    // 주문서 처리와 함께 카드 취소
    if (isset($duel_card_cancel) == true && $duel_card_cancel == true) {
        $card_cancel_result = ($r->resultCode === '00') ? 'success' : $r->resultMsg;
        return;
    }

	msg($r->resultMsg, "reload", "parent");

?>