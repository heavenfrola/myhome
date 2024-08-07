<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  출석체크설정
	' +----------------------------------------------------------------------------------------------+*/

	adminCheck(2);

	$cfg[intra_day_check]=$cfg[intra_day_check] ? $cfg[intra_day_check] : "N";

	$_time=$_day=array("00");
	for($ii=1; $ii<=23; $ii++){
		if($ii < 10) $ii="0".$ii;
		$_time[$ii]=$ii;
	}
	for($ii=1; $ii<=59; $ii++){
		if($ii < 10) $ii="0".$ii;
		$_day[$ii]=$ii;
	}

	$cfg[intra_day_check_start]=$cfg[intra_day_check_start] ? $cfg[intra_day_check_start] : "09:00";
	$cfg[intra_day_check_end]=$cfg[intra_day_check_end] ? $cfg[intra_day_check_end] : "18:00";
	list($intra_day_check_st, $intra_day_check_sm)=explode(":", $cfg[intra_day_check_start]);
	list($intra_day_check_et, $intra_day_check_em)=explode(":", $cfg[intra_day_check_end]);

?>
<form name="" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="config_code" value="intra">
	<div class="box_title">
		<h2 class="title">출석체크 설정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">출석체크 설정</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">출석체크기능</th>
			<td>
				<input type="radio" name="intra_day_check" id="intra_day_check2" value="Y" <?=checked($cfg[intra_day_check], "Y")?>> <label for="intra_day_check2" class="p_cursor">사용함</label> &nbsp;
				<input type="radio" name="intra_day_check" id="intra_day_check1" value="N" <?=checked($cfg[intra_day_check], "N")?>> <label for="intra_day_check1" class="p_cursor">사용안함</label>
			</td>
		</tr>
		<tr>
			<th scope="row">출근시간설정</td>
			<td>
				<?=selectArray($_time, "intra_day_check_st", 1, "", $intra_day_check_st);?>시
				<?=selectArray($_day, "intra_day_check_sm", 1, "", $intra_day_check_sm);?>분
			</td>
		</tr>
		<tr>
			<th scope="row">퇴근시간설정</td>
			<td>
				<?=selectArray($_time, "intra_day_check_et", 1, "", $intra_day_check_et);?>시
				<?=selectArray($_day, "intra_day_check_em", 1, "", $intra_day_check_em);?>분
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>