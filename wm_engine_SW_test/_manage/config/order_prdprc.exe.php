<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  주문상품금액별 할인설정 처리
	' +----------------------------------------------------------------------------------------------+*/

	if($_POST['exec'] == 'remove') {
		$no = numberOnly($_POST['no']);
		$pdo->query("delete from $tbl[order_config_prdprc] where no='$no'");
		exit('OK');
	}

	$prd_prc = $_POST['prd_prc'];
	$pkey = numberOnly($_POST['pkey']);

	foreach($prd_prc as $key => $val) {

		$_no = numberOnly($_POST['no'][$key]);
		$_prd_prc = numberOnly($val);
		$_per = numberOnly($_POST['per'][$key]);
		$_unit = addslashes($_POST['unit'][$key]);

		if($_no < 1 && (!$_prd_prc || !$_per)) continue;

		if(!$_prd_prc) msg('상품금액 범위를 입력 해 주세요.');
		if(!$_per) msg('할인금액(율)을 입력 해 주세요.');
		if($_no < 1 && $pdo->row("select count(*) from $tbl[order_config_prdprc] where prd_prc='$_prd_prc'") > 0) {
			msg('이미 입력된 범위값입니다.');
		}

		if($_no) {
			$pdo->query("update $tbl[order_config_prdprc] set prd_prc='$_prd_prc', per='$_per', unit='$_unit' where no='$_no'");
		} else {
			$pdo->query("insert into $tbl[order_config_prdprc] (prd_prc, per, unit, reg_date) values ('$_prd_prc', '$_per', '$_unit', '$now')");
		}
	}

	msg('', 'reload', 'parent');

?>