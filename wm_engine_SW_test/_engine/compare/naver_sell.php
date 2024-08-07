<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  네이버 지식쇼핑 판매지수
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir.'/_engine/include/common.lib.php';

	set_time_limit(0);
	header('Content-type: text/plain;charset='._BASE_CHARSET_);

	function fputtsvfeed($array) {
		if(!is_array($array)) return;

		$csv = '';
		foreach($array as $key => $val) {
			if($csv) $csv .= "\t";
			$csv .= str_replace("\t", "", $val);
		}
		$csv .= "\n";

		return $csv;
	}

	$compare_key = md5($wec->config['wm_key_code']);
	if($compare_key != $_GET['site_key'] || !$_GET['site_key']) exit('사이트키가 정확하지 않습니다');

	// 피드 구조
	$feed_struct = array(
		'mall_id',
		'sale_count',
		'sale_price',
		'order_count',
		'dt',
	);

	$yesterday = date('Y-m-d', strtotime('-1 days'));
	$starttime = strtotime($yesterday);
	$endtime = $starttime + 86399;

	$productln = '';
	$total_prc = 0;
	$total_cnt = 0;

	echo fputtsvfeed($feed_struct);
	$qry = "select r.`hash`, r.`no`,
			count(*) as prd_cnt,
			count(distinct ono) as `ord_cnt`,
			sum(p.sell_prc) as all_pay_prc
			from `$tbl[order_product]` p ".
		   "inner join `$tbl[order]` o using(`ono`) ".
		   "inner join `$tbl[product]` r on p.`pno` = r.`no` ".
		   "where p.stat between 1 and 5 ".
		   "and (`date1` between '$starttime' and '$endtime') ".
		   "and `conversion` like '%@naver_is%' ".
		   "group by r.`hash` order by null";
	$res = $pdo->iterator($qry);
    foreach ($res as $data) {
		$_feed[$data['hash']]['prd_cnt'] = $data['prd_cnt'];
		$_feed[$data['hash']]['all_pay_prc'] = $data['all_pay_prc'];
		$_feed[$data['hash']]['ord_cnt'] = $data['ord_cnt'];
	}

	$qry = "select r.`hash`, r.`no`,
			count(*) as ret_prd_cnt,
			count(distinct ono) as ret_ord_cnt,
			sum(p.sell_prc) as return_pay_prc
			from `$tbl[order_product]` p ".
		   "inner join `$tbl[order]` o using(`ono`) ".
		   "inner join `$tbl[product]` r on p.`pno` = r.`no` ".
		   "where p.stat in (13,15,17) ".
		   "and (p.`repay_date` between '$starttime' and '$endtime') ".
		   "and `conversion` like '%@naver_is%' ".
		   "group by r.`hash` order by null";
	$res = $pdo->iterator($qry);
    foreach ($res as $data) {
		$_feed[$data['hash']]['ret_prd_cnt'] = $data['ret_prd_cnt'];
		$_feed[$data['hash']]['return_pay_prc'] = $data['return_pay_prc'];
		$_feed[$data['hash']]['ret_ord_cnt'] = $data['ret_ord_cnt'];
	}

	if(count($_feed)>0) {
		foreach($_feed as $key=>$val) {
			$feed['mall_id'] = $key;
			$feed['sale_count'] = $val['prd_cnt']-$val['ret_prd_cnt'];
			$feed['sale_price'] = $val['all_pay_prc']-$val['return_pay_prc'];
			$feed['order_count'] = $val['ord_cnt']+$val['ret_ord_cnt'];
			$feed['dt'] = date('Y-m-d', strtotime('-1 days'));

			$line = array();
			foreach($feed_struct as $key) {
				$line[$key] = $feed[$key];
			}

			echo fputtsvfeed($line);
		}
	}

?>