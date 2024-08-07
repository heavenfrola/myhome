<?php

    $_replace_code[$_file_name]['order_cpn_layer_list'] = "";
	$_replace_hangul[$_file_name]['order_cpn_layer_list'] = "레이어쿠폰리스트";
	$_code_comment[$_file_name]['order_cpn_layer_list'] = "사용 가능한 보유 쿠폰 리스트";
	$_replace_datavals[$_file_name]['order_cpn_layer_list'] = "쿠폰선택:radio;쿠폰코드:code;쿠폰명:name;쿠폰할인금액:sale_prc;쿠폰사용기간:udate_type;쿠폰최소구매금액:prc_limit;쿠폰최고할인금액:sale_limit_k;쿠폰고유번호:cno;클릭이벤트:onclick;데이터옵션:dataopt;선택여부:selected;";

    $_replace_code[$_file_name]['has_prdcpn'] = "";
	$_replace_hangul[$_file_name]['has_prdcpn'] = "개별상품쿠폰보유여부";
	$_code_comment[$_file_name]['has_prdcpn'] = "접속 회원에게 사용 가능한 개별상품 쿠폰이 있는지 출력합니다.";

    $_replace_code[$_file_name]['order_prd_cpn_use_list'] = '';
	$_replace_hangul[$_file_name]['order_prd_cpn_use_list'] = '쿠폰적용대상상품목록';
	$_replace_datavals[$_file_name]['order_prd_cpn_use_list'] = '상품이미지:img;상품명:name;옵션명:option_name;주문수량:buy_ea;상품가격:sell_prc;판매금액:total_prc;적용가능쿠폰목록:order_prd_cpn_sel_list::editable;';
	$_code_comment[$_file_name]['order_prd_cpn_use_list'] = '쿠폰 적용 가능한 상품의 목록을 출력합니다.';

	$_replace_code[$_file_name]['order_prd_cpn_sel_list'] = '';
	$_replace_hangul[$_file_name]['order_prd_cpn_sel_list'] = '적용가능쿠폰목록';
	$_replace_datavals[$_file_name]['order_prd_cpn_sel_list'] = '선택체크박스:checkbox;쿠폰명:name;할인율:sale_prc:할인율 또는 할인금액;사용만료일:ufinish_date;중복사용불가:duplication;';
	$_code_comment[$_file_name]['order_prd_cpn_sel_list'] = '쿠폰 적용 가능한 상품의 목록을 출력합니다.';
	$_code_sub[$_file_name]['order_prd_cpn_sel_list'] = 'Y'; // detail_cpn_use_list 모듈의 하위 모듈로 작동

    $_replace_code[$_file_name]['order_prdcpn_form_start'] = '';
	$_replace_hangul[$_file_name]['order_prdcpn_form_start'] = '개별상품쿠폰폼시작';
	$_auto_replace[$_file_name]['order_prdcpn_form_start'] = 'Y';
	$_code_comment[$_file_name]['order_prdcpn_form_start'] = '주문서 개별상품 쿠폰 폼 시작 선언';

	$_replace_code[$_file_name]['order_prdcpn_form_end'] = '';
	$_replace_hangul[$_file_name]['order_prdcpn_form_end'] = '개별상품쿠폰폼끝';
	$_auto_replace[$_file_name]['order_prdcpn_form_end'] = 'Y';
	$_code_comment[$_file_name]['order_prdcpn_form_end'] = '주문서 개별상품 쿠폰 폼 끝 선언';
