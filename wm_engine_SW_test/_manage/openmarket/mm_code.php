<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  기타코드설정내역
	' +----------------------------------------------------------------------------------------------+*/

	// 2008-09-09 : 마이마케팅 기타코드 설정 - Han
    $data = array();
	$res = $pdo->query("select * from `{$tbl['default']}` where `code` in ('ysm_accountid','google_conversion_id','auction_clickid_id', 'kakao_url_code')");
	foreach($res as $set) {
		$data[$set['code']] = $set['value'];
	}

    // 구 공통 페이지 스크립트
    $file_dir = $root_dir.'/'.$dir['upload'].'/'.$dir['compare'].'/common_script.php';
    if (file_exists($file_dir) == true) $common_script = trim(file_get_contents($file_dir));

?>
<form name="ogFrm" method="post" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="openmarket@mm_login.exe">
	<input type="hidden" name="type" value="save_site_id">
	<div class="box_title first">
		<h2 class="title">구매전환 스크립트 설정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">구매전환 스크립트 설정</caption>
		<colgroup>
			<col style="width:15%">
			<col style="width:15%">
			<col>
		</colgroup>
		<tr>
			<th scope="rowgroup" rowspan="2" class="line_r">네이버 클릭초이스</th>
			<th scope="row">웹로그 분석 업체</th>
			<td>
				<label class="p_cursor"><input type="radio" name="cchoice_env_type" value="AceCounter" <?=checked($cfg['cchoice_env_type'], 'AceCounter')?>> AceCounter</label>
				<span style="color:#ccc;">
				<label class="p_cursor"><input disabled type="radio" name="cchoice_env_type" value="logger" <?=checked($cfg['cchoice_env_type'], 'logger')?>> logger</label>
				<label class="p_cursor"><input disabled type="radio" name="cchoice_env_type" value="WiseLog" <?=checked($cfg['cchoice_env_type'], 'WiseLog')?>> WiseLog</label>
				<label class="p_cursor"><input disabled type="radio" name="cchoice_env_type" value="WSOS" <?=checked($cfg['cchoice_env_type'], 'WSOS')?>> WSOS</label>
				</span>
			</td>
		</tr>
		<tr>
			<th scope="row">분석 코드</th>
			<td>
				<ul>
					<li>계정코드 <input type="text" name="cchoice_env_code" value="<?=$cfg['cchoice_env_code']?>" class="input" size="20" maxlength="20" style="ime-mode:disabled; "></li>
					<li>서버코드 <input type="text" name="cchoice_env_JV" value="<?=$cfg['cchoice_env_JV']?>" class="input" size="20" maxlength="20" style="ime-mode:disabled; "></li>
					<li>서버주소 <input type="text" name="cchoice_env_TGUL" value="<?=$cfg['cchoice_env_TGUL']?>" class="input" size="20" maxlength="20" style="ime-mode:disabled; "></li>
				</ul>
				<p class="explain icon p_color2" style="margin-top:5px;">
					클릭초이스 관리자에서 웹로그 분석 신청 후 설정 이메일의 첨부파일로 스크립트가 전달됩니다.
					<a href="javascript:;" onclick="mmCodeInfo('code_info3');">분석코드 찾는법</a>
				</p>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
	<!-- 레이어 -->
	<div id="popupContent" class="layerPop" style="display:none; position:absolute; top:100px; width:600px; padding:10px; border:2px solid #000; background:#fff;">
		<div id="header">
			<h1 id="logo"><img src="<?=$engine_url?>/_manage/image/wisa.gif"></h1>
			<div id="mngTab_pop">코드 안내</div>
		</div>
		<div id="headerLine"></div>
		<div id="popupContentArea">
			<div id="code_info1" class="hidden">
				<p class="p_color2">* 오버추어에 로그인 후 해당 페이지를 클릭하여 빨간색으로 표시된 부분을 찾아서 넣어주시면 됩니다.</p>
				<img src="<?=$engine_url?>/_manage/image/promotion/mm_code_info1.gif">
			</div>
			<div id="code_info2" class="hidden">
				<p class="p_color2">* 구글 로그인 후 해당 페이지를 클릭하여 빨간색으로 표시된 부분을 찾아서 넣어주시면 됩니다.</p>
				<img src="<?=$engine_url?>/_manage/image/promotion/mm_code_info2.gif">
			</div>
			<div id="code_info3" class="hidden">
				<ul class="list_msg">
					<li>네이버로부터 다운받은 첨부파일의 full-script.txt 파일을 열어 코드를 복사해 붙여넣기 해 주시면 됩니다.</li>
					<li>순서대로 <strong>계정코드</strong>, <strong>서버코드</strong>, <strong>서버주소란</strong>에 입력해 주세요.</li>
				</ul>
				<img src="<?=$engine_url?>/_manage/image/promotion/mm_code_info3.png">
			</div>
			<div class="center">
				<span class="box_btn gray"><input type="button" value="닫기" onclick="layTgl2('popupContent')"></span>
			</div>
		</div>
	</div>
	<!-- //레이어 -->
</form>
<?php if ($common_script) { ?>
<form name="scriptFrm" method="post" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="openmarket@mm_login.exe">
	<input type="hidden" name="type" value="common_script">
	<div class="box_title">
		<h2 class="title">공통 페이지 스크립트 삽입부분 편집</h2>
	</div>
	<div class="box_middle">
		<p class="p_color2 left">편집내용에 <u>&lt;script&gt; &lt;/script&gt;선언 부분을 포함하여 입력해주시기 바랍니다.</p>
		<textarea class="txta" name="common_script" style="height:300px;"><?=$common_script?></textarea>
	</div>
	<div class="box_bottom top_line">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<?php } ?>

<script type="text/javascript">
	function mmCodeInfo(id) {
		$('[id^=code_info]').each(function(){
			this.className = (this.id == id) ? '' : 'hidden';
		});

		$('#popupContent').show().css('left', ((screen.availWidth/2)-250)).draggable({'cursor':'pointer'});
	}
</script>