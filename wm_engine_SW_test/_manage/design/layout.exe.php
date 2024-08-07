<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  레이아웃 편집 처리
	' +----------------------------------------------------------------------------------------------+*/

	checkBasic();

	include_once $engine_dir."/_engine/include/design.lib.php";
	include_once $engine_dir."/_manage/design/version_check.php";
	include_once $engine_dir."/_engine/include/img_ftp.lib.php";
	$connection=ftpCon();
	if(!$connection) msg("FTP 접속이 실패하였습니다. 1:1고객센터 문의 글로 접수 바랍니다.");

	$_skin_name=editSkinName();
	include_once $root_dir."/_skin/".$_skin_name."/skin_config.".$_skin_ext['g'];

	$layout=$_POST['layout'];
	checkBlank($layout,"레이아웃을 선택해주세요.");

	if($page_name == ""){ // 기본 레이아웃 설정일 경우
		$_skin['default_layout']=$layout;
	}

	if($page_name != "" && $layout != ""){ // 페이지별 레이아웃 설정일 경우
		$_page_layout[$page_name]=$layout;
	}

	// 전체 적용일 경우
	$all_apply_layout=$_POST['all_apply_layout'];
	if($all_apply_layout != ""){
		unset($_page_layout);
		$_skin['default_layout']=$all_apply_layout;
	}

	include_once $engine_dir."/_manage/design/skin_config.exe.php";

	msg("", "reload", "parent");

?>