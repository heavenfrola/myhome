<?PHP

	$_updir=$dir[upload]."/mng_excel_set";
	$file=$root_dir."/".$_updir."/".$_pg_type."_set.php";
	$file_content="";
	if(is_file($file)) $file_content=@file_get_contents($file);
	if($exec == "save_set"){
		$set_name=stripslashes($set_name);
		$set_name=str_replace("@", "", $set_name);
		checkBlank($set_name, "세트명을 입력해주세요.");
		makeFullDir($_updir);
		if(@strchr($file_content, "@".$set_name."@")) msg("세트명이 중복되므로 다르게 지정해주시기 바랍니다");
		$content=$file_content."@".$set_name."@".$excel_fd_selected."@\n";
		$msg="저장되었습니다";
	}elseif($exec == "delete_set"){
		checkBlank($del_set, "삭제 세트를 입력해주세요.");
		$del_content="@".$del_set."@\n";
		$content=str_replace($del_content, "", $file_content);
		$msg="삭제되었습니다";
	}
	$fp=fopen($file, "w");
	fwrite($fp, $content);
	fclose($fp);

?>