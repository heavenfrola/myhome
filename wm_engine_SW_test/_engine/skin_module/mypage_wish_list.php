<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  위시리스트
	' +----------------------------------------------------------------------------------------------+*/

	$row = $_skin['mypage_wish_list_cols']*$_skin['mypage_wish_list_rows'];

	include_once $engine_dir."/_engine/include/paging.php";
	$page = numberOnly($_GET['page']);
	if($page <= 1) $page = 1;
	if(!$row) $row = 20;
	if(!$block) $block = 10;
	$QueryString = "";

	$NumTotalRec = $pdo->row("select count(*) from `".$tbl['product']."` p inner join `$tbl[wish]` w on p.no=w.pno where w.`member_no`='$member[no]' and p.`stat`!='4' order by w.`no` desc");
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString($QueryString);
	$PagingResult = $PagingInstance->result($pg_dsn);
	$sql .= $PagingResult['LimitQuery'];

	$pageRes = $PagingResult['PageLink'];
	$wishRes = $pdo->iterator($sql);
	$idx = $NumTotalRec-($row*($page-1))+1;

	$GLOBALS['total_wish'] = $NumTotalRec;

	$_replace_code[$_file_name][form_start]="<form name=\"wishFrm\" method=\"post\" action=\"".$root_url."/main/exec.php\" target=\"hidden".$now."\" class=\"boxMiddleright\">
<input type=\"hidden\" name=\"exec_file\" value=\"mypage/wish.exe.php\">";

	$_tmp="";
	$_line=getModuleContent("mypage_wish_list");
	$_para1=($_skin[mypage_wish_list_imgw]) ? $_skin[mypage_wish_list_imgw] : 50;
	$_para2=($_skin[mypage_wish_list_imgh]) ? $_skin[mypage_wish_list_imgh] : 50;
	$_para3=($_skin[mypage_wish_list_btw_opt]) ? $_skin[mypage_wish_list_btw_opt] : "";
	$_tr = $_skin['mypage_wish_list_disable_tr'];
	$_cols = $_skin['mypage_wish_list_cols'];
	while($wish=wishList(3,$_para1,$_para2, $_para3)){
		if($_tr == 'Y' && $widx%$_cols == 1 && $widx > 1) $_tmp .= '<tr>';
		$wish[widx]=$widx;
		$wish[img]="<a href=\"".$wish['link']."\"><img src=\"".$wish[img]."\" ".$wish[imgstr]." border=\"0\"></a>";
		$wish[name]="<a href=\"".$wish['link']."\" target=\"_parent\">".$wish[name]."</a>";
		$wish[checkbox]="<input type=\"checkbox\" name=\"wno[]\" id=\"wno\" value=\"".$wish[wno]."\">";
		$wish['del_link'] = "<a href='#' onclick='deletePartWishAjax($wish[wno]); return false;'>";
		$wish['cart_link'] = "<a href='#' onclick='cartPartWishAjax($wish[wno]); return false;'>";
		$_tmp .= lineValues("mypage_wish_list", $_line, $wish);
		if($_tr == 'Y' && $widx%$_cols == 0 && $NumTotalRec != $widx) $_tmp .= '</tr>';
	}
	if($_tr == 'Y' && $widx%$_cols != 0) {
		for($i = 0; $i < $_cols-($widx%$_cols); $i++) {
			$_tmp .= '<td class="empty_cell"></td>';
		}
	}

	$_tmp=listContentSetting($_tmp, $_line);
	$_replace_code[$_file_name][mypage_wish_list]=$_tmp;
	unset($_tmp, $_line, $_para1, $_para2, $_para3, $_tr, $_cols);

	$_replace_code[$_file_name][wish_cart_url]="javascript:cartWish(document.wishFrm);";
	$_replace_code[$_file_name][wish_del_url]="javascript:deleteWish(document.wishFrm);";
	$_replace_code[$_file_name]['wish_trnc_url']="javascript:truncateWish(document.wishFrm);";

	$_replace_code[$_file_name][form_end]="<input type=\"hidden\" name=\"total_wish\" value=\"".$total_wish."\">
<input type=\"hidden\" name=\"exec\" value=\"\">
</form>";

?>