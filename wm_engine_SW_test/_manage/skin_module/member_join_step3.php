<?PHP

	$_replace_code[$_file_name]['join_end_regdate']="";
	$_replace_hangul[$_file_name]['join_end_regdate']="광고성정보등록일자";
	$_code_comment[$_file_name]['join_end_regdate']="광고성 정보 등록 일자";
	$_auto_replace[$_file_name]['join_end_regdate']="Y";

	$_replace_code[$_file_name]['join_end_agree']="";
	$_replace_hangul[$_file_name]['join_end_agree']="SMS이메일수신동의여부";
	$_code_comment[$_file_name]['join_end_agree']="SMS/이메일 수신 동의 여부";
	$_auto_replace[$_file_name]['join_end_agree']="Y";

	$_replace_code[$_file_name]['join_end_14limit']="";
	$_replace_hangul[$_file_name]['join_end_14limit']="만14세미만회원가입";
	$_code_comment[$_file_name]['join_end_14limit']="만 14세 미만 회원가입";
	$_auto_replace[$_file_name]['join_end_14limit']="Y";

	$_replace_code[$_file_name]['join_end_14up']="";
	$_replace_hangul[$_file_name]['join_end_14up']="만14세이상회원가입";
	$_code_comment[$_file_name]['join_end_14up']="만 14세 이상 회원가입";
	$_auto_replace[$_file_name]['join_end_14up']="Y";

	$_replace_code[$_file_name]['join_end_14agree']=($cfg['14_join_form_file']) ? $root_url.'/_data/member_addinfo/'.$cfg['14_join_form_file'] : $engine_url."/_manage/member/14_joinform.docx";
	$_replace_hangul[$_file_name]['join_end_14agree']="법정대리인동의서링크";
	$_code_comment[$_file_name]['join_end_14agree']="법정대리인 동의서 다운로드 링크주소";
	$_auto_replace[$_file_name]['join_end_14agree']="Y";

?>