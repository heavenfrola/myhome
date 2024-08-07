<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  올앳 모바일 DB 처리
	' +----------------------------------------------------------------------------------------------+*/


	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/shop.lib.php";
	include_once $engine_dir."/_engine/card.allat/allatutil.php";

	checkBasic();

	define('__pg_card_pay.exe__', $allat_order_no);
	makePGLog($allat_order_no, 'allat mobile Start');

	foreach($_POST as $key => $val) {
		${$key} = addslashes(trim($val));
	}

	$ono		= $allat_order_no;
	$card_tbl	= ($allat_vbank_yn == "Y") ? $tbl['vbank'] : $tbl['card'];

	$card = $pdo->assoc("select * from `$card_tbl` where `wm_ono`='$ono'");
	if($card['stat'] == 2) exit('OK');

	$at_data	= "allat_shop_id=".$cfg['mobile_card_partner_id']."&allat_amt=".$allat_amt."&allat_enc_data=".$allat_enc_data."&allat_cross_key=".$cfg['mobile_card_cross_key'];
	$at_txt		= ApprovalReq($at_data, "NOSSL");

	makePGLog($allat_order_no, 'allat approval finish', 'at_data : '.$at_data."\n\nat_txt : ".$at_txt);

	$good_mny	= numberOnly($allat_amt);
	$pay_type	= getValue("pay_type", $at_txt);
	$bank_id	= getValue("bank_id", $at_txt);
	$bank_nm    = getValue("bank_nm", $at_txt);
	$bank_code	= getValue("bank_id", $at_txt);
	$escrow_yn	= getValue("escrow_yn", $at_txt);
    $account_no	= getValue("account_no", $at_txt);
    $account_nm	= getValue("account_nm", $at_txt);
	$card_cd	= getValue("card_id", $at_txt);
	$card_nm	= getValue("card_nm", $at_txt);
	$quota		= getValue("sell_mm", $at_txt);
	$app_no		= getValue("approval_no", $at_txt);
	$app_time	= getValue("approval_ymdhms", $at_txt);
	$noinf		= getValue("zerofee_yn", $at_txt);
	$res_cd		= getValue("reply_cd", $at_txt);
	$res_msg	= addslashes(mb_convert_encoding(getValue("reply_msg", $at_txt), _BASE_CHARSET_, 'euc-kr'));
	$tno		= getValue("seq_no", $at_txt);
	$stat		= $res_cd == '0000' ? 2 : 3;

	$card_nm    = mb_convert_encoding($card_nm, _BASE_CHARSET_, 'euc-kr');
	$bank_nm    = mb_convert_encoding($bank_nm, _BASE_CHARSET_, 'euc-kr');
	$account_nm = mb_convert_encoding($account_nm, _BASE_CHARSET_, 'euckr');

	if($pay_type == 'ABANK') {
		$card_cd = $bank_id;
		$card_nm = $bank_nm;
		$quota = $escrow_yn;
	}
	elseif($pay_type == 'VBANK'){
		$account = $account_no;
		$depositor = $account_nm;
		$bank_info = "$bank_nm $account $account_nm";
	}

	if($pay_type == 'VBANK') {
		$asql = ",`bankname`='$bank_nm', `account`='$account', `depositor`='$account_nm', `bank_code`='$bank_code'";
	} else {
		$asql = ",`card_cd`='$card_cd', `card_name`='$card_nm', `app_time`='$app_time', `app_no`='$app_no', `noinf`='$noinf', `quota`='$quota'";
	}

	$pdo->query("
		update `$card_tbl` set `stat`='$stat', `res_cd`='$res_cd', `res_msg`='$res_msg', `ordr_idxx`='$ono', `tno`='$tno',
							   `good_mny`='$good_mny', `good_name`='$allat_product_nm', `buyr_name`='$allat_buyer_nm', `buyr_mail`='allat_email_addr',
							   `use_pay_method`='$pay_type' $asql
		where `wm_ono`='$ono'
	");

	makePGLog($ono, 'allat db update');

	if($res_cd != '0000') {
		alert(php2java("결재가 실패되었습니다.\n$res_msg"));
		msg('', $root_url.'/shop/order.php');
	} else {
		$card_pay_ok=true;
		include_once $engine_dir."/_engine/order/order2.exe.php";
	}

	exit('OK');
?>