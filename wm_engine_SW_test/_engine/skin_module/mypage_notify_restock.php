<?php

	include_once $engine_dir."/_engine/include/paging.php";

	// 신청상태 배열
	$stat_array = array(
		  "1" => "<span class=\"stat1\">".__lang_notify_restock_stat1__."</span>"
		, "2" => "<span class=\"stat2\">".__lang_notify_restock_stat2__."</span>"
		, "3" => "<span class=\"stat3\">".__lang_notify_restock_stat3__."</span>"
		, "4" => "<span class=\"stat4\">".__lang_notify_restock_stat4__."</span>"
	);

	// 재입고알림 목록
	$column = "   dr.*
				, p.`name` as product_name, p.`sell_prc`, p.`updir`, p.`upfile2`, p.`upfile3`, p.`hash` ";
	$where = " 1 AND dr.`del_stat`='N' AND `member_no`='".$member['no']."' ";
	$orderby = " ORDER BY dr.`no` DESC  ";
	$sql = "SELECT 
				$column
			FROM
				".$tbl['notify_restock']." dr 
				LEFT JOIN ".$tbl['member']." m ON dr.`member_no`=m.`no`
				LEFT JOIN ".$tbl['product']." p ON dr.`pno`=p.`no`
			WHERE
				$where
			$orderby
	  ";

	// 카운트
	$count_sql = str_replace($column, " count(dr.`no`) as cnt ", $sql);
	$count_result = $pdo->assoc($count_sql);
	$_replace_code[$_file_name]['mypage_notify_restock_count']=$count_result['cnt'];

	$page = numberOnly($_GET['page']);
	$row = numberOnly($_GET['row']);

	if($page<=1) $page=1;
	if(!$row) $row=10;
	$block=10;

	$QueryString = makeQueryString('page');
	$NumTotalRec=$pdo->row(str_replace($column, " count(*) ", $sql));
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString($QueryString);
	$PagingResult=$PagingInstance->result($pg_dsn);
	$sql.=$PagingResult[LimitQuery];

	$pg_res=$PagingResult[PageLink];
	$_replace_code[$_file_name]['mypage_notify_restock_pg_res']=$pg_res;



	$result = $pdo->iterator($sql);

	$_tmp = "";
	$_line = getModuleContent('mypage_notify_restock_list');
	$_index = $NumTotalRec - (($page-1)*$row);
    foreach ($result as $_row) {
		// 상품이미지
		$_file_url = getFileDir($_row['updir']);
		$_file_url .= "/".$_row['updir']."/".$_row['upfile3'];
		if(!$_row['upfile3']) $_file_url = $root_url."/_image/_default/prd/noimg3.gif";

		// 상품옵션
		$_options_str = $_row['option'];

		$_stat = $_row['stat'];

		$_row['index'] = $_index;
		$_row['reg_date'] = date('Y.m.d', $_row['reg_date']);
		$_row['stat'] = $stat_array[$_stat];
		$_row['stat1'] = ($_stat == 1) ? "Y" : "";
		$_row['sell_prc'] = number_format($_row['sell_prc']);
		$_row['product_img'] = $_file_url;
		$_row['option_str'] = $_options_str;
		$_row['link'] = "/shop/detail.php?pno=".$_row['hash'];
		$_tmp .= lineValues('mypage_notify_restock_list', $_line, $_row, $_file_name);
		$_index--;
	}
	$_tmp = listContentSetting($_tmp, $_line);
	$_replace_code[$_file_name]['mypage_notify_restock_list']=$_tmp;
	unset($_row, $_tmp, $_line, $_file_url, $_options_str, $_index, $_stat, $_stat_script);
?>