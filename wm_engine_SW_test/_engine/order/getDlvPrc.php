<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  지역별 배송비 리턴
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir.'/_engine/include/common.lib.php';
	include_once $engine_dir.'/_engine/include/shop2.lib.php';

	if(!count($_POST)) $_POST = $_GET;

	header('Content-type:application/json; charset='._BASE_CHARSET_);

	$coupon = numberOnly($_POST['coupon']);
	$cpn_auth_code = addslashes($_POST['cpn_auth_code']);
	$off_cpn_use = $_POST['off_cpn_use'];
	$cart_selected = $_POST['cart_selected'];
	$nations = $_POST['nations'];
	$delivery_com = $_POST['delivery_com'];
	$delivery_fee_type =  $_POST['delivery_fee_type'];
	$sbscr = ($_POST['sbscr']=='Y') ? 'Y':'N';

	if($_POST['exec'] == 'delivery') {
		ob_start();
		$ptnOrd = new OrderCart();
        $cpnname = array();
        $ordprd_cnt = $setprd = 0; // 총 상품 / 세트상품 카운트

		if($coupon >0) {
			$cpn = $pdo->assoc("select d.*, c.attachtype,c.attach_items from {$tbl['coupon_download']} as d inner join {$tbl['coupon']} as c on d.cno=c.no where d.member_no='{$member['no']}' and d.`ono`='' and d.`no`='$coupon'");
			$ptnOrd->setCoupon($cpn);
            $cpnname[] = $cpn['name'];
		} else if($cpn_auth_code && $off_cpn_use == 'Y') {
			$offcpn = offCouponAuth(trim($cpn_auth_code));
			$ptnOrd->setCoupon($offcpn);
		}
		if($sbscr=='Y') $sbscr_yn = 'Y';
		while($cart = cartList()) {
			$ptnOrd->addCart($cart);
            if( $cart['prdcpn_no'] ) {
                $cpnname[] = $pdo->row("SELECT name FROM {$tbl['coupon_download']} WHERE no='{$cart['prdcpn_no']}'");
            }
            if (!isset($cart['partner_no'])) $cart['partner_no'] = 0;
            $check = checkDeliveryRange($_POST['addressee_addr1'], (int) $cart['partner_no']);
            if (!$check[0]) {
                $delivery_disable = true;
            }
            if ($cart['set_idx']) $setprd++;
            $ordprd_cnt++;
		}
		$ptnOrd->complete('sbscr');
		$debug = ob_get_contents();
		ob_end_clean();

		$delivery_com_use = "T";
		if($nations && $delivery_com){
			$area_no = $pdo->row("select area_no from ${tbl['os_delivery_country']} where delivery_com='".$delivery_com."' and country_code='".$nations."'");
			if(!$area_no){
				$delivery_com_use = "F";
			}else{
				$posible_weight = $pdo->row("select weight from  ${tbl['os_delivery_prc']} where delivery_com='".$delivery_com."' and area_no='".$area_no."' order by weight desc limit 1");
				if($posible_weight < $ptnOrd->cart_weight){
					$delivery_com_use = "O";
				}
			}
		}

		// 주문시 사은품

		if($cfg['order_gift_timing'] == 'order' && $sbscr != 'Y') {
			$ord['pay_prc'] = $ptnOrd->getData('pay_prc')-$ptnOrd->getData('dlv_prc');
			include_once 'order_gift.inc.php';

			if($total_gift_res > 0) {
				$_skin = getSkinCfg();
				$_file_name = 'shop_order_finish.php';
				include_once $engine_dir.'/_manage/skin_module/shop_order_finish.php';
				include_once $engine_dir.'/_engine/skin_module/shop_order_finish.php';

				$gift_html = $_replace_code[$_file_name]['order_gift_list'];
				$gift_html = contentReset($gift_html, $_file_name);
			}
		}

		$sales_data = array();
		foreach($_order_sales_org as $key => $val) {
			$sales_data[$key] = parsePrice($ptnOrd->getData($key));
		}

		$json = json_encode(array_merge(array(
			'sum_prd_prc' => parsePrice($ptnOrd->getData('sum_prd_prc')),
			'dlv_prc' => parsePrice($ptnOrd->dlv_prc),
			'basic_dlv_prc' => parsePrice($ptnOrd->getData('basic_dlv_prc')),
			'prd_dlv_prc' => parsePrice($ptnOrd->getData('prd_dlv_prc')),
			'add_dlv_prc' => parsePrice($ptnOrd->add_dlv_prc),
			'free_dlv_prc' => parsePrice($ptnOrd->getData('free_dlv_prc')),
			'free_dlv_prc_m' => parsePrice($ptnOrd->sale4_dlv),
			'free_dlv_prc_e' => parsePrice($ptnOrd->sale2_dlv),
            'sbscr_firsttime_pay_prc' => parsePrice($ptnOrd->getData('sbscr_firsttime_pay_prc'), true),
			'sale9' => parsePrice($ptnOrd->getData('sale9')),
			'remain_cpn_prc' => parsePrice($ptnOrd->getData('remain_cpn_prc')),
			'member_milage' => parsePrice($ptnOrd->getData('member_milage')),
			'event_milage' => parsePrice($ptnOrd->getData('event_milage')),
			'total_milage' => parsePrice($ptnOrd->getData('total_milage')),
			'total_sale_prc' => parsePrice($ptnOrd->getData('total_sale')),
			'pay_prc' => parsePrice($ptnOrd->getData('pay_prc')),
			'delivery_com_use'=>$delivery_com_use,
			'oversea_free_dlv_stat'=>$ptnOrd->getData('oversea_free_dlv_stat'),
			'default_delivery_fee'=>$ptnOrd->getData('default_delivery_fee'),
			'tax_prc'=>$ptnOrd->getData('tax_prc'),
			'tax_use_delivery_com'=>$ptnOrd->getData('tax_use_delivery_com'),
			'gift_html' => $gift_html,
			'no_milage' => $ptnOrd->getData('no_milage'),
			'no_cpn' => $ptnOrd->getData('no_cpn'),
            'delivery_range' => !isset($delivery_disable),
            'cpn_name' => ( count($cpnname) > 0 ? implode('|', $cpnname) : "" ),
            'only_setprd' => ($ordprd_cnt == $setprd) ? 1 : 0,
			'debug' => $debug,
		), $sales_data));

		exit($json);
	}

?>