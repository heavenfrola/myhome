<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  코디샵 상세보기
	' +----------------------------------------------------------------------------------------------+*/

	$_replace_code[$_file_name][prd_name]=$cdv[name];
	$_replace_code[$_file_name][prd_content]=$cdv[content];

	$_line=getModuleContent("coordi_ref_prd_list");
	$_para1=($_skin[coordi_ref_prd_list_imgw]) ? $_skin[coordi_ref_prd_list_imgw] : 500;
	$_para2=($_skin[coordi_ref_prd_list_imgh]) ? $_skin[coordi_ref_prd_list_imgh] : 500;
	$_para3=($_skin[coordi_ref_prd_list_cols]) ? $_skin[coordi_ref_prd_list_cols] : 10;
	ob_start();
	while($cds_prd=cdsPrds($_para1,$_para2,$_para3)){
		$cds_prd[img]="<img src=\"".$cds_prd[img_src]."\" ".$cds_prd[img_size]." border=\"0\">";
		$cds_prd[img_link]="<a href=\"".$cds_prd['link']."\">".$cds_prd[img]."</a>";
		$cds_prd[name_link]="<a href=\"".$cds_prd['link']."\">".$cds_prd[name]."</a>";
		echo lineValues("coordi_ref_prd_list", $_line, $cds_prd);
	}
	$_tmp=ob_get_contents();
	ob_end_clean();
	$_tmp=listContentSetting($_tmp, $_line);
	$_replace_code[$_file_name][coordi_ref_prd_list]=$_tmp;

	$_replace_code[$_file_name][list_url]=$rURL;

?>