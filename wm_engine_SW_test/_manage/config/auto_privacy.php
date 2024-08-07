<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  개인정보처리방침 자동생성
	' +----------------------------------------------------------------------------------------------+*/

	// 수집하는 개인정보의 항목
	$privacy['일반정보']=array("이름", "생년월일", "성별", "로그인ID", "비밀번호", "비밀번호 질문과 답변", "자택 전화번호", "자택 주소", "휴대전화번호", "이메일");
	$privacy['직장정보']=array("직업", "회사명", "부서", "직책", "회사전화번호");
	$privacy['관심사, 기념일정보']=array("취미", "결혼여부", "기념일");
	$privacy['법정대리인정보']=array("법정대리인정보");
	$privacy['활동 정보 등']=array("종교", "학력", "신체정보");
	$privacy['금융정보']=array("신용카드 정보", "은행계좌 정보", "학력", "신체정보");
	$privacy['자동 생성 정보']=array("서비스 이용기록", "접속 로그", "쿠키", "접속 IP 정보", "결제기록", "기타정보");
	$privacy['기타정보']=array("기타");

	// 개인정보의 수집 및 이용목적
	$use['서비스']=array("콘텐츠 제공", "구매 및 요금 결제", "물품배송 또는 청구서 등 발송", "금융거래 본인 인증 및 금융 서비스", "요금추심");
	$use['회원관리']=array("회원제 서비스 이용에 따른 본인확인", "개인 식별", "불량회원의 부정 이용 방지와 비인가 사용 방지", "가입 의사 확인", "연령확인", "만14세 미만 아동 개인정보 수집 시 법정 대리인 동의여부 확인", "불만처리 등 민원처리", "고지사항 전달");
	$use['마케팅']=array("신규 서비스(제품) 개발 및 특화", "이벤트 등 광고성 정보 전달", "인구통계학적 특성에 따른 서비스 제공 및 광고 게재", "접속 빈도 파악 또는 회원의 서비스 이용에 대한 통계");
	$use['기타']=array("기타");


	// 개인정보 파기 절차 및 방법
	$destroy['파기방법']=array("파일 재사용이 불가능한 방법으로 삭제", "물리적으로 분쇄 또는 소각", "기타");

?>
<form name="prvFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="return prvCk(this);">
	<input type="hidden" name="body" value="config@auto_privacy.exe">
	<div class="box_full">
		<ul class="list_msg">
			<li>아래의 사항을 정확히 체크하신 뒤 확인을 누르시면 개인정보처리방침에 관련된 시스템이 쇼핑몰에 세팅됩니다.</li>
			<li>세팅이 완료된 이후 개인정보처리방침 페이지는 <a href="/_manage/?body=design@editor&edit_pg=2%2F5">디자인관리 > 페이지편집</a>에서 페이지를 편집하십시오.</li>
		</ul>
	</div>
	<div class="box_title">
		<h2 class="title">수집하는 개인정보의 항목</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">수집하는 개인정보의 항목</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<?
			$ii = 0;
			foreach($privacy as $key=>$val){
		?>
		<tr>
			<th scope="row"><?=$key?></th>
			<td>
			<?
				$xx = 0;
				foreach($privacy[$key] as $key2=>$val2){
					echo "<label for=\"p{$ii}\" class=\"p_cursor\" style=\"padding-right:10px;\"><input type=\"checkbox\" name=\"privacy[]\" value=\"$val2\" id=\"p{$ii}\"> $val2</label>";
					if($val2 == "기타") echo " <input type=\"text\" name=\"privacy_etc\" class=\"input\"> ( , 로구분)";
					$ii++;
					$xx++;
				}
			?>
			</td>
		</tr>
		<?}?>
	</table>
	<div class="box_title">
		<h2 class="title">개인정보의 수집 및 이용목적</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">개인정보의 수집 및 이용목적</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<?
			$ii=$jj=0;
			foreach($use as $key=>$val){
		?>
		<tr>
			<th scope="row"><?=$key?></th>
			<td>
			<?
				foreach($val as $key2=>$val2){
					$br = $val2 == "기타" ? '' : '<br>';
					echo "<label for=\"u{$ii}\" class=\"p_cursor\" style=\"padding-right:10px;\"><input type=\"checkbox\" name=\"use_{$jj}[]\" value=\"$val2\" id=\"u{$ii}\"> $val2</label>".$br;
					if($val2 == "기타") echo "<input type=\"text\" name=\"use_etc\" class=\"input\"> ( , 로구분)";
					echo "";
					$ii++;
				}
				$jj++;
			?>
			</td>
		</tr>
		<?}?>
	</table>
	<div class="box_title">
		<h2 class="title">개인정보 파기 절차 및 방법</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">개인정보의 수집 및 이용목적</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">파기 절차 및 방법</th>
			<td>
				<?
					$ii=0;
					foreach($destroy['파기방법'] as $key=>$val){
						$br = $val == "기타" ? '' : '<br>';
						echo "<label for=\"d{$ii}\" class=\"p_cursor\" style=\"padding-right:10px;\"><input type=\"checkbox\" name=\"destroy[]\" value=\"$val\" id=\"d{$ii}\"> $val</label>".$br;
						if($val == "기타") echo " <input type=\"text\" name=\"destroy_etc\" class=\"input\"> ( , 로구분)";
						$ii++;
					}
				?>
			</td>
		</tr>
	</table>
	<div class="box_title">
		<h2 class="title">개인정보 파기 절차 및 방법</h2>
	</div>
	<div class="box_bottom top_line left">
		<dl>
			<dt>쿠키와 같은 개인정보 자동수집 장치를 설치운영하고 있는지 여부와, 설치/운영 시 그 거부 방법에 대해 입력합니다.</dt>
			<dd><label class="p_cursor"><input type="radio" name="cookie" value="Y"> 예</label></dd>
			<dd><label class="p_cursor"><input type="radio" name="cookie" value="N" checked> 아니오</label></dd>
		</dl>
	</div>
	<div style="padding-top:20px; text-align:center;"><span class="box_btn blue" style="text-align:center;"><input type="submit" value="확인"></span></div>
</form>

<script language="JavaScript">
	function prvCk(f){
		if(!confirm('개인정보 처리방침 파일을 생성하시겠습니까?')) return false;
	}
</script>