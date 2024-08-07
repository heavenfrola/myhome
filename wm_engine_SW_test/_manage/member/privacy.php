<?PHP

	$mail_case = 15;
	include $engine_dir.'/_engine/include/mail.lib.php';

	if(!$cfg['use_advinfo']) $cfg['use_advinfo'] = 'N';
	if(!$cfg['advinfo_type']) $cfg['advinfo_type'] = 'email';
	if(!$cfg['use_edit_receive']) $cfg['use_edit_receive'] = 'N';

	$use_advinfo = explode('@', trim($cfg['email_checked'], '@'));
	$use_advinfo = (in_array('15', $use_advinfo)) ? 'Y' : 'N';

?>
<form method="post" action="?" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="member@privacy.exe">
	<input type="hidden" name="email_checked" value="<?=$email_checked?>">
	<input type="hidden" name="config_code" value="advertising_receive">
	<div class="box_title first">
		<h2 class="title">광고성정보 수신동의 알림</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">광고성정보 수신동의 알림</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tr>
			<th scope="row">수신동의 알림 사용</th>
			<td>
				<label class="p_cursor"><input type="radio" name="use_advinfo" value="Y" <?=checked($use_advinfo, 'Y')?>> 사용함</label>
				<label class="p_cursor"><input type="radio" name="use_advinfo" value="N" <?=checked($use_advinfo, 'N')?>> 사용안함</label>
				<ul class="list_info">
					<li>수신동의 후 매 2년마다 회원에게 확인 안내 메일을 발송해야 합니다.</li>
					<li>해당 서비스 사용 이전 가입한 회원의 경우 SMS 및 이메일 동의/거부 일자가 가입일로 처리됩니다.</li>
					<li>서비스 사용 중 대표 도메인을 변경할 경우 '확인'버튼을 다시 눌러 변경된 도메인 정보를 송신해야 합니다.</li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row">알림 방법</th>
			<td>
				<!--
				<label class="p_cursor"><input type="radio" name="advinfo_type" value="sms" <?=checked($cfg['advinfo_type'], 'sms')?>> SMS</label>
				-->
				<label class="p_cursor"><input type="radio" name="advinfo_type" value="email" <?=checked($cfg['advinfo_type'], 'email')?>> 이메일</label>
				<span class="box_btn_s"><a href="?body=design@email_msg&mail_case=15" target="_blank">디자인편집</a></span>
				<ul class="list_info">
					<li>이메일은 매일 새벽 4시부터 순차적으로 발송됩니다.</li>
					<li>이메일 발송 후 해당 회원은 자동으로 수신동의날짜가 메일 발송일로 재설정됩니다.</li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row">알림 주기</th>
			<td>
				<select name="advinfo_day">
					<option value="">매일</option>
					<?for($i = 1; $i <= 28; $i++) {?>
					<option value="<?=$i?>" <?=checked($cfg['advinfo_day'], $i, true)?>>매월 <?=$i?>일</option>
					<?}?>
				</select>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>

<form method="post" action="?" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="config@config.exe">
	<div class="box_title">
		<h2 class="title">광고성정보 수신여부 변경 알림</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">광고성정보 수신여부 변경 알림</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tr>
			<th scope="row">사용여부</th>
			<td>
				<label class="p_cursor"><input type="radio" name="use_edit_receive" value="Y" <?=checked($cfg['use_edit_receive'], 'Y')?>> 사용함</label>
				<label class="p_cursor"><input type="radio" name="use_edit_receive" value="N" <?=checked($cfg['use_edit_receive'], 'N')?>> 사용안함</label>
				<div class="list_info">
					<p>관리자 내 고객CRM 정보 변경 및 이메일/080수신거부에 따른 광고성정보 수신여부 변경 사실을 해당 회원에게 안내합니다.</p>
				</div>
			</td>
		</tr>
		<tr>
			<th scope="row">알림 방법</th>
			<td>
				<label class="p_cursor"><input type="radio" name="edit_receive_type" value="sms" <?=checked($cfg['edit_receive_type'], 'sms')?>> SMS</label>
				<label class="p_cursor"><input type="radio" name="edit_receive_type" value="email" <?=checked($cfg['edit_receive_type'], 'email')?>> 이메일</label>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>