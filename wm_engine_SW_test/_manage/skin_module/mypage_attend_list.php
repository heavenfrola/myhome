<?PHP

	$_replace_code[$_file_name]['mypage_member_attend']="";
	$_replace_hangul[$_file_name]['mypage_member_attend']="출석횟수";
	$_code_comment[$_file_name]['mypage_member_attend']="출석 체크를 완료한 횟수 출력";
	$_auto_replace[$_file_name]['mypage_member_attend']="Y";

	$_replace_code[$_file_name]['mypage_attend_point']="";
	$_replace_hangul[$_file_name]['mypage_attend_point']="총출석포인트";
	$_code_comment[$_file_name]['mypage_attend_point']="출석 체크로 얻은 총 포인트/적립금";
	$_auto_replace[$_file_name]['mypage_attend_point']="Y";

	$_replace_code[$_file_name]['mypage_attendNum']="";
	$_replace_hangul[$_file_name]['mypage_attendNum']="포인트적용출석횟수";
	$_code_comment[$_file_name]['mypage_attendNum']="포인트/적립금이 적용되는 연속 출석 횟수";
	$_auto_replace[$_file_name]['mypage_attendNum']="Y";

	$_replace_code[$_file_name]['mypage_attendPoint']="";
	$_replace_hangul[$_file_name]['mypage_attendPoint']="적용포인트";
	$_code_comment[$_file_name]['mypage_attendPoint']="출석 체크가 횟수만큼 완료되었을 경우 적용 포인트";
	$_auto_replace[$_file_name]['mypage_attendPoint']="Y";

	$_replace_code[$_file_name]['mypage_attendMilage']="";
	$_replace_hangul[$_file_name]['mypage_attendMilage']="적용적립금";
	$_code_comment[$_file_name]['mypage_attendMilage']="출석 체크가 횟수만큼 완료되었을 경우 적용 적립금";
	$_auto_replace[$_file_name]['mypage_attendMilage']="Y";

	$_replace_code[$_file_name]['mypage_this_year']="";
	$_replace_hangul[$_file_name]['mypage_this_year']="현재연도";
	$_code_comment[$_file_name]['mypage_this_year']="현재 연도 출력";
	$_auto_replace[$_file_name]['mypage_this_year']="Y";

	$_replace_code[$_file_name]['mypage_this_month']="";
	$_replace_hangul[$_file_name]['mypage_this_month']="현재월";
	$_code_comment[$_file_name]['mypage_this_month']="현재 월 출력";
	$_auto_replace[$_file_name]['mypage_this_month']="Y";

	$_replace_code[$_file_name]['mypage_pre_year_url']="";
	$_replace_hangul[$_file_name]['mypage_pre_year_url']="이전연도보기";
	$_code_comment[$_file_name]['mypage_pre_year_url']="이전 연도 조회 링크 주소 출력";
	$_auto_replace[$_file_name]['mypage_pre_year_url']="Y";

	$_replace_code[$_file_name]['mypage_next_year_url']="";
	$_replace_hangul[$_file_name]['mypage_next_year_url']="다음연도보기";
	$_code_comment[$_file_name]['mypage_next_year_url']="다음 연도 조회 링크 주소 출력";
	$_auto_replace[$_file_name]['mypage_next_year_url']="Y";

	$_replace_code[$_file_name]['mypage_pre_month_url']="";
	$_replace_hangul[$_file_name]['mypage_pre_month_url']="이전달보기";
	$_code_comment[$_file_name]['mypage_pre_month_url']="이전 달 조회 링크 주소 출력";
	$_auto_replace[$_file_name]['mypage_pre_month_url']="Y";

	$_replace_code[$_file_name]['mypage_next_month_url']="";
	$_replace_hangul[$_file_name]['mypage_next_month_url']="다음달보기";
	$_code_comment[$_file_name]['mypage_next_month_url']="다음 달 조회 링크 주소 출력";
	$_auto_replace[$_file_name]['mypage_next_month_url']="Y";

	$_replace_code[$_file_name]['mypage_attend_form_start']="";
	$_replace_hangul[$_file_name]['mypage_attend_form_start']="출석체크폼시작";
	$_code_comment[$_file_name]['mypage_attend_form_start']="출석 체크 실행 폼 시작 선언";
	$_auto_replace[$_file_name]['mypage_attend_form_start']="Y";

	$_replace_code[$_file_name]['mypage_attend_form_end']="";
	$_replace_hangul[$_file_name]['mypage_attend_form_end']="출석체크폼끝";
	$_code_comment[$_file_name]['mypage_attend_form_end']="출석 체크 실행 폼 끝 선언";
	$_auto_replace[$_file_name]['mypage_attend_form_end']="Y";

	$_replace_code[$_file_name]['mypage_check_calendar']="";
	$_replace_hangul[$_file_name]['mypage_check_calendar']="출석현황달력";
	$_code_comment[$_file_name]['mypage_check_calendar']="날짜별 출석 현황을 TR TD 태그를 포함한 달력으로 출력";
	$_auto_replace[$_file_name]['mypage_check_calendar']="Y";

	$_replace_code[$_file_name]['mypage_attend_type'] = $cfg['attendMP'] == 'M' ? '적립금' : '포인트';
	$_replace_hangul[$_file_name]['mypage_attend_type'] = '출석체크보상방법';
	$_code_comment[$_file_name]['mypage_attend_type'] = '출석체크 달성시 보상방법을 적립금/포인트 중 출력합니다.';
	$_auto_replace[$_file_name]['mypage_attend_type'] = 'Y';

	$_replace_code[$_file_name]['mypage_attend_reward_list'] = '';
	$_replace_hangul[$_file_name]['mypage_attend_reward_list'] = '출석체크적립안내';
	$_code_comment[$_file_name]['mypage_attend_reward_list'] = '출석체크 목록을 출력';
	$_replace_datavals[$_file_name]['mypage_attend_reward_list']="지급적립금:prize_milage;지급포인트:prize_point;지급쿠폰명:prize_cno;총출석횟수:complete_day;출석횟수:check_cnt;";

?>