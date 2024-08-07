<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  채널 채팅 플러그인 설정
	' +----------------------------------------------------------------------------------------------+*/

	if($cfg['use_happytalk'] != 'Y') $cfg['use_happytalk'] = 'N';
	if($cfg['happytalk_button'] != 'B') $cfg['happytalk_button'] = 'A';

?>
<form name="" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" enctype="multipart/form-data">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="config_code" value="happytalk">
	<div class="box_title first">
		<h2 class="title">
			카카오 상담톡 설정&nbsp;
			<a href="http://happytalk.io" target="_blank"><img src="<?=$engine_url?>/_manage/image/shortcut2.gif"></a>
		</h2>
	</div>
	<table class="tbl_row">
		<colgroup>
			<col style="width:15%;">
			<col>
		</colgroup>
		<tr>
			<th scope="row">서비스 신청</th>
			<td><a href="#" onclick="goMywisa('?body=wing@main'); return false;"><span class="box_btn_s gray"><input type="button" value="카카오 상담톡 서비스 신청"></span></a></td>
		</tr>
		<tr>
			<th scope="row">사용여부</th>
			<td>
				<label><input type="radio" name="use_happytalk" value="Y" <?=checked($cfg['use_happytalk'], 'Y')?>>사용함</label>
				<label><input type="radio" name="use_happytalk" value="N" <?=checked($cfg['use_happytalk'], 'N')?>>사용안함</label>
			</td>
		</tr>
		<tr>
			<th scope="row">카카오톡 채널 아이디</th>
			<td>
				<input type="text" name="alimtalk_id" value="<?=$cfg['alimtalk_id']?>" readonly class="input input_data" size="50">
				<ul class="list_info">
					<li>윙스토어 > 부가서비스 > 부가서비스 현황 페이지 내 '카카오톡 채널 아이디 등록'을 통해 채널 아이디를 등록해 주세요. <a href="#" onclick="goMywisa('?body=wing@main'); return false;">바로가기</a></li>
					<li>부가서비스 현황 페이지 내 등록되어 있는 채널 아이디가 자동으로 연동됩니다.</li>
					<li>채널 아이디가 없으면 [카카오톡 채널 관리자]에서 신청해 주세요. <a href="https://center-pf.kakao.com/" target="_blank">바로가기</a></li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row">해피톡 사이트 아이디</th>
			<td>
				<input type="text" name="happytalk_site_id" value="<?=$cfg['happytalk_site_id']?>" class="input" size="50">
				<div class="list_info">
					<p>해피톡 사이트 아이디는 [해피톡 관리자 페이지] 서비스설정 > 채팅 버튼 달기에서 확인할 수 있습니다.</p>
				</div>
			</td>
		</tr>
		<tr>
			<th scope="row">상담분류 코드</th>
			<td>
				대분류 대표코드 : <input type="text" name="happytalk_big_code" value="<?=$cfg['happytalk_big_code']?>" class="input" size="15"> 
				중분류 대표코드 : <input type="text" name="happytalk_mid_code" value="<?=$cfg['happytalk_mid_code']?>" class="input" size="15">
				<ul class="list_info">
					<li>고객이 상담진행 시 유입되는 대표분류 코드를 지정해주세요. 없으면 '0000'을 입력해주세요.</li>
					<li>해피톡 상담분류는 [해피톡 관리자 페이지] 서비스설정 > 상담 분류 관리에서 확인할 수 있습니다.</li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row">채팅 상담버튼</th>
			<td>
				<label><input type="radio" name="happytalk_button" value="A" <?=checked($cfg['happytalk_button'], 'A')?>>기본</label>
				<label><input type="radio" name="happytalk_button" value="B" <?=checked($cfg['happytalk_button'], 'B')?>>사용자</label>
				<input type="file" name="upfile1" class="input"> <span class="explain">(권장사이즈 : 가로 116px X 세로 116px)</span> 
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>