<?PHP

	// 공통 모듈 선언
	// $_replace_code : 치환코드, $_code_comment : 치환코드설명, $_auto_replace : 자동출력코드(편집불가), $_replace_datavals : 데이터필드매칭, $_replace_user_code : 사용자 생성 코드
	$_replace_code['common']['site_url']=($_SESSION['browser_type'] == 'mobile') ? $m_root_url : $root_url;
	$_replace_hangul['common']['site_url']="사이트주소";
	$_code_comment['common']['site_url']="현재 사이트의 주소";
	$_auto_replace['common']['site_url'] = 'Y';

	$_replace_code['common']['site_url2']=$p_root_url;
	$_replace_hangul['common']['site_url2']="PC사이트주소";
	$_code_comment['common']['site_url2'] = '현재 PC 사이트의 주소';
	$_auto_replace['common']['site_url2'] = 'Y';

	$_replace_code['common']['logout_url']=$root_url."/main/exec.php?exec_file=member/logout.exe.php";
	$_replace_hangul['common']['logout_url']="로그아웃";
	$_code_comment['common']['logout_url']="로그아웃을 실행하는 페이지의 주소";
	$_auto_replace['common']['logout_url'] = 'Y';

	$_replace_code['common']['img_url'] = getFileDir('_skin/skin/img')."/_skin/".$design['skin']."/img";
	$_replace_hangul['common']['img_url']="이미지경로";
	$_code_comment['common']['img_url']="현재 사용중인 스킨의 이미지 경로";
	$_auto_replace['common']['img_url'] = 'Y';

	$_replace_code['common']['favorite_url']="javascript:addFav('".$root_url."','".$cfg['br_title']."')";
	$_replace_hangul['common']['favorite_url']="즐겨찾기추가";
	$_code_comment['common']['favorite_url']="즐겨찾기에 현재 사이트를 추가할 수 있는 주소";
	$_auto_replace['common']['favorite_url'] = 'Y';

	$_replace_code['common']['navigator']=(function_exists("getPageName")) ? getPageName() : "";
	$_replace_hangul['common']['navigator']="네비게이터";
	$_code_comment['common']['navigator']="현재 접속중인 페이지의 경로";
	$_auto_replace['common']['navigator']="Y";

	$_replace_code['common']['company_name']=$cfg['company_name'];
	$_replace_hangul['common']['company_name']="회사명";
	$_code_comment['common']['company_name']="회사 상호";
	$_auto_replace['common']['company_name'] = 'Y';

	$_replace_code['common']['company_mall_name']=$cfg['company_mall_name'];
	$_replace_hangul['common']['company_mall_name']="쇼핑몰명";
	$_code_comment['common']['company_mall_name']="쇼핑몰 명";
	$_auto_replace['common']['company_mall_name'] = 'Y';

	$_replace_code['common']['company_biz_num']=$cfg['company_biz_num'];
	$_replace_hangul['common']['company_biz_num']="사업자등록번호";
	$_code_comment['common']['company_biz_num']="사업자 등록번호";
	$_auto_replace['common']['company_biz_num'] = 'Y';

	$_replace_code['common']['company_biz_url']="<a href='#' onclick='wisaOpen(\"http://www.ftc.go.kr/info/bizinfo/communicationViewPopup.jsp?wrkr_no=".numberOnly($cfg['company_biz_num'])."\", \"bizinfo\", \"false\", \"880\", \"610\"); return false;'>";
	$_replace_hangul['common']['company_biz_url']="사업자등록확인링크";
	$_code_comment['common']['company_biz_url']="사업자 등록확인 링크 주소(A 태그)";
	$_auto_replace['common']['company_biz_url'] = 'Y';

	$_replace_code['common']['company_biz_num_url']=$_replace_code['common']['company_biz_url'].$cfg['company_biz_num']."</a>";
	$_replace_hangul['common']['company_biz_num_url']="사업자등록확인";
	$_code_comment['common']['company_biz_num_url']="사업자 등록확인 링크가 포함된 사업자 등록번호";
	$_auto_replace['common']['company_biz_num_url'] = 'Y';

	$_replace_code['common']['company_online_num']=$cfg['company_online_num'];
	$_replace_hangul['common']['company_online_num']="통신판매신고번호";
	$_code_comment['common']['company_online_num']="통신 판매 신고 번호";
	$_auto_replace['common']['company_online_num'] = 'Y';

	$_replace_code['common']['company_biz_type1']=$cfg['company_biz_type1'];
	$_replace_hangul['common']['company_biz_type1']="업태";
	$_code_comment['common']['company_biz_type1']="업태";
	$_auto_replace['common']['company_biz_type1'] = 'Y';

	$_replace_code['common']['company_biz_type2']=$cfg['company_biz_type2'];
	$_replace_hangul['common']['company_biz_type2']="종목";
	$_code_comment['common']['company_biz_type2']="종목";
	$_auto_replace['common']['company_biz_type2'] = 'Y';

	$_replace_code['common']['company_owner']=$cfg['company_owner'];
	$_replace_hangul['common']['company_owner']="대표자성명";
	$_code_comment['common']['company_owner']="대표자 성명";
	$_auto_replace['common']['company_owner'] = 'Y';

	$_replace_code['common']['admin_nick']=$cfg['admin_nick'] ? $cfg['admin_nick'] : "관리자";
	$_replace_hangul['common']['admin_nick']="운영자닉네임";
	$_code_comment['common']['admin_nick']="쇼핑몰 관리 기본 설정에서 설정된 운영자 닉네임 (기본 : 관리자)";
	$_auto_replace['common']['admin_nick'] = 'Y';

	$_replace_code['common']['company_email']=$cfg['company_email'];
	$_replace_hangul['common']['company_email']="회사이메일";
	$_code_comment['common']['company_email']="회사 대표 이메일 주소";
	$_auto_replace['common']['company_email'] = 'Y';

	$_replace_code['common']['company_phone']=$cfg['company_phone'];
	$_replace_hangul['common']['company_phone']="회사전화번호";
	$_code_comment['common']['company_phone']="회사 전화번호";
	$_auto_replace['common']['company_phone'] = 'Y';

	$_replace_code['common']['company_fax']=$cfg['company_fax'];
	$_replace_hangul['common']['company_fax']="회사팩스번호";
	$_code_comment['common']['company_fax']="회사 팩스번호";
	$_auto_replace['common']['company_fax'] = 'Y';

	$_replace_code['common']['company_zip']=$cfg['company_zip'];
	$_replace_hangul['common']['company_zip']="사업장우편번호";
	$_code_comment['common']['company_zip']="사업장 우편번호";
	$_auto_replace['common']['company_zip'] = 'Y';

	$_replace_code['common']['company_addr']=$cfg['company_addr1']." ".$cfg['company_addr2'];
	$_replace_hangul['common']['company_addr']="사업장주소";
	$_code_comment['common']['company_addr']="사업장 주소";
	$_auto_replace['common']['company_addr'] = 'Y';

	$_replace_code['common']['return_zip']=$cfg['return_zip'];
	$_replace_hangul['common']['return_zip']="반품지우편번호";
	$_code_comment['common']['return_zip']="반품지 우편번호";
	$_auto_replace['common']['return_zip']="Y";

	$_replace_code['common']['return_addr'] = stripslashes($cfg['return_addr1'].' '.$cfg['return_addr2']);
	$_replace_hangul['common']['return_addr'] = '반품지주소';
	$_code_comment['common']['return_addr'] = '반품지 주소';
	$_auto_replace['common']['return_addr'] = 'Y';

	$_replace_code['common_module']['bank_account_list'] = '';
	$_replace_hangul['common_module']['bank_account_list'] = '공통입금계좌정보';
	$_code_comment['common_module']['bank_account_list'] = '입금계좌정보를 출력합니다.';
	$_replace_datavals['common_module']['bank_account_list'] = '은행명:bank;계좌번호:account;예금주:owner;';

	$_replace_code['common']['milage_join']=@number_format($cfg['milage_join']);
	$_replace_hangul['common']['milage_join']="회원가입적립금";
	$_code_comment['common']['milage_join']="회원 가입 시 지급되는 적립금";
	$_auto_replace['common']['milage_join'] = 'Y';

	$_replace_code['common']['info_milage_review'] = ($cfg['milage_use'] == '1' && $cfg['milage_review'] > 0) ? parsePrice($cfg['milage_review'], true) : '';
	$_replace_hangul['common']['info_milage_review'] = '상품평적립금';
	$_code_comment['common']['info_milage_review'] = '상품평 작성 시 지급되는 적립금';
	$_auto_replace['common']['info_milage_review'] = 'Y';

	$_replace_code['common']['info_milage_review_image'] = ($cfg['milage_use'] == '1' && $cfg['milage_review_image'] > 0) ? parsePrice($cfg['milage_review_image'], true) : '';
	$_replace_hangul['common']['info_milage_review_image'] = '상품평이미지추가적립금';
	$_code_comment['common']['info_milage_review_image'] = '상품평 작성 시 이미지를 첨부할 경우 추가 지급되는 적립금';
	$_auto_replace['common']['info_milage_review_image'] = 'Y';

	$_replace_code['common']['info_milage_review_total'] = ($cfg['milage_use'] == '1' && ($cfg['milage_review']+$cfg['milage_review_image']) > 0) ? parsePrice($cfg['milage_review']+$cfg['milage_review_image'], true) : '';
	$_replace_hangul['common']['info_milage_review_total'] = '상품평총적립금';
	$_code_comment['common']['info_milage_review_total'] = '상품평 작성 시 지급되는 총 적립금';
	$_auto_replace['common']['info_milage_review_total'] = 'Y';

	$_replace_code['common']['milage_use_min'] = @number_format($cfg['milage_use_min']);
	$_replace_hangul['common']['milage_use_min']="최소적립금";
	$_code_comment['common']['milage_use_min']="적립금 사용가능한 최소단위";

	$_replace_code['common']['milage_use_order_min']=@number_format((float) $cfg['milage_use_order_min']);
	$_replace_hangul['common']['milage_use_order_min']="적립금최소주문금액";
	$_code_comment['common']['milage_use_order_min']="적립금이 사용가능한 최소 주문금액";
	$_auto_replace['common']['milage_use_order_min'] = 'Y';

	if($_file_name != 'shop_cart.php') {
		$_replace_code['common']['delivery_fee']=number_format($cfg['delivery_fee']);
		$_replace_hangul['common']['delivery_fee']="배송비";
		$_code_comment['common']['delivery_fee']="배송비";
		$_auto_replace['common']['delivery_fee'] = 'Y';

		$_replace_code['common']['delivery_r_fee']=number_format((float) showExchangeFee($cfg['delivery_fee']));
		$_replace_hangul['common']['delivery_r_fee']="참조배송비";
		$_code_comment['common']['delivery_r_fee']="참조배송비";
		$_auto_replace['common']['delivery_r_fee'] = 'Y';
	}

	$_replace_code['common']['dlv_fee2']=@number_format($cfg['dlv_fee2']);
	$_replace_hangul['common']['dlv_fee2']="착불배송비";
	$_code_comment['common']['dlv_fee2']="착불배송비(설정값이 있을때에만 표출)";
	$_auto_replace['common']['dlv_fee2'] = 'Y';

	$_replace_code['common']['delivery_free_limit'] = ($cfg['delivery_fee_type'] == 'O' || $delivery_fee_type == 'O' || $cfg['delivery_type'] == '6' || $cfg['delivery_type'] == '1') ? '' : number_format($cfg['delivery_free_limit']);
	$_replace_hangul['common']['delivery_free_limit']="배송비무료적용";
	$_code_comment['common']['delivery_free_limit']="배송비 무료 적용 구매금액";
	$_auto_replace['common']['delivery_free_limit'] = 'Y';

	$_replace_code['common']['milage_use_min'] = ($cfg['milage_use_min'] > 0) ? parsePrice($cfg['milage_use_min'], true) : 0;
	$_replace_hangul['common']['milage_use_min']="적립금사용조건";
	$_auto_replace['common']['milage_use_min'] = 'Y';

	$_replace_code['common']['company_privacy_items']="";
	$_replace_hangul['common']['company_privacy_items']="개인정보취급방침수집항목";
	$_code_comment['common']['company_privacy_items']="개인정보취급방침의 개인정보 수집항목";
	$_auto_replace['common']['company_privacy_items']="Y";

	$_replace_code['common']['company_privacy_get']="";
	$_replace_hangul['common']['company_privacy_get']="개인정보취급방침수집방법";
	$_code_comment['common']['company_privacy_get']="개인정보취급방침의 개인정보 수집방법";
	$_auto_replace['common']['company_privacy_get']="Y";

	$_replace_code['common']['company_privacy_date2']=$cfg['company_privacy_date2'];
	$_replace_hangul['common']['company_privacy_date2']="개인정보취급방침시행일";
	$_auto_replace['common']['company_privacy_date2'] = 'Y';

	$_replace_code['common']['company_privacy1_part']=$cfg['company_privacy1_part'];
	$_replace_hangul['common']['company_privacy1_part']="고객서비스담당부서";
	$_auto_replace['common']['company_privacy1_part'] = 'Y';

	$_replace_code['common']['company_privacy1_phone']=$cfg['company_privacy1_phone'];
	$_replace_hangul['common']['company_privacy1_phone']="고객서비스전화번호";
	$_auto_replace['common']['company_privacy1_phone'] = 'Y';

	$_replace_code['common']['company_privacy1_email']=$cfg['company_privacy1_email'];
	$_replace_hangul['common']['company_privacy1_email']="고객서비스이메일";
	$_auto_replace['common']['company_privacy1_email'] = 'Y';

	$_replace_code['common']['company_privacy2_name']=$cfg['company_privacy2_name'];
	$_replace_hangul['common']['company_privacy2_name']="보호관리책임자";
	$_auto_replace['common']['company_privacy2_name'] = 'Y';

	$_replace_code['common']['company_privacy2_phone']=$cfg['company_privacy2_phone'];
	$_replace_hangul['common']['company_privacy2_phone']="보호관리전화번호";
	$_auto_replace['common']['company_privacy2_phone'] = 'Y';

	$_replace_code['common']['company_privacy2_email']=$cfg['company_privacy2_email'];
	$_replace_hangul['common']['company_privacy2_email']="보호관리이메일";
	$_auto_replace['common']['company_privacy2_email'] = 'Y';

	$_replace_code['common']['detail_review_write_url']="javascript:writeReview();";
	$_replace_hangul['common']['detail_review_write_url']="상품평등록";
	$_code_comment['common']['detail_review_write_url']="상품평을 등록할 수 있는 링크 주소 출력";
	$_auto_replace['common']['detail_review_write_url'] = 'Y';

	$_replace_code['common']['detail_review_write2_url'] = '';
	$_replace_hangul['common']['detail_review_write2_url'] = '상품평등록(레이어)';
	$_code_comment['common']['detail_review_write2_url'] = '상품평 등록 레이어 링크 출력';
	$_auto_replace['common']['detail_review_write2_url'] = 'Y';

	$_replace_code['common']['detail_qna_write_url']="javascript:writeQna();";
	$_replace_hangul['common']['detail_qna_write_url']="상품질답등록";
	$_code_comment['common']['detail_qna_write_url']="상품 질문과 답변을 등록할 수 있는 링크 주소 출력";
	$_auto_replace['common']['detail_qna_write_url'] = 'Y';

	$_replace_code['common']['banking_time']=$cfg['banking_time'];
	$_replace_hangul['common']['banking_time']="미입금주문취소일";
	$_auto_replace['common']['banking_time']="Y";
	$_code_comment['common']['banking_time']="무통장 입금 주문시 자동 취소전 입금기한";

	$_replace_code['common']['this_url'] = in_array($root_dir.'/shop/detail.php', get_included_files()) ? '/shop/detail.php?pno='.$_GET['pno'] : str_replace($root_url, '', getUrl());
	$_replace_hangul['common']['this_url']="현재페이지주소";
	$_auto_replace['common']['this_url']="Y";
	$_code_comment['common']['this_url']="현재 접속중인 페이지 주소 출력";

	$_replace_code['common']['auto_login_id']='';
	$_replace_hangul['common']['auto_login_id']="자동로그인아이디";
	$_code_comment['common']['auto_login_id']="자동로그인아이디";
	$_auto_replace['common']['auto_login_id'] = 'Y';

	$_replace_code['common']['auto_login_pw']='';
	$_replace_hangul['common']['auto_login_pw']="자동로그인비밀번호";
	$_code_comment['common']['auto_login_pw']="자동로그인비밀번호";
	$_auto_replace['common']['auto_login_pw'] = 'Y';

	$_replace_code['common']['auto_login_id_check']='';
	$_replace_hangul['common']['auto_login_id_check']="자동로그인아이디체크";
	$_code_comment['common']['auto_login_id_check']="자동로그인아이디체크";
	$_auto_replace['common']['auto_login_id_check'] = 'Y';
	$_auto_replace['common']['auto_login_id_check'] = 'Y';

	$_replace_code['common']['auto_login_pw_check']='';
	$_replace_hangul['common']['auto_login_pw_check']="자동로그인비밀번호체크";
	$_code_comment['common']['auto_login_pw_check']="자동로그인비밀번호체크";
	$_auto_replace['common']['auto_login_pw_check'] = 'Y';

	$_replace_code['common']['member_name']=$member['name'];
	$_replace_hangul['common']['member_name']="회원명";
	$_code_comment['common']['member_name']="접속중인 회원의 성명";
	$_auto_replace['common']['member_name']="Y";

	$_replace_code['common']['member_id']=$member['member_id'];
	$_replace_hangul['common']['member_id']="회원아이디";
	$_code_comment['common']['member_id']="접속중인 회원의 아이디";
	$_auto_replace['common']['member_id']="Y";

	$_replace_code['common']['member_nick']=$member['nick'];
	$_replace_hangul['common']['member_nick']="회원닉네임";
	$_code_comment['common']['member_nick']="접속중인 회원의 닉네임";
	$_auto_replace['common']['member_nick']="Y";

	$_replace_code['common']['member_milage']=number_format($member['milage'],$cfg['currency_decimal']);
	$_replace_hangul['common']['member_milage']="회원보유적립금액";
	$_code_comment['common']['member_milage']="접속중인 회원의 적립금";
	$_auto_replace['common']['member_milage']="Y";

	$_replace_code['common']['member_emoney']=number_format($member['emoney'],$cfg['currency_decimal']);
	$_replace_hangul['common']['member_emoney']="회원보유예치금액";
	$_code_comment['common']['member_emoney']="접속중인 회원의 예치금";
	$_auto_replace['common']['member_emoney']="Y";

	$_replace_code['common']['milage_use']= ($cfg['milage_use'] == 1) ? 'Y' : '';
	$_replace_hangul['common']['milage_use']="적립금사용유무";
	$_code_comment['common']['milage_use']="적립금사용유무";
	$_auto_replace['common']['milage_use']="Y";

	$_replace_code['common']['emoney_use']= ($cfg['emoney_use'] == 'Y') ? 'Y' : '';
	$_replace_hangul['common']['emoney_use']="예치금사용유무";
	$_code_comment['common']['emoney_use']="예치금사용유무";
	$_auto_replace['common']['emoney_use']="Y";

	$_replace_code['common']['member_group']=getGroupName($member['level']);
	$_replace_hangul['common']['member_group']="회원등급";
	$_code_comment['common']['member_group']="접속중인 회원의 등급";
	$_auto_replace['common']['member_group']="Y";

	$_replace_code['common']['member_icon']=getMemberIcon($member['no'],$member['member_id']);
	$_replace_hangul['common']['member_icon']="회원아이콘";
	$_code_comment['common']['member_icon']="접속중인 회원의 등급아이콘";
	$_auto_replace['common']['member_icon']="Y";

	$_replace_code['common']['member_phone']=$member['phone'];
	$_replace_hangul['common']['member_phone']="회원전화번호";
	$_code_comment['common']['member_phone']="접속중인 회원의 일반 전화번호";
	$_auto_replace['common']['member_phone']="Y";

	$_replace_code['common']['member_cell']=$member['cell'];
	$_replace_hangul['common']['member_cell']="회원휴대전화번호";
	$_code_comment['common']['member_cell']="접속중인 회원의 휴대 전화번호";
	$_auto_replace['common']['member_cell']="Y";

	$_replace_code['common']['member_addr']=$member['no'] ? $member['addr1']." ".$member['addr2'] : "";
	$_replace_hangul['common']['member_addr']="회원주소";
	$_code_comment['common']['member_addr']="접속중인 회원의 주소";
	$_auto_replace['common']['member_addr']="Y";

	$_replace_code['common']['member_coupon'] = "";
	$_replace_hangul['common']['member_coupon'] = "회원보유쿠폰개수";
	$_code_comment['common']['member_coupon'] = "접속중인 회원의 보유 쿠폰 개수";
	$_dinamic_define['common']['member_coupon'] = "Y";
	$_auto_replace['common']['member_coupon'] = "Y";

	$_replace_code['common']['total_member_qna'] = "";
	$_replace_hangul['common']['total_member_qna'] = "회원작성문의개수";
	$_code_comment['common']['total_member_qna'] = "접속중인 회원이 작성한 문의글 개수";
	$_dinamic_define['common']['total_member_qna'] = "Y";
	$_auto_replace['common']['total_member_qna'] = "Y";

	$_replace_code['common']['recent_view_scrollu_url']="javascript:wingRecentVScroll(1);";
	$_replace_hangul['common']['recent_view_scrollu_url']="최근상품스크롤상";
	$_code_comment['common']['recent_view_scrollu_url']="최근 본 상품의 리스트의 박스스크롤 기능 사용 시 스크롤 주소 (상)";
	$_auto_replace['common']['recent_view_scrollu_url']="Y";

	$_replace_code['common']['recent_view_scrolld_url']="javascript:wingRecentVScroll(2);";
	$_replace_hangul['common']['recent_view_scrolld_url']="최근상품스크롤하";
	$_code_comment['common']['recent_view_scrolld_url']="최근 본 상품의 리스트의 박스스크롤 기능 사용 시 스크롤 주소 (하)";
	$_auto_replace['common']['recent_view_scrolld_url']="Y";

	$_replace_code['common']['total_cart'] = "";
	$_replace_hangul['common']['total_cart'] = "장바구니개수";
	$_code_comment['common']['total_cart'] = "현재 장바구니에 담긴 상품 개수";
	$_auto_replace['common']['total_cart'] = "Y";

	$_replace_code['common']['total_wish'] = '';
	$_replace_hangul['common']['total_wish'] = '위시리스트개수';
	$_code_comment['common']['total_wish'] = '위시리스트에 있는 상품 개수 출력';
	$_auto_replace['common']['total_wish'] = 'Y';

	for($i = 1; $i <= 2; $i++) {
		if($cfg['use_quick_cart'.$i] == 'Y') {
			$_replace_code['common_module']['quick_cart_load'.$i] = "";
			$_replace_hangul['common_module']['quick_cart_load'.$i] = "퀵카트{$i}출력영역";
			$_code_comment['common_module']['quick_cart_load'.$i] = "해당 위치에 마우스 클릭/오버시 퀵카트가 출력됩니다.";
			$_replace_datavals['common_module']['quick_cart_load'.$i] = "퀵카트장바구니리스트:quick_cart_list:지정위치에 퀵카트가 출력됩니다.;퀵카트클릭스크립트:quick_cart_click_link:클릭시 출력으로 설정된 경우 onclick 이벤트 안에 입력해주세요.";
		}
	}

	$_replace_code['common']['escrow_confirm_url']="";
	$_replace_hangul['common']['escrow_confirm_url']="에스크로가입확인";
	$_code_comment['common']['escrow_confirm_url']="에스크로 서비스 가입 확인할 수 있는 주소";
	$_auto_replace['common']['escrow_confirm_url']="Y";

	$_replace_code['common']['root_domain_name'] = '';
	$_replace_hangul['common']['root_domain_name'] = '사이트기본도메인';
	$_code_comment['common']['root_domain_name'] = 'http 가 제외된 도메인 정보';
	$_auto_replace['common']['root_domain_name'] = 'Y';

	$_replace_code['common']['hostingby1']="";
	$_replace_hangul['common']['hostingby1']="호스팅사업자로고1";
	$_code_comment['common']['hostingby1']="호스팅 사업자 로고 135px x 18px";
	$_auto_replace['common']['hostingby1']="Y";

	$_replace_code['common']['hostingby2']="";
	$_replace_hangul['common']['hostingby2']="호스팅사업자로고2";
	$_code_comment['common']['hostingby2']="호스팅 사업자 로고 135px x 18px";
	$_auto_replace['common']['hostingby2']="Y";

	$_replace_code['common']['hostingby3']="";
	$_replace_hangul['common']['hostingby3']="호스팅사업자로고3";
	$_code_comment['common']['hostingby3']="호스팅 사업자 로고 135px x 18px";
	$_auto_replace['common']['hostingby3']="Y";

	$_replace_code['common']['hostingby4']="";
	$_replace_hangul['common']['hostingby4']="호스팅사업자로고4";
	$_code_comment['common']['hostingby4']="호스팅 사업자 로고 80px x 82px";
	$_auto_replace['common']['hostingby4']="Y";

	$_replace_code['common']['hostingby5']="";
	$_replace_hangul['common']['hostingby5']="호스팅사업자로고5";
	$_code_comment['common']['hostingby5']="호스팅 사업자 로고 80px x 82px";
	$_auto_replace['common']['hostingby5']="Y";

	$_replace_code['common']['hostingby6']="";
	$_replace_hangul['common']['hostingby6']="호스팅사업자로고6";
	$_code_comment['common']['hostingby6']="호스팅 사업자 로고 80px x 82px";
	$_auto_replace['common']['hostingby6']="Y";

	$_replace_code['common']['hostingby7']="";
	$_replace_hangul['common']['hostingby7']="호스팅사업자로고7";
	$_code_comment['common']['hostingby7']="호스팅 사업자 로고 46px x 31px";
	$_auto_replace['common']['hostingby7']="Y";

	$_replace_code['common']['hostingby8']="";
	$_replace_hangul['common']['hostingby8']="호스팅사업자로고8";
	$_code_comment['common']['hostingby8']="호스팅 사업자 로고 78px x 81px";
	$_auto_replace['common']['hostingby8']="Y";

	$_replace_code['common']['hostingbyt1']="Hosting by WISA";
	$_replace_hangul['common']['hostingbyt1']="호스팅사업자게시1";
	$_code_comment['common']['hostingbyt1']="Hosting by WISA 텍스트 문구";
	$_auto_replace['common']['hostingbyt1']="Y";

	$_replace_code['common']['hostingbyt2']="Hosting by (주)위사";
	$_replace_hangul['common']['hostingbyt2']="호스팅사업자게시2";
	$_code_comment['common']['hostingbyt2']="Hosting by (주)위사 텍스트 문구";
	$_auto_replace['common']['hostingbyt2']="Y";

	$_replace_code['common']['hostingbyt3']="(주)위사";
	$_replace_hangul['common']['hostingbyt3']="호스팅사업자게시3";
	$_code_comment['common']['hostingbyt3']="(주)위사 텍스트 문구";
	$_auto_replace['common']['hostingbyt3']="Y";

	$_replace_code['common_module']['title_img']="";
	$_replace_hangul['common_module']['title_img']="타이틀이미지";
	$_code_comment['common_module']['title_img']="현재 접속중인 페이지의 타이틀 이미지";
	$_auto_replace['common_module']['title_img']="Y";

	$_replace_code['common_module']['pageres']="";
	$_replace_hangul['common_module']['pageres']="페이지선택";

	$_replace_code['common_module']['common_login_form_start']="";
	$_replace_hangul['common_module']['common_login_form_start']="공통로그인폼시작";
	$_code_comment['common_module']['common_login_form_start']="로그인 폼 시작 선언";
	$_auto_replace['common_module']['common_login_form_start']="Y";

	$_replace_code['common_module']['common_login_form_end']="";
	$_replace_hangul['common_module']['common_login_form_end']="공통로그인폼끝";
	$_code_comment['common_module']['common_login_form_end']="로그인 폼 끝 선언";
	$_auto_replace['common_module']['common_login_form_end']="Y";

	$_replace_code['common_module']['common_ord_form_start']="";
	$_replace_hangul['common_module']['common_ord_form_start']="공통주문조회폼시작";
	$_auto_replace['common_module']['common_ord_form_start']="Y";

	$_replace_code['common_module']['common_ord_form_end']="";
	$_replace_hangul['common_module']['common_ord_form_end']="공통주문조회폼끝";
	$_auto_replace['common_module']['common_ord_form_end']="Y";

	$_replace_code['common_module']['prd_search_form_start']="";
	$_replace_hangul['common_module']['prd_search_form_start']="상품검색폼시작";
	$_code_comment['common_module']['prd_search_form_start']="상품 검색 폼 시작 선언";
	$_auto_replace['common_module']['prd_search_form_start']="Y";

	$_replace_code['common_module']['prd_search_form_end']="";
	$_replace_hangul['common_module']['prd_search_form_end']="상품검색폼끝";
	$_code_comment['common_module']['prd_search_form_end']="상품 검색 폼 끝 선언";
	$_auto_replace['common_module']['prd_search_form_end']="Y";

	$_replace_code['common_module']['member_login0'] = ($member['no'] > 0) ? 'Y' : '';
	$_replace_hangul['common_module']['member_login0'] = '회원로그인상태';
	$_code_comment['common_module']['member_login0'] = '로그인된 상태일 경우 Y를 출력합니다.';
	$_auto_replace['common_module']['member_login0'] = 'Y';

	$_replace_code['common_module']['member_logout0'] = (!$member['no']) ? 'Y' : '';
	$_replace_hangul['common_module']['member_logout0'] = '비로그인상태';
	$_code_comment['common_module']['member_logout0'] = '비로그인 상태일 경우 Y를 출력합니다.';
	$_auto_replace['common_module']['member_logout0'] = 'Y';

	$_replace_code['common_module']['member_login1']="";
	$_replace_hangul['common_module']['member_login1']="로그인후1";
	$_code_comment['common_module']['member_login1']="회원일 경우 구문 출력 1(로그아웃, 정보수정 버튼 포함)";

	$_replace_code['common_module']['member_logout1']="";
	$_replace_hangul['common_module']['member_logout1']="로그인전1";
	$_code_comment['common_module']['member_logout1']="비회원일 경우 구문 출력 1(로그인폼 또는 로그인, 회원가입 버튼 포함)";

	$_replace_code['common_module']['member_login2']="";
	$_replace_hangul['common_module']['member_login2']="로그인후2";
	$_code_comment['common_module']['member_login2']="회원일 경우 구문 출력 2(로그아웃, 정보수정 버튼 포함)";

	$_replace_code['common_module']['member_logout2']="";
	$_replace_hangul['common_module']['member_logout2']="로그인전2";
	$_code_comment['common_module']['member_logout2']="비회원일 경우 구문 출력 2(로그인폼 또는 로그인, 회원가입 버튼 포함)";

	$_replace_code['common_module']['member_login3']="";
	$_replace_hangul['common_module']['member_login3']="로그인후3";
	$_code_comment['common_module']['member_login3']="회원일 경우 구문 출력 3(로그아웃, 정보수정 버튼 포함)";

	$_replace_code['common_module']['member_logout3']="";
	$_replace_hangul['common_module']['member_logout3']="로그인전3";
	$_code_comment['common_module']['member_logout3']="비회원일 경우 구문 출력 3(로그인폼 또는 로그인, 회원가입 버튼 포함)";

	$_replace_code['common_module']['member_login4']="";
	$_replace_hangul['common_module']['member_login4']="로그인후4";
	$_code_comment['common_module']['member_login4']="회원일 경우 구문 출력 4(로그아웃, 정보수정 버튼 포함)";

	$_replace_code['common_module']['member_logout4']="";
	$_replace_hangul['common_module']['member_logout4']="로그인전4";
	$_code_comment['common_module']['member_logout4']="비회원일 경우 구문 출력 4(로그인폼 또는 로그인, 회원가입 버튼 포함)";

	$_replace_code['common_module']['member_login5']="";
	$_replace_hangul['common_module']['member_login5']="로그인후5";
	$_code_comment['common_module']['member_login5']="회원일 경우 구문 출력 5(로그아웃, 정보수정 버튼 포함)";

	$_replace_code['common_module']['member_logout5']="";
	$_replace_hangul['common_module']['member_logout5']="로그인전5";
	$_code_comment['common_module']['member_logout5']="비회원일 경우 구문 출력 5(로그인폼 또는 로그인, 회원가입 버튼 포함)";

	$_replace_code['common_module']['current_currency'] = $cfg['currency'];
	$_replace_hangul['common_module']['current_currency'] = "화폐단위";
	$_code_comment['common_module']['current_currency'] = "현재 화폐단위 '{$cfg['currency']}'를 출력합니다.";
	$_auto_replace['common_module']['current_currency'] = 'Y';

	$_replace_code['common_module']['current_currency_f'] = ($cfg['currency_position'] == 'F' && !$prd['sell_prc_consultation']) ? $cfg['currency'] : '';
	$_replace_hangul['common_module']['current_currency_f'] = "화폐단위전";
	$_code_comment['common_module']['current_currency_f'] = "현재 화폐단위 '{$cfg['currency']}'를 출력합니다.";
	$_auto_replace['common_module']['current_currency_f'] = 'Y';

	$_replace_code['common_module']['current_currency_b'] = ($cfg['currency_position'] == 'B' && !$prd['sell_prc_consultation']) ? $cfg['currency'] : '';
	$_replace_hangul['common_module']['current_currency_b'] = "화폐단위후";
	$_code_comment['common_module']['current_currency_b'] = "현재 화폐단위 '{$cfg['currency']}'를 출력합니다.";
	$_auto_replace['common_module']['current_currency_b'] = 'Y';

	$_replace_code['common_module']['recent_view_list']="";
	$_replace_hangul['common_module']['recent_view_list']="최근상품리스트";
	$_code_comment['common_module']['recent_view_list'] = '최근 본 상품의 목록';
	$_replace_datavals['common_module']['recent_view_list']="상품명:name;상품명(링크포함):name_link:상품링크(A 태그)를 포함한 상품명;상품링크:link:해당 상품의 페이지 주소 출력;상품이미지:imgr:상품의 소 이미지 출력;상품이미지(링크포함):imgr_link:상품링크(A 태그)를 포함한 소 이미지 출력;상품이미지경로:img:상품의 소 이미지의 경로 출력;상품이미지정보:imgstr:상품의 사이즈 정보 출력 (예 - width=100 height=100);상품가격:sell_prc;";

	$_replace_code['common_module']['product_box']="";
	$_replace_hangul['common_module']['product_box']="기본상품박스";
	$_code_comment['common_module']['product_box']="상품리스트의 상품 출력 기본 박스";
	$_replace_datavals['common_module']['product_box'] = $_user_code_typec['p'];

	for($i = 1; $i <= 5; $i++) {
		$_replace_code['common_module']['product_box'.$i] = "";
		$_replace_hangul['common_module']['product_box'.$i] = "기본상품박스{$i}";
		$_code_comment['common_module']['product_box'.$i] = "상품리스트의 상품 출력 기본 박스{$i}";
		$_replace_datavals['common_module']['product_box'.$i] = $_user_code_typec['p'];
	}

	$_replace_code['common_module']['product_review_login_form']="";
	$_replace_hangul['common_module']['product_review_login_form']="상품평회원등록폼";
	$_code_comment['common_module']['product_review_login_form']="회원일 경우 상품평을 등록하는 폼(숨겨진 레이어 형식)";
	$_replace_datavals['common_module']['product_review_login_form']="폼시작:form_start;작성자:writer;분류선택:cate;제한제목목록:review_title_sel;폼끝:form_end;자동생성방지:review_cap;상품평점:rev_pt;작성자명:name;상품평본문내용:content;";

	$_replace_code['common_module']['product_review_logout_form']="";
	$_replace_hangul['common_module']['product_review_logout_form']="상품평비회원등록폼";
	$_code_comment['common_module']['product_review_logout_form']="비회원일 경우 상품평을 등록하는 폼(숨겨진 레이어 형식)";
	$_replace_datavals['common_module']['product_review_logout_form']=$_replace_datavals['common_module']['product_review_login_form'];

	$_replace_code['common_module']['product_review_form_close'] = "closeReviewDetail('$rno', '{$_POST['rev_idx']}');";
	$_replace_hangul['common_module']['product_review_form_close'] = '상품평등록폼닫기링크';
	$_auto_replace['common_module']['product_review_form_close'] = 'Y';

	$_replace_code['common_module']['product_review_comment_list']="";
	$_replace_hangul['common_module']['product_review_comment_list']="상품평댓글리스트";
	$_code_comment['common_module']['product_review_comment_list']="해당 상품의 상품평 글당 댓글 리스트";
	$_replace_datavals['common_module']['product_review_comment_list']="상품평댓글작성자:name;상품평댓글내용:content;상품평등록일:reg_date;상품평댓글수정링크태그:mod_link;상품평댓글삭제링크태그:del_link;";

	$_replace_code['common_module']['product_review_commment_login_form']="";
	$_replace_hangul['common_module']['product_review_commment_login_form']="상품평댓글회원등록폼";
	$_code_comment['common_module']['product_review_commment_login_form']="회원일 경우 상품평의 댓글을 등록하는 폼";

	$_replace_code['common_module']['product_review_commment_logout_form']="";
	$_replace_hangul['common_module']['product_review_commment_logout_form']="상품평댓글비회원등록폼";
	$_code_comment['common_module']['product_review_commment_logout_form']="비회원일 경우 상품평의 댓글 등록 안내메세지";

	$_replace_code['common_module']['product_review_img1'] = '';
	$_replace_hangul['common_module']['product_review_img1'] = "상품후기첨부파일1";
	$_code_comment['common_module']['product_review_img1'] = "첨부파일1을 출력합니다.";

	$_replace_code['common_module']['product_review_img2'] = '';
	$_replace_hangul['common_module']['product_review_img2'] = "상품후기첨부파일2";
	$_code_comment['common_module']['product_review_img2'] = "첨부파일2를 출력합니다.";

	$_replace_code['common_module']['product_review_delimg1'] = '';
	$_replace_hangul['common_module']['product_review_delimg1'] = "상품후기첨부파일1삭제";
	$_code_comment['common_module']['product_review_delimg1'] = "첨부파일1을 삭제합니다.";

	$_replace_code['common_module']['product_review_delimg2'] = '';
	$_replace_hangul['common_module']['product_review_delimg2'] = "상품후기첨부파일2삭제";
	$_code_comment['common_module']['product_review_delimg2'] = "첨부파일2를 삭제합니다.";

	$_replace_code['common_module']['product_qna_login_form']="";
	$_replace_hangul['common_module']['product_qna_login_form']="상품질답회원등록폼";
	$_code_comment['common_module']['product_qna_login_form']="회원일 경우 상품 질문과 답변을 등록하는 폼(숨겨진 레이어 형식)";
	$_replace_datavals['common_module']['product_qna_login_form']="폼시작:form_start;작성자:writer;분류선택:cate;비밀글선택:secret;제한제목목록:qna_title_sel;폼끝:form_end;자동생성방지:qna_cap;";

	$_replace_code['common_module']['product_qna_logout_form']="";
	$_replace_hangul['common_module']['product_qna_logout_form']="상품질답비회원등록폼";
	$_code_comment['common_module']['product_qna_logout_form']="비회원일 경우 상품 질문과 답변을 등록하는 폼(숨겨진 레이어 형식)";
	$_replace_datavals['common_module']['product_qna_logout_form']=$_replace_datavals['common_module']['product_qna_login_form'];

	$_replace_code['common_module']['product_qna_img1'] = '';
	$_replace_hangul['common_module']['product_qna_img1'] = "상품문의첨부파일1";
	$_code_comment['common_module']['product_qna_img1'] = "첨부파일1을 출력합니다.";
	$_auto_replace['common_module']['product_qna_img1'] = 'Y';

	$_replace_code['common_module']['product_qna_img2'] = '';
	$_replace_hangul['common_module']['product_qna_img2'] = "상품문의첨부파일2";
	$_code_comment['common_module']['product_qna_img2'] = "첨부파일2를 출력합니다.";
	$_auto_replace['common_module']['product_qna_img2'] = 'Y';

	$_replace_code['common_module']['product_qna_delimg1'] = '';
	$_replace_hangul['common_module']['product_qna_delimg1'] = "상품문의첨부파일1삭제";
	$_code_comment['common_module']['product_qna_delimg1'] = "첨부파일1을 삭제합니다.";
	$_auto_replace['common_module']['product_qna_delimg1'] = 'Y';

	$_replace_code['common_module']['product_qna_delimg2'] = '';
	$_replace_hangul['common_module']['product_qna_delimg2'] = "상품문의첨부파일2삭제";
	$_code_comment['common_module']['product_qna_delimg2'] = "첨부파일2를 삭제합니다.";
	$_auto_replace['common_module']['product_qna_delimg2'] = 'Y';

	$_replace_hangul['common_module']['my_cpn_list']="주문쿠폰리스트";
	$_code_comment['common_module']['my_cpn_list']="주문시 사용가능한 보유 쿠폰 리스트";
	$_replace_datavals['common_module']['my_cpn_list']="쿠폰선택:radio;쿠폰코드:code;쿠폰명:name;쿠폰할인금액:sale_prc;쿠폰사용기간:udate_type;쿠폰최소구매금액:prc_limit;쿠폰최고할인금액:sale_limit_k;";

	$_replace_code['common_module']['content_prd_info']="";
	$_replace_hangul['common_module']['content_prd_info']="상품게시판상품정보";
	$_code_comment['common_module']['content_prd_info']="상품평 또는 상품 질문과 답변 리스트의 상품 정보";
	$_replace_datavals['common_module']['content_prd_info']="상품번호:pno;상품명:name;상품이미지:img:상품중이미지 (상품이미지 설정과 관계없이 150x150 고정);상품소이미지:simg:상품소이미지 ({$cfg['thumb3_w_mng']} x {$cfg['thumb3_h_mng']});상품중이미지:mimg:상품중이미지 ({$cfg['thumb2_w_mng']} x {$cfg['thumb2_h_mng']});상품아이콘:icons;판매가:sell_prc_str;소비자가:normal_prc;적립금:milage;상품코드:code;";

	$_replace_code['common_module']['common_poll_list']="";
	$_replace_hangul['common_module']['common_poll_list']="진행중설문조사";
	$_code_comment['common_module']['common_poll_list']="고객이 투표가능한 현재 진행중인 최신 설문조사 항목 리스트";
	$_replace_datavals['common_module']['common_poll_list']="설문조사주제:title:진행 중 설문 조사 주제 출력;항목명:item:해당 항목명 출력;결과퍼센티지:per:해당 항목의 결과 %;득표수:total:해당 항목 총 득표수;결과보기:result:투표 결과 주소;";

	$_replace_code['common_module']['auto_slide']="";
	$_replace_hangul['common_module']['auto_slide']="자동슬라이드";
	$_code_comment['common_module']['auto_slide']="슬라이드 기능을 장착한 내용 편집";

	$_replace_code['common_module']['use_qd1'] = $_skin['qd1_use'] == 'Y' ? 'Y' : '';
	$_replace_hangul['common_module']['use_qd1']="퀵프리뷰사용";
	$_code_comment['common_module']['use_qd1']="팝업형태의 퀵프리뷰를 사용하는지 여부를 출력합니다.";
	$_auto_replace['common']['use_qd1']="Y";

	if($cfg['ipay_logo']){
		$_replace_code['common_module']['ipay_logo']="";
		$_replace_hangul['common_module']['ipay_logo']="iPay로고";
		$_auto_replace['common_module']['ipay_logo']="Y";
		$_code_comment['common_module']['ipay_logo']="iPay 로고 이미지";
	}

	$_replace_code['common_module']['fb_like'] = "";
	$_replace_hangul['common_module']['fb_like']="페이스북좋아요";
	$_code_comment['common_module']['fb_like']="페이스북 좋아요 버튼 출력. 사이트주소가 좋아요 처리됩니다.";

	$_replace_code['common']['striptags']="";
	$_replace_hangul['common']['striptags']="태그제거";
	$_code_comment['common']['striptags']='문자열의 태그를 제거합니다. {{$태그제거(${{상품명}})}}';
	$_auto_replace['common']['striptags']="Y";
	$_dinamic_define['common']['striptags']="Y";

	$_replace_code['common']['use_milage']=$cfg['milage_use'] == '1' ? 'milage' : '';
	$_replace_hangul['common']['use_milage']="적립금사용여부";
	$_code_comment['common']['use_milage']='적립금 사용시 milage 문자를 출력합니다.';
	$_auto_replace['common']['use_milage']="Y";

	$_replace_code['common']['use_emoney']=$cfg['emoney_use'] == 'Y' ? 'emoney' : '';
	$_replace_hangul['common']['use_emoney']="예치금사용여부";
	$_code_comment['common']['use_emoney']='예치금 사용시 emoney 문자를 출력합니다.';
	$_auto_replace['common']['use_emoney']="Y";

	$_replace_code['common']['use_attend'] = $cfg['use_attend'] == 'N' ? '' : 'attend';
	$_replace_hangul['common']['use_attend']="출석체크사용여부";
	$_code_comment['common']['use_attend']='출석체크 사용시 attend 문자를 출력합니다.';
	$_auto_replace['common']['use_attend']="Y";

	$_replace_code['common']['join_pwd_basic']="";
	$_replace_hangul['common']['join_pwd_basic']="비밀번호설정";
	$_code_comment['common']['join_pwd_basic']="비밀번호 설정 조건";
	$_auto_replace['common']['join_pwd_basic']="Y";

	$_replace_code['common']['password_min'] = ($cfg['password_min'] >= 4) ? $cfg['password_min'] : 4;
	$_replace_hangul['common']['password_min']="비밀번호최소길이";
	$_code_comment['common']['password_min']="비밀번호의 최소길이를 출력합니다.(4글자 미만으로 설정되지 않습니다.)";
	$_auto_replace['common']['password_min']="Y";

	$_replace_code['common']['password_max'] = ($cfg['password_max'] > 4 && $cfg['password_max'] > $cfg['password_min']) ? $cfg['password_max'] : '';
	$_replace_hangul['common']['password_max']="비밀번호최대길이";
	$_code_comment['common']['password_max']="비밀번호의 최대길이를 출력합니다.";
	$_auto_replace['common']['password_max']="Y";

	$_replace_code['common']['password_engnum'] = ($cfg['password_engnum'] == 'Y') ? 'Y' : '';
	$_replace_hangul['common']['password_engnum']="비밀번호영수혼용사용";
	$_code_comment['common']['password_engnum']="비밀번호 영문숫자혼용 필수 사용시 Y가 출력됩니다.";
	$_auto_replace['common']['password_engnum']="Y";

	$_replace_code['common']['r_prc_use'] = (trim($cfg['r_currency_type'])) ? $cfg['r_currency_type'] : '';
	$_replace_hangul['common']['r_prc_use']="참조화폐사용";
	$_code_comment['common']['r_prc_use']="참조화폐사용여부";
	$_auto_replace['common']['r_prc_use']="Y";

	$_replace_code['common_module']['current_r_currency'] = $cfg['r_currency'];
	$_replace_hangul['common_module']['current_r_currency'] = "참조화폐단위";
	$_code_comment['common_module']['current_r_currency'] = "현재 참조화폐단위 '{$cfg['r_currency']}'를 출력합니다.";
	$_auto_replace['common_module']['current_r_currency'] = 'Y';

	$_replace_code['common_module']['current_r_currency_f'] = ($cfg['r_currency_position'] == 'F') ? $cfg['r_currency'] : '';
	$_replace_hangul['common_module']['current_r_currency_f'] = "참조화폐단위전";
	$_code_comment['common_module']['current_r_currency_f'] = "현재 참조화폐단위 '{$cfg['r_currency']}'를 출력합니다.";
	$_auto_replace['common_module']['current_r_currency_f'] = 'Y';

	$_replace_code['common_module']['current_r_currency_b'] = ($cfg['r_currency_position'] == 'B') ? $cfg['r_currency'] : '';
	$_replace_hangul['common_module']['current_r_currency_b'] = "참조화폐단위후";
	$_code_comment['common_module']['current_r_currency_b'] = "현재 참조화폐단위 '{$cfg['r_currency']}'를 출력합니다.";
	$_auto_replace['common_module']['current_r_currency_b'] = 'Y';

	$_replace_code['common_module']['common_xbig_name'] = stripslashes($cfg['xbig_name']);
	$_replace_hangul['common_module']['common_xbig_name'] = "2차분류명";
	$_code_comment['common_module']['common_xbig_name'] = '설정된 2차분류 이름';
	$_auto_replace['common_module']['common_xbig_name'] = 'Y';

	$_replace_code['common_module']['common_ybig_name'] = stripslashes($cfg['ybig_name']);
	$_replace_hangul['common_module']['common_ybig_name'] = "3차분류명";
	$_code_comment['common_module']['common_ybig_name'] = '설정된 3차분류 이름';
	$_auto_replace['common_module']['common_ybig_name'] = 'Y';

	// 최근본상품개수
	$_replace_code['common_module']['today_cnt'] = ($_click_prd) ? count($_click_prd) : 0;
	$_replace_hangul['common_module']['today_cnt'] = '최근본상품개수';
	$_code_comment['common_module']['today_cnt'] = '최근본상품개수를 출력합니다.';
	$_auto_replace['common_module']['today_cnt'] = 'Y';

	if($cfg['use_easemob_plugin'] == 'Y') {
		$_replace_code['common_module']['easemob_call'] = "callEasemobim();";
		$_replace_hangul['common_module']['easemob_call'] = 'easemob호출';
		$_auto_replace['common_module']['easemob_call'] = 'Y';
	}



	// 페이지별 모듈 재선언
	if(defined('__WORKING_CACHE_PAGE__') == false) {
		if(defined("_wisa_manage_edit_") && $_edit_pg != "") $_file_name=str_replace(".".$_skin_ext['p'], ".php", $_edit_pg);
		if($_file_name && file_exists($engine_dir."/_manage/skin_module/".$_file_name)) include_once $engine_dir."/_manage/skin_module/".$_file_name;
	}

	// 코더 전용 모듈 선언
	if(file_exists($root_dir."/skin_module/_common_module.php")) include_once $root_dir."/skin_module/_common_module.php";
	if($_file_name && file_exists($root_dir."/skin_module/".$_file_name)) include_once $root_dir."/skin_module/".$_file_name;

	// 사용자 생성 모듈
	$_skin_name=$_skin_name ? $_skin_name : $design['skin'];
	if(file_exists($root_dir."/_skin/".$_skin_name."/user_code.".$_skin_ext['g'])){
		include_once $root_dir."/_skin/".$_skin_name."/user_code.".$_skin_ext['g'];
		if(is_array($_user_code)){
			foreach($_user_code as $key=>$val){
				$_code_type=$_user_code[$key]['code_type'];
				$_code_name=userCodeName($key);
				$_replace_code['user_code'][$_code_name]="";
				$_replace_user_code['user_code'][$_code_name]=$key;
				$_replace_hangul['user_code'][$_code_name]=userCodeName($key, 1);
				$_ucode_comment=$_user_code[$key]['code_comment'];
				if($_ucode_comment == ""){
					$_ctitle="";
					if($_code_type == "p"){
						$_ctitle=$pdo->row("select `name` from {$tbl['category']} where `no` in (".$_user_code[$key]['cate'].") limit 1");
					}elseif($_code_type == "b"){
						$_board_name=$_user_code[$key]['board_name'];
						if($_board_name == "prd:review") $_ctitle="상품 후기";
						elseif($_board_name == "prd:qna") $_ctitle="상품 Q&A";
						else $_ctitle=$pdo->row("select `title` from `mari_config` where `db`='".$_board_name."' limit 1");
					}
				}
				$_code_comment['user_code'][$_code_name]=$_ucode_comment ? $_ucode_comment : "생성된 ".$_ctitle." ".$_user_code_form[$_code_type];
				if(!($_user_code[$key]['page_type'] == "a" || @strchr($_user_code[$key]['page_list'], "@".$_edit_pg."@"))) continue;
				$_replace_code[$_file_name][$_code_name]="";
				$_replace_user_code[$_file_name][$_code_name]=$_replace_user_code['user_code'][$_code_name];
				$_replace_hangul[$_file_name][$_code_name]=$_replace_hangul['user_code'][$_code_name];
				$_code_comment[$_file_name][$_code_name]=$_code_comment['user_code'][$_code_name];
			}
		}
	}

	$_replace_code['common_module']['naver_login'] = "";
	$_replace_hangul['common_module']['naver_login'] = "네이버로그인버튼";
	$_code_comment['common_module']['naver_login'] = "네이버로그인버튼";
	$_auto_replace['common_module']['naver_login'] = "Y";

	$_replace_code['common_module']['naver_login_script'] = "";
	$_replace_hangul['common_module']['naver_login_script'] = "네이버로그인스크립트";
	$_code_comment['common_module']['naver_login_script'] = "네이버로그인 실행할 스크립트";
	$_auto_replace['common_module']['naver_login_script'] = "Y";

	$_replace_code['common_module']['facebook_login'] = "";
	$_replace_hangul['common_module']['facebook_login'] = "페이스북로그인버튼";
	$_code_comment['common_module']['facebook_login'] = "페이스북로그인버튼";
	$_auto_replace['common_module']['facebook_login'] = "Y";

	$_replace_code['common_module']['facebook_login_script'] = "";
	$_replace_hangul['common_module']['facebook_login_script'] = "페이스북로그인스크립트";
	$_code_comment['common_module']['facebook_login_script'] = "페이스북로그인 실행할 스크립트";
	$_auto_replace['common_module']['facebook_login_script'] = "Y";

	$_replace_code['common_module']['kakao_login'] = "";
	$_replace_hangul['common_module']['kakao_login'] = "카카오톡로그인버튼";
	$_code_comment['common_module']['kakao_login'] = "카카오톡로그인버튼";
	$_auto_replace['common_module']['kakao_login'] = "Y";

	$_replace_code['common_module']['kakao_login_script'] = "";
	$_replace_hangul['common_module']['kakao_login_script'] = "카카오톡로그인스크립트";
	$_code_comment['common_module']['kakao_login_script'] = "카카오톡로그인 실행할 스크립트";
	$_auto_replace['common_module']['kakao_login_script'] = "Y";

	$_replace_code['common_module']['payco_login'] = "";
	$_replace_hangul['common_module']['payco_login'] = "페이코로그인버튼";
	$_code_comment['common_module']['payco_login'] = "페이코로그인버튼";
	$_auto_replace['common_module']['payco_login'] = "Y";

	$_replace_code['common_module']['payco_login_script'] = "";
	$_replace_hangul['common_module']['payco_login_script'] = "페이코로그인스크립트";
	$_code_comment['common_module']['payco_login_script'] = "페이코로그인 실행할 스크립트";
	$_auto_replace['common_module']['payco_login_script'] = "Y";

	$_replace_code['common_module']['wonder_login'] = "";
	$_replace_hangul['common_module']['wonder_login'] = "원더로그인버튼";
	$_code_comment['common_module']['wonder_login'] = "원더로그인버튼";
	$_auto_replace['common_module']['wonder_login'] = "Y";
	$_hidden_code['common_module']['wonder_login'] = "Y";

	$_replace_code['common_module']['wonder_login_script'] = "";
	$_replace_hangul['common_module']['wonder_login_script'] = "원더로그인스크립트";
	$_code_comment['common_module']['wonder_login_script'] = "원더로그인 실행할 스크립트";
	$_auto_replace['common_module']['wonder_login_script'] = "Y";
	$_hidden_code['common_module']['wonder_login_script'] = "Y";

	$_replace_code['common_module']['wemarkeprice_login'] = "";
	$_replace_hangul['common_module']['wemarkeprice_login'] = "위메프로그인버튼";
	$_code_comment['common_module']['wemarkeprice_login'] = "위메프로그인버튼";
	$_auto_replace['common_module']['wemarkeprice_login'] = "Y";

	$_replace_code['common_module']['wemarkeprice_login_script'] = "";
	$_replace_hangul['common_module']['wemarkeprice_login_script'] = "위메프로그인스크립트";
	$_code_comment['common_module']['wemarkeprice_login_script'] = "위메프로그인 실행할 스크립트";
	$_auto_replace['common_module']['wemarkeprice_login_script'] = "Y";

	$_replace_code['common_module']['apple_login'] = '';
	$_replace_hangul['common_module']['apple_login'] = "애플로그인버튼";
	$_code_comment['common_module']['apple_login'] = "애플로그인버튼";
	$_auto_replace['common_module']['apple_login'] = "Y";

	$_replace_code['common_module']['sns_login_use_cnt'] = '';
	$_replace_hangul['common_module']['sns_login_use_cnt'] = 'SNS로그인사용여부';
	$_code_comment['common_module']['sns_login_use_cnt'] = 'SNS로그인이 설정되어있을 경우 설정된 개수를 출력합니다.';
	$_auto_replace['common_module']['sns_login_use_cnt'] = 'Y';

	$_replace_code['common_module']['sns_login_button_use'] = "";
	$_replace_hangul['common_module']['sns_login_button_use'] = "SNS로그인버튼사용";
	$_code_comment['common_module']['sns_login_button_use'] = "SNS로그인버튼사용";
	$_auto_replace['common_module']['sns_login_button_use'] = "Y";

	$_replace_code['common_module']['notify_restock_use']= "";
	$_replace_hangul['common_module']['notify_restock_use']="재입고알림사용유무";
	$_code_comment['common_module']['notify_restock_use']="재입고알림사용유무";
	$_auto_replace['common_module']['notify_restock_use']="Y";

	$_replace_code['common_module']['cart_sub_use'] = ($cfg['use_sbscr'] == 'Y') ? 'Y' : '';
	$_replace_hangul['common_module']['cart_sub_use'] = '정기배송적용여부';
	$_code_comment['common_module']['cart_sub_use'] = '정기배송기능이 설정되어 있을 경우 Y가 출력됩니다.';
	$_auto_replace['common_module']['cart_sub_use'] = 'Y';

	if($cfg['use_prc_consult'] == 'Y') {
		$_replace_code['common_module']['detail_sell_prc_consultation']="";
		$_replace_hangul['common_module']['detail_sell_prc_consultation']="판매가대체문구";
		$_auto_replace['common_module']['detail_sell_prc_consultation']="Y";
		$_code_comment['common_module']['detail_sell_prc_consultation']="판매가대체문구";
	}

	$_replace_code['common_module']['prd_dlvprc_use'] = ($cfg['use_prd_dlvprc'] == 'Y') ? 'Y' : '';
	$_replace_hangul['common_module']['prd_dlvprc_use'] = '개별배송비사용여부';
	$_code_comment['common_module']['prd_dlvprc_use'] = '상품별 개별배송비를 사용중일 경우 Y가 출력됩니다.';
	$_auto_replace['common_module']['prd_dlvprc_use'] = 'Y';

	$_replace_code['common_module']['common_cpn_list']="";
	$_replace_hangul['common_module']['common_cpn_list']="전체쿠폰리스트";
	$_code_comment['common_module']['common_cpn_list']="전체 쿠폰 리스트";
	$_replace_datavals['common_module']['common_cpn_list']="쿠폰주소링크:link;쿠폰명:name;쿠폰이미지경로:img;쿠폰설명:explain;쿠폰다운후:coupon_ny;쿠폰다운전:coupon_yn;할인혜택:sale_type;쿠폰최소구매금액:prc_limit;";

	$_replace_code['common_module']['mail_order_product_list']="";
	$_replace_hangul['common_module']['mail_order_product_list']="메일주문상품목록";
	$_code_comment['common_module']['mail_order_product_list']="자동 메일 주문 상품리스트";
	$_replace_datavals['common_module']['mail_order_product_list']="상품링크:plink;상품사진:img;상품명:name;상품옵션정보:option_str;수량:buy_ea;상품가격:sell_prc;";

	$_replace_code['common_module']['admin_nick'] = $cfg['admin_nick'];
	$_replace_hangul['common_module']['admin_nick'] = '운영자닉네임';
	$_code_comment['common_module']['admin_nick'] = '관리자모드에 등록한 운영자 닉네임을 출력합니다.';
	$_auto_replace['common_module']['admin_nick'] = 'Y';

	if($cfg['use_bs_list_addimg'] == 'Y') {
		$_replace_code['common_module']['product_all_image_list'] = '';
		$_replace_hangul['common_module']['product_all_image_list'] = '상품전체이미지리스트';
		$_code_comment['common_module']['product_all_image_list']  = '상품별 목록이미지 및 부가이미지 리스트';
		$_replace_datavals['common_module']['product_all_image_list'] = '이미지주소:url;상품링크:link;';
	}

	$_replace_code[$_file_name]['product_colorchip_list'] = '';
	$_replace_hangul[$_file_name]['product_colorchip_list'] = '이미지칩리스트';
	$_replace_datavals[$_file_name]['product_colorchip_list'] = '옵션명:name;이미지주소:url;색상코드:code;';
	$_code_sub[$_file_name]['product_colorchip_list'] = 'Y';

	$_replace_code[$_file_name]['use_juso_api_type_Y'] = ($cfg['juso_api_use'] == 'Y') ? 'true' : '';
	$_replace_hangul[$_file_name]['use_juso_api_type_Y'] = '행안부우편번호API사용';
	$_auto_replace['common_module']['use_juso_api_type_Y'] = 'Y';

	$_replace_code[$_file_name]['use_juso_api_type_D'] = ($cfg['juso_api_use'] == 'D') ? 'true' : '';
	$_replace_hangul[$_file_name]['use_juso_api_type_D'] = '다음우편번호API사용';
	$_auto_replace['common_module']['use_juso_api_type_D'] = 'Y';

	$_replace_code['common_module']['select_cp'] = '';
	$_replace_hangul['common_module']['select_cp'] = '실명인증방법선택';
	$_code_comment['common_module']['select_cp'] = '아이핀/체크플러스 인증방법 선택';
	$_auto_replace['common_module']['select_cp'] = 'Y';

	$_replace_code['common_module']['select_cp_ok'] = '';
	$_replace_hangul['common_module']['select_cp_ok'] = '실명인증상태';
	$_code_comment['common_module']['select_cp_ok'] = '실명인증상태 확인';
	$_auto_replace['common_module']['select_cp_ok'] = 'Y';

	$_replace_code['common_module']['limit_19'] = '';
	$_replace_hangul['common_module']['limit_19'] = '미성년자가입불가';
	$_code_comment['common_module']['limit_19'] = '미성년자가입불가';
	$_auto_replace['common_module']['limit_19'] = 'Y';

	$_replace_code['common_module']['limit_19_use'] = $cfg['limit_19'];
	$_replace_hangul['common_module']['limit_19_use'] = '미성년자가입불가사용여부';
	$_code_comment['common_module']['limit_19_use'] = '미성년자가입불가사용여부';
	$_auto_replace['common_module']['limit_19_use'] = 'Y';

?>