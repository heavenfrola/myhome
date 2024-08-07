<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  다날 결제 취소
	' +----------------------------------------------------------------------------------------------+*/

	header("Pragma: No-Cache");

	include $engine_dir."/_engine/card.danal/inc/function.php";

	$Trans = array();

	$Trans['ID'] = $cfg['danal_cp_id'];
	$Trans['PWD'] = $cfg['danal_cp_pwd'];
	$Trans['TID'] = $card['tno'];

	$Addition = array("ServerInfo");
	$Trans = MakeAddtionalInput($Trans,$GLOBALS,$Addition);
	$Res = CallTeleditCancel( $Trans,false );
	$Res['ErrMsg'] = mb_convert_encoding($Res['ErrMsg'], _BASE_CHARSET_, 'euckr');

	if($Res["Result"] == "0") {
		$pdo->query("update `$tbl[card]` set `stat`='3', `wm_price` = '0' where `no`='$card[no]' limit 1");
		$log_stat=2;
		$ems = '거래취소성공!';
	} else {
		$log_stat=1;
		$ems = '거래취소실패!';
		$ems .= "\\n".$Res['ErrMsg'];
	}

	$pdo->query(
		"insert into `$tbl[card_cc_log]` (`cno`, `stat`, `ono`, `price`, `tno`, `res_cd`, `res_msg`, `admin_id`, `admin_no`, `ip`, `reg_date`)
		values ('$card[no]', '$log_stat', '$card[wm_ono]', '$card[wm_price]', '$card[tno]', '$Res[Result]', '".addslashes($Res["ErrMsg"])."', '$admin[admin_id]', '$admin[no]', '$_SERVER[REMOTE_ADDR]', '$now')
	");

    // 주문서 처리와 함께 카드 취소
    if (isset($duel_card_cancel) == true && $duel_card_cancel == true) {
        $card_cancel_result = ($Res["Result"] == '0') ? 'success' : $Res['ErrMsg'];
        return;
    }

	msg($ems, "reload", "parent");

?>