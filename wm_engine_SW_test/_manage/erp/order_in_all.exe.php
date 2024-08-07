<?PHP

	require_once 'excel/reader.php';
	$data = new Spreadsheet_Excel_Reader();
	$data->setUTFEncoder('mb');
	$data->setOutputEncoding(_BASE_CHARSET_);
	$data->read($_FILES['xls']['tmp_name']);

	if($data->sheets[0]['numCols'] != 15) {
		msg('엑셀파일 양식이 맞지 않습니다. 다시한번 확인하신 후 업로드 해주세요.', '', 'parent');
	}

	if($data->sheets[0]['numRows'] <= 1) {
		msg('데이터가 없습니다. 다시한번 확인하신 후 업로드 해주세요.', '', 'parent');
		exit;
	}
	$input_log = "[".date('Y-m-d H:i:s', $now)."] 엑셀 입고 시작\n\n";
	$over = array();
	$onos = array();
	$input_array = array();
	$cnt = 0;
	$total_cnt = 0;
	$remote_ip = $_SERVER[REMOTE_ADDR];
	for ($i = 2; $i <= $data->sheets[0]['numRows']; $i++) {
		$order_dtl_no = $data->sheets[0]['cells'][$i][1];
		$in_qty = $data->sheets[0]['cells'][$i][11];
		$in_price = $data->sheets[0]['cells'][$i][12];
		$remark = $data->sheets[0]['cells'][$i][13];

		if(!$in_qty || !$order_dtl_no) continue;

		$total_cnt++;

		$order_dtl = $pdo->assoc("
						select order_no, complex_no, sno, order_qty,
						ifnull((select sum(qty) from erp_inout x where inout_kind = 'I' and x.order_dtl_no = a.order_dtl_no and x.complex_no = a.complex_no),0) as in_comp_qty
						from erp_order_dtl a where order_dtl_no = {$order_dtl_no}
					");
		$order_qty = $order_dtl['order_qty'];
		$in_comp_qty = $order_dtl['in_comp_qty'];

		$input_log .= "■ $order_dtl_no -  입고수량($in_qty) > 발주수량($order_qty) - 기입고수량($in_comp_qty)\n";

		if($in_qty > $order_qty - $in_comp_qty) { // 발주 가능 수량을 초과한 입고
			$over[] = $order_dtl_no;
			$input_log .= "     -> 초과 입고 ($order_dtl_no)\n";
			continue;
		}

		$input_log .= "     -> 입고성공\n";

		$complex_no = $order_dtl['complex_no'];
		$sno = $order_dtl['sno'];
		$order_no = $order_dtl['order_no'];

		$onos[] = $order_no;
		if($order_dtl_no && $complex_no && $in_qty && $in_price && $in_qty > 0) {
			if($sql1) {
				$sql1 .= ",";
			}
			$in_price = numberOnly($in_price);
			$sql1 .= "({$complex_no}, 'I', {$in_qty}, '{$remark}', '{$admin[admin_id]}', now(), '{$_SERVER[REMOTE_ADDR]}', {$sno}, {$in_price}, {$order_dtl_no})";
			$cnt++;

			$input_array[$complex_no] += $in_qty;
		}
	}

	$fp = fopen($root_dir."/_data/erp_log_{$now}.txt", 'w');
	fwrite($fp, $input_log);
	fclose($fp);

	$over_cnt = count($over);
	if($cnt > 0) {
		$pdo->query("insert into erp_inout (complex_no, inout_kind, qty, remark, reg_user, reg_date, remote_ip, sno, in_price, order_dtl_no) values " . $sql1);

		// 입고시 배송지연 해제
		foreach($input_array as $complex_no => $ea) {
			$pdo->query("update erp_complex_option set qty=curr_stock($complex_no) where complex_no='$complex_no'");
			resolveHold($complex_no, $ea);

			// 재입고 알림
			sendNotifyRestockSMS($complex_no);
		}

		$onos = array_unique($onos);
		foreach($onos as $order_no) {
			$sql = "update erp_order set order_stat = if(".
				   "   (select sum(b.qty) from erp_order_dtl a inner join erp_inout b".
				   "      on a.order_dtl_no = b.order_dtl_no".
				   "      and b.inout_kind = 'I'".
				   "      where order_no = '{$order_no}'".
				   "      ) < total_qty, 2, 3)".
				   "   where order_no = '{$order_no}'";
			$pdo->query($sql);
		}
	}

	if($over_cnt > 0) { // 입고량 초과건 처리
		$order_dtl_nos = implode(',', $over);
		?>
		<script type="text/javascript">
			window.alert("<?=$total_cnt?>건 중 <?=$cnt?>건이 입고 처리 되었습니다.\n입고수량이 발주 수량을 초과 하였거나 이미 입고 완료 된 상품이 <?=$over_cnt?>개 있습니다.\n\n'초과입고 엑셀'을 다운로드 하여 발주수량을 수정하신 후 해당 상품을 다시 입고처리 해주십시오.");
			var f = parent.document.getElementById('overFrm');
			f.order_dtl_no.value = '<?=$order_dtl_nos?>';
			f.style.display = 'block';
		</script>
		<?
		exit;
	}


	if($cnt > 0) msg("총 {$cnt}건의 입고가 완료되었습니다.","reload","parent");
	else msg("처리 된 내역이 없습니다.\\n발주상세번호, 입고수량, 발주단가를 꼭 입력하셔서 업로드 해주시기 바랍니다.","","parent");

?>