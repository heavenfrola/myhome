<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  실명인증/i-PIN 설정
	' +----------------------------------------------------------------------------------------------+*/

	if(!$cfg['namecheck_use']) $cfg['namecheck_use']="N"; // 실명인증
	if(!$cfg['ipin_use']) $cfg['ipin_use']="N"; // ipin

	$_sw="N";

?>
<div class="box_title first">
	<h2 class="title">계약 안내 및 신청</h2>
</div>
<div class="box_bottom left top_line" style="overflow:hidden">
	<p class="explain icon">현재 한국신용정보에서 제공하는 서비스를 사용하실 수 있으며 먼저 절차에 따라 신청을 해주시기 바랍니다.</p>
	<ul class="step_nc">
		<li><a href="http://redirect.wisa.co.kr/namecheck_doc"><img src="<?=$engine_url?>/_manage/image/config/namecheck/nc_1.gif"></a>
		<li><img src="<?=$engine_url?>/_manage/image/config/namecheck/nc_2.gif">
		<li><img src="<?=$engine_url?>/_manage/image/config/namecheck/nc_3.gif">
		<li class="last-child"><img src="<?=$engine_url?>/_manage/image/config/namecheck/nc_4.gif">
	</ul>
</div>
<div id="controlTab">
	<ul class="tabs namecheck">
		<li id="se_tab1" onclick="showTab(1);" class="selected">실명확인</li>
		<li id="se_tab2" onclick="showTab(2);">i-PIN</li>
	</ul>
