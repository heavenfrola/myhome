<?PHP

	$sadmin = 'Y';

	addField($tbl['partner_shop'], 'partner_email', "varchar(50) not null default ''");
	addField($tbl['partner_shop'], 'partner_email_use', "enum('Y', 'N') not null default 'N'");

	$tmp = $pdo->assoc("select * from $tbl[partner_sms] where partner_no='$admin[partner_no]'");
	$sms_rec = $pdo->row("select sms_rec $tbl[partner_shop] where no='$admin[partner_no]'");

	$cfg['config_sms_rec'] = $sms_rec;

	include $engine_dir.'/_manage/member/email_config.php';

?>