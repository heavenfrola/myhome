<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  쿠폰 할인금액 계산
	' +----------------------------------------------------------------------------------------------+*/

	if(!$real_order_mode) {
		include_once $engine_dir.'/_engine/include/common.lib.php';

		printAjaxHeader();

		// 쿠폰 정보
		$cpn_no = numberOnly($_GET['cpn_no']);
		$event_sale_prc = numberOnly($_GET['event_sale_prc']);
		$msale_prc = numberOnly($_GET['msale_prc']);
		$pay_type = numberOnly($_GET['pay_type']);
        $cart_selected = $_GET['cart_selected'];

		if($event_sale_prc > 0) {
			if($cfg['event_ptype']=='2' && $pay_type !=2) {
				$event_sale_prc = 0;
			}
		}
	}
	if($cpn_no){
		$cpn = $pdo->assoc("select a.attachtype, a.attach_items, b.* from $tbl[coupon] a inner join $tbl[coupon_download] b on b.cno=a.no where b.no='$cpn_no'");
	}else{
		$cpn = $pdo->assoc("select * from $tbl[coupon] where no='".$offcpn['no']."'");
		$cpn_no = $offcpn['no'];
	}
	$cpn_use_limit = $cpn['use_limit'];
	$cpn_ex_tot = $cpn_ex_remain = $cpn['sale_prc'];
	$cpn_ex_prd = 0;
	if($cpn['stype'] == 2) $cpn['stype'] = 1;

	// 요일 전용 쿠폰 체크
	if(strlen($cpn['weeks']) > 0) {
		$_weeks = array(1 => __lang_common_week_mon__, 2 => __lang_common_week_tue__, 3 => __lang_common_week_wed__, 4 => __lang_common_week_thu__, 5 => __lang_common_week_fri__, 6 => __lang_common_week_sat__, 0 => __lang_common_week_sun__);
		$weeks = explode('@', $cpn['weeks']);

		if(!in_array(date('w'), $weeks)) {
			$cpnerr = sprintf(__lang_cpn_error_week__, $cpn['weeks']);
			foreach($weeks as $val) {
				$cpnerr = str_replace($val, $_weeks[$val], $cpnerr);
			}
			$cpnerr = str_replace('@', ', ', $cpnerr);
			$cpn_no = 0;
		}
	}

	// 장바구니 비교
	$mwhere = mwhere('a.');
	$cart_selected = preg_replace('/^[^0-9]+|[^0-9]+$/', '', $cart_selected);
	if($cart_selected) $selected_where = " and a.`no` in ($cart_selected)";

	$cpn_prc = 0;
	$cpn_cno = $sale_cno_cpn = array();

	$addcpnf = '';
	if($cfg['use_partner_shop'] == 'Y') {
		$addcpnf .= ", b.partner_no";
	}
	if($cfg['max_cate_depth'] >= 4) {
		$addcpnf .= ", b.depth4, b.xdepth4, b.ydepth4";
	}

	$res = $pdo->iterator("select a.no, a.pno, a.buy_ea, a.option_prc, b.name, b.sell_prc, b.big, b.mid, b.small, b.xbig, b.xmid, b.xsmall, b.ybig, b.ymid, b.ysmall, b.ebig, b.event_sale, b.member_sale $addcpnf from $tbl[cart] a inner join $tbl[product] b on a.pno=b.no where 1 $mwhere $selected_where $w");
    foreach ($res as $cart) {
		if(!$cpn_no) continue;

		if($cpn_use_limit == 1) { // 할인 된 상품은 쿠폰할인 하지 않음
			if($msale_prc > 0 && $cart['member_sale'] == 'Y') continue;
			if($event_sale_prc > 0 && $cart['event_sale'] == 'Y') continue;
		}

		if($cpn_use_limit == 2) { // 할인 된 상품이 하나라도 있을 경우 쿠폰할인 하지 않음
			if($msale_prc > 0 || $event_sale_prc > 0) continue;
		}

		if(isCpnAttached($cpn, $cart) == false) continue; // 상품별/카테고리별 쿠폰 체크

		$cart['option_pr'] = explode('<split_big>', $cart['option_prc']);
		foreach($cart['option_pr'] as $val) {
			$tmp = explode('<split_small>', $val);
			if($tmp[0] > 0) $cart['sell_prc'] += $tmp[0];
		}

		if($cpn['sale_type'] === 'e') { // EA 단위 할인
			$cpn_ex_tot -= $cart['buy_ea'];
			$cpn_ex_prd++;
			if($cart['buy_ea'] > $cpn_ex_remain) $cart['buy_ea'] = $cpn_ex_remain;
			$cpn_ex_remain -= $cart['buy_ea'];
		}

		$cpn_prc += ($cart['sell_prc'] * $cart['buy_ea']);
		if($cpn['stype'] != 3) {
			$cpn_cno[$cart['no']] = ($cart['sell_prc'] * $cart['buy_ea']);
		}
	}

	if($cpn_ex_prd > 0 && $cpn['sale_limit'] > 0 && $cpn_prc > $cpn['sale_limit']) {
		$cpn_prc = 0;
	} elseif($cpn_ex_prd > 0 && $cpn_ex_tot < $cpn_ex_remain) {
		$cpn_no = 0;
	}

	if(!$cpn_prc && $cpn_no > 0 && !$cpnerr) $cpnerr = __lang_cpn_error_notAvailable__;

	if(!$real_order_mode) {
		printAjaxHeader();
		exit('{"cpn_no":"'.$cpn_no.'","cpn_prc":"'.$cpn_prc.'", "cpnerr":"'.$cpnerr.'"}');
	}

	if($cpn_use_limit == 3 && $cpn_prc > 0) {
		$cpn_sale_only = true;
	}

?>