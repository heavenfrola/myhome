<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  주문서 작성
	' +----------------------------------------------------------------------------------------------+*/
	include_once $engine_dir.'/_config/set.country.php';

	if($cfg['use_sbscr'] == 'Y') {
		include_once $engine_dir."/_plugin/subScription/sbscr.lib.php";
	}

	if(!$delivery_fee_type) $delivery_fee_type = $cfg['delivery_fee_type']=='A'?"D":$cfg['delivery_fee_type'];

	$_replace_code[$_file_name]['form_start']="<form name=\"ordFrm\" method=\"post\" action=\"".$root_url."/main/exec.php\" target=\"hidden".$now."\" onSubmit=\"return checkOrdFrm(this)\" style=\"margin:0px\">
<input type=\"hidden\" name=\"exec_file\" value=\"order/order.exe.php\">
<input type=\"hidden\" name=\"ono\" value=\"\">
<input type=\"hidden\" name=\"cart_selected\" value=\"$cart_selected\">
<input type=\"hidden\" name=\"currency_decimal\" value=\"".$cfg['currency_decimal']."\">
<input type=\"hidden\" name=\"sbscr\" value=\"".$sbscr."\">
<input type=\"hidden\" name=\"addr_add\" value=\"\">
<input type=\"hidden\" name=\"addr_update\" value=\"\">
<input type=\"hidden\" name=\"addr_default\" value=\"\">
";

	$_tmp="";
	$_line=getModuleContent('order_cart_'.$file_sbscr.'list');
	if(!$_skin['order_cart_list_imgw']) $_skin['order_cart_list_imgw']=50;
	if(!$_skin['order_cart_list_imgh']) $_skin['order_cart_list_imgh']=50;
	$prd_weights = 0;
	$cart_no_arr = array();

	//장바구니 상품 목록
	if($sbscr=='Y') $sbscr_yn = 'Y';
    $this_cart_cnt = 0;
	$ptnOrd = new OrderCart();
	while($cart = cartList($_skin['order_cart_list_btw_opt'], $_skin['order_cart_list_opt_prc'], $_skin['order_cart_list_opt_f'], $_skin['order_cart_list_opt_b'], $_skin['order_cart_list_imgw'], $_skin['order_cart_list_imgh'])) {
		if(getPrdBuyLevel($cart) != null) {
			$pdo->query("delete from {$tbl['cart']} where no='{$cart['cno']}'");
			continue;
		}
		$ptnOrd->addCart($cart);
        $this_cart_cnt++;
	}
	$ptnOrd->complete();
	$total_order_price = $ptnOrd->getData('total_order_price');
	$delivery_prc = $ptnOrd->getData('dlv_prc');
	$cart_sum_price = $ptnOrd->getData('sum_prd_prc');
	$event_total_prc = $ptnOrd->getData('total_sale2_prc');
	$timesale_total_prc = $ptnOrd->getData('total_sale3_prc');
	$member_total_prc = $ptnOrd->getData('total_sale4_prc');
	$sbscr_total_prc = $ptnOrd->getData('total_sale8_prc');

    if($sbscr == 'Y' && $cfg['sbscr_cart_type'] == 'S' && $this_cart_cnt > 1) {
        msg('정기배송 상품은 한번에 하나만 주문이 가능합니다.', $root_url.'/shop/cart.php');
    }

	while($obj = $ptnOrd->loopCart()) {
		$cart = $obj->data;

        if ($sbscr == 'Y') { // 예전에 장바구니에 넣은 주문 체크
            if ($cart['start_date'] < strtotime(date('Y-m-d'))+($cfg['sbscr_first_date']*86400)) {
                msg('주문 불가능한 정기배송 일자입니다.\\n날짜를 변경하여 재주문해주세요.', $root_url.'/shop/cart.php');
            }
        }

		if($cfg['use_prc_consult'] == 'Y') {
			$cart['sell_prc_consultation_msg'] = $pdo->row("SELECT sell_prc_consultation_msg FROM {$tbl['product']} WHERE no='{$cart['pno']}' AND sell_prc_consultation!='' ");
			if($cart['sell_prc_consultation_msg']!="") {
				if(!$prd["sell_prc_consultation_msg"]) $prd["sell_prc_consultation_msg"] = "주문 전 협의가 필요한 상품입니다.";
				msg(php2java($cart['sell_prc_consultation_msg']), 'back', '');
			}
		}

		$cart_no_arr[] = $cart['cno'];
		$cart['link'] = $root_url.'/shop/detail.php?pno='.$cart['hash'];
		$cart['name_link']="<a href=\"".$cart['link']."\">".$cart['name']."</a>";
		$cart['imgr']="<img src=\"".$cart['img']."\" ".$cart['imgstr']." barder=\"0\">";
		$cart['imgr_link']="<a href=\"".$cart['link']."\">".$cart['imgr']."</a>";
		$cart['etc']=stripslashes($cart['etc']);
		$cart['weight'] = $cart['weight']*$cart['buy_ea'];
		$cart['is_dlv_alone'] = ($cart['dlv_alone'] == 'Y') ? 'singleorder' : '';
		$cart['prd_dlv_prc'] = parsePrice($cart['prd_dlv_prc'], true);

		// 정기배송 배송정보 가져오기
		if($cfg['use_sbscr'] == 'Y') {
			$cart = parseUserSbscr($cart);
		}

        // 세트 메인 상품 출력
		if ($cart['set_idx'] && $cart['set_pno'] && $obj->set_order == 1) {
			$_setprd = prdOneData(
                shortCut($pdo->assoc("select *, '{$cart['cno']}' as cno from {$tbl['product']} where no='{$cart['set_pno']}'")),
                $_skin['cart_list_imgw'],
                $_skin['cart_list_imgh'],
                3
            );
			$_setprd['sum_prd_prc_c'] = parsePrice($obj->productSets->get('total_prc'), true);
			$_setprd['discount_prc'] = parsePrice($obj->productSets->get('discount_prc'), true);
			$_setprd['sum_sell_prc_c'] = parsePrice($obj->productSets->get('pay_prc'), true);
			$_setprd['sum_milage'] = parsePrice($obj->productSets->get('milage'), true);
			$_setprd = parseUserCart($_setprd);
			$_setprd['cno'] = $cart['cno'];
            $_setprd['buy_ea'] = '';
			$_tmp .= lineValues('order_cart_list', $_line[5], $_setprd, '', 1);
		}

		$_tmp .= lineValues('order_cart_list', ($cart['set_idx'] ? $_line[6] : $_line[2]), $cart);

        if ($scfg->comp('use_kakaopay', 'Y') == true) {
            if ($cart['sell_prc'] > 5000000) {
                $kakaopay_disable = 'price';
            }

            if($cfg['use_sbscr'] == 'Y') {
                $end_date = strtotime($cart['end_date']);
                if ($end_date > strtotime('+2 month', strtotime(date('Y-m-d')))) {
                    $kakaopay_disable = 'sbscr';
                }
            }
        }
	}

	// 단독배송 상품 체크
    if($total_dlv_alone_rows > 0 && $cart_rows > 1) {
		msg(__lang_shop_error_dlvalone1__, $root_url.'/shop/cart.php');
	}

	$_replace_code[$_file_name]['order_cart_list'] = listContentSetting($_tmp, $_line);
	$_replace_code[$_file_name]['order_cart_sum'] = $ptnOrd->getData('sum_prd_prc', true);
	$_replace_code[$_file_name]['order_weight_sum'] = number_format($ptnOrd->getData('cart_weight'), 2);

	$_replace_code[$_file_name]['order_r_cart_sum'] = showExchangeFee($ptnOrd->getData('sum_prd_prc', true));
	$_replace_code[$_file_name]['order_cart_point'] = ($cfg['point_buy1'] && $cfg['point_buy2']) ? number_format(floor($ptnOrd->getData('sum_prd_prc')/ $cfg['point_buy1']) * $cfg['point_buy2']) : "";
	$_replace_code[$_file_name]['order_delivery_fee'] = deliveryStr();
	$_replace_code[$_file_name]['order_r_delivery_fee'] = showExchangeFee(deliveryStr());
	$_replace_code[$_file_name]['order_cod_prc'] = parsePrice($ptnOrd->getData('cod_prc'), true);

	$_replace_code[$_file_name]['order_basic_dlvfee'] = parsePrice($ptnOrd->getData('basic_dlv_prc'), true);
	$_replace_code[$_file_name]['order_basic_dlvfee2'] = parsePrice($ptnOrd->getData('basic_dlv_prc'), true).$cfg['r_currency_type'];
	$_replace_code[$_file_name]['order_prd_dlvfee'] = parsePrice($ptnOrd->getData('prd_dlv_prc'), true);
	$_replace_code[$_file_name]['order_prd_dlvfee2'] = parsePrice($ptnOrd->getData('prd_dlv_prc'), true).$cfg['r_currency_type'];

	$_replace_code[$_file_name]['order_order_sum'] = $ptnOrd->getData('sum_prd_prc', true);
	$_replace_code[$_file_name]['order_r_order_sum'] = showExchangeFee($ptnOrd->getData('sum_prd_prc', true));
	$_replace_code[$_file_name]['order_sum_milage'] = $ptnOrd->getData('total_milage', true);
	$_replace_code[$_file_name]['order_r_sum_milage'] = showExchangeFee($ptnOrd->getData('total_milage', true));

	// 입점업체 장바구니
	if($cfg['use_partner_shop'] == 'Y' && $cfg['use_partner_delivery'] == 'Y') {
		$_tmp = '';
		$_line = getModuleContent('order_cart_partner_list');
		$_line2 = getModuleContent('order_cart_partner_'.$file_sbscr.'sub_list');
		$_cart_module_name = 'order_cart_list';

		include_once 'shop_cart_partner.php';

		$_replace_code[$_file_name]['order_cart_'.$file_sbscr.'partner_list'] = $_tmp;
		$_replace_code[$_file_name]['order_cart_'.$file_sbscr.'list'] = $_tmp;
	}

	$_replace_code[$_file_name]['order_buyer_name']=$buyer['name'];
	$_replace_code[$_file_name]['order_buyer_phone']=$buyer['phone'];
	$_replace_code[$_file_name]['order_buyer_phone1']=$buyer_phone[0];
	$_replace_code[$_file_name]['order_buyer_phone2']=$buyer_phone[1];
	$_replace_code[$_file_name]['order_buyer_phone3']=$buyer_phone[2];
	$_replace_code[$_file_name]['order_cell_phone']=$buyer['cell'];
	$_replace_code[$_file_name]['order_cell_phone1']=$buyer_cell[0];
	$_replace_code[$_file_name]['order_cell_phone2']=$buyer_cell[1];
	$_replace_code[$_file_name]['order_cell_phone3']=$buyer_cell[2];
	$_replace_code[$_file_name]['order_cell_email']=$buyer['email'];
	$_replace_code[$_file_name]['order_addr_select']=oldAddressee();
	$_replace_code[$_file_name]['order_pay_type']=orderPayType(__lang_order_info_depositor__.' : ');
	if(!$usable_milage_c) usable_milage();
	$_replace_code[$_file_name]['order_milage']=$usable_milage_c;
	$_replace_code[$_file_name]['order_emoney']=$usable_emoney_c;

	$addressee_cell = explode('-', $request_data['sms_send']);

	// 국/해외배송 선택
	$_replace_code[$_file_name]['delivery_fee_type']=$cfg['delivery_fee_type']=='A'?'Y':'';

	$PHP_SELF = $_SERVER['PHP_SELF'];
	if($_GET['delivery_fee_type']) $delivery_fee_type = $_GET['delivery_fee_type'];
    if ($delivery_fee_type != 'O') $_replace_code[$_file_name]['order_overseas_delivery'] = '';

	$_replace_code[$_file_name]['delivery_fee_type_radio']="
		<label><input type=\"radio\" value=\"D\" name=\"delivery_fee_type\" ".($delivery_fee_type=='D'?"checked":"")." onclick=\"onChangeOrderAddr('".$PHP_SELF."','".$cart_selected."',this)\"  />".__lang_order_domestic_shipping__."</label>\n
		<label><input type=\"radio\" value=\"O\" name=\"delivery_fee_type\" ".($delivery_fee_type=='O'?"checked":"")." onclick=\"onChangeOrderAddr('".$PHP_SELF."','".$cart_selected."',this)\"  />".__lang_order_international_shipping__."</label>\n
	";
	$_replace_code[$_file_name]['bill_order_add_field_use']=$cfg['order_add_field_use']=='Y'?'Y':'';



	$overseas = "";
	// 설정이 국내/해외배송 이고 해외 배송을 선택했거나 설정이 해외 배송일경우 폼 변경
	if(($cfg['delivery_fee_type'] == 'A' && $delivery_fee_type == 'O') || $cfg['delivery_fee_type'] == 'O'){
		$overseas = "_oversea";

		// 국가 및 국가 번호
		$_tmp = "<option value=\"\">:: ".__lang_order_info_nations__." ::</option>\n";
		$_tmp_p = "";
		$_nations_arr = getDeliveryPossibleCountry(); //배송 가능한 국가만(해외배송 업체에 세팅된 국가만)

		foreach($_nations_arr as $k=>$v){
			$_tmp .= "<option value='${v['code']}' data-phone='${v['phone']}'>${v['name']}</option>\n";
			$_tmp_p .= "<option value='${v['phone']}'> +${v['phone']}</option>\n";
		}

		$_replace_code[$_file_name]['delivery_nations'] = "<select name='nations' onchange=\"onChangePhoneCode(this);getIntShipping(this, '$cart_weight',document.all.delivery_com);\">$_tmp</select>";
		$_replace_code[$_file_name]['order_oversea_phone'] = "<select name='addressee_phone_code'>$_tmp_p</select>";
		$_replace_code[$_file_name]['order_oversea_cell'] = "<select name='addressee_cell_code'>$_tmp_p</select>";
		$_replace_code[$_file_name]['bill_order_oversea_phone'] = "<select name='buyer_phone_code'>$_tmp_p</select>";
		$_replace_code[$_file_name]['bill_order_oversea_cell'] = "<select name='buyer_cell_code'>$_tmp_p</select>";

		// 해외배송업체
		$_tmp_arr = getOverseaDeliveryComList();
		$_tmp = "<option value=\"\">:: ".__lang_order_info_delivery_com__." ::</option>\n";

		if($_tmp_arr['cnt'] > 1){
			foreach($_tmp_arr['list'] as $k=>$v){
				$_tmp .= "<option value='${v['no']}'>${v['name']}</option>\n";
			}
			$_replace_code[$_file_name]['delivery_com_list'] = "<select name='delivery_com' onchange=\"getIntShipping(document.all.nations, '$cart_weight',this);\">$_tmp</select>";
		}else{
			$_replace_code[$_file_name]['delivery_com_list'] = "<input type='hidden' value='".$_tmp_arr['list'][0]['no']."' name='delivery_com'/>";
			$_replace_code[$_file_name]['delivery_com_display'] = "style=\"display:none;\"";
		}
	}

	// 주소 입력 박스
	$_replace_code[$_file_name]['order_addr_box_s'] = getModuleContent('order_addr_box'.$overseas);
	if($overseas) {
		$_replace_code[$_file_name]['order_addr_box_oversea'] = getModuleContent('order_addr_box_oversea');
	} else {
		$_replace_code[$_file_name]['order_addr_box'] = getModuleContent('order_addr_box');
	}

	$_replace_code[$_file_name]['order_addre_name']=stripslashes($request_data['name']);
	$_replace_code[$_file_name]['order_addre_cell1']=$addressee_cell[0];
	$_replace_code[$_file_name]['order_addre_cell2']=$addressee_cell[1];
	$_replace_code[$_file_name]['order_addre_cell3']=$addressee_cell[2];
	$_replace_code[$_file_name]['order_addre_zip']=stripslashes($request_data['zip']);
	$_replace_code[$_file_name]['order_addre_addr1']=stripslashes($request_data['addr1']);
	$_replace_code[$_file_name]['order_addre_addr2']=stripslashes($request_data['addr2']);

	$_replace_code[$_file_name]['ord_receipt_url']="javascript:printReceipt(1, '$cart_selected');";

    $total_cpn_cnt = 0; // 사용가능한 쿠폰 갯수

	// 주문 쿠폰 사용불가시 쿠폰 리스트 로딩 안함
	if($cfg['order_cpn_paytype'] != 3 && $sbscr != 'Y'){
		$_tmp="";
		$_line=getModuleContent('order_cpn_list');
		// 쿠폰 목록
		while($cpn=myCouponList(1)){
			$cpn['sale_prc']=($cpn['type'] == '3' || $cpn['stype'] == '4') ? '' : number_format($cpn['sale_prc']).$cpn['sale_type_k'];
			$cpn['prc_limit']=number_format($cpn['prc_limit']);
			$_tmp .= lineValues('order_cpn_list', $_line, $cpn);
            $total_cpn_cnt++;
		}
		if($_tmp){
			unset($cpn);
			$cpn['radio']="<input type=\"radio\" id=\"no_cpn\" name=\"coupon\" value=\"\" onClick=\"useCpn(this.form,'','');\">";
			$cpn['name'] = __lang_common_info_notUsed__;
			$cpn['code'] = __lang_common_info_notUsed__;
			$_tmp .= lineValues('order_cpn_list', $_line, $cpn);
		}
		$_tmp=listContentSetting($_tmp, $_line);
		$_replace_code[$_file_name]['order_cpn_list']=$_tmp;

		// 오프라인 쿠폰
		if($Cpn2){
			$_line=getModuleContent("order_offcpn");
			$_tmp="
	<input type=\"hidden\" name=\"off_cpn_price\" value=\"0\">
	<input type=\"hidden\" name=\"off_cpn_sale\" value=\"0\">
	<input type=\"hidden\" name=\"off_cpn_type\">
	<input type=\"hidden\" name=\"off_cpn_min\">
	<input type=\"hidden\" name=\"off_cpn_limit\">
	<input type=\"hidden\" name=\"off_cpn_use\" value=\"N\">
	<input type=\"hidden\" name=\"off_cpn_no\">
	<input type=\"hidden\" name=\"off_cpn_pay_type\">
	".$_line;
			$_replace_code[$_file_name]['order_offcpn']=$_tmp;
		}
	}

	// 2009-11-06 : 스크립트 제어 원활하도록 기본적으로 0 세팅 - Han
	$total_order_price=$total_order_price ? $total_order_price : 0;
	$event_total_prc=$event_total_prc ? $event_total_prc : 0;
	$member_total_prc=$member_total_prc ? $member_total_prc : 0;
	$usable_milage=$usable_milage ? $usable_milage : 0;
	$delivery_prc=$delivery_prc ? $delivery_prc : 0;

	$_replace_code[$_file_name]['form_end'].="
