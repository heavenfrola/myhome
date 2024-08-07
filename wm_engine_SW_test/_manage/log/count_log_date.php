<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  방문자수통계 (요일별)
	' +----------------------------------------------------------------------------------------------+*/
	$lmode = addslashes($_GET['lmode']);
	$date = addslashes($_GET['date']);
	$mode = addslashes($_GET['mode']);

	$r=array();

	$max=$total=0;
	$sql="select `week`, sum(`hit`) as `hit` from `$tbl[log_day]` group by `week`";
	$res = $pdo->iterator($sql);
    foreach ($res as $data) {
		$num=$data[week];
		$r[$num]=$data[hit];
		if($r[$num]>$max) $max=$r[$num];
		$total+=$r[$num];
	}

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

	$week = array(일, 월, 화, 수, 목, 금, 토);

?>
<div class="box_title first">요일별 방문자 분석</div>
<div id="controlTab" class="none_margin">
	<ul class="tabs">
		<li id="ctab_1" onclick="location.href='?body=<?=$_GET['body']?>&lmode=<?=$lmode?>&date=<?=$date?>&mode=height';" <? if($mode == 'height') { ?>class="selected"<? } ?>>세로보기</li>
		<li id="ctab_2" onclick="location.href='?body=<?=$_GET['body']?>&lmode=<?=$lmode?>&date=<?=$date?>&mode=width';" <? if($mode == 'width') { ?>class="selected"<? } ?>>가로보기</li>
	</ul>
	<div class="context">
		<?if($mode == 'height') {?>
		<div class="box_middle2 left">요일별 접속통계</div>
		<div class="graphFrm box_bottom">
			<table>
				<caption class="hidden">요일별 접속통계</caption>
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
							for($i = 0; $i <= 6; $i++) {
								$name = "$ii 시";
								$result = $r[$i];
								$height = (147 / 100) * @ceil(($result / $max) * 100);
								switch($i){
									case '0' : $color = 'red'; break;
									case '6' : $color = 'black'; break;
									default : $color = ''; break;
								}
						?>
						<td rowspan="5">
							<dl class="grp big <?=$color?>" onmouseover="onStatView(this,event,true)" onmousemove="onStatView(this,event,true)" onmouseout="onStatView(this,event,false)">
								<dt><span><?=$name?></span></dt>
								<dd style="height:<?=$height?>px"><span><?=number_format($result)?></span></dd>
							</dl>
						</td>
						<?}?>
					</tr>
				</tbody>
				<tfoot>
					<tr>
						<th></th>
						<?for($i = 0; $i <= 6; $i++) {?>
						<td style="font-size:11px"><?=$week[$i]?></td>
						<?}?>
					</tr>
				</tfoot>
			</table>
		</div>
		<?} else {?>
		<div class="box_middle2 left">요일별 접속통계</div>
		<div class="graphFrm2 box_bottom">
			<table>
				<caption class="hidden">요일별 접속통계</caption>
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
					for($i = 0; $i <= 6; $i++) {
						$name = "$ii 시";
						$result = $r[$i];
						$width = @ceil(($result / $max) * 100)-1;
						switch($i){
							case '0' : $color = 'red'; break;
							case '6' : $color = 'black'; break;
							default : $color = ''; break;
						}
				?>
				<tr>
					<th><?=$week[$i]?></td>
					<td colspan="8">
						<dl class="grp big <?=$color?>">
							<?if($width > 0) {?><dd style="width:<?=$width?>%"><?=number_format($result)?></dd><?}?>
							<dt>&nbsp;</dt>
						</dl>
					</td>
				</tr>
				<?}?>
				</tbody>
			</table>
		</div>
		<?}?>
	</div>
</div>