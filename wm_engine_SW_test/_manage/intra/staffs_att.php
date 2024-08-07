<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  사원근태통계
	' +----------------------------------------------------------------------------------------------+*/

	adminCheck(2);
	$start_date=$_GET['start_date'] ? addslashes($_GET['start_date']) : date("Y-m-d", $now);
	$finish_date=$_GET['finish_date'] ? addslashes($_GET['finish_date']) : date("Y-m-d", $now);
	$_team=getIntraTeam();

	include_once $engine_dir."/_manage/main/main_box.php";

	$mno = numberOnly($_GET['mno']);
	if($mno) $mname = $pdo->row("select `name` from `$tbl[mng]` where `no`='$mno' limit 1");

?>
<?if($mno){?>
<div class="box_title first">
	<h2 class="title"><?=$mname?>님의 월간 근태현황</h2>
</div>
<?
	$db="my_attend";
	include $engine_dir."/_manage/intra/calendar_inc.php";
?>
<?return;}?>

<form name="" method="get" action="<?=$_SERVER['PHP_SELF']?>" id="search">
	<input type="hidden" name="body" value="<?=$body?>">
	<div class="box_title first">
		<h2 class="title">사원근태통계</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">사원근태통계</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">기간별</th>
			<td>
				<input type="text" name="start_date" value="<?=$start_date?>" size="10" class="input datepicker"> ~ <input type="text" name="finish_date" value="<?=$finish_date?>" size="10" class="input datepicker">
				<span class="box_btn_s blue"><input type="submit" value="조회"></span>
			</td>
		</tr>
	</table>
</form>
<?if($cfg[intra_day_check] == "Y") {?>
<form name="reFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="return remodCk(this);">
	<input type="hidden" name="body" value="intra@staffs_att.exe">
	<div class="box_title">
		<h2 class="title">출근 정정</h2>
	</div>
	<table class="tbl_col">
		<caption class="hidden">출근 정정</caption>
		<colgroup>
			<col style="width:70px">
			<col style="width:130px">
			<col style="width:120px">
			<col style="width:120px">
			<col>
		</colgroup>
		<thead>
			<tr>
				<th scope="col">선택</th>
				<th scope="col">지각자</th>
				<th scope="col">날짜</th>
				<th scope="col">출근시간</th>
				<th scope="col">출근사유</th>
			</tr>
		</thead>
		<tbody>
			<?php
				$rec=0;
				$w=" and `date`>='$start_date' and `date`<='$finish_date'";
				if($mod && $mod_num){
					$w .= " and `re_modi`='Y' and `member_no`='$mod_num'";
				}
				if($late && $late_num){
					$w .= " and `member_no`='$late_num'";
				}
				$sql = $pdo->iterator("select * from `$tbl[intra_day_check]` where `late`='Y' $w order by `no`");
                foreach ($sql as $data) {
					$mname = $pdo->row("select `name` from `$tbl[mng]` where `no`='$data[member_no]' limit 1");
			?>
			<tr>
				<td>
					<?
					if($data[re_modi] == "Y") {
						$rec++;
						echo "<input type='checkbox' name='mod[]' value='$data[no]'>";
					} else {
						echo "&nbsp;";
					}
					?>
				</td>
				<td><?=$mname?></td>
				<td>[<?=$data['date']?>]</td>
				<td class="p_color"><?=date("H:i:s", $data[stime])?></td>
				<td class="left"><?=$data[late_detail]?></td>
			</tr>
			<?
				}
				if($rec){
			?>
			<tr>
				<td colspan="5" class="explain">지각정정요청을 한 경우 체크박스가 표시됩니다</td>
			</tr>
			<?}?>
		</tbody>
	</table>
	<?if($rec) {?>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="선택사항 정정"></span>
	</div>
	<?}?>
</form>
<?}?>
<div class="box_title">
	<h2 class="title">출근 내역</h2>
</div>
<table class="tbl_col">
	<caption class="hidden">출근 내역</caption>
	<?if($cfg[intra_day_check] == "Y") {?>
	<thead>
		<tr>
			<th scope="col">이름</th>
			<th scope="col">팀</th>
			<th scope="col">직급</th>
			<th scope="col">지각정정요청</th>
			<th scope="col">총 지각횟수</th>
			<th scope="col">총 개근일수</th>
			<th scope="col">총 출근일수</th>
		</tr>
	</thead>
	<tbody>
		<?php
			$sql = $pdo->iterator("select `no`, `name`, `team1`, `team2`, `position` from `$tbl[mng]` where `level`=3 order by `name`");
            foreach ($sql as $data) {
				$data[team]=$data[team2] ? $data[team2] : $data[team1];
				$teamname=($data[team]) ? $_team[$data[team]][name] : "-";
				$position=($data[position]) ? $data[position] : "-";

				$w=" and `member_no`='$data[no]' and `date`>='$start_date' and `date`<='$finish_date'";
				$day_ck_num=$pdo->row("select count(*) from `$tbl[intra_day_check]` where 1 $w");
				$day_late_num=$pdo->row("select count(*) from `$tbl[intra_day_check]` where 1 $w and `late`='Y'");
				$day_modi_num=$pdo->row("select count(*) from `$tbl[intra_day_check]` where 1 $w and `re_modi`='Y'");
				$day_perfect_num=$day_ck_num-$day_late_num;
				?>
				<tr class="tcol2" onMouseOver="this.style.backgroundColor='#ffffcc'" onMouseOut="this.style.backgroundColor=''">
					<td><a href="./?body=<?=$body?>&mno=<?=$data[no]?>"><?=$data[name]?></a></td>
					<td><?=$teamname?></td>
					<td><?=$position?></td>
					<td><?=($day_modi_num > 0) ? "<a href=\"./?body=$body&start_date=$start_date&finish_date=$finish_date&mod=1&mod_num=$data[no]\"><span  style=\"color:#FF3300\"><b>".$day_modi_num."</b></span></a>" : "0";?></td>
					<td><?=($day_late_num > 0) ? "<a href=\"./?body=$body&start_date=$start_date&finish_date=$finish_date&late=1&late_num=$data[no]\"><span  style=\"color:#FF3300\"><b>".$day_modi_num."</b></span></a>" : "0";?></td>
					<td><?=$day_perfect_num?></td>
					<td><b><?=$day_ck_num?></b></td>
				</tr>
				<?}
			}else{
			?>
		<tr>
			<td colspan="7">
			현재 출근체크기능이 사용안함으로 설정되어 있습니다. <a href="./?body=intra@att_ck">변경하기</a>
			</td>
		</tr>
		<?}?>
	</tbody>
</table>

<script type="text/javascript">
	function remodCk(f){
		if(!checkCB(f['mod[]'], '정정하실 요청을 선택해주세요')) return false;
		if(!confirm('체크를 하신 사원의 지각 상태를 정상으로 수정합니다  ')) return false;
	}
</script>