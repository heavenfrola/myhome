<?PHP

	include_once $engine_dir.'/_engine/include/common.lib.php';
	require_once $engine_dir.'/_engine/card.inicis/INIweb/libs/INILib.php';

	$cno = numberOnly($_POST['cno']);
	$card = $pdo->assoc("select * from $tbl[card] where no='$cno'");
	$price = parsePrice($_POST['price']);
	if(!$price) $price = parsePrice($card['wm_price']);
	$confirm_price = parsePrice($card['wm_price'] - $price);

	$inipay = new INIpay50;

	$inipay->SetField('inipayhome', $root_dir.'/_data/INIpay41');
	$inipay->SetField('type', 'repay');
	$inipay->SetField('pgid', 'INIphpRPAY');
	$inipay->SetField('subpgip','203.238.3.10');
	$inipay->SetField('debug', false);
	$inipay->SetField('mid', $_POST['mid']);
	$inipay->SetField('admin', '1111');
	$inipay->SetField('oldtid', $card['tno']);
	$inipay->SetField('currency', 'WON');
	$inipay->SetField('price', $price);
	$inipay->SetField('confirm_price', $confirm_price);
	$inipay->SetField('buyeremail', $buyeremail);
	$inipay->SetField('tax', $tax);
	$inipay->SetField('taxfree', $taxfree);

	$inipay->startAction();

	$ResultCode = $inipay->GetResult('ResultCode');
    $resultMsg_charset = mb_detect_encoding($inipay->GetResult('ResultMsg'), array('EUC-KR','CP949','UTF-8'));
    $ResultMsg = iconv($resultMsg_charset, _BASE_CHARSET_, $inipay->GetResult('ResultMsg'));
	//$ResultMsg = $inipay->GetResult('ResultMsg');
	$PRTC_Remains = $inipay->GetResult('PRTC_Remains');
	$TID = $inipay->getResult('TID');

	if($ResultCode == '00'){
		$stat = 2;
		$msg =  '거래가 취소되었습니다.';

		$asql = '';
		if(!$price || $confirm_price == 0) {
			$asql .= ", stat=3";
		}
		if($price > 0 && $TID) {
			$asql .= ", env_info='$TID'";
		}

		$pdo->query("update $tbl[card] set stat='$stat', wm_price='$confirm_price' $asql where no='$card[no]'");
	}else{
		$stat = 1;
		$msg = "거래취소실패! ($ResultCode : $ResultMsg)";
	}

	$pdo->query("
		insert into $tbl[card_cc_log]
		(cno, stat, ono, tno, price, res_cd, res_msg, admin_id, admin_no, ip, reg_date)
		values
		('$card[no]', '$stat', '$card[wm_ono]', '$card[tno]', '$price', '$ResultCode', '$ResultMsg', '$admin[admin_id]', '$admin[no]', '$_SERVER[REMOTE_ADDR]', '$now')
	");

    // 주문서 처리와 함께 카드 취소
    if (isset($duel_card_cancel) == true && $duel_card_cancel == true) {
        $card_cancel_result = ($ResultCode === '00') ? 'success' : $ResultMsg;
        return;
    }

	msg($msg, "reload", "parent");

?>