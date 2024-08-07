<?PHP

	if($cfg['logger_smartMD_id']) {
		msg('이미 스마트MD 신청이 완료되었습니다.', '?body=log@smartMD');
	}

	$wec = new weagleEyeClient($_we, 'account');
	$account_id = $wec->call('getAccountId');

	if(!preg_match('/^[a-z][a-z0-9]+$/', $account_id)) {
		echo 'API 연동 오류';
		return;
	}

	$cusId = 'ws_SM'.$account_id;
	$cusPw = substr(str_replace('-', '', $_we['wm_key_code']), 2);

?>
<form method="post" action="http://logger.co.kr/register/registerPs.tsp" onSubmit="return formCheck(this)">
	<input type="hidden" name="body" value="log@smartMD.exe">
	<input type="hidden" name="partnerName" value="wisa">
	<input type="hidden" name="partnerNum" value="32741">
	<input type="hidden" name="cusId" value="<?=$cusId?>">
	<input type="hidden" name="level" value="4">
	<input type="hidden" name="isMobile" value="4">
	<input type="hidden" name="password" value="<?=$cusPw?>">
	<input type="hidden" name="password" value="<?=$cusPw?>">
	<input type="hidden" name="domain" value="<?=$root_url?>">
	<input type="hidden" name="returnURL" value="<?=$root_url?>/main/exec.php?exec_file=log/smartMD.exe.php">
	<input type="hidden" name="errorURL" value="">
	<div class="box_title first">
		<h2 class="title">스마트MD신청</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">스마트MD신청</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">상점명</th>
			<td><input type="text" name="siteName" class="input" size="30" value=""></td>
		</tr>
		<tr>
			<th scope="row">회사명</th>
			<td><input type="text" name="name" class="input" size="30" value=""></td>
		</tr>
		<tr>
			<th scope="row">담당자 성명</th>
			<td><input type="text" name="contactName" class="input" size="20" value=""></td>
		</tr>
		<tr>
			<th scope="row">담당자 직책</th>
			<td><input type="text" name="contactRole" class="input" size="20" value=""></td>
		</tr>
		<tr>
			<th scope="row">담당자 전화번호</th>
			<td><input type="text" name="phone" class="input" size="20" value="<?=$member['tel']?>"></td>
		</tr>
		<tr>
			<th scope="row">담당자 핸드폰번호</th>
			<td><input type="text" name="mobile" class="input" size="20" value="<?=$member['cell']?>"></td>
		</tr>
		<tr>
			<th scope="row">담당자 이메일</th>
			<td><input type="text" name="email" class="input" size="50" value="<?=$member['email']?>"></td>
		</tr>
	</table>
	<div class="box_middle">
		<ul class="list_msg left">
			<li>[확인] 버튼을 클릭하면 로거(LOGGER)의 회원약관, 서비스약관 및 개인정보수집동의에 대한 내용을 읽고 동의하신 것으로 간주됩니다.</li>
			<li><a href="#" onclick="openLoggerAgree(); return false;"><strong>이용약관을 확인합니다.</strong></a></li>
		</ul>
	</div>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>

<script type="text/javascript">
	function formCheck(f) {
		if(!checkText(f.siteName, '상점명을')) return false;
		if(!checkText(f.name, '회사명을')) return false;
		if(!checkText(f.contactName, '담당자 성명을')) return false;
		if(!checkText(f.contactRole, '담당자 직책을')) return false;
		if(!checkText(f.phone, '담당자 전화번호를')) return false;
		if(!checkText(f.mobile, '담당자 핸드폰번호를')) return false;
		if(!checkText(f.email, '담당자 이메일주소를')) return false;

		return confirm('서비스약관에 동의하시겠습니까?');
	}

	function openLoggerAgree() {
		window.open('http://www.logger.co.kr/2010/join/BizspringPolicy.tsp', 'agree', 'width=830px, height=400px, scrollbars=yes');
	}
</script>