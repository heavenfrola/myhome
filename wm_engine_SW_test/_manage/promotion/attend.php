<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  출석체크 설정
	' +----------------------------------------------------------------------------------------------+*/

	if($cfg['use_attend'] != 'Y') {
		include 'attend_new.php';
		return;
	}

	$attendMP_str = ($cfg['attendMP']=='M') ? "적립금":"포인트" ;
	if($cfg['use_attend'] != 'N') $cfg['use_attend'] = 'Y';

?>
<form name="" method="post" action="./" target="hidden<?=$now?>" onSubmit="return checkAttend(this)">
	<input type="hidden" name="body" value="config@config.exe">
	<div class="box_title first">
		<h2 class="title">출석체크 설정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">출석체크 설정</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">출석체크 사용여부</th>
			<td>
				<label class="p_cursor"><input type="radio" name="use_attend" id="use_attend" value="Y" <?=checked($cfg['use_attend'], 'Y')?>> 사용함</label>
				<label class="p_cursor"><input type="radio" name="use_attend" id="use_attend" value="N" <?=checked($cfg['use_attend'], 'N')?>> 사용안함</label>
			</td>
		</tr>
		<tr>
			<th scope="row">출석적용 적립설정</th>
			<td>
				<label class="p_cursor"><input type="radio" name="attendMP" id="attendMP" value="M" <?=checked($cfg['attendMP'],'M')?>> 적립금</label>
				<label class="p_cursor"><input type="radio" name="attendMP" id="attendMP" value="P" <?=checked($cfg['attendMP'],'P')?>> 포인트</label>
			</td>
		</tr>
		<tr>
			<th scope="row">적립금 적용 출석횟수</th>
			<td>
				<input type="text" name="attendNum" value="<?=$cfg['attendNum']?>" class="input" size="10"> 회
				<label class="p_cursor"><input type="radio" name="attendType" id="attendType" value="1" <?=checked($cfg['attendType'],1)?>> 누적</label>
				<label class="p_cursor"><input type="radio" name="attendType" id="attendType" value="2" <?=checked($cfg['attendType'],2)?>> 연속</label>
				<p class="explain">적립방식 변경 시 '연속'일 경우 변경일 기준으로 초기화됩니다.</p>
			</td>
		</tr>
		<tr>
			<th scope="row">출석 <?=$attendMP_str?></th>
			<td>
				<input type="text" name="attendMilage" value="<?=$cfg['attendMilage']?>" class="input" size="10"> 원
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>

<script language="JavaScript">
	function checkAttend(f){
		if(!checkBlank(f.attendNum, '출석횟수를 입력해주세요.')) return false;
		if(!checkBlank(f.attendMilage, '적립금을 입력해주세요.')) return false;
	}
</script>