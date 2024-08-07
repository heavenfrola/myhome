<?PHP

	include_once $engine_dir.'/_engine/include/common.lib.php';
	include_once $engine_dir.'/_engine/include/shop.lib.php';

	$cart_selected = addslashes($_POST['cno']);
	if(!$cart_selected) exit(json_encode(array('total'=>0)));

	$sbscr_yn = $_POST['sbscr'];
    $sbscr = ($sbscr_yn == 'Y') ? 'sbscr' : '';

	$total_milage = $member_milage = 0;
	$ptnOrd = new OrderCart();
	while($cart=cartList("","","","","","",$cart_where)) {
		$ptnOrd->addCart($cart);
	}
	$ptnOrd->complete('sbscr');

	$cart_sum_price_c = $ptnOrd->getData('sum_prd_prc', true);
	$dlv_prc = $ptnOrd->getData('dlv_prc');
	$total_order_price_c = $ptnOrd->getData('pay_prc', true);
	$cart_sum_milage_c = $ptnOrd->getData('total_milage', true);

	// 입점사 장바구니
	$ptnCart = $ptnOrd->getData('ptns');
	if(is_array($ptnCart)) {
		foreach($ptnCart as $ptncart) {
			$_partner_no = $ptncart->getData('partner_no');
            $_pay_prc = $ptncart->getData('sum_prd_prc')+$ptncart->getData('dlv_prc');
            foreach ($_order_sales as $key => $val) {
                $_pay_prc -= $ptncart->getData($key);
            }

			$ptncart_data[$_partner_no] = array(
				'partner_no' => $_partner_no,
				'prd_prc' => parsePrice($ptncart->getData('sum_prd_prc'), true),
				'dlv_prc' => parsePrice($ptncart->getData('dlv_prc'), true),
				'dlv_prc_basic' => parsePrice($ptncart->getData('basic_dlv_prc'), true),
				'dlv_prc_prd' => parsePrice($ptncart->getData('prd_dlv_prc'), true),
				'pay_prc' => parsePrice($_pay_prc, true),
                'pay_prc_r' => showExchangeFee($_pay_prc),
				'milage_prc' => parsePrice($ptncart->getData('total_milage'), true),
			);
			foreach($_order_sales_org as $nm => $val) {
				$ptncart_data[$_partner_no][$nm.'_prc'] = parsePrice($ptncart->getData($nm), true);
			}
		}
	}

	// 전체 장바구니
	$result = array(
		'total_prd_prc' => $cart_sum_price_c,
		'total_pay_prc' => $total_order_price_c,
        'total_pay_prc_r' => showExchangeFee($ptnOrd->getData('pay_prc')),
		'total_dlv_prc' => $dlv_prc,
		'total_dlv_prc_basic' => $ptnOrd->getData('basic_dlv_prc', true),
		'total_dlv_prc_prd' => $ptnOrd->getData('prd_dlv_prc', true),
		'total_total_milage' => $cart_sum_milage_c,
		'ptndata' => $ptncart_data
	);
	foreach($_order_sales_org as $nm => $val) {
		$result['total_'.$nm.'_prc'] = $ptnOrd->getData($nm, true);
	}

	echo json_encode($result);
	exit;

?>