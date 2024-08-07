<?PHP

	set_time_limit(0);

	header('Content-type:text/html; charset=utf-8;');

	if(defined('_wisa_set_included') == false) {
		chdir(dirname($_SERVER['SCRIPT_NAME']));
		chdir('..');
		include '_config/set.php';
	}

	$ps = shell_exec('ps -ef | grep dooson_off.php');
	if(substr_count($ps, 'cron/dooson_off.php') > 1) exit;

	$urlfix = 'Y';
	include '../../../_config/set.php';
	include_once $engine_dir.'/_engine/include/common.lib.php';

	$_SESSION['admin'] = array(
		'admin_id' => 'dooson_api',
	);

	echo "<div>6. get off orders</div>";
	$erpListener->getOrders();

	exit('Clon End');

?>
