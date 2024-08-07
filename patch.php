<?PHP
/*패치 파일 */
include '_config/set.php';
include_once $engine_dir.'/_engine/include/common.lib.php';

printAjaxHeader();

$_patch_name = 'patch';

$http_host_patch = preg_replace('/^((https?:)?\/\/)(www\.)?/', '', $_SERVER['HTTP_HOST']);
$http_host_patch = preg_replace('/^(www\.)?/', '', $http_host_patch);

$now_status = 'N';
if($_GET['patch']) {
	setcookie($_patch_name, "", time() - 3600, "/",'.'.$http_host_patch);
	setcookie($_patch_name, "", time() - 3600, "/");
	if($_GET['patch'] == 'Y'){
		$cookie_time = $now+60*60*24*1;
		setCookie($_patch_name, 'Y', $cookie_time, '/','.'.$http_host_patch);
		$now_status = 'Y';
	}
}else{
	$now_status = $_COOKIE[$_patch_name];
}

?>
<html>
<head>
	<link rel="stylesheet" type="text/css" href="<?=$engine_url?>/_manage/css/manage.css">
	<style type="text/css">
        body {
            background: #e8e8e8;
        }
        #contentArea {
            margin: 0 auto;
            width: 500px;
        }
	</style>
</head>
<body id="manage">
<div id="container">
	<header id="adminHeader">
	</header>
	<section id="wrapper">
		<div id="contentArea">
			<?if($now_status  == 'Y') {?>
				<div class="box_title first" style="color:blue;">패치가 적용 된 상태입니다.</div>
			<?} else {?>
				<div class="box_title first" style="color:black;">패치가 해제되어있습니다.</div>
			<?}?>
			<div class="box_middle2">
				<span class="box_btn blue"><a href="?patch=Y">현재 PC에 패치 적용</a></span>
				<span class="box_btn gray"><a href="?patch=N">패치 적용 해제</a></span>
			</div>
			<div class="box_bottom">
						<span class="box_btn large">
							<a href="/">테스트하기</a>
						</span>
			</div>
		</div>
	</section>
</div>
</body>
</html>