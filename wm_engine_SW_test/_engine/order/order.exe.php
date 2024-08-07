<?PHP
	/* +----------------------------------------------------------------------------------------------+
	' |  주문서 저장
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/shop.lib.php";
	include_once $engine_dir."/_engine/include/milage.lib.php";
	include_once $engine_dir.'/_engine/include/wingPos.lib.php';
    include_once __ENGINE_DIR__.'/_engine/include/MemberAddress.lib.php';

	// 정기배송
	$sbscr = ($_POST['sbscr']=='Y') ? 'Y':'N';
	if($sbscr=='Y') {
		include_once $engine_dir.'/_engine/order/order_sbscr.exe.php';
		exit;
	}
	if($_GET['is_payco'] == 'true') {
		$easy_pay_direct_order = true;
		$pay_type = $_POST['pay_type'] = 17;
	} else {
		checkBasic();
	}


	$layer1 = 'order1';
	$layer2 = 'order2';
    $layer3 = 'order3'; // 주문 3.0

	$nations = $_POST['nations'] ? addslashes($_POST['nations']) : $member['nations'];
	$delivery_com = numberOnly($_POST['delivery_com']);
	$delivery_fee_type = $_POST['delivery_fee_type'];
	$pay_type = numberOnly($_POST['pay_type']);
	$buyer_name = addslashes(trim($_POST['buyer_name']));
	$buyer_phone = $_POST['buyer_phone'];
	$buyer_cell = $_POST['buyer_cell'];
	$buyer_email = addslashes(trim($_POST['buyer_email']));
    $addressee_title = addslashes(html_entity_decode(trim($_POST['addr_name'])));
	$addressee_name = addslashes(html_entity_decode(trim($_POST['addressee_name'])));
	$addressee_phone = $_POST['addressee_phone'];
	$addressee_cell = $_POST['addressee_cell'];
	$addressee_zip = addslashes(html_entity_decode(trim($_POST['addressee_zip'])));
	$addressee_addr1 = addslashes(html_entity_decode(trim($_POST['addressee_addr1'])));
	$addressee_addr2 = addslashes(html_entity_decode(trim($_POST['addressee_addr2'])));
	$addressee_addr3 = addslashes(html_entity_decode(trim($_POST['addressee_addr3'])));
	$addressee_addr4 = addslashes(html_entity_decode(trim($_POST['addressee_addr4'])));
	$coupon = $_POST['coupon'];
	$cpn_auth_code = addslashes(trim($_POST['cpn_auth_code']));
	$cart_selected = $_REQUEST['cart_selected'];
	$off_cpn_use = $_POST['off_cpn_use'];
	$sms = ($_POST['sms'] == 'Y') ? 'Y' : 'N';

    $addr_add = ($_POST['addr_add'] == 'Y' ? 'Y' : "N");
    $addr_update = numberOnly($_POST['addr_update']);
    $addr_default = ($_POST['addr_default'] == 'Y' ? 'Y' : "N");

	if($easy_pay_direct_order != true){
		checkBlank($buyer_name, __lang_order_input_oname__);

		if($_use['nec_buyer_phone'] != 'S'){
			if($cfg['use_order_phone'] != 'N') {
				if($cfg['delivery_fee_type'] == "D" || $_POST['delivery_fee_type'] == "D") {
					if(is_array($buyer_phone)){
						checkBlank($buyer_phone[0], __lang_order_input_ophone__);
						checkBlank($buyer_phone[1], __lang_order_input_ophone__);
						checkBlank($buyer_phone[2], __lang_order_input_ophone__);
					}else{
						checkBlank($buyer_phone, __lang_order_input_ophone__);
					}
				}
			}
			if(is_array($buyer_cell)){
				checkBlank($buyer_cell[0], __lang_order_input_ocell__);
				checkBlank($buyer_cell[1], __lang_order_input_ocell__);
				checkBlank($buyer_cell[2], __lang_order_input_ocell__);
			}else{
				checkBlank($buyer_cell, __lang_order_input_ocell__);
			}
		}

		if($_use['nec_buyer_email']!='N') checkBlank($buyer_email, __lang_order_input_oemail__);

		checkBlank($addressee_name, __lang_order_input_rname__);

		if($_use['nec_addressee_phone'] == 'S'){
			if($cfg['use_order_phone'] != 'N') {
				if($cfg['delivery_fee_type'] == 'D' || $_POST['delivery_fee_type'] == 'D') {
					if(is_array($addressee_phone)){
						checkBlank($addressee_phone[0], __lang_order_input_rphone__);
						checkBlank($addressee_phone[1], __lang_order_input_rphone__);
						checkBlank($addressee_phone[2], __lang_order_input_rphone__);
					}else{
						checkBlank($addressee_phone, __lang_order_input_rphone__);
					}
				}
			}
			if(is_array($addressee_phone)){
				checkBlank($addressee_cell[0], __lang_order_input_rcell__);
				checkBlank($addressee_cell[1], __lang_order_input_rcell__);
				checkBlank($addressee_cell[2], __lang_order_input_rcell__);
			}else{
				checkBlank($addressee_cell, __lang_order_input_rcell__);
			}
		}

		checkBlank($addressee_zip, __lang_order_input_rzip__);
		checkBlank($addressee_addr1,__lang_order_input_raddr1__);
		checkBlank($addressee_addr2, __lang_order_input_raddr1__);
	}

	$milage_prc = numberOnly($_POST['milage_prc'], true);
	$emoney_prc = numberOnly($_POST['emoney_prc'], true);

	if($cfg['delivery_fee_type'] == "O" || $cfg['delivery_fee_type'] == "A") {
		if($addressee_cell_code) $addressee_cell = $addressee_cell_code.'-'.$addressee_cell;
		if($addressee_phone_code) $addressee_phone = $addressee_phone_code.'-'.$addressee_phone;

		if($buyer_cell_code) $buyer_cell = $buyer_cell_code.'-'.$buyer_cell;
		if($buyer_phone_code) $buyer_phone = $buyer_phone_code.'-'.$buyer_phone;
	}

	if(is_array($buyer_cell)) $buyer_cell=addBar($buyer_cell);
	if(is_array($buyer_phone)) $buyer_phone=addBar($buyer_phone);
	if(is_array($addressee_cell)) $addressee_cell=addBar($addressee_cell);
	if(is_array($addressee_phone)) $addressee_phone=addBar($addressee_phone);
	$buyer_cell = addslashes($buyer_cell);
	$buyer_phone = addslashes($buyer_phone);
	$addressee_cell = addslashes($addressee_cell);
	$addressee_phone = addslashes($addressee_phone);

	$dlv_memo=addslashes(del_html($_POST['dlv_memo']));
	$mail_send = $_POST['mail_send'] == 'Y' ? 'Y' : 'N';

	checkBlank($pay_type, __lang_order_select_paytype__);

	// 쿠폰 사용환경점검
	if($cfg['order_cpn_paytype'] == 2 && $pay_type != 2 && ($off_cpn_use == "Y" || $coupon)) msg(__lang_cpn_error_bankcpn__);

	// 적립금 사용환경점검
	if($cfg['order_milage_paytype'] == 2 && $pay_type != 2 && $milage_prc > 0) msg(__lang_order_error_bankMileage__);

	// 할인제한점검
	if($cfg['order_cpn_milage'] == 2 && ($off_cpn_use == "Y" || $coupon) && $milage_prc > 0) msg(__lang_order_error_cpnMileage__);

	// 오프라인 쿠폰 체크
	$_member = false;
	if($member['no']) $_member = true;
	if($_member && $off_cpn_use == "Y"){
		if($cpn_auth_code) $offcpn = offCouponAuth($cpn_auth_code);
		if($offcpn) $coupon = ""; // 오프라인쿠폰이 인증되면 일반쿠폰은 사용이 안되도록 없애기
		if($off_cpn_pay_type == 2 && $pay_type != 2) msg(__lang_cpn_error_bankcpn__);
	}

	// 온라인 쿠폰체크
	if($member['no'] && $coupon) {
		$nowYmd = date("Y-m-d", $now);
		$cpn = $pdo->assoc("select d.*,  c.attachtype,c.attach_items from `$tbl[coupon_download]` as d inner join `$tbl[coupon]` as c on d.cno=c.no where d.`member_no`='$member[no]' and d.`ono`='' and d.`no`='$coupon' and ((d.`udate_type`='2' and d.`ustart_date`<='$nowYmd' and d.`ufinish_date`>='$nowYmd') or d.`udate_type`='1' or (d.`udate_type`='3' and d.`ufinish_date` >= '$nowYmd'))");
		if($cpn['stype'] == 3) $cpn['sale_prc'] = deliveryPrc(); // 무료배송 쿠폰
		if(!$cpn['no']) msg(__lang_cpn_error_cannotUse__);
		if($cpn['pay_type'] == 2 && $pay_type != 2) msg(__lang_cpn_error_bankcpn__);

		$cpn_no = $cpn['no'];
	}

	// 주문서 추가 정보
	$add_info_file=$root_dir."/_config/order.php";
	if(is_file($add_info_file)) {
		include_once $add_info_file;
		$total_add_info=count($_ord_add_info);
		if($total_add_info>0) {
			$aisql1=$aisql2="";
			foreach($_ord_add_info as $key=>$val) {
				$add_val = $_POST['add_info'.$key];
				if($val['ncs']=="Y" && $add_val=="") {
					msg(sprintf(__lang_member_input_required__, $val['name']));
				}

				addField($tbl['order'],"add_info".$key,"varchar(100) NULL");
				$aisql1.=",`add_info".$key."`";
				if($val['type']=="checkbox" && is_array($add_val) > 0){
					$_addval="@";
					foreach($add_val as $key2=>$val2){
						$_addval .= $val2."@";
					}
					$add_val=$_addval;
				}
				else if($val['type']=="date" && $val['format']==2){
					if($_POST["add_info".$key."_h"]!=""){
						$add_val = $add_val." ".$_POST["add_info".$key."_h"]."시";
					}
				}

				$aisql2.=",'".addslashes(trim(strip_tags($add_val)))."'";
			}
		}
	}

	// 장바구니 체크
	$mwhere = mwhere("c.");

	$cart_check = $pdo->row("select count(*) from `$tbl[cart]` where 1 ".mwhere());
	if(!$cart_check) msg(__lang_shop_error_nocart2__, $root_url.'/shop/cart.php', 'parent');

	// 적립금 결제
	if($milage_prc > 0 && $cfg['milage_use'] == 1 && $member['no']) {
		if($member['milage'] < $cfg['milage_use_min']) msg(sprintf(__lang_order_error_minMileage__, $cfg['milage_use_min']));
		if($milage_prc > $member['milage']) msg(sprintf(__lang_order_error_userMileage__, $member['milage']));
	} else {
		$milage_prc=0;
	}

	// 예치금 결제
	if($emoney_prc > 0 && $member['no']) {
		if($emoney_prc > $member['emoney']) msg(sprintf(__lang_order_error_userEmoney__, $member['emoney']));

		$apsql1 = ",`emoney_prc`";
		$apsql2 = ",'$emoney_prc'";
	} else {
		$emoney_prc=0;
	}

	// 사은품 체크
	if($cfg['order_gift_multi'] != 'Y') $cfg['order_gift_multi_ea'] = 1;
	$gift_ea = (is_array($_POST['gift']) == true) ? count($_POST['gift']) : 0;
	if($gift_ea > 1 && $cfg['order_gift_multi_ea'] > 0 && $gift_ea > $cfg['order_gift_multi_ea']) {
		msg(sprintf(__lang_order_gift_ea__, $cfg['order_gift_multi_ea']));
	}

	// 주문번호 생성
	unset($ono);
	if(!$ono) {
		function makeOrdNo() {
			global $now,$tbl,$member,$onow, $pdo;
			$ono1=date("Ymd",$now);

			$mr=mt_rand();
			if(!$onow) {
				$onow=$now;
			}
			$onow++;

			$ono2=strtoupper(substr(md5($now+$mr+$member['no']),1,5));
			$tmp=$pdo->row("select `no` from `$tbl[order_no]` where `ono1`='$ono1' and `ono2`='$ono2'");
			if($tmp) {
				return false;
			}
			else {
				$pdo->query("insert into `$tbl[order_no]` (`ono1`,`ono2`) values ('$ono1','$ono2')");
				return $ono1."-".$ono2;
			}
		}

		include_once $engine_dir."/_manage/manage2.lib.php";

		while(!$ono) {
			$ono=makeOrdNo();
		}
	}

    startOrderLog($ono, 'order.exe.php');

	$cart_where = $_POST['cart_where'];
	$ptnOrd = new OrderCart();
	if($cpn) $ptnOrd->setCoupon($cpn);
	if($offcpn) $ptnOrd->setCoupon($offcpn);
	while($cart = cartList(null, null, null, null, null, null, $cart_where)) {
		$ptnOrd->addCart($cart);

        makePGLog($ono, 'cartdata', print_r($cart, true));

        if (!isset($cart['partner_no'])) $cart['partner_no'] = 0;
        $check = checkDeliveryRange($addressee_addr1, (int) $cart['partner_no']);
        if ($check[0] == false) {
            msg(php2java($check[1]));
        }
	}
	$ptnOrd->complete();

	// 장바구니 체크, 결제금액 계산
	$real_order_mode = true;
	$cart_del_where = '';
	$member_total_prc = $event_total_prc=0;
	$order_product_no = $prd_log = array();
	$tot_buy_ea = 0;

	addField($tbl['order_product'], 'event_milage', 'int(5) not null default "0" after member_milage');

	if(fieldExist($tbl['order'], 'sale7') == false) {
		unset($_order_sales['sale7']);
	}

	// 쿠폰 및 적립금 사용 불가 상품 체크
	if($ptnOrd->getData('no_milage') > 0 && $milage_prc > 0) {
		msg('적립금을 사용할수 없는 주문서입니다.');
	}
	if($ptnOrd->getData('no_cpn') > 0 && ($cpn_no > 0 || $offcpn['no'] > 0)) {
		msg('쿠폰을 사용할수 없는 주문서입니다.');
	}

	while($obj = $ptnOrd->loopCart()) {
		$cart = $obj->data;

        checkMaxOrd($cart, $cart['buy_ea'], $cart['cno']);

        if ($cart['option_idx']) { // 숨김 옵션, 삭제된 옵션 체크
            $opt_idx = explode('<split_big>', $cart['option_idx']);
            $opt_nm = explode('<split_big>', $cart['option']);
            foreach ($opt_idx as $_idx => $tmp) {
                list($opno, $ino) = explode('<split_small>', $tmp);
                list($opname, $iname) = explode('<split_small>', $opt_nm[$_idx]);
                if ($ino > 0) {
                    $optionitem = $pdo->assoc("select no, hidden from {$tbl['product_option_item']} where no=?", array($ino));
                    if ($optionitem['hidden'] == 'Y') {
                        msg(__lang_shop_error_soldoutOption__."\\n".$cart['name']."\\n{$opname} : {$iname}");
                    }
                    if ($optionitem == false) {
                        msg(__lang_shop_error_unregistOption__);
                    }
                }
            }
        }

        if ($_GET['is_payco'] == 'true' && $cart['set_idx']) {
            msg('세트상품은 페이코로 구매할수 없습니다.');
        }

        if ($obj->getData('sum_sell_prc') < 0) {
            $pdo->query("delete from {$tbl['order']} where ono='$ono'");
            msg("할인 금액이 너무 커서 상품 금액이 0원 미만인 상품이 있습니다.\\n할인 혜택을 조정해주세요.");
        }

        if ($pay_type == '12') {
            if ($cart['sell_prc'] > 5000000) {
                msg('500만원을 초과하는 상품은 카카오페이로 결제할 수 없습니다.');
            }
        }

        if($cart['stat'] == '4') msg(sprintf(__lang_shop_error_nopurchase__)); //숨김상품인 경우
		if($cart['stat'] != 2) msg(sprintf(__lang_shop_error_soldout__, $cart['name']));

		$prd_log[] = $cart; // 주문서 파일 로그
		$tot_buy_ea += $cart['buy_ea'];
		$cart_del_where .= " or `no`='$cart[cno]'";
		if(!$cart['sum_sell_prc']) {
			$cart['sum_sell_prc'] = $cart['sell_prc']*$cart['buy_ea'];
		}

		if(!$title) {
			$title = $cart['name'].' ('.$cart['buy_ea'].')';
			$title_code = ($cart['code']) ? $cart['code'] : $cart['hash']; // allat, inicis
			if($cart['upfile3']) $title_img = $root_url.'/'.$cart['updir']."/".$cart['upfile3']; // allat

            $representativ_product_name = strip_tags($cart['name']);
            $representativ_product_code = $cart['hash'];
		}

		if($pay_type == '4') { // kcp 가상계좌
			$chr31 = chr(31);
			$chr30 = chr(30);

			if($c_idx <= 20) {
				$c_idx++;
				$good_info .= 'seq='.$c_idx.$chr31.'ordr_numb='.$ono.$chr31.'good_name='.cutStr(inputText($cart['name']), 30, '').'good_cntx='.$cart['buy_ea'].$chr31.'good_amtx='.$cart['sell_prc'].$chr30;
			}
		}

		$_stat = ($pay_type == 2 || $pay_type == 3 || $pay_type == 6) ? 1 : 11;
		$cart['option'] = addslashes($cart['option']);
		$cart['etc'] = addslashes($cart['etc']);

		$pasql1 = $pasql2 = '';
		if($cfg['use_partner_shop'] == 'Y') {
			$pasql1 = ", partner_no, fee_rate, fee_prc, dlv_type, cpn_rate, cpn_fee";
			$pasql2 = ", '$cart[partner_no]', '$cart[partner_rate]', '$obj->fee_prc', '$cart[dlv_type]', '$obj->cpn_promo_rate', '$obj->cpn_promo_fee'";
		}

		foreach($_order_sales as $key => $val) {
			$tmp = numberOnly($obj->{$key}, true);
			if($tmp) {
				$pasql1 .= ", $key";
				$pasql2 .= ", '$tmp'";
			}
		}

		if($cart['prdcpn_no']) {
			$prdcpn_no = str_replace('@', ',', trim($cart['prdcpn_no'], '@'));
			$pasql1 .= ", prdcpn_no";
			$pasql2 .= ", '$prdcpn_no'";
		}

		if($cfg['use_prd_dlvprc'] == 'Y') { // 개별 배송비
			$prd_dlv_prc = $obj->getData('prd_dlv_prc');
			$pasql1 .= ", prd_dlv_prc";
			$pasql2 .= ", '$prd_dlv_prc'";
		}

		if ($scfg->comp('use_set_product', 'Y') == true && $cart['set_pno'] && $cart['set_idx']) {
			$pasql1 .= " ,set_idx, set_pno";
			$pasql2 .= " ,'{$cart['set_idx']}', '{$cart['set_pno']}'";

            $has_set = 'Y';

            $set_stat = $pdo->row("select stat from {$tbl['product']} where no=?", array($cart['set_pno']));
            if ($set_stat != '2') {
                msg(__lang_common_error_nosaleprd__);
            }
		}

		$psql  = "INSERT INTO `$tbl[order_product]` (`ono`, `pno`, `name`, `sell_prc`, `milage`, `buy_ea`, `total_prc`, `total_milage`, `member_milage`, event_milage, `option`, `option_prc`, `option_idx`, `complex_no`, `prd_type`, `etc`, `etc2`, stat, r_name, r_zip, r_addr1, r_addr2, r_phone, r_cell, r_message $pasql1) ";
		$psql .= "VALUES ('$ono', '$cart[pno]', '".addslashes($cart['name'])."', '$cart[sell_prc]', '$cart[milage]', '$cart[buy_ea]', '$cart[sum_sell_prc]', '$cart[total_milage]', '$cart[member_milage]', '$cart[event_milage]', '$cart[option]','$cart[option_prc]', '$cart[option_idx]', '$cart[complex_no]', '$cart[prd_type]', '$cart[etc]', '$cart[etc2]', '$_stat', '$addressee_name', '$addressee_zip', '$addressee_addr1', '$addressee_addr2', '$addressee_phone', '$addressee_cell', '$dlv_memo' $pasql2)";
		$r = $pdo->query($psql);
		if (!$r) {
			msg('주문처리 중 오류가 발생하였습니다.');
		}

		$opno = $pdo->lastInsertId();
		$order_product_no[$cart['cno']] = $opno;

		// wingPos 품절체크
		if($cart['ea_type'] == 1 && $cart['complex_no']) {
			$_buy_ea_complex[$cart['complex_no']] += $cart['buy_ea']; // 커스터마이징, 텍스트옵션 등의 사유로 동일한 SKU가 한 장바구니에 같이 있을 경우
			if($ret = stockCheck($cart['complex_no'], $_buy_ea_complex[$cart['complex_no']], $cart['name'])) {
				$pdo->query("delete from $tbl[order_product] where ono = '$ono'");
				javac("parent.order2.style.display='block'");
				msg($ret);
			}
		}
	}

	if(count($order_product_no) < -1) {
		msg(__lang_shop_error_nocart2__);
	}

	if($cpn['no'] > 0 || $offcpn['no'] > 0) {
		include_once $engine_dir.'/_engine/order/coupon_attach_check.exe.php';
		if($cpn['stype'] == 3) $cpn['sale_prc'] = $delivery_prc;
	}

	$title = addslashes(makeOrderTitle($ono));

	$buy_ea = $tot_buy_ea;

	$prd_prc = (int) $ptnOrd->getData('sum_prd_prc');
	$dlv_prc = (int) $ptnOrd->getData('dlv_prc');
	$tax_prc = (int) $ptnOrd->getData('tax_prc');
	$total_sale = (int) $ptnOrd->getData('total_sale');

	$total_prc = $ptnOrd->getData('total_order_price'); // 총 결제액
	$pay_prc = $ptnOrd->getData('pay_prc');
	$taxfree_amount = parsePrice($ptnOrd->getData('taxfree_amount'));

    // 페이코 결제시 결제금액이 0원이면 결제 불가
    if($pay_prc == 0 && $_GET['is_payco'] == 'true') {
        msg('결제 금액이 없을 경우 페이코 결제방식을 선택할 수 없습니다.');
    }

	if($pay_type == 2 && $cfg['cash_receipt_use'] == 'Y' && $cfg['cash_receipt_ness'] == 'Y' && !$_POST['cash_reg_num']) {
		if($cfg['cash_receipt_nessprc'] <= $pay_prc) {
			$_POST['cash_reg_num'] = "0100001234";
		}
	}

	// 적립금, 예치금 결제
	$total_emoneys = $milage_prc + $emoney_prc;
	$pay_prc_no_point = $pay_prc+$ptnOrd->milage_prc+$ptnOrd->emoney_prc;
	if($total_emoneys > $pay_prc_no_point) msg(sprintf(__lang_order_error_maxEmoneys__, $pay_prc_no_point, $total_emoneys));

    // 총 상품 금액 중 적립금 사용 비율 더블 체크
    if ($scfg->comp('milage_use_max_type', '2') == true && $milage_prc > 0 && isset($cfg['milage_use_max_per']) == true) {
        $milage_use_max_per = (int) $cfg['milage_use_max_per'];
        if ($milage_use_max_per > 0) {
            $max_milage = round($prd_prc*($milage_use_max_per/100));

            if ($milage_prc > $max_milage) {
                msg(sprintf(__lang_order_error_maxMileage__, $max_milage));
            }
        }
    }

	// 전체를 적립금이나 예치금으로 결제시
	if($pay_prc == 0) {
		if($emoney_prc > 0) $pay_type = 6;
		else $pay_type = 3;
	}

	if($pay_type==1 || $pay_type==4 || $pay_type==5 || $pay_type==7 || $pay_type==10 || $pay_type==11 || $pay_type==12 || $pay_type==13 || $pay_type==14 || $pay_type==15 || $pay_type==16 || $pay_type==17 || $pay_type==18 || $pay_type==19 || $pay_type==20 || $pay_type==21 || $pay_type==22 || $pay_type==25 || $pay_type==27 || $pay_type==28) {
		$stat=11;
	}
	elseif($pay_type==2) {
		$bank = addslashes(trim($_POST['bank']));
		$bank_name = addslashes(trim($_POST['bank_name']));
		$stat=1;
		checkBlank($bank, __lang_order_error_noBankdata__);
		$_bank=get_info($tbl['bank_account'],"no",$bank);
		if(!$_bank['no']) msg(__lang_order_error_noBankdata__);
		$bank="$_bank[bank] $_bank[account] $_bank[owner]";
	}
	elseif($pay_prc==0) {
		$stat=2;
		$date2=$now;
	}

	if($pay_type != 2) {
		unset($bank_name, $bank, $_POST['cash_reg_num']);
	}

	addField($tbl['order'], "mobile", "enum('Y','N','A') default 'N' not null after `ono`");
	addField($tbl['order'], "delivery_com", "int comment '배송업체(해외배송 사용시)'");
	addField($tbl['order'], 'sale2_dlv', "double(8,2) comment '이벤트 무료배송'");
	addField($tbl['order'], 'sale4_dlv', "double(8,2) comment '회원 무료배송'");

	$adrsql1=$adrsql2="";

	if($addressee_addr4){
		addField($tbl['order'], 'addressee_addr4', "varchar(150) after addressee_addr2");
		$adrsql1 = ", addressee_addr4";
		$adrsql2 = ", '$addressee_addr4'";
	}
	if($addressee_addr3){
		addField($tbl['order'], 'addressee_addr3', "varchar(150) after addressee_addr2");
		$adrsql1 .= ", addressee_addr3";
		$adrsql2 .= ", '$addressee_addr3'";
	}

	$adfldsql1=$adfldsql2="";
	if($_POST['addressee_id'] && $cfg['order_add_field_use']=='Y' && fieldExist($tbl['order'],'addressee_id')){
		$adfldsql1 .= ", `addressee_id`";
		$adfldsql2 .= ", '".addslashes($_POST['addressee_id'])."'";
	}

	if($cfg['delivery_fee_type'] == "O" || $cfg['delivery_fee_type'] == "A"){
		addField($tbl['order'], 'tax', "double(10,2) default '0.00'");

		if(fieldExist($tbl['order'],'tax')){
			$aisql1 .= ", tax";
			$aisql2 .= ", '$tax_prc'";
		}
	}

	foreach($_order_sales as $key => $val) {
		$tmp = ${$key} = numberOnly($ptnOrd->{$key}, true);
		if($tmp) {
			$aisql1 .= ", `$key`";
			$aisql2 .= ", '$tmp'";
		}
	}

    if (isset($has_set) == true) {
        $aisql1 .= ", has_set";
        $aisql2 .= ", '$has_set'";
    }

	$mobile_payment = ($_SESSION['browser_type'] == "mobile") ? "Y" : "N";
	if($_COOKIE['wisamall_access_device'] == 'APP') $mobile_payment = 'A';

	$ord = $pdo->assoc("select `no` from `$tbl[order]` where `ono`='$ono'");
	if(!$ord['no']) {
		if($nations && ($cfg['delivery_fee_type'] == "O" || $cfg['delivery_fee_type'] == "A")) {
			$aisql1 .= ", `nations`, `cart_weight`, `delivery_com`";
			$aisql2 .= ", '".addslashes($nations)."', '".($cart_weight+$cfg['ems_box_weight'])."','".$delivery_com."'";
		}

		if($cfg['use_prd_dlvprc'] == 'Y') { // 상품별 개별 배송비
			$prd_dlv_prc = $ptnOrd->getData('prd_dlv_prc');
			$aisql1 .= ", prd_dlv_prc";
			$aisql2 .= ", '$prd_dlv_prc'";
		}

        $isChkPrd = $pdo->row("select count(*) from {$tbl['order_product']} where ono='$ono'");
        if ($isChkPrd == 0) {
            msg('주문처리 중 오류가 발생하였습니다.');
        }
		$sql = "INSERT INTO `$tbl[order]` (`ono`, `mobile`, `date1`, `date2`, `stat`, `member_no`, `member_id`,`buyer_name`, `buyer_email`, `buyer_phone`, `buyer_cell`, `addressee_name`, `addressee_phone`, `addressee_cell`, `addressee_zip`, `addressee_addr1`, `addressee_addr2`, `dlv_memo`, `mng_memo`, `total_prc`, `milage_prc`, `pay_prc`, `prd_prc`, `dlv_prc`, `pay_type`, `bank`, `milage_down`, `milage_down_date`, `title`, `dlv_type`, `sms`, `mail_send`, `ip`, sale2_dlv, sale4_dlv, `extra1`, `total_milage`, `member_milage`, `dlv_date`, `tax_receipt`, `bank_name`, `cart_where`, `prd_nums`, `free_delivery`, `conversion` $afsql1 $apsql1 $aisql1 $adrsql1 $adfldsql1) ";
		$sql .= "VALUES ('$ono', '$mobile_payment', '$now', '$date2', '$stat', '$member[no]', '$member[member_id]', '$buyer_name', '$buyer_email', '$buyer_phone', '$buyer_cell', '$addressee_name', '$addressee_phone', '$addressee_cell', '$addressee_zip', '$addressee_addr1', '$addressee_addr2', '$dlv_memo', '$mng_memo', '$total_prc', '$milage_prc', '$pay_prc', '$prd_prc', '$ptnOrd->dlv_prc', '$pay_type', '$bank', '$milage_down', '$milage_down_date', '$title', '$dlv_type', '$sms', '$mail_send', '$_SERVER[REMOTE_ADDR]', '$ptnOrd->sale2_dlv', '$ptnOrd->sale4_dlv', '$extra1', '$ptnOrd->total_milage', '$ptnOrd->member_milage', '$dlv_date', '$tax_receipt', '$bank_name', '$cart_where', '$cart_selected', '$free_delivery', '$_SESSION[conversion]' $afsql2 $apsql2 $aisql2 $adrsql2 $adfldsql2)";

		$r = $pdo->query($sql);
		if($r) ordStatLogw($ono, $stat, "Y");
		else {
			msg('주문처리 중 오류가 발생하였습니다.');
		}

		ordChgPart($ono, true);
	}

	// 사은품 저장
	$gift_timing = 'order';
	include 'order_gift_select.exe.php';

	// 업체별 배송비 저장
	if($cfg['use_partner_delivery'] == 'Y') {
		if(!isTable($tbl['order_dlv_prc'])) {
			include_once $engine_dir.'/_config/tbl_schema.php';
			$pdo->query($tbl_schema['order_dlv_prc']);
		}
		addField($tbl['order_dlv_prc'], 'first_prc', "int(10) NOT NULL comment '업체별 주문 첫 배송비'");
		$ptns = $ptnOrd->getData('ptns');
		foreach($ptns as $val) {
			$ptn_no = $val->getData('partner_no');
			$ptn_dlv_prc = $val->getData('dlv_prc');
			$pdo->query("insert into $tbl[order_dlv_prc] (ono, partner_no, dlv_prc, first_prc) values ('$ono', '$ptn_no', '$ptn_dlv_prc', '$ptn_dlv_prc')");
		}
	}

	// 현금영수증
	$cash_reg_num = numberOnly($_POST['cash_reg_num']);
	if ($cfg['cash_receipt_use'] == "Y" && $cash_reg_num && $pay_prc > 0 && $pay_type==2) {
		$amt1 = $pay_prc; // 현금결제액
		if($amt1 > 0) {
            require_once __ENGINE_DIR__.'/_engine/include/migration/cfg_cash_receipt_taxfree.inc.php';

			$amt4 = round(($pay_prc-$taxfree_amount)/11); // 부가세
			$amt3 = 0; // 봉사료
			$amt2 = ($amt1-$amt4); // 공급가액

			$prod_name = cutStr(strip_tags($title),28); // 상품명
			$cons_name = $buyer_name; // 주문자명
			$cons_tel = $buyer_cell; // 전화번호
			$cons_email = $buyer_email; // 이메일

			$sql_cash = "insert into $tbl[cash_receipt] set
						`ono`			= '$ono',
						`cash_reg_num`	= '$cash_reg_num',
						`pay_type`		= '$pay_type',
						`reg_date`		= $now,
						`amt1`			= '$amt1',
						`amt2`			= '$amt2',
						`amt3`			= '$amt3',
						`amt4`			= '$amt4',
                        taxfree_amt     = '$taxfree_amount',
						`b_num`			= '".numberOnly($cfg['company_biz_num'])."',
						`prod_name`		= '$prod_name',
						`cons_name`		= '$cons_name',
						`cons_tel`		= '$cons_tel',
						`cons_email`	= '$cons_email'
					";
			@$pdo->query($sql_cash);
            cashReceiptLog(array(
                'cno' => $pdo->lastInsertId(),
                'ono' => $ono,
                'stat' => 1,
                'ori_stat' => 0,
                'system' => 'Y'
            ));
		}
	}

	$payment_no = createPayment(array(
		'type' => 0,
		'ono' => $ono,
		'pno' => $order_product_no,
		'pay_type' => $pay_type,
		'amount' => $pay_prc,
		'bank' => $bank,
		'bank_name' => $bank_name,
		'dlv_prc' => $dlv_prc,
		'emoney_prc' => $emoney_prc,
		'milage_prc' => $milage_prc,
		'cpn_no' => $cpn_no.$offcpn['no'],
	), 1);

	// 배송지 정보를 회원 개인 정보로 저장
	if($_POST['save_addr'] == 'Y' && $member['no'] > 0) {
		$phone = $addressee_phone;
		$cell = $addressee_cell;
		$zip = $addressee_zip;
		$addr1 = $addressee_addr1;
		$addr2 = $addressee_addr2;

		$pdo->query("
			update $tbl[member] set phone='$phone', cell='$cell', zip='$zip', addr1='$addr1', addr2='$addr2'
			where no='$member[no]' and member_id='$member[member_id]'
		");
	}

    // 주소록 정보 저장
    if ($member['no'] > 0 && ($addr_add == 'Y' || $addr_update > 0) ) {
        // 차후 배송지 관리 기능 생기면 삭제하고 '현재 주문 주소를 주소록에 추가' 형태로 변경
        $address_idx = memberAddressSet(
            $addressee_title, 'order', $addressee_name, $addressee_phone, $addressee_cell,
            $addressee_zip, $addressee_addr1, $addressee_addr2, $addressee_addr3, $addressee_addr4, '', (int) $addr_update, $nations
        );
        // 현재 주소를 기본배송지로 지정
        if (isset($_POST['addr_default']) && $_POST['addr_default'] == 'Y') {
            memberAddressDefault($address_idx, $nations);
        }
    }

	include_once $engine_dir.'/_engine/order/order_paytype.exe.php';

	if($stat == 11) include_once $engine_dir."/_engine/card.{$card_pg}/{$pg_version}card_pay.php";
	else include_once $engine_dir."/_engine/order/order2.exe.php";

	close();

?>