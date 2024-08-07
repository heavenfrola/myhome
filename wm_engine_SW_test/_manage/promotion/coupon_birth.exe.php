<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  생일자 자동쿠폰 설정 처리
	' +----------------------------------------------------------------------------------------------+*/

	if($_POST['auto_birth_cpn_type'] == 1 && $_POST['auto_birth_cpn_date1'] < 0) msg('발급기간을 0일 이상으로 입력해주세요.');
	if($_POST['auto_birth_cpn_type'] == 2 && $_POST['auto_birth_cpn_date2'] < 1) msg('발급일을 1일 이상으로 입력해주세요.');
	$_POST['auto_birth_cpn_date'] = addslashes($_POST['auto_birth_cpn_date'.$_POST['auto_birth_cpn_type']]);

	$cpn_use = addslashes($_POST['auto_birtn_cpn_use']);
	$cpn_time = numberOnly($_POST['auto_birth_cpn_time']);

	$wec_acc = new weagleEyeClient($_we, 'account');
	$wec_acc->call('setbirthCpn', array('use'=>$cpn_use, 'time'=>$cpn_time));
	if($wec_acc->error) {
		alert(php2java($wec_acc->error));
		exit;
	}

	$use_check = ($_POST['auto_birtn_cpn_sms'] == 'Y') ? 'Y' : 'N';

	if($pdo->row("select count(*) from $tbl[sms_case] where `case`=16") > 0) {
		$pdo->query("update `$tbl[sms_case]` set `use_check`='$use_check' where `case`='16'");
	} else {
		if($use_check == 'Y') {
			include_once $engine_dir.'/_engine/sms/sms_module.php';
			$pdo->query("insert into `$tbl[sms_case]` (`case`,`msg`,`use_check`) values ('16','$sms_def_msg[16]','$use_check')");
		}
	}

	include $engine_dir.'/_manage/config/config.exe.php';

?>