</div>
<!-- 실명확인 -->
<div id="nameck_div">
	<form method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="return ckFrm(this);">
		<input type="hidden" name="body" value="config@config.exe">
		<div class="box_title first">
			<h2 class="title">실명인증 설정</h2>
		</div>
		<div class="box_middle left">
			<dl class="list_msg">
				<dt>실명확인서비스에 대한 안내입니다. 꼭 읽어보세요.</dt>
				<dd>① 실명확인서비스를 제공하는 한국신용정보의 계약 약정서를 작성합니다. &nbsp;<span class="p_color2">(1개월 무료체험 이벤트 진행중)</span></dd>
				<dd>② 작성한 약정서와 서류(업자등록증사본, 인감증명서)를 한국신용정보로 우편으로 등기발송하세요.</dd>
				<dd>③ 한국신용정보의 주소는 "서울시 영등포구 여의도동 14-33 한국신용정보 e-biz사업실 e-infra팀 김가별 대리 앞" 입니다.</dd>
				<dd>④ 한국신용정보의 담당자로부터 회원사 ID를 발급 받으시게 됩니다.</dd>
				<dd>⑤ 발급 받으신 회원사 ID를 아래 기입란에 입력하고 확인 버튼을 누릅니다.</dd>
				<dd>⑥ 이제 쇼핑몰 화면에서 회원가입 절차중에 실명확인 서비스가 정상적으로 동작되는지 확인하세요.</dd>
			</dl>
		</div>
		<table class="tbl_row">
			<caption class="hidden">실명인증 설정</caption>
			<colgroup>
				<col style="width:15%">
				<col>
			</colgroup>
			<tr>
				<th scope="row">사용여부</th>
				<td>
					<input type="radio" name="namecheck_use" value="Y" id="namecheck_use1" <?=checked($cfg['namecheck_use'],'Y')?>> <label for="namecheck_use1" class="p_cursor">사용함</label> &nbsp;
					<input type="radio" name="namecheck_use" value="N" id="namecheck_use2" <?=checked($cfg['namecheck_use'],'N')?>> <label for="namecheck_use2" class="p_cursor">사용안함</label>
				</td>
			</tr>
			<tr>
				<th scope="row">나이스 아이디</th>
				<td>
					<input type="text" name="namecheck_id" value="<?=$cfg[namecheck_id]?>" class="input" size="40">
					<span class="explain">계약 후 발급받으신 Nws로 시작하는 아이디를 입력해주시기 바랍니다</span>
				</td>
			</tr>
		</table>
		<div class="box_bottom">
			<span class="box_btn blue"><button type="submit">확인</button></span>
		</div>
	</form>
	<div class="box_title">
		<h2 class="title">실명확인 서비스란</h2>
	</div>
	<div class="box_bottom top_line left">
		<dl class="list_msg">
			<dt>개인이 제공하는 주민등록번호와 성명의 일치여부를 확인하는 서비스로서 인터넷 사업자가 가장 간편한 본인확인 수단으로 널리 활용되고 있습니다.</dt>
			<dd><img src="<?=$engine_url?>/_manage/image/config/namecheck/nc_about01.gif"></dd>
		</dl>
		<dl class="list_msg">
			<dt>서비스 내용</dt>
			<dd>- 개인의 주민등록번호와 성명의 일치여부를 실시간으로 확인하는 서비스</dd>
			<dd>- 한국신용정보에서 직접 확인한 실명정보와 국내 금융기관 (은행, 카드, 캐피탈, 신협, 금고, 저축은행, 보험 등)과 백화점, 의류, 통신 등 비금융기관에서 확인한 실명정보를 토대로 서비스</dd>
			<dd>- 개인 약 4천5백만명 이상으로서 높은 인증율</dd>
		</dl>
		<dl class="list_msg">
			<dt>이용대상</dt>
			<dd>- 회원가입을 받는 인터넷 사업자</dd>
			<dd>- 게시판을 운영하는 인터넷사업자 및 개인</dd>
			<dd>- 쇼핑몰, 홈쇼핑, 경매 등 거래사이트</dd>
			<dd>- 물품을 직접 판매하는 사이트</dd>
			<dd>- 미성년자의 이용을 제한해야 하는 성인사이트</dd>
			<dd>- 기타 회원의 실명확인이 필요한 인터넷 사이트 및 사업자</dd>
		</dl>
	</div>
	<div class="box_title">
		<h2 class="title">실명확인 서비스의 필요성 및 효과</h2>
	</div>
	<div class="box_bottom top_line left" style="overflow:hidden">
		<dl class="effect_nc">
			<dt><img src="<?=$engine_url?>/_manage/image/config/namecheck/nc_about02.gif"></dt>
			<dd>실명확인을 통하여 회원별 컨텐츠 차별화 등의 실질적인 고객관리가 가능합니다.<br>
			또한, 불량회원으로 전이될 수 있는 비실명 회원가입을 사전에 방지합니다.</dd>
		</dl>
		<dl class="effect_nc">
			<dt><img src="<?=$engine_url?>/_manage/image/config/namecheck/nc_about03.gif"></dt>
			<dd>
				회원가입에 실명확인 프로세스를 추가함으로써, 허위 정보입력을 사전에 차단합니다.<br>
				이를 통하여 고객이 등록한 고객정보에 대한 신뢰도와 가치가 향상됩니다.
			</dd>
		</dl>
		<dl class="effect_nc">
			<dt><img src="<?=$engine_url?>/_manage/image/config/namecheck/nc_about04.gif"></dt>
			<dd>
				타인 주민등록번호 도용으로 인한 실명회원의 피해를 예방할 수 있습니다.<br>
				허위정보에 의한 가입 및 정보 도용을 방지합니다.
			</dd>
		</dl>
		<dl class="effect_nc">
			<dt><img src="<?=$engine_url?>/_manage/image/config/namecheck/nc_about05.gif"></dt>
			<dd>
				실명정보의 클린징을 통하여 회원정보에 대한 관리비용이 감소합니다.<br>
				비지니스 활성화 및 타켓 마케팅이 가능합니다.
			</dd>
		</dl>
	</div>
	<div class="box_title">
		<h2 class="title">서비스의 장점</h2>
	</div>
	<div class="box_bottom top_line left">
		<dl class="list_msg">
			<dt>국내 개인정보 서비스의 최강자</dt>
			<dd>- 국내 최대, 최초 개인신용정보(CB)사업자</dd>
			<dd>- 국내 최대, 최초 실명확인서비스 제공</dd>
		</dl>
		<dl class="list_msg">
			<dt>안정된 시스템</dt>
			<dd>- 빠른 응답, 무장애 시스템 지향</dd>
			<dd>- 최근 차세대 시스템 오픈으로 우수한 성능</dd>
		</dl>
		<dl class="list_msg">
			<dt>높은 공신력</dt>
			<dd>- 신용평가기관으로서의 높은 공신력</dd>
			<dd>- 업계 유일의 증권거래소 상장</dd>
		</dl>
		<dl class="list_msg">
			<dt>정보의 높은 신뢰성</dt>
			<dd>- 미확인 정보의 서비스 배제</dd>
			<dd>- 확인된 정보의 신뢰수준별 정보관리</dd>
		</dl>
		<dl class="list_msg">
			<dt>국내 최대 실명정보 보유</dt>
			<dd>- 4천5백만명 이상의 실명정보 보유</dd>
			<dd>- 빠른 실명정보 보유량 증가 (월 30만건 이상)</dd>
		</dl>
		<dl class="list_msg">
			<dt>최고의 민원서비스 전문성</dt>
			<dd>- 오랜 경험과 처리능력을 보유한 상담원</dd>
			<dd>- 국내 최대규모의 상담센터 운영</dd>
		</dl>
		<dl class="list_msg">
			<dt>사이트 적용 편의성</dt>
			<dd>- 운영자 편의를 위한 서비스 자동 탑재 지원</dd>
		</dl>
		<dl class="list_msg">
			<dt>합리적 비용</dt>
			<dd>- 운영비용 부담을 최소화하는 업계 최저의 합리적 수수료 체계</dd>
		</dl>
	</div>
	<div class="box_title">
		<h2 class="title">서비스 이용료</h2>
	</div>
	<div class="box_bottom top_line left">
		<p>위사 서비스 이용 고객님께 국내 최저가로 서비스 이용료를 제공해 드립니다!!!</p>
		<table class="tbl_mini full">
			<caption class="hidden">서비스 이용료</caption>
			<tr>
				<th scope="col">내역</th>
				<th scope="col">타사기준</th>
				<th scope="col">위사 서비스 이용고객</th>
			</tr>
			<tr>
				<td>보증금</td>
				<td>15만원</td>
				<td>10만원 (해지 시 환불)</td>
			</tr>
			<tr>
				<td>월 기본이용건수</td>
				<td>5,000건</td>
				<td>무제한</td>
			</tr>
			<tr>
				<td>월 기본료</td>
				<td>10만원 (초과건수 발생 시 별도 과금)</td>
				<td>5만원</td>
			</tr>
		</table>
		<p class="explain">부가세 별도</p>
		<table cellpadding="0" cellspacing="0">
			<tr>
				<td>
				<div style="border:4px solid #EFEFEF; padding:20px; margin:10px; height:100px;">
					<div style="float:left; width:50%;">
						<li><img src="<?=$engine_url?>/_manage/image/config/namecheck/nc_about06.gif" vspace="5">
						<li><img src="<?=$engine_url?>/_manage/image/config/namecheck/nc_about08.gif" align="absmiddle"> <b class="tel">02-2122-4548 / 010-9313-7757</b>
						<li><img src="<?=$engine_url?>/_manage/image/config/namecheck/nc_about09.gif" align="absmiddle"> <b class="tel">02-2122-4579</b>
						<li><img src="<?=$engine_url?>/_manage/image/config/namecheck/nc_about10.gif" vspace="5">
					</div>
					<div style="float:left; width:45%;">
						<li><img src="<?=$engine_url?>/_manage/image/config/namecheck/nc_about07.gif" vspace="5">
						<li><img src="<?=$engine_url?>/_manage/image/config/namecheck/nc_about08.gif" align="absmiddle"> <b class="tel">1599-4435</b>
					</div>
				</div>
				</td>
			</tr>
			<tr>
				<td style="padding:10px; font-size:8pt;">
				<b>이용료 납부 방법은 약정서를 보내주시면 한국신용정보 담당자께서 유선으로 친절히 안내해드립니다.</b><br>
				실명확인서비스 약정서 (<a href="http://redirect.wisa.co.kr/namecheck_doc" class="sblue"><b>다운로드</b></a>) / 사업자등록증사본 1부 / 인감증명서사본 1부<br>
				보내실 곳 : 서울시 영등포구 여의도동 14-33 한국신용정보 e-biz사업실 e-infra팀 김가별 대리 앞
				</td>
			</tr>
		</table>
	</div>
