<?PHP

	$_replace_code[$_file_name]['mypage_order_list']="";
	$_replace_hangul[$_file_name]['mypage_order_list']="주문리스트";
	$_code_comment[$_file_name]['mypage_order_list']="주문한 내역 리스트";
	$_replace_datavals[$_file_name]['mypage_order_list']="내역번호:oidx;주문번호:ono:주문 상세 내용 조회 페이지의 링크를 포함한 주문번호 출력;주문번호(링크없음):ono2;주문일:date1:주문 날짜(년/월/일);상품명:title:주문한 상품명;결제금액:total_prc;실결제금액:pay_prc;주문상태:stat:주문의 현재 처리 상태 출력(미입금, 입금완료, 배송중, 배송완료 등);수취확인:receive:수취확인이 가능할 경우 수취확인 링크(A 태그) 출력;주문자명:buyer_name;수취인명:addressee_name;총구매수량:total_buy_ea;참조결제금액:total_r_prc;참조실결제금액:pay_r_prc;배송조회링크:delivery_link;";

	$_replace_code[$_file_name]['mypage_search_date_list']="";
	$_replace_hangul[$_file_name]['mypage_search_date_list']="주문목록날짜검색";
	$_code_comment[$_file_name]['mypage_search_date_list']="주문한 내역에 대한 날짜 검색";
	$_replace_datavals[$_file_name]['mypage_search_date_list']="선택체크:selected_on:선택된 기간 활성화;기준날짜:btn_key:오늘/1개월/3개월/6개월/1년/전체 기간 제공;자동클릭이벤트:auto_click_event:클릭 시 날짜값 삽입 및 자동으로 검색할 수 있는 이벤트;클릭이벤트:click_event:클릭 시 날짜값만 삽입되는 이벤트;";
	
	$_replace_code[$_file_name]['mypage_start_date']="";
	$_replace_hangul[$_file_name]['mypage_start_date']="주문검색시작날짜";
	$_code_comment[$_file_name]['mypage_start_date']="날짜 검색 시 시작날짜";
	$_auto_replace[$_file_name]['mypage_start_date']="Y";

	$_replace_code[$_file_name]['mypage_finish_date']="";
	$_replace_hangul[$_file_name]['mypage_finish_date']="주문검색마지막날짜";
	$_code_comment[$_file_name]['mypage_finish_date']="날짜 검색 시 마지막날짜";
	$_auto_replace[$_file_name]['mypage_finish_date']="Y";

?>