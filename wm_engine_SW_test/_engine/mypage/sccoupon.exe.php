<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  소셜쿠폰 사용 처리
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/shop.lib.php";
	include_once $engine_dir."/_engine/include/milage.lib.php";

	checkBasic();
	memberOnly($root_url.'/mypage/sccoupon.php', 'parent');

	$sccode=addslashes(trim($_POST['sccode']));
	checkBlank($sccode, __lang_sccpn_input_authcode__);

	$sc=$pdo->assoc("select no, `scno` from `$tbl[sccoupon_code]` where `code`='$sccode' and `use`=1");
	$scno = $sc['scno'];
	if(!$sc['no']) msg(__lang_sccpn_error_authcode__);

	$scpn = $pdo->assoc("select * from `$tbl[sccoupon]` where `no`='$scno'");
	if(empty($scpn['no'])) msg(__lang_cpn_error_nocpn__);
	if($scpn['date_type'] == 2 && ($scpn['start_date'] > date('Y-m-d', $now) || $scpn['finish_date'] < date('Y-m-d', $now))) msg(__lang_cpn_error_expirecpn__);

    /*
	if($pdo->row("select count(*) from $tbl[sccoupon_use] where scno='$scno' and member_id='$member[member_id]'") > 0) {
		msg(__lang_cpn_error_used__);
	}
    */

	if($scpn['is_type'] == 1) { // 적립금

		ctrlMilage("+", 14, $scpn['milage_prc'], $member, "");
		$ems = sprintf(__lang_sccpn_info_milage__, number_format($scpn['milage_prc']));

		$sql="insert into `$tbl[sccoupon_use]` (`scno`, `code`, `milage_prc`, `member_no`, `member_id`, `member_name`, `reg_date`) values ('$scno', '$sccode', '$scpn[milage_prc]', '$member[no]', '$member[member_id]', '$member[name]', '$now')";
	} else { // 쿠폰

		$today=date("Y-m-d");
		$cpn = $pdo->assoc("select * from `$tbl[coupon]` where no='$scpn[cno]' and (`rdate_type`=1 or (`rdate_type`='2' and `rstart_date`<='$today' and `rfinish_date`>='$today'))");
		if(putCoupon($cpn, $member) == false) msg(__lang_sccpn_error_download__);
		$pdo->query("update `$tbl[coupon]` set `down_hit`=`down_hit`+1 where `no`='$cpn[no]'");

		$ems = $cpn['name'].' '.__lang_cpn_info_downloaded__;

		$sql="insert into `$tbl[sccoupon_use]` (`scno`, `code`, `cno`, `member_no`, `member_id`, `member_name`, `reg_date`) values ('$scno', '$sccode', '$scpn[cno]', '$member[no]', '$member[member_id]', '$member[name]', '$now')";
	}

	$pdo->query($sql);
	$pdo->query("update `$tbl[sccoupon_code]` set `use`=2 where `code`='$sccode' and no='$sc[no]'");

	msg($ems, $root_url."/mypage/sccoupon_list.php", "parent");

?>