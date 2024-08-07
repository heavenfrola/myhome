<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  회원 아이디/비번찾기
	' +----------------------------------------------------------------------------------------------+*/
	
	if($cfg['find_search']!="Y") {
		$find_page = 'member/find_id.exe.php';
		$_replace_code[$_file_name]['find_pwd_new_type'] = "Y";
	}else {
		$find_page = 'member/new_find_id.exe.php';
		$_replace_code[$_file_name]['find_pwd_new_type'] = "";
	}
	$_replace_code[$_file_name]['find_id_form_start']="<form name=\"findFrm1\" method=\"post\" action=\"".$root_url."/main/exec.php\" target=\"hidden".$now."\" onSubmit=\"return checkFind(this,1)\" style=\"margin:0px\">
<input type=\"hidden\" name=\"exec_file\" value=\"".$find_page."\">
<input type=\"hidden\" name=\"ftype\" value=\"1\">
";
	$_replace_code[$_file_name]['find_id_form_email']=getModuleContent("find_id_form_email");
	$_replace_code[$_file_name]['find_id_form_end']="</form>";
	$_replace_code[$_file_name]['find_pwd_form_start']="<form name=\"findFrm2\" method=\"post\" action=\"".$root_url."/main/exec.php\" target=\"hidden".$now."\" onSubmit=\"return checkFind(this,2)\" style=\"margin:0px\">
<input type=\"hidden\" name=\"exec_file\" value=\"".$find_page."\">
<input type=\"hidden\" name=\"ftype\" value=\"2\">
";
	$_replace_code[$_file_name]['find_pwd_form_end']="</form>";
	// 2009-09-10 : SMS 방식 추가 - Han
	$_sms_popw=$_skin['sms_find_sizew'] ? $_skin['sms_find_sizew'] : 500;
	$_sms_poph=$_skin['sms_find_sizeh'] ? $_skin['sms_find_sizeh'] : 300;
	$_replace_code[$_file_name]['sms_find_url']="javascript:wingSmsFind(".$_sms_popw.", ".$_sms_poph.");";

	$id_find_default = 0;
	if($cfg['join_jumin_use'] != 'Y' && $cfg['member_confirm_email'] != 'Y' && $cfg['member_confirm_sms'] != 'Y') {
		$cfg['member_confirm_email'] = 'Y';
		$id_find_default = 1;
	}
	if($cfg['member_confirm_sms'] == 'Y') $id_find_default = 2;
	if($cfg['member_confirm_email'] == 'Y') $id_find_default = 1;
	$id_find_checked[$id_find_default] = 'checked';

	$_replace_code[$_file_name]['find_id_selector'] = '';
	$_replace_code[$_file_name]['find_pw_selector'] = '';
	
	if($cfg['find_search']!="Y") {
		if($cfg['join_jumin_use'] == 'Y') $_replace_code[$_file_name]['find_id_selector'] = "<input type='radio' name='find_id_type' value='1' onclick='setFindIDPW(this)' $id_find_checked[0] />주민번호 ";
		if($cfg['member_confirm_email'] == 'Y') $_replace_code[$_file_name]['find_id_selector'] .= "<label><input type='radio' name='find_id_type' value='2' onclick='setFindIDPW(this)' $id_find_checked[1] /> ".__lang_member_info_atype2a__.'</label>';
		if($cfg['member_confirm_sms'] == 'Y') $_replace_code[$_file_name]['find_id_selector'] .= "<label><input type='radio' name='find_id_type' value='3' onclick='setFindIDPW(this)' $id_find_checked[2] /> ".__lang_member_info_atype1a__.'</label>';

		if($cfg['join_jumin_use'] == 'Y') $_replace_code[$_file_name]['find_pw_selector'] = "<input type='radio' name='find_pw_type' value='1' onclick='setFindIDPW(this)' $id_find_checked[0] />주민번호 ";
		if($cfg['member_confirm_email'] == 'Y') $_replace_code[$_file_name]['find_pw_selector'] .= "<label><input type='radio' name='find_pw_type' value='2' onclick='setFindIDPW(this)' $id_find_checked[1] /> ".__lang_member_info_atype2a__.'</label>';
		if($cfg['member_confirm_sms'] == 'Y') $_replace_code[$_file_name]['find_pw_selector'] .= "<label><input type='radio' name='find_pw_type' value='3' onclick='setFindIDPW(this)' $id_find_checked[2] /> ".__lang_member_info_atype1a__.'</label>';
	}else {
		$_replace_code[$_file_name]['find_id_selector'] .= "<label><input type='radio' name='find_id_type' value='2' onclick='setFindIDPW(this)' $id_find_checked[1] /> ".__lang_member_info_atype2a__.'</label>';
		$_replace_code[$_file_name]['find_id_selector'] .= "<label><input type='radio' name='find_id_type' value='3' onclick='setFindIDPW(this)' $id_find_checked[2] /> ".__lang_member_info_atype1a__.'</label>';
		$_replace_code[$_file_name]['find_pw_selector'] .= "<label><input type='radio' name='find_pw_type' value='2' onclick='setFindIDPW(this)' $id_find_checked[1] /> ".__lang_member_info_atype2a__.'</label>';
		$_replace_code[$_file_name]['find_pw_selector'] .= "<label><input type='radio' name='find_pw_type' value='3' onclick='setFindIDPW(this)' $id_find_checked[2] /> ".__lang_member_info_atype1a__.'</label>';
	}
?>
<script type='text/javascript'>
$(document).ready(function() {
	setFindIDPW($(':checked[name=find_id_type]')[0]);
	setFindIDPW($(':checked[name=find_pw_type]')[0]);
});
</script>