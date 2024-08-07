<style type='text/css'>
#stopRegistrationNUmber {
	position: absolute;
	border: solid 5px #343b48;
	background: #fff;
	width: 660px;
	text-align: justify;
	display: none;
}

#stopRegistrationNUmber * {
	font-size: 11px;
	letter-spacing: -1px;
	font-family: dotum;
}

#stopRegistrationNUmber h3 {
	background: #343b48;
	color: #fff;
	font-size: 11px;
	padding: 5px;
}

#stopRegistrationNUmber h4 {
	font-size: 11px;
}

#stopRegistrationNUmber .close {
	float: right;
	color: #fff;
	cursor: pointer;
}

#stopRegistrationNUmber .content {
	padding: 15px 3px;
}

#stopRegistrationNUmber .content p {
	font-weight: bold;
	margin: 0 15px;
}

#stopRegistrationNUmber .content .btns {
	text-align: center;
	margin: 10px 0;
}

#stopRegistrationNUmber .content .nopopup {
	text-align: right;
	margin: 5px 0;
}

.law {
	margin: 15px 0;
}

.law .law_content {
	border: solid 1px #666;
	padding: 2px;
}

.law .law_content dt {
	font-weight: bold;
}
</style>

<div id='stopRegistrationNUmber'>
	<h3>
		현재 본 쇼핑몰은 회원가입시 주민등록번호를 입력받고 있습니다.
		<div class='close' onclick='nornclose()'>×</div>
	</h3>
	<div class='content'>
		<p>2012년 8월 18일부터 개정 시행된 정보통신만 이용촉진 및 정보보호 등에 관한 법률에 따라 주민등록번호의 수집 및 이용제한이 됩니다. 2013년 2월 18일부터 계도기간이 종료되어 법적 제재를 받을 수 있습니다.</p>

		<div class='law'>
			<h4><근거 법률> 망법 제 23조의 2 (주민등록번호의 사용 제한)</h4>
			<dl class='law_content'>
				<dt>제23조의2(주민등록번호의 사용 제한)</dt>
				<dd>① 정보통신서비스 제공자는 다음 각 호의 어느 하나에 해당하는 경우를 제외하고는 이용자의 주민등록번호를 수집·이용할 수 없다.</dd>
				<dd>1. 제23조의3에 따라  본인확인기관으로 지정받은 경우</dd>
				<dd>2. 법령에서 이용자의 주민등록번호 수집·이용을 허용하는 경우</dd>
				<dd>3. 영업상 목적을 위하여 이용자의 주민등록번호 수집이용이 불가피한 정보통신서비스 제공자로서 방송통신위원회가 고시하는 경우</dd>
				<dd>② 제1항제2호 또는 제3호에 따라 주민등록번호를 수집·이용할 수 있는 경우에도 이용자의 주민등록번호를 사용하지 아니하고 본인을 확인하는 방법(이하 "대체수단"이라 한다)을 제공하여야 한다.</dd>
				<dd>※(76조 과태로)제23조의2 위반하여 필요한 조치를 하지 아니한 자는 3천만 원 이하의 과태료</dd>
			</dl>
		</div>

		<p>위사는 관련법령에 맞춰 개인정보 수집기능을 개선하였습니다.</p>
		<p>아래 변경하기 버튼을 통해 해당 설정페이지로 이동하시어 기능설정을 해주시기 바랍니다.</p>

		<div class='btns'>
			<span class='btn large blue'><input type='button' value='변경하기' onclick="rn1();" /></span>
			<span class='btn large '><input type='button' value='공지사항확인' onclick="rn2();" /></span>
		</div>

		<div class='nopopup'><input type='checkbox' onclick='nornpopup()' /> 3일간 창닫기</div>
	</div>
</div>

<script type='text/javascript'>
$(window).load(function(){
	var pop = $('#stopRegistrationNUmber');
	pop.show();
	pop.css('top', (document.documentElement.clientHeight/2 - pop.height()/2));
	pop.css('left', (document.documentElement.clientWidth/2 - pop.width()/2));
	pop.draggable({"cursor":"pointer", "containment":"#contentArea"});
});

function rn1() {
	location.href='?body=config@setPrivateInfo';
}

function rn2() {
	window.open('http://help.wisa.co.kr/notice/notice/read/10285');
}

function nornpopup() {
	window.alert('관리자모드 메인페이지 배너를 클릭하시면 이 안내를 다시 보실수 있습니다.');
	setCookie('no_jumin_popup', true, 3);
	nornclose();
}

function nornclose() {
	$('#stopRegistrationNUmber').hide('fast');
}
</script>