<?PHP

	// 스케쥴러 테이블 생성
	if($_POST['use_log_scheduler'] == 'Y') {
		if(isTable($tbl['log_schedule']) == false) {
			include_once $engine_dir.'/_config/tbl_schema.php';
			$pdo->query($tbl_schema['log_schedule']);
		}
	} else {
		require_once $engine_dir.'/_engine/cron/cron_log_scheduler.exe.php';
	}

	// 크론 서버 등록
	if($_POST['use_log_scheduler'] == 'Y' || ($_POST['use_log_scheduler'] == 'N' && $cfg['use_log_scheduler'] == 'Y')) {
		$wec_acc = new weagleEyeClient($_we, 'Etc');
		$npay = $wec_acc->call('setLogSchedulerCron', array(
			'use_yn' => $_POST['use_log_scheduler'],
			'root_url' => $root_url,
		));
	}

	include $engine_dir.'/_manage/config/config.exe.php';

?>