<?PHP

    set_time_limit(0);
    ini_set('memory_limit', -1);

	include_once $engine_dir.'/_engine/include/common.lib.php';
	printAjaxHeader();

	$email_checked = explode('@', trim($cfg['email_checked'], '@'));
	if(!in_array(15, $email_checked)) {
		exit('service is not use');
	}

	$cnt = 0;
	$limitdate = strtotime('-2 years', strtotime(date('Y-m-d 00:00:00')));
	$res = $pdo->iterator("select no, name, email, member_id, mailing, mailing_chg_date, sms, sms_chg_date, reg_date from $tbl[member] where ((sms='Y' and sms_chg_date < $limitdate) or (mailing='Y' and mailing_chg_date < $limitdate)) and withdraw='N'");
    foreach ($res as $mem) {
		if($mem['mailing'] == 'Y' && !$mem['mailing_chg_date']) $mem['mailing_chg_date'] = $mem['reg_date'];
		if($mem['sms'] == 'Y' && !$mem['sms_chg_date']) $mem['sms_chg_date'] = $mem['reg_date'];

		$mail_case = 15;
		include $engine_dir.'/_engine/include/mail.lib.php';
		sendMailContent($mail_case, $member_name, $to_mail);

		$asql = '';
		if($mem['mailing'] == 'Y') $asql .= ", mailing_chg_date='$now'";
		if($mem['sms'] == 'Y') $asql .= ", sms_chg_date='$now'";

		if($asql) {
			$asql = substr($asql, 1);
			$pdo->query("update $tbl[member] set $asql where no='$mem[no]'");
			$cnt++;
		}
	}

	echo $cnt;
	exit;

?>