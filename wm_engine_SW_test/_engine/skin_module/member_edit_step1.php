<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  회원정보 수정 비밀번호 인증
	' +----------------------------------------------------------------------------------------------+*/

	$_replace_code[$_file_name][form_start]="<form method=\"post\" action=\"".$root_url."/main/exec.php\" onSubmit=\"return checkEditStep1(this)\" target=\"hidden".$now."\" style=\"margin:0px;\">
<input type=\"hidden\" name=\"exec_file\" value=\"member/edit_step1.exe.php\">
";
	$_replace_code[$_file_name][form_end]="</form>";

?>