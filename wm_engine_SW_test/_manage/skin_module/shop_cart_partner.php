<?PHP

	$file_order = ($_file_name == "shop_order.php") ? "order_" : "";
	$sbscr = ($_GET['sbscr']=='Y') ? 'Y':'N';
	$file_sbscr = ($sbscr=='Y') ? "sbscr_" : "";

	$_replace_code[$_file_name][$file_order.'cart_partner_'.$file_sbscr.'list'] = '';
	$_replace_hangul[$_file_name][$file_order.'cart_partner_'.$file_sbscr.'list'] = '장바구니리스트(입점)';
	$_code_comment[$_file_name][$file_order.'cart_partner_'.$file_sbscr.'list'] = '입점몰 기능 사용시 업체별 반복문';
	$_replace_datavals[$_file_name][$file_order.'cart_partner_'.$file_sbscr.'list'] = '입점사코드:partner_no;입점사명:partner_name;본사여부:is_master;입점사여부:is_partner;입점사별무료배송비:dlv_free_limit;장바구니합계금액:cart_list_sum;이벤트할인금액:sale2;회원할인금액:sale4;구매금액별할인금액:sale6;상품별쿠폰할인금액:sale7;정기배송할인금액:sale8;배송비:cart_list_dlvfee;장바구니배송비:cart_list_dlvfee;장바구니배송비(단위없음):cart_list_dlvfee2n;장바구니결제금액:cart_list_pay;적립금:cart_list_milage;장바구니리스트(입점)서브:'.$file_order.'cart_partner_'.$file_sbscr.'sub_list:입점몰별 장바구니 목록:editable;참조장바구니합계금액:cart_r_list_sum;참조입점사별무료배송비:dlv_r_free_limit;참조이벤트할인금액:r_sale2;참조회원할인금액:r_sale4;참조구매금액별할인금액:r_sale6;참조배송비:cart_r_list_dlvfee;참조장바구니배송비:cart_r_list_dlvfee;참조장바구니결제금액:cart_r_list_pay;참조적립금:cart_r_list_milage;참조장바구니배송비(단위없음):cart_r_list_dlvfee2n;입점사장바구니개수:partner_cart_cnt;일반배송비(단위없음):cart_list_basic_dlvfee;일반배송비:cart_list_basic_dlvfee2;개별배송비(단위없음):cart_list_prd_dlvfee;개별배송비:cart_list_prd_dlvfee2;';

?>