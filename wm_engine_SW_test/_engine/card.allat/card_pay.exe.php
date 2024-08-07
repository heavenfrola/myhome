<?php

	/* +----------------------------------------------------------------------------------------------+
	' |  올앳 PG결제 DB처리
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir.'/_engine/include/common.lib.php';
	include_once $engine_dir.'/_engine/card.allat/allatutil.php';

	if(function_exists('extractParam')) {
		extractParam();
	}

	$ono = addslashes(trim($_SESSION['allat_ono']));
	if(!$_SESSION['allat_ono']) {
		makePGLog($ono, 'allat Start Error');

		msg('정상적인 주문이 아닙니다.');
	}

	makePGLog($ono, 'allat Start');

	$ord = $pdo->assoc("select no, pay_type, pay_prc from $tbl[order] where ono='$ono'");

	$card_tbl = ($ord['pay_type'] == 4) ? $tbl['vbank'] : $tbl['card'];
	$card = $pdo->assoc("select * from `$card_tbl` where `wm_ono`='$ono'");
	if($card['stat'] == 2) exit('OK');

	$at_cross_key = $cfg['card_cross_key'];
	$at_shop_id = $cfg['card_partner_id'];
	$at_amt = parsePrice($ord['pay_prc']);

	$at_data = 'allat_shop_id='.$at_shop_id.
			   '&allat_amt='.$at_amt.
			   '&allat_enc_data='.$_POST['allat_enc_data'].
			   '&allat_cross_key='.$at_cross_key;


	$at_txt = ApprovalReq($at_data, 'SSL');
	$REPLYCD = getValue('reply_cd', $at_txt);
	$REPLYMSG = iconv('euc-kr', _BASE_CHARSET_, getValue('reply_msg', $at_txt));

	makePGLog($ono, 'allat process', $at_txt);

	if(!strcmp($REPLYCD,'0000')) {
		$ORDER_NO         = getValue('order_no', $at_txt);
		$AMT              = getValue('amt', $at_txt);
		$PAY_TYPE         = getValue('pay_type', $at_txt);
		$APPROVAL_YMDHMS  = getValue('approval_ymdhms', $at_txt);
		$SEQ_NO           = getValue('seq_no', $at_txt);
		$APPROVAL_NO      = getValue('approval_no', $at_txt);
		$CARD_ID          = getValue('card_id', $at_txt);
		$CARD_NM          = iconv('euc-kr', _BASE_CHARSET_, getValue('card_nm', $at_txt));
		$SELL_MM          = getValue('sell_mm', $at_txt);
		$ZEROFEE_YN       = getValue('zerofee_yn', $at_txt);
		$CERT_YN          = getValue('cert_yn', $at_txt);
		$CONTRACT_YN      = getValue('contract_yn', $at_txt);
		$SAVE_AMT         = getValue('save_amt', $at_txt);
		$CARD_POINTDC_AMT = getValue('card_pointdc_amt', $at_txt);
		$BANK_ID          = getValue('bank_id', $at_txt);
		$BANK_NM          = iconv('euc-kr', _BASE_CHARSET_, getValue('bank_nm', $at_txt));
		$CASH_BILL_NO     = getValue('cash_bill_no', $at_txt);
		$ESCROW_YN        = getValue('escrow_yn', $at_txt);
		$ACCOUNT_NO       = getValue('account_no', $at_txt);
		$ACCOUNT_NM       = iconv('euc-kr', _BASE_CHARSET_, getValue('account_nm', $at_txt));
		$INCOME_ACC_NM    = getValue('income_account_nm', $at_txt);
		$INCOME_LIMIT_YMD = getValue('income_limit_ymd', $at_txt);
		$INCOME_EXPECT_YMD= getValue('income_expect_ymd', $at_txt);
		$CASH_YN          = getValue('cash_yn', $at_txt);
		$HP_ID            = getValue('hp_id', $at_txt);
		$TICKET_ID        = getValue('ticket_id', $at_txt);
		$TICKET_PAY_TYPE  = getValue('ticket_pay_type', $at_txt);
		$TICKET_NAME      = getValue('ticket_nm', $at_txt);
		$PARTCANCEL_YN    = getValue('partcancel_yn', $at_txt);

		$card_tbl = ($ord['pay_type'] == 4) ? $tbl['vbank'] : $tbl['card'];
		switch($ord['pay_type']) {
			case '4' :
				$card_asql = ", bank_code='$BANK_ID', bankname='$BANK_NM', account='$ACCOUNT_NO', depositor='$ACCOUNT_NM'";
			break;
			case '5' :
				$card_asql = ", card_cd='$BANK_ID', card_name='$BANK_NM'";
			break;
			default :
				$card_asql = ", card_cd='$CARD_ID', card_name='$CARD_NM', quota='$SELL_MM'";
		}

		$pdo->query("
			update $card_tbl set
				stat='2', res_cd='$REPLYCD', res_msg='$REPLYMSG',
				ordr_idxx='$ORDER_NO', tno='$SEQ_NO', use_pay_method='$PAY_TYPE',
				good_mny='$AMT' $card_asql
			where wm_ono='$ono'
		");
		$card_pay_ok = true;

		makePGLog($ono, 'allat success', $response_array);
		include_once $engine_dir."/_engine/order/order2.exe.php";

		javac("
			parent.location.href = '/shop/order_finish.php';
			self.close();
		");
	} else {
		$pdo->query("update $tbl[order] set stat=31 where ono='$ono'");
		$pdo->query("update $tbl[order_product] set stat=31 where ono='$ono'");
		$pdo->query("update $card_tbl set stat='3', res_cd='$REPLYCD', res_msg='$REPLYMSG' where wm_ono='$ono'");

		makePGLog($ono, 'allat fail');

		msg($REPLYMSG, 'close');
	}

?>