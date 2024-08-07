<?PHP

	if(!isTable($tbl['partner_sms'])) {
		include_once $engine_dir."/_config/tbl_schema.php";
		$pdo->query($tbl_schema['partner_sms']);
	}

	addField($tbl['partner_shop'], 'partner_sms', "varchar(50) not null default ''");
	addField($tbl['partner_shop'], 'partner_sms_use', "enum('N', 'Y') not null default 'N'");
	addField($tbl['partner_shop'], 'night_sms_start', "varchar(2) not null default ''");
	addField($tbl['partner_shop'], 'night_sms_end', "varchar(2) not null default ''");

	$_GET['sadmin'] = 'Y';

	$tmp = $pdo->assoc("select * from $tbl[partner_sms] where partner_no='$admin[partner_no]'");
	$partner_sms = $pdo->assoc("select * from $tbl[partner_shop] where no='$admin[partner_no]'");

	$cfg['config_sms_rec'] = $partner_sms['sms_rec'];
	$cfg['night_sms_start'] = ($partner_sms['night_sms_start']) ? $partner_sms['night_sms_start'] : $cfg['night_sms_start'];
	$cfg['night_sms_end'] = ($partner_sms['night_sms_end']) ? $partner_sms['night_sms_end'] : $cfg['night_sms_end'];

	include $engine_dir.'/_manage/config/sms_config.php';

?>