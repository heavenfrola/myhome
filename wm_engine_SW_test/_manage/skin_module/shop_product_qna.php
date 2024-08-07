<?PHP

	$_replace_code[$_file_name][$_mypage.'qna_list']="";
	$_replace_hangul[$_file_name][$_mypage.'qna_list']="상품질답리스트";
	$_code_comment[$_file_name][$_mypage.'qna_list']="질문과 답변 리스트";
	$_replace_datavals[$_file_name][$_mypage.'qna_list']="글번호:qna_idx;글고유번호:no;새글아이콘:new_i:최신글일 경우 출력 아이콘;비밀글아이콘:secret_i:비밀글로 등록되었을 경우 출력 아이콘;글답변전아이콘:reply_b_i:답변이 완료되기 전 출력 아이콘;글답변아이콘:reply_i:답변이 완료되었을 경우 출력 아이콘;파일아이콘:file_icon:첨부 이미지 파일이 존재할 경우 아이콘 출력;글제목:title:해당 질문 내용 조회 페이지 링크를 포함한 제목 출력;글제목(링크없음):title_nolink:해당 질문 내용 조회 페이지 링크를 포함하지 않은 제목 출력;글작성자:name;글등록일:reg_date:글 등록일(년/월/일);글등록일2:reg_date2:글 등록일(월/일);QNA첨부파일1:img1:작성자 첫번째 첨부 이미지 출력;QNA첨부파일2:img2:작성자 두번째 첨부 이미지 출력;글내용:content:글 상세 내용 출력;글답변:answer_str:관리자의 답변 내용 출력;글수정링크태그:edit_link:글 수정이 가능할 경우 수정 링크(A 태그) 출력;글삭제링크태그:del_link:글 삭제가 가능할 경우 삭제 링크(A 태그) 출력;조회수:hit;카테고리:cate:카테고리 정보가 있을 경우 출력;링크:link2:해당 게시물의 페이지 주소 출력;관련상품링크:prd_link:게시판사용자리스트의 관련상품링크;QNA관리자파일첨부1:img3:관리자 답변 첫번째 첨부 이미지 출력;QNA관리자파일첨부2:img4:관리자 답변 두번째 첨부 이미지 출력;";

	$_replace_code[$_file_name]['qna_title_sel']="";
	$_replace_hangul[$_file_name]['qna_title_sel']="제한제목목록";
	$_code_comment[$_file_name]['qna_title_sel']="관리자자가 지정한 제목중에서만 선택할수 있는 제목 입력란";
	$_replace_datavals[$_file_name]['qna_title_sel'] = "글제목:title:글 수정시 글제목 출력;";

	$_replace_code[$_file_name]['qna_script']="";
	$_replace_hangul[$_file_name]['qna_script']="하단스크립트";
	$_code_comment[$_file_name]['qna_script']="글 선택시 출력 스크립트(필수)";
	$_auto_replace[$_file_name]['qna_script']="Y";

?>