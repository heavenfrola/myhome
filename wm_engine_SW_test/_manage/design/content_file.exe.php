<?PHP

	$updir=$dir['upload']."/".$dir['content'];
	if($exec=="delete") {
		if(!$img) {
			msg("삭제할 파일을 입력하세요");
		}
		$img=$root_dir."/".$updir."/".$img;
		if(!is_file($img)) {
			msg("존재하지 않는 파일입니다");
		}
		@unlink($img);
		$ems="파일을 삭제하였습니다";
	}
	else {
		if(!$_FILES["upfile"]["name"]) {
			msg("업로드할 파일을 입력하세요");
		}
		$up_filename=md5(time());
		$up_info=uploadFile($_FILES["upfile"],$up_filename,$updir,"jpg|jpeg|gif|png|bmp");
	}

	msg($ems,"reload","parent");

?>