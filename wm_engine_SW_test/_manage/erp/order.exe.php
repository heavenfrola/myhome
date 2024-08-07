<?PHP

	$order_date = addslashes($_POST['order_date']);
	$sno = numberOnly($_POST['sno']);
	$pno = numberOnly($_POST['pno']);
	$accept = $_POST['accept'];
	$order_target_qty = numberOnly($_POST['order_target_qty']);
	$order_qty = numberOnly($_POST['order_qty']);
	$order_price = parsePrice($_POST['order_price']);
	$remark = $_POST['remark'];

	$pdo->query("
		insert into erp_order(order_date, sno, order_stat, reg_user, reg_date, remote_ip)
		values ('{$order_date}', {$sno}, '1', '{$admin[admin_id]}', now(), '{$_SERVER[REMOTE_ADDR]}')
    ");

	$ono = $pdo->lastInsertId();
    $suffix = sprintf('%03d', $pdo->row("select count(*) from erp_order where order_date='$order_date'"));
	$order_no = str_replace("-", "", $order_date).'-'.$suffix;

	$pdo->query("update erp_order set order_no = '{$order_no}' where ono='$ono'");

	$i = $complete = $total_qty = $total_qty = 0;
	foreach($pno as $v) {
		if(in_array($v, $accept) == false) {
			$i++;
			continue;
		}

		if($order_qty[$i] > 0) {
			if($sql1) {
				$sql1 .= ',';
			}
			$remark[$i] = addslashes($remark[$i]);
			$sql1 .= "('{$order_no}', {$sno}, {$v}, {$order_target_qty[$i]}, {$order_qty[$i]}, '{$order_price[$i]}', '{$remark[$i]}')";
			$complete++;

            $total_qty += $order_qty[$i];
            $total_amt += ($order_price[$i]*$order_qty[$i]);
		}
		$i++;
	}

	if($complete < 1) {
		$pdo->query("delete from erp_order where ono='$ono'");
		msg('발주 할 내역이 없습니다.\\n발주수량 및 체크 여부를 확인 해 주세요.');
	}

	$pdo->query("insert into erp_order_dtl (order_no, sno, complex_no, order_target_qty, order_qty, order_price, remark) values ".$sql1);
    $pdo->query("update erp_order set total_qty=?, total_amt=? where ono=?", array(
        $total_qty, $total_amt, $ono
    ));

	msg('발주 처리되었습니다.', '?body=erp@order_detail&ono='.$order_no, 'parent');

?>