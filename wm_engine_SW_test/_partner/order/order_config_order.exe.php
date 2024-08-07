<?PHP

	if(!isTable($tbl['partner_config'])) {
		include_once $engine_dir."/_config/tbl_schema.php";
		$pdo->query($tbl_schema['partner_config']);
	}

	$partner_order = "partner_order";
	include $engine_dir.'/_manage/order/order_config_order.exe.php';

?>