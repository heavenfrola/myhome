<?PHP

	include_once $engine_dir.'/_engine/include/common.lib.php';
	include_once $engine_dir.'/_engine/include/file.lib.php';

	foreach($_GET as $key => $val) {
		$log .= "[GET] $key => $val\n";
	}
	foreach($_POST as $key => $val) {
		$log .= "[POST] $key => $val\n";
	}

	$fp = fopen($root_dir.'/_data/acecounter_'.$now.'_'.rand(1000,9999).'.txt', 'w');
	fwrite($fp, $log);
	fclose($fp);

	$query = trim($_POST['q']);

	if(!$query) {
		exit('ACE03');
	}

	$data = json_decode(base64_decode($query));
	$data = $data->DATA;
	$data->ACE_NAME = iconv('utf-8', _BASE_CHARSET_, $data->ACE_NAME);

	if($data->ACE_VERS == 'M') {
		$_POST['ace_counter_gcode_m'] = $data->ACE_CODE;
	} else {
		$_POST['ace_counter_gcode'] = $data->ACE_CODE;
	}
	$_POST['ace_counter_id'] = $data->ACE_ID;
	$_POST['ace_counter_pwd'] = 'wisa123';
	$_POST['ace_counter_Ver'] = '2';

	ob_start();
	$no_reload_config=1;
	include $engine_dir."/_manage/config/config.exe.php";
	ob_end_clean();

	exit('ACE00');

?>