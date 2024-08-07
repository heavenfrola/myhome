<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  이니시스 모바일 가상계좌 입금 확인
	' +----------------------------------------------------------------------------------------------+*/

	$urlfix = 'Y';
	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_manage/manage2.lib.php";

	$tno = addslashes(trim($_POST['P_TID']));
	$ono = addslashes(trim($_POST['P_OID']));
	$amt = numberOnly($_POST['P_AMT']);

    if ($_POST['P_TYPE'] == 'CARD') {
        include 'card_pay.exe.php';
        return;
    }

	if($_POST['P_STATUS'] != '02') exit('OK'); // 입금통보가 아닐 경우 종료

	makePGLog($ono, 'INIPay Vbank input Start');

	$ord = $pdo->assoc("select * from $tbl[order] where ono='$ono'");
	$card = $pdo->assoc("select * from $tbl[vbank] where wm_ono='$ono'");
	if(!$ord['ono']) exit('존재하지 않는 주문번호입니다.');
	if(!$card['wm_ono']) exit('승인된 가상계좌 데이터가 아닙니다.');
	if($card['wm_price'] != $amt) exit('입금액이 정확하지 않습니다.');
	if($card['tno'] != $tno) exit('승인데이터가 정확하지 않습니다.');
	if(in_array($ord['stat'], array(1, 11, 31)) == false) exit('이미 처리 된 데이터입니다.');

	$r = $pdo->query("update $tbl[order_product] set stat='2' where ono='$ono' and stat in (1, 11)");
	if($r) {
		ordChgPart($ono);
		ordStatLogw($ono, 2, 'Y');

		include_once $engine_dir.'/_engine/sms/sms_module.php';
		$sms_replace['buyer_name'] = $ord['buyer_name'];
		$sms_replace['ono'] = $ord['ono'];
		$sms_replace['pay_prc'] = number_format($ord['pay_prc']);
		SMS_send_case(3, $ord['buyer_cell']);
		SMS_send_case(18);

		if($cfg['partner_sms_config'] == 1 || $cfg['partner_sms_config'] == 2) {
			partnerSmsSend($ord['ono'], 18);
		}
	}

	makePGLog($ono, 'INIPay Vbank input Finish');

	exit('OK');

?>