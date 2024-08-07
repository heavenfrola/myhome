<?PHP

	// 파트너 권한 체크
	$pno = $_POST['pno'];
	foreach($pno as $tmp_no) {
		$tmp_no = numberOnly($tmp_no);
		$tmp = $pdo->assoc("select p.partner_no, o.stat from $tbl[order_product] o inner join $tbl[product] p on o.pno=p.no where o.no='$tmp_no'");

		if($tmp['partner_no'] != $admin['partner_no']) msg('선택한 상품에 대한 수정권한이 없습니다.');
	}

	include $engine_dir.'/_manage/order/order_prd_stat.exe.php';

?>