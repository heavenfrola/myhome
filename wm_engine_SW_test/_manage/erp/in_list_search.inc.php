<?PHP

	$_search_type['name'] = '상품명';
	$_search_type['barcode'] = '바코드';
	$_search_type['content2'] = '내용';
	$_search_type['keyword'] = '검색 키워드';
	$_search_type['code'] = '상품 코드';
	$_search_type['hash'] = '시스템 코드';
	$_search_type['origin_name'] = '장기명';
	$_search_type['mng_memo'] = '관리자 메모';

	// 조정일자
	$start_date = addslashes($_GET['start_date']);
	$finish_date = addslashes($_GET['finish_date']);
	$all_date = ($_GET['all_date'] == 'Y') ? 'Y' : 'N';
	if(!$start_date || !$finish_date) $all_date="Y";
	if($all_date != 'Y') {
		$w .= " and c.reg_date between '$start_date 00:00:00' and '$finish_date 23:59:59'";
	}
	if(!$start_date || !$finish_date) $start_date = $finish_date = date('Y-m-d', $now);

	// 매장분류
	$only_cate = ($_GET['only_cate'] == 'Y') ? 'Y' : 'N';
	if($only_cate == 'Y') {
		for($i = 1; $i <= $cfg['max_cate_depth']; $i++) {
			$cl = $_cate_colname[1][$i];
			$cval = numberOnly($_REQUEST[$cl]);
			if($cval) $cval = 0;
			$w .= " and a.$cl='$cval'";
		}
	} else {
		for($i = $cfg['max_cate_depth']; $i >= 1; $i--) {
			$cl = $_cate_colname[1][$i];
			$cval = numberOnly($_REQUEST[$cl]);
			if($cval > 0) {
				$w .= " and a.$cl='$cval'";
				break;
			}
		}
	}

	$seller = numberOnly($_GET['seller']);
	if($seller) {
		$w .= " and c.sno='$seller'";
	}

	// 검색어
	$search_type = $_GET['search_type'];
	$search_str = trim($_GET['search_str']);
	if($_search_type[$search_type] && $search_str) {
		$_search_str = addslashes($search_str);
		$w.=" and `$search_type` like '%$_search_str%'";
	}

	// 정렬순서
	$stock_sort = array('c.reg_date desc, a.name', 'c.reg_date, a.name', 'd.provider desc, a.name', 'd.provider, a.name', 'a.name desc', 'a.name', 'b.barcode desc', 'b.barcode');
	$sort = numberOnly($_GET['sort']);
	if(!$sort) $sort = 0;
	$sort_str = $stock_sort[$sort];

	// 실시간 재고 조회
	if($cfg['max_cate_depth'] >= 4) {
		$add_field .= ", a.depth4";
	}
	$list_sql = "select a.no, a.hash, a.name, a.updir, a.upfile3, a.w3, a.h3, a.prd_type, a.origin_name, a.wm_sc, a.big, a.mid, a.small" .
		   "      , b.complex_no, b.barcode, b.opts ".
		   "	  , c.inout_no, c.reg_date, c.sno, c.qty, c.in_price, c.qty " .
		   "	  , d.provider, d.arcade, d.floor, d.plocation, d.pceo, d.pcell, d.ptel ".
		   "	  , d.account1_name, d.account1_bank, d.account1 ".
		   "	  , d.account2_name, d.account2_bank, d.account2 $add_field ".
		   "  from wm_product a, erp_complex_option b, erp_inout c, wm_provider d" .
		   " where a.stat in (2, 3, 4) and a.no = b.pno and c.inout_kind = 'I'" .
		   "   and b.complex_no = c.complex_no and c.sno = d.no and b.del_yn = 'N'" .
		   $w .
		   " order by {$sort_str}";

?>