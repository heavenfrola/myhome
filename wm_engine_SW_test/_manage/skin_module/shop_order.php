<?PHP

	$_replace_code[$_file_name]['form_start'] = "";
	$_replace_hangul[$_file_name]['form_start'] = "폼시작";
	$_auto_replace[$_file_name]['form_start'] = "Y";
	$_code_comment[$_file_name]['form_start'] = "주문서 폼 시작 선언";

	$_replace_code[$_file_name]['form_end'] = "";
	$_replace_hangul[$_file_name]['form_end'] = "폼끝";
	$_auto_replace[$_file_name]['form_end'] = "Y";
	$_code_comment[$_file_name]['form_end'] = "주문서 폼 끝 선언";

	$_replace_code[$_file_name]['order_cart_list'] = "";
	$_replace_hangul[$_file_name]['order_cart_list'] = "장바구니리스트";
	$_code_comment[$_file_name]['order_cart_list'] = "주문서의 장바구니 상품의 리스트";
	$_replace_datavals[$_file_name]['order_cart_list'] = "장바구니번호:cno;상품명:name;상품명(링크포함):name_link:상품링크(A 태그)를 포함한 상품명;상품링크:link:해당 상품의 페이지 주소 출력;상품이미지경로:img:상품의 소 이미지의 경로 출력;상품이미지정보:imgstr:상품의 사이즈 정보 출력 (예 - width=100 height=100);상품이미지:imgr:상품의 소 이미지 출력;상품이미지(링크포함):imgr_link:상품링크(A 태그)를 포함한 소 이미지 출력;상품옵션정보:option_str:선택한 옵션 정보 출력;상품옵션정보(추가가격포함):option_str2;상품가격:sell_prc_c;상품총가격:sum_prd_prc_c;옵션제외가격:prd_prc_str;총옵션제외가격:sum_prd_prc_str;옵션가격:option_prc_str;총옵션가격:sum_option_prc_str;상품판매가격:sum_sell_prc_c:할인 후 상품 가격;구매수량:buy_ea;상품적립금:milage;상품총적립금:sum_milage;주문상품메모:etc;option_r_str2;참조상품가격:sell_r_prc_c;참조상품총가격:sum_r_sell_prc_c;참조옵션제외가격:prd_r_prc_str;참조총옵션제외가격:sum_r_prd_prc_str;참조옵션가격:option_r_prc_str;참조총옵션가격:sum_r_option_prc_str;참조상품적립금:r_milage;참조상품총적립금:sum_r_milage;상품합계무게:weight;기타메세지:etc:장바구니 옵션 기타메세지;위시리스트담김:is_wish:위시리스트에 담긴 상품일 경우 on 출력;상품기타입력사항1:etc1;상품기타입력사항2:etc2;상품기타입력사항3:etc3;오늘출발:today_dlv;오늘출발주문마감시간:today_time;배송기간:dlv_date;배송요일:week;배송주기:period_text;단독배송여부:is_dlv_alone:단독배송일 경우 singleorder 메시지가 출력됩니다.;개별배송비:prd_dlv_prc:상품별 개별 배송비가 설정되어있을 경우 배송비 출력;개별배송상품여부:delivery_set;";
	if($cfg['use_partner_shop'] == 'Y' && $cfg['use_partner_delivery'] == 'Y') {
		include_once 'shop_cart_partner.php';
	}

	$_replace_code[$_file_name]['order_cart_sum'] = "";
	$_replace_hangul[$_file_name]['order_cart_sum'] = "총상품구매금액";
	$_auto_replace[$_file_name]['order_cart_sum'] = "Y";
	$_code_comment[$_file_name]['order_cart_sum'] = "상품 구매 금액 합계";

	$_replace_code[$_file_name]['order_delivery_fee'] = "";
	$_replace_hangul[$_file_name]['order_delivery_fee'] = "배송비";
	$_auto_replace[$_file_name]['order_delivery_fee'] = "Y";
	$_code_comment[$_file_name]['order_delivery_fee'] = "주문서 배송비";

	$_replace_code[$_file_name]['order_cod_prc'] = '';
	$_replace_hangul[$_file_name]['order_cod_prc'] = '착불배송비';
	$_auto_replace[$_file_name]['order_cod_prc'] = 'Y';
	$_code_comment[$_file_name]['order_cod_prc'] = '주문서 총 착불 배송비';

	$_replace_code[$_file_name]['order_basic_dlvfee'] = '';
	$_replace_hangul[$_file_name]['order_basic_dlvfee'] = '일반배송비(단위없음)';
	$_auto_replace[$_file_name]['order_basic_dlvfee'] = 'Y';
	$_code_comment[$_file_name]['order_basic_dlvfee'] = '개별배송비를 제외한 장바구니의 일반 배송비';

	$_replace_code[$_file_name]['order_basic_dlvfee2'] = '';
	$_replace_hangul[$_file_name]['order_basic_dlvfee2'] = '일반배송비';
	$_auto_replace[$_file_name]['order_basic_dlvfee2'] = 'Y';
	$_code_comment[$_file_name]['order_basic_dlvfee2'] = '개별배송비를 제외한 장바구니의 일반 배송비';

	$_replace_code[$_file_name]['order_prd_dlvfee'] = '';
	$_replace_hangul[$_file_name]['order_prd_dlvfee'] = '개별배송비(단위없음)';
	$_auto_replace[$_file_name]['order_prd_dlvfee'] = 'Y';
	$_code_comment[$_file_name]['order_prd_dlvfee'] = '상품별배송비 합계';

	$_replace_code[$_file_name]['order_prd_dlvfee2'] = '';
	$_replace_hangul[$_file_name]['order_prd_dlvfee2'] = '개별배송비';
	$_auto_replace[$_file_name]['order_prd_dlvfee2'] = 'Y';
	$_code_comment[$_file_name]['order_prd_dlvfee2'] = '상품별배송비 합계';

	$_replace_code[$_file_name]['order_order_sum'] = "";
	$_replace_hangul[$_file_name]['order_order_sum'] = "총주문합계";
	$_auto_replace[$_file_name]['order_order_sum'] = "Y";
	$_code_comment[$_file_name]['order_order_sum'] = "주문서 결제 금액";

	$_replace_code[$_file_name]['order_sum_milage'] = '';
	$_replace_hangul[$_file_name]['order_sum_milage'] = "총상품적립금";
	$_auto_replace[$_file_name]['order_sum_milage'] = "Y";
	$_code_comment[$_file_name]['order_sum_milage'] = "상품구매로 획득할 총 적립금. 이벤트 추가적립금 및 회원추가 적립금은 제외됩니다.";

	$_replace_code[$_file_name]['order_cart_point'] = '';
	$_replace_hangul[$_file_name]['order_cart_point'] = "총구매포인트";
	$_auto_replace[$_file_name]['order_cart_point'] = "Y";
	$_code_comment[$_file_name]['order_cart_point'] = "구매액(상품가격) 별로 적립되는 포인트";

	$_replace_code[$_file_name]['order_weight_sum'] = "";
	$_replace_hangul[$_file_name]['order_weight_sum'] = "주문합계무게";
	$_auto_replace[$_file_name]['order_weight_sum'] = "Y";
	$_code_comment[$_file_name]['order_weight_sum'] = "주문서 총 합계 무게";

	$_replace_code[$_file_name]['order_buyer_name'] = "";
	$_replace_hangul[$_file_name]['order_buyer_name'] = "주문자명";
	$_auto_replace[$_file_name]['order_buyer_name'] = "Y";
	$_code_comment[$_file_name]['order_buyer_name'] = "주문자 성명";

	$_replace_code[$_file_name]['order_buyer_phone'] = "";
	$_replace_hangul[$_file_name]['order_buyer_phone'] = "전화번호";
	$_auto_replace[$_file_name]['order_buyer_phone'] = "Y";
	$_code_comment[$_file_name]['order_buyer_phone'] = "주문자 전화번호";


	$_replace_code[$_file_name]['order_buyer_phone1'] = "";
	$_replace_hangul[$_file_name]['order_buyer_phone1'] = "전화번호1";
	$_auto_replace[$_file_name]['order_buyer_phone1'] = "Y";
	$_code_comment[$_file_name]['order_buyer_phone1'] = "주문자 전화번호1";

	$_replace_code[$_file_name]['order_buyer_phone2'] = "";
	$_replace_hangul[$_file_name]['order_buyer_phone2'] = "전화번호2";
	$_auto_replace[$_file_name]['order_buyer_phone2'] = "Y";
	$_code_comment[$_file_name]['order_buyer_phone2'] = "주문자 전화번호2";

	$_replace_code[$_file_name]['order_buyer_phone3'] = "";
	$_replace_hangul[$_file_name]['order_buyer_phone3'] = "전화번호3";
	$_auto_replace[$_file_name]['order_buyer_phone3'] = "Y";
	$_code_comment[$_file_name]['order_buyer_phone3'] = "주문자 전화번호3";

	$_replace_code[$_file_name]['order_cell_phone'] = "";
	$_replace_hangul[$_file_name]['order_cell_phone'] = "휴대전화";
	$_auto_replace[$_file_name]['order_cell_phone'] = "Y";
	$_code_comment[$_file_name]['order_cell_phone'] = "주문자 휴대 전화번호";


	$_replace_code[$_file_name]['order_cell_phone1'] = "";
	$_replace_hangul[$_file_name]['order_cell_phone1'] = "휴대전화1";
	$_auto_replace[$_file_name]['order_cell_phone1'] = "Y";
	$_code_comment[$_file_name]['order_cell_phone1'] = "주문자 휴대 전화번호1";

	$_replace_code[$_file_name]['order_cell_phone2'] = "";
	$_replace_hangul[$_file_name]['order_cell_phone2'] = "휴대전화2";
	$_auto_replace[$_file_name]['order_cell_phone2'] = "Y";
	$_code_comment[$_file_name]['order_cell_phone2'] = "주문자 휴대 전화번호2";

	$_replace_code[$_file_name]['order_cell_phone3'] = "";
	$_replace_hangul[$_file_name]['order_cell_phone3'] = "휴대전화3";
	$_auto_replace[$_file_name]['order_cell_phone3'] = "Y";
	$_code_comment[$_file_name]['order_cell_phone3'] = "주문자 휴대 전화번호3";

	$_replace_code[$_file_name]['order_cell_email'] = "";
	$_replace_hangul[$_file_name]['order_cell_email'] = "주문자이메일";
	$_auto_replace[$_file_name]['order_cell_email'] = "Y";
	$_code_comment[$_file_name]['order_cell_email'] = "주문자 이메일 주소";

	$_replace_code[$_file_name]['order_addr_select'] = "";
	$_replace_hangul[$_file_name]['order_addr_select'] = "배송지선택";
	$_auto_replace[$_file_name]['order_addr_select'] = "Y";
	$_code_comment[$_file_name]['order_addr_select'] = "주문자 배송지 선택 셀렉트 박스 출력";


	$_replace_code[$_file_name]['order_addr_box_s'] = "";
	$_replace_hangul[$_file_name]['order_addr_box_s'] = "주소및전화입력박스";
	$_code_comment[$_file_name]['order_addr_select'] = "배송방식(국내/해외)에 따라 지정된 주소 및 전화 입력박스가 자동으로 출력됩니다.";
	$_auto_replace[$_file_name]['order_addr_box_s'] = "Y";

	$_replace_code[$_file_name]['order_addr_box'] = "";
	$_replace_hangul[$_file_name]['order_addr_box'] = "주소및전화입력박스(국내)";

	$_replace_code[$_file_name]['order_addr_box_oversea'] = "";
	$_replace_hangul[$_file_name]['order_addr_box_oversea'] = "주소및전화입력박스(해외)";

	$_replace_code[$_file_name]['order_oversea_phone'] = "";
	$_replace_hangul[$_file_name]['order_oversea_phone'] = "전화국가번호";
	$_auto_replace[$_file_name]['order_oversea_phone'] = "Y";

	$_replace_code[$_file_name]['order_oversea_cell'] = "";
	$_replace_hangul[$_file_name]['order_oversea_cell'] = "휴대국가번호";
	$_auto_replace[$_file_name]['order_oversea_cell'] = "Y";

	$_replace_code[$_file_name]['bill_order_oversea_phone'] = "";
	$_replace_hangul[$_file_name]['bill_order_oversea_phone'] = "주문자전화국가번호";
	$_auto_replace[$_file_name]['bill_order_oversea_phone'] = "Y";

	$_replace_code[$_file_name]['bill_order_oversea_cell'] = "";
	$_replace_hangul[$_file_name]['bill_order_oversea_cell'] = "주문자휴대국가번호";
	$_auto_replace[$_file_name]['bill_order_oversea_cell'] = "Y";

	$_replace_code[$_file_name]['bill_order_add_field_use'] = "";
	$_replace_hangul[$_file_name]['bill_order_add_field_use'] = "주문자추가필드사용";
	$_auto_replace[$_file_name]['bill_order_add_field_use'] = "Y";

	$_replace_code[$_file_name]['delivery_fee_type'] = "";
	$_replace_hangul[$_file_name]['delivery_fee_type'] = "국내해외배송선택";
	$_auto_replace[$_file_name]['delivery_fee_type'] = "Y";

	$_replace_code[$_file_name]['delivery_nations'] = "";
	$_replace_hangul[$_file_name]['delivery_nations'] = "배송국가선택";
	$_auto_replace[$_file_name]['delivery_nations'] = "Y";

	$_replace_code[$_file_name]['delivery_fee_type_radio'] = "";
	$_replace_hangul[$_file_name]['delivery_fee_type_radio'] = "국내해외배송라디오";
	$_auto_replace[$_file_name]['delivery_fee_type_radio'] = "Y";

	$_replace_code[$_file_name]['delivery_com_list'] = "";
	$_replace_hangul[$_file_name]['delivery_com_list'] = "배송업체목록";
	$_auto_replace[$_file_name]['delivery_com_list'] = "Y";

	$_replace_code[$_file_name]['delivery_com_display'] = "";
	$_replace_hangul[$_file_name]['delivery_com_display'] = "단일배송업체숨김처리";
	$_auto_replace[$_file_name]['delivery_com_display'] = "Y";

	$_replace_code[$_file_name]['order_pay_type'] = "";
	$_replace_hangul[$_file_name]['order_pay_type'] = "결제방식선택";
	$_auto_replace[$_file_name]['order_pay_type'] = "Y";
	$_code_comment[$_file_name]['order_pay_type'] = "결제 방식 선택 라디오 버튼 출력";

	$_replace_code[$_file_name]['order_pay_type2'] = "";
	$_replace_hangul[$_file_name]['order_pay_type2'] = "결제방식선택2";
	$_code_comment[$_file_name]['order_pay_type2'] = "편집 가능한 결제방식 버튼 출력 결제방식선택/결제방식선택2 중 한가지만 사용가능";
	$_replace_datavals[$_file_name]['order_pay_type2'] = "입금계좌선택:bank_account;현금영수증사용:use_cash_receipt;";
	foreach($_pay_type as $key => $val) {
		if($key == 3 || $key == 6 || $key == 8 || $key == 9) continue;
		$val = str_replace(' ', '', $val);
		$_replace_datavals[$_file_name]['order_pay_type2'] .= "{$val}선택:paytype{$key};";
	}

    $_replace_code[$_file_name]['order_pay_type3_list'] = "";
	$_replace_hangul[$_file_name]['order_pay_type3_list'] = "결제방식선택3";
	$_code_comment[$_file_name]['order_pay_type3_list'] = "편집 가능한 결제방식 버튼 출력 결제방식선택/결제방식선택2/결제방식선택3 중 한가지만 사용가능";
	$_replace_datavals[$_file_name]['order_pay_type3_list'] = "입금계좌선택:bank_account;현금영수증사용:use_cash_receipt;결제수단번호:pay_type;결제수단이름:pay_name;결제수단노출클래스:class_name;";

    $_replace_code[$_file_name]['order_paytype_bankuse'] = ( $scfg->comp('pay_type_2', 'Y') ? "Y":"" );
	$_replace_hangul[$_file_name]['order_paytype_bankuse'] = "무통장입금사용여부";
	$_auto_replace[$_file_name]['order_paytype_bankuse'] = "Y";
	$_code_comment[$_file_name]['order_paytype_bankuse'] = "무통장입금 사용 여부";

    $_replace_code[$_file_name]['order_paytype_bankinfo'] = "";
	$_replace_hangul[$_file_name]['order_paytype_bankinfo'] = "무통장입금시입력정보";
	$_code_comment[$_file_name]['order_paytype_bankinfo'] = "무통장입금시입력정보";
	$_replace_datavals[$_file_name]['order_paytype_bankinfo'] = "입금계좌선택:bank_account;현금영수증사용:use_cash_receipt;";

	$_replace_code[$_file_name]['order_milage'] = "";
	$_replace_hangul[$_file_name]['order_milage'] = "보유적립금액";
	$_auto_replace[$_file_name]['order_milage'] = "Y";
	$_code_comment[$_file_name]['order_milage'] = "주문자 사용가능 적립금액";

	$_replace_code[$_file_name]['order_emoney'] = "";
	$_replace_hangul[$_file_name]['order_emoney'] = "보유예치금액";
	$_auto_replace[$_file_name]['order_emoney'] = "Y";
	$_code_comment[$_file_name]['order_emoney'] = "주문자 사용가능 예치금액";

	$_replace_code[$_file_name]['order_cpn_list'] = "";
	$_replace_hangul[$_file_name]['order_cpn_list'] = "쿠폰리스트";
	$_code_comment[$_file_name]['order_cpn_list'] = "사용 가능한 보유 쿠폰 리스트";
	$_replace_datavals[$_file_name]['order_cpn_list'] = "쿠폰선택:radio;쿠폰코드:code;쿠폰명:name;쿠폰할인금액:sale_prc;쿠폰사용기간:udate_type;쿠폰최소구매금액:prc_limit;쿠폰최고할인금액:sale_limit_k;";

	$_replace_code[$_file_name]['order_offcpn'] = "";
	$_replace_hangul[$_file_name]['order_offcpn'] = "오프라인쿠폰";
	$_code_comment[$_file_name]['order_offcpn'] = "사용 가능한 시리얼 쿠폰이 존재할 경우 시리얼쿠폰 구문 출력";

	$_replace_code[$_file_name]['has_prdcpn'] = "";
	$_replace_hangul[$_file_name]['has_prdcpn'] = "개별상품쿠폰보유여부";
	$_code_comment[$_file_name]['has_prdcpn'] = "접속 회원에게 사용 가능한 개별상품 쿠폰이 있는지 출력합니다.";

	$_replace_code[$_file_name]['zip_url'] = "";
	$_replace_hangul[$_file_name]['zip_url'] = "우편번호찾기";
	$_code_comment[$_file_name]['zip_url'] = "배송지 주소 입력의 우편번호 찾기 주소";
	$_auto_replace[$_file_name]['zip_url'] = "Y";

	$_replace_code[$_file_name]['street_zip_url'] = "";
	$_replace_hangul[$_file_name]['street_zip_url'] = "도로명우편번호찾기";
	$_code_comment[$_file_name]['street_zip_url'] = "도로명 우편번호 찾기 링크 주소 출력";
	$_auto_replace[$_file_name]['street_zip_url'] = "Y";

	$_replace_code[$_file_name]['order_addfd_list'] = "";
	$_replace_hangul[$_file_name]['order_addfd_list'] = "주문추가항목리스트";
	$_code_comment[$_file_name]['order_addfd_list'] = "[쇼핑몰설정] > [주문추가항목 설정]에서 설정한 추가입력사항 출력";
	$_replace_datavals[$_file_name]['order_addfd_list'] = "필드명:name;필드값입력:value;";

	$_replace_code[$_file_name]['event_sale_info'] = "";
	$_replace_hangul[$_file_name]['event_sale_info'] = "이벤트할인정보";
	$_auto_replace[$_file_name]['event_sale_info'] = "Y";
	$_code_comment[$_file_name]['event_sale_info'] = "이벤트할인 정보를 출력합니다.";

	$_replace_code[$_file_name]['time_sale_info'] = "";
	$_replace_hangul[$_file_name]['time_sale_info'] = "타임세일정보";
	$_auto_replace[$_file_name]['time_sale_info'] = "Y";
	$_code_comment[$_file_name]['time_sale_info'] = "타임세일 정보를 출력합니다.";

	$_replace_code[$_file_name]['msale_info'] = "";
	$_replace_hangul[$_file_name]['msale_info'] = "회원할인정보";
	$_auto_replace[$_file_name]['msale_info'] = "Y";
	$_code_comment[$_file_name]['msale_info'] = "회원할인 정보를 출력합니다.";

	$_replace_code[$_file_name]['prdprc_sale_info'] = "";
	$_replace_hangul[$_file_name]['prdprc_sale_info'] = "주문상품금액별할인금액";
	$_auto_replace[$_file_name]['prdprc_sale_info'] = "Y";
	$_code_comment[$_file_name]['prdprc_sale_info'] = "주문에 적용된 주문상품 금액별 할인 금액 출력";

	$_replace_code[$_file_name]['dhtml_set_prc'] = '<span class="order_saleinfo_set_prc"></span>';
	$_replace_hangul[$_file_name]['dhtml_set_prc'] = "세트할인금액";
	$_auto_replace[$_file_name]['dhtml_set_prc'] = "Y";
	$_code_comment[$_file_name]['dhtml_set_prc'] = "실시간으로 계산된 세트 할인금액이 출력됩니다.";

	$_replace_code[$_file_name]['dhtml_event_prc'] = '<span class="order_saleinfo_event_prc"></span>';
	$_replace_hangul[$_file_name]['dhtml_event_prc'] = "이벤트할인금액";
	$_auto_replace[$_file_name]['dhtml_event_prc'] = "Y";
	$_code_comment[$_file_name]['dhtml_event_prc'] = "실시간으로 계산된 이벤트 할인금액이 출력됩니다.";

	$_replace_code[$_file_name]['dhtml_event_dlv'] = '<span class="order_saleinfo_event_dlv"></span>';
	$_replace_hangul[$_file_name]['dhtml_event_dlv'] = "이벤트배송비할인금액";
	$_auto_replace[$_file_name]['dhtml_event_dlv'] = "Y";
	$_code_comment[$_file_name]['dhtml_event_dlv'] = "실시간으로 계산된 무료배송 이벤트 할인금액이 출력됩니다.";

	$_replace_code[$_file_name]['dhtml_timesale'] = '<span class="order_saleinfo_timesale"></span>';
	$_replace_hangul[$_file_name]['dhtml_timesale'] = "타임세일금액";
	$_auto_replace[$_file_name]['dhtml_timesale'] = "Y";
	$_code_comment[$_file_name]['dhtml_timesale'] = "실시간으로 계산된 타임세일 금액이 출력됩니다.";

	$_replace_code[$_file_name]['dhtml_member_prc'] = '<span class="order_saleinfo_member_prc"></span>';
	$_replace_hangul[$_file_name]['dhtml_member_prc'] = "회원할인금액";
	$_auto_replace[$_file_name]['dhtml_member_prc'] = "Y";
	$_code_comment[$_file_name]['dhtml_member_prc'] = "실시간으로 계산된 회원할인 금액이 출력됩니다.";

	$_replace_code[$_file_name]['dhtml_member_dlv'] = '<span class="order_saleinfo_member_dlv"></span>';
	$_replace_hangul[$_file_name]['dhtml_member_dlv'] = "회원배송비할인금액";
	$_auto_replace[$_file_name]['dhtml_member_dlv'] = "Y";
	$_code_comment[$_file_name]['dhtml_member_dlv'] = "실시간으로 계산된 회원 무료배송 할인금액이 출력됩니다.";

	$_replace_code[$_file_name]['dhtml_sbscrsale'] = '<span class="order_saleinfo_sbscr_prc"></span>';
	$_replace_hangul[$_file_name]['dhtml_sbscrsale'] = "정기배송할인금액";
	$_auto_replace[$_file_name]['dhtml_sbscrsale'] = "Y";
	$_code_comment[$_file_name]['dhtml_sbscrsale'] = "실시간으로 계산된 정기배송 할인 금액이 출력됩니다.";

	$_replace_code[$_file_name]['dhtml_sale9'] = '<span class="order_saleinfo_sale9_prc"></span>';
	$_replace_hangul[$_file_name]['dhtml_sale9'] = "상품별수량할인금액";
	$_auto_replace[$_file_name]['dhtml_sale9'] = "Y";
	$_code_comment[$_file_name]['dhtml_sale9'] = "실시간으로 계산된 상품별수량 할인 금액이 출력됩니다.";

	$_replace_code[$_file_name]['dhtml_event_milage'] = '<span class="order_saleinfo_event_milage"></span>';
	$_replace_hangul[$_file_name]['dhtml_event_milage'] = "이벤트적립금";
	$_auto_replace[$_file_name]['dhtml_event_milage'] = "Y";
	$_code_comment[$_file_name]['dhtml_event_milage'] = "실시간으로 계산된 이벤트적립금이 출력됩니다.";

	$_replace_code[$_file_name]['dhtml_member_milage'] = '<span class="order_saleinfo_member_milage"></span>';
	$_replace_hangul[$_file_name]['dhtml_member_milage'] = "회원적립금";
	$_auto_replace[$_file_name]['dhtml_member_milage'] = "Y";
	$_code_comment[$_file_name]['dhtml_member_milage'] = "실시간으로 계산된 회원적립금이 출력됩니다.";

	$_replace_code[$_file_name]['dhtml_cpn_prc'] = '<span class="order_saleinfo_cpn_prc"></span>';
	$_replace_hangul[$_file_name]['dhtml_cpn_prc'] = "쿠폰할인금액";
	$_auto_replace[$_file_name]['dhtml_cpn_prc'] = "Y";
	$_code_comment[$_file_name]['dhtml_cpn_prc'] = "실시간으로 계산된 쿠폰할인금액이 출력됩니다.";

	$_replace_code[$_file_name]['dhtml_prdcpn_prc'] = '<span class="order_saleinfo_prdcpn_prc"></span>';
	$_replace_hangul[$_file_name]['dhtml_prdcpn_prc'] = "개별상품쿠폰할인금액";
	$_auto_replace[$_file_name]['dhtml_prdcpn_prc'] = "Y";
	$_code_comment[$_file_name]['dhtml_prdcpn_prc'] = "실시간으로 계산된 쿠폰할인금액이 출력됩니다.";

	$_replace_code[$_file_name]['dhtml_prdcpn_prc'] = '<span class="order_saleinfo_prdcpn_prc"></span>';
	$_replace_hangul[$_file_name]['dhtml_prdcpn_prc'] = "개별상품쿠폰할인금액";
	$_auto_replace[$_file_name]['dhtml_prdcpn_prc'] = "Y";
	$_code_comment[$_file_name]['dhtml_prdcpn_prc'] = "실시간으로 계산된 쿠폰할인금액이 출력됩니다.";

	$_replace_code[$_file_name]['dhtml_prd_prc'] = '<span class="order_saleinfo_prd_prc"></span>';
	$_replace_hangul[$_file_name]['dhtml_prd_prc'] = "상품금액별할인금액";
	$_auto_replace[$_file_name]['dhtml_prd_prc'] = "Y";
	$_code_comment[$_file_name]['dhtml_prd_prc'] = "실시간으로 계산된 상품금액별 할인 금액이 출력됩니다.";

	$_replace_code[$_file_name]['dhtml_pgcharge_prc'] = '<span class="order_saleinfo_pgcharge_prc"></span>';
	$_replace_hangul[$_file_name]['dhtml_pgcharge_prc'] = "결제방식별추가금액";
	$_auto_replace[$_file_name]['dhtml_pgcharge_prc'] = "Y";
	$_code_comment[$_file_name]['dhtml_pgcharge_prc'] = "실시간으로 계산된 결제방식별 추가금액이 출력됩니다.";

	$_replace_code[$_file_name]['dhtml_sale2_dlv'] = '<span class="order_info_free_dlv_prc_e"></span>';
	$_replace_hangul[$_file_name]['dhtml_sale2_dlv'] = "이벤트무료배송금액";
	$_auto_replace[$_file_name]['dhtml_sale2_dlv'] = "Y";
	$_code_comment[$_file_name]['dhtml_sale2_dlv'] = "실시간으로 계산된 이벤트 무료배송금액이 출력됩니다.";

	$_replace_code[$_file_name]['dhtml_sale4_dlv'] = '<span class="order_info_free_dlv_prc_m"></span>';
	$_replace_hangul[$_file_name]['dhtml_sale4_dlv'] = "회원무료배송금액";
	$_auto_replace[$_file_name]['dhtml_sale4_dlv'] = "Y";
	$_code_comment[$_file_name]['dhtml_sale4_dlv'] = "실시간으로 계산된 회원 무료배송금액이 출력됩니다.";

	$_replace_code[$_file_name]['dhtml_total_sale_prc'] = '<span class="total_sale_prc"></span>';
	$_replace_hangul[$_file_name]['dhtml_total_sale_prc'] = "총할인금액합계";
	$_auto_replace[$_file_name]['dhtml_total_sale_prc'] = "Y";
	$_code_comment[$_file_name]['dhtml_total_sale_prc'] = "실시간으로 계산된 총할인금액 합계가 출력됩니다.";

	$_replace_code[$_file_name]['dhtml_order_info_sale_prc'] = '<span class="order_info_sale_prc"></span>';
	$_replace_hangul[$_file_name]['dhtml_order_info_sale_prc'] = "실결제금액";
	$_auto_replace[$_file_name]['dhtml_order_info_sale_prc'] = "Y";
	$_code_comment[$_file_name]['dhtml_order_info_sale_prc'] = "실시간으로 계산된 실결제금액이 합계가 출력됩니다.";

	$_replace_code[$_file_name]['dhtml_order_info_firsttime_pay_prc'] = '<span class="order_info_firsttime_pay_prc"></span>';
	$_replace_hangul[$_file_name]['dhtml_order_info_firsttime_pay_prc'] = "최초결제일결제금액";
	$_auto_replace[$_file_name]['dhtml_order_info_firsttime_pay_prc'] = "Y";
	$_code_comment[$_file_name]['dhtml_order_info_firsttime_pay_prc'] = "정기결제시 최초 결제일 결제금액이 출력됩니다.";

	$_replace_code[$_file_name]['dhtml_prd_milage'] = '<span class="order_saleinfo_prd_milage"></span>';
	$_replace_hangul[$_file_name]['dhtml_prd_milage'] = "상품적립금";
	$_auto_replace[$_file_name]['dhtml_prd_milage'] = "Y";
	$_code_comment[$_file_name]['dhtml_prd_milage'] = "실시간으로 계산된 상품적립금이 출력됩니다.";

	$_replace_code[$_file_name]['dhtml_total_milage'] = '<span class="total_milage"></span>';
	$_replace_hangul[$_file_name]['dhtml_total_milage'] = "총지급적립금";
	$_auto_replace[$_file_name]['dhtml_total_milage'] = "Y";
	$_code_comment[$_file_name]['dhtml_total_milage'] = "실시간으로 계산된 상품적립금+회원적립금+이벤트적립금 합계가 출력됩니다.";

	$_replace_code[$_file_name]['naver_milage_use_url'] = "";
	$_replace_hangul[$_file_name]['naver_milage_use_url'] = "네이버마일리지적립및사용팝업주소";
	$_code_comment[$_file_name]['naver_milage_use_url'] = "네이버마일리지 적립 및 사용 팝업주소";

	$_replace_code[$_file_name]['ord_receipt_url'] = "";
	$_replace_hangul[$_file_name]['ord_receipt_url'] = "계산서출력";
	$_auto_replace[$_file_name]['ord_receipt_url'] = "Y";
	$_code_comment[$_file_name]['ord_receipt_url']  =  "계산서출력 링크 주소 출력";

	$_replace_code[$_file_name]['order_r_cart_sum'] = "";
	$_replace_hangul[$_file_name]['order_r_cart_sum'] = "참조총상품구매금액";
	$_auto_replace[$_file_name]['order_r_cart_sum'] = "Y";
	$_code_comment[$_file_name]['order_r_cart_sum'] = "상품 구매 참조금액 합계";

	$_replace_code[$_file_name]['order_r_delivery_fee'] = "";
	$_replace_hangul[$_file_name]['order_r_delivery_fee'] = "참조배송비";
	$_auto_replace[$_file_name]['order_r_delivery_fee'] = "Y";
	$_code_comment[$_file_name]['order_r_delivery_fee'] = "주문서 참조배송비";

	$_replace_code[$_file_name]['order_r_order_sum'] = "";
	$_replace_hangul[$_file_name]['order_r_order_sum'] = "참조총주문합계";
	$_auto_replace[$_file_name]['order_r_order_sum'] = "Y";
	$_code_comment[$_file_name]['order_r_order_sum'] = "주문서 결제 참조금액";

	$_replace_code[$_file_name]['order_r_sum_milage'] = '';
	$_replace_hangul[$_file_name]['order_r_sum_milage'] = "참조총상품적립금";
	$_auto_replace[$_file_name]['order_r_sum_milage'] = "Y";
	$_code_comment[$_file_name]['order_r_sum_milage'] = "상품구매로 획득할 총 참조적립금. 이벤트 추가적립금 및 회원추가 적립금은 제외됩니다.";

	$_replace_code[$_file_name]['order_overseas_delivery'] = ($cfg['delivery_fee_type'] == 'A' || $cfg['delivery_fee_type'] == 'O'?'Y':'');
	$_replace_hangul[$_file_name]['order_overseas_delivery'] = "해외배송사용";
	$_auto_replace[$_file_name]['order_overseas_delivery'] = "Y";

	$_replace_code[$_file_name]['milage_use_unit'] = ($cfg['milage_use_unit'] > 0) ? number_format($cfg['milage_use_unit']) : '';
	$_replace_hangul[$_file_name]['milage_use_unit'] = '적립금사용단위';
	$_auto_replace[$_file_name]['milage_use_unit'] = 'Y';

	$_replace_code[$_file_name]['order_cpn_milage'] = ($cfg['order_cpn_milage'] == '2' ? 'Y':'');
	$_replace_hangul[$_file_name]['order_cpn_milage'] = "쿠폰적립금중복사용가능여부";
	$_auto_replace[$_file_name]['order_cpn_milage'] = "Y";

	$_replace_code[$_file_name]['order_sbscr_Y'] = ($sbscr == 'Y') ? 'Y' : '';
	$_replace_hangul[$_file_name]['order_sbscr_Y'] = "정기배송적용여부_주문서";
	$_auto_replace[$_file_name]['order_sbscr_Y'] = "Y";
	$_code_comment[$_file_name]['order_sbscr_Y'] = "정기배송 주문서 작성일 경우 Y가 출력됩니다.";

	$_replace_code[$_file_name]['order_sbscr_N'] = ($sbscr != 'Y') ? 'Y' : '';
	$_replace_hangul[$_file_name]['order_sbscr_N'] = "일반배송적용여부_주문서";
	$_auto_replace[$_file_name]['order_sbscr_N'] = "Y";
	$_code_comment[$_file_name]['order_sbscr_N'] = "정기배송이 아닌 일반 배송 주문서 작성일 경우 Y가 출력됩니다.";

	$_replace_code[$_file_name]['order_sbscr_all_yn'] = "";
	$_replace_hangul[$_file_name]['order_sbscr_all_yn'] = "정기배송결제수단";
	$_auto_replace[$_file_name]['order_sbscr_all_yn'] = "Y";
	$_code_comment[$_file_name]['order_sbscr_all_yn'] = "정기배송결제수단";

	$_replace_code[$_file_name]['sbscr_firsttime_pay_prc'] = '';
	$_replace_hangul[$_file_name]['sbscr_firsttime_pay_prc'] = '첫결제금액';
	$_auto_replace[$_file_name]['sbscr_firsttime_pay_prc'] = 'Y';
	$_code_comment[$_file_name]['sbscr_firsttime_pay_prc'] = '첫결제금액';

	$_replace_code[$_file_name]['order_gift_list'] = '<div id="gift_area"></div>';
	$_replace_hangul[$_file_name]['order_gift_list'] = '사은품리스트';
	$_code_comment[$_file_name]['order_gift_list'] = "사은품 선택 시점이 주문서인 경우 사은품이 출력됩니다.";
	$_auto_replace[$_file_name]['order_gift_list'] = '';

    // 기본배송지
    $_replace_code[$_file_name]['order_default_address'] = "";
    $_replace_hangul[$_file_name]['order_default_address'] = "기본배송지정보";
    $_code_comment[$_file_name]['order_default_address'] = "기본 배송지 정보";
    $_replace_datavals[$_file_name]['order_default_address'] = "기본배송지명:title;기본배송지받는사람:name;기본배송지우편번호:zip;기본배송지주소:addr1;기본배송지상세주소:addr2;기본배송지전화번호:phone;기본배송지모바일번호:cell;기본배송지상세주소3:addr3;기본배송지상세주소4:addr4;기본배송지국가:nations;기본배송지:is_default;버튼영역:btn_area;";

    // 배송메모
    $_replace_code[$_file_name]['order_default_dlvmemo_list'] = "";
    $_replace_hangul[$_file_name]['order_default_dlvmemo_list'] = "배송메시지입력";
    $_code_comment[$_file_name]['order_default_dlvmemo_list'] = "배송메시지입력";
    $_replace_datavals[$_file_name]['order_default_dlvmemo_list'] = "배송메모:memo;";

    // 사용가능한 쿠폰 갯수
    $_replace_code[$_file_name]['order_use_coupon_cnt'] = "";
    $_replace_hangul[$_file_name]['order_use_coupon_cnt'] = '사용가능한쿠폰갯수';
	$_auto_replace[$_file_name]['order_use_coupon_cnt'] = 'Y';
	$_code_comment[$_file_name]['order_use_coupon_cnt'] = '사용가능한쿠폰갯수';
?>