<?PHP

	define('__ENGINE_DIR__', $engine_dir);
    define('__CLASS_DIR__', $engine_dir.'/_engine/include/classes');

	$base_com_suffix = (file_exists($engine_dir.'/_engine/include/account/getMngUrl.inc.php')) ? 'mywisa.com' : 'mywisa.co.kr';
	define('_BASE_DOM_SUFFIX_', $base_com_suffix);
	define('_BASE_CHARSET_', 'utf-8');
	$hp_dom = 'wisa.co.kr';

	$cfg['up_prefix'] = '_data';

	if (isset($_REQUEST['exec_file']) == true) { // main/exec.php 인클루드 보안
		if (in_array(pathinfo($_REQUEST['exec_file'], PATHINFO_EXTENSION), array('php', 'js', 'html')) == false) exit;
		if (preg_match('/\.{2,}|^\//', $_REQUEST['exec_file'])) exit;
	}

	// 관리자 상품 관리 정렬
	$cfg['mng_sort']=array(
	'`edt_date` desc','`edt_date`','`reg_date` desc','`reg_date`','binary(`name`) desc','binary(`name`)',
	'`sell_prc` desc','`sell_prc`','`milage` desc','`milage`','`hit_view` desc','`hit_view`','`hit_order` desc','`hit_order`','`hit_sales` desc','`hit_sales`','`hit_wish` desc','`hit_wish`','`hit_cart` desc','`hit_cart`', '`seller`', '`seller` desc', '`normal_prc` desc', '`normal_prc`',
    'rev_cnt desc', 'rev_avg desc');

	if(empty($cfg['milage_name'])) $cfg['milage_name']='적립금';
	$cfg['max_cate_depth'] = 3; // 최대 카테고리 깊이

	// 상품 상태
	$_prd_stat[1]='등록';
	$_prd_stat[2]='정상';
	$_prd_stat[3]='품절';
	$_prd_stat[4]='숨김';
	$_prd_stat[5]='휴지통';

	// 결제 형태
	$_pay_type[1]="신용카드";
	$_pay_type[2]="무통장 입금";
	$_pay_type[3]="적립금";
	$_pay_type[4]="가상계좌";
	$_pay_type[5]="계좌이체";
	$_pay_type[6]="예치금";
	$_pay_type[7]="휴대폰";
	$_pay_type[8]="현금";
	$_pay_type[9]="복합";
	$_pay_type[24]="나중에결제";
	$_pay_type[10]="Alipay";
	$_pay_type[12]="카카오페이";
	$_pay_type[21]="Paynow";
	$_pay_type[13]="Paypal";
	//$_pay_type[14]="Cyrexpay";
	$_pay_type[15]="EContext";
	$_pay_type[16]="Paypal(e)";
	$_pay_type[17]="payco";
	$_pay_type[18]="Wechat";
	$_pay_type[19]="Alipay(e)";
	$_pay_type[20]="Eximbay";
	$_pay_type[22]="토스결제";
	$_pay_type[23]="정기결제";
	$_pay_type[26]="후불결제";
	$_pay_type[25]="네이버페이";
	$_pay_type[27]="네이버페이\n(정기)";
    $_pay_type[28]="삼성페이";

	// 주문 상태
	$_order_stat[1]="미입금";
	$_order_stat[2]="입금완료";
	$_order_stat_sbscr[2]="결제대기";
	$_order_stat[3]="상품준비중";
	$_order_stat[4]="배송중";
	$_order_stat[5]="배송완료";

	$_order_stat[11]="승인대기";
	$_order_stat[12]="취소요청";
	$_order_stat[21]="취소처리중";
	$_order_stat[13]="취소완료";
	$_order_stat[14]="환불요청";
	$_order_stat[15]="환불완료";
	$_order_stat[16]="반품요청";
	$_order_stat[22]="반품수거중"; // naverpay
	$_order_stat[23]="반품수거완료"; // naverpay
	$_order_stat[28]="반품보류"; // kakaopaybuy
	$_order_stat[17]="반품완료";

	$_order_stat[18]="교환요청";
	$_order_stat[24]="교환수거중"; // naverpay
	$_order_stat[25]="교환수거완료"; // naverpay
	$_order_stat[26]="교환재배송중"; // naverpay
	$_order_stat[19]="교환완료";
	$_order_stat[27]="교환보류"; // kakaotalkstore

	$_order_stat[20]="재고확인중";
	$_order_stat[31]="승인실패";
	$_order_stat[32]="삭제";
	$_order_stat[40]="등록대기";
    $_order_stat[41]="동명이인입금";

	$_sbscr_order_stat[1] = $_order_stat[1];
	$_sbscr_order_stat[2] = $_order_stat[2];
	$_sbscr_order_stat[3] = "진행중";
	$_sbscr_order_stat[5] = '진행종료';
	$_sbscr_order_stat[13] = $_order_stat[13];

	$_order_stat_group[1]=array(1);
	$_order_stat_group[2]=array(2,3,4);
	$_order_stat_group[3]=array(5);
	$_order_stat_group[4]=array(11,12,14);
	$_order_stat_group[5]=array(13,15);
	$_order_stat_group[6]=array(16,22,23,18,24,25,26);
	$_order_stat_group[7]=array(17,19);
	$_order_stat_group[8]=array(11, 31);
	$_order_stat_group[9]=array(40);

	// 상품 재고관리 방법
	$_prd_ea_type[1] = 'ERP';
	$_prd_ea_type[2] = '무제한';
	//$_prd_ea_type[3] = '한정';

