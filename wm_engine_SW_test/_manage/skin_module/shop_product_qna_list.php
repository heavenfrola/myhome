<?PHP

	$_replace_code[$_file_name]['qna_notice_list']="";
	$_replace_hangul[$_file_name]['qna_notice_list']="질답공지리스트";
	$_code_comment[$_file_name]['qna_notice_list']="질문과 답변 공지 리스트";
	$_replace_datavals[$_file_name]['qna_notice_list']="글번호:qna_idx;글제목:title:해당 공지 내용 조회 페이지 링크를 포함한 제목 출력;글제목(링크없음):title_nolink:해당 공지 내용 조회 페이지 링크를 포함하지 않은 제목 출력;새글아이콘:new_i:최신글일 경우 출력 아이콘;파일아이콘:file_icon:첨부 이미지 파일이 존재할 경우 아이콘 출력;등록일:reg_date:글 등록일(년/월/일);등록일2:reg_date2:글 등록일(월/일);카테고리:cate:카테고리 정보가 있을 경우 출력;글고유번호:no;QNA첨부파일1:img1:첫번째 첨부 이미지 출력;QNA첨부파일2:img2:두번째 첨부 이미지 출력;글내용:content:글 상세 내용 출력;링크:link2:해당 공지 게시물의 글보기 링크 출력";

	$_replace_code[$_file_name]['qna_total_list']="";
	$_replace_hangul[$_file_name]['qna_total_list']="질답리스트";
	$_code_comment[$_file_name]['qna_total_list']="질문과 답변 리스트";
	$_replace_datavals[$_file_name]['qna_total_list']="글번호:qna_idx;상품명:prd_name;새글아이콘:new_i:최신글일 경우 출력 아이콘;비밀글아이콘:secret_i:비밀글로 등록되었을 경우 출력 아이콘;답변전아이콘:reply_b_i:답변이 완료되기 전 출력 아이콘;답변아이콘:reply_i:답변이 완료되었을 경우 출력 아이콘;파일아이콘:file_icon:첨부 이미지 파일이 존재할 경우 아이콘 출력;글제목:title:해당 질문 내용 조회 페이지 링크를 포함한 제목 출력;글제목(링크없음):title_nolink:해당 질문 내용 조회 페이지 링크를 포함하지 않은 제목 출력;상품이미지:prd_img:상품정보가 존재할 경우 해당 상품의 소 이미지 출력;작성자:name;등록일:reg_date:글 등록일(년/월/일);등록일2:reg_date2:글 등록일(월/일);조회수:hit;카테고리:cate:카테고리 정보가 있을 경우 출력;링크:link2:해당 게시물의 페이지 주소 출력;";

    $_replace_code[$_file_name]['qna_nextpage_link'] = '';
    $_replace_hangul[$_file_name]['qna_nextpage_link'] = '다음페이지링크';
    $_code_comment[$_file_name]['qna_nextpage_link'] = '다음 페이지가 있을 경우 링크를 출력';
    $_auto_replace[$_file_name]['qna_nextpage_link'] = 'Y';

	$_replace_code[$_file_name]['qna_search_str'] = inputText($_GET['rsearch_str']);
	$_replace_hangul[$_file_name]['qna_search_str'] = '검색어';
	$_auto_replace[$_file_name]['qna_search_str'] = 'Y';
	$_code_comment[$_file_name]['qna_search_str'] = '상품문의 검색어';

	$_replace_code[$_file_name]['qna_search_column1'] = ($_GET['search_column'] == 1) ? 'selected' : '';
	$_replace_hangul[$_file_name]['qna_search_column1'] = '작성자검색상태';
	$_auto_replace[$_file_name]['qna_search_column1'] = 'Y';
	$_code_comment[$_file_name]['qna_search_column1'] = '작성자 검색상태일 경우 selected 문자열 출력';

	$_replace_code[$_file_name]['qna_search_column2'] = ($_GET['search_column'] == 2) ? 'selected' : '';
	$_replace_hangul[$_file_name]['qna_search_column2'] = '아이디검색상태';
	$_auto_replace[$_file_name]['qna_search_column2'] = 'Y';
	$_code_comment[$_file_name]['qna_search_column2'] = '작성자 검색상태일 경우 selected 문자열 출력';

	$_replace_code[$_file_name]['qna_search_column3'] = ($_GET['search_column'] == 3) ? 'selected' : '';
	$_replace_hangul[$_file_name]['qna_search_column3'] = '제목검색상태';
	$_auto_replace[$_file_name]['qna_search_column3'] = 'Y';
	$_code_comment[$_file_name]['qna_search_column3'] = '작성자 검색상태일 경우 selected 문자열 출력';

	$_replace_code[$_file_name]['qna_search_column4'] = ($_GET['search_column'] == 4) ? 'selected' : '';
	$_replace_hangul[$_file_name]['qna_search_column4'] = '내용검색상태';
	$_auto_replace[$_file_name]['qna_search_column4'] = 'Y';
	$_code_comment[$_file_name]['qna_search_column4'] = '내용 검색상태일 경우 selected 문자열 출력';

?>