<?PHP

	$_search_type['name'] = '상품명';
	$_search_type['barcode'] = '바코드';
	$_search_type['content2'] = '내용';
	$_search_type['keyword'] = '검색 키워드';
	$_search_type['code'] = '상품 코드';
	$_search_type['hash'] = '시스템 코드';
	$_search_type['seller'] = '사입처';
	$_search_type['origin_name'] = '장기명';
	$_search_type['mng_memo'] = '관리자 메모';

	$w = " and c.inout_kind in ('U', 'P')";

	$all_date = $_GET['all_date'];
	$start_date = $_GET['start_date'];
	$finish_date = $_GET['finish_date'];
	$all_date2 = $_GET['all_date2'];
	$start_rdate = $_GET['start_rdate'];
	$finish_rdate = $_GET['finish_rdate'];

	// 조정일자
    if (!$start_date) $start_date = date('Y-m-d', strtotime('-15 DAYS'));
    if (!$finish_date) $finish_date = date('Y-m-d');
    // 상품등록일자
	if(!$start_rdate || !$finish_rdate) $all_date2 = 'Y';
	if(!$all_date) {
		$w .= " and c.reg_date between '$start_date 00:00:00' and '$finish_date 23:59:59'";
	}
	if(!$start_date || !$finish_date) $start_date = $finish_date = date('Y-m-d', $now);
	if(!$all_date2) {
		$_start_rdate = strtotime($start_rdate);
		$_finish_rdate = strtotime($finish_rdate)+86399;
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

	$w .= " and `wm_sc`='0'";

	// 검색어
	$search_type = $_GET['search_type'];
	$search_str = trim($_GET['search_str']);
	if($_search_type[$search_type] && $search_str) {
		$_search_str = addslashes($search_str);
		$w .= " and `$search_type` like '%$_search_str%'";
	}

	if($admin['level'] == 4) {
		$w .= " and a.partner_no='$admin[partner_no]'";
	}

	// 정렬순서
	$stock_sort = array('c.reg_date desc, a.name', 'c.reg_date, a.name', 'a.name desc', 'a.name asc', 'b.barcode desc', 'b.barcode asc');
	$sort = $_GET['sort'];
	if($sort == '') $sort = 0;
	if(!$sort_str) $sort_str = $stock_sort[$sort];

	// 실시간 재고 조회
	if($cfg['max_cate_depth'] >= 4) {
		$add_field .= ", a.depth4";
	}
	$list_sql = "select a.no, a.hash, a.name, a.code, a.updir, a.upfile3, a.w3, a.h3, a.prd_type, a.wm_sc, a.big, a.mid, a.small" .
		   "     , b.complex_no, b.barcode, b.opts, c.inout_kind, c.qty, c.reg_user, c.reg_date, c.remark " .
		   "	 , (select sum(if(inout_kind in ('I', 'U'), qty, -qty)) from erp_inout where complex_no=b.complex_no and inout_no < c.inout_no) as prev_qty $add_field ".
		   "  from erp_complex_option b inner join erp_inout c using(complex_no) inner join wm_product a on a.no=b.pno " .
		   " where a.stat!=1 and b.del_yn = 'N'" .
		   $w.
		   " order by {$sort_str}";

?>