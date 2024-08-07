<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  종합매출 처리
	' +----------------------------------------------------------------------------------------------+*/

	if(!$sdate) {
		exit();
	}

	$r=$sdate;

	$o="`pay_prc`";
	if($mile) {
		$o.="+`milage_prc`";
	}
	if(!$dlv) {
		$o.="-`dlv_prc`";
	}
	$statq=" and `stat` not in (11,31)"; // 2007-06-21 - Han

	$edate = $sdate+86399;

	$term_where = "between $sdate and $edate";
	$data = $pdo->assoc("
	select
		sum(if(`date1` $term_where, $o, 0)) as r1_sum, sum(if(`date1` $term_where, 1, 0)) as r1_cnt,
		sum(if(`stat` = 13 and `date2` = 0 and `ext_date` $term_where, $o, 0)) as r2_sum, sum(if(`stat`=13 and `date2` = 0 and `ext_date` $term_where, 1, 0)) as r2_cnt,
		sum(if(`stat` < 10 and `date2` $term_where, $o, 0)) as r3_sum, sum(if(`stat`<10 and `date2` $term_where, 1, 0)) as r3_cnt,
		sum(if(`stat` = 13 and `date2` > 0 and `ext_date` $term_where, $o, 0)) as r4_sum, sum(if(`stat`=13 and `date2` > 0 and `ext_date` $term_where, 1, 0)) as r4_cnt,
		sum(if(`stat` = 5 and `date5` $term_where, $o, 0)) as r5_sum, sum(if(`stat`=5 and `date5` $term_where, 1, 0)) as r5_cnt,
		sum(if(`stat` in (13,15,17) and `date5` > 0 and `ext_date` $term_where, $o, 0)) as r6_sum, sum(if(`stat` in (13,15,17) and `date5` > 0 and `ext_date` $term_where, 1, 0)) as r6_cnt
	from `$tbl[order]`
	where `stat` not in (11,31) and (x_order_id='' or x_order_id='checkout')
	");

	$inc=$data['r5_sum'] - $data['r6_sum'];

	$r.="@".$data['r1_sum'];
	$r.="@".$data['r1_cnt'];
	$r.="@".$data['r2_sum'];
	$r.="@".$data['r2_cnt'];
	$r.="@".$data['r3_sum'];
	$r.="@".$data['r3_cnt'];
	$r.="@".$data['r4_sum'];
	$r.="@".$data['r4_cnt'];
	$r.="@".$data['r5_sum'];
	$r.="@".$data['r5_cnt'];
	$r.="@".$data['r6_sum'];
	$r.="@".$data['r6_cnt'];

	$r.="@".$inc;

	exit($r);

?>