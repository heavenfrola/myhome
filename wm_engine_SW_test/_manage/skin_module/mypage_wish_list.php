<?PHP

	$_replace_code[$_file_name]['form_start']="";
	$_replace_hangul[$_file_name]['form_start']="폼시작";
	$_code_comment[$_file_name]['form_start']="위시리스트 폼 시작 선언";
	$_auto_replace[$_file_name]['form_start']="Y";

	$_replace_code[$_file_name]['mypage_wish_list']="";
	$_replace_hangul[$_file_name]['mypage_wish_list']="위시리스트";
	$_code_comment[$_file_name]['mypage_wish_list']="찜한 상품 리스트";
	$_replace_datavals[$_file_name]['mypage_wish_list']="내역번호:widx;상품이미지:img;상품명:name;상품옵션:option:체크 장바구니 기능 사용 시 선택가능한 옵션 출력;상품가격:sell_prc_str;상품적립금:milage_str;체크박스:checkbox;상품삭제:del_link:해당 상품을 위시리스트에서 삭제하는 링크(A태그)를 출력합니다.;장바구니담기:cart_link:해당 상품을 장바구니에 담는 링크(A태그)를 출력합니다.;상품링크:link:상품 상세페이지로 이동하는 URL을 출력합니다.;상품요약설명:content1:해당 상품에 등록된 요약 설명;상품품절:sold_out:해당 상품이 품절일 경우 'out' 문자 출력;";

	$_replace_code[$_file_name]['wish_cart_url']="";
	$_replace_hangul[$_file_name]['wish_cart_url']="체크장바구니";
	$_code_comment[$_file_name]['wish_cart_url']="체크한 상품 장바구니 담기 링크 주소 출력";
	$_auto_replace[$_file_name]['wish_cart_url']="Y";

	$_replace_code[$_file_name]['wish_del_url']="";
	$_replace_hangul[$_file_name]['wish_del_url']="체크삭제";
	$_code_comment[$_file_name]['wish_del_url']="체크한 상품 삭제 하기 링크 주소 출력";
	$_auto_replace[$_file_name]['wish_del_url']="Y";

	$_replace_code[$_file_name]['wish_trnc_url'] = '';
	$_replace_hangul[$_file_name]['wish_trnc_url'] = '위시리스트비우기';
	$_code_comment[$_file_name]['wish_trnc_url'] = '해당 회원의 전체 위시리스트를 삭제합니다.';
	$_auto_replace[$_file_name]['wish_trnc_url'] = 'Y';

	$_replace_code[$_file_name]['form_end']="";
	$_replace_hangul[$_file_name]['form_end']="폼끝";
	$_code_comment[$_file_name]['form_end']="위시리스트 폼 끝 선언";
	$_auto_replace[$_file_name]['form_end']="Y";

?>