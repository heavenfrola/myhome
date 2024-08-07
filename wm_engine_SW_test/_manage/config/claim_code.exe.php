<?PHP

	$no = numberOnly($_POST['no']);

	if($_POST['exec'] == 'removeReason') {
		$pdo->query("delete from $tbl[claim_reasons] where no='$no'");
		exit;
	}

	if($_POST['exec'] == 'getReason') {
		header('Content-type:application/json;');

		$data = $pdo->assoc("select * from {$tbl['claim_reasons']} where no='$no'");
		exit(json_encode($data));
	}

	$reason = addslashes(trim($_POST['reason']));
	$admin_only = ($_POST['admin_only'] == 'Y') ? 'Y' : 'N';

	checkBlank($reason, '사유명을 정확히 입력해 주세요.');

	if($no > 0) {
		$pdo->query("update $tbl[claim_reasons] set reason='$reason', admin_only='$admin_only' where no='$no'");
	} else {
		$sort = $pdo->row("select max(sort) from $tbl[claim_reasons]")+1;
		$pdo->query("insert into $tbl[claim_reasons] (reason, sort, admin_id, admin_only, reg_date) values ('$reason', '$sort', '$admin[admin_id]', '$admin_only', '$now')");
	}

	msg('', 'reload', 'parent');

?>