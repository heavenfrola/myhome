<?PHP

	$_replace_code[$_file_name]['form_start'] = '';
	$_replace_hangul[$_file_name]['form_start'] = '폼시작';
	$_code_comment[$_file_name]['form_start'] = '비밀번호 변경 폼 시작 선언';
	$_auto_replace[$_file_name]['form_start'] = 'Y';

	$_replace_code[$_file_name]['form_end'] = '';
	$_replace_hangul[$_file_name]['form_end'] = '폼끝';
	$_code_comment[$_file_name]['form_end'] = '비밀번호 변경 폼 끝 선언';
	$_auto_replace[$_file_name]['form_end'] = 'Y';

	$_replace_code[$_file_name]['member_id'] = '';
	$_replace_hangul[$_file_name]['member_id'] = '변경회원아이디';
	$_code_comment[$_file_name]['member_id'] = '비밀번호를 변경할 회원 아이디';
	$_auto_replace[$_file_name]['member_id'] = 'Y';

	$_replace_code[$_file_name]['member_name'] = '';
	$_replace_hangul[$_file_name]['member_name'] = '변경회원이름';
	$_code_comment[$_file_name]['member_name'] = '비밀번호를 변경할 회원 이름';
	$_auto_replace[$_file_name]['member_name'] = 'Y';

	$_replace_code[$_file_name]['pwd_change_find'] = ($member['no']) ? '' : 'Y';
	$_replace_hangul[$_file_name]['pwd_change_find'] = '비밀번호변경';
	$_code_comment[$_file_name]['pwd_change_find'] = '비밀번호 찾기를 통한 비밀번호 변경';
	$_auto_replace[$_file_name]['pwd_change_find'] = 'Y';

	$_replace_code[$_file_name]['pwd_change_guide'] = $member['no'];
	$_replace_hangul[$_file_name]['pwd_change_guide'] = '비밀번호변경주기';
	$_code_comment[$_file_name]['pwd_change_guide'] = '비밀번호 변경 주기만료로 인한 비밀번호 변경';
	$_auto_replace[$_file_name]['pwd_change_guide'] = 'Y';

	$_replace_code[$_file_name]['pwd_change_next'] = $root_url.'/main/exec.php?exec_file=member/modify_pwd.exe.php&next_change=Y';
	$_replace_hangul[$_file_name]['pwd_change_next'] = '다음에변경하기';
	$_code_comment[$_file_name]['pwd_change_next'] = '비밀번호 다음에 변경하기';
	$_auto_replace[$_file_name]['pwd_change_next'] = 'Y';

?>