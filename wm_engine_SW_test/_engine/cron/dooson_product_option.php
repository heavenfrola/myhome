<?PHP

	set_time_limit(0);

	header('Content-type:text/html; charset=utf-8;');

	if(defined('_wisa_set_included') == false) {
		chdir(dirname($_SERVER['SCRIPT_NAME']));
		chdir('..');
		include '_config/set.php';
	}

	$ps = shell_exec('ps -ef | grep "dooson_product_option.php '.$cfg['dooson_corp_id'].'"');
	if(substr_count($ps, 'cron/dooson_product_option.php '.$cfg['dooson_corp_id']) > 1) exit;

	$urlfix = 'Y';
	$no_qcheck = true;
	include '../../../_config/set.php';
	include_once $engine_dir.'/_engine/include/common.lib.php';

	$_SESSION['admin'] = array(
		'admin_id' => 'dooson_api',
	);

	$erpListener->getProductOption();

	$fp = fopen($root_dir.'/_data/crontest.txt', 'a+');
	fwrite($fp, date('Y-m-d H:i:s')."\n");
	fclose($fp);

	exit('Clon End');

?>