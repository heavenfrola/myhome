<?php

include_once $engine_dir."/_engine/include/common.lib.php";


if(empty($_POST['facebookid']) || empty($_POST['name']) || empty($_POST['email'])){
	$login_type_fail = "페이스북 계정 오류";
	return;
}

$bind = array(
    ':email' => $_POST['email']
);

// 가입 여부 체크
$facebook_member_no = $pdo->row("SELECT no FROM wm_member WHERE member_id=:email and login_type='facebook'", $bind);

// 첫 로그인 이면 임시 회원 가입 처리
if(!$facebook_member_no){

	// 이미 페이스북 메일로 가입된 이메일 주소인지 체크 후에 회원가입 처리함
	$facebook_no = $pdo->row("SELECT no FROM wm_member WHERE member_id=:email", $bind);
	if(!empty($facebook_no)) {
		$login_type_fail = "이미 가입되어 있는 메일주소입니다.";
		return;
	}
	$sql="INSERT INTO `$tbl[member]` (`member_id`, `name` , `email` , `ip` , `reg_date`, `login_type`, `access_id`) VALUES ('".$_POST[email]."', '".$_POST[name]."', '".$_POST[email]."', '".$_SERVER[REMOTE_ADDR]."', '$now' , 'facebook', '".$_POST[facebookid]."')";
	$pdo->query($sql);
	$facebook_member_no = $pdo->lastInsertId();
	$data = $pdo->assoc("select * from `$tbl[member]` where `no`='$facebook_member_no'");
} else {
	$data = $pdo->assoc("select * from `$tbl[member]` where `no`='$facebook_member_no'");
}

$login_type = "facebook";
?>