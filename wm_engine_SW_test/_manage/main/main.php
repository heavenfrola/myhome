<?PHP

	// 개별 계정별 관리자모드 메인 컨트롤
	if(file_exists($root_dir.'/_manage/main/main.php')) {
		require $root_dir.'/_manage/main/main.php';
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  관리자 메인
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/shop.lib.php";
	include_once $engine_dir."/_manage/main/bankingorder.exe.php";
	$admin_main_include=true;

	$nowHours = date('H');
	$today = date('Ymd', $now);
	$today_s = strtotime(date('Y-m-d', $now));

	/* +----------------------------------------------------------------------------------------------+
	' |  주문현황
	' +----------------------------------------------------------------------------------------------+*/
	$ostat_chart_period = 30;
	$ostat_chart_avg_period = 7;
	$ostat_start_day = strtotime(date('Y-m-d 00:00:00', strtotime('-'.($ostat_chart_period-1).' days', $now)));
	$ostat_avg_start_day = strtotime(date('Y-m-d 00:00:00', strtotime('-'.($ostat_chart_avg_period).' days', $ostat_start_day)));
	$ores = $pdo->iterator("select sum(total_prc) as total_prc, count(*) as total_count, from_unixtime(date1, '%Y%m%d') as dt, date1 from {$tbl['order']} where stat < 11 and date1 >= '$ostat_start_day' group by dt order by dt");

	//라벨 초기화 및 날짜 데이터 초기화
	$ostat_labels = array(); //라벨
	$weekend_kor = array('일요일', '월요일', '화요일', '수요일', '목요일', '금요일', '토요일');
	$ostat = array(
		'prc' => array(),
		'count' => array(),
		'prc_avg' => array(),
		'count_avg' => array(),
	);
	$ago30days = strtotime('-29 days', $now);
	for ($day = 0; $day < 30; $day++) {
		$str_k = strtotime('+'.$day.' days', $ago30days);
		$k = date('Ymd', $str_k);
		$ostat['prc'][$k] = 0;
		$ostat['count'][$k] = 0;
		$ostat['prc_avg'][$k] = 0;
		$ostat['count_avg'][$k] = 0;

		//평균구하기
		$ago7days = strtotime(date('Y-m-d 00:00:00', strtotime('-7 days', $str_k)));
		$thisday = strtotime(date('Y-m-d 23:59:59', strtotime('-1 days', $str_k)));
		$agores = $pdo->assoc("select sum(total_prc) as total_prc, count(*) as total_count from {$tbl['order']} where stat < 11 and (date1 >= '$ago7days' && date1 <= '$thisday')"); //지난 7일 평균
		if ($agores['total_prc'] > 0) {
			$ostat['prc_avg'][$k] = round($agores['total_prc'] / $ostat_chart_avg_period);
		}
		if ($agores['total_count'] > 0) {
			$ostat['count_avg'][$k] = round($agores['total_count'] / $ostat_chart_avg_period);
		}

		//라벨 생성
		$ostat_labels_text = date('Y년 m월 d일', strtotime('+'.$day.' days', $ago30days)).' '.$weekend_kor[date('w', strtotime('+'.$day.' days', $ago30days))];
		array_push($ostat_labels, $ostat_labels_text);
	}

	if (!empty($ores)) {
		foreach ($ores as $odata) {
			$ostat['prc'][$odata['dt']] = $odata['total_prc'];
			$ostat['count'][$odata['dt']] = $odata['total_count'];
		}
	}
	unset($ores);

	$latestDayValue = 7; //7일

	//지난주 같은 요일 대비 데이터
	$lastWeekSameDay_prc_start = strtotime(date('Y-m-d 00:00:00', strtotime('-7 days', $now)));
	$lastWeekSameDay_prc_finish = strtotime(date('Y-m-d H:00:00', strtotime('-7 days', $now)));
	$ores_lastweek = $pdo->assoc("select sum(total_prc) as total_prc, count(*) as total_count, from_unixtime(date1, '%Y%m%d') as dt, date1 from {$tbl['order']} where stat < 11 and (date1 >= '$lastWeekSameDay_prc_start' && date1 <= '$lastWeekSameDay_prc_finish') group by dt order by null");

	//오늘 데이터
	$today_prc_start = strtotime(date('Y-m-d 00:00:00', $now));
	$today_prc_finish = strtotime(date('Y-m-d H:00:00', $now));
	$ores_today = $pdo->assoc("select sum(total_prc) as total_prc, count(*) as total_count, from_unixtime(date1, '%Y%m%d') as dt, date1 from {$tbl['order']} where stat < 11 and (date1 >= '$today_prc_start' && date1 <= '$today_prc_finish') group by dt order by null");

	// 오늘 총매출 - 지난주 같은 요일 대비 + -
	$oldValue = $ores_lastweek['total_prc'];
	if ($oldValue == 0) $oldValue = 1; // 지난주 같은 요일 값이 없으면 1로 나누기
	$lastWeekSameDay_prc_updown_percent = number_format((($ores_today['total_prc'] - $ores_lastweek['total_prc']) / $oldValue) * 100, 1);
	$lastWeekSameDay_prc_updown_percent = getRollingPercentText($lastWeekSameDay_prc_updown_percent);

	// 오늘 총매출 - 최근 7일 평균 대비 + -
	$latest7_prc = 0; // 최근 7일 합
	$latestWeek = strtotime('-'.($latestDayValue + 1).' days', $now);
	for ($ostatD = 0; $ostatD < 7; $ostatD++) {
		$latestWeek = strtotime('+1 days', $latestWeek);
		$latest7_prc = $latest7_prc + $ostat['prc'][date('Ymd', $latestWeek)];
	}
	$latest7_prc_updown_percent = round((($latest7_prc / $latestDayValue) / 24) * $nowHours, 1); // round(((최근 7일 평균) / 24시간) * 현재시각, 1)

	$oldValue = $latest7_prc_updown_percent;
	if ($oldValue == 0) $oldValue = 1;
	$order_latest7_updown_percent = number_format((($ores_today['total_prc'] - $latest7_prc_updown_percent) / $oldValue) * 100, 1);
	$order_latest7_updown_percent = getRollingPercentText($order_latest7_updown_percent);

	// 오늘 주문수 - 지난주 같은 요일 대비 + -
	$oldValue = $ores_lastweek['total_count'];
	if ($oldValue == 0) $oldValue = 1;
	$lastWeekSameDay_order_updown_percent = number_format((($ores_today['total_count'] - $ores_lastweek['total_count']) / $oldValue) * 100, 1);
	$lastWeekSameDay_order_updown_percent = getRollingPercentText($lastWeekSameDay_order_updown_percent);

	//오늘 주문수 - 최근 7일 평균 대비 + -
	$latest7_order = 0;
	$latestWeek = strtotime('-'.($latestDayValue+1).' days', $now);
	for ($ostatD = 0; $ostatD < 7; $ostatD++) {
		$latestWeek = strtotime('+1 days', $latestWeek);
		$latest7_order = $latest7_order + $ostat['count'][date('Ymd', $latestWeek)];
	}
	$latest7_order = round((($latest7_order / $latestDayValue) / 24) * $nowHours, 1);
	$oldValue = $latest7_order;
	if ($oldValue == 0) $oldValue = 1;
	$latest7_order_updown_percent = number_format((($ores_today['total_count'] - $latest7_order) / $oldValue) * 100, 1);
	$latest7_order_updown_percent = getRollingPercentText($latest7_order_updown_percent);

	unset($ores_lastweek);
	unset($ores_today);
	function setNumber($str) {
		if(!$str) $str = 0;
		return $str;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  판매 상품 순위
	' +----------------------------------------------------------------------------------------------+*/
	$sell_start_day = strtotime(date('Y-m-d 00:00:00', strtotime('-13 days', $now)));
	$todaystrtotime = strtotime(date('Y-m-d 00:00:00', $now));
	$selling_res = $pdo->iterator("select count(*) as total_count, p.pno, sum(if(o.date1 >= '$todaystrtotime', 1, 0)) as today_count, pd.name, pd.updir, pd.upfile2 from {$tbl['order_product']} as p inner join {$tbl['order']} as o on p.ono=o.ono inner join {$tbl['product']} as pd on p.pno=pd.no where p.stat < 11 and o.date1 >= '$sell_start_day' group by p.pno order by total_count desc limit 0, 10");

	$selling_array_data = array();
    foreach ($selling_res as $selldata) {

		$file_dir = getFileDir($selldata['updir']);
		if($selldata['upfile2'] && ((!$_use['file_server'] && is_file($root_dir."/".$selldata['updir']."/".$selldata['upfile2'])) || $_use['file_server'] == "Y")) {
			$img = "$file_dir/{$selldata['updir']}/{$selldata['upfile2']}";
		} else {
			$img = $root_url.$cfg['noimg2'];
		}

		array_push($selling_array_data, array(
			'pno' => $selldata['pno'],
			'name' => $selldata['name'],
			'total' => $selldata['total_count'],
			'today_count' => $selldata['today_count'],
			'img' => $img,
		));
	}
	unset($selling_res);

	/* +----------------------------------------------------------------------------------------------+
	' |  회원현황
	' +----------------------------------------------------------------------------------------------+*/
	deleteAuto(); // 탈퇴회원 자동삭제 처리

	$data = $pdo->assoc("select count(*) as `cnt`, sum(if(`reg_date` >= '$today_s', 1, 0)) as `new`, sum(if(`last_con` >= '$today_s', 1, 0)) as `today`, sum(if(`withdraw` = 'Y',1,0)) as `draw` from `$tbl[member]`");
	$member_total = number_format($data['cnt'] - $data['draw']);
	$member_new = number_format($data['new']);
	$member_today = number_format($data['today']);
	$member_draw = number_format($data['draw']);
	$member_order = number_format($pdo->row("select count(*) from `$tbl[order]` where `stat` not in  (11,31) and `member_no` > 0 and `date1` >= '$today_s' and (x_order_id='' or x_order_id='checkout')"));

	//오늘 현재 시각까지의 가입자
	$thisDay_member_start = strtotime(date('Y-m-d 00:00:00', $now));
	$thisDay_member_finish = strtotime(date('Y-m-d H:00:00', $now));
	$thisDay_member_res = $pdo->row("select count(*) as cnt from {$tbl['member']} where reg_date >= '$thisDay_member_start' && reg_date <= '$thisDay_member_finish'");

	//지난주 같은요일 가입자
	$lastWeekSameDay_member_start = strtotime(date('Y-m-d 00:00:00', strtotime('-7 days', $now)));
	$lastWeekSameDay_member_finish = strtotime(date('Y-m-d H:00:00', strtotime('-7 days', $now)));
	$lastWeekSameDay_member_res = $pdo->row("select count(*) as cnt from {$tbl['member']} where reg_date >= '$lastWeekSameDay_member_start' && reg_date <= '$lastWeekSameDay_member_finish'");

	//오늘 가입자 - 최근 7일 평균 대비 + -
	$latest7_member_start = strtotime(date('Y-m-d 00:00:00', strtotime('-'.$latestDayValue.' days', $now)));
	$latest7_member_end = strtotime(date('Y-m-d 23:59:59', strtotime('-1 days', $now)));
	$latest7_member_res = $pdo->assoc("select count(*) as cnt from {$tbl['member']} where reg_date >= '$latest7_member_start' && reg_date <= '$latest7_member_end'");
	$latest7_member = round((($latest7_member_res['cnt'] / $latestDayValue) / 24) * $nowHours, 1);
	$oldValue = $latest7_member;
	if ($oldValue == 0) $oldValue = 1;
	$latest7_member_updown_percent = number_format((($thisDay_member_res - $latest7_member) / $oldValue) * 100, 1);
	$latest7_member_updown_percent = getRollingPercentText($latest7_member_updown_percent);

	//오늘 가입자 - 지난주 같은 요일 대비 + -
	$lastWeekSameDay_member = $lastWeekSameDay_member_res;
	$oldValue = $lastWeekSameDay_member;
	if ($oldValue == 0) $oldValue = 1;
	$lastWeekSameDay_member_updown_percent = number_format((($thisDay_member_res - $lastWeekSameDay_member) / $oldValue) * 100, 1);
	$lastWeekSameDay_member_updown_percent = getRollingPercentText($lastWeekSameDay_member_updown_percent);

	//오늘 방문자
	list($yy, $mm, $dd) = explode('-', date('Y-m-d', $now));
	$access_today = $pdo->row("select hit from {$tbl['log_day']} where yy='$yy' and mm='$mm' and dd='$dd'");

	//오늘 방문자 - 지난주 같은 요일 대비 + -
	$lastWeekSameDay_visitor_ex = explode('-', date('Y-m-d', strtotime('-7 days', $now)));
	$_agoyy = $lastWeekSameDay_visitor_ex[0];
	$_agomm = $lastWeekSameDay_visitor_ex[1];
	$_agodd = $lastWeekSameDay_visitor_ex[2];

	//시간별 방문자 필드 생성 (현재 시각까지)
	$h_hours = array();
	for($i = 0; $i < $nowHours; $i++) {
		array_push($h_hours, $i);
	}
	$flds = "";
	foreach($h_hours as $k) {
		$flds .= "h".$k.",";
	}
	$today_visitor_res = $pdo->assoc("select $flds hit from {$tbl['log_day']} where yy='$yy' and mm='$mm' and dd='$dd'");
	$lastWeekSameDay_visitor_res = $pdo->assoc("select $flds hit from {$tbl['log_day']} where yy='$_agoyy' and mm='$_agomm' and dd='$_agodd'");
	$today_visitor = 0;
	$lastWeekSameDay_visitor = 0;
	foreach ($h_hours as $k) {
		$today_visitor += $today_visitor_res['h'.$k];
		$lastWeekSameDay_visitor += $lastWeekSameDay_visitor_res['h'.$k];
	}
	$oldValue = $lastWeekSameDay_visitor;
	if ($oldValue == 0) $oldValue = 1;
	$lastWeekSameDay_visitor_updown_percent = number_format((($today_visitor - $lastWeekSameDay_visitor) / $oldValue) * 100, 1);
	$lastWeekSameDay_visitor_updown_percent = getRollingPercentText($lastWeekSameDay_visitor_updown_percent);

	//오늘 방문자 - 최근 7일 대비 + -
	$latest7_visitor = 0;
	$latest7Day = strtotime('-'.($latestDayValue+1).' days', $now);
	for ($ostatD = 0; $ostatD < $latestDayValue; $ostatD++) {
		$latest7Day = strtotime('+1 days', $latest7Day);
		$latestSameDay = explode('-', date('Y-m-d', $latest7Day));
		$yy = $latestSameDay[0];
		$mm = $latestSameDay[1];
		$dd = $latestSameDay[2];
		$access_latestSameDay = $pdo->row("select hit from {$tbl['log_day']} where yy='$yy' and mm='$mm' and dd='$dd'");
		$latest7_visitor += $access_latestSameDay;
	}
	$latest7_visitor = round((($latest7_visitor / $latestDayValue) / 24) * $nowHours, 1);
	$oldValue = $latest7_visitor;
	if ($oldValue == 0) $oldValue = 1;
	$latest7_visitor_updown_percent = number_format((($today_visitor - $latest7_visitor) / $oldValue) * 100, 1);
	$latest7_visitor_updown_percent = getRollingPercentText($latest7_visitor_updown_percent);

	// 회원가입 추이 - 일 (최근 30일)
	$member_join_day_start = strtotime(date('Y-m-d 00:00:00', strtotime('-29 days', $now)));
	$member_join_day_headText = '최근 30일 ('.date('Y.m.d', $member_join_day_start).' ~ '.date('Y.m.d', $now).')';

	$member_join_day = array();
	$member_join_day_avg = array();
	$res = $pdo->iterator("select reg_date, from_unixtime(reg_date, '%Y-%m-%d') as dt, count(*) as cnt from {$tbl['member']} where reg_date >= '$member_join_day_start' group by dt");
	foreach ($res as $data) {
		$member_join_key = date('Y년 m월 d일', $data['reg_date']).' '.$weekend_kor[date('w', $data['reg_date'])];
		$member_join_day[$member_join_key] = $data['cnt'];
	}
	for ($i = 0; $i < 30; $i++) {
		$_day = date('Y년 m월 d일', strtotime('-'.$i.' days', $now)).' '.$weekend_kor[date('w', strtotime('-'.$i.' days', $now))];
		$ago7days = strtotime(date('Y-m-d 00:00:00', strtotime('-'.($i+7).' days', $now)));
		$thisday = strtotime('-'.$i.' days', strtotime(date('Y-m-d 00:00:00', $now)));
		$agores = $pdo->assoc("select count(*) as cnt from {$tbl['member']} where (reg_date >= '$ago7days' && reg_date < '$thisday')"); //지난 7일 평균
		if($agores['cnt'] == 0) {
			array_unshift($member_join_day_avg, 0);
		} else {
			array_unshift($member_join_day_avg, round($agores['cnt'] / 7, 1));
		}
		if (!$member_join_day[$_day]) $member_join_day[$_day] = 0;
	}
	ksort($member_join_day);

	// 회원가입 추이 - 주 (최근 6개월)
	$member_join_week_start = strtotime(date('Y-m-d 00:00:00', strtotime('-6 months', $now)));
	$member_join_week_headText = '최근 6개월 ('.date('Y.m.d', $member_join_week_start).' ~ '.date('Y.m.d', $now).')';

	$init_member_join_week = array(); //주단위 초기화
	$start_member_join_week = explode('-', date('Y-m-d', $member_join_week_start));
	$end_member_join_week = explode('-', date('Y-m-d', $now));
	$week = date('w', strtotime($start_member_join_week[0].'-'.$start_member_join_week[1].'-'.$start_member_join_week[2]));
	$label_text = $start_member_join_week[0].'년 '.$start_member_join_week[1].'월 '.$start_member_join_week[2].'일 '.$weekend_kor[$week];
	$key_text = $start_member_join_week[0].'-'.$start_member_join_week[1].'-'.$start_member_join_week[2];
	for ($init_y = $start_member_join_week[0]; $init_y <= $end_member_join_week[0]; $init_y++) {
		$init_m_start = 1;
		$init_m_end = 12;
		if ($init_y == $start_member_join_week[0]) $init_m_start = $start_member_join_week[1];
		if ($init_y == $end_member_join_week[0]) $init_m_end = $end_member_join_week[1];
		for ($init_m = $init_m_start; $init_m <= $init_m_end; $init_m++) {
			$init_m_text = sprintf('%02d', $init_m);
			$init_d_start = 1;
			$init_d_end = date('t', strtotime($init_y.'-'.$init_m_text));
			if ($init_y == $start_member_join_week[0] && $init_m_text == $start_member_join_week[1]) $init_d_start = $start_member_join_week[2];
			if ($init_y == $end_member_join_week[0] && $init_m_text == $end_member_join_week[1]) $init_d_end = $end_member_join_week[2];
			for ($init_d = $init_d_start; $init_d <= $init_d_end; $init_d++) {
				$init_d_text = sprintf('%02d', $init_d);
				if ($week == '0' || ($init_y == $end_member_join_week[0] && $init_m == $end_member_join_week[1] && $init_d == $end_member_join_week[2])) {
					if ($label_text == '') $label_text = $init_y.'년 '.$init_m_text.'월 '.$init_d_text.'일 '.$weekend_kor[$week];
					if ($key_text == '') $key_text = $init_y.'-'.$init_m_text.'-'.$init_d_text;
					$label_text .= ' ~ '.$init_y.'년 '.$init_m_text.'월 '.$init_d_text.'일 '.$weekend_kor[$week];
					$init_member_join_week[$key_text]['label'] = $label_text;
					$init_member_join_week[$key_text]['day']['cnt'] = 0;
					$init_member_join_week[$key_text]['avg']['cnt'] = 0;
					$key_text = '';
					$label_text = '';
				}
				if ($week == '1') {
					$key_text = $init_y.'-'.$init_m_text.'-'.$init_d_text;
					$label_text = $init_y.'년 '.$init_m_text.'월 '.$init_d_text.'일 '.$weekend_kor[$week];
				}
				$week = ($week == '6') ? '0' : $week + 1;
			}
		}
	}

	$member_join_week = array(); // 주단위 데이터
	$res = $pdo->iterator("select reg_date, from_unixtime(reg_date, '%Y-%m-%d') as dt, from_unixtime(reg_date, '%u') as week, count(*) as cnt from {$tbl['member']} where reg_date >= '$member_join_week_start' && reg_date <= '$now' group by week");

	$firstWeekCheck = true;
	foreach ($res as $data) {
		$ex = explode('-', $data['dt']);
		$w = date('w', strtotime($data['dt']));
		$today_week_sday1 = mktime(0, 0, 0, $ex[1], $ex[2] - $w, $ex[0]); //주의 시작일(일요일)
		$today_week_sday1 = date('Y-m-d', strtotime('+1 days', $today_week_sday1)); //+1 days로 월요일 구하기
		if ($firstWeekCheck) {
			if ($member_join_week_start <= strtotime($data['dt']) && strtotime('+6 days', $member_join_week_start) >= strtotime($data['dt'])) {
				$today_week_sday1 = $data['dt'];
			}
			$firstWeekCheck = false;
		}
		$init_member_join_week[$today_week_sday1]['day']['cnt'] = $data['cnt'];
	}
	foreach ($init_member_join_week as $mK => $mV) {
		$member_join_week[$mV['label']] = $mV['day']['cnt'];
	}

	$member_join_week_avg = array(); // 주단위 지난 4주 평균 데이터
	foreach ($init_member_join_week as $mK => $mV) {
		$ago7days = strtotime('-4 weeks', strtotime($mK));
		$ago7daysEnd = strtotime($mK);
		$agores = $pdo->assoc("select count(*) as cnt from {$tbl['member']} where (reg_date >= '$ago7days' && reg_date < '$ago7daysEnd')"); //지난 4주 평균
		$init_member_join_week[$mK]['avg']['cnt'] = round($agores['cnt'] / 4, 1);
	}
	foreach ($init_member_join_week as $mK => $mV) {
		$member_join_week_avg[$mV['label']] = $mV['avg']['cnt'];
	}
	unset($init_member_join_week);

	// 회원가입 추이 - 월 (최근 1년)
	$member_join_month_start = strtotime(date('Y-m-d 00:00:00', strtotime('-1 year', $now)));
	$member_join_month_headText = '최근 1년 ('.date('Y.m.d', $member_join_month_start).' ~ '.date('Y.m.d', $now).')';

	$init_member_join_month = array();
	$start_member_join_week = explode('-', date('Y-m-d', $member_join_month_start));
	$end_member_join_week = explode('-', date('Y-m-d', $now));
	$label_text = $start_member_join_week[0].'년 '.$start_member_join_week[1].'월 '.$start_member_join_week[2].'일 ';
	$key_text = $start_member_join_week[0].'-'.$start_member_join_week[1].'-'.$start_member_join_week[2];
	for ($init_y = $start_member_join_week[0]; $init_y <= $end_member_join_week[0]; $init_y++) {
		$init_m_start = 1;
		$init_m_end = 12;
		if ($init_y == $start_member_join_week[0]) $init_m_start = $start_member_join_week[1];
		if ($init_y == $end_member_join_week[0]) $init_m_end = $end_member_join_week[1];
		for ($init_m = $init_m_start; $init_m <= $init_m_end; $init_m++) {
			$init_m_text = sprintf('%02d', $init_m);
			$key_text = $init_y.'-'.$init_m_text;
			$label_text = $init_y.'년 '.$init_m_text.'월 1일 ~ '.$init_y.'년 '.$init_m_text.'월 '.date('t', strtotime($init_y.'-'.$init_m)).'일';
			$avg_s = strtotime('-3 months', strtotime($init_y.'-'.$init_m));
			$avg_e = strtotime(date('Y-m-t 23:59:59', strtotime('-1 months', strtotime($init_y.'-'.$init_m))));
			if ($init_y == $start_member_join_week[0] && $init_m == $init_m_start) {
				$label_text = $init_y.'년 '.$init_m_text.'월 '.$start_member_join_week[2].'일 ~ '.$init_y.'년 '.$init_m_text.'월 '.date('t', strtotime($init_y.'-'.$init_m)).'일';
			}
			if ($init_y == $end_member_join_week[0] && $init_m == $init_m_end) {
				$label_text = $init_y.'년 '.$init_m_text.'월 1일 ~ '.$init_y.'년 '.$init_m_text.'월 '.$end_member_join_week[2].'일';
			}
			$init_member_join_month[$key_text]['label'] = $label_text;
			$init_member_join_month[$key_text]['day']['cnt'] = 0;
			$init_member_join_month[$key_text]['avg']['cnt'] = 0;
			$init_member_join_month[$key_text]['avg_s'] = $avg_s;
			$init_member_join_month[$key_text]['avg_e'] = $avg_e;
		}
	}

	$member_join_month = array();
	$member_join_month_avg = array();
	$res = $pdo->iterator("select reg_date, from_unixtime(reg_date, '%Y-%m') as month, count(*) as cnt from {$tbl['member']} where reg_date >= '$member_join_month_start' && reg_date <= '$now' group by month");
	foreach ($res as $data) {
		$init_member_join_month[$data['month']]['day']['cnt'] = $data['cnt'];
	}
	foreach ($init_member_join_month as $mK => $mV) {
		$member_join_month[$mV['label']] = $mV['day']['cnt'];
		$a_s = $mV['avg_s'];
		$a_e = $mV['avg_e'];
		$a_sum = 0;
		$res_avg = $pdo->assoc("select count(*) as cnt from {$tbl['member']} where (reg_date >= '$a_s' && reg_date <= '$a_e')"); //지난 3개월 평균
		$member_join_month_avg[$mK] = round($res_avg['cnt'] / 3, 1);
	}
	unset($init_member_join_month);

	$lastweekday_text = $weekend_kor[date('w', strtotime('-7 days', $now))];

	function getRollingPercentText($oldNumber) {
		$triangle = '▲ ';
		$percentMark = '%';
		$updown_percent_color = '#EF532F'; //red
		if (round($oldNumber) < 0) {
			$triangle = '▼ ';
			$updown_percent_color = '#27AEE6'; //blue
		}
		if ($oldNumber == 0) {
			$triangle = '';
			$updown_percent_color = '#888888'; //gray
			$oldNumber = '-';
			$percentMark = '';
		}
		$oldNumber = '<strong style="color:'.$updown_percent_color.';">'.$triangle.$oldNumber.$percentMark.'</strong>';

		return $oldNumber;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  열람기록 삭제
	' +----------------------------------------------------------------------------------------------+*/
	deletePrivacyViewLog();

	/* +----------------------------------------------------------------------------------------------+
	' |  서비스 이용현황
	' +----------------------------------------------------------------------------------------------+*/
	# PG
	if($cfg['card_pg'] == 'kcp') if(!$cfg['card_site_cd'] || !$cfg['card_site_key']) $cfg['card_pg'] = '';
	if($cfg['card_pg'] == 'dacom') if(!$cfg['card_dacom_id'] || !$cfg['card_dacom_key']) $cfg['card_pg'] = '';
	if($cfg['card_pg'] == 'allat') if(!$cfg['card_partner_id'] || !$cfg['card_form_key']) $cfg['card_cross_key'] = '';
	if($cfg['card_pg'] == 'inicis') if(!$cfg['card_mall_id'] || !$cfg['card_key_password']) $cfg['card_pg'] = '';
	if($cfg['card_pg'] == 'allthegate') if(!$cfg['allthegate_StoreId']) $cfg['card_pg'] = '';
    if($cfg['card_pg'] == 'nicepay') if(!$cfg['nicepay_mid']) $cfg['card_pg'] = '';
	$pg_onoff = ($cfg['card_pg']) ? 'on' : 'off';

	# Mobile PG
	$mob_pg_onoff = 'off';
	if($cfg['card_mobile_pg'] == 'kcp' && $cfg['card_mobile_site_cd']) $mob_pg_onoff = 'on';
	if($cfg['card_mobile_pg'] == 'dacom' && $cfg['card_mobile_dacom_id']) $mob_pg_onoff = 'on';
	if($cfg['card_mobile_pg'] == 'allat' && $cfg['mobile_card_partner_id']) $mob_pg_onoff = 'on';
	if($cfg['card_mobile_pg'] == 'inicis' && $cfg['card_inicis_mobile_id']) $mob_pg_onoff = 'on';
	if($cfg['card_mobile_pg'] == 'allthegate' && $cfg['mobile_allthegate_StoreId']) $mob_pg_onoff = 'on';
    if($cfg['card_mobile_pg'] == 'nicepay' && $cfg['nicepay_m_mid']) $mob_pg_onoff = 'on';

	# Pay_type
	$use_danal = ($cfg['pay_type_7'] == 'Y' && $cfg['mobile_danal'] == 'Y' && $cfg['danal_subcp_id']) ? 'on' : 'off';
	$use_pay_type_5 = ($pg_onoff == 'on' && $cfg['pay_type_5'] == 'Y') ? 'on' : 'off';
	$use_escrow = 'on';

	# dooson erp
	$use_dooson = ($cfg['use_dooson'] == 'Y') ? 'on' : 'off';

	# SMS
	$sms_rest = numberOnly($asvcs[0]->sms_rest[0]);
	$total_members = $pdo->row("select count(*) from $tbl[member] where withdraw in ('N', 'A')");

	# 자동입금
	$bankda_fin = $asvcs[0]->bankda_fin[0];
	$bankda_accounts = numberOnly($asvcs[0]->bankda_accounts[0]);
	$bankda_limit = ($bankda_fin == 'OFF') ? 0 : strtotime('+1 days', strtotime(date($bankda_fin)));
	$bankda_left = floor(($bankda_limit-$now)/86400);
	$use_wingbank = ($bankda_fin == 'OFF' || $bankda_limit < $today_s) ? 'off' : 'on';

	# 현금영수증
	$use_cash_receipt = ($cfg['cash_receipt_use'] == 'Y') ? 'on' : 'off';

	# 에이스카운터
	$ace_use = ($cfg['ace_counter_gcode']) ? 'ON' : 'OFF';

	# 서비스 등급
	$mall_goods_idx = $asvcs[0]->mall_goods_idx[0];

	# 기본하드디스크 사용량
	$_bimg_limit = ((int) $asvcs[0]->img_limit[0] > 0) ? $asvcs[0]->img_limit[0] : '무제한';
	if($_bimg_limit != '무제한') {
		$bimg_limit = ($_bimg_limit >= 1000) ? ($_bimg_limit/1000).'G' : $_bimg_limit.'M';
	}
	$bimg_used = $pdo->row("select sum(`filesize`) from `$tbl[product_image]` where `filetype` in (2,3,6)");
	$_bimg_used = round($bimg_used/1024/1024);
	if($_bimg_limit == '무제한') {
		$bimg_per = 50;
        $bimg_limit = '무제한';
	} else {
		$bimg_per = round(($_bimg_used/($_bimg_limit*1024))*100);
		if($bimg_per > 100) $bimg_per = 100;
	}
	$bimg_used = filesizestr($bimg_used, 2);
	$bimg_finish = $hosting_finish = ($asvcs[0]->img_finish[0] == '무제한') ? '무제한' : date('Y.m.d', $asvcs[0]->img_finish[0]);
	$bimg_warning = ($bimg_per > (int)$asvcs[0]->img_warning[0]) ? 'warning' : '';
    if ($bimg_finish == '무제한') {
        $bimg_left = '무제한';
    } else {
    	$bimg_left = ceil(($asvcs[0]->img_finish[0]-$now)/86400);
        if ($bimg_left > 0) {
            $bimg_left = 'D-'.$bimg_left;
        } else {
            $bimg_left = '만료';
        }
    }
    $hosting_left = $bimg_left;
	$bimg_left_warning = (numberOnly($bimg_left) > 14) ? '' : 'warning';

	# 구 윙디스크
	if($mall_goods_idx == '3') {
		$_bimg_used = $pdo->row("select sum(`filesize`) from `$tbl[product_image]` where `filetype` in (7,8,9)");
		$bimg_used = filesizeStr($_bimg_used);
		$_bimg_used = $bimg_used/1024/1024;

		$bimg_per = round(($_bimg_used/$_bimg_limit)*100);
		if($bimg_per > 100) $bimg_per = 100;
		$bimg_finish = date('Y-m-d', $asvcs[0]->wdisk_finish[0]);
		$bimg_left = ($bimg_finish == '무제한') ? '무제한' : 'D-'.floor(($asvcs[0]->wdisk_finish[0]-$now)/86400);

		$wdisk_limit = $asvcs[0]->wdisk[0];
		$wdisk_used = $asvcs[0]->img_used[0]/1024/1024;
		$wdisk_expire = date('Y-m-d', $asvcs[0]->wdisk_finish[0]);
		$wdisk_per = @round($asvcs[0]->img_used[0]/($wdisk_limit*1024*1024), 1)*100;
		$wdisk_fin = ($wdisk_use == 'OFF' || $asvcs[0]->wdisk_finish[0] < $now) ? 'OFF' :  date('m/d', $asvcs[0]->wdisk_finish[0]);
		$wdisk_left = floor(($asvcs[0]->wdisk_finish[0]-$now)/86400);
	}

	# CDN
	$cdn_use = ($asvcs[0]->cdn_use[0] == 'Y') ? 'Y' : 'N';
	if($cdn_use == 'Y') {
        $bimg_used = filesizeStr($asvcs[0]->cdn_used[0]*=1024, 2);
		$wdisk_per = floor(($asvcs[0]->cdn_used[0]/($asvcs[0]->cdn_limit[0]*1000*1000))*100);
		$bimg_limit = $asvcs[0]->cdn_limit[0].'G';
		$bimg_finish = date('Y.m.d', $asvcs[0]->cdn_expire[0]);
		$bimg_warning = ($bimg_per > (int)$asvcs[0]->img_warning[0]) ? 'warning' : '';
		$bimg_left = ($cdn_finish == '무제한') ? '무제한' : floor(($asvcs[0]->cdn_expire[0]-$now)/86400);
        if ($bimg_left > 0) {
            $bimg_left = 'D-'.$bimg_left;
        } else {
            $bimg_left = 0;
        }
        $cdn_left_warning = (numberOnly($bimg_left) > 14) ? '' : 'warning';
	}
	$disk_svc_name = $asvcs[0]->disk_svc_name[0];

	# 대표도메인
	$mdomain = (preg_match('/mywisa\.(com|co\.kr)$/', $root_url)) ? '미설정' : '설정완료';
	$mdomain_onoff = ($mdomain == '미설정') ? 'OFF' : 'ON';

	# 모바일 서비스 사용여부
	$mobile_use_css = $cfg['mobile_use'] == 'Y' ? 'over' : '';
	$mobile_use_stat = $cfg['mobile_use'] == 'Y' ? '사용중' : '사용안함';

	# 앱 사용여부
	$wec_app = new weagleEyeClient($_we, 'push');
	$app = $wec_app->call('appStatus');
	$app = json_decode($app,true);

	$app_use_stat = str_replace(' ', '', $app['status_str']);
    $app_use_css = $app['status'];

	# 관리자 앱 사용 여부
	if($cfg['use_mng_app'] == 'Y') {
		$mng_app_use_css = 'over';
		$mng_app_use_stat = '사용중';
	} else {
		$mng_app_use_css = '';
		$mng_app_use_stat = '사용안함';
	}

	# 광고 서비스
	$use_criteo = ($cfg['criteo_use'] == 'Y') ? 'on' : 'off';
	$use_smartmd = ($cfg['logger_smartMD_id']) ? 'on' : 'off';
	$use_heatmap = ($cfg['logger_heatmap_cusId']) ? 'on' : 'off';
	$use_acecounter = ($cfg['ace_counter_gcode']) ? 'on' : 'off';

	# 그룹 메일
	$account = $wec->get('410', '', true);
	$use_groupmail = ($account[0]->mail_rest[0] > 0) ? 'on' : 'off';

	function mainSvcBtn($stat) {
		return ($stat == 'on') ? '관리' : '신청';
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  재입고 알림 알림기간만료 처리
	' +----------------------------------------------------------------------------------------------+*/
	// 재입고 알림 사용시
	if($cfg['notify_restock_use'] == "Y") {
		// 알림기간만료 설정값 경과한 신청내역 알림기간만료로 전환
		$notify_restock_expiretime = ($cfg['notify_restock_expire']) ? strtotime($cfg['notify_restock_expire']) : "";
		if($notify_restock_expiretime) {
			$notify_restock_expire_sql_add = " AND `reg_date` <= '$notify_restock_expiretime' ";
			$notify_restock_expire_sql = "UPDATE $tbl[notify_restock] SET stat=4, update_date='$now' WHERE del_stat='N' AND stat=1 $notify_restock_expire_sql_add ";
			$pdo->query($notify_restock_expire_sql);
		}
	}

?>
<style type="text/css" title="">
body {background:none; background:#e8e8e8;}
.solving {display:none;}
#wrapper {clear:both; width:100%; height:100%; margin-left:0;}
#contentArea {margin-left:0; padding:0;}
#navigation {display:none;}
#main {position:relative; margin:20px 50px 40px 50px; text-align:center;}
</style>

<link rel="stylesheet" href="<?=$engine_url?>/_engine/common/swiper/swiper-bundle.css"/>
<link rel="stylesheet" href="<?=$engine_url?>/_manage/css/main.css"/>

<?php if (getIsCardTest()) { ?>
<div class="msg_topbar check">
	<strong>PG연동 실행모드가 테스트로 되어 있습니다.</strong>
	<a href="/_manage/?body=config@card" class="list_move">설정변경</a>
	<a onclick="$('.msg_topbar').slideUp('fast');" class="close">닫기</a>
</div>
<?php } ?>
<?php if (isset($cfg['count_log_use']) && $cfg['count_log_use'] == 'N') { ?>
<div class="msg_topbar check">
	<strong>접속통계가 사용안함 상태입니다.</strong>
	<a href="/_manage/?body=log@count_log_config" class="list_move">설정변경</a>
	<a onclick="$('.msg_topbar').slideUp('fast');" class="close">닫기</a>
</div>
<?php } ?>
<div id="main">
	<div class="tab">
		<ul>
			<li><a onclick="tabover(0)" class="over">대시보드</a></li>
			<li><a onclick="tabover(1)">전체현황</a></li>
		</ul>
	</div>
	<div class="p_left">
		<div class="box">
			<h2>
				요금제
			</h2>
			<div class="content wingdisk">
				<div class="stat">
					<h3><?=str_replace('윙독립형 ', '', $asvcs[0]->mall_goods_name[0])?></h3>
					<a href="http://redirect.wisa.co.kr/userPolicy" target="_blank">이용정책</a>
				</div>
				<div class="frame <?=$bimg_warning?>">
					<div class="bar">
						<div class="gauge" style="width:<?=$bimg_per?>%;"><span class="volume"><?=$bimg_used?></span></div>
					</div>
				</div>
				<?php if ($cdn_use == 'Y') { ?>
				<div class="info">
                    <ul>
                        <li>유료신청용량 : <strong><?=$bimg_limit?></strong></li>
                        <?php if ($bimg_left === 0) {?>
						<li>
                            유료만료기간 : <strong class="p_color2">~<?=$bimg_finish?></strong>
                            <a href="#Expand" onclick="goMywisa('?body=wing@main'); return false;" class="<?=$cdn_left_warning?>">기간연장</a>
                        </li>
                        <?php } else { ?>
                        <li>
                            유료만료기간 : <strong>~<?=$bimg_finish?></strong> (<?=$bimg_left?>)
                        </li>
                        <?php } ?>
                     </ul>
                </div>
                <?php } else if($mall_goods_idx == '3') { ?>
                <!-- 구 윙요금제 -->
				<div class="info">
					<ul>
						<?php if($wdisk_limit == '0' && $cdn_use == 'N') { ?>
						<li>유료신청용량 : <strong>신청전</strong></li>
						<li>유료만료기간 : <strong>신청전</strong></li>
						<?php } else { ?>
						<li>유료신청용량 : <strong><?=number_format($wdisk_limit)?></strong>MB</li>
						<?php if($wdisk_fin == 'OFF') { ?>
						<li>유료만료기간 : <strong class="p_color2"><?=$wdisk_expire?></strong></li>
						<?php } else { ?>
						<li>유료만료기간 : <strong>~<?=$wdisk_fin?></strong> (D-<?=$wdisk_left?>)</li>
						<?php } ?>
						<?php } ?>
					</ul>
				</div>
				<?php } else { ?>
                <!-- 클라우드 요금제 && 독립형 요금제 -->
				<div class="info">
					<ul>
						<li>
							<?=$asvcs[0]->disk_svc_name[0]?> 이미지용량 : <strong><?=$bimg_limit?></strong>
							<?php if ($mall_goods_idx == '3' && $cdn_use == 'Y') { ?>
							<a href="#Expand"  onclick="goMywisa('?body=wing@main'); return false;" class="<?=$bimg_warning?>">용량추가</a>
							<?php } ?>
						</li>
						<li>
							이용기간 : <?=$bimg_finish?>
							<?php if ($bimg_left != '무제한') { ?>
							(<strong class="<?=$bimg_left_warning?>"><?=$bimg_left?></strong>)
							<?php } ?>
							<?php if ($mall_goods_idx == '5' || $mall_goods_idx == '6' || $asvcs[0]->type[0] == '10') { ?>
							<a href="#Expand"  onclick="goMywisa('?body=wing@main'); return false;" class="<?=$bimg_left_warning?>">기간연장</a>
							<?php } ?>
						</li>
					</ul>
				</div>
				<?php } ?>
                <?php if ($asvcs[0]->type[0] == '10' && $cdn_use == 'N') { ?>
                <!-- 독립형 CDN 미사용시 -->
				<div class="notice">
					<p>트래픽초과 또는 기간만료후에는 이미지만<br>차단, 솔루션과 데이터는 정상 유지됩니다.</p>
					<div class="btn">
						<a href="http://redirect.wisa.co.kr/hostingExpand" target="_blank">트래픽확인</a>
						<a href="http://redirect.wisa.co.kr/hostingExpand" target="_blank">업그레이드</a>
					</div>
				</div>
                <?php } else if($asvcs[0]->type[0] != '10' && $cdn_use == 'Y') { ?>
                <!-- 임대형 CDN 사용시 -->
				<div class="notice">
					<p>트래픽초과 또는 기간만료후에는 이미지만<br>차단, 솔루션과 데이터는 정상 유지됩니다.</p>
					<div class="btn">
						<a href="http://redirect.wisa.co.kr/wingCDNExpandDate" target="_blank">트래픽확인</a>
						<a href="http://redirect.wisa.co.kr/wingCDNExpandDate" target="_blank">업그레이드</a>
					</div>
				</div>
				<?php } ?>
			</div>
			<?php if (($asvcs[0]->type[0] == '10' && $cdn_use == 'Y') || ($asvcs[0]->type[0] != '10' && $cdn_use != 'Y')) { ?>
			<div class="content recommend">
                <?php if ($mall_goods_idx == '4') { ?>
                <!-- 클라우드 스탠다드 -->
				<ul>
					<li>무제한 용량 <a href="http://redirect.wisa.co.kr/wingUpgrade" target="_blank"><strong>PRO로 업그레이드</strong>하기</a></li>
					<li>외부링크가 가능한 <a href="http://redirect.wisa.co.kr/wingCDNApplication" target="_blank"><strong>윙CDN</strong> 전환</a>하기</li>
				</ul>
				<?php } ?>
				<?php if($mall_goods_idx == '5') { ?>
                <!-- 클라우드 프로 -->
				<ul>
					<li>속도, 안정성이 우수한 <a href="http://redirect.wisa.co.kr/wingUpgrade" target="_blank"><strong>단독서버</strong>로 업그레이드</a></li>
					<li>외부링크가 가능한 <a href="http://redirect.wisa.co.kr/wingCDNApplication" target="_blank"><strong>윙CDN</strong> 전환</a>하기</li>
				</ul>
				<?php } ?>
				<?php if ($mall_goods_idx == '6') { ?>
                <!-- 클라우드 마스터 -->
				<ul>
					<li>
						지금보다 더 안정적이고 쇼핑몰 규모에 맞도록 네트워크 설계가 가능합니다.<br>
						<a href="http://redirect.wisa.co.kr/viewServiceHosting" target="_blank">서비스호스팅 상품보기</a><br>
						<a href="#inquery" onclick="goMywisa('?body=customer@list'); return false;">문의하기</a>
					</li>
				</ul>
				<?php } elseif($mall_goods_idx == '3') { ?>
                <!-- 구 윙요금제 -->
				<ul>
					<li>
						<?php if($wdisk_limit == '0') { ?>
						<a href="#" onclick="goMywisa('?body=wing@main'); return false;">신청하기</a>
						<?php } else { ?>
						<?php if ($cdn_use == 'Y') { ?>
						<a href="http://www.wisa.co.kr/mypage/cdn" target="_blank">기간연장</a>
						<?php } else { ?>
						<a href="#" onclick="goMywisa('?body=wing@main'); return false;">기간연장</a>
						<?php } ?>
						<?php } ?>
					</li>
					<li>개편된 <a href="https://www.wisa.co.kr/bbs/detail/116274" target="_blank"><strong>클라우드 요금제 보러</a>가기</strong></li>
				</ul>
				<?php } ?>
                <?php if($asvcs[0]->type[0] == '10' && $cdn_use == 'Y') { ?>
				<div class="server">
					<h3><?=$asvcs[0]->mall_hosting_name[0]?></h3>
                    <?php if ($hosting_left == '만료') {?>
					<p>
                        이용기간 : <strong class="p_color2">~<?=$hosting_finish?></strong>
					    <a href="#Expand"  onclick="goMywisa('?body=wing@main'); return false;" class="<?=$bimg_left_warning?>">기간연장</a>
                    </p>
                    <?php } else { ?>
					<p>
                        이용기간 : <?=$hosting_finish?> (<strong><?=$hosting_left?></strong>)
					    <a href="#Expand"  onclick="goMywisa('?body=wing@main'); return false;" class="<?=$bimg_left_warning?>">기간연장</a>
                    </p>
                    <?php } ?>
				</div>
                <?php } ?>
			</div>
			<?php } ?>
		</div>

		<div class="box">
			<h2>문자서비스 현황</h2>
			<?php if ($sms_rest < 1) { ?>
			<div class="content2 sms set">
				<p>내 고객에게 가장 빠른 프로모션<br>전송률 98%를 자랑하는<br>윙문자를 이용해보세요!</p>
				<div class="bottom_btn">
					<span class="box_btn"><a href="?body=member@sms_config">문자설정</a></span>
					<span class="box_btn"><a href="#" onclick="goMywisa('?body=wing@sms_list'); return false;">발송내역</a></span>
					<span class="box_btn"><a href="#" onclick="goMywisa('?body=wing@order&service=sms&type=4'); return false;">문자충전소</a></span>
				</div>
			</div>
			<?php } else { ?>
			<div class="content2 sms use">
				<div class="point">
					<?php if($sms_rest > ($total_members*21)) { ?>
					<div class="icon"><div class="gauge" style="height:20%;"></div></div>
					<?php } else { ?>
					<div class="icon icon2"><div class="gauge" style="height:80%;"></div></div>
					<?php } ?>
					<dl>
						<dt>잔여포인트</dt>
						<dd><?=number_format($sms_rest)?>P</dd>
					</dl>
					<span class="box_btn_s log"><a href="#" onclick="goMywisa('?body=wing@sms_list'); return false;">발송내역</a></span>
					<span class="box_btn_s charge"><a href="#" onclick="goMywisa('?body=wing@order&service=sms&type=4'); return false;">문자충전소</a></span>
				</div>
				<div class="summary">
					<?php if ($sms_rest > ($total_members*21)) { ?>
					<ul class="tip">
						<li><a href="http://redirect.wisa.co.kr/callback_help">[안내] 발신번호 사전등록하기</a>
						<li><a href="http://redirect.wisa.co.kr/080service">[안내] 080 수신거부 서비스 신청하기</a></li>
					</ul>
					<?php } else { ?>
					<p class="warning">소중한 나의 고객에게 안내 문자가<br>송출되지 않을 수 있습니다</p>
					<?php } ?>
				</div>
			</div>
			<?php } ?>
		</div>
		<div class="box">
			<h2>자동입금 확인</h2>
			<?php if ($bankda_fin == 'OFF' && $bankda_accounts > 0) { ?>
			<div class="content deposit set">
				<p>서비스가 <strong class="p_color2">만료</strong>되었습니다.<br>주문자동매칭이 안 될 수 있습니다.</p>
				<span class="box_btn_s"><a href="#" onclick="goMywisa('?body=wing@order&service=bank&type=1'); return false;">신청하기</a></span>
			</div>
			<?php } elseif($bankda_fin == 'OFF') { ?>
			<div class="content deposit set">
				<p>무통장으로 주문한 내역과<br>내 통장의 입금내역을<br>자동매칭하는서비스입니다.</p>
				<span class="box_btn_s"><a href="#" onclick="goMywisa('?body=wing@order&service=bank&type=1'); return false;">신청하기</a></span>
			</div>
			<?php } else { ?>
			<div class="content deposit use">
				<ul>
					<li>등록계좌 : <strong><?=$bankda_accounts?></strong> 개</li>
					<li>만료기간 : <strong>~<?=$bankda_fin?></strong> <?if($bankda_left > -1) {?>(D-<?=$bankda_left?>)<?}?></li>
				</ul>
				<div class="btn">
					<span class="box_btn_s"><a href="#" onclick="goMywisa('?body=wing@bank_account'); return false;">계좌추가</a></span>
					<span class="box_btn_s"><a href="#" onclick="goMywisa('?body=wing@order&service=bank&type=2'); return false;">기간연장</a></span>
				</div>
			</div>
			<?php } ?>
		</div>
		<div class="box">
			<h2>모바일</h2>
			<div class="content2 device">
				<ul class="list">
					<li class="web p_cursor <?=$mobile_use_css?>" onclick="location.href='/_manage/?body=wmb@config'">
						<div class="img"></div>
						<p>WEB</p>
						<p class="use"><span><?=$mobile_use_stat?></span></p>
					</li>
					<li class="app_shop p_cursor <?=$app_use_css?>">
						<a href="?body=wmb@push.exe" target="hidden<?=$now?>">
							<div class="img"></div>
							<p>쇼핑 APP</p>
							<p class="use"><span><?=$app_use_stat?></span></p>
						</a>
					</li>
					<li class="app_admin <?=$mng_app_use_css?>">
						<a href="http://redirect.wisa.co.kr/adminapp" target="_blank">
							<div class="img"></div>
							<p>관리 APP</p>
							<p class="use"><span><?=$mng_app_use_stat?></span></p>
						</a>
					</li>
				</ul>
			</div>
		</div>
	</div>
	<div class="p_right">
		<iframe name="wisatagram" src="//www.wisa.co.kr/wisatagram3.php?account_idx=<?=$asvcs[0]->account_idx[0]?>&admin_id=<?=$admin['admin_id']?>" scrolling="no" frameborder="0" style="width:300px; height:934px;"></iframe>
	</div>
	<div class="p_center tabcnt0">
		<div class="today">
			<div class="sales">
				<a href="./?body=income@income_log&log_mode=1" target="_blank" title="자세히보기">
					<div class="frame">
						<div class="top_frame_div">
							<span class="subject">오늘 총매출(<?=$cfg['currency_type']?>)</span>
							<p><?=number_format($ostat['prc'][date('Ymd', $now)],$cfg['currency_decimal'])?></p>
						</div>
					</div>
				</a>
				<div class="bottom_frame rolling" style="vertical-align:center;">
					<div class="swiper swiperVertical">
						<div class="swiper-wrapper">
							<div class="swiper-slide"><span>최근 7일 평균 대비 <?=$order_latest7_updown_percent?></span></div>
							<div class="swiper-slide"><span>지난주 <?=$lastweekday_text?> 대비 <?=$lastWeekSameDay_prc_updown_percent?></span></div>
						</div>
						<div class="swiper-button-next" data-name="sales"></div>
						<div class="swiper-button-prev" data-name="sales"></div>
					</div>
				</div>
			</div>
			<div class="order">
				<a href="./?body=order@order_list" target="_blank" title="자세히보기">
					<div class="frame">
						<div class="top_frame_div">
							<span class="subject">오늘 주문수(건)</span>
							<p><?=number_format($ostat['count'][date('Ymd', $now)])?></p>
						</div>
					</div>
				</a>
				<div class="bottom_frame rolling" style="vertical-align:center;">
					<div class="swiper swiperVertical">
						<div class="swiper-wrapper">
							<div class="swiper-slide"><span>최근 7일 평균 대비 <?=$latest7_order_updown_percent?></span></div>
							<div class="swiper-slide"><span>지난주 <?=$lastweekday_text?> 대비 <?=$lastWeekSameDay_order_updown_percent?></span></div>
						</div>
						<div class="swiper-button-next" data-name="order"></div>
						<div class="swiper-button-prev" data-name="order"></div>
					</div>
				</div>
			</div>
			<div class="visiter">
				<a href="./?body=log@count_log" target="_blank" class="p_cursor" title="자세히보기">
					<div class="frame">
						<div class="top_frame_div">
							<span class="subject">오늘 방문자(명)</span>
							<p><?=number_format($access_today)?></p>
						</div>
					</div>
				</a>
				<div class="bottom_frame rolling" style="vertical-align:center;">
					<div class="swiper swiperVertical">
						<div class="swiper-wrapper">
							<div class="swiper-slide"><span>최근 7일 평균 대비 <?=$latest7_visitor_updown_percent?></span></div>
							<div class="swiper-slide"><span>지난주 <?=$lastweekday_text?> 대비 <?=$lastWeekSameDay_visitor_updown_percent?></span></div>
						</div>
						<div class="swiper-button-next" data-name="visiter"></div>
						<div class="swiper-button-prev" data-name="visiter"></div>
					</div>
				</div>
			</div>
			<div class="signin">
				<a href="./?body=member@member_list" target="_blank" title="자세히보기">
					<div class="frame">
						<div class="top_frame_div">
							<span class="subject">오늘 가입자(명)</span>
							<p><?=$member_new?></p>
						</div>
					</div>
				</a>
				<div class="bottom_frame rolling" style="vertical-align:center;">
					<div class="swiper swiperVertical">
						<div class="swiper-wrapper">
							<div class="swiper-slide"><span>최근 7일 평균 대비 <?=$latest7_member_updown_percent?></span></div>
							<div class="swiper-slide"><span>지난주 <?=$lastweekday_text?> 대비 <?=$lastWeekSameDay_member_updown_percent?></span></div>
						</div>
						<div class="swiper-button-next" data-name="signin"></div>
						<div class="swiper-button-prev" data-name="signin"></div>
					</div>
				</div>
			</div>
		</div>
		<div class="box sales">
			<h2>
				<span><span id="mainText">매출</span> 현황 <span class="mainTextDate">최근 30일 (<?=date('Y.m.d', $ostat_start_day)?> ~ <?=date('Y.m.d', $now)?>)</span></span>

				<span class="box_btn_group">
					<span class="box_btn_s active"><input type="button" value="매출" onclick="salesStatus(this, 'sales');"></span>
					<span class="box_btn_s"><input type="button" value="주문" onclick="salesStatus(this, 'order');"></span>
				</span>
			</h2>
			<div class="content" style="border-top:0px;">
				<div style="width:100%;">
					<canvas id="chart_sales" style="width:100%;height:280px;margin:0 30px;"></canvas>
				</div>
			</div>
		</div>
		<div class="graph">
			<div class="box customer">
				<h2>
					<span>판매 상품 순위 <span class="mainTextDate">최근 14일 (<?=date('Y.m.d', $sell_start_day)?> ~ <?=date('Y.m.d', $now)?>)</span></span>
				</h2>
				<div class="content">
					<div class="swiper swiperSlide">
						<div class="swiper-wrapper">
							<div class="swiper-slide">
								<table class="tbl_col" style="border:0;height:280px;">
									<caption class="hidden">판매 상품 순위</caption>
									<colgroup>
										<col style="width:15px">
										<col style="width:70px">
										<col>
										<col style="width:40px">
										<col style="width:60px">
									</colgroup>
									<?php if(count($selling_array_data) == 0) { ?>
									<tr class="none">
										<td colspan="6" style="border-bottom:0;"><p class="nodata">최근 14일간 판매된 상품이 없습니다.</p></td>
									</tr>
									<?php } else { ?>
									<tbody>
										<?php
										$selling_array_data_5 = array_slice($selling_array_data, 0, 5);
										foreach($selling_array_data_5 as $sK => $sV) {
											$todaySale = '-';
											if($sV['today_count'] > '0') $todaySale = '<span style="color:#F35958">▲ '.number_format($sV['today_count']).'</span>';
										?>
										<tr>
											<td><strong><?=($sK+1)?>.</strong></td>
											<td><a href="./?body=income@income_product_detail&pno=<?=$sV['pno']?>" target="_blank"><img src="<?=$sV['img']?>" width="32" height="32"></a></td>
											<td style="text-align:left;"><a href="./?body=income@income_product_detail&pno=<?=$sV['pno']?>" target="_blank"><?=$sV['name']?></a></td>
											<td title="총 판매건수" style="cursor:pointer;"><strong><?=number_format($sV['total'])?></strong><div id="jquery_btt_4" class="jquery_btt tooltip_square" style="position: absolute; left: 450px; top: 330px; display: none;"><div class="message">일</div></div></td>
											<td title="오늘 판매건수" style="cursor:pointer;"><span><?=$todaySale?></span></td>
										</tr>
										<?php } ?>

										<?php
										if(count($selling_array_data_5) < 5) {
											for($i = count($selling_array_data_5); $i < 5; $i++) {
										?>
										<tr>
											<td><strong>-</strong></td>
											<td></td>
											<td style="text-align:left;">-</td>
											<td title="총 판매건수" style="cursor:pointer;"><strong>-</strong></td>
											<td title="오늘 판매건수" style="cursor:pointer;"><span>-</span></td>
										</tr>
										<?php
											}
										}
										?>
									</tbody>
									<?php } ?>
								</table>
							</div>
							<?php if(count($selling_array_data) > 5) { ?>
							<div class="swiper-slide">
								<table class="tbl_col" style="border:0;height:280px;">
									<caption class="hidden">판매 상품 순위</caption>
									<colgroup>
										<col style="width:15px">
										<col style="width:70px">
										<col>
										<col style="width:40px">
										<col style="width:60px">
									</colgroup>
									<tbody>
										<?php
										$selling_array_data_10 = array_slice($selling_array_data, 5, 5);
										foreach($selling_array_data_10 as $sK => $sV) {
											$todaySale = '-';
											if($sV['today_count'] > '0') $todaySale = '<span style="color:#F35958">▲ '.number_format($sV['today_count']).'</span>';
										?>
										<tr>
											<td><strong><?=($sK+6)?>.</strong></td>
											<td><a href="./?body=income@income_product_detail&pno=<?=$sV['pno']?>" target="_blank"><img src="<?=$sV['img']?>" width="32" height="32"></a></td>
											<td style="text-align:left;"><a href="./?body=income@income_product_detail&pno=<?=$sV['pno']?>" target="_blank"><?=$sV['name']?></a></td>
											<td title="총 판매건수" style="cursor:pointer;"><strong><?=number_format($sV['total'])?></strong></td>
											<td title="오늘 판매건수" style="cursor:pointer;"><span><?=$todaySale?></span></td>
										</tr>
										<?php } ?>

										<?php
										if(count($selling_array_data_10) < 5) {
											for($i = count($selling_array_data_10); $i < 5; $i++) {
										?>
										<tr>
											<td><strong>-</strong></td>
											<td></td>
											<td style="text-align:left;">-</td>
											<td title="총 판매건수" style="cursor:pointer;"><strong">-</strong></td>
											<td title="오늘 판매건수" style="cursor:pointer;"><span>-</span></td>
										</tr>
										<?php
											}
										}
										?>
									</tbody>
								</table>
							</div>
							<?php } ?>
						</div>
						<div class="swiper-pagination"></div>
					</div>
				</div>
			</div>
			<div class="box join">
				<h2>
					<span class="joinMainText">회원가입 추이 <span class="mainTextDate"><?=$member_join_day_headText?></span></span>

					<span class="box_btn_group">
						<span class="box_btn_s active"><input type="button" value="일" onclick="memberJoinStatus(this, 'day');"></span>
						<span class="box_btn_s"><input type="button" value="주" onclick="memberJoinStatus(this, 'week');"></span>
						<span class="box_btn_s"><input type="button" value="월" onclick="memberJoinStatus(this, 'month');"></span>
					</span>
				</h2>
				<div class="content" style="border-top:0px;">
					<div style="width:100%;">
						<canvas id="chartJoin" style="width:100%;height:311px;"></canvas>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="p_center tabcnt1" style="display:none;">
		<div class="service">
			<div class="box">
				<h2>결제서비스</h2>
				<div class="content2">
					<ul class="list">
						<li class="<?=$pg_onoff?>"><a href="?body=config@card">PG통합결제</a> <span><strong><?=strtoupper($pg_onoff)?></strong> | <a href="?body=config@card"><?=mainSvcBtn($pg_onoff)?></a></span></li>
						<li class="<?=$mob_pg_onoff?>"><a href="?body=config@card">모바일결제</a> <span><strong><?=strtoupper($mob_pg_onoff)?></strong> | <a href="?body=config@card"><?=mainSvcBtn($mob_pg_onoff)?></a></span></li>
						<li class="<?=$use_danal?>"><a href="?body=config@account">휴대폰소액결제</a> <span><strong><?=strtoupper($use_danal)?></strong> | <a href="?body=config@account"><?=mainSvcBtn($use_danal)?></a></span></li>
						<li class="<?=$use_pay_type_5?>"><a href="?body=config@account">실시간계좌이체</a> <span><strong><?=strtoupper($use_pay_type_5)?></strong> | <a href="?body=config@account"><?=mainSvcBtn($use_pay_type_5)?></a></span></li>
						<li class="<?=$use_wingbank?>"><a href="#" onclick="goMywisa('?body=wing@bank_account'); return false;">윙BANK(무통장자동입금확인)</a> <span><strong><?=strtoupper($use_wingbank)?></strong> | <a href="#" onclick="goMywisa('?body=wing@bank_account'); return false;"><?=mainSvcBtn($use_wingbank)?></a></span></li>
						<li class="<?=$use_escrow?>"><a href="?body=config@escrow">에스크로우</a> <span><strong><?=strtoupper($use_escrow)?></strong> | <a href="?body=config@escrow"><?=mainSvcBtn($use_escrow)?></a></span></li>
						<li class="<?=$use_cash_receipt?>"><a href="?body=config@cash_receipt">현금영수증</a> <span><strong><?=strtoupper($use_cash_receipt)?></strong> | <a href="?body=config@cash_receipt"><?=mainSvcBtn($use_cash_receipt)?></a></span></li>
						<li><a href="#" onclick="return false;">해외결제</a> <span><strong>OFF</strong> | <a href="#" onclick="return false;">신청</a></span></li>
					</ul>
				</div>
			</div>
			<div class="box">
				<h2>부가서비스</h2>
				<div class="content2">
					<ul class="list">
						<li class="on"><a href="?body=erp@erp_config">윙POS(재고관리)</a><span><strong>ON</strong> | <a href="?body=erp@erp_config">관리</a></span></li>
						<li class="<?=$use_dooson?>">ERP(재고관리) <span><strong><?=strtoupper($use_dooson)?></strong></li>
						<li class="on"><a href="http://redirect.wisa.co.kr/mcbox?userid=<?=str_replace('-', '', $wec->config['wm_key_code']);?>" target="_blank">포장박스 할인</a>  <span><strong>ON</strong> | <a href="http://redirect.wisa.co.kr/mcbox?userid=<?=str_replace('-', '', $wec->config['wm_key_code']);?>" target="_blank">신청</a></span></li>
					</ul>
				</div>
			</div>
		</div>
		<div class="summary">
			<div class="box">
				<h2>마케팅 현황</h2>
				<div class="content2">
					<ul class="list">
						<?php if ($cfg['compare_use'] == 'Y') { ?>
						<li class="on"><a href="?body=openmarket@compare_setup">네이버쇼핑</a> <span><strong>ON</strong> | <a href="?body=openmarket@compare_setup">관리</a></span></li>
						<?php } else { ?>
						<li><a href="http://redirect.wisa.co.kr/naver_shopping" target="_blank">네이버쇼핑</a> <span><strong>OFF</strong> | <a href="http://redirect.wisa.co.kr/naver_shopping" target="_blank">신청</a></span></li>
						<?php } ?>
						<?php if ($cfg['show_use'] == 'Y') { ?>
						<li class="on"><a href="?body=openmarket@show_setup">다음쇼핑하우</a> <span><strong>ON</strong> | <a href="?body=openmarket@show_setup">관리</a></span></li>
						<?php } else { ?>
						<li><a href="http://redirect.wisa.co.kr/daum_shopping" target="_blank">다음쇼핑하우</a> <span><strong>OFF</strong> | <a href="http://redirect.wisa.co.kr/daum_shopping" target="_blank">신청</a></span></li>
						<?php } ?>
						<li class="<?=$use_crited?>"><a href="?body=openmarket@criteo">크리테오</a> <span><strong><?=strtoupper($use_criteo)?></strong> | <a href="?body=promotion@mm_shopadd"><?=mainSvcBtn($use_criteo)?></a></span></li>
						<li class="<?=$use_smartmd?>"><a href="?body=log@smartMD_info">스마트MD</a> <span><strong><?=strtoupper($use_smartmd)?></strong> | <a href="?body=log@smartMD_info"><?=mainSvcBtn($use_smartmd)?></a></span></li>
						<li class="<?=$use_acecounter?>"><a href="?body=log@ac_apply">방문자분석</a> <span><strong><?=strtoupper($use_acecounter)?></strong> | <a href="?body=log@ac_apply"><?=mainSvcBtn($use_acecounter)?></a></span></li>
						<!-- <li class="<?=$use_heatmap?>"><a href="?body=log@heatmap_info">실시간클릭 히트맵</a> <span><strong><?=strtoupper($use_heatmap)?></strong> | <a href="?body=log@heatmap_info"><?=mainSvcBtn($use_heatmap)?></a></span></li> -->
						<li class="<?=$use_groupmail?>"><a href="#" onclick="goMywisa('?body=wing@order&service=mail&type=4'); return false;">대량메일</a> <span><strong><?=strtoupper($use_groupmail)?></strong> | <a href="#" onclick="goMywisa('?body=wing@order&service=mail&type=4'); return false;"><?=mainSvcBtn($use_groupmail)?></a></span></li>
					</ul>
				</div>
			</div>
			<div class="box">
				<h2>도메인</h2>
				<div class="content2">
					<ul class="list">
						<li class="on">기본도메인 <span><a href="http://<?=$asvcs[0]->account_id[0]?>.<?=_BASE_DOM_SUFFIX_?>" target="_blank">http://<?=$asvcs[0]->account_id[0]?>.<?=_BASE_DOM_SUFFIX_?></a></span></li>
						<li class="on">대표도메인 <span><a href="<?=$root_url?>" target="_blank"><?=$root_url?></a></span></li>
						<li class="on">모바일URL <span><a href="<?=$m_root_url?>" target="_blank"><?=$m_root_url?></a></span></li>
						<li class="on">연결도메인 <span><a href="<?=$root_url?>" target="_blank"><?=$root_url?></a></span></li>
					</ul>
				</div>
			</div>
		</div>
		<div class="security">
			<div class="box">
				<h2>보안&백업시스템</h2>
				<div class="content2">
					<ul>
						<li class="webfirewall">
							<p>웹방화벽</p>
							<p class="normal">정상가동중</p>
						</li>
						<li class="firewall">
							<p>방화벽</p>
							<p class="normal">정상가동중</p>
						</li>
						<li class="ssl">
							<p>SSL 보안</p>
							<p class="normal">정상가동중</p>
						</li>
						<li class="ddos">
							<p>DDOS 보안</p>
							<p class="normal">정상가동중</p>
						</li>
						<li class="backup">
							<p>백업시스템</p>
							<p class="normal">정상가동중</p>
						</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</div>

<script src="<?=$engine_url?>/_engine/common/chart/chart.js"></script>
<script src="<?=$engine_url?>/_engine/common/chart/chartjs-function.js"></script>
<script src="<?=$engine_url?>/_engine/common/swiper/swiper-bundle.js"></script>
<script type="text/javascript">
	function tabover(no) {
		var tabs = $('#main .tab').find('li');
		tabs.each(function(idx) {
			var detail = $('.tabcnt'+idx);
			if(no == idx) {
				detail.fadeIn('fast');
				$(this).find('a').addClass('over');
			} else {
				detail.fadeOut('fast');
				$(this).find('a').removeClass('over');
			}
		})
	}

	const swiperSlide = new Swiper('.swiperSlide', {
		spaceBetween: 60,
		grabCursor: true,
		autoplay: {
			delay: 5000,
			disableOnInteraction: true,
		},
		pagination: {
			el: '.swiper-pagination',
			clickable: true,
		},
	});

    // 마우스 오버시 autoplay 정지
    $('.swiperSlide').hover(function(){
        swiperSlide.autoplay.stop();
    }, function(){
        swiperSlide.autoplay.start();
    });

	const swiperVertical = new Swiper(".swiperVertical", {
		direction: "vertical",
		loop: true,
		cssMode: true,
		autoplay: {
			delay: 7000,
			disableOnInteraction: false,
		},
		mousewheel: true,
		loopedSlides: 1,
		keyboard: true,
	});

	//상단 롤링에서 이전 버튼 누르면
	$(".swiperVertical div.swiper-button-prev").on('click', function (e) {
		for (let i = 0; i < swiperVertical.length; i++) {
			if ($(swiperVertical[i].$el[0]).parent().hasClass('rolling')) {
				swiperVertical[i].slidePrev();
			}

			if ($(swiperVertical[i].$el[0]).parent().hasClass('rolling') && $(swiperVertical[i].el).find('.swiper-slide-active').hasClass('swiper-slide-duplicate')) {
				swiperVertical[i].slideTo(1);
			}
		}
	});

	//상단 롤링에서 이후 버튼 누르면
	$(".swiperVertical div.swiper-button-next").on('click', function (e) {
		for (let i = 0; i < swiperVertical.length; i++) {
			if ($(swiperVertical[i].$el[0]).parent().hasClass('rolling')) {
				swiperVertical[i].slideNext();
			}

			if ($(swiperVertical[i].$el[0]).parent().hasClass('rolling') && $(swiperVertical[i].el).find('.swiper-slide-active').hasClass('swiper-slide-duplicate')) {
				swiperVertical[i].slideTo(2);
			}
		}
	});

	//상단 박스 롤링 마우스 오버시 stop
	$(document).on('mouseover', '.bottom_frame', function(e) {
		$(this).css('cursor', 'grabbing');
		swiperVertical.forEach(function(s) {
			s.autoplay.stop();
		});
	});

	//상단 박스 롤링 마우스 아웃시 start
	$(document).on('mouseout', '.bottom_frame', function(e) {
		for (let i = 0; i < $('.bottom_frame.rolling').length; i++) {
			$($('.bottom_frame.rolling')[i]).children('.swiper')[0].swiper.autoplay.start();
		}
	});

	//상단 박스 롤링 클릭시 stop or 재실행
	$(document).on('click', '.bottom_frame .swiperVertical .swiper-slide span', function(e) {
        if ($(this).closest('div.bottom_frame').hasClass('rolling')) {//subject
            $(this).closest('div.bottom_frame').removeClass('rolling');
            $(this).closest('div.swiper.swiperVertical')[0].swiper.autoplay.stop();
			$(this).css('font-weight', 'bold');
		} else {
			$(this).closest('div.bottom_frame').addClass('rolling');
			$(this).closest('div.swiper.swiperVertical')[0].swiper.autoplay.start();
			$(this).css('font-weight', 'normal');
		}
	});

	//매출 현황, 주문 현황 데이터 배열
	let array_chart_datas = {
		'sales' : [
			{
				type: 'line',
				label: '지난 7일 평균',
				data: <?=json_encode(array_values($ostat['prc_avg']))?>,
				borderColor: '#FFBFD1',
				backgroundColor: '#FFBFD1',
				id: 'sales_avg',
				sort: 2,
			},
			{
				radius: 0,
				label: '일매출',
				data: <?=json_encode(array_values($ostat['prc']))?>,
				borderRadius: 30,
				borderColor: '#759EDF',
				backgroundColor: '#759EDF',
				maxBarThickness: 8,
				id: 'sales',
				sort: 1,
			},
		],
		'order' : [
			{
				type: 'line',
				label: '지난 7일 평균',
				data: <?=json_encode(array_values($ostat['count_avg']))?>,
				borderColor: '#DF7593',
				backgroundColor: '#DF7593',
				id: 'order_avg',
				sort: 4,
			},
			{
				radius: 0,
				label: '일주문',
				data: <?=json_encode(array_values($ostat['count']))?>,
				borderRadius: 30,
				borderColor: '#A7C9FF',
				backgroundColor: '#A7C9FF',
				maxBarThickness: 8,
				id: 'order',
				sort: 3,
			},
		]
	};

	//매출 / 주문 버튼 클릭시 데이터 세팅 및 차트 업데이트
	function salesStatus(e, v) {
		$('#main .box.sales h2 span.box_btn_group .active').removeClass('active');
		$(e).parent().addClass('active');

		if (v == 'sales') {
			main_income_graph.data.datasets = array_chart_datas['sales'];
		}
		if (v == 'order') {
			main_income_graph.data.datasets = array_chart_datas['order'];
		}

		main_income_graph.update();
		$('#mainText').html(e.value);
	}

	// 매출 현황, 주문 현황 그래프
	let main_income_graph = chartMake('chart_sales', {
		type: 'bar',
		data: {
			labels: <?=json_encode($ostat_labels)?>,
			datasets: array_chart_datas['sales'],
		},
		options: {
			responsive: true,
			maintainAspectRatio: false,
			interaction: {
				intersect: false,
				mode: 'index',
			},
			plugins: {
				legend: {
					display: true,
					position: 'bottom',
					labels: {
						usePointStyle: true,
						boxWidth: 8,
						sort: function(a, b, data) {
							return data.datasets[a.datasetIndex].sort - data.datasets[b.datasetIndex].sort;
						},
					},
					onClick: function(event, legendItem, legend) {
						let hiddenChk = 0;
						legend.legendItems.forEach(function(e) {
							if (e.hidden == false) {
								hiddenChk++; //show 갯수 체크
							}
						});

						let index = legendItem.datasetIndex;
						let ci = legend.chart;

						if (ci.isDatasetVisible(index)) { //hidden할때
							if (hiddenChk == 1) { //1개만 켜져있으면 반대로 켜기
								legend.legendItems.forEach(function(e, i) {
									if(index != i) {
										ci.show(i);
										legend.legendItems[i] = false;
									}
								});
								ci.hide(index);
								legendItem.hidden = true;
							} else {
								ci.hide(index);
								legendItem.hidden = true;
							}
						} else {
							ci.show(index);
							legendItem.hidden = false;
						}
					},
				},
				title: {
					display: true,
				},
				tooltip: {
					enabled: false,
					external: externaltooltip,
        			position: 'custom',
					itemSort: function(a, b) {
						return a.dataset.sort - b.dataset.sort;
					},
					callbacks: {
						title: function(tooltipItems) {
							if(tooltipItems[0].label == '') {
								return '';
							} else {
								return tooltipItems[0].label;
							}
						},
						label: function(tooltipItems) {
							let unit = ' 원';
							if(tooltipItems.dataset.id == 'order' || tooltipItems.dataset.id == 'order_avg') {
								unit = ' 건';
							}
							return [tooltipItems.dataset.label, tooltipItems.formattedValue + unit];
						},
					},
				},
			},
			scales: {
				x: {
					display: true,
					grid: {
						display: false,
					},
					title: {
						display: false,
					},
					ticks: {
						font: {
							size: 11.5,
						},
						color: '#999999',
						callback: function(v, i, all) {
							let label_split = this.getLabelForValue(v).split(' ');
							if(i == all.length - 1) {
								return '오늘';
							}
							return label_split[2];
						},
					},
				},
				y: {
					display: true,
					position: 'left',
					min : 0,
					ticks: {
						color: '#999999',
					},
					afterFit: function(scaleInstance) {
						scaleInstance.width = 70;
					},
				},
			},
		},
	});

	//회원가입추이 데이터 배열
	let array_chart_datas_member_join = {
		'day' : {
			'labels' : <?=json_encode(array_keys($member_join_day))?>,
			'datas' : <?=json_encode(array_values($member_join_day))?>,
			'avg' : <?=json_encode(array_values($member_join_day_avg))?>,
			'headText' : '<?=$member_join_day_headText?>',
			'avg_text' : '지난 7일 평균',
		},
		'week' : {
			'labels' : <?=json_encode(array_keys($member_join_week))?>,
			'datas' : <?=json_encode(array_values($member_join_week))?>,
			'avg' : <?=json_encode(array_values($member_join_week_avg))?>,
			'headText' : '<?=$member_join_week_headText?>',
			'avg_text' : '지난 4주 평균',
		},
		'month' : {
			'labels' : <?=json_encode(array_keys($member_join_month))?>,
			'datas' : <?=json_encode(array_values($member_join_month))?>,
			'avg' : <?=json_encode(array_values($member_join_month_avg))?>,
			'headText' : '<?=$member_join_month_headText?>',
			'avg_text' : '지난 3개월 평균',
		},
	};

	function memberJoinStatus(e, v) {
		$('#main .box.join h2 span.box_btn_group .active').removeClass('active');
		$(e).parent().addClass('active');

		if (v == 'day') {
			main_join_graph.data.labels = array_chart_datas_member_join['day']['labels'];
			main_join_graph.data.datasets[0].data = array_chart_datas_member_join['day']['avg'];
			main_join_graph.data.datasets[0].label = array_chart_datas_member_join['day']['avg_text'];
			main_join_graph.data.datasets[1].data = array_chart_datas_member_join['day']['datas'];
			main_join_graph.options.scales.x.ticks.callback = function(v, i, all) {
				let label_split = this.getLabelForValue(v).split(' ');
				if (all.length - 1 == i) {
					return `오늘`;
				}
				if (i % 3 == 0) {
					return label_split[2];
				}
			};

			$('#main .box.join h2 .joinMainText .mainTextDate').html(array_chart_datas_member_join['day']['headText']);
		}
		if (v == 'week') {
			main_join_graph.data.labels = array_chart_datas_member_join['week']['labels'];
			main_join_graph.data.datasets[0].data = array_chart_datas_member_join['week']['avg'];
			main_join_graph.data.datasets[0].label = array_chart_datas_member_join['week']['avg_text'];
			main_join_graph.data.datasets[1].data = array_chart_datas_member_join['week']['datas'];
			main_join_graph.options.scales.x.ticks.callback = function(v, i, all) {
				let label_split = this.getLabelForValue(v).split(' ');
				let regex = /[^0-9]/g;
				if(all.length - 1 == i) {
					return `이번주`;
				}
				if(i % 3 == 0) {
					return `${label_split[1].replace(regex, '')}/${label_split[2].replace(regex, '')}`;
				}
			};

			$('#main .box.join h2 .joinMainText .mainTextDate').html(array_chart_datas_member_join['week']['headText']);
		}
		if (v == 'month') {
			main_join_graph.data.labels = array_chart_datas_member_join['month']['labels'];
			main_join_graph.data.datasets[0].data = array_chart_datas_member_join['month']['avg'];
			main_join_graph.data.datasets[0].label = array_chart_datas_member_join['month']['avg_text'];
			main_join_graph.data.datasets[1].data = array_chart_datas_member_join['month']['datas'];
			main_join_graph.options.scales.x.ticks.callback = function(v, i, all) {
				let label_split = this.getLabelForValue(v).split(' ');
				if(all.length - 1 == i) {
					return `이번달`;
				}
				if(i % 1 == 0) {
					return `${label_split[1]}`;
				}
			};

			$('#main .box.join h2 .joinMainText .mainTextDate').html(array_chart_datas_member_join['month']['headText']);
		}
		main_join_graph.clear();
		main_join_graph.update();
	}

	// 회원가입추이
	let main_join_graph = chartMake('chartJoin', {
		type: 'bar',
		data: {
			labels: array_chart_datas_member_join['day']['labels'],
			datasets: [
				{
					type: 'line',
					label: array_chart_datas_member_join['day']['avg_text'],
					data: array_chart_datas_member_join['day']['avg'],
					backgroundColor: '#F9CD30',
					borderColor: '#F9CD30',
					sort: 2,
				},
				{
					label: '회원가입',
					data: array_chart_datas_member_join['day']['datas'],
					backgroundColor: '#7FCFF3',
					borderColor: '#7FCFF3',
					borderRadius: 30,
					maxBarThickness: 8,
					sort: 1,
				},
			],
		},
		options: {
			responsive: true,
			maintainAspectRatio: false,
			interaction: {
				intersect: false,
				mode: 'index',
			},
			plugins: {
				legend: {
					display: false,
				},
				title: {
					display: false,
				},
				tooltip: {
					enabled: false,
					external: externaltooltip,
        			position: 'custom',
					itemSort: function(a, b) {
						return a.dataset.sort - b.dataset.sort;
					},
					callbacks: {
						title: function(tooltipItems) {
							if(tooltipItems[0].label == '') {
								return '';
							} else {
								return tooltipItems[0].label;
							}
						},
						label: function(tooltipItems) {
							return [tooltipItems.dataset.label, tooltipItems.formattedValue];
						},
					},
				},
			},
			scales: {
				x: {
					display: true,
					grid: {
						display: false,
					},
					title: {
						display: false,
					},
					ticks: {
						font: {
							size: 11.5,
						},
						color: '#999999',
						callback: function(v, i, all) {
							let label_split = this.getLabelForValue(v).split(' ');
							if (all.length - 1 == i) {
								return `오늘`;
							}
							if (i % 3 == 0) {
								return label_split[2];
							}
						},
					},
				},
				y: {
					display: true,
					min : 0,
					ticks: {
						color: '#999999',
					},
				},
			},
		},
	});
</script>
<?php

	/* +----------------------------------------------------------------------------------------------+
	' |  팝업 출력
	' +----------------------------------------------------------------------------------------------+*/
	$wpopup = $cxml->wpopup[0]->article[0];
	$popidx = $wpopup->idx[0];
	$popcontent = php2java($wpopup->content[0]);

	if(!$popidx) return;
?>

<script type="text/javascript">
if(getCookie('wm_popup_<?=$popidx?>') != "Y") {
	var wm_back = document.createElement("DIV");
	wm_back.id = 'wm_back';
	wm_back.style.position = 'absolute';
	wm_back.style.left = 0;
	wm_back.style.top = 0;
	wm_back.style.width = document.documentElement.scrollWidth+'px';
	wm_back.style.height = document.documentElement.scrollHeight+'px';
	wm_back.style.backgroundColor = '#000';
	wm_back.style.zIndex = 99;
	wm_back.style.filter = 'alpha(opacity=50)';
	wm_back.style.opacity = '.5';
	document.body.insertBefore(wm_back, document.body.firstChild);

	var wm_popup = document.createElement("DIV")
	wm_popup.id = "wm_popup";
	wm_popup.style.position = "absolute";
	wm_popup.style.backgroundColor = "#fff";
	wm_popup.style.border = "solid 4px #666";
	wm_popup.style.textAlign = "right";
	wm_popup.style.zIndex = 100;
	wm_popup.innerHTML = "<div style='text-align: justify'><?=$popcontent?></div>\n<div style='padding:3px'><a href=\"javascript:closeWPopup('wm_popup',<?=$popidx?>);\">3일 동안 열지 않기</a> | <b><a href=\"javascript:closeWPopup('wm_popup');\">창닫기</a></b></div>";

	document.body.insertBefore(wm_popup, document.body.firstChild);
	wm_popup.style.left = ((document.documentElement.scrollWidth / 2) - (wm_popup.offsetWidth / 2))+'px';
	wm_popup.style.top = ((document.documentElement.scrollHeight / 2) - (wm_popup.offsetHeight / 2))+'px';

	wpopupReize = function() {
		var wm_back = document.getElementById('wm_back');
		var wm_popup = document.getElementById('wm_popup');
		if(!wm_back || !wm_popup) return;
		var w = document.body.scrollWidth
		var h = screen.availHeight;

		wm_back.style.width = document.documentElement.scrollWidth+'px';
		wm_back.style.height = document.documentElement.scrollHeight+'px';

		wm_popup.style.left = ((w / 2) - (wm_popup.offsetWidth / 2))+'px';
		wm_popup.style.top = ((h / 2) - (wm_popup.offsetHeight / 2))+'px';
	}

	addEvent(window, 'resize', wpopupReize);
	addEvent(window, 'load', wpopupReize);
}
</script>
