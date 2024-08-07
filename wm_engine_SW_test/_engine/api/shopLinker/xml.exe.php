<?PHP

	include_once $engine_dir.'/_engine/include/common.lib.php';
	include $engine_dir.'/_engine/api/shopLinker/shopLinker.class.php';
	include $engine_dir.'/_engine/api/shopLinker/shopLinkerProduct.class.php';
	include $engine_dir.'/_engine/api/shopLinker/shopLinkerOrder.class.php';

	$classname = 'shopLinker'.ucfirst($_GET['type']);

	$shoplinker = new $classname();
	$shoplinker->{$_REQUEST['method'].'Xml'}($_GET['no']);

?>