<?PHP

	define('__CRON_SCRIPT__', true);

	chdir(dirname(__FILE__));
	$urlfix = 'Y';

	include_once '../../../_config/set.php';
	include_once $engine_dir.'/_engine/include/common.lib.php';

	include $engine_dir.'/_manage/main/bankingorder.exe.php';

	exit;

?>