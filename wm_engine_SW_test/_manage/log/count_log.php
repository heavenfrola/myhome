<?php

	/* +----------------------------------------------------------------------------------------------+
	' |  방문자수통계 (일/월/연별)
	' +----------------------------------------------------------------------------------------------+*/

	// 오늘 날자
	$today = date('Y-m-d');

	// 검색프리셋
	$spno = numberOnly(addslashes($_GET['spno']));
	unset($_GET['spno']);
	if ($spno) {
		$spdata = $pdo->assoc("select * from {$tbl['search_preset']} where no='$spno'");
		if ($spdata['querystring']) {
			$_GET = array_merge($_GET, json_decode($spdata['querystring'], true));
			if ($_GET['setterm'] == 'term') {
				$firstDate = new DateTime($_GET['start_date']);
				$secondDate = new DateTime($_GET['finish_date']);
				$intvl = $firstDate->diff($secondDate);
				$diffDay = $intvl->days;

				$_GET['start_date'] = date('Y-m-d', strtotime($today.' -'.$diffDay.'days'));
				$_GET['finish_date'] = $today;
			}
		}
	}

	// 검색 데이터
	$viewType = addslashes($_GET['viewType']); // 시간/일/주/월 탭
	$start_date = addslashes($_GET['start_date']); // 시작일
	$finish_date = addslashes($_GET['finish_date']); // 종료일

	// 오늘 날짜
	$explodeDate = explode('-', $today);
	$todayY = $explodeDate[0];
	$todayM = $explodeDate[1];
	$todayD = $explodeDate[2];

	// 어제 날짜
	$yesterDay = date('Y-m-d', strtotime($today.' -1days'));

	// 최근 7일 시작일
	$thisWeekStartDay = date('Y-m-d', strtotime($today.' -6days'));
	$thisWeekEndDay = $today;

	// 최근 30일
	$last30StartDay = date('Y-m-d', strtotime($today.' -29days'));
	$last30EndDay = $today;

	// 최근 2개월
	$last2mStartDay = date('Y-m-d', strtotime($today.' -2month'));
	$last2mEndDay = $today;

	// 최근 3개월
	$last3MonthStartDay = date('Y-m-d', strtotime($today.' -3month'));
	$last3MonthEndDay = $today;

	// 이번달 (1일 ~ 현재)
	$thisMonthStartDay = $todayY.'-'.$todayM.'-01';
	$thisMonthEndDay = $today;

	// 지난달
	$prev_month = strtotime('-1 month', strtotime($todayY.'-'.$todayM.'-01')); // 이번달 01일부터 계산
	$lastMonthStartDay = date('Y-m-01', $prev_month);
	$lastMonthEndDay = date('Y-m-t', $prev_month);

	// viewType 저장
	if ($start_date == '' && $finish_date == '' && !$spno) {
		$cookie_view = 'dayView';
	} else {
		if ($spno) {
			$cookie_view = $_GET['viewType'];
		} else {
			$cookie_view = $_COOKIE['log_view'];
		}
	}

	// 없거나 첫 화면은 '오늘'로 기본 세팅
	if ($viewType == '') $viewType = 'dayView';
	if ($start_date == '') $start_date = $thisWeekStartDay;
	if ($finish_date == '') $finish_date = $today;

	// 검색 데이터 explode
	$ex_start_date = explode('-', $start_date);
	$start_dateY = $ex_start_date[0];
	$start_dateM = $ex_start_date[1];
	$start_dateD = $ex_start_date[2];

	$ex_finish_date = explode('-', $finish_date);
	$finish_dateY = $ex_finish_date[0];
	$finish_dateM = $ex_finish_date[1];
	$finish_dateD = $ex_finish_date[2];

	// 간편검색 array
	$easySearchBtn = array(
		'오늘' => array(1, $today, $today),
		'어제' => array(2, $yesterDay, $yesterDay),
		'7일' => array(3, $thisWeekStartDay, $thisWeekEndDay),
		'30일' => array(8, $last30StartDay, $last30EndDay),
		'2개월' => array(9, $last2mStartDay, $last2mEndDay),
		'3개월' => array(7, $last3MonthStartDay, $last3MonthEndDay),
		'이번달 (1일~현재)' => array(6, $thisMonthStartDay, $thisMonthEndDay),
		'지난달 (1일~말일)' => array(5, $lastMonthStartDay, $lastMonthEndDay),
	);

	// 지난 평균 기간 array
	$average_select_day = array(7 => '지난 7일 평균', 14 => '지난 14일 평균', 30 => '지난 30일 평균',); // 일 평균 select
	$average_select_week = array(2 => '지난 2주 평균', 4 => '지난 4주 평균',); // 주 평균 select
	$average_select_month = array(3 => '지난 3개월 평균',); // 월 평균 select

	$weekday_kor = array('0' => '일', '1' => '월', '2' => '화', '3' => '수', '4' => '목', '5' => '금', '6' => '토');

	if ($body == 'log@count_log_excel.exe') {
		$title = $y.$m.$d.'부터 '.$y2.$m2.$d2.'까지';

		$excelData = array();
		$col_list = array();
		if ($excelDownView == 'monthView') { // 월
			$title .= '_월별_';

			$col_list['startday'] = '시작일';
			$col_list['endday'] = '종료일';
			$col_list['cnt'] = '방문자수';

			for ($_y = $y; $_y <= $y2; $_y++) {
				$ms = ($_y == $y) ? $m : 1;
				$me = ($_y == $y2) ? $m2 : 12;
				for ($_m = $ms; $_m <= $me; $_m++) {
					$ds = ($_y == $y && $_m == $m) ? $d : 1;
					$de = ($_y == $y2 && $_m == $m2) ? $d2 : date('t', strtotime("$_y-$_m-1"));

					$data = $pdo->assoc("select SUM(hit) AS hit from {$tbl['log_day']} where yy='$_y' && mm='$_m' && (dd >= '$ds' && dd <= '$de')");
					array_push($excelData, array('startday' => $_y.'-'.addZero($_m, 2).'-'.addZero($ds, 2), 'endday' => $_y.'-'.addZero($_m, 2).'-'.addZero($de, 2), 'cnt' => number_format($data['hit']),));
				}
			}
		} elseif ($excelDownView == 'weekView') { // 주
			$title .= '_주별_';

			$col_list['startday'] = '시작일';
			$col_list['endday'] = '종료일';
			$col_list['cnt'] = '방문자수';

			$weekSum = 0;
			$w = date('w', strtotime($y.'-'.$m.'-'.$d));
			$excelDataSet = array();
			for ($_y = $y; $_y <= $y2; $_y++) {
				$ms = ($_y == $y) ? $m : 1;
				$me = ($_y == $y2) ? $m2 : 12;
				for ($_m = $ms; $_m <= $me; $_m++) {
					$ds = ($_y == $y && $_m == $m) ? $d : 1;
					$de = ($_y == $y2 && $_m == $m2) ? $d2 : date('t', strtotime("$_y-$_m-1"));
					for ($_d = $ds; $_d <= $de; $_d++) {
						$data = $pdo->assoc("select hit, week from {$tbl['log_day']} where yy='$_y' && mm='$_m' && dd='$_d'");

						$weekSum += $data['hit'];
						if ($w == '1' || ($_y == $y && $_m == $m && $_d == $d)) { // 월요일 || 시작
							$excelDataSet['startday'] = $_y.'-'.addZero($_m, 2).'-'.addZero($_d, 2);
						}
						if ($w == '0' || ($_y == $y2 && $_m == $m2 && $_d == $d2)) { // 일요일 || 끝
							$excelDataSet['endday'] = $_y.'-'.addZero($_m, 2).'-'.addZero($_d, 2);
							$excelDataSet['cnt'] = number_format($weekSum);
							array_push($excelData, $excelDataSet);
							$weekSum = 0;
						}
						$w++;
						if ($w == '7') $w = '0';
					}
				}
			}
		} elseif ($excelDownView == 'timeViewNew') { // 시
			$title .= '_시간별_';

			$col_list['year'] = '연';
			$col_list['month'] = '월';
			$col_list['day'] = '일';
			$col_list['time'] = '시';
			$col_list['week'] = '요일';
			$col_list['cnt'] = '방문자수';

			for ($_y = $y; $_y <= $y2; $_y++) {
				$ms = ($_y == $y) ? $m : 1;
				$me = ($_y == $y2) ? $m2 : 12;
				for ($_m = $ms; $_m <= $me; $_m++) {
					$ds = ($_y == $y && $_m == $m) ? $d : 1;
					$de = ($_y == $y2 && $_m == $m2) ? $d2 : date('t', strtotime("$_y-$_m-1"));
					for ($_d = $ds; $_d <= $de; $_d++) {
						$data = $pdo->assoc("select * from {$tbl['log_day']} where yy='$_y' && mm='$_m' && dd='$_d'");
						$week = date('w', strtotime($_y.'-'.$_m.'-'.$_d));

						for ($h = 0; $h < 24; $h++) {
							array_push($excelData, array('year' => $_y, 'month' => addZero($_m, 1), 'day' => addZero($_d, 1), 'time' => $h.'시', 'week' => $weekday_kor[$week], 'cnt' => number_format($data['h'.$h]),));
						}
					}
				}
			}
		} else { // 일
			$title .= '_일별_';

			$col_list['year'] = '연';
			$col_list['month'] = '월';
			$col_list['day'] = '일';
			$col_list['week'] = '요일';
			$col_list['cnt'] = '방문자수';

			for ($_y = $y; $_y <= $y2; $_y++) {
				$ms = ($_y == $y) ? $m : 1;
				$me = ($_y == $y2) ? $m2 : 12;
				for ($_m = $ms; $_m <= $me; $_m++) {
					$ds = ($_y == $y && $_m == $m) ? $d : 1;
					$de = ($_y == $y2 && $_m == $m2) ? $d2 : date('t', strtotime("$_y-$_m-1"));
					for ($_d = $ds; $_d <= $de; $_d++) {
						$data = $pdo->assoc("select hit from {$tbl['log_day']} where yy='$_y' && mm='$_m' && dd='$_d'");
						$week = date('w', strtotime($_y.'-'.$_m.'-'.$_d));

						array_push($excelData, array('year' => $_y, 'month' => addZero($_m, 1), 'day' => addZero($_d, 1), 'week' => $weekday_kor[$week], 'cnt' => number_format($data['hit']),));
					}
				}
			}
		}
		$title = $title.' 접속통계';

		if ($body == 'log@count_log_excel.exe') return;
	}
