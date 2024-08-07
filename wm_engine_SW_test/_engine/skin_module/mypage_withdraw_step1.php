<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  탈퇴요청
	' +----------------------------------------------------------------------------------------------+*/

	$_replace_code[$_file_name][form_start]="<form method=\"post\" action=\"".$root_url."/main/exec.php\" target=\"hidden".$now."\" onSubmit=\"return checkDraw(this)\" style=\"margin:0px;text-align:center;\">
<input type=\"hidden\" name=\"exec_file\" value=\"mypage/withdraw.exe.php\">
";
	$_replace_code[$_file_name][form_end]="</form>";

?>