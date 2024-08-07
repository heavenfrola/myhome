<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  마이페이지 나의 주문리스트
	' +----------------------------------------------------------------------------------------------+*/
	
	if($_GET['start_date'] && $_GET['finish_date']) {
		$_start_date = $_GET['start_date'];
		$_finish_date = $_GET['finish_date'];
		$start_date = strtotime($_GET['start_date']);
		$finish_date = strtotime($_GET['finish_date']) + 86399;
		$date_where = "and date1>=$start_date and date1<=$finish_date";
	}

	$_tmp = '';
	$_line = getModuleContent('mypage_order_list');
	while($ord = orderList($date_where)){
		$ord['oidx'] = $oidx;
		$ord['total_buy_ea'] = number_format($pdo->row("select sum(buy_ea) from $tbl[order_product] where ono='$ord[ono]'"));
		$ord['ono2'] = $ord['ono'];
		$ord['ono'] = "<a href=\"".$ord['link']."\">".$ord[ono]."</a>";
		$ord['pay_prc'] = parsePrice($ord['pay_prc'], true);
		$ord['pay_r_prc'] = showExchangeFee($ord['pay_prc']);

		$_tmp .= lineValues('mypage_order_list', $_line, $ord);
	}
	$_tmp = listContentSetting($_tmp, $_line);
	$_replace_code[$_file_name]['mypage_order_list'] = $_tmp;

	$_tmp = '';

	$btn = array();
	$_line = getModuleContent('mypage_search_date_list');
	foreach($cfg['mypage_search_date_type'] as $key=>$val) {
		if($val=="") {
			$btn['selected_on'] = (!$_finish_date && !$_start_date) ? "on" : "";
			$_sdate="";
			$_fdate="";
		}else {
			$btn['selected_on'] = ($val && $_finish_date == date("Y-m-d", $now) && $_start_date == date("Y-m-d", strtotime($val))) ? "on" : "";
			if($val) {
				$_sdate=date("Y-m-d", strtotime($val));
				$_fdate=date("Y-m-d", $now);
			}
		}
		$btn['btn_key'] = $key;
		$btn['btn_val'] = $val;
		$btn['auto_click_event'] = "onclick=\"ordSearchdate('$_sdate', '$_fdate', true);\"";
		$btn['click_event'] = "onclick=\"ordSearchdate('$_sdate', '$_fdate');\"";

		$_tmp .= lineValues('mypage_search_date_list', $_line, $btn);
	}
	$_tmp = listContentSetting($_tmp, $_line);
	$_replace_code[$_file_name]['mypage_search_date_list'] = $_tmp;

	$_replace_code[$_file_name]['mypage_start_date'] = $_start_date;
	$_replace_code[$_file_name]['mypage_finish_date'] = $_finish_date;
?>