<?PHP
	trncCart(6);

	include_once $engine_dir."/_engine/include/shop.lib.php";

	//검색프리셋
	$spno = numberOnly($_GET['spno']);
	$approval = numberOnly($_GET['approval']);
	unset($_GET['spno']);
	if($spno) {
		$spdata = $pdo->assoc("select * from $tbl[search_preset] where no='$spno'");
		if($spdata['querystring']) {
			$_GET = array_merge($_GET, json_decode($spdata['querystring'], true));
			$_REQUEST = array_merge($_REQUEST, json_decode($spdata['querystring'], true));
		}
	}

	$prd_part = ($admin['level'] == 4) ? " and partner_no='$admin[partner_no]'" : '';

	$sfield  = "a.ono, a.date1, a.date2, a.date3, a.date4, a.date5, a.buyer_name, a.member_no, a.member_id, a.bank, a.bank_name, a.stat, a.stat2, a.pay_type, a.milage_prc, a.emoney_prc, a.point_use, a.dlv_no, a.dlv_code, a.conversion, a.total_prc, a.pay_prc, a.repay_prc, a.buyer_cell, a.title, a.print, a.order_gift, a.mobile, a.checkout, a.sale5, a.x_order_id, a.s_order_id, a.prd_prc, a.dlv_prc, a.postpone_yn ";
	$sfield .= ", (select group_concat(concat(name,'(',buy_ea,')') separator ' / ') from $tbl[order_product] where ono = a.ono $prd_part) as `opname`";

	if($cfg['n_smart_store'] == 'Y') $sfield .= ", a.smartstore";
	if($cfg['use_talkpay'] == 'Y') $sfield .= ", a.external_order";
    if ($cfg['use_set_product'] == 'Y') $sfield .= ", a.has_set";

	$w = $j = '';

	if($cfg['use_trash_ord'] == 'Y') {
		$sfield .= ", a.del_date, a.del_admin";
	}
	$is_prdcpn = ($pdo->row("select count(*) from $tbl[coupon] where stype=5") > 0) ? 'Y' : 'N';
	if($is_prdcpn == 'Y') {
		$sfield .= ", a.sale7";
	}
	if($cfg['use_kakaoTalkStore'] == 'Y') {
		$sfield .= ", a.talkstore";
	}

	if($cfg['opmk_api']) { // 오픈마켓 연동
		$sfield .= ", a.openmarket_id";
		$opmk_names = array();
		if(!is_array($_GET['opmk'])) $_GET['opmk'] = array();
		$opmk_res = $pdo->iterator("select * from $tbl[openmarket_cfg] order by name asc");
        foreach ($opmk_res as $opmk_data) {
			$opmk_data['name'] = stripslashes($opmk_data['name']);
			$checked = in_array($opmk_data['no'], $_GET['opmk']) ? 'checked' : '';
			$opmk_search .= "<li><label><input type='checkbox' name='opmk[]' value='$opmk_data[no]' $checked> $opmk_data[name]</label></li>";
			$opmk_names[$opmk_data['api_code']] = $opmk_data['name'];

			if($checked == 'checked') {
				$opmk_w .= ", '$opmk_data[api_code]'";
			}
		}
		if($opmk_w) {
			$w .= " and openmarket_id in (".substr($opmk_w, 1).")";
		}
	}

	if($cfg['ord_list_memo_icon'] == 'Y') $sfield .= ", memo_cnt";
	if($cfg['recipient'] == 'Y') $sfield .= ", addressee_name";

	if($_GET['search_date_type']) $search_date_type = numberOnly($_GET['search_date_type']);
	if($_GET['all_date']) $all_date = $_GET['all_date'];
	if($_GET['stat']) $stat = numberOnly($_GET['stat']);
	if($_GET['order_stat_group']) $order_stat_group = numberOnly($_GET['order_stat_group']);
	if(!$search_date_type) { // 관리자별 주문리스트 커스텀
		if(!$order_stat_group) $_mng_sset = mySearchSet('ordersearch');
		$_stat = explode('@', $_mng_sset['ostat']);
		for($ii=0; $ii < count($_stat); $ii++){
			if(!$_stat[$ii]) continue;
			$stat[]=$_stat[$ii];
		}
		$search_date_type = $_mng_sset['period'];
		$all_date = $_mng_sset['period_all'];
		if(!$all_date) {
			$_default_start_date = ($_mng_sset['seach_date_period']) ? $_mng_sset['seach_date_period'] : '-15 days';
			$start_date = date('Y-m-d', strtotime($_default_start_date, $now));
			$finish_date = date('Y-m-d', $now);
		}
		$pay_type = $_mng_sset['paytype'];
		$orderby = ($orderby) ? $orderby : $_mng_sset['orderby'];
		$search_type = $_mng_sset['search'];
		if(!$_GET['row']) {
			$row = numberOnly($_mng_sset['sort_fd']);
		}
		unset($_mng_sset);
	}

	// 상태검색
	if($_GET['order_stat_group']) $order_stat_group = $_GET['order_stat_group'];
	if($order_stat_group && count($stat) == 0) {
		$stat = $_order_stat_group[$order_stat_group];
	}
	$stat_check_str = '';
	if(is_array($_order_stat_group[$order_stat_group])) {
		if(count($_order_stat_group[$order_stat_group])==1) {
			$stat_check_str.=$_order_stat[$_order_stat_group[$order_stat_group][0]];
		} else {
			foreach($_order_stat_group[$order_stat_group] as $key=>$val) {
				$chkd=(in_array($val,$stat)) ? 'checked' : '';
				$stat_check_str .= "<li><input type='checkbox' id='stat' name='stat[]' value='$val' $chkd> $_order_stat[$val]</li>";
			}
		}
		$ws_ok=1;
	} else {
		foreach($_order_stat as $key=>$val) {
			if(in_array($key, array(31, 33, 40))) continue;
			$chkd = (is_array($stat) && in_array($key,$stat)) ? 'checked' : '';
			if($key == 11) $stat_check_str .= '</ul><ul class="list_common3">';
			else $stat_check_str .= "<li><label class=\"p_cursor\"><input type='checkbox' id='stat' name='stat[]' value='$key' $chkd> $val</label></li>";
		}
		if(is_array($stat) == true && count($stat) > 0) $ws_ok=1;
	}

	$ws = '';
	if($ws_ok) {
		$ws = " and c.stat in (".implode(',', $stat).")";
	}
	$ord_stat = numberOnly($_GET['ord_stat']);
	if($ord_stat > 0) {
		$ws_tab .= " /*order_stat_tab*/ and c.stat='$ord_stat'";
	}
	$ws = '/*order_stat*/ '.$ws.$ws_tab;
	$w .= $ws;

	if($order_stat_group == 8 || $order_stat_group == 9){

	} else if($order_stat_group == 10) {
		$w .= " and a.stat=32";
	} else {
		if($cfg['approval_standby'] == 'N' || !$cfg['approval_standby']) {
			$w .= " and a.`stat` not in (11, 31, 32, 40)";
		}
	}
	$w .= " and a.`stat` != 33";

	// 기간검색
	if($_GET['start_date']) $start_date = $_GET['start_date'];
	if($_GET['finish_date']) $finish_date = $_GET['finish_date'];
	if(!$start_date || !$finish_date) {
		$all_date = 'Y';
	}
	if(!$search_date_type) $search_date_type = 1;
	if(!$all_date) {
		$_start_date = strtotime($start_date);
		$_finish_date = strtotime($finish_date) + 86399;
		if($search_date_type == 13) {
			$w .= " and c.repay_date between '$_start_date' and '$_finish_date'";
		} else {
			$w .= " and a.`date".$search_date_type."` between '$_start_date' and '$_finish_date'";
		}
	}
	if(!$start_date || !$finish_date) {
		$start_date = $finish_date=date('Y-m-d', $now);
	}

	if(!$pay_type) $pay_type = numberOnly($_GET['pay_type']);
	if($pay_type) $w .= " and a.`pay_type`='$pay_type'";
	if($_GET['use_milage'] == 'Y') $w .= " and (a.milage_prc > 0 or a.point_use > 0)";
	if($_GET['use_emoney'] == 'Y') $w .= " and a.emoney_prc > 0";
	if($_GET['use_cpn'] == 'Y') {
		if($is_prdcpn == 'Y') $w .= " and (a.sale5 > 0 or a.sale7 > 0)";
		else $w .= " and a.sale5 > 0";
	}
	if(isset($_GET['chg_paytype']) == true && $_GET['chg_paytype'] == 'Y') {
		$w .= " and pay_type_changed > 0";
	}

	if($_GET['member_type'] == 'Y') $w .= ' and a.`member_no` > 0';
	elseif($_GET['member_type'] == 'N') $w .= ' and a.`member_no`=0';

	if($_POST['xls_searchtype'] == 2 && $_POST['checked']) {
		$_checked = preg_replace('/[^0-9,]/', '', $_POST['checked']);
		$w .= " and a.`no` in ($_checked)";
	}

	$pay_prc_s = numberOnly($_GET['pay_prc_s']);
	$pay_prc_f = numberOnly($_GET['pay_prc_f']);
	if($pay_prc_s != '') $w .= " and a.`pay_prc` >= '$pay_prc_s'";
	if($pay_prc_f != '') $w .= " and a.`pay_prc` <= '$pay_prc_f'";

	$prd_prc_s = numberOnly($_GET['prd_prc_s']);
	$prd_prc_f = numberOnly($_GET['prd_prc_f']);
	if($prd_prc_s != '') $w .= " and a.`prd_prc` >= '$prd_prc_s'";
	if($prd_prc_f != '') $w .= " and a.`prd_prc` <= '$prd_prc_f'";

	$b_dlv_prc_s = numberOnly($_GET['b_dlv_prc_s']);
	$b_dlv_prc_f = numberOnly($_GET['b_dlv_prc_f']);
	if($b_dlv_prc_s > 0) $w .= " and a.extra1 >= '$b_dlv_prc_s'";
	if($b_dlv_prc_f > 0) $w .= " and a.extra1 <= '$b_dlv_prc_f'";

	$is_dlv_part = $_GET['is_dlv_part'];
	switch($is_dlv_part) {
		case 'N' : // 배송 전
			$w .= " and (a.stat2 not like '%@4@%' and a.stat2 not like '%@5@%' and a.stat2 not like '%@17@%')";
		break;
		case 'Y' : // 부분배송
		   $w .= " and a.stat < 6 and (a.stat!=4 and a.stat!=5 and a.stat!=17 and (a.stat2 like '%@4@%' || a.stat2 like '%@5@%' || a.stat2 like '%@17@%'))";
		break;
		case 'A' : // 전체배송
			$w .= " and a.stat in (4, 5)";
		break;
	}

	$is_nation = $_GET['is_nation'];
	switch($is_nation) {
		case 'Y' : $w .= " and a.nations!=''"; break;
		case 'N' : $w .= " and a.nations=''"; break;
	}

	$mobile = $_GET['mobile'];
	if(is_array($mobile)){
		$tmp = $mobile;
		foreach($mobile as $key => $val) {
			$tmp[$key] = "'".addslashes($val)."'";
		}
		$w .= " and a.mobile in (".implode(',', $tmp).")";
	} else {
		$mobile = array();
	}

	$conversion_s = $_GET['conversion_s'];
	if(is_array($conversion_s)) {
		$tmp = '';
		foreach($conversion_s as $key => $val) {
			$tmp .= "or a.conversion like '%@".addslashes($val)."%'";
		}
		if($tmp) {
			$tmp = substr($tmp, 3);
			$w .= " and ($tmp)";
		}
	} else {
		$conversion_s = array();
	}

	$checkout_order = $_GET['checkout_order'];
	if($checkout_order == 'Y') $w .= " and a.`checkout`='Y'";
	else if($checkout_order == 'N') {
		$w .= " and a.`checkout`!='Y'";
		if($cfg['use_kakaoTalkStore'] == 'Y') $w .= " and a.talkstore='N'";
		if($cfg['n_smart_store'] == 'Y') $w .= " and a.smartstore='N'";
		if($cfg['use_talkpay'] == 'Y') $w .= " and a.external_order=''";
	}
	else if($checkout_order == 'K') $w .= " and a.talkstore='Y'";
	else if($checkout_order == 'S') $w .= " and a.smartstore='Y'";
	else if($checkout_order == 'T') $w .= " and a.external_order='talkpay'";

	if($_GET['nations'] == 'Y') $w .= " and nations != ''";
	if($_GET['memo_yn'] == 'Y') $w .= " and `memo_cnt` > 0";

	// 배송지 변경 주문상품 검색
	$addr_changed = $_GET['addr_changed'];
	if($addr_changed) {
		if(fieldExist($tbl['order_product'], 'addr_changed') == true) {
			if($addr_changed == 'Y') {
				$w .= " and c.addr_changed='Y'";
			} else if($addr_changed == 'N') {
				$w .= " and c.addr_changed='N'";
			}
		}
	}

	// 텍스트 검색
	$_search_type = array();
	$_search_type['ono'] = '주문번호';
	if($cfg['checkout_id']) {
		$_search_type['checkout_ono'] = '네이버페이 상품주문번호';
	}
	if($cfg['use_kakaoTalkStore'] == 'Y') {
		$_search_type['talkstore_ono'] = '톡스토어 주문번호';
	}
	if($cfg['use_talkpay'] == 'Y') {
		$_search_type['talkpay_ono'] = '카카오 페이구매 상품주문번호';
	}
	$_search_type['member_id'] = '회원아이디';
	$_search_type['name'] = '주문자/입금자/수령인';
	$_search_type['phone'] = '주문자 연락처';
	$_search_type['buyer_email'] = '주문자 이메일';
	$_search_type['dlv_code'] = '송장번호';
	$_search_type['pname'] = '상품명';
	$_search_type['addressee_addr'] = '배송지 주소';

	if(!$search_type) $search_type = $_GET['search_type'];
	if($search_type == 'bank_name') $search_type = 'name';
	$search_str = addslashes(trim($_GET['search_str']));
	if($_search_type[$search_type] && $search_str) {
		if($search_type == 'pname') {
			$w .= " and ( c.`name` like '%$search_str%' OR a.title like '%$search_str%')";
		} elseif(in_array($search_type, array('checkout_ono', 'talkstore_ono'))) {
			$w .= " and c.`$search_type` like '%$search_str%'";
		} elseif($search_type == 'talkpay_ono') {
			$w .= " and a.external_order='talkpay' and c.external_id='$search_str'";
		} elseif($search_type == 'name') {
			$w .= " and (a.buyer_name like '%$search_str%' or a.addressee_name like '%$search_str%' or a.bank_name like '%$search_str%')";
		} elseif($search_type == 'phone') {
			$w .= " and (a.buyer_phone like '%$search_str%' or a.buyer_cell like '%$search_str%')";
		} elseif($search_type == 'addressee_addr') {
			$w .= " and (a.addressee_addr1 like '%$search_str%' or a.addressee_addr2 like '%$search_str%')";
		} else {
			$w .= " and a.`$search_type` like '%$search_str%'";
		}
	}

	$pno = numberOnly($_GET['pno']);
	$optstr = addslashes(trim($_GET['optstr']));
	$dlv_hold = addslashes($_GET['dlv_hold']);

	$sfield = "distinct a.no,".$sfield;
	$j .= " inner join `$tbl[order_product]` c using(ono)";

	if($body == 'order@order_excel.exe') $g = ' group by a.no';
	if($pno > 0) {
		$w .= " and c.pno='$pno'";
		if($optstr) $w .= " and c.`option` like '%$optstr%'";
	}
	if ($admin['partner_no'] > 0) $w .= " and c.partner_no='$admin[partner_no]'";
	if($dlv_hold) {
		if(strlen($dlv_hold) == 2) {
			$w .= " and a.postpone_yn='".substr($dlv_hold, 1)."'";
		} else {
			$w .= " and c.`dlv_hold`='$dlv_hold'";
			if($dlv_hold == 'Y') $w .= " and c.stat between 1 and 3";
		}
	}

	$QueryString = makeQueryString(true, 'page');
	$xls_query = makeQueryString('page', 'body');
	$list_tab_qry = makeQueryString(true, 'page', 'ord_stat');

	// 정렬
	$_order_by_name=array();
	$_order_by=array();

	$_order_by_name[1]="주문일역순";
	$_order_by[1]="`date1` desc";
	$_order_by_name[2]="주문일순";
	$_order_by[2]="`date1` asc";
	$_order_by_name[3] = $_order_stat[2].'일역순';
	$_order_by[3]="`date2` desc";
	$_order_by_name[4] = $_order_stat[2].'일순';
	$_order_by[4]="`date2` asc";
	$_order_by_name[5] = $_order_stat[3].'일역순';
	$_order_by[5]="`date3` desc";
	$_order_by_name[6] = $_order_stat[3].'일순';
	$_order_by[6]="`date3` asc";
	$_order_by_name[7] = $_order_stat[4].'일역순';
	$_order_by[7]="`date4` desc";
	$_order_by_name[8] = $_order_stat[4].'일순';
	$_order_by[8]="`date4` asc";
	$_order_by_name[9] = $_order_stat[5].'일역순';
	$_order_by[9]="`date5` desc";
	$_order_by_name[10] = $_order_stat[5].'일순';
	$_order_by[10]="`date5` asc";

	$orderby = numberOnly($_GET['orderby']);
	if(!$orderby || !$_order_by[$orderby]) {
		$orderby=1;
	}

    if ( is_array($_POST['check_ono']) && !empty($_POST['check_ono'][0]) ) {
        $w .= ' and a.ono IN ("'.implode('", "', $_POST['check_ono']).'")';
    }

	if($cfg['use_erp_interface'] == 'Y' && $cfg['erp_interface_name'] == 'dooson') {
		$x_order_id = $_GET['x_order_id'];
		if(!$x_order_id) $x_order_id = 'ON';
		if($x_order_id != 'ALL') {
			$w .= ($x_order_id == 'ON') ? " and (x_order_id='' or x_order_id='checkout')" : " and x_order_id='$x_order_id'";
		}

		$_stores = array();
		$tmpres = $pdo->iterator("select code, name from $tbl[off_store]");
        foreach ($tmpres as $sdata) {
			$_stores[$sdata['code']] = stripslashes($sdata['name']);
		}
	}

	if($_GET['is_cashreceipt']) $is_cashreceipt = ($_GET['is_cashreceipt']);
	if($is_cashreceipt == 'Y') {
		$j .= " inner join $tbl[cash_receipt] d using(ono)";
		$sfield .= ",d.stat as cash_receipt";
	}

	$_seller = array();
	$tres = $pdo->iterator("select no, provider from $tbl[provider] order by provider asc");
    foreach ($tres as $tmp) {
		$_seller[$tmp['no']] = stripslashes($tmp['provider']);
	}
	$seller_idx = numberOnly($_GET['seller_idx']);
	if($seller_idx > 0) {
		$j .= " inner join $tbl[product] p on c.pno=p.no";
		$w .= " and p.seller_idx='$seller_idx'";
	}

	if($admin['level'] < 4 && $cfg['use_partner_shop'] == 'Y') {
		$_partners = array('0' => '본사');
		$tres = $pdo->iterator("select no, corporate_name from $tbl[partner_shop] where stat=2 order by corporate_name asc");
        foreach ($tres as $tmp) {
			$_partners[$tmp['no']] = stripslashes($tmp['corporate_name']);
		}

		$partner_no = numberOnly($_GET['partner_no']);
		if ($partner_no !== '') {
			$w .= " and c.partner_no='$partner_no'";
            $partnerSelect = $partner_no;
		} else {
            $partnerSelect = "";
        }
	}

	if($cfg['use_sbscr']=='Y' && $_GET['sbscr_order'] == 'Y') $j .= " inner join `$tbl[sbscr_schedule_product]` ssp using(ono)";

	//한정상품
	$is_limited = $_GET['is_limited'];
	if($is_limited == 'Y') {
		$j .= "inner join erp_complex_option e using(complex_no)";
		$w .= " and e.force_soldout='L' and e.del_yn='N'";
	}

    // 회원 등급
    if ($scfg->comp('ord_list_mgroup', 'Y') == true || $body == 'order@order_excel.exe') {
        $j .= " left join {$tbl['member']} m on a.member_no=m.no";
        $sfield .= ", m.level";

        $grps = getGroupName();
        $mgroup = $_GET['mgroup'];
        if (empty($mgroup) == false) {
            $w .= " and m.level='$mgroup'";
        }
    }

	$sql  = "select $sfield from `$tbl[order]` a $j where 1 $w $g order by ".$_order_by[$orderby];
	$count_field = $j ? 'distinct a.no' : '*';
	$sql2 = "select count($count_field) from `$tbl[order]` a $j where 1 $w ";

	if($order_stat_group == 8 && $approval) {
		$sql = "select o.* from ($sql) o, `$tbl[card]` c where o.`ono`=c.`wm_ono` and c.`stat`='2' order by o.".$_order_by[$orderby];
		$sql2 = "select count(*) from ($sql) o, `$tbl[card]` c where o.`ono`=c.`wm_ono` and c.`stat`='2'";
	}

	if(preg_match('/order@order_excel|order@order_product/', $body)) {
		return;
	}

	include $engine_dir.'/_engine/include/paging.php';

	$page = numberOnly($_GET['page']);
	if(!$row) $row = numberOnly($_GET['row']);
	if($page <= 1) $page = 1;
	if($row < 1 || $row > 1000) {
		$row = ($order_stat_group == 9) ? 100 : 10;
	}
	if($row > 100) $cfg['ord_list_first_prc'] = 'N';
	$block = 10;

	$NumTotalRec = $pdo->row($sql2);
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString(makeQueryString('page'));
	$PagingResult=$PagingInstance->result($pg_dsn);
	$sql.=$PagingResult['LimitQuery'];

	$pg_res = $PagingResult['PageLink'];
	$res = $pdo->iterator($sql);
	$idx = $NumTotalRec-($row*($page-1));

	include_once $engine_dir."/_manage/config/order_excel_config.php";
	foreach($ord_excel_set as $key=>$val){
		$xls_sets .= "<option value='$key' $sel>- $ord_excel_set_name[$key]</option>\n";
	}

	$qs_without_row = '?'.preg_replace('/&row=[^&]+/', '', $_SERVER['QUERY_STRING']);
	$qs_without_sort = '?'.preg_replace('/&orderby=[^&]+/', '', $_SERVER['QUERY_STRING']);

	// 상태별 통계
	$_tabcnt = array();
	$wt = str_replace($ws, '', $w);
	$_tmpres = $pdo->iterator("select c.stat, count(distinct ono) as cnt from $tbl[order] a $j where 1 $wt group by c.stat");
    foreach ($_tmpres as $_tmp) {
		$_tabcnt[$_tmp['stat']] = $_tmp['cnt'];
	}
	$list_tab_qry = preg_replace('/^&/', '?', $list_tab_qry);
	${'list_tab_active'.$ord_stat} = 'class="active"';
	$wt = str_replace($ws_tab, '', $w);
	$_tabcnt['total'] = $pdo->row("select count($count_field) from `$tbl[order]` a $j where 1 $wt");

	unset($_pay_type[23]);

    // 엑셀 다운로드 인증 방식
    if ($scfg->comp('use_oexcel_protect', 'Y') == true) {
        if ($scfg->comp('oexcel_otp_method', 'sms') == true) $excel_auth_str = '관리자 휴대폰번호';
        else if ($scfg->comp('oexcel_otp_method', 'mail') == true) $excel_auth_str = '관리자 이메일주소';
    }

