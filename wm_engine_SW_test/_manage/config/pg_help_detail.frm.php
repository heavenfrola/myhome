<style type="text/css" title="">
body {background:#fff; line-height:1.6;}
</style>
<?

	if($mode == lg) {
		$registration = $redirect_url.'/pg_help_dacom04';
		$insurance = $redirect_url.'/pg_help_dacom05';
	}elseif($mode == 'kcp'){
		$registration = $redirect_url.'/pg_help_kcp04';
		$insurance = $redirect_url.'/pg_help_kcp05';
	}elseif($mode == 'inicis'){
		$registration = $redirect_url.'/pg_help_inicis04';
		$insurance = $redirect_url.'/pg_help_inicis05';
	}elseif($mode == 'allat'){
		$registration = $redirect_url.'/pg_help_allat04';
		$insurance = $redirect_url.'/pg_help_allat05';
	}elseif($mode == 'allthegate'){
		$registration = 'https://www.allthegate.com/ags/app/app_05.jsp';
		$insurance = 'http://www.sgia.co.kr/aegis/index.php';
	}elseif($mode == 'danal'){
		$registration = '';
		$insurance = '';
	}

?>
<div id="pg_help_detail">
	<h1><img src="<?=$engine_url?>/_manage/image/config/pg_help/detail_title.gif" alt="결제 서비스 상세안내"></h1>
	<h2><img src="<?=$engine_url?>/_manage/image/config/pg_help/logo_<?=$mode?>.gif" alt="<?=$mode?>"></h2>
	<div class="content">
		<?if($mode == 'lg') {?>
			<h3>01.서비스 신청</h3>
			<ul class="list">
				<li>계약을 원하시는 고객께서는 <a href="<?=$redirect_url?>/pg_help_dacom01" target="_blank">서비스 신청</a>을 클릭하여 가입정보를 작성하여 주세요.</li>
				<li>전자지불 서비스업체와의 계약은 쇼핑몰사업자가 신청하여 주십시오.</li>
			</ul>
			<table class="tbl_pg">
				<caption class="hidden">서비스 신청</caption>
				<colgroup>
					<col style="width:20%">
				</colgroup>
				<tr>
					<th scope="row">수수료</th>
					<td>3.5% (부가세 별도)</td>
				</tr>
				<tr>
					<th scope="row">초기 가입비</th>
					<td>20만원 (부가세 별도)</td>
				</tr>
			</table>
			<h3>02.서비스 안내</h3>
			<ul class="list">
				<li>서비스 신청을 하시면 영업담당자와의 전화상담으로 안내됩니다.</li>
				<li>상담시 신청심사를 거치게 되며, 이용조건을 안내 받으실 수 있습니다.</li>
			</ul>
			<table class="tbl_pg">
				<caption class="hidden">서비스 안내</caption>
				<colgroup>
					<col style="width:20%">
				</colgroup>
				<tr>
					<th scope="row">상담안내</th>
					<td>(LG유플러스) 1544-7772, 02-2089-6766<br>(1번- 계약접수, 2번- 카드등록, 3번- 정산,세금계산서) </td>
				</tr>
			</table>
			<h3>03.계약서류 작성</h3>
			<p class="icon">
				신청심사가 완료되면 계약서류를 등기 또는 택배 발송해 주세요.<br>
				※ 인감증명서, 등기부등본은 최근 3개월 내 발급분이여야 합니다.<br>
				※  계약서 발송전 계약서 날인 인감과 인감증명서의 인감이 같은지 꼭 확인하여 주십시오 (인감 상이시 사용인감계 제출)
			</p>
			<table class="tbl_pg">
				<caption class="hidden">계약서류 작성</caption>
				<colgroup>
					<col style="width:20%">
				</colgroup>
				<tr>
					<th scope="row">준비서류</th>
					<td>
						<strong>[개인사업자]</strong><br>
						대표자 인감증명서 원본 1부, 대표자 신분증사본 1부, 계약서 2부, 사업자등록증 사본 1부, 정산통장 사본 1부<br><br>
						<strong>[법인사업자]</strong><br>
						법인인감증명서 원본 1부, 법인등기부등본 원본 1부, 대표자 인감증명서 원본 1부, 대표자 신분증사본 1부<br>
						계약서 2부, 사업자등록증 사본 1부, 정산통장 사본 1부 
					</td>
				</tr>
				<tr>
					<th scope="row">계약서</th>
					<td>
						<a href="<?=$redirect_url?>/pg_help_dacom03" target="_blank">다운로드 및 작성 방법보기</a>
					</td>
				</tr>
				<tr>
					<th scope="row">발송처</th>
					<td>(우) 427-800<br>경기도 과천시 별양동 1-13 제일쇼핑 505호 e-Biz사업부 전자결제사업팀 eCredit 계약담당자 앞 </td>
				</tr>
			</table>
		
		<?}elseif($mode == 'kcp') {?>
			<h3>01.서비스 신청</h3>
			<ul class="list">
				<li>계약을 원하시는 고객께서는 <a href="<?=$redirect_url?>/pg_help_kcp01" target="_blank">서비스 신청</a>을 클릭하여 가입정보를 작성하여 주세요.</li>
				<li>전자지불 서비스업체와의 계약은 쇼핑몰사업자가 신청하여 주십시오.</li>
			</ul>
			<table class="tbl_pg">
				<caption class="hidden">서비스 신청</caption>
				<colgroup>
					<col style="width:20%">
				</colgroup>
				<tr>
					<th scope="row">수수료</th>
					<td>3.5% (부가세 별도)</td>
				</tr>
				<tr>
					<th scope="row">초기 가입비</th>
					<td>20만원 (부가세 별도)</td>
				</tr>
			</table>
			<h3>02.서비스 안내</h3>
			<ul class="list">
				<li>서비스 신청을 하시면 영업담당자와의 전화상담으로 안내됩니다.</li>
				<li>상담시 신청심사를 거치게 되며, 이용조건을 안내 받으실 수 있습니다.</li>
			</ul>
			<table class="tbl_pg">
				<caption class="hidden">서비스 안내</caption>
				<colgroup>
					<col style="width:20%">
				</colgroup>
				<tr>
					<th scope="row">상담안내</th>
					<td>계약문의 : 1544-8662<br>기술문의 : 1544-8661<br>상점문의 : 1544-8660</td>
				</tr>
			</table>
			<h3>03.계약서류 작성</h3>
			<p class="icon">
				신청심사가 완료되면 계약서류를 등기 또는 택배 발송해 주세요.<br>
				※ 인감증명서, 등기부등본은 최근 3개월 내 발급분이여야 합니다.<br>
				※  계약서 발송전 계약서 날인 인감과 인감증명서의 인감이 같은지 꼭 확인하여 주십시오 (인감 상이시 사용인감계 제출)
			</p>
			<table class="tbl_pg">
				<caption class="hidden">계약서류 작성</caption>
				<colgroup>
					<col style="width:20%">
				</colgroup>
				<tr>
					<th scope="row">준비서류</th>
					<td>
						<strong>[개인사업자]</strong><br>
						대표자 인감증명서 원본 1부, 대표자 신분증사본 1부, 계약서 2부, 사업자등록증 사본 1부, 정산통장 사본 1부<br><br>
						<strong>[법인사업자]</strong><br>
						법인인감증명서 원본 1부, 법인등기부등본 원본 1부, 대표자 인감증명서 원본 1부, 대표자 신분증사본 1부<br>
						계약서 2부, 사업자등록증 사본 1부, 정산통장 사본 1부 
					</td>
				</tr>
				<tr>
					<th scope="row">계약서</th>
					<td>
						<a href="<?=$redirect_url?>/pg_help_kcp03" target="_blank">다운로드 및 작성 방법보기</a>
					</td>
				</tr>
				<tr>
					<th scope="row">발송처</th>
					<td>(우)152-050<br>서울시 구로구 구로동 170-5 우림 e-Biz 센터 5 층 508 호 [KCP 결제서비스 계약 담당]</td>
				</tr>
			</table>

		<?}elseif($mode == 'inicis') {?>
			<h3>01.서비스 신청</h3>
			<ul class="list">
				<li>계약을 원하시는 고객께서는 <a href="<?=$redirect_url?>/pg_help_inicis01" target="_blank">서비스 신청</a>을 클릭하여 가입정보를 작성하여 주세요.</li>
				<li>전자지불 서비스업체와의 계약은 쇼핑몰사업자가 신청하여 주십시오.</li>
			</ul>
			<table class="tbl_pg">
				<caption class="hidden">서비스 신청</caption>
				<colgroup>
					<col style="width:20%">
				</colgroup>
				<tr>
					<th scope="row">수수료</th>
					<td>3.5% (부가세 별도)</td>
				</tr>
				<tr>
					<th scope="row">초기 가입비</th>
					<td>20만원 (부가세 별도)</td>
				</tr>
			</table>
			<h3>02.서비스 안내</h3>
			<ul class="list">
				<li>서비스 신청을 하시면 영업담당자와의 전화상담으로 안내됩니다.</li>
				<li>상담시 신청심사를 거치게 되며, 이용조건을 안내 받으실 수 있습니다.</li>
			</ul>
			<table class="tbl_pg">
				<caption class="hidden">서비스 안내</caption>
				<colgroup>
					<col style="width:20%">
				</colgroup>
				<tr>
					<th scope="row">상담안내</th>
					<td>신규업체문의 : 02-3430-5858</td>
				</tr>
			</table>
			<h3>03.계약서류 작성</h3>
			<p class="icon">
				신청심사가 완료되면 계약서류를 등기 또는 택배 발송해 주세요.<br>
				※ 인감증명서, 등기부등본은 최근 3개월 내 발급분이여야 합니다.<br>
				※  계약서 발송전 계약서 날인 인감과 인감증명서의 인감이 같은지 꼭 확인하여 주십시오 (인감 상이시 사용인감계 제출)
			</p>
			<table class="tbl_pg">
				<caption class="hidden">계약서류 작성</caption>
				<colgroup>
					<col style="width:20%">
				</colgroup>
				<tr>
					<th scope="row">준비서류</th>
					<td>
						<strong>[개인사업자]</strong><br>
						대표자 인감증명서 원본 1부, 대표자 신분증사본 1부, 계약서 2부, 사업자등록증 사본 1부, 정산통장 사본 1부<br><br>
						<strong>[법인사업자]</strong><br>
						법인인감증명서 원본 1부, 법인등기부등본 원본 1부, 대표자 인감증명서 원본 1부, 대표자 신분증사본 1부<br>
						계약서 2부, 사업자등록증 사본 1부, 정산통장 사본 1부 
					</td>
				</tr>
				<tr>
					<th scope="row">계약서</th>
					<td>
						<a href="<?=$redirect_url?>/pg_help_inicis03" target="_blank">다운로드 및 작성 방법보기</a>
					</td>
				</tr>
				<tr>
					<th scope="row">발송처</th>
					<td>(우)463-400<br>경기도 성남시 분당구 삼평동 670번지 유스페이스1 A동 5층 ㈜케이지이니시스 신규계약 담당자앞</td>
				</tr>
			</table>

		<?}elseif($mode == 'allat') {?>
			<h3>01.서비스 신청</h3>
			<ul class="list">
				<li>계약을 원하시는 고객께서는 <a href="<?=$redirect_url?>/pg_help_allat01" target="_blank">서비스 신청</a>을 클릭하여 가입정보를 작성하여 주세요.</li>
				<li>전자지불 서비스업체와의 계약은 쇼핑몰사업자가 신청하여 주십시오.</li>
			</ul>
			<table class="tbl_pg">
				<caption class="hidden">서비스 신청</caption>
				<colgroup>
					<col style="width:20%">
				</colgroup>
				<tr>
					<th scope="row">수수료</th>
					<td>3.5% (부가세 별도)</td>
				</tr>
				<tr>
					<th scope="row">초기 가입비</th>
					<td>20만원 (부가세 별도)</td>
				</tr>
			</table>
			<h3>02.서비스 안내</h3>
			<ul class="list">
				<li>서비스 신청을 하시면 영업담당자와의 전화상담으로 안내됩니다.</li>
				<li>상담시 신청심사를 거치게 되며, 이용조건을 안내 받으실 수 있습니다.</li>
			</ul>
			<table class="tbl_pg">
				<caption class="hidden">서비스 안내</caption>
				<colgroup>
					<col style="width:20%">
				</colgroup>
				<tr>
					<th scope="row">상담안내</th>
					<td>TEL : 02-3783-9990<br>FAX : 02-3783-9833</td>
				</tr>
			</table>
			<h3>03.계약서류 작성</h3>
			<p class="icon">
				신청심사가 완료되면 계약서류를 등기 또는 택배 발송해 주세요.<br>
				※ 인감증명서, 등기부등본은 최근 3개월 내 발급분이여야 합니다.<br>
				※  계약서 발송전 계약서 날인 인감과 인감증명서의 인감이 같은지 꼭 확인하여 주십시오 (인감 상이시 사용인감계 제출)
			</p>
			<table class="tbl_pg">
				<caption class="hidden">계약서류 작성</caption>
				<colgroup>
					<col style="width:20%">
				</colgroup>
				<tr>
					<th scope="row">준비서류</th>
					<td>
						<strong>[개인사업자]</strong><br>
						계약서 2부, 사업자등록증 사본 1부, 입금계좌(대표자 명의) 사본 1부, 인감증명서(대표자 명의) 1부 <br><br>
						<strong>[법인사업자]</strong><br>
						계약서 2부, 사업자등록증 사본 1부, 입금계좌(법인 명의) 사본 1부<br>
						법인 인감증명서 1부, 등기사항전부증명서(현재사항) 1부<br>
						사용인감계(계약서에 사용인감 날인 시에만 제출) : 법인인감도장으로 계약서에 날인 시에는 사용인감계를 제출하지 않으셔도 됩니다. 
					</td>
				</tr>
				<tr>
					<th scope="row">계약서</th>
					<td>
						<a href="<?=$redirect_url?>/pg_help_allat03" target="_blank">다운로드 및 작성 방법보기</a>
					</td>
				</tr>
				<tr>
					<th scope="row">발송처</th>
					<td>(우) 135-766<br>서울특별시 강남구 학동로 401 금하빌딩 10층(청담동) ㈜올앳 PG계약 담당자 앞</td>
				</tr>
			</table>

		<?}elseif($mode == 'allthegate') {?>
			<h3>01.서비스 신청</h3>
			<ul class="list">
				<li>
					<form action ="http://www.allthegate.com/ags/partner/ptn_start.jsp" method="post" target="_blank">
						<input type="hidden" name="ptn_id" value="wisamall">
						<input type="hidden" name="rtnurl" value="http://www.allthegate.com/AGS_pay_ing.html">
						계약을 원하시는 고객께서는 <input type="submit" value="서비스 신청" style="padding:0; margin:0; border:0; font-weight:bold; background:none; text-decoration:underline;">을 클릭하여 가입정보를 작성하여 주세요.
					</form>
				</li>
				<li>전자지불 서비스업체와의 계약은 쇼핑몰사업자가 신청하여 주십시오.</li>
			</ul>
			<table class="tbl_pg">
				<caption class="hidden">서비스 신청</caption>
				<colgroup>
					<col style="width:20%">
				</colgroup>
				<tr>
					<th scope="row">수수료</th>
					<td>3.5% (부가세 별도)</td>
				</tr>
				<tr>
					<th scope="row">초기 가입비</th>
					<td>20만원 (부가세 별도)</td>
				</tr>
			</table>
			<h3>02.서비스 안내</h3>
			<ul class="list">
				<li>서비스 신청을 하시면 영업담당자와의 전화상담으로 안내됩니다.</li>
				<li>상담시 신청심사를 거치게 되며, 이용조건을 안내 받으실 수 있습니다.</li>
			</ul>
			<table class="tbl_pg">
				<caption class="hidden">서비스 안내</caption>
				<colgroup>
					<col style="width:20%">
				</colgroup>
				<tr>
					<th scope="row">상담안내</th>
					<td>TEL : 1661-7335</td>
				</tr>
			</table>
			<h3>03.계약서류 작성</h3>
			<p class="icon">
				신청심사가 완료되면 계약서류를 등기 또는 택배 발송해 주세요.<br>
				※ 인감증명서, 등기부등본은 최근 3개월 내 발급분이여야 합니다.<br>
				※  계약서 발송전 계약서 날인 인감과 인감증명서의 인감이 같은지 꼭 확인하여 주십시오 (인감 상이시 사용인감계 제출)
			</p>
			<table class="tbl_pg">
				<caption class="hidden">계약서류 작성</caption>
				<colgroup>
					<col style="width:20%">
				</colgroup>
				<tr>
					<th scope="row">준비서류</th>
					<td>
						<strong>[개인사업자]</strong><br>
						대표자 인감증명서 원본 1부, 정산통장 사본 1부, 통합 전자지불서비스 계약서 2부, 사업자등록증 사본 2부<br><br>
						<strong>[법인사업자]</strong><br>
						법인인감증명서 원본 1부, 법인등기부등본 원본 1부<br>통합 전자지불서비스 계약서 2부, 사업자등록증 사본 2부, 정산통장 사본 1부
					</td>
				</tr>
				<tr>
					<th scope="row">계약서</th>
					<td>
						<a href="http://file.wisa.co.kr/_data/pg_data/NICE_payment_agreement_wisa.pdf" target="_blank">다운로드 및 작성 방법보기</a>
					</td>
				</tr>
				<tr>
					<th scope="row">발송처</th>
					<td>(우)121-709<br>서울특별시 마포구 마포대로 217 크레디트센터 8층 나이스정보통신㈜ PG사업2본부 계약담당자 앞</td>
				</tr>
			</table>


		<?}elseif($mode == 'ksnet') {?>
			내용필요

		<?}elseif($mode == 'danal') {?>
			내용필요

		<?}?>

		<h3>04.가입비 납부</h3>
		<p class="icon">가입비는 계약서류 발송 후 2~3일내 결제해 주셔야 합니다.</p>
		<table class="tbl_pg">
			<caption class="hidden">서비스 안내</caption>
			<colgroup>
				<col style="width:20%">
			</colgroup>
			<tr>
				<th scope="row">납부방법안내</th>
				<td>
					- 신용카드 : 온라인 신용카드 결제<br>
					- 계좌이체 : 온라인 계좌이체 결제<br>
					- 무통장입금<br><br>
					<a href="<?=$registration?>" target="_blank">등록비 결제 바로가기</a><br>
					(무통장 입금시 상호명 또는 대표자명으로 입금해 주시기 바랍니다.)
				</td>
			</tr>
		</table>
		<h3>05.보증보험 가입</h3>
		<dl>
			<dt>보증보험이란?</dt>
			<dd>만일에 발생할 수 있는 사고로부터 고객보호를 위해 발급되는 보험증권입니다. 아래의 <strong>보증보험신청하기</strong>을 클릭하여 온라인 증권발급요청서를 작성합니다. 작성 후 보중보험서 담당자의 안내에 따라 보험료를 결제해주세요.</dd>
			<dd><a href="<?=$insurance?>" target="_blank">보증보험 신청하기</a></dd>
		</dl>
		<h3>06.카드사 심사</h3>
		<p class="icon">신용카드사에서는 PG사 관리 지침에 따른 심사제도를 운영하고 있습니다.</p>
		<h3>07.카드사 승인</h3>
		<p class="icon">서비스 이용을 위한 결제시스템을 연동하기 위하여 <strong>WISA 1:1 고객센터</strong>문의 글을 작성해 주시면 작업이 진행됩니다.<br>(승인정보 등록시 오류방지를 위하여 등록대행 및 이용시 불편사항을 상담하여 드립니다.)<br><a href="#" onclick="goMywisa('?body=customer@cs_reg'); return false;">고객센터로 이동</a></p>
		<h3>08.서비스 연동</h3>
		<p class="icon">카드사 등록이 완료되면, 승인정보를 관리자에 적용합니다.<br>신용카드를 제외한 부가서비스는 연동 즉시 이용하실 수 있습니다.</p>
		<h3>09.서비스 오픈</h3>
	</div>
</div>