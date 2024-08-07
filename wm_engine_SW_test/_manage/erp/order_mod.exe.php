<?PHP

	if($_GET['exec'] == 'remove') {
		printAjaxHeader();

		$order_dtl_no = numberOnly($_GET['order_dtl_no']);
		$in_qty = $pdo->row("select count(*) from erp_inout where order_dtl_no='$order_dtl_no'");
		if($in_qty > 0) {
			exit('현재 발주서를 통해 일부 또는 전체가 입고 된 상품은 삭제할 수 없습니다.');
		}

		$pdo->query("delete from erp_order_dtl where order_dtl_no='$order_dtl_no'");
		exit('OK');
	}

	$order_no = addslashes(trim($_POST['order_no']));
	$order_qty = numberOnly($_POST['order_qty']);
	$order_qty_org = numberOnly($_POST['order_qty_org']);
	$order_price = parsePrice($_POST['order_price']);
	$order_price_org = numberOnly($_POST['order_price_org']);
	$order_dtl_no = numberOnly($_POST['order_dtl_no']);
	$remark = $_POST['remark'];

	$process = 0;
	foreach($order_qty as $idx => $val) {
		if($order_qty[$idx] != $order_qty_org[$idx] || $order_price[$idx] != $order_price_org[$idx]) {
			$remark[$idx] = addslashes(trim($remark[$idx]));
			$pdo->query("update erp_order_dtl set order_qty='$val', order_price='$order_price[$idx]', remark='$remark[$idx]' where order_dtl_no='$order_dtl_no[$idx]'");
			$process++;
		}
	}

	$pdo->query("update erp_order set total_qty = (select sum(order_qty) from erp_order_dtl where order_no='$order_no') where order_no='$order_no'");

	// 총 발주량 및 발주서 상태 수정
	$order_stat = $pdo->row("select order_stat from erp_order where order_no='$order_no'");
	if($order_stat > 1) {
		$pdo->query("update erp_order set total_qty = (select sum(order_qty) from erp_order_dtl where order_no='$order_no') where order_no='$order_no'");
		$sql = "update erp_order set order_stat = if(".
			   "   (select sum(b.qty) from erp_order_dtl a inner join erp_inout b".
			   "      on a.order_dtl_no = b.order_dtl_no".
			   "      and b.inout_kind = 'I'".
			   "      where order_no = '{$order_no}'".
			   "      ) < total_qty, 2, 3)".
			   "   where order_no = '{$order_no}'";
		$pdo->query($sql);
	}

	msg('발주서 수정이 완료되었습니다.', 'reload', 'parent');

?>