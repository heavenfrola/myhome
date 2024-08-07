<?PHP

	if(is_array($_apps_n) && in_array(1, $_apps_n)) {
		$inc_path = $engine_dir.'/_plugin/quickCart/manage_design_quickCart.php';
		if(file_exists($inc_path)) {
			include $engine_dir.'/_plugin/quickCart/manage_design_quickCart.php';
		}
	}
?>