<input type=\"hidden\" name=\"total_order_price\" value=\"".$total_order_price."\">
<input type=\"hidden\" name=\"event_total_prc\" value=\"".$event_total_prc."\"> <!-- 이벤트할인 2006-05-18 -->
<input type=\"hidden\" name=\"member_total_prc\" value=\"".$member_total_prc."\"> <!-- 회원할인 2006-05-18 -->
<input type=\"hidden\" name=\"usable_milage\" value=\"".$usable_milage."\">
<input type=\"hidden\" name=\"delivery_prc\" value=\"".$delivery_prc."\"> <!--배송비 2006-05-16-->
<input type=\"hidden\" name=\"cart_where\" value=\"".$cart_where."\"> <!-- 장바구니조건값 2006-05-16 -->
</form>
<script type='text/javascript'>
var prdprc_sale = '$prdprc_sale';
var prdprc_sale_ptype = '$cfg[prdprc_sale_ptype]';
var prdprc_sale_add = '$cfg[prdprc_sale_add]';
</script>
";
    if (isset($kakaopay_disable) == true) {
        $_replace_code[$_file_name]['form_end'] .= "<script>kakaopayDisabled('$kakaopay_disable');</script>";
    }

    $isMb = ($mobile_browser == 'mobile') ? 'Y' : '';
	$_replace_code[$_file_name]['zip_url']="javascript:zipSearch('ordFrm','addressee_zip','addressee_addr1','addressee_addr2','', '".$isMb."');";
	$_replace_code[$_file_name]['street_zip_url']="javascript:zipSearch('ordFrm','addressee_zip','addressee_addr1','addressee_addr2',2,'".$isMb."');";

	if(is_array($_ord_add_info)){
		$_tmp="";
		$_line=getModuleContent("order_addfd_list");
		foreach($_ord_add_info as $key=>$val){//ADDINFO_DONE
			$_oaddfd['name']=stripslashes($_ord_add_info[$key]['name']);
			$_oaddfd['value']=orderAddFrm($key, 1);
			$_tmp .= lineValues("order_addfd_list", $_line, $_oaddfd);
		}
		unset($_oaddfd);
		$_tmp=listContentSetting($_tmp, $_line);
		$_replace_code[$_file_name]['order_addfd_list']=$_tmp."<!-- ADDINFO_DONE -->";
	}

	if($free_delivery) echo "<script type='text/javascript'>delivery_fee=0;</script>"; // 2011-09-30 무료배송 상품이 스크립트에 포함 안되는 버그 수정 by zardsama

	if($cfg['use_ems'] == 'Y') {
		$_tmp = '';
		if(file_exists($root_dir.'/_data/config/ems_nation.php')) {
			include_once $root_dir.'/_data/config/ems_nation.php';
		} else {
			include_once $engine_dir.'/_config/set.ems_nation.default.php';
		}
		foreach($ems_nation as $key => $val) {
			$sel = $member['nations'] == $key ? 'selected' : '';
			$_tmp .= "<option value='$key' $sel>$key</option>\n";
		}
		$_tmp = "<select name='nations' onchange=\"getIntShipping(this, '$cart_weight')\">$_tmp</select>";
		$_replace_code[$_file_name]['order_nations']=$_tmp;
		$_tmp = '';
	}

	// 이벤트, 회원할인 정보
	$tmp = '';
	if($event_total_prc > 0 && checkEventAble()) {
		$tmp .= preg_replace('/^([0-9]{4})([0-9]{2})([0-9]{2})/', '$1년 $2월 $3일', $cfg[event_finish]).' 까지 ';
		if($cfg['event_min_pay'] > 0) $tmp .= number_format($cfg['event_min_pay']).'원 이상 ';
		if($cfg['event_ptype'] == 2) $tmp .= '현금 ';
		$tmp .= '결제시';
		if($cfg['event_obj'] == 2) $tmp .= ' 회원 ';
		$tmp .= $cfg['event_per'].'% ';
		$tmp .= ($cfg['event_type'] == 1) ? '적립' : '할인';

		$_replace_code[$_file_name]['event_sale_info'] = $tmp;
	}
	unset($tmp);

	$tmp = '';
	if($event_total_prc > 0 && $cfg['freedeli_event_use'] == 'Y' && ($cfg['freedeli_event_obj'] == 1 || ($cfg['freedeli_event_obj'] == 2 && $member['no'] > 0))) {
		$ev_begin = str_replace('/', '', $cfg['freedeli_event_begin']);
		$ev_fin = str_replace('/', '', $cfg['freedeli_event_finish']);
		$ev_date = (strlen($cfg['freedeli_event_begin']) > 10) ? date('YmdHi') : date('Ymd');

		if($ev_date >= $ev_begin && $ev_date <= $ev_fin) {
			$tmp .= (strlen($cfg['freedeli_event_begin']) > 10) ? preg_replace('/^([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})/', '$1년 $2월 $3일 $4시 $5분', $ev_fin).' 까지 ' : preg_replace('/^([0-9]{4})([0-9]{2})([0-9]{2})/', '$1년 $2월 $3일', $ev_fin).' 까지 ';
			if($cfg['freedeli_event_min_pay'] > 0) $tmp .= number_format($cfg['freedeli_event_min_pay']).' 원 이상 결제시';
			$_replace_code[$_file_name]['event_sale_info'] .= "<br />$tmp 무료배송";
		}
	}
	unset($tmp, $ev_begin, $ev_fin, $ev_date);

	$tmp = '';
	if($member['no'] > 0 && $member_total_prc > 0 && $cfg['member_event_use'] == 'Y') {
		$mgroup = $pdo->assoc("select * from {$tbl['member_group']} where no='$member[level]'");

		$tmp .= stripslashes($mgroup['name']).'회원 ';
		if($mgroup['milage_cash'] == 'Y') $tmp .= '현금구매시 ';
		$tmp .= "$mgroup[milage]% ";
		$tmp .= $cfg['member_event_type'] == 1 ? '적립' : '할인';
		if($cfg['mgroup_free_delivery'] == 'Y' && $mgroup['free_delivery'] == 'Y') $tmp .= ' + 무료배송 ';

		$_replace_code[$_file_name]['msale_info'] = $tmp;
	}
	unset($tmp, $mgroup);

	if($prdprc_sale > 0) $_replace_code[$_file_name]['prdprc_sale_info'] = "<span class=\"order_saleinfo_prd_prc\"></span>";
	if($event_total_prc > 0) $_replace_code[$_file_name]['time_sale_info'] = $timesale_total_prc;

	// 결제방식2
	$paytypes = array();
	if($scfg->comp('pay_type_1', 'Y') == true) $paytypes['paytype1'] = '<input type="radio" name="pay_type" id="pay_type1" value="1" onClick="useMilage(this.form,3)">';
	if($scfg->comp('pay_type_2', 'Y') == true) {
		$paytypes['paytype2'] = '<input type="radio" name="pay_type" id="pay_type2" value="2" onClick="useMilage(this.form,3)">';

		$bres = $pdo->iterator("select * from $tbl[bank_account] where 1 $bank_where order by sort");
		$paytypes['bank_account']  = '<span id="bank_list_span"><select name="bank">';
		$paytypes['bank_account'] .= '<option value="">:: '.__lang_order_select_bank__.'::</option>';
        foreach ($bres as $bdata) {
			$paytypes['bank_account'] .= "<option value='$bdata[no]'>".stripslashes($bdata['bank'].' '.$bdata['account'].' '.$bdata['owner']).'</option>';
		}
		$paytypes['bank_account'] .= '</select></span>';
		if($cfg['cash_receipt_use'] == 'Y') $paytypes['use_cash_receipt'] = 'Y';
	}
	if($scfg->comp('pay_type_4', 'Y') == true) $paytypes['paytype4'] = '<input type="radio" name="pay_type" id="pay_type4" value="4" onClick="useMilage(this.form,3)">';
	if($scfg->comp('pay_type_5', 'Y') == true) $paytypes['paytype5'] = '<input type="radio" name="pay_type" id="pay_type5" value="5" onClick="useMilage(this.form,3)">';
	if($scfg->comp('pay_type_7', 'Y') == true) $paytypes['paytype7'] = '<input type="radio" name="pay_type" id="pay_type7" value="7" onClick="useMilage(this.form,3)">';
	if($cfg['use_alipay'] == 'Y') $paytypes['paytype10'] = '<input type="radio" name="pay_type" id="pay_type10" value="10" onClick="useMilage(this.form,3)">';
	if($cfg['use_alipay_e'] == 'Y') $paytypes['paytype19'] = '<input type="radio" name="pay_type" id="pay_type19" value="19" onClick="useMilage(this.form,3)">';
	if($cfg['use_kakaopay'] == 'Y') $paytypes['paytype12'] = '<input type="radio" name="pay_type" id="pay_type12" value="12" onClick="useMilage(this.form,3)">';
	if($cfg['use_paypal'] == 'Y') $paytypes['paytype13'] = '<input type="radio" name="pay_type" id="pay_type13" value="13" onClick="useMilage(this.form,3)">';
	if($cfg['use_paypal_c'] == 'Y') $paytypes['paytype16'] = '<input type="radio" name="pay_type" id="pay_type16" value="16" onClick="useMilage(this.form,3)">';
	if($cfg['use_cyrexpay'] == 'Y') $paytypes['paytype14'] = '<input type="radio" name="pay_type" id="pay_type14" value="14" onClick="useMilage(this.form,3)">';
	if($cfg['use_sbipay'] == 'Y') $paytypes['paytype15'] = '<input type="radio" name="pay_type" id="pay_type15" value="15" onClick="useMilage(this.form,3)">';
	if($cfg['use_payco'] == 'Y') $paytypes['paytype17'] = '<input type="radio" name="pay_type" id="pay_type17" value="17" onClick="useMilage(this.form,3)">';
	if($cfg['use_wechat'] == 'Y') $paytypes['paytype18'] = '<input type="radio" name="pay_type" id="pay_type18" value="18" onClick="useMilage(this.form,3)">';
	if($cfg['use_alipay_e'] == 'Y') $paytypes['paytype19'] = '<input type="radio" name="pay_type" id="pay_type19" value="19" onClick="useMilage(this.form,3)">';
	if($cfg['use_exim'] == 'Y') $paytypes['paytype20'] = '<input type="radio" name="pay_type" id="pay_type20" value="20" onClick="useMilage(this.form,3)">';
	if($cfg['use_tosspayment'] == 'Y' || $cfg['use_tosscard'] == 'Y') $paytypes['paytype22'] = '<input type="radio" name="pay_type" id="pay_type22" value="22" onClick="useMilage(this.form,3)">';
	if($cfg['use_sbscr'] == 'Y' && $cfg['sbscr_order_split']=='Y') $paytypes['paytype23'] = '<input type="radio" name="pay_type" id="pay_type23" value="23">';
	if ($scfg->comp('use_nsp', 'Y') == true) {
            $icon = ($_SESSION['browser_type'] == 'mobile') ? 'naverpay_m.png' : 'naverpay_pc.png';
			$paytypes['paytype25']  = '<div class="'.$pay_type_prefix.'">';
			$paytypes['paytype25'] .= '<label class="pay_label"><input type="radio" name="pay_type" id="pay_type25" value="25" onClick="useMilage(this.form,3)"> ';
            switch($scfg->get('nsp_button_type')) {
                case '1' :
                    $paytypes['paytype25'] .= '<img src="'.$engine_url.'/_engine/card.naverSimplePay/'.$icon.'" height="20">';
                    break;
                case '2' :
                    $paytypes['paytype25'] .= '네이버페이';
                    break;
                case '3' :
                    $paytypes['paytype25'] .= '<img src="'.$engine_url.'/_engine/card.naverSimplePay/'.$icon.'" height="20"> 네이버페이';
                    break;
            }
			$paytypes['paytype25'] .= '</label>';
			$paytypes['paytype25'] .= '</div>';
    }
    if ($scfg->comp('use_samsungpay', 'Y') == true) $paytypes['paytype28'] = '<input type="radio" name="pay_type" id="pay_type28" value="28" onClick="useMilage(this.form,3)">';

    // 카카오페이 버튼을 통해 접근 시 카카오페이만 선택 가능
    if (isset($_GET['pay_type']) == true && $_GET['pay_type'] == 'kakaopay') {
        foreach ($paytypes as $key => $val) {
            if ($key != 'paytype12') unset($paytypes[$key]);
        }
    }

	$_line = getModuleContent('order_pay_type2');
	$_tmp = lineValues('order_pay_type2', $_line, $paytypes);
	$_tmp = listContentSetting($_tmp, $_line);
	$_replace_code[$_file_name]['order_pay_type2'] = $_tmp;
	unset($_line, $tmp);

	// 주문 쿠폰 사용불가시 쿠폰 리스트 로딩 안함
	if($cfg['order_cpn_paytype'] != 3){
		// 개별상품쿠폰 존재 여부
		$_tmp = '';
		if($member['no'] > 0) {
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
    // 전체/개별 상품 쿠폰이 없는 경우 쿠폰 영역 비노출
    if ($total_cpn_cnt < 1) $_replace_code[$_file_name]['order_cpn_list'] = '';

	if($sbscr=='Y') {
		$sbscr_input = "";
		if($cfg['sbscr_order_all']=='Y') $sbscr_input .= "<div class='paytype_gr3'><input type=\"radio\" name=\"sbscr_all\" value=\"Y\" onclick=\"sbscrTypeChk('Y')\" id='sbscr_all_y' checked><label for='sbscr_all_y'>일괄결제</label></div>";
		if($cfg['sbscr_order_split']=='Y') $sbscr_input .= ($sbscr_input) ? "<div class='paytype_gr4'><input type=\"radio\" name=\"sbscr_all\" value=\"N\" onclick=\"sbscrTypeChk('N')\" id='sbscr_all_n'><label for='sbscr_all_n'>정기결제</label></div>" : "<div class='paytype_gr4'><input type=\"radio\" name=\"sbscr_all\" value=\"N\" onclick=\"sbscrTypeChk('N')\"; id='sbscr_all_n' checked><label class='sbscr_all_n'>정기결제</label></div>";
		$_replace_code[$_file_name]['order_sbscr_all_yn'] = $sbscr_input;
		$_replace_code[$_file_name]['sbscr_firsttime_pay_prc'] = parsePrice($ptnOrd->getData('sbscr_firsttime_pay_prc'), true);
	}

    // 페이스북 픽셀 API
    if ($scfg->comp('use_fb_conversion', 'Y') == true && $scfg->comp('fb_pixel_id') == true) {
        require_once __ENGINE_DIR__.'/_engine/promotion/fd_conversion_checkout.inc.php';
    }

    // 기본배송지
    if($member['no'] > 0) {
        if ($_GET['delivery_fee_type'] == 'O') $where = "AND is_default='Y' AND ifnull(nations, '') != '' ";
        else $where = "AND is_default='Y' AND ifnull(nations, '') = '' ";
        $addr_res = memberAddressGet($member, $where, '');
        $defaultYN = 'N';
        $btn_txt = ($_SESSION['browser_type'] == 'mobile') ? '변경' : '정보 변경';
        if ($addr_res) {
            foreach ($addr_res as $addr) {
                $addr->is_default = ($addr->is_default == 'Y') ? 'Y' : '';
                $addr->title = replaceEntities($addr->title, true);
                $addr->name = replaceEntities($addr->name, true);
                $addr->addr1 = replaceEntities($addr->addr1, true);
                $addr->addr2 = replaceEntities($addr->addr2, true);
                $addr->addr3 = replaceEntities($addr->addr3, true);
                $addr->addr4 = replaceEntities($addr->addr4, true);
                $addr->btn_area = '<a onclick="openOrderAddress(0); ">'.$btn_txt.'</a>';
                if ($addr->is_default == 'Y') {
                    $_line = getModuleContent('order_default_address');
                    $_tmp = lineValues('order_default_address', $_line, (array)$addr);
                    $_tmp = listContentSetting($_tmp, $_line);
                    $_replace_code[$_file_name]['order_default_address'] = $_tmp;
                    unset($_line, $tmp);
                    $defaultYN = 'Y';
                }
            }
        }

        if (!$addr_res || $defaultYN == 'N') {
            if ($_GET['delivery_fee_type'] == 'O') $where = "AND ifnull(nations, '') != '' ";
            else $where = "AND ifnull(nations, '') = '' ";
            $ordersql = "order by no desc limit 1 ";
            $addr_res = memberAddressGet($member, $where, $ordersql);
            if ($addr_res) {
                foreach ($addr_res as $addr) {
                    $addr->is_default = '';
                    $addr->btn_area = '<a onclick="openOrderAddress(0); ">'.$btn_txt.'</a>';
                    $_line = getModuleContent('order_default_address');
                    $_tmp = lineValues('order_default_address', $_line, (array)$addr);
                    $_tmp = listContentSetting($_tmp, $_line);
                    $_replace_code[$_file_name]['order_default_address'] = $_tmp;
                    unset($_line, $tmp);
                }
            } else {
                $btn_txt = ($_SESSION['browser_type'] == 'mobile') ? '등록' : '신규 등록';
                $addr = array();
                $addr['btn_area'] = '<a onclick="openOrderAddress(2);" class="n_addr_btn">'.$btn_txt.'</a>';
                $_line = getModuleContent('order_default_address');
                $_tmp = lineValues('order_default_address', $_line, (array)$addr);
                $_tmp = listContentSetting($_tmp, $_line);
                $_replace_code[$_file_name]['order_default_address'] = $_tmp;
                unset($_line, $tmp);
            }
        }
    }

    // 배송메세지
    unset($memo);
    $_tmp = "";
    $_line = getModuleContent('order_default_dlvmemo_list');

    // 고정 배송메모
    $default_memo_arr = array();
    foreach ($_default_dlv_memo as $_memo) {
        $memo['memo'] = $_memo;
        $default_memo_arr[] = $_memo;
        $_tmp .= lineValues('order_default_dlvmemo_list', $_line, $memo);
    }

    if( $member['no'] > 0 ) {
        unset($memo);
        $default_memo_notin = implode("','", $default_memo_arr);
        $dlv_res = $pdo->iterator("select distinct dlv_memo from {$tbl['order']} where `stat` != 11 and `member_no`='{$member['no']}' and `member_id`='{$member['member_id']}' AND dlv_memo!='' AND dlv_memo not in ('{$default_memo_notin}') order by `no` desc limit 3");
        foreach ($dlv_res as $key => $dlvmemo) {
            $memo['memo'] = "<span>(최근 메시지)</span> " . $dlvmemo['dlv_memo'];
            $_tmp .= lineValues('order_default_dlvmemo_list', $_line, $memo);
        }
    }
    $_tmp = listContentSetting($_tmp, $_line);
    $_replace_code[$_file_name]['order_default_dlvmemo_list'] = $_tmp;

    // 사용가능한 쿠폰 갯수
    $_replace_code[$_file_name]['order_use_coupon_cnt'] = number_format($total_cpn_cnt);

    // 결제수단3
    $_tmp = "";
    $_line = getModuleContent('order_pay_type3_list');
    $_ptype = array();

    $pay_type_prefix = ($sbscr=='Y') ? 'paytype_gr1' : '';
    // 간편결제
    $_easypay_type = array(22 => 'use_tosspayment', 25 => 'use_nsp', 12 => 'use_kakaopay', 28 => 'use_samsungpay', 17 => 'use_payco');
    foreach ($_easypay_type as $key => $val) {
        if ($key == 22) {
            if ($cfg[$val] == 'Y' || $cfg['use_tosscard'] == 'Y')  {
                $_ptype['pay_type'] = $key;
                $_ptype['pay_name'] = (defined('__lang_order_info_paytype'.$key.'__')) ? constant('__lang_order_info_paytype'.$key.'__') : $_pay_type[$key];
                $_ptype['class_name'] = $pay_type_prefix.( $key == 12 || $key == 17 || $key == 22 ? " simple ptype{$key}" : "");;
                $_tmp .= lineValues('order_pay_type3_list', $_line, $_ptype);
            }
        } else {
            if ($cfg[$val] == 'Y')  {
                $_ptype['pay_type'] = $key;
                if ($val == 'use_nsp') {
                    $icon = ($_SESSION['browser_type'] == 'mobile') ? 'naverpay_m.png' : 'naverpay_pc.png';
                    switch($scfg->get('nsp_button_type')) {
                        case '1' :
                            $pay_name = '<img src="'.$engine_url.'/_engine/card.naverSimplePay/'.$icon.'" height="20">';
                            break;
                        case '3' :
                            $pay_name = '<img src="'.$engine_url.'/_engine/card.naverSimplePay/'.$icon.'" height="20">네이버페이';
                            break;
                        default :
                            $pay_name = '네이버페이';
                            break;
                    }
                    $_ptype['pay_name'] = $pay_name;
                } else {
                    $_ptype['pay_name'] = (defined('__lang_order_info_paytype'.$key.'__')) ? constant('__lang_order_info_paytype'.$key.'__') : $_pay_type[$key];
                }
                $_ptype['class_name'] = $pay_type_prefix.( $key == 12 || $key == 17 || $key == 22 ? " simple ptype{$key}" : "");
                if ($val == 'use_nsp' && $scfg->comp('nsp_button_type','3')) $_ptype['class_name'] .= ' mix';
                $_tmp .= lineValues('order_pay_type3_list', $_line, $_ptype);
            }
        }
    }
    //일반결제
    foreach($_pay_type as $key => $val) {
        if($key == 3 || $key == 6 || $key == 8 || $key == 9) continue;

        if($scfg->comp('pay_type_'.$key.'', 'Y') == true) {
            $_ptype['pay_type'] = $key;
            $_ptype['pay_name'] = $val;
            $_ptype['class_name'] = $pay_type_prefix.( $key == '12' || $key == '17' || $key == '22' ? " simple ptype{$key}" : "");
            $_tmp .= lineValues('order_pay_type3_list', $_line, $_ptype);
        }
    }
    //해외결제
    $_cardint_type = array(15 => 'use_sbipay', 10 => 'use_alipay', 16 => 'use_paypal_c', 13 => 'use_paypal', 19 => 'use_alipay_e', 14 => 'use_cyrexpay', 18 => 'use_wechat', 20 => 'use_exim');
    foreach ($_cardint_type as $key => $val) {
        if ($cfg[$val] == 'Y')  {
            $_ptype['pay_type'] = $key;
            $_ptype['pay_name'] = (defined('__lang_order_info_paytype'.$key.'__')) ? constant('__lang_order_info_paytype'.$key.'__') : $_pay_type[$key];
            $_ptype['class_name'] = $pay_type_prefix.( $key == 12 || $key == 17 || $key == 22 ? " simple ptype{$key}" : "");;
            $_tmp .= lineValues('order_pay_type3_list', $_line, $_ptype);
        }
    }

    // 카카오페이 버튼을 통해 접근 시 카카오페이만 선택 가능
    if (isset($_GET['pay_type']) == true && $_GET['pay_type'] == 'kakaopay') {
        $_ptype['pay_type'] = 12;
        $_ptype['pay_name'] = (defined('__lang_order_info_paytype'.$_ptype['pay_type'].'__')) ? constant('__lang_order_info_paytype'.$_ptype['pay_type'].'__') : $_pay_type[$key];
        $_ptype['class_name'] = $pay_type_prefix." simple ptype{$_ptype['pay_type']}";
        $_tmp = lineValues('order_pay_type3_list', $_line, $_ptype);

    }

    if( $sbscr == "Y" ) {
       if( $scfg->comp('use_nsp_sbscr', 'Y') == true && $GLOBALS['this_cart_cnt'] == 1 ) {
            $icon = ($_SESSION['browser_type'] == 'mobile') ? 'naverpay_m.png' : 'naverpay_pc.png';
            $_ptype['pay_type'] = 27;
            $_ptype['class_name'] = "paytype_gr2";
            switch($scfg->get('nsp_sub_button_type')) {
                case '1' :
                    $_ptype['pay_name'] = '<img src="'.$engine_url.'/_engine/card.naverSimplePay/'.$icon.'" >';
                    break;
                case '2' :
                    $_ptype['pay_name'] = '네이버페이';
                    break;
                case '3' :
                    $_ptype['class_name'] = "mix";
                    $_ptype['pay_name'] = '<img src="'.$engine_url.'/_engine/card.naverSimplePay/'.$icon.'">네이버페이';
                    break;
            }
            $_tmp .= lineValues('order_pay_type3_list', $_line, $_ptype);
        }

        $_ptype['pay_type'] = 23;
        $_ptype['pay_name'] = "신용카드";
        $_ptype['class_name'] = "paytype_gr2";
        $_tmp .= lineValues('order_pay_type3_list', $_line, $_ptype);
    }
    $_tmp = listContentSetting($_tmp, $_line);
    $_replace_code[$_file_name]['order_pay_type3_list'] = $_tmp;

    // 무통장입금 정보
    if( $scfg->comp('pay_type_2', 'Y') == true ) {
        $bankinfo = array();
        $bres = $pdo->iterator("select * from {$tbl['bank_account']} where 1 order by sort");
        $bankinfo['bank_account']  = '<span id="bank_list_span"><select name="bank">';
        $bankinfo['bank_account'] .= '<option value="">:: '.__lang_order_select_bank__.'::</option>';
        foreach ($bres as $bdata) {
            $bankinfo['bank_account'] .= "<option value='{$bdata['no']}'>".stripslashes($bdata['bank'].' '.$bdata['account'].' '.$bdata['owner']).'</option>';
        }
        $bankinfo['bank_account'] .= '</select></span>';
        if($cfg['cash_receipt_use'] == 'Y') $bankinfo['use_cash_receipt'] = 'Y';

        $_line = getModuleContent('order_paytype_bankinfo');
        $_tmp = lineValues('order_paytype_bankinfo', $_line, $bankinfo);
        $_tmp = listContentSetting($_tmp, $_line);
        $_replace_code[$_file_name]['order_paytype_bankinfo'] = $_tmp;
    }
    // 주문3.0 결제버튼
    $layer3 = 'order3';
?>