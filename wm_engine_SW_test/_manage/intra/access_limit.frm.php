<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' | 계정잠금 해제 인증
	' +----------------------------------------------------------------------------------------------+*/

	$admin_no = numberOnly($_SESSION['access_admin_no']);
	$mng_data = $pdo->assoc("select email, cell from `$tbl[mng]` where no='$admin_no'");

?>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/intra_factor.js"></script>
<style type="text/css" title="">
body {background-color:#303742;}
</style>
<div class="admin_login">
	<h1><a href="http://www.wisa.co.kr" target="_blank"><img src="<?=$engine_url?>/_manage/image/intra/logo.png" alt="WISA."></a></h1>
	<div class="box">
		<h2>계정 잠금 해제</h2>
		<p class="msg">아이디 또는 비밀번호 <span>오류가 <?=$cfg['access_lock']?>회 실패</span>하여 사용이 중지되었습니다.<br>등록되어 있는 휴대폰번호 또는 이메일 인증 후 해제가 가능합니다.</p>
		<form name="unlockFrm" method="post" target="hidden<?=$now?>" action="./index.php" onSubmit="return unlockchk(this);">
		<input type="hidden" name="body" value="intra@access_limit.exe">
		<input type="hidden" name="exec" value="">
			<div class="select">
				<label><input type="radio" id="find_cell" name="find_type" value="1" onclick="typechk(1)" checked> 휴대폰 번호로 인증</label>
				<label><input type="radio" id="find_email" name="find_type" value="2" onclick="typechk(2)"> 이메일로 인증</label>
			</div>
			<div>
				<input type="text" id="text_cell" name="cell" value="<?=$mng_data['cell']?>" class="form_input_access disabled block" placeholder="휴대폰 (-없이 입력)" readOnly><span id="counter" style="display:none"></span>
				<input type="text" id="text_email" name="email" value="<?=$mng_data['email']?>" class="form_input_access disabled block" placeholder="이메일" readOnly>
			</div>
			<div><input type="button" id="btn_confirm" name="btn_confirm" value="인증번호 받기" onclick="confirmSend(document.unlockFrm);"  class="btn white"></div>
			<div><input type="text" id="reg_code" name="reg_code" class="form_input_access block" placeholder="인증번호 입력"></div>
			<div><input type="submit" value="확인" class="btn"></div>
		</form>
	</div>
</div>