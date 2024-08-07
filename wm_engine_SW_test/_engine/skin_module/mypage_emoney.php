<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  마이페이지 예치금 리스트
	' +----------------------------------------------------------------------------------------------+*/

	$_tmp="";
	$_line=getModuleContent("mypage_emoney_list");
	while($em=emoneyLoop("")){
		$em[idx]=$idx;
		$_tmp .= lineValues("mypage_emoney_list", $_line, $em);
	}
	$_tmp=listContentSetting($_tmp, $_line);
	$_replace_code[$_file_name][mypage_emoney_list]=$_tmp;

?>