<?PHP

	$_replace_code[$_file_name]['total_milage']="";
	$_replace_hangul[$_file_name]['total_milage']="보유적립금액";
	$_code_comment[$_file_name]['total_milage']="보유한 총 적립 금액 출력";
	$_auto_replace[$_file_name]['total_milage']="Y";

	$_replace_code[$_file_name]['mypage_milage_list']="";
	$_replace_hangul[$_file_name]['mypage_milage_list']="적립금리스트";
	$_code_comment[$_file_name]['mypage_milage_list']="적립금 상세 내역 리스트";
	$_replace_datavals[$_file_name]['mypage_milage_list']="내역번호:idx;등록일:date;만료일:expire_date;상세내역:mtitle;적립금액:plus;사용금액:minus;소계:member_milage;";

	$_replace_code[$_file_name]['milage_expire'] = $cfg['milage_expire'];
	$_replace_hangul[$_file_name]['milage_expire'] = '적립금만료기한';
	$_code_comment[$_file_name]['milage_expire'] = '적립금 만료기한 설정이 출력됩니다.';
	$_auto_replace[$_file_name]['milage_expire'] = 'Y';

?>