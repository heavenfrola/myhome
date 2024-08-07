<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  올앳 PG 취소
	' +----------------------------------------------------------------------------------------------+*/

	$urlfix = 'Y';
	include_once $engine_dir."/_engine/include/common.lib.php";

	if(function_exists('extractParam')) {
		extractParam();
	}

	if(!$admin['no'] || $admin['level'] > 3) msg('카드 취소 권한이 없습니다.');

	$cno = numberOnly($_POST['cno']);
	$card = get_info($tbl['card'], 'no', $cno);

	$ord = $pdo->assoc("select * from {$tbl['order']} where ono='{$card['wm_ono']}'");
	// Request Value Define

	if($ord['mobile'] == 'Y' || $ord['mobile']=='A'){
		include $engine_dir."/_engine/card.allat/mobile/allatutil.php";
	}else{
		include $engine_dir."/_engine/card.allat/allatutil.php";
	}

	$at_shop_id = ($card['pg_version'] == 'mobile') ? $cfg['mobile_card_partner_id'] : $cfg['card_partner_id'];
	$at_cross_key = ($card['pg_version'] == 'mobile') ? $cfg['mobile_card_cross_key'] : $cfg['card_cross_key']; //$cfg['card_cross_key'];

	// 올앳 결제 서버와 통신 : CancelReq->통신함수, $at_txt->결과값
	$at_data = "allat_shop_id=".$at_shop_id."&allat_enc_data=".$_POST["allat_enc_data"]."&allat_cross_key=".$at_cross_key;
	$at_txt = CancelReq($at_data,"NOSSL");

	// 결제 결과 값 확인
	$REPLYCD   = getValue("reply_cd", $at_txt);
	$REPLYMSG  = getValue("reply_msg", $at_txt);
	$REPLYMSG = iconv('euc-kr', 'utf-8', $REPLYMSG);

	$CANCEL_YMDHMS=getValue("cancel_ymdhms",$at_txt);
	$PART_CANCEL_FLAG=getValue("part_cancel_flag",$at_txt);
	$REMAIN_AMT=getValue("remain_amt",$at_txt);
	$PAY_TYPE=getValue("pay_type",$at_txt);

	$REPLYCD=trim($REPLYCD);
	$stat=1;
	if($REPLYCD == "0000" || $REPLYCD == "0001") {
		$cstat = ($PART_CANCEL_FLAG = 1 && $REMAIN_AMT > 0) ? 2 : 3;
		$pdo->query("update `$tbl[card]` set `stat`='$cstat', wm_price='$REMAIN_AMT' where `no`='$card[no]'");
		$msg = '거래취소성공!';
		$stat = 2;
	} else {
		$msg = '거래취소실패! ('.$REPLYCD.' : '.addslashes($REPLYMSG).')';
	}
	$pdo->query("
		insert into `$tbl[card_cc_log]` (`cno`, `stat`, `ono`, `price`, `tno`, `res_cd`, `res_msg`, `admin_id`, `admin_no`, `ip`, `reg_date`)
		values ('$card[no]', '$stat', '$card[wm_ono]', '$allat_amt', '$card[tno]', '$REPLYCD', '$REPLYMSG', '$admin[admin_id]', '$admin[no]', '$_SERVER[REMOTE_ADDR]', '$now')"
	);

	msg($msg, "reload", "parent");

?>