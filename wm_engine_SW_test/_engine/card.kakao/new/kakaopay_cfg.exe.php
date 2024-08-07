<?PHP

    require_once $engine_dir.'/_engine/include/common.lib.php';

	$_POST['kakao_cid'] = addslashes(trim($_GET['cid']));
	$_POST['kaka_admin_key'] = addslashes(trim($_GET['adminkey']));
	$_POST['kakao_version'] = "new";

	if(!$_POST['kakao_cid'] || !$_POST['kaka_admin_key']) {
		msg('error');
	}

	if($cfg['kakao_cid']) {
		msg('already exists');
	}

	$no_reload_config = true;

	$wec = new weagleEyeClient($_we, 'account');
	$keycode = $wec->call('getKeyCodeByAPI');
	if($keycode != $_GET['keycode']) {
		msg('Wrong keycode');
	}

	include $engine_dir.'/_manage/config/config.exe.php';

	exit('OK');

?>