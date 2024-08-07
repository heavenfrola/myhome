<?php

    $coupon_no = numberOnly($_POST['coupon_no']);

    if ($cfg['order_cpn_paytype'] != 3) {
        $_tmp = "";
        $_line = getModuleContent('order_cpn_layer_list');

        $cpn['radio'] = "<input type=\"radio\" id=\"no_cpn\" name=\"coupon\" value=\"\" onClick=\"useCpn(this.form,'','');\">";
        $cpn['name'] = __lang_common_info_notUsed__;
        $cpn['code'] = __lang_common_info_notUsed__;
        $_tmp .= lineValues('order_cpn_layer_list', $_line, $cpn);

        unset($cpn);
        // 쿠폰 목록
        while ($cpn = myCouponList(1)) {
            $cpn['sale_prc'] = ($cpn['type'] == '3' || $cpn['stype'] == '4') ? '' : number_format($cpn['sale_prc']) . $cpn['sale_type_k'];
            $cpn['prc_limit'] = number_format($cpn['prc_limit']);
            $cpn['selected'] = ($coupon_no == $cpn['no'] ? "selected" : "");
            $_tmp .= lineValues('order_cpn_layer_list', $_line, $cpn);
        }
        $_tmp = listContentSetting($_tmp, $_line);
        $_replace_code[$_file_name]['order_cpn_layer_list'] = $_tmp;
        unset($mcouponRes);

        // 개별상품쿠폰 존재 여부
        $_tmp = '';
        if ($member['no'] > 0) {
            $nowYmd = date('Y-m-d');
            $_tmp = $pdo->row("
                select count(*)
                    from {$tbl['coupon_download']}
                where
                    member_no='{$member['no']}' and ono='' and stype=5
                    and (udate_type=1 or ustart_date<='$nowYmd' and ufinish_date>='$nowYmd')
            ");
            $total_cpn_cnt += $_tmp;
            $_tmp = ($_tmp > 0) ? 'Y' : '';
        }
        $_replace_code[$_file_name]['has_prdcpn'] = $_tmp;
        unset($_tmp, $nowYmd);
    }


    // 개별상품쿠폰 리스트
    $attach_mode = 2;
	$pay_type = addslashes(trim($_POST['pay_type']));
    $cart_selected = trim($_POST['cart_selected']);

	$prdcpnCart = new OrderCart();
	$prdcpnCart->skip_dlv = 'Y';

	$cpns = array();
	while($cpn = myCouponList(1, null, array('is_prdcpn'=>true))) {
		$cpns[] = $cpn;
	}

    if ($cno > 0) $cart_selected = $cno;
    include_once $engine_dir.'/_engine/include/shop.lib.php';
    $cart_cpn = $cart_cpn_arr = array();
    while ($prd = cartList()) {
        if ($prd['set_pno'] > 0) continue; // 세트 부속은 개별 쿠폰 사용 불가

        foreach ($cpns as $_cpn) {
            if(isCpnAttached($_cpn, $prd) == false) continue;
        }
        $prd['option_name'] = str_replace('<split_big>', '<br>', str_replace('<split_small>', ' : ', $prd['option']));
        $prdcpnCart->addCart($prd);
        if ($prd['prdcpn_no']) {
            $cart_cpn[] = $prd['prdcpn_no'];
            $cart_cpn_arr = array_merge($cart_cpn_arr, explode('@', trim($prd['prdcpn_no'], '@')));
        }
    }
    $prdcpnCart->complete();

    $_tmp = '';
	$_line = getModuleContent("order_prd_cpn_use_list");
	$_line2 = getModuleContent("order_prd_cpn_sel_list");

    while ($obj = $prdcpnCart->loopCart()) {
		$cart = $obj->data;
		$sum_sell_prc = parsePrice($obj->getData('sum_sell_prc'), true);
		$sum_prd_prc = $obj->getData('sum_prd_prc');
		if(!$cart['option_prc']) $cart['option_prc'] = 0;

		$data = array_merge($cart, array(
			'img' => ($cart['upfile3']) ? getFiledir($cart['updir']).'/'.$cart['updir'].'/'.$cart['upfile3'] : $cfg['noimg3'],
			'buy_ea' => $cart['buy_ea'],
			'total_prc' => "<span class='prdcpn_prc_preview_$cart[cno]'>$sum_sell_prc</span>",
			'option_name' => $cart['option_name'],
			'order_prd_cpn_sel_list' => ''
		));

		// 현재 상품에 적용 가능한 쿠폰
		$_checked = explode('@', trim($cart['prdcpn_no'], '@'));
		$_usable_cpn = array();
		foreach ($cpns as $cpn) {
			$cpn = @array_merge(
				$cpn,
				$pdo->assoc("select attachtype, attach_items from $tbl[coupon] where no='$cpn[cno]'")
			);

			if (!$cpn['name']) continue;
			if ($cpn['use_limit'] == 1 && ($obj->getData('sale2') > 0 || $obj->getData('sale4') > 0)) continue;
			if ($cpn['prc_limit'] > 0 && $cpn['prc_limit'] > ($sum_prd_prc/$cart['buy_ea'])) continue;
			if ($cpn['sale_type'] == 'm' && $cpn['sale_prc'] > ($sum_prd_prc/$cart['buy_ea'])) {
				if ($cpn['sale_prc_over'] != 'Y') continue;
			}
			if ($cart['no_cpn'] == 'Y') continue;

			if (isCpnAttached($cpn, $cart) == true) { // 카테고리, 상품별 권한 체크
				$is_reserved = ($attach_mode == 1 && $pdo->row("select count(*) from $tbl[cart] where no='$cpn[cart_no]'") > 0) ? $cpn['cart_no'] : 0;
				$is_checked = (in_array($cpn['no'], $_checked)) ? 'checked' : '';
				if ($cpn['pay_type'] == 2 && $pay_type != 2) $is_checked = ''; // 무통장 전용 쿠폰. 결제방식 체크
                $is_disabled = (in_array($cpn['no'], $cart_cpn_arr) && $is_checked == '') ? 'disabled' : '';
				$cpn = array(
					'no' => $cpn['no'],
					'name' => stripslashes($cpn['name']),
					'use_limit' => $cpn['use_limit'],
					'sale_prc' => number_format($cpn['sale_prc']).(($cpn['sale_type'] == 'm') ? $cfg['currency'] : '%'),
					'ufinish_date' => (($cpn['udate_type'] == 1) ? '' : $cpn['ufinish_date']),
					'duplication' => (($cpn['use_limit'] == 4) ? 'Y' : ''),
					'checkbox' => "
						<input type='hidden' name='cart_no[$cart[cno]]' value='$cart[cno]'>
						<input type='checkbox' $is_checked $is_disabled
							data-cno='$cart[cno]'
							data-pno='$cart[pno]'
							data-cpnno='$cpn[no]'
							data-uselimit='$cpn[use_limit]'
							data-reserved='$is_reserved'
							class='sw_PrdCpn sw_PrdCpn_$cpn[no] sw_PrdCpnCno_$cart[cno] sw_PrdCpnUseLimit_$cpn[use_limit]'
							name='prdCpn[$cart[cno]][]'
							value='$cpn[no]' 
							onclick='return previewPrdCpnPrc(this, $cart[cno], $cart[buy_ea], $cart[option_prc], $attach_mode, \"order\"); '
						>"
				);
				$data['order_prd_cpn_sel_list'] .= lineValues('order_prd_cpn_sel_list', $_line2, $cpn);
			}
		}
		$data['order_prd_cpn_sel_list'] = listContentSetting($data['order_prd_cpn_sel_list'], $_line2);
		unset($GLOBALS['mcouponRes']);

		$_tmp .= lineValues('order_prd_cpn_use_list', $_line, $data);
	}
	$_tmp = listContentSetting($_tmp, $_line);
	$_replace_code[$_file_name]['order_prd_cpn_use_list'] = $_tmp;
	unset($_tmp, $data);

	$_replace_code[$_file_name]['order_prdcpn_form_start'] = "<form method='post' name='prdform' action='/main/exec.php' onsubmit='return setPrdCpnList(this);'><input type='hidden' name='exec_file' value='cart/cart_cpn.exe.php'><input type='hidden' name='attach_mode' value='$attach_mode'>";
	$_replace_code[$_file_name]['order_prdcpn_form_end'] = '</form>';
