<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  방문자수통계 (시간별)
	' +----------------------------------------------------------------------------------------------+*/

	$date = addslashes($_GET['date']);
	$mode = addslashes($_GET['mode']);
	if(!$date) $date = date('Y-m-d');

	list($y, $m, $d) = explode('-', $date);

	$m = number_format($m);
	$d = number_format($d);

	$query="";
	checkBlank($y,"필수값(1)을 입력해주세요.");
	checkBlank($m,"필수값(2)을 입력해주세요.");
	checkBlank($d,"필수값(3)을 입력해주세요.");
	$w="`yy`='$y'";
	$w.=" and `mm`='$m'";
	$w.=" and `dd`='$d'";

	$date="$y-$m-$d";

	$r=array();
	$max=0;
	$data = $pdo->assoc("select * from `$tbl[log_day]` where $w");
	for($ii=0; $ii<=23; $ii++) {
		$r[$ii]=$data['h'.$ii];
		if($r[$ii]>$max) $max=$r[$ii];
	}
	$total=$data[hit];

	if($m<10) $m="0".$m;
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

?>
<div id="controlTab" class="none_margin">
	<ul class="tabs">
		<li id="ctab_1" onclick="location.href='?body=<?=$_GET['body']?>&date=<?=$date?>&mode=height';" <? if($mode == 'height') { ?>class="selected"<? } ?>>세로보기</li>
		<li id="ctab_2" onclick="location.href='?body=<?=$_GET['body']?>&date=<?=$date?>&mode=width';" <? if($mode == 'width') { ?>class="selected"<? } ?>>가로보기</li>
	</ul>
	<div class="context">
		<?if($mode == 'height') {?>
		<div class="box_middle2 left">
			<input type="text" id="log_date" size="10" value="<?=$date?>" class="input datepicker">
			<span class="box_btn_s blue"><input type="button" value="검색" onclick="location.href='?body=<?=$_GET['body']?>&mode=<?=$mode?>&date='+$('#log_date').val();"></span>
		</div>
		<div class="graphFrm box_bottom">
			<table>
				<caption class="hidden">세로 그래프</caption>
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
							<a href="?body=log@count_log_list">
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
		<div class="box_middle2 left">
			<input type="text" id="log_date" size="10" value="<?=$date?>" class="input datepicker">
			<span class="box_btn_s blue"><input type="button" value="검색" onclick="location.href='?body=<?=$_GET['body']?>&mode=<?=$mode?>&date='+$('#log_date').val();"></span>
		</div>
		<div class="graphFrm2 box_bottom">
			<table>
				<caption class="hidden">가로그래프</caption>
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
							$name = "$i 시";
							$result = $r[$i];
							$width=@ceil(($result / $max) * 100)-1;
					?>
					<tr>
						<th><?=addZero($i,2)?></td>
						<td colspan="8">
							<a href="?body=log@count_log_list">
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