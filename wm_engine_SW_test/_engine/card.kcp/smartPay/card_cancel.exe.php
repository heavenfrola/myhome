<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  KCP smartPay 결제 취소
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";

	$card=get_info($tbl[card], "no", $cno);
	$res_cd=trim($res_cd);
	$stat=1;
	if($res_cd == "0000"){
		$stat = ($card[wm_price] == $mod_mny) ? 3 : 2;
		$pdo->query("update `$tbl[card]` set `stat`='$stat', `wm_price` = `wm_price` - '$mod_mny' where `no`='$card[no]' limit 1");
		$msg="거래취소성공!";
	}else{
		$msg="거래취소실패! (".$res_cd." : ".addslashes($res_msg).")";
	}
	$sql="insert into `$tbl[card_cc_log]` (`cno`, `stat`, `ono`, `price`, `tno`, `res_cd`, `res_msg`, `admin_id`, `admin_no`, `ip`, `reg_date`) values('$card[no]', '$stat', '$card[wm_ono]', '$mod_mny', '$card[tno]', '$res_cd', '$res_msg', '$admin[admin_id]', '$admin[no]', '$_SERVER[REMOTE_ADDR]', '$now')";
	$pdo->query($sql);

	msg($msg, "reload", "parent");

?>