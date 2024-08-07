<?PHP

	header("Content-type: text/css; charset=utf-8;");
	$engine_url = $_GET['engine_url'];

?>

body {background:#303742;}

.admin_login h1 {position:fixed; left:40px; top:40px;}
.admin_login .box {position:absolute; left:50%; top:50%; width:514px; height:394px; margin:-197px 0 0 -257px; background:url('<?=$engine_url?>/_manage/image/login/need.png') no-repeat center 133px; text-align:center;}
.admin_login .box p {position:absolute; left:0; top:0; text-indent:-9999px;}
.admin_login .box .lock {display:block; width:63px; height:91px; margin:0 auto; background:url('<?=$engine_url?>/_manage/image/login/lock.png') no-repeat; -webkit-animation: lock 1s infinite linear alternate; -moz-animation: lock 1s infinite linear alternate; -ms-animation: lock 1s infinite linear alternate; -o-animation: lock 1s infinite linear alternate; animation: lock 1s infinite linear alternate;}
.admin_login .box .btn {display:inline-block; width:250px; height:60px; margin-top:242px; border-radius:5px; background:#26ace2; color:#fff; font-size:20px; font-weight:bold; line-height:60px;}
.admin_login .box .btn:hover {background:#0e8ec2;}

@-webkit-keyframes lock {
	from {-webkit-transform: rotate(20deg);}
	to {-webkit-transform: rotate(-20deg);}
}
@-moz-keyframes lock {
	from {-webkit-transform: rotate(20deg);}
	to {-webkit-transform: rotate(-20deg);}
}
@-ms-keyframes lock {
	from {-webkit-transform: rotate(20deg);}
	to {-webkit-transform: rotate(-20deg);}
}
@-o-keyframes lock {
	from {-webkit-transform: rotate(20deg);}
	to {-webkit-transform: rotate(-20deg);}
}
@keyframes lock {
	from {-webkit-transform: rotate(20deg);}
	to {-webkit-transform: rotate(-20deg);}
}