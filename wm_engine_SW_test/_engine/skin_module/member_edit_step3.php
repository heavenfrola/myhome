<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  회원정보 변경 완료
	' +----------------------------------------------------------------------------------------------+*/


	$_replace_code[$_file_name]['join_edit_date'] = date('Y/m/d');

	$chg_date = "";
	$mail_text = "";
	$sms_text = "";
	if($_POST['mail_chg_yn']=="Y") {
		$chg_date = "Y";
		$mail_text = ($member['mailing']=="Y") ? __lang_agree_email_receiving__ : __lang_agree_email_optout__;
	}
	if($_POST['sms_chg_yn']=="Y") {
		$chg_date = "Y";
		$sms_text  = ($member['sms']=="Y") ? __lang_agree_sms_receiving__ : __lang_agree_sms_optout__;
		$sms_text .= ($mail_text) ? ',' : '';
	}
	$_replace_code[$_file_name]['join_edit_yn'] = $chg_date;
	$_replace_code[$_file_name]['join_edit_agree'] = sprintf(__lang_agree_sms_email_yn__, $sms_text.$mail_text);

?>