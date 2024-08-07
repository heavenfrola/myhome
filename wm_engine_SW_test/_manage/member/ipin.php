<?PHP

	if(!$cfg['ipin_use']) $cfg['ipin_use'] = 'N';
	if(!$cfg['ipin_checkplus_use']) $cfg['ipin_checkplus_use'] = 'N';

?>
<div class="box_full">
	<p>아이핀과 체크플러스 두 개를 동시에 사용하실 경우 반드시 <a href="?body=design@editor&type=&edit_pg=5%2F8" target="_blank" class="p_color2">약관동의 페이지</a>에 {{$실명인증방법선택}} 코드를 삽입하셔야 합니다.</p>
</div>
<form method="post" action="?" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="config_code" value="ipin_config">
	<div class="box_title">
		<h2 class="title">아이핀 설정 <span class="explain">NICE 평가정보 아이핀 서비스 계정 신청 후 계정정보를 등록해 주세요.</span></h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">아이핀 설정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tr>
			<th scope="row">서비스 사용</th>
			<td>
				<label class="p_cursor"><input type="radio" name="ipin_use" value="Y" <?=checked($cfg['ipin_use'], 'Y')?>> 사용함</label>
				<label class="p_cursor"><input type="radio" name="ipin_use" value="N" <?=checked($cfg['ipin_use'], 'N')?>> 사용안함</label>
			</td>
		</tr>
		<tr>
			<th scope="row">아이디</th>
			<td><input type="text" name="ipin_id" class="input" value="<?=$cfg['ipin_id']?>"></td>
		</tr>
		<tr>
			<th scope="row">패스워드</th>
			<td><input type="password" name="ipin_pw" class="input" size="21" value="<?=$cfg['ipin_pw']?>"></td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
	<div class="box_title">
		<h2 class="title">아이핀 체크플러스(휴대폰 인증) 설정 <span class="explain">NICE 평가정보 안심본인인증 서비스(체크플러스) 계정 신청 후 계정정보를 등록해 주세요.</span></h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">아이핀 체크플러스(휴대폰 인증) 설정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tr>
			<th scope="row">서비스 사용</th>
			<td>
				<label class="p_cursor"><input type="radio" name="ipin_checkplus_use" value="Y" <?=checked($cfg['ipin_checkplus_use'], 'Y')?>> 사용함</label>
				<label class="p_cursor"><input type="radio" name="ipin_checkplus_use" value="N" <?=checked($cfg['ipin_checkplus_use'], 'N')?>> 사용안함</label>
			</td>
		</tr>
		<tr>
			<th scope="row">아이디</th>
			<td><input type="text" name="ipin_checkplus_id" class="input" value="<?=$cfg['ipin_checkplus_id']?>"></td>
		</tr>
		<tr>
			<th scope="row">패스워드</th>
			<td><input type="password" name="ipin_checkplus_pw" class="input" size="21" value="<?=$cfg['ipin_checkplus_pw']?>"></td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>