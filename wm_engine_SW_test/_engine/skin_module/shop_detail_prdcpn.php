<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  개별상품 쿠폰 선택 레이어
	' +----------------------------------------------------------------------------------------------+*/

	header('Content-type:application/json; charset='._BASE_CHARSET_);
	define('_LOAD_AJAX_PAGE_', true);

	$attach_mode = $_GET['attach_mode'];
	$pay_type = $_GET['pay_type'];

	$prdcpnCart = new OrderCart();
	$prdcpnCart->skip_dlv = 'Y';

	$cpns = array();
	while($cpn = myCouponList(1, null, array('is_prdcpn'=>true))) {
		$cpns[] = $cpn;
	}

	if($attach_mode == 1) { // 상품 상세의 상품을 가상 장바구니 형태로 출력
		$pno = addslashes($_GET['pno']);
		$prd = shortCut($pdo->assoc("select * from $tbl[product] where hash='$pno'"));
		if(!$prd['no']) return;
		$prd['pno'] = $prd['no'];
		$prd['name'] = stripslashes($prd['name']);
		$prd['sell_prc'] = $prd['sell_prc'];

		if(count($_GET['multi_option_pno']) > 0) { // 다중옵션 사용시
			foreach($_GET['multi_option_pno'] as $idx => $_pno) {
				$_tmp = ($_pno != $pno) ? $pdo->assoc("select * from {$tbl['product']} where hash='$_pno'") : $prd;
				$_tmp['cno'] = $idx;
				$_tmp['buy_ea'] = numberOnly($_GET['m_buy_ea'][$idx]);
				$_tmp['prdcpn_no'] = addslashes($_GET['multi_option_prdcpn_no'][$idx]);
				$_tmp['option_prc'] = 0;
				$_tmp['prdcpn_no_array'] = explode('@', trim($_tmp['prdcpn_no'], '@'));

				$opt = explode('<split_option>', $_GET['multi_option_vals'][$idx]);
				foreach($opt as $key => $_opt) {
					$type = $_GET['option_type'.($key+1)];
					if($type == '4') {
						$_text = getTextOptionPrc($_GET['txt_option_set_no'][($key+1)], $_opt);
						$_opt = $_opt.'::'.$_text['price'];
					}
					$_opt = explode('::', $_opt);
					if($_tmp['option_name']) $_tmp['option_name'] .= ' / ';
					$_tmp['option_name'] .= $_opt[0];
					$_tmp['sell_prc'] += numberOnly($_opt[1], true);
					$_tmp['option_prc'] += numberOnly($_opt[1], true);
				}
				$prdcpnCart->addCart($_tmp);
			}
		} else { // 옵션 선택 전 또는 다중옵션 미사용 시
			$prd['cno'] = 0;
			$prd['buy_ea'] = numberOnly($_GET['buy_ea']);
			$prd['prdcpn_no'] = $_GET['prdcpn_no'];
			$prd['option_prc'] = 0;

			$prdcpnCart->addCart($prd);
		}
	} else { // 장바구니, 주문서
		$cno = numberOnly($_GET['cno']);
		if($cno > 0) $cart_selected = $cno;
		include_once $engine_dir.'/_engine/include/shop.lib.php';
		while($prd = cartList()) {
            if ($prd['set_pno'] > 0) continue; // 세트 부속은 개별 쿠폰 사용 불가

			foreach($cpns as $_cpn) {
				if(isCpnAttached($_cpn, $prd) == false) continue;
			}
			$prd['option_name'] = str_replace('<split_big>', '<br>', str_replace('<split_small>', ' : ', $prd['option']));
			$prdcpnCart->addCart($prd);
		}
	}
	$prdcpnCart->complete();

	// 스킨
	$_tmp = '';
	$_line = getModuleContent("detail_cpn_use_list");
	$_line2 = getModuleContent("detail_cpn_sel_list");
	while($obj = $prdcpnCart->loopCart()) {
		$cart = $obj->data;
		$sum_sell_prc = parsePrice($obj->getData('sum_sell_prc'), true);
		$sum_prd_prc = $obj->getData('sum_prd_prc');
		if(!$cart['option_prc']) $cart['option_prc'] = 0;

		$data = array_merge($cart, array(
			'img' => ($cart['upfile3']) ? getFiledir($cart['updir']).'/'.$cart['updir'].'/'.$cart['upfile3'] : $cfg['noimg3'],
			'buy_ea' => $cart['buy_ea'],
			'total_prc' => "<span class='prdcpn_prc_preview_$cart[cno]'>$sum_sell_prc</span>",
			'option_name' => $cart['option_name'],
			'detail_cpn_sel_list' => ''
		));

		// 현재 상품에 적용 가능한 쿠폰
		$_checked = explode('@', trim($cart['prdcpn_no'], '@'));
		$_usable_cpn = array();
		foreach($cpns as $cpn) {
			$cpn = @array_merge(
				$cpn,
				$pdo->assoc("select attachtype, attach_items from $tbl[coupon] where no='$cpn[cno]'")
			);

			if(!$cpn['name']) continue;
			if($cpn['use_limit'] == 1 && ($obj->getData('sale2') > 0 || $obj->getData('sale4') > 0)) continue;
			if($cpn['prc_limit'] > 0 && $cpn['prc_limit'] > ($sum_prd_prc/$cart['buy_ea'])) continue;
			if($cpn['sale_type'] == 'm' && $cpn['sale_prc'] > ($sum_prd_prc/$cart['buy_ea'])) {
				if($cpn['sale_prc_over'] != 'Y') continue;
			}
			if($cart['no_cpn'] == 'Y') continue;

			if(isCpnAttached($cpn, $cart) == true) { // 카테고리, 상품별 권한 체크
				$is_reserved = ($attach_mode == 1 && $pdo->row("select count(*) from $tbl[cart] where no='$cpn[cart_no]'") > 0) ? $cpn['cart_no'] : 0;
				$is_checked = (in_array($cpn['no'], $_checked)) ? 'checked' : '';
				if($cpn['pay_type'] == 2 && $pay_type != 2) $is_checked = ''; // 무통장 전용 쿠폰. 결제방식 체크
				$cpn = array(
					'no' => $cpn['no'],
					'name' => stripslashes($cpn['name']),
					'use_limit' => $cpn['use_limit'],
					'sale_prc' => number_format($cpn['sale_prc']).(($cpn['sale_type'] == 'm') ? $cfg['currency'] : '%'),
					'ufinish_date' => (($cpn['udate_type'] == 1) ? '' : $cpn['ufinish_date']),
					'duplication' => (($cpn['use_limit'] == 4) ? 'Y' : ''),
					'checkbox' => "
						<input type='hidden' name='cart_no[$cart[cno]]' value='$cart[cno]'>
						<input type='checkbox' $is_checked
							data-cno='$cart[cno]'
							data-pno='$cart[pno]'
							data-cpnno='$cpn[no]'
							data-uselimit='$cpn[use_limit]'
							data-reserved='$is_reserved'
							class='sw_PrdCpn sw_PrdCpn_$cpn[no] sw_PrdCpnCno_$cart[cno] sw_PrdCpnUseLimit_$cpn[use_limit]'
							name='prdCpn[$cart[cno]][]'
							value='$cpn[no]'
							onclick='return previewPrdCpnPrc(this, $cart[cno], $cart[buy_ea], $cart[option_prc], $attach_mode)'
						>"
				);
				$data['detail_cpn_sel_list'] .= lineValues('detail_cpn_sel_list', $_line2, $cpn);
			}
		}
		$data['detail_cpn_sel_list'] = listContentSetting($data['detail_cpn_sel_list'], $_line2);
		unset($GLOBALS['mcouponRes']);

		$_tmp .= lineValues('detail_cpn_use_list', $_line, $data);
	}
	$_tmp = listContentSetting($_tmp, $_line);
	$_replace_code[$_file_name]['detail_cpn_use_list'] = $_tmp;
	unset($_tmp, $data);

	$_replace_code[$_file_name]['prdcpn_form_start'] = "<form method='post' action='/main/exec.php' onsubmit='return setPrdCpnList(this);'><input type='hidden' name='exec_file' value='cart/cart_cpn.exe.php'><input type='hidden' name='attach_mode' value='$attach_mode'>";
	$_replace_code[$_file_name]['prdcpn_form_end'] = '</form>';

?>