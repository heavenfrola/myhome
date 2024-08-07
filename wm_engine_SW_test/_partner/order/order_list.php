<?PHP

	if(!isTable($tbl['partner_config'])) {
		include_once $engine_dir."/_config/tbl_schema.php";
		$pdo->query($tbl_schema['partner_config']);
	}

	$cfgs = $pdo->iterator("select name, value from $tbl[partner_config] where partner_no='$admin[partner_no]'");
    foreach ($cfgs as $cfgdata) {
		$cfg[$cfgdata['name']] = stripslashes($cfgdata['value']);
	}

	$_order_color = setOrderColor();

	include $engine_dir.'/_manage/order/order_list.php';

?>