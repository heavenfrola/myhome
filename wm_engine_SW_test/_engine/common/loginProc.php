<?PHP

	if(file_exists($engine_dir.'/_engine/include/account/checkToken.inc.php')) {
		include_once $engine_dir.'/_engine/include/account/checkToken.inc.php';
	} else {
		exit('직접 로그인 해 주세요.');
	}

?>