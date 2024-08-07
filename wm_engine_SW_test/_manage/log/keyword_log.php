<?php

	/* +----------------------------------------------------------------------------------------------+
	' |  상품검색어
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
				$firstDate  = new DateTime($_GET['start_date']);
				$secondDate = new DateTime($_GET['finish_date']);
				$intvl = $firstDate->diff($secondDate);
				$diffDay = $intvl->days;

				$_GET['start_date'] = date('Y-m-d', strtotime($today." -".$diffDay." days"));
				$_GET['finish_date'] = $today;
			}
		}
	}

	// 검색 데이터
	$viewType = addslashes($_GET['viewType']); // 시간/일/주/월 탭
	$start_date = addslashes($_GET['start_date']); // 시작일
	$finish_date = addslashes($_GET['finish_date']); // 종료일

	// 오늘 날자
	$explodeDate = explode('-', $today);
	$todayY = $explodeDate[0];
	$todayM = $explodeDate[1];
	$todayD = $explodeDate[2];

	// 어제 날짜
	$yesterDay = date('Y-m-d', strtotime($today.' -1days'));

	// 최근 7일
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
	$lastMonthStartDay = date('Y-m-01', $prev_month );
	$lastMonthEndDay = date('Y-m-t', $prev_month );

	// viewType 저장
	$cookie_view = 'dayView';
	if ($start_date == '' && $finish_date == '' && !$spno) {
		$cookie_view = 'dayView';
	} else {
		if ($spno) {
			$cookie_view = $_GET['viewType'];
		} else {
			$cookie_view = $_COOKIE['keywordlog_view'];
		}
	}

	// 값이 없거나 첫 화면은 '오늘'로 기본 세팅
	if ($viewType == '') $viewType = 'dayView';
	if ($start_date == '') $start_date = $last30StartDay;
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

	$xls_query = makeQueryString('body');
?>
<script src="<?=$engine_url?>/_engine/common/chart/chart.js"></script>
<script src="<?=$engine_url?>/_engine/common/chart/chartjs-function.js"></script>
<link rel="stylesheet" href="<?=$engine_url?>/_manage/css/log.css"/>

<div class="box_title first"><h2 class="title">상품검색어</h2></div>
<form name="keywordCountFrm" id="keywordCountFrm" onsubmit="return chkDateTerm();">
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
					foreach ($easySearchBtn as $k=>$v) {
						$class_on = ($start_date == $v[1] && $finish_date == $v[2]) ? 'on' : '';
					?>
						<span class="box_btn_d <?=$class_on?>"><input type="button" value="<?=$k?>" onclick="searchDate('<?=$v[1]?>','<?=$v[2]?>','<?=$v[0]?>');"></span>
					<?php } ?>
				</td>
			</tr>
			<tr>
				<th scope="row">단축검색</th>
				<td>
					<?php
					$preset_menu = 'keyword_log';
					$sp_count = $pdo->row("select count(*) from {$tbl['search_preset']} where menu='$preset_menu'");
					if ($admin['level'] < 4) {
					?>
						<ul class="list_info" style="display: none;">
							<li>자주 검색하는 기간은 [#단축검색등록]을 통해 편하게 검색할 수 있습니다.</li>
						</ul>
						<ul class="quick_search left">
							<?php include_once $engine_dir."/_manage/config/quicksearch.inc.php"; ?>
						</ul>
					<?php } ?>
				</td>
			</tr>
		</table>
	</div>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="검색" id="search"></span>
		<span class="box_btn"><input type="button" value="초기화" onclick="location.href='?body=<?=$_GET['body']?>'"></span>
		<span class="box_btn quicksearch" style="display: inline; float:right;"><a onclick="keywordLogViewQuickSearch('keywordCountFrm', 'keyword_log');">#단축검색등록</a></span>
	</div>
</form>

<div class="box_title">
	<h2 class="title">상품검색어 리스트</h2>
</div>
<table class="tbl_col" id="cartRank">
	<caption class="hidden">상품검색어 리스트</caption>
	<colgroup>
		<col style="width:100px">
		<col style="width:100px;">
		<col>
		<col style="width:150px">
	</colgroup>
	<thead>
		<tr>
			<th scope="col"><input type="checkbox" name="kwTableLeftChk" onclick="kwAllCheck();"></th>
			<th scope="col">순위</th>
			<th scope="col">검색어</th>
			<th scope="col">검색량</th>
		</tr>
	</thead>
	<tbody id="keyword_table_list"></tbody>
</table>
<div class="box_bottom left keywordlog" id="kwdListSearchButton">
    <div class="textSearch">검색 <a href="javascript:;" onclick="selectKeywordListDelete('all');" title="초기화"><i class="xi-refresh xi-x"></i></a></div>
    <div class="selectSearch">
        <ul class="quick_search2" id="select_search_list"></ul>
        <div class="box_btn_s blue" onclick="listKeywordSearch('click');"><input type="button" class="selectSearchBtn" value="검색"></div>
    </div>
    <div class="maxSearchCount">
		<ul class="list_info">
			<li>최대 10개 선택 가능</li>
		</ul>
	</div>
</div>
<div class="box_bottom" id="pg_res"></div>

<div class="box_middle sort" style="border-bottom:1px solid #c9c9c9;margin-top:35px;">
	<ul class="tab_sort">
		<li class="dayView active"><a href="#" onclick="gridView('<?=$start_date?>', '<?=$finish_date?>', 'dayView'); return false;">일</a></li>
		<li class="weekView"><a href="#" onclick="gridView('<?=$start_date?>', '<?=$finish_date?>', 'weekView'); return false;">주</a></li>
		<li class="monthView"><a href="#" onclick="gridView('<?=$start_date?>', '<?=$finish_date?>', 'monthView'); return false;">월</a></li>
	</ul>
</div>

<div class="box_middle2">
	<canvas id="chartKeyword" style="width:100%;height:350px;margin-bottom:10px;"></canvas>
</div>

<script type="text/javascript">
	let directClick = false; // 직접 클릭 false

	$(document).ready(function() {
		getKeywordListAjax('<?=$start_date?>', '<?=$finish_date?>', '<?=$searchType?>', '<?=$cookie_view?>', '1');

		if ($('#keywordCountFrm .quick_search')[0].children.length === 0) {
			$('.list_info').css('display', 'block');
		}
	});

	// 단축검색 존재 여부 - 감시 인스턴스 생성
	var observer = new MutationObserver(function() {
		if ($('#keywordCountFrm .quick_search')[0].children.length === 0) {
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
	function keywordLogViewQuickSearch(frm, m) {
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

			if ($('#keywordCountFrm .quick_search')[0].children.length > 0) {
				$('.list_info').css('display', 'none');
			}
		}
	}

	// 전체선택 체크박스 클릭시 실행
	function kwAllCheck() {
		let now_this_name = 'keyword_table_list';
		if ($('input:checkbox[name="kwTableLeftChk"]').is(':checked') == true) {
			for (var i = 0; i < $('#' + now_this_name + ' tr input').length; i++) {
				let e_input = $('#' + now_this_name + ' tr input')[i];
				if (!$(e_input).is(':checked')) {
					if (setKeywordListCountCheck()) {
						$(e_input).prop('checked', true);
						selectKeywordList(e_input, e_input.value);
					} else {
						$(e_input).prop('checked', false);
						return false;
					}
				}
			}
		} else {
			$('#' + now_this_name + ' tr input').prop('checked', false);
			for (var i = 0; i < $('#' + now_this_name + ' tr input').length; i++) {
				let e_input = $('#' + now_this_name + ' tr input')[i];
				selectKeywordList(e_input, e_input.value);
			}
		}
	}

	// 키워드 리스트 페이징 버튼 클릭시 실행
	function keywordListSet(r, p) {
		getKeywordListAjax('<?=$start_date?>', '<?=$finish_date?>', '<?=$searchType?>', '<?=$cookie_view?>', p);
	}

	// 키워드 리스트에서 개별 체크박스 클릭
	function selectKeywordList(e, v) {
		directClick = true; // 직접 클릭 true
		let r = `<li class="" onclick="selectKeywordListDelete(this);"><a data-idx="${v}" class="link">${v}</a><a class="delete">삭제</a></li>`
		if ($(e).is(':checked') == true) {
			if (setKeywordListCountCheck()) {
				$('ul#select_search_list').append(r);
			} else {
				$(e).prop('checked', false);
				return false;
			}
		} else {
			for (var i = 0; i < $('ul#select_search_list')[0].children.length; i++) {
				if ($('ul#select_search_list')[0].children[i].children[0].dataset.idx === v) {
					$('ul#select_search_list')[0].children[i].click();
					break;
				}
			}
		}
	}

	// 선택한 키워드 삭제
	function selectKeywordListDelete(v = 'all') {
		if (v == 'all') { // 초기화
			$('#select_search_list li').remove();
			for (var i = 0; i < $('#keyword_table_list tr input').length; i++) {
				let e_input = $('#keyword_table_list tr input')[i];
				$(e_input).prop('checked', false);
			}
			$('input:checkbox[name="kwTableLeftChk"]').prop('checked', false);
			return;
		}

		$(v).remove();
		for (var i = 0; i < $('#keyword_table_list tr input').length; i++) {
			let e_input = $('#keyword_table_list tr input')[i];
			if ($(e_input).is(':checked') && $(v)[0].children[0].dataset.idx == e_input.value) {
				$(e_input).prop('checked', false);
				return false;
			}
		}
	}

	// 선택한 키워드들 검색 -> 차트그리기
	function listKeywordSearch(chk) {
		var list = setKeywordList();
		if (list.length > 0 ) {
			chartGridAjax('<?=$start_date?>', '<?=$finish_date?>', '<?=$cookie_view?>', list);
		} else {
			if (chk == 'click') {
				alert('검색어를 선택하세요.');
			}
		}
	}

	// 선택한 키워드 리스트 생성
	function setKeywordList() {
		var list = [];
		for (var i = 0; i < $('#select_search_list li').length; i++) {
			list.push({value : $('#select_search_list li')[i].children[0].dataset.idx});
		}
		return list;
	}

	// 선택한 키워드 리스트 갯수 확인
	function setKeywordListCountCheck() {
		let maxKwCnt = 10;

		if ($('#kwdListSearchButton ul#select_search_list li').length < maxKwCnt) {
			return true;
		}
		alert('최대 10개만 가능합니다.');
		return false;
	}

	// view 타입
	function gridView(s,f,vt) {
		var tab = $('.tab_sort>li');
		tab.removeClass('active');
		tab.filter('.'+vt).addClass('active');

		var list = setKeywordList();
		chartGridAjax(s,f,vt,list);
	}

	// 검색어 리스트 데이터 ajax
	function getKeywordListAjax(start_date, finish_date, searchType, viewType, page) {
		$.ajax({
			type: 'POST',
			url: './index.php?body=log@keyword_log.exe',
			data: {
				mode : 'keywordList',
				start_date : start_date,
				finish_date : finish_date,
				searchType : searchType,
				viewType : viewType,
				page : page,
			},
			dataType: 'json',
			timeout: 5000, // 5초
			success: function(response) {
				if (response.result) {
					let searchWords = [];
					for (var i = 0; i < $('#select_search_list')[0].children.length; i++) {
						searchWords.push($('#select_search_list')[0].children[i].children[0].dataset.idx);
					}
					var htmlcode = '';
					let checked = '';

					if (response.data.data.length > 0) {
						$('#kwdListSearchButton').css('display','flex');
						$('#pg_res').css('display','block');
						response.data.data.forEach(function(e) {
							checked = '';
							if (searchWords.includes(e[1])) {
								checked = 'checked';
							}
							htmlcode = `${htmlcode}<tr><td scope='row' class='line_r'><input type='checkbox' value='${e[1]}' onclick='selectKeywordList(this, this.value);' ${checked}></td><td class='line_r'>${e[0]}</td><td class='left line_r'>${e[1]}</td><td>${e[2]}</td></tr>`;
						});

						$('#pg_res').html(response.pg_res);
					} else {
						htmlcode = `<tr class='none'><td colspan='4' style='border-bottom:0;'><p class='nodata'>검색된 상품검색어가 없습니다.</p></td></tr>`;
						$('#kwdListSearchButton').css('display','none');
						$('#pg_res').css('display','none');
					}

					$('#keyword_table_list').html(htmlcode);
				} else {
					console.log(response.msg);
				}
			},
			complete: function(data,textStatus) {
				$('#start_date').val(data.responseJSON.data.startSearch);
				$('#finish_date').val(data.responseJSON.data.finishSearch);

				$('input:checkbox[name="kwTableLeftChk"]').prop('checked', false);
				if ('<?=count($_GET)?>' == 1 && directClick == false) {
					$('input:checkbox[name="kwTableLeftChk"]').prop('checked', true);
					kwAllCheck();
					listKeywordSearch('auto');
				}

				if (data.responseJSON.type === 2) {
					alert(data.responseJSON.typeAlert);
				}
			},
			error: function(request,status,error) {
				removeLoading();
				alert('데이터 통신중 오류가 발생했습니다.\n\n다시 시도해주세요.');
			},
		});
	}

	// 그래프 데이터 ajax
	function chartGridAjax(s,f,v,kw) {
		$.ajax({
			type: 'POST',
			url: './index.php?body=log@keyword_log.exe',
			data: {
				mode : 'keyword_log',
				start_date : s,
				finish_date : f,
				viewType : v,
				keyword : kw,
			},
			dataType: 'json',
			timeout: 5000, // 5초
			beforeSend: function() {
				printLoading();
			},
			success: function(response) {
				if (response.result) {
					$('#viewType').val(response.data.viewType);

					// view 타입 배경
					let tab = $('.tab_sort>li');
					tab.removeClass('active');
					tab.filter('.'+response.data.viewType).addClass('active');

					let barColors = ['#DF7593', '#FFCF9F', '#D2E38E', '#3EC483', '#CDB3DB', '#CC75DF', '#FFAFCC', '#8C75DF', '#BDE0FE', '#759EDF'];

					// 일
					if (response.data.viewType === 'dayView') {
						chartKeyword.data.labels = response.data.labels;
						chartKeyword.data.datasets = [];

						// 기본 데이터 셋
						let pointRadiusVal = 0;
						if (response.data.labels.length == 1) {
							pointRadiusVal = 3;
						}
						let color_i = 0;
						for (k in response.data.data) {
                            var labelText = k;
                            if (k.length > 10) {
                                labelText = k.substr(0, 10) + '...';
                            }
							chartKeyword.data.datasets.push(
								{
									type: 'line',
									label: labelText,
									data: response.data.data[k],
									pointStyle: 'circle',
									pointRadius: pointRadiusVal,
									pointHoverRadius: 3,
									backgroundColor: barColors[color_i],
									borderColor: barColors[color_i],
								}
							);
							color_i++;
						}
					}

					// 주
					if (response.data.viewType === 'weekView') {
						chartKeyword.data.labels = response.data.labels;
						chartKeyword.data.datasets = [];

						// 기본 데이터 셋
						let pointRadiusVal = 0;
						if (response.data.labels.length == 1) {
							pointRadiusVal = 3
						}
						let color_i = 0;
						for (k in response.data.data) {
                            var labelText = k;
                            if (k.length > 10) {
                                labelText = k.substr(0, 10) + '...';
                            }
							var per_datasets = {
								type: 'line',
								label: labelText,
								data: response.data.data[k],
								pointStyle: 'circle',
								pointRadius: pointRadiusVal,
								pointHoverRadius: 3,
								backgroundColor: barColors[color_i],
								borderColor: barColors[color_i],
							};
							chartKeyword.data.datasets.push(per_datasets);
							color_i++;
						}
					}

					// 월
					if (response.data.viewType === 'monthView') {
						chartKeyword.data.labels = response.data.labels;
						chartKeyword.data.datasets = [];
						let color_i = 0;

						let pointRadiusVal = 0;
						if (response.data.labels.length == 1) {
							pointRadiusVal = 3
						}
						for (k in response.data.data) {
                            var labelText = k;
                            if (k.length > 10) {
                                labelText = k.substr(0, 10) + '...';
                            }
							var per_datasets = {
								type: 'line',
								label: labelText,
								data: response.data.data[k],
								pointStyle: 'circle',
								pointRadius: pointRadiusVal,
								pointHoverRadius: 3,
								backgroundColor: barColors[color_i],
								borderColor: barColors[color_i],
							};
							chartKeyword.data.datasets.push(per_datasets);
							color_i++;
						}
					}

					// x축 라벨 출력 간격
					if (response.data.viewType === 'dayView') {
						if (chartKeyword.data.labels.length < 32) {
							chartKeyword.options.scales.x.ticks.callback = function(v, i) {
								let exVal = this.getLabelForValue(v).split(' ');
								return exVal[2];
							};
						} else {
							chartKeyword.options.scales.x.ticks.callback = function(v, i) {
								let exVal = this.getLabelForValue(v).split(' ');
								if (exVal[3] == '월요일') {
									return exVal[2];
								}
							};
						}
					}

					if (response.data.viewType === 'weekView') {
						if (chartKeyword.data.labels.length < 20) {
							chartKeyword.options.scales.x.ticks.callback = function(v, i) {
								let exVal = this.getLabelForValue(v).split(' ');
								let regex = /[^0-9]/g;
								return `${exVal[1].replace(regex, '')}/${exVal[2].replace(regex, '')}`;
							};
						} else {
							let firstLabelName = chartKeyword.data.labels[0].split(' ')[1];
							chartKeyword.options.scales.x.ticks.callback = function(v, i) {
								let exVal = this.getLabelForValue(v).split(' ');
								if (i === 0 || firstLabelName != exVal[1]) {
									firstLabelName = exVal[1];
									let regex = /[^0-9]/g;
									return `${exVal[1].replace(regex, '')}/${exVal[2].replace(regex, '')}`;
								}
							};
						}
					}

					if (response.data.viewType === 'monthView') {
						chartKeyword.options.scales.x.ticks.callback = function(v, i) {
							return this.getLabelForValue(v).split(' ')[1];
						};
					}
				} else {
					console.log(response.msg);
					removeLoading();
				}
			},
			complete: function(data,textStatus) {
				if (textStatus == 'success') {
					removeLoading();

					chartKeyword.clear();
					chartKeyword.update();

					$('#start_date').val(data.responseJSON.data.startSearch);
					$('#finish_date').val(data.responseJSON.data.finishSearch);
				}
			},
			error: function(request,status,error){
				removeLoading();
				alert('데이터 통신중 오류가 발생했습니다.\n\n다시 시도해주세요.');
			},
		});
	}

	// 그래프 생성
	const chartKeyword = chartMake('chartKeyword', {
		data: {
			labels: [],
			datasets: [],
		},
		options: {
			responsive: true,
			maintainAspectRatio: true,
			interaction: {
				intersect: false,
				mode: 'index',
			},
			layout: {
				padding: {
					top: 40
				}
			},
			plugins: {
				legend: {
					display: true,
					position: 'bottom',
					labels: {
						usePointStyle: true,
						boxWidth: 8
					},
				},
				title: {
					display: false,
				},
				tooltip: {
					enabled: false,
					external: externaltooltip,
					position: 'custom',
					callbacks: {
						title: function(tooltipItems) {
							if (tooltipItems[0].label == '') {
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
					ticks: {
						color: '#999999',
						callback: function(v, i) {
							if (this.getLabelForValue(v) == '') {
								return '';
							} else {
								return this.getLabelForValue(v);
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
					afterFit: function(scaleInstance) {
						scaleInstance.width = 50;
					},
				}
			},
		},
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

	// 데이터 검색
	function searchDate(s,e,n) {
		$('#start_date').val(s);
		$('#finish_date').val(e);
		$('#searchType').val(n);
		$('form[name="keywordCountFrm"]').submit();
	}
</script>