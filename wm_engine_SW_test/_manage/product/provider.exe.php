<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  사입처 등록 처리
	' +----------------------------------------------------------------------------------------------+*/

	$exec = $_POST['exec'];

	 // 사입처 실시간 검색
	if($exec == 'search') {
		$keyword = addslashes(trim($_POST['keyword']));
		if(!$keyword) exit;

		$result = '';
		$res = $pdo->iterator("select no, arcade, floor, provider from $tbl[provider] a where provider like '%$keyword%' order by provider");
        foreach ($res as $data) {
			$_provider = cutStr(stripslashes($data['provider']), 50);
			$_arcade  = stripslashes($data['arcade']);
			if($data['floor']) $_arcade .= ' '.stripslashes($data['floor']).'층';
			if($_arcade) $_provider = "[$_arcade] ".$_provider;

			$result .= "###$data[no]@@@$_provider";
		}

		printAjaxHeader();
		exit(preg_replace('/^###/', '', $result));
	}

	// 사입처 리스트 실시간 출력
	if($exec == 'getAllSeller') {
		printAjaxHeader();

		$prql = $pdo->iterator("select `no`, `provider`, `plocation` from `$tbl[provider]` order by `plocation` !='' desc, `plocation` asc, `provider` asc");
        foreach ($prql as $prdata) {
			$_provider = stripslashes($prdata[provider]);
			$_plocation= stripslashes($prdata[plocation]);
			$_ptitle = ($_plocation) ? "[$_plocation] $_provider" : "$_provider";

			echo "<option value='$prdata[no]'>$_ptitle</option>";
		}

		exit;
	}

	// 등록/수정
	if ($exec == "register") {
		$no = numberOnly($_POST['no']);
		$provider = addslashes(trim($_POST['provider']));
		$plocation = addslashes(trim($_POST['plocation']));
		$content = addslashes(trim($_POST['content']));
		$arcade = addslashes(trim($_POST['arcade']));
		$floor = addslashes(trim($_POST['floor']));
		$ptel = addslashes(trim($_POST['ptel']));
		$pcell = addslashes(trim($_POST['pcell']));
		$account1 = addslashes(trim($_POST['account1']));
		$account2 = addslashes(trim($_POST['account2']));
		$account1_name = addslashes(trim($_POST['account1_name']));
		$account2_name = addslashes(trim($_POST['account2_name']));
		$pceo = addslashes(trim($_POST['pceo']));
		$account1_bank = addslashes(trim($_POST['account1_bank']));
		$account2_bank = addslashes(trim($_POST['account2_bank']));

		if($no) {
			$r = $pdo->query("update `$tbl[provider]` set
					`provider`='$provider', `ptel`='$ptel', `pcell`='$pcell', `pceo`='$pceo',
					`arcade`='$arcade', `floor`='$floor', `plocation`='$plocation',
					`content`='$content',
					`account1`='$account1', `account1_bank`='$account1_bank', `account1_name`='$account1_name', `account2`='$account2', `account2_bank`='$account2_bank', `account2_name`='$account2_name'
				where `no`='$no'");
			$pdo->query("update `$tbl[product]` set `seller`='$provider' where `seller_idx`='$no'");
		} else {
			$r = $pdo->query("insert into `$tbl[provider]`
				(`provider`, `ptel`, `pcell`, `pceo`, `arcade`, `floor`, `plocation`, `content`, `account1`, `account1_bank`, `account1_name`, `account2`, `account2_bank`, `account2_name`, `reg_date`)
				values ('$provider', '$ptel', '$pcell', '$pceo', '$arcade', '$floor', '$plocation', '$content', '$account1', '$account1_bank', '$account1_name', '$account2', '$account2_bank', '$account2_name', '$reg_date')
			");
		}

		if($r) msg('', '?body=product@provider', 'parent');
		else msg('사입처 저장 중 오류가 발생했습니다.\t');
	}

	// 삭제
	if ($exec == "delete") {
		$check_pno = numberOnly($_POST['check_pno']);
		$cnt = count($check_pno);
		if(!$cnt) msg("삭제할 사입처를 선택해 주십시오");

		foreach ($check_pno as $no) {
			$provider = $pdo->row("select `provider` from `$tbl[provider]` where `no`='$no'");
			$provider = addslashes($provider);

			$pdo->query("delete from `$tbl[provider]` where `no`='$no'");
			$pdo->query("update `$tbl[product]` set `seller`='' where `seller`='$provider'");
		}

		msg("", "reload", "parent");
	}

?>