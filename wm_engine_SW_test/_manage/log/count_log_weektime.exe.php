<?php

	/* +----------------------------------------------------------------------------------------------+
	' |  방문자분석 - 요일별, 시간별 데이터 불러오기
	' +----------------------------------------------------------------------------------------------+*/

	header('Content-type: application/json');

	$result = array('result' => false, 'msg' => '데이터 로딩 실패'); // 결과값 변수
	$resData = array(); // 결과값에 보낼 데이터 변수

	// 방문자분석 log@count_log.php 페이지 -> 요일별, 시간별
	if (isset($_POST['mode']) == true && $_POST['mode'] == 'count_log_weektime') {
		$viewType = addslashes($_POST['viewType']); // 시간/일/주/월 탭
		$start_date = addslashes($_POST['start_date']); // 시작일
		$finish_date = addslashes($_POST['finish_date']); // 종료일

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

		// 요일, 시간 데이터 초기화
		$resData['data'] = array('week' => array(), 'time' => array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0));

		// 일자 배열
		$standardData = array();

		// 요일별, 시간별, 표준데이터 세팅
		function dataInitSet($where, $y, $m, $startday, $lastday) {
			global $pdo, $tbl, $resData, $standardData, $sWeek;

			$sql = "select * from {$tbl['log_day']} $where";
			$res = $pdo->iterator($sql);

			// 요일별 세팅
			foreach ($res as $data) {
				array_push($resData['data']['week'], array($data['yy'], $data['mm'], $data['dd'], $data['hit'], $data['week']));
			}

			// 시간별 세팅
			foreach ($res as $data) {
				for ($i = 0; $i < 24; $i++) {
					$fld = 'h'.$i;
					$resData['data']['time'][$i] = $resData['data']['time'][$i] + $data[$fld];
				}
			}

			// 표준데이터에 값 넣기
			for ($d = $startday; $d <= $lastday; $d++) {
				array_push($standardData, array($y, $m, $d, 0, $sWeek));
				$sWeek++;
				if ($sWeek == 7) $sWeek = 0;
			}
		}

		// 평균값 구하기. 요일별, 시간별
		function calcAverage($diff) {
			global $standardData, $resData;

			$tmp = array(
				'count' => array(0 => 0, 1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0),
				'sum' => array(0 => 0, 1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0)
			);

			$i = 0;
			// 표준데이터에 넣은 값을 sum and count 넣기
			foreach ($standardData as $k) {
				if (($k[0] == $resData['data']['week'][$i][0]) && ($k[1] == $resData['data']['week'][$i][1]) && ($k[2] == $resData['data']['week'][$i][2])) {
					$k[3] = $resData['data']['week'][$i][3];
					$tmp['sum'][$k[4]] = $tmp['sum'][$k[4]] + $resData['data']['week'][$i][3];
					$i++;
				}
				$tmp['count'][$k[4]] = $tmp['count'][$k[4]] + 1;
			}

			$resData['data']['week'] = array(); // 초기화

			// 평균값 구하기 -> 요일별
			for ($i = 0; $i < 7; $i++) {
				if ($tmp['sum'][$i] > 0) {
					array_push($resData['data']['week'], round($tmp['sum'][$i] / $tmp['count'][$i]));
				} else {
					array_push($resData['data']['week'], 0);
				}
			}
			// 평균값 구하기 -> 시간별
			for ($i = 0; $i < 24; $i++) {
				if ($resData['data']['time'][$i] > 0) {
					$resData['data']['time'][$i] = round($resData['data']['time'][$i] / $diff);
				}
			}

			unset($tmp);
		}

		// 연 || 월 다른 경우
		if (($sY != $fY) || ($sM != $fM)) {
			$sWeek = date('w', strtotime($sY.'-'.$sM.'-'.$sD));
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

					// 월의 마지막 일
					$endDD = date('t', strtotime($y.'-'.$for_m));
					if ($y == $fY && $m == $for_m_end ) $endDD = $fD;

					$where = "where yy='$y' && mm='$for_m' && (dd >= '$startDD' && dd <= '$endDD')";
					dataInitSet($where, $y, $for_m, $startDD, $endDD);

					for ($m = $for_m + 1; $m <= $for_m_end; $m++) {
						// 월의 마지막 일
						$endDD = date('t', strtotime($y.'-'.$m));
						if ($y == $fY && $m == $for_m_end ) $endDD = $fD;

						$where = "where yy='$y' && mm='$m' && (dd >= '1' && dd <= '$endDD')";
						dataInitSet($where, $y, $m, 1, $endDD);
					}
				}
				calcAverage($diffDay);
			}

			// 연은 같고 월만 다른 경우
			if ($sY == $fY && $sM != $fM) {
				// 첫 월
				$endDD = date('t', strtotime($sY.'-'.$sM));
				$where = "where yy='$sY' && mm='$sM' && (dd >= '$sD' && dd <= '$endDD')";
				dataInitSet($where, $sY, $sM, $sD, $endDD);

				// 중간 월
				if (abs($sM - $fM) - 1 > '0') {
					for ($i = 1; $i < abs($sM - $fM); $i++) {
						$for_m = $sM + $i;
						$endDD = date('t', strtotime($sY.'-'.$for_m));
						$lastDayWeek[$sY][$for_m] = $endDD; // 월의 마지막 일 삽입
						$where = "where yy='$sY' && mm='$for_m'";
						dataInitSet($where, $sY, $for_m, 1, $endDD);
					}
				}

				// 마지막 월
				$where = "where yy='$sY' && mm='$fM' && (dd >= '1' && dd <= '$fD')";
				dataInitSet($where, $sY, $fM, 1, $fD);

				calcAverage($diffDay);
			}
		} else { // 연, 월 모두 같은 경우
			$sWeek = date('w', strtotime($sY.'-'.$sM.'-'.$sD));
			$where = "where yy='$sY' && mm='$sM' && (dd >= '$sD' && dd <= '$fD')";
			dataInitSet($where, $sY, $sM, $sD, $fD);
			calcAverage($diffDay);
		}

		$startYear = $sY;
		$startMonth = sprintf('%02d',$sM);
		$startDay = sprintf('%02d',$sD);

		$finishYear = $fY;
		$finishMonth = sprintf('%02d',$fM);
		$finishDay = sprintf('%02d',$fD);

		// 데이터 세팅
		$resData['searchType'] = 'week';
		$resData['viewType'] = $viewType;
		$resData['startWeekend'] = ($viewType == 'weekView') ? date('w', strtotime($start_date)) : '';
		$resData['lastDayWeek'] = $lastDayWeek;

		$resData['startSearch'] = $startYear;
		if ($startMonth) $resData['startSearch'] .= '-'.$startMonth;
		if ($startDay) $resData['startSearch'] .= '-'.$startDay;

		$resData['finishSearch'] = $finishYear;
		if ($finishMonth) $resData['finishSearch'] .= '-'.$finishMonth;
		if ($finishDay) $resData['finishSearch'] .= '-'.$finishDay;

		// 결과 세팅
		$result['result'] = true;
		$result['msg'] = '성공';
		$result['data'] = $resData;
		$result['type'] = 1;
		if ($overDateString) {
			$result['type'] = 2;
			$result['typeAlert'] = $overDateString;
		}
		ksort($result['data']['data']); // 데이터 정렬
	}

	echo json_encode($result);