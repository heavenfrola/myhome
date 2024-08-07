<?PHP

	$no = numberOnly($_POST['no']);
	if(!$no) $no = 0;
	$search_str = mb_convert_encoding(trim($_GET['search_str']), _BASE_CHARSET_, 'utf8');

	// 상품검색
	include_once $engine_dir."/_engine/include/paging.php";

	$page = addslashes($_REQUEST['page']);
	if($page <= 1) $page = 1;
	$row = 5;
	if($search_str) $where .= " and a.name like '%$search_str%'";
	if($cfg['use_prd_perm'] == 'Y') {
		$where .= " and (perm_lst='Y' and perm_sch='Y')";
	}

	$NumTotalRec = $pdo->row("select count(*) from $tbl[product] a inner join $tbl[category] c on a.big=c.no where a.stat in (2,3) and c.private!='Y' $where");
	$PagingInstance = new Paging($NumTotalRec, $page, $row, 10);
	$PagingInstance->addQueryString($QueryString);
	$PagingResult=$PagingInstance->result($pg_dsn);

	$pg_res = $PagingResult[PageLink];
	$pg_res = preg_replace('/href="\?page=([0-9]+)"/', "href='#' onclick='refProductSearch(this.form, $1); return false;'", $pg_res);
	$res = $pdo->query($sql);
	$idx = $NumTotalRec-($row*($page-1));


	// form 출력
	$_tmp  = "<form id='product_search_frm' method='post' onsubmit='this.page.value=1; return refProductSearch(this)' style='width:700px;'>";
	$_tmp .= "<input type='hidden' name='no' value='$no' />";
	$_tmp .= "<input type='hidden' name='page' value='$page' />";
	$_replace_code[$_file_name]['common_products_form_start'] = $_tmp;
	$_replace_code[$_file_name]['common_products_form_end'] = '</form>';

	$w = $_skin['common_product_select_list_w'] ? $_skin['common_product_select_list_w'] : 60;
	$h = $_skin['common_product_select_list_h'] ? $_skin['common_product_select_list_h'] : 60;

	$_tmp = '';
	$res = $pdo->iterator("select a.no, a.hash, a.name, a.sell_prc, a.normal_prc, a.updir, a.upfile3, a.w3, a.h3, a.reg_date, c.name as big_name from $tbl[product] a inner join $tbl[category] c on a.big=c.no where a.stat in (2,3) and c.private!='Y' $where order by name asc ".$PagingResult['LimitQuery']);
	$_line = getModuleContent('common_product_select_list');
    foreach ($res as $data) {
		$data['big_name'] = stripslashes(strip_tags($data['big_name']));
		$data['checkbox'] = "<input type='checkbox' name='product_select_pno[]' class='pno' value='$data[no]' />";

		$data = prdOneData($data, $w, $h, 3);
		$_tmp .= lineValues("common_product_select_list", $_line, $data);
	}
	$_tmp = listContentSetting($_tmp, $_line);

	$_replace_code[$_file_name]['common_product_select_list'] = $_tmp;
	$_replace_code[$_file_name]['common_product_page'] = $pg_res;
	$_replace_code[$_file_name]['common_product_search_str'] = inputText($search_str);
	$_replace_code[$_file_name]['common_product_search_ok'] = "<a href='#' onclick='refProductSelectOK(true); return false;'>";

	unset($pg_res, $res, $_tmp, $_line, $PagingInstance);

?>