?>
<?php if ($order_stat_group == 10) { ?>
<div class="box_title first">
	<h2 class="title">주문 휴지통</h2>
</div>
<?php } ?>
<form method="post" id="ordSearchFrm" name="ordSearchFrm" action="./" onsubmit="searchSubmit(this,'<?=$_GET['body']?>')" >
	<input type="hidden" name="body" value="">
	<input type="hidden" name="order_stat_group" value="<?=$order_stat_group?>">
	<input type="hidden" name="exec" value="">
	<input type="hidden" name="ext" value="">
	<input type="hidden" name="t1" value="<?=$t1?>">
	<input type="hidden" name="approval" value="<?=$approval?>">
	<input type="hidden" name="msg_where">
	<input type="hidden" name="sms_deny">
	<input type="hidden" name="prd_no" value="<?=$prd_no?>">
	<!-- 검색 폼 -->
	<div id="search">
		<div class="box_search">
			<div class="box_input">
				<div class="select_input shadow">
					<div class="select order">
						<?=selectArray($_search_type,"search_type",2,"",$search_type)?>
					</div>
					<div class="area_input order">
						<input type="text" name="search_str" value="<?=inputText($search_str)?>" class="input" placeholder="검색어를 입력해주세요.">
                        <?php if (!$order_stat_group) { ?>
						    <span id="btn_search_setup" class="setup btt p_cursor" onclick="wisaOpen('./pop.php?body=order@order_search.frm',false,'600px','400px');" tooltip="검색설정"></span>
                        <?php } ?>
					</div>
				</div>
				<div class="view">
					<div id="searchCtl" onclick="toggle_shadow()"><?php searchBoxBtn("ordSearchFrm", $_COOKIE['ord_detail_search_on']) ?></div>
					<label class="p_cursor always"><input type="checkbox" id="search_cookie_ck" onclick="searchBoxCookie(this, 'ord_detail_search_on');" <?=checked($_COOKIE['ord_detail_search_on'], "Y")?>> 항상 상세검색</label>
				</div>
			</div>
            <?php if($admin['level'] < 4) { ?>
			<ul class="quick_search">
				<?php
				$preset_menu = 'order';
				include_once $engine_dir."/_manage/config/quicksearch.inc.php";
				?>
			</ul>
            <?php } ?>
		</div>
		<table class="tbl_search search_box_omit">
			<caption class="hidden">전체주문조회 </caption>
			<colgroup>
				<col style="width:150px">
				<col>
				<col style="width:150px">
				<col>
			</colgroup>
			<tr>
				<th scope="row">기간</th>
				<td colspan="3">
					<select name="search_date_type">
						<option value="1" <?=checked($search_date_type,1,1)?>>주문일</option>
						<option value="2" <?=checked($search_date_type,2,1)?>><?=$_order_stat[2]?>일</option>
						<option value="3" <?=checked($search_date_type,3,1)?>><?=$_order_stat[3]?>일</option>
						<option value="4" <?=checked($search_date_type,4,1)?>><?=$_order_stat[4]?>일</option>
						<option value="5" <?=checked($search_date_type,5,1)?>><?=$_order_stat[5]?>일</option>
						<option value="13" <?=checked($search_date_type,13,1)?>>취소일</option>
					</select>
					<label><input type="checkbox" name="all_date" value="Y" <?=checked($all_date,"Y")?> onClick="searchDate(this.form)"> 전체기간</label>
					<input type="text" name="start_date" value="<?=$start_date?>" size="7" class="input datepicker"> ~ <input type="text" name="finish_date" value="<?=$finish_date?>" size="7" class="input datepicker">
					<?PHP
						$date_type = array(
							'오늘' => '-0 days',
							'1주일' => '-1 weeks',
							'15일' => '-15 days',
							'1개월' => '-1 months',
							'3개월' => '-3 months',
							'6개월' => '-6 months',
							'1년' => '-1 years',
							'2년' => '-2 years',
							'3년' => '-3 years'
						);
						foreach($date_type as $key => $val) {
							$_btn_class=($val && !$all_date && $finish_date == date("Y-m-d", $now) && $start_date == date("Y-m-d", strtotime($val))) ? "on" : "";
							$_sdate=$_fdate = null;
							if($val) {
								$_sdate=date("Y-m-d", strtotime($val));
								$_fdate=date("Y-m-d", $now);
							}
							?> <span class="box_btn_d <?=$_btn_class?> strong"><input type="button" value="<?=$key?>" onclick="setSearchDatee(this.form, 'start_date', 'finish_date', '<?=$_sdate?>', '<?=$_fdate?>', '<?=$_GET['body']?>');"></span><?php
						}
					?>
					<script type="text/javascript">
						searchDate(document.ordSearchFrm);
					</script>
				</td>
			</tr>
			<?php if ($cfg['use_erp_interface'] == 'Y' && $cfg['erp_interface_name'] == 'dooson') { ?>
			<tr>
				<th scope="row">판매처</th>
				<td colspan="3">
					<label class="p_cursor"><input type="radio" name="x_order_id" value="ALL" <?=checked($x_order_id, 'ALL')?>> 전체</label>
					<label class="p_cursor"><input type="radio" name="x_order_id" value="ON" <?=checked($x_order_id, 'ON')?>> 온라인</label>
					<label class="p_cursor"><input type="radio" name="x_order_id" value="OFF" <?=checked($x_order_id, 'OFF')?>> 오프라인</label>
					<label class="p_cursor"><input type="radio" name="x_order_id" value="SBN" <?=checked($x_order_id, 'SBN')?>> 사방넷</label>
					<label class="p_cursor"><input type="radio" name="x_order_id" value="OPN" <?=checked($x_order_id, 'OPN')?>> 오픈마켓</label>
				</td>
			</tr>
			<?php } ?>
			<?php if ($order_stat_group != 10) { ?>
			<tr>
				<th scope="row">거래상태</th>
				<td colspan="3">
					<ul class="list_common3">
						<?=$stat_check_str?>
					</ul>
				</td>
			</tr>
			<tr>
				<th scope="row">결제수단</th>
				<td>
					<?=selectArray($_pay_type,"pay_type",2,"::전체::",$pay_type)?>
					<label class="p_cursor"><input type="checkbox" name="use_milage" value="Y" <?=checked($_GET['use_milage'],'Y')?>> 적립금</label>
					<label class="p_cursor"><input type="checkbox" name="use_emoney" value="Y" <?=checked($_GET['use_emoney'],'Y')?>> 예치금</label>
					<label class="p_cursor"><input type="checkbox" name="use_cpn" value="Y" <?=checked($_GET['use_cpn'],'Y')?>> 쿠폰</label>
					<?php if (empty($cfg['use_paytype_change']) == false && $cfg['use_paytype_change'] == 'Y') { ?>
					<label class="p_cursor"><input type="checkbox" name="chg_paytype" value="Y" <?=checked($_GET['chg_paytype'],'Y')?>> 결제수단 변경 주문서</label>
					<?php } ?>
				</td>
				<th scope="row">회원여부</th>
				<td>
					<label class="p_cursor"><input type="radio" name="member_type" value="" <?=checked($_GET['member_type'],"")?>> 전체</label>
					<label class="p_cursor">
                        <input type="radio" name="member_type" value="Y" <?=checked($_GET['member_type'],"Y")?>> 회원
                        <?php if (isset($grps) == true) { ?>
                        <?=selectArray($grps, 'mgroup', false, ':: 전체 ::', $mgroup)?>
                        <?php } ?>
                    </label>
					<label class="p_cursor"><input type="radio" name="member_type" value="N" <?=checked($_GET['member_type'],"N")?>> 비회원</label>
				</td>
			</tr>
			<tr>
				<th scope="row">결제금액</th>
				<td>
					<input type="text" name="pay_prc_s" size="10" value="<?=$pay_prc_s?>" class="input"> ~ <input type="text" name="pay_prc_f" size="10" value="<?=$pay_prc_f?>" class="input">
				</td>
				<th scope="row">페이지모드 </th>
				<td>
					<label class="p_cursor"><input type="checkbox" name="mobile[]" value="N" <?=in_array("N",$mobile)?'checked':''?>> PC화면</label>
					<label class="p_cursor"><input type="checkbox" name="mobile[]" value="Y" <?=in_array("Y",$mobile)?'checked':''?>> <?=$cfg['mobile_name']?> Web</label>
					<label class="p_cursor"><input type="checkbox" name="mobile[]" value="A" <?=in_array("A",$mobile)?'checked':''?>> <?=$cfg['mobile_name']?> App</label>
				</td>
			</tr>
			<tr>
				<th scope="row">상품가격</th>
				<td>
					<input type="text" name="prd_prc_s" size="10" value="<?=$prd_prc_s?>" class="input"> ~ <input type="text" name="prd_prc_f" size="10" value="<?=$prd_prc_f?>" class="input">
				</td>
				<th>배송지 변경이력</th>
				<td>
					<label class="p_cursor"><input type="radio" name="addr_changed" value="" <?=checked($addr_changed, '')?>> 전체</label>
					<label class="p_cursor"><input type="radio" name="addr_changed" value="Y" <?=checked($addr_changed, 'Y')?>> 변경 있음</label>
					<label class="p_cursor"><input type="radio" name="addr_changed" value="N" <?=checked($addr_changed, 'N')?>> 변경 없음</label>
				</td>
			</tr>
			<?php if ($cfg['use_trigger_extra1'] == 'Y') { ?>
			<tr>
				<th scope="row">배송 전 상품금액</th>
				<td colspan="3">
					<input type="text" name="b_dlv_prc_s" size="10" value="<?=$b_dlv_prc_s?>" class="input"> ~ <input type="text" name="b_pay_prc_f" size="10" value="<?=$b_pay_prc_f?>" class="input">
				</td>
			</tr>
			<?php } ?>
			<?php if (empty($cfg['checkout_id']) == false || $cfg['use_kakaoTalkStore'] == 'Y' || $scfg->comp('n_smart_store', 'Y') || empty($cfg['use_talkpay']) == false) { ?>
			<tr>
				<th scope="row">판매채널</th>
				<td colspan="3">
					<label><input type="radio" name="checkout_order" value=""  <?=checked($checkout_order, '')?>> 전체</label>
					<label><input type="radio" name="checkout_order" value="N" <?=checked($checkout_order, 'N')?>> 쇼핑몰</label>
					<label><input type="radio" name="checkout_order" value="Y" <?=checked($checkout_order, 'Y')?> <?php if($cfg['checkout_id'] == '') {?>disabled<?php } ?>> 네이버페이 주문형</label>
					<label><input type="radio" name="checkout_order" value="T" <?=checked($checkout_order, 'T')?> <?php if(empty($cfg['use_talkpay'])) {?>disabled<?php } ?>> 톡체크아웃</label>
					<label><input type="radio" name="checkout_order" value="K" <?=checked($checkout_order, 'K')?> <?php if($cfg['use_kakaoTalkStore'] != 'Y') {?>disabled<?php } ?>> 카카오톡 스토어</label>
					<label><input type="radio" name="checkout_order" value="S" <?=checked($checkout_order, 'S')?> <?php if(!$scfg->comp('n_smart_store', 'Y')) {?>disabled<?php } ?>> 네이버 스마트스토어</label>
				</td>
			</tr>
			<?php } ?>
			<tr>
				<th scope="row">검색옵션</th>
				<td>
					<label class="p_cursor"><input type="checkbox" name="is_cashreceipt" value="Y" <?=checked($_GET['is_cashreceipt'], 'Y')?>> 현금영수증</label>
					<?php if ($cfg['ord_list_memo_icon'] == 'Y') { ?>
					<label class="p_cursor"><input type="checkbox" name="memo_yn" value="Y" <?=checked($_GET['memo_yn'], 'Y')?>> 주문메모</label>
					<?php } ?>
					<?php if ($cfg['use_sbscr'] == 'Y') { ?>
					<label class="p_cursor"><input type="checkbox" name="sbscr_order" value="Y" <?=checked($_GET['sbscr_order'], 'Y')?>> 정기배송</label>
					<?php } ?>
					<label class="p_cursor"><input type="checkbox" name="is_limited" value="Y" <?=checked($_GET['is_limited'], 'Y')?>> 한정상품</label>
				</td>
				<th scope="row">부분배송 여부</th>
				<td>
					<label><input type="radio" name="is_dlv_part" value=""  <?=checked($is_dlv_part, '')?> > 전체</label>
					<label><input type="radio" name="is_dlv_part" value="N" <?=checked($is_dlv_part, 'N')?>> 배송 전</label>
					<label><input type="radio" name="is_dlv_part" value="Y" <?=checked($is_dlv_part, 'Y')?>> 부분배송</label>
					<label><input type="radio" name="is_dlv_part" value="A" <?=checked($is_dlv_part, 'A')?>> 전체배송</label>
				</td>
			</tr>
			<tr>
				<th scope="row">주문 상품</th>
				<td>
					<span class="box_btn_s"><input type="button" value="찾기" onclick="searchOrderPrd(this)"></span>
					<span class="box_btn_s"><input type="button" value="검색취소" onclick="$('#search_prd').html('')"></span>
					<div id="search_prd" style="margin: 5px 0">
						<?php if ($pno) include $engine_dir.'/_manage/order/order_search_inc.exe.php'; ?>
					</div>
				</td>
				<th scope="row">배송보류</th>
				<td>
					<label class="p_cursor"><input type="radio" name="dlv_hold" value="" <?=checked($_GET['dlv_hold'], '')?>> 전체</label>
					<label class="p_cursor"><input type="radio" name="dlv_hold" value="N" <?=checked($_GET['dlv_hold'], 'N')?>> 정상</label>
					<label class="p_cursor"><input type="radio" name="dlv_hold" value="Y" <?=checked($_GET['dlv_hold'], 'Y')?>> 보류</label>
					<label class="p_cursor"><input type="radio" name="dlv_hold" value="AN" <?=checked($_GET['dlv_hold'], 'AN')?>> 전체정상</label>
					<label class="p_cursor"><input type="radio" name="dlv_hold" value="AY" <?=checked($_GET['dlv_hold'], 'AY')?>> 전체보류</label>
					<label class="p_cursor"><input type="radio" name="dlv_hold" value="AB" <?=checked($_GET['dlv_hold'], 'AB')?>> 부분보류</label>
				</td>
			</tr>

			<tr>
				<th>상품 사입처</th>
				<td>
					<?=selectArray($_seller, 'seller_idx', false, ':: 사입처 ::', $seller_idx)?>
				</td>
				<?php if ($admin['level'] < 4 && $cfg['use_partner_shop'] == 'Y') { ?>
				<th>입점사</th>
				<td>
					<?=selectArray($_partners, 'partner_no', 2, ':: 입점사 ::', $partnerSelect)?>
				</td>
				<?php } ?>
			</tr>
			<?php if ($cfg['delivery_fee_type'] == 'A') { ?>
			<tr>
				<th scope="row">국내/해외배송</th>
				<td colspan="3">
					<label><input type="radio" name="is_nation" value="" <?=checked($is_nation, '')?>> 전체</label>
					<label><input type="radio" name="is_nation" value="N" <?=checked($is_nation, 'N')?>> 국내배송</label>
					<label><input type="radio" name="is_nation" value="Y" <?=checked($is_nation, 'Y')?>> 해외배송</label>
				</td>
			</tr>
			<?php } ?>
			<?php if ($opmk_search) { ?>
			<tr>
				<th scope="row">오픈마켓</th>
				<td colspan="3">
					<ul id="conversion_list" class="list">
						<?=$opmk_search?>
					</ul>
				</td>
			</tr>
			<?php } ?>
			<tr>
				<th scope="row">유입경로</th>
				<td class="bcol2" colspan="3">
					<?=selectArrayConv("conversion_s")?>
				</td>
			</tr>
			<?php
				$convarr2 = selectArrayConv("conversion_s", 2);
				if($convarr2) {
			?>
			<tr>
				<th scope="row">배너광고유입</th>
				<td scope="row" class="bcol2" colspan="3">
					<?=$convarr2?>
				</td>
			</tr>
			<?php } ?>
			<?php } // order_stat_group != 10?>
		</table>
		<div class="box_bottom top_line">
			<span class="box_btn blue"><input type="submit" value="검색"></span>
			<span class="box_btn"><input type="button" value="초기화" onclick="location.href='./?body=<?=$_GET['body']?>&order_stat_group=<?=$order_stat_group?>'"></span>
			<?php if ($admin['level'] < 4) { ?><span class="box_btn quicksearch"><a onclick="viewQuickSearch('ordSearchFrm', 'order');">#단축검색등록</a></span><?php } ?>
		</div>
	</div>
	<!-- //검색 폼 -->
	<!-- 검색 총합 -->
	<?php if (!$order_stat_group) { ?>
	<style type="text/css" title="">
	@media all and (max-width:1700px) {
		.box_tab .btns {top:55px; z-index:5;}
	}
	</style>
	<div class="box_tab">
		<ul>
			<li><a href="<?=$list_tab_qry?>" <?=$list_tab_active?>>전체&nbsp;<i class="icon_info btt" tooltip="'전체' 탭은 취소/환불/반품/교환 등 모든 상태를 포함하므로<br> 일반상태(<?=$_order_stat[1]?>~<?=$_order_stat[5]?>)탭의 합과 차이가 있습니다."></i><span><?=number_format($_tabcnt['total'])?></span></a></li>
			<li><a href="<?=$list_tab_qry?>&ord_stat=1" <?=$list_tab_active1?>><?=$_order_stat[1]?><span><?=number_format($_tabcnt[1])?></span></a></li>
			<li><a href="<?=$list_tab_qry?>&ord_stat=2" <?=$list_tab_active2?>><?=$_order_stat[2]?><span><?=number_format($_tabcnt[2])?></span></a></li>
			<li><a href="<?=$list_tab_qry?>&ord_stat=3" <?=$list_tab_active3?>><?=$_order_stat[3]?><span><?=number_format($_tabcnt[3])?></span></a></li>
			<li><a href="<?=$list_tab_qry?>&ord_stat=4" <?=$list_tab_active4?>><?=$_order_stat[4]?><span><?=number_format($_tabcnt[4])?></span></a></li>
			<li><a href="<?=$list_tab_qry?>&ord_stat=5" <?=$list_tab_active5?>><?=$_order_stat[5]?><span><?=number_format($_tabcnt[5])?></span></a></li>
		</ul>
		<div class="btns">
        	<?php if ($admin['level'] < 3 || $admin['level'] == 4 || strchr($admin['auth'], '@auth_orderexcel') == true) { ?>
			<span class="box_btn_s icon excel btt"><input type="button" value="엑셀다운" onclick="showExcelBtn(event);"></span>
            <?php } ?>
			<?php if ($admin['level'] != 4) { ?>
			<span class="box_btn_s icon setup btt"><input type="button" value="주문 관리 설정" onclick="oconfig.open();"></span>
			<?php } ?>
		</div>
	</div>
	<?php } else { ?>
	<div class="box_title">
		<strong id="total_prd"><?=number_format($NumTotalRec)?></strong> 개의 주문이 <?php if($order_stat_group == 10) { ?>주문 휴지통에서<?php } ?> 검색되었습니다.
		<div class="btns">
			<?php if($order_stat_group == 8) { ?>
			<span class="box_btn_s icon list"><input type="button" value="업데이트 누락 예상주문 보기" onclick="location.href='./<?=$QueryString?>&order_stat_group=8&approval=1'"></span>
			<span class="box_btn_s icon guide"><input type="button" value="PG승인결과 대사" onclick="location.href='./?body=order@pg_compare'"></span>
			<?php } ?>
			<?php if ($order_stat_group != 10) { ?>
			<span class="box_btn_s icon excel btt"><input type="button" value="엑셀다운" onclick="showExcelBtn(event);"></span>
			<?php } ?>
		</div>
	</div>
	<?php } ?>
	<!-- //검색 총합 -->
	<?php if ($order_stat_group == 8) { ?>
	<div class="box_middle4 left">
		<ul class="list_info">
			<li>승인대기 주문은 주문자가 결제진행을 끝까지 진행하지 않고 <span>도중에 취소</span>하거나, <span>승인 후 주문상태 업데이트 과정에서 누락</span>된 경우입니다.</li>
			<li>승인이 완료된 주문은 주문번호가 붉은색으로 표시되며, 검토 후 승인거래취소 또는 정상처리해주시기 바랍니다.</li>
			<li>PG승인결과 대사는 CSV 파일 업로드를 통해 누락된 정상 승인상태의 주문서 조회가 가능합니다.</li>
		</ul>
	</div>
	<?php } ?>
	<!-- 정렬 -->
	<div class="box_sort">
		<dl class="list">
			<dt class="hidden">정렬</dt>
			<dd>
				<select name="row" onchange="location.href='<?=$qs_without_row?>&row='+this.value">
					<option value="10" <?=checked($row,10,1)?>>10개</option>
					<option value="20" <?=checked($row,20,1)?>>20개</option>
					<option value="30" <?=checked($row,30,1)?>>30개</option>
					<option value="50" <?=checked($row,50,1)?>>50개</option>
					<option value="70" <?=checked($row,70,1)?>>70개</option>
					<option value="100" <?=checked($row,100,1)?>>100개</option>
					<option value="500" <?=checked($row,500,1)?>>500개</option>
					<option value="1000" <?=checked($row,1000,1)?>>1000개</option>
				</select>&nbsp;&nbsp;
				정렬
				<?=selectArray($_order_by_name,"orderby",2,"",$orderby, "location.href='$qs_without_sort&orderby='+this.value")?>
			</dd>
		</dl>
	</div>
	<!-- //정렬 -->
</form>
<!-- 엑셀 저장 레이어 -->
<form method="post" action="./?body=order@order_excel.exe<?=$xls_query?>" target="hidden<?=$now?>" id="excelLayer" class="popup_layer"style="display:none;">
	<input type="hidden" name="ckno" value="">
	<input type="hidden" name="checked" value="">
	<table class="tbl_mini">
		<tr>
			<th scope="row">엑셀양식</th>
			<td class="left">
				<select name="xls_set_temp" class="xls_set" onchange="change_xls_set(this)">
					<?=$xls_sets; unset($xls_sets);?>
				</select>
			</td>
		</tr>
		<tr>
			<th scope="row">대상주문</th>
			<td class="left">
				<label class="p_cursor"><input type="radio" name="xls_searchtype" value="1" checked> 검색된 내역</label>
				<label class="p_cursor"><input type="radio" name="xls_searchtype" value="2"> 선택된 내역</label>
			</td>
		</tr>
		<tr>
			<th scope="row">저장기준</th>
			<td class="left">
				<label class="p_cursor"><input type="radio" name="xlsmode" value="order" checked> 주문번호</label>
				<label class="p_cursor"><input type="radio" name="xlsmode" value="product"> 주문상품</label>
			</td>
		</tr>
        <?php if ($excel_auth_str) { ?>
		<tr>
			<th scope="row">인증</th>
			<td class="left">
                <input type="text" name="xls_down" class="input" placeholder="<?=$excel_auth_str?>">
			</td>
		</tr>
        <?php } ?>
	</table>
	<div class="btn_bottom">
		<span class="box_btn_s blue"><input type="button" value="엑셀다운" onclick="orderExcel()"></span>
		<span class="box_btn_s gray"><input type="button" value="엑셀설정" onclick="goM('config@order_excel_config')"></span>
		<span class="box_btn_s"><input type="button" value="닫기" onclick="showExcelBtn(event);"></span>
	</div>
</form>
<!-- //엑셀 저장 레이어 -->
<!-- 검색 테이블 -->
<form method="post" name="prdFrm" action="./" onsubmit="searchSubmit(this,'<?=$_GET['body']?>')">
	<input type="hidden" name="body" value="">
	<input type="hidden" name="exec" value="">
	<input type="hidden" name="ext" value="">
	<input type="hidden" name="t1" value="<?=$t1?>">
	<input type="hidden" name="order_stat_group" value="<?=$order_stat_group?>">
	<input type="hidden" name="approval" value="<?=$approval?>">
	<input type="hidden" name="msg_where">
	<input type="hidden" name="sms_deny">
	<input type="hidden" name="prd_no" value="<?=$prd_no?>">
	<input type="hidden" name="black">
	<table class="tbl_col">
		<caption class="hidden">전체주문조회 리스트</caption>
		<colgroup>
			<col style="width:40px;">
			<col style="width:50px;">
			<col>
			<col>
			<col style="width:90px;">
			<col style="width:90px;">
			<?php if ($cfg['ord_list_phone'] == 'Y') { ?>
			<col style="width:90px;">
			<?php } ?>
			<?php if ($cfg['recipient'] == 'Y'){?>
			<col style="width:75px;">
			<?php } ?>
			<?php if ($cfg['bank_name2'] == 'Y') { ?>
			<col style="width:75px;">
			<?php } ?>
			<?php if($_use['recom_member'] == 'Y'){?>
			<col style="width:50px;">
			<?php } ?>
			<col style="width:70px;">
			<?php if ($cfg['ord_list_first_prc'] == 'Y') { ?>
			<col style="width:70px;">
			<?php } ?>
			<?php if ($cfg['bank_price'] == 'Y') { ?>
			<col style="width:70px;">
			<?php } ?>
			<col style="width:70px;">
			<?php if ($order_stat_group == 10) { ?>
			<col style="width:90px;">
			<col style="width:80px;">
			<?php if ($cfg['use_trash_ord'] == 'Y' && $cfg['trash_ord_trcd'] > 0) { ?>
			<col style="width:80px;">
			<?php } ?>
			<col style="width:150px;">
			<?php } else { ?>
			<col style="width:125px;">
			<?php if ($cfg['ord_list_postpone'] == 'Y') { ?>
			<col style="width:70px;">
			<?php } ?>
			<col style="width:50px;">
			<?php } ?>
		</colgroup>
		<thead>
			<tr>
				<th><input type="checkbox" onclick="checkAll(document.prdFrm.check_pno,this.checked)"></th>
				<th>번호</th>
				<th>주문번호</th>
				<th>주문상품</th>
				<th>주문일시</th>
				<th>주문자</th>
				<?php if ($cfg['ord_list_phone'] == 'Y') { ?>
				<th>주문자 휴대폰</th>
				<?php } ?>
				<?php if($cfg['recipient'] == 'Y') {?>
				<th>수령인</th>
				<?php } ?>
				<?php if ($cfg['bank_name2'] == 'Y') { ?>
				<th>입금자</th>
				<?php } ?>
				<?php if ($_use['recom_member'] == 'Y') { ?>
				<th>추천인</th>
				<?php } ?>
				<th>총주문액</th>
				<?php if ($cfg['ord_list_first_prc'] == 'Y') { ?>
				<th>최초결제</th>
				<?php } ?>
				<?php if ($cfg['bank_price'] == 'Y') { ?>
				<th onmouseover="showToolTip(event,'적립금, 예치금이외에 실제로<br>현금, 카드, 계좌이체 등으로<br>고객이 지불한 금액')" onmouseout="hideToolTip();">실결제</th>
				<?php } ?>
				<th>결제방법</th>
				<?php if ($order_stat_group == 10) { ?>
				<th>삭제일자</th>
				<?php if ($cfg['use_trash_ord'] == 'Y' && $cfg['trash_ord_trcd'] > 0) { ?>
				<th>삭제 예정일</th>
				<?php } ?>
				<th>삭제처리자</th>
				<?php } else { ?>
				<th>상태</th>
				<?php if ($cfg['ord_list_postpone'] == 'Y') { ?>
				<th>배송보류</th>
				<?php } ?>
				<th>경로</th>
				<?php } ?>
			</tr>
		</thead>
		<tbody>
			<?PHP
                foreach ($res as $data) {
					$data['ostat'] = $data['stat'];
					$data = parseOrder($data);
					$data['title'] = ($data['has_set'] == 'Y') ? makeOrderTitle($data['ono'], false, $prd_part) : strip_tags($data['opname']);
					$data['title'] = addslashes(str_replace("'", "\"", $data['title']));

					$date2 = ($data['date2'] > 0) ? date("Y/m/d h:i:s A", $data['date2']) : " -";
					$date3 = ($data['date3'] > 0) ? date("Y/m/d h:i:s A", $data['date3']) : " -";
					$date4 = ($data['date4'] > 0) ? date("Y/m/d h:i:s A", $data['date4']) : " -";
					$date5 = ($data['date5'] > 0) ? date("Y/m/d h:i:s A", $data['date5']) : " -";

					$gift_img = $data['order_gift'] ? "<img src='$engine_url/_manage/image/icon/ic_gift.gif' alt='사은품 지급'> " : "";

					$dono = $data['ono'];
					if($data['print']>0) {
						$dono = "<font color=\"#3300CC\" onmouseover=\"showToolTip(event,'인쇄:".$data['print']."회')\" onmouseout=\"hideToolTip();\">$dono</font>";
					}
					if($order_stat_group == 8 && !$approval){
						$card_ck = $pdo->row("select `stat` from `$tbl[card]` where `wm_ono`='$data[ono]' limit 1");
					}

					$data['conversion'] = dispConversion($data['conversion']);
					$data['mobile_icon'] = ($data['mobile'] == 'Y') ? "mobile" : "";
					$data['mobile_icon'] = ($data['mobile'] == 'A') ? "app" : $data['mobile_icon'];

					$res_msg = null;
					$pg = null;
					$pg_cc = null;
					if($data['ostat'] == 31) {

						if($data['pay_type'] == 4) $pg = $pdo->assoc("select `res_msg`, `stat` from `$tbl[vbank]` where `ono`='$data[ono]'");
						else $pg = $pdo->assoc("select `res_msg`, `stat` from `$tbl[card]` where `wm_ono`='$data[ono]'");

						if($pg['stat'] == 3) $pg_cc = $pdo->assoc("select `res_msg`, `stat` from `$tbl[card_cc_log]` where `ono`='$data[ono]'");
						if($pg['res_msg']) $res_msg = $pg['res_msg'];
						if($pg_cc['res_msg']) $res_msg = $pg_cc['res_msg'];
					}

					$printOno = ($card_ck == "2" || ($order_stat_group == 8 && $approval)) ? "<font color='#FF0000'>$dono</font>" : $dono;

					$dlv_tulltip  = "<b>주문</b> : ".date("Y/m/d h:i:s A", $data['date1'])."<br>";
					$dlv_tulltip .= "<b>입금</b> : $date2<br>";
					$dlv_tulltip .= "<b>상품준비</b> : $date3<br>";
					$dlv_tulltip .= "<b>배송시작</b> : $date4<br>";
					$dlv_tulltip .= "<b>배송완료</b> : $date5";

					if(!$_GET['is_cashreceipt']) {
						$data['cash_receipt'] = $pdo->row("select stat from $tbl[cash_receipt] where ono='$data[ono]' order by no desc limit 1");
					}

					switch($data['cash_receipt']) {
						case '1' : $receipt = "<img src='$engine_url/_manage/image/icon/ic_receipt_1.gif' alt='현금영수증 신청'>"; break;
						case '2' : $receipt = "<img src='$engine_url/_manage/image/icon/ic_receipt_2.gif' alt='현금영수증 발급'>"; break;
						case '3' : $receipt = "<img src='$engine_url/_manage/image/icon/ic_receipt_3.gif' alt='현금영수증 취소'>"; break;
						default  : $receipt = '';
					}

					if($receipt) {
						$receipt = "<a href='?body=order@order_cash_receipt_new&stat=all&search_type=ono&search_str=$data[ono]' target='_blank'>$receipt</a>";
					}

					if($cfg['ord_list_first_prc'] == 'Y') {
						$first_prc = $pdo->row("select amount from $tbl[order_payment] where ono='$data[ono]' and type=0");
						$data['first_prc'] = parsePrice($first_prc, true);
						if($data['pay_prc'] != $first_prc) {
							$data['first_prc'] = "<span class='p_color'>$data[first_prc]</span>";
						}
					}
					if($NumTotalRec==$idx) {
						addPrivacyViewLog(array(
							'page_id' => 'order',
							'page_type' => 'list',
							'target_id' => $data['member_id'],
							'target_cnt' => $NumTotalRec
						));
					}

                    // 리스트에서 개인정보 마스킹
                    $data['member_id_v'] = $data['member_id'];
                    if ($scfg->comp('use_order_list_protect', 'Y') == true) {
                        $data['name'] = strMask($data['name'], 2, '＊');
                        $data['member_id_v'] = strMask($data['member_id'], 5, '***');
                    }

			?>
			<tr>
				<td <?php if($data['ostat'] == 31 && $res_msg) { ?>rowspan='2'<?php } ?>><input type="checkbox" name="check_pno[]" id="check_pno" value="<?=$data['no']?>"></td>
				<td <?php if($data['ostat'] == 31 && $res_msg) { ?>rowspan='2'<?php } ?>><?=$idx?></td>
				<td class="left" style="white-space: nowrap;">
					<?php if ($data['checkout'] == 'Y') { ?>
					<img src="<?=$engine_url?>/_manage/image/order/ic_npay.png">
					<?php } ?>
					<?php if ($data['external_order'] == 'talkpay') { ?>
					<img src="<?=$engine_url?>/_manage/image/order/ic_talkpay.png" style="width:16px">
					<?php } ?>
					<?php if ($data['smartstore'] == 'Y' ) { ?>
					<img src="<?=$engine_url?>/_manage/image/order/ic_smartstore.png">
					<?php } ?>
					<?php if ($data['talkstore'] == 'Y') { ?>
					<img src="<?=$engine_url?>/_manage/image/order/ic_talkstore.png">
					<?php } ?>
					<?php if ($data['x_order_id'] == 'subscription') { ?>
					<img src="<?=$engine_url?>/_manage/image/order/ic_subscription.png">
					<?php } ?>
					<a href="javascript:;" onClick="viewOrder('<?=$data['ono']?>')"><strong><?=$printOno?></strong></a>
					<?php if ($cfg['ord_list_memo_icon'] == 'Y' && $data['memo_cnt'] > 0) { ?>
					<a href="javascript:;" onClick="viewOrderMemo('<?=$data['ono']?>','#mng_memo_area')"><img src="<?=$engine_url?>/_manage/image/icon/img_mod.gif" alt="메모" onmouseover="showToolTip(event,'관리자 메모:<?=$data['memo_cnt']?>개')" onmouseout="hideToolTip();"></a>
					<?php } ?>
					<?php if ($_stores[$data['s_order_id']]) { ?>
					<span class="p_color">[<?=$_stores[$data['s_order_id']]?>]</span>
					<?php } ?>
					<?php if ($data['openmarket_id']) { ?>
					<strong class="p_color">[<?=$opmk_names[$data['openmarket_id']]?>]</strong>
					<?php } ?>
				</td>
				<td class="left order_title" title="" data-ono="<?=$data['ono']?>">
					<div class="magicDIV <?=$data['mobile_icon']?>">
						<?=$gift_img?><?=stripslashes($data['title'])?>
					</div>
				</td>
				<td onmouseover="showToolTip(event,'<?=$dlv_tulltip?>')" onmouseout="hideToolTip();"><?=date("m/d H:i:s",$data['date1'])?></td>
				<td>
                    <?=stripslashes($data['buyer_name'])?> <?=blackIconPrint('',$data)?>
                    <?php if ($data['level'] > 0) { ?>
                    <div class="explain"><?=getGroupName($data['level'])?></div>
                    <?php } ?>
                </td>
				<?php if ($cfg['ord_list_phone'] == 'Y') { ?>
				<td><?=$data['buyer_cell']?></td>
				<?php } ?>
				<?php if ($cfg['recipient'] == 'Y') { ?>
				<td><?=stripslashes($data['addressee_name'])?></td>
				<?php } ?>
				<?php if ($cfg['bank_name2'] == 'Y') { ?>
				<td><?=$receipt?> <font color="#FF6600"><?=stripslashes($data['bank_name'])?></font></td>
				<?php } ?>
				<?php if ($_use['recom_member'] == 'Y') { ?>
				<td><?=stripslashes($data['recom_member'])?></td>
				<?php } ?>
				<td onmouseover="showToolTip(event,'<b>상품가격</b> : <?=parsePrice($data['prd_prc'], true)?> 원<br><b>배송비</b> : <?=parsePrice($data['dlv_prc'], true)?> 원<br>')" onmouseout="hideToolTip();"><?=parsePrice($data['total_prc'], true)?></td>
				<?php if ($cfg['ord_list_first_prc'] == 'Y') { ?>
				<td><?=$data['first_prc']?></td>
				<?php } ?>
				<?php if($cfg['bank_price'] == 'Y') { ?>
				<td>
					<?=parsePrice($data['pay_prc'], true)?>
				</td>
				<?php } ?>
				<td><?=$pay_type?></td>
				<?php if ($order_stat_group == 10) { ?>
				<td><?=date('Y-m-d', $data['del_date'])?></td>
				<?php if ($cfg['use_trash_ord'] == 'Y' && $cfg['trash_ord_trcd'] > 0) { ?>
				<td><?=date('Y-m-d', strtotime($cfg['trash_ord_trcd'].'day', $data['del_date']))?></td>
				<?php } ?>
				<td><?=$data['del_admin']?></td>
				<?php } else { ?>
				<td class='right'><ul class="list_common"><li><?=$data['stat']?></li></ul></td>
				<?php if ($cfg['ord_list_postpone'] == 'Y') { ?>
				<td><?=$data['postpone_yn']?></td>
				<?php } ?>
				<td><?=$data['conversion']?></td>
				<?php } ?>
			</tr>
			<?php
				if($data['ostat'] == 31 && $res_msg) {
			?>
			<tr>
				<td colspan="20"><img src="<?=$engine_url?>/_manage/image/icon/ic_alert.png" alt="" width="15px"> <span class="desc3"><?=$res_msg?></span></td>
			</tr>
			<?php
				}
				$idx--;
			}
			?>
		</tbody>
	</table>
	<!-- 페이징 & 버튼 -->
	<?php if ($order_stat_group != 10) { ?>
	<div class="box_middle2">
		<ul class="list_info left">
			<li><?=$cfg['mobile_name']?>에서 주문된 내역은 <img src="<?=$engine_url?>/_manage/image/mobile_icon.gif" align="absmiddle"> 아이콘이 표시됩니다.</li>
			<li>송장번호가 입력된 주문서의 경우 상태 클릭 시 송장추적이 가능합니다.</li>
			<li>주문서를 인쇄한 주문서의 경우 주문번호의 색상이 파란색으로 변경됩니다.</li>
			<?php if ($admin['level'] < 4) { ?>
			<li>주문 설정 내 주문 관리 설정을 통해 주문서 인쇄 시 주문을 자동으로 <?=$_order_stat[3]?>으로 변경할 수 있습니다. <a href="javascript:goM('config@order')">바로가기</a></li>
			<?php } ?>
		</ul>
	</div>
	<?php } ?>

	<?php if ($body == 'order@order_trash') { ?>
	<div class="box_bottom">
		<?=$pg_res?>
		<div class="left_area">
			<span class="box_btn"><input type="button" value="선택 복구" onclick="restoreOrd(document.prdFrm);"></span>
		</div>
		<div class="right_area">
			<span class="box_btn gray"><input type="button" value="휴지통 비우기" onclick="deleteOrd(document.prdFrm, true);"></span>
			<span class="box_btn gray"><input type="button" value="영구 삭제" onclick="deleteOrd(document.prdFrm);"></span>
		</div>
	</div>
	<?php } else { ?>
	<div class="box_bottom">
		<?=$pg_res?>
		<?php if ($admin['level'] < 4) { ?>
		<div class="left_area">
			<span class="box_btn_s icon delete"><input type="button" value="선택 삭제" onclick="deleteOrd(document.prdFrm);"></span>
		</div>
		<?php } ?>
		<div class="right_area">
			<?php if ($use_pack['print'] != 'Y') { ?>
			<span class="box_btn_s icon print"><input type="button" value="주문서 인쇄" onclick="printOrder(document.prdFrm);"></span>
			<?php } ?>
			<span class="box_btn_s icon stats"><input type="button" value="주문 상품수량 통계" onclick="ordPrd();"></span>
		</div>
	</div>
	<!-- //페이징 & 버튼 -->
	<!-- 하단 탭 메뉴 -->
	<div id="controlTab">
		<ul class="tabs">
			<li id="ctab_1" onclick="tabSH(1)" class="selected">일괄상태변경</li>
			<?php if ($admin['level'] < 4) { ?>
			<li id="ctab_2" onclick="tabSH(2)">문자 발송</li>
			<?php } ?>
		</ul>
		<div class="context">
			<div id="edt_layer_1">
				<div class="box_middle2 left">
					선택된 주문서 내
					<select name="ext1">
						<?php if ($admin['level'] < 4) { ?>
						<option value="">전체</option>
						<option value="1"><?=$_order_stat[1]?></option>
						<?php } ?>
						<option value="2"><?=$_order_stat[2]?></option>
						<option value="3"><?=$_order_stat[3]?></option>
						<option value="4"><?=$_order_stat[4]?></option>
						<option value="5"><?=$_order_stat[5]?></option>
					</select>
					상태의 상품을
					<select name="ext2">
						<?php if ($admin['level'] < 4) { ?>
						<option value="1"><?=$_order_stat[1]?></option>
						<option value="2" <?=checked($order_stat_group, 9, true)?>><?=$_order_stat[2]?></option>
						<?php } ?>
						<option value="3"><?=$_order_stat[3]?></option>
						<option value="4"><?=$_order_stat[4]?></option>
                        <?php if ($admin['level'] < 4) { ?>
						<option value="5"><?=$_order_stat[5]?></option>
                        <?php } ?>
					</select>
					상태로 변경합니다.
                    <p>
                        <label>
                            <input type="checkbox" name="except_hold" value="Y" checked>
                            배송보류 상태의 주문을 제외하고 처리합니다.
                        </label>
                    </p>
				</div>
				<div class="box_bottom"><span class="box_btn blue"><a href="javascript:" onclick="chgOrdStat(document.prdFrm);">확인</a></span></div>
			</div>
			<div id="edt_layer_2" style="display: none">
				<div class="box_middle2 left">
					<select name="ssmode">
						<option value="2">선택한 주문</option>
						<option value="4">검색된 모든 주문(<?=number_format($NumTotalRec)?>명)</option>
						<option value="3">전체 주문</option>
					</select>
					<label><input type="checkbox" name="smsblock" value="Y" checked> 수신거부 주문제외</label>
					<label><input type="checkbox" name="correct_num" value="Y" checked> 부정확한 번호제외</label>
				</div>
				<div class="box_bottom"><span class="box_btn blue"><a href="javascript:" onclick="multiSMS();">확인</a></span></div>
			</div>
		</div>
	</div>
	<!-- //하단 탭 메뉴 -->
	<?php } ?>
</form>
<!-- //검색 테이블 -->
<script type="text/javascript">
	var mw='<?=addslashes($w)?>';
	var use_trash_ord = '<?=$cfg['use_trash_ord']?>';
	function deleteOrd(f, trnc){
		if(trnc != true && !checkCB(f.check_pno,"삭제할 주문을 선택해주세요.")) return;

		var del_msg = (use_trash_ord == 'Y') ?
			'선택한 주문을 휴지통으로 이동시키겠습니까?\n휴지통에 이동된 주문은 설정 된 기간 경과 후 자동으로 영구삭제 됩니다.' :
			'선택한 모든 주문이 삭제됩니다.\n정말로 주문을 삭제하시겠습니까?';
		if(trnc == true) {
			del_msg = '휴지통을 비우면 휴지통의 모든 주문이 영구삭제됩니다.\n영구삭제된 주문은 절대 복구할 수 없습니다.\n정말로 주문 휴지통을 비우시겠습니까?';
		}
		if(!confirm(del_msg)) return;
		f.body.value="order@order_update.exe";
		f.exec.value = (trnc == true) ? 'truncate' : 'delete';
		f.method='post';
		f.target=hid_frame;
		f.ext.value="all";
		f.submit();
	}

	function restoreOrd(f) {
		if(!checkCB(f.check_pno, '복구할 주문을 선택해주세요.')) return;
		if(!confirm('선택하신 주문을 복구 하시겠습니까?\n주문서 삭제 시 증가된 재고가 다시 차감되며,\n재고가 0개 이하로 변경될 수 있습니다.')) return;

		f.body.value = 'order@order_update.exe';
		f.exec.value = 'restore';
		f.method = 'post';
		f.ext.value = 'all';
		f.target = hid_frame;
		f.submit();
	}

	function chgOrdStat(f){
		if(!checkCB(f.check_pno,"변경할 주문을 선택해주세요.")) return;
		if (!confirm('선택하신 주문의 상태를 일괄 변경하시겠습니까?     ')) return;

        printLoading();

		f.body.value="order@order_multi.exe";
		f.method='post';
		f.target=hid_frame;
		f.submit();
	}

	function ordPrd(){
		chk=confirm('현재 검색된 주문상품의 수량 및 옵션의 통계를 작성합니다.\n검색결과가 500건 이상일 경우에 분석 중 멈출 수 있습니다.\n거래상태 및 기간을 먼저 검색한 후에 분석을 시작하십시오.\n\n확인을 누르면 분석을 시작합니다.');
		if (chk)
		{
			location.href='./?body=order@order_product<?=$xls_query?>';
		}
	}

	var tmp_lyr='';
	function multiSMS(tp){
		f=document.prdFrm;
		if(tp==1){
			layerSH('smsDiv');
			return;
		}else{
			if (f.ssmode.value==2)
			{
				if(!checkCB(f.check_pno,"문자를 발송할 주문을 선택해주세요.")) return;
			}
			if (f.ssmode.value==3){
				if(!confirm("전체 주문자에게 문자를 발송하시겠습니까?")) return;
			}
			if(f.smsblock.checked) f.sms_deny.value="Y";
			else f.sms_deny.value="N";

			window.open('','wm_sms','top=10,left=200,width=920,height=650,status=no,toolbars=no,scrollbars=yes');
			var old_body=f.body.value;
			f.body.value='member@sms_sender.frm';
			f.target='wm_sms';
			f.method='post';
			f.msg_where.value=mw;
			f.exec.value='from_ord';
			f.submit();

			f.body.value=old_body;
			f.exec.value='';
			f.target='';
		}
	}

	function layerSH(layer_name){
		 if(tmp_lyr != layer_name && tmp_lyr != ''){
			 if(document.getElementById(tmp_lyr).style.display == 'block') layTgl2(tmp_lyr);
		 }
		 tmp_lyr=layer_name;
		 layTgl(document.getElementById(layer_name));
	}

	function showExcelBtn(ev) {
		var ev = window.event ? window.event : ev;
		var layer = document.getElementById('excelLayer');

		if(layer.style.display == 'block') {
			layer.style.display = 'none';
		} else {
			layer.style.display = 'block';
			layer.style.position = 'absolute';
			layer.style.top = ($(document).scrollTop()+ev.clientY+25)+'px';
			layer.style.right = '72px';
		}
	}

	function orderExcel() {
		var f = document.getElementById('excelLayer');
		var checked = '';

		if(f.xls_searchtype[1].checked == true) {
			var check_pno = document.getElementsByName('check_pno[]');
			for(var i = 0; i < check_pno.length; i++) {
				if(check_pno[i].checked == true) checked += ','+check_pno[i].value;
			}
			f.checked.value = checked.replace(/^,/, '');
			if(f.checked.value == '') {
				window.alert('엑셀로 저장할 주문을 선택하세요.');
				return false;
			}
		}

		f.submit();
	}

	var psearch = new layerWindow('product@product_inc.exe');
	psearch.psel = function(pno) {
		$.post('?body=order@order_search_inc.exe', {"exec":"prd", "pno":pno}, function(data) {
			$('#search_prd').html(data);
		})
		this.close();
	}

	function searchOrderPrd(obj) {
		psearch.input = obj;
		psearch.open();
	}

	function getOpenmarketOrders() {
		setDimmed('#000000', '.2');
		$.post('./index.php', {'body':'order@order_update.exe', 'exec':'getOpenmarketOrder'}, function(r) {
			window.alert(r);
			location.reload();
		});
	}

	function viewOrderMemo(ono) {
		setDimmed();
		window.memoLayer = new layerWindow('order@order_memo.exe');
		window.memoLayer.open('&exec=viewList&ono='+ono);
	}

	$('input[name=search_str]').bind({
		"focus" : function() {
			$('#btn_search_setup').mouseenter();
		},
		"blur" : function() {
			$('#btn_search_setup').mouseleave();
		}
	});

	var oconfig = new layerWindow('order@order_config_order.exe');

	// 주문 상품 미리보기
	$('.order_title').tooltip({
		'show': {'effect':'fade', 'duration':100},
		'hide': {'effect':'fade', 'duration':100},
		'track': true,
		'content': function(callback) {
			var ono = $(this).attr('data-ono');
			$.ajax({
				'url': "./index.php",
				'data': {'body':'order@order_preview.exe', 'ono':ono},
				'type': "GET",
				'success': function(r) {
					callback(r);
				}
			});
		}
	}).click(function() {
		var ono = $(this).attr('data-ono');
		viewOrder(ono);
	});

    function changeMembeType(val)
    {
        if (val == 'Y') {
            $('select[name=mgroup]').prop('disabled', false).removeClass('input_disabled');
        } else {
            $('select[name=mgroup]').prop('disabled', true).addClass('input_disabled');
        }
    }

    $(function() {
        $(':radio[name=member_type]').on('ready change', function() {
            changeMembeType(this.value);
        });
        changeMembeType($(':radio:checked[name=member_type]').val());
    });
</script>
<style type="text/css">
.order_title {
	cursor: pointer;
}
.magicDIV {
	height: 16px;
	overflow: hidden;
}

.magicDIV.mobile {
	padding-left: 15px;
	background: url('<?=$engine_url?>/_manage/image/mobile_icon.gif') no-repeat left 0;
}

.magicDIV.app {
	padding-left: 15px;
	background: url('<?=$engine_url?>/_manage/image/app_icon.gif') no-repeat left 0;
}
</style>