</div>
<!-- //실명확인 -->
<!-- i-PIN -->
<div id="ipin_div" style="display:none;">
	<form name="" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="return ckFrm2(this);">
		<input type="hidden" name="body" value="config@config.exe">
		<div class="box_title first">
			<h2 class="title">i-PIN 설정</h2>
		</div>
		<div class="box_middle left">
			<dl class="list_msg">
				<dt>i-PIN 서비스에 대한 안내입니다. 꼭 읽어보세요.</dt>
				<dd>① i-PIN 서비스를 제공하는 한국신용정보의 계약 약정서를 작성합니다.</dd>
				<dd>② 작성한 약정서와 서류(업자등록증사본, 인감증명서)를 한국신용정보로 우편으로 등기발송하세요.</dd>
				<dd>③ 한국신용정보의 주소는 "서울시 영등포구 여의도동 14-33 한국신용정보 e-biz사업실 e-infra팀 김가별 대리 앞" 입니다.</dd>
				<dd>④ 한국신용정보의 담당자로부터 회원사 정보를 발급 받으시게 됩니다.</dd>
				<dd>⑤ 발급 받으신 회원사 정보를 아래 기입란에 입력하고 확인 버튼을 누릅니다.</dd>
				<dd>⑥ 이제 쇼핑몰 화면에서 회원가입 절차중에 i-PIN 서비스가 정상적으로 동작되는지 확인하세요.</dd>
			</dl>
		</div>
		<table class="tbl_row">
			<caption class="hidden">i-PIN 설정</caption>
			<colgroup>
				<col style="width:15%">
				<col>
			</colgroup>
			<tr>
				<th scope="row">사용여부</th>
				<td>
					<input type="radio" name="ipin_use" value="Y" id="ipin_use1" <?=checked($cfg['ipin_use'],'Y')?>> <label for="ipin_use1" class="p_cursor">사용함</label> &nbsp;
					<input type="radio" name="ipin_use" value="N" id="ipin_use2" <?=checked($cfg['ipin_use'],'N')?>> <label for="ipin_use2" class="p_cursor">사용안함</label>
				</td>
			</tr>
			<tr>
				<th scope="row">나이스 아이디</th>
				<td>
					<input type="text" name="ipin_id" value="<?=$cfg[ipin_id]?>" class="input" size="40">
					<span class="explain">계약 후 발급받으신 <?=$_sw?>로 시작하는 아이디를 입력해주시기 바랍니다</span>
				</td>
			</tr>
			<tr>
				<th scope="row">나이스 SIKey</th>
				<td>
					<input type="text" name="ipin_sikey" value="<?=$cfg[ipin_sikey]?>" class="input" size="40">
					<span class="explain">계약 후 발급받으신 SIKey(사이트식별정보 12자리)를 입력해주시기 바랍니다</span>
				</td>
			</tr>
			<tr>
				<th scope="row">나이스 키스트링</th>
				<td>
					<input type="text" name="ipin_keystring" value="<?=$cfg[ipin_keystring]?>" class="input" size="40">
					<span class="explain">계약 후 발급받으신 키스트링(80자리)를 입력해주시기 바랍니다</span>
				</td>
			</tr>
		</table>
		<div class="box_bottom">
			<span class="box_btn blue"><button type="submit">확인</button></span>
		</div>
	</form>
	<div class="box_title">
		<h2 class="title">i-PIN 이란</h2>
	</div>
	<div class="box_bottom top_line left">
		<dl class="list_msg">
			<dt>아이핀이란 인터넷 상에서 주민번호를 대신하여 아이디와 패스워드를 이용하여 본인확인을 하는 수단입니다.<br>
			아이핀 아이디와 패스워드를 이용한면 웹사이트에 더이상 주민번호를 이용하지 않아도 회원가입 및 기타 서비스<br>
			이용이 가능합니다.</dt>
			<dd><img src="<?=$engine_url?>/_manage/image/config/namecheck/ipin_about01.gif"></dd>
		</dl>
		<dl class="list_msg">
			<dt>서비스 이용을 위한 신원확인</dt>
			<dd>나이스아이핀 서비스를 이용하기 위해서는 반드시 온라인 신원확인을 거쳐야 합니다.</dd>
			<dd>온라인 신원확인은 공인증서(범용), 신용카드, 대면인증 중 1가지를 선택하여 이용 하실 수 있습니다.</dd>
			<dd><img src="<?=$engine_url?>/_manage/image/config/namecheck/ipin_about02.gif"></dd>
			<dd>공인증서는 범용인증서만 사용하실 수 있습니다. (용도제한용 이용불가)</dd>
			<dd>그리고, 만 14세 미만이면서 아이핀 발급을 원하는 경우에는 반드시 법정대리인(부모)이 동의를 얻어야 가능합니다.</dd>
		</dl>
		<dl class="list_msg">
			<dt>나이스아이핀 구성</dt>
			<dd>아이핀은 온라인 주민등록번호 <strong>13자리 난수</strong>로 구성되어 있으며, 중복가입 방지를 위해 <strong>중복가입확인정보</strong>(64byte)를 별도로 제공함과 동시에,<br>
			회원사 마케팅 활용을 위해 성명,성별, 생년월일, 연령대 등이 <strong>통합제공</strong>됩니다.</dd>
			<dd><img src="<?=$engine_url?>/_manage/image/config/namecheck/ipin_about03.gif"></dd>
		</dl>
	</div>
	<div class="box_title">
		<h2 class="title">i-PIN 서비스 특징</h2>
	</div>
	<div class="box_bottom top_line left">
		<dl class="list_msg">
			<dt>서비스 특징</dt>
			<dd><img src="<?=$engine_url?>/_manage/image/config/namecheck/ipin_about04.gif"></dd>
		</dl>
		<dl class="list_msg">
			<dt>강력한 보안 체계</dt>
			<dd><img src="<?=$engine_url?>/_manage/image/config/namecheck/ipin_about05.gif"></dd>
		</dl>
	</div>
	<div class="box_title">
		<h2 class="title">서비스 이용료</h2>
	</div>
	<div class="box_bottom top_line left">
		<p>위사 서비스 이용 고객님께 국내 최저가로 서비스 이용료를 제공해 드립니다!!!</p>
		<table class="tbl_mini full">
			<caption class="hidden">서비스 이용료</caption>
			<tr>
				<th scope="col">내역</th>
				<th scope="col">위사고객</th>
				<th scope="col">비고</th>
			</tr>
			<tr>
				<td>보증금</td>
				<td>5만원</td>
				<td>해지 시 환불</td>
			</tr>
			<tr>
				<td>월 기본료1</td>
				<td>5만원</td>
				<td>아이핀만 이용시</td>
			</tr>
			<tr>
				<td>월 기본료2</td>
				<td>무료</td>
				<td>실명확인 서비스 선납요금제 이용시 무료제공(무료서비스 기간 이후 요금 부과)</td>
			</tr>
		</table>
		<p class="explain">부가세 별도</p>
		<table cellpadding="0" cellspacing="0">
			<tr>
				<td>
				<div style="border:4px solid #EFEFEF; padding:20px; margin:10px; height:100px;">
					<div style="float:left; width:50%;">
						<li><img src="<?=$engine_url?>/_manage/image/config/namecheck/nc_about06.gif" vspace="5">
						<li><img src="<?=$engine_url?>/_manage/image/config/namecheck/nc_about08.gif" align="absmiddle"> <b class="tel">02-2122-4548 / 010-9313-7757</b>
						<li><img src="<?=$engine_url?>/_manage/image/config/namecheck/nc_about09.gif" align="absmiddle"> <b class="tel">02-2122-4579</b>
						<li><img src="<?=$engine_url?>/_manage/image/config/namecheck/nc_about10.gif" vspace="5">
					</div>
					<div style="float:left; width:45%;">
						<li><img src="<?=$engine_url?>/_manage/image/config/namecheck/nc_about07.gif" vspace="5">
						<li><img src="<?=$engine_url?>/_manage/image/config/namecheck/nc_about08.gif" align="absmiddle"> <b class="tel">1599-4435</b>
					</div>
				</div>
				</td>
			</tr>
			<tr>
				<td style="padding:10px; font-size:8pt;">
				<b>이용료 납부 방법은 약정서를 보내주시면 한국신용정보 담당자께서 유선으로 친절히 안내해드립니다.</b><br>
				i-PIN서비스 약정서 (<a href="http://redirect.wisa.co.kr/namecheck_doc" class="sblue"><b>다운로드</b></a>) / 사업자등록증사본 1부 / 인감증명서사본 1부<br>
				보내실 곳 : 서울시 영등포구 여의도동 14-33 한국신용정보 e-biz사업실 e-infra팀 김가별 대리 앞
				</td>
			</tr>
		</table>
	</div>
