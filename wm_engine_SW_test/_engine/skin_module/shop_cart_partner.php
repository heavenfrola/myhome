<?PHP

	$file_order = ($_file_name == "shop_order.php") ? "order_" : "";
	$file_sbscr = ($sbscr=='Y') ? "sbscr_" : "";

	$ptnCart = $ptnOrd->getData('ptns');
	if(is_array($ptnCart)) {
		foreach($ptnCart as $ptncart) {
			$_tmp2 = '';
			$_partner_no = $ptncart->getData('partner_no');
			$ptncart_conf = $ptncart->getData('conf');
			$ptncartprd = $ptncart->getData('cartprd');

			if(!is_array($ptncartprd)) continue;

			$partner_cart_cnt = 0;
			foreach($ptncartprd as $obj) { // 입점사 하위 장바구니
				$_cart = $obj->data;
				$_cart = parseUserCart($_cart);

				// 정기배송 배송정보 가져오기
				if($sbscr == 'Y') {
					$_cart = parseUserSbscr($_cart);
				}

                // 세트 메인 상품
				if ($_cart['set_idx'] && $_cart['set_pno'] && $obj->set_order == 1) { // 세트 메인 상품 출력
					$_setprd = prdOneData(shortCut($pdo->assoc("select *, '{$_cart['cno']}' as cno from {$tbl['product']} where no='{$_cart['set_pno']}'")),$_skin['cart_list_imgw'], $_skin['cart_list_imgh'], 3);
					$_setprd['sum_prd_prc_c'] = parsePrice($obj->productSets->get('total_prc'), true);
					$_setprd['discount_prc'] = parsePrice($obj->productSets->get('discount_prc'), true);
					$_setprd['sum_sell_prc_c'] = parsePrice($obj->productSets->get('pay_prc'), true);
					$_setprd['sum_milage'] = parsePrice($obj->productSets->get('milage'), true);
					$_setprd = parseUserCart($_setprd);
					$_setprd['cno'] = $_cart['cno'];
                    $_setprd['is_set_1'] = ($_setprd['prd_type'] == '4') ? 'Y' : '';
                    $_setprd['is_set_2'] = ($_setprd['prd_type'] == '5' || $_setprd['prd_type'] == '6') ? 'Y' : '';
                    $_setprd['buy_ea'] = '';

					$_tmp2 .= lineValues($_cart_module_name, $_line2[5], $_setprd, '', 1);
				}
				$_tmp2 .= lineValues($_cart_module_name, ($_cart['set_idx'] ? $_line2[6] : $_line2[2]), $_cart, '', 1);

				$partner_cart_cnt++;
			}
			$_tmp2 = listContentSetting($_tmp2, $_line2);

			$ptncart_data = array(
				'partner_no' => $_partner_no,
				'partner_name' => $ptncart->getPartnerName(),
				'dlv_free_limit' => parsePrice($ptncart_conf['delivery_free_limit'], true),
				'dlv_r_free_limit' => showExchangeFee($ptncart_conf['delivery_free_limit']),
				'cart_list_sum' => parsePrice($ptncart->getData('sum_prd_prc'), true),
				'cart_r_list_sum' => showExchangeFee($ptncart->getData('sum_prd_prc')),
				'cart_list_dlvfee' => parsePrice($ptncart->getData('dlv_prc'), true).$cfg['currency_type'],
				'cart_r_list_dlvfee' => showExchangeFee($ptncart->getData('dlv_prc')).$cfg['r_currency_type'],
				'cart_list_dlvfee2' => parsePrice($ptncart->getData('dlv_prc'), true).' '.$cfg['currency_type'],
				'cart_list_dlvfee2n' => parsePrice($ptncart->getData('dlv_prc'), true),
				'cart_r_list_dlvfee2n' => showExchangeFee($ptncart->getData('dlv_prc'), true),
				'cart_list_basic_dlvfee' => parsePrice($ptncart->getData('basic_dlv_prc'), true),
				'cart_list_basic_dlvfee2' => parsePrice($ptncart->getData('basic_dlv_prc'), true).$cfg['r_currency_type'],
				'cart_list_prd_dlvfee' => parsePrice($ptncart->getData('prd_dlv_prc'), true),
				'cart_list_prd_dlvfee2' => parsePrice($ptncart->getData('prd_dlv_prc'), true).$cfg['r_currency_type'],
				'cart_list_pay' => parsePrice($ptncart->getData('sum_prd_prc')+$ptncart->getData('dlv_prc')-$ptncart->getData('total_sale'), true),
				'cart_r_list_pay' => showExchangeFee($ptncart->getData('sum_prd_prc')+$ptncart->getData('dlv_prc')-$ptncart->getData('total_sale')),
				'cart_list_milage' => parsePrice($ptncart->getData('total_milage'), true),
				'cart_r_list_milage' => showExchangeFee($ptncart->getData('total_milage'), true),
				'sale2' => parsePrice($ptncart->getData('sale2'), true),
				'r_sale2' => showExchangeFee($ptncart->getData('sale2')),
				'sale3' => parsePrice($ptncart->getData('sale3'), true),
				'sale4' => parsePrice($ptncart->getData('sale4'), true),
				'r_sale4' => showExchangeFee($ptncart->getData('sale4')),
				'sale6' =>parsePrice($ptncart->getData('sale6'), true),
				'r_sale6' =>showExchangeFee($ptncart->getData('sale6')),
				'sale7' =>parsePrice($ptncart->getData('sale7'), true),
				'sale8' =>parsePrice($ptncart->getData('sale8'), true),
				'is_master' => ($_partner_no == 0) ? 'Y' : '',
				'is_partner' => ($_partner_no > 0) ? 'Y' : '',
				'partner_cart_cnt' => number_format($partner_cart_cnt)
			);
			$ptncart_data[$file_order.'cart_partner_'.$file_sbscr.'sub_list'] = lineValues($file_order.'cart_partner_'.$file_sbscr.'list', $_tmp2, $ptncart_data); // 입점사 하위 장바구니

			$_tmp .= lineValues($file_order.'cart_partner_'.$file_sbscr.'list', $_line, $ptncart_data, '', 1);
		}
	}

	$_tmp = listContentSetting($_tmp, $_line);

?>