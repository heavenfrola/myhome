<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  네이버 지식쇼핑 판매지수 수신
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir.'/_engine/include/common.lib.php';

	printAjaxHeader();

	$compare_key = md5($wec->config['wm_key_code']);
	if($compare_key != $_GET['site_key'] || !$_GET['site_key']) exit('사이트키가 정확하지 않습니다');

	$yesterday = date('Y-m-d', strtotime('-1 days'));
	$starttime = strtotime($yesterday);
	$endtime = $starttime + 86399;

	$productln = '';
	$total_prc = 0;
	$total_cnt = 0;

	$qry = "select `hash`, `buy_ea`, p.`total_prc`, o.`prd_prc`, r.`rev_cnt`, o.`ono` from `$tbl[order]` o ".
		   "inner join `$tbl[order_product]` p using(`ono`) ".
		   "inner join `$tbl[product]` r on p.`pno` = r.`no` ".
		   "where 1 ".
		   "and `date1` between ? and ? ".
		   "and `conversion` like '%@naver_is%' ".
		   "order by `ono`";

	$res = $pdo->iterator($qry, array(
        $starttime,
        $endtime,
    ));
    foreach ($res as $data) {
		if($data['ono'] != $rev_ono) {
			$total_prc += $data['prd_prc'];
			$rev_ono = $data['ono'];
		}
		$total_cnt += $data['buy_ea'];
		$productln .= "$data[hash]|$data[buy_ea]|$data[total_prc]|$data[rev_cnt]\n";
	}
?>
<<<mstart>>>
<?=$total_prc?>|<?=$total_cnt?>|<?=$yesterday?><?="\n"?>
<<<mend>>>
<<<pstart>>>
<?=$productln?>
<<<pend>>>
