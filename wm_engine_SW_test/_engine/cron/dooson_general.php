<?PHP

	set_time_limit(0);

	header('Content-type:text/html; charset=utf-8;');

	if(defined('_wisa_set_included') == false) {
		chdir(dirname($_SERVER['SCRIPT_NAME']));
		chdir('..');
		include '_config/set.php';
	}

	$ps = shell_exec('ps -ef | grep "dooson_general.php '.$cfg['dooson_corp_id'].'"');
	if(substr_count($ps, 'cron/dooson_general.php') > 1) exit;

	$urlfix = 'Y';
	include_once $engine_dir.'/_engine/include/common.lib.php';

	$_SESSION['admin'] = array(
		'admin_id' => 'dooson_api',
	);

	echo "<div>0. get SKU hidden</div>";
	$erpListener->getSKU();

	echo "<div>1. Member (SKIP)</div>";
	//$erpListener->setChangedMember();

	echo "<div>2. Milage</div>";
	$erpListener->getMilage('milage');

	echo "<div>3. Emoney</div>";
	$erpListener->getMilage('emoney');

	echo "<div>4. get Delivery</div>";
	$erpListener->getDelivery();
	$erpListener->getStock();

	exit('Clon End');

?>
