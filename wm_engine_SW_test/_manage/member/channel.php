<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  채널 채팅 플러그인 설정
	' +----------------------------------------------------------------------------------------------+*/

	if($cfg['use_channel_plugin'] != 'Y') $cfg['use_channel_plugin'] = 'N';
	$cfg['use_channel_plugin'] = trim($cfg['use_channel_plugin']);

?>
<form name="" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="member@channel.exe">
	<input type="hidden" name="config_code" value="channel">
	<div class="box_title first">
		<h2 class="title">
			Channel 채팅 설정&nbsp;
			<a href="http://channel.io/" target="_blank"><img src="<?=$engine_url?>/_manage/image/shortcut2.gif"></a>
		</h2>
	</div>
	<table class="tbl_row">
		<colgroup>
			<col style="width:15%;">
			<col>
		</colgroup>
		<tr>
			<th scope="row">사용여부</th>
			<td>
				<label><input type="radio" name="use_channel_plugin" value="Y" <?=checked($cfg['use_channel_plugin'], 'Y')?>>사용함</label>
				<label><input type="radio" name="use_channel_plugin" value="N" <?=checked($cfg['use_channel_plugin'], 'N')?>>사용안함</label>
			</td>
		</tr>
		<tr>
			<th scope="row">Plugin Key</th>
			<td>
				<input type="text" name="channel_plugin_id" value="<?=$cfg['channel_plugin_id']?>" class="input" size="50">
				<ul class="list_msg">
					<li>채널데스크의 설정아이콘 클릭 후 플러그인 설정 메뉴의 Plugin Key를 입력해주세요.</li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row">accessSecret</th>
			<td>
				<input type="text" name="channel_accessSecret" value="<?=$cfg['channel_accessSecret']?>" class="input" size="50">
				<ul class="list_msg">
					<li>채널데스크의 설정아이콘 클릭 후 플러그인 설정 메뉴의 accessSecret를 입력해주세요.</li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row">관리자모드 접속</th>
			<td>
				<span class="box_btn_s"><a href="https://desk.channel.io/" target="_blank">접속하기</a></span>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>