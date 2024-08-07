<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  매출요약
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir.'/_manage/income/income.inc.php';

	$yy = numberOnly($_GET['yy']);
	$mm = numberOnly($_GET['mm']);
	$dd = numberOnly($_GET['dd']);

	$min_year = $pdo->row("select from_unixtime(min(date1), '%Y') as miny from $tbl[order] where stat not in (11,31) and (x_order_id in ('', 'checkout', 'talkstore') or x_order_id is null)");
	if(!$min_year) $min_year = date('Y');

	if(!$yy && !$mm && !$dd) {
		$yy = date('Y');
		$mm = date('m');
		$dd = date('d');
	}

	// 월별
	$ys = strtotime(date("$yy-01-01"));
	$yf = strtotime('+1 years', $ys)-1;
	$ytotal = 0;
	$ydata = array();
	$res = $pdo->iterator("select from_unixtime(date1, '%m') as month, sum(pay_prc+point_use) as total_prc from $tbl[order] where date1 between $ys and $yf and stat between 2 and 5 and (x_order_id in ('', 'checkout', 'talkstore') or x_order_id is null) group by month");
    foreach ($res as $data) {
		$ytotal += $data['total_prc'];
		$ydata[number_format($data['month'])] = $data['total_prc'];
	}

	// 일별
	$ys = strtotime(date("$yy-$mm-01"));
	$yf = strtotime('+1 months', $ys)-1;
	$mtotal = 0;
	$mdata = array();
	$res = $pdo->iterator("select from_unixtime(date1, '%d') as day, sum(pay_prc+point_use) as total_prc from $tbl[order] where date1 between $ys and $yf and stat between 2 and 5 and (x_order_id in ('', 'checkout', 'talkstore') or x_order_id is null) group by day");
    foreach ($res as $data) {
		$mtotal += $data['total_prc'];
		$mdata[number_format($data['day'])] = $data['total_prc'];
	}

	// 시간별
	if($dd) {
		$ys = strtotime(date("$yy-$mm-$dd"));
		$yf = strtotime('+1 days', $ys)-1;
	} else {
		$ys = strtotime(date("$yy-$mm-01"));
		$yf = strtotime('+1 months', $ys)-1;
	}
	$dtotal = 0;
	$ddata = array();
	$res = $pdo->iterator("select from_unixtime(date1, '%H') as hour, sum(pay_prc+point_use) as total_prc from $tbl[order] where date1 between $ys and $yf and stat between 2 and 5 and (x_order_id in ('', 'checkout', 'talkstore') or x_order_id is null) group by hour");
    foreach ($res as $data) {
		$dtotal += $data['total_prc'];
		$ddata[number_format($data['hour'])] = $data['total_prc'];
	}

?>
<div class="box_title first">
	<h2 class="title">매출요약 (월별, 일별, 시간별 매출현황 항목을 클릭하시면 매출집계를 보실 수 있습니다.)</h2>
