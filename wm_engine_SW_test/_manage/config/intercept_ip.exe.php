<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  IP 차단 설정
	' +----------------------------------------------------------------------------------------------+*/

	$mode = trim($_POST['mode']);
	$intercept_ip_no = trim(addslashes($_POST['intercept_ip_no']));

	if($mode == "U") {
		$intercept_adm_yn = "N";
	} else {
		$intercept_adm_yn = "Y";
	}


	checkBlank($mode,  '잘못된 경로로 접근하였습니다.');
	checkBlank($intercept_ip_no,  '잘못된 경로로 접근하였습니다.');


	//DEL
	if($_POST['exec'] == 'remove') {
		$pdo->query("delete from $tbl[intercept_ip] where intercept_adm_yn = '$intercept_adm_yn' and intercept_ip = '$intercept_ip_no'");
		exit('삭제되었습니다.');
	}


	//ADD
	if($intercept_adm_yn == 'Y' && $intercept_ip_no == $_SERVER[REMOTE_ADDR])msg("사용중인 IP는 관리자 등록이 불가능합니다.");
	$cnt=$pdo->row("select count(*) as cnt from $tbl[intercept_ip] where intercept_adm_yn = '$intercept_adm_yn' and intercept_ip = '$intercept_ip_no'");
	if($cnt)msg('해당 IP는 이미 리스트에 등록되어있습니다.');


	$pdo->query("
		insert into $tbl[intercept_ip]
		(intercept_ip ,intercept_adm_yn ,reg_date ) values
		('$intercept_ip_no', '$intercept_adm_yn', '$now')
	");

	msg($intercept_ip_no.' 가 차단 IP에 추가되었습니다.', '?body=config@intercept_ip&mode='.$mode, 'parent');
?>