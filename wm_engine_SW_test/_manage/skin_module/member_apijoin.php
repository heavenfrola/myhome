<?PHP


	$_replace_code[$_file_name]['form_start']="";
	$_replace_hangul[$_file_name]['form_start']="api가입폼시작";
	$_code_comment[$_file_name]['form_start']="SNS 가입폼 시작선언";
	$_auto_replace[$_file_name]['form_start']="Y";


	$_replace_code[$_file_name]['form_end']="";
	$_replace_hangul[$_file_name]['form_end']="api가입폼끝";
	$_code_comment[$_file_name]['form_end']="SNS 가입 폼 끝 선언";
	$_auto_replace[$_file_name]['form_end']="Y";


	$_replace_code[$_file_name]['sns_join_name']="";
	$_replace_hangul[$_file_name]['sns_join_name']="SNS가입이름";
	$_code_comment[$_file_name]['sns_join_name']="SNS 가입 이름 선언";
	$_auto_replace[$_file_name]['sns_join_name']="Y";


	$_replace_code[$_file_name]['sns_join_email']="";
	$_replace_hangul[$_file_name]['sns_join_email']="SNS가입이메일";
	$_code_comment[$_file_name]['sns_join_email']="SNS 가입 이메일 선언";
	$_auto_replace[$_file_name]['sns_join_email']="Y";

	$_replace_code[$_file_name]['sns_join_cell'] = '';
	$_replace_hangul[$_file_name]['sns_join_cell']="SNS가입휴대폰번호";
	$_code_comment[$_file_name]['sns_join_cell']="SNS 가입 휴대폰번호 선언";
	$_auto_replace[$_file_name]['sns_jsns_join_cell']="Y";

	$_replace_code[$_file_name]['birth1_select']="";
	$_replace_hangul[$_file_name]['birth1_select']="SNS생년월일년선택";
	$_code_comment[$_file_name]['birth1_select']="SNS생년월일 사용시 년입력 구문 출력";
	$_replace_datavals[$_file_name]['birth1_select']="Y";


	$_replace_code[$_file_name]['birth2_select']="";
	$_replace_hangul[$_file_name]['birth2_select']="SNS생년월일월선택";
	$_code_comment[$_file_name]['birth2_select']="SNS생년월일 사용시 월입력 구문 출력";
	$_replace_datavals[$_file_name]['birth2_select']="Y";


	$_replace_code[$_file_name]['birth3_select']="";
	$_replace_hangul[$_file_name]['birth3_select']="SNS생년월일일선택";
	$_code_comment[$_file_name]['birth3_select']="SNS생년월일 사용시 일입력 구문 출력";
	$_replace_datavals[$_file_name]['birth3_select']="Y";

	$_replace_code[$_file_name]['join_sex']="";
	$_replace_hangul[$_file_name]['join_sex']="성별";
	$_code_comment[$_file_name]['join_sex']="성별 사용시 입력 구문 출력";
	$_replace_datavals[$_file_name]['join_sex']="성별남체크:sex_ck1;성별여체크:sex_ck2;";

	$_replace_code[$_file_name]['join_birth']="";
	$_replace_hangul[$_file_name]['join_birth']="생년월일";
	$_code_comment[$_file_name]['join_birth']="생년월일 사용시 입력 구문 출력";
	$_replace_datavals[$_file_name]['join_birth']="생년월일년선택:birth1_select;생년월일월선택:birth2_select;생년월일일선택:birth3_select;생년월일양체크:birth_type_ck1;생년월일음체크:birth_type_ck2;";

	$_replace_code[$_file_name]['join_nick_chk']="";
	$_replace_hangul[$_file_name]['join_nick_chk']="정보수정닉네임";
	$_code_comment[$_file_name]['join_nick_chk']="정보수정시 아이디 구문 출력";
	$_replace_datavals[$_file_name]['join_nick_chk']="닉네임:nick;닉네임중복체크:nick_dbl_ck;";

	$_replace_code[$_file_name]['join_rull_agree']="";
	$_replace_hangul[$_file_name]['join_rull_agree']="이용약관동의";
	$_code_comment[$_file_name]['join_rull_agree']="이용 약관 페이지 동의 체크박스 출력";
	$_auto_replace[$_file_name]['join_rull_agree']="Y";

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

	$_replace_code[$_file_name]['join_required_birthday'] = ($cfg['join_birth_use'] == 'Y' && $cfg['member_join_birth'] == 'Y') ? 'required' : '';
	$_replace_hangul[$_file_name]['join_required_birthday'] = '생년월일필수여부';
	$_code_comment[$_file_name]['join_required_birthday'] = '생년월일이 가입 필수항목일 경우 required 문자가 출력됩니다.';
	$_auto_replace[$_file_name]['join_required_birthday'] = '';

	$_replace_code[$_file_name]['join_required_gender'] = ($cfg['join_sex_use'] == 'Y' && $cfg['member_join_sex'] == 'Y') ? 'required' : '';
	$_replace_hangul[$_file_name]['join_required_gender'] = '성별필수여부';
	$_code_comment[$_file_name]['join_required_gender'] = '성별이 가입 필수항목일 경우 required 문자가 출력됩니다.';
	$_auto_replace[$_file_name]['join_required_gender'] = '';

?>