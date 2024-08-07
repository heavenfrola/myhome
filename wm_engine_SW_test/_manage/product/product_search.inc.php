<?PHP

	$w = '';

	//검색프리셋
	$spno = numberOnly($_GET['spno']);
	unset($_GET['spno']);
	if($spno) {
		$spdata = $pdo->assoc("select * from $tbl[search_preset] where no='$spno'");
		if($spdata['querystring']) {
			$_GET = array_merge($_GET, json_decode($spdata['querystring'], true));
		}
	}

	if($admin['level'] == 4) $w .= " and p.partner_no='$admin[partner_no]'";

	$only_cate = ($_GET['only_cate'] == 'Y') ? 'Y' : '';
	if($only_cate == 'Y') {
		for($i = 1; $i <= $cfg['max_cate_depth']; $i++) {
			$cl = $_cate_colname[1][$i];
			$cval = numberOnly($_REQUEST[$cl]);
			if(!$cval) $cval = 0;
			$w .= " and p.$cl='$cval'";
		}
	} else {
		for($i = $cfg['max_cate_depth']; $i >= 1; $i--) {
			$cl = $_cate_colname[1][$i];
			$cval = numberOnly($_REQUEST[$cl]);
			if($cval) {
				$w .= " and p.$cl='$cval'";
				break;
			}
		}
	}

	foreach($_cate_colname as $key => $val) {
		foreach($val as $key2 => $val2) {
			${$val2} = numberOnly($_GET[$val2]);
		}
	}
	if($xsmall) $w .= " and p.`xsmall`='$xsmall'";
	elseif($xmid) $w .= " and p.`xmid`='$xmid'";
	elseif($xbig) $w .= " and p.`xbig`='$xbig'";

	if($ysmall) $w .= " and p.`ysmall`='$ysmall'";
	elseif($ymid) $w .= " and p.`ymid`='$ymid'";
	elseif($ybig) $w .= " and p.`ybig`='$ybig'";

	$cno = numberOnly($_GET['cno']);
	$mno = numberOnly($_GET['mno']);
	$prno = numberOnly($_GET['prno']);
	if($cno) $w .= " and ebig like '%@$cno@%'";
	if($mno) $w .= " and mbig like '%@$mno@%'";
	if($prno) {
		$w .= " and pr.`pgrp_no`='$prno'";
		$prd_join .= " inner join $tbl[promotion_pgrp_link] pr on p.no=pr.pno";
	}

	$prd_stat = numberOnly($_GET['prd_stat']);
	if($prd_stat) $w .= " and p.`stat`='$prd_stat'";

	$w .= ($is_trash == 'Y') ? " and p.stat=5" : " and p.stat!=5";

	$ea_type = numberOnly($_GET['ea_type']);
	if($ea_type) $w .= " and p.`ea_type`='$ea_type'";

	$dlv_alone = ($_GET['dlv_alone'] == 'Y') ? 'Y' : '';
	$event_sale = ($_GET['event_sale'] == 'Y') ? 'Y' : '';
	$member_sale = ($_GET['member_sale'] == 'Y') ? 'Y' : '';
	$free_delivery = ($_GET['free_delivery'] == 'Y') ? 'Y' : '';
	$oversea_free_delivery = ($_GET['oversea_free_delivery'] == 'Y') ? 'Y' : '';
	$checkout = ($_GET['checkout'] == 'Y') ? 'Y' : '';
	$talkpay = ($_GET['talkpay'] == 'Y') ? 'Y' : '';
	$smartstore = ($_GET['smartstore'] == 'Y') ? 'Y' : '';
	$talkstore = ($_GET['talkstore'] == 'Y') ? 'Y' : '';
	$timesale = ($_GET['timesale'] == 'Y') ? 'Y' : '';
	$sbscr_product = ($_GET['sbscr_product'] == 'Y') ? 'Y' : '';
	$import_flag = ($_GET['import_flag'] == 'Y') ? 'Y' : '';
	$compare_today_start = ($_GET['compare_today_start'] == 'Y') ? 'Y' : '';
    $tax_free = ($_GET['tax_free'] == 'Y') ? 'Y' : '';
	$no_milage = ($_GET['no_milage'] == 'Y') ? 'Y' : '';
	$no_cpn = ($_GET['no_cpn'] == 'Y') ? 'Y' : '';
    $is_book = $_GET['is_book'];
    $no_ep = ($_GET['no_ep'] == 'N') ? 'N' : '';
	if($dlv_alone) $w .= " and p.`dlv_alone`='$dlv_alone'";
	if($event_sale) $w .= " and p.`event_sale`='$event_sale'";
	if($member_sale) $w .= " and p.`member_sale`='$member_sale'";
	if($free_delivery) $w .= " and p.`free_delivery`='$free_delivery'";
	if(($cfg['delivery_fee_type'] == 'O' || $cfg['delivery_fee_type'] == 'A') && fieldExist($tbl['product'], 'oversea_free_delivery')){
		if($oversea_free_delivery) $w .= " and p.`oversea_free_delivery`='$oversea_free_delivery'";
	}
	if($checkout) $w .= " and p.`checkout`='$checkout'";
	if($talkpay) $w .= " and p.use_talkpay='$talkpay'";
	if($smartstore) $w .= " and p.`n_store_check`='$smartstore'";
	if($talkstore) $w .= " and p.`use_talkstore`='$talkstore'";
	if($timesale) $w .= " and p.ts_ing='Y'";
	if($sbscr_product) {
		$w .= " and ssp.`use`='Y'";
		$prd_join .= " inner join $tbl[sbscr_set_product] ssp on p.no=ssp.pno";
	}
	if($import_flag) $w .= " and p.import_flag='Y'";
	if($compare_today_start) $w .= " and p.compare_today_start='Y'";
    if ($tax_free) $w .= " and p.tax_free='Y'";
	if($no_milage) $w .= " and p.no_milage='Y'";
	if($no_cpn) $w .= " and p.no_cpn='Y'";
    if ($is_book == 'Y') $w .= " and p.is_book!='N'";
    if ($no_ep == 'N') $w .= " and p.no_ep='N'";

	$short_cut = addslashes($_GET['short_cut']);
	if($short_cut == '' && $is_trash!='Y') {
		$w .= " and p.`wm_sc`='0'";
	}
	elseif($short_cut == "B") {
		$w .= " and p.`wm_sc`>'0'";
	}

	$is_mcontent = addslashes($_GET['is_mcontent']);
	if($is_mcontent == 'A') {
		$w .= " and p.m_content!=''";
	}
	elseif($is_mcontent == 'B') {
		$w .= " and p.m_content=''";
	}

	if($_GET['icons']) {
		$_icons = numberOnly(explode(',', 	$_GET['icons']));
		if(is_array($_icons)) {
			$w2 = " and (";
			$ii = 0;
			foreach($_icons as $key => $val) {

				if(empty($val)) continue;
				$ii++;
				if($ii > 1) $w2 .= " or ";
				$w2 .= "concat('@',p.`icons`,'@') like '%@$val@%'";
			}
			$w2 .= ")";
			$w .= $w2;
		}
	}

	$start_prc = numberOnly($_GET['start_prc']);
	if($start_prc > 0) $w .= " and p.`sell_prc`>=$start_prc";
	$finish_prc = numberOnly($_GET['finish_prc']);
	if($finish_prc > 0) $w .= " and p.`sell_prc`<=$finish_prc";

	$start_milage = numberOnly($_GET['start_milage']);
	if($start_milage > 0) $w .= " and p.`milage`>=$start_milage";
	$finish_milage = numberOnly($_GET['finish_milage']);
	if($finish_milage>0) $w .= " and p.`milage`<=$finish_milage";

	$_search_type['name'] = '상품명';
	$_search_type['content2'] = '내용';
	$_search_type['keyword'] = '검색 키워드';
	$_search_type['code'] = '상품 코드';
	$_search_type['hash'] = '시스템 코드';
	$_search_type['seller'] = '사입처';
	$_search_type['origin_name'] = '장기명';
	$_search_type['mng_memo'] = '관리자 메모';

	$search_type = $_GET['search_type'];
	$search_str = addslashes(trim($_GET['search_str']));
	if($_search_type[$search_type] && $search_str != "") {
		$_tmp = explode(',', $search_str);
		if($search_type == 'code' && count($_tmp) > 1) {
			$_search_str = implode(',', array_map(function($val) {
				return "'$val'";
			}, $_tmp));
			$w .= " and p.code in ($_search_str)";
		} else {
			$w .= " and p.`$search_type` like '%$search_str%'";
		}
	}

	if($_GET['seller_chk']) {
		$w .= " and p.seller_idx < 1 and wm_sc='0'";
		if($seller_chk == 'wp') $w .= " and p.ea_type=1";
	}

	$all_date = ($_GET['all_date'] == 'Y') ? 'Y' : '';
	$start_date = preg_replace('/[^0-9-]/', '', $_GET['start_date']);
	$finish_date = preg_replace('/[^0-9-]/', '', $_GET['finish_date']);
	if(!$start_date || !$finish_date) $all_date = "Y";
	if(!$all_date) {
		$w .= " and FROM_UNIXTIME(p.`reg_date`, '%Y-%m-%d') >= '$start_date'";
		$w .= " and FROM_UNIXTIME(p.`reg_date`, '%Y-%m-%d') <= '$finish_date'";
	}
	if(!$start_date || !$finish_date) $start_date = $finish_date = date("Y-m-d", $now);

	if($_GET['ctype']) $ctype = numberOnly($_GET['ctype']);
	if($ctype == 2 || $ctype == 6) {
		$prd_join .= " inner join $tbl[product_link] l on p.no=l.pno ";
		if($cno > 0) $w .= " and l.nbig='$cno'";
		if($mno > 0) $w .= " and l.nbig='$mno'";
		$prd_add_where .= ", l.sort_big, l.idx as sortidx ";
	}

	// 입점몰 검색
	if($cfg['use_partner_shop'] == 'Y') {
		$_partner_names = array('0' => '본사');
		$ptns = $pdo->iterator("select no, corporate_name from $tbl[partner_shop] where stat between 2 and 4 order by corporate_name asc");
        foreach ($ptns as $ptn) {
			$_partner_names[$ptn['no']] = stripslashes($ptn['corporate_name']);
		}

		$partner_no = numberOnly($_GET['partner_no']);
		if ($partner_no !== '') $w .= " and partner_no='$partner_no'";
		$prd_add_where .= ", partner_no";
	}

	if($cfg['prd_name_referer'] == 'Y') {
		$prd_add_where .= ', p.name_referer';
	}

	// 창고 검색
	$sbig = numberonly($_REQUEST['sbig']);
	$smid = numberonly($_REQUEST['smid']);
	$ssmall = numberonly($_REQUEST['small']);
	if($sbig > 0) {
		$prd_join .= " inner join $tbl[erp_storage] st on p.storage_no=st.no";
		$w .= " and st.big='$sbig'";
		if($smid > 0) $w .= " and st.mid='$smid'";
		if($ssmall > 0) $w.= " and st.small='$ssmall'";
	}

	// 상품별 개별 배송비 세트 검색
	if(isset($cfg['use_prd_dlvprc']) == true && $cfg['use_prd_dlvprc'] == 'Y') {
		$_delivery_sets = array();
		$tres = $pdo->iterator("select * from {$tbl['product_delivery_set']} where partner_no='{$admin['partner_no']}' order by set_name asc");
        foreach ($tres as $tset) {
			$_delivery_sets[$tset['no']] = stripslashes($tset['set_name']);
		}
		if(numberOnly($_GET['dset']) > 0) {
			$w .= " and p.delivery_set='{$_GET['dset']}'";
		}
	}

	// 타임세일 세트 검색
	if(isTable($tbl['product_timesale_set']) == true) {
		$_timesale_sets = array();
		$tres = $pdo->iterator("select * from {$tbl['product_timesale_set']} order by name asc");
        foreach ($tres as $tset) {
			if($tset['ts_datee'] == '0000-00-00 00:00:00') $tset['name'] = '[무제한] '.$tset['name'];
            else {
                if(strtotime($tset['ts_datee']) < $now) $tset['name'] = '[종료] '.$tset['name'];
                if(strtotime($tset['ts_dates']) > $now) $tset['name'] = '[시작전] '.$tset['name'];
            }
			$_timesale_sets[$tset['no']] = stripslashes($tset['name']);
		}
		if(numberOnly($_GET['ts_set']) > 0) {
			$w .= " and p.ts_set='{$_GET['ts_set']}'";
		}
	}

	$prd_type = numberOnly($_GET['prd_type']);
	if ($prd_type > 0) {
		$w .= " and p.prd_type='$prd_type'";
	}

?>