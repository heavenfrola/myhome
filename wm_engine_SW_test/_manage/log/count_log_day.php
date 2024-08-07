<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  방문자수통계 (오늘)
	' +----------------------------------------------------------------------------------------------+*/
	$month = addslashes($month);
	$year = addslashes($year);
	$day = addslashes($day);
	$mode = addslashes($mode);
    $y = (int) $_GET['y'];
    $m = (int) $_GET['m'];
    $d = (int) $_GET['d'];

	if($_inc[0]=='income') {
		$m = $month;
		$y = $year;
		$d = $day;
	}
	if((!$y || !$m || !$d) && $body != 'log@count_log_excel.exe') {
		$y=date("Y",$now);
		$m=date("n",$now);
		$d=date("j",$now);
	}
	$min_year = $pdo->row("select min(yy) from $tbl[log_day]");

	$query="";
	checkBlank($y,"필수값(년)을 입력해주세요.");
	checkBlank($m,"필수값(월)을 입력해주세요.");
	checkBlank($d,"필수값(일)을 입력해주세요.");
	$w="`yy`='$y'";
	$w.=" and `mm`='$m'";
	$w.=" and `dd`='$d'";

	$title="$y 년 $m 월 $d 일";

	$r=array();
	$max=0;
	$data = $pdo->assoc("select * from `$tbl[log_day]` where $w");
	for($ii=0; $ii<=23; $ii++) {
		$r[$ii]=$data['h'.$ii];
		if($r[$ii]>$max) $max=$r[$ii];
	}
	$total=$data[hit];

	if($m<10 && strlen($m) == '1') $m="0".$m;
	if($d<10) $d="0".$d;

	$mode = ($mode) ? $mode : 'height';
	if($mode == 'height') $key1=3;
	else $key1=7;
	$key2=$key1+1;

	$digit = strlen($max);
	if($digit > $key1) {
		$pow = pow(10, ($digit -$key1));
		$amax = ceil($max / $pow) * $pow;
	} else {
		$amax = $max;
	}

	$div = $amax / $key2;
	$amax0 = number_format($amax);
	$ii=0;
	for($i=$key1; $i >= 0; $i--) {

		$ii++;
		${"amax".$ii} = number_format(ceil($div*$i));
	}

	$xls_query = makeQueryString('body');
	if($body == 'log@count_log_excel.exe') return;

?>
<div class="box_title first">방문자 분석
	<div class="btns">
		<span class="box_btn_s icon excel"><input type="button" value="엑셀저장" onclick="location.href='?body=log@count_log_excel.exe&<?=$xls_query?>&logday=Y'"></span>
	</div>
