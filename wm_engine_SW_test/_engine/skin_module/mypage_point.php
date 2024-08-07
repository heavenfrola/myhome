<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  마이페이지 포인트내역
	' +----------------------------------------------------------------------------------------------+*/

	if($change_use){
		$_line=getModuleContent("mypage_point_change");
		$_mpm[form_start]="<form name=\"frm\" method=\"post\" action=\"".$root_url."/main/exec.php\" target=\"hidden".$now."\" onSubmit=\"return checkFrm(this,1);\">
<input type=\"hidden\" name=\"exec_file\" value=\"mypage/milage.exe.php\">
<input type=\"hidden\" name=\"exec\">
";
		$_mpm[point_c]=$member[point_c];
		$_mpm[ratio]=$cfg[point_change_ratio];
		$_mpm[form_end]="</form>";
		$_replace_code[$_file_name][mypage_point_change]=lineValues("mypage_point_change", $_line, $_mpm);
		unset($_mpm);
	}

	$_tmp="";
	$_line=getModuleContent("mypage_point_list");
	while($pt=pointLoop("")){
		$pt[idx]=$idx;
		$_tmp .= lineValues("mypage_point_list", $_line, $pt);
	}
	$_tmp=listContentSetting($_tmp, $_line);
	$_replace_code[$_file_name][mypage_point_list]=$_tmp;

?>