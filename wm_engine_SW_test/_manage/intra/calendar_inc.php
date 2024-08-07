<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  달력
	' +----------------------------------------------------------------------------------------------+*/

	$year = numberOnly($_GET['year']);
	$month = numberOnly($_GET['month']);
	$day = numberOnly($_GET['day']);
	$mno = numberOnly($_GET['mno']);
    if (!isset($db)) $db = addslashes(trim($_GET['db']));

	if(!$year) $year=date("Y", $now);
	if(!$month) $month=date("n", $now);
	if(!$day) $day=date("j", $now);
	$totalDays=date("t",mktime(0,0,0,$month,1,$year));
	$dayOfWeek=date("w",mktime(0,0,0,$month,1,$year));

	$prevYear=$year;
	$prevMonth=$month-1;
	if($prevMonth < 1){ $prevYear--; $prevMonth=12; }

	$nextYear=$year;
	$nextMonth=$month+1;
	if($nextMonth > 12){ $nextYear++; $nextMonth=1; }

	$mm=$month;
	if($mm < 10) $mm="0".$mm;

	if($db == "main_schedule"){
		$sql = $pdo->iterator("select * from `$tbl[intra_schedule]` where `date` like '".$year."-".$mm."%' limit 31");
        foreach ($sql as $data) {
			$_schedule[$data['date']]=($data[font_color]) ? "<font color=\"$data[font_color]\">".$data[content]."</a>" : $data[content];
		}
	}

?>
<div id="intra_calendar">
	<div class="box_title first center">
		<?if($db == "main_schedule" && $admin[level] < 3){?>
		<div class="right_area2">
			<span class="box_btn blue"><input type="button" value="작성하기" onclick="location.href='./?body=intra@schedule'"></span>
		</div>
		<?}?>
		<a href="javascript:;" onclick="getCalContent('&year=<?=$prevYear?>&month=<?=$prevMonth?>&mno=<?=$mno?>&db=<?=$db?>');"><img src="<?=$engine_url?>/_manage/image/intra/ic_arrow2_l.gif" alt="이전 달"></a>
		<strong style="font-size:14px;"><?=$year;?>년 <?=$month;?>월</strong>
		<a href="javascript:;" onclick="getCalContent('&year=<?=$nextYear?>&month=<?=$nextMonth?>&mno=<?=$mno?>&db=<?=$db?>');"><img src="<?=$engine_url?>/_manage/image/intra/ic_arrow2_r.gif" alt="다음 달"></a>
	</div>
	<table class="tbl_col calender">
		<caption class="hidden">내월간근태</caption>
		<colgroup>
			<col style="width:12.5%">
			<col style="width:15%">
			<col style="width:15%">
			<col style="width:15%">
			<col style="width:15%">
			<col style="width:15%">
			<col style="width:12.5%">
		</colgroup>
		<thead>
			<tr>
				<?
				for($ii=0; $ii<7; $ii++){
				   if($ii == 0) $weekc = 'sun';
				   elseif($ii == 6) $weekc = 'sat';
				   else $weekc = '';
				?>
				<th scope="col" class="<?=$weekc?>"><?=$yoil[$ii]?>요일</th>
				<?}?>
			</tr>
		</thead>
		<tbody>
			<?php
				$start = 1 - date('w', strtotime("$year-$month-01"));
				for($jj=$start; $jj<=$totalDays; $jj++,$ii++){
					$dd=($jj < 10) ? "0".$jj : $jj;
					if($ii%7 == 0) $fontC=$_yoil_color[sun];
					if($ii%7 == 6) $fontC=$_yoil_color[sat];
					$class = (date("Ymd") == $year.$mm.$dd) ? 'today' : '';
					$pd = ($jj > 0) ? $jj : '';
			?>
				<td class="<?=$class?> left">
					<dl>
						<dt><?=$pd?></dt>
						<?
							if($db == "main_schedule"){ // 일정
								$date=$year."-".$mm."-".$dd;
								if($_schedule[$date]){
									echo "<dd>".$_schedule[$date]."</dd>";
								}
							} elseif($db == "my_attend"){ // 근태
								if($cfg[intra_day_check] == "Y"){
									$data = $pdo->assoc("select `no`, `stime`, `etime`, `late` from `$tbl[intra_day_check]` where `member_no`='$mno' and `date`='".$year."-".$mm."-".$dd."' limit 1");

									if($data[no]){
										$data[etime]=$data[etime] ? date("H:i", $data[etime]) : "-";
										if($data[late] == "Y") echo "<center><span class=\"sred\" style=\"width:95%; background-color:#fce3fd\"><b>지각</b></span></center>";
										echo "<dd><label>출근 /</label> ".date("H:i", $data[stime])."</dd>\n";
										echo "<dd><label>퇴근 /</label> ".$data[etime]."</dd>";
									}
								}
							}
						?>
					</dl>
				</td>
			<?
					if($ii%7 == 6){
					  echo "</tr>";
					  if($jj != $totalDays) echo "<tr>";
					}
				}
				for($ii; $ii%7 != 0; $ii++){
					echo '<td>&nbsp;</td>';
				}
			?>
			</tr>
		</tbody>
	</table>
</div>

<script language="JavaScript">
	function getCalContent(addq){
		if(!addq) addq='';
		$.get('./?body=intra@calendar_inc.exe'+addq, function(r) {
			$('#intra_calendar').html(r);
		});
	}
</script>