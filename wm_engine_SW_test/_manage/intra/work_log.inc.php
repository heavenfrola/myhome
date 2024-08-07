<?php

/**
 * 데이터 수정 내역 변수 목록
 **/

// 페이지
$_page = array(
    $tbl['product'] => '상품',
    $tbl['mari_board'] => '게시판',
    $tbl['cs'] => '1대1 상담',
    $tbl['qna'] => '상품문의',
    /*$tbl['coupon_download'] => '쿠폰발급내역',*/
);

// 데이터 필드 사전
$_dic = array(
    $tbl['product'] => array(
        'name' => '상품명',
        'name_referer' => '참조상품명',
        'code' => '상품코드',
        'sell_prc' => '판매가',
        'm_sell_prc' => '모바일판매가',
        'normal_prc' => '소비자가',
        'm_normal_prc' => '모바일소비자가',
        'origin_prc' => '사입원가',
        'origin_name' => '상기명',
        'seller' => '사입처',
        'seller_idx' => '사입처 코드',
        'stat' => '상품상태',
        'big' => '대분류',
        'mid' => '중분류',
        'small' => '소분류',
        'depth4' => '세분류',
        'ebig' => '기획전',
        'mbig' => '모바일 기획전',
        'keyword' => '검색키워드',
        'dlv_type' => ' 배송비타입',
        'delivery_set' => '개별배송비 세트',
        'content1' => '요약설명',
        'content2' => '상세설명',
        'set_rate' => '세트할인율',
        'tax_free' => '비과세',
        'perm_lst' => '목록 노출',
        'perm_dtl' => '상세 노출',
        'perm_sch' => '검색 노출',
        'ts_use' => '타임세일 사용',
        'ts_set' => '타임세일 세트',
        'ts_dates' => '타임세일 시작일',
        'ts_datee' => '타임세일 종료일',
        'ts_saletype' => '타임세일 할인방법',
        'ts_saleprc' => '타임세일 할인금액(율)',
        'ts_cut' => '타임세일 절사단위',
        'event_sale' => '할인/적립 이벤트',
        'member_sale' => '회원혜택설정',
        'free_delivery' => '무료배송',
        'dlv_alone' => '단독배송',
        'no_cpn' => '쿠폰 사용불가',
        'no_milage' => '적립금 사용불가',
        'checkout' => '네이버페이',
        'compare_today_start' => '오늘출발설정',
        'icons' => '아이콘',
        'del_stat' => '삭제전 상태',
        'del_date' => '휴지통 처리일',
        'weight' => '무게',
        'min_ord' => '최소구매수량',
        'max_ord' => '최대구매수량',
    ),
    $tbl['mari_board'] => array(
        'title' => '제목',
        'content' => '본문',
        'm_content' => '모바일 본문',
        'use_m_content' => '모바일 본문 사용여부',
        'start_date' => '시작일',
        'end_date' => '종료일',
        'upfile1' => '첨부파일 1',
        'upfile2' => '첨부파일 2',
        'upfile3' => '첨부파일 3',
        'upfile4' => '첨부파일 4',
        'upfile5' => '첨부파일 5',
        'notice' => '공지사항',
        'secret' => '비밀글',
        'hidden' => '숨김',
        'temp1' => '추가항목 1',
        'temp2' => '추가항목 2',
        'temp3' => '추가항목 3',
        'temp4' => '추가항목 4',
        'temp5' => '추가항목 5',
        'cate' => '분류',
    ),
    $tbl['cs'] => array(
        'reply' => '답변',
        'reply_date' => '답변일시',
        'reply_ok' => '답변여부',
        'reply_id' => '답변 관리자',
    ),
    $tbl['qna'] => array(
        'content' => '질문내용',
        'answer' => '답변',
        'answer_date' => '답변일시',
        'answer_ok' => '답변여부',
        'answer_id' => '답변 관리자',
    ),
    $tbl['coupon_download'] => array(
        'use_date' => '사용일자',
        'ono' => '대상 주문번호',
    )
);