<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  상품상세 이미지 크게보기
	' +----------------------------------------------------------------------------------------------+*/

	$_replace_code[$_file_name]['prd_name']=$prd['name'];
	$_replace_code[$_file_name]['prd_zoom_img']="<div id=\"mimg_div\">".mainImg($_skin['prd_img_bimgw'],$_skin['prd_img_bimgh'],1)."</div>";
	$_replace_code[$_file_name]['prd_img1']=attatchPrdImg($_skin['prd_img_simgw'],$_skin['prd_img_simgh'],1);

	$wisa_max = $pdo->row("
        select count(*) from {$tbl['product_image']} a inner join {$tbl['product']} b on a.pno=b.no
        where b.no=:no and a.filetype in ('2', '8')
    ", array(
        ':no' => $prd['parent']
    ));
	$_tmp="";
	$_line=getModuleContent("prd_img_list");
	for($wisa=1; $wisa<=$wisa_max; $wisa++){
		$_img[img]=attatchPrdImg($_skin['prd_img_simgw'],$_skin['prd_img_simgh'],1);
		$_tmp .= lineValues("prd_img_list", $_line, $_img);
	}
	$_tmp=listContentSetting($_tmp, $_line);
	$_replace_code[$_file_name]['prd_img_list']=$_tmp;

?>