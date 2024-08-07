<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  스킨 환경 설정 처리
	' +----------------------------------------------------------------------------------------------+*/

	if(!$_skin_name){
		$_skin_name=editSkinName();
	}

	$file_content="<?php\n// 스킨 설정파일 : ".date("Y-m-d H:i", $now)." 변경됨 - ".$admin['admin_id']."\n\n";
	if(is_array($_skin)){
		foreach($_skin as $key=>$val){
			$file_content .= "\$_skin['$key']=\"".$val."\";\n";
		}
	}

	if(is_array($_board_skin)){
		foreach($_board_skin as $key=>$val){
			$file_content .= "\$_board_skin['$key']=\"".$val."\";\n";
		}
	}

	if(is_array($_page_layout)){ // 페이지별 레이아웃
		foreach($_page_layout as $key=>$val){
			$file_content .= "\$_page_layout['$key']=\"".$val."\";\n";
		}
	}
	$file_content .= "?>";

	$_save_skin_dir=$_save_skin_dir ? $_save_skin_dir : $root_dir."/_skin/".$_skin_name;

	$_filedir=$_save_skin_dir."/skin_config.".$_skin_ext['g'];
	$_filebakdir=$root_dir."/_data/skin_config_tmp.".$_skin_ext['g'];
	$of=fopen($_filebakdir, "w");
	$fw=fwrite($of, $file_content);
	if(!$fw) msg("계정디렉토리 권한이 잘못되어있습니다. 1:1고객센터 문의 글로 접수 바랍니다.");
	fclose($of);

	$file['name']="skin_config.".$_skin_ext['g'];
	$file['tmp_name']=$_filebakdir;
	ftpUploadFile($_save_skin_dir, $file, $_skin_ext['g']);
	unlink($file['tmp_name']);

?>