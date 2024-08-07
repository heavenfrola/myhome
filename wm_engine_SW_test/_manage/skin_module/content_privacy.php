<?PHP

	$_replace_code[$_file_name]['hidden_start']="";
	$_replace_hangul[$_file_name]['hidden_start']="숨김처리시작";
	$_code_comment[$_file_name]['hidden_start']="회원가입 시 방침 내용 중 숨김처리 시작 부분";
	$_auto_replace[$_file_name]['hidden_start']="Y";

	$_replace_code[$_file_name]['hidden_end']="";
	$_replace_hangul[$_file_name]['hidden_end']="숨김처리끝";
	$_code_comment[$_file_name]['hidden_end']="회원가입 시 방침 내용 중 숨김처리 끝 부분";
	$_auto_replace[$_file_name]['hidden_end']="Y";

	// 2009-10-09 : 개인정보 취급 방침 페이지와 회원약관 구분 - Han
	$_replace_code[$_file_name]['privacy_join_part']="";
	$_replace_hangul[$_file_name]['privacy_join_part']="개인정보취급방침전용";
	$_code_comment[$_file_name]['privacy_join_part']="개인정보취급방침 페이지의 전용 출력 구문";

	$_replace_code[$_file_name]['privacy_join_part']="";
	$_replace_hangul[$_file_name]['privacy_join_part']="회원가입전용";
	$_code_comment[$_file_name]['privacy_join_part']="회원가입 시 전용 출력 구문";
	$_auto_replace[$_file_name]['privacy_join_part'] = 'Y';

    $_replace_code[$_file_name]['company_privacy_list'] = '';
    $_replace_hangul[$_file_name]['company_privacy_list'] = '이전개인정보처리방침목록';
    $_code_comment[$_file_name]['company_privacy_list'] = '이전개인정보처리방침목록';
    $_auto_replace[$_file_name]['company_privacy_list'] = 'Y';

    $_replace_code[$_file_name]['company_privacy_cont'] = '';
    $_replace_hangul[$_file_name]['company_privacy_cont'] = '개인정보처리방침본문';
    $_code_comment[$_file_name]['company_privacy_cont'] = '개인정보처리방침본문';
    $_auto_replace[$_file_name]['company_privacy_cont'] = 'Y';
