<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  생일자 자동쿠폰 설정
	' +----------------------------------------------------------------------------------------------+*/

	$wec_acc = new weagleEyeClient($_we, 'account');
	$result = $wec_acc->call('getbirthCpn');

	$cpn_use = $result[0]->cpn_use[0] == 'Y' ? 'Y' : 'N';
	$cpn_time = numberOnly($result[0]->cpn_time[0]);
	$use_check = $pdo->row("select use_check from $tbl[sms_case] where `case`=16") == 'Y' ? 'Y' : 'N';

	if(!$cfg['auto_birth_cpn_type']) {
		$cfg['auto_birth_cpn_type'] = 1;
		$cfg['auto_birth_cpn_date'] = 0;
	}

	${'auto_birth_cpn_date'.$cfg['auto_birth_cpn_type']} = $cfg['auto_birth_cpn_date'];

?>
<form method="post" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="promotion@coupon_birth.exe">
	<div class="box_title first">
		<h2 class="title">생일 자동쿠폰 설정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">생일 자동쿠폰 설정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tr>
			<th scope="row">사용여부</th>
			<td>
				<label class="p_cursor"><input type="radio" name="auto_birtn_cpn_use" value="Y" <?=checked($cpn_use, 'Y')?>> 사용함</label><br>
				<label class="p_cursor"><input type="radio" name="auto_birtn_cpn_use" value="N" <?=checked($cpn_use, 'N')?>> 사용안함</label>
			</td>
		</tr>
		<tr>
			<th scope="row">발급일자</th>
			<td>
				<label class="p_cursor"><input type="radio" name="auto_birth_cpn_type" value="1" <?=checked($cfg['auto_birth_cpn_type'], 1)?>> 생일</label>
				<input type="text" name="auto_birth_cpn_date1" value="<?=$auto_birth_cpn_date1?>" class="input" size="3"> 일 전
				<span class="desc1">(0 입력시 생일 당일 발송됩니다.)</span><br>
				<label class="p_cursor"><input type="radio" name="auto_birth_cpn_type" value="2" <?=checked($cfg['auto_birth_cpn_type'], 2)?>> 매월</label>
				<input type="text" name="auto_birth_cpn_date2" value="<?=$auto_birth_cpn_date2?>" class="input" size="3"> 일
			</td>
		</tr>
		<tr>
			<th scope="row">발급시간</th>
			<td>
				<select name="auto_birth_cpn_time">
				<?for($i = 0; $i <= 23; $i++) { $selected = $cpn_time == $i ? 'selected' : '';?>
				<option value="<?=$i?>" <?=$selected?>><?=date('A h', strtotime("2012-06-14 $i:00:00"))?> 시</option>
				<?}?>
				</select> 에 일괄 발송
			</td>
		</tr>
		<tr>
			<th scope="row">문자발송</th>
			<td>
				<label class="p_cursor"><input type="radio" name="auto_birtn_cpn_sms" value="Y" <?=checked($use_check,'Y')?>> 쿠폰 발급과 동시에 문자로 안내</label> <span class="box_btn_s"><a href="?body=member@sms_config" target="_blank">문자내용 편집하기</a></span><br>
				<label class="p_cursor"><input type="radio" name="auto_birtn_cpn_sms" value="N" <?=checked($use_check,'N')?>> 사용안함</label>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>