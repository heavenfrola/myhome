<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  SNS 서비스 설정
	' +----------------------------------------------------------------------------------------------+*/

	if(!$cfg['kakaolink_use']) $cfg['kakaolink_use'] = "N";
	if(!$cfg['kakaostory_use']) $cfg['kakaostory_use'] = "N";
    if ($scfg->comp('kakao_url_code') == true && $scfg->comp('kakao_sns_id') == true && ($cfg['kakao_login_use'] == "Y" ||  $cfg['kakao_login_use'] == "S")) {
        $cfg['kakao_url_code'] = $cfg['kakao_sns_id'];
    }

?>
<form name="" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
<input type="hidden" name="body" value="config@config.exe">
	<div class="box_title first">
		<h2 class="title">카카오링크 사용 설정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">카카오링크 사용 설정</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">앱키(JavaScript 키)</th>
			<td>
				<input type="text" name="kakao_url_code" value="<?=$cfg['kakao_url_code']?>" class="input" size="50">
				<div class="list_info">
					<p>Kakao Developers에서 JavaScript 키를 확인 후 입력하세요. <a href="https://developers.kakao.com/" target="_blank">바로가기</a></p>
				</div>
			</td>
		</tr>
		<tr>
			<th scope="row">카카오톡 사용여부</th>
			<td>
				<label>
					<input type="radio" name="kakaolink_use" value='Y' <?=checked($cfg['kakaolink_use'], 'Y')?>/> 사용함
				</label>
				<label><input type="radio" name="kakaolink_use" value='N' <?=checked($cfg['kakaolink_use'],  'N')?>/> 사용안함</label>
			</td>
		</tr>
		<tr>
			<th scope="row">카카오스토리 사용여부</th>
			<td>
				<label>
					<input type="radio" name="kakaostory_use" value='Y' <?=checked($cfg['kakaostory_use'], 'Y')?>/> 사용함
				</label>
				<label><input type="radio" name="kakaostory_use" value='N'  <?=checked($cfg['kakaostory_use'],  'N')?>/> 사용안함</label>
			</td>
		</tr>
	</table>
	<div class="box_middle2 left">
		<div class="summary_sns">
			<p class="title">JavaScript 키 발급 안내</p>
			<ol>
				<li>1) Kakao Developers(<a href="https://developers.kakao.com/" target="_blank">https://developers.kakao.com/</a>) 접속 후 카카오 계정으로 로그인합니다.(카카오 계정이 없는 경우 회원가입 후 이용가능)</li>
				<li>
					2) Kakao Developer 오른쪽 상단 [▼] 버튼 클릭 후 내 애플리케이션 메뉴를 클릭합니다.
					<ul>
						<li>- 기존 애플리케이션이 있는 경우 애플리케이션명을 클릭합니다.</li>
					</ul>
				</li>
				<li>
					3) 애플리케이션이 없는 경우 앱 만들기 버튼 클릭 후 아이콘과 애플리케이션 이름을 등록한 후 앱만들기 버튼을 클릭합니다.
					<ul>
						<li>- 기존 애플리케이션이 있는 경우 해당 과정을 생략하셔도 됩니다.</li>
					</ul>
				</li>
				<li>
					4) 좌측메뉴 설정 내 일반 페이지 접속 후 플랫폼 추가 버튼을 클릭합니다.
					<ul>
						<li>- 웹을 선택하시고 사용 중인 도메인 정보를 등록하신 후 저장 버튼을 누르시면 세팅이 완료됩니다.</li>
						<li>- 예) http://www.wisa.co.kr, http://m.wisa.co.kr/</li>
					</ul>
				</li>
				<li>5) 해당 페이지 상단 JavaScript 키를 확인합니다.</li>
			</ol>
		</div>
	</div>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>