<?PHP

	$remote_ip = $_SERVER['REMOTE_ADDR'];
	$ono = implode("','", $_POST['check_ono']);
	$pdo->query("update erp_order set order_stat = 5 where order_no in ('$ono')");

	msg('발주취소 처리가 완료되었습니다.', 'reload', 'parent');

?>