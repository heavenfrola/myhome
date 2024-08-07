<?PHP

	$stat = array(1 => '발주', 2 => '부분입고', 3 => '입고완료', 5 => '발주취소');

	// 조정일자
	$start_date = addslashes($_GET['start_date']);
	$finish_date = addslashes($_GET['finish_date']);
	$all_date = ($_GET['all_date'] == 'Y') ? 'Y' : 'N';
	if(!$start_date || !$finish_date) $all_date = 'Y';
	if(!$all_date) {
		$w .= " and order_date between str_to_date('$start_date', '%Y-%m-%d') and str_to_date('$finish_date', '%Y-%m-%d')";
	}
	if(!$start_date || !$finish_date) $start_date = $finish_date = date('Y-m-d', $now);

	// 발주상태
	$order_stat = numberOnly($_GET['order_stat']);
	if($order_stat) {
		$w .= " and a.order_stat = '{$order_stat}'";
	}

	// 검색어
	$search_type = addslashes(trim($_GET['search_type']));
	$search_str = addslashes(trim($_GET['search_str']));
	if($search_type && $search_str != '') {
		$w .= " and `$search_type` like '%$search_str%'";
	}

	// 정렬순서
	$stock_sort = array('a.order_no desc', 'a.order_no', 'a.order_date desc', 'a.order_date', 'provider desc', 'provider', 'a.order_stat desc', 'a.order_stat');
	$sort = numberOnly($_GET['sort']);
	if($sort == '') $sort = 0;
	if(!$sort_str) $sort_str = $stock_sort[$sort];


	// 실시간 재고 조회
	$list_sql = "select a.order_no, a.order_date, a.sno, ifnull((select b.provider from wm_provider b where a.sno = b.no), '전체') as provider, a.order_stat, a.total_qty, a.total_amt" .
				"  from erp_order a " .
				" where 1 = 1 " .
			   $w .
			   " order by {$sort_str}";

?>