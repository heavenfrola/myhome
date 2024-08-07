<?PHP

	include_once $engine_dir.'/_engine/include/cart.class.php';

	if(count($_POST['data']) < 1) return false;

	// 적립금 계산용 회원정보 읽기
	unset($member);

	$ono = addslashes($_POST['ono']);
	$mid = addslashes($_POST['mid']);
	if($ono) {
		$member = $pdo->assoc("select m.*, o.addressee_addr1 from {$tbl['order']} o inner join {$tbl['member']} m on o.member_no=m.no where o.ono='$ono'");
		$_POST['addressee_addr1'] = $member['addressee_addr1'];
	}
	if($mid) {
		$member = $pdo->assoc("select m.* from {$tbl['member']} m where m.member_id='$mid'");
	}
    if ($member['no'] > 0) {
        $member = getMemberAttr($member);
    }

	// 장바구니 생성
	$cart = new OrderCart();
	foreach($_POST['data'] as $key => $val) {
		if(is_array($val) == false) continue;

		$prd = $pdo->assoc("select * from {$tbl['product']} where no='{$val['pno']}'");
		foreach($_order_sales as $_key => $_nm) {
            if (isset($val[$_key]) == false) continue;
			if($_key == 'sale9') continue;
			$prd[$_key] = $val[$_key];
		}
		$cart->addCart(array_merge($prd, array(
			'cno' => $key,
			'pno' => $val['pno'],
			'sell_prc' => $val['sell_prc'],
			'buy_ea' => $val['buy_ea'],
		)));
	}
	$cart->complete();

	// 결과 출력
	$result = array(
		'products' => array(),
		'pay_prc' => $cart->getData('pay_prc')
	);
	foreach($_order_sales as $key => $val) {
		$result[$key] = $cart->getData($key);
	}
	while($obj = $cart->loopCart()) {
		$_total_milage = parsePrice($obj->getData('total_milage'))-parsePrice($obj->getData('member_milage'));

		$result['products'][$obj->data['cno']] = array(
			'sum_sell_prc' => parsePrice($obj->getData('sum_sell_prc')),
			'discount_prc' => parsePrice($obj->getData('discount_prc')),
			'prd_dlv_prc' => parsePrice($obj->getData('prd_dlv_prc')),
			'total_milage' => parsePrice($_total_milage),
			'member_milage' => parsePrice($obj->getData('member_milage'))
		);
		foreach($_order_sales as $key => $val) {
			$result['products'][$obj->data['cno']][$key] = $obj->getData($key);
		}
	}

	header('Content-type:application/json;');
	exit(json_encode($result));

?>