<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  KCP 결제승인정보 DB처리
	' +----------------------------------------------------------------------------------------------+*/

	header('Content-type:text/html; charset='._BASE_CHARSET_);
	set_time_limit(0);

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/shop.lib.php";

	$ordr_idxx = $_POST['ordr_idxx'];

	define('__pg_card_pay.exe__', $ordr_idxx);
	makePGLog($ordr_idxx, 'kcp Start');

	$ono=$ordr_idxx;
	$ordr_mony=$pdo->row("select `pay_prc` from `$tbl[order]` where `ono`='$ono'");
	$ordr_mony = parsePrice($ordr_mony);

	checkBasic();

	include_once $engine_dir."/_engine/card.kcp/pp_ax_hub.php";

	makePGLog($ono, 'kcp pp_ax_hub end');

	switch($use_pay_method) {
		case "001000000000" :
			$card_tbl=$tbl['vbank'];
			$bankname = mb_convert_encoding($bankname, _BASE_CHARSET_, 'euckr');
			$depositor = mb_convert_encoding($depositor, _BASE_CHARSET_, 'euckr');
		break;
		case "010000000000" :
			$card_tbl=$tbl['card'];
			$card_cd=$bank_issu;
			$card_name=$bank_name;
		break;
		default :
			$card_tbl=$tbl['card'];
		break;
	}
	$card_name = iconv('euc-kr', _BASE_CHARSET_, $card_name);

	$card = $pdo->assoc("select * from `$card_tbl` where `wm_ono`='$ono'");
	$card_stat = ($res_cd == '0000') ? 2 : 1;

	if($card['res_cd'] == '0000') { // 기결제된 주문 중복호출시 2012-09-13
		$_SESSION['last_order']=$ono;
		msg('', $root_url.'/shop/order_finish.php', 'parent');
		exit;
	}

	$ems=null;
	if(empty($ono)) $ems="주문번호 누락";
	if(empty($card['no'])) $ems="카드테이블 누락";
	if($card['stat'] != 1) $ems="기결제된 주문 중복 호출";
	if(empty($tno)) $ems="거래번호 누락";
	if($res_cd != '0000') {
        $ems = iconv('euc-kr', _BASE_CHARSET_, $res_msg);
        if (!$ems) $ems = $res_msg;
    }

	if($use_pay_method == "001000000000") {
		javac("parent.$('#bank_list_span').html('<input type=\"hidden\" name=\"bank\" value=\"<?=$bankname?> <?=$account?> <?=$depositor?>\">');");
		$addSql=", `bankname`='$bankname', `account`='$account', `depositor`='$depositor', `bank_code`='$bank_code'";
	} else {
		$addSql=", `card_cd`='$card_cd', `card_name`='$card_name', `app_time`='$app_time', `app_no`='$app_no', `noinf`='$noinf', `quota`='$quota'";
	}

	$pdo->query("update `$card_tbl` set `stat`='$card_stat', `res_cd`='$res_cd', `res_msg`='$res_msg', `ordr_idxx`='$ordr_idxx', `tno`='$tno', `good_mny`='$good_mny', `good_name`='$good_name', `buyr_name`='$buyr_name', `buyr_mail`='$buyr_mail', `buyr_tel1`='$buyr_tel1', `buyr_tel2`= '$buyr_tel2',`use_pay_method`='$use_pay_method' $addSql where `wm_ono`='$ono'");

	makePGLog($ono, 'kcp db update finish');

	if($ems) { // 결제실패처리
		if($res_cd == '0000') { // 자동취소 처리
			$c_PayPlus->mf_clear();
			$tran_cd = "00200000";

			$c_PayPlus->mf_set_modx_data( "tno",      $tno                         );
			$c_PayPlus->mf_set_modx_data( "mod_type", "STSC"                       );
			$c_PayPlus->mf_set_modx_data( "mod_ip",   $cust_ip                     );
			$c_PayPlus->mf_set_modx_data( "mod_desc", "결과 처리 오류 - 자동 취소" );

			$c_PayPlus->mf_do_tx( $tno,  $g_conf_home_dir, $g_conf_site_cd,
								  "",  $tran_cd,    "",
								  $g_conf_gw_url,  $g_conf_gw_port,  "payplus_cli_slib",
								  $ordr_idxx, $cust_ip, "3" ,
								  0, 0, $g_conf_key_dir, $g_conf_log_dir);

			$res_cd  = $c_PayPlus->m_res_cd;
			$res_msg = $c_PayPlus->m_res_msg;

			$log_stat=($res_cd == '0000') ? 2 : 1;

			if($res_cd == '0000') $pdo->query("update `$tbl[card]` set `stat`='3', `wm_price`='0' where `no`='$card[no]' limit 1");

			$pdo->query("insert into `$tbl[card_cc_log]` (`cno`, `stat`, `ono`, `price`, `tno`, `res_cd`, `res_msg`, `admin_id`, `admin_no`, `ip`, `reg_date`) values('$card[no]', '$log_stat', '$card[wm_ono]', '$good_mny', '$tno', '$res_cd', '자동취소 : $res_msg , 취소사유 - $ems', '시스템', '0', '$_SERVER[REMOTE_ADDR]', '$now')");
		}

		$pdo->query("update `$tbl[order]` set `stat`=31 where `ono`='$card[wm_ono]' and stat != 2");
		$pdo->query("update `$tbl[order_product]` set `stat`=31 where `ono`='$card[wm_ono]' and stat != 2");

		makePGLog($ono, 'kcp faild');

		javac("alert(\"\\n 결제가 실패하였습니다 : ".$ems."             \\n\\n 다시 결제하기를 클릭하세요.             \\n\");
		parent.closeEvent();
        parent.layTgl3('order1', 'Y');
        parent.layTgl3('order2', 'N');
        parent.layTgl3('order3', 'Y');

		cancelf=document.pay_cFrm;
		cancelf.ono.value='".$ono."';
		cancelf.mode.value='fail';
		cancelf.submit();
        ");
		exit;
	}

	$card_pay_ok=true;
	include_once $engine_dir."/_engine/order/order2.exe.php";
	exit;

?>