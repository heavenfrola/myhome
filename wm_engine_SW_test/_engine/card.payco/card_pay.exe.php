<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  페이코 결제 접수
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir.'/_engine/include/common.lib.php';
	include_once $engine_dir.'/_engine/include/wingPos.lib.php';

	$ono = trim(addslashes($_POST['ono']));
	$timestamp = numberOnly($_POST['timestamp']);
	$json = json_decode($_POST['response']);
	$orderCertifyKey = addslashes($json->orderCertifyKey);
	$card = $pdo->assoc("select * from $tbl[card] where wm_ono='$ono' and reg_date='$timestamp'");
	$card['wm_price'] = parsePrice($card['wm_price']);

	makePGLog($ono, 'payco start');

	// 로그인 세션 복구
	$_SESSION['member_no'] = numberOnly($card['member_no']);
	$_SESSION['guest_no'] = $card['guest_no'];
	if($_SESSION['member_no'] > 0) {
		$member = $pdo->assoc("select * from $tbl[member] where no='$_SESSION[member_no]'");
	}

	if($ono && $_POST['message'] == 'success' && $_POST['code'] == '0') {
		// 바로 주문 시 발생한 추가 배송비 추가
		$payco_add_dlv_prc = $json->totalRemoteAreaDeliveryFeeAmt;
		if($payco_add_dlv_prc > 0) {
			if(($card['wm_price']+$payco_add_dlv_prc) == $json->totalPaymentAmt) {
				$card['wm_price'] += $payco_add_dlv_prc;
				$pdo->query("update $tbl[card] set wm_price=wm_price+$payco_add_dlv_prc where no='$card[no]'");
				$pdo->query("update $tbl[order] set pay_prc=pay_prc+$payco_add_dlv_prc, total_prc=total_prc+$payco_add_dlv_prc, dlv_prc=dlv_prc+$payco_add_dlv_prc where ono='$ono'");
				$pdo->query("update $tbl[order_payment] set amount=amount+$payco_add_dlv_prc, add_dlv_prc='$payco_add_dlv_prc' where ono='$ono'");
			}
		}

		if(!$card['no']) {
			makePGLog($ono, 'payco no card');
			exit('Error');
		}
		if($card['stat'] == 2) {
			makePGLog($ono, 'payco already completed');
			exit('OK');
		}
		if($card['wm_price'] != $json->totalPaymentAmt) {
			makePGLog($ono, 'payco wrong price');
			exit('Error');
		}

		$oasql = '';
		foreach($json->paymentDetails as $val) {
			if($val->nonBankbookSettleInfo) { // 무통장 입금
				$bankCode = addslashes($val->nonBankbookSettleInfo->bankCode);
				$bankName = addslashes($val->nonBankbookSettleInfo->bankName);
				$accountNo = addslashes($val->nonBankbookSettleInfo->accountNo);

				$pdo->query("update $tbl[card] set card_cd='$bankCode', card_name='$bankName' where no='$card[no]'");
				$oasql .= ", bank='$bankName $accountNo', bank_name='PAYCO'";
			}
		}

		if($json->deliveryPlace) { // 바로 구매 시 주소 수집
			$addr = $json->deliveryPlace;
			$recipient = addslashes($addr->recipient);
			$zip = addslashes($addr->zipcode);
			$addr1 = addslashes($addr->address1);
			$addr2 = addslashes($addr->address2);
			$dlv_memo = addslashes($addr->deliveryMemo);
			$cell = addslashes($addr->telephone);
			$oasql .= ", buyer_name='$recipient', buyer_cell='$cell',
				addressee_name='$recipient', addressee_zip='$zip', addressee_addr1='$addr1', addressee_addr2='$addr2', addressee_cell='$cell',
				dlv_memo='$dlv_memo', member_no='$member[no]', member_id='$member[member_id]', buyer_email='$member[email]'
			";
		}

		if($oasql) {
			$oasql = substr($oasql, 1);
			$pdo->query("update $tbl[order] set $oasql where ono='$ono'");
			if($pdo->getError()) exit('Error');
		}

		$stat = ($json->paymentCompletionYn == 'Y') ? 2 : 1;
		$pay_method = addslashes($json->paymentDetails[0]->paymentMethodName);

		$pdo->query("
			update $tbl[card] set
				stat='$stat', res_cd='0', res_msg='success',
				ordr_idxx='$json->orderNo', app_time='$json->paymentCompleteYmdt', tno='$json->orderCertifyKey', good_mny='$json->totalPaymentAmt',
				use_pay_method='$pay_method'
			where wm_ono='$ono'
		");

		makePGLog($ono, 'payco success', $_POST);

		$dacom_note_url = true;
		include_once $engine_dir.'/_engine/order/order2.exe.php';

		exit('OK');
	} else {
		exit('Error');
	}

?>