<?PHP

	$account_id = preg_replace('/^http:\/\/|\..*$/', '', $manage_url);
	$shopid = 'ws_'.$account_id;

	// 신청여부 체크
	$show_status = $wec->get(520, 'shopid='.$shopid, 1);

	$resp_stat = $show_status[0]->stat[0];
	$resp_date = $show_status[0]->confirm_date[0];

	switch($resp_stat) {
		case '1' :
			$show_msg = "<li>쇼핑하우 가입신청대기중입니다.</li>\n<li>승인이후 사용하실수 있습니다.</li>";
		break;
		case '2' :
			$show_msg = "<li>이미 쇼핑하우 가입신청이 승인된 쇼핑몰입니다.</li>";
		break;
		case '3' :
			$show_msg = "<li>쇼핑하우 가입신청이 반려되었습니다.</li>\n<li>1:1 고객센터로 문의 글을 작성하여 반려사유 확인 후 재승인 요청을 해 주시기 바랍니다.</li>";
		break;
	}


	$_scates = array(
		'A' => '디지털가전/휴대폰',
		'C' => '영상/생활/계절가전',
		'U' => '주방/이미용/건강가전',
		'B' => '컴퓨터/주변기기',
		'S' => '여성의류',
		'T' => '남성의류',
		'G' => '의류브랜드',
		'F' => '신발/수제화',
		'J' => '가방/지갑/잡화',
		'H' => '스포츠패션',
		'E' => '명품',
		'D' => '해외쇼핑',
		'K' => '쥬얼리/시계/액세서리',
		'L' => '화장품/향수',
		'P' => '유아동/출산',
		'V' => '가구/인테리어',
		'W' => '침구/커튼/카페트',
		'N' => '생활/주방/문구',
		'Q' => '스포츠/레저/취미',
		'O' => '식품/슈퍼마켓',
		'R' => '공연',
		'Y' => '자동차용품',
	);

?>
<?if($show_msg){?>
<ul class='desc1' style='margin:20px; padding: 5px; border: solid 1px #ddd; background: #f8f8f8;'>
	<?=$show_msg?>
</ul>
<?return;}?>