</div>
<!-- i-PIN -->

<script language="JavaScript">
	function ckFrm(f){
		if(f.namecheck_use[0].checked == true){
			if(!checkBlank(f.namecheck_id, 'NICE 아이디를 입력해주세요.')) return false;
		}
		txt=f.namecheck_id.value;
		if(txt != ''){
			f.namecheck_id.value=txt.replace(/\s/g, '');
			if(txt.search('Nws') == -1){
				alert('Nws로 시작하는 아이디를 입력해주시기 바랍니다');
				return false;
			}
		}
		return true;
	}
	function ckFrm2(f){
		if(f.ipin_use[0].checked == true){
			if(!checkBlank(f.ipin_id, 'NICE 아이디를 입력해주세요.')) return false;
			if(!checkBlank(f.ipin_sikey, 'NICE SIKey를 입력해주세요.')) return false;
		}
		txt=f.ipin_id.value;
		if(txt != ''){
			f.ipin_id.value=txt.replace(/\s/g, '');
			if(txt.search('<?=$_sw?>') == -1){
				alert('<?=$_sw?>로 시작하는 아이디를 입력해주시기 바랍니다');
				return false;
			}
		}
		return true;
	}
	function showTab(type){
		w1=document.getElementById('nameck_div');
		w2=document.getElementById('ipin_div');
		t1=document.getElementById('se_tab1');
		t2=document.getElementById('se_tab2');
		if(type == 1){
			w2.style.display='none';
			w1.style.display='block';
			t2.className='';
			t1.className='selected';
		}else{
			w1.style.display='none';
			w2.style.display='block';
			t1.className='';
			t2.className='selected';
		}
		setCookie('showSTabNum', type, 1);
	}
	<?
		if($_COOKIE[showSTabNum]){
			echo "showTab(".$_COOKIE[showSTabNum].");";
		} else {
			echo "showTab(2);";
		}
	?>
</script>