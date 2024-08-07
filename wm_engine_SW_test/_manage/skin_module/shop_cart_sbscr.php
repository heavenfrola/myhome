<?PHP

	function getShopCart() {
		global $_file_name, $cfg, $scfg;
		include 'shop_cart.php';
		return array(
			$_replace_code[$_file_name],
			$_replace_hangul[$_file_name],
			$_auto_replace[$_file_name],
			$_code_comment[$_file_name],
			$_replace_datavals[$_file_name],
		);
	}
	$_shop_cart_code = getShopCart();

	$_replace_code[$_file_name]['sbscr_cart_form_start']="";
	$_replace_hangul[$_file_name]['sbscr_cart_form_start']="폼시작";
	$_auto_replace[$_file_name]['sbscr_cart_form_start']="Y";
	$_code_comment[$_file_name]['sbscr_cart_form_start']="장바구니 폼 시작 선언";

	$_replace_code[$_file_name]['cart_sbscr_list']="";
	$_replace_hangul[$_file_name]['cart_sbscr_list']="정기장바구니리스트";
	$_code_comment[$_file_name]['cart_sbscr_list']="장바구니 상품의 리스트";
	$_replace_datavals[$_file_name]['cart_sbscr_list']=$_shop_cart_code[4]['cart_list'].";배송기간:dlv_date;배송요일:week;배송주기:period_text;";

	$_replace_code[$_file_name]['cart_partner_sbscr_list'] = '';
	$_replace_hangul[$_file_name]['cart_partner_sbscr_list'] = '정기장바구니리스트(입점)';
	$_code_comment[$_file_name]['cart_partner_sbscr_list'] = '입점몰 기능 사용시 업체별 반복문';
	$_replace_datavals[$_file_name]['cart_partner_sbscr_list'] = '입점사코드:partner_no;입점사명:partner_name;본사여부:is_master;입점사여부:is_partner;입점사별무료배송비:dlv_free_limit;장바구니합계금액:cart_list_sum;이벤트할인금액:sale2;회원할인금액:sale4;구매금액별할인금액:sale6;상품별쿠폰할인금액:sale7;정기배송할인금액:sale8;배송비:cart_list_dlvfee;장바구니배송비:cart_list_dlvfee;장바구니배송비(단위없음):cart_list_dlvfee2n;장바구니결제금액:cart_list_pay;적립금:cart_list_milage;장바구니리스트(입점)서브:'.$file_order.'cart_partner_sbscr_sub_list:입점몰별 장바구니 목록:editable;참조장바구니합계금액:cart_r_list_sum;참조입점사별무료배송비:dlv_r_free_limit;참조이벤트할인금액:r_sale2;참조회원할인금액:r_sale4;참조구매금액별할인금액:r_sale6;참조배송비:cart_r_list_dlvfee;참조장바구니배송비:cart_r_list_dlvfee;참조장바구니결제금액:cart_r_list_pay;참조적립금:cart_r_list_milage;참조장바구니배송비(단위없음):cart_r_list_dlvfee2n;입점사장바구니개수:partner_cart_cnt;일반배송비(단위없음):cart_list_basic_dlvfee;일반배송비:cart_list_basic_dlvfee2;개별배송비(단위없음):cart_list_prd_dlvfee;개별배송비:cart_list_prd_dlvfee2;';

	$_replace_code[$_file_name]['sbscr_cart_form_end']="";
	$_replace_hangul[$_file_name]['sbscr_cart_form_end']="폼끝";
	$_auto_replace[$_file_name]['sbscr_cart_form_end']="Y";
	$_code_comment[$_file_name]['sbscr_cart_form_end']="장바구니 폼 끝 선언";

	$_replace_code[$_file_name]['sbscr_cart_yn']="";
	$_replace_hangul[$_file_name]['sbscr_cart_yn']="정기배송여부";
	$_auto_replace[$_file_name]['sbscr_cart_yn']="Y";
	$_code_comment[$_file_name]['sbscr_cart_yn']="정기배송여부";

	$_replace_code[$_file_name]['sbscr_cart_order_url']="";
	$_replace_hangul[$_file_name]['sbscr_cart_order_url']="주문하기";
	$_auto_replace[$_file_name]['sbscr_cart_order_url']="Y";
	$_code_comment[$_file_name]['sbscr_cart_order_url']="주문하기 링크 주소 출력";

	$_replace_code[$_file_name]['sbscr_cart_sorder_url']="";
	$_replace_hangul[$_file_name]['sbscr_cart_sorder_url']="선택주문하기";
	$_auto_replace[$_file_name]['sbscr_cart_sorder_url']="Y";
	$_code_comment[$_file_name]['sbscr_cart_sorder_url']="선택주문 링크 주소 출력";

	$_replace_code[$_file_name]['sbscr_cart_delete_url']="";
	$_replace_hangul[$_file_name]['sbscr_cart_delete_url']="선택삭제";
	$_auto_replace[$_file_name]['sbscr_cart_delete_url']="Y";
	$_code_comment[$_file_name]['sbscr_cart_delete_url']="선택삭제 링크 주소 출력";

	$_replace_code[$_file_name]['sbscr_cart_truncate_url']="";
	$_replace_hangul[$_file_name]['sbscr_cart_truncate_url']="장바구니비우기";
	$_auto_replace[$_file_name]['sbscr_cart_truncate_url']="Y";
	$_code_comment[$_file_name]['sbscr_cart_truncate_url']="장바구니비우기 링크 주소 출력";

	$_replace_code[$_file_name]['sbscr_cart_shopping_url']="";
	$_replace_hangul[$_file_name]['sbscr_cart_shopping_url']="계속쇼핑하기";
	$_auto_replace[$_file_name]['sbscr_cart_shopping_url']="Y";
	$_code_comment[$_file_name]['sbscr_cart_shopping_url']="계속쇼핑하기 링크 주소 출력";

	$_replace_code[$_file_name]['sbscr_cart_receipt_url']="";
	$_replace_hangul[$_file_name]['sbscr_cart_receipt_url']="계산서출력";
	$_auto_replace[$_file_name]['sbscr_cart_receipt_url']="Y";
	$_code_comment[$_file_name]['sbscr_cart_receipt_url']="계산서출력 링크 주소 출력";

	$_replace_code[$_file_name]['sbscr_cart_list_sum']="";
	$_replace_hangul[$_file_name]['sbscr_cart_list_sum']="장바구니합계금액";
	$_auto_replace[$_file_name]['sbscr_cart_list_sum']="Y";
	$_code_comment[$_file_name]['sbscr_cart_list_sum']="장바구니 상품 총 합계 금액";

	$_replace_code[$_file_name]['sbscr_cart_list_pay']="";
	$_replace_hangul[$_file_name]['sbscr_cart_list_pay']="장바구니결제금액";
	$_auto_replace[$_file_name]['sbscr_cart_list_pay']="Y";
	$_code_comment[$_file_name]['sbscr_cart_list_pay']="장바구니 상품 결제 금액";

	$_replace_code[$_file_name]['sbscr_cart_list_r_pay']="";
	$_replace_hangul[$_file_name]['sbscr_cart_list_r_pay']="참조장바구니결제금액";
	$_auto_replace[$_file_name]['sbscr_cart_list_r_pay']="Y";
	$_code_comment[$_file_name]['sbscr_cart_list_r_pay']="장바구니 상품 결제 금액";

	$_replace_code[$_file_name]['sbscr_cart_list_dlvfee']="";
	$_replace_hangul[$_file_name]['sbscr_cart_list_dlvfee']="배송비";
	$_auto_replace[$_file_name]['sbscr_cart_list_dlvfee']="Y";
	$_code_comment[$_file_name]['sbscr_cart_list_dlvfee']="해당 장바구니 적용 배송비";

	$_replace_code[$_file_name]['sbscr_cart_list_dlvfee2']="";
	$_replace_hangul[$_file_name]['sbscr_cart_list_dlvfee2']="장바구니배송비";
	$_auto_replace[$_file_name]['sbscr_cart_list_dlvfee2']="Y";
	$_code_comment[$_file_name]['sbscr_cart_list_dlvfee2']="해당 장바구니 적용 배송비";

	$_replace_code[$_file_name]['sbscr_cart_list_dlvfee2n'] = '';
	$_replace_hangul[$_file_name]['sbscr_cart_list_dlvfee2n'] = '장바구니배송비(단위없음)';
	$_auto_replace[$_file_name]['sbscr_cart_list_dlvfee2n'] = 'Y';
	$_code_comment[$_file_name]['sbscr_cart_list_dlvfee2n'] = '해당 장바구니 적용 배송비 (단위 출력하지 않음)';

	$_replace_code[$_file_name]['sbscr_cart_def_count']="";
	$_replace_hangul[$_file_name]['sbscr_cart_def_count']="일반장바구니갯수";
	$_auto_replace[$_file_name]['sbscr_cart_def_count']="Y";
	$_code_comment[$_file_name]['sbscr_cart_def_count']="일반장바구니갯수";

	$_replace_code[$_file_name]['sbscr_cart_sub_count']="";
	$_replace_hangul[$_file_name]['sbscr_cart_sub_count']="정기장바구니갯수";
	$_auto_replace[$_file_name]['sbscr_cart_sub_count']="Y";
	$_code_comment[$_file_name]['sbscr_cart_sub_count']="정기장바구니갯수";

	$_replace_code[$_file_name]['sbscr_cart_list_sbscr_sale']="";
	$_replace_hangul[$_file_name]['sbscr_cart_list_sbscr_sale']="정기배송할인금액";
	$_auto_replace[$_file_name]['sbscr_cart_list_sbscr_sale']="Y";
	$_code_comment[$_file_name]['sbscr_cart_list_sbscr_sale']="정기배송할인금액";

	$_replace_code[$_file_name]['sbscr_cart_list_msale']="";
	$_replace_hangul[$_file_name]['sbscr_cart_list_msale']="회원할인금액";
	$_auto_replace[$_file_name]['sbscr_cart_list_msale']="Y";
	$_code_comment[$_file_name]['sbscr_cart_list_msale']="회원할인 존재시 할인되는 금액";

	$_replace_code[$_file_name]['sbscr_cart_list_event']="";
	$_replace_hangul[$_file_name]['sbscr_cart_list_event']="이벤트할인금액";
	$_auto_replace[$_file_name]['sbscr_cart_list_event']="Y";
	$_code_comment[$_file_name]['sbscr_cart_list_event']="이벤트 존재시 할인되는 금액";

	// if 문 처리를 위해 일반 장바구니의 코드 중 정의 되지 않은 코드 가져오지
	foreach($_shop_cart_code[1] as $key => $val) {
		if($key == 'cart_list') continue;
		foreach($_replace_hangul[$_file_name] as $key2 => $val2) {
			if($val == $val2) continue 2;
		}
		$_replace_code[$_file_name][$key] = '';
		$_replace_hangul[$_file_name][$key] = $val;
		$_auto_replace[$_file_name][$key] = 'Y';
	}

	$_replace_code[$_file_name]['sbscr_firsttime_pay_prc'] = '';
	$_replace_hangul[$_file_name]['sbscr_firsttime_pay_prc'] = '첫결제금액';
	$_auto_replace[$_file_name]['sbscr_firsttime_pay_prc'] = 'Y';
	$_code_comment[$_file_name]['sbscr_firsttime_pay_prc'] = '첫결제금액';

	$_replace_code[$_file_name]['sbscr_firsttime_r_pay_prc'] = '';
	$_replace_hangul[$_file_name]['sbscr_firsttime_r_pay_prc'] = '참조첫결제금액';
	$_auto_replace[$_file_name]['sbscr_firsttime_r_pay_prc'] = 'Y';
	$_code_comment[$_file_name]['sbscr_firsttime_r_pay_prc'] = '참조첫결제금액';

?>