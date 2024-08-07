<?PHP

	checkBasic();

	$no = numberOnly($_POST['no']);

	if($no){
		$data = get_info($tbl['hs_code'], "no", $no);
		if(!$data['no']) msg("존재하지 않는 자료입니다", "popup");
	}

	if($_POST['exec'] == 'delete'){
		checkBlank($data['no'], "필수값을 입력해주세요.");
		$sql = "delete from `{$tbl['hs_code']}` where `no`='$no'";
		$pdo->query($sql);

		msg("삭제되었습니다", "reload", "parent");

	}else{
		$name = addslashes(trim($_POST['name']));
		$hs_code = addslashes(trim($_POST['hs_code']));

		checkBlank($name, "항목명을 입력해주세요.");
		checkBlank($hs_code, "HS 코드를 입력해주세요.");

		if($no) {
			$pdo->query("update ${tbl['hs_code']} set name='$name', hs_code='$hs_code' where no='$no'");
			msg("항목이 수정되었습니다","popup");
		} else {
			$pdo->query("insert into ${tbl['hs_code']} set name='$name', hs_code='$hs_code', regdate='$now'");
			msg("항목이 추가되었습니다","reload","parent");
		}
	}

?>