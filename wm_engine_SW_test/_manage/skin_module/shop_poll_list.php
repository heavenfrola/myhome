<?PHP

	$_replace_code[$_file_name]['poll_view_hids']="";
	$_replace_hangul[$_file_name]['poll_view_hids']="설문상세숨김시작";
	$_code_comment[$_file_name]['poll_view_hids']="설문 상세 정보가 없을 경우 숨김 처리 시작";
	$_auto_replace[$_file_name]['poll_view_hids']="Y";

	$_replace_code[$_file_name]['poll_view_hide']="";
	$_replace_hangul[$_file_name]['poll_view_hide']="설문상세숨김끝";
	$_code_comment[$_file_name]['poll_view_hide']="설문 상세 정보가 없을 경우 숨김 처리 끝";
	$_auto_replace[$_file_name]['poll_view_hide']="Y";

	$_replace_code[$_file_name]['poll_apply_form']="";
	$_replace_hangul[$_file_name]['poll_apply_form']="설문조사폼";
	$_code_comment[$_file_name]['poll_apply_form']="설문을 실행하는 폼(필수)";
	$_auto_replace[$_file_name]['poll_apply_form']="Y";

	$_replace_code[$_file_name]['poll_title']="";
	$_replace_hangul[$_file_name]['poll_title']="설문조사주제";
	$_code_comment[$_file_name]['poll_title']="설문 조사 주제 출력";
	$_auto_replace[$_file_name]['poll_title']="Y";

	$_replace_code[$_file_name]['poll_content']="";
	$_replace_hangul[$_file_name]['poll_content']="설문조사내용";
	$_code_comment[$_file_name]['poll_content']="설문 조사 내용 출력";
	$_auto_replace[$_file_name]['poll_content']="Y";

	$_replace_code[$_file_name]['poll_item_list']="";
	$_replace_hangul[$_file_name]['poll_item_list']="설문항목리스트";
	$_code_comment[$_file_name]['poll_item_list']="설문 조사 항목 리스트";
	$_replace_datavals[$_file_name]['poll_item_list']="항목명:title:해당 항목명 출력;결과퍼센티지:per:해당 항목의 결과 %;득표수:total:해당 항목 총 득표수;";

	$_replace_code[$_file_name]['poll_apply_url']="";
	$_replace_hangul[$_file_name]['poll_apply_url']="설문완료링크태그";
	$_code_comment[$_file_name]['poll_apply_url']="설문 참여 링크(A 태그) 출력";
	$_auto_replace[$_file_name]['poll_apply_url']="Y";

	$_replace_code[$_file_name]['poll_delete_pwd']="";
	$_replace_hangul[$_file_name]['poll_delete_pwd']="설문댓글삭제확인";
	$_code_comment[$_file_name]['poll_delete_pwd']="비회원 댓글 삭제 실행시 비밀번호 확인 구문";

	$_replace_code[$_file_name]['poll_comment_list']="";
	$_replace_hangul[$_file_name]['poll_comment_list']="설문댓글리스트";
	$_code_comment[$_file_name]['poll_comment_list']="설문 조사의 댓글 리스트";
	$_replace_datavals[$_file_name]['poll_comment_list']="댓글작성자:name:해당 댓글 작성자 출력;댓글내용:content:해당 댓글 내용 출력;댓글삭제:del_link:해당 댓글 삭제 가능한 경로 출력;댓글작성일시:reg_date:댓글 작성일시(년-월-일 시,분);";

	$_replace_code[$_file_name]['poll_comment_form_start']="";
	$_replace_hangul[$_file_name]['poll_comment_form_start']="설문댓글폼시작";
	$_code_comment[$_file_name]['poll_comment_form_start']="설문 댓글 입력 폼 시작 선언";
	$_auto_replace[$_file_name]['poll_comment_form_start']="Y";

	$_replace_code[$_file_name]['poll_comment_form_end']="";
	$_replace_hangul[$_file_name]['poll_comment_form_end']="설문댓글폼끝";
	$_code_comment[$_file_name]['poll_comment_form_end']="설문 댓글 입력 폼 끝 선언";
	$_auto_replace[$_file_name]['poll_comment_form_end']="Y";

	$_replace_code[$_file_name]['poll_comment_writer_hids']="";
	$_replace_hangul[$_file_name]['poll_comment_writer_hids']="로그인전처리시작";
	$_code_comment[$_file_name]['poll_comment_writer_hids']="비회원일 경우 작성자/비밀번호 입력 구문 출력 시작";
	$_auto_replace[$_file_name]['poll_comment_writer_hids']="Y";

	$_replace_code[$_file_name]['poll_comment_writer_hide']="";
	$_replace_hangul[$_file_name]['poll_comment_writer_hide']="로그인전처리끝";
	$_code_comment[$_file_name]['poll_comment_writer_hide']="비회원일 경우 작성자/비밀번호 입력 구문 출력 끝";
	$_auto_replace[$_file_name]['poll_comment_writer_hide']="Y";

	$_replace_code[$_file_name]['poll_list']="";
	$_replace_hangul[$_file_name]['poll_list']="설문조사리스트";
	$_code_comment[$_file_name]['poll_list']="등록된 모든 설문 조사 내역 리스트";
	$_replace_datavals[$_file_name]['poll_list']="번호:idx;설문조사주제:title:설문 조사 주제 출력;설문조사시작일:sdate:설문 조사 시행 시작일 출력;설문조사종료일:fdate:설문 조사 시행 종료일 출력;총참여수:total_vote;";

?>