</div>
<div class="box_middle income_basic">
	<div class="box" style="width:22%;">
		<div class="search_select">
			월별 주문금액
			<select id="yy">
				<?for($i = $min_year; $i <= date('Y'); $i++){?>
				<option value="<?=$i?>" <?=checked($i, $yy, 1)?>><?=$i?>년</option>
				<?}?>
			</select>
			<span class="box_btn_s blue"><input type="button" value="검색" onclick="setStatDate(null, '<?=$mm?>', '<?=$dd?>')"></span>
		</div>
		<table class="tbl_mini">
			<colgroup>
				<col style="width:30%">
				<col>
			</colgroup>
			<?for($i = 1; $i <= 12; $i++) {?>
			<tr>
				<th><a href="#" onclick="setIncomBasic(<?=$yy?>, <?=$i?>); return false;"><?=$i?>월</a></th>
				<td class="setincom p_cursor" onclick="setIncomBasic(<?=$yy?>, <?=$i?>)"><?=nformat($ydata[$i])?></td>
			</tr>
			<?}?>
			<tr>
				<th>계</th>
				<td><?=nformat($ytotal)?></td>
			</tr>
		</table>
	</div>
	<div class="box" style="width:39%;">
		<div class="search_select">
			일별 주문금액
			<select id="mm">
				<?for($i = 1; $i <= 12; $i++){?>
				<option value="<?=$i?>" <?=checked($i, $mm, 1)?>><?=$i?>월</option>
				<?}?>
			</select>
			<span class="box_btn_s blue"><input type="button" value="검색" onclick="setStatDate('<?=$yy?>', null, '<?=$dd?>')"></span>
		</div>
		<table class="tbl_mini">
			<colgroup>
				<col style="width:20%">
				<col style="width:30%">
				<col style="width:20%">
				<col style="width:30%">
			</colgroup>
			<?php
            $weekday_to_korean = array('Mon' => '월', 'Tue' => '화', 'Wed' => '수', 'Thu' => '목', 'Fri' => '금', 'Sat' => '토', 'Sun' => '일', );
            for ($i = 1; $i <= 16; $i++) {
                $weekday_print = date('D', strtotime($yy.'-'.$mm.'-'.$i));
                $weekday_print_r = date('D', strtotime($yy.'-'.$mm.'-'.($i+16)));
                $printKoreanColor = '';
                $printKoreanColor_r = '';
                if ($weekday_print == 'Sun') $printKoreanColor = 'color:#DF7593;';
                if ($weekday_print == 'Sat') $printKoreanColor = 'color:#759EDF;';
                if ($weekday_print_r == 'Sun') $printKoreanColor_r = 'color:#DF7593;';
                if ($weekday_print_r == 'Sat') $printKoreanColor_r = 'color:#759EDF;';
            ?>
			<tr>
				<th class="<?=strtolower($weekday_print)?> setincom p_cursor" onclick="setIncomBasic(<?=$yy?>, <?=$mm?>, <?=$i?>)"><?=$i?>일 (<span style="<?=$printKoreanColor?>"><?=$weekday_to_korean[$weekday_print]?></span>)</th>
				<td class="setincom p_cursor" onclick="setIncomBasic(<?=$yy?>, <?=$mm?>, <?=$i?>)"><?=nformat($mdata[$i])?></td>
				<?if($i == 16) {?>
				<td colspan="2"></td>
				<?} else {?>
                <th class="setincom p_cursor" onclick="setIncomBasic(<?=$yy?>, <?=$mm?>, <?=($i+16)?>)"><?=($i+16)?>일 (<span style="<?=$printKoreanColor_r?>"><?=$weekday_to_korean[$weekday_print_r]?></span>)</th>
				<td class="setincom p_cursor" onclick="setIncomBasic(<?=$yy?>, <?=$mm?>, <?=($i+16)?>)"><?=nformat($mdata[($i+16)])?></td>
				<?}?>
			</tr>
			<?}?>
		</table>
	</div>
	<div class="box" style="width:39%;">
		<div class="search_select">
			시간별 주문금액
			<select id="dd">
				<?for($i = 1; $i <= 31; $i++){?>
				<option value="<?=$i?>" <?=checked($i, $dd, 1)?>><?=$i?>일</option>
				<?}?>
			</select>
			<span class="box_btn_s blue"><input type="button" value="검색" onclick="setStatDate('<?=$yy?>', '<?=$mm?>', null)"></span>
		</div>
		<table class="tbl_mini">
			<colgroup>
				<col style="width:20%">
				<col style="width:30%">
				<col style="width:20%">
				<col style="width:30%">
			</colgroup>
			<?for($i = 0; $i <= 11; $i++) {?>
			<tr>
				<th><?=$i?>시</th>
				<td><?=nformat($ddata[$i])?></td>
				<?if($i == 16) {?>
				<td colspan="2"></td>
				<?} else {?>
				<th><?=($i+12)?>시</th>
				<td><?=nformat($ddata[($i+12)])?></td>
				<?}?>
			</tr>
			<?}?>
		</table>
	</div>
</div>
<div class="box_bottom top_line">
	<p class="explain left icon">매출요약은 실결제금액 기준으로 제공됩니다.</p>
</div>
<div id="statSummary">
	<?include $engine_dir."/_manage/income/income_graph.exe.php";?>
</div>

<script type="text/javascript">
	function setStatDate(y, m, d) {
		if(!y) y = $('#yy').val();
		if(!m) m = $('#mm').val();
		if(!d) d = $('#dd').val();

		location.href = '?body=income@income_basic&yy='+y+'&mm='+m+'&dd='+d;
	}

	function setIncomBasic(y, m, d) {
		if(!m) m = '';
		if(!d) d = '';
		location.href = '?body=income@income_basic&yy='+y+'&mm='+m+'&dd='+d;
	}

	$('.setincom').bind({
		'mouseover' : function() {
			$(this).addClass('over');
		},
		'mouseout' : function() {
			$(this).removeClass('over');
		}
	});
</script>