<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  휴대폰번호로 회원비밀번호 찾기
	' +----------------------------------------------------------------------------------------------+*/

		$_replace_code[$_file_name][form_start]="<form name=\"findFrm2\" method=\"post\" action=\"".$root_url."/main/exec.php\" target=\"hidden".$now."\" onSubmit=\"return checkFind(this,3)\" style=\"margin:0;padding:0;\">
<input type=\"hidden\" name=\"exec_file\" value=\"member/find_id.exe.php\">
<input type=\"hidden\" name=\"ftype\" value=\"3\">
";
		$_replace_code[$_file_name][form_end]="</form>";
		$_replace_code[$_file_name][sms_auth_url]="javascript:smsAuthSend();";

?>