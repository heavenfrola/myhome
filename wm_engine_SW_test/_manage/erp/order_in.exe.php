<?PHP

	$i = 0;
	$input_array = array();
	$pno = numberOnly($_POST['pno']);
	$in_qty = numberOnly($_POST['in_qty']);
	$order_dtl_no = numberOnly($_POST['order_dtl_no']);
	$exec = $_POST['exec'];
	$sno = numberOnly($_POST['sno']);
	$sno2 = numberOnly($_POST['sno2']);
	$in_price = numberOnly($_POST['in_price'], true);
	$remark = $_POST['remark'];
	$ono = $_POST['ono'];

	foreach($pno as $v) {
		if($in_qty[$i] > 0) {
			$order_dtl = $pdo->assoc("
							select order_no, complex_no, sno, order_qty,
							ifnull((select sum(qty) from erp_inout x where inout_kind = 'I' and x.order_dtl_no = a.order_dtl_no and x.complex_no = a.complex_no),0) as in_comp_qty
							from erp_order_dtl a where order_dtl_no = {$order_dtl_no[$i]}
						");

			$order_qty = $order_dtl['order_qty'];
			$in_comp_qty = $order_dtl['in_comp_qty'];

			if($in_qty[$i] > $order_qty - $in_comp_qty && $exec != 'in') { // 발주 가능 수량을 초과한 입고
				msg('발주수량을 초과한 입고건이 있습니다.\\n입고수량을 다시 확인 해 주십시오.\\n\\n발주수량을 초과한 입고가 필요할 경우 발주서를 수정해 주십시오.');
			}

			if($sql1) $sql1 .= ",";
			$_sno = $sno ? $sno : $sno2[$i];
			$remark[$i] = addslashes(trim($remark[$i]));
			$sql1 .= "({$v}, 'I', {$in_qty[$i]}, '{$remark[$i]}', '{$admin[admin_id]}', now(), '{$_SERVER[REMOTE_ADDR]}', '{$_sno}', '{$in_price[$i]}', '{$order_dtl_no[$i]}')";

			$input_array[$v] += $in_qty[$i];
		}
		$i++;
	}

	$pdo->query("insert into erp_inout (complex_no, inout_kind, qty, remark, reg_user, reg_date, remote_ip, sno, in_price, order_dtl_no) values " . $sql1);

	// 입고시 배송지연 해제
	foreach($input_array as $complex_no => $ea) {
		$pdo->query("update erp_complex_option set qty=curr_stock($complex_no) where complex_no='$complex_no'");
		resolveHold($complex_no, $ea);

		// 재입고 알림
		sendNotifyRestockSMS($complex_no);
	}

	$sql = "update erp_order set order_stat = if(".
		   "   (select sum(b.qty) from erp_order_dtl a inner join erp_inout b".
		   "      on a.order_dtl_no = b.order_dtl_no".
		   "      and b.inout_kind = 'I'".
		   "      where order_no = '{$ono}'".
		   "      ) < total_qty, 2, 3)".
		   "   where order_no = '{$ono}'";

	$pdo->query($sql);
	msg('입고 처리되었습니다.', '?body=erp@in_list', 'parent');

?>