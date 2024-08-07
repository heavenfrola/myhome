<?PHP

	$no = numberOnly($_POST['no']);
	$pno = numberOnly($_POST['pno']);
	$sort = 1;
	foreach($no as $val) {
		$pdo->query("update $tbl[product_image] set sort='$sort' where pno='$pno' and no='$val'");
		$sort++;
	}
	exit('OK');

?>