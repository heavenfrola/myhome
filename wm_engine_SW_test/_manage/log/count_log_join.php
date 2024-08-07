<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  기간별 회원 가입통계
	' +----------------------------------------------------------------------------------------------+*/

	$start_date = addslashes($_GET['start_date']);
	$finish_date = addslashes($_GET['finish_date']);
	$log_mode = addslashes($_GET['log_mode']);
	$login_type = $_GET['login_type'];

	if(!$start_date || !$finish_date) {
		$start_date = date('Y-m-d', strtotime('-2 week'));
		$finish_date = date('Y-m-d');
	}

	if(!$log_mode) {
		$log_mode = 2;
	}

	list($start_yy, $start_mm, $start_dd) = explode('-', $start_date);
	list($finish_yy, $finish_mm, $finish_dd) = explode('-', $finish_date);

	$_start_date = strtotime($start_date);
	$_finish_date = strtotime($finish_date) + 86399;

	if($log_mode > 1 && $start_mm != $finish_mm && $start_dd <= $finish_dd) {
		$start_date = date('Y-m-d');
		$finish_date = date('Y-m-d');
		$_start_date = strtotime($start_date);
		$_finish_date = strtotime($finish_date) + 86399;
		list($start_yy, $start_mm, $start_dd) = explode('-', $start_date);
		list($finish_yy, $finish_mm, $finish_dd) = explode('-', $finish_date);
		alert('일 별 보기의 경우 한 달 범위만 검색 가능합니다.\n ex) 9월 13일~10월 12일');
	}

	if(!$all_date) {
		$w .= " and `reg_date` >= '$_start_date'";
		$w .= " and `reg_date` <= '$_finish_date'";
	}

	if(is_array($login_type)) {
		foreach($login_type as $key => $val) {
			$_val = '%@'.addslashes($val).'%';
			if($val == 'com') {
				$_val = '';
			}
			$_w[] = "`login_type` like '$_val'";
		}
		$w .= " and (".implode(" or ", $_w).")";
	}

	foreach($_SESSION['myAccounts'] as $key => $val) {
		if($val->current[0] == 1) {
			$site_flag = $val -> flag_url[0];
		}
	}

	$sql = "select from_unixtime(reg_date, '%Y-%m-%d') as reg_date2, count(*) as cnt, sum(if(last_order>0, 1, 0)) as buy, sum(if(reg_date!=last_con, 1, 0)) as recon from wm_member where 1 $w group by reg_date2";

	$year = date('Y');

	$res = $pdo->iterator("select birth, (floor(($year+1-substr(birth, 1, 4))/10)*10) as birth_y, count(*) as cnt, sex from $tbl[member] where 1 $w group by birth_y, sex");
    foreach ($res as $data) {
		if($data['birth_y'] > 60 || !$data['birth_y']) $data['birth_y'] = 'etc';
		if(!$data['sex']) $data['sex'] = 'etc';
		${"birth_yg_".$data['birth_y'].'_'.$data['sex']} += $data['cnt'];
		${"birth_y_".$data['birth_y']} += $data['cnt'];
		${"birth_g_".$data['sex']} += $data['cnt'];
		$total += $data['cnt'];
	}

	$res = $pdo->iterator($sql);
    foreach ($res as $data) {
		$cnt = $data['cnt'];
		$recon = $data['recon'];
		$buy = $data['buy'];
	}

	$tickinterval = '1 month';
	$suffix = '%m';
	$unit = '월';
	$graph = 12;

	if($log_mode > 1) {
		$tickinterval = '1 day';
		$suffix = '%d';
		$unit = '일';
		$graph = date('t', strtotime("$start_yy-$start_mm-01"));
	}

	$sql="select from_unixtime(reg_date, '%Y-%m-%d') as reg_date2, count(*) as cnt, sum(if(last_order>0, 1, 0)) as buy, sum(if(reg_date!=last_con, 1, 0)) as recon from wm_member where 1 $w group by reg_date2";

	$res = $pdo->iterator("
		select from_unixtime(reg_date, '$suffix') as unit,
			count(*) as cnt,
			sum(if(last_order>0, 1, 0)) as buy,
			sum(if(reg_date!=last_con, 1, 0)) as recon
		from wm_member where reg_date between $_start_date and $_finish_date $w group by unit");
	$tmp = array();
    foreach ($res as $sdata) {
		$tmp[$sdata['unit']] = array(
			'recon' => round($sdata['recon']),
			'cnt' => round($sdata['cnt']),
			'buy' => round($sdata['buy'])
		);
	}

	if($log_mode > 1) {
		$end_graph = $finish_dd + $graph;
		$start_graph = $start_dd;
		if($start_mm == $finish_mm) {
			$end_graph = $finish_dd - $start_dd;
			$end_graph = $start_dd + $end_graph;
		}
	} else {
		$start_graph = $start_mm;
		$end_graph = $finish_mm;
	}
	for($i = $start_graph; $i <= $end_graph; $i++) {
		$start_day = $i;
		if($i == $graph + 1) {
			$start_mm = $start_mm + 1;
			$start_day = '1';
		}
		if($i > $graph + 1) {
			$start_month++;
			$start_day = $start_month + 1;
		}
		$sdata = $tmp[sprintf('%02d', $start_day)];

		if(!$sdata) $sdata = array('recon' => 0, 'cnt' => 0, 'moblle' => 0);
		if(!$sdata['recon']) $sdata['recon'] = 0;
		if(!$sdata['cnt']) $sdata['cnt'] = 0;
		if(!$sdata['buy']) $sdata['buy'] = 0;

		switch($suffix) {
			case '%d' : $field = "$start_yy-$start_mm-$start_day"; break;
			case '%m' : $field = date('Y-m-d', strtotime("$start_yy-$start_day-01")); break;
		}

		$line2 .= ", ['$field', $sdata[cnt]]";
		$line3 .= ", ['$field', $sdata[buy]]";
		$line4 .= ", ['$field', $sdata[recon]]";
	}
	$line2 = preg_replace('/^,/', '', $line2);
	$line3 = preg_replace('/^,/', '', $line3);
	$line4 = preg_replace('/^,/', '', $line4);
?>

<link rel="stylesheet" type="text/css" href="<?=$engine_url?>/_engine/common/jquery.jqplot/jquery.jqplot.min.css">
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/jquery.jqplot/jquery.jqplot.min.js"></script>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/jquery.jqplot/excanvas.min.js"></script>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/jquery.jqplot/plugins/jqplot.dateAxisRenderer.min.js"></script>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/jquery.jqplot/plugins/jqplot.canvasTextRenderer.min.js"></script>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/jquery.jqplot/plugins/jqplot.canvasAxisTickRenderer.min.js"></script>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/jquery.jqplot/plugins/jqplot.barRenderer.min.js"></script>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/jquery.jqplot/plugins/jqplot.highlighter.min.js"></script>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/jquery.jqplot/plugins/jqplot.cursor.min.js"></script>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/jquery.jqplot/plugins/jqplot.pointLabels.min.js"></script>
<form name="joinFrm" method="get">
	<input type="hidden" name="body" value="<?=$_GET['body']?>">
	<div class="box_title first">
		<h2 class="title">기간별 회원가입</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">기간별 회원가입</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">회원가입일</th>
			<td><?=setDateBunttonSet('start_date', 'finish_date', $start_date, $finish_date, true)?></td>
		</tr>
		<tr>
			<th scope="row">가입수단</th>
			<td>
				<label class='p_cursor'><input type='checkbox' name='login_type[]' value='com' <?= ((is_array($_GET[login_type]) && in_array('com', $_GET[login_type]))) ? "checked" : "" ?>><img src="<?=$site_flag?>" width='13px'; height='13px'; onmouseover="showToolTip(event,'쇼핑몰')" onmouseout="hideToolTip();"></label>
				<label class='p_cursor'><input type='checkbox' name='login_type[]' value='nvr' <?= ((is_array($_GET[login_type]) && in_array('nvr', $_GET[login_type]))) ? "checked" : "" ?>><img src="<?=$engine_url?>/_manage/image/icon/ic_conv_na.png"  onmouseover="showToolTip(event,'네이버')" onmouseout="hideToolTip();"></label>
				<label class='p_cursor'><input type='checkbox' name='login_type[]' value='fb' <?= ((is_array($_GET[login_type]) && in_array('fb', $_GET[login_type]))) ? "checked" : "" ?>><img src="<?=$engine_url?>/_manage/image/icon/ic_conv_fb.png" onmouseover="showToolTip(event,'페이스북')" onmouseout="hideToolTip();"></label>
				<label class='p_cursor'><input type='checkbox' name='login_type[]' value='kko' <?= ((is_array($_GET[login_type]) && in_array('kko', $_GET[login_type]))) ? "checked" : "" ?>><img src="<?=$engine_url?>/_manage/image/icon/ic_conv_ka.png" onmouseover="showToolTip(event,'카카오톡')" onmouseout="hideToolTip();"></label>
			</td>
		</tr>
		<tr>
			<th scope="row">보기방식</th>
			<td>
			<select name="log_mode">
				<option value="2" <?=checked($log_mode, 2, 1)?>>일별보기</option>
				<option value="1" <?=checked($log_mode, 1, 1)?>>월별보기</option>
			</select>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="검색"></span>
		<span class="box_btn"><input type="button" value="초기화" onclick="location.href='?body=<?=$_GET['body']?>'"></span>
	</div>
</form>

<div class="box_title">
	<h2 class="title">연령별 / 성별 회원가입 통계</h2>
</div>
<table class="tbl_mini full">
	<caption class="hidden">연령별 / 성별 회원가입 통계</caption>
	<thead>
		<tr>
			<th scope="col" rowspan="2">구분</th>
			<th scope="col">총</th>
			<th scope="col">10대</th>
			<th scope="col">20대</th>
			<th scope="col">30대</th>
			<th scope="col">40대</th>
			<th scope="col">50대</th>
			<th scope="col">60대</th>
			<th scope="col">기타</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<th>남성</th>
			<td><?=nformat($birth_g_남)?></td>
			<td><?=nformat($birth_yg_10_남)?></td>
			<td><?=nformat($birth_yg_20_남)?></td>
			<td><?=nformat($birth_yg_30_남)?></td>
			<td><?=nformat($birth_yg_40_남)?></td>
			<td><?=nformat($birth_yg_50_남)?></td>
			<td><?=nformat($birth_yg_60_남)?></td>
			<td><?=nformat($birth_yg_etc_남)?></td>
		</tr>
		<tr>
			<th>여성</th>
			<td><?=nformat($birth_g_여)?></td>
			<td><?=nformat($birth_yg_10_여)?></td>
			<td><?=nformat($birth_yg_20_여)?></td>
			<td><?=nformat($birth_yg_30_여)?></td>
			<td><?=nformat($birth_yg_40_여)?></td>
			<td><?=nformat($birth_yg_50_여)?></td>
			<td><?=nformat($birth_yg_60_여)?></td>
			<td><?=nformat($birth_yg_etc_여)?></td>
		</tr>
		<tr>
			<th>기타</th>
			<td><?=nformat($birth_g_etc)?></td>
			<td><?=nformat($birth_yg_10_etc)?></td>
			<td><?=nformat($birth_yg_20_etc)?></td>
			<td><?=nformat($birth_yg_30_etc)?></td>
			<td><?=nformat($birth_yg_40_etc)?></td>
			<td><?=nformat($birth_yg_50_etc)?></td>
			<td><?=nformat($birth_yg_60_etc)?></td>
			<td><?=nformat($birth_yg_etc_etc)?></td>
		</tr>
		<tr>
			<th>합계</th>
			<td><?=nformat($total)?></td>
			<td><?=nformat($birth_y_10)?></td>
			<td><?=nformat($birth_y_20)?></td>
			<td><?=nformat($birth_y_30)?></td>
			<td><?=nformat($birth_y_40)?></td>
			<td><?=nformat($birth_y_50)?></td>
			<td><?=nformat($birth_y_60)?></td>
			<td><?=nformat($birth_yg_etc_남+$birth_yg_etc_여+$birth_yg_etc_etc)?></td>
		</tr>
	</tbody>
</table>
<div class="box_bottom">
	<ul class="list_msg left">
		<li>회원 나이는 현재 기준으로 집계됩니다.</li>
	</ul>
</div>

<div class="box_title">
	<h2 class="title">구매전환율 그래프</h2>
</div>
<div class="box_middle">
	<div id="statis_summary"></div>
</div>

<script type="text/javascript">
	var plot2 = $.jqplot('statis_summary', [[<?=$line2?>], [<?=$line3?>], [<?=$line4?>]], {
		stackSeries: true,
		series:[{renderer:$.jqplot.BarRenderer, label:'가입 회원'}, {label:'가입 후 구매 회원', disableStack:true}, {label:'가입 후 재접속 회원', disableStack:true}],
		seriesDefaults: {
			rendererOptions:{ highlightMouseOver:false}
		},
		seriesColors: ['#2fbf86', '#025aec', '#ff59b0'],
		axesDefaults: {
			tickRenderer: $.jqplot.CanvasAxisTickRenderer,
			tickOptions: {
				fontSize: '11px',
				fontFamily: '맑은 고딕',
				textColor: '#666'
			}
		},
		legend:{show:true, escapeHtml:true},
		axes: {
			xaxis: {
				renderer: $.jqplot.DateAxisRenderer,
				sortMergedLabels: false,
				tickInterval: '<?=$tickinterval?>',
				tickOptions:{
					formatString: "<?=$suffix?>",
					show: true
				}
			},
			yaxis: {
				min: 0,
				tickOptions:{
					formatString:"%'d 명",
					show: true
				},
				labelOptions: {
					fontSize: '20px'
				}
			}
		},
		highlighter : {
			show: true,
			sizeAdjust: 6,
			formatString: "<a %s /> %s"
		},
		cursor: {
			show: false
		}
	});
</script>