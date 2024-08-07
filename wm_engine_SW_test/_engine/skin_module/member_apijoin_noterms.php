<?PHP

/**
 * 약관 없이 SNS 바로 가입
 **/

$email = $_SESSION['sns_login']['email'];
$cell = str_replace('-', '', $_SESSION['sns_login']['cell']);
$inti_id = $pdo->row("select member_id from {$tbl['member']} where email=? or member_id=?", array($email, $email));
if (!$inti_id && $cell) {
    $inti_id = $pdo->row("select member_id from {$tbl['member']} where cell=?", array($cell));
}

// 폼
$_replace_code[$_file_name]['form_start']="<form name=\"joinFrm\" method=\"post\" action=\"".$root_url."/main/exec.php\" target=\"hidden".$now."\" onsubmit=\"printFLoading()\">
<input type=\"hidden\" name=\"exec_file\" value=\"member/join.exe.php\">
<input type=\"hidden\" name=\"sns_simplified\" value=\"Y\">
<input type=\"hidden\" name=\"sns_integrate\" value=\"Y\">
<input type=\"hidden\" name=\"sns_type\" value=\"{$_SESSION['sns_login']['sns_type']}\">
<input type=\"hidden\" name=\"member_id\" value=\"$inti_id\">
";
$_replace_code[$_file_name]['form_end'] = '</form>';

// 통합 할 이메일 아이디
$_replace_code[$_file_name]['sns_join_email'] = $inti_id;

?>