<?PHP

	checkBasic();

	if($_POST['exec'] == 'remove') {
		$no = numberOnly($_POST['no']);
		$no = implode(',', $no);

		// 첨부파일 삭제
		$res = $pdo->iterator("select updir, upfile1 from {$tbl['product_field_set']} where no in ($no) and upfile1!=''");
        foreach ($res as $data) {
			deletePrdImage($data, 1);
		}

		// 추가항목 세트 삭제
		$pdo->query("delete from {$tbl['product_field_set']} where no in ($no)");

		// 추가항목 입력 사항 삭제
		$pdo->query("delete from {$tbl['product_field']} where fno in ($no)");

		exit;
	}

	$fno = numberOnly($_POST['fno']);

	if($fno) {
		$data = get_info($tbl['product_filed_set'], "no", $fno);
		if(!$data['no']) msg("존재하지 않는 자료입니다", "popup");
	}

	if($_POST['exec'] == "sort") {
		$source = numberOnly($_POST['source']);
		$target = numberOnly($_POST['target']);

		$source = $pdo->assoc("select `no`, `sort`, category from `{$tbl['product_field_set']}` where `no`='$source'");
		$target = $pdo->assoc("select `no`, `sort` from `{$tbl['product_field_set']}` where `no`='$target'");
		if(!$source['no'] || !$target['no']) exit;

		$pdo->query("update `{$tbl['product_field_set']}` set `sort`='$source[sort]' where `no`='$target[no]'");
		$pdo->query("update `{$tbl['product_field_set']}` set `sort`='$target[sort]' where `no`='$source[no]'");

		exit;
	}

	$name = addslashes(trim($_POST['name']));
	$soptions = addslashes(trim($_POST['soptions']));
	$category = numberOnly($_POST['category']);
	$ftype = numberOnly($_POST['ftype']);

    $asql = $asql1 = $asql2 = '';

    if (isset($_POST['doosoun_fd']) == true) {
        addField($tbl['product_filed_set'], 'doosoun_fd', 'varchar(50) not null default ""');

        $doosoun_fd = addslashes($_POST['doosoun_fd']);

        $asql .= ", doosoun_fd='$doosoun_fd'";
        $asql1 .= ", doosoun_fd";
        $asql2 .= ", '$doosoun_fd'";
    }

	checkBlank($name, '항목명을 입력해주세요.');
	if($ftype == 2) {
		checkBlank(str_replace(',', '', $soptions), '선택형 항목을 입력해주세요.');
	}

	$chg_file = "";
	if($data['updir'] && $_POST['delfile1'] == "Y") {  //삭제
		deletePrdImage($data, 1);
		$up_filename = $width = $height = "";
		$chg_file = 1;
	}

	if($_FILES['upfile1']['tmp_name']) {
		$def_updir = "_data/product_filed";
		$updir = $data['updir'] ? $data['updir'] : $def_updir;

		if(!$asql) {
			$asql .= " , `updir` = '".$updir."'";
		}

		if(!is_dir($root_dir."/".$updir)) makeFullDir($updir);
		$up_filename = md5(time());
		$up_info = uploadFile($_FILES["upfile1"], $up_filename, $updir, "jpg|jpeg|gif|png|swf");
		print_r($up_info);
		$filename = $up_info[0];
		chmod($up_info[2], 0777); // 파일권한 777 로 처리 2007-11-28 by zardsama
		$chg_file = 1;
	}

	if($chg_file) $asql .= " , `upfile1` = '".$filename."'";
	if($cfg['opmk_api'] == 'shopLinker') {
		$shoplinker_cd = addslashes(trim($_POST['shoplinker_cd']));
		$asql .= ", shoplinker_cd='$shoplinker_cd'";
		$asql1 .= ", shoplinker_cd";
		$asql2 .= ", '$shoplinker_cd'";
	}

	if($data['no']) {
		$pdo->query("update `{$tbl['product_filed_set']}` set category='$category', `ftype`='$ftype' , `name`='$name' , `soptions`='$soptions' $asql where `no`='$fno'");
	}else {
		$sort = $pdo->row("select max(sort) from $tbl[product_filed_set] where category='$category'")+1;
		$pdo->query("INSERT INTO `{$tbl['product_filed_set']}` (category, `ftype` , `name` , `soptions`, `updir`, `upfile1`, sort $asql1) values ('$category', '$ftype','$name','$soptions', '$updir', '$filename', '$sort' $asql2)");
	}

	javac("parent.fdFrm.reload();");

?>