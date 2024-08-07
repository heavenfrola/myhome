<?PHP
	/* +----------------------------------------------------------------------------------------------+
	' |  [매장지도] 매장 상세 내용
	' +----------------------------------------------------------------------------------------------+*/
	$_replace_code[$_file_name]['store_location_info_title'] = '';
	$_replace_hangul[$_file_name]['store_location_info_title'] = '상호명';
	$_auto_replace[$_file_name]['store_location_info_title'] = 'Y';
	$_code_comment[$_file_name]['store_location_info_title'] = ' 상호명 출력 입니다.';

	$_replace_code[$_file_name]['store_location_info_zipcode'] = '';
	$_replace_hangul[$_file_name]['store_location_info_zipcode'] = '우편번호';
	$_auto_replace[$_file_name]['store_location_info_zipcode'] = 'Y';
	$_code_comment[$_file_name]['store_location_info_zipcode'] = '주소 우편번호 출력 입니다.';

	$_replace_code[$_file_name]['store_location_info_addr1'] = '';
	$_replace_hangul[$_file_name]['store_location_info_addr1'] = '주소1';
	$_auto_replace[$_file_name]['store_location_info_addr1'] = 'Y';
	$_code_comment[$_file_name]['store_location_info_addr1'] = '기본 주소 출력 입니다.';

	$_replace_code[$_file_name]['store_location_info_addr2'] = '';
	$_replace_hangul[$_file_name]['store_location_info_addr2'] = '주소2';
	$_auto_replace[$_file_name]['store_location_info_addr2'] = 'Y';
	$_code_comment[$_file_name]['store_location_info_addr2'] = '주소 상세내용 출력 입니다.';

	$_replace_code[$_file_name]['store_location_info_phone'] = '';
	$_replace_hangul[$_file_name]['store_location_info_phone'] = '전화번호';
	$_auto_replace[$_file_name]['store_location_info_phone'] = 'Y';
	$_code_comment[$_file_name]['store_location_info_phone'] = '전화번호 출력 입니다.';

	$_replace_code[$_file_name]['store_location_info_kakao_load'] = '';
	$_replace_hangul[$_file_name]['store_location_info_kakao_load'] = '카카오길찾기';
	$_auto_replace[$_file_name]['store_location_info_kakao_load'] = 'Y';
	$_code_comment[$_file_name]['store_location_info_kakao_load'] = '카카오길찾기 출력 입니다.';

	$_replace_code[$_file_name]['store_location_info_content'] = '';
	$_replace_hangul[$_file_name]['store_location_info_content'] = '기타내용';
	$_auto_replace[$_file_name]['store_location_info_content'] = 'Y';
	$_code_comment[$_file_name]['store_location_info_content'] = '기타내용 출력 입니다.';

	for ($i = 1; $i <= 3; $i++) {
		$_replace_code[$_file_name]['store_location_info_img'.$i] = '';
		$_replace_hangul[$_file_name]['store_location_info_img'.$i] = $_img_arr[$i].'이미지';
		$_auto_replace[$_file_name]['store_location_info_img'.$i] = 'Y';
		$_code_comment[$_file_name]['store_location_info_img'.$i] ='이미지'.$i.' 출력 입니다.';
	}

	$_replace_code[$_file_name]['store_location_operate_list'] = '';
	$_replace_hangul[$_file_name]['store_location_operate_list'] = '영업시간리스트';
	$_auto_replace[$_file_name]['store_location_operate_list'] = 'Y';
	$_replace_datavals[$_file_name]['store_location_operate_list'] = '해당요일표시:week_today;해당요일:today;시간:hour;브레이크타임:break_time';

	$_replace_code[$_file_name]['store_location_operate_break_list'] = '';
	$_replace_hangul[$_file_name]['store_location_operate_break_list'] = '브레이크타임';
	$_auto_replace[$_file_name]['store_location_operate_break_list'] = 'Y';
	$_replace_datavals[$_file_name]['store_location_operate_break_list'] = '시간:hour';

	$_replace_code[$_file_name]['store_location_info_icons'] = '';
	$_replace_hangul[$_file_name]['store_location_info_icons'] = '아이콘';
	$_auto_replace[$_file_name]['store_location_info_icons'] = 'Y';
	$_code_comment[$_file_name]['store_location_info_icons'] = '아이콘 출력 입니다.';

	$_replace_code[$_file_name]['store_location_info_facility_list'] = '';
	$_replace_hangul[$_file_name]['store_location_info_facility_list'] = '시설안내리스트';
	$_code_comment[$_file_name]['store_location_info_facility_list'] = '시설안내 리스트 출력';
	$_replace_datavals[$_file_name]['store_location_info_facility_list'] = '순서:sort;이름:name;시설이미지:fimg;';

	$_replace_code[$_file_name]['store_location_info_kakao_map'] = '';
	$_replace_hangul[$_file_name]['store_location_info_kakao_map'] = '카카오지도';
	$_code_comment[$_file_name]['store_location_info_kakao_map'] = '카카오지도 출력';
	$_code_comment[$_file_name]['store_location_info_kakao_map'] = '카카오지도 출력 입니다.';
	?>