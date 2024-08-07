<?PHP

	use Wing\API\Naver\Checkout;
    use Wing\API\Kakao\KakaoTalkPay;

	/* +----------------------------------------------------------------------------------------------+
	' |  장바구니 리스트
	' +----------------------------------------------------------------------------------------------+*/

	$quickcart = numberOnly($_GET['quickcart']);
	$_tmp="<form name=\"cartFrm$quickcart\" method=\"post\" action=\"$root_url/main/exec.php\" target=\"hidden$now\" style=\"margin:0px\">
			<input type=\"hidden\" name=\"exec_file\" value=\"cart/cart.exe.php\">
			<input type=\"hidden\" name=\"exec\" value=\"\">
			<input type='hidden' name='is_quickcart' value='$quickcart'>
			";

	$_replace_code[$_file_name]['cart_form_start']=$_tmp;

	$cart_list_skin = 'cart_list';
	if($quickcart == 1) $cart_list_skin = 'cart_quick1_list';
	if($quickcart == 2) $cart_list_skin = 'cart_quick2_list';

	$_tmp="";
	$_line=getModuleContent($cart_list_skin);
	if(!$_skin['cart_list_imgw']) $_skin['cart_list_imgw']=50;
	if(!$_skin['cart_list_imgh']) $_skin['cart_list_imgh']=50;

	$cart_no_arr = array();
	$total_npay_rows = $total_talkpay_rows = 0;
	//장바구니 상품 목록:cartList(옵션사이,옵션명과가격사이,옵션앞,옵션뒤)

	//장바구니 상품 목록
	$privates = $pdo->row("select group_concat(no) from $tbl[category] where private='Y'");
	$privates = explode(',', $privates);
	$ptnOrd = new OrderCart();
	while($cart = cartList($_skin['cart_list_btw_opt'], $_skin['cart_list_opt_prc'], $_skin['cart_list_opt_f'], $_skin['cart_list_opt_b'], $_skin['cart_list_imgw'] , $_skin['cart_list_imgh'])) {
		if(getPrdBuyLevel($cart) != null) {
			$pdo->query("delete from {$tbl['cart']} where no='{$cart['cno']}'");
			continue;
		}
		$ptnOrd->addCart($cart);

		if($cart['checkout'] == 'Y' && $cart['sum_sell_prc'] > 0 && in_array2(array($cart['big'],$cart['mid'],$cart['small']), $privates) == false && !$cart['qty_rate']) {
			$total_npay_rows++;
		}
        if ($cart['use_talkpay'] == 'Y') {
            $total_talkpay_rows++;
        }
	}
	$ptnOrd->complete();

	$cart_sum_price_c = $ptnOrd->getData('sum_prd_prc', true);
	$set_sale_prc_c = $ptnOrd->getData('sale1', true);
	$event_sale_prc_c = $ptnOrd->getData('sale2', true);
	$time_sale_prc_c = $ptnOrd->getData('sale3', true);
	$member_sale_prc_c = $ptnOrd->getData('sale4', true);
	$prdprc_sale_c = $ptnOrd->getData('sale6', true);
	$prdcpn_sale_c = $ptnOrd->getData('sale7', true);
	$dlv_prc = $ptnOrd->getData('dlv_prc');
	$total_order_price_c = $ptnOrd->getData('pay_prc', true);
	$cart_sum_milage_c = $ptnOrd->getData('total_milage', true);
    $cart_total_prc = parsePrice($cart_sum_price_c);

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

        // 세트 메인 상품 출력
		if($cart['set_idx'] && $cart['set_pno'] && $obj->set_order == 1) {
			$_setprd = prdOneData(shortCut($pdo->assoc("select *, '{$cart['cno']}' as cno from {$tbl['product']} where no='{$cart['set_pno']}'")),$_skin['cart_list_imgw'], $_skin['cart_list_imgh'], 3);
			$_setprd['sum_prd_prc_c'] = parsePrice($obj->productSets->get('total_prc'), true);
			$_setprd['discount_prc'] = parsePrice($obj->productSets->get('discount_prc'), true);
			$_setprd['sum_sell_prc_c'] = parsePrice($obj->productSets->get('pay_prc'), true);
			$_setprd['sum_milage'] = parsePrice($obj->productSets->get('milage'), true);
			$_setprd = parseUserCart($_setprd);
			$_setprd['cno'] = $cart['cno'];
            $_setprd['is_set_1'] = ($_setprd['prd_type'] == '4') ? 'Y' : '';
            $_setprd['is_set_2'] = ($_setprd['prd_type'] == '5' || $_setprd['prd_type'] == '6') ? 'Y' : '';
            $_setprd['buy_ea'] = '';

			$_tmp .= lineValues('cart_list', $_line[5], $_setprd, '', 1);
		}

		$_tmp .= lineValues('cart_list', ($cart['set_idx'] ? $_line[6] : $_line[2]), $cart, '', 1);

		if($cfg['cart_gift_list'] == "Y" || $cfg['cart_gift_list'] == "A") {
			$gift_carts[] = array(
				'pno' => $obj->data['pno'],
				'partner_no' => $obj->data['partner_no'],
				'cates' => getPrdAllCates($obj->data),
				'pay_prc' => $obj->getData('sum_sell_prc')
			);
		}
	}

	$_tmp = listContentSetting($_tmp, $_line);
	$_replace_code[$_file_name]['cart_list'] = $_tmp;

	$_partner_data = implode('|', array_unique($_partner_data));
	$_tmp="<input type=\"hidden\" name=\"cart_rows\" value=\"$cart_rows\"><input type=\"hidden\" id=\"partner_data\" name=\"partner_data\" value=\"$_partner_data\"></form>";
	$_replace_code[$_file_name]['cart_form_end']=$_tmp;

	if(is_array($gift_carts) == true && checkCodeUsed('사은품리스트') == true) {

		include_once $engine_dir."/_engine/order/order_gift.inc.php";

		$order_gift_cnt = "select count(*) from {$tbl['order']} where member_no='{$member['no']}' and ono!='{$ord['ono']}' and stat > 6";

		$_tmp2="";
		$_line2=getModuleContent("cart_gift_list");
		while($gift=ordGift()){
			if($gift['order_gift_member'] == "Y" && !$member['no']) continue;
			if($gift['order_gift_first'] == 'Y' && $member['no'] > 0) {
				if(0 < $order_gift_cnt) continue;
			}
			$_tmp2 .= lineValues("cart_gift_list", $_line2, $gift);
		}
		$_tmp2=listContentSetting($_tmp2, $_line2);
		$_replace_code[$_file_name]['cart_gift_list']=$_tmp2;
	}

	$_replace_code[$_file_name]['cart_order_url']="javascript:orderCart(document.cartFrm$quickcart);";
	$_replace_code[$_file_name]['cart_update_url']="javascript:updateCart(document.cartFrm$quickcart);";
	$_replace_code[$_file_name]['cart_delete_url']="javascript:deleteCart(document.cartFrm$quickcart);";
	$_replace_code[$_file_name]['cart_truncate_url']="javascript:truncateCart(document.cartFrm$quickcart);";
	$_replace_code[$_file_name]['cart_wish_url']="javascript:cartToWish(document.cartFrm$quickcart);"; //2011-04-11 위시리스트 담기 Jung
	$_replace_code[$_file_name]['cart_sorder_url']="javascript:orderCart(document.cartFrm$quickcart,'checked');";
	$_replace_code[$_file_name]['cart_shopping_url']=$rURL;
	$_replace_code[$_file_name]['cart_receipt_url']="javascript:printReceipt(1, '$ono');";

	$_replace_code[$_file_name]['cart_list_sum']=$cart_sum_price_c;
	$_replace_code[$_file_name]['cart_r_list_sum']=showExchangeFee($cart_sum_price_c);
	$_replace_code[$_file_name]['cart_list_set'] = $set_sale_prc_c;
	$_replace_code[$_file_name]['cart_list_event']=$event_sale_prc_c > 0?$event_sale_prc_c:'0';
	$_replace_code[$_file_name]['cart_r_list_event']=$event_sale_prc_c > 0?showExchangeFee($event_sale_prc_c):'0';

	$_replace_code[$_file_name]['cart_list_timesale'] = $time_sale_prc_c > 0?$time_sale_prc_c:'0';
	$_replace_code[$_file_name]['cart_r_list_timesale'] = $time_sale_prc_c > 0?showExchangeFee($time_sale_prc_c):'0';
	$_replace_code[$_file_name]['cart_list_msale'] = ($member_sale_prc_c > 0) ? $member_sale_prc_c : '0';
	$_replace_code[$_file_name]['cart_r_list_msale'] = ($member_sale_prc_c > 0) ? showExchangeFee($member_sale_prc_c) : '0';
	$_replace_code[$_file_name]['cart_list_prdprc']=$prdprc_sale_c > 0?$prdprc_sale_c:'0';
	$_replace_code[$_file_name]['cart_list_prdcpn_prc'] = ($prdcpn_sale_c > 0) ? $prdcpn_sale_c : 0;
	$_replace_code[$_file_name]['cart_list_sale9'] = $ptnOrd->getData('sale9', true);
	$_replace_code[$_file_name]['cart_r_list_prdprc']=$prdprc_sale_c > 0?showExchangeFee($prdprc_sale_c):'0';

    $_replace_code[$_file_name]['cart_list_event_prd'] = parsePrice((int) $ptnOrd->getData('sale2') - (int) $ptnOrd->getData('sale2_dlv'));
    $_replace_code[$_file_name]['cart_list_event_dlv'] = $ptnOrd->getData('sale2_dlv', true);
    $_replace_code[$_file_name]['cart_list_msale_prd'] = parsePrice((int) $ptnOrd->getData('sale4') - (int) $ptnOrd->getData('sale4_dlv'));
    $_replace_code[$_file_name]['cart_list_msale_dlv'] = $ptnOrd->getData('sale4_dlv', true);

	$_replace_code[$_file_name]['cart_list_dlvfee'] = $_replace_code[$_file_name]['cart_list_dlvfee2'] = parsePrice($dlv_prc, true).' '.$cfg['currency_type'];
	$_replace_code[$_file_name]['cart_list_dlvfee2n'] = parsePrice($dlv_prc, true);
	$_replace_code[$_file_name]['cart_r_list_dlvfee'] = $_replace_code[$_file_name]['cart_r_list_dlvfee2'] = deliveryStr()>0?showExchangeFee(deliveryStr()):'';
	$_replace_code[$_file_name]['cart_r_list_dlvfee2n'] = showExchangeFee($dlv_prc);
	$_replace_code[$_file_name]['cart_cod_prc'] = parsePrice($ptnOrd->getData('cod_prc'), true);

	$_replace_code[$_file_name]['cart_list_basic_dlvfee'] = parsePrice($ptnOrd->getData('basic_dlv_prc'), true);
	$_replace_code[$_file_name]['cart_list_basic_dlvfee2'] = parsePrice($ptnOrd->getData('basic_dlv_prc'), true).$cfg['r_currency_type'];
	$_replace_code[$_file_name]['cart_list_prd_dlvfee'] = parsePrice($ptnOrd->getData('prd_dlv_prc'), true);
	$_replace_code[$_file_name]['cart_list_prd_dlvfee2'] = parsePrice($ptnOrd->getData('prd_dlv_prc'), true).$cfg['r_currency_type'];

	$_replace_code[$_file_name]['cart_list_pay']=$total_order_price_c;
	$_replace_code[$_file_name]['cart_r_list_pay']=showExchangeFee($total_order_price_c);
	$_replace_code[$_file_name]['cart_list_milage']=$cart_sum_milage_c > 0?$cart_sum_milage_c:'0';
	$_replace_code[$_file_name]['cart_r_list_milage']=$cart_sum_milage_c > 0?showExchangeFee($cart_sum_milage_c):'0';
	$_replace_code[$_file_name]['cart_list_event_milage']=$event_sale_milage_c > 0?$event_sale_milage_c:'0';
	$_replace_code[$_file_name]['cart_r_list_event_milage']=$event_sale_milage_c > 0?showExchangeFee($event_sale_milage_c):'0';
	$_replace_code[$_file_name]['cart_list_point'] = ($cfg['point_buy1'] && $cfg['point_buy2']) ? number_format(floor($cart_sum_price / $cfg['point_buy1']) * $cfg['point_buy2']) : "";

	// 입점업체 장바구니
	if($cfg['use_partner_shop'] == 'Y' && $cfg['use_partner_delivery'] == 'Y' && defined('__quick_cart__') == false) {
		$_tmp = '';
		$_line = getModuleContent('cart_partner_list');
		$_line2 = getModuleContent('cart_partner_sub_list');
		$_cart_module_name = 'cart_list';

		include_once 'shop_cart_partner.php';

		$_replace_code[$_file_name]['cart_partner_list'] = $_tmp;
		$_replace_code[$_file_name]['cart_list'] = $_tmp;
	}

	// 2010-04-22 : Naver Checkout 버튼 연동
	if($cfg['checkout_id'] && $cfg['checkout_key'] && ($cfg['checkout_auth'] == 'Y' || $member['no'] == 0)) {
		list($btn_type, $btn_color) = explode('_', $cfg['checkout_cart_btn']);
		$checkout_btn_enable = $total_npay_rows > 0 ? 'Y' : 'N';

		$is_mobile = ($mobile_browser == 'mobile' || $_SESSION['browser_type'] == 'mobile') ? 'mobile' : 'pc';

		$checkout_button = 'naverPayButton.js';
		if($is_mobile == 'mobile') {
			$checkout_button = 'mobile/naverPayButton.js';
			if(!$cfg['m_checkout_detail_btn']) $cfg['m_checkout_detail_btn'] = 'MA_1';
			list($btn_type, $btn_color) = explode('_', $cfg['m_checkout_cart_btn']);
		}

		$checkout = new Checkout();

		$_replace_code[$_file_name]['cart_checkout_url'] = "
		<div id='naver_checkout_buttons'>
			<script type='text/javascript' src='//{$checkout->testplug}pay.naver.com/customer/js/$checkout_button' charset='UTF-8'></script>
			<script type='text/javascript'>
				var is_mobile = '$is_mobile';
				var npay_target = '$cfg[npay_target]';
				naver.NaverPayButton.enable = '$checkout_btn_enable';
				naver.NaverPayButton.apply(
					{
						BUTTON_KEY: '$cfg[checkout_btn_key]',
						TYPE : '$btn_type',
						COLOR : $btn_color,
						COUNT : 1,
						ENABLE : '$checkout_btn_enable',
						BUY_BUTTON_HANDLER : order_nc,
						'':''
					}
				);
			</script>
		</div>
		";
	}

    // 카카오페이 구매
    if ($scfg->comp('use_talkpay') == true && $scfg->comp('talkpay_authkey') == true) {
        $talkpay = new KakaoTalkPay($scfg);
        $_replace_code[$_file_name]['cart_talkpay_url'] = $talkpay->printButton($total_talkpay_rows);
    }

	// payco
	if($cfg['use_payco'] == 'Y' && $cfg['payco_sellerKey'] && $cart_total_prc > 0) {
		if($_SESSION['browser_type'] == 'mobile') {
			$_payco_btn_url = 'https://static-bill.nhnent.com/payco/checkout/js/payco_mobile.js';
			$_payco_btn_type = $cfg['payco_type4_sel'];
		} else {
			$_payco_btn_url = 'https://static-bill.nhnent.com/payco/checkout/js/payco.js';
			$_payco_btn_type = $cfg['payco_type2_sel'];
		}
		$_replace_code[$_file_name]['cart_payco_url'] = "
		<div id='payco_detail_btn'></div>
		<script type='text/javascript' src='$_payco_btn_url'></script>
		<script type='text/javascript'>
		Payco.Button.register({
			SELLER_KEY : '$cfg[payco_sellerKey]',
			ORDER_METHOD : 'CHECKOUT',
			BUTTON_TYPE : '$_payco_btn_type',
			BUTTON_HANDLER : order_payco,
			BUTTON_HANDLER_ARG : ['param1', 'param2'],
			DISPLAY_PROMOTION : 'Y',
			DISPLAY_ELEMENT_ID : 'payco_detail_btn',
			\"\" : \"\"
		});
		</script>
		";
	}

	// 주문 쿠폰 사용불가시 쿠폰 리스트 로딩 안함
	if($cfg['order_cpn_paytype'] != 3){
		// 개별상품쿠폰 존재 여부
		$_tmp = '';
		if($member['no'] > 0) {
			$nowYmd = date('Y-m-d');
			$_tmp = $pdo->row("
				select count(*)
					from $tbl[coupon_download]
				where
					member_no='$member[no]' and ono='' and stype=5
					and (udate_type=1 or ustart_date<='$nowYmd' and ufinish_date>='$nowYmd')
			");
			$_tmp = ($_tmp > 0) ? 'Y' : '';
		}
		$_replace_code[$_file_name]['has_prdcpn'] = $_tmp;
		unset($_tmp, $nowYmd);
	}

	$def_count = $pdo->row("select count(*) from $tbl[cart] where 1 ".mwhere());
    if ($scfg->comp('use_set_product', 'Y') == true) { // 세트 사용 시 세트당 장바구니 1개로 표현
        $def_count -= $pdo->row("select count(*)-count(distinct set_idx) from {$tbl['cart']} where set_idx!='' ".mwhere());
    }

	$_replace_code[$_file_name]['cart_def_count'] = ($def_count>0) ? $def_count : "0";

	if($cfg['use_sbscr']=='Y') {
		$sub_count = $pdo->row("select count(*) from $tbl[sbscr_cart] where 1 ".mwhere());
		$_replace_code[$_file_name]['cart_sub_count'] = ($sub_count>0) ? $sub_count : "0";
	}
	if(!$cfg['cart_delete_term']) $cfg['cart_delete_term'] = "7";

	if($cfg['cart_delete_term'] == "N") {
		$_replace_code[$_file_name]['cart_member_delete'] = __lang_shop_cart_end8__;
	} else {
		$_replace_code[$_file_name]['cart_member_delete'] = constant('__lang_shop_cart_end'.$cfg['cart_delete_term'].'__');
	}

?>