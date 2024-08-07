<?PHP

	$ii = 0;
	$exec = $_POST['exec'];
	$check_pno = $_POST['check_pno'];

	if($exec == "delete") {
		foreach($check_pno as $val) {
			$val = numberOnly($val);
			$r = $pdo->query("delete from wm_mkt_script where no='$val'");
			if($r) $ii++;
		}
		msg("$ii 개의 스크립트를 삭제하였습니다.", 'reload', 'parent');
	}

	if($exec == 'useon' || $exec == 'useoff') {
		$use_yn = ($exec == 'useon') ? 'Y' : 'N';
		foreach($check_pno as $val) {
			$val = numberOnly($val);
			$r = $pdo->query("update wm_mkt_script set use_yn='$use_yn' where no='$val'");
			if($r) $ii++;
		}
		msg("$ii 개의 스크립트가 변경되었습니다.", 'reload', 'parent');
	}

	if($exec == 'toggle') {
		$no = numberOnly($_POST['no']);
		$use_yn = ($pdo->row("select use_yn from {$tbl['mkt_script']} where no='$no'") == 'Y') ? 'N' : 'Y';
		$pdo->query("update {$tbl['mkt_script']} set use_yn='$use_yn' where no='$no'");

		header("Content-type:application/json;");
		exit(json_encode(array('changed' => $use_yn)));
	}

	include_once 'mkt_script_regist.php';

	$no = numberOnly($_POST['no']);
	$name = addslashes(trim($_POST['name']));
	$memo = addslashes(trim($_POST['memo']));
	$asql1 = $asql2 = $asql3 = '';
	foreach($page_list as $key => $val) {
		$val1 = addslashes(trim($_POST[$key]));
		$val2 = addslashes(trim($_POST[$key.'_pc']));
		$val3 = addslashes(trim($_POST[$key.'_mb']));

		$asql1 .= ", {$key}, {$key}_pc, {$key}_mb";
		$asql2 .= ", '$val1', '$val2', '$val3'";
		$asql3 .= ", {$key}='$val1', {$key}_pc='$val2', {$key}_mb='$val3'";
	}
	$use_yn = ($_POST['use_yn'] == 'Y') ? 'Y' : 'N';

	if(fieldExist($tbl['mkt_script'], 'scr_header_pc') == false) {
		foreach($page_list as $key => $val) {
			addField($tbl['mkt_script'], $key.'_pc', 'text not null');
			addField($tbl['mkt_script'], $key.'_mb', 'text not null');
		}
		$pdo->query("alter table {$tbl['mkt_script']} CHANGE COLUMN reg_date reg_date int(10) not null comment '등록 일시' after scr_bottom_mb");
	}

	checkBlank($name, "광고명을 입력해주세요.");

	if(!$no) {
		$pdo->query("
			insert into {$tbl['mkt_script']}
			(no, name, use_yn, info, memo, reg_date $asql1)
			values
			('$no', '$name' , '$use_yn', '$info', '$memo', '$now' $asql2)
		");
	} else {
		$pdo->query("update wm_mkt_script set name='$name', use_yn='$use_yn', info='$info', memo='$memo', reg_date='$now' $asql3 where no='$no'");
	}

    if($pdo->getError()) {
        msg(php2java($pdo->getError()));
    }

	msg('등록되었습니다', './?body=design@mkt_script_list', 'parent');

?>