<?PHP

	$_replace_code[$_file_name][$_mypage.'review_list']="";
	$_replace_hangul[$_file_name][$_mypage.'review_list']="상품평리스트";
	$_code_comment[$_file_name][$_mypage.'review_list']="상품평 리스트";
	$_replace_datavals[$_file_name][$_mypage.'review_list']=$_user_code_typec['review'];

	$_replace_code[$_file_name]['review_title_sel']="";
	$_replace_hangul[$_file_name]['review_title_sel']="제한제목목록";
	$_code_comment[$_file_name]['review_title_sel']="관리자자가 지정한 제목중에서만 선택할수 있는 제목 입력란";
	$_replace_datavals[$_file_name]['review_title_sel'] = "글제목:title:글 수정시 글제목 출력;";

	$_replace_code[$_file_name]['review_script']="";
	$_replace_hangul[$_file_name]['review_script']="하단스크립트";
	$_code_comment[$_file_name]['review_script']="글 선택시 출력 스크립트(필수)";
	$_auto_replace[$_file_name]['review_script']="Y";

    //페이지 디자인 코드 추가
    $_replace_code[$_file_name]['product_review_pageres']="";
    $_replace_hangul[$_file_name]['product_review_pageres']="상품평페이지선택";
    $_auto_replace[$_file_name]['product_review_pageres']="Y";
    $_code_comment[$_file_name]['product_review_pageres']="해당 상품의 상품평 리스트 페이지 선택";

	require 'shop_detail_review.inc.php'; // 공통 후기 데이터

?>