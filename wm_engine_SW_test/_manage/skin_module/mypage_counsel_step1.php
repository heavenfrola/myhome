<?PHP

	$_replace_code[$_file_name]['form_start']="";
	$_replace_hangul[$_file_name]['form_start']="폼시작";
	$_code_comment[$_file_name]['form_start']="고객 상담 폼 시작 선언";
	$_auto_replace[$_file_name]['form_start']="Y";

	$_replace_code[$_file_name]['mypage_1to1_logout']="";
	$_replace_hangul[$_file_name]['mypage_1to1_logout']="비회원상담연락처";
	$_code_comment[$_file_name]['mypage_1to1_logout']="비회원 상담시 연락처 입력 구문";
	$_replace_datavals[$_file_name]['mypage_1to1_logout']="주문자명:buyer_name;주문자연락처:buyer_phone;주문자이메일:buyer_email;";

	$_replace_code[$_file_name]['mypage_1to1_cate']="";
	$_replace_hangul[$_file_name]['mypage_1to1_cate']="고객상담구분";
	$_code_comment[$_file_name]['mypage_1to1_cate']="상담 구분 존재시 출력 구문";
	$_replace_datavals[$_file_name]['mypage_1to1_cate']="상담구분:cate_str;";

	$_replace_code[$_file_name]['mypage_1to1_ordinfo']="";
	$_replace_hangul[$_file_name]['mypage_1to1_ordinfo']="주문관련상담";
	$_code_comment[$_file_name]['mypage_1to1_ordinfo']="주문 관련 상담일 경우 주문 정보 출력 구문";
	$_replace_datavals[$_file_name]['mypage_1to1_ordinfo']="주문번호:ono;주문상품:title;";

	$_replace_code[$_file_name]['form_end']="";
	$_replace_hangul[$_file_name]['form_end']="폼끝";
	$_code_comment[$_file_name]['form_end']="고객 상담 폼 끝 선언";
	$_auto_replace[$_file_name]['form_end']="Y";

	$_replace_code[$_file_name]['mypage_1to1_prd_list'] = '';
	$_replace_hangul[$_file_name]['mypage_1to1_prd_list'] = '취소대상상품리스트';
	$_code_comment[$_file_name]['mypage_1to1_prd_list'] = '취소대상 상품을 출력합니다.';
	$_replace_datavals[$_file_name]['mypage_1to1_prd_list'] = '상품선택:checkbox;상품명:name;상품옵션:option_str;주문수량:buy_ea;상품이미지:img;상품이미지정보:img_str;주문금액:total_prc;결제금액:pay_prc;주문상태:stat;';

	$_replace_code[$_file_name]['mypage_1to1_cap_use']="";
	$_replace_hangul[$_file_name]['mypage_1to1_cap_use']="자동생성방지";
	$_code_comment[$_file_name]['mypage_1to1_cap_use']="자동생성방지 사용(리캡차)";
	$_auto_replace[$_file_name]['mypage_1to1_cap_use']="Y";

	$_replace_code[$_file_name]['mypage_claimreason_list'] = '';
	$_replace_hangul[$_file_name]['mypage_claimreason_list'] = '취소사유리스트';
	$_code_comment[$_file_name]['mypage_claimreason_list'] = '주문취소사유를 선택할수 있는 리스트 출력';
	$_replace_datavals[$_file_name]['mypage_claimreason_list'] = '취소사유:reason;';

	$_replace_code[$_file_name]['mypage_previous_url'] = '';
	$_replace_hangul[$_file_name]['mypage_previous_url'] = '이전페이지주소';
	$_code_comment[$_file_name]['mypage_previous_url'] = '1:1문의 작성 중 취소 시 이전 페이지로 이동';
	$_auto_replace[$_file_name]['mypage_previous_url'] = 'Y';

?>