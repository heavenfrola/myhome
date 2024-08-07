<?PHP

	$_POST['alimtalk_id'] = addslashes(trim($_GET['id']));
	$_POST['alimtalk_profile_key'] = addslashes(trim($_GET['profile_key']));

	if(!$_POST['alimtalk_id'] || !$_POST['alimtalk_profile_key']) {
		exit('standby');
	}

	if($cfg['alimtalk_id']) {
		exit('already exists');
	}

	$no_reload_config = true;
	include $engine_dir.'/_engine/include/common.lib.php';

	$wec = new weagleEyeClient($_we, 'account');
	$keycode = $wec->call('getKeyCodeByAPI');
	if($keycode != $_GET['keycode']) {
		exit('Wrong keycode');
	}

	include $engine_dir.'/_manage/config/config.exe.php';

	exit('OK');

?>