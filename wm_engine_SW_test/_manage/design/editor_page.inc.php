<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' | 사용자 코드 편집 추가정보 공통인클루드
	' +----------------------------------------------------------------------------------------------+*/

	// 추가 페이지
	if(file_exists($root_dir."/_config/content_add.php")) include_once $root_dir."/_config/content_add.php";
	if(is_array($_content_add_info)){
		foreach($_content_add_info as $ctkey=>$ctval){
			$ctno++;
			$_edit_list['기타']["content/content.php?cont=".$ctkey]=$_content_add_info[$ctkey]['name'] ? $_content_add_info[$ctkey]['name'] : "추가페이지".$ctno;
		}
	}
	// 게시판 링크
	$_bsql = $pdo->iterator("select `db`, `title` from `mari_config`");
    foreach ($_bsql as $_bdata) {
		$_edit_list['게시판정보']["board/?db=".$_bdata['db']]=$_bdata['title'];
	}
	// 상품
	$_csql = $pdo->iterator("select `no`, `name`, `ctype` from {$tbl['category']} where `level`=1 order by `ctype`, `sort`");
    foreach ($_csql as $_cdata) {
		$_ctype_name="일반";
		if($_cdata['ctype'] == '2') $_ctype_name="기획전";
		if($_cdata['ctype'] == '4' && $cfg['xbig_name']) $_ctype_name=$cfg['xbig_name'];
		if($_cdata['ctype'] == '5' && $cfg['ybig_name']) $_ctype_name=$cfg['ybig_name'];
		$_edit_list['상품관련']["shop/big_section.php?cno1=".$_cdata['no']]="<font color=\"#6699CC\">[".$_ctype_name."]</font> ".$_cdata['name'];
	}

?>