</div>
<div id="controlTab" class="none_margin">
	<ul class="tabs">
		<li id="ctab_1" onclick="location.href='?body=<?=$_GET['body']?>&lmode=<?=$lmode?>&date=<?=$date?>&mode=height';" <? if($mode == 'height') { ?>class="selected"<? } ?>>세로보기</li>
		<li id="ctab_2" onclick="location.href='?body=<?=$_GET['body']?>&lmode=<?=$lmode?>&date=<?=$date?>&mode=width';" <? if($mode == 'width') { ?>class="selected"<? } ?>>가로보기</li>
	</ul>
	<div class="context">
		<form method="get" action="./index.php" name="count_log" class="box_middle2 left">
			<input type="hidden" name="body" value="log@count_log" />
			<input type="hidden" name="mode" value="<?=$mode?>" />
			<select name="y">
				<option value="">전체</option>
				<?for($i = date('Y'); $i >= $min_year; $i--) {?>
				<option value="<?=$i?>" <?=checked($y, $i, true)?>><?=$i?> 년</option>
				<?}?>
			</select>
			<select name="m">
				<option value="">전체</option>
				<?for($i = 1; $i <= 12; $i++) {?>
				<option value="<?=$i?>" <?=checked($m, $i, true)?>><?=$i?> 월</option>
				<?}?>
			</select>
			<select name="d">
				<option value="">전체</option>
				<?for($i = 1; $i <= 31; $i++) {?>
				<option value="<?=$i?>" <?=checked($d, $i, true)?>><?=$i?> 일</option>
				<?}?>
			</select>
			<span class="box_btn_s blue"><input type="submit" value="검색"></span>
			<span class="box_btn_s blue"><input type="button" value="오늘" onclick="oneday();"></span>
		</form>
		<?if($mode == 'height') { ?>
		<div class="box_middle2 left"><?=$title?> 접속통계</div>
		<div class="graphFrm box_bottom">
			<table>
				<caption class="hidden"><?=$title?> 접속통계</caption>
				<colgroup>
					<col style="width:60px">
				</colgroup>
				<tbody>
					<tr>
						<th>
							<dl>
								<dt>단위 : 명</dt>
								<dd><?=$amax0?></dd>
								<dd><?=$amax1?></dd>
								<dd><?=$amax2?></dd>
								<dd><?=$amax3?></dd>
							</dl>
						</th>
						<?
							for($i = 0; $i <= 23; $i++) {
							$name = "$i 시";
							$result = $r[$i];
							$height = (147 / 100) * @ceil(($result / $max) * 100);
						?>
						<td rowspan="5">
							<a href="?body=log@count_log_list&exec=search&ext=&y1=<?=$y?>&m1=<?=$m?>&d1=<?=$d?>&h1=<?=$i?>&y2=<?=$y?>&m2=<?=$m?>&d2=<?=$d?>&h2=<?=$i?>&search_type=referer">
							<dl class="grp" onmouseover="onStatView(this,event,true)" onmousemove="onStatView(this,event,true)" onmouseout="onStatView(this,event,false)">
								<dt><span><?=$name?></span></dt>
								<dd style="height:<?=$height?>px"><span><?=number_format($result)?></span></dd>
							</dl>
							</a>
						</td>
						<?}?>
					</tr>
				</tbody>
				<tfoot>
					<tr>
						<th></th>
						<?for($i = 0; $i <= 23; $i++) {?>
						<td><?=addZero($i,2)?></td>
						<?}?>
					</tr>
				</tfoot>
			</table>
		</div>
		<?} else {?>
		<div class="box_middle2 left"><?=$title?> 접속통계</div>
		<div class="graphFrm2 box_bottom">
			<table>
				<caption class="hidden"><?=$title?> 접속통계</caption>
				<colgroup>
					<col style="width:4%">
					<col style="width:12%">
					<col style="width:12%">
					<col style="width:12%">
					<col style="width:12%">
					<col style="width:12%">
					<col style="width:12%">
					<col style="width:12%">
					<col style="width:12%">
				</colgroup>
				<thead>
					<tr>
						<th>단위 : 명</th>
						<td><?=$amax7?></td>
						<td><?=$amax6?></td>
						<td><?=$amax5?></td>
						<td><?=$amax4?></td>
						<td><?=$amax3?></td>
						<td><?=$amax2?></td>
						<td><?=$amax1?></td>
						<td><?=$amax0?></td>
					</tr>
				</thead>
				<tbody>
					<?
						for($i = 0; $i <= 23; $i++) {
							$link="";
							$name = "$i 명";
							$result = $r[$i];
							$width = @ceil(($result / $max) * 100)-1;
					?>
					<tr>
						<th><?=addZero($i,2)?></td>
						<td colspan="8">
							<a href="?body=log@count_log_list&exec=search&ext=&y1=<?=$y?>&m1=<?=$m?>&d1=<?=$d?>&y2=<?=$y?>&m2=<?=$m?>&d2=<?=$d?>&search_type=referer">
							<dl class="grp">
								<?if($width > 0) {?><dd style="width:<?=$width?>%"><?=number_format($result)?></dd><?}?>
								<dt>&nbsp;</dt>
							</dl>
							</a>
						</td>
					</tr>
					<?}?>
				</tbody>
			</table>
		</div>
		<?}?>
	</div>
</div>
<script type='text/javascript'>
	function oneday(){
		f=document.count_log;
		f.y.value=<?=$oneday[0]?>;
		f.m.value=<?=$oneday[1]?>;
		f.d.value=<?=$oneday[2]?>;
		f.submit();
	}
</script>