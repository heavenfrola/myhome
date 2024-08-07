<?PHP

	$GLOBALS['no_qcheck'] == true;

	set_time_limit(0);

	define('__CRON_SCRIPT__', true);

	chdir(dirname(__FILE__));
	$urlfix = 'Y';

	include '../../../_config/set.php';
	include_once $engine_dir.'/_engine/include/common.lib.php';


	$urlfix = 'Y';
	$no_qcheck = true;

	include '../../../_config/set.php';
	include_once $engine_dir.'/_engine/include/common.lib.php';

	$date2 = strtotime(date('Y-m-d', strtotime('-12 months', $now)));
	$date3 = $date2 + 86399;

	$year = date('Y', $now);

    $today_md = date('m-d');
    $today_time = strtotime(date('Y-m-d'));
    $w = "and reg_date < '$today_time' and FROM_UNIXTIME(reg_date, '%m-%d') = '$today_md'";

	$res = $pdo->iterator("select * from $tbl[member] where withdraw = 'N' $w");

    foreach ($res as $data) {
		if($_REQUEST['email_use'] == "Y") {
			$_mstr['회원이름'] = $data['name'];
			include $engine_dir.'/_engine/include/mail.lib.php';
			sendMailContent(22, $data['name'], $data['email']);
		}

		if($_REQUEST['sms_use'] == "Y") {
			include_once $engine_dir."/_engine/sms/sms_module.php";
			$sms_replace['name'] = $data['name'];
			SMS_send_case(31, $data['cell']);
		}
		
	}
	exit();

?>