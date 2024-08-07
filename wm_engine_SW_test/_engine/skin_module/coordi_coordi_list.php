<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  코디샵 리스트
	' +----------------------------------------------------------------------------------------------+*/

	$_line=getModuleContent("coordi_list");
	$_para1=($_skin[coordi_list_imgw]) ? $_skin[coordi_list_imgw] : 500;
	$_para2=($_skin[coordi_list_imgh]) ? $_skin[coordi_list_imgh] : 500;
	$_para3=($_skin[coordi_list_cols]) ? $_skin[coordi_list_cols] : 0;
	$_para3_2=($_skin[coordi_list_rows]) ? $_skin[coordi_list_rows] : 0;
	$_total_prd=$_para3*$_para3_2;
	$_para5=($_skin[coordi_list_contentcut]) ? $_skin[coordi_list_contentcut] : 500;
	ob_start();
	// 코디목록출력 : 이미지가로, 이미지세로, 칸넘기기, 페이지당 총개수, 설명글 자르기
	while($cds=coordiList($_para1, $_para2, $_para3, $_total_prd, $_para5)){
		$cds[img]="<img src=\"".$cds[img_src]."\" ".$cds[img_size].">";
		$cds[img_link]="<a href=\"".$cds['link']."\">".$cds[img]."</a>";
		$cds[name_link]="<a href=\"".$cds['link']."\">".$cds[name]."</a>";
		echo lineValues("coordi_list", $_line, $cds);
	}
	$_tmp=ob_get_contents();
	ob_end_clean();
	$_tmp=listContentSetting($_tmp, $_line);
	$_replace_code[$_file_name][coordi_list]=$_tmp;

?>