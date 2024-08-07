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
	$cate_sprit = ' &gt; ';

	// 매장분류
	for($i = $cfg['max_cate_depth']; $i >= 1; $i--) {
		$cl = $_cate_colname[1][$i];
		$cval = numberOnly($_REQUEST[$cl]);
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
		$_search_str = addslashes(str_replace('_', '#_', $search_str));
		$w.=" and `$search_type` like '%$_search_str%' ESCAPE '#'";
	}

	// 정렬순서
	$stock_sort = array('a.edt_date desc', 'a.edt_date', 'c.complex_no desc', 'c.complex_no', 'a.name desc', 'a.name', 'b.barcode desc', 'b.barcode');
	$sort = numberOnly($_GET['sort']);
	if($sort == '') $sort = 2;
	if(!$sort_str) $sort_str = $stock_sort[$sort];

	// 상품 상태
	$stat = numberOnly($_GET['stat']);
	if(!$stat) $stat = array(2, 3, 4);
	$stats = implode(',', $stat);
	$w .= " and a.stat in ($stats)";


	// 강제품절 상태
	$force = $_GET['force'];
	if(!$force) $force = array();
	else {
		$_force = preg_replace('/([a-z])/i', "'$1'", implode(',', $force));
		$w .= " and force_soldout in ($_force)";
	}
	foreach($_erp_force_stat as $key => $val) {
		$checked = in_array($key, $force) ? 'checked' : '';
		$force_select .= "<label class='p_cursor'><input type='checkbox' name='force[]' value='$key' $checked /> $val\n</label>";
	}

	$_todate = date('Y-m-d 00:00L00', $now);

	$shortage = ($_GET['shortage'] == 'Y') ? 'Y' : 'N';
	$outtoday = ($_GET['outtoday'] == 'Y') ? 'Y' : 'N';
	if($shortage == 'Y') $h .= " and stock_qty < 1";
	if($outtoday == 'Y') $h .= " and out_qty > 0";

	if($admin['level'] == 4) {
		$w .= " and a.partner_no='$admin[partner_no]'";
	}

	// 실시간 재고 조회
	if($cfg['max_cate_depth'] >= 4) {
		$add_field .= ", a.depth4";
	}
	if($pdo->row("select count(*) from $tbl[erp_storage]") > 0) {
		$add_field .= ", a.storage_no";
	}
	if($_GET['ebig']) {
		$w .= " and a.`ebig` like '%@$_GET[ebig]@%'";
	}
	if($_GET['mbig']) {
		$w .= " and a.`mbig` like '%@$_GET[mbig]@%'";
	}
	$list_sql = "select a.no, a.hash, a.name, a.origin_name, a.origin_prc, a.updir, a.upfile3, a.w3, a.h3, a.prd_type, a.wm_sc, b.opts, a.big, a.mid, a.small " .
		   "      , b.complex_no, b.barcode, b.force_soldout, b.opt1, b.opt2" .
		   "      , pr.provider, pr.arcade, pr.floor, pr.plocation, pr.ptel".
		   "      , sum(if(inout_kind in ('I', 'U'), c.qty, -c.qty)) as stock_qty ".
		   "      , sum(if(c.reg_date < '$_todate', if(inout_kind in ('I', 'U'), c.qty, -c.qty), 0)) as prev_qty ".
		   "      , sum(if(c.reg_date >= '$_todate' and inout_kind in ('O', 'P'), c.qty, 0)) as out_qty $add_field ".
		   "  from wm_product a inner join erp_complex_option b on a.no=b.pno inner join erp_inout c using(complex_no) left join wm_provider pr on a.seller_idx = pr.no" .
		   " where b.del_yn = 'N' $w ".
		   " group by b.complex_no " .
		   $_sql .
		   " having 1 $h order by {$sort_str}";

?>