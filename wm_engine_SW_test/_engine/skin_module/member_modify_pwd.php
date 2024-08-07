<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  비밀번호 찾기 - 회원 비밀번호 변경
	' +----------------------------------------------------------------------------------------------+*/

	$_replace_code[$_file_name]['form_start'] = "<form name=\"pwdFrm\" method=\"post\" action=\"".$root_url."/main/exec.php\" target=\"hidden".$now."\" onSubmit=\"return chkPwdFrm(this);\" style=\"margin:0px;\">
<input type=\"hidden\" name=\"exec_file\" value=\"member/modify_pwd.exe.php\">
<input type=\"hidden\" name=\"key\" value=\"".$_GET['key']."\">
";

	$_replace_code[$_file_name]['member_id'] = $data['member_id'];
	$_replace_code[$_file_name]['member_name'] = $data['member_name'];
	$_replace_code[$_file_name]['form_end'] = "</form>";

?>