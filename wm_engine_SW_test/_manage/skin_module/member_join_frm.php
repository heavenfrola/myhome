<?PHP

	$_replace_code[$_file_name]['form_start']="";
	$_replace_hangul[$_file_name]['form_start']="폼시작";
	$_code_comment[$_file_name]['form_start']="회원가입/정보수정 폼 시작 선언";
	$_auto_replace[$_file_name]['form_start']="Y";

	$_replace_code[$_file_name]['join_login_name']="";
	$_replace_hangul[$_file_name]['join_login_name']="가입시성명";
	$_code_comment[$_file_name]['join_login_name']="회원가입시 성명 입력 구문 출력";

	$_replace_code[$_file_name]['join_login_first_name'] = ($member['first_name']) ? $member['first_name'] : $member['name'];
	$_replace_hangul[$_file_name]['join_login_first_name'] = 'first_name';
	$_code_comment[$_file_name]['join_login_first_name'] = '회원 가입 시 first_name 입력 구문 출력';

	$_replace_code[$_file_name]['join_login_family_name'] = $member['family_name'];
	$_replace_hangul[$_file_name]['join_login_family_name'] = 'family_name';
	$_code_comment[$_file_name]['join_login_family_name'] = '회원 가입 시 family_name 입력 구문 출력';

	$_replace_code[$_file_name]['join_logout_name']="";
	$_replace_hangul[$_file_name]['join_logout_name']="정보수정성명";
	$_code_comment[$_file_name]['join_logout_name']="정보수정시 성명 구문 출력";

	$_replace_code[$_file_name]['join_birth']="";
	$_replace_hangul[$_file_name]['join_birth']="생년월일";
	$_code_comment[$_file_name]['join_birth']="생년월일 사용시 입력 구문 출력";
	$_replace_datavals[$_file_name]['join_birth']="생년월일년선택:birth1_select;생년월일월선택:birth2_select;생년월일일선택:birth3_select;생년월일양체크:birth_type_ck1;생년월일음체크:birth_type_ck2;";

	$_replace_code[$_file_name]['join_sex']="";
	$_replace_hangul[$_file_name]['join_sex']="성별";
	$_code_comment[$_file_name]['join_sex']="성별 사용시 입력 구문 출력";
	$_replace_datavals[$_file_name]['join_sex']="성별남체크:sex_ck1;성별여체크:sex_ck2;";

	$_replace_code[$_file_name]['join_whole_mem']="";
	$_replace_hangul[$_file_name]['join_whole_mem']="평생회원동의";
	$_code_comment[$_file_name]['join_whole_mem']="평생회원동의 사용시 입력 구문 출력";
	$_replace_datavals[$_file_name]['join_whole_mem']="동의함:whole_y;동의안함:whole_n;";

	$_replace_code[$_file_name]['join_login_id']="";
	$_replace_hangul[$_file_name]['join_login_id']="가입시아이디";
	$_code_comment[$_file_name]['join_login_id']="회원가입시 아이디 입력 구문 출력";

	$_replace_code[$_file_name]['join_logout_id']="";
	$_replace_hangul[$_file_name]['join_logout_id']="정보수정아이디";
	$_code_comment[$_file_name]['join_logout_id']="정보수정시 아이디 구문 출력";
	$_replace_datavals[$_file_name]['join_logout_id']="아이디중복체크:id_dbl_ck;";

	//2012-01-17 닉네임 Jung
	$_replace_code[$_file_name]['join_nick_chk']="";
	$_replace_hangul[$_file_name]['join_nick_chk']="정보수정닉네임";
	$_code_comment[$_file_name]['join_nick_chk']="정보수정시 아이디 구문 출력";
	$_replace_datavals[$_file_name]['join_nick_chk']="닉네임:nick;닉네임중복체크:nick_dbl_ck;";

	$_replace_code[$_file_name]['join_phone']="";
	$_replace_hangul[$_file_name]['join_phone']="전화번호";
	$_code_comment[$_file_name]['join_phone']="정보수정시 전화번호1 출력";
	$_auto_replace[$_file_name]['join_phone']="Y";

	$_replace_code[$_file_name]['join_phone1']="";
	$_replace_hangul[$_file_name]['join_phone1']="전화번호1";
	$_code_comment[$_file_name]['join_phone1']="정보수정시 전화번호1 출력";
	$_auto_replace[$_file_name]['join_phone1']="Y";

	$_replace_code[$_file_name]['join_phone2']="";
	$_replace_hangul[$_file_name]['join_phone2']="전화번호2";
	$_code_comment[$_file_name]['join_phone2']="정보수정시 전화번호2 출력";
	$_auto_replace[$_file_name]['join_phone2']="Y";

	$_replace_code[$_file_name]['join_phone3']="";
	$_replace_hangul[$_file_name]['join_phone3']="전화번호3";
	$_code_comment[$_file_name]['join_phone3']="정보수정시 전화번호3 출력";
	$_auto_replace[$_file_name]['join_phone3']="Y";

	$_replace_code[$_file_name]['join_cell']="";
	$_replace_hangul[$_file_name]['join_cell']="휴대전화";
	$_code_comment[$_file_name]['join_cell']="정보수정시 휴대전화 출력";
	$_auto_replace[$_file_name]['join_cell']="Y";

	$_replace_code[$_file_name]['join_cell1']="";
	$_replace_hangul[$_file_name]['join_cell1']="휴대전화1";
	$_code_comment[$_file_name]['join_cell1']="정보수정시 휴대전화 출력1";
	$_auto_replace[$_file_name]['join_cell1']="Y";

	$_replace_code[$_file_name]['join_cell2']="";
	$_replace_hangul[$_file_name]['join_cell2']="휴대전화2";
	$_code_comment[$_file_name]['join_cell2']="정보수정시 휴대전화2 출력";
	$_auto_replace[$_file_name]['join_cell2']="Y";

	$_replace_code[$_file_name]['join_cell3']="";
	$_replace_hangul[$_file_name]['join_cell3']="휴대전화3";
	$_code_comment[$_file_name]['join_cell3']="정보수정시 휴대전화3 출력";
	$_auto_replace[$_file_name]['join_cell3']="Y";

	$_replace_code[$_file_name]['join_sms_checked']="";
	$_replace_hangul[$_file_name]['join_sms_checked']="SMS동의체크";
	$_code_comment[$_file_name]['join_sms_checked']="SMS 수신 동의 체크여부 출력";
	$_auto_replace[$_file_name]['join_sms_checked']="Y";

	$_replace_code[$_file_name]['join_addr']="";
	$_replace_hangul[$_file_name]['join_addr']="주소";
	$_code_comment[$_file_name]['join_addr']="주소 사용시 입력 구문 출력";
	$_replace_datavals[$_file_name]['join_addr']="우편번호:zip;주소1:addr1;주소2:addr2;우편번호찾기:zip_url;도로명우편번호찾기:street_zip_url;";

	$_replace_code[$_file_name]['join_zip']="";
	$_replace_hangul[$_file_name]['join_zip']="우편번호";
	$_code_comment[$_file_name]['join_zip']="정보수정시 우편번호 출력";
	$_auto_replace[$_file_name]['join_zip']="Y";

	$_replace_code[$_file_name]['join_addr1']="";
	$_replace_hangul[$_file_name]['join_addr1']="주소1";
	$_code_comment[$_file_name]['join_addr1']="정보수정시 주소1 출력";
	$_auto_replace[$_file_name]['join_addr1']="Y";

	$_replace_code[$_file_name]['join_addr2']="";
	$_replace_hangul[$_file_name]['join_addr2']="주소2";
	$_code_comment[$_file_name]['join_addr2']="정보수정시 주소2 출력";
	$_auto_replace[$_file_name]['join_addr2']="Y";

	$_replace_code[$_file_name]['find_zip_url']="";
	$_replace_hangul[$_file_name]['find_zip_url']="우편번호찾기";
	$_code_comment[$_file_name]['find_zip_url']="우편번호 찾기 링크 주소 출력";
	$_auto_replace[$_file_name]['find_zip_url']="Y";

	$_replace_code[$_file_name]['find_street_zip_url']="";
	$_replace_hangul[$_file_name]['find_street_zip_url']="도로명우편번호찾기";
	$_code_comment[$_file_name]['find_street_zip_url']="도로명 우편번호 찾기 링크 주소 출력";
	$_auto_replace[$_file_name]['find_street_zip_url']="Y";

	$_replace_code[$_file_name]['join_email1']="";
	$_replace_hangul[$_file_name]['join_email1']="이메일1";
	$_code_comment[$_file_name]['join_email1']="정보수정시 이메일1 출력";
	$_auto_replace[$_file_name]['join_email1']="Y";

	$_replace_code[$_file_name]['join_email2']="";
	$_replace_hangul[$_file_name]['join_email2']="이메일2";
	$_code_comment[$_file_name]['join_email2']="정보수정시 이메일2 출력";
	$_auto_replace[$_file_name]['join_email2']="Y";

	$_replace_code[$_file_name]['join_email'] = $member['email'];
	$_replace_hangul[$_file_name]['join_email']="이메일";
	$_code_comment[$_file_name]['join_email']="정보수정시 이메일 출력";
	$_auto_replace[$_file_name]['join_email']="Y";

	$_replace_code[$_file_name]['join_email_checked']="";
	$_replace_hangul[$_file_name]['join_email_checked']="메일동의체크";
	$_code_comment[$_file_name]['join_email_checked']="이메일 수신 동의 체크여부 출력";
	$_auto_replace[$_file_name]['join_email_checked']="Y";

	$_replace_code[$_file_name]['join_login_recom']="";
	$_replace_hangul[$_file_name]['join_login_recom']="정보수정추천인";
	$_code_comment[$_file_name]['join_login_recom']="회원가입시 추천인 아이디 입력 구문 출력";
	$_replace_datavals[$_file_name]['join_login_recom']="추천아이디:recom_member;";

	$_replace_code[$_file_name]['join_reg_type']="";
	$_replace_hangul[$_file_name]['join_reg_type']="가입시개인확인방법";
	$_code_comment[$_file_name]['join_reg_type']="회원가입시 개인인증방법 선택 구문 출력";
	$_replace_datavals[$_file_name]['join_reg_type']="개인확인방법:reg_type_radio:설정된 개인확인방법(이메일/휴대폰)이 라디오버튼 형태로 출력됩니다.;";


	$_replace_code[$_file_name]['join_logout_recom']="";
	$_replace_hangul[$_file_name]['join_logout_recom']="가입시추천인";
	$_code_comment[$_file_name]['join_logout_recom']="정보수정시 추천인 아이디 구문 출력";

	$_replace_code[$_file_name]['join_addfd_list']="";
	$_replace_hangul[$_file_name]['join_addfd_list']="추가필드리스트";
	$_code_comment[$_file_name]['join_addfd_list']="추가 필드가 존재할 경우의 필드 리스트";
	$_replace_datavals[$_file_name]['join_addfd_list']="필드명:name;필드값입력:value;추가필드이미지:add_img:가입추가항목이미지 출력;추가항목분류명:cate;필수여부:is_required;";
	$_dinamic_define[$_file_name]['join_addfd_list'] = 'Y';

	$_replace_code[$_file_name]['form_end']="";
	$_replace_hangul[$_file_name]['form_end']="폼끝";
	$_code_comment[$_file_name]['form_end']="회원가입 폼 끝 선언";
	$_auto_replace[$_file_name]['form_end']="Y";

	if($cfg['use_biz_member'] == 'Y') {
		$_replace_code[$_file_name]['biz_frm']="";
		$_replace_hangul[$_file_name]['biz_frm']="기업회원추가입력";
		$_code_comment[$_file_name]['biz_frm']="기업회원 사용시 추가 가입폼";
		$_replace_datavals[$_file_name]['biz_frm']="담당자명:dam;대표자명:owner;사업자등록번호1:biz_num1;사업자등록번호2:biz_num2;사업자등록번호3:biz_num3;가입업종:biz_type1;가입업태:biz_type2;;사업자업태:biz_type1;사업자종목:biz_type2;";

		$_replace_code[$_file_name]['biz_api_check_button'] = ($cfg['use_biz_api_yn'] == 'Y') ? "<a onclick=\"businessNumApi();\">확인</a>":'';
		$_replace_hangul[$_file_name]['biz_api_check_button'] = '사업자번호체크버튼';
		$_code_comment[$_file_name]['biz_api_check_button'] = '사업자번호 유효체크 API 버튼이 출력됩니다.';
		$_auto_replace[$_file_name]['biz_api_check_button'] = '';
	}

	$_replace_code[$_file_name]['join_required_nickname'] = ($cfg['member_join_nickname'] == 'Y' && $cfg['nickname_essential'] == 'Y') ? 'required' : '';
	$_replace_hangul[$_file_name]['join_required_nickname'] = '닉네임필수여부';
	$_code_comment[$_file_name]['join_required_nickname'] = '닉네임이 가입 필수항목일 경우 required 문자가 출력됩니다.';
	$_auto_replace[$_file_name]['join_required_nickname'] = '';

	$_replace_code[$_file_name]['join_required_birthday'] = ($cfg['join_birth_use'] == 'Y' && $cfg['member_join_birth'] == 'Y') ? 'required' : '';
	$_replace_hangul[$_file_name]['join_required_birthday'] = '생년월일필수여부';
	$_code_comment[$_file_name]['join_required_birthday'] = '생년월일이 가입 필수항목일 경우 required 문자가 출력됩니다.';
	$_auto_replace[$_file_name]['join_required_birthday'] = '';

	$_replace_code[$_file_name]['join_required_gender'] = ($cfg['join_sex_use'] == 'Y' && $cfg['member_join_sex'] == 'Y') ? 'required' : '';
	$_replace_hangul[$_file_name]['join_required_gender'] = '성별필수여부';
	$_code_comment[$_file_name]['join_required_gender'] = '성별이 가입 필수항목일 경우 required 문자가 출력됩니다.';
	$_auto_replace[$_file_name]['join_required_gender'] = '';

	$_replace_code[$_file_name]['join_required_address'] = ($cfg['join_addr_use'] == 'Y' && $cfg['member_join_addr'] == 'Y') ? 'required' : '';
	$_replace_hangul[$_file_name]['join_required_address'] = '주소필수여부';
	$_code_comment[$_file_name]['join_required_address'] = '주소가 가입 필수항목일 경우 required 문자가 출력됩니다.';
	$_auto_replace[$_file_name]['join_required_address'] = '';

	$_replace_code[$_file_name]['join_sns_integrate_list'] = '';
	$_replace_hangul[$_file_name]['join_sns_integrate_list'] = 'SNS통합인증리스트';
	$_code_comment[$_file_name]['join_sns_integrate_list'] = '회원 정보 수정에서 SNS아이디를 통합합니다.';
	$_replace_datavals[$_file_name]['join_sns_integrate_list'] = 'SNS이름:name;SNS코드:code;연결상태:status;해제됨:disconnected;연결링크:link;연결해제링크:link2;';

?>