<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  소셜쿠폰 사용하기
	' +----------------------------------------------------------------------------------------------+*/

	$_replace_code[$_file_name][form_start]="<form name=\"cpnFrm\" method=\"post\" action=\"".$root_url."/main/exec.php\" target=\"hidden".$now."\" onSubmit=\"return checkcpnFrm(this);\">
<input type=\"hidden\" name=\"exec_file\" value=\"mypage/sccoupon.exe.php\">
";
	$_replace_code[$_file_name][form_end]="</form>";
?>