<form id="showJoin" method="post" enctype="multipart/form-data" action="?" target="hidden<?=$now?>" onsubmit="return showCk(this)">
	<input type="hidden" name="body" value="openmarket@show_join.exe">
	<input type="hidden" name="url" value="<?=$manage_url?>">
	<input type="hidden" name="shopid" value="<?=$shopid?>">
	<div class="box_title first">
		<h2 class="title">다음 쇼핑하우 가입신청</h2>
	</div>
	<div class="box_middle left show_join">
		<h3>다음 쇼핑하우 이용약관</h3>
		<div class="frame">
			<?
				$content = file_get_contents($engine_dir.'/_manage/openmarket/show_agree1.txt');
				echo nl2br($content);
			?>
		</div>
		<p><label class="p_cursor"><input type="checkbox" id="show_agree1"> 약관을 모두 숙지하였으며, 동의합니다.</label></p>
		<h3>개인정보 정보수집 이용동의</h3>
		<div class="frame">
			<?
				$content = file_get_contents($engine_dir.'/_manage/openmarket/show_agree2.txt');
				echo nl2br($content);
			?>
		</div>
		<p><label class="p_cursor"><input type="checkbox" id="show_agree2"> 개인정보 정보수집 이용약관에 동의합니다.</label></p>
	</div>
	<table class="tbl_row">
		<caption class="hidden">운영자 정보</caption>
		<colgroup>
			<col style="width:10%">
			<col style="width:10%">
			<col>
		</colgroup>
		<tr>
			<th colspan="2">쇼핑몰 아이디</th>
			<td>
				<?=$shopid?>
			</td>
		</tr>
		<tr>
			<th colspan="2"><strong>다음 통합 광고주아이디</strong></th>
			<td>
				<input type="text" name="loginid" class="input" size="15">
				<span class="explain">
					광고주 아이디가 없으실 경우 먼저 다음 광고주 회원가입을 해 주시기 바랍니다.
					<span class="box_btn_s"><input type="button" value="다음 광고주 가입" onclick="daumCommerce()"></span>
				</span>
			</td>
		</tr>
		<tr>
			<th colspan="2"><strong>쇼핑몰명(한글)</strong></th>
			<td>
				<input type="text" name="shopname" class="input" size="30" value="<?=$cfg[company_name]?>">
			</td>
		</tr>
		<tr>
			<th colspan="2"><strong>쇼핑몰명(영문)</strong></th>
			<td>
				<input type="text" name="shopengname" class="input" size="30">
			</td>
		</tr>
		<tr>
			<th colspan="2"><strong>대표카테고리</strong></th>
			<td>
				<?=selectArray($_scates, 'categoryid', 2, null, 'S')?>
			</td>
		</tr>
		<tr>
			<th colspan="2"><strong>대표URL</strong></th>
			<td>
				<input type="text" name="domain" class="input" size="50" value="<?=$root_url?>?ref2=daum_show">
				<span class="explain">가급적 기본도메인이 아닌 본도메인 연결 후 신청해 주시기 바랍니다.</span>
			</td>
		</tr>
		<tr>
			<th colspan="2"><strong>대표전화번호</strong></th>
			<td>
				<input type="text" name="tel" class="input" size="20" value="<?=$cfg[company_phone]?>">
			</td>
		</tr>
		<tr>
			<th colspan="2"><strong>고객센터전화번호</strong></th>
			<td>
				<input type="text" name="cstel" class="input" size="20">
			</td>
		</tr>
		<tr>
			<th colspan="2"><strong>고객문의 메일주소</strong></th>
			<td>
				<input type="text" name="csmail" class="input" size="50">
			</td>
		</tr>
		<tr>
			<th colspan="2"><strong>사업자등록번호</strong></th>
			<td>
				<input type="text" name="biznum" class="input" size="20" value="<?=$cfg['company_biz_num ']?>">
				<dl class="list_msg">
					<dt>다음 경우 반려사유가 되므로 확인해주시기 바랍니다.</dt>
					<dd>- 해당 사업자 번호가 다음광고주 회원 정보에 없을 경우</dd>
					<dd>- 광고주 아이디의 정보와 다를 경우</dd>
					<dd>- 해당 사업자번호나 도메인이 같은 업체가 이미 쇼핑하우에 입점되어 있을 경우</dd>
				</dl>
			</td>
		</tr>
		<tr>
			<th colspan="2">법인번호(주민번호)</th>
			<td>
				<input type="text" name="corpnum" class="input" size="50">
			</td>
		</tr>
		<tr>
			<th colspan="2">통신판매번호</th>
			<td>
				<input type="text" name="salenum" class="input" size="50" value="<?=$cfg['company_online_num']?>">
			</td>
		</tr>
		<tr>
			<th colspan="2"><strong>회사소개</strong><div class="explain">(한글 100자, 영문 200자 이내)</div></th>
			<td>
				<textarea name="corppt" class="txta" cols="80" rows="5"></textarea>
			</td>
		</tr>
		<tr>
			<th rowspan="4" class="line_r">제휴담당자</th>
			<th><strong>이름</strong></th>
			<td>
				<input type="text" name="joname" class="input" size="20">
			</td>
		</tr>
		<tr>
			<th><strong>핸드폰번호</strong></th>
			<td>
				<input type="text" name="johpnum" class="input" size="20">
			</td>
		</tr>
		<tr>
			<th><strong>전화번호</strong></th>
			<td>
				<input type="text" name="jotel" class="input" size="20">
			</td>
		</tr>
		<tr>
			<th><strong>이메일</strong></th>
			<td>
				<input type="text" name="jomail" class="input" size="50">
			</td>
		</tr>
		<tr>
			<th rowspan="4" class="line_r">정산담당자</th>
			<th>이름</th>
			<td>
				<input type="text" name="acname" class="input" size="20">
			</td>
		</tr>
		<tr>
			<th>핸드폰번호</th>
			<td>
				<input type="text" name="achpnum" class="input" size="20">
			</td>
		</tr>
		<tr>
			<th>전화번호</th>
			<td>
				<input type="text" name="actel" class="input" size="20">
			</td>
		</tr>
		<tr>
			<th>이메일</th>
			<td>
				<input type="text" name="acmail" class="input" size="50">
			</td>
		</tr>
		<tr>
			<th rowspan="4" class="line_r">기술담당자</th>
			<th>이름</th>
			<td>
				<input type="text" name="tename" class="input" size="20">
			</td>
		</tr>
		<tr>
			<th>핸드폰번호</th>
			<td>
				<input type="text" name="tehpnum" class="input" size="20">
			</td>
		</tr>
		<tr>
			<th>전화번호</th>
			<td>
				<input type="text" name="tetel" class="input" size="20">
			</td>
		</tr>
		<tr>
			<th>이메일</th>
			<td>
				<input type="text" name="temail" class="input" size="50">
			</td>
		</tr>
		<tr>
			<th rowspan="4" class="line_r">쇼핑박스담당자</th>
			<th>이름</th>
			<td>
				<input type="text" name="sbname" class="input" size="20">
			</td>
		</tr>
		<tr>
			<th>핸드폰번호</th>
			<td>
				<input type="text" name="sbhpnum" class="input" size="20">
			</td>
		</tr>
		<tr>
			<th>전화번호</th>
			<td>
				<input type="text" name="sbtel" class="input" size="20">
			</td>
		</tr>
		<tr>
			<th>이메일</th>
			<td>
				<input type="text" name="sbmail" class="input" size="50">
			</td>
		</tr>
		<tr>
			<th colspan="2"><strong>몰로고 이미지</strong><div class="explain">좌측정렬 이미지</div></th>
			<td>
				<input type="file" name="logoimg1" class="input" size="50">
				<ul class="list_msg">
					<li>사이즈 65x15의 gif 파일로 제작하셔야 합니다.</li>
					<li>움직이는 이미지(animated GIF)는 사용하실수 없습니다.</li>
				</ul>
			</td>
		</tr>
		<tr>
			<th colspan="2"><strong>몰로고 이미지</strong><div class="explain">중앙정렬 이미지</div></th>
			<td>
				<input type="file" name="logoimg2" class="input" size="50">
				<ul class="list_msg">
					<li>사이즈 65x15의 gif 파일로 제작하셔야 합니다.</li>
					<li>움직이는 이미지(animated GIF)는 사용하실수 없습니다.</li>
				</ul>
			</td>
		</tr>
	</table>
	<input type="hidden" name="hostingname" value="wisa">
	<input type="hidden" name="agreeyn" value="Y">
	<input type="hidden" name="agreetime" value="<?=date('YmdHis')?>">
	<input type="hidden" name="agreeip" value="<?=$_SERVER['REMOTE_ADDR']?>">
	<input type="hidden" name="cagreeyn" value="Y">
	<input type="hidden" name="cagreetime" value="<?=date('YmdHis')?>">
	<input type="hidden" name="cagreeip" value="<?=$_SERVER['REMOTE_ADDR']?>">
	<input type="hidden" name="status" value="A">
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>

