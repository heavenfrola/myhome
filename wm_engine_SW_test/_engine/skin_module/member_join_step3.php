<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  회원가입 완료
	' +----------------------------------------------------------------------------------------------+*/


	$_replace_code[$_file_name]['join_end_regdate'] = date('Y/m/d');
	
	if($member['mailing']=="Y") {
		$mail_text = " ".__lang_agree_email_receiving__;
	}else {
		$mail_text = " ".__lang_agree_email_optout__;
	}
	if($member['sms']=="Y") {
		$sms_text = __lang_agree_sms_receiving__.",";
	}else {
		$sms_text = __lang_agree_sms_optout__.",";
	}
	$_replace_code[$_file_name]['join_end_agree'] = sprintf(__lang_agree_sms_email_yn__, $sms_text.$mail_text);

	if($cfg['join_14_limit'] == "B" && $cfg['join_14_limit_method'] = 1 && $member['14_limit'] == "Y") {
		$_replace_code[$_file_name]['join_end_14limit'] = "Y";
		unset($_SESSION['member_no']);
	} else {
	    $_replace_code[$_file_name]['join_end_14up'] = "Y";
	}

?>