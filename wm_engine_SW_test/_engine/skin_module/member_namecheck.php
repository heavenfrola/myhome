<?

	/* +----------------------------------------------------------------------------------------------+
	' |  실명인증
	' +----------------------------------------------------------------------------------------------+*/

	$_replace_code[$_file_name][form_start]="<form name=\"pageForm\" method=\"POST\">
<input type=\"hidden\" id=\"foreigner\" name=\"foreigner\" value=\"1\">
<input type=\"hidden\" id=\"inqRsn\" name=\"inqRsn\" value=\"10\">
";
	$_replace_code[$_file_name][form_end]="</form>";

	$_replace_code[$_file_name][complete_url]="javascript:goIDCheck();";


?>