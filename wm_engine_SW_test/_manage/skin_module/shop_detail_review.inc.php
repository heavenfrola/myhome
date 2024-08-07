<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  상품 상세 및 상품별 상품평 페이지 공통
	' +----------------------------------------------------------------------------------------------+*/

	if($_file_name == 'shop_detail.php' || $_file_name == 'shop_product_review.php') {
		$_replace_code[$_file_name]['detail_total_review'] = '';
		$_replace_hangul[$_file_name]['detail_total_review'] = '상품평개수';
		$_auto_replace[$_file_name]['detail_total_review'] = 'Y';
		$_code_comment[$_file_name]['detail_total_review'] = '해당 상품에 등록된 총 상품평 개수';

		$_replace_code[$_file_name]['detail_review_avg'] = '';
		$_replace_hangul[$_file_name]['detail_review_avg'] = '상품평평균평점';
		$_auto_replace[$_file_name]['detail_review_avg'] = 'Y';
		$_code_comment[$_file_name]['detail_review_avg'] = '상품평의 평균 평점이 소수점 1자리 까지 표시됩니다.';

		$_replace_code[$_file_name]['detail_review_per'] = '';
		$_replace_hangul[$_file_name]['detail_review_per'] = '상품평평균평점%';
		$_auto_replace[$_file_name]['detail_review_per'] = 'Y';
		$_code_comment[$_file_name]['detail_review_per'] = '상품평의 평균 평점이 만점 기준 퍼센테이지로 표시됩니다.';

		$_rev_pts_module_name = ($_file_name == 'shop_detail.php') ? 'detail_review_pts_list' : 'product_review_pts_list';
		$_replace_code[$_file_name][$_rev_pts_module_name] = '';
		$_replace_hangul[$_file_name][$_rev_pts_module_name] = '상품평점수리스트';
		$_code_comment[$_file_name][$_rev_pts_module_name] = '상품평 점수별 분포 리스트';
		$_replace_datavals[$_file_name][$_rev_pts_module_name] = '점수:rev_pts;게시글수:counts;평점비율:percent;최고평점여부:is_best;최저평점여부:is_worst;';
	}

	$add_link_pno = (isset($_GET['pno']) == true) ? '&pno='.$_GET['pno'] : '';

	$_replace_code[$_file_name]['detail_review_sort_lastest'] = "reloadProductBoard('review', '?rev_page=1{$add_link_pno}', 1); return false;";
	$_replace_hangul[$_file_name]['detail_review_sort_lastest'] = '상품평최신순';
	$_auto_replace[$_file_name]['detail_review_sort_lastest'] = 'Y';
	$_code_comment[$_file_name]['detail_review_sort_lastest'] = '상품평을 최신순으로 정렬합니다.';

	$_replace_code[$_file_name]['detail_review_sort_pts_desc'] = "reloadProductBoard('review', '?rev_page=1{$add_link_pno}', 2); return false;";
	$_replace_hangul[$_file_name]['detail_review_sort_pts_desc'] = '상품평평점높은순';
	$_auto_replace[$_file_name]['detail_review_sort_pts_desc'] = 'Y';
	$_code_comment[$_file_name]['detail_review_sort_pts_desc'] = '상품평을 평점이 높은순으로 정렬합니다.';

	$_replace_code[$_file_name]['detail_review_sort_pts_asc'] = "reloadProductBoard('review', '?rev_page=1{$add_link_pno}', 3); return false;";
	$_replace_hangul[$_file_name]['detail_review_sort_pts_asc'] = '상품평평점낮은순';
	$_auto_replace[$_file_name]['detail_review_sort_pts_asc'] = 'Y';
	$_code_comment[$_file_name]['detail_review_sort_pts_asc'] = '상품평을 평점이 낮은순으로 정렬합니다.';

	$_replace_code[$_file_name]['detail_review_sort_recommend'] = "reloadProductBoard('review', '?rev_page=1{$add_link_pno}', 4); return false;";
	$_replace_hangul[$_file_name]['detail_review_sort_recommend'] = '상품평추천순';
	$_auto_replace[$_file_name]['detail_review_sort_recommend'] = 'Y';
	$_code_comment[$_file_name]['detail_review_sort_recommend'] = '추천을 많이 받은 상품평순으로 정렬합니다.';

	$_replace_code[$_file_name]['detail_review_sort_lastest_sel'] = (!$_COOKIE['b_review_sort'] || $_COOKIE['b_review_sort'] == 1) ? 'selected' : '';
	$_replace_hangul[$_file_name]['detail_review_sort_lastest_sel'] = '상품평최신순선택';
	$_auto_replace[$_file_name]['detail_review_sort_lastest_sel'] = 'Y';
	$_code_comment[$_file_name]['detail_review_sort_lastest_sel'] = '상품평 정렬 기준이 최신순일 경우 selected 문자열이 출력됩니다.';

	$_replace_code[$_file_name]['detail_review_sort_pts_desc_sel'] = ($_COOKIE['b_review_sort'] == 2) ? 'selected' : '';
	$_replace_hangul[$_file_name]['detail_review_sort_pts_desc_sel'] = '상품평평점높은순선택';
	$_auto_replace[$_file_name]['detail_review_sort_pts_desc_sel'] = 'Y';
	$_code_comment[$_file_name]['detail_review_sort_pts_desc_sel'] = '상품평 정렬 기준이 평점 높은순일 경우 selected 문자열이 출력됩니다.';

	$_replace_code[$_file_name]['detail_review_sort_pts_asc_sel'] = ($_COOKIE['b_review_sort'] == 3) ? 'selected' : '';
	$_replace_hangul[$_file_name]['detail_review_sort_pts_asc_sel'] = '상품평평점낮은순선택';
	$_auto_replace[$_file_name]['detail_review_sort_pts_asc_sel'] = 'Y';
	$_code_comment[$_file_name]['detail_review_sort_pts_asc_sel'] = '상품평 정렬 기준이 평점 낮은순일 경우 selected 문자열이 출력됩니다.';

	$_replace_code[$_file_name]['detail_review_sort_recommend_sel'] = ($_COOKIE['b_review_sort'] == 4) ? 'selected' : '';
	$_replace_hangul[$_file_name]['detail_review_sort_recommend_sel'] = '상품평추천순선택';
	$_auto_replace[$_file_name]['detail_review_sort_recommend_sel'] = 'Y';
	$_code_comment[$_file_name]['detail_review_sort_recommend_sel'] = '상품평 정렬 기준이 추천순일 경우 selected 문자열이 출력됩니다.';

    if (
        ($cfg['product_review_auth'] == '3' || $cfg['product_review_auth'] == '4')
        || ($_file_name === 'shop_detail.php' && in_array($prd['prd_type'], array('4', '5', '6')))
    ) {
        //세트상품은 상세페이지에서 작성 금지. 그 외 설정조건 확인
        $_replace_code[$_file_name]['detail_review_write'] = '';
    } else {
        $_replace_code[$_file_name]['detail_review_write'] = 'Y';
    }
    $_replace_hangul[$_file_name]['detail_review_write'] = '상품평작성권한';
    $_auto_replace[$_file_name]['detail_review_write'] = 'Y';
    $_code_comment[$_file_name]['detail_review_write'] = '상품 상세, 후기 목록 등의 일반 페이지에서 상품평 후기 작성이 가능할 경우 Y 문자가 출력됩니다.';

?>