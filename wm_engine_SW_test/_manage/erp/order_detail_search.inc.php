<?PHP

	foreach($_GET as $key=>$val) {
		if($key != 'page') $qs .= '&'.$key.'='.$val;
	}
	$xls_query = preg_replace('/&body=[^&]+/', '', $qs);
	$xls_query = str_replace('&body='.$pgCode, '', $xls_query);
	$xls_query = preg_replace('/&?exec=[^&]+/', '', $xls_query);

	if(!$_SESSION['listURL']) $_SESSION['listURL'] = '?body=erp@order_list';

	$ono = addslashes(trim($_GET['ono']));
	$stat = array(1 => '발주', 2 => '부분입고', 3 => '입고완료', 5 => '발주취소');
	$order = $pdo->assoc("select order_no, ifnull((select b.provider from wm_provider b where a.sno = b.no), '전체') as provider, order_date, order_stat, total_qty, total_amt from erp_order a where a.order_no = '{$ono}'");

	if($cfg['max_cate_depth'] >= 4) {
		$add_field .= ", a.depth4";
	}

	$sql = "select a.no, a.hash, a.name, a.updir, a.upfile3, a.w3, a.h3, a.prd_type, a.wm_sc, a.origin_name, a.big, a.mid, a.small, c.sno, c.order_dtl_no, d.arcade, d.floor, d.provider $add_field " .
		   "     , b.complex_no, b.barcode, b.opts, c.order_target_qty, c.order_qty, c.order_price, c.remark" .
		   "     , ifnull((select sum(qty) from erp_inout x where inout_kind = 'I' and x.order_dtl_no = c.order_dtl_no and x.complex_no = c.complex_no),0) as in_qty".
		   "  from wm_product a, erp_complex_option b, erp_order_dtl c left join wm_provider d on c.sno = d.no" .
		   " where a.stat in ('2','3','4') and a.no = b.pno" .
		   "   and b.complex_no = c.complex_no and b.del_yn = 'N'" .
		   "   and c.order_no = '{$ono}' $w" .
		   " order by d.arcade, d.floor, d.provider, a.name, b.complex_no";

?>