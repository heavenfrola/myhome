<?PHP

	include_once $engine_dir.'/_engine/include/common.lib.php';

	printAjaxHeader();

	$_tmp_file_name = 'shop_detail_prdcpn.php';
	$striplayout = $_GET['striplayout'] = 1;
	$cart_selected = $_GET['cart_selected'];

	include_once $engine_dir."/_engine/common/skin_index.php";

?>