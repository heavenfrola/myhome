<?php

	/* +----------------------------------------------------------------------------------------------+
	' |  상품검색어 데이터 불러오기
	' +----------------------------------------------------------------------------------------------+*/

	header('Content-type: application/json');

	$result = array('result' => false, 'msg' => '데이터 로딩 실패'); // 결과값 변수
	$resData = array(); // 결과값에 보낼 데이터 변수

	// 상품검색어 리스트
	if (isset($_POST['mode']) == true && $_POST['mode'] == 'keywordList') {
		$today = date('Y-m-d');
		$start_date = addslashes($_POST['start_date']); // 시작일
		$finish_date = addslashes($_POST['finish_date']); // 종료일
		$page = numberOnly(addslashes($_POST['page']));

		if (!$start_date) $start_date = date('Y-m-d', strtotime('-6 days', strtotime($today)));
		if (!$finish_date) $finish_date = $today;

		// 검색 기간 최대 1년 체크
		$firstDate = new DateTime($start_date);
		$secondDate = new DateTime($finish_date);
		$intvl = $firstDate->diff($secondDate);
		$diffDay = $intvl->days;

		$overDateString = '';
		if ($diffDay > 365) {
			$start_date = date('Y-m-d');
			$finish_date = date('Y-m-d');
			$overDateString = '최대 검색 기간은 1년입니다.';
		}

		$where = "where 1";
		if ($start_date) $where .= " && date >= '".strtotime($start_date.' 00:00:00')."'";
		if ($finish_date) $where .= " && date <= '".strtotime($finish_date.' 23:59:59')."'";

		$sql = "select *, sum(hit) as sHit from {$tbl['log_search_day']} $where group by keyword order by sHit desc, no asc";
		$totalSql = "select count(*) from (select * from {$tbl['log_search_day']} $where group by keyword order by null) as a";

		if ($page <= 1) $page = 1;
		$row = 10;
		$block = 10;
		include_once $engine_dir.'/_engine/include/paging.php';

		$NumTotalRec = $pdo->row($totalSql);
		$PagingInstance = new Paging($NumTotalRec, $page, $row, $block, '', 'keywordListSet');
		$PagingResult = $PagingInstance->result('ajax_admin2');
		$pg_res = $PagingResult['PageLink'];

		$sql .= $PagingResult['LimitQuery'];
		$res = $pdo->iterator($sql);

		$r = array();
		$rowCheck = 0;
		foreach ($res as $data) {
			if ($data['sHit']) $data['hit'] = $data['sHit'];
			array_push($r, array((($page*$block) - ($block - $rowCheck)) + 1, $data['keyword'], number_format($data['hit'])));
			$rowCheck++;
		}

		$resData['data'] = $r;
		$resData['startSearch'] = $start_date;
		$resData['finishSearch'] = $finish_date;

		$result['result'] = true;
		$result['msg'] = '성공';
		$result['type'] = 1;
		if ($overDateString) {
			$result['type'] = 2;
			$result['typeAlert'] = $overDateString;
		}
		$result['data'] = $resData;
		$result['pg_res'] = $pg_res;
	}

	// 그래프 데이터
	if (isset($_POST['mode']) == true && $_POST['mode'] == 'keyword_log') {
		$start_date = addslashes($_POST['start_date']); // 시작일
		$finish_date = addslashes($_POST['finish_date']); // 종료일
		$viewType = addslashes($_POST['viewType']); // 시간/일/주/월 탭
		$keyword = $_POST['keyword']; // array

		if (!$viewType) $viewType = 'dayView';

		$exp = explode('-',$start_date);
		$sY = $exp[0];
		$sM = number_format($exp[1]);
		$sD = number_format($exp[2]);

		$exp = explode('-',$finish_date);
		$fY = $exp[0];
		$fM = number_format($exp[1]);
		$fD = number_format($exp[2]);

		setcookie('keywordlog_view', $viewType, time()+3600, '/'); // 1시간 유지

		$weekendText = array('일요일', '월요일', '화요일', '수요일', '목요일', '금요일', '토요일');

		$resData['labels'] = array();
		$resData['data'] = array();

		//데이터 초기화
		$initDataSet = array();
		$week = date('w', strtotime($sY.'-'.$sM.'-'.$sD));
		$resData['startWeekend'] = $week;
		for ($init_y = $sY; $init_y <= $fY; $init_y++) {
			$init_m_start = ($init_y == $sY) ? $sM : 1;
			$init_m_end = ($init_y == $fY) ? $fM : 12;
			for ($init_m = $init_m_start; $init_m <= $init_m_end; $init_m++) {
				$init_m = sprintf('%02d',$init_m);
				$init_d_start = ($init_m == $sM && $init_y == $sY) ? $sD : 1;
				$init_d_end = ($init_m == $fM && $init_y == $fY) ? $fD : date('t', strtotime($init_y.'-'.$init_m));
				for ($init_d = $init_d_start; $init_d <= $init_d_end; $init_d++) {
					$init_d = sprintf('%02d',$init_d);
					$dateset = $init_y.'-'.$init_m.'-'.$init_d;
					array_push($initDataSet, array($init_d, 0, $init_m, $week, $init_y));
					if ($week == '6') $week = '0';
					else $week++;
				}
			}
		}

		$where = "where 1";
		if ($start_date) $where .= " && date >= '".strtotime($start_date.' 00:00:00')."'";
		if ($finish_date) $where .= " && date <= '".strtotime($finish_date.' 23:59:59')."'";

		// 상품검색어 검색이 존재하면 where 조건 추가
		if (!empty($keyword)) {
			$where .= " && (";
			foreach ($keyword as $v) {
				$where .= " keyword = '".$v['value']."' || ";
				$resData['data'][$v['value']] = array();
			}
			$where = substr($where,0,-3);
			$where .= ")";
		}

		$sql = "select keyword, hit, from_unixtime(date, '%Y-%m-%d') as dt from {$tbl['log_search_day']} $where group by dt, keyword order by hit desc, no asc";
		$res = $pdo->iterator($sql);

		$r = array();
		foreach ($res as $data) {
			if (array_key_exists($data['dt'], $r)) {
				$r[$data['dt']][$data['keyword']]['hit'] += $data['hit'];
				continue;
			}
			$r[$data['dt']][$data['keyword']]['hit'] = $data['hit'];
		}

		if ($viewType != 'dayView' && $viewType != 'weekView') $weekendText = array();
		$gong = '';
		if ($viewType == 'dayView' || $viewType == 'weekView') $gong = ' ';
		foreach ($initDataSet as $k => $v) {
			$lbs = date('Y년 m월 d일', strtotime($v[4].'-'.$v[2].'-'.$v[0]));
			$lbs .= $gong.$weekendText[date('w', strtotime($v[4].'-'.$v[2].'-'.$v[0]))];
			array_push($resData['labels'], $lbs); // 일단위 라벨 생성
			$reg_date = date('Y-m-d', strtotime($v[4].'-'.$v[2].'-'.$v[0]));
			if (!empty($keyword)) {
				foreach ($keyword as $v2) {
					if ($r[$reg_date][$v2['value']]['hit'] == '') {
						$in = 0;
					} else {
						$in = $r[$reg_date][$v2['value']]['hit'];
					}
					array_push($resData['data'][$v2['value']], $in); // 일단위 데이터 생성
				}
			}
		}

		if ($viewType == 'weekView') { // 주
			// 1. 라벨 생성
			$weekCheck = $resData['startWeekend'];
			$resData_labels_copy = $resData['labels'];
			$resData['labels'] = array();
			$labels_name = '';
			for ($i = 0; $i < count($resData_labels_copy); $i++) {
				if ($weekCheck == '1' || $i == 0) { //월요일 || 처음
					$average_arr[$resData_labels_copy[$i]] = 0;
					$labels_name = $resData_labels_copy[$i].' ~ ';
				}
				if ($weekCheck == '0' || $i == count($resData_labels_copy) - 1) { //일요일 || 마지막
					$labels_name = $labels_name.$resData_labels_copy[$i];
					array_push($resData['labels'], $labels_name);
				}

				$weekCheck++;
				if ($weekCheck == '7') $weekCheck = '0';
			}

			// 2. data 생성
			$weekCheck = $resData['startWeekend'];
			$resData_copy = $resData['data'];
			$resData['data'] = array();
			foreach ($resData_copy as $k=>$v) {
                $weekCheck = $resData['startWeekend'];
				$resData['data'][$k] = array();
				$datacount = 0;
				for ($i = 0; $i < count($resData_copy[$k]); $i++) {
					$datacount += $resData_copy[$k][$i];
					if ($weekCheck == '0') {
						array_push($resData['data'][$k], $datacount);
						$datacount = 0;
					}
					if ($i == count($resData_copy[$k]) - 1) {
						array_push($resData['data'][$k], $datacount);
					}

					$weekCheck++;
					if ($weekCheck == '7') $weekCheck = '0';
				}
			}

			unset($resData_labels_copy);
			unset($resData_copy);
		}

		if ($viewType == 'monthView') { // 주
			$resData['labels'] = array();
			$per_label = $initDataSet[0][4].$initDataSet[0][2];
			array_push($resData['labels'], $initDataSet[0][4].'년 '.$initDataSet[0][2].'월 '.$initDataSet[0][0].'일 ~ '.$initDataSet[0][4].'년 '.$initDataSet[0][2].'월 '.date('t', strtotime($initDataSet[0][4].'-'.$initDataSet[0][2])).'일'); // 라벨 삽입 (첫번째 라벨)

			// 1. 라벨 생성
			foreach ($initDataSet as $v) {
				$now_label = $v[4].$v[2];
				if ($per_label != $now_label) {
					$per_label = $v[4].$v[2];
					if ($per_label == $fY.sprintf('%02d',$fM)) {
						array_push($resData['labels'], $fY.'년 '.sprintf('%02d',$fM).'월 01일 ~ '.$fY.'년 '.sprintf('%02d',$fM).'월 '.sprintf('%02d',$fD).'일'); // 라벨 삽입 (마지막 라벨)
						break;
					}
					array_push($resData['labels'], $v[4].'년 '.$v[2].'월 '.$v[0].'일 ~ '.$v[4].'년 '.$v[2].'월 '.date('t', strtotime($v[4].'-'.$v[2])).'일');
				}
			}

			// 2. data 생성
			$tmpResData = $resData['data'];
			$resData['data'] = array();
			foreach ($tmpResData as $k => $v) {
				$i = 0;
				$dataValue = 0;

				$resData['data'][$k] = array();
				$per_label = $initDataSet[0][4].$initDataSet[0][2];
				foreach ($initDataSet as $v2) {
					$now_label = $v2[4].$v2[2];
					if ($per_label != $now_label) {
						array_push($resData['data'][$k], $dataValue);
						$per_label = $v2[4].$v2[2];
						$dataValue = 0;
					}

					$dataValue += $v[$i];
					if ($i == count($initDataSet) - 1) {
						array_push($resData['data'][$k], $dataValue);
					}
					$i++;
				}
			}
			unset($tmpResData);
		}

		$resData['startSearch'] = $start_date;
		$resData['finishSearch'] = $finish_date;
		$resData['viewType'] = $viewType;

		$result['result'] = true;
		$result['msg'] = '성공';
		$result['data'] = $resData;
	}

	echo json_encode($result);