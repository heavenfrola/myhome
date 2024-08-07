<?PHP
	/* +----------------------------------------------------------------------------------------------+
	' |  [매장지도]
	' +----------------------------------------------------------------------------------------------+*/
	$_replace_code[$_file_name]['store_location_form_start'] = '';
	$_replace_hangul[$_file_name]['store_location_form_start'] = '폼시작';
	$_auto_replace[$_file_name]['store_location_form_start'] = 'Y';
	$_code_comment[$_file_name]['store_location_form_start'] = '매장지도 변경폼 시작';

	$_replace_code[$_file_name]['store_location_form_end'] = '';
	$_replace_hangul[$_file_name]['store_location_form_end'] = '폼끝';
	$_auto_replace[$_file_name]['store_location_form_end'] = 'Y';
	$_code_comment[$_file_name]['store_location_form_end'] = '매장지도 변경폼 종료';

	$_replace_code[$_file_name]['store_location_search_str'] = '';
	$_replace_hangul[$_file_name]['store_location_search_str'] = '검색값';
	$_auto_replace[$_file_name]['store_location_search_str'] = 'Y';
	$_code_comment[$_file_name]['store_location_search_str'] = '검색 값 출력';

	$_replace_code[$_file_name]['store_sido_list'] = '';
	$_replace_hangul[$_file_name]['store_sido_list'] = '지역리스트';
	$_auto_replace[$_file_name]['store_sido_list'] = 'Y';
	$_replace_datavals[$_file_name]['store_sido_list'] = '옵션값:name;선택:selected;';

	$_replace_code[$_file_name]['store_search_list'] = '';
	$_replace_hangul[$_file_name]['store_search_list'] = '검색조건';
	$_auto_replace[$_file_name]['store_search_list'] = 'Y';
	$_replace_datavals[$_file_name]['store_search_list'] = '옵션값:key;옵션이름:name;';

//	$_replace_code[$_file_name]['store_location_list'] = '';
//	$_replace_hangul[$_file_name]['store_location_list'] = '매장리스트';
//	$_code_comment[$_file_name]['store_location_list'] = '매장 리스트 출력';
//	$_replace_datavals[$_file_name]['store_location_list'] = '번호:no;매장명:title;전화번호:cell;주소1:addr1;주소2:addr2;기타내용:content;위도:lat;경도:lng;순번:seq;';

	$_replace_code[$_file_name]['store_location_facility_list'] = '';
	$_replace_hangul[$_file_name]['store_location_facility_list'] = '시설안내리스트';
	$_code_comment[$_file_name]['store_location_facility_list'] = '시설안내 리스트 출력';
	$_replace_datavals[$_file_name]['store_location_facility_list'] = '번호:no;순서:sort;이름:name;';

	$_replace_code[$_file_name]['store_location_api_start'] = '';
	$_replace_hangul[$_file_name]['store_location_api_start'] = '카카오매장안내실행';
	$_auto_replace[$_file_name]['store_location_api_start'] = 'Y';
	$_code_comment[$_file_name]['store_location_search_str'] = '카카오 매장 안내 스크립트 출력';

	$_replace_code[$_file_name]['store_naver_location_api_start'] = '';
	$_replace_hangul[$_file_name]['store_naver_location_api_start'] = '네이버매장안내실행';
	$_auto_replace[$_file_name]['store_naver_location_api_start'] = 'Y';
	$_code_comment[$_file_name]['store_naver_location_api_start'] = '네이버 매장 안내 스크립트 출력';

	$_replace_code[$_file_name]['store_layout_type'] = $cfg['location_layout_type'];
	$_replace_hangul[$_file_name]['store_layout_type'] = '레이아웃타입';
	$_auto_replace[$_file_name]['store_layout_type'] = 'Y';
	$_code_comment[$_file_name]['store_layout_type'] = '레이아웃 구조 출력 입니다.';

	$_replace_code[$_file_name]['store_location_type'] = "";
	$_replace_hangul[$_file_name]['store_location_type'] = '오프라인매장';
	$_auto_replace[$_file_name]['store_location_type'] = 'Y';
	$_code_comment[$_file_name]['store_location_type'] = '오프라인 매장 출력 입니다.';
?>