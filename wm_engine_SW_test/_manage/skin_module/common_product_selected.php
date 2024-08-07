<?PHP

	$_replace_code[$_file_name]['common_product_selected_list']="";
	$_replace_hangul[$_file_name]['common_product_selected_list']="선택된상품리스트";
	$_code_comment[$_file_name]['common_product_selected_list']="선택할 상품 리스트 출력";
	$_replace_datavals[$_file_name]['common_product_selected_list'] = $_replace_datavals['common_module']['product_box'].'관련상품제거링크:remove_link;';

	$_replace_code[$_file_name]['common_product_search_link'] = "<a href='#addProduct' onclick='addRefProduct($data[no]); return false;'>";
	$_replace_hangul[$_file_name]['common_product_search_link']="글관련상품추가링크";
	$_code_comment[$_file_name]['common_product_search_link']="게시물 관련상품 추가하기 버튼의 링크 출력";
	$_auto_replace[$_file_name]['common_product_search_link']="Y";

?>