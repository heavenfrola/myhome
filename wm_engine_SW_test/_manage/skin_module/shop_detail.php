<?PHP

	$_replace_code[$_file_name]['detail_form_start']="";
	$_replace_hangul[$_file_name]['detail_form_start']="폼시작";
	$_auto_replace[$_file_name]['detail_form_start']="Y";
	$_code_comment[$_file_name]['detail_form_start']="상품 상세페이지 폼 시작 선언";

	$_replace_code[$_file_name]['detail_form_end']="";
	$_replace_hangul[$_file_name]['detail_form_end']="폼끝";
	$_auto_replace[$_file_name]['detail_form_end']="Y";
	$_code_comment[$_file_name]['detail_form_end']="상품 상세페이지 폼 끝 선언";

	$_replace_code[$_file_name]['detail_preprd_url']="";
	$_replace_hangul[$_file_name]['detail_preprd_url']="이전상품보기";
	$_auto_replace[$_file_name]['detail_preprd_url']="Y";
	$_code_comment[$_file_name]['detail_preprd_url']="이전 상품 상세페이지 링크(A 태그) 출력";

	$_replace_code[$_file_name]['detail_nextprd_url']="";
	$_replace_hangul[$_file_name]['detail_nextprd_url']="다음상품보기";
	$_auto_replace[$_file_name]['detail_nextprd_url']="Y";
	$_code_comment[$_file_name]['detail_nextprd_url']="다음 상품 상세페이지 링크(A 태그) 출력";

	$_replace_code[$_file_name]['cate_name']="";
	$_replace_hangul[$_file_name]['cate_name']="카테고리명";
	$_auto_replace[$_file_name]['cate_name']="Y";
	$_code_comment[$_file_name]['cate_name']="선택된 카테고리명";

	$_replace_code[$_file_name]['cate_cno1']="";
	$_replace_hangul[$_file_name]['cate_cno1']="카테고리코드";
	$_auto_replace[$_file_name]['cate_cno1']="Y";
	$_code_comment[$_file_name]['cate_cno1']="선택된 카테고리 코드";

	$_replace_code[$_file_name]['cate_ctype']="";
	$_replace_hangul[$_file_name]['cate_ctype']="카테고리타입";
	$_auto_replace[$_file_name]['cate_ctype']="Y";
	$_code_comment[$_file_name]['cate_ctype']="선택된 카테고리타입(1:일반분류, 2:기획전";
	if($cfg['xbig_mng'] == "Y") $_code_comment[$_file_name]['cate_ctype'].=", 4:".$cfg['xbig_name_mng'];
	if($cfg['ybig_mng'] == "Y") $_code_comment[$_file_name]['cate_ctype'].=", 5:".$cfg['ybig_name_mng'];
	$_code_comment[$_file_name]['cate_ctype'].=")";

	$_replace_code[$_file_name]['cate_level']="";
	$_replace_hangul[$_file_name]['cate_level']="카테고리레벨";
	$_auto_replace[$_file_name]['cate_level']="Y";
	$_code_comment[$_file_name]['cate_level']="선택된 카테고리레벨(1:대분류, 2:중분류, 3:소분류)";

	$_replace_code[$_file_name]['prd_big'] = $prd['big'];
	$_replace_hangul[$_file_name]['prd_big'] = "상품대분류코드";
	$_auto_replace[$_file_name]['prd_big'] = "Y";
	$_code_comment[$_file_name]['prd_big'] = "현재 상품의 카테고리 대분류 코드";

	$_replace_code[$_file_name]['prd_mid'] = $prd['mid'];
	$_replace_hangul[$_file_name]['prd_mid'] = "상품중분류코드";
	$_auto_replace[$_file_name]['prd_mid'] = "Y";
	$_code_comment[$_file_name]['prd_mid'] = "현재 상품의 카테고리 중분류 코드";

	$_replace_code[$_file_name]['prd_small'] = $prd['small'];
	$_replace_hangul[$_file_name]['prd_small'] = "상품소분류코드";
	$_auto_replace[$_file_name]['prd_small'] = "Y";
	$_code_comment[$_file_name]['prd_small'] = "현재 상품의 카테고리 소분류 코드";

	$_replace_code[$_file_name]['detail_prd_img']="";
	$_replace_hangul[$_file_name]['detail_prd_img']="상품이미지";
	$_auto_replace[$_file_name]['detail_prd_img']="Y";
	$_code_comment[$_file_name]['detail_prd_img']="상품상세페이지 상품이미지";

	$_replace_code[$_file_name]['detail_img1']="";
	$_replace_hangul[$_file_name]['detail_img1']="상품대이미지";
	$_auto_replace[$_file_name]['detail_img1']="Y";
	$_code_comment[$_file_name]['detail_img1']="상품 대이미지 주소";

	$_replace_code[$_file_name]['detail_img2']="";
	$_replace_hangul[$_file_name]['detail_img2']="상품중이미지";
	$_auto_replace[$_file_name]['detail_img2']="Y";
	$_code_comment[$_file_name]['detail_img2']="상품 중이미지 주소";

	$_replace_code[$_file_name]['detail_img3']="";
	$_replace_hangul[$_file_name]['detail_img3']="상품소이미지";
	$_auto_replace[$_file_name]['detail_img3']="Y";
	$_code_comment[$_file_name]['detail_img3']="상품 소이미지 주소";

	$_replace_code[$_file_name]['detail_zoom_url']="";
	$_replace_hangul[$_file_name]['detail_zoom_url']="크게보기";
	$_auto_replace[$_file_name]['detail_zoom_url']="Y";
	$_code_comment[$_file_name]['detail_zoom_url']="상품 확대이미지 팝업창 링크 주소 출력";

	$_replace_code[$_file_name]['detail_prd_name']="";
	$_replace_hangul[$_file_name]['detail_prd_name']="상품명";
	$_auto_replace[$_file_name]['detail_prd_name']="Y";
	$_code_comment[$_file_name]['detail_prd_name']="해당 상품명";

	$_replace_code[$_file_name]['detail_referer_name']="";
	$_replace_hangul[$_file_name]['detail_referer_name']="참고상품명";
	$_auto_replace[$_file_name]['detail_referer_name']="Y";
	$_code_comment[$_file_name]['detail_referer_name']="참고상품명 출력";

	$_replace_code[$_file_name]['detail_prd_icons']="";
	$_replace_hangul[$_file_name]['detail_prd_icons']="상품아이콘";
	$_auto_replace[$_file_name]['detail_prd_icons']="Y";
	$_code_comment[$_file_name]['detail_prd_icons']="해당 상품의 아이콘";

	$_replace_code[$_file_name]['detail_prd_code']="";
	$_replace_hangul[$_file_name]['detail_prd_code']="상품코드";
	$_auto_replace[$_file_name]['detail_prd_code']="Y";
	$_code_comment[$_file_name]['detail_prd_code']="해당 상품의 코드";

	$_replace_code[$_file_name]['detail_sell_prc_name']="";
	$_replace_hangul[$_file_name]['detail_sell_prc_name']="판매가격명칭";
	$_auto_replace[$_file_name]['detail_sell_prc_name']="Y";
	$_code_comment[$_file_name]['detail_sell_prc_name']="상품 판매 가격 명칭";

	$_replace_code[$_file_name]['detail_sell_prc']="";
	$_replace_hangul[$_file_name]['detail_sell_prc']="판매가격";
	$_auto_replace[$_file_name]['detail_sell_prc']="Y";
	$_code_comment[$_file_name]['detail_sell_prc']="해당 상품의 판매 가격";

	$_replace_code[$_file_name]['detail_nml_prc_name']="";
	$_replace_hangul[$_file_name]['detail_nml_prc_name']="소비자가격명칭";
	$_auto_replace[$_file_name]['detail_nml_prc_name']="Y";
	$_code_comment[$_file_name]['detail_nml_prc_name']="상품 소비자 가격 명칭";

	$_replace_code[$_file_name]['detail_nml_prc']="";
	$_replace_hangul[$_file_name]['detail_nml_prc']="소비자가격";
	$_auto_replace[$_file_name]['detail_nml_prc']="Y";
	$_code_comment[$_file_name]['detail_nml_prc']="해당 상품의 소비자가격";

	$_replace_code[$_file_name]['detail_msale_prc2']="";
	$_replace_hangul[$_file_name]['detail_msale_prc2']="회원할인가격";
	$_auto_replace[$_file_name]['detail_msale_prc2']="Y";
	$_code_comment[$_file_name]['detail_msale_prc2']="회원할인이 있을 경우 회원할인가격. 없을 경우 판매가격";

	$_replace_code[$_file_name]['detail_pay_prc']="";
	$_replace_hangul[$_file_name]['detail_pay_prc']="할인후실판매가";
	$_auto_replace[$_file_name]['detail_pay_prc']="Y";
	$_code_comment[$_file_name]['detail_pay_prc']="이벤트할인, 회원할인, 타임세일이 적용된 상품 판매가";

	$_replace_code[$_file_name]['total_sale_per1'] = '';
	$_replace_hangul[$_file_name]['total_sale_per1'] = '할인율';
	$_auto_replace[$_file_name]['total_sale_per1'] = 'Y';
	$_code_comment[$_file_name]['total_sale_per1'] = '할인금액/판매가 기준 할인율';

	$_replace_code[$_file_name]['total_sale_per2'] = '';
	$_replace_hangul[$_file_name]['total_sale_per2'] = '판매가인하율';
	$_auto_replace[$_file_name]['total_sale_per2'] = 'Y';
	$_code_comment[$_file_name]['total_sale_per2'] = '소비자가/판매가 기준 인하율';

	$_replace_code[$_file_name]['total_sale_per3'] = '';
	$_replace_hangul[$_file_name]['total_sale_per3'] = '할인및인하율';
	$_auto_replace[$_file_name]['total_sale_per3'] = 'Y';
	$_code_comment[$_file_name]['total_sale_per4'] = '할인금액+(소비자가-판매가)/판매가 기준 할인율';

	$_replace_code[$_file_name]['detail_is_sale']="";
	$_replace_hangul[$_file_name]['detail_is_sale']="할인적용여부";
	$_auto_replace[$_file_name]['detail_is_sale']="Y";
	$_code_comment[$_file_name]['detail_is_sale']="현재상품의 이벤트할인, 회원할인, 타임세일 적용시 Y 출력";

	$_replace_code[$_file_name]['detail_prd_milage']="";
	$_replace_hangul[$_file_name]['detail_prd_milage']="적립금";
	$_auto_replace[$_file_name]['detail_prd_milage']="Y";
	$_code_comment[$_file_name]['detail_prd_milage']="해당 상품의 적립금";

	$_replace_code[$_file_name]['detail_prd_milage2'] = '';
	$_replace_hangul[$_file_name]['detail_prd_milage2'] = '상품적립금';
	$_auto_replace[$_file_name]['detail_prd_milage2'] = 'Y';
	$_code_comment[$_file_name]['detail_prd_milage2'] = '할인적용 후 적용되는 실 상품 적립금';

	$_replace_code[$_file_name]['detail_member_milage'] = '';
	$_replace_hangul[$_file_name]['detail_member_milage'] = '회원적립금';
	$_auto_replace[$_file_name]['detail_member_milage'] = 'Y';
	$_code_comment[$_file_name]['detail_member_milage'] = '해당 상품의 우수회원 적립금';

	$_replace_code[$_file_name]['detail_event_milage'] = '';
	$_replace_hangul[$_file_name]['detail_event_milage'] = '이벤트적립금';
	$_auto_replace[$_file_name]['detail_event_milage'] = 'Y';
	$_code_comment[$_file_name]['detail_event_milage'] = '해당 상품의 할인적립이벤트 적립금';

	$_replace_code[$_file_name]['detail_all_milage'] = '';
	$_replace_hangul[$_file_name]['detail_all_milage'] = '총적립금';
	$_auto_replace[$_file_name]['detail_all_milage'] = 'Y';
	$_code_comment[$_file_name]['detail_all_milage'] = '해당 상품시 지급 받을수 있는 상품적립금과 회원적립금을 포함한 총 실적립금';

	$_replace_code[$_file_name]['detail_delivery_prc'] = number_format($prd['delivery_prc']);
	$_replace_hangul[$_file_name]['detail_delivery_prc']="상품별배송비";
	$_auto_replace[$_file_name]['detail_delivery_prc']="Y";
	$_code_comment[$_file_name]['detail_delivery_prc']="상품 낱개 배송비";

	$_replace_code[$_file_name]['detail_cod_prc'] = '';
	$_replace_hangul[$_file_name]['detail_cod_prc'] = '상품별착불배송비';
	$_auto_replace[$_file_name]['detail_cod_prc'] = 'Y';
	$_code_comment[$_file_name]['detail_cod_prc'] = '상품 낱개의 착불 배송비';

	$_replace_code[$_file_name]['detail_event_prc']="";
	$_replace_hangul[$_file_name]['detail_event_prc']="이벤트금액";
	$_auto_replace[$_file_name]['detail_event_prc']="Y";
	$_code_comment[$_file_name]['detail_event_prc']="이벤트 존재시 해당 상품의 할인 또는 적립금액";

	$_replace_code[$_file_name]['detail_timesale_prc']="";
	$_replace_hangul[$_file_name]['detail_timesale_prc']="타임세일금액";
	$_auto_replace[$_file_name]['detail_timesale_prc']="Y";
	$_code_comment[$_file_name]['detail_timesale_prc']="타임세일 적용시 할인 된 금액";

	$_replace_code[$_file_name]['detail_timesale_ing']="";
	$_replace_hangul[$_file_name]['detail_timesale_ing']="타임세일진행여부";
	$_auto_replace[$_file_name]['detail_timesale_ing']="Y";
	$_code_comment[$_file_name]['detail_timesale_ing']="타임세일이 현재 진행중일 경우 Y 출력";

	$_replace_code[$_file_name]['detail_timesale_timer']="";
	$_replace_hangul[$_file_name]['detail_timesale_timer']="타임세일타이머";
	$_auto_replace[$_file_name]['detail_timesale_timer']="Y";
	$_code_comment[$_file_name]['detail_timesale_timer']="타임세일 적용시 남은 시간을 출력";

	$_replace_code[$_file_name]['detail_msale_prc']="";
	$_replace_hangul[$_file_name]['detail_msale_prc']="회원할인금액";
	$_auto_replace[$_file_name]['detail_msale_prc']="Y";
	$_code_comment[$_file_name]['detail_msale_prc']="회원할인 적용시 할인 된 금액";

	$_replace_code[$_file_name]['detail_min_num']="";
	$_replace_hangul[$_file_name]['detail_min_num']="최소주문수량";
	$_auto_replace[$_file_name]['detail_min_num']="Y";
	$_code_comment[$_file_name]['detail_min_num']="해당 상품의 최소 주문 가능 수량";

	$_replace_code[$_file_name]['detail_max_num']="";
	$_replace_hangul[$_file_name]['detail_max_num']="최대주문수량";
	$_auto_replace[$_file_name]['detail_max_num']="Y";
	$_code_comment[$_file_name]['detail_max_num']="해당 상품의 최대 주문 가능 수량";

	$_replace_code[$_file_name]['detail_soldout']="";
	$_replace_hangul[$_file_name]['detail_soldout']="품절시내용";
	$_code_comment[$_file_name]['detail_soldout']="품절시에만 출력할 내용.";

	$_replace_code[$_file_name]['detail_soldout2']="";
	$_replace_hangul[$_file_name]['detail_soldout2']="품절시내용2";
	$_code_comment[$_file_name]['detail_soldout2']="품절시에만 출력할 내용.";

	$_replace_code[$_file_name]['detail_not_soldout']="";
	$_replace_hangul[$_file_name]['detail_not_soldout']="정상판매시내용";
	$_code_comment[$_file_name]['detail_not_soldout']="정상 판매상품 조회시에만 출력할 내용.";

	$_replace_code[$_file_name]['detail_not_soldout2']="";
	$_replace_hangul[$_file_name]['detail_not_soldout2']="정상판매시내용2";
	$_code_comment[$_file_name]['detail_not_soldout2']="정상 판매상품 조회시에만 출력할 내용.";

	$_replace_code[$_file_name]['detail_partner_name'] = ($prd['partner_no'] > 0) ? stripslashes($pdo->row("select corporate_name from $tbl[partner_shop] where no='$prd[partner_no]'")) : '';
	$_replace_hangul[$_file_name]['detail_partner_name'] = '입점파트너명';
	$_code_comment[$_file_name]['detail_partner_name'] = '현재 상품의 입점파트너명 표시';

	$_replace_code[$_file_name]['detail_weight'] = number_format($prd['weight'], 2);
	$_replace_hangul[$_file_name]['detail_weight'] = '상품무게';
	$_auto_replace[$_file_name]['detail_weight'] = 'Y';
	$_code_comment[$_file_name]['detail_weight'] = '상품별로 입력한 무게정보';

	$_replace_code[$_file_name]['detail_is_dlv_alone'] = ($prd['dlv_alone'] == 'Y') ? 'singleorder' : '';
	$_replace_hangul[$_file_name]['detail_is_dlv_alone']="단독배송여부";
	$_auto_replace[$_file_name]['detail_is_dlv_alone']="Y";
	$_code_comment[$_file_name]['detail_is_dlv_alone']="단독배송일 경우 singleorder 메시지가 출력됩니다.";

	$_replace_code[$_file_name]['detail_stock_qty'] = '';
	$_replace_hangul[$_file_name]['detail_stock_qty'] = '총상품재고';
	$_code_comment[$_file_name]['detail_stock_qty'] = '현재 상품의 총 재고를 출력. 무제한일 경우 무제한으로 표시됩니다.';

	$_replace_code[$_file_name]['detail_content1']="";
	$_replace_hangul[$_file_name]['detail_content1']="상품요약설명";
	$_auto_replace[$_file_name]['detail_content1']="Y";
	$_code_comment[$_file_name]['detail_content1']="해당 상품의 요약 설명 출력";

	$_replace_code[$_file_name]['detail_content2']="";
	$_replace_hangul[$_file_name]['detail_content2']="상품상세설명";
	$_auto_replace[$_file_name]['detail_content2']="Y";
	$_code_comment[$_file_name]['detail_content2']="해당 상품의 상세 정보 출력";

	$_replace_code[$_file_name]['detail_content2_more'] = '';
	$_replace_hangul[$_file_name]['detail_content2_more'] = '더보기형상품상세설명';
	$_code_comment[$_file_name]['detail_content2_more'] = '더보기형 상품상세설명 추가';
	$_replace_datavals[$_file_name]['detail_content2_more'] = '상품상세설명:content2;상세설명더보기클래스:more_view_class;상세설명숨기기클래스:more_hide_class;';

	$_replace_code[$_file_name]['detail_content3']="";
	$_replace_hangul[$_file_name]['detail_content3']="배송정보";
	$_auto_replace[$_file_name]['detail_content3']="Y";
	$_code_comment[$_file_name]['detail_content3']="해당 상품의 ".($cfg['content3'] ? $cfg['content3'] : "배송정보 (공통 정보1)");

	$_replace_code[$_file_name]['detail_content4']="";
	$_replace_hangul[$_file_name]['detail_content4']="반품취소안내";
	$_auto_replace[$_file_name]['detail_content4']="Y";
	$_code_comment[$_file_name]['detail_content4']="해당 상품의 ".($cfg['content4'] ? $cfg['content4'] : "배송정보 (공통 정보2)");;

	$_replace_code[$_file_name]['detail_content5']="";
	$_replace_hangul[$_file_name]['detail_content5']="AS안내";
	$_auto_replace[$_file_name]['detail_content5']="Y";
	$_code_comment[$_file_name]['detail_content5']="해당 상품의 ".($cfg['content5'] ? $cfg['content5'] : "배송정보 (공통 정보3)");;

	$_replace_code[$_file_name]['detail_content6']="";
	$_replace_hangul[$_file_name]['detail_content6']="공통정보1";
	$_auto_replace[$_file_name]['detail_content6']="Y";
	$_code_comment[$_file_name]['detail_content6']="해당 상품의 ".($cfg['content3'] ? $cfg['content3'] : "배송정보 (공통 정보1)");

	$_replace_code[$_file_name]['detail_content7']="";
	$_replace_hangul[$_file_name]['detail_content7']="공통정보2";
	$_auto_replace[$_file_name]['detail_content7']="Y";
	$_code_comment[$_file_name]['detail_content7']="해당 상품의 ".($cfg['content4'] ? $cfg['content4'] : "배송정보 (공통 정보2)");;

	$_replace_code[$_file_name]['detail_content8']="";
	$_replace_hangul[$_file_name]['detail_content8']="공통정보3";
	$_auto_replace[$_file_name]['detail_content8']="Y";
	$_code_comment[$_file_name]['detail_content8']="해당 상품의 ".($cfg['content5'] ? $cfg['content5'] : "배송정보 (공통 정보3)");;

	$_replace_code[$_file_name]['detail_total_qna']="";
	$_replace_hangul[$_file_name]['detail_total_qna']="상품질답개수";
	$_auto_replace[$_file_name]['detail_total_qna']="Y";
	$_code_comment[$_file_name]['detail_total_qna']="해당 상품에 등록된 총 질문과 답변 개수";

	$_replace_code[$_file_name]['detail_review_url']="";
	$_replace_hangul[$_file_name]['detail_review_url']="상품평보기";
	$_auto_replace[$_file_name]['detail_review_url']="Y";
	$_code_comment[$_file_name]['detail_review_url']="해당 상품의 상품평 링크 주소 출력";

	$_replace_code[$_file_name]['detail_qna_url']="";
	$_replace_hangul[$_file_name]['detail_qna_url']="상품질답보기";
	$_auto_replace[$_file_name]['detail_qna_url']="Y";
	$_code_comment[$_file_name]['detail_qna_url']="해당 상품의 질문과 답변 주소 출력";

	$_replace_code[$_file_name]['detail_review_pageres']="";
	$_replace_hangul[$_file_name]['detail_review_pageres']="상품평페이지선택";
	$_auto_replace[$_file_name]['detail_review_pageres']="Y";
	$_code_comment[$_file_name]['detail_review_pageres']="해당 상품의 상품평 리스트 페이지 선택";

	$_replace_code[$_file_name]['detail_qna_pageres']="";
	$_replace_hangul[$_file_name]['detail_qna_pageres']="상품질답페이지선택";
	$_auto_replace[$_file_name]['detail_qna_pageres']="Y";
	$_code_comment[$_file_name]['detail_qna_pageres']="해당 상품의 질문과 답변 리스트 페이지 선택";

	$_replace_code[$_file_name]['detail_fd_list']="";
	$_replace_hangul[$_file_name]['detail_fd_list']="상품항목리스트";
	$_code_comment[$_file_name]['detail_fd_list']="해당 상품의 항목 리스트";
	$_replace_datavals[$_file_name]['detail_fd_list']="항목명:name;항목값:value;항목이미지:fd_img:항목이미지 출력";

	$_replace_code[$_file_name]['detail_fdinfo_list']="";
	$_replace_hangul[$_file_name]['detail_fdinfo_list']="상품정보고시리스트";
	$_code_comment[$_file_name]['detail_fdinfo_list']="해당 상품의 상품정보고시 리스트";
	$_replace_datavals[$_file_name]['detail_fdinfo_list']="항목명:name;항목값:value;항목이미지:fd_img:항목이미지 출력";

	$_replace_code[$_file_name]['detail_opt_list']="";
	$_replace_hangul[$_file_name]['detail_opt_list']="상품옵션리스트";
	$_code_comment[$_file_name]['detail_opt_list']="해당 상품의 옵션 리스트";
	$_replace_datavals[$_file_name]['detail_opt_list']="옵션명:name;옵션값:option_str;옵션설명:option_desc;";

	$_replace_code[$_file_name]['normal_opt_list']="";
	$_replace_hangul[$_file_name]['normal_opt_list']="상품옵션리스트1";
	$_code_comment[$_file_name]['normal_opt_list']="면적옵션을 제외한 상품옵션 리스트. '상품옵션리스트'와 같이 사용불가.";
	$_replace_datavals[$_file_name]['normal_opt_list']="옵션명:name;옵션값:option_str;옵션설명:option_desc;";

	$_replace_code[$_file_name]['area_opt_list']="";
	$_replace_hangul[$_file_name]['area_opt_list']="상품옵션리스트2";
	$_code_comment[$_file_name]['area_opt_list']="면적옵션 리스트. '상품옵션리스트'와 같이 사용불가.";
	$_replace_datavals[$_file_name]['area_opt_list']="옵션명:name;옵션값:option_str;옵션설명:option_desc;면적옵션실시간가격코드:area_option_id;";

	$_replace_code[$_file_name]['detail_opt_img_list']="";
	$_replace_hangul[$_file_name]['detail_opt_img_list']="이미지칩리스트";
	$_code_comment[$_file_name]['detail_opt_img_list']="옵션형식이 컬러칩일 경우 출력되는 컬러칩 리스트";
	$_replace_datavals[$_file_name]['detail_opt_img_list'] = "옵션순서:idx;옵션번호:no;옵션칩선택:script;이미지주소:upfile1;색상코드:color_code;옵션값:iname;";

	$_replace_code[$_file_name]['detail_opt_txt_list']="";
	$_replace_hangul[$_file_name]['detail_opt_txt_list']="텍스트칩리스트";
	$_code_comment[$_file_name]['detail_opt_txt_list']="옵션형식이 텍스트칩일 경우 출력되는 텍스트칩 리스트";
	$_replace_datavals[$_file_name]['detail_opt_txt_list'] = "옵션순서:idx;옵션번호:no;옵션칩선택:script;옵션값:iname;";

	for($refkey = 1; $refkey <= $cfg['refprds']; $refkey++) {
		$_refkey = $refkey == 1 ? '' : $refkey;
		$refname = $cfg['refprd'.$refkey.'_name'];
		if(!$refname) $refname = '관련상품'.$refkey;

		$_replace_code[$_file_name]['detail_ref'.$_refkey.'_cart_url']="";
		$_replace_hangul[$_file_name]['detail_ref'.$_refkey.'_cart_url']="관련상품장바구니담기".$_refkey;
		$_auto_replace[$_file_name]['detail_ref'.$_refkey.'_cart_url']="Y";
		$_code_comment[$_file_name]['detail_ref'.$_refkey.'_cart_url']="$refname 일괄 장바구니 담기 주소 출력";

		$_replace_code[$_file_name]['detail_ref'.$_refkey.'_buy_url']="";
		$_replace_hangul[$_file_name]['detail_ref'.$_refkey.'_buy_url']="관련상품구매하기".$_refkey;
		$_auto_replace[$_file_name]['detail_ref'.$_refkey.'_buy_url']="Y";
		$_code_comment[$_file_name]['detail_ref'.$_refkey.'_buy_url']="$refname 일괄 구매하기 주소 출력";

		$_replace_code[$_file_name]['detail_ref'.$_refkey.'_list']="";
		$_replace_hangul[$_file_name]['detail_ref'.$_refkey.'_list']="관련상품리스트".$_refkey;
		$_code_comment[$_file_name]['detail_ref'.$_refkey.'_list']="해당 상품의 $refname 리스트";
		$_replace_datavals[$_file_name]['detail_ref'.$_refkey.'_list']=$_replace_datavals['common_module']['product_box']."최소주문수량:min_ord:해당 상품의 최대 주문 가능 수량;수량필드명:buy_ea_n:해당 상품 장바구니 담기 기능 사용 시 수량 입력 필드명;체크박스:ck_box:해당 상품 체크박스 필드 사용 시 체크박스 출력;상품옵션선택:option_select:해당 상품 장바구니 담기의 옵션 선택;";
	}

	$_replace_code[$_file_name]['detail_multi_option_list'] = '';
	$_replace_hangul[$_file_name]['detail_multi_option_list'] = '선택된멀티옵션리스트';
	$_code_comment[$_file_name]['detail_multi_option_list'] = '선택 된 옵션 리스트를 출력합니다.';
	$_replace_datavals[$_file_name]['detail_multi_option_list'] = '옵션세트번호:idx:선택된 옵션번호;주문수량:buy_ea:상품 주문수량;옵션명:option_name;옵션명(간략):option_name2;옵션데이터:option;옵션별가격:ea_prc:옵션별 가격 합계;수량더하기:ea_plus:onlcick 스크립트;수량빼기:ea_minus:onlcick 스크립트;옵션삭제:ea_remove:옵션삭제 onclick 스크립트;참조옵션별가격:r_ea_prc:옵션별 참조가격 합계;';

	$_replace_code[$_file_name]['detail_multi_option_prc'] = "<span id='detail_multi_option_prc'>0</span>";
	$_replace_hangul[$_file_name]['detail_multi_option_prc'] = '총멀티옵션금액';
	$_code_comment[$_file_name]['detail_multi_option_prc'] = '멀티 옵션으로 선택한 상품의 총 금액';
	$_replace_datavals[$_file_name]['detail_multi_option_prc'] = 'Y';

	$_replace_code[$_file_name]['detail_is_wish'] = '';
	$_replace_hangul[$_file_name]['detail_is_wish'] = '위시리스트담김';
	$_code_comment[$_file_name]['detail_is_wish'] = '위시리스트에 담긴 상품인 경우 on 문자가 출력됩니다.';
	$_replace_datavals[$_file_name]['detail_is_wish'] = 'Y';

	$_replace_code[$_file_name]['detail_cpn_list']="";
	$_replace_hangul[$_file_name]['detail_cpn_list']="상품쿠폰리스트";
	$_code_comment[$_file_name]['detail_cpn_list']="해당 상품의 쿠폰 리스트";
	$_replace_datavals[$_file_name]['detail_cpn_list']="쿠폰주소링크:link;쿠폰명:name;쿠폰이미지경로:img;쿠폰설명:explain;쿠폰다운후:coupon_ny;쿠폰다운전:coupon_yn;쿠폰최소구매금액:prc_limit;";

	$_replace_code[$_file_name]['detail_cpn_use_list'] = '';
	$_replace_hangul[$_file_name]['detail_cpn_use_list'] = '쿠폰적용대상상품목록';
	$_code_comment[$_file_name]['detail_cpn_use_list'] = '쿠폰 적용 가능한 상품의 목록을 출력합니다.';
	$_replace_datavals[$_file_name]['detail_cpn_use_list'] = '상품이미지:img;상품명:name;옵션명:option_name;주문수량:buy_ea;상품가격:sell_prc;판매금액:total_prc;적용가능쿠폰목록:coupons;';

	$_replace_code[$_file_name]['detail_cpn_cnt'] = '';
	$_replace_hangul[$_file_name]['detail_cpn_cnt']="상품쿠폰개수";
	$_code_comment[$_file_name]['detail_cpn_cnt']="현재 상품에서 다운로드 가능한 쿠폰 개수";
	$_replace_datavals[$_file_name]['detail_cpn_cnt'] = 'Y';

	$_replace_code[$_file_name]['detail_review_list']="";
	$_replace_hangul[$_file_name]['detail_review_list']="상품평리스트";
	$_code_comment[$_file_name]['detail_review_list']="해당 상품의 상품평 리스트";
	$_replace_datavals[$_file_name]['detail_review_list'] = $_user_code_typec['review'];

	require 'shop_detail_review.inc.php'; // 상품 후기 추가 정보 (상품평 리스트 보다 앞에 인클루드 되면 안됩니다.)

	$_replace_code[$_file_name]['detail_qna_list']="";
	$_replace_hangul[$_file_name]['detail_qna_list']="상품질답리스트";
	$_code_comment[$_file_name]['detail_qna_list']="해당 상품의 질문과 답변 리스트";
	$_replace_datavals[$_file_name]['detail_qna_list']="글번호:qna_idx;글고유번호:no;새글아이콘:new_i:최신글일 경우 출력 아이콘;비밀글아이콘:secret_i:비밀글로 등록되었을 경우 출력 아이콘;글답변전아이콘:reply_b_i:답변이 완료되기 전 출력 아이콘;글답변아이콘:reply_i:답변이 완료되었을 경우 출력 아이콘;파일아이콘:file_icon:첨부 이미지 파일이 존재할 경우 아이콘 출력;글제목:title:해당 질문 내용 조회 페이지 링크를 포함한 제목 출력;글제목(링크없음):title_nolink:해당 질문 내용 조회 페이지 링크를 포함하지 않은 제목 출력;글작성자:name;글등록일:reg_date:글 등록일(년/월/일);글내용:content:글 상세 내용 출력;글답변:answer_str:관리자의 답변 내용 출력;글수정링크태그:edit_link:글 수정이 가능할 경우 수정 링크(A 태그) 출력;글삭제링크태그:del_link:글 삭제가 가능할 경우 삭제 링크(A 태그) 출력;조회수:hit;카테고리:cate:카테고리 정보가 있을 경우 출력;링크:link2:해당 게시물의 페이지 주소 출력;";

	$_replace_code[$_file_name]['detail_cy_url']="";
	$_replace_hangul[$_file_name]['detail_cy_url']="싸이오픈스크랩";
	$_auto_replace[$_file_name]['detail_cy_url']="Y";
	$_code_comment[$_file_name]['detail_cy_url']="싸이월드 오픈스크랩 상품링크(A 태그) 출력";

	$_replace_code[$_file_name]['detail_cybtn_url']="";
	$_replace_hangul[$_file_name]['detail_cybtn_url']="싸이오픈스크랩버튼";
	$_auto_replace[$_file_name]['detail_cybtn_url']="Y";
	$_code_comment[$_file_name]['detail_cybtn_url']="싸이월드 오픈스크랩 상품링크(기본버튼포함) 출력";

	$_replace_code[$_file_name]['detail_checkout_url']="";
	$_replace_hangul[$_file_name]['detail_checkout_url']="네이버체크아웃버튼";
	$_auto_replace[$_file_name]['detail_checkout_url']="Y";
	$_code_comment[$_file_name]['detail_checkout_url']="네이버 페이 구매하기, 찜하기 버튼";

	$_replace_code[$_file_name]['detail_talkpay_url'] = '';
	$_replace_hangul[$_file_name]['detail_talkpay_url'] = '카카오페이구매버튼';
	$_auto_replace[$_file_name]['detail_talkpay_url'] = 'Y';
	$_code_comment[$_file_name]['detail_talkpay_url'] = '카카오 페이구매하기, 찜하기 버튼';

	$_replace_code[$_file_name]['detail_payco_url'] = '';
	$_replace_hangul[$_file_name]['detail_payco_url'] = '페이코즉시구매버튼';
	$_auto_replace[$_file_name]['detail_payco_url'] = 'Y';
	$_code_comment[$_file_name]['detail_payco_url'] = '페이코즉시구매버튼';

	$_replace_code[$_file_name]['sns_twitter_url']="";
	$_replace_hangul[$_file_name]['sns_twitter_url']="SNS연동주소(트위터)";
	$_auto_replace[$_file_name]['sns_twitter_url']="Y";
	$_code_comment[$_file_name]['sns_twitter_url']="소셜 네트워크 서비스(SNS) 트위터 연동 주소";

	$_replace_code[$_file_name]['sns_facebook_url']="";
	$_replace_hangul[$_file_name]['sns_facebook_url']="SNS연동주소(페이스북)";
	$_auto_replace[$_file_name]['sns_facebook_url']="Y";
	$_code_comment[$_file_name]['sns_facebook_url']="소셜 네트워크 서비스(SNS) 페이스북 연동 주소";

	$_replace_code[$_file_name]['sns_me_url']="";
	$_replace_hangul[$_file_name]['sns_me_url']="SNS연동주소(미투데이)";
	$_auto_replace[$_file_name]['sns_me_url']="Y";
	$_code_comment[$_file_name]['sns_me_url']="소셜 네트워크 서비스(SNS) 미투데이 연동 주소";

	$_replace_code[$_file_name]['sns_yozm_url']="";
	$_replace_hangul[$_file_name]['sns_yozm_url']="SNS연동주소(요즘)";
	$_auto_replace[$_file_name]['sns_yozm_url']="Y";
	$_code_comment[$_file_name]['sns_yozm_url']="소셜 네트워크 서비스(SNS) 요즘 연동 주소";

	$_replace_code[$_file_name]['product_add_image_list']="";
	$_replace_hangul[$_file_name]['product_add_image_list']="상품부가이미지리스트";
	$_code_comment[$_file_name]['product_add_image_list']="상품부가이미지 리스트 출력";
	$_replace_datavals[$_file_name]['product_add_image_list']="부가이미지주소:add_img:부가이미지주소 출력(최대5개까지)";

	$_replace_code[$_file_name]['fb_like_detail'] = "";
	$_replace_hangul[$_file_name]['fb_like_detail']="페이스북상품좋아요";
	$_code_comment[$_file_name]['fb_like_detail']="페이스북 좋아요 버튼 출력. 현재 상품의 URL이 좋아요 처리됩니다.";

	$_replace_code[$_file_name]['qd_close_url'] = "javascript:parent.removequickDetailPopup()";
	$_replace_hangul[$_file_name]['qd_close_url']="퀵프리뷰닫기";
	$_code_comment[$_file_name]['qd_close_url']="팝업 퀵프리뷰를 닫을때 사용하는 링크입니다.";

	$_replace_code[$_file_name]['naver_milage_api_id']=$cfg['milage_api_id'];
	$_replace_hangul[$_file_name]['naver_milage_api_id']="네이버마일리지API아이디";

	$_replace_code[$_file_name]['kakao_url']	= '';
	$_replace_hangul[$_file_name]['kakao_url']	= "카카오링크";
	$_code_comment[$_file_name]['kakao_url']	= '카카오링크 공유하기 링크(A 태그) 출력';

	$_replace_code[$_file_name]['kakaostory_url']	= '';
	$_replace_hangul[$_file_name]['kakaostory_url']	= "카카오스토리";
	$_code_comment[$_file_name]['kakaostory_url']	= '카카오스토리 공유하기 링크(A 태그) 출력';

	$_replace_code[$_file_name]['kakao_url_use']	= '';
	$_replace_hangul[$_file_name]['kakao_url_use']	= "카카오링크사용여부";
	$_code_comment[$_file_name]['kakao_url_use']	= '카카오링크 공유하기 링크 사용여부';

	$_replace_code[$_file_name]['kakaostory_url_use']	= '';
	$_replace_hangul[$_file_name]['kakaostory_url_use']	= "카카오스토리사용여부";
	$_code_comment[$_file_name]['kakaostory_url_use']	= '카카오스토리 공유하기 링크 사용여부';

	if($cfg['xbig_mng'] == 'Y') {
		$_replace_code[$_file_name]['xbig']="";
		$_replace_hangul[$_file_name]['xbig']="이분류코드대";
		$_auto_replace[$_file_name]['xbig']="Y";
		$_code_comment[$_file_name]['xbig']="선택된 상품의 ".$cfg['xbig_name'].' 대분류 코드';

		$_replace_code[$_file_name]['xmid']="";
		$_replace_hangul[$_file_name]['xmid']="이분류코드중";
		$_auto_replace[$_file_name]['xmid']="Y";
		$_code_comment[$_file_name]['xmid']="선택된 상품의 ".$cfg['xbig_name'].' 중분류 코드';

		$_replace_code[$_file_name]['xsmall']="";
		$_replace_hangul[$_file_name]['xsmall']="이분류코드소";
		$_auto_replace[$_file_name]['xsmall']="Y";
		$_code_comment[$_file_name]['xsmall']="선택된 상품의 ".$cfg['xbig_name'].' 소분류 코드';

		$_replace_code[$_file_name]['xbig_name']="";
		$_replace_hangul[$_file_name]['xbig_name']="이분류명대";
		$_auto_replace[$_file_name]['xbig_name']="Y";
		$_code_comment[$_file_name]['xbig_name']="선택된 상품의 ".$cfg['xbig_name'].' 대분류명';

		$_replace_code[$_file_name]['xmid_name']="";
		$_replace_hangul[$_file_name]['xmid_name']="이분류명중";
		$_auto_replace[$_file_name]['xmid_name']="Y";
		$_code_comment[$_file_name]['xmid_name']="선택된 상품의 ".$cfg['xbig_name'].' 중분류명';

		$_replace_code[$_file_name]['xsmall_name']="";
		$_replace_hangul[$_file_name]['xsmall_name']="이분류명소";
		$_auto_replace[$_file_name]['xsmall_name']="Y";
		$_code_comment[$_file_name]['xsmall_name']="선택된 상품의 ".$cfg['xbig_name'].' 소분류명';
	}

	if($cfg['ybig_mng'] == 'Y') {
		$_replace_code[$_file_name]['ybig']="";
		$_replace_hangul[$_file_name]['ybig']="삼분류코드대";
		$_auto_replace[$_file_name]['ybig']="Y";
		$_code_comment[$_file_name]['ybig']="선택된 상품의 ".$cfg['ybig_name'].' 대분류 코드';

		$_replace_code[$_file_name]['ymid']="";
		$_replace_hangul[$_file_name]['ymid']="삼분류코드중";
		$_auto_replace[$_file_name]['ymid']="Y";
		$_code_comment[$_file_name]['ymid']="선택된 상품의 ".$cfg['ybig_name'].' 중분류 코드';

		$_replace_code[$_file_name]['ysmall']="";
		$_replace_hangul[$_file_name]['ysmall']="삼분류코드소";
		$_auto_replace[$_file_name]['ysmall']="Y";
		$_code_comment[$_file_name]['ysmall']="선택된 상품의 ".$cfg['ybig_name'].' 소분류 코드';

		$_replace_code[$_file_name]['ybig_name']="";
		$_replace_hangul[$_file_name]['ybig_name']="삼분류명대";
		$_auto_replace[$_file_name]['ybig_name']="Y";
		$_code_comment[$_file_name]['ybig_name']="선택된 상품의 ".$cfg['ybig_name'].' 대분류명';

		$_replace_code[$_file_name]['ymid_name']="";
		$_replace_hangul[$_file_name]['ymid_name']="삼분류명중";
		$_auto_replace[$_file_name]['ymid_name']="Y";
		$_code_comment[$_file_name]['ymid_name']="선택된 상품의 ".$cfg['ybig_name'].' 중분류명';

		$_replace_code[$_file_name]['ysmall_name']="";
		$_replace_hangul[$_file_name]['ysmall_name']="삼분류명소";
		$_auto_replace[$_file_name]['ysmall_name']="Y";
		$_code_comment[$_file_name]['ysmall_name']="선택된 상품의 ".$cfg['ybig_name'].' 소분류명';
	}

	$_replace_code[$_file_name]['recopick']="";
	$_replace_hangul[$_file_name]['recopick']="레코픽추천상품";
	$_auto_replace[$_file_name]['recopick']="Y";
	$_code_comment[$_file_name]['recopick']="레코픽추천상품";

	$_replace_code[$_file_name]['m_recopick']="";
	$_replace_hangul[$_file_name]['m_recopick']="모바일레코픽추천상품";
	$_auto_replace[$_file_name]['m_recopick']="Y";
	$_code_comment[$_file_name]['m_recopick']="모바일레코픽추천상품";

	$_replace_code[$_file_name]['detail_product_no']="";
	$_replace_hangul[$_file_name]['detail_product_no']="디테일상품번호";
	$_auto_replace[$_file_name]['detail_product_no']="Y";
	$_code_comment[$_file_name]['detail_product_no']="디테일 페이지 상품 번호";

	$_replace_code[$_file_name]['detail_parent_no']=$prd['parent'];
	$_replace_hangul[$_file_name]['detail_parent_no']="원본상품번호";
	$_auto_replace[$_file_name]['detail_parent_no']="Y";
	$_code_comment[$_file_name]['detail_parent_no']="상품번호 또는 바로가기일 경우 원본 상품번호.";

	if($cfg['use_prc_consult'] == 'Y') {
		$_replace_code[$_file_name]['detail_sell_prc_consultation']="";
		$_replace_hangul[$_file_name]['detail_sell_prc_consultation']="판매가대체문구";
		$_auto_replace[$_file_name]['detail_sell_prc_consultation']="Y";
		$_code_comment[$_file_name]['detail_sell_prc_consultation']="판매가대체문구";
	}

	for($i = 1; $i <= 3; $i++) {
		$_replace_code[$_file_name]['detail_etc'.$i] = stripslashes($prd['etc'.$i]);
		$_replace_hangul[$_file_name]['detail_etc'.$i] = '기타입력사항'.$i;
		$_auto_replace[$_file_name]['detail_etc'.$i]="Y";
		$_code_comment[$_file_name]['detail_etc'.$i] = $cfg['prd_etc'.$i];
	}

	// 참조화폐 코드 추가
	$_replace_code[$_file_name]['detail_r_sell_prc']="";
	$_replace_hangul[$_file_name]['detail_r_sell_prc']="참조판매가격";
	$_auto_replace[$_file_name]['detail_r_sell_prc']="Y";
	$_code_comment[$_file_name]['detail_r_sell_prc']="해당 상품의 판매 참조가격";

	$_replace_code[$_file_name]['detail_r_nml_prc']="";
	$_replace_hangul[$_file_name]['detail_r_nml_prc']="참조소비자가격";
	$_auto_replace[$_file_name]['detail_r_nml_prc']="Y";
	$_code_comment[$_file_name]['detail_r_nml_prc']="해당 상품의 참조소비자가격";

	$_replace_code[$_file_name]['detail_r_msale_prc2']="";
	$_replace_hangul[$_file_name]['detail_r_msale_prc2']="참조회원할인가격";
	$_auto_replace[$_file_name]['detail_r_msale_prc2']="Y";
	$_code_comment[$_file_name]['detail_r_msale_prc2']="회원할인이 있을 경우 참조 회원할인가격. 없을 경우 참조판매가격";

	$_replace_code[$_file_name]['detail_r_pay_prc']="";
	$_replace_hangul[$_file_name]['detail_r_pay_prc']="참조할인후실판매가";
	$_auto_replace[$_file_name]['detail_r_pay_prc']="Y";
	$_code_comment[$_file_name]['detail_r_pay_prc']="이벤트할인, 회원할인, 타임세일이 적용된 참조 상품 판매가";

	$_replace_code[$_file_name]['detail_r_prd_milage']="";
	$_replace_hangul[$_file_name]['detail_r_prd_milage']="참조적립금";
	$_auto_replace[$_file_name]['detail_r_prd_milage']="Y";
	$_code_comment[$_file_name]['detail_r_prd_milage']="해당 상품의 참조적립금";

	$_replace_code[$_file_name]['detail_r_delivery_prc'] = number_format(showExchangeFee($prd['delivery_prc']));
	$_replace_hangul[$_file_name]['detail_r_delivery_prc']="참조상품별배송비";
	$_auto_replace[$_file_name]['detail_r_delivery_prc']="Y";
	$_code_comment[$_file_name]['detail_r_delivery_prc']="상품 낱개 참조배송비";

	$_replace_code[$_file_name]['detail_r_timesale_prc']="";
	$_replace_hangul[$_file_name]['detail_r_timesale_prc']="참조타임세일금액";
	$_auto_replace[$_file_name]['detail_r_timesale_prc']="Y";
	$_code_comment[$_file_name]['detail_r_timesale_prc']="타임세일 적용시 할인 된 참조금액";

	$_replace_code[$_file_name]['detail_r_msale_prc']="";
	$_replace_hangul[$_file_name]['detail_r_msale_prc']="참조회원할인금액";
	$_auto_replace[$_file_name]['detail_r_msale_prc']="Y";
	$_code_comment[$_file_name]['detail_r_msale_prc']="회원할인 적용시 할인 된 참조금액";

	$_replace_code[$_file_name]['detail_multi_r_option_prc'] = '0';
	$_replace_hangul[$_file_name]['detail_multi_r_option_prc'] = '참조총멀티옵션금액';
	$_code_comment[$_file_name]['detail_multi_r_option_prc'] = '멀티 옵션으로 선택한 상품의 총 참조금액';
	$_auto_replace[$_file_name]['detail_multi_r_option_prc'] = 'Y';

	$_replace_code[$_file_name]['purchaser_explanation_yn'] = '';
	$_replace_hangul[$_file_name]['purchaser_explanation_yn'] = '구매자설명기입란여부';
	$_code_comment[$_file_name]['purchaser_explanation_yn'] = '구매자가 상품구매시 설명기입에 추가로 작성가능';
	$_auto_replace[$_file_name]['purchaser_explanation_yn'] = 'Y';

	$_replace_code[$_file_name]['detail_today_dlv'] = '';
	$_replace_hangul[$_file_name]['detail_today_dlv'] = '오늘출발';
	$_code_comment[$_file_name]['detail_today_dlv'] = '네이버쇼핑 오늘출발 기준 표기';
	$_auto_replace[$_file_name]['detail_today_dlv'] = 'Y';

	$_replace_code[$_file_name]['detail_today_time'] = '';
	$_replace_hangul[$_file_name]['detail_today_time'] = '오늘출발주문마감시간';
	$_code_comment[$_file_name]['detail_today_time'] = '네이버쇼핑 오늘출발주문마감시간';
	$_auto_replace[$_file_name]['detail_today_time'] = 'Y';

	$_replace_code[$_file_name]['detail_sbscr_yn'] = '';
	$_replace_hangul[$_file_name]['detail_sbscr_yn'] = '정기배송적용여부_상품별';
	$_code_comment[$_file_name]['detail_sbscr_yn'] = '정기배송 주문이 가능한 상품일 경우 Y가 출력됩니다.';
	$_auto_replace[$_file_name]['detail_sbscr_yn'] = 'Y';

	$_replace_code[$_file_name]['detail_delivery_type'] = '';
	$_replace_hangul[$_file_name]['detail_delivery_type'] = '배송타입';
	$_code_comment[$_file_name]['detail_delivery_type'] = '상품의 기본배송/개별배송 여부를 출력합니다.';
	$_auto_replace[$_file_name]['detail_delivery_type'] = 'Y';

	$_replace_code[$_file_name]['detail_delivery_type_list'] = '';
	$_replace_hangul[$_file_name]['detail_delivery_type_list'] = '개별배송상세리스트';
	$_code_comment[$_file_name]['detail_delivery_type_list'] = '개별배송 정책을 출력합니다.';
	$_replace_datavals[$_file_name]['detail_delivery_type_list'] = '기준범위시작:condition_s;기준범위끝:condition_e;적용배송비:price;반복단위:unit:'.$cfg['currency_type'].' 또는 개 표시;';

	$_replace_code[$_file_name]['detail_delivery_loop_list'] = '';
	$_replace_hangul[$_file_name]['detail_delivery_loop_list'] = '개별배송상세리스트(범위반복)';
	$_code_comment[$_file_name]['detail_delivery_loop_list'] = '범위 반복형 개별배송 정책을 출력합니다.';
	$_replace_datavals[$_file_name]['detail_delivery_loop_list'] = '기준범위끝:condition_e;적용배송비:price;반복단위:unit:'.$cfg['currency_type'].' 또는 개 표시;';

	/* 아래 모듈은 shop_detail 의 가장 아래에 배치해야 합니다. */
	$_replace_code[$_file_name]['detail_addon_normal'] = '';
	$_replace_hangul[$_file_name]['detail_addon_normal'] = '일반상품항목';
	$_code_comment[$_file_name]['detail_addon_normal'] = '일반상품 상세페이지에만 출력되는 항목을 출력합니다.';
	$_auto_replace[$_file_name]['detail_addon_normal'] = 'Y';

	$_replace_code[$_file_name]['detail_addon_set_list'] = '';
	$_replace_hangul[$_file_name]['detail_addon_set_list'] = '세트상품리스트';
	$_code_comment[$_file_name]['detail_addon_set_list'] = '세트상품 선택목록을 출력합니다.';
	$_replace_datavals[$_file_name]['detail_addon_set_list'] ="상품번호:parent;상품명:name;상품링크:link;상품이미지:img;상품판매가:sell_prc;상품옵션:option;품절여부:soldout;퀵프리뷰링크:preview_link;";

	$_replace_code[$_file_name]['detail_addon_choice_list'] = '';
	$_replace_hangul[$_file_name]['detail_addon_choice_list'] = '골라담기리스트';
	$_code_comment[$_file_name]['detail_addon_choice_list'] = '골라담기 세트상품 선택목록을 출력합니다.';
	$_replace_datavals[$_file_name]['detail_addon_choice_list'] ="상품번호:parent;상품명:name;상품링크:link;상품이미지:img;상품판매가:sell_prc;상품옵션:option;선택하기:btn_script;품절여부:soldout;퀵프리뷰링크:preview_link;";

    $_replace_code[$_file_name]['prd_link_url'] = '';
    $_replace_hangul[$_file_name]['prd_link_url'] = '상품링크';
    $_auto_replace[$_file_name]['prd_link_url']  = 'Y';
    $_code_comment[$_file_name]['prd_link_url'] = '상품링크 주소 출력';
?>