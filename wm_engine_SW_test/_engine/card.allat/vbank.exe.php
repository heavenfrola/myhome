<?PHP

	include $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir.'/_engine/include/wingPos.lib.php';

	$ono = $_POST['order_no'];
	$tno = $_POST['tx_seq_no'];
	$account_no = $_POST['account_no'];
	$bank_cd = $_POST['bank_cd'];
	$common_bank_cd = $_POST['common_bank_cd'];
	$apply_ymdhms = $_POST['apply_ymdhms'];
	$income_ymdhms = $_POST['income_ymdhms'];
	$apply_amt = $_POST['apply_amt'];
	$income_amt = $_POST['income_amt'];
	$income_account_nm = $_POST['income_account_nm'];
	$noti_currenttimemillis = $_POST['noti_currenttimemillis'];
	$hash_value = $_POST['hash_value'];

	$card = $pdo->assoc("select * from $tbl[vbank] where wm_ono='$ono'");
	if(!$card['no']) exit('9999 존재하지 않은 가상계좌 결제입니다.');
	$card['wm_price'] = parsePrice($card['wm_price']);

	$ord = $pdo->assoc("select * from $tbl[order] where ono='$ono'");
	if(!$ord['no']) exit('9999 존재하지 않는 주문번호입니다.');

	if($card['wm_price'] != $income_amt) exit('9999 결제금액이 일치하지 않습니다.');

	$allat_id = $ord['mobile'] == 'N' ? $cfg['card_partner_id'] : $cfg['mobile_card_partner_id'];
	$allat_cross_key = $ord['mobile'] == 'N' ? $cfg['card_cross_key'] : $cfg['mobile_card_cross_key'];
	$hash = md5($allat_id.$allat_cross_key.$ono.$noti_currenttimemillis);
	if($hash != $hash_value) exit('9999 정확하지 않은 HASH데이터 입니다.');

	if($ord['stat'] > 2) exit('0000');
	elseif($ord['stat'] == 1) {
		$pdo->query("update `$tbl[vbank]` set `stat`='2' where `wm_ono`='$ono'");

		orderStock($ono, $ord['stat'], 2);

		$pdo->query("update `$tbl[order_product]` set `stat`='2' where `ono`='$ono'");
		ordChgPart($ono);

		ordStatLogw($ono, 2, 'Y');
		makeOrderLog($ono);

		exit('0000');
	}

?>