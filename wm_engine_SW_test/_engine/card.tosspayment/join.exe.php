<?PHP

	include_once $engine_dir.'/_engine/include/common.lib.php';

	$site_keycode = $wec->config['wm_key_code'];
	if(empty($_GET['keycode']) == true) {
		exit('Keycode not set');
	}
	if($_GET['keycode'] != $site_keycode){
		exit('Wrong keycode');
	}

	$_POST['use_tosscard'] = 'Y';
	$_POST['tossc_mid'] = trim($_GET['mid']);
	$_POST['tossc_liveApiKey'] = trim($_GET['liveApiKey']);

	// 기존 토스 머니 설정 해제
	if($cfg['use_tosspayment'] == 'Y') {
		$_POST['use_tosspayment'] = 'N';
	}

	$no_reload_config = true;
	include_once $engine_dir.'/_manage/config/config.exe.php';

	exit('OK');

?>