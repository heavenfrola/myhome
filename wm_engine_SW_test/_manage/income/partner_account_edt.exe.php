<?PHP

	if($admin['level'] > 3) msg('접근 권한이 없습니다.');

	$partner_no = numberOnly($_POST['partner_no']);
	$account_idx = numberOnly($_POST['account_idx']);
	$fee_prc = numberOnly($_POST['fee_prc']);
	$cpn_fee = numberOnly($_POST['cpn_fee']);
	$dlv_prc = $_POST['dlv_prc'];
	$ono = $_POST['ono'];

	if($account_idx > 0) {
		$account = $pdo->assoc("select no, stat from $tbl[order_account] where no='$account_idx'");
		if(!$account['no']) msg('정상적인 정산 데이터가 아닙니다.');
		if($account['stat'] > 1) msg("수정할 수 없는 정산상태입니다.\\n정산등록 상태에서만 금액 편집이 가능합니다.");
	}

	foreach($dlv_prc as $no => $prc) {
		$no = numberOnly($no);
		$prc = numberOnly($prc);
		$order_no = addslashes($ono[$no]);

		$order_dlv_prc = $pdo->row("select `no` from $tbl[order_dlv_prc] where no = '$no' and partner_no = '$partner_no'");
		if ($order_dlv_prc) {
		    $pdo->query("update $tbl[order_dlv_prc] set dlv_prc='$prc' where no='$no' and partner_no='$partner_no'");
		} else if (!$order_dlv_prc && $prc != 0 && $order_no){
		    $pdo->query("insert into $tbl[order_dlv_prc] (ono, partner_no, dlv_prc, first_prc)  values ('$order_no', '$partner_no', '$prc', '0')");
		}
	}

	foreach($fee_prc as $no => $prc) {
		$no = numberOnly($no);
		$_fee_prc = numberOnly($prc);
		$_cpn_fee = numberOnly($cpn_fee[$no]);

		$pdo->query("update $tbl[order_product] set fee_prc='$_fee_prc', cpn_fee='$_cpn_fee' where no='$no' and partner_no='$partner_no'");
	}

	if($account_idx > 0) {
		$data = $pdo->assoc("
			select  sum(fee_prc) as fee_prc, sum(cpn_fee) as cpn_fee
			from $tbl[order_product] where account_idx='$account_idx'
		");
		$data['dlv_prc'] = $pdo->row("select sum(d.dlv_prc) from $tbl[order_dlv_prc] d inner join (select distinct ono, partner_no from wm_order_product where account_idx='$account_idx' and partner_no='$partner_no') op using(ono, partner_no)");

		$pdo->query("
			update $tbl[order_account] set dlv_prc='$data[dlv_prc]', fee_prc='$data[fee_prc]', cpn_partner='$data[cpn_fee]', cpn_master=cpn_tot-'$data[cpn_fee]'
			where no='$account_idx'
		");
		if($pdo->lastRowCount() > 0) {
			orderAccountLog($account_idx, '정산 편집');
		}
	}

	msg('정산 정보가 수정되었습니다.', 'reload', 'parent');

?>