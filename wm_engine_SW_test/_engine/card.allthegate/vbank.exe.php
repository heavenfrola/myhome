<?PHP

	include_once $engine_dir.'/_engine/include/common.lib.php';

	foreach($_POST as $key => $val) {
		$log .= "[$key] $val\n";
	}
	$fp = fopen($root_dir.'/_data/order_log/ags_vbank_'.date('Ym').'.txt', 'a+');
	fwrite($fp, $log."\n\n\n\n\n");
	fclose($fp);

	$trcode = trim($_POST["trcode"]);
	$service_id = trim($_POST["service_id"]);
	$orderdt = trim($_POST["orderdt"]);
	$virno = trim($_POST["virno"]);
	$deal_won = trim($_POST["deal_won"]);
	$ono = trim($_POST["ordno"]);
	$inputnm = iconv(trim($_POST["inputnm"]), 'euc-kr', _BASE_CHARSET_);

	if($trcode == 1) {
		$ord = $pdo->assoc("select * from $tbl[order] where ono='$ono'");
		$card_tbl = $ord['pay_type'] == 4 ? $tbl['vbank'] : $tbl['card'];
		$card = $pdo->assoc("select * from `$card_tbl` where `wm_ono`='$ono'");

		if($card['wm_price'] != $deal_won) exit('입금액이 정확하지 않습니다.');
		if(!$card['no'] || $ord['stat'] != 1) exit('잘못된 주문 정보입니다(1)');
		if($card['buyr_name'] != $inputnm) exit('입금자명이 정확하지 않습니다.');

		$asql .= ", `account`='$rVirNo', `bankname`='', `ipgm_time`='$rAppTm', `op_cd`='$rApprNo'";

		$pdo->query("update `$card_tbl` set`stat`='2', `res_msg`='$trcode', `good_mny`='$deal_won', `buyr_name`='$inputnm' where `wm_ono`='$ono'");

		include_once $engine_dir.'/_engine/sms/sms_module.php';
		$sms_replace['buyer_name'] = $ord['buyer_name'];
		$sms_replace['ono'] = $ord['ono'];
		$sms_replace['pay_prc'] = number_format($ord['pay_prc']);
		SMS_send_case(3, $ord['buyer_cell']);
		SMS_send_case(18);

		if($cfg['partner_sms_config'] == 1 || $cfg['partner_sms_config'] == 2) {
			partnerSmsSend($ord['ono'], 18);
		}

		$pay_type = $ord['pay_type'];

		$tamount = $deal_won;
		$cflag = 'I';
		$dacom_note_url = true;
		include_once $engine_dir.'/_engine/order/order2.exe.php';

		return;
	}

?>