<?php

	$_replace_code[$_file_name]['total_product'] = "";
	$_replace_hangul[$_file_name]['total_product'] = "총상품개수";
	$_code_comment[$_file_name]['total_product'] = "총 등록된 상품의 수";
	$_auto_replace[$_file_name]['total_product'] = "Y";

	$_replace_code[$_file_name]['cate_name'] = "";
	$_replace_hangul[$_file_name]['cate_name'] = "카테고리명";
	$_code_comment[$_file_name]['cate_name'] = "선택된 카테고리명";
	$_auto_replace[$_file_name]['cate_name'] = "Y";

	$_replace_code[$_file_name]['cate_cno1'] = "";
	$_replace_hangul[$_file_name]['cate_cno1'] = "카테고리코드";
	$_code_comment[$_file_name]['cate_cno1'] = "선택된 카테고리 코드";
	$_auto_replace[$_file_name]['cate_cno1'] = "Y";

	$_replace_code[$_file_name]['cate_cno2'] = $_cno2['no'];
	$_replace_hangul[$_file_name]['cate_cno2'] = "서브카테고리코드";
	$_code_comment[$_file_name]['cate_cno2'] = "선택된 서브 카테고리 코드";
	$_auto_replace[$_file_name]['cate_cno2'] = "Y";

	$_replace_code[$_file_name]['cate_101'] = $_cno1[101];
	$_replace_hangul[$_file_name]['cate_101'] = "대분류코드";
	$_code_comment[$_file_name]['cate_101'] = "선택된 카테고리의 대분류 코드";
	$_auto_replace[$_file_name]['cate_101'] = "Y";

	$_replace_code[$_file_name]['cate_102'] = $_cno1[102];
	$_replace_hangul[$_file_name]['cate_102'] = "중분류코드";
	$_code_comment[$_file_name]['cate_102'] = "선택된 카테고리의 중분류 코드";
	$_auto_replace[$_file_name]['cate_102'] = "Y";

	$_replace_code[$_file_name]['cate_ctype'] = "";
	$_replace_hangul[$_file_name]['cate_ctype'] = "카테고리타입";
	$_code_comment[$_file_name]['cate_ctype'] = "선택된 카테고리타입(1:일반분류, 2:기획전";
	if($cfg['xbig_mng'] == "Y") $_code_comment[$_file_name]['cate_ctype'] .= ", 4:".$cfg['xbig_name_mng'];
	if($cfg['ybig_mng'] == "Y") $_code_comment[$_file_name]['cate_ctype'] .= ", 5:".$cfg['ybig_name_mng'];
	$_code_comment[$_file_name]['cate_ctype'] .= ")";
	$_auto_replace[$_file_name]['cate_ctype'] = "Y";

	$_replace_code[$_file_name]['cate_level'] = "";
	$_replace_hangul[$_file_name]['cate_level'] = "카테고리레벨";
	$_code_comment[$_file_name]['cate_level'] = "선택된 카테고리레벨(1:대분류, 2:중분류, 3:소분류)";
	$_auto_replace[$_file_name]['cate_level'] = "Y";

	$_replace_code[$_file_name]['cate_upfile1'] = '';
	$_replace_hangul[$_file_name]['cate_upfile1'] = '분류타이틀이미지';
	$_code_comment[$_file_name]['cate_upfile1'] = '선택된 분류에 업로드 된 타이틀이미지';
	$_auto_replace[$_file_name]['cate_upfile1'] = 'Y';

	$_replace_code[$_file_name]['product_sort'] = "";
	$_replace_hangul[$_file_name]['product_sort'] = "상품정렬선택";
	$_code_comment[$_file_name]['product_sort'] = "상품 정렬 설정의 콤보박스/라디오 버튼 형태";
	$_auto_replace[$_file_name]['product_sort'] = "Y";

	$_replace_code[$_file_name]['sort_list'] = "";
	$_replace_hangul[$_file_name]['sort_list'] = "상품정렬선택리스트";
	$_replace_datavals[$_file_name]['sort_list'] = "정렬선택링크:link;정렬선택명:name;선택된콤보박스:chk1:현재 정렬조건일 경우 selected 가 출력됩니다.;선택라디오버튼:chk2:현재 정렬조건일 경우 checked 가 출력됩니다.;";
	$_code_comment[$_file_name]['sort_list'] = "상품 정렬 설정의 사용자 지정 형태";

	$_replace_code[$_file_name]['product_list'] = "";
	$_replace_hangul[$_file_name]['product_list'] = "상품리스트";
	$_code_comment[$_file_name]['product_list'] = "상품의 리스트";
	$_replace_datavals[$_file_name]['product_list'] = $_replace_datavals['common_module']['product_box'];

	$_replace_code[$_file_name]['nextpage_link'] = '';
	$_replace_hangul[$_file_name]['nextpage_link'] = '다음페이지링크';
	$_code_comment[$_file_name]['nextpage_link'] = '다음 페이지가 있을 경우 링크를 출력';
	$_auto_replace[$_file_name]['nextpage_link'] = 'Y';

?>