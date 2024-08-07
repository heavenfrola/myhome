<?PHP

	$pno = numberOnly($_POST['pno']);
	$filetype = numberOnly($_POST['filetype']);
	$ea = $pdo->row("select count(*) from `$tbl[product_image]` where `pno` = '$pno' and `filetype`='$filetype'");
	include_once $engine_dir.'/_manage/product/product_wdisk.exe.php';

?>