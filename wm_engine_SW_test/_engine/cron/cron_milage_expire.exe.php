<?PHP

    set_time_limit(0);
    ini_set('memory_limit', -1);

	define('__CRON_SCRIPT__', true);

	chdir(dirname(__FILE__));
	$urlfix = 'Y';

	include_once $engine_dir.'/_engine/include/common.lib.php';
	include_once $engine_dir.'/_engine/include/milage.lib.php';
	include_once $engine_dir.'/_engine/include/shop.lib.php';

    if ($_REQUEST['site_key'] != $_we['wm_key_code']) {
        exit('Site keys do not match.');
    }

	if(!fieldExist($tbl['milage'], 'expire_sms')) {
		addField($tbl['milage'],"expire_sms","enum('Y','N') NOT NULL default 'N'");
		$pdo->query("alter table $tbl[milage] add index expire_date (expire_date)");
		$pdo->query("alter table $tbl[milage] add index expire_date (expire)");
		addField($tbl['milage'],"expire_email","enum('Y','N') NOT NULL default 'N'");
	}

    // 실 만료 처리
    expireMilage();

    // 만료 알림
    $tomorrow = strtotime(date('Y-m-d 23:59:59'))+1;
	if($cfg['expire_sms_use'] == 'Y') {
		include_once $engine_dir.'/_engine/sms/sms_module.php';
		if($cfg['milage_expire_sms_case'] == 'B') {
			$case = 21;
		} else {
			$case = 20;
		}
		$sms_use = $pdo->row("select `use_check` from `$tbl[sms_case]` where `case` = '$case'");
		if($sms_use = 'Y') {
			$sms_time = strtotime('+ '.$cfg['milage_expire_sms'], strtotime(date('Y-m-d 23:59:59')));
			$res = $pdo->iterator("select a.no, a.expire_date, sum(a.amount-a.use_amount) as expire_amount, a.expire_sms, b.cell, b.sms, b.name from $tbl[milage] a inner join $tbl[member] b on a.member_no=b.no where a.expire_date between '$tomorrow' and '$sms_time' and a.expire_date != 0 and a.expire='N' and a.expire_sms='N' and b.sms='Y' and a.amount != a.use_amount and b.withdraw!='Y' group by a.member_no");
            foreach ($res as $data) {
                $sms_replace['name'] = $data['name'];
				$sms_replace['expire_date'] = date('Y/m/d', $data['expire_date']);
				$sms_replace['amount'] = parsePrice($data['expire_amount'], true);
				SMS_send_case($case, $data['cell']);
			}
			$res = $pdo->query("update $tbl[milage]  set expire_sms='Y' where expire_date<'$sms_time' and expire_date!=0 and expire='N' and expire_sms='N'");
		}
	}
	if($cfg['expire_email_use'] == 'Y') {
		$email_time = strtotime('+ '.$cfg['milage_expire_email'], strtotime(date('Y-m-d 23:59:59')));
		$res = $pdo->iterator("select a.no, a.expire_date, sum(a.amount-a.use_amount) as expire_amount, a.expire_email, b.email,  b.mailing, b.name from $tbl[milage] a inner join $tbl[member] b on a.member_no=b.no where a.expire_date between '$tomorrow' and '$email_time' and a.expire_date != 0 and a.expire='N' and a.expire_email='N' and b.mailing='Y' and a.amount != a.use_amount and b.withdraw!='Y' group by a.member_no");
        foreach ($res as $data) {
			$mail_case = ($cfg['milage_expire_email_case'] == "A") ? 17 : 18;
			$amount =  parsePrice($data['expire_amount'], true);
			$expire_date = date('Y/m/d', $data['expire_date']);
			include $engine_dir.'/_engine/include/mail.lib.php';
			sendMailContent($mail_case, $data['name'], $data['email']);
		}
		$res = $pdo->query("update $tbl[milage]  set expire_email='Y' where expire_date<'$email_time' and expire_date!=0 and expire='N' and expire_email='N'");
	}

	exit('OK');

?>