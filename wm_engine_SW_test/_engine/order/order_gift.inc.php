<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  주문/주문완료 사은품 처리 공통
	' +----------------------------------------------------------------------------------------------+*/

	$destroy_gift = 'N';

	// 사은품 지급기간
	if($cfg['order_gift_term'] == 'Y' && $cfg['order_gift_sdate'] && $cfg['order_gift_edate']) {
		if(!(date("Y-m-d") >= $cfg['order_gift_sdate'] && date('Y-m-d') <= $cfg['order_gift_edate'])) $destroy_gift = 'Y';
	}

	if(addField($tbl['product_gift'], 'order_gift_member', 'char(1)') == true) {
		addField($tbl['product_gift'], 'order_gift_first', 'char(1)');
		$pdo->query("update `{$tbl['product_gift']}` set `order_gift_member`='$cfg[order_gift_member]', `order_gift_first`='$cfg[order_gift_first]'");
	}

	if(empty($ord['order_gift']) == true && $destroy_gift != 'Y') {
		if(!fieldExist($tbl['product_gift'], 'complex_no')) {
			addField($tbl['product_gift'], 'complex_no', "int(10) not null default 0");
			$pdo->query("alter table $tbl[product_gift] add index complex_no (complex_no)");

			addField($tbl['product_gift'], 'sdate', "int(10) not null default 0");
			addField($tbl['product_gift'], 'edate', "int(10) not null default 0");
			$pdo->query("alter table $tbl[product_gift] add index use_date (sdate, edate)");
		}

		// 대상 상품 목록 구성
		if($_file_name != "shop_cart.php") {
			$gift_carts = array();
			if($ord['ono']) { // 주문완료시
				$_sale_fd = getOrderSalesField('o', '-');
				$_tmpres = $pdo->iterator("select p.*, p.no as pno, (o.total_prc-$_sale_fd) as pay_prc from {$tbl['order_product']} o inner join {$tbl['product']} p on o.pno=p.no where o.ono='{$ord['ono']}'");
                foreach ($_tmpres as $_tmpdt) {
					$gift_carts[] = array(
						'pno' => $_tmpdt['pno'],
						'partner_no' => $_tmpdt['partner_no'],
						'cates' => getPrdAllCates($_tmpdt),
						'pay_prc' => $_tmpdt['pay_prc']
					);
				}
			} else { // 주문서 작성시
				while($obj = $ptnOrd->loopCart()) {
					$gift_carts[] = array(
						'pno' => $obj->data['pno'],
						'partner_no' => $obj->data['partner_no'],
						'cates' => getPrdAllCates($obj->data),
						'pay_prc' => $obj->getData('sum_sell_prc')
					);
				}
			}
		}
		// 대상 상품과 사은품 비교
		$use_gifts = array();
		$gift_res = $pdo->query("select * from `{$tbl['product_gift']}` g where `use`='Y'");
        foreach($gift_res as $data) {
			$attach_items = numberOnly(explode('][', $data['attach_items']));
			$gift_price = $gift_prd_cnt = 0;
			foreach($gift_carts as $_cart) {
				// 입점사 조건
				if($cfg['use_partner_shop'] == 'Y') {
					switch($data['partner_type']) {
						case '1' : // 본사
							if($_cart['partner_no'] > 0) continue 2;
							break;
						case '2' : // 입점사
							if($_cart['partner_no'] != $data['partner_no']) continue 2;
							break;
						case '3' : // 본사 및 입점사
							if($_cart['partner_no'] > 0 && $_cart['partner_no'] != $data['partner_no']) continue 2;
							break;
					}
				}
				// 적용 파트너사 조건
				if(empty($data['attach_type'])) $data['attach_type'] = '0';
				switch($data['attach_type']) {
					case '0' : // 전체
						$gift_price += $_cart['pay_prc'];
						$gift_prd_cnt++;
						break;
					case '1' : // 카테고리
						if(in_array2($attach_items, $_cart['cates'])) {
							$gift_price += $_cart['pay_prc'];
							$gift_prd_cnt++;
						}
						break;
					case '2' : // 상품
						if(in_array($_cart['pno'], $attach_items)) {
							$gift_price += $_cart['pay_prc'];
							$gift_prd_cnt++;
						}
						break;
				}
			}

			if($gift_prd_cnt > 0 && $gift_price > 0) {
				if(
					($data['price_limit'] == 0 || $gift_price >= $data['price_limit']) &&
					($data['price_max'] == 0 || $gift_price <= $data['price_max'])
				) {
					$use_gifts[] = $data['no'];
				}
			}
		}
		unset($gift_res, $gift_price, $attach_items, $gift_carts);

		// 실제 선택 가능한 사은품
		$use_gifts = implode(',', array_unique($use_gifts));
		if($_file_name == "shop_cart.php" && $cfg['cart_gift_list'] == "A") {
			$gift_res = $pdo->iterator("
				select g.no, g.name, g.complex_no, g.updir, g.upfile, g.price_limit
				from {$tbl['product_gift']} g
					left join erp_complex_option c using(complex_no)
					left join $tbl[product] p on c.pno=p.no
				where g.`use`='Y'
					and (c.complex_no is null or (p.stat=2 and (c.force_soldout='N' or (c.force_soldout='L' and c.qty > 0))))
					and ((sdate=0 and edate=0) or (sdate<='$now' and edate>='$now'))
				order by price_limit desc, no asc
			");
			$total_gift_res = $gift_res->rowCount();
		} else {
			if($use_gifts) {
				$gift_res = $pdo->iterator("
					select g.no, g.name, g.complex_no, g.updir, g.upfile, g.price_limit, g.order_gift_member, g.order_gift_first
					from `$tbl[product_gift]` g
						left join erp_complex_option c using(complex_no)
						left join $tbl[product] p on c.pno=p.no
					where g.`use`='Y'
						and (c.complex_no is null or (p.stat=2 and (c.force_soldout='N' or (c.force_soldout='L' and c.qty > 0))))
						and ((sdate=0 and edate=0) or (sdate<='$now' and edate>='$now'))
						and g.no in ($use_gifts)
					order by price_limit desc, no asc
				");
				$total_gift_res = $gift_res->rowCount();
			}
		}
	}
	if(isset($total_gift_res) == false) $total_gift_res=0;

?>