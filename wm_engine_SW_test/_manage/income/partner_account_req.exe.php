<?PHP

	$no_qcheck = true;

	if($_POST['exec'] == 'refund') {
		$refund_no = implode(',', $_POST['refund_no']);
		$accres = $pdo->iterator("
			select
				partner_no,
				sum(prd_prc*-1) as prd_prc,
				sum(dlv_prc-dlv_prc_return) as dlv_prc,
				sum(fee_prc*-1) as fee_prc,
				sum(cpn_fee*-1) as cpn_fee,
				sum(cpn_fee_m*-1) as cpn_master
			from {$tbl['order_account_refund']}
			where no in ($refund_no)
			group by partner_no
		");
		function parseStat($res) {
            $data = $res->current();
            $res->next();
			if($data == false) return false;

			return $data;
		}
	} else if($_POST['exec'] == 'refundDel') {
		$no = numberOnly($_POST['no']);
		$data = $pdo->assoc("select del_yn from {$tbl['order_account_refund']} where no='$no'");
		if(!$data['del_yn']) {
			$message = '정상적인 데이터가 아닙니다.';
			$stat = '';
		}
		elseif($data['del_yn'] == 'Y') {
			$message = ' 정산 환불 데이터가 복구처리 되었습니다.';
			$stat = 'N';
		} elseif($data['del_yn'] == 'N') {
			$message = ' 정산 환불 데이터가 숨김처리 되었습니다.';
			$stat = 'Y';
		}
		if(empty($stat) == false) $pdo->query("update {$tbl['order_account_refund']} set del_yn='$stat' where no='$no'");
		header('Content-type:application/json; charset='._BASE_CHARSET_);
		exit(json_encode(array(
			'stat' => $stat,
			'message' => $message,
		)));
	} else {
		$page_mode = 'reg';
		$partner_no = $_POST['partner_no'];

		foreach($partner_no as $key => $val) {
			$val = numberOnly($val);
			if($val < 1) unset($partner_no[$key]);
		}
		$pwhere = implode(',', $partner_no);
		if(!$pwhere) {
			msg('정산 등록할 입점사를 선택해 주세요.');
		}
		$pwhere1 = " and op.partner_no in ($pwhere)";
		$pwhere2 = " and d.partner_no in ($pwhere)";

		include 'partner_account_reg.php';
	}

	while($data = parseStat($accres)) {
		$pdo->query("
			insert into $tbl[order_account]
			(startdate, finishdate, partner_no, prd_prc, dlv_prc, fee_prc, cpn_tot, cpn_master, cpn_partner, stat, request_date)
			values
			('$_dates', '$_datee', '$data[partner_no]', '$data[prd_prc]', '$data[dlv_prc]', '$data[fee_prc]', '$data[cpn_sale]', '$data[cpn_master]', '$data[cpn_fee]', 1, '$now')
		");
		$account_idx = $pdo->lastInsertId();
		if(!$account_idx) continue;

		orderAccountLog($account_idx, '신규등록');

		if($_POST['exec'] == 'refund') {
			$pdo->query("
				update {$tbl['order_account_refund']} set account_idx='$account_idx' where no in ($refund_no) and partner_no='{$data['partner_no']}'
			");
		} else {
			$pdo->query("
				update
					$tbl[order] o
					inner join $tbl[order_product] op using(ono)
					left join $tbl[order_dlv_prc] od using(ono, partner_no)
				set op.account_idx='$account_idx'
				where op.partner_no='{$data['partner_no']}' $w $pwhere1
					and
					(
						(op.stat=5 and o.date{$cfg['partner_account_date']} between $_dates and $_datee)
						or ((o.stat=17 or o.stat=5) and op.stat=17 and od.dlv_prc>0 and (op.repay_date between $_dates and $_datee))
					)
					and op.account_idx=0
			");
		}
	}

	msg("정산 등록이 완료되었습니다.", "?body=income@partner_account", "parent");

?>