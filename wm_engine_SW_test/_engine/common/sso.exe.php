<?PHP

	if($_GET['ssl_test'] == 'Y') {
		$urlfix = 'Y';
		include_once $engine_dir.'/_engine/include/common.lib.php';

		if($_SERVER['HTTP_X_FORWARDED_PORT'] == '443') {
			$_SERVER['HTTPS'] = 'on';
		}

		header('Content-type:application/json');
		exit('callback('.json_encode(array(
			'url' => getURL(),
			'https' => $_SERVER['HTTPS']
		)).')');
	}

	if(file_exists($engine_dir.'/_engine/include/account/ssl.inc.php')) {
		include_once $engine_dir.'/_engine/include/account/ssl.inc.php';
	}

?>