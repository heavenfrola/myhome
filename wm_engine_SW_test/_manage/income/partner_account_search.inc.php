<?PHP

	if(!$cfg['partner_account_day']) $cfg['partner_account_day'] = 1;
	if(!$cfg['partner_account_date']) $cfg['partner_account_date'] = 2;

	$yeardata = $pdo->assoc("select min(date1) as miny, max(date1) as maxy from $tbl[order]");
	$min_year = date('Y', $yeardata['miny']);
	$max_year = date('Y', $yeardata['maxy']);

	$dates = addslashes($_REQUEST['dates']);
	$datee = addslashes($_REQUEST['datee']);

	if(!$dates) $dates = date('Y-m-'.addZero($cfg['partner_account_day'], 2));
	if(!$datee) $datee = date('Y-m-d', strtotime('+1 months', strtotime($dates))-1);
	$_dates = strtotime($dates);
	$_datee = strtotime($datee)+86399;

	if($datee > strtotime('+3months', $dates)) {
		msg('최대 3개월 이내로만 검색해주세요.', 'back');
	}

	$sel_pay_type = $_GET['sel_pay_type'];
	if(is_array($sel_pay_type) == false) $sel_pay_type = array();
	else {
		$w .= " and o.pay_type in (".addslashes(implode(',', $sel_pay_type)).")";
	}

?>