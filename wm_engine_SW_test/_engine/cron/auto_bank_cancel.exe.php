<?PHP

	define('__bank_cancel_cron__', true);

	include_once $engine_dir.'/_engine/include/common.lib.php';

	if(!$_REQUEST['key_code'] || $wec->config['wm_key_code'] != $_REQUEST['key_code']) {
		exit('Wrong key code');
	}

	require_once $engine_dir.'/_manage/main/bankingorder.exe.php';

	echo $cancel_cnt;
	exit;

?>