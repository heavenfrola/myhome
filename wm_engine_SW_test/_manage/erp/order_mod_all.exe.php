<?PHP

	$layer1 = 'field_btn';
	$layer2 = 'field_wait';

	require_once 'excel/reader.php';
	$data = new Spreadsheet_Excel_Reader();
	$data->setUTFEncoder('mb');
	$data->setOutputEncoding(_BASE_CHARSET_);
	$data->read($_FILES['xls']['tmp_name']);

	if($data->sheets[0]['numCols'] != 15) {
		msg('엑셀파일 양식이 맞지 않습니다. 다시한번 확인하신 후 올려주세요.', '', 'parent');
		exit;
	}

	if($data->sheets[0]['numRows'] <= 1) {
		msg('데이터가 없습니다. 다시한번 확인하신 후 올려주세요.', '', 'parent');
		exit;
	}

	$cnt = 0;
	$err = 0;
	for ($i = 2; $i <= $data->sheets[0]['numRows']; $i++) {
		$order = $data->sheets[0]['cells'][$i];

		$order_dtl_no = $order[1];
		$order_qty = numberOnly($order[9]);
		$in_eps = numberOnly($order[11]);
		$order_price = parsePrice($order[12]);
		$remark = addslashes($order[14]);

		$org = $pdo->assoc("
					select order_qty, order_price, ifnull(sum(qty), 0) as in_qty
					from erp_order_dtl a left join erp_inout b on a.order_dtl_no = b.order_dtl_no and inout_kind = 'I'
					where a.order_dtl_no='$order_dtl_no' group by a.order_dtl_no
				");
		if($order_qty != $org['order_qty'] || $order_price != $org['order_price']) {
			if(($org['in_qty']+$in_eps) > $order_qty) {
				$err++;
			} else {
				$pdo->query("update erp_order_dtl set order_qty='$order_qty', order_price='$order_price', remark='$remkar' where order_dtl_no='$order_dtl_no'");
				$cnt++;
			}
		}
	}

	// 총 발주량 및 발주서 상태 수정
	$pdo->query("update erp_order set total_qty = (select sum(order_qty) from erp_order_dtl where order_no='$order_no') where order_no='$order_no'");
	$pdo->query("
		update erp_order set order_stat = if((select sum(b.qty) from erp_order_dtl a inner join erp_inout b on a.order_dtl_no=b.order_dtl_no and b.inout_kind = 'I' where order_no='{$order_no}') < total_qty, 2, 3)
		where order_no='{$order_no}'
	");

	if($cnt < 1) msg('처리된 발주내용이 없습니다.');
	else if($err > 0) msg("$err 건이 처리되지 않았습니다.\\n실 입고량보다 발주량이 작은 내역이 없는지 확인 해 주십시오.\\n입고량은 엑셀상의 입고가 아닌 윙POS 상의 실 입고량을 의미합니다.");
	else msg("$cnt 건의 발주가 수정 완료되었습니다.", 'reload', 'parent');

?>