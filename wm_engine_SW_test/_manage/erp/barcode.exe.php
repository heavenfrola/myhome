<?PHP

	include_once $engine_dir.'/_manage/erp/pear_barcode/Barcode.php';
	$bar = new wingBarcode;
	$bar->draw($_GET['text'], 'code128', 'png');

?>