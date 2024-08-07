<?PHP

	if($_POST['exec'] == 'single') {
		$_complex_no = numberOnly($_POST['complex_no']);
		$_qty = numberOnly($_POST['qty']);
		$_force_soldout = $_POST['force_soldout'];
		$_remark = addslashes(trim($_POST['remark']));
		$data = $pdo->assoc("select complex_no, force_soldout, curr_stock(complex_no) as qty from erp_complex_option where complex_no='$_complex_no' and del_yn='N'");

		if(!$data['complex_no']) msg('바코드 정보가 없습니다.');
		if($_qty === '') msg('재고수량을 입력해주세요.');
		if(in_array($_force_soldout, array('Y', 'N', 'L')) == false) msg('재고 품절 방식을 선택해주세요.');
		if(!$_remark) msg('변경 사유를 입력해주세요.');
		if($_qty == $data['qty'] && $_force_soldout == $data['force_soldout']) msg('변경된 내역이 없습니다.');

		$complex_no = array($_complex_no);
		$stock_qty = array($_qty);
		$force_soldout = array($_complex_no => $_force_soldout);
		$adjust_reason = array($_remark);
		$org_stock_qty = array($data['qty']);
		$org_soldout = array($data['force_soldout']);
	} else {
		$complex_no = numberOnly($_POST['complex_no']);
		$stock_qty = $_POST['stock_qty'];
		$org_stock_qty = numberOnly($_POST['org_stock_qty']);
		$out_qty = numberOnly($_POST['out_qty']);
		$force_soldout = $_POST['force_soldout'];
		$org_soldout = $_POST['org_soldout'];
		$adjust_reason = $_POST['adjust_reason'];
		$safe_qty = numberOnly($_POST['safe_qty']);
		$org_safe_qty = numberOnly($_POST['org_safe_qty']);
	}

	$remote_ip = $_SERVER['REMOTE_ADDR'];
	$changed = 0;
	$schanged_Y = array();
	$schanged_N = array();
	$schanged_L = array();
	foreach($complex_no as $i => $_complex_no) {
		if($admin['level'] == 4) { // 권한이 없는 파트너가 수정 불가
			$_partner_no = $pdo->row("select partner_no from $tbl[product] p inner join erp_complex_option e on p.no=e.pno where e.complex_no='$_complex_no'");
			if($_partner_no != $admin['partner_no']) {
				continue;
			}
		}

		$stock_qty[$i] = preg_replace('/[^-0-9]/', '', $stock_qty[$i]);
		$soldout = addslashes($force_soldout[$_complex_no]);
		$_org_soldout = $org_soldout[$_complex_no];
		$v = $org_stock_qty[$i];

		if($out_qty[$i]) {
			$stock = $pdo->row("select curr_stock($_complex_no) as stock from erp_complex_option where complex_no='$_complex_no'");
			$org_stock_qty[$i] = $stock;
			$stock_qty[$i] = $org_stock_qty[$i]-$out_qty[$i];
			$out = true;
		}

		// 한계재고
		$_limit_qty = numberOnly($_POST['limit_qty'][$i]);
		if($_limit_qty > 0) $_limit_qty = 0;
		$_org_limit_qty = $_POST['org_limit_qty'][$i];

		if(($v != $stock_qty[$i] && $stock_qty[$i] != '') || ($soldout != $_org_soldout) || ($_limit_qty != $_org_limit_qty)) {
			if($stock_qty[$i] == '') {
				if($soldout != $_org_soldout || $_limit_qty != $_org_limit_qty) $stock_qty[$i] = $v;
				else continue;
			}
			//if(trim($adjust_reason[$i]) == '') msg('조정 사유를 입력 해 주십시오');

			if($nos) {
				$nos .= ",";
				$sql2 .= ",";
			}
			$nos .= $_complex_no;
			$gap = $stock_qty[$i] - $org_stock_qty[$i];
			if($soldout == $_org_soldout) $out_status = 'S';
			else {
				$out_status = $soldout;
				${'schanged_'.$out_status}[] = $_complex_no;
			}

			if($gap > 0) {
				$inout_kind = "U";
				resolveHold($_complex_no, $gap);
			} else {
				$inout_kind = "P";
				$gap = - $gap;
				setOutputToHold($_complex_no, $gap);
			}
			$chg_stat = ($soldout != $_org_soldout) ? ' / '.$_erp_force_stat[$_org_soldout].' => '.$_erp_force_stat[$soldout] : '';
			$adjust_reason[$i] = addslashes(trim($adjust_reason[$i]));
			$sql2 .= "({$_complex_no}, '{$inout_kind}', $gap, '재고조정({$adjust_reason[$i]})$chg_stat', '{$admin['admin_id']}', now(), '{$remote_ip}')";
			$changed++;

			$asql = '';
			if($cfg['erp_force_limit'] == 'Y' && $_POST['exec'] != 'single') {
				$asql .= ", limit_qty='$_limit_qty'";
			}

            //입력재고가 존재한다면 옵션의 품절상태 해제
            if ($stock_qty[$i] > 0) {
                $asql .= ", is_soldout = 'N'";
            }

			$pdo->query("update erp_complex_option set qty='$stock_qty[$i]' $asql where complex_no='$_complex_no'");

			// 재입고 알림용 complex_no
			$notify_restock_array[] = $_complex_no;
		}

		if($org_safe_qty[$i] != $safe_qty[$i]) {
			if($safe_qty[$i] < 0) $safe_qty[$i] = 0;
			$pdo->query("update erp_complex_option set safe_stock_qty='$safe_qty[$i]' where complex_no='$complex_no[$i]'");
			$changed++;
		}
	}


	if(!$changed) msg('변경 된 사항이 없습니다.');
	for($i = 0; $i <= 2; $i++) {
		switch($i) {
			case 0 : $plug = 'Y'; break;
			case 1 : $plug = 'N'; break;
			case 2 : $plug = 'L'; break;
		}
		$changed_no = ${'schanged_'.$plug};
		if(count($changed_no) > 0) {
			$_no = implode(',', $changed_no);
			$pdo->query("update erp_complex_option set force_soldout='$plug' where complex_no in ($_no)");
		}
	}
	$pdo->query("insert into erp_inout (complex_no, inout_kind, qty, remark, reg_user, reg_date, remote_ip) values " . $sql2);

	// 재입고 알림
	for($ii = 0; $ii < count($notify_restock_array); $ii++) {
		sendNotifyRestockSMS($notify_restock_array[$ii]);
	}

	msg('재고조정이 완료되었습니다.', 'reload', 'parent');

?>