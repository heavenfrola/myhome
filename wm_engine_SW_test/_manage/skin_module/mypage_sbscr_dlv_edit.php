<?PHP

	$_replace_code[$_file_name]['form_start']="";
	$_replace_hangul[$_file_name]['form_start']="폼시작";
	$_code_comment[$_file_name]['form_start']="마이페이지 배송지 변경 시작 선언";
	$_auto_replace[$_file_name]['form_start']="Y";

	$_replace_code[$_file_name]['form_end']="";
	$_replace_hangul[$_file_name]['form_end']="폼끝";
	$_code_comment[$_file_name]['form_end']="마이페이지 배송지 변경 끝 선언";
	$_auto_replace[$_file_name]['form_end']="Y";

	$_replace_code[$_file_name]['old_addr']="";
	$_replace_hangul[$_file_name]['old_addr']="기존배송지";
	$_code_comment[$_file_name]['old_addr']="마이페이지 배송지 변경 기존배송지";
	$_auto_replace[$_file_name]['old_addr']="Y";

	$_replace_code[$_file_name]['addressee_name']="";
	$_replace_hangul[$_file_name]['addressee_name']="받으시는분";
	$_code_comment[$_file_name]['addressee_name']="마이페이지 배송지 변경 끝 선언";
	$_auto_replace[$_file_name]['addressee_name']="Y";

	$_replace_code[$_file_name]['addressee_phone']="";
	$_replace_hangul[$_file_name]['addressee_phone']="전화번호";
	$_code_comment[$_file_name]['addressee_phone']="마이페이지 배송지 변경 끝 선언";
	$_auto_replace[$_file_name]['addressee_phone']="Y";

	$_replace_code[$_file_name]['addressee_cell']="";
	$_replace_hangul[$_file_name]['addressee_cell']="휴대폰";
	$_code_comment[$_file_name]['addressee_cell']="마이페이지 배송지 변경 끝 선언";
	$_auto_replace[$_file_name]['addressee_cell']="Y";

	$_replace_code[$_file_name]['addressee_zip']="";
	$_replace_hangul[$_file_name]['addressee_zip']="우편번호";
	$_code_comment[$_file_name]['addressee_zip']="마이페이지 배송지 변경 끝 선언";
	$_auto_replace[$_file_name]['addressee_zip']="Y";

	$_replace_code[$_file_name]['addressee_addr1']="";
	$_replace_hangul[$_file_name]['addressee_addr1']="주소1";
	$_code_comment[$_file_name]['addressee_addr1']="마이페이지 배송지 변경 끝 선언";
	$_auto_replace[$_file_name]['addressee_addr1']="Y";

	$_replace_code[$_file_name]['addressee_addr2']="";
	$_replace_hangul[$_file_name]['addressee_addr2']="주소2";
	$_code_comment[$_file_name]['addressee_addr2']="마이페이지 배송지 변경 끝 선언";
	$_auto_replace[$_file_name]['addressee_addr2']="Y";

	$_replace_code[$_file_name]['zip_url'] = "zipSearch('dlv_edit', 'addressee_zip', 'addressee_addr1', 'addressee_addr2', 1);";
	$_replace_hangul[$_file_name]['zip_url'] = "우편번호찾기";
	$_code_comment[$_file_name]['zip_url'] = "배송지 주소 입력의 우편번호 찾기 주소";
	$_auto_replace[$_file_name]['zip_url'] = "Y";

	$_replace_code[$_file_name]['street_zip_url'] = "zipSearch('dlv_edit', 'addressee_zip', 'addressee_addr1', 'addressee_addr2', 2);";
	$_replace_hangul[$_file_name]['street_zip_url'] = "도로명우편번호찾기";
	$_code_comment[$_file_name]['street_zip_url'] = "도로명 우편번호 찾기 링크 주소 출력";
	$_auto_replace[$_file_name]['street_zip_url'] = "Y";

?>