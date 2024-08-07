<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  상품리뷰 상세
	' +----------------------------------------------------------------------------------------------+*/

	$_tmp = "";
	$_line = getModuleContent("review_list");
	$_para1 = (!$_skin['review_list_imgw']) ? 1000 : $_skin['review_list_imgw'];
	$_para2 = (!$_skin['review_list_imgh']) ? 1000 : $_skin['review_list_imgh'];
	$_para3 = (!$_skin['review_list_titlecut']) ? 100 : $_skin['review_list_titlecut'];
    $_para4 = (!$_skin['review_list_rows']) ? 10 : $_skin['review_list_rows'];

	$_oprd = ($prd['no']) ? "r." : "";
	$rev_order = ($_skin['review_list_best_use'] == "Y") ? " ".$_oprd."`stat` desc, ".$_oprd."`reg_date` desc " : " ".$_oprd."`reg_date` desc ";
	while($review = reviewList($_para1, $_para2, $_para4)){
		$review['link2'] = "javascript:prdBoardView('review','".$review['no']."');";
		$review['title_nolink'] = cutstr($review['title'], $_para3);
		$review['title'] = "<a href=\"".$review['link2']."\">".cutstr($review['title'], $_para3)."</a>";
		$review['file_icon'] = ($review['atc']) ? $_prd_board_icon['file'] : "";
		$review['new_i'] = ($review['new_check']) ? $_prd_board_icon['new'] : "";
		$review['star'] = reviewStar($_prd_board_icon['star']);
		$_tmp .= lineValues("review_list", $_line, $review, "", 2);
	}
	$_tmp = listContentSetting($_tmp, $_line);
	$_replace_code[$_file_name]['review_list'] = $_tmp;

    //페이징 영역 추가
    $_replace_code[$_file_name]['product_review_pageres'] = $rev_pageRes;

	$rno = numberOnly($_GET['rno']);
	if($rno){
		$_replace_code[$_file_name]['review_script']="
<script type='text/javascript'>
<!--
$(window).ready(function() {
prdBoardView('review','".$rno."');
	});
//-->
</script>";
	}

	require 'shop_detail_review.inc.php'; // 공통 후기 데이터

	// override (shop_detail_review.inc.php);
	$_replace_code[$_file_name]['detail_review_sort_lastest'] = makeQueryString(true, 'rev_sort').'&rev_sort=1';
	$_replace_code[$_file_name]['detail_review_sort_pts_desc'] = makeQueryString(true, 'rev_sort').'&rev_sort=2';
	$_replace_code[$_file_name]['detail_review_sort_pts_asc'] = makeQueryString(true, 'rev_sort').'&rev_sort=3';
	$_replace_code[$_file_name]['detail_review_sort_recommend'] = makeQueryString(true, 'rev_sort').'&rev_sort=4';
	$_replace_code[$_file_name]['detail_review_sort_lastest_sel'] = (!$_GET['rev_sort'] || $_GET['rev_sort'] == 1) ? 'selected' : '';
	$_replace_code[$_file_name]['detail_review_sort_pts_desc_sel'] = ($_GET['rev_sort'] == 2) ? 'selected' : '';
	$_replace_code[$_file_name]['detail_review_sort_pts_asc_sel'] = ($_GET['rev_sort'] == 3) ? 'selected' : '';
	$_replace_code[$_file_name]['detail_review_sort_recommend_sel'] = ($_GET['rev_sort'] == 4) ? 'selected' : '';

?>