?>
<script src="<?=$engine_url?>/_engine/common/chart/chart.js"></script>
<script src="<?=$engine_url?>/_engine/common/chart/chartjs-function.js"></script>

<style>
	.box_search .quick_search > li > a.link {
		background: #eee;
	}

	.box_search .quick_search {
		text-align: left;
		padding-top: 0;
	}

	.box_search .quick_search > li {
		margin: 3px;
	}
</style>

<div class="box_title first">방문자분석
	<div class="btns">
        <span class="box_btn_s icon excel"><input type="button" value="엑셀다운" onclick="excelDownloadPermissionCheck(); return false;"></span>
	</div>
</div>
<form name="logCountFrm" id="logCountFrm" onsubmit="return chkDateTerm();">
	<input type="hidden" name="body" value="<?=$body?>">
	<input type="hidden" name="viewType" id="viewType" value="dayView">
	<div style="text-align:left;">
		<table class="tbl_row box_search">
			<colgroup>
				<col style="width:150px">
				<col>
			</colgroup>
			<tr>
				<th scope="row">기간</th>
				<td>
					<input type="text" name="start_date" id="start_date" value="<?=$start_date?>" size="10" class="input datepicker">
					~
					<input type="text" name="finish_date" id="finish_date" value="<?=$finish_date?>" size="10" class="input datepicker">
					<?php
						foreach ($easySearchBtn as $k => $v) {
							$class_on = ($start_date == $v[1] && $finish_date == $v[2]) ? 'on' : '';
					?>
							<span class="box_btn_d <?=$class_on?>"><input type="button" value="<?=$k?>" onclick="searchDate('<?=$v[1]?>','<?=$v[2]?>');"></span>
						<?php } ?>
				</td>
			</tr>
			<tr>
				<th scope="row">단축검색</th>
				<td>
					<?php
						$preset_menu = 'log';
						$sp_count = $pdo->row("select count(*) from {$tbl['search_preset']} where menu='$preset_menu'");
						if ($admin['level'] < 4) {
					?>
							<ul class="list_info" style="display: none;">
								<li>자주 검색하는 기간은 [#단축검색등록]을 통해 편하게 검색할 수 있습니다.</li>
							</ul>
							<ul class="quick_search">
								<?php include_once $engine_dir.'/_manage/config/quicksearch.inc.php'; ?>
							</ul>
						<?php } ?>
				</td>
			</tr>
		</table>
	</div>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="검색" id="search"></span>
		<span class="box_btn"><input type="button" value="초기화" onclick="location.href='?body=<?=$_GET['body']?>'"></span>
		<span class="box_btn quicksearch" style="display: inline; float:right;"><a onclick="countLogViewQuickSearch('logCountFrm', 'log');">#단축검색등록</a></span>
	</div>
</form>

<div class="box_middle sort" style="margin-top:35px;">
	<ul class="tab_sort">
		<li class="timeViewNew"><a href="#" onclick="gridView('<?=$start_date?>', '<?=$finish_date?>', 'timeViewNew'); return false;">시간</a></li>
		<li class="dayView active"><a href="#" onclick="gridView('<?=$start_date?>', '<?=$finish_date?>', 'dayView'); return false;">일</a></li>
		<li class="weekView"><a href="#" onclick="gridView('<?=$start_date?>', '<?=$finish_date?>', 'weekView'); return false;">주</a></li>
		<li class="monthView"><a href="#" onclick="gridView('<?=$start_date?>', '<?=$finish_date?>', 'monthView'); return false;">월</a></li>
	</ul>
</div>
<div class="box_middle4">
	<div style="text-align:left;">
		<div class="viewList" id="viewList" style="padding-bottom:40px;">
			<span style="text-align:left;" id="averageDayDiv">
				비교 데이터
				<select name="averageDay" id="averageDay" onchange="ajaxReStart();">
					<option value="7">지난 7일 평균</option>
					<option value="14">지난 14일 평균</option>
					<option value="30">지난 30일 평균</option>
				</select>
			</span>
		</div>
	</div>
	<div style="position:relative;">
		<canvas id="chartPeriod" style="width:100%;height:350px;margin-bottom:50px;"></canvas>
	</div>
	<div style="position:relative;overflow:hidden;">
		<div style="width:48%;float:left;">
			<canvas id="chartWeek" style="width:100%;height:350px;">
		</div>
		<div style="width:48%;float:right;">
			<canvas id="chartTime" style="width:100%;height:350px;"></canvas>
		</div>
	</div>
</div>

<script type="text/javascript">
	$(document).ready(function () {
		averageSelectDisabled('<?=$cookie_view?>');
		chartPeriodAjax('<?=$start_date?>', '<?=$finish_date?>', '<?=$cookie_view?>');
		chartWeekTimeAjax('<?=$start_date?>', '<?=$finish_date?>');

		if ($('.quick_search')[0].children.length === 0) {
			$('.list_info').css('display', 'block');
		}
	});

	// 검색 기간 최대 1년 체크
	function chkDateTerm() {
		var sd = $('#start_date').val();
		var fd = $('#finish_date').val();

		var sdArr = sd.split('-');
		var fdArr = fd.split('-');

		var sDate = new Date(parseInt(sdArr[0]), parseInt(sdArr[1]), parseInt(sdArr[2]));
		var fDate = new Date(parseInt(fdArr[0]), parseInt(fdArr[1]), parseInt(fdArr[2]));

		var btMs = fDate.getTime() - sDate.getTime();
		var btDay = btMs / (1000*60*60*24);

		if (btDay > 365) {
			alert('최대 검색 기간은 1년입니다.');
			return false;
		}

		return true;
	}

	// 단축검색 존재 여부 - 감시 인스턴스 생성
	var observer = new MutationObserver(function() {
		if ($('.quick_search')[0].children.length === 0) {
			$('.list_info').css('display', 'block');
		}
	});

	// 단축검색 존재 여부 - 감시 시작
	observer.observe(document.getElementsByClassName('quick_search')[0], {
		attributes: false,
		childList: true,
		characterData: false,
	});

	// 단축검색등록 팝업
	function countLogViewQuickSearch(frm, m) {
		// 검색기간 1년 체크
		var limitDateChk = chkDateTerm();
		if (!limitDateChk) {
			return false;
		}

		viewQuickSearch(frm, m);

		window.quicksearch.close = function() {
			$('.layerPop').fadeOut('fast', function() {
				$(this).remove();
				removeDimmed();
				$('body').off('keyup');
			});

			if ($('.quick_search')[0].children.length > 0) {
				$('.list_info').css('display', 'none');
			}
		}
	}

	// 엑셀 다운로드
	function excelDownloadPermissionCheck() {
		// 검색기간 1년 체크
		var limitDateChk = chkDateTerm();
		if(!limitDateChk) {
			return false;
		}
		var sdArr = $('#start_date').val().split('-');
		var fdArr = $('#finish_date').val().split('-');

		let xls_query = '&y='+sdArr[0]+'&m='+sdArr[1]+'&d='+sdArr[2]+'&y2='+fdArr[0]+'&m2='+fdArr[1]+'&d2='+fdArr[2];
		let viewType = $('#logCountFrm #viewType').val();
		if (viewType == '') {
			viewType = 'dayView';
		}
		location.href = '?body=log@count_log_excel.exe&' + xls_query + '&logday=N&view=' + viewType;
	}

	// 기간 그래프 데이터 ajax
	function chartPeriodAjax(s, f, v) {
		$.ajax({
			type: 'POST',
			url: './index.php?body=log@count_log.exe',
			data: {
				mode: 'count_log',
				start_date: s,
				finish_date: f,
				viewType: v,
				averageDay: $('#averageDay').val(),
			},
			dataType: 'json',
			timeout: 5000, // 5초
			beforeSend: function () {
				printLoading();
			},
			success: function (response) {
				if (response.result) {
					$('#viewType').val(response.data.viewType);

					// view 타입 배경
					let tab = $('.tab_sort>li');
					tab.removeClass('active');
					tab.filter('.' + response.data.viewType).addClass('active');

					// x축 ticks, tooltipTitle 포맷
					let weekendText = ['일요일', '월요일', '화요일', '수요일', '목요일', '금요일', '토요일'];

					// data labels 생성
					chartPeriod.data.labels = [];
					for (var key in response.data.data) {
						chartPeriod.data.labels.push(response.data.data[key][0]);
					}

					// data 생성
					chartPeriod.data.datasets = chartPeriod_chart_datasets;
					chartPeriod.data.datasets[0].data = [];
					for (key in chartPeriod.data.labels) {
						if (chartPeriod.data.labels[key] == '') {
							chartPeriod.data.datasets[0].data.push(0);
						} else {
							chartPeriod.data.datasets[0].data.push(response.data.data[key][1]);
						}
					}

					let startYear = <?=$start_dateY?>;
					let endYear = <?=$finish_dateY?>;
					let startDay = <?=$start_dateD?>;
					let endDay = <?=$finish_dateD?>;
					let startMonth = <?=$start_dateM?>;
					let endMonth = <?=$finish_dateM?>;

					// 시간
					if (response.data.viewType === 'timeViewNew') {
						var datasetting = [];
						var weekend = response.data.startWeekend;
						chartPeriod.data.datasets = [chartPeriod_chart_datasets[1]];

						// 기본 데이터 생성. (연 다를때)
						if (startYear != endYear) {
							for (var y = startYear; y < endYear + 1; y++) {
								if (y == startYear) {
									var start_m = startMonth;
									var end_m = 13;
								} else {
									var start_m = 1;
									if (y == endYear) {
										var end_m = endMonth + 1;
									} else {
										var end_m = 13;
									}
								}

								for (var m = start_m; m < end_m; m++) {
									if (m == endMonth && y == endYear) {
										end_d = endDay;
									} else {
										end_d = response.data.lastDayWeek[y][m];
									}

									start_d = 1;
									if (m == startMonth && y == startYear) {
										start_d = startDay;
									}
									for (var d = start_d; d <= end_d; d++) {
										for (var h = 0; h < 24; h++) {
											datasetting.push([h, 0, d, m, y, weekend]);
										}
										if (weekend == '6') {
											weekend = '0';
										} else {
											weekend++;
										}
									}
								}
							}
						}

						// 기본 데이터 생성. (연 같고, 월만 다를때)
						if (startYear == endYear && startMonth != endMonth) {
							for (var m = startMonth; m < endMonth + 1; m++) {
								if (m == endMonth) {
									var end_d = endDay;
								} else {
									var end_d = response.data.lastDayWeek[startYear][m];
								}

								if (m == startMonth) {
									var start_d = startDay;
								} else {
									var start_d = 1;
								}

								for (var d = start_d; d <= end_d; d++) {
									for (var h = 0; h < 24; h++) {
										datasetting.push([h, 0, d, m, startYear, weekend]);
									}
									if (weekend == '6') {
										weekend = '0';
									} else {
										weekend++;
									}
								}
							}
						}

						// 기본 데이터 생성. (연 같고, 월 같고, 일만 다를때)
						if (startYear == endYear && startMonth == endMonth) {
							for (var d = startDay; d < endDay + 1; d++) {
								for (var h = 0; h < 24; h++) {
									datasetting.push([h, 0, d, startMonth, startYear, weekend]);
								}
								if (weekend == '6') {
									weekend = '0';
								} else {
									weekend++;
								}
							}
						}

						// 데이터 합치기
						let checkKey = 0;
						for (var k in datasetting) {
							if (response.data.data[checkKey]) {
								if (datasetting[k][0] == response.data.data[checkKey][0] && datasetting[k][2] == response.data.data[checkKey][2] && datasetting[k][3] == response.data.data[checkKey][3]) {
									datasetting[k][1] = response.data.data[checkKey][1];
									checkKey = checkKey + 1;
								}
							} else {
								break;
							}
						}

						// data labels 생성
						chartPeriod.data.labels = [];
						chartPeriod.data.datasets[0].onClickData = [];
						let checkMtext = '';
						for (var k in datasetting) {
							datasetting[k][3] = datasetting[k][3] < 10 ? '0' + datasetting[k][3] : datasetting[k][3];
							datasetting[k][2] = datasetting[k][2] < 10 ? '0' + datasetting[k][2] : datasetting[k][2];
							checkMtext = datasetting[k][4] + '년 ' + datasetting[k][3] + '월 ' + datasetting[k][2] + '일 ' + weekendText[datasetting[k][5]] + ' ';
							chartPeriod.data.labels.push(checkMtext + datasetting[k][0] + '시');

							// bar 클릭했을때 상세접속로그 이동 날짜
							chartPeriod.data.datasets[0].onClickData.push({'y':datasetting[k][4], 'm':datasetting[k][3], 'd1':datasetting[k][2], 'd2':datasetting[k][2], 'h1':datasetting[k][0], 'h2':datasetting[k][0]});
						}

						// data 생성
						chartPeriod.data.datasets[0].data = [];
						for (var k in chartPeriod.data.labels) {
							if (chartPeriod.data.labels[k] == '') {
								chartPeriod.data.datasets[0].data.push(0);
							} else {
								chartPeriod.data.datasets[0].data.push(datasetting[k][1]);
							}
						}
					}

					// 일
					if (response.data.viewType === 'dayView') {
						chartPeriod.data.datasets = chartPeriod_chart_datasets;

						// data labels 생성
						chartPeriod.data.labels = [];
						chartPeriod.data.datasets[1].onClickData = [];
						let checkM = '';
						let checkD = '';
						let checkMtext = '';
						for (var k in response.data.data) {
							checkM = (response.data.data[k][2].length < 2 || response.data.data[k][2] < 10) ? '0' + response.data.data[k][2] : response.data.data[k][2];
							checkMtext = response.data.data[k][4] + '년 ' + checkM + '월 ';

							checkD = response.data.data[k][0] < 10 ? '0' + response.data.data[k][0] : response.data.data[k][0];
							chartPeriod.data.labels.push(checkMtext + checkD + '일 ' + weekendText[response.data.data[k][3]]);

							// bar 클릭했을때 상세접속로그 이동 날짜
							chartPeriod.data.datasets[1].onClickData.push({'y':response.data.data[k][4], 'm':checkM, 'd1':checkD, 'd2':checkD, 'h1':'00', 'h2':'23'});
						}

						// data labels 생성 - 평균N일값
						chartPeriod.data.datasets[0].label = '지난 ' + response.data.averageDay + '일 평균';

						// data 생성
						chartPeriod.data.datasets[1].data = [];
						for (var k in chartPeriod.data.labels) {
							if (chartPeriod.data.labels[k] == '') {
								chartPeriod.data.datasets[1].data.push(0);
							} else {
								chartPeriod.data.datasets[1].data.push(response.data.data[k][1]);
							}
						}

						// data 생성 - 평균N일값
						chartPeriod.data.datasets[0].data = [];
						for (var k in chartPeriod.data.labels) {
							if (chartPeriod.data.labels[k] == '') {
								chartPeriod.data.datasets[0].data.push(0);
							} else {
								chartPeriod.data.datasets[0].data.push(response.data.averageData[k][1]);
							}
						}
					}

					// 주단위
					if (response.data.viewType == 'weekView') {
						chartPeriod.data.datasets = chartPeriod_chart_datasets;

						// data labels 생성 && data 생성
						chartPeriod.data.labels = [];
						chartPeriod.data.datasets[1].data = [];
						chartPeriod.data.datasets[1].onClickData = [];
						let labelName = '';
						let dataValue = 0;
						for (var i = 0; i < response.data.data.length; i++) {
							response.data.data[i][2] = response.data.data[i][2].length < 2 || response.data.data[i][2] < 10 ? '0' + response.data.data[i][2] : response.data.data[i][2];
							response.data.data[i][0] = response.data.data[i][0] < 10 ? '0' + response.data.data[i][0] : response.data.data[i][0];

							dataValue = dataValue + parseInt(response.data.data[i][1]);

							if (response.data.data[i][3] == '1' || i == 0) { // 월요일 || 첫시작
								labelName = response.data.data[i][4] + '년 ' + response.data.data[i][2] + '월 ' + response.data.data[i][0] + '일 ' + weekendText[response.data.data[i][3]];

								var onClickData_per = {
									'y':response.data.data[i][4],
									'm':response.data.data[i][2],
									'd1':response.data.data[i][0],
									'h1':'00',
									'h2':'23'
								}
							}
							if (response.data.data[i][3] == '0' || i == response.data.data.length - 1) { // 일요일 || 마지막 i
								if (labelName != '') {
									labelName = labelName + ' ~ ';
								}

								chartPeriod.data.labels.push(labelName + response.data.data[i][4] + '년 ' + response.data.data[i][2] + '월 ' + response.data.data[i][0] + '일 ' + weekendText[response.data.data[i][3]]);
								chartPeriod.data.datasets[1].data.push(dataValue);
								onClickData_per.d2 = response.data.data[i][0];

								dataValue = 0;
								if (response.data.data[i + 1]) {
									labelName = response.data.data[i][4] + '년 ' + response.data.data[i + 1][2] + '월 ' + response.data.data[i + 1][0] + '일 ' + weekendText[response.data.data[i][3]];
								}

								// bar 클릭했을때 상세접속로그 이동 날짜
								if (onClickData_per.m != response.data.data[i][2]) { // 월이 다른 경우 시작날로
									var onlyNum = onClickData_per.m;
									if (onlyNum.length < 2 || onlyNum < 10) {
										onlyNum = onlyNum.replace(/^0+/, '');
									}
									onClickData_per.d2 = response.data.lastDayWeek[onClickData_per.y][onlyNum];
								}
								chartPeriod.data.datasets[1].onClickData.push(onClickData_per);
							}
						}

						// data labels 생성 - 평균N주값
						chartPeriod.data.datasets[0].label = '지난 ' + response.data.averageDay + '주 평균';

						// data 생성 - 평균N주값
						chartPeriod.data.datasets[0].data = [];
						for (var i = 0; i < response.data.averageData.length; i++) {
							if (response.data.averageData[i][3] == '1' || i == 0) { // 월요일 || 첫번째 i
								chartPeriod.data.datasets[0].data.push(response.data.averageData[i][1]);
							}
						}
					}

					// 월단위
					if (response.data.viewType == 'monthView') {
						chartPeriod.data.datasets = chartPeriod_chart_datasets;

						// data labels 생성 && data 생성
						let monthFirstDay = '';
						let monthLastDay = '';
						chartPeriod.data.labels = [];
						chartPeriod.data.datasets[1].data = [];
						chartPeriod.data.datasets[1].onClickData = [];
						for (var i = 0; i < response.data.data.length; i++) {
							monthFirstDay = '01';
							if (response.data.data[i][4] == startYear && response.data.data[i][2] == startMonth) {
								monthFirstDay = startDay;
								if (startDay < 10) {
									monthFirstDay = '0' + startDay;
								}
							}
							monthLastDay = '0' + endDay;
							if (response.data['lastDayWeek']) {
								monthLastDay = response.data['lastDayWeek'][response.data.data[i][4]][response.data.data[i][2]];
							}
							if (response.data.data[i][4] == endYear && response.data.data[i][2] == endMonth) {
								if (endDay < 10) {
									endDay = '0' + endDay;
								}
								monthLastDay = endDay;
							}
							response.data.data[i][2] = response.data.data[i][2].length < 2 || response.data.data[i][2] < 10 ? '0' + response.data.data[i][2] : response.data.data[i][2];
							chartPeriod.data.labels.push(response.data.data[i][4] + '년 ' + response.data.data[i][2] + '월 ' + monthFirstDay + '일 ~ ' + response.data.data[i][4] + '년 ' + response.data.data[i][2] + '월 ' + monthLastDay + '일');
							chartPeriod.data.datasets[1].data.push(parseInt(response.data.data[i][1]));

							// bar 클릭했을때 상세접속로그 이동 날짜
							chartPeriod.data.datasets[1].onClickData.push({
								'y':response.data.data[i][4],
								'm':response.data.data[i][2],
								'd1':monthFirstDay,
								'd2':monthLastDay,
								'h1':'00',
								'h2':'23'
							});
						}

						// data labels 생성 - 평균N개월값
						chartPeriod.data.datasets[0].label = '지난 ' + response.data.averageDay + '개월 평균';

						// data 생성 - 평균N개월값
						chartPeriod.data.datasets[0].data = [];
						for (var i = 0; i < response.data.averageData.length; i++) {
							chartPeriod.data.datasets[0].data.push(parseInt(response.data.averageData[i][1]));
						}
					}

					// x축 라벨 일정 간격만큼 출력
					let strSplitLabel = '';
					if (response.data.viewType === 'timeViewNew') {
						if (chartPeriod.data.labels.length < 744) {
							chartPeriod.options.scales.x.ticks.callback = function (v, i) {
								strSplitLabel = this.getLabelForValue(v).split(' ');
								if (strSplitLabel[4] === '0시') {
									return strSplitLabel[2];
								}
							};
						} else {
							chartPeriod.options.scales.x.ticks.callback = function (v, i) {
								strSplitLabel = this.getLabelForValue(v).split(' ');
								if (strSplitLabel[3] === '월요일' && strSplitLabel[4] === '0시') {
									return strSplitLabel[2];
								}
							};
						}
					}

					if (response.data.viewType == 'dayView') {
						if (chartPeriod.data.labels.length < 32) {
							chartPeriod.options.scales.x.ticks.callback = function (v, i) {
								strSplitLabel = this.getLabelForValue(v).split(' ');
								return strSplitLabel[2];
							};
						} else {
							chartPeriod.options.scales.x.ticks.callback = function (v, i) {
								strSplitLabel = this.getLabelForValue(v).split(' ');
								if (strSplitLabel[3] == '월요일') {
									return strSplitLabel[2];
								}
							};
						}
					}

					if (response.data.viewType == 'weekView') {
						if (chartPeriod.data.labels.length < 20) {
							chartPeriod.options.scales.x.ticks.callback = function (v, i) {
								strSplitLabel = this.getLabelForValue(v).split(' ');
								let regex = /[^0-9]/g;
								return `${strSplitLabel[1].replace(regex, '')}/${strSplitLabel[2].replace(regex, '')}`;
							};
						} else {
							var prevLabelText = chartPeriod.data.labels[0].split(' ')[1];
							chartPeriod.options.scales.x.ticks.callback = function (v, i) {
								strSplitLabel = this.getLabelForValue(v).split(' ');
								if (i === 0 || (prevLabelText != strSplitLabel[1])) {
									prevLabelText = strSplitLabel[1];
									let regex = /[^0-9]/g;
									return `${strSplitLabel[1].replace(regex, '')}/${strSplitLabel[2].replace(regex, '')}`;
								}
							};
						}
					}

					if (response.data.viewType == 'monthView') {
						chartPeriod.options.scales.x.ticks.callback = function (v, i) {
							strSplitLabel = this.getLabelForValue(v).split(' ');
							return strSplitLabel[1];
						};
					}
				} else {
					removeLoading();
					alert(response.msg);
				}
			},
			complete: function (data, textStatus) {
				if (textStatus === 'success') {
					removeLoading();

					chartPeriod.clear();
					chartPeriod.update();

					$('#start_date').val(data.responseJSON.data.startSearch);
					$('#finish_date').val(data.responseJSON.data.finishSearch);

					if (data.responseJSON.type === 2) {
						alert(data.responseJSON.typeAlert);
					}
				}
			},
			error: function (request, status, error) {
				removeLoading();
				alert('데이터 통신중 오류가 발생했습니다.\n\n다시 시도해주세요.');
			},
		});
	}

	// 요일별, 시간별 그래프 ajax
	function chartWeekTimeAjax(s, f) {
		$.ajax({
			type: 'POST',
			url: './index.php?body=log@count_log_weektime.exe',
			data: {
				mode: 'count_log_weektime',
				start_date: s,
				finish_date: f,
			},
			dataType: 'json',
			timeout: 5000, // 5초
			success: function (response) {
				if (response.result) {
					chartWeek.data.datasets[0].data = response.data.data['week'];
					chartTime.data.datasets[0].data = response.data.data['time'];
					chartTime.options.plugins.tooltip.callbacks.title = function (tooltipItems) {
						return tooltipItems[0].label + '시';
					};
				}
			},
			complete: function (data, textStatus) {
				if (textStatus == 'success') {
					chartWeek.clear();
					chartWeek.update();
					chartTime.clear();
					chartTime.update();
				}
			},
		});
	}

	// 기간 그래프 data 세팅
	let chartPeriod_chart_datasets = [
		{
			type: 'line',
			label: '지난 평균',
			data: [],
			borderColor: '#DF7593',
			backgroundColor: '#DF7593',
			sort: 2,
		},
		{
			type: 'bar',
			label: '방문자 수',
			data: [],
			backgroundColor: '#759EDF',
			borderRadius: 30,
			maxBarThickness: 8,
			sort: 1,
			onClickData: [],
		},
	];

	// 기간 그래프 생성
	let chartPeriod = chartMake('chartPeriod', {
		data: {
			labels: [],
			datasets: chartPeriod_chart_datasets,
		},
		options: {
			responsive: true,
			maintainAspectRatio: true,
			interaction: {
				intersect: false,
				mode: 'index',
			},
			onClick: function(e, d, c) {
				let viewType = $('#viewType').val();
				if (viewType === 'timeViewNew') {
					var clickData = c.config._config.data.datasets[0].onClickData[d[0].index];
				} else {
					var clickData = c.config._config.data.datasets[1].onClickData[d[1].index];
				}
				window.open('./?body=log@count_log_list&exec=search&y1='+clickData.y+'&m1='+clickData.m+'&d1='+clickData.d1+'&h1='+clickData.h1+'&d2='+clickData.d2+'&h2='+clickData.h2, '_blank');
			},
			plugins: {
				legend: {
					display: true,
					position: 'bottom',
					labels: {
						usePointStyle: true,
						boxWidth: 8,
					},
					reverse: true,
					onClick: function (event, legendItem, legend) {
						let hiddenChk = 0;
						legend.legendItems.forEach(function (e) {
							if (e.hidden == false) {
								hiddenChk++; // show 갯수 체크
							}
						});

						let index = legendItem.datasetIndex;
						let ci = legend.chart;

						if (ci.isDatasetVisible(index)) { // hidden할때
							if (hiddenChk == 1) { // 1개만 켜져있으면 반대로 켜기
								legend.legendItems.forEach(function (e, i) {
									if (index != i) {
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
				tooltip: {
					enabled: false,
					external: externaltooltip,
					position: 'custom',
					itemSort: function (a, b) {
						return a.dataset.sort - b.dataset.sort;
					},
					callbacks: {
						title: function (tooltipItems) {
							if (tooltipItems[0].label == '') {
								return '';
							} else {
								return tooltipItems[0].label;
							}
						},
						label: function (tooltipItems) {
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
					ticks: {
						color: '#999999',
					},
				},
				y: {
					display: true,
					min: 0,
					afterFit: function (scaleInstance) {
						scaleInstance.width = 50;
					},
					ticks: {
						color: '#999999',
					},
				}
			},
		},
	});

	// 요일별 그래프 생성
	let chartWeek = chartMake('chartWeek', {
		type: 'bar',
		data: {
			labels: ['일', '월', '화', '수', '목', '금', '토'],
			datasets: [
				{
					label: '방문자 수',
					data: [],
					borderColor: '#759EDF',
					backgroundColor: '#759EDF',
					borderRadius: 30,
					maxBarThickness: 8,
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
					position: 'top',
					title: {
						display: true
					}
				},
				title: {
					display: true,
					text: '요일별',
					align: 'start',
					padding: {
						bottom: 50
					},
					font: {
						size: 15,
						color: '#000',
					},
				},
				tooltip: {
					enabled: false,
					external: externaltooltip,
					position: 'custom',
					callbacks: {
						title: function (tooltipItems) {
							if (tooltipItems[0].label == '') {
								return '';
							} else {
								return tooltipItems[0].label;
							}
						},
						label: function (tooltipItems) {
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
					ticks: {
						color: '#999999',
					},
				},
				y: {
					display: true,
					min: 0,
					afterFit: function (scaleInstance) {
						scaleInstance.width = 50;
					},
					ticks: {
						color: '#999999',
					},
				}
			},
		},
	});

	// 시간별 그래프 생성
	let chartTime = chartMake('chartTime', {
		type: 'bar',
		data: {
			labels: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23],
			datasets: [
				{
					label: '방문자 수',
					data: [],
					borderRadius: 30,
					borderColor: '#759EDF',
					backgroundColor: '#759EDF',
					maxBarThickness: 8,
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
					position: 'top',
					title: {
						display: true,
					}
				},
				title: {
					display: true,
					text: '시간별',
					align: 'start',
					padding: {
						bottom: 50
					},
					font: {
						size: 15,
						color: '#000',
					},
				},
				tooltip: {
					enabled: false,
					external: externaltooltip,
					position: 'custom',
					callbacks: {
						title: function (tooltipItems) {
							if (tooltipItems[0].label == '') {
								return '';
							} else {
								return tooltipItems[0].label;
							}
						},
						label: function (tooltipItems) {
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
					ticks: {
						color: '#999999',
					}
				},
				y: {
					display: true,
					min: 0,
					afterFit: function (scaleInstance) {
						scaleInstance.width = 50;
					},
					ticks: {
						color: '#999999',
					},
				}
			},
		},
	});

	// 데이터 검색
	function searchDate(s, e) {
		$('#start_date').val(s);
		$('#finish_date').val(e);
		$('form[name="logCountFrm"]').submit();
	}

	// 지난 평균 기간 셀렉트박스 설정
	function averageSelectDisabled(view) {
		if (view === 'dayView' || view === 'weekView' || view === 'monthView') {
			$('#averageDayDiv').css('visibility', 'visible');
			$('#averageDay').attr('disabled', false);
			$('#averageDay option').remove();
			if (view === 'dayView') {
				$('#averageDay').append("<?php
					foreach ($average_select_day as $k => $v) {
						echo "<option value='$k'>$v</option>";
					}
					?>");
			}
			if (view === 'weekView') {
				$('#averageDay').append("<?php
					foreach ($average_select_week as $k => $v) {
						$selected = '';
						if ($k == 4) $selected = 'selected';
						echo "<option value='$k' $selected>$v</option>";
					}
					?>");
			}
			if (view === 'monthView') {
				$('#averageDay').append("<?php
					foreach ($average_select_month as $k => $v) {
						echo "<option value='$k'>$v</option>";
					}
					?>");
			}
		} else {
			$('#averageDay').attr('disabled', true);
			$('#averageDayDiv').css('visibility', 'hidden');
		}
	}

	// view 타입
	function gridView(s, f, viewType) {
		let tab = $('.tab_sort > li');
		tab.removeClass('active');
		tab.filter('.' + viewType).addClass('active');
		averageSelectDisabled(viewType);

		let sd = $('#start_date').val();
		let fd = $('#finish_date').val();
		chartPeriodAjax(sd, fd, viewType);
	}

	// 평균일 onchange되면 ajax 다시 실행
	function ajaxReStart() {
		let view = $('#logCountFrm #viewType').val();
		chartPeriodAjax('<?=$start_date?>', '<?=$finish_date?>', view);
	}
</script>