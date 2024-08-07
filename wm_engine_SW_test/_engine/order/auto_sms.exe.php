<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  미입금 문제 발송
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_manage/main/bankingorder.exe.php";

	// 키코드
	$site_keycode=$wec->config['wm_key_code'];

	if(!$_GET[key_code]) {
		exit('NO KEY CODE');
	}
	if(!$site_keycode){
		exit('KEY CODE NOT SET');
	}
	if($_GET[key_code] != $site_keycode){
		exit('WRONG KEY CODE');
	}

	$wdomain = preg_replace('/^www\./', '', $_SERVER['HTTP_HOST']);

	$bkSMSck = $pdo->row("select `use_check` from `$tbl[sms_case]` where `case`=9");
    if($bkSMSck){
         if(!$cfg[banking_sms_time]) $cfg[banking_sms_time]=1;
         if(!$cfg[banking_sms_until]) $cfg[banking_sms_until]=7;
		 $order_time = strtotime("-".($cfg['banking_sms_time'])." days", strtotime(date('Y-m-d 23:59:59')));
		 $limit_day = strtotime("-".($cfg['banking_sms_until'])." days", strtotime(date('Y-m-d 00:00:00')));

         $sms_q="select `no`,`buyer_cell`,`pay_prc`,`buyer_name`,`ono`, `bank`, `pay_type`, `bank_name`, FROM_UNIXTIME(`date1`,'%Y-%m-%d %H:%i') as `ord_time` from `$tbl[order]` where 1";
		 $sms_q .= " and `pay_type` in (2,4)"; // 무통장 주문
		 $sms_q .= " and `stat`=1"; // 미입금 주문
		 $sms_q .= " and `sms`='Y'"; // SMS 수신 동의 주문
		 $sms_q .= " and `buyer_cell` != ''"; // 휴대폰번호가 존재 주문
		 $sms_q .= " and `date1` between '$limit_day' and '$order_time'"; // 설정기간 조건

		 $sms_sql = $pdo->iterator($sms_q);
		 $cc=0;

         if($sms_sql->rowCount() > 0){
             include_once $engine_dir."/_engine/sms/sms_module.php";
             foreach ($sms_sql as $ord) {
				 echo $ord['buyer_name'].'<br />';
                 $sms_replace[ono]=$ord[ono];
                 $sms_replace[buyer_name]=$ord[buyer_name];
                 $sms_replace[pay_prc] = parsePrice($ord['pay_prc'], true);
				 $sms_replace[bank_name] = $ord[bank_name];
				 if ($ord[pay_type] == 4) {
					$sms_replace[account] = $pdo->row("select concat(`bankname`,' ',`account`) from `$tbl[vbank]` where `wm_ono` = '$ord[ono]'");
				 } else {
					 $sms_replace[account]= $ord[bank];
				 }
				 SMS_send_case(9,$ord[buyer_cell]);
				 $cc++;
             }
         }
    }

	echo $cc;

?>