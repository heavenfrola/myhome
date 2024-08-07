<?PHP

	$_replace_code[$_file_name]['search_result_total_product'] = "";
	$_replace_hangul[$_file_name]['search_result_total_product'] = "총상품개수";
	$_auto_replace[$_file_name]['search_result_total_product'] = "Y";
	$_code_comment[$_file_name]['search_result_total_product'] = "총 검색된 상품의 수";

	$_replace_code[$_file_name]['form_start'] = "";
	$_replace_hangul[$_file_name]['form_start'] = "폼시작";
	$_code_comment[$_file_name]['form_start'] = "검색 폼 시작 선언";
	$_auto_replace[$_file_name]['form_start'] = "Y";

	$_replace_code[$_file_name]['form_end'] = "";
	$_replace_hangul[$_file_name]['form_end'] = "폼끝";
	$_code_comment[$_file_name]['form_end'] = "검색 폼 끝 선언";
	$_auto_replace[$_file_name]['form_end'] = "Y";

	$_replace_code[$_file_name]['search1'] = "";
	$_replace_hangul[$_file_name]['search1'] = "검색어1";
	$_code_comment[$_file_name]['search1'] = "검색중인 단어 (일반 출력용)";
	$_auto_replace[$_file_name]['search1'] = "Y";

	$_replace_code[$_file_name]['search2'] = "";
	$_replace_hangul[$_file_name]['search2'] = "검색어2";
	$_code_comment[$_file_name]['search2'] = "검색중인 단어 (input 박스 출력용)";
	$_auto_replace[$_file_name]['search2'] = "Y";

	$_replace_code[$_file_name]['search_result_product_sort'] = "";
	$_replace_hangul[$_file_name]['search_result_product_sort'] = "상품정렬선택";
	$_code_comment[$_file_name]['search_result_product_sort'] = "상품 정렬 설정의 콤보박스/라디오 버튼 형태";
	$_auto_replace[$_file_name]['search_result_product_sort'] = "Y";

	$_replace_code[$_file_name]['search_result_sort_list'] = "";
	$_replace_hangul[$_file_name]['search_result_sort_list'] = "상품정렬선택리스트";
	$_replace_datavals[$_file_name]['search_result_sort_list'] = "정렬선택링크:link;정렬선택명:name;";
	$_code_comment[$_file_name]['search_result_sort_list'] = "상품 정렬 설정의 사용자 지정 형태";

	$_replace_code[$_file_name]['search_cate_list'] = "";
	$_replace_hangul[$_file_name]['search_cate_list'] = "카테고리리스트";
	$_code_comment[$_file_name]['search_cate_list'] = "검색어를 포함한 상품의 카테고리 리스트";
	$_replace_datavals[$_file_name]['search_cate_list'] = "카테고리명:name:해당 카테고리 조회 링크를 포함한 카테고리명 출력;총상품개수:total;";

	$_replace_code[$_file_name]['search_xbig_list'] = "";
	$_replace_hangul[$_file_name]['search_xbig_list'] = "이차분류리스트";
	$_code_comment[$_file_name]['search_xbig_list'] = '검색어를 포함한 상품의 '.$cfg['xbig_name_mng'].' 리스트';
	$_replace_datavals[$_file_name]['search_xbig_list'] = $_replace_datavals[$_file_name]['search_cate_list'];

	$_replace_code[$_file_name]['search_ybig_list'] = "";
	$_replace_hangul[$_file_name]['search_ybig_list'] = "삼차분류리스트";
	$_code_comment[$_file_name]['search_ybig_list'] = '검색어를 포함한 상품의 '.$cfg['ybig_name_mng'].' 리스트';
	$_replace_datavals[$_file_name]['search_ybig_list'] = $_replace_datavals[$_file_name]['search_cate_list'];

	$_replace_code[$_file_name]['search_rank_list'] = "";
	$_replace_hangul[$_file_name]['search_rank_list'] = "검색어순위리스트";
	$_code_comment[$_file_name]['search_rank_list'] = "검색어순위 리스트";
	$_replace_datavals[$_file_name]['search_rank_list'] = "검색어순위:ridx;검색어:keyword;";

	$_replace_code[$_file_name]['search_result_product_list'] = "";
	$_replace_hangul[$_file_name]['search_result_product_list'] = "상품리스트";
	$_code_comment[$_file_name]['search_result_product_list'] = "상품의 리스트";
	$_replace_datavals[$_file_name]['search_result_product_list'] = $_replace_datavals['common_module']['product_box'];

	$_replace_code[$_file_name]['nextpage_link'] = '';
	$_replace_hangul[$_file_name]['nextpage_link'] = '다음페이지링크';
	$_code_comment[$_file_name]['nextpage_link'] = '다음 페이지가 있을 경우 링크를 출력';
	$_auto_replace[$_file_name]['nextpage_link'] = 'Y';

?>