<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' | 정기배송 장바구니 리스트
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_plugin/subScription/sbscr.lib.php";

	$_tmp="<form name=\"cartSbscrFrm\" method=\"post\" action=\"$root_url/main/exec.php\" target=\"hidden$now\" style=\"margin:0px\">
			<input type=\"hidden\" name=\"exec_file\" value=\"cart/cart.exe.php\">
			<input type=\"hidden\" name=\"exec\" value=\"\">
			<input type=\"hidden\" name=\"sbscr\" value=\"Y\">
			";

	$_replace_code[$_file_name]['sbscr_cart_form_start'] = $_tmp;

	$yoil = array("","월","화","수","목","금","토","일");

	$_tmp="";
	$_line=getModuleContent("cart_sbscr_list");
	if(!$_skin[cart_list_imgw]) $_skin[cart_list_imgw]=50;
	if(!$_skin[cart_list_imgh]) $_skin[cart_list_imgh]=50;

	$cart_no_arr = array();
	//장바구니 상품 목록:cartList(옵션사이,옵션명과가격사이,옵션앞,옵션뒤)

	//장바구니 상품 목록
	$sbscr_yn = 'Y';
	$ptnOrd = new OrderCart();
	while($cart = cartList($_skin['cart_list_btw_opt'], $_skin['cart_list_opt_prc'], $_skin['cart_list_opt_f'], $_skin['cart_list_opt_b'], $_skin['cart_list_imgw'] , $_skin['cart_list_imgh'])) {
		$ptnOrd->addCart($cart);
	}
	$ptnOrd->complete('sbscr');

	$cart_sum_price_c = $ptnOrd->getData('sum_prd_prc', true);
	$event_sale_prc_c = $ptnOrd->getData('sbscr_sale2', true);
	$time_sale_prc_c = $ptnOrd->getData('sbscr_sale3', true);
	$member_sale_prc_c = $ptnOrd->getData('sbscr_sale4', true);
	$prdprc_sale_c = $ptnOrd->getData('sbscr_sale6', true);
	$prdcpn_sale_c = $ptnOrd->getData('sbscr_sale7', true);
	$sbscr_sale_c = $ptnOrd->getData('sbscr_sale8', true);
	$dlv_prc = $ptnOrd->getData('dlv_prc');
	$total_order_price_c = $ptnOrd->getData('pay_prc', true);
	$cart_sum_milage_c = $ptnOrd->getData('total_milage', true);
	$event_sale_milage_c = $ptnOrd->getData('event_milage', true);

	$_mkt_data_list = array();
	$_partner_data = array();
	while($obj = $ptnOrd->loopCart()) {
		$cart = $obj->data;
		$cart_no = $cart_no ? $cart_no : $total_cart_rows;
		$cart_no_arr[] = $cart['cno'];
		$_mkt_data_list[] = $cart;

		if($cfg['use_partner_delivery'] == 'Y') {
			$cart['partner_name'] = $obj->parent->getPartnerName();
		}

		$cart = parseUserCart($cart, $quickcart);
		$cart_no--;

		$_partner_data[] = $cart['partner_no'];

		// 정기배송 배송정보 가져오기
		$cart = parseUserSbscr($cart);

		$_tmp .= lineValues('cart_sbscr_list', $_line, $cart, '', 1);
	}

	$_tmp = listContentSetting($_tmp, $_line);
	$_replace_code[$_file_name]['cart_sbscr_list'] = $_tmp;

	$_partner_data = implode('|', array_unique($_partner_data));
	$_tmp="<input type=\"hidden\" name=\"cart_rows\" value=\"$cart_rows\"><input type=\"hidden\" id=\"partner_data\" name=\"partner_data\" value=\"$_partner_data\"></form>";
	$_replace_code[$_file_name][sbscr_cart_form_end]=$_tmp;

	$_replace_code[$_file_name][sbscr_cart_order_url]="javascript:orderCart(document.cartSbscrFrm, '', 'Y');";
	$_replace_code[$_file_name][sbscr_cart_delete_url]="javascript:deleteCart(document.cartSbscrFrm);";
	$_replace_code[$_file_name][sbscr_cart_truncate_url]="javascript:truncateCart(document.cartSbscrFrm);";
	$_replace_code[$_file_name][sbscr_cart_sorder_url]="javascript:orderCart(document.cartSbscrFrm,'checked', 'Y');";
	$_replace_code[$_file_name][sbscr_cart_shopping_url]=$rURL;

	$_replace_code[$_file_name][sbscr_cart_list_sum]=$cart_sum_price_c;

	$_replace_code[$_file_name]['sbscr_cart_list_dlvfee'] = parsePrice($dlv_prc, true).$cfg['currency_type'];
	$_replace_code[$_file_name]['sbscr_cart_list_dlvfee2n'] = parsePrice($dlv_prc, true);

	$_replace_code[$_file_name]['sbscr_cart_list_pay'] = $total_order_price_c;
	$_replace_code[$_file_name]['sbscr_cart_list_r_pay'] = showExchangeFee($total_order_price_c);

	$_replace_code[$_file_name]['sbscr_cart_list_event']=$event_sale_prc_c > 0?$event_sale_prc_c:'0';
	$_replace_code[$_file_name]['sbscr_cart_list_timesale'] = $time_sale_prc_c > 0?$time_sale_prc_c:'0';
	$_replace_code[$_file_name]['sbscr_cart_list_msale'] = ($member_sale_prc_c > 0) ? $member_sale_prc_c : '0';
	$_replace_code[$_file_name]['sbscr_cart_list_sbscr_sale']=$sbscr_sale_c > 0?$sbscr_sale_c:'0';
	$_replace_code[$_file_name]['cart_list_milage']=$cart_sum_milage_c > 0?$cart_sum_milage_c:'0';
	$_replace_code[$_file_name]['cart_r_list_milage']=$cart_sum_milage_c > 0?showExchangeFee($cart_sum_milage_c):'0';
	$_replace_code[$_file_name]['cart_list_event_milage']=$event_sale_milage_c > 0?$event_sale_milage_c:'0';
	$_replace_code[$_file_name]['cart_r_list_event_milage']=$event_sale_milage_c > 0?showExchangeFee($event_sale_milage_c):'0';

	// 입점업체 장바구니
	if($cfg['use_partner_shop'] == 'Y' && $cfg['use_partner_delivery'] == 'Y' && defined('__quick_cart__') == false) {
		$_tmp = '';
		$_line = getModuleContent('cart_partner_sbscr_list');
		$_line2 = getModuleContent('cart_partner_sbscr_sub_list');
		$_cart_module_name = 'cart_sbscr_list';

		include_once 'shop_cart_partner.php';

		$_replace_code[$_file_name]['cart_partner_sbscr_list'] = $_tmp;
		$_replace_code[$_file_name]['cart_sbscr_list'] = $_tmp;
	}

	$def_count = $pdo->row("select count(*) from $tbl[cart] where 1 ".mwhere());
	$sub_count = $pdo->row("select count(*) from $tbl[sbscr_cart] where 1 ".mwhere());
	$_replace_code[$_file_name]['sbscr_cart_def_count'] = ($def_count>0) ? $def_count : "0";
	$_replace_code[$_file_name]['sbscr_cart_sub_count'] = ($sub_count>0) ? $sub_count : "0";

	$_replace_code[$_file_name]['sbscr_firsttime_pay_prc'] = parsePrice($ptnOrd->getData('sbscr_firsttime_pay_prc'), true);
	$_replace_code[$_file_name]['sbscr_firsttime_r_pay_prc'] = showExchangeFee($ptnOrd->getData('sbscr_firsttime_pay_prc'));

?>