<?PHP

	$_POST['advinfo_day'] = numberOnly($_POST['advinfo_day']);
	$_POST['use_advinfo'] = ($_POST['use_advinfo'] == 'Y') ? 'Y' : 'N';

	// 자동 이메일 설정
	$email_checked = explode('@', trim($cfg['email_checked'], '@'));
	$tmp = array_search('15', $email_checked);
	if($tmp !== false) {
		unset($email_checked[$tmp]);
	}
	$email_checked = '@'.implode('@', $email_checked).'@';
	if($_POST['use_advinfo'] == 'Y') {
		$email_checked .= '15@';
	}
	$_POST['email_checked'] = $email_checked;

	// db 마이그레이션
	if(!fieldExist($tbl['member'], 'mailing_chg_date')) {
		addField($tbl['member'], 'mailing_chg_date', 'int(10) not null default "0"');
		addField($tbl['member'], 'mailing_chg_id', 'varchar(50) not null default ""');
		addField($tbl['member'], 'sms_chg_date', 'int(10) not null default "0"');
		addField($tbl['member'], 'sms_chg_id', 'varchar(50) not null default ""');
	}

	if($_POST['use_advinfo'] == 'Y') {
		$pdo->query("update $tbl[member] set mailing_chg_date=reg_date where mailing_chg_date=0");
		$pdo->query("update $tbl[member] set sms_chg_date=reg_date=reg_date where sms_chg_date=reg_date=0");
	}

	// 크론 정보 등록
	$wec_acc = new weagleEyeClient($_we, 'mall');
	$wec_acc->call('setAdvChgMail', array('use'=>$_POST['use_advinfo'], 'day'=>$_POST['advinfo_day'], 'domain'=>$root_url));

	include $engine_dir.'/_manage/config/config.exe.php';

?>