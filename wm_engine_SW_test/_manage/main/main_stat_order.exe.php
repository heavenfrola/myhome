<?PHP

	printAjaxHeader();

	$type = $_GET['type'];

	/* +----------------------------------------------------------------------------------------------+
	' |  주간매출현황
	' +----------------------------------------------------------------------------------------------+*/
	$graph1 = array();
	$last_week = strtotime('-6 days', strtotime(date('Y-m-d')));
	$res = $pdo->iterator("select from_unixtime(date1, '%m.%d') as date, date1,
			sum(pay_prc) as total, count(*) as total_cnt,
			sum(if(stat between 2 and 5, pay_prc, 0)) as pay, sum(if(stat between 2 and 5, 1, 0)) as pay_cnt,
			sum(if(stat in (13,15,17,19),pay_prc,repay_prc)) as cancel, sum(if(stat in (13,15,17,19),1,0)) as cancel_cnt
		from $tbl[order] where stat not in (11,31) and date1 > $last_week group by date");
    foreach ($res as $data) {
		list($_yy, $_mm, $_dd) = explode('-', date('Y-m-d', $data['date1']));
		$day_hit = $pdo->row("select hit from $tbl[log_day] where yy='$_yy' and mm='$_mm' and dd='$_dd'");
		if($day_hit == 0 || $data['total_cnt'] == 0) $data['order_per'] = 0;
		else $data['order_per'] = round($data['total_cnt'] / $day_hit, 4)*100;

		$graph1[$data['date']] = $data;
	}

	$line1 = $line2 = $line3 = '';
	for($i = 6; $i >= 0; $i--) {
		$date = date('m.d', strtotime("-$i days"));
		$total = $graph1[$date]['total']/10000;
		$cancel = $graph1[$date]['cancel']/10000;
		$pay = $graph1[$date]['pay']/10000;
		if(!$total) $total = 0;
		if(!$cancel) $cancel = 0;
		if(!$pay) $pay = 0;
		$line1 .= ",['$date', $total]";
		$line2 .= ",['$date', $pay]";
		$line3 .= ",['$date', $cancel]";
	}

	$line1 = preg_replace('/^,/', '', $line1);
	$line2 = preg_replace('/^,/', '', $line2);
	$line3 = preg_replace('/^,/', '', $line3);
?>
		<caption class='caption'>
			<select onchange='mainStatChg(2)'>
				<option selected>주간매출현황</option>
				<option>주간방문자현황</option>
			</select>
		</caption>
		<tr>
			<td colspan='7'>
				<div id='statis_summary' style='height: 230px; margin: 0 5px;'>
					<script type='text/javascript'>
					$(document).ready(function(){
						var plot2 = $.jqplot('statis_summary', [[<?=$line1?>], [<?=$line2?>], [<?=$line3?>]], {
							series:[{renderer:$.jqplot.BarRenderer, label:'주문금액', rendererOptions:{highlightMouseOver:false}}, {label:'결제금액'}, {label:'취소금액'}],
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
										formatString:"%'d만",
										show: true
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
			<th rowspan='2'>구분</th>
			<th colspan='3'>주문</th>
			<th colspan='2'>결제</th>
			<th colspan='2'>취소/반품</th>
		</tr>
		<tr>
			<th>금액</th>
			<th>건수</th>
			<th>구매율</th>
			<th>금액</th>
			<th>건수</th>
			<th>금액</th>
			<th>건수</th>
		</tr>
		<?for($i = 6; $i >= 0; $i--) {$date = date('m.d', strtotime("-$i days"));?>
		<tr class='number'>
			<td><?=$date?></td>
			<td><?=nformat($graph1[$date]['total'])?></td>
			<td><?=nformat($graph1[$date]['total_cnt'])?></td>
			<td><?=$graph1[$date]['order_per']?>%</td>
			<td><?=nformat($graph1[$date]['pay'])?></td>
			<td><?=nformat($graph1[$date]['pay_cnt'])?></td>
			<td><?=nformat($graph1[$date]['cancel'])?></td>
			<td><?=nformat($graph1[$date]['cancel_cnt'])?></td>
		</tr>
		<?}?>