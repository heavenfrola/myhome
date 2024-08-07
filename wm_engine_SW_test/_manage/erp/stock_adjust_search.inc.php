<?PHP

	$_search_type['name']='상품명';
	$_search_type['barcode']='바코드';
	$_search_type['content2']='내용';
	$_search_type['keyword']='검색 키워드';
	$_search_type['code']='상품 코드';
	$_search_type['hash']='시스템 코드';
	$_search_type['seller']='사입처';
	$_search_type['origin_name']='장기명';
	$_search_type['mng_memo']='관리자 메모';

	// 상품 등록일자
	$all_date2 = ($_GET['all_date2'] == 'Y') ? 'Y' : 'N';
	$start_rdate = $_GET['start_rdate'];
	$finish_rdate = $_GET['finish_rdate'];
	if(!$start_rdate || !$finish_rdate) $all_date2 = 'Y';
	if($all_date2 != 'Y') {
		$_start_rdate = strtotime($_GET['start_rdate']);
		$_finish_rdate = strtotime($_GET['finish_rdate'])+86399;
		$w .= " and a.reg_date between '$_start_rdate' and '$_finish_rdate'";
	}
	if(!$start_rdate || !$finish_rdate) $start_rdate = $finish_rdate = date('Y-m-d', $now);

	// 매장분류
	for($i = $cfg['max_cate_depth']; $i >= 1; $i--) {
		$cl = $_cate_colname[1][$i];
		$cval = numberOnly($_GET[$cl]);
		if($cval) {
			$w .= " and a.$cl='$cval'";
			break;
		}
	}

	$w.=" and `wm_sc`='0'";

	// 검색어
	$search_type = $_GET['search_type'];
	$search_str = trim($_GET['search_str']);
	if($_search_type[$search_type] && $search_str) {
		$_search_str = addslashes($search_str);
		$w.=" and `$search_type` like '%$_search_str%'";
	}

	if($admin['level'] == 4) {
		$w .= " and a.partner_no='$admin[partner_no]'";
	}

	// 정렬순서
	$stock_sort = array("a.edt_date desc", "a.edt_date", "a.no desc", "a.no", "a.name desc", "a.name", "b.barcode desc", "b.barcode");
	$sort = numberOnly($_GET['sort']);
	if($sort == '') $sort = 2;
	if(!$sort_str) $sort_str = $stock_sort[$sort];

	// 강제품절 상태
	$force = $_GET['force'];
	if(!$force) $force = array();
	else {
		$_force = preg_replace('/(.{1})/', "'$1'", implode(',', $force));
		$w .= " and force_soldout in ($_force)";
	}
	foreach($_erp_force_stat as $key => $val) {
		$checked = in_array($key, $force) ? 'checked' : '';
		$force_select .= "<label class='p_cursor'><input type='checkbox' name='force[]' value='$key' $checked /> $val\n</label>";
	}

	// 부족재고만 조회
	$shortage = ($_GET['shortage'] == 'Y') ? 'Y' : 'N';
	if($shortage == "Y") $h = " having `current_qty` <= 0";

	if($cfg['erp_force_limit'] == 'Y') {
		$add_field = " , b.limit_qty";
	}
	if($_GET['ebig']) {
		$w .= " and a.`ebig` like '%@$_GET[ebig]@%'";
	}
	if($_GET['mbig']) {
		$w .= " and a.`mbig` like '%@$_GET[mbig]@%'";
	}

	// 실시간 재고 조회
	if($cfg['max_cate_depth'] >= 4) {
		$add_field .= ", a.depth4";
	}
	$list_sql = "select a.no, a.hash, a.name, a.code, a.origin_name, a.updir, a.upfile3, a.w3, a.h3, a.prd_type, a.wm_sc, a.big, a.mid, a.small, a.storage_no ".
				"		,b.complex_no, b.barcode, b.force_soldout, b.safe_stock_qty, b.opts $add_field ".
				"		, pr.provider, pr.arcade, pr.floor, pr.plocation, pr.ptel".
				"		, curr_stock(b.complex_no) as `current_qty`".
				" from `wm_product` a inner join `erp_complex_option` b on a.no = b.pno left join wm_provider pr on a.seller_idx = pr.no".
				" where a.stat in (2,3,4) and b.del_yn = 'N'".
				$w.
				$h.
				" order by {$sort_str}, complex_no asc";

?>