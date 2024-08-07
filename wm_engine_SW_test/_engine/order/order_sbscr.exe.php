<?PHP
	/* +----------------------------------------------------------------------------------------------+
	' |  주문서 저장
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/shop.lib.php";
	include_once $engine_dir."/_engine/include/milage.lib.php";
	include_once $engine_dir.'/_engine/include/wingPos.lib.php';
	include_once $engine_dir."/_plugin/subScription/sbscr.lib.php";
    include_once __ENGINE_DIR__.'/_engine/include/MemberAddress.lib.php';


	$layer1 = 'order1';
	$layer2 = 'order2';
	$layer3 = 'order3'; // 주문3.0

	$nations = $_POST['nations'] ? addslashes($_POST['nations']) : $member['nations'];
	$delivery_com = numberOnly($_POST['delivery_com']);
	$delivery_fee_type = $_POST['delivery_fee_type'];
	$pay_type = numberOnly($_POST['pay_type']);
	$buyer_name = addslashes(trim($_POST['buyer_name']));
	$buyer_phone = $_POST['buyer_phone'];
	$buyer_cell = $_POST['buyer_cell'];
	$buyer_email = addslashes(trim($_POST['buyer_email']));
    $addressee_title = addslashes(trim($_POST['addr_name']));
	$addressee_name = addslashes(trim($_POST['addressee_name']));
	$addressee_phone = $_POST['addressee_phone'];
	$addressee_cell = $_POST['addressee_cell'];
	$addressee_zip = addslashes(trim($_POST['addressee_zip']));
	$addressee_addr1 = addslashes(trim($_POST['addressee_addr1']));
	$addressee_addr2 = addslashes(trim($_POST['addressee_addr2']));
	$addressee_addr3 = addslashes(trim($_POST['addressee_addr3']));
	$addressee_addr4 = addslashes(trim($_POST['addressee_addr4']));
	$coupon = $_POST['coupon'];
	$cpn_auth_code = addslashes(trim($_POST['cpn_auth_code']));
	$cart_selected = $_POST['cart_selected'];
	$off_cpn_use = $_POST['off_cpn_use'];
	$sms = ($_POST['sms'] == 'Y') ? 'Y' : 'N';
	$sbscr_all = addslashes($_POST['sbscr_all']);
	$sbscr_ymd = numberOnly($_POST['sbscr_ymd']);

    $addr_add = ($_POST['addr_add'] == 'Y' ? 'Y' : "N");
    $addr_update = numberOnly($_POST['addr_update']);
    $addr_default = ($_POST['addr_default'] == 'Y' ? 'Y' : "N");

	$pay_sbscr = 'N';
	if($sbscr=='Y' && $sbscr_all=='N' && $pay_type != 27) {
		$pay_type = 23;
		$pay_sbscr = 'Y';
	}

	if($easy_pay_direct_order != true){
		checkBlank($buyer_name, __lang_order_input_oname__);

		if($_use['nec_buyer_phone'] != 'S'){
			if($cfg['use_order_phone'] != 'N') {
				if($cfg['delivery_fee_type'] == 'D' || $_POST['delivery_fee_type'] == 'D') {
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

		if($_use[nec_buyer_email]!='N') checkBlank($buyer_email, __lang_order_input_oemail__);

		checkBlank($addressee_name, __lang_order_input_rname__);

		if($_use['nec_addressee_phone'] == 'S'){
			if($cfg['use_order_phone'] != 'N') {
				if($cfg['delivery_fee_type'] == "D" || $_POST['delivery_fee_type'] == "D") {
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

	$dlv_memo=addslashes(del_html($_POST['dlv_memo']));
	$mail_send = $_POST['mail_send'] == 'Y' ? 'Y' : 'N';

	checkBlank($pay_type, __lang_order_select_paytype__);

	// 주문서 추가 정보
	$add_info_file=$root_dir."/_config/order.php";
	if(is_file($add_info_file)) {
		include_once $add_info_file;
		$total_add_info=count($_ord_add_info);
		if($total_add_info>0) {
			$aisql1=$aisql2="";
			foreach($_ord_add_info as $key=>$val) {
				$add_val = $_POST['add_info'.$key];
				if($val[ncs]=="Y" && $add_val=="") {
					msg(sprintf(__lang_member_input_required__, $val['name']));
				}

				addField($tbl[order],"add_info".$key,"varchar(100) NULL");
				$aisql1.=",`add_info".$key."`";
				if($val[type]=="checkbox" && sizeof($add_val) > 0){
					$_addval="@";
					foreach($add_val as $key2=>$val2){
						$_addval .= $val2."@";
					}
					$add_val=$_addval;
				}
				else if($val[type]=="date" && $val[format]==2){
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

	$cart_check = $pdo->row("select count(*) from `$tbl[sbscr_cart]` where 1 ".mwhere());
	if(!$cart_check) msg(__lang_shop_error_nocart2__, $root_url.'/shop/cart.php?sbscr=Y', 'parent');

	// 사은품 체크
	if($cfg['order_gift_multi'] != 'Y') $cfg['order_gift_multi_ea'] = 1;
	$gift_ea = count($_POST['gift']);
	if($gift_ea > 1 && $cfg['order_gift_multi_ea'] > 0 && $gift_ea > $cfg['order_gift_multi_ea']) {
		msg(sprintf(__lang_order_gift_ea__, $cfg['order_gift_multi_ea']));
	}

	// 정기주문번호 생성
	unset($sno);
	if(!$sno) {
		function makeOrdsubNo() {
			global $tbl, $now, $pdo;

			$sno1 = 'SS'.date("ymd",$now);

			$mr=mt_rand();
			if(!$onow) {
				$onow=$now;
			}
			$onow++;

			$sno2=strtoupper(substr(md5($now+$mr+$member[no]),1,5));
			$sno = $sno1."-".$sno2;
			$tmp=$pdo->row("select `no` from `$tbl[sbscr]` where `sbono`='$sno'");
			if($tmp) {
				return false;
			}
			else {
				return $sno;
			}
		}

		while(!$sno) {
			$sno=makeOrdsubNo();
		}
	}

    startOrderLog($sno, 'order_sbscr.exe.php');

	$cart_where = $_POST['cart_where'];
	$ptnOrd = new OrderCart();
	$sbscr_yn = 'Y';
	$all_pass = '';
	while($cart = cartList("", "", "", "", "", "", $cart_where)) {
		if($sbscr_all=='Y' && $cart['end_date']==0) $all_pass = 'Y';
        if ($pay_type == '12' && strtotime('+2 months', strtotime(date('Y-m-d'))) <= $cart['end_date']) {
            msg('배송 기간이 2개월 이상인 상품은 카카오페이로 결제할 수 없습니다.');
        }
		$ptnOrd->addCart($cart);
	}
	$ptnOrd->complete();

	if($all_pass=='Y') msg("종료일이 없는 상품은 일괄결제가 불가능합니다.");

	// 장바구니 체크, 결제금액 계산
	$real_order_mode = true;
	$cart_del_where = '';
	$order_product_no = $prd_log = array();
	$tot_buy_ea = 0;

	while($obj = $ptnOrd->loopCart()) {
		$cart = $obj->data;

		if($cart['stat'] != 2) msg(sprintf(__lang_shop_error_soldout__, $cart['name']));

		$prd_log[] = $cart; // 주문서 파일 로그
		$tot_buy_ea += $cart['buy_ea'];
		$cart_del_where .= " or `no`='$cart[cno]'";
		if(!$cart['sum_sell_prc']) {
			$cart['sum_sell_prc'] = $cart['sell_prc']*$cart['buy_ea'];
		}
		$cart['milage'] += $obj->getData('sbscr_member_milage')+$obj->getData('sbscr_event_milage');

		if(!$title) {
			$title = $cart['name'].' ('.$cart['buy_ea'].')';
			$title_code = ($cart['code']) ? $cart['code'] : $cart['hash']; // allat, inicis
			if($cart['upfile3']) $title_img = $root_url.'/'.$cart['updir']."/".$cart['upfile3']; // allat

            $representativ_product_name = strip_tags($cart['name']);
            $representativ_product_code = $cart['hash'];
		}

		$_stat = ($pay_type == 2 || $pay_type == 3 || $pay_type == 6) ? 1 : 11;
		$cart['option'] = addslashes($cart['option']);

		$pasql1 = $pasql2 = '';
		if($cfg['use_partner_shop'] == 'Y') {
			$pasql1 = ", partner_no, fee_rate, fee_prc";
			$pasql2 = ", '$cart[partner_no]', '$cart[partner_rate]', '$obj->fee_prc'";
		}

		foreach($_order_sales as $key => $val) {
			$tmp = numberOnly($obj->{$key}, true);
			if($tmp) {
				$pasql1 .= ", $key";
				$pasql2 .= ", '$tmp'";
			}
		}

		if($cfg['use_prd_dlvprc'] == 'Y') { // 상품별 개별 배송비
			$prd_dlv_prc = $obj->getData('prd_dlv_prc');
			$pasql1 .= ", prd_dlv_prc";
			$pasql2 .= ", '$prd_dlv_prc'";
		}

		$cart['start_date'] = date('Y-m-d', $cart['start_date']);
		$cart['end_date'] = ($cart['end_date']) ? date('Y-m-d', $cart['end_date']) : '0000-00-00';

        addField($tbl['sbscr_product'], 'period', 'varchar(20) not null');

		$psql  = "INSERT INTO `$tbl[sbscr_product]` (`sbono`, `pno`, `name`, `sell_prc`, `milage`, `buy_ea`, `total_prc`, `total_milage`, `option`, `option_prc`, `option_idx`, `complex_no`, `dlv_start_date`, `dlv_finish_date`, `dlv_total_cnt`, period, `dlv_week`, `stat`, cno, `stop` $pasql1) ";
		$psql .= "VALUES ('$sno', '$cart[pno]', '".addslashes($cart['name'])."', '$cart[sell_prc]', '$cart[milage]', '$cart[buy_ea]', '$cart[sum_sell_prc]', '$cart[total_milage]', '$cart[option]','$cart[option_prc]', '$cart[option_idx]', '$cart[complex_no]', '$cart[start_date]', '$cart[end_date]', '$cart[dlv_cnt]', '$cart[period]', '$cart[week]', '$_stat', '$cart[cno]', 'N' $pasql2)";
		$r = $pdo->query($psql);

		$opno = $pdo->lastInsertId();
		$order_product_no[$cart['cno']] = $opno;
	}

	if(count($order_product_no) < -1) {
		msg(__lang_shop_error_nocart2__);
	}

	if($cart_rows>1) $title.=" 外 ".($cart_rows-1);
	$title=addslashes($title);

	$buy_ea = $tot_buy_ea;

	$prd_prc = $ptnOrd->getData('sum_prd_prc');
	$dlv_prc = $ptnOrd->dlv_prc;
	$tax_prc = $ptnOrd->tax_prc;

	$total_prc = $ptnOrd->getData('total_order_price'); // 총 결제액
	$pay_prc = $ptnOrd->getData('pay_prc');
	$taxfree_amount = parsePrice($ptnOrd->getData('taxfree_amount'));
	$taxfree_amount_sbscr = parsePrice($ptnOrd->getData('taxfree_amount_sbscr'));

    if($pay_type == 2 && $cfg['cash_receipt_use'] == 'Y' && $cfg['cash_receipt_ness'] == 'Y' && !$_POST['cash_reg_num']) {
		if($cfg['cash_receipt_nessprc'] <= $pay_prc) {
			$_POST['cash_reg_num'] = "0100001234";
		}
	}

	if($pay_type==1 || $pay_type==4 || $pay_type==5 || $pay_type==7 || $pay_type==10 || $pay_type==11 || $pay_type==12 || $pay_type==13 || $pay_type==14 || $pay_type==15 || $pay_type==16 || $pay_type==17 || $pay_type==18 || $pay_type==19 || $pay_type==20 || $pay_type==21 || $pay_type==22 || $pay_type == '25') {
		$stat=11;
	}
	elseif($pay_type==2) {
		$bank = addslashes(trim($_POST['bank']));
		$bank_name = addslashes(trim($_POST['bank_name']));
		$stat=1;
		checkBlank($bank, __lang_order_error_noBankdata__);
		$_bank=get_info($tbl['bank_account'],"no",$bank);
		if(!$_bank[no]) msg(__lang_order_error_noBankdata__);
		$bank="$_bank[bank] $_bank[account] $_bank[owner]";
	}
	elseif($pay_prc==0) {
		$stat=2;
		$date2=$now;
	}elseif($pay_type == '23' || $pay_type == '27') {
		$stat=11;
	}

	if($pay_type != 2) {
		unset($bank_name, $bank, $_POST['cash_reg_num']);
	}


	$mobile_payment = ($_SESSION['browser_type'] == "mobile") ? "Y" : "N";
	if($_COOKIE['wisamall_access_device'] == 'APP') $mobile_payment = 'A';

	$s_sale_prc = 0;
	foreach($_order_sales as $key => $val) {
		$tmp = numberOnly($ptnOrd->getData($key), true);
		if($tmp) {
			$s_sale_prc += $tmp;
		}
	}

	$ord = $pdo->assoc("select `no` from `$tbl[sbscr]` where `sbono`='$sno'");
	if(!$ord['no']) {
		$sql = "INSERT INTO `$tbl[sbscr]` (`sbono`, `mobile`, `stat`, `member_no`, `member_id`,`date1`, `date2`, `buyer_name`, `buyer_email`, `buyer_phone`, `buyer_cell`, `addressee_name`, `addressee_phone`, `addressee_cell`, `addressee_zip`, `addressee_addr1`, `addressee_addr2`, `dlv_memo`, `mng_memo`, `pay_type`, `pay_sbscr`, `s_total_prc`, `s_pay_prc`, `s_prd_prc`, `s_dlv_prc`, `s_sale_prc`, `s_total_milage`, `bank`, `bank_name`, `title`, `sms_send`, `mail_send`, `conversion`) ";
		$sql .= "VALUES ('$sno', '$mobile_payment', '$stat', '$member[no]', '$member[member_id]', '$now', '$date2', '$buyer_name', '$buyer_email', '$buyer_phone', '$buyer_cell', '$addressee_name', '$addressee_phone', '$addressee_cell', '$addressee_zip', '$addressee_addr1', '$addressee_addr2', '$dlv_memo', '$mng_memo', '$pay_type', '$pay_sbscr', '$total_prc', '$pay_prc', '$prd_prc', '$ptnOrd->dlv_prc', '$s_sale_prc', '$ptnOrd->total_milage', '$bank', '$bank_name', '$title', '$sms', '$mail_send', '$_SESSION[conversion]')";

		$r = $pdo->query($sql);

		sbscrChgPart($sno);
	}

	// 배송비 테이블
	$ptns = $ptnOrd->getData('ptns');
	foreach($ptns as $ptn) {
		$partner_no = $ptn->getData('partner_no');
		$dlv_info = $ptn->getData('sbscr_dlv_prc');
		$dlv_date = $ptn->getData('sbscr_dlv_date');
		$prd_cnt = $ptn->getData('sbscr_prd_cnt');
		$sbscr_pay_prc = $ptn->getData('sbscr_pay_prc');
		$sbscr_prd = $ptn->getData('sbscr_prd');
        $s_taxfree_amount = $ptn->getData('taxfree_amount');

		foreach($dlv_info as $_date => $_dlv_prc) {
			$_date2 = date('Y-m-d', $_date);
			$prd_prc = $dlv_date[$_date];
			$prd_count = $prd_cnt[$_date];
			$total_prc = $sbscr_pay_prc[$_date]+$_dlv_prc;

			$pdo->query("
				insert into $tbl[sbscr_schedule]
				(`sbono`, `date`, `date_org`, `product_cnt`, total_prc, prd_prc, dlv_prc)
				values
				('$sno', '$_date2', '$_date2', '$prd_count', '$total_prc', '$prd_prc', '$_dlv_prc')
			");

			$schno = $pdo->lastInsertId();
            if ($taxfree_amount > 0) {
                $pdo->query("update {$tbl['sbscr_schedule']} set taxfree_prc='$s_taxfree_amount' where no='$schno'");
            }

			if(is_array($sbscr_prd[$_date])) {
				foreach($sbscr_prd[$_date] as $key=>$val) {
					$spdata = $pdo->assoc("select no, pno from $tbl[sbscr_product] where sbono='$sno' and cno='$key'");
					$pdo->query("
						insert into $tbl[sbscr_schedule_product]
						(`schno`, `sbono`, sbpno, `pno`, partner_no, delivery_type, delivery_base, delivery_free_limit, delivery_fee, `stat`)
						values
						('$schno', '$sno', '$spdata[no]', '$spdata[pno]', '$val[partner_no]', '$val[delivery_type]', '$val[delivery_base]', '$val[delivery_free_limit]', '$val[delivery_fee]', '1')
					");
				}
			}
		}
	}
	$pdo->query("update $tbl[sbscr_product] set cno='0' where sbono='$sno'");

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

	// KCP 현금영수증
	$cash_reg_num = numberOnly($_POST['cash_reg_num']);
	if($cfg[cash_receipt_use] == "Y" && $cash_reg_num && $pay_prc > 0 && $pay_type==2) {

		$amt1 = $pay_prc-$taxfree_amount; // 현금결제액
		if($amt1 > 0) {
			$amt2 = round($pay_prc/1.1); // 공급가액
			$amt3 = 0; // 봉사료
			$amt4 = $amt1-$amt2; // 부가세

			$prod_name = cutStr(strip_tags($title),28); // 상품명
			$cons_name = $buyer_name; // 주문자명
			$cons_tel = $buyer_cell; // 전화번호
			$cons_email = $buyer_email; // 이메일

			$sql_cash = "insert into $tbl[cash_receipt] set
						`ono`			= '$sno',
						`cash_reg_num`	= '$cash_reg_num',
						`pay_type`		= '$pay_type',
						`reg_date`		= $now,
						`amt1`			= '0',
						`amt2`			= '0',
						`amt3`			= '0',
						`amt4`			= '0',
						`b_num`			= '".numberOnly($cfg[company_biz_num])."',
						`prod_name`		= '$prod_name',
						`cons_name`		= '$cons_name',
						`cons_tel`		= '$cons_tel',
						`cons_email`	= '$cons_email'
					";
			@$pdo->query($sql_cash);
		}
	}

	if($pay_type!=23) {
		$payment_no = createPayment(array(
			'type' => 0,
			'ono' => $sno,
			'pno' => $order_product_no,
			'pay_type' => $pay_type,
			'amount' => $pay_prc,
			'bank' => $bank,
			'bank_name' => $bank_name,
			'dlv_prc' => $dlv_prc,
		), 1);
		$ono = $sno;
	}


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

	makeOrderLog($sno, "order_sbscr.exe.php");

	if($mobile_browser == 'mobile' && $_SESSION['browser_type'] != 'mobile' && $cfg['mobile_pg_use'] == 'Y') {
		$mobile_pg_use = 'Y';
	}

	if($_SESSION['browser_type'] == 'mobile' || $mobile_pg_use == 'Y') {
		if($cfg['pg_mobile_version']) $pg_version = $cfg['pg_mobile_version']."/";
		$card_pg = $cfg['card_mobile_pg'];
	}else {
		if($cfg['card_pg'] != 'dacom' && $cfg['card_pg'] != 'inicis') $cfg['pg_version'] = '';
		if($cfg['pg_version']) $pg_version = $cfg['pg_version']."/";

		$card_pg = $cfg['card_pg'];
	}

	if ($pay_type == '23') {
		if(isset($cfg['autobill_pg']) == false || empty($cfg['autobill_pg']) == true) {
			$cfg['autobill_pg'] = 'dacom';
		}
		switch($cfg['autobill_pg']) {
			case 'dacom' : $pg_version = 'XpayAutoBilling/'; break;
			case 'nicepay' : $pg_version = 'autobill/'; break;
		}
		include_once $engine_dir."/_engine/card.{$cfg['autobill_pg']}/{$pg_version}card_pay.php";
		close();
		exit;
	}

    include_once $engine_dir.'/_engine/order/order_paytype.exe.php';

	if($stat == 11) include_once $engine_dir."/_engine/card.{$card_pg}/{$pg_version}card_pay.php";
	else include_once $engine_dir."/_engine/order/order2.exe.php";
	close();

	exit;

?>