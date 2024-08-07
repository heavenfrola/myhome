<?PHP

	$_replace_code[$_file_name]['buyer_name'] = "";
	$_replace_hangul[$_file_name]['buyer_name'] = "주문자";
	$_code_comment[$_file_name]['buyer_name'] = "주문자 성명 출력";
	$_auto_replace[$_file_name]['buyer_name'] = "Y";

	$_replace_code[$_file_name]['ono'] = "";
	$_replace_hangul[$_file_name]['ono'] = "주문번호";
	$_code_comment[$_file_name]['ono'] = "주문번호 출력";
	$_auto_replace[$_file_name]['ono'] = "Y";

	$_replace_code[$_file_name]['mypage_order_stat_img_list'] = "";
	$_replace_hangul[$_file_name]['mypage_order_stat_img_list'] = "처리상태이미지리스트";
	$_code_comment[$_file_name]['mypage_order_stat_img_list'] = "처리상태 이미지 온 오프형식 출력";
	$_replace_datavals[$_file_name]['mypage_order_stat_img_list'] = "처리상태이미지:stat_img;";

	$_replace_code[$_file_name]['mypage_order_stat_img'] = "";
	$_replace_hangul[$_file_name]['mypage_order_stat_img'] = "처리상태이미지";
	$_code_comment[$_file_name]['mypage_order_stat_img'] = "처리상태 이미지 온 오프형식 출력";
	$_replace_datavals[$_file_name]['mypage_order_stat_img'] = "처리상태이미지:stat_img;";

	$_tmp = eval(__lang_order_stat);
	$_replace_code[$_file_name]['mypage_order_stat'] = $_tmp[$ord['stat']];
	$_replace_hangul[$_file_name]['mypage_order_stat'] = '주문상태';
	$_code_comment[$_file_name]['mypage_order_stat'] = '주문상태명 출력';
	$_auto_replace[$_file_name]['mypage_order_stat'] = 'Y';

	$_replace_code[$_file_name]['mypage_order_stat_code'] = $ord['stat'];
	$_replace_hangul[$_file_name]['mypage_order_stat_code'] = '주문상태코드';
	$_code_comment[$_file_name]['mypage_order_stat_code'] = '주문상태명 출력';
	$_auto_replace[$_file_name]['mypage_order_stat_code'] = 'Y';

	$_replace_code[$_file_name]['mypage_dlv_trace'] = "";
	$_replace_hangul[$_file_name]['mypage_dlv_trace'] = "배송추적";
	$_code_comment[$_file_name]['mypage_dlv_trace'] = "송장번호가 존재할 경우의 배송정보 구문";
	$_replace_datavals[$_file_name]['mypage_dlv_trace'] = "배송추척주소:url;배송업체:name;송장번호:dlv_code;";

	$_replace_code[$_file_name]['mypage_ord_cart_list'] = "";
	$_replace_hangul[$_file_name]['mypage_ord_cart_list'] = "주문상품리스트";
	$_code_comment[$_file_name]['mypage_ord_cart_list'] = "주문한 상품의 리스트";
	$_replace_datavals[$_file_name]['mypage_ord_cart_list'] = "상품명:name:해당 상품의 상세 페이지의 링크를 포함한 상품명 출력;상품이미지경로:img:상품의 소 이미지의 경로 출력;상품이미지정보:imgstr:상품의 사이즈 정보 출력 (예 - width=100 height=100);상품이미지:imgr:상품의 소 이미지 출력;상품이미지(링크포함):imgr_link:상품링크(A 태그)를 포함한 소 이미지 출력;옵션정보:option_str:주문 상품의 옵션 정보;상품가격:sell_prc;구매수량:buy_ea;상품적립금:milage;총합계금액:total_prc;주문상태:stat:상품별 현재 주문 상태 출력(미입금, 입금완료, 배송중, 배송완료 등);후기작성:review_url:상품별 후기작성페이지 주소;추가필드:etc;상품번호:prdno;참조상품가격:sell_r_prc;참조상품적립금:r_milage;참조총합계금액:total_r_prc;기타메세지:etc:장바구니 옵션 기타메세지;배송추적링크:dlv_url;배송업체:dlv_name;송장번호:dlv_code;";

	$_replace_code[$_file_name]['prd_prc'] = "";
	$_replace_hangul[$_file_name]['prd_prc'] = "총상품구매금액";
	$_code_comment[$_file_name]['prd_prc'] = "주문한 상품의 합계 금액 출력";
	$_auto_replace[$_file_name]['prd_prc'] = "Y";

	$_replace_code[$_file_name]['sale2'] = "";
	$_replace_hangul[$_file_name]['sale2'] = "이벤트할인금액";
	$_code_comment[$_file_name]['sale2'] = "주문에 적용된 이벤트할인 금액 출력";
	$_auto_replace[$_file_name]['sale2'] = "Y";

	$_replace_code[$_file_name]['sale3'] = "";
	$_replace_hangul[$_file_name]['sale3'] = "타임세일할인금액";
	$_code_comment[$_file_name]['sale3'] = "주문에 적용된 타임세일 할인 금액 출력";
	$_auto_replace[$_file_name]['sale3'] = "Y";

	$_replace_code[$_file_name]['sale4'] = "";
	$_replace_hangul[$_file_name]['sale4'] = "회원할인금액";
	$_code_comment[$_file_name]['sale4'] = "주문에 적용된 회원할인 금액 출력";
	$_auto_replace[$_file_name]['sale4'] = "Y";

	$_replace_code[$_file_name]['sale5'] = "";
	$_replace_hangul[$_file_name]['sale5'] = "쿠폰할인금액";
	$_code_comment[$_file_name]['sale5'] = "주문에 적용된 쿠폰할인 금액 출력";
	$_auto_replace[$_file_name]['sale5'] = "Y";

	$_replace_code[$_file_name]['sale6'] = "";
	$_replace_hangul[$_file_name]['sale6'] = "주문상품금액별할인금액";
	$_code_comment[$_file_name]['sale6'] = "주문에 적용된 주문상품 금액별 할인 금액 출력";
	$_auto_replace[$_file_name]['sale6'] = "Y";

	$_replace_code[$_file_name]['sale7'] = "";
	$_replace_hangul[$_file_name]['sale7'] = "상품별쿠폰할인금액";
	$_code_comment[$_file_name]['sale7'] = "주문에 적용된 상품별쿠폰할인 금액 출력";
	$_auto_replace[$_file_name]['sale7'] = "Y";

	$_replace_code[$_file_name]['cpn_name'] = "";
	$_replace_hangul[$_file_name]['cpn_name'] = "사용쿠폰명";
	$_code_comment[$_file_name]['cpn_name'] = "사용한 쿠폰의 이름을 출력합니다.";
	$_auto_replace[$_file_name]['cpn_name'] = "Y";

	$_replace_code[$_file_name]['total_sale_prc'] = "";
	$_replace_hangul[$_file_name]['total_sale_prc'] = "총할인금액";
	$_code_comment[$_file_name]['total_sale_prc'] = "주문에 적용된 총 할인금액 합계";
	$_auto_replace[$_file_name]['total_sale_prc'] = "Y";

	$_replace_code[$_file_name]['dlv_prc'] = "";
	$_replace_hangul[$_file_name]['dlv_prc'] = "배송비";
	$_code_comment[$_file_name]['dlv_prc'] = "주문에 적용된 배송비 출력";
	$_auto_replace[$_file_name]['dlv_prc'] = "Y";

	$_replace_code[$_file_name]['total_prc'] = "";
	$_replace_hangul[$_file_name]['total_prc'] = "총주문합계금액";
	$_code_comment[$_file_name]['total_prc'] = "주문의 합계 금액 출력";
	$_auto_replace[$_file_name]['total_prc'] = "Y";

	$_replace_code[$_file_name]['total_milage'] = "";
	$_replace_hangul[$_file_name]['total_milage'] = "적립금";
	$_code_comment[$_file_name]['total_milage'] = "적립된 금액 출력";
	$_auto_replace[$_file_name]['total_milage'] = "Y";

	$_replace_code[$_file_name]['ord_chg1_url'] = "";
	$_replace_hangul[$_file_name]['ord_chg1_url'] = "주문변경";
	$_code_comment[$_file_name]['ord_chg1_url'] = "주문변경 링크 주소 출력";
	$_auto_replace[$_file_name]['ord_chg1_url'] = "Y";

	$_replace_code[$_file_name]['ord_chg2_url'] = "";
	$_replace_hangul[$_file_name]['ord_chg2_url'] = "주문문의";
	$_code_comment[$_file_name]['ord_chg2_url'] = "주문문의 링크 주소 출력";
	$_auto_replace[$_file_name]['ord_chg2_url'] = "Y";

	$_replace_code[$_file_name]['ord_chg3_url'] = "";
	$_replace_hangul[$_file_name]['ord_chg3_url'] = "취소신청";
	$_code_comment[$_file_name]['ord_chg3_url'] = "취소신청/환불신청 링크 주소 출력";
	$_auto_replace[$_file_name]['ord_chg3_url'] = "Y";

	$_replace_code[$_file_name]['ord_chg4_url'] = "";
	$_replace_hangul[$_file_name]['ord_chg4_url'] = "환불신청";
	$_code_comment[$_file_name]['ord_chg4_url'] = "취소신청/환불신청 링크 주소 출력";
	$_auto_replace[$_file_name]['ord_chg4_url'] = "Y";

	$_replace_code[$_file_name]['ord_chg5_url'] = "";
	$_replace_hangul[$_file_name]['ord_chg5_url'] = "반품신청";
	$_code_comment[$_file_name]['ord_chg5_url'] = "반품신청 링크 주소 출력";
	$_auto_replace[$_file_name]['ord_chg5_url'] = "Y";

	$_replace_code[$_file_name]['ord_receipt_url'] = "";
	$_replace_hangul[$_file_name]['ord_receipt_url'] = "계산서출력";
	$_auto_replace[$_file_name]['ord_receipt_url'] = "Y";
	$_code_comment[$_file_name]['ord_receipt_url'] = "계산서출력 링크 주소 출력";

	$_replace_code[$_file_name]['mypage_ord_1to1_list'] = "";
	$_replace_hangul[$_file_name]['mypage_ord_1to1_list'] = "고객상담리스트";
	$_code_comment[$_file_name]['mypage_ord_1to1_list'] = "해당 주문의 고객 상담 내역 리스트";
	$_replace_datavals[$_file_name]['mypage_ord_1to1_list'] = "글고유번호:no;글번호:widx;글분류:cate:상담 내용의 글 분류 출력(주문, 취소, 반품등);글제목:title:상담 내용 조회 페이지의 링크를 포함한 글의 제목 출력;글제목(링크없음):title2:링크를 포함하지 않은 글의 제목 출력;링크:link:게시물링크주소;등록일:date:상담 등록일(년/월/일);답변유무:reply_yn:관리자의 처리 상태 출력(처리중, 완료);문의내용:content:상담 내용 출력;답변내용:reply:관리자의 답변 내용 출력;1대1첨부파일1:img1:첫번째 첨부 이미지 출력;1대1첨부파일2:img2:두번째 첨부 이미지 출력;1대1파일아이콘:file_icon:첨부 이미지 파일이 존재할 경우 아이콘 출력;";

	$_replace_code[$_file_name]['pay_type_str'] = "";
	$_replace_hangul[$_file_name]['pay_type_str'] = "결제방식";
	$_code_comment[$_file_name]['pay_type_str'] = "해당 주문의 결제 방식 출력";
	$_auto_replace[$_file_name]['pay_type_str'] = "Y";

	$_replace_code[$_file_name]['mypage_ord_cash'] = "";
	$_replace_hangul[$_file_name]['mypage_ord_cash'] = "무통장입금정보";
	$_code_comment[$_file_name]['mypage_ord_cash'] = "무통장 입금으로 결제한 경우의 구문";
	$_replace_datavals[$_file_name]['mypage_ord_cash'] = "입금계좌정보:bank;";

	$_replace_code[$_file_name]['mypage_ord_card'] = "";
	$_replace_hangul[$_file_name]['mypage_ord_card'] = "카드정보";
	$_code_comment[$_file_name]['mypage_ord_card'] = "카드로 결제한 경우의 구문";
	$_replace_datavals[$_file_name]['mypage_ord_card'] = "카드명:card_name;할부개월수:quota_str;카드영수증:receipt;";

	$_replace_code[$_file_name]['mypage_ord_receipt'] = "";
	$_replace_hangul[$_file_name]['mypage_ord_receipt'] = "현금영수증정보";
	$_code_comment[$_file_name]['mypage_ord_receipt'] = "현금영수증으로 결제한 경우의 구문";
	$_replace_datavals[$_file_name]['mypage_ord_receipt'] = "발급상태:stat;승인번호:cash_reg_num;현금영수증보기:link;";

	$_replace_code[$_file_name]['date2'] = "";
	$_replace_hangul[$_file_name]['date2'] = "입금일";
	$_code_comment[$_file_name]['date2'] = "입금이 확인된 날짜 출력";
	$_auto_replace[$_file_name]['date2'] = "Y";

	$_replace_code[$_file_name]['pay_prc'] = "";
	$_replace_hangul[$_file_name]['pay_prc'] = "총결제금액";
	$_code_comment[$_file_name]['pay_prc'] = "최종 결제 금액 출력";
	$_auto_replace[$_file_name]['pay_prc'] = "Y";

	$_replace_code[$_file_name]['milage_prc_c'] = "";
	$_replace_hangul[$_file_name]['milage_prc_c'] = "적립금결제금액";
	$_code_comment[$_file_name]['milage_prc_c'] = "적립금을 사용하여 결제한 금액 출력";
	$_auto_replace[$_file_name]['milage_prc_c'] = "Y";

	$_replace_code[$_file_name]['emoney_prc_c'] = "";
	$_replace_hangul[$_file_name]['emoney_prc_c'] = "예치금결제금액";
	$_code_comment[$_file_name]['emoney_prc_c'] = "예치금을 사용하여 결제한 금액 출력";
	$_auto_replace[$_file_name]['emoney_prc_c'] = "Y";

	$_replace_code[$_file_name]['date1'] = "";
	$_replace_hangul[$_file_name]['date1'] = "주문일";
	$_code_comment[$_file_name]['date1'] = "주문일 출력";
	$_auto_replace[$_file_name]['date1'] = "Y";

	$_replace_code[$_file_name]['buyer_phone'] = "";
	$_replace_hangul[$_file_name]['buyer_phone'] = "주문자전화번호";
	$_code_comment[$_file_name]['buyer_phone'] = "주문자 전화번호 출력";
	$_auto_replace[$_file_name]['buyer_phone'] = "Y";

	$_replace_code[$_file_name]['buyer_cell'] = "";
	$_replace_hangul[$_file_name]['buyer_cell'] = "주문자휴대전화";
	$_code_comment[$_file_name]['buyer_cell'] = "주문자 휴대번호 출력";
	$_auto_replace[$_file_name]['buyer_cell'] = "Y";

	$_replace_code[$_file_name]['addressee_name'] = "";
	$_replace_hangul[$_file_name]['addressee_name'] = "수령자";
	$_code_comment[$_file_name]['addressee_name'] = "수령자 성명 출력";
	$_auto_replace[$_file_name]['addressee_name'] = "Y";

	$_replace_code[$_file_name]['nations'] = "";
	$_replace_hangul[$_file_name]['nations'] = "배송국가";
	$_auto_replace[$_file_name]['nations'] = "Y";

	$_replace_code[$_file_name]['delivery_com'] = "";
	$_replace_hangul[$_file_name]['delivery_com'] = "배송업체";
	$_auto_replace[$_file_name]['delivery_com'] = "Y";

	$_replace_code[$_file_name]['addressee_zip'] = "";
	$_replace_hangul[$_file_name]['addressee_zip'] = "배송지우편번호";
	$_code_comment[$_file_name]['addressee_zip'] = "배송지 우편번호 출력";
	$_auto_replace[$_file_name]['addressee_zip'] = "Y";

	$_replace_code[$_file_name]['addressee_addr1'] = "";
	$_replace_hangul[$_file_name]['addressee_addr1'] = "배송지주소1";
	$_code_comment[$_file_name]['addressee_addr1'] = "배송지 주소 출력";
	$_auto_replace[$_file_name]['addressee_addr1'] = "Y";

	$_replace_code[$_file_name]['addressee_addr2'] = "";
	$_replace_hangul[$_file_name]['addressee_addr2'] = "배송지주소2";
	$_code_comment[$_file_name]['addressee_addr2'] = "배송지 상세주소 출력";
	$_auto_replace[$_file_name]['addressee_addr2'] = "Y";

	$_replace_code[$_file_name]['addressee_addr3'] = "";
	$_replace_hangul[$_file_name]['addressee_addr3'] = "배송지주소3";
	$_code_comment[$_file_name]['addressee_addr3'] = "배송지 상세주소 출력";
	$_auto_replace[$_file_name]['addressee_addr3'] = "Y";

	$_replace_code[$_file_name]['addressee_addr4'] = "";
	$_replace_hangul[$_file_name]['addressee_addr4'] = "배송지주소4";
	$_code_comment[$_file_name]['addressee_addr4'] = "배송지 상세주소 출력";
	$_auto_replace[$_file_name]['addressee_addr4'] = "Y";

	$_replace_code[$_file_name]['addressee_phone'] = "";
	$_replace_hangul[$_file_name]['addressee_phone'] = "수령자전화번호";
	$_code_comment[$_file_name]['addressee_phone'] = "수령자 전화번호 출력";
	$_auto_replace[$_file_name]['addressee_phone'] = "Y";

	$_replace_code[$_file_name]['addressee_cell'] = "";
	$_replace_hangul[$_file_name]['addressee_cell'] = "수령자휴대전화";
	$_code_comment[$_file_name]['addressee_cell'] = "수령자 휴대전화번호 출력";
	$_auto_replace[$_file_name]['addressee_cell'] = "Y";

	$_replace_code[$_file_name]['dlv_memo'] = "";
	$_replace_hangul[$_file_name]['dlv_memo'] = "배송메모";
	$_code_comment[$_file_name]['dlv_memo'] = "주문메세지 혹시 배송 메모 출력";
	$_auto_replace[$_file_name]['dlv_memo'] = "Y";

	$_replace_code[$_file_name]['mypage_ord_backtolist'] = "";
	$_replace_hangul[$_file_name]['mypage_ord_backtolist'] = "목록보기";
	$_code_comment[$_file_name]['mypage_ord_backtolist'] = "비회원주문이 아닌 경우 주문목록으로 돌아가기 구문";
	$_replace_datavals[$_file_name]['mypage_ord_backtolist'] = "목록보기링크:back_url;";

?>