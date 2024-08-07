<?PHP

	if($_POST['exec'] == 'remove') {
		$no = implode(',', $_POST['no']);
		$res = $pdo->iterator("select no from {$tbl['product_field_set']} where category in ($no)");
        foreach ($res as $data) {
			$pdo->query("delete from {$tbl['product_field']} where fno='{$data['no']}'");
		}
		$pdo->query("delete from {$tbl['category']} where no in ($no)");
		$pdo->query("delete from {$tbl['product_field_set']} where category in ($no)");
		$pdo->query("update {$tbl['product']} set fieldset='0' where fieldset in ($no)");
		exit;
	}

	$no = numberOnly($_POST['no']);
	$code = addslashes(trim($_POST['code']));
	$name = addslashes(trim($_POST['name']));

	if(empty($no) == true) checkBlank($code, '상품군을 선택해주세요.');
	checkBlank($name, '정보고시 제목을 입력해주세요.');

	if($no > 0) {
		$pdo->query("update {$tbl['category']} set code='$code', name='$name' where no='$no'");
	} else {
		$pdo->query("
			insert into {$tbl['category']}
				(name, code, ctype, level, reg_date) values ('$name', '$code', '3', '1', now())
		");
		$no = $pdo->lastInsertId();
	}

	$fno_all = array();
	foreach($_POST['fn'] as $key => $val) {
		$fno = numberOnly($_POST['fno'][$key]);
		$fn = addslashes(trim($_POST['fn'][$key]));
        $ftype = ($_POST['ftype'][$key] == 'Y') ? '2' : '1';
		$default_value = addslashes(trim($_POST['default_value'][$key]));
        $soptions = ($ftype == '2') ? $default_value : '';

		if($fno > 0) {
			$pdo->query("update {$tbl['product_field_set']} set ftype='$ftype', soptions='$soptions', default_value='$default_value' where no='$fno'");
		} else {
			$pdo->query("insert into {$tbl['product_field_set']} (category, ftype, name, soptions, default_value, sort) values ('$no', '$ftype', '$fn', '$soptions', '$default_value', '$key')");
            $fno = $pdo->lastInsertId();
		}
		$fno_all[] = $fno;
	}
	$fno_all = implode(',', $fno_all);
	$pdo->query("delete from {$tbl['product_field_set']} where category='$no' and no not in ($fno_all)");

	$listURL = getListURL('product_definition');
	if(empty($listURL) == true) $listURL = './?body=product@product_definition';

	msg('', $listURL, 'parent');

?>