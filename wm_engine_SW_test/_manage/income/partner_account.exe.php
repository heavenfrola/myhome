<?PHP

	if($_POST['exec'] == 'accept') {
		$account_idx = numberOnly($_POST['account_idx']);
		$account = $pdo->assoc("select * from $tbl[order_account] where no='$account_idx'");

		if($admin['level'] > 3 && $account['partner_no'] != $admin['partner_no']) {
			exit('처리 권한이 없습니다.');
		}

		$pdo->query("update $tbl[order_account] set stat=3, confirm_date='$now' where no='$account_idx'");

		exit('OK');
	}

	if($admin['level'] > 3) msg('처리 권한이 없습니다.');

	if($_POST['exec'] == 'remove') {
		printAjaxHeader();

		$account_idx = numberOnly($_POST['account_idx']);
		$account = $pdo->assoc("select stat from $tbl[order_account] where no='$account_idx'");
		if($account['stat'] != 1 && $account['stat'] != 6) {
			exit('등록/취소 상태의 정산만 삭제할수 있습니다.');
		}

		$pdo->query("delete from $tbl[order_account] where no='$account_idx'");
		$pdo->query("delete from $tbl[order_account_log] where account_idx='$account_idx'");
		$pdo->query("update $tbl[order_product] set account_idx='0' where account_idx='$account_idx'");
		$pdo->query("update {$tbl['order_account_refund']} set account_idx='0' where account_idx='$account_idx'");

		exit('OK');
	}

	$no = $_POST['no'];
	$input_prc = $_POST['input_prc'];
	$stat = $_POST['stat'];

	if(count($no) == 0) msg('처리할 정산 데이터를 선택해주세요.');

	foreach($no as $account_idx => $_no) {
		$_input_prc = numberOnly($input_prc[$account_idx], true);
		$_stat = $stat[$account_idx];

		$account = $pdo->assoc("select (ROUND(prd_prc+cpn_master+dlv_prc-fee_prc)) as prc, stat from $tbl[order_account] where no='$account_idx'");

		if($account['prc'] > 0 && $_input_prc > $account['prc']) {
			alert("정산금액(".parsePrice($account['prc'], 2).")보다 많은 입금 금액(".parsePrice($_input_prc, 2).")을 입력할수 없습니다.");
			return false;
		}
		if($account['prc'] < 0 && $_input_prc < $account['prc']) {
			alert("환불 정산금액(".parsePrice($account['prc'], 2).")보다 많은 환불 금액(".parsePrice($_input_prc, 2).")을 입력할수 없습니다.");
			return false;
		}

		if($_input_prc > 0 && $stat[$account_idx] == 1) {
			alert('정산 승인 전에는 입금 처리를 하실수 없습니다.');
			return false;
		}
		if($_input_prc == 0 && $stat[$account_idx] == 5) {
			continue;
		}

		$asql = '';
		if($account['stat'] != $_stat) {
			switch($_stat) {
				case '3' : $asql .= ", confirm_date='$now'"; break;
				case '5' : $asql .= ", complete_date='$now'"; break;
			}
		}

		$pdo->query("update $tbl[order_account] set input_prc='$_input_prc', stat='$_stat' $asql where no='$account_idx'");
		if($pdo->lastRowCount() > 0) {
			orderAccountLog($account_idx, '입금액/상태변경');
		}
	}

	msg('수정되었습니다.', 'reload', 'parent');

?>
