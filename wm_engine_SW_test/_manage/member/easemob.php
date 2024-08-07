<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  Easemob 채팅 플러그인 설정
	' +----------------------------------------------------------------------------------------------+*/

	if($cfg['use_easemob_plugin'] != 'Y') $cfg['use_easemob_plugin'] = 'N';
	if($cfg['easemob_btn_use'] != 'N') $cfg['easemob_btn_use'] = 'Y';
	if(!$cfg['use_easemob_lang']) $cfg['use_easemob_lang'] = 'CH';

?>
<form name="" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="config_code" value="easemob">
	<div class="box_title first">
		<h2 class="title">
			Easemob 채팅 설정&nbsp;
			<a href="https://www.easemob.com/" target="_blank"><img src="<?=$engine_url?>/_manage/image/shortcut2.gif"></a>
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
				<label><input type="radio" name="use_easemob_plugin" value="Y" <?=checked($cfg['use_easemob_plugin'], 'Y')?>>사용함</label>
				<label><input type="radio" name="use_easemob_plugin" value="N" <?=checked($cfg['use_easemob_plugin'], 'N')?>>사용안함</label>
			</td>
		</tr>
		<tr>
			<th scope="row">플러그인 아이디</th>
			<td>
				<input type="text" name="easemob_plugin_id" value="<?=$cfg['easemob_plugin_id']?>" class="input" size="50">
				<ul class="list_msg">
					<li>Easemob 관리자 상단의 Admin mode 이동후 Channels > Web > Embed widget 항목에서 취득할수 있습니다.</li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row">사용언어</th>
			<td>
				<label><input type="radio" name="use_easemob_lang" value="EN" <?=checked($cfg['use_easemob_lang'], 'EN')?>>영어</label>
				<label><input type="radio" name="use_easemob_lang" value="CH" <?=checked($cfg['use_easemob_lang'], 'CH')?>>중국어</label>
			</td>
		</tr>
		<tr>
			<th scope="row">자동 버튼 출력</th>
			<td>
				<label><input type="radio" name="easemob_btn_use" value="Y" <?=checked($cfg['easemob_btn_use'], 'Y')?>>출력함</label>
				<label><input type="radio" name="easemob_btn_use" value="N" <?=checked($cfg['easemob_btn_use'], 'N')?>>출력안함</label>
				<ul class="list_msg">
					<li>'출력함' 선택시 <strong class="p_color">PC화면에서</strong> 기본 Easemob 관리자에서 설정한 버튼이 우측 하단(기본설정)에 출력됩니다.</li>
					<li>배너나 아이콘 클릭시 채팅창이 출력되게 하시려면 버튼 '출력안함'으로 선택하신 후 자바스크립트 callEasemobim() 를 호출해 주세요.</li>
					<li><strong class="p_color">모바일 페이지에서 이용시</strong> 에도 자바스크립트 버튼을 이용해 호출하셔야 합니다.</li>
					<li>ex) &lt;a href="javascript:;" onclick="{{$easemob호출}}"&gt;{{$사용자배너1}}&lt;/a&gt;</li>
				</ul>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>