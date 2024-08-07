<?PHP

	include $engine_dir.'/_engine/include/common.lib.php';

	// check
	$refer = preg_replace('/^https?:\/\/|\/.*$/', '', $_SERVER['HTTP_REFERER']);

	if(!$wec->config['wm_key_code']) msg('인증되지 않은 사이트입니다');
	if(!$_POST['key_code']) msg('키코드가없습니다');
	if($wec->config['wm_key_code'] != $_POST['key_code']) msg('키코드가 일치하지 않습니다');
	if($refer != 'wep.wisa.co.kr' && $refer != 'wep.dev.wisa.re.kr') msg('인증되지 않은 접속입니다');

	// Save
	$no_reload_config = 1;
	if(!$config_exec) $config_exec = 'config.exe.php';
	include $engine_dir.'/_manage/config/'.$config_exec;

	msg('설정이 저장되었습니다');

?>