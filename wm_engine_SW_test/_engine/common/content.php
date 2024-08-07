<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  추가페이지(content) 출력
	' +----------------------------------------------------------------------------------------------+*/

	include $engine_dir."/_engine/include/common.lib.php";

	$cont = basename($_GET['cont']);

	$cont_edit_file=$root_dir."/_config/content_add.php";
	if(file_exists($cont_edit_file)){
		include_once $cont_edit_file;
		if($_content_add_info[$cont]['pg_name']) $ext=getExt($_content_add_info[$cont]['pg_name']);
		$_mgroup = explode("@", $_content_add_info[$cont]['mgroup']);
		if (!in_array($member['level'], $_mgroup) && $_content_add_info[$cont]['mgroup']) {
			msg("접근할 수 없는 페이지입니다", $root_url);
		}
	}
	if(!$ext) $ext="php";

    if (
        $_SESSION['browser_type'] == 'mobile'
        && $_content_add_info[$cont]['use_m_content'] == 'Y'
        && file_exists($root_dir."/_template/content/".$cont.'_m.'.$ext) == true
    ) {
    	$cont_file = $root_dir."/_template/content/".$cont.'_m.'.$ext;
    } else {
    	$cont_file = $root_dir."/_template/content/".$cont.'.'.$ext;
    }
	if(!is_file($cont_file)) {
		msg("접근할 수 없는 페이지입니다","back");
	}

	if($cont=="guide") {
		$sql="select * from `".$tbl['bank_account']."` order by `sort`";
		$res = $pdo->iterator($sql);
		$bank_list="";
        foreach ($res as $data) {
			$str="$data[bank] $data[account] $data[owner]";
			$bank_list.="$str<br>";
		}
	}
	common_header();

	// 디자인 버전 점검 & 페이지 출력
	include_once $engine_dir."/_engine/common/skin_index.php";

?>