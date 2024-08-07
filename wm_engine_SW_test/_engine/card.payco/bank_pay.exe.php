<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  가상계좌 입금 확인
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir.'/_engine/include/common.lib.php';
	include_once $engine_dir.'/_engine/include/wingPos.lib.php';

	header('Content-type: text/html; charset: UTF-8');

	$json = $_REQUEST['response']; // php 버전에 따라 stripslashes 필요
	$json = json_decode($json);

	$ono = addslashes($json->sellerOrderReferenceKey);
	$payco_ono = addslashes($json->orderNo);
	$pay_prc = numberOnly($json->totalPaymentAmt);
	$orderCertifyKey = addslashes($json->orderCertifyKey);

	makePGLog($ono, 'payco bank start');

	$card = $pdo->assoc("select no, stat from $tbl[card] where ordr_idxx='$payco_ono' and wm_ono='$ono' and tno='$orderCertifyKey'");
	$ord = $pdo->assoc("select ono, stat, buyer_name, buyer_cell, pay_prc from $tbl[order] where ono='$ono'");
	if(!$card['no'] || !$ord['ono']) {
		makePGLog($ono, 'payco bank no data');
		exit('Error');
	}
	if(($ord['stat'] != 1 && $ord['stat'] != 11) || $card['stat'] != 1) {
		makePGLog($ono, 'payco bank already finished');
		exit('OK');
	}

	// 결제 입금 처리
	$pdo->query("update $tbl[order_payment] set stat=2, confirm_date='$now' where ono='$ono' and type='0'");

	$erp_auto_input = 'Y'; // 재고가 모자랄경우 재고확인상태로 변경
	if(!orderStock($ono, 1, 2)) { // 재고가 남아있을경우
		$pdo->query("update $tbl[card] set stat=2 where no='$card[no]'");
		$pdo->query("update $tbl[order_product] set stat='2' where ono='$ono'");
		ordChgPart($ono);
		ordStatLogw($ono, 2, 'Y');
	}

	makePGLog($ono, 'payco bank finish');

	// 입금확인 SMS
	include_once $engine_dir.'/_engine/sms/sms_module.php';
	$sms_replace['buyer_name'] = $ord['buyer_name'];
	$sms_replace['ono'] = $ord['ono'];
	$sms_replace['pay_prc'] = number_format($ord['pay_prc']);
	SMS_send_case(3, $ord['buyer_cell']);
	SMS_send_case(18);

	if($cfg['partner_sms_config'] == 1 || $cfg['partner_sms_config'] == 2) {
		partnerSmsSend($ord['ono'], 18);
	}

	exit('OK');

?>