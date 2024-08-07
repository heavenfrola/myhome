<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  회원가입 약관출력
	' +----------------------------------------------------------------------------------------------+*/

	$_replace_code[$_file_name]['join_rull_url']=$root_url."/content/content.php?cont=join_rull&mode=1";
	$_replace_code[$_file_name]['join_rull_agree']="<input type=\"checkbox\" id=\"ck_agree\" class='join1_chk'>";
	$_replace_code[$_file_name]['join_privacy_url']=$root_url."/content/content.php?cont=privacy&mode=1&hidden=Y";
	$_replace_code[$_file_name]['join_privacy_agree']="<input type=\"checkbox\" id=\"ck_privacy\" class='join1_chk'>";
	$_replace_code[$_file_name]['join_agree_url']="javascript:joinAgree();";

	$_replace_code[$_file_name]['join_14_limit_method'] = ($cfg['join_14_limit_method'] == 2 && $cfg['join_14_limit'] == "C") ? "<input type=\"checkbox\" id=\"14_up_agree\" class='join1_chk'>":"";

	$_tmp = '';
	if($cfg['ipin_use'] == 'Y') $_tmp .= "<label><input type='radio' name='cprvd' value='ipin' /> 아이핀인증</label> ";
	if($cfg['ipin_checkplus_use'] == 'Y') $_tmp .= "<label><input type='radio' name='cprvd' value='ipinCheckPlus' /> 휴대폰인증</label> ";
    if ($scfg->comp('use_kcb', 'Y')) $_tmp .= "<label><input type='radio' name='cprvd' value='kcb' /> 휴대폰 본인확인</label> ";
	$_replace_code[$_file_name]['select_cp'] = $_tmp;
	unset($_tmp);

	$_replace_code[$_file_name]['join_all_chk'] = "<script>$(function() {new chainCheckbox($('.join_all_chk'), $('.join1_chk'))});</script><input type='checkbox' id='agree_all' class='join_all_chk'>";

?>