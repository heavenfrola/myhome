<?php

	/* +----------------------------------------------------------------------------------------------+
	' |  방문자분석 - 기간별 데이터 불러오기
	' +----------------------------------------------------------------------------------------------+*/

	header('Content-type: application/json');

	$result = array('result' => false, 'msg' => '데이터 로딩 실패'); // 결과값 변수
	$resData = array(); // 결과값에 보낼 데이터 변수
	$resData['data'] = array();

	// 방문자분석 log@count_log.php 페이지
	if (isset($_POST['mode']) == true && $_POST['mode'] == 'count_log') {
		$viewType = addslashes($_POST['viewType']); // 시간/일/주/월 탭
		$start_date = addslashes($_POST['start_date']); // 시작일
		$finish_date = addslashes($_POST['finish_date']); // 종료일
		$averageDay = addslashes($_POST['averageDay']); // 지난 N일/주/월 평균 기간

		if ($start_date == '') $start_date = date('Y-m-d');
		if ($finish_date == '') $finish_date = date('Y-m-d');

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

		$exp = explode('-', $start_date);
		$sY = $exp[0];
		$sM = number_format($exp[1]);
		$sD = number_format($exp[2]);

		$exp = explode('-', $finish_date);
		$fY = $exp[0];
		$fM = number_format($exp[1]);
		$fD = number_format($exp[2]);

		// 값이 없거나 첫 화면은 '오늘'로 기본 세팅
		if ($viewType == '') $viewType = 'dayView';
		if ($start_date == '') $start_date = date('Y-m-d');
		if ($finish_date == '') $finish_date = date('Y-m-d');

		setcookie('log_view', $viewType, time()+3600, '/'); // 1시간 유지

		// 시간별 보기 데이터 세팅
		function dataSetTimeView($where) {
			global $pdo, $tbl, $resData;

			$sql = "select * from {$tbl['log_day']} ".$where;
			$res = $pdo->iterator($sql);
			foreach ($res as $data) {
				for ($i = 0; $i < 24; $i++) {
					array_push($resData['data'], array($i, $data['h'.$i], $data['dd'], $data['mm']));
				}
			}
			return $resData['data'];
		}

		// 데이터 초기화 & 세팅
		function dataSetInit($viewType, $y, $m, $sd, $ed, $where, $groupBy) {
			global $pdo, $tbl, $resData, $initDataSet;

			$sql = "select yy, mm, dd, week, sum(hit) as hit from {$tbl['log_day']} $where $groupBy order by null";
			$res = $pdo->iterator($sql);
			if ($viewType == 'dayView' || $viewType == 'weekView') {
				// 데이터 초기화
				$week = date('w', strtotime($y.'-'.$m.'-'.$sd));
				for ($init_i = $sd; $init_i <= $ed; $init_i++) {
					array_push($initDataSet, array($init_i, 0, $m, $week, $y));
					if ($week == '6') $week = '0';
					else $week++;
				}

				// 실제 데이터 세팅
				foreach ($res as $data) {
					array_push($resData['data'], array($data['dd'], $data['hit'], $data['mm'], $data['week'], $data['yy']));
				}
			} elseif ($viewType == 'monthView') {
				// 데이터 초기화
				array_push($initDataSet, array($m, 0, $m, 0, $y));

				// 실제 데이터 세팅
				foreach ($res as $data) {
					array_push($resData['data'], array($data['mm'], $data['hit'], $data['mm'], 0, $data['yy']));
				}
			}
		}

		// 데이터 합
		function dataCombine() {
			global $resData, $initDataSet;

			$chkKey = 0;
			for ($i = 0; $i < count($initDataSet); $i++) {
				if ($resData['data'][$chkKey]) {
					if ($initDataSet[$i][0] == $resData['data'][$chkKey][0] && $initDataSet[$i][2] == $resData['data'][$chkKey][2] && $initDataSet[$i][3] == $resData['data'][$chkKey][3] && $initDataSet[$i][4] == $resData['data'][$chkKey][4]) {
						$initDataSet[$i][1] = $resData['data'][$chkKey][1];
						$chkKey++;
					}
				} else {
					break;
				}
			}
			return $initDataSet;
		}

		// 평균 구하기
		function dataSetAverage($viewType, $averageDay) {
			global $pdo, $tbl, $initDataSet;

			$dataArray = array();
			if ($viewType == 'monthView') {
				for ($i = 0; $i < count($initDataSet); $i++) {
					$averageHit = 0;
					for ($prevDay = 0; $prevDay < $averageDay; $prevDay++) {
						$lastDay = date('Y-m-d', strtotime($initDataSet[$i][4].'-'.$initDataSet[$i][2].'-01'." -".($prevDay+1)."months"));
						$exLastDay = explode('-',$lastDay);
						$sql = "select sum(hit) as hit from {$tbl['log_day']} where yy='$exLastDay[0]' && mm='$exLastDay[1]' group by mm order by null";
						$res = $pdo->assoc($sql);
						$averageHit = $averageHit + $res['hit'];
					}
					array_push($dataArray, array($initDataSet[$i][0], round($averageHit / $averageDay), $initDataSet[$i][2], $initDataSet[$i][3], $initDataSet[$i][4]));
				}
			} else { // dayView, weekView
				$forDay = $averageDay;
				if ($viewType == 'weekView') $forDay = $averageDay * 7;
				for ($i = 0; $i < count($initDataSet); $i++) {
					$averageHit = 0;
					for ($prevDay = 0; $prevDay < $forDay; $prevDay++) {
						$lastDay = date('Y-m-d', strtotime($initDataSet[$i][4].'-'.$initDataSet[$i][2].'-'.$initDataSet[$i][0].' -'.($prevDay+1).'days'));
						$exLastDay = explode('-',$lastDay);
						$sql = "select hit from {$tbl['log_day']} where yy='$exLastDay[0]' && mm='$exLastDay[1]' && dd='$exLastDay[2]'";
						$res = $pdo->assoc($sql);
						$averageHit = $averageHit + $res['hit'];
					}
					array_push($dataArray, array($initDataSet[$i][0], round($averageHit / $averageDay), $initDataSet[$i][2], $initDataSet[$i][3], $initDataSet[$i][4]));
				}
			}
			return $dataArray;
		}

		$where = "where 1";
		$where .= ($sY == $fY) ? " && yy = '$sY'" : " && (yy >= '$sY' && yy <= '$fY')";
		$where .= ($sM == $fM) ? " && mm = '$sM'" : " && (mm >= '$sM' && mm <= '$fM')";
		$where .= ($sD == $fD) ? " && dd = '$sD'" : " && (dd >= '$sD' && dd <= '$fD')";

		$groupBy = "";
		if ($viewType == 'dayView') $groupBy = " group by dd"; // 일
		if ($viewType == 'weekView') $groupBy = " group by dd"; // 주
		if ($viewType == 'monthView') $groupBy = " group by mm"; // 월

		$initDataSet = array(); // 초기 데이터
		$averageData = array(); // N일 평균 구하기
		$lastDayWeek = array(); // 연도별 월별의 마지막 일자, 요일 구하기

		// 연 또는 월이 다르면
		if (($sY != $fY) || ($sM != $fM)) {
			// 연도가 다르면
			if ($sY != $fY) {
				$for_m = $sM;
				$startDD = $sD;
				$endDD = $fD;
				for ($y = $sY; $y <= $fY; $y++) {
					$for_m = 1;
					$for_m_end = 12;
					$startDD = 1;
					if ($y == $sY) {
						$for_m = $sM;
						$startDD = $sD;
					}
					if ($y == $fY) $for_m_end = $fM;

					// 연별 월별 마지막 일자 삽입
					$lastDayWeek[$y] = array();
					$lastDayWeek[$y][$for_m] = date('t', strtotime($y.'-'.$for_m));

					$where = "where yy='$y' && mm='$for_m' && (dd >= '$startDD' && dd <= '".date('t', strtotime($y.'-'.$for_m))."')";
					if ($viewType == 'timeViewNew') {
						$resData['data'] = dataSetTimeView($where);
					} else {
						$endDD = date('t', strtotime($y.'-'.$for_m));
						dataSetInit($viewType, $y, $for_m, $startDD, $endDD, $where, $groupBy);
					}

					for ($m = $for_m + 1; $m <= $for_m_end; $m++) {
						// 연별 월별 마지막 일자 삽입
						$lastDayWeek[$y][$m] = date('t', strtotime($y.'-'.$m));

						$endDD = date('t', strtotime($y.'-'.$m));
						if ($y == $fY && $m == $fM) $endDD = $fD;

						$where = "where yy='$y' && mm='$m' && (dd >= '1' && dd <= '".$endDD."')";
						if ($viewType == 'timeViewNew') {
							$resData['data'] = dataSetTimeView($where);
						} else {
							dataSetInit($viewType, $y, $m, 1, $endDD, $where, $groupBy);
						}
					}
				}

				if ($viewType != 'timeViewNew') {
					// 데이터 결합
					$initDataSet = dataCombine();

					// 평균 구하기
					$averageData = dataSetAverage($viewType, $averageDay);

					$resData['data'] = array();
					$resData['data'] = $initDataSet;
				}
			}

			if ($sY == $fY && $sM != $fM) {
				// 첫 월
				$lastDayWeek[$sY] = array();
				$lastDayWeek[$sY][$sM] = date('t', strtotime($sY.'-'.$sM)); // 연별 월별 마지막 일자 삽입
				$where = "where yy='$sY' && mm='$sM' && (dd >= '$sD' && dd <= '".date('t', strtotime($sY.'-'.$sM))."')";
				if ($viewType == 'timeViewNew') {
					$resData['data'] = dataSetTimeView($where);
				} else {
					$theLastDay = date('t', strtotime($sY.'-'.$sM));
					dataSetInit($viewType, $sY, $sM, $sD, $theLastDay, $where, $groupBy);
				}

				// 중간 월
				if (abs($sM - $fM) - 1 > '0') {
					for ($i = 1; $i < abs($sM - $fM); $i++) {
						$for_m = $sM + $i;
						$lastDayWeek[$sY][$for_m] = date('t', strtotime($sY.'-'.$for_m)); // 연별 월별 마지막 일자, 요일 삽입
						$where = "where yy='$sY' && mm='$for_m'";
						if ($viewType == 'timeViewNew') {
							$resData['data'] = dataSetTimeView($where);
						} else {
							$theLastDay = date('t', strtotime($sY.'-'.$for_m));
							dataSetInit($viewType, $sY, $for_m, 1, $theLastDay, $where, $groupBy);
						}
					}
				}

				// 마지막 월
				$lastDayWeek[$sY][$fM] = date('t', strtotime($sY.'-'.$fM)); // 연별 월별 마지막 일자, 요일 삽입
				$where = "where yy='$sY' && mm='$fM' && (dd >= '1' && dd <= '$fD')";
				if ($viewType == 'timeViewNew') {
					$resData['data'] = dataSetTimeView($where);
				} else {
					dataSetInit($viewType, $sY, $fM, 1, $fD, $where, $groupBy);
				}

				if ($viewType != 'timeViewNew') {
					//데이터 결합
					$initDataSet = dataCombine();

					//평균 구하기
					$averageData = dataSetAverage($viewType, $averageDay);

					$resData['data'] = array();
					$resData['data'] = $initDataSet;
				}
			}
		} else {
			$lastDayWeek[$sY][$sM] = $fD;

			if ($viewType == 'timeViewNew') {
				$resData['data'] = dataSetTimeView($where);
			} else {
				dataSetInit($viewType, $sY, $sM, $sD, $fD, $where, $groupBy);

				//데이터 결합
				$initDataSet = dataCombine();

				//평균 구하기
				$averageData = dataSetAverage($viewType, $averageDay);

				$resData['data'] = array();
				$resData['data'] = $initDataSet;
			}
		}

		$startYear = $sY;
		$startMonth = sprintf('%02d',$sM);
		$startDay = sprintf('%02d',$sD);

		$finishYear = $fY;
		$finishMonth = sprintf('%02d',$fM);
		$finishDay = sprintf('%02d',$fD);

		// 데이터 세팅
		$resData['viewType'] = $viewType;
		$resData['startWeekend'] = date('w', strtotime($start_date));
		$resData['lastDayWeek'] = $lastDayWeek;

		$resData['startSearch'] = $startYear;
		if ($startMonth) $resData['startSearch'] .= '-'.$startMonth;
		if ($startDay) $resData['startSearch'] .= '-'.$startDay;

		$resData['finishSearch'] = $finishYear;
		if ($finishMonth) $resData['finishSearch'] .= '-'.$finishMonth;
		if ($finishDay) $resData['finishSearch'] .= '-'.$finishDay;

		$resData['averageData'] = $averageData;
		$resData['averageDay'] = $averageDay;

		// 결과 세팅
		$result['result'] = true;
		$result['msg'] = '성공';
		$result['type'] = 1;
		if ($overDateString) {
			$result['type'] = 2;
			$result['typeAlert'] = $overDateString;
		}
		$result['data'] = $resData;
		ksort($result['data']['data']); // 데이터 정렬
	}

	echo json_encode($result);