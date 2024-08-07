<?php

$scfg->def('ssl_type', 'N');

?>
<?if($cfg['ssl_type'] != 'Y') {?>
<div class="msg_topbar sub quad warning">
	보안서버 사용설정을 '사용'으로 변경하기 전 보안서버 인증 발급신청/완료 및 설치여부를 반드시 확인해주세요.
	<a onclick="$('.msg_topbar').slideUp('fast');" class="close">닫기</a>
</div>
<?}?>
<form method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="config@ssl.exe">
	<div class="box_title first">
		<h2 class="title">보안서버 설정</h2>
	</div>
	<div class="box_middle left">
		<div class="list_info">
			<p class="title">[보안서버]</p>
			<p>네트워크 상에서 제 3자가 중요한 정보를 임의로 변경하는 것을 방지하기 위하여 사용자의 웹 브라우저와 서버 사이에 암호화된 통신을 구현하는 서버를 말합니다.</p><br>
			<p class="title">[보안서버 구축 의무화 안내]</p>
			<p>정보통신망법에 따라 <span class="warning">개인정보를 취급하는 모든 웹사이트의 보안서버 구축이 의무화</span>되었습니다.(의무사항 위반 시 최대 3천만 원의 과태료가 부과됩니다.)</p><br>
			<p class="title">[기타 안내사항]</p>
			<p>보안서버 신청은 위사홈페이지 > 호스팅 > SSL 보안서버인증서에서 가능합니다. <a href="https://www.wisa.co.kr/hosting/ssl" target="_blank" class="list_move">바로가기</a></p>
			<p>보안서버 사용 시 관리자 > 설정 > 대표도메인 설정에 등록된 대표도메인에 설정됩니다. <span class="warning">다른 연결도메인을 대표도메인으로 설정 시 주의</span>를 부탁드립니다.</p>
			<p>보안서버 사용 시 웹사이트의 접속주소는 'http://' 대신 'https://'로 시작됩니다. </p>
			<p>SNS로그인을 사용하는 경우, 웹사이트 접속주소 변경에 따른 <span class="warning">각 매체사 별 Callback URL을 변경 또는 추가</span>해야 합니다.</p>
		</div>
	</div>
	<table class="tbl_row">
		<caption class="hidden">보안서버 설정</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">보안서버 사용설정</th>
			<td>
				<label><input type="radio" name="ssl_type" value="Y" <?=checked($cfg['ssl_type'], 'Y')?>> 사용</label>
				<label><input type="radio" name="ssl_type" value="N" <?=checked($cfg['ssl_type'], 'N')?>> 미사용</label>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>