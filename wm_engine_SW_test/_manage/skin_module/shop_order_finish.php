<?PHP

	$_replace_code[$_file_name]['order_no']="";
	$_replace_hangul[$_file_name]['order_no']="주문번호";
	$_code_comment[$_file_name]['order_no']="주문 완료 번호";
	$_auto_replace[$_file_name]['order_no']="Y";

	$_replace_code[$_file_name]['order_bemail']="";
	$_replace_hangul[$_file_name]['order_bemail']="주문자이메일";
	$_code_comment[$_file_name]['order_bemail']="주문자의 이메일 주소";
	$_auto_replace[$_file_name]['order_bemail']="Y";

	$_replace_code[$_file_name]['order_gift_form_start']="";
	$_replace_hangul[$_file_name]['order_gift_form_start']="사은품폼시작";
	$_code_comment[$_file_name]['order_gift_form_start']="사은품 선택 폼 시작 선언";
	$_auto_replace[$_file_name]['order_gift_form_start']="Y";

	$_replace_code[$_file_name]['order_gift_form_end']="";
	$_replace_hangul[$_file_name]['order_gift_form_end']="사은품폼끝";
	$_code_comment[$_file_name]['order_gift_form_end']="사은품 선택 폼 끝 선언";
	$_auto_replace[$_file_name]['order_gift_form_end']="Y";

	$_replace_code[$_file_name]['order_gift_list']="";
	$_replace_hangul[$_file_name]['order_gift_list']="사은품리스트";
	$_code_comment[$_file_name]['order_gift_list']="사은품 리스트";
	$_replace_datavals[$_file_name]['order_gift_list']="사은품선택:select;사은품이미지경로:img;사은품명:name;";

	$_replace_code[$_file_name]['order_gift_timing'] = ($cfg['order_gift_timing'] == '') ? 'Y' : '';
	$_replace_hangul[$_file_name]['order_gift_timing'] = '주문완료후사은품지급';
	$_auto_replace[$_file_name]['order_gift_timing'] = 'Y';

	$_replace_code[$_file_name]['order_account']="";
	$_replace_hangul[$_file_name]['order_account']="결제계좌정보";
	$_code_comment[$_file_name]['order_account']="고객이 입금해야 할 '은행명 계좌번호' 정보";
	$_auto_replace[$_file_name]['order_account']="Y";

	$_replace_code[$_file_name]['order_bank_limit']="";
	$_replace_hangul[$_file_name]['order_bank_limit']="무통장입금기한";
	$_code_comment[$_file_name]['order_bank_limit']="무통장 및 가상계좌의 입금 기한";
	$_auto_replace[$_file_name]['order_bank_limit']="Y";

	$_replace_code[$_file_name]['order_cart_sum']="";
	$_replace_hangul[$_file_name]['order_cart_sum']="총상품구매금액";
	$_auto_replace[$_file_name]['order_cart_sum']="Y";
	$_code_comment[$_file_name]['order_cart_sum']="상품 구매 금액 합계";

	$_replace_code[$_file_name]['order_delivery_fee']="";
	$_replace_hangul[$_file_name]['order_delivery_fee']="배송비";
	$_auto_replace[$_file_name]['order_delivery_fee']="Y";
	$_code_comment[$_file_name]['order_delivery_fee']="주문서 배송비";

	$_replace_code[$_file_name]['order_order_sum']="";
	$_replace_hangul[$_file_name]['order_order_sum']="총주문합계";
	$_auto_replace[$_file_name]['order_order_sum']="Y";
	$_code_comment[$_file_name]['order_order_sum']="주문서 결제 금액";

	$_replace_code[$_file_name]['total_prc']="";
	$_replace_hangul[$_file_name]['total_prc']="총주문금액";
	$_auto_replace[$_file_name]['total_prc']="Y";
	$_code_comment[$_file_name]['total_prc']="총 주문서 금액(콤마 제외 숫자만 출력)";

	$_replace_code[$_file_name]['pay_prc'] = numberOnly($ord['pay_prc']);
	$_replace_hangul[$_file_name]['pay_prc'] = '실결제금액';
	$_auto_replace[$_file_name]['pay_prc'] = 'Y';
	$_code_comment[$_file_name]['pay_prc'] = '할인 등이 적용 된 실제 결제 금액(콤마 제외 숫자만 출력)';

	$_replace_code[$_file_name]['alipay_trans_id']="";
	$_replace_hangul[$_file_name]['alipay_trans_id']="알리페이거래번호";
	$_auto_replace[$_file_name]['alipay_trans_id']="Y";
	$_code_comment[$_file_name]['alipay_trans_id']="알리페이 사용시 알리페이 거래번호";

	$_replace_code[$_file_name]['order_r_cart_sum']="";
	$_replace_hangul[$_file_name]['order_r_cart_sum']="참조총상품구매금액";
	$_auto_replace[$_file_name]['order_r_cart_sum']="Y";
	$_code_comment[$_file_name]['order_r_cart_sum']="상품 구매 금액 참조합계";

	$_replace_code[$_file_name]['order_r_delivery_fee']="";
	$_replace_hangul[$_file_name]['order_r_delivery_fee']="참조배송비";
	$_auto_replace[$_file_name]['order_r_delivery_fee']="Y";
	$_code_comment[$_file_name]['order_r_delivery_fee']="주문서 참조배송비";

	$_replace_code[$_file_name]['order_r_order_sum']="";
	$_replace_hangul[$_file_name]['order_r_order_sum']="참조총주문합계";
	$_auto_replace[$_file_name]['order_r_order_sum']="Y";
	$_code_comment[$_file_name]['order_r_order_sum']="주문서 결제 참조금액";

	$_replace_code[$_file_name]['total_r_prc']="";
	$_replace_hangul[$_file_name]['total_r_prc']="참조총주문금액";
	$_auto_replace[$_file_name]['total_r_prc']="Y";
	$_code_comment[$_file_name]['total_r_prc']="총 주문서 참조금액(콤마 제외 숫자만 출력)";

	$_replace_code[$_file_name]['tax_prc']="";
	$_replace_hangul[$_file_name]['tax_prc']="해외배송관세";
	$_auto_replace[$_file_name]['tax_prc']="Y";
	$_code_comment[$_file_name]['tax_prc']="해외배송관세";

	$_replace_code[$_file_name]['tax_r_prc']="";
	$_replace_hangul[$_file_name]['tax_r_prc']="참조해외배송관세";
	$_auto_replace[$_file_name]['tax_r_prc']="Y";
	$_code_comment[$_file_name]['tax_r_prc']="참조해외배송관세";

	$_replace_code[$_file_name]['order_finish_prd_list'] = '';
	$_replace_hangul[$_file_name]['order_finish_prd_list'] = '주문상품리스트';
	$_code_comment[$_file_name]['order_finish_prd_list'] = '주문상품리스트';
	$_replace_datavals[$_file_name]['order_finish_prd_list'] = $_user_code_typec['p'].';결제금액:total_prc;구매수량:buy_ea;상품옵션:option;옵션추가금액:option_prc;';;

	$_replace_code[$_file_name]['mypage_order_list_url'] = ($_POST['sno']) ? $root_url.'/mypage/order_list.php?sbscr=Y' : $root_url.'/mypage/order_list.php';
	$_replace_hangul[$_file_name]['mypage_order_list_url'] = '주문조회주소';
	$_auto_replace[$_file_name]['mypage_order_list_url'] = 'Y';
	$_code_comment[$_file_name]['mypage_order_list_url'] = '마이페이지 주문 조회 주소를 출력합니다.';

	$_replace_code[$_file_name]['dlv_edit'] = ($ord['sbono']) ? "<a href='#' onclick='mypageSbscr(\"{$ord['sbono']}\", \"edit\"); return false;'>" : '';
	$_replace_hangul[$_file_name]['dlv_edit'] = '배송지변경링크';
	$_code_comment[$_file_name]['dlv_edit'] = '정기배송 주문의 배송지를 변경';
	$_auto_replace[$_file_name]['dlv_edit'] = 'Y';

    $_replace_code[$_file_name]['order_finish_date'] = "";
    $_replace_hangul[$_file_name]['order_finish_date']="주문완료날짜";
    $_auto_replace[$_file_name]['order_finish_date']="Y";
    $_code_comment[$_file_name]['order_finish_date']="주문완료날짜";

    $_replace_code[$_file_name]['order_finish_address'] = "";
    $_replace_hangul[$_file_name]['order_finish_address'] = "주문완료배송지정보";
    $_code_comment[$_file_name]['order_finish_address'] = "주문시 배송지 정보";
    $_replace_datavals[$_file_name]['order_finish_address'] = "받는사람:addressee_name;우편번호:addressee_zip;주소:addressee_addr1;상세주소:addressee_addr2;상세주소3:addressee_addr3;상세주소4:addressee_addr4;전화번호:addressee_phone;모바일번호:addressee_cell;배송메모:dlv_memo;신규배송지변경:dlv_addr_change;";

    $_replace_code[$_file_name]['pay_type_str'] = "";
    $_replace_hangul[$_file_name]['pay_type_str'] = "결제방식";
    $_code_comment[$_file_name]['pay_type_str'] = "해당 주문의 결제 방식 출력";
    $_auto_replace[$_file_name]['pay_type_str'] = "Y";

    $_replace_code[$_file_name]['order_finish_ord_cash'] = "";
    $_replace_hangul[$_file_name]['order_finish_ord_cash'] = "무통장입금정보";
    $_code_comment[$_file_name]['order_finish_ord_cash'] = "무통장 입금으로 결제한 경우의 구문";
    $_replace_datavals[$_file_name]['order_finish_ord_cash'] = "입금계좌정보:bank;입금자명:bank_name;입금기한:bank_limit;";

    $_replace_code[$_file_name]['order_finish_ord_card'] = "";
    $_replace_hangul[$_file_name]['order_finish_ord_card'] = "카드정보";
    $_code_comment[$_file_name]['order_finish_ord_card'] = "카드로 결제한 경우의 구문";
    $_replace_datavals[$_file_name]['order_finish_ord_card'] = "카드명:card_name;할부개월수:quota_str;카드영수증:receipt;";

    $_replace_code[$_file_name]['order_finish_ord_receipt'] = "";
    $_replace_hangul[$_file_name]['order_finish_ord_receipt'] = "현금영수증정보";
    $_code_comment[$_file_name]['order_finish_ord_receipt'] = "현금영수증으로 결제한 경우의 구문";
    $_replace_datavals[$_file_name]['order_finish_ord_receipt'] = "발급상태:stat;승인번호:cash_reg_num;국세청승인번호:chk_approval_no;현금영수증보기:link;현금영수증링크:link_addr;";

    $_replace_code[$_file_name]['sbscr_firsttime_pay_prc'] = '';
	$_replace_hangul[$_file_name]['sbscr_firsttime_pay_prc'] = '첫결제금액';
	$_auto_replace[$_file_name]['sbscr_firsttime_pay_prc'] = 'Y';
	$_code_comment[$_file_name]['sbscr_firsttime_pay_prc'] = '첫결제금액';
?>