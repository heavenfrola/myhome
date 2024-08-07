<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  게시판 상단 디자인 이미지 업로드 처리
	' +----------------------------------------------------------------------------------------------+*/

	$updir=$dir['upload']."/".$dir['board_common'];
	if($_POST['exec'] == 'delete') {
		$img = basename($_POST['img']);
		if(!$img) {
			msg("삭제할 파일을 입력하세요");
		}
		$img=$root_dir."/".$updir."/".$img;
		if(!is_file($img)) {
			msg("존재하지 않는 파일입니다");
		}
		deleteAttachFile($updir, $img);
		$ems="파일을 삭제하였습니다";
	}
	else {
		if(!$_FILES["upfile"]["name"]) {
			msg("업로드할 파일을 입력하세요");
		}
		$up_filename=md5(time());
		$up_info=uploadFile($_FILES["upfile"],$up_filename,$updir,"jpg|jpeg|gif|png|bmp");
		$ems="파일이 업로드 되었습니다";
	}

	msg($ems,"reload","parent");

?>