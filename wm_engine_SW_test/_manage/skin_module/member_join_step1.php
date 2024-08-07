<?PHP

	$_replace_code[$_file_name]['join_rull_url']="";
	$_replace_hangul[$_file_name]['join_rull_url']="이용약관주소";
	$_code_comment[$_file_name]['join_rull_url']="이용 약관 페이지 링크 주소 출력";
	$_auto_replace[$_file_name]['join_rull_url']="Y";

	$_replace_code[$_file_name]['join_rull_agree']="";
	$_replace_hangul[$_file_name]['join_rull_agree']="이용약관동의";
	$_code_comment[$_file_name]['join_rull_agree']="이용 약관 페이지 동의 체크박스 출력";
	$_auto_replace[$_file_name]['join_rull_agree']="Y";

	$_replace_code[$_file_name]['join_privacy_url']="";
	$_replace_hangul[$_file_name]['join_privacy_url']="개인정보취급방침주소";
	$_code_comment[$_file_name]['join_privacy_url']="개인정보취급방침 페이지 링크 주소 출력";
	$_auto_replace[$_file_name]['join_privacy_url']="Y";

	$_replace_code[$_file_name]['join_privacy_agree']="";
	$_replace_hangul[$_file_name]['join_privacy_agree']="개인정보취급방침동의";
	$_code_comment[$_file_name]['join_privacy_agree']="개인정보취급방침 동의 체크박스 출력";
	$_auto_replace[$_file_name]['join_privacy_agree']="Y";

	$_replace_code[$_file_name]['join_14_limit_method'] = '';
	$_replace_hangul[$_file_name]['join_14_limit_method'] = '만14세이상가입동의';
	$_code_comment[$_file_name]['join_14_limit_method'] = '만 14세 이상 가입동의 체크박스 출력';
	$_auto_replace[$_file_name]['join_14_limit_method'] = 'Y';

	$_replace_code[$_file_name]['join_all_chk'] = '';
	$_replace_hangul[$_file_name]['join_all_chk'] = '가입페이지전체동의';
	$_code_comment[$_file_name]['join_all_chk'] = '회원 가입 페이지 전체 동의 체크박스 출력';
	$_auto_replace[$_file_name]['join_all_chk'] = 'Y';

	$_replace_code[$_file_name]['join_agree_url']="";
	$_replace_hangul[$_file_name]['join_agree_url']="회원가입동의";
	$_code_comment[$_file_name]['join_agree_url']="약관 동의시 회원가입 폼 링크 주소 출력";
	$_auto_replace[$_file_name]['join_agree_url']="Y";

	$_replace_code[$_file_name]['use_biz']=$cfg['use_biz_member'] == 'Y' ? 'Y' : '';
	$_replace_hangul[$_file_name]['use_biz']="사업자회원사용";
	$_code_comment[$_file_name]['use_biz']="사업자회원사용여부";
	$_auto_replace[$_file_name]['use_biz']="Y";

	$_replace_code[$_file_name]['use_cert_any'] = ($cfg['member_confirm_email'] == 'Y' || $cfg['member_confirm_sms'] == 'Y') ? 'Y' : '';
	$_replace_hangul[$_file_name]['use_cert_any'] = '가입인증사용여부';
	$_code_comment[$_file_name]['use_cert_any'] = '가입인증(휴대폰/이메일) 사용시 Y 출력';
	$_auto_replace[$_file_name]['use_cert_any'] = 'Y';

	$_replace_code[$_file_name]['use_cert_sms'] = $cfg['member_confirm_sms'] == 'Y' ? 'Y' : '';
	$_replace_hangul[$_file_name]['use_cert_sms'] = '휴대전화인증사용여부';
	$_code_comment[$_file_name]['use_cert_sms'] = '휴대전화인증 사용시 Y 출력';
	$_auto_replace[$_file_name]['use_cert_sms'] = 'Y';

	$_replace_code[$_file_name]['cert_phone_link'] = '<a href="#" onclick="showCertForm(1)">';
	$_replace_hangul[$_file_name]['cert_phone_link'] = '휴대전화인증링크';
	$_code_comment[$_file_name]['cert_phone_link'] = '휴대전화인증링크 A태그';
	$_auto_replace[$_file_name]['cert_phone_link'] = 'Y';

	$_replace_code[$_file_name]['cert_sms1_form_start'] = '<div id="sms_cert_layer" style="display:none;">';
	$_replace_hangul[$_file_name]['cert_sms1_form_start'] = '휴대전화인증폼시작';
	$_code_comment[$_file_name]['cert_sms1_form_start'] = '휴대전화인증폼 전체 시작';
	$_auto_replace[$_file_name]['cert_sms1_form_start'] = 'Y';

	$_replace_code[$_file_name]['cert_sms1_form_end'] = '</div>';
	$_replace_hangul[$_file_name]['cert_sms1_form_end'] = '휴대전화인증폼끝';
	$_code_comment[$_file_name]['cert_sms1_form_end'] = '휴대전화인증폼 전체 끝';
	$_auto_replace[$_file_name]['cert_sms1_form_end'] = 'Y';

	$_replace_code[$_file_name]['cert_sms2_form_start'] = '<form method="post" onsubmit="return createCertCode(1, this);">';
	$_replace_hangul[$_file_name]['cert_sms2_form_start'] = '휴대전화인증번호받기폼시작';
	$_code_comment[$_file_name]['cert_sms2_form_start'] = '휴대전화인증폼 인증 전화번호 입력폼 시작';
	$_auto_replace[$_file_name]['cert_sms2_form_start'] = 'Y';

	$_replace_code[$_file_name]['cert_sms2_form_end'] = '</form>';
	$_replace_hangul[$_file_name]['cert_sms2_form_end'] = '휴대전화인증번호받기폼끝';
	$_code_comment[$_file_name]['cert_sms2_form_end'] = '휴대전화인증폼 인증 전화번호 입력폼 끝';
	$_auto_replace[$_file_name]['cert_sms2_form_end'] = 'Y';

	$_replace_code[$_file_name]['cert_sms3_form_start'] = '<form id="sms_cert_frm" onsubmit="return checkCertCode(this);" style="display:none;">';
	$_replace_hangul[$_file_name]['cert_sms3_form_start'] = '휴대전화인증번호입력폼시작';
	$_code_comment[$_file_name]['cert_sms3_form_start'] = '휴대전화인증폼 인증번호 입력폼 시작';
	$_auto_replace[$_file_name]['cert_sms3_form_start'] = 'Y';

	$_replace_code[$_file_name]['cert_sms3_form_end'] = '</form>';
	$_replace_hangul[$_file_name]['cert_sms3_form_end'] = '휴대전화인증번호입력폼끝';
	$_code_comment[$_file_name]['cert_sms3_form_end'] = '휴대전화인증폼 인증번호 입력폼 끝';
	$_auto_replace[$_file_name]['cert_sms3_form_end'] = 'Y';

	$_replace_code[$_file_name]['use_cert_email'] = $cfg['member_confirm_email'] == 'Y' ? 'Y' : '';
	$_replace_hangul[$_file_name]['use_cert_email'] = '이메일인증사용여부';
	$_code_comment[$_file_name]['use_cert_email'] = '이메일인증 사용시 Y 출력';
	$_auto_replace[$_file_name]['use_cert_email'] = 'Y';

	$_replace_code[$_file_name]['cert_email_link'] = '<a href="#" onclick="showCertForm(2)">';
	$_replace_hangul[$_file_name]['cert_email_link'] = '이메일인증링크';
	$_code_comment[$_file_name]['cert_email_link'] = '이메일인증링크 A태그';
	$_auto_replace[$_file_name]['cert_email_link'] = 'Y';

	$_replace_code[$_file_name]['cert_email1_form_start'] = '<div id="email_cert_layer" style="display:none;">';
	$_replace_hangul[$_file_name]['cert_email1_form_start'] = '이메일인증폼시작';
	$_code_comment[$_file_name]['cert_email1_form_start'] = '휴대전화인증폼 전체 시작';
	$_auto_replace[$_file_name]['cert_email1_form_start'] = 'Y';

	$_replace_code[$_file_name]['cert_email1_form_end'] = '</div>';
	$_replace_hangul[$_file_name]['cert_email1_form_end'] = '이메일인증폼끝';
	$_code_comment[$_file_name]['cert_email1_form_end'] = '휴대전화인증폼 전체 끝';
	$_auto_replace[$_file_name]['cert_email1_form_end'] = 'Y';

	$_replace_code[$_file_name]['cert_email2_form_start'] = '<form method="post" onsubmit="return createCertCode(2, this);">';
	$_replace_hangul[$_file_name]['cert_email2_form_start'] = '이메일인증번호받기폼시작';
	$_code_comment[$_file_name]['cert_email2_form_start'] = '이메일인증폼 인증 전화번호 입력폼 시작';
	$_auto_replace[$_file_name]['cert_email2_form_start'] = 'Y';

	$_replace_code[$_file_name]['cert_email2_form_end'] = '</form>';
	$_replace_hangul[$_file_name]['cert_email2_form_end'] = '이메일인증번호받기폼끝';
	$_code_comment[$_file_name]['cert_email2_form_end'] = '이메일인증폼 인증 전화번호 입력폼 끝';
	$_auto_replace[$_file_name]['cert_email2_form_end'] = 'Y';

	$_replace_code[$_file_name]['cert_email3_form_start'] = '<form id="email_cert_frm" onsubmit="return checkCertCode(this);" style="display:none;">';
	$_replace_hangul[$_file_name]['cert_email3_form_start'] = '이메일인증번호입력폼시작';
	$_code_comment[$_file_name]['cert_email3_form_start'] = '이메일인증폼 인증번호 입력폼 시작';
	$_auto_replace[$_file_name]['cert_email3_form_start'] = 'Y';

	$_replace_code[$_file_name]['cert_email3_form_end'] = '</form>';
	$_replace_hangul[$_file_name]['cert_email3_form_end'] = '이메일인증번호입력폼끝';
	$_code_comment[$_file_name]['cert_email3_form_end'] = '이메일인증폼 인증번호 입력폼 끝';
	$_auto_replace[$_file_name]['cert_email3_form_end'] = 'Y';

	$_replace_code[$_file_name]['select_cp'] = '';
	$_replace_hangul[$_file_name]['select_cp'] = '실명인증방법선택';
	$_code_comment[$_file_name]['select_cp'] = '아이핀/체크플러스 인증방법 선택';
	$_auto_replace[$_file_name]['select_cp'] = 'Y';

?>