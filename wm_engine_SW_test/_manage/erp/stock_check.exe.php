<?PHP

	$exec = $_REQUEST['exec'];

	if($exec == 'search') {
		$barcode = addslashes(trim($_POST['barcode']));
		$data = $pdo->assoc("select c.pno, c.complex_no, p.hash, p.name, p.updir, p.upfile3, p.w3, p.h3, curr_stock(c.complex_no) as stock, c.opts from $tbl[product] p inner join erp_complex_option c on c.pno = p.no where barcode='$barcode'");

		if(!$data['pno']) exit('{"result":"500"}');

		$data['name'] = stripslashes(strip_tags($data['name']));
		$data['opt_name'] = stripslashes(getComplexOptionName($data['opts']));

		$is = setImageSize($data['w3'], $data['h3'], 50, 50);
		$file_dir = getFileDir($data['updir']);
		$imgstr = "<img src='$file_dir/$data[updir]/$data[upfile3]' $is[2]>";

		$result = "<div class='box_setup'>
			<div class='thumb'><a href='$root_url/shop/detail.php?pno=$data[hash]' target='_blank'>$imgstr</a></div>
			<dl>
				<dt class='title'><a href='#' onclick='viewStockDetail($data[complex_no]); return false;'>$data[name]</a></dt>
				<dd class='func'>$data[opt_name]</dd>
				<dd class='func'>현재고 $data[stock] ea</dd>
			</dl>
		</div>";

		header('Content-Type: text/html; charset='._BASE_CHARSET_);
		exit(json_encode(array(
			'result'=>'200',
			'complex_no'=>$data['complex_no'],
			'html'=>mb_convert_encoding($result, 'utf-8', _BASE_CHARSET_)
		)));
	}

	// 재고 변경
	if($exec == 'complete') {
		$remote_ip = $_SERVER['REMOTE_ADDR'];
		$sql1 = '';
		$sql2 = '';
		$changed = 0;
		$changed_complex_no = array();
		$stock = numberOnly($_POST['stock']);

		foreach($stock as $key => $stock_qty) {
			$no = $_POST['complex_no'][$key];
			$org_stock_qty = $_POST['org'][$key];

			if($org_stock_qty != $stock_qty) {

				if($nos) {
					$nos .= ",";
					$sql1 .= ",";
					$sql2 .= ",";
				}
				$gap = $stock_qty - $org_stock_qty;

				$sql1 .= "('9999-12-31', {$no}, now(), {$stock_qty}, {$gap}, '바코드 재고파악', '{$admin[admin_id]}', '{$remote_ip}')";

				$inout_kind = ($gap > 0) ? 'U' : 'P';
				$gap = abs($gap);

				$sql2 .= "('$no', '$inout_kind', '$gap', '바코드 재고파악', '$admin[admin_id]', now(), '$remote_ip')";
				$nos .= $no;
				$changed++;
				$changed_complex_no[] = $no;
			}
		}

		if(!$changed) msg('변경 된 사항이 없습니다.');
		$pdo->query("insert into erp_inout (complex_no, inout_kind, qty, remark, reg_user, reg_date, remote_ip) values " . $sql2);

		// 캐시재고 수정
		$changed_complex_no = implode(',', $changed_complex_no);
		$pdo->query("update erp_complex_option set qty=curr_stock(complex_no) where complex_no in ($changed_complex_no)");

		msg('재고조정이 완료되었습니다.', 'reload', 'parent');
	}

?>