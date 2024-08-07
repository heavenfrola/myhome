<?PHP

	$mng_url_file = $engine_dir.'/_engine/include/account/getMngUrl.inc.php';
	if(file_exists($mng_url_file)) {
		include_once $mng_url_file;
	}

	if($_SESSION['ssokey'] && $_SESSION['ssoId']) {
		$_use['direct_login'] = 'Y';
	}

	if($_use['direct_login'] == 'Y') {
		include_once $engine_dir.'/_manage/main/login_direct.inc.php';
		return;
	}

	common_header();

?>
<link rel="stylesheet" type="text/css" href="<?=$engine_url?>/_manage/css/login.css.php?engine_url=<?=urlencode($engine_url)?>">
<div class="admin_login">
	<h1><a href="http://www.wisa.co.kr" target="_blank"><img src="<?=$engine_url?>/_manage/image/login/logo.png" alt="WISA."></a></h1>
	<div class="box">
		<p>로그인이 필요한 서비스입니다.</p>
		<p>로그인 후 장시간 자리를 비우셨거나 로그인을 하지 않으셨습니다. 로그인 후 이용해주세요!</p>
		<span class="lock"></span>
		<a href="http://www.wisa.co.kr" target="_blank" class="btn">로그인</a>
	</div>
</div>
<?php close(1); ?>