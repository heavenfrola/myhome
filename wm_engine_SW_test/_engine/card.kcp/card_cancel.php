<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  KCP PG결제 취소
	' +----------------------------------------------------------------------------------------------+*/

	$mobile_paymented = $pdo->row("select `mobile` from `$tbl[order]` where `ono` = '$card[wm_ono]'");
	$sum_cancel_price = $pdo->row("select sum(`price`) from `$tbl[card_cc_log]` where `ono` = '$card[wm_ono]' and `stat` = 2");

	$pg_version = ($mobile_paymented  ==  'Y' || $mobile_paymented  ==  'A') ? "smartPay/" : "";
	$curl_fd['exec_file'] = "card.kcp/".$pg_version."pp_ax_hub.php";
	$curl_fd['site_cd'] = ($mobile_paymented  ==  'Y' || $mobile_paymented  ==  'A') ? $cfg['card_mobile_site_cd'] : $cfg['card_site_cd'];
	$curl_fd['site_key'] = ($mobile_paymented  ==  'Y' || $mobile_paymented  ==  'A') ? $cfg['card_mobile_site_key'] : $cfg['card_site_key'];

	if($cfg['kcp_part_cancel']  ==  'Y' && $price > 0)  $curl_fd['mod_type'] = "RN07";
	else {
		if($sum_cancel_price > 0) { // 전액 취소
			$price = $card['wm_price'];
			$curl_fd['mod_type'] = "RN07";
		} else $curl_fd['mod_type'] = "STSC";
	}

	if($card['use_pay_method'] == '010000000000' && $curl_fd['mod_type'] == 'RN07' && $mobile_paymented != 'Y' && $mobile_paymented != 'A') $curl_fd['mod_type'] = 'STPC';

	$curl_fd['req_tx'] = "mod";
	$curl_fd['tno'] = $card['tno'];
	$curl_fd['ono'] = $card['wm_ono'];
	$curl_fd['cno'] = $card['no'];
	$curl_fd['rem_mny'] = parsePrice($card['wm_price']);
	$curl_fd['mod_mny'] = $price;
	$curl_fd['urlfix'] = "Y";

	foreach($curl_fd as $ck => $cv) {
		$post_args .=  ($post_args) ? "&" : "";
		$post_args .=  $ck."=".$cv;
	}
	$r = comm($root_url."/main/exec.php", $post_args);
	$r = addslashes(mb_convert_encoding($r, _BASE_CHARSET_, 'euckr'));

	if($r) {
		$r = explode(";", $r);
		foreach($r as $val){
			$_val = explode(":", $val);
			${$_val[0]} = trim($_val[1]);
		}
		$card = get_info($tbl['card'], "no", $cno);
		$res_cd = trim($res_cd);

		$stat = 1;
		if($res_cd == "0000") {
			$mod_mny = $mod_mny ? $mod_mny : $card[wm_price];
			$prc_q = ($mod_mny < $card[wm_price]) ? ", `wm_price`  =  `wm_price` - '$mod_mny'" : "";
			$stat  =  ($card[wm_price]  ==  $mod_mny) ? 3 : 2;
			$pdo->query("update `$tbl[card]` set `stat` = '$stat'".$prc_q." where `no` = '$card[no]' limit 1");
			$msg = "거래취소성공!";
			$log_stat = 2;
		} else {
            $res_msg = addslashes(mb_convert_encoding($res_msg, _BASE_CHARSET_, array('UTF-8', 'EUC-KR', 'CP949')));
			$msg = "거래취소실패! (".$res_cd." : ".addslashes($res_msg).")";
			$log_stat = 1;
		}

		$pdo->query("
			insert into `$tbl[card_cc_log]` (`cno`, `stat`, `ono`, `price`, `tno`, `res_cd`, `res_msg`, `admin_id`, `admin_no`, `ip`, `reg_date`)
			values ('$card[no]', '$log_stat', '$card[wm_ono]', '$mod_mny', '$card[tno]', '$res_cd', '$res_msg', '$admin[admin_id]', '$admin[no]', '$_SERVER[REMOTE_ADDR]', '$now')
		");
	}

    // 주문서 처리와 함께 카드 취소
    if (isset($duel_card_cancel) == true && $duel_card_cancel == true) {
        $card_cancel_result = ($res_cd == '0000') ? 'success' : $res_msg;
        return;
    }

	if(!$_from_order_fail) msg($msg, "reload", "parent");

	exit;

?>