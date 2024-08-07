<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  게시판 상단 디자인 처리
	' +----------------------------------------------------------------------------------------------+*/

	$no = numberOnly($_POST['no']);
	$exec = $_POST['exec'];
	$content2 = addslashes($_POST['content2']);
	$hidden = ($_POST['hidden'] == 'Y') ? 'Y' : 'N';
	$top_use = ($_POST['top_use'] == 'Y') ? 'Y' : 'N';

	if($no) {
		$board=get_info('mari_config',"no",$no);
		checkBlank($board[no],"게시판 정보를 입력해주세요.");
	}

	if($exec=="delete") {
		$sql="update `mari_config` set top_use='N', `top_content`='' where `no`='$no'";
		$pdo->query($sql);
		msg("삭제가 완료되었습니다","reload","parent.parent");
	} else {
		if($no) {
			$sql="update `mari_config` set top_use='$top_use', `top_content`='$content2' where `no`='$no'";
			$pdo->query($sql);

			msg("등록하였습니다","reload","parent");
		}
	}

?>