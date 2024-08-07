<?PHP

	$_replace_code[$_file_name]['cart_form_start']="";
	$_replace_hangul[$_file_name]['cart_form_start']="폼시작";
	$_auto_replace[$_file_name]['cart_form_start']="Y";
	$_code_comment[$_file_name]['cart_form_start']="장바구니 폼 시작 선언";

	$_replace_code[$_file_name]['cart_list']="";
	$_replace_hangul[$_file_name]['cart_list']="장바구니리스트";
	$_code_comment[$_file_name]['cart_list']="장바구니 상품의 리스트";
	$_replace_datavals[$_file_name]['cart_list']="번호:cart_no:장바구니 상품의 번호;장바구니번호:cno:장바구니 상품의 고유 키값;상품명:name;상품명(링크포함):name_link:상품링크(A 태그)를 포함한 상품명;상품링크:link:해당 상품의 페이지 주소 출력;상품이미지경로:img:상품의 소 이미지의 경로 출력;상품이미지정보:imgstr:상품의 사이즈 정보 출력 (예 - width=100 height=100);상품이미지:imgr:상품의 소 이미지 출력;상품이미지(링크포함):imgr_link:상품링크(A 태그)를 포함한 소 이미지 출력;상품옵션정보:option_str:선택한 옵션 정보 출력;상품옵션정보(추가가격포함):option_str2;상품가격:sell_prc_c;상품총가격:sum_prd_prc_c;옵션제외가격:prd_prc_str;총옵션제외가격:sum_prd_prc_str;옵션가격:option_prc_str;총옵션가격:sum_option_prc_str;상품판매가격:sum_sell_prc_c:할인 후 상품 가격;총할인금액:discount_prc:상품별 총 할인된 금액;구매수량:buy_ea;상품적립금:milage;상품총적립금:sum_milage;주문상품메모:etc;상품삭제:del_link:해당 상품을 장바구니에서 삭제하는 링크(A태그)를 출력합니다.;위시담기:wish_link:해당 상품을 위시리스트에 담습니다.;대분류코드:big;중분류코드:mid;소분류코드:small;참조상품총가격:sum_r_sell_prc_c;참조상품가격:sell_r_prc_c;참조옵션제외가격:prd_r_prc_str;참조총옵션제외가격:sum_r_prd_prc_str;참조옵션가격:option_r_prc_str;참조총옵션가격:sum_r_option_prc_str;참조상품총적립금:sum_r_milage;입점업체명:partner_name:입점업체명;기타메세지:etc:장바구니 옵션 기타메세지;옵션변경링크:chgopt_link;위시리스트담김:is_wish:위시리스트에 담긴 상품일 경우 on 출력;상품기타입력사항1:etc1;상품기타입력사항2:etc2;상품기타입력사항3:etc3;오늘출발:today_dlv;오늘출발주문마감시간:today_time;단독배송여부:is_dlv_alone:단독배송일 경우 singleorder 메시지가 출력됩니다.;개별배송비:prd_dlv_prc:상품별 개별 배송비가 설정되어있을 경우 배송비 출력;개별배송상품여부:delivery_set;";

    if ($scfg->comp('use_set_product', 'Y') == true) {
    	$_replace_datavals[$_file_name]['cart_list'] .= '일반세트:is_set_1;골라담기:is_set_2;';
    }

	if($cfg['use_partner_shop'] == 'Y' && $cfg['use_partner_delivery'] == 'Y' && defined('__quick_cart__') == false) {
		include_once 'shop_cart_partner.php';
	}

	$_replace_code[$_file_name]['cart_form_end']="";
	$_replace_hangul[$_file_name]['cart_form_end']="폼끝";
	$_auto_replace[$_file_name]['cart_form_end']="Y";
	$_code_comment[$_file_name]['cart_form_end']="장바구니 폼 끝 선언";

	$_replace_code[$_file_name]['cart_order_url']="";
	$_replace_hangul[$_file_name]['cart_order_url']="주문하기";
	$_auto_replace[$_file_name]['cart_order_url']="Y";
	$_code_comment[$_file_name]['cart_order_url']="주문하기 링크 주소 출력";

	$_replace_code[$_file_name]['cart_sorder_url']="";
	$_replace_hangul[$_file_name]['cart_sorder_url']="선택주문하기";
	$_auto_replace[$_file_name]['cart_sorder_url']="Y";
	$_code_comment[$_file_name]['cart_sorder_url']="선택주문 링크 주소 출력";

	$_replace_code[$_file_name]['cart_update_url']="";
	$_replace_hangul[$_file_name]['cart_update_url']="다시계산";
	$_auto_replace[$_file_name]['cart_update_url']="Y";
	$_code_comment[$_file_name]['cart_update_url']="다시계산 링크 주소 출력";

	$_replace_code[$_file_name]['cart_delete_url']="";
	$_replace_hangul[$_file_name]['cart_delete_url']="선택삭제";
	$_auto_replace[$_file_name]['cart_delete_url']="Y";
	$_code_comment[$_file_name]['cart_delete_url']="선택삭제 링크 주소 출력";

	$_replace_code[$_file_name]['order_offcpn'] = "";
	$_replace_hangul[$_file_name]['order_offcpn'] = "오프라인쿠폰";
	$_code_comment[$_file_name]['order_offcpn'] = "사용 가능한 시리얼 쿠폰이 존재할 경우 시리얼쿠폰 구문 출력";

	$_replace_code[$_file_name]['has_prdcpn'] = "";
	$_replace_hangul[$_file_name]['has_prdcpn'] = "개별상품쿠폰보유여부";
	$_code_comment[$_file_name]['has_prdcpn'] = "접속 회원에게 사용 가능한 개별상품 쿠폰이 있는지 출력합니다.";

	$_replace_code[$_file_name]['cart_truncate_url']="";
	$_replace_hangul[$_file_name]['cart_truncate_url']="장바구니비우기";
	$_auto_replace[$_file_name]['cart_truncate_url']="Y";
	$_code_comment[$_file_name]['cart_truncate_url']="장바구니비우기 링크 주소 출력";

	$_replace_code[$_file_name]['cart_wish_url']="";
	$_replace_hangul[$_file_name]['cart_wish_url']="위시리스트 담기";
	$_auto_replace[$_file_name]['cart_wish_url']="Y";
	$_code_comment[$_file_name]['cart_wish_url']="장바구니 상품을 위시리스트에 담기 링크 주소 출력";

	$_replace_code[$_file_name]['cart_shopping_url']="";
	$_replace_hangul[$_file_name]['cart_shopping_url']="계속쇼핑하기";
	$_auto_replace[$_file_name]['cart_shopping_url']="Y";
	$_code_comment[$_file_name]['cart_shopping_url']="계속쇼핑하기 링크 주소 출력";

	$_replace_code[$_file_name]['cart_receipt_url']="";
	$_replace_hangul[$_file_name]['cart_receipt_url']="계산서출력";
	$_auto_replace[$_file_name]['cart_receipt_url']="Y";
	$_code_comment[$_file_name]['cart_receipt_url']="계산서출력 링크 주소 출력";

	$_replace_code[$_file_name]['cart_list_sum']="";
	$_replace_hangul[$_file_name]['cart_list_sum']="장바구니합계금액";
	$_auto_replace[$_file_name]['cart_list_sum']="Y";
	$_code_comment[$_file_name]['cart_list_sum']="장바구니 상품 총 합계 금액";

	$_replace_code[$_file_name]['cart_list_cpn_use']="";
	$_replace_hangul[$_file_name]['cart_list_cpn_use']="장바구니쿠폰사용";
	$_auto_replace[$_file_name]['cart_list_cpn_use']="Y";
	$_code_comment[$_file_name]['cart_list_cpn_use']="장바구니에서 쿠폰사용여부";

	$_replace_code[$_file_name]['cart_list_cpn_prc']="";
	$_replace_hangul[$_file_name]['cart_list_cpn_prc']="장바구니쿠폰금액";
	$_auto_replace[$_file_name]['cart_list_cpn_prc']="Y";
	$_code_comment[$_file_name]['cart_list_cpn_prc']="장바구니에서 쿠폰 기본금액";

	$_replace_code[$_file_name]['cart_list_prdcpn_prc']="";
	$_replace_hangul[$_file_name]['cart_list_prdcpn_prc']="상품별쿠폰금액";
	$_auto_replace[$_file_name]['cart_list_prdcpn_prc']="Y";
	$_code_comment[$_file_name]['cart_list_prdcpn_prc']="상품별 쿠폰으로 할인되는 금액";

	$_replace_code[$_file_name]['cart_list_set'] = '';
	$_replace_hangul[$_file_name]['cart_list_set']= '세트할인금액';
	$_auto_replace[$_file_name]['cart_list_set'] = 'Y';
	$_code_comment[$_file_name]['cart_list_set'] = '세트구매로 할인되는 금액';

	$_replace_code[$_file_name]['cart_list_event']="";
	$_replace_hangul[$_file_name]['cart_list_event']="이벤트할인금액";
	$_auto_replace[$_file_name]['cart_list_event']="Y";
	$_code_comment[$_file_name]['cart_list_event']="이벤트 존재시 할인되는 금액";

	$_replace_code[$_file_name]['cart_list_event_prd']="";
	$_replace_hangul[$_file_name]['cart_list_event_prd']="이벤트상품할인금액";
	$_auto_replace[$_file_name]['cart_list_event_prd']="Y";
	$_code_comment[$_file_name]['cart_list_event_prd']="이벤트 존재 시 상품에서 할인되는 금액";

	$_replace_code[$_file_name]['cart_list_event_dlv']="";
	$_replace_hangul[$_file_name]['cart_list_event_dlv']="이벤트배송비할인금액";
	$_auto_replace[$_file_name]['cart_list_event_dlv']="Y";
	$_code_comment[$_file_name]['cart_list_event_dlv']="이벤트 무료배송 할인 금액";

	$_replace_code[$_file_name]['cart_list_timesale']="";
	$_replace_hangul[$_file_name]['cart_list_timesale']="타임세일금액";
	$_auto_replace[$_file_name]['cart_list_timesale']="Y";
	$_code_comment[$_file_name]['cart_list_timesale']="타임세일 할인되는 금액";

	$_replace_code[$_file_name]['cart_list_msale']="";
	$_replace_hangul[$_file_name]['cart_list_msale']="회원할인금액";
	$_auto_replace[$_file_name]['cart_list_msale']="Y";
	$_code_comment[$_file_name]['cart_list_msale']="회원할인 존재시 할인되는 금액";

	$_replace_code[$_file_name]['cart_list_msale_prd']="";
	$_replace_hangul[$_file_name]['cart_list_msale_prd']="회원상품할인금액";
	$_auto_replace[$_file_name]['cart_list_msale_prd']="Y";
	$_code_comment[$_file_name]['cart_list_msale_prd']="회원 할인 적용 시 상품에서 할인되는 금액";

	$_replace_code[$_file_name]['cart_list_msale_dlv']="";
	$_replace_hangul[$_file_name]['cart_list_msale_dlv']="회원배송비할인금액";
	$_auto_replace[$_file_name]['cart_list_msale_dlv']="Y";
	$_code_comment[$_file_name]['cart_list_msale_dlv']="회원 무료배송 할인 금액";

	$_replace_code[$_file_name]['cart_list_prdprc']="";
	$_replace_hangul[$_file_name]['cart_list_prdprc']="구매금액별할인금액";
	$_auto_replace[$_file_name]['cart_list_prdprc']="Y";
	$_code_comment[$_file_name]['cart_list_prdprc']="구매금액별 할인되는 금액";

	$_replace_code[$_file_name]['cart_list_sale9'] = '';
	$_replace_hangul[$_file_name]['cart_list_sale9'] = '상품별수량할인금액';
	$_auto_replace[$_file_name]['cart_list_sale9'] = 'Y';
	$_code_comment[$_file_name]['cart_list_sale9'] = '상품별 수량할인 금액';

	$_replace_code[$_file_name]['cart_list_dlvfee']="";
	$_replace_hangul[$_file_name]['cart_list_dlvfee']="배송비";
	$_auto_replace[$_file_name]['cart_list_dlvfee']="Y";
	$_code_comment[$_file_name]['cart_list_dlvfee']="해당 장바구니 적용 배송비";

	$_replace_code[$_file_name]['cart_list_dlvfee2']="";
	$_replace_hangul[$_file_name]['cart_list_dlvfee2']="장바구니배송비";
	$_auto_replace[$_file_name]['cart_list_dlvfee2']="Y";
	$_code_comment[$_file_name]['cart_list_dlvfee2']="해당 장바구니 적용 배송비";

	$_replace_code[$_file_name]['cart_cod_prc'] = '';
	$_replace_hangul[$_file_name]['cart_cod_prc'] = '장바구니착불배송비';
	$_auto_replace[$_file_name]['cart_cod_prc'] = 'Y';
	$_code_comment[$_file_name]['cart_cod_prc'] = '해당 장바구니 적용되는 착불 배송비';

	$_replace_code[$_file_name]['cart_list_dlvfee2n'] = '';
	$_replace_hangul[$_file_name]['cart_list_dlvfee2n'] = '장바구니배송비(단위없음)';
	$_auto_replace[$_file_name]['cart_list_dlvfee2n'] = 'Y';
	$_code_comment[$_file_name]['cart_list_dlvfee2n'] = '해당 장바구니 적용 배송비 (단위 출력하지 않음)';

	$_replace_code[$_file_name]['cart_list_basic_dlvfee'] = '';
	$_replace_hangul[$_file_name]['cart_list_basic_dlvfee'] = '일반배송비(단위없음)';
	$_auto_replace[$_file_name]['cart_list_basic_dlvfee'] = 'Y';
	$_code_comment[$_file_name]['cart_list_basic_dlvfee'] = '개별배송비를 제외한 장바구니의 일반 배송비';

	$_replace_code[$_file_name]['cart_list_basic_dlvfee2'] = '';
	$_replace_hangul[$_file_name]['cart_list_basic_dlvfee2'] = '일반배송비';
	$_auto_replace[$_file_name]['cart_list_basic_dlvfee2'] = 'Y';
	$_code_comment[$_file_name]['cart_list_basic_dlvfee2'] = '개별배송비를 제외한 장바구니의 일반 배송비';

	$_replace_code[$_file_name]['cart_list_prd_dlvfee'] = '';
	$_replace_hangul[$_file_name]['cart_list_prd_dlvfee'] = '개별배송비(단위없음)';
	$_auto_replace[$_file_name]['cart_list_prd_dlvfee'] = 'Y';
	$_code_comment[$_file_name]['cart_list_prd_dlvfee'] = '상품별배송비 합계';

	$_replace_code[$_file_name]['cart_list_prd_dlvfee2'] = '';
	$_replace_hangul[$_file_name]['cart_list_prd_dlvfee2'] = '개별배송비';
	$_auto_replace[$_file_name]['cart_list_prd_dlvfee2'] = 'Y';
	$_code_comment[$_file_name]['cart_list_prd_dlvfee2'] = '상품별배송비 합계';

	$_replace_code[$_file_name]['cart_list_pay']="";
	$_replace_hangul[$_file_name]['cart_list_pay']="장바구니결제금액";
	$_auto_replace[$_file_name]['cart_list_pay']="Y";
	$_code_comment[$_file_name]['cart_list_pay']="장바구니 상품 결제 금액";

	$_replace_code[$_file_name]['cart_list_milage']="";
	$_replace_hangul[$_file_name]['cart_list_milage']="적립금";
	$_auto_replace[$_file_name]['cart_list_milage']="Y";
	$_code_comment[$_file_name]['cart_list_milage']="장바구니 상품 총 적립금";

	$_replace_code[$_file_name]['cart_list_event_milage']="";
	$_replace_hangul[$_file_name]['cart_list_event_milage']="이벤트적립금";
	$_auto_replace[$_file_name]['cart_list_event_milage']="Y";
	$_code_comment[$_file_name]['cart_list_event_milage']="적립금 이벤트 총 적립되는 금액";

	$_replace_code[$_file_name]['cart_list_point']="";
	$_replace_hangul[$_file_name]['cart_list_point']="포인트";
	$_auto_replace[$_file_name]['cart_list_point']="Y";
	$_code_comment[$_file_name]['cart_list_point']="구매액(상품가격) 별로 적립되는 포인트";

	$_replace_code[$_file_name]['cart_checkout_url']="";
	$_replace_hangul[$_file_name]['cart_checkout_url']="네이버체크아웃버튼";
	$_auto_replace[$_file_name]['cart_checkout_url']="Y";
	$_code_comment[$_file_name]['cart_checkout_url']="네이버 페이 구매하기, 찜하기 버튼";

	$_replace_code[$_file_name]['cart_talkpay_url'] = '';
	$_replace_hangul[$_file_name]['cart_talkpay_url'] = '카카오페이구매버튼';
	$_auto_replace[$_file_name]['cart_talkpay_url'] = 'Y';
	$_code_comment[$_file_name]['cart_talkpay_url'] = '카카오 페이구매하기, 찜하기 버튼';

	$_replace_code[$_file_name]['cart_payco_url'] = '';
	$_replace_hangul[$_file_name]['cart_payco_url'] = '페이코즉시구매버튼';
	$_auto_replace[$_file_name]['cart_payco_url'] = 'Y';
	$_code_comment[$_file_name]['cart_payco_url'] = '페이코즉시구매버튼';

	$_replace_code[$_file_name]['cart_r_list_sum']="";
	$_replace_hangul[$_file_name]['cart_r_list_sum']="참조장바구니합계금액";
	$_auto_replace[$_file_name]['cart_r_list_sum']="Y";
	$_code_comment[$_file_name]['cart_r_list_sum']="장바구니 상품 총 합계 참조금액";

	$_replace_code[$_file_name]['cart_r_list_dlvfee']="";
	$_replace_hangul[$_file_name]['cart_r_list_dlvfee']="참조배송비";
	$_auto_replace[$_file_name]['cart_r_list_dlvfee']="Y";
	$_code_comment[$_file_name]['cart_r_list_dlvfee']="해당 장바구니 적용 참조배송비";

	$_replace_code[$_file_name]['cart_r_list_dlvfee2']="";
	$_replace_hangul[$_file_name]['cart_r_list_dlvfee2']="참조장바구니배송비";
	$_auto_replace[$_file_name]['cart_r_list_dlvfee2']="Y";
	$_code_comment[$_file_name]['cart_r_list_dlvfee2']="해당 장바구니 적용 참조배송비";

	$_replace_code[$_file_name]['cart_r_list_pay']="";
	$_replace_hangul[$_file_name]['cart_r_list_pay']="참조장바구니결제금액";
	$_auto_replace[$_file_name]['cart_r_list_pay']="Y";
	$_code_comment[$_file_name]['cart_r_list_pay']="장바구니 상품 결제 참조금액";

	$_replace_code[$_file_name]['cart_r_list_event']="";
	$_replace_hangul[$_file_name]['cart_r_list_event']="참조이벤트할인금액";
	$_auto_replace[$_file_name]['cart_r_list_event']="Y";
	$_code_comment[$_file_name]['cart_r_list_event']="이벤트 존재시 할인되는 참조금액";

	$_replace_code[$_file_name]['cart_r_list_timesale']="";
	$_replace_hangul[$_file_name]['cart_r_list_timesale']="참조타임세일금액";
	$_auto_replace[$_file_name]['cart_r_list_timesale']="Y";
	$_code_comment[$_file_name]['cart_r_list_timesale']="타임세일 할인되는 참조금액";

	$_replace_code[$_file_name]['cart_r_list_msale']="";
	$_replace_hangul[$_file_name]['cart_r_list_msale']="참조회원할인금액";
	$_auto_replace[$_file_name]['cart_r_list_msale']="Y";
	$_code_comment[$_file_name]['cart_r_list_msale']="회원할인 존재시 할인되는 참조금액";

	$_replace_code[$_file_name]['cart_r_list_prdprc']="";
	$_replace_hangul[$_file_name]['cart_r_list_prdprc']="참조구매금액별할인금액";
	$_auto_replace[$_file_name]['cart_r_list_prdprc']="Y";
	$_code_comment[$_file_name]['cart_r_list_prdprc']="구매금액별 할인되는 참조금액";

	$_replace_code[$_file_name]['cart_r_list_milage']="";
	$_replace_hangul[$_file_name]['cart_r_list_milage']="참조적립금";
	$_auto_replace[$_file_name]['cart_r_list_milage']="Y";
	$_code_comment[$_file_name]['cart_r_list_milage']="장바구니 상품 총 참조적립금";

	$_replace_code[$_file_name]['cart_r_list_event_milage']="";
	$_replace_hangul[$_file_name]['cart_r_list_event_milage']="참조이벤트적립금";
	$_auto_replace[$_file_name]['cart_r_list_event_milage']="Y";
	$_code_comment[$_file_name]['cart_r_list_event_milage']="적립금 이벤트 총 적립되는 참조금액";

	$_replace_code[$_file_name]['cart_r_list_dlvfee2n'] = '';
	$_replace_hangul[$_file_name]['cart_r_list_dlvfee2n'] = '참조장바구니배송비(단위없음)';
	$_auto_replace[$_file_name]['cart_r_list_dlvfee2n'] = 'Y';
	$_code_comment[$_file_name]['cart_r_list_dlvfee2n'] = '해당 장바구니 적용 참조배송비 (단위 출력하지 않음)';

	$_replace_code[$_file_name]['cart_def_count']="";
	$_replace_hangul[$_file_name]['cart_def_count']="일반장바구니갯수";
	$_auto_replace[$_file_name]['cart_def_count']="Y";
	$_code_comment[$_file_name]['cart_def_count']="일반장바구니갯수";

	$_replace_code[$_file_name]['cart_sub_count']="";
	$_replace_hangul[$_file_name]['cart_sub_count']="정기장바구니갯수";
	$_auto_replace[$_file_name]['cart_sub_count']="Y";
	$_code_comment[$_file_name]['cart_sub_count']="정기장바구니갯수";

	$_replace_code[$_file_name]['cart_member_delete'] = "";
	$_replace_hangul[$_file_name]['cart_member_delete'] = "회원상품보관기간";
	$_auto_replace[$_file_name]['cart_member_delete'] = "Y";
	$_code_comment[$_file_name]['cart_member_delete'] = "회원상품보관기간";

	$_replace_code[$_file_name]['cart_gift_list']="";
	$_replace_hangul[$_file_name]['cart_gift_list']="사은품리스트";
	$_code_comment[$_file_name]['cart_gift_list']="사은품 리스트";
	$_replace_datavals[$_file_name]['cart_gift_list']="사은품이미지경로:img;사은품명:name;";
?>