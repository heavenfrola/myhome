<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  smartMD 로그인 연동
	' +----------------------------------------------------------------------------------------------+*/

	if(!$cfg['logger_smartMD_id'] || !$cfg['logger_smartMD_sid']) msg('서비스 신청 후 이용해 주세요.', '?body=log@smartMD_apply');

	$cusPw = substr(str_replace('-', '', $_we['wm_key_code']), 2);

?>
<form action="https://logger.co.kr/login/loginPs.tsp" target="_blank" onsubmit="return checkform(this)">
	<input type="hidden" name="password" class="input" size="20" value="<?=$cusPw?>">
	<div class="box_title first">
		<h2 class="title">스마트MD 접속</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">스마트MD 접속</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">아이디</th>
			<td><input type="text" name="cusId" class="input" size="20" value="<?=$cfg['logger_smartMD_id']?>" readonly></td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="스마트MD 접속"></span>
	</div>
</form>

<script type="text/javascript">
	function checkform(f) {
		if(!checkBlank(f.password, '패스워드를 입력해주세요.')) return false;
		if(f.pw_cookie.checked == true) {
			setCookie('logger_smartMD_pw', f.password.value, 365);
		} else {
			setCookie('logger_smartMD_pw', '', 365);
		}
		return true;
	}
</script>