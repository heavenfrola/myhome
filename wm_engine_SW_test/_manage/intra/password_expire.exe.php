<?php

/**
 * 관리자 비밀번호 만료 변경
 **/

$admin = $pdo->assoc("select no, pwd from {$tbl['mng']} where no=?", array(
    $_SESSION['access_admin_no']
));

$password_old = trim($_POST['password_old']);
$password_new = trim($_POST['password_new']);
$password_cfm = trim($_POST['password_cfm']);
$password_now = $pdo->row("select pwd from {$tbl['mng']} where no='{$admin['no']}'");

// 기존 비밀번호 체크
if (empty($password_old) == true) {
    msg('현재 비밀번호를 입력해주세요.');
}
if (strcmp(sql_password($password_old), $password_now) !== 0) {
    msg('현재 비밀번호가 일치하지 않습니다.');
}

// 신규 비밀번호 체크
if (empty($password_new) == true) {
    msg('신규 비밀번호를 입력해주세요.');
}
if (empty($password_cfm) == true) {
    msg('확인 비밀번호를 입력해주세요.');
}
if (strcmp($password_new, $password_cfm) !== 0) {
    msg('신규 비밀번호와 확인 비밀번호가 일치하지 않습니다.');
}
if (strcmp(sql_password($password_new), $password_now) === 0) {
    msg('현재 비밀번호와 신규 비밀번호가 같습니다.');
}
if (preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[$@$!%*#?&])[A-Za-z\d$@$!%*#?&]{8,}$/', $password_new) == false) {
    msg('비밀번호는 영문, 숫자, 특수문자를 조합하여 8자 이상으로 입력해주세요.');
}
if (preg_match('/(.)\1\1\1/', $password_new) == true) {
    msg('비밀번호는 동일한 문자를 4회 이상 연속 입력할 수 없습니다.');
}

$pdo->query("update {$tbl['mng']} set pwd=?, expire_pwd=? where no=?", array(
    sql_password($password_new),
    date('Y-m-d', strtotime("+{$cfg['mng_pass_expire']} months")),
    $admin['no']
));

// 세션 생성
$_SESSION['admin_no'] = $admin['no'];
unset($_SESSION['access_admin_no']);

msg('', '?body=main@main', 'parent');