<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  SNS 가입폼
	' +----------------------------------------------------------------------------------------------+*/


	$_replace_code[$_file_name][form_start]="<form name=\"joinFrm\" method=\"post\" action=\"".$root_url."/main/exec.php\" target=\"hidden".$now."\" onSubmit=\"return checkRegSNS(this);\">
	<input type=\"hidden\" name=\"exec_file\" value=\"member/join.exe.php\">
	<input type=\"hidden\" name=\"member_id_checked\" value=\"0\">
	<input type=\"hidden\" name=\"sns_type\" value=\"".$sns_type."\">
	<input type=\"hidden\" name=\"sns_cid\" value=\"".$cid."\">
	<input type=\"hidden\" name=\"sns_name\" value=\"".$name."\">
	<input type=\"hidden\" name=\"sns_email\" value=\"".$email."\">
	<input type=\"hidden\" name=\"sns_data\" value='".$serialize."'>";
	$_replace_code[$_file_name][form_end]="</form>";

	if(!$member[birth1] && $_SESSION['sns_login']['birth']) {
		$member[birth1] = substr($_SESSION['sns_login']['birth'], 0, 4);
	}
	if(!$member[birth2] && $_SESSION['sns_login']['birth']) {
		$member[birth2] = substr($_SESSION['sns_login']['birth'], 4, 2);
	}
	if(!$member[birth3] && $_SESSION['sns_login']['birth']) {
		$member[birth3] = substr($_SESSION['sns_login']['birth'], 6, 2);
	}

	$_replace_code[$_file_name]['sns_join_name']		= $name;
	$_replace_code[$_file_name]['sns_join_email']		= $email;
    $_replace_code[$_file_name]['sns_join_cell']        = (isset($_SESSION['sns_login']['cell']) == true) ? $_SESSION['sns_login']['cell'] : '';

	for($ii=date('Y'); $ii>=1900; $ii--){
		$birth1_arr[]=$ii;
	}
	for($ii=1; $ii<=12; $ii++){
		if($ii<10) $ii="0".$ii;
		$birth2_arr[]=$ii;
	}
	for($ii=1; $ii<=31; $ii++){
		if($ii<10) $ii="0".$ii;
		$birth3_arr[]=$ii;
	}
	$_replace_code[$_file_name][birth1_select]= selectArray($birth1_arr,'birth1',1,'----',$member[birth1]);
	$_replace_code[$_file_name][birth2_select]= selectArray($birth2_arr,'birth2',1,'--',$member[birth2]);
	$_replace_code[$_file_name][birth3_select]= selectArray($birth3_arr,'birth3',1,'--',$member[birth3]);

	if($cfg['join_birth_use'] == 'Y') {
		$_line = getModuleContent("join_birth");
		$_jbirth['birth1_select'] = $_replace_code[$_file_name]['birth1_select'];
		$_jbirth['birth2_select'] = $_replace_code[$_file_name]['birth2_select'];
		$_jbirth['birth3_select'] = $_replace_code[$_file_name]['birth3_select'];
		$_jbirth['birth_type_ck1'] = 'checked';
		$_replace_code[$_file_name]['join_birth'] = lineValues('join_birth', $_line, $_jbirth);
		unset($_jbirth);
	}
	if($cfg['join_sex_use'] == 'Y') {
		$_line = getModuleContent('join_sex');
		if($_SESSION['sns_login']['gender'] === 0 || $_SESSION['sns_login']['gender'] == 1) {
			if($_SESSION['sns_login']['gender']==1) {
				$_gender['sex_ck1'] = 'checked';
				$_gender['sex_ck2'] = '';
			}else {
				$_gender['sex_ck2'] = 'checked';
				$_gender['sex_ck1'] = '';
			}
		}
		$_replace_code[$_file_name]['join_sex'] = lineValues('join_sex', $_line,  $_gender);
	}

	$_replace_code[$_file_name]['join_rull_agree']="<input type=\"checkbox\" id=\"ck_agree\" class=\"join1_chk\">";
	$_replace_code[$_file_name]['join_privacy_agree']="<input type=\"checkbox\" id=\"ck_privacy\" class=\"join1_chk\">";

	$_replace_code[$_file_name]['join_14_limit_method'] = ($cfg['join_14_limit_method'] == 2 && $cfg['join_14_limit'] == "C") ? "<input type=\"checkbox\" id=\"14_up_agree\">":"";

	$_replace_code[$_file_name]['join_all_chk'] = "<script>$(function() {new chainCheckbox($('.join_all_chk'), $('.join1_chk'))});</script><input type='checkbox' id='agree_all' class='join_all_chk'>";

	if($cfg['member_join_nickname'] != 'N') {
		$_line=getModuleContent('join_nick_chk');
		$_jnick['nick_dbl_ck'] = 'javascript:checkDuplNick(document.joinFrm.nick);';
		$_jnick['nick'] = $member['nick'];
		$_replace_code[$_file_name]['join_nick_chk'] = lineValues('join_nick_chk', $_line, $_jnick);
	}

?>