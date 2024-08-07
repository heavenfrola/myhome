<?PHP

	// 데이터베이스 테이블명
	$tbl['config'] = 'wm_config';
	$tbl['bank_account']='wm_bank_account'; // 은행 계좌
	$tbl['delivery_url']='wm_delivery_url'; // 배송추적 URL
	$tbl['seo_config'] = 'wm_seo_config'; // seo 설정
    $tbl['work_log'] = 'wm_work_log'; // 관리자 작업 로그

	$tbl['mng']='wm_mng';
	$tbl['mng_bookmark']='wm_mng_bookmark';

	$tbl['category']='wm_category';
	$tbl['product']='wm_product';
	$tbl['product_link'] = 'wm_product_link';
	$tbl['product_filed']=$tbl['product_field']='wm_product_filed';
	$tbl['product_filed_set']=$tbl['product_field_set']='wm_product_filed_set';
	$tbl['product_option_set']='wm_product_option_set';
	$tbl['product_option_item']='wm_product_option_item';
	$tbl['product_option_img']='wm_product_option_img';
	$tbl['product_option_colorchip']='wm_product_option_colorchip';
	$tbl['product_image']='wm_product_image';
	$tbl['product_image_tmp']='wm_product_image_tmp';
	$tbl['product_icon']='wm_product_icon';
	$tbl['product_gift']='wm_product_gift';
	$tbl['product_sort']='wm_product_sort';
	$tbl['product_price']='wm_product_price'; // 다중 가격
	$tbl['product_price_log']='wm_product_price_log';
	$tbl['product_refprd'] = 'wm_product_refprd';
	$tbl['product_talkstore'] = 'wm_product_talkstore';
	$tbl['product_talkstore_announce'] = 'wm_product_talkstore_announce';
	$tbl['product_delivery_set'] = 'wm_product_delivery_set'; // 상품별 배송비 정책 세트
	$tbl['product_timesale_set'] = 'wm_product_timesale_set'; // 타임세일 프리셋
    $tbl['product_book'] = 'wm_product_book'; // 도서 정보 테이블
	$tbl['talkstore_api_log'] = 'wm_talkstore_api_log';
	$tbl['cart']='wm_cart';
	$tbl['wish']='wm_wish';
	$tbl['order_product']='wm_order_product';
	$tbl['order']='wm_order';
	$tbl['order_stat_log']='wm_order_stat_log';
	$tbl['order_memo']='wm_order_memo';
	$tbl['order_config_prdprc'] = 'wm_order_config_prdprc'; // 주문상품금액별 할인
	$tbl['order_payment'] = 'wm_order_payment';
	$tbl['order_dlv_prc'] = 'wm_order_dlv_prc';
	$tbl['order_account'] = 'wm_order_account';
	$tbl['order_account_refund'] = 'wm_order_account_refund';
	$tbl['order_account_log'] = 'wm_order_account_log';
	$tbl['order_addr_log'] = 'wm_order_addr_log';
	$tbl['claim_reasons'] = 'wm_claim_reasons';
	$tbl['erp_storage'] = 'wm_erp_storage';

	$tbl['member']='wm_member';
	$tbl['member_deleted']='wm_member_deleted';
	$tbl['biz_member']='wm_biz_member'; // 2006-01-23
	$tbl['member_log']='wm_member_log';
	$tbl['member_group']='wm_member_group';
	$tbl['member_checker']='wm_member_checker';
	$tbl['member_level_log']='wm_member_level_log';
    $tbl['member_auto_login'] = 'wm_member_auto_login';
    $tbl['member_address'] = 'wm_member_address';
    $tbl['member_cert'] = 'wm_member_cert';
	$tbl['milage']='wm_milage';
	$tbl['review']='wm_review';
	$tbl['review_comment']='wm_review_comment'; // 2006-03-17
	$tbl['review_recommend'] = 'wm_review_recommend'; // 상품평 추천 2019-07-17
	$tbl['qna']='wm_qna';
	$tbl['default']='wm_default'; // 2006-01-23
	$tbl['product_set']='wm_product_set';
	$tbl['coupon']='wm_coupon'; // 2006-03-19
	$tbl['coupon_download']='wm_coupon_download'; // 2006-03-19
	$tbl['coupon_auth_code'] ='wm_coupon_auth_code';

	$tbl['sccoupon']='wm_social_coupon_info'; // 2013-04-22
	$tbl['sccoupon_code']='wm_social_coupon_code'; // 2013-04-22
	$tbl['sccoupon_use']='wm_social_coupon_use'; // 2013-04-22
	$tbl['sccoupon_log']='wm_social_coupon_log'; // 2013-04-22

	$tbl['banner']='wm_banner'; // 2006-03-20
	$tbl['mail']='wm_mail'; // 단체메일
	$tbl['mng_log']='wm_mng_log'; // 관리자 로그인 로그
	$tbl['mng_auth_log']='wm_mng_auth_log'; // 관리자 권한 수정 로그
	$tbl['coordi_category']='wm_coordi_category'; // 코디세트 카테고리
	$tbl['coordi_set']='wm_coordi_set'; // 코디세트 상품
	$tbl['coordi_image']='wm_coordi_image'; // 코디세트 이미지
	$tbl['product_annex']='wm_product_annex'; // 부속상품
	$tbl['blacklist_log']='wm_blacklist_log'; // 블랙리스트 로그

    $tbl['mari_board'] = 'mari_board'; // 게시판
	$tbl['mari_comment'] = 'mari_comment';
	$tbl['mari_config'] = 'mari_config';
	$tbl['mari_cate'] = 'mari_cate';

	$tbl['zipcode']='zipcode';
	$tbl['zipcode_street']='zipcode_area';


	$tbl['cooprate']='wm_cooprate'; // 제휴문의
	$tbl['card']='wm_card'; // 결제 관련 카드 정보테이블
	$tbl['card_ready']='wm_card_ready'; // 결제 관련 카드 백업테이블
	$tbl['vbank']='wm_vbank'; // 에스크로 가상계좌
	$tbl['cs']='wm_cs'; // 고객상담
	$tbl['tax_receipt']='wm_tax_receipt'; // 세금 계산서
	$tbl['sms_case']='wm_sms_case'; // SMS
	$tbl['emoney']='wm_emoney'; // 예치금 내역

	$tbl['delete_log']='wm_delete_log'; // 상품, 주문 삭제 내역
	$tbl['member_xls_log']='wm_member_xls_log'; // 회원엑셀출력 내역
	$tbl['mng_cs_log']='wm_mng_cs_log'; // 관리자 고객센터 접근 로그
	$tbl['intra_board']='wm_intra_board'; // 인트라넷 사내게시판
	$tbl['intra_board_config']='wm_intra_board_config'; // 인트라넷 사내게시판 설정
	$tbl['intra_comment']='wm_intra_comment'; // 인트라넷 사내게시판 설정
	$tbl['intra_group']='wm_intra_group'; // 인트라넷 사원조직도
	$tbl['intra_day_check']='wm_intra_day_check'; // 인트라넷 출석체크
	$tbl['intra_schedule']='wm_intra_schedule'; // 인트라넷 일정

	// 일반 카운터 테이블
	$tbl['log_agent']='wm_log_agent';
	$tbl['log_count']='wm_log_count'.date('_ym'); // 월별 생성
	$tbl['log_day']='wm_log_day';
	$tbl['log_today']='wm_log_today';
	$tbl['log_referer']='wm_log_referer'; // 월별 생성
	$tbl['log_server']='wm_log_server';

	$tbl['log_search_day']='wm_log_search_day'; // 일별 검색어
	$tbl['log_search']='wm_log_search'; // 일별 검색어

	$tbl['log_search_engine']='wm_log_search_engine'; // 검색엔진 로그
	$tbl['log_schedule'] = 'wm_log_schedule';

	$tbl['poll_config']='wm_poll_config'; // 설문조사
	$tbl['poll_item']='wm_poll_item'; // 설문조사 아이템
	$tbl['poll_comment']='wm_poll_comment'; // 설문조사 댓글

	//팝업 2006-02-27
	$tbl['popup_frame']='wm_popup_frame';
	$tbl['popup']='wm_popup';

	$tbl['recom']='wm_recom_tmp';
	$tbl['reserve']='wm_reserve'; // 예약

	$tbl['manage_menu_static_total']='wm_manage_menu_static_total'; // 관리자 메뉴 통계
	$tbl['manage_menu_static_day']='wm_manage_menu_static_day'; // 관리자 메뉴 통계

	$tbl['order_no']='wm_order_no'; // 주문 번호
	$tbl['attend']='wm_attend'; // 출석체크
	$tbl['attend_member']='wm_attend_member'; // 출석체크
	$tbl['attend_day'] = 'wm_attend_day'; // new 출석체크
	$tbl['attend_new']='wm_attend_new'; // 출석체크 3.0
	$tbl['attend_list']='wm_attend_list'; // 출석체크 3.0 리스트
	$tbl['product_saleset']='wm_product_saleset'; // 세트할인
	$tbl['delivery_area']='wm_delivery_area'; // 지역별배송비 간편설정
	$tbl['delivery_area_detail']='wm_delivery_area_detail'; // 지역별배송비 세부설정
	$tbl['delivery_range']='wm_delivery_range'; // 배송 불가지역 설정
	$tbl['order_product_log']='wm_order_product_log'; // 주문상품 수량/옵션 변경로그
	$tbl['cash_receipt']='wm_cash_receipt'; // 현금영수증
	$tbl['cash_receipt_log']='wm_cash_receipt_log'; // 현금영수증 로그
	$tbl['point']='wm_point'; //  포인트내역
	$tbl['product_stat_log']='wm_product_stat_log'; // 상품상태변경로그
	$tbl['coupon_log']='wm_coupon_log'; // 쿠폰로그
	$tbl['product_log']='wm_product_log'; // 상품로그
	$tbl['pwd_log']='wm_pwd_log'; // 비밀번호 변경로그
	$tbl['namecheck_log']='wm_namecheck_log'; // 실명확인 로그
	$tbl['card_cc_log']='wm_card_cc_log'; // 카드거래취소 로그
	$tbl['mng_auth']='wm_mng_auth'; // 인트라넷 사원 세부 권한
	$tbl['provider']='wm_provider'; // 사입처 정보
	$tbl['domain_expire']='wm_domain_expire'; // 도메인 만료일 관리 테이블
	$tbl['urlshortenter']='wm_urlshortenter'; // 단축 URL 등록 테이블
    $tbl['npay_api_log'] = 'wm_npay_api_log'; // 네이버페이 실패 로그 테이블

	$tbl['pbanner'] = 'wm_pbanner'; // 프로모션 배너
	$tbl['pbanner_group'] = 'wm_pbanner_group'; // 프로모션 배너 그룹
	$tbl['ipin_log'] = 'wm_ipin_log'; // 아이핀 로그

	$tbl['join_sms'] = 'wm_join_sms';
	$tbl['join_sms_new'] = 'wm_join_sms_new';

	$tbl['session'] = 'wm_session'; // DB세션
	$tbl['excel_preset'] = 'wm_excel_preset';
	$tbl['neko'] = 'wm_neko';
	$tbl['off_store'] = 'wm_store';
	$tbl['common_trashbox'] = 'wm_common_trashbox';
	$tbl['alimtalk_template'] = 'wm_alimtalk_template'; // 알림톡 메시지

	// 해외배송 관련 테이블
	$tbl['os_delivery_area'] = 'wm_os_delivery_area';
	$tbl['os_delivery_country'] = 'wm_os_delivery_country';
	$tbl['os_delivery_prc'] = 'wm_os_delivery_prc';

	// HS Code
	$tbl['hs_code'] = 'wm_hs_code';

	//입점신청테이블
	$tbl['partner_shop'] = 'wm_partner_shop';
	$tbl['partner_delivery'] = 'wm_partner_delivery';
	$tbl['partner_product_log'] = 'wm_partner_product_log';

	// 오픈마켓연동
	$tbl['openmarket_cfg'] = 'wm_openmarket_cfg';
	$tbl['product_openmarket'] = 'wm_product_openmarket';
	$tbl['openmarket_api_log'] = 'wm_openmarket_api_log';
    $tbl['kakaopaybuy_info'] = 'wm_kakaopaybuy_info';

	// SNS 연결 정보
	$tbl['sns_join']='wm_sns_join';

	// 마케팅 스크립트 관리 테이블
	$tbl['mkt_script'] = 'wm_mkt_script';

    // ERP API 키 테이블
    $tbl['erp_api'] = 'wm_erp_api';

	// 기본 디렉토리명
	$dir['upload']='_data';
	$dir['product']='product';
	$dir['attach']='attach';
	$dir['icon']='icon';
	$dir['gift']='gift';
	$dir['etc']='etc';
	$dir['prd_set']='prd_set';
	$dir['popup']='popup';
	$dir['review']='review';
	$dir['qna']='qna';
	$dir['coupon']='coupon';
	$dir['banner']='banner';
	$dir['prd_common']='prd_common'; // 상품공통정보
	$dir['cate_common']='cate_common';
	$dir['member']='member'; // 회원그룹 아이콘
	$dir['compare']='compare'; // 가격비교 데이터 파일
	$dir['mail']='mail'; // 메일 첨부
	$dir['board_common']='board_common';
	$dir['content']='content';
	$dir['config']='config';
	$dir['conut_log']='conut_log'; // 카운터 로그
	$dir['mobile']='mobile';	// 윙Mobile설정파일

	// 카테고리 컬럼명
	$_cate_colname[1][1]='big';
	$_cate_colname[1][2]='mid';
	$_cate_colname[1][3]='small';
	$_cate_colname[1][4]='depth4';
	$_cate_colname[2][1]='ebig'; // 기획전
	$_cate_colname[3][1]='obig';
	$_cate_colname[3][2]='omid';
	$_cate_colname[4][1]='xbig';
	$_cate_colname[4][2]='xmid';
	$_cate_colname[4][3]='xsmall';
	$_cate_colname[4][4]='xdepth4';
	$_cate_colname[5][1]='ybig';
	$_cate_colname[5][2]='ymid';
	$_cate_colname[5][3]='ysmall';
	$_cate_colname[5][4]='ydepth4';
	$_cate_colname[6][1]='mbig'; // 모바일기획전
	$_cate_colname[9][1]='sbig'; // 창고
	$_cate_colname[9][2]='smid';
	$_cate_colname[9][3]='ssmall';
	$_cate_colname[9][4]='sdepth4';

	//이벤트
	$tbl['event'] = 'wm_event';

	//ip 차단
	$tbl['deny_ip'] = 'wm_deny_ip';

	//관리자2차인증
	$tbl['cfg_confirm_list']='wm_cfg_confirm_list';

	//입점사sms발송
	$tbl['partner_sms'] = 'wm_partner_sms';

	//검색 프리셋
	$tbl['search_preset'] = 'wm_search_preset';

	//프로모션 기획전
	$tbl['promotion_list'] = 'wm_promotion_list';
	$tbl['promotion_link'] = 'wm_promotion_link';
	$tbl['promotion_pgrp_link'] = 'wm_promotion_pgrp_link';
	$tbl['promotion_pgrp_list'] = 'wm_promotion_pgrp_list';

	//입금은행 추가
	$tbl['bank_customer'] = 'wm_bank_customer';

	//개인정보 접속기록
	$tbl['privacy_view_log'] = 'wm_privacy_view_log';

	// bitly 단축 URL 등록 테이블
	$tbl['bitly_shortenter']='wm_bitly_shortenter';

	// robots 로그
	$tbl['robots_log'] = 'wm_robots_log';

	// 자주쓰는 댓글 테이블
	$tbl['often_comment']='wm_often_comment';

	// 재입고 알림 관리
	$tbl['notify_restock'] = 'wm_notify_restock';

	// 상품상세설명 로그
	$tbl['product_content_log']='wm_product_content_log';
	//정기배송
	$tbl['sbscr'] = 'wm_sbscr';
	$tbl['sbscr_product'] = 'wm_sbscr_product';
	$tbl['sbscr_schedule'] = 'wm_sbscr_schedule';
	$tbl['sbscr_schedule_product'] = 'wm_sbscr_schedule_product';
	$tbl['sbscr_set'] = 'wm_sbscr_set';
	$tbl['sbscr_set_product'] = 'wm_sbscr_set_product';
	$tbl['sbscr_holiday'] = 'wm_sbscr_holiday';
	$tbl['sbscr_cart'] = 'wm_sbscr_cart';
    $tbl['subscription_key'] = 'wm_subscription_key';

	// 스마트스토어
	$tbl['product_nstore'] = 'wm_product_nstore';
	$tbl['store_summary'] = 'wm_store_summary';
	$tbl['store_summary_list'] = 'wm_store_summary_list';
	$tbl['store_summary_type'] = 'wm_store_summary_type';

	$tbl['partner_config'] = 'wm_partner_config';

    // 개인정보처리방침
    $tbl['privacy_policy'] = 'wm_privacy_policy';

	//[매장지도] 스키마
	$tbl['store_location'] = 'wm_store_location';
	$tbl['store_operate'] = 'wm_store_operate';
	$tbl['store_operate_time'] = 'wm_store_operate_time';
	$tbl['store_operate_break'] = 'wm_store_operate_break';
	$tbl['store_facility_set'] = 'wm_store_facility_set';
	$tbl['store_wish'] = 'wm_store_wish';
?>