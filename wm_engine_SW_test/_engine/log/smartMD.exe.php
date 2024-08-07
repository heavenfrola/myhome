<?PHP

	include_once $engine_dir.'/_engine/include/common.lib.php';
	include_once $engine_dir.'/_engine/include/file.lib.php';

	// log
	foreach($_GET as $key => $val) {
		$log .= "[GET] $key => $val\n";
	}
	foreach($_POST as $key => $val) {
		$log .= "[POST] $key => $val\n";
	}
	foreach($_SERVER as $key => $val) {
		$log .= "[SERVER] $key => $val\n";
	}

	$fp = fopen($root_dir.'/_data/smartMD_'.$now.'.txt', 'w');
	fwrite($fp, $log);
	fclose($fp);

	$_POST['logger_smartMD_id'] = $_GET['cusId'];
	$_POST['logger_smartMD_sid'] = $_GET['id'];

	ob_start();
	$no_reload_config = true;
	include $engine_dir.'/_manage/config/config.exe.php';
	ob_end_clean();

	$wec = new WeagleEyeClient($config['WEAGLE'], 'logger');
	$r = $wec->call('setSmartMD', array('cusId'=>$_GET['cusId'], 'id'=>$_GET['id']));

	msg('', $root_url.'/_manage/?body=log@smartMD');

?>