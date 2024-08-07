<?PHP

	$_replace_code[$_file_name]['board_name']="";
	$_replace_hangul[$_file_name]['board_name']="게시판명";
	$_code_comment[$_file_name]['board_name']="현재 게시판 이름";
	$_auto_replace[$_file_name]['board_name']="Y";

	$_replace_code[$_file_name]['board_db'] = $config['db'];
	$_replace_hangul[$_file_name]['board_db'] = '게시판코드';
	$_code_comment[$_file_name]['board_db'] = '현재 출력중인 게시판의 코드명';
	$_auto_replace[$_file_name]['board_db'] = 'Y';

	$_replace_code[$_file_name]['board_skin_url']="";
	$_replace_hangul[$_file_name]['board_skin_url']="게시판스킨경로";
	$_code_comment[$_file_name]['board_skin_url']="게시판 스킨 경로 출력";
	$_auto_replace[$_file_name]['board_skin_url']="Y";

	$_replace_code[$_file_name]['board_list_url']="";
	$_replace_hangul[$_file_name]['board_list_url']="목록보기링크태그";
	$_code_comment[$_file_name]['board_list_url']="목록으로 돌아갈 수 있는 링크(A 태그) 출력";
	$_auto_replace[$_file_name]['board_list_url']="Y";

	$_replace_code[$_file_name]['board_write_url']="";
	$_replace_hangul[$_file_name]['board_write_url']="글쓰기링크태그";
	$_code_comment[$_file_name]['board_write_url']="글을 입력할 수 있는 링크(A 태그) 출력";
	$_auto_replace[$_file_name]['board_write_url']="Y";

	if($filename == "view.php" || $mari_mode == "view@view"){

		$_replace_code[$_file_name]['board_del_url']="";
		$_replace_hangul[$_file_name]['board_del_url']="글삭제링크태그";
		$_code_comment[$_file_name]['board_del_url']="글을 삭제할 수 있는 링크(A 태그) 출력";
		$_auto_replace[$_file_name]['board_del_url']="Y";

		$_replace_code[$_file_name]['board_mod_url']="";
		$_replace_hangul[$_file_name]['board_mod_url']="글수정링크태그";
		$_code_comment[$_file_name]['board_mod_url']="글을 수정할 수 있는 링크(A 태그) 출력";
		$_auto_replace[$_file_name]['board_mod_url']="Y";

		$_replace_code[$_file_name]['board_reply_url']="";
		$_replace_hangul[$_file_name]['board_reply_url']="답글쓰기링크태그";
		$_code_comment[$_file_name]['board_reply_url']="답글을 입력할 수 있는 링크(A 태그) 출력";
		$_auto_replace[$_file_name]['board_reply_url']="Y";

		$_replace_code[$_file_name]['board_title']="";
		$_replace_hangul[$_file_name]['board_title']="글제목";
		$_code_comment[$_file_name]['board_title']="해당 글의 제목";
		$_auto_replace[$_file_name]['board_title']="Y";

		$_replace_code[$_file_name]['board_content']="";
		$_replace_hangul[$_file_name]['board_content']="글내용";
		$_code_comment[$_file_name]['board_content']="해당 글의 상세 내용";
		$_auto_replace[$_file_name]['board_content']="Y";

		$_replace_code[$_file_name]['board_writer']="";
		$_replace_hangul[$_file_name]['board_writer']="작성자";
		$_code_comment[$_file_name]['board_writer']="해당 글의 작성자";
		$_auto_replace[$_file_name]['board_writer']="Y";

		$_replace_code[$_file_name]['board_reg_date']="";
		$_replace_hangul[$_file_name]['board_reg_date']="작성일시";
		$_code_comment[$_file_name]['board_reg_date']="해당 글의 작성일시(년-월-일 시:분:초)";
		$_auto_replace[$_file_name]['board_reg_date']="Y";

		$_replace_code[$_file_name]['board_hit']="";
		$_replace_hangul[$_file_name]['board_hit']="조회수";
		$_code_comment[$_file_name]['board_hit']="해당 글의 조회수";
		$_auto_replace[$_file_name]['board_hit']="Y";

		$_replace_code[$_file_name]['board_email']="";
		$_replace_hangul[$_file_name]['board_email']="이메일";
		$_code_comment[$_file_name]['board_email']="해당 글의 작성자 이메일 주소";
		$_auto_replace[$_file_name]['board_email']="Y";

		$_replace_code[$_file_name]['board_homepage']="";
		$_replace_hangul[$_file_name]['board_homepage']="홈페이지";
		$_code_comment[$_file_name]['board_homepage']="해당 글의 작성자 홈페이지";
		$_auto_replace[$_file_name]['board_homepage']="Y";

		$_replace_code[$_file_name]['board_link1']="";
		$_replace_hangul[$_file_name]['board_link1']="링크";
		$_code_comment[$_file_name]['board_link1']="해당 글의 링크 출력";
		$_auto_replace[$_file_name]['board_link1']="Y";

		$_replace_code[$_file_name]['board_next_page']="";
		$_replace_hangul[$_file_name]['board_next_page']="다음글보기";
		$_code_comment[$_file_name]['board_next_page']="다음글보기";
		$_auto_replace[$_file_name]['board_next_page']="Y";

		$_replace_code[$_file_name]['board_pre_page']="";
		$_replace_hangul[$_file_name]['board_pre_page']="이전글보기";
		$_code_comment[$_file_name]['board_pre_page']="이전글보기";
		$_auto_replace[$_file_name]['board_pre_page']="Y";

		for($ii = 1; $ii <= 4; $ii++) {
			$_replace_code[$_file_name]['board_upfile'.$ii] = '';
			$_replace_hangul[$_file_name]['board_upfile'.$ii] = '첨부파일'.$ii;
			$_code_comment[$_file_name]['board_upfile'.$ii] = $ii.'번째 첨부 파일명';
			$_auto_replace[$_file_name]['board_upfile'.$ii] = 'Y';

			$_replace_code[$_file_name]['board_dn'.$ii.'_url'] = '';
			$_replace_hangul[$_file_name]['board_dn'.$ii.'_url'] = '다운로드'.$ii;
			$_code_comment[$_file_name]['board_dn'.$ii.'_url'] = $ii.'번째 첨부파일 다운로드 링크(A 태그) 출력';
			$_auto_replace[$_file_name]['board_dn'.$ii.'_url'] = 'Y';

			$_replace_code[$_file_name]['board_upfile'.$ii.'_hids'] = '';
			$_replace_hangul[$_file_name]['board_upfile'.$ii.'_hids'] = '첨부파일'.$ii.'숨김시작';
			$_code_comment[$_file_name]['board_upfile'.$ii.'_hids'] = $ii.'번째 첨부 파일이 존재하지 않을 경우 숨김 처리 시작';
			$_auto_replace[$_file_name]['board_upfile'.$ii.'_hids'] = 'Y';

			$_replace_code[$_file_name]['board_upfile'.$ii.'_hide'] = '';
			$_replace_hangul[$_file_name]['board_upfile'.$ii.'_hide'] = '첨부파일'.$ii.'숨김끝';
			$_code_comment[$_file_name]['board_upfile'.$ii.'_hide'] = $ii.'번째 첨부 파일이 존재하지 않을 경우 숨김 처리 끝';
			$_auto_replace[$_file_name]['board_upfile'.$ii.'_hide'] = 'Y';

			$_replace_code[$_file_name]['board_upfile'.$ii.'_img'] = '';
			$_replace_hangul[$_file_name]['board_upfile'.$ii.'_img'] = '첨부파일'.$ii.'이미지';
			$_code_comment[$_file_name]['board_upfile'.$ii.'_img'] = $ii.'번째 첨부 이미지 출력';
			$_auto_replace[$_file_name]['board_upfile'.$ii.'_img'] = 'Y';
		}

		$_replace_code[$_file_name]['board_cate_name']="";
		$_replace_hangul[$_file_name]['board_cate_name']="글분류명";
		$_code_comment[$_file_name]['board_cate_name']="글분류를 사용할 경우 분류명 출력";
		$_auto_replace[$_file_name]['board_cate_name']="Y";

		for($i=1; $i <= $cfg['board_add_temp']; $i++) {
			$_replace_code[$_file_name]['board_temp'.$i] = '';
			$_replace_hangul[$_file_name]['board_temp'.$i] = '추가항목'.$i;
			$_code_comment[$_file_name]['board_temp'.$i] = '추가항목'.$i;
			$_auto_replace[$_file_name]['board_temp'.$i] = 'Y';

			$_replace_code[$_file_name]['board_temp_name'.$i] = '';
			$_replace_hangul[$_file_name]['board_temp_name'.$i] = '추가항목명'.$i;
			$_code_comment[$_file_name]['board_temp_name'.$i] = '추가항목명'.$i;
			$_auto_replace[$_file_name]['board_temp_name'.$i] = 'Y';
		}

		$_replace_code[$_file_name]['board_refPrd_list']="";
		$_replace_hangul[$_file_name]['board_refPrd_list']="관련상품리스트";
		$_code_comment[$_file_name]['board_refPrd_list']="";
		$_replace_datavals[$_file_name]['board_refPrd_list'] = $_replace_datavals['common_module']['product_box'].'관련상품제거링크:remove_link;';

		$_replace_code[$_file_name]['board_hidden'] = $hidden_yn;
		$_replace_hangul[$_file_name]['board_hidden'] = "숨김글표시";
		$_code_comment[$_file_name]['board_hidden'] = "숨김글표시";
		$_auto_replace[$_file_name]['board_hidden'] = "Y";

		$_replace_code[$_file_name]['board_gallery_cols']= $config['gallery_cols'];
		$_replace_hangul[$_file_name]['board_gallery_cols']="갤러리한줄글수";
		$_code_comment[$_file_name]['board_gallery_cols']="갤러리한줄글수.";
		$_auto_replace[$_file_name]['board_gallery_cols']="Y";

	}
	if($filename == "list_loop.php" || $filename == "notice.php" || ($mari_mode == "view@list" && defined('_wisa_manage_edit_'))){

		if($filename == "list_loop.php") $_replace_code[$_file_name]['board_class']="";
		$_replace_hangul[$_file_name]['board_class']="리스트스타일";
		$_code_comment[$_file_name]['board_class']="리스트 CSS 클래스명 출력";
		$_auto_replace[$_file_name]['board_class']="Y";

		$_replace_code[$_file_name]['board_sno']="";
		$_replace_hangul[$_file_name]['board_sno']="글번호";
		$_code_comment[$_file_name]['board_sno']="리스트 글 번호 출력";
		$_auto_replace[$_file_name]['board_sno']="Y";

		$_replace_code[$_file_name]['board_cate']="";
		$_replace_hangul[$_file_name]['board_cate']="글분류";
		$_code_comment[$_file_name]['board_cate']="글분류를 사용할 경우 분류명 출력";
		$_auto_replace[$_file_name]['board_cate']="Y";

		$_replace_code[$_file_name]['board_new_icon']="";
		$_replace_hangul[$_file_name]['board_new_icon']="아이콘";
		$_code_comment[$_file_name]['board_new_icon']="최신글/비밀글일 경우 출력 아이콘";
		$_auto_replace[$_file_name]['board_new_icon']="Y";

		$_replace_code[$_file_name]['board_icon1']="";
		$_replace_hangul[$_file_name]['board_icon1']="새글아이콘";
		$_code_comment[$_file_name]['board_icon1']="최신글일 경우 출력 아이콘";
		$_auto_replace[$_file_name]['board_icon1']="Y";

		$_replace_code[$_file_name]['board_icon2']="";
		$_replace_hangul[$_file_name]['board_icon2']="첨부아이콘";
		$_code_comment[$_file_name]['board_icon2']="첨부파일이 있을 경우 출력 아이콘";
		$_auto_replace[$_file_name]['board_icon2']="Y";

		$_replace_code[$_file_name]['board_icon3']="";
		$_replace_hangul[$_file_name]['board_icon3']="비밀아이콘";
		$_code_comment[$_file_name]['board_icon3']="비밀글일 경우 출력 아이콘";
		$_auto_replace[$_file_name]['board_icon3']="Y";

		$_replace_code[$_file_name]['board_del_url']="";
		$_replace_hangul[$_file_name]['board_del_url']="글삭제링크태그";
		$_code_comment[$_file_name]['board_del_url']="글을 삭제할 수 있는 링크(A 태그) 출력";
		$_auto_replace[$_file_name]['board_del_url']="Y";

		$_replace_code[$_file_name]['board_mod_url']="";
		$_replace_hangul[$_file_name]['board_mod_url']="글수정링크태그";
		$_code_comment[$_file_name]['board_mod_url']="글을 수정할 수 있는 링크(A 태그) 출력";
		$_auto_replace[$_file_name]['board_mod_url']="Y";

		$_replace_code[$_file_name]['board_comment']="";
		$_replace_hangul[$_file_name]['board_comment']="댓글";
		$_code_comment[$_file_name]['board_comment']="목록에 댓글이 필요한 경우";
		$_auto_replace[$_file_name]['board_comment']="Y";

		$_replace_code[$_file_name]['board_title']="";
		$_replace_hangul[$_file_name]['board_title']="글제목";
		$_code_comment[$_file_name]['board_title']="해당 글의 제목 (댓글 수와 링크 포함)";
		$_auto_replace[$_file_name]['board_title']="Y";

		$_replace_code[$_file_name]['board_title2']="";
		$_replace_hangul[$_file_name]['board_title2']="글제목2";
		$_code_comment[$_file_name]['board_title2']="해당 글의 제목 (댓글 수와 링크 미포함된 제목 텍스트)";
		$_auto_replace[$_file_name]['board_title2']="Y";

		$_replace_code[$_file_name]['board_link']="";
		$_replace_hangul[$_file_name]['board_link']="링크";
		$_code_comment[$_file_name]['board_link']="해당 글의 링크 출력";
		$_auto_replace[$_file_name]['board_link']="Y";

		$_replace_code[$_file_name]['board_total_comment']="";
		$_replace_hangul[$_file_name]['board_total_comment']="댓글수";
		$_code_comment[$_file_name]['board_total_comment']="해당 글의 댓글 수";
		$_auto_replace[$_file_name]['board_total_comment']="Y";

		$_replace_code[$_file_name]['board_writer']="";
		$_replace_hangul[$_file_name]['board_writer']="작성자";
		$_code_comment[$_file_name]['board_writer']="해당 글의 작성자";
		$_auto_replace[$_file_name]['board_writer']="Y";

		$_replace_code[$_file_name]['board_reg_date']="";
		$_replace_hangul[$_file_name]['board_reg_date']="작성일";
		$_code_comment[$_file_name]['board_reg_date']="해당 글의 작성일(년/월/일)";
		$_auto_replace[$_file_name]['board_reg_date']="Y";

		$_replace_code[$_file_name]['board_hit']="";
		$_replace_hangul[$_file_name]['board_hit']="조회수";
		$_code_comment[$_file_name]['board_hit']="해당 글의 조회수";
		$_auto_replace[$_file_name]['board_hit']="Y";

		for($ii = 1; $ii <= 4; $ii++) {
			$_replace_code[$_file_name]['board_upfile'.$ii] = '';
			$_replace_hangul[$_file_name]['board_upfile'.$ii] = '첨부이미지'.$i;
			$_code_comment[$_file_name]['board_upfile'.$ii] = '첨부이미지가 존재할 경우 첫번째 첨부 이미지 출력';
			$_auto_replace[$_file_name]['board_upfile'.$ii] = 'Y';

			$_replace_code[$_file_name]['board_upfile'.$ii.'_link'] = '';
			$_replace_hangul[$_file_name]['board_upfile'.$ii.'_link'] = '첨부이미지'.$ii.'(링크포함)';
			$_code_comment[$_file_name]['board_upfile'.$ii.'_link'] = '상품링크(A 태그)를 포함한 첫번째 첨부이미지 출력';
			$_auto_replace[$_file_name]['board_upfile'.$ii.'_link'] = 'Y';
		}

		$_replace_code[$_file_name]['board_temp01']="";
		$_replace_hangul[$_file_name]['board_temp01']="입금일";
		$_code_comment[$_file_name]['board_temp01']="입금일 정보 필드 사용시 입금일 출력";
		$_auto_replace[$_file_name]['board_temp01']="Y";

		for($i=1; $i <= $cfg['board_add_temp']; $i++) {
			$_replace_code[$_file_name]['board_temp'.$i]="";
			$_replace_hangul[$_file_name]['board_temp'.$i]="추가항목".$i;
			$_code_comment[$_file_name]['board_temp'.$i]="추가항목".$i;
			$_auto_replace[$_file_name]['board_temp'.$i]="Y";

			$_replace_code[$_file_name]['board_temp_name'.$i]="";
			$_replace_hangul[$_file_name]['board_temp_name'.$i]="추가항목명".$i;
			$_code_comment[$_file_name]['board_temp_name'.$i]="추가항목명".$i;
			$_auto_replace[$_file_name]['board_temp_name'.$i]="Y";
		}

		$_replace_code[$_file_name]['board_content']="";
		$_replace_hangul[$_file_name]['board_content']="글내용";
		$_code_comment[$_file_name]['board_content']="해당 글의 상세 내용";
		$_auto_replace[$_file_name]['board_content']="Y";

		$_replace_code[$_file_name]['board_content2']="";
		$_replace_hangul[$_file_name]['board_content2']="글내용(비밀글잠금)";
		$_code_comment[$_file_name]['board_content2']="해당 글의 상세 내용 클릭시 비밀글인 경우 \"비밀입니다.\" 텍스트가 출력됩니다.";
		$_auto_replace[$_file_name]['board_content2']="Y";

		$_replace_code[$_file_name]['board_start_day'] = '';
		$_replace_hangul[$_file_name]['board_start_day'] = '시작일';
		$_code_comment[$_file_name]['board_start_day'] = '기간 설정에서 입력한 시작일 출력';
		$_auto_replace[$_file_name]['board_start_day'] = 'Y';

		$_replace_code[$_file_name]['board_end_day'] = '';
		$_replace_hangul[$_file_name]['board_end_day'] = '종료일';
		$_code_comment[$_file_name]['board_end_day'] = '기간 설정에서 입력한 종료일 출력';
		$_auto_replace[$_file_name]['board_end_day'] = 'Y';

		$_replace_code[$_file_name]['board_start_date'] = '';
		$_replace_hangul[$_file_name]['board_start_date'] = '시작일시';
		$_code_comment[$_file_name]['board_start_date'] = '기간 설정에서 입력한 시작일과 시간 출력';
		$_auto_replace[$_file_name]['board_start_date'] = 'Y';

		$_replace_code[$_file_name]['board_end_date'] = '';
		$_replace_hangul[$_file_name]['board_end_date'] = '종료일시';
		$_code_comment[$_file_name]['board_end_date'] = '기간 설정에서 입력한 종료일과 시간 출력';
		$_auto_replace[$_file_name]['board_end_date'] = 'Y';
	}
	if($filename == "del.php" || $filename == "edit.php" || $filename == "secret.php" || $mari_mode == "write@del" || $mari_mode == "write@write@edit" || $mari_mode == "view@view"){

		$_replace_code[$_file_name]['board_pwd_form_start']="";
		$_replace_hangul[$_file_name]['board_pwd_form_start']="비밀번호폼시작";
		$_code_comment[$_file_name]['board_pwd_form_start']="비밀번호 폼 시작 선언";
		$_auto_replace[$_file_name]['board_pwd_form_start']="Y";

		$_replace_code[$_file_name]['board_pwd_form_end']="";
		$_replace_hangul[$_file_name]['board_pwd_form_end']="비밀번호폼끝";
		$_code_comment[$_file_name]['board_pwd_form_end']="비밀번호 폼 끝 선언";
		$_auto_replace[$_file_name]['board_pwd_form_end']="Y";

	}

	if($filename == "list_top.php" || $filename == "list_bottom.php" || $mari_mode == "view@list" || (($config['list_mode'] == '1' || $config['list_mode'] == '3')&& $mari_mode == 'view@view')){
		$_replace_code[$_file_name]['board_list_total']="";
		$_replace_hangul[$_file_name]['board_list_total']="총게시물수";
		$_code_comment[$_file_name]['board_list_total']="현재 게시판의 총게시물수 출력";
		$_auto_replace[$_file_name]['board_list_total']="Y";

		$_replace_code[$_file_name]['board_nextpage_link'] = (isset($nextPage_link) == true) ? $nextPage_link : '';
        $_replace_hangul[$_file_name]['board_nextpage_link'] = '다음페이지링크';
        $_code_comment[$_file_name]['board_nextpage_link'] = '다음 페이지가 있을 경우 링크를 출력';
		$_auto_replace[$_file_name]['board_nextpage_link'] = 'Y';

		$_replace_code[$_file_name]['board_cate_list']="";
		$_replace_hangul[$_file_name]['board_cate_list']="게시판카테고리선택";
		$_code_comment[$_file_name]['board_cate_list']="카테고리를 사용할 경우 카테고리 선택 메뉴 출력(코딩 수정 불가)";
		$_auto_replace[$_file_name]['board_cate_list']="Y";

		$_replace_code[$_file_name]['board_cate_select_list']="";
		$_replace_hangul[$_file_name]['board_cate_select_list']="게시판카테고리셀렉트선택";
		$_code_comment[$_file_name]['board_cate_select_list']="게시판 분류가 설정되어있을경우 분류선택 콤보박스 출력";
		$_auto_replace[$_file_name]['board_cate_select_list']="Y";

		$_replace_code[$_file_name]['board_cate2_list']="";
		$_replace_hangul[$_file_name]['board_cate2_list']="게시판카테고리목록";
		$_code_comment[$_file_name]['board_cate2_list']="카테고리를 사용할 경우 카테고리 출력(코딩 수정 가능)";
		$_replace_datavals[$_file_name]['board_cate2_list']="카테고리명:name;카테고리코드:no;현재카테고리:selected:현재 카테고리일 경우 selected 텍스트 출력;카테고리링크:link:선택된 카테고리로 이동할수 있는 URL;";

		$_replace_code[$_file_name]['board_top_url'] = $mari_url.'index.php?db='.$db;
		$_replace_hangul[$_file_name]['board_top_url']="게시판링크";
		$_code_comment[$_file_name]['board_top_url']="카테고리가 선택되지 않은 현재게시판의 전체보기 링크";
		$_auto_replace[$_file_name]['board_top_url']="Y";

		$_replace_code[$_file_name]['board_search_form_start']="";
		$_replace_hangul[$_file_name]['board_search_form_start']="검색폼시작";
		$_code_comment[$_file_name]['board_search_form_start']="검색 폼 시작 선언";
		$_auto_replace[$_file_name]['board_search_form_start']="Y";

		$_replace_code[$_file_name]['board_search_form_end']="";
		$_replace_hangul[$_file_name]['board_search_form_end']="검색폼끝";
		$_code_comment[$_file_name]['board_search_form_end']="검색 폼 끝 선언";
		$_auto_replace[$_file_name]['board_search_form_end']="Y";

		$_replace_code[$_file_name]['board_search_radio']="";
		$_replace_hangul[$_file_name]['board_search_radio']="검색영역선택";
		$_code_comment[$_file_name]['board_search_radio']="검색 영역을 선택할 수 있는 라디오 버튼 출력";
		$_auto_replace[$_file_name]['board_search_radio']="Y";

		$_replace_code[$_file_name]['board_search_txt']="";
		$_replace_hangul[$_file_name]['board_search_txt']="검색어";
		$_code_comment[$_file_name]['board_search_txt']="검색어 출력";
		$_auto_replace[$_file_name]['board_search_txt']="Y";

		$_replace_code[$_file_name]['board_search_name_check']="";
		$_replace_hangul[$_file_name]['board_search_name_check']="작성자셀렉트선택여부";
		$_code_comment[$_file_name]['board_search_name_check']="작성자로 검색했을 경우 selected 가 출력됩니다.";
		$_auto_replace[$_file_name]['board_search_name_check']="Y";

		$_replace_code[$_file_name]['board_search_title_check']="";
		$_replace_hangul[$_file_name]['board_search_title_check']="제목셀렉트선택여부";
		$_code_comment[$_file_name]['board_search_title_check']="제목으로 검색했을 경우 selected 가 출력됩니다.";
		$_auto_replace[$_file_name]['board_search_title_check']="Y";

		$_replace_code[$_file_name]['board_search_content_check']="";
		$_replace_hangul[$_file_name]['board_search_content_check']="내용셀렉트선택여부";
		$_code_comment[$_file_name]['board_search_content_check']="내용으로 검색했을 경우 selected 가 출력됩니다.";
		$_auto_replace[$_file_name]['board_search_content_check']="Y";

		$_replace_code[$_file_name]['board_search_cate_check'] = ($_GET['cate'] > 0) ? 'cate_search' : 'cate_all';
		$_replace_hangul[$_file_name]['board_search_cate_check']="카테고리검색여부";
		$_code_comment[$_file_name]['board_search_cate_check']="카테고리 검색중일 경우 cate_search. 전체 카테고리일 경우 cate_all 이 출력됩니다.";
		$_auto_replace[$_file_name]['board_search_cate_check']="Y";

		$_replace_code[$_file_name]['board_gallery_cols']= $config['gallery_cols'];
		$_replace_hangul[$_file_name]['board_gallery_cols']="갤러리한줄글수";
		$_code_comment[$_file_name]['board_gallery_cols']="갤러리한줄글수.";
		$_auto_replace[$_file_name]['board_gallery_cols']="Y";

		for($i=1; $i <= $cfg['board_add_temp']; $i++) {
			$_replace_code[$_file_name]['board_temp'.$i]="";
			$_replace_hangul[$_file_name]['board_temp'.$i]="추가항목".$i;
			$_code_comment[$_file_name]['board_temp'.$i]="추가항목".$i;
			$_auto_replace[$_file_name]['board_temp'.$i]="Y";

			$_replace_code[$_file_name]['board_temp_name'.$i]="";
			$_replace_hangul[$_file_name]['board_temp_name'.$i]="추가항목명".$i;
			$_code_comment[$_file_name]['board_temp_name'.$i]="추가항목명".$i;
			$_auto_replace[$_file_name]['board_temp_name'.$i]="Y";
		}
	}

	if($filename == "write.php" || preg_match("/^write@write/", $mari_mode)){

		$_replace_code[$_file_name]['board_write_form_start']="";
		$_replace_hangul[$_file_name]['board_write_form_start']="글쓰기폼시작";
		$_code_comment[$_file_name]['board_write_form_start']="글쓰기 폼 시작 선언";
		$_auto_replace[$_file_name]['board_write_form_start']="Y";

		$_replace_code[$_file_name]['board_write_form_end']="";
		$_replace_hangul[$_file_name]['board_write_form_end']="글쓰기폼끝";
		$_code_comment[$_file_name]['board_write_form_end']="글쓰기 폼 끝 선언";
		$_auto_replace[$_file_name]['board_write_form_end']="Y";

		$_replace_code[$_file_name]['board_writer_hids']="";
		$_replace_hangul[$_file_name]['board_writer_hids']="작성자필드시작";
		$_code_comment[$_file_name]['board_writer_hids']="작성자 필드의 앞부분에 삽입";
		$_auto_replace[$_file_name]['board_writer_hids']="Y";

		$_replace_code[$_file_name]['board_writer_hide']="";
		$_replace_hangul[$_file_name]['board_writer_hide']="작성자필드끝";
		$_code_comment[$_file_name]['board_writer_hide']="작성자 필드의 끝부분에 삽입";
		$_auto_replace[$_file_name]['board_writer_hide']="Y";

		$_replace_code[$_file_name]['board_pwd_hids']="";
		$_replace_hangul[$_file_name]['board_pwd_hids']="비밀번호숨김시작";
		$_code_comment[$_file_name]['board_pwd_hids']="비밀번호 필드가 필요하지 않을 경우 숨김 처리 시작";
		$_auto_replace[$_file_name]['board_pwd_hids']="Y";

		$_replace_code[$_file_name]['board_pwd_hide']="";
		$_replace_hangul[$_file_name]['board_pwd_hide']="비밀번호숨김끝";
		$_code_comment[$_file_name]['board_pwd_hide']="비밀번호 필드가 필요하지 않을 경우 숨김 처리 끝";
		$_auto_replace[$_file_name]['board_pwd_hide']="Y";

		$_replace_code[$_file_name]['board_upfile_hids']="";
		$_replace_hangul[$_file_name]['board_upfile_hids']="첨부파일숨김시작";
		$_code_comment[$_file_name]['board_upfile_hids']="첨부파일 사용 권한이 없을 경우 숨김 처리 시작";
		$_auto_replace[$_file_name]['board_upfile_hids']="Y";

		$_replace_code[$_file_name]['board_upfile_hide']="";
		$_replace_hangul[$_file_name]['board_upfile_hide']="첨부파일숨김끝";
		$_code_comment[$_file_name]['board_upfile_hide']="첨부파일 사용 권한이 없을 경우 숨김 처리 끝";
		$_auto_replace[$_file_name]['board_upfile_hide']="Y";

		$_replace_code[$_file_name]['board_cate_str']="";
		$_replace_hangul[$_file_name]['board_cate_str']="글분류";
		$_code_comment[$_file_name]['board_cate_str']="글분류를 사용할 경우 셀렉트 필드 출력";
		$_auto_replace[$_file_name]['board_cate_str']="Y";

		$_replace_code[$_file_name]['board_notice_hids']="";
		$_replace_hangul[$_file_name]['board_notice_hids']="공지필드숨김시작";
		$_code_comment[$_file_name]['board_notice_hids']="공지글 작성 권한이 없을 경우 숨김 처리 시작";
		$_auto_replace[$_file_name]['board_notice_hids']="Y";

		$_replace_code[$_file_name]['board_notice_hide']="";
		$_replace_hangul[$_file_name]['board_notice_hide']="공지필드숨김끝";
		$_code_comment[$_file_name]['board_notice_hide']="공지글 작성 권한이 없을 경우 숨김 처리 끝";
		$_auto_replace[$_file_name]['board_notice_hide']="Y";

		$_replace_code[$_file_name]['board_secret_hids']="";
		$_replace_hangul[$_file_name]['board_secret_hids']="비밀글필드숨김시작";
		$_code_comment[$_file_name]['board_secret_hids']="게시판 설정을 자동비밀글로 설정할 경우 숨김 처리 시작";
		$_auto_replace[$_file_name]['board_secret_hids']="Y";

		$_replace_code[$_file_name]['board_secret_hide']="";
		$_replace_hangul[$_file_name]['board_secret_hide']="비밀글필드숨김끝";
		$_code_comment[$_file_name]['board_secret_hide']="게시판 설정을 자동비밀글로 설정할 경우 숨김 처리 끝";
		$_auto_replace[$_file_name]['board_secret_hide']="Y";

		$_replace_code[$_file_name]['board_img_hids']="";
		$_replace_hangul[$_file_name]['board_img_hids']="이미지숨김시작";
		$_code_comment[$_file_name]['board_img_hids']="이미지 첨부 권한이 없을 경우 숨김 처리 시작";
		$_auto_replace[$_file_name]['board_img_hids']="Y";

		$_replace_code[$_file_name]['board_img_hide']="";
		$_replace_hangul[$_file_name]['board_img_hide']="이미지숨김끝";
		$_code_comment[$_file_name]['board_img_hide']="이미지 첨부 권한이 없을 경우 숨김 처리 끝";
		$_auto_replace[$_file_name]['board_img_hide']="Y";

		$_replace_code[$_file_name]['board_writer']="";
		$_replace_hangul[$_file_name]['board_writer']="작성자";
		$_code_comment[$_file_name]['board_writer']="글 수정시 작성자명 출력";
		$_auto_replace[$_file_name]['board_writer']="Y";

		$_replace_code[$_file_name]['board_pwd']="";
		$_replace_hangul[$_file_name]['board_pwd']="비밀번호";
		$_code_comment[$_file_name]['board_pwd']="글 수정시 비밀번호 출력";
		$_auto_replace[$_file_name]['board_pwd']="Y";

		$_replace_code[$_file_name]['board_email']="";
		$_replace_hangul[$_file_name]['board_email']="이메일";
		$_code_comment[$_file_name]['board_email']="글 수정시 이메일 출력";
		$_auto_replace[$_file_name]['board_email']="Y";

		$_replace_code[$_file_name]['board_homepage']="";
		$_replace_hangul[$_file_name]['board_homepage']="홈페이지";
		$_code_comment[$_file_name]['board_homepage']="글 수정시 홈페이지 출력";
		$_auto_replace[$_file_name]['board_homepage']="Y";

		$_replace_code[$_file_name]['board_link1']="";
		$_replace_hangul[$_file_name]['board_link1']="링크";
		$_code_comment[$_file_name]['board_link1']="글 수정시 링크 출력";
		$_auto_replace[$_file_name]['board_link1']="Y";

		$_replace_code[$_file_name]['board_title']="";
		$_replace_hangul[$_file_name]['board_title']="글제목";
		$_code_comment[$_file_name]['board_title']="글 수정시 글제목 출력";
		$_auto_replace[$_file_name]['board_title']="Y";

		$_replace_code[$_file_name]['board_title_sel']="";
		$_replace_hangul[$_file_name]['board_title_sel']="제한제목목록";
		$_code_comment[$_file_name]['board_title_sel']="관리자자가 지정한 제목중에서만 선택할수 있는 제목 입력란";
		$_replace_datavals[$_file_name]['board_title_sel'] = "글제목:title:글 수정시 글제목 출력;";

		for($ii = 1; $ii <= 4; $ii++) {
			$_replace_code[$_file_name]['board_upfile'.$ii] = '';
			$_replace_hangul[$_file_name]['board_upfile'.$ii] = '첨부파일'.$ii;
			$_code_comment[$_file_name]['board_upfile'.$ii] = '글 수정시 첨부파일'.$ii.' 출력';
			$_auto_replace[$_file_name]['board_upfile'.$ii] = 'Y';
		}

		$_replace_code[$_file_name]['board_content']="";
		$_replace_hangul[$_file_name]['board_content']="글내용";
		$_code_comment[$_file_name]['board_content']="글 수정시 글내용 출력";
		$_auto_replace[$_file_name]['board_content']="Y";

		$_replace_code[$_file_name]['board_temp01']="";
		$_replace_hangul[$_file_name]['board_temp01']="입금일";
		$_code_comment[$_file_name]['board_temp01']="입금일 정보 필드 사용시 입금일 출력";
		$_auto_replace[$_file_name]['board_temp01']="Y";

		for($i=1; $i <= $cfg['board_add_temp']; $i++) {
			$_replace_code[$_file_name]['board_temp'.$i]="";
			$_replace_hangul[$_file_name]['board_temp'.$i]="추가항목".$i;
			$_code_comment[$_file_name]['board_temp'.$i]="추가항목".$i;
			$_auto_replace[$_file_name]['board_temp'.$i]="Y";

			$_replace_code[$_file_name]['board_temp_name'.$i]="";
			$_replace_hangul[$_file_name]['board_temp_name'.$i]="추가항목명".$i;
			$_code_comment[$_file_name]['board_temp_name'.$i]="추가항목명".$i;
			$_auto_replace[$_file_name]['board_temp_name'.$i]="Y";
		}

		$_replace_code[$_file_name]['board_notice']="";
		$_replace_hangul[$_file_name]['board_notice']="공지글체크";
		$_code_comment[$_file_name]['board_notice']="공지글일 경우 체크 여부 출력 (예 : checked - checkbox 내부에 삽입됨)";
		$_auto_replace[$_file_name]['board_notice']="Y";

		$_replace_code[$_file_name]['board_secret']="";
		$_replace_hangul[$_file_name]['board_secret']="비밀글체크";
		$_code_comment[$_file_name]['board_secret']="비밀글일 경우 체크 여부 출력 (예 : checked - checkbox 내부에 삽입됨)";
		$_auto_replace[$_file_name]['board_secret']="Y";

		$_replace_code[$_file_name]['board_imgfr_url']="";
		$_replace_hangul[$_file_name]['board_imgfr_url']="이미지프레임주소";
		$_code_comment[$_file_name]['board_imgfr_url']="내용에 삽입가능한 이미지 삽입 프레임 주소 출력";
		$_auto_replace[$_file_name]['board_imgfr_url']="Y";

		$_replace_code[$_file_name]['board_products_list']="";
		$_replace_hangul[$_file_name]['board_products_list']="글관련상품보기";
		$_code_comment[$_file_name]['board_products_list']="게시물에 관련된 상품 보기/설정";
		$_auto_replace[$_file_name]['board_products_list']="Y";

		$_replace_code[$_file_name]['board_products_link']="";
		$_replace_hangul[$_file_name]['board_products_link']="글관련상품추가링크";
		$_code_comment[$_file_name]['board_products_link']="게시물에 관련된 상품 추가 버튼 링크";
		$_auto_replace[$_file_name]['board_products_link']="Y";

		//캡차
		$_replace_code[$_file_name]['board_captcha_use']="";
		$_replace_hangul[$_file_name]['board_captcha_use']="자동생성방지";
		$_code_comment[$_file_name]['board_captcha_use']="자동생성방지 (리캡차)";
		$_auto_replace[$_file_name]['board_captcha_use']="Y";

	}
	if($filename == "comment_list_loop.php" || $mari_mode == "view@view"){

		$_replace_code[$_file_name]['board_cm_writer']="";
		$_replace_hangul[$_file_name]['board_cm_writer']="댓글작성자";
		$_code_comment[$_file_name]['board_cm_writer']="해당 댓글의 작성자";
		$_auto_replace[$_file_name]['board_cm_writer']="Y";

		$_replace_code[$_file_name]['board_cm_reg_date']="";
		$_replace_hangul[$_file_name]['board_cm_reg_date']="댓글작성일시";
		$_code_comment[$_file_name]['board_cm_reg_date']="해당 댓글의 작성일시(년-월-일 시:분)";
		$_auto_replace[$_file_name]['board_cm_reg_date']="Y";

		$_replace_code[$_file_name]['board_cm_del_url']="";
		$_replace_hangul[$_file_name]['board_cm_del_url']="댓글삭제링크태그";
		$_code_comment[$_file_name]['board_cm_del_url']="해당 댓글을 삭제할 수 있는 링크(A 태그) 출력";
		$_auto_replace[$_file_name]['board_cm_del_url']="Y";

		$_replace_code[$_file_name]['board_cm_content']="";
		$_replace_hangul[$_file_name]['board_cm_content']="댓글내용";
		$_code_comment[$_file_name]['board_cm_content']="해당 댓글의 내용";
		$_auto_replace[$_file_name]['board_cm_content']="Y";

	}
	if($filename == "comment_write.php" || $mari_mode == "view@view" || $mari_mode == "view@list"){

		$_replace_code[$_file_name]['board_cwrite_form_start']="";
		$_replace_hangul[$_file_name]['board_cwrite_form_start']="댓글쓰기폼시작";
		$_code_comment[$_file_name]['board_cwrite_form_start']="댓글쓰기 폼 시작 선언";
		$_auto_replace[$_file_name]['board_cwrite_form_start']="Y";

		$_replace_code[$_file_name]['board_cwrite_form_end']="";
		$_replace_hangul[$_file_name]['board_cwrite_form_end']="댓글쓰기폼끝";
		$_code_comment[$_file_name]['board_cwrite_form_end']="댓글쓰기 폼 끝 선언";
		$_auto_replace[$_file_name]['board_cwrite_form_end']="Y";

		$_replace_code[$_file_name]['board_cwrite_member_hids']="";
		$_replace_hangul[$_file_name]['board_cwrite_member_hids']="로그인전처리시작";
		$_code_comment[$_file_name]['board_cwrite_member_hids']="비회원일 경우 작성자/비밀번호 입력 구문 출력 시작";
		$_auto_replace[$_file_name]['board_cwrite_member_hids']="Y";

		$_replace_code[$_file_name]['board_cwrite_member_hide']="";
		$_replace_hangul[$_file_name]['board_cwrite_member_hide']="로그인전처리끝";
		$_code_comment[$_file_name]['board_cwrite_member_hide']="비회원일 경우 작성자/비밀번호 입력 구문 출력 끝";
		$_auto_replace[$_file_name]['board_cwrite_member_hide']="Y";

	}
	if($filename == "comment_del.php" || $mari_mode == "write@comment_del"){

		$_replace_code[$_file_name]['board_cpwd_form_start']="";
		$_replace_hangul[$_file_name]['board_cpwd_form_start']="댓글비밀번호폼시작";
		$_code_comment[$_file_name]['board_cpwd_form_start']="댓글 비밀번호 폼 시작 선언";
		$_auto_replace[$_file_name]['board_cpwd_form_start']="Y";

		$_replace_code[$_file_name]['board_cpwd_form_end']="";
		$_replace_hangul[$_file_name]['board_cpwd_form_end']="댓글비밀번호폼끝";
		$_code_comment[$_file_name]['board_cpwd_form_end']="댓글 비밀번호 폼 끝 선언";
		$_auto_replace[$_file_name]['board_cpwd_form_end']="Y";

	}
	if($filename == "file_loop.php" || preg_match("/^write@write/", $mari_mode)){

		$_replace_code[$_file_name]['board_imgfile_url']="";
		$_replace_hangul[$_file_name]['board_imgfile_url']="첨부이미지경로";
		$_code_comment[$_file_name]['board_imgfile_url']="첨부 이미지 경로";
		$_auto_replace[$_file_name]['board_imgfile_url']="Y";

		$_replace_code[$_file_name]['board_imgfile']="";
		$_replace_hangul[$_file_name]['board_imgfile']="첨부이미지";
		$_code_comment[$_file_name]['board_imgfile']="첨부 이미지 출력";
		$_auto_replace[$_file_name]['board_imgfile']="Y";

		$_replace_code[$_file_name]['board_imginsert_url']="";
		$_replace_hangul[$_file_name]['board_imginsert_url']="첨부이미지삽입경로";
		$_code_comment[$_file_name]['board_imginsert_url']="글내용에 이미지 파일 삽입 가능한 경로 출력";
		$_auto_replace[$_file_name]['board_imginsert_url']="Y";

		$_replace_code[$_file_name]['board_imgdel_url']="";
		$_replace_hangul[$_file_name]['board_imgdel_url']="첨부이미지삭제경로";
		$_code_comment[$_file_name]['board_imgdel_url']="해당 이미지 파일 삭제 가능한 경로 출력";
		$_auto_replace[$_file_name]['board_imgdel_url']="Y";

	}
	if($filename == "file_top.php" || $mari_mode == "write@file_frm_exec"){

		$_replace_code[$_file_name]['board_imgfile_form_start']="";
		$_replace_hangul[$_file_name]['board_imgfile_form_start']="이미지첨부폼시작";
		$_code_comment[$_file_name]['board_imgfile_form_start']="글내용에 삽입 가능한 이미지 폼 시작 선언";
		$_auto_replace[$_file_name]['board_imgfile_form_start']="Y";

	}
	if($filename == "file_bottom.php" || $mari_mode == "write@file_frm_exec"){

		$_replace_code[$_file_name]['board_imgfile_form_end']="";
		$_replace_hangul[$_file_name]['board_imgfile_form_end']="이미지첨부폼끝";
		$_code_comment[$_file_name]['board_imgfile_form_end']="글내용에 삽입 가능한 이미지 폼 끝 선언";
		$_auto_replace[$_file_name]['board_imgfile_form_end']="Y";

		$_replace_code[$_file_name]['board_imgfile_up_url']="";
		$_replace_hangul[$_file_name]['board_imgfile_up_url']="이미지업로드경로";
		$_code_comment[$_file_name]['board_imgfile_up_url']="첨부한 이미지 파일을 서버에 업로드 가능한 경로 출력";
		$_auto_replace[$_file_name]['board_imgfile_up_url']="Y";

	}

?>