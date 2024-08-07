<?php

	/* +----------------------------------------------------------------------------------------------+
	' |  방문경로분석 - 도메인/링크URL별
	' +----------------------------------------------------------------------------------------------+*/

	$stype = addslashes($_GET['stype']);
	$isDirect = addslashes($_GET['direct']); // 즐겨찾기, 직접접속 제외 여부. y = 포함, '' = 제외
	$page = numberOnly(addslashes($_GET['page']));

	$list_tab_qry = makeQueryString(true);
	$list_tab_qry_noDirect = makeQueryString(true, 'direct');

	if (!$stype) {
		$stype = 'log_server';
	}

	if ($stype == 'log_server') {
		$title = '접속 서버별';
	} else {
		$title = '링크된 페이지별';
	}

	if (!$tbl[$stype]) msg('', '?body=main@main');

	include_once $engine_dir.'/_engine/include/paging.php';
	if ($page <= 1) $page = 1;
	$row = 20;
	$block = 10;

	$sql = "select * from `$tbl[$stype]` order by hit desc";
	$NumTotalRec = $pdo->row("select count(*) from `$tbl[$stype]`");
	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$QueryString = "&body=$body&stype=$stype";
	if ($isDirect) $QueryString .= "&direct=".$isDirect;
	$PagingInstance->addQueryString($QueryString);
	$PagingResult = $PagingInstance->result($pg_dsn);
	$sql .= $PagingResult['LimitQuery'];

	$pg_res = $PagingResult['PageLink'];
	$res = $pdo->iterator($sql);

	$r_chart = array();
	foreach ($res as $data) {
		if ($data['log'] == 'http://' || !$data['log']) {
			if ($isDirect == '') continue;
			$data['log'] = '즐겨찾기, 주소창에 직접입력';
		}
		$data['name'] = $data['log'];
		$r_chart[$data['name']] = $data['hit'];
	}
	$_sub_title = ($_GET['stype'] == 'log_referer') ? '링크Url별' : '도메인별';
?>
<script src="<?=$engine_url?>/_engine/common/chart/chart.js"></script>
<script src="<?=$engine_url?>/_engine/common/chart/chartjs-plugin-datalabels.js"></script>
<script src="<?=$engine_url?>/_engine/common/chart/chartjs-function.js"></script>

<div class="box_title first">링크URL별 방문자 분석</div>
<div class="box_middle left">
	<label class="p_cursor"><input type="checkbox" id="no_direct" name="no_direct" value="y" onclick="directCheck('<?=$isDirect?>');" <?=checked('', $isDirect)?>> '즐겨찾기, 주소창에 직접입력' 제외</label>
</div>
<div class="box_middle4">
	<div style="position:relative;margin-bottom:50px;">
		<canvas id="chartUrl" style="width:100%;height:500px;"></canvas>
	</div>
</div>
<div class="box_bottom"><?=$pg_res?></div>

<script type="text/javascript">
	// 즐겨찾기, 직접접속 제외 여부
	function directCheck(v) {
		if (v == '') {
			location.href = '<?=$list_tab_qry?>&direct=y';
		} else {
			location.href = '<?=$list_tab_qry_noDirect?>';
		}
	}

	let data_values = {
		labels: <?=urldecode(json_encode(array_keys($r_chart)))?>,
		data: <?=json_encode(array_values($r_chart))?>,
	}

	let chartUrl = chartMake('chartUrl', {
		plugins: [ChartDataLabels, showTooltipOnLabelsHover('y'), goLinkOnLabelClick()],
		type: 'bar',
		data: {
			labels: data_values.labels,
			datasets: [
				{
					type: 'bar',
					label: '방문자수',
					data: data_values.data,
					datalabels: {
						display: true,
						align: 'end',
						anchor: 'end'
					},
					borderColor: '#759EDF',
					backgroundColor: '#759EDF',
					borderRadius: 30,
					maxBarThickness: 8,
				},
			],
		},
		options: {
			responseive: true,
			maintainAspectRatio: false,
			indexAxis: 'y',
			interaction: {
				intersect: false,
				mode: 'y',
			},
			plugins: {
				legend: {
					display: false,
				},
				datalabels: {
					color: '#999999',
					font: {
						weight: 'bold'
					},
					padding: {
						right: 20,
					},
					formatter: function (value, context) {
						return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
					},
				},
				tooltip: {
					enabled: false,
					external: externaltooltip,
					position: 'custom2',
					callbacks: {
						title: function (tooltipItems) {
							return tooltipItems[0].label;
						},
						label: function (tooltipItems) {
							return [tooltipItems.dataset.label, tooltipItems.formattedValue + '명'];
						},
					},
				},
			},
			scales: {
				x: {
					display: true,
					ticks: {
						color: '#999999',
						callback: function (v, i) {
							return this.getLabelForValue(v) + ' 명';
						},
					}
				},
				y: {
					display: true,
					ticks: {
						color: '#999999',
						callback: function (v, i) {
							let val = this.getLabelForValue(v).substr(0,30) + '...';
							return val;
						}
					},
				}
			}
		},
	});
</script>