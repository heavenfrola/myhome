<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  사입처 일괄관리 처리
	' +----------------------------------------------------------------------------------------------+*/
    ini_set('memory_limit', '-1');

	$csv = $_FILES['csv'];
	if(!$csv['size']) msg('csv 파일을 업로드 해 주십시오.');

    $csv = csvFileEncoding($csv);

	$bank_names = array();
	foreach($bank_codes as $key => $val) {
		$bank_names[$val] = $key;
	}

	$idx = -1;
	$insert = '';
	$fp = fopen($csv['tmp_name'], 'r');
	while($data = fgetcsv($fp, 2048)) {
		$idx++;

		if($idx == 0) continue;

		$no = $data[0];

		$arcade = addslashes($data[1]);
		$floor = addslashes($data[2]);
		$location = addslashes($data[3]);
		$provider = addslashes($data[4]);
		$tel = $data[5];
		$cell = $data[6];
		$ceo = addslashes($data[7]);
		$account1_bank = addslashes($data[8]);
		$account1 = addslashes($data[10]);
		$account1_name = addslashes($data[11]);
		$account2_bank = addslashes($data[12]);
		$account2 = addslashes($data[14]);
		$account2_name = addslashes($data[15]);
		$content = addslashes($data[16]);
		if(!$provider) continue;
		if($no) {
			$pdo->query("update `$tbl[provider]` set arcade='$arcade', floor='$floor', plocation='$location', provider='$provider', ptel='$tel', pcell='$cell', pceo='$ceo', account1='$account1', account1_bank='$account1_bank', account1_name='$account1_name', account2='$account2', account2_bank='$account2_bank', account2_name='$account2_name', content='$content' where no='$no'");
			$pdo->query("update `$tbl[product]` set seller='$provider' where seller_idx='$no'");
		} else {
			$insert .= ", ('$provider', '$tel', '$cell', '$ceo', '$arcade', '$floor', '$location', '$content', '$account1', '$account1_bank', '$account1_name', '$account2', '$account2_bank', '$account2_name', '$now')";
		}

	}

	$insert = preg_replace('/^, /' ,'', $insert);
	if($insert) {
		$pdo->query("insert into `$tbl[provider]` (`provider`, `ptel`, `pcell`, `pceo`, `arcade`, `floor`, `plocation`, `content`, `account1`, `account1_bank`, `account1_name`, `account2`, `account2_bank`, `account2_name`, `reg_date`) values $insert");
	}

	msg('사입처 일괄 수정이 완료되었습니다', '?body=product@provider', 'parent');

?>
