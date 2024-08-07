<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  윙Mobile 생성 처리
	' +----------------------------------------------------------------------------------------------+*/
	if(file_exists($engine_dir.'/_engine/include/account/setWMB.inc.php')) {
		include_once $engine_dir.'/_engine/include/account/setWMB.inc.php';
	} else {
		unset($_POST);
		$_POST['mobile_use'] = 'Y';
		include $engine_dir.'/_manage/config/config.exe.php';
	}

?>