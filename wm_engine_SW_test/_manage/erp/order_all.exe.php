<?PHP

	$accept = $_POST['accept'];
	$order_date = addslashes($_POST['order_date']);
	$total_qty = numberOnly($_POST['total_qty']);
	$total_amt = numberOnly($_POST['total_amt']);
	$pno = numberOnly($_POST['pno']);
	$order_qty = numberOnly($_POST['order_qty']);
	$order_target_qty = numberOnly($_POST['order_target_qty']);
	$order_price = parsePrice($_POST['order_price']);
	$sno = numberOnly($_POST['sno']);
	$remark = $_POST['remark'];

	if(count($accept) < 1) msg('발주 할 상품을 하나 이상 선택해 주세요.');

    $pdo->query("start transaction");

	$pdo->query(
		"insert into erp_order(order_date, sno, order_stat, total_qty, total_amt, reg_user, reg_date, remote_ip)" .
		" values ('{$order_date}', 0, '1', '{$total_qty}', '{$total_amt}', '{$admin[admin_id]}', now(), '{$_SERVER[REMOTE_ADDR]}')"
	);
	$ono = $pdo->lastInsertId();
    $suffix = sprintf('%03d', $pdo->row("select count(*) from erp_order where order_date='$order_date'"));
    $order_no = str_replace("-", "", $order_date).'-'.$suffix;

	$pdo->query("update erp_order set order_no = '{$order_no}' where ono = {$ono}");
	$i = $total_qty = $total_amt = 0;
	foreach($pno as $v) {
		if(in_array($v, $accept) == false) {
			$i++;
			continue;
		}
		if(!$sno[$i]) continue;
		if($order_qty[$i] > 0) {
			if($sql1) {
				$sql1 .= ",";
			}
			$remark[$i] = addslashes(trim($remark[$i]));
			$sql1 .= "('{$order_no}', {$sno[$i]}, {$v}, {$order_target_qty[$i]}, {$order_qty[$i]}, {$order_price[$i]}, '{$remark[$i]}')";
		}

		$total_qty += $order_qty[$i];
		$total_amt += ($order_price[$i] * $order_qty[$i]);
		$i++;
	}

	if(!$i) {
        $pdo->query("rollback");
        msg('처리 된 내역이 없습니다.');
    }

	$pdo->query("insert into erp_order_dtl (order_no, sno, complex_no, order_target_qty, order_qty, order_price, remark) values " . $sql1);
	$pdo->query("update erp_order set total_qty='$total_qty', total_amt='$total_amt' where ono='$ono'");

    $pdo->query("commit");

	msg('발주 처리되었습니다.', '?body=erp@order_detail&ono='.$order_no, 'parent');

?>