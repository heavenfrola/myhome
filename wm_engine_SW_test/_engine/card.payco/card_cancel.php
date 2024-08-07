<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  페이코 결제 취소
	' +----------------------------------------------------------------------------------------------+*/

	// 취소할 금액
	$price = $_GET['price'];
	if($price == 0) { // 전체취소
		$price = $card['wm_price'];
	}

	$json = json_encode(array(
		'sellerKey' => $cfg['payco_sellerKey'],
		'orderNo' => $card['ordr_idxx'],
		'cancelTotalAmt' => parsePrice($price),
		'totalCancelPossibleAmt' => parsePrice($card['wm_price']),
		'orderCertifyKey' => $card['tno'],
		'requestMemo' => $admin['admin_id']
	));

	makePGLog($card['wm_ono'], 'payco cancel start', $json);

	// 취소 처리
	include $engine_dir.'/_engine/card.payco/lib/payco_config.php';
	include $engine_dir.'/_engine/card.payco/lib/payco_util.php';
	$ret = payco_cancel(urldecode(stripslashes($json)));
	$ret = json_decode($ret);

	// DB 처리
	$msg = addslashes($ret->message);
	$transaction = $ret->result->cancelTradeSeq;
	if($ret->code === 0 && $transaction) {
		$cancel_stat = 2;
		$remain_amt = $ret->result->remainCancelPossibleAmt;
		if($remain_amt == 0) {
			$pdo->query("update $tbl[card] set stat='3' where no='$card[no]'");
		}
		$pdo->query("update $tbl[card] set wm_price='$remain_amt' where no='$card[no]'");
	} else {
		$cancel_stat = 1;
	}

	$pdo->query("
		insert into $tbl[card_cc_log] (cno, stat, ono, tno, res_cd, res_msg, admin_id, admin_no, ip, reg_date)
		values ('$card[no]', '$cancel_stat', '$card[wm_ono]', '$transaction', '$ret->code', '$ret->message', '$admin[admin_id]', '$admin[no]', '$_SERVER[REMOTE_ADDR]', '$now')
	");

	makePGLog($card['wm_ono'], 'payco cancel finish', print_r($ret, true));

    // 주문서 처리와 함께 카드 취소
    if (isset($duel_card_cancel) == true && $duel_card_cancel == true) {
        $card_cancel_result = ($ret->code === 0) ? 'success' : $ret->message;
        return;
    }

	msg($msg, 'reload', 'parent');

?>