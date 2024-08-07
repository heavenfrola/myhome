<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  마이페이지 나의 정기주문리스트
	' +----------------------------------------------------------------------------------------------+*/

	$_tmp = '';
	$_line = getModuleContent('mypage_order_sbscr_list');
	while($ord = orderSbscrList()){
		$ord['osidx'] = $osidx;
		$ord['total_buy_ea'] = number_format($pdo->row("select sum(buy_ea) from $tbl[sbscr_product] where sbono='$ord[sbono]'"));
		$ord['sbono'] = "<a href=\"".$ord['link']."\">".$ord['sbono']."</a>";
		$ord['pay_prc'] = parsePrice($ord['pay_prc'], true);
		$ord['pay_r_prc'] = showExchangeFee($ord['pay_prc']);

		$_tmp .= lineValues('mypage_order_sbscr_list', $_line, $ord);
	}
	$_tmp = listContentSetting($_tmp, $_line);
	$_replace_code[$_file_name]['mypage_order_sbscr_list'] = $_tmp;

?>