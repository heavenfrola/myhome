<?PHP

	$_replace_code[$_file_name]['prdcpn_form_start'] = '';
	$_replace_hangul[$_file_name]['prdcpn_form_start'] = '폼시작';
	$_auto_replace[$_file_name]['prdcpn_form_start'] = 'Y';
	$_code_comment[$_file_name]['prdcpn_form_start'] = '상품 상세페이지 폼 시작 선언';

	$_replace_code[$_file_name]['prdcpn_form_end'] = '';
	$_replace_hangul[$_file_name]['prdcpn_form_end'] = '폼끝';
	$_auto_replace[$_file_name]['prdcpn_form_end'] = 'Y';
	$_code_comment[$_file_name]['prdcpn_form_end'] = '상품 상세페이지 폼 끝 선언';

	$_replace_code[$_file_name]['detail_cpn_use_list'] = '';
	$_replace_hangul[$_file_name]['detail_cpn_use_list'] = '쿠폰적용대상상품목록';
	$_replace_datavals[$_file_name]['detail_cpn_use_list'] = '상품이미지:img;상품명:name;옵션명:option_name;주문수량:buy_ea;상품가격:sell_prc;판매금액:total_prc;적용가능쿠폰목록:detail_cpn_sel_list::editable;';
	$_code_comment[$_file_name]['detail_cpn_use_list'] = '쿠폰 적용 가능한 상품의 목록을 출력합니다.';

	$_replace_code[$_file_name]['detail_cpn_sel_list'] = '';
	$_replace_hangul[$_file_name]['detail_cpn_sel_list'] = '적용가능쿠폰목록';
	$_replace_datavals[$_file_name]['detail_cpn_sel_list'] = '선택체크박스:checkbox;쿠폰명:name;할인율:sale_prc:할인율 또는 할인금액;사용만료일:ufinish_date;중복사용불가:duplication;';
	$_code_comment[$_file_name]['detail_cpn_sel_list'] = '쿠폰 적용 가능한 상품의 목록을 출력합니다.';
	$_code_sub[$_file_name]['detail_cpn_sel_list'] = 'Y'; // detail_cpn_use_list 모듈의 하위 모듈로 작동

?>