<?PHP

	include_once $engine_dir.'/_engine/include/classes/SpreadsheetExcelReader.class.php';
	include_once $engine_dir.'/_engine/include/file.lib.php';

	if($_FILES['csv']['size'] < 1) msg('정상적인 파일을 업로드 해주세요.');

	$excel = new Spreadsheet_Excel_Reader();
	$excel->setUTFEncoder('mb');
	$excel->setOutputEncoding(_BASE_CHARSET_);
	$excel->read($_FILES['csv']['tmp_name']);

	if($excel->sheets[0]['numRows'] < 4) msg('처리 가능한 데이터가 없습니다.');

	$total = 0;
	$findex = array();
	$setnos = $excel->sheets[0]['cells'][3];
	foreach($setnos as $key => $val) {
		if($key < 4) continue;
		$findex[$key] = $val;
	}
	$flen = count($findex)+3;

	for($i = 4; $i <= $excel->sheets[0]['numRows']; $i++) {
		$data = $excel->sheets[0]['cells'][$i];

		$pno = $data[1];
		if($data[3]) {
			$fieldset = numberOnly($pdo->row("select no from $tbl[category] where ctype=3 and name='$data[3]'"));
			if($fieldset > 0) {
				$pdo->query("update $tbl[product] set fieldset='$fieldset' where no='$pno'");
			}
		}

		for($x = 4; $x <= $flen; $x++) {
			$fdata = $pdo->assoc("select no, fno, value from $tbl[product_filed] where pno='$pno' and fno='$findex[$x]'");
			$fno = $fdata['no'];
			$ffno = ($fdata['fno'] > 0) ? $fdata['fno'] : $findex[$x];
			$value = addslashes(trim($data[$x]));

			if($fno > 0 && $value) { // 수정
				if($value != stripslashes($fdata['value'])) {
					$pdo->query("update $tbl[product_filed] set value='$value' where no='$fno'");
				}
			} else if($value) { // 신규
				$pdo->query("insert into $tbl[product_filed] (pno, fno, value) values ('$pno', '$ffno', '$value')");
			} else if(!$value && $fno > 0) { // 삭제
				$pdo->query("delete from $tbl[product_filed] where no='$fno'");
			}
		}

		$total++;
	}

	msg($total.' 건의 상품정보가 수정되었습니다.');

?>