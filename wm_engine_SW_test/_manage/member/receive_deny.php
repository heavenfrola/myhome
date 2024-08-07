<?PHP

	$mail_case = 15;
	include $engine_dir.'/_engine/include/mail.lib.php';

	if(!$cfg['080_access_ip']) $cfg['080_access_ip'] = '211.171.255.60';

?>
<form name="" method="post" id="080_config" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="config_code" value="080_call">
	<div class="box_title first">
		<h2 class="title">
			080수신거부 설정&nbsp;
			<a href="http://redirect.wisa.co.kr/080service" target="_blank"><img src="<?=$engine_url?>/_manage/image/shortcut2.gif"></a>
		</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">080수신거부 설정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tr>
			<th>사용여부</th>
			<td>
				<label><input type="radio" name="use_080sms" value='Y' <?=checked($cfg['use_080sms'], 'Y')?>  /> 사용함</label>
				<label><input type="radio" name="use_080sms" value=''  <?=checked($cfg['use_080sms'],  '')?> /> 사용안함</label>
			</td>
		</tr>
		<tr>
			<th scope="row">080번호</th>
			<td>
				<input type="text" name="080_number" value="<?=inputText($cfg['080_number'])?>" class="input" size="15">
			</td>
		</tr>
		<tr>
			<th scope="row">080 수신거부 송신 아이피</th>
			<td>
				<input type="text" name="080_access_ip" value="<?=inputText($cfg['080_access_ip'])?>" class="input" size="15">
			</td>
		</tr>
		<tr>
			<th scope="row">수신URL</th>
			<td>
				<?=$root_url?>/main/exec.php?exec_file=member/sms_deny.exe.php
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>