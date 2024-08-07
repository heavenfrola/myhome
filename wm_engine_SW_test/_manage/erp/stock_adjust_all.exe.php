<?PHP

    set_time_limit(0);
    ini_set('memory_limit', -1);

	require_once 'excel/reader.php';
	$data = new Spreadsheet_Excel_Reader();
	$data->setUTFEncoder('mb');
	$data->setOutputEncoding(_BASE_CHARSET_);
	$data->read($_FILES['xls']['tmp_name']);

	if($data->sheets[0]['numCols'] != 17) {
		msg('엑셀파일 양식이 맞지 않습니다. 다시한번 확인하신 후 업로드 해주세요.', '', 'parent');
	}

	if($data->sheets[0]['numRows'] <= 1) {
		msg('데이터가 없습니다. 다시한번 확인하신 후 올려주세요.', '', 'parent');
	}

	$cnt = 0;
	$remote_ip = $_SERVER['REMOTE_ADDR'];
    $notify_restock_array = array();
	for($i = 2; $i <= $data->sheets[0]['numRows']; $i++) {
		$complex_no = $data->sheets[0]['cells'][$i][1];
		$org_stock_qty = $pdo->row("select curr_stock($complex_no)");
		$stock_qty = preg_replace('/[^-0-9]/', '', $data->sheets[0]['cells'][$i][8]);
		$adjust_reason = addslashes($data->sheets[0]['cells'][$i][9]);
		$barcode = $data->sheets[0]['cells'][$i][5];
		$force_soldout = array_search($data->sheets[0]['cells'][$i][6], $_erp_force_stat);
		if(!$force_soldout) $force_soldout = 'N';

		if($admin['level'] == 4) { // 권한이 없는 파트너가 수정 불가
			$_partner_no = $pdo->row("select partner_no from $tbl[product] p inner join erp_complex_option e on p.no=e.pno where e.complex_no='$complex_no'");
			if($_partner_no != $admin['partner_no']) {
				continue;
			}
		}

		if($complex_no && $stock_qty != '' && $adjust_reason && $org_stock_qty != $stock_qty) {
			$gap = $stock_qty-$org_stock_qty;
			if($gap > 0) {
				$inout_kind = "U";
				resolveHold($complex_no, $gap);
			} else {
				$inout_kind = "P";
				$gap = - $gap;
				setOutputToHold($complex_no, $gap);
			}
			$pdo->query("insert into erp_inout (complex_no, inout_kind, qty, remark, reg_user, reg_date, remote_ip) values ({$complex_no}, '{$inout_kind}', $gap, '재고조정({$adjust_reason})', '{$admin[admin_id]}', now(), '{$remote_ip}')");
		}
		$pdo->query("update erp_complex_option set barcode='$barcode', force_soldout='$force_soldout', sku='$barcode', qty=curr_stock(complex_no) where complex_no='$complex_no'");
		if($pdo->lastRowCount() > 0) {
            $cnt++;

			// 재입고 알림용 complex_no
			$notify_restock_array[] = $complex_no;
        }
	}

	// 재입고 알림
    array_unique($notify_restock_array);
	for($ii = 0; $ii < count($notify_restock_array); $ii++) {
		sendNotifyRestockSMS($notify_restock_array[$ii]);
	}

	msg("총 {$cnt}건의 재고조정이 완료되었습니다.", 'reload', 'parent');

?>