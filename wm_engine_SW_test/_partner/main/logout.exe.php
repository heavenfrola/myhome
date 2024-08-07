<?PHP

	if($_SESSION['partner_login_no'] > 0) {
		unset($_SESSION['partner_login_no']);
		msg('', '?body=product@product_join_shop');
	}

	include $engine_dir.'/_manage/main/logout.exe.php';

?>