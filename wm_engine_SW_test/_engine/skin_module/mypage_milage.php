<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  마이페이지 적립금리스트
	' +----------------------------------------------------------------------------------------------+*/

	$_replace_code[$_file_name][total_milage]=number_format($member[milage]);
	$_tmp="";
	$_line=getModuleContent("mypage_milage_list");
	while($mile=milageLoop("")){
		$mile[idx]=$idx;
		$_tmp .= lineValues("mypage_milage_list", $_line, $mile);
	}
	$_tmp=listContentSetting($_tmp, $_line);
	$_replace_code[$_file_name][mypage_milage_list]=$_tmp;

?>