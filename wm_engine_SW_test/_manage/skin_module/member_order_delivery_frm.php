<?php
    $_replace_code[$_file_name]['form_start'] = '';
    $_replace_hangul[$_file_name]['form_start'] = '폼시작';
    $_auto_replace[$_file_name]['form_start'] = 'Y';
    $_code_comment[$_file_name]['form_start'] = '배송지변경 폼 시작 선언';

    $_replace_code[$_file_name]['form_end'] = '';
    $_replace_hangul[$_file_name]['form_end'] = '폼끝';
    $_auto_replace[$_file_name]['form_end'] = 'Y';
    $_code_comment[$_file_name]['form_end'] = '배송지변경 폼 끝 선언';

    $_replace_code[$_file_name]['address_type_view'] = '';
    $_replace_hangul[$_file_name]['address_type_view'] = '활성된배송지타입';
    $_auto_replace[$_file_name]['address_type_view'] = 'Y';
    $_code_comment[$_file_name]['address_type_view'] = '활성된배송지타입';

    $_replace_code[$_file_name]['member_address_list'] = '';
    $_replace_hangul[$_file_name]['member_address_list'] = '나의주소록리스트';
    $_code_comment[$_file_name]['member_address_list'] = '나의주소록리스트';
    $_replace_datavals[$_file_name]['member_address_list'] = '배송지명:title;배송지받는사람:name;배송지우편번호:zip;배송지주소:addr1;배송지상세주소:addr2;배송지상세주소3:addr3;배송지상세주소4:addr4;배송국가:nations;배송지전화번호:phone;배송지모바일번호:cell;기본배송지여부:is_default;일반배송지여부:is_default_no;배송지고유번호:no;체크여부:checked';

    $_replace_code[$_file_name]['order_lately_addr_list'] = '';
    $_replace_hangul[$_file_name]['order_lately_addr_list'] = '최근배송지정보리스트';
    $_code_comment[$_file_name]['order_lately_addr_list'] = '최근 배송지 정보';
    $_replace_datavals[$_file_name]['order_lately_addr_list'] = '배송지명:addressee_name;받는사람:addressee_name;우편번호:addressee_zip;주소:addressee_addr1;상세주소:addressee_addr2;상세주소3:addressee_addr3;상세주소4:addressee_addr4;전화번호:addressee_phone;모바일번호:addressee_cell;배송지순서:cnt;';

    $_replace_code[$_file_name]['order_new_address'] = '';
    $_replace_hangul[$_file_name]['order_new_address'] = '배송지관리신규입력(국내)';
    $_code_comment[$_file_name]['order_new_address'] = '주소및전화입력박스';
    $_replace_datavals[$_file_name]['order_new_address'] = '배송지고유번호:no;배송지명:title;받는사람:name;전화번호:phone;휴대폰번호:cell;우편번호:zip;주소1:addr1;주소2:addr2;주소3:addr3;주소4:addr4;주소록수정:addressupdate;주소록추가:addressadd;';

    $_replace_code[$_file_name]['order_new_address_oversea'] = '';
    $_replace_hangul[$_file_name]['order_new_address_oversea'] = '배송지관리신규입력(해외)';
    $_code_comment[$_file_name]['order_new_address_oversea'] = '주소및전화입력박스';
    $_replace_datavals[$_file_name]['order_new_address_oversea'] = '배송지고유번호:no;배송지명:title;받는사람:name;전화번호:phone;휴대폰번호:cell;우편번호:zip;주소1:addr1;주소2:addr2;주소3:addr3;주소4:addr4;주소록수정:addressupdate;주소록추가:addressadd;';

    $_replace_code[$_file_name]['delivery_nations'] = '';
    $_replace_hangul[$_file_name]['delivery_nations'] = '배송국가선택';
    $_auto_replace[$_file_name]['delivery_nations'] = 'Y';

    $_replace_code[$_file_name]['order_oversea_phone'] = '';
    $_replace_hangul[$_file_name]['order_oversea_phone'] = '전화국가번호';
    $_auto_replace[$_file_name]['order_oversea_phone'] = 'Y';

    $_replace_code[$_file_name]['order_oversea_cell'] = '';
    $_replace_hangul[$_file_name]['order_oversea_cell'] = '휴대국가번호';
    $_auto_replace[$_file_name]['order_oversea_cell'] = 'Y';

    $_replace_code[$_file_name]['delivery_com_list'] = '';
    $_replace_hangul[$_file_name]['delivery_com_list'] = '배송업체목록';
    $_auto_replace[$_file_name]['delivery_com_list'] = 'Y';

    $_replace_code[$_file_name]['delivery_com_display'] = '';
    $_replace_hangul[$_file_name]['delivery_com_display'] = '단일배송업체숨김처리';
    $_auto_replace[$_file_name]['delivery_com_display'] = 'Y';
