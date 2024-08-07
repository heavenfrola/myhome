<?PHP

	$_replace_code[$_file_name]['review_notice_list']="";
	$_replace_hangul[$_file_name]['review_notice_list']="상품평공지리스트";
	$_code_comment[$_file_name]['review_notice_list']="상품평 공지 리스트";
	$_replace_datavals[$_file_name]['review_notice_list']="글고유번호:no;글제목:title:공지 조회 페이지 링크를 포함한 제목 출력;글제목(링크없음):title_nolink:공지 조회 페이지 링크를 포함하지 않은 제목 출력;등록일:reg_date:상품평 등록일(년/월/일);등록일2:reg_date2:상품평 등록일(월/일);파일첨부아이콘:file_icon:첨부 이미지 파일이 존재할 경우 아이콘 출력;상품평카테고리:cate:카테고리 정보가 있을 경우 출력;상품평첨부파일1:img1:첫번째 첨부 이미지 출력;상품평첨부파일2:img2:두번째 첨부 이미지 출력;글내용:content:상품평 상세 내용 출력;링크:link3:공지사항보기 링크;";

	$_replace_code[$_file_name]['review_total_list']="";
	$_replace_hangul[$_file_name]['review_total_list']="상품평리스트";
	$_code_comment[$_file_name]['review_total_list']="상품평 리스트";
	$_replace_datavals[$_file_name]['review_total_list']="글번호:rev_idx:내림차순 글번호;상품명:prd_name;글제목:title:상품평 조회 페이지 링크를 포함한 제목 출력;글제목(링크없음):title_nolink:상품평 조회 페이지 링크를 포함하지 않은 제목 출력;상품이미지:prd_img:상품정보가 존재할 경우 해당 상품의 소 이미지 출력;총댓글수:total_comment_str;파일첨부아이콘:file_icon:첨부 이미지 파일이 존재할 경우 아이콘 출력;새글아이콘:new_i:최신글일 경우 출력 아이콘;작성자:name;등록일:reg_date:상품평 등록일(년/월/일);등록일2:reg_date2:상품평 등록일(월/일);상품평카테고리:cate:카테고리 정보가 있을 경우 출력;상품평점수(텍스트):rev_pt:상품의 평가를 숫자로 출력;상품평점수:star:상품의 평가를 별(최고 5개) 이미지로 출력;글내용:content:상품평 상세 내용 출력;상품평내용(태그없음):content_plain;상품평요약내용:content_short;첨부파일존재유무:file_exist:첨부 파일이 존재 유무에 따라 yes 또는 no 문자 출력;상품평조회수:hit;상품평베스트:best:베스트 상품평으로 설정된 경우 best 문자 출력;상품평인기:hot:인기 상품평으로 설정된 경우 hot 문자 출력;글번호2:rev_idx2:오름차순 글번호;링크:link2:해당 게시물의 페이지 주소 출력;상품평보기링크:title_layer_link:해당 게시물을 레이어로 볼 수 있는 페이지 주소 출력;상품평첨부파일1:img1;상품평첨부파일2:img2;이미지개수:img_cnt:첨부파일 및 본문에 사용된 이미지 개수;상품평추천수:recommend_y;상품평비추천수:recommend_n;포토리뷰:has_photo:첨부이미지가 있을 경우 Y가 출력";

    $_replace_code[$_file_name]['review_nextpage_link'] = '';
    $_replace_hangul[$_file_name]['review_nextpage_link'] = '다음페이지링크';
    $_code_comment[$_file_name]['review_nextpage_link'] = '다음 페이지가 있을 경우 링크를 출력';
    $_auto_replace[$_file_name]['review_nextpage_link'] = 'Y';

	$_replace_code[$_file_name]['review_search_str'] = inputText($_GET['rsearch_str']);
	$_replace_hangul[$_file_name]['review_search_str'] = '검색어';
	$_auto_replace[$_file_name]['review_search_str'] = 'Y';
	$_code_comment[$_file_name]['review_search_str'] = '상품후기 검색어';

	$_replace_code[$_file_name]['review_search_column1'] = ($_GET['search_column'] == 1) ? 'selected' : '';
	$_replace_hangul[$_file_name]['review_search_column1'] = '작성자검색상태';
	$_auto_replace[$_file_name]['review_search_column1'] = 'Y';
	$_code_comment[$_file_name]['review_search_column1'] = '작성자 검색상태일 경우 selected 문자열 출력';

	$_replace_code[$_file_name]['review_search_column2'] = ($_GET['search_column'] == 2) ? 'selected' : '';
	$_replace_hangul[$_file_name]['review_search_column2'] = '아이디검색상태';
	$_auto_replace[$_file_name]['review_search_column2'] = 'Y';
	$_code_comment[$_file_name]['review_search_column2'] = '작성자 검색상태일 경우 selected 문자열 출력';

	$_replace_code[$_file_name]['review_search_column3'] = ($_GET['search_column'] == 3) ? 'selected' : '';
	$_replace_hangul[$_file_name]['review_search_column3'] = '제목검색상태';
	$_auto_replace[$_file_name]['review_search_column3'] = 'Y';
	$_code_comment[$_file_name]['review_search_column3'] = '작성자 검색상태일 경우 selected 문자열 출력';

	$_replace_code[$_file_name]['review_search_column4'] = ($_GET['search_column'] == 4) ? 'selected' : '';
	$_replace_hangul[$_file_name]['review_search_column4'] = '내용검색상태';
	$_auto_replace[$_file_name]['review_search_column4'] = 'Y';
	$_code_comment[$_file_name]['review_search_column4'] = '내용 검색상태일 경우 selected 문자열 출력';

	$_replace_code[$_file_name]['review_type'] = ($_GET['type']) ? $_GET['type'] : '1';
	$_replace_hangul[$_file_name]['review_type'] = '후기조회타입';
	$_auto_replace[$_file_name]['review_type'] = 'Y';
	$_code_comment[$_file_name]['review_type'] = '전체 후기 1, 포토 후기 2, 일반 후기일 경우 3이 출력됩니다.';

	$_replace_code[$_file_name]['review_type_is_1'] = ($_GET['type'] == '1' || empty($_GET['type']) == true) ? 'on' : '';
	$_replace_hangul[$_file_name]['review_type_is_1'] = '전체후기';
	$_auto_replace[$_file_name]['review_type_is_1'] = 'Y';
	$_code_comment[$_file_name]['review_type_is_1'] = '전체 후기 조회 상태일때 on이 출력됩니다.';

	$_replace_code[$_file_name]['review_type_is_2'] = ($_GET['type'] == '2') ? 'on' : '';
	$_replace_hangul[$_file_name]['review_type_is_2'] = '포토후기';
	$_auto_replace[$_file_name]['review_type_is_2'] = 'Y';
	$_code_comment[$_file_name]['review_type_is_2'] = '포토 후기 조회 상태일때 on이 출력됩니다.';

	$_replace_code[$_file_name]['review_type_is_3'] = ($_GET['type'] == '3') ? 'on' : '';
	$_replace_hangul[$_file_name]['review_type_is_3'] = '일반후기';
	$_auto_replace[$_file_name]['review_type_is_3'] = 'Y';
	$_code_comment[$_file_name]['review_type_is_3'] = '일반 후기 상태일때 on이 출력됩니다.';

	$_replace_code[$_file_name]['review_mbr_addr_list'] = '';
	$_replace_hangul[$_file_name]['review_mbr_addr_list'] = '상품평회원추가항목검색리스트';
	$_code_comment[$_file_name]['review_mbr_addr_list'] = '가입시 입력받은 회원 추가항목에 해당하는 작성자의 후기를 검색합니다.';
	$_replace_datavals[$_file_name]['review_mbr_addr_list'] = '항목명:name;선택사항:checkbox;';

	$_replace_code[$_file_name]['review_writable'] = ($cfg['product_review_auth'] == 1 || ($cfg['product_review_auth'] == 2 && $member['no'] > 0)) ? 'Y' : '';
	$_replace_hangul[$_file_name]['review_writable'] = '상품후기작성가능여부';
	$_code_comment[$_file_name]['review_writable'] = '상품 후기 목록페이지에서 후기 작성이 가능할경우 Y 를 출력합니다.';
	$_auto_replace[$_file_name]['review_writable'] = 'Y';

	require 'shop_detail_review.inc.php'; // 공통 후기 데이터

?>