<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=<?=_BASE_CHARSET_?>">
	<title>관리자 로그인</title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="stylesheet" type="text/css" href="<?=$engine_url?>/_manage/css/manage.css">
	<?php if(isset($cfg['favicon']) == true){ ?>
	<link rel="shortcut icon" href="<?=$root_url?>/favicon.ico">
	<?php } ?>
	<script type="text/javascript" src='<?=$engine_url?>/_engine/common/jquery/jquery-1.4.min.js'></script>
	<script type="text/javascript" src="<?=$engine_url?>/_engine/common/common.js"></script>
	<script type="text/javascript">
    const root_url = "<?=$root_url?>";
    const manage_url = "<?=$manage_url?>";
    function checkFrm(f){
		if (!f.admin_id.value) {
			window.alert('관리자 아이디를 입력하세요');
			f.admin_id.focus();
			return false;
		}
		if (!f.admin_pwd.value) {
			window.alert('비밀번호를 입력하세요');
			f.admin_pwd.focus();
			return false;
		}

        //자동로그인정보 저장
        let loginInfo = {};
        if (f.admin_id_save.checked) {
            loginInfo.id = f.admin_id.value
        }
        if (f.admin_pwd_save.checked) {
            loginInfo.pwd = f.admin_pwd.value
        }
        autoLoginInfo('admin', 'set', loginInfo);

		f.action = "/main/exec.php";
		return true;
	}

	$(function(){
		setTimeout(function() {
			if($('#admin_id').val() != '') $('#admin_id').focus().prev().hide();
			if($('#admin_pwd').val() != '') $('#admin_pwd').focus().prev().hide();

			$('.box_input .input').focus(function(){
				if($(this).val() == '') $(this).prev().hide();
			}).blur(function(){
				if($(this).val() == '') $(this).prev().show();
			});
		}, 100);
	});

	function bg_resize() {
		var body_w = $(window).width();
		var body_h = $(window).height();
		var ratio = body_w/body_h;
		if (ratio < 2.08) {
			$('body').css('background-size','auto 100%');
		} else {
			$('body').css('background-size','100% auto');
		}
	}
	$(document).ready(function() {
		bg_resize();
        //자동로그인 처리
        autoLoginInfo('admin', 'get').then((autoLogin) => {
            if (autoLogin.id) {
                document.querySelector('#admin_id').value = autoLogin.id;
                document.querySelector('#admin_id_save').checked = true
            }
            if (autoLogin.pwd) {
                document.querySelector('#admin_pwd').value = autoLogin.pwd;
                document.querySelector('#admin_pwd_save').checked = true
            }
        });
	});
	$(window).resize(function() {
		bg_resize();
	});
	</script>
</head>
<?php
/*기존정보 삭제*/
setcookie('admin_id_save', '', 0, '/');
setcookie('admin_pwd_save', '', 0, '/');
?>
<body id="admin_login">
	<iframe name="hidden<?=$now?>" src="" width="0" height="0" scrolling="no" frameborder="0" style="display:none"></iframe>
	<form method="post" action="/main/exec.php" onSubmit="return checkFrm(this)">
		<input type="hidden" name="exec_file" value="common/ssoLogin2.php">
		<input type="hidden" name="login_type" value="direct">
		<input type="hidden" name="site_code" value="<?=$_we['wm_key_code']?>">
		<input type="hidden" name="query_string" value="<?=makeQueryString()?>">
		<div class="box_input">
			<label for="admin_id" class="admin_id"><input type="text" name="admin_id" id="admin_id" value="" class="input" placeholder="아이디" autocomplete="username"></label>
			<label for="admin_pwd" class="admin_pwd"><input type="password" name="admin_pwd" id="admin_pwd" value="" class="input" placeholder="비밀번호" autocomplete="current-password"></label>
		</div>
		<input type="submit" value="로그인" class="btn_sign">
		<div class="opt_check">
			<input type="checkbox" name="admin_id_save" id="admin_id_save" value="1" style=""> <label for="admin_id_save">아이디 저장</label>
			<input type="checkbox" name="admin_pwd_save" id="admin_pwd_save" value="1"> <label for="admin_pwd_save">패스워드 저장</label>
		</div>
		<p class="info">
			(주) 위사 | 서울시 강남구 선릉로 111길 8 윙갤러리빌딩  | 고객센터 1599-4435<br>COPYRIGHT (C) WISA. ALL RIGHTS RESERVED
		</p>
	</form>
</body>
</html>