<script type="text/javascript">
	function daumCommerce() {
		window.open('https://user.biz.daum.net/joinuser/advertiser.do');
	}

	function showCk(f) {
		if(!checkBlank(f.loginid, '다음 통합 광고주아이디를 입력해주세요.')) return false;
		if(!checkBlank(f.shopname, '쇼핑몰명(한글)을 입력해주세요.')) return false;
		if(!checkBlank(f.shopengname, '쇼핑몰명(영문)을 입력해주세요.')) return false;
		if(!checkBlank(f.domain, '대표URL을 입력해주세요.')) return false;
		if(!checkBlank(f.tel, '대표 전화번호를 입력해주세요.')) return false;
		if(!checkBlank(f.cstel, '고객센터 전화번호를 입력해주세요.')) return false;
		if(!checkBlank(f.csmail, '고객문의 메일주소를 입력해주세요.')) return false;
		if(!checkBlank(f.biznum, '사업자등록번호를 입력해주세요.')) return false;
		if(!checkBlank(f.corppt, '회사소개를 입력해주세요.')) return false;
		if(!checkBlank(f.joname, '제휴담당자 이름을 입력해주세요.')) return false;
		if(!checkBlank(f.johpnum, '제휴담당자 휴대폰번호를 입력해주세요.')) return false;
		if(!checkBlank(f.jotel, '제휴담당자 전화번호를 입력해주세요.')) return false;
		if(!checkBlank(f.jomail, '제휴담당자 메일주소를 입력해주세요.')) return false;
		if(!checkBlank(f.logoimg1, '몰로고 이미지(좌측)을 입력해주세요.')) return false;
		if(!checkBlank(f.logoimg2, '몰로고 이미지(중앙)을 입력해주세요.')) return false;

		if(document.getElementById('show_agree1').checked != true || document.getElementById('show_agree2').checked != true) {
			window.alert('약관동의에 체크해 주세요');
			return false;
		}

		return true;
	}
</script>