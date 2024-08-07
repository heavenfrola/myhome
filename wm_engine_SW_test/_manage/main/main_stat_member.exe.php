<?PHP

	printAjaxHeader();


	/* +----------------------------------------------------------------------------------------------+
	' |  주간 방문자 현황
	' +----------------------------------------------------------------------------------------------+*/
	$line1 = $line2 = '';
	$last_week = strtotime('-6 days', strtotime(date('Y-m-d')));
	list($y, $m, $d) = explode('-', date('Y-m-d', $last_week));
	$tmp = $pdo->row("select no from $tbl[log_day] where yy='$y' and mm='$m' and dd='$d'");
	$res = $pdo->iterator("select yy, mm, dd, hit from $tbl[log_day] where no >= '$tmp'");
    foreach ($res as $data) {
		$date = sprintf('%02d.%02d', $data['mm'], $data['dd']);
		$a = strtotime("$data[yy]-$data[mm]-$data[dd]");
		$b = $a+86399;
		$join = $pdo->row("select count(*) from $tbl[member] where reg_date between $a and $b");
		$order = $pdo->row("select count(distinct member_no) from $tbl[order] where date1 between $a and $b and `stat` not in (11,31,33) and member_no > 0");

		if(!$data['hit']) $data['hit'] = 0;
		if(!$join) $join = 0;
		if(!$order) $order = 0;

		$line1 .= ",['$date', $data[hit]]";
		$line2 .= ",['$date', $join]";
		$line3 .= ",['$date', $order]";

		$graph1[$date] = array(
			'total' => $data['hit'],
			'join' => $join,
			'order' => $order
		);
	}

	$line1 = preg_replace('/^,/', '', $line1);
	$line2 = preg_replace('/^,/', '', $line2);
	$line3 = preg_replace('/^,/', '', $line3);
?>
		<caption class='caption'>
			<select onchange='mainStatChg(1)'>
				<option>주간매출현황</option>
				<option selected>주간방문자현황</option>
			</select>
		</caption>
		<tr>
			<td colspan='7'>
				<div id='statis_summary' style='height: 230px; margin: 0 5px;'>
					<script type='text/javascript'>
					$(document).ready(function(){
						var plot2 = $.jqplot('statis_summary', [[<?=$line1?>], [<?=$line2?>], [<?=$line3?>]], {
							series:[{renderer:$.jqplot.BarRenderer, label:'총접속', rendererOptions:{highlightMouseOver:false}}, {xaxis:'x2axis', yaxis:'y2axis', label:'가입회원'}, {xaxis:'x2axis', yaxis:'y2axis', label:'구매회원'}],
							seriesColors: ['#00aeef', '#025aec', '#ab03eb'],
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
									renderer: $.jqplot.CategoryAxisRenderer
								},
								yaxis: {
									min: 0,
									tickOptions:{
										formatString:"%'d",
										show: true
									}
								},
								x2axis: {
									renderer: $.jqplot.CategoryAxisRenderer,
									tickOptions:{
										show: false
									}
								},
								y2axis: {
									min: 0,
									autoscale: true,
									tickOptions: {
										showGridline: false
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
					});
					</script>
				</div>
			</td>
		</tr>
		<tr>
			<th>구분</th>
			<th>총접속</th>
			<th>가입회원</th>
			<th>구매회원</th>
		</tr>
		<?for($i = 6; $i >= 0; $i--) {$date = date('m.d', strtotime("-$i days"));?>
		<tr class='number'>
			<td><?=$date?></td>
			<td><?=nformat($graph1[$date]['total'])?></td>
			<td><?=nformat($graph1[$date]['join'])?></td>
			<td><?=nformat($graph1[$date]['order'])?></td>
		</tr>
		<?}?>