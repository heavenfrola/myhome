<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  마이페이지 내가 작성한 상품후기
	' +----------------------------------------------------------------------------------------------+*/

	$_rev_imgw = (!$_skin['mypage_review_list_prd_imgw']) ? 50 : $_skin['mypage_review_list_prd_imgw'];
	$_rev_imgh = (!$_skin['mypage_review_list_prd_imgw']) ? 50 : $_skin['mypage_review_list_prd_imgw'];

	$_tmp="";
	$_line=getModuleContent("mypage_review_list");
	while($review=contentList($_rev_imgw, $_rev_imgh)){
		$review[secret_i]=($review[secret]=="Y") ? $_prd_board_icon['secret'] : "";
		$review[new_i]=($review[new_check]) ? $_prd_board_icon['new'] : "";
		$review[reply_i]=($review[answer]) ? $_prd_board_icon['reply'] : "";
		$review[link2]="javascript:prdBoardView('review','".$review[no]."');";
		$review['title_nolink'] = cutstr($review['title'], 50);
		$review[title]="<a href=\"".$review[link2]."\">".cutstr($review[title],50)."</a>";
		$review['file_icon'] = ($review['atc']) ? $_prd_board_icon['file'] : "";
		$review[star]=reviewStar($_prd_board_icon['star']);
		$_tmp .= lineValues("mypage_review_list", $_line, $review, "", 2);
	}
	$_tmp=listContentSetting($_tmp, $_line);
	$_replace_code[$_file_name][mypage_review_list]=$_tmp;

	if($rno){
		$_replace_code[$_file_name][review_script]="
<script language=\"JavaScript\">
<!--
prdBoardView('review','".$rno."');
//-->
</script>";
	}

	// override (shop_detail_review.inc.php);
	require_once 'shop_detail_review.inc.php';
	$_replace_code[$_file_name]['detail_review_sort_lastest'] = '?rev_sort=1';
	$_replace_code[$_file_name]['detail_review_sort_pts_desc'] = '?rev_sort=2';
	$_replace_code[$_file_name]['detail_review_sort_pts_asc'] = '?rev_sort=3';
	$_replace_code[$_file_name]['detail_review_sort_recommend'] = '?rev_sort=4';
	$_replace_code[$_file_name]['detail_review_sort_lastest_sel'] = (!$_GET['rev_sort'] || $_GET['rev_sort'] == 1) ? 'selected' : '';
	$_replace_code[$_file_name]['detail_review_sort_pts_desc_sel'] = ($_GET['rev_sort'] == 2) ? 'selected' : '';
	$_replace_code[$_file_name]['detail_review_sort_pts_asc_sel'] = ($_GET['rev_sort'] == 3) ? 'selected' : '';
	$_replace_code[$_file_name]['detail_review_sort_recommend_sel'] = ($_GET['rev_sort'] == 4) ? 'selected' : '';

?>