// 상품평 상태
	$_review_stat[1]="대기";
	$_review_stat[2]="등록";
	$_review_stat[3]="베스트"; // 베스트 리뷰 (상품평)  2006-01-19
	$_review_stat[4]="인기"; // 인기 리뷰 (상품평)  2010-07-29

// 예약 상태 2006-06-05
	$_reserve_stat[1]="예약완료";
	$_reserve_stat[2]="발송완료";

// 1:1 상담 분류
	$_cust_cate[0][0]="기타";
	$_cust_cate[1][1]="주문 변경";
	$_cust_cate[1][2]="주문 문의";
	$_cust_cate[2][12]="취소 신청";
	$_cust_cate[2][14]="환불 신청";
	$_cust_cate[2][16]="반품 신청";

// 카드 (무이자) 2006-05-18
	$_card_cmp=array('삼성','비씨','국민','외환','신한','엘지','현대','롯데');

	$_order_cash_stat = array(''=>'전체', 1=>'신청',2=>'발급',3=>'취소',4=>'실패',5=>'재발급'); // 2008-02-19 : 재발급 추가(데이콤) - Han


// 적립금 적립 내역 타이틀
	// 적립
	$milage_title[0]="상품 구매";
	$milage_title[1]="회원 가입";
	$milage_title[4]="가입 추천인";
	$milage_title[5]="가입 피추천인";
	$milage_title[2]="결제 금액 적립 이벤트";
	$milage_title[6]="상품평 등록";
	$milage_title[7]="설문 참여";
	$milage_title[3]="기타"; // 추가 타이틀 필수
	$milage_title[8]='사용적립금 복구';
	$milage_title[16]='첫구매적립금';
	$milage_title[17]="가입 추천인(첫구매)";
	$milage_title[18]="가입 피추천인(첫구매)";

	// 사용
	$milage_title[11]="상품 구매";
	$milage_title[12]="주문 상태 변경";
	$milage_title[13]="주문 취소";
	$milage_title[14]="소셜 쿠폰 적립";
	$milage_title[15]="적립금만료";
	$milage_title[19]="가입 추천인(첫구매 취소)";
	$milage_title[20]="가입 피추천인(첫구매 취소)";

	if(empty($cfg['escrow_limit'])) {
		$cfg['escrow_limit'] = 0;
	}

	$bank_codes = array(
		'004' => '국민은행',
		'003' => '기업은행',
		'011' => '농협중앙회',
		'012' => '농협개인',
		'002' => '산업은행',
		'050' => '상호저축은행',
		'007' => '수협',
		'027' => '시티은행',
		'088' => '신한은행',
		'048' => '신협',
		'045' => '새마을금고',
		'020' => '우리은행',
		'071' => '우체국',
		'023' => '제일은행',
		'081' => 'KEB하나은행',
		'034' => '광주은행',
		'039' => '경남은행',
		'031' => '대구은행',
		'032' => '부산은행',
		'037' => '전북은행',
		'035' => '제주은행',
		'090' => '카카오뱅크',
		'089' => '케이뱅크',
		'055' => '도이치은행',
		'060' => '뱅크오브아메리카',
		'061' => '비엔피파리바은행',
		'057' => '제이피모간체이스',
		'054' => '홍콩상하이(HSBC)',
	);

	$_erp_force_stat = array(
		'N' => '무제한',
		'Y' => '강제품절',
		'L' => '한정',
	);

	// 결제 상태
	$_order_payment_type = array(
		0 => '최초결제',
		1 => '환불',
		2 => '추가결제',
		3 => '변경',
		4 => '복구',
	);

	// 결제 승인 상태
	$_order_payment_stat = array(
		0 => '미입금취소',
		1 => '대기',
		2 => '완료',
		3 => '복구',
	);

	// 결제 추가 배송비 타입
	$_order_payment_dlv = array(
		1 => '편도',
		2 => '왕복',
	);

	// 쿠폰 종류
	$_cpn_stype = array(
		1 => '장바구니 쿠폰',
		3 => '무료배송 쿠폰',
		5 => '개별상품 쿠폰',
	);

	$_cpn_downtype = array(
		'A' => '전체 다운로드 가능',
		'B' => '회원 등급별 다운로드'
	);

	$_cpn_pay_type = array(
		'1' => '모든 결제',
		'2' => '현금 전용',
	);

	$_cpn_sale_type = array(
		'm' => '원',
		'p' => '%',
		'e' => '개',
	);

	$_cpn_release_type = array(
		1 => '무한',
		2 => '한정',
	);

	$_cpn_download_limit_type = array(
		1 => '무한',
		2 => '사용 후 다시 다운로드 가능',
		3 => '한정'
	);

	$_cpn_use_limit_type = array(
		'' => '무제한',
		'1' => '할인 된 상품은 쿠폰할인 하지 않음',
		'2' => '할인 된 상품이 있을 경우 쿠폰 사용 불가',
		'3' => '주문서 내의 다른 할인을 취소하고 쿠폰 적용',
	);

	$_cpn_attatch_type = array(
		0 => '전체 적용',
		1 => '지정 카테고리에만 적용',
		2 => '지정 상품에만 적용',
		3 => '지정 카테고리 제외',
		4 => '지정 상품 제외'
	);

	$_exchange_before_stat = array(
		13 => '입금 전 교환',
		15 => '배송 전 교환',
		17 => '배송 후 교환',
		19 => '배송 후 교환',
		00 => '상품 추가',
	);

	// 화폐 단위
	$_currency = array(
		'원'=>'한국 원(KRW)',
		'USD'=>'미국 달러(USD)',
		'CNY'=>'중국 위안(CNY)',
		'JPY'=>'일본 엔(JPY)',
		'AUD'=>'호주 달러(AUD)'
	);

	// 화폐별 소수점
	$_currency_decimal = array(
		'원'=>'0',
		'USD'=>'2',
		'$' => '3',
		'CNY'=>'2',
		'￥' => '2',
		'JPY'=>'0',
		'AUD'=>'2'
	);

	// 입점 회원사 상태
	$_partner_stats = array(
		1 => '신청',
		2 => '정상',
		3 => '보류',
		4 => '만료',
	);

	$_partner_prd_stat = array(
        5 => '신청대기',
		1 => '신청완료',
		2 => '등록',
		3 => '반려',
		4 => '만료',
	);

	$_mng_levels = array(
		2 => '최고관리자',
		3 => '부관리자',
		4 => '입점사관리자',
	);

	$_order_account_stat = array(
		1 => '정산등록',
		3 => '정산승인',
		4 => '부분정산',
		5 => '정산완료',
		6 => '정산취소',
	);

	$_sns_type = array(
		"KA" => 'kko',
		"NA" => 'nvr',
		"FB" => 'fb',
		"PC" => 'pyc',
		"WN" => 'wnd',
		"AP" => 'apple'
	);

    $_sns_type_info = array(
		'KA' => array(
            'name' => '카카오',
            'name_en' => 'kakao',
        ),
		'NA' => array(
            'name' => '네이버',
            'name_en' => 'naver',
        ),
		'FB' => array(
            'name' => '페이스북',
            'name_en' => 'facebook',
        ),
		'PC' => array(
            'name' => '페이코',
            'name_en' => 'payco',
        ),
		'WN' => array(
            'name' => '위메프',
            'name_en' => 'wonder',
        ),
		'AP' => array(
            'name' => '애플',
            'name_en' => 'apple',
        ),
    );

	$sns_join_url = "/member/apijoin.php";

	$_w_naver_sns_id = "IN37OvXxQKKjgC1a3aqs";
	$_w_naver_sns_secret = "DmGdQ1G5TJ";

	// 옵션 형태
	$_otype['2A']="콤보박스";
	$_otype['3A']="라디오버튼";
	$_otype['3B']="라디오버튼 + 줄바꿈";
	$_otype['4A']="면적입력";
	$_otype['4B']="텍스트입력";
	$_otype['5A']="컬러칩";
	$_otype['5B']="텍스트칩";

	// 우편번호 DB 호환 매핑
	$_sido_mapping = array(
		'강원특별자치도' => '강원',
		'경기도' => '경기',
		'경상남도' => '경남',
		'경상북도' => '경북',
		'광주광역시' => '광주',
		'대구광역시' => '대구',
		'대전광역시' => '대전',
		'부산광역시' => '부산',
		'서울특별시' => '서울',
		'세종특별자치시' => '세종',
		'울산광역시' => '울산',
		'인천광역시' => '인천',
		'전라남도' => '전남',
		'전라북도' => '전북',
		'제주특별자치도' => '제주',
		'충청남도' => '충남',
		'충청북도' => '충북',
	);

	// 사용되는 세일 종류 (독립몰 전용 할인 기능 생성 시 50번대 이후로 생성해주세요.)
	$_order_sales = $_order_sales_org = array(
		'sale1' => '세트할인',
		'sale2' => '이벤트',
		'sale3' => '타임세일',
		'sale4' => '회원할인',
		'sale5' => '전체쿠폰',
		'sale7' => '개별쿠폰',
		'sale6' => '금액할인',
		'sale0' => '결제방식',
		'sale8' => '정기배송',
		'sale9' => '수량할인',
	);

	// 프로모션 상품 정렬
	$_prd_by_name=array();
	$_prd_by=array();

	$_prd_by_name[1]="높은가격순";
	$_prd_by[1]="`sell_prc` desc";
	$_prd_by_name[2]="낮은가격순";
	$_prd_by[2]="`sell_prc` asc";
	$_prd_by_name[3]="판매량높은순";
	$_prd_by[3]="`hit_order` desc";
	$_prd_by_name[4]="판매량낮은순";
	$_prd_by[4]="`hit_order` asc";
	$_prd_by_name[5]="조회수높은순";
	$_prd_by[5]="`hit_view` desc";
	$_prd_by_name[6]="조회수낮은순";
	$_prd_by[6]="`hit_view` asc";

	$_kr_state_code = array(
		'서울특별시',
		'부산광역시',
		'대구광역시',
		'인천광역시',
		'광주광역시',
		'대전광역시',
		'울산광역시',
		'세종특별자치시',
		'경기도',
		'강원특별자치도',
		'충청북도',
		'충청남도',
		'전라북도',
		'전라남도',
		'경상북도',
		'경상남도',
		'제주특별자치도',
	);

	// 자주쓰는 댓글 대상 게시판 목록
	$_often_cate_name = array(
		'qna' => '상품Q&A',
		'cs' => '1:1문의',
		'review' => '상품후기',
	);

	// 배송타입
	$_delivery_types = array(
		1 => '무료 배송',
		2 => '착불 배송',
		3 => '금액별 배송',
		4 => '금액별 차등 배송',
		5 => '수량별 차등 배송',
		6 => '고정 배송',
	);

	// 관리자 수신 sms 의 case 번호
	$sms_case_admin = array(
		11, 12, 18, 17, 19, 40
	);

    // 배송업체 종류
	$_overseas_delivery_arr = array(
        'D' => '국내배송',
        'O' => '해외배송'
    );

	// email suffix
	$_email_suffix = array(
		'naver.com',
		'daum.net',
		'hanmail.net',
		'gmail.com',
		'nate.com',
		'hotmail.com',
		'yahoo.com',
		'empas.com',
		'korea.com',
		'dreamwiz.com',
	);

    // 도서 상품
    $_is_book_type = array(
        'N' => '일반상품',
        'P' => '지류도서',
        'E' => 'E북',
        'A' => '오디오북',
    );

    // 배송메세지
    $_default_dlv_memo = array(
        '배송 전에 미리 연락바랍니다.',
        '부재시 경비실에 맡겨 주세요.',
        '부재시 전화 주시거나 문자 남겨주세요.'
    );
	//[매장지도] 상태 추가
	$_store_config_stat = array(
		'2'=>'정상',
		'3'=>'휴업',
		'4'=>'폐업'
	);

	//[매장지도] 상태 추가
	$_store_config_reverse_stat = array(
		'정상'=>'2',
		'휴업'=>'3',
		'폐업'=>'4'
	);

	//[매장지도] 노출 여부 추가
	$_store_config_hidden = array(
		'N'=>'노출',
		'Y'=>'숨김'
	);

	//[매장지도] 영업 시간 설정
	$_schedul_period_config = array(
		"1"=>"매일",
		"2"=>"매주",
		"3"=>"매월",
		"4"=>"매년",
		"5"=>"공휴일"
	);

	//[매장지도] 요일 표시
	$_schedul_week_config = array(
		"1"=>"월",
		"2"=>"화",
		"3"=>"수",
		"4"=>"목",
		"5"=>"금",
		"6"=>"토",
		"7"=>"일"
	);

	//[매장지도] 영업상태 표시
	$_operate_stat_config = array(
		'1'=>'영업중',
		'2'=>'영업종료',
		'3'=>'휴업',
		'4'=>'폐업'
	);

	//[매장지도] 영업시간 요일 표시
	$_operate_otype_config['A'] = array(
		'1' =>'영업시간'
	);

	$_operate_otype_week_config['A'] = array(
		'1' =>'1,2,3,4,5,6,7'
	);

	$_operate_otype_config['B'] = array(
		'1' =>'평일(월~금)',
		'2' =>'토요일',
		'3' =>'일요일'
	);

	$_operate_otype_week_config['B'] = array(
		'1' =>'1,2,3,4,5',
		'2' =>'6',
		'3' =>'7'
	);

	$_operate_otype_config['C'] = array(
		'1' =>'영업시간'
	);


?>