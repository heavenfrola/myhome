<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  상품후기 상세보기
	' +----------------------------------------------------------------------------------------------+*/

	$_replace_code[$_file_name]['review_title'] = $data['title'];
	$_replace_hangul[$_file_name]['review_title'] = '글제목';
	$_code_comment[$_file_name]['review_title'] = '상품평 제목을 출력합니다.';
	$_auto_replace[$_file_name]['review_title'] = 'Y';

	$_replace_code[$_file_name]['review_content'] = $data['content'];
	$_replace_hangul[$_file_name]['review_content'] = '글내용';
	$_code_comment[$_file_name]['review_content'] = '상품평 본문을 출력합니다.';
	$_auto_replace[$_file_name]['review_content'] = 'Y';

	$_replace_code[$_file_name]['review_name'] = $data['name'];
	$_replace_hangul[$_file_name]['review_name'] = '작성자명';
	$_code_comment[$_file_name]['review_name'] = '상품평의 작성자명을 출력합니다.';
	$_auto_replace[$_file_name]['review_name'] = 'Y';

	$_replace_code[$_file_name]['review_regdate'] = $data['reg_date'];
	$_replace_hangul[$_file_name]['review_regdate'] = '작성일시';
	$_code_comment[$_file_name]['review_regdate'] = '상품평 본문을 출력합니다.';
	$_auto_replace[$_file_name]['review_regdate'] = 'Y';

	$_replace_code[$_file_name]['review_points_num'] = $data['rev_pt'];
	$_replace_hangul[$_file_name]['review_points_num'] = '상품평점수(텍스트)';
	$_code_comment[$_file_name]['review_points_num'] = '상품평 점수를 출력합니다.';
	$_auto_replace[$_file_name]['review_points_num'] = 'Y';

	$_replace_code[$_file_name]['review_points'] = '';
	$_replace_hangul[$_file_name]['review_points'] = '상품평점수';
	$_code_comment[$_file_name]['review_points'] = '상품평 점수 이미지를 출력합니다.';
	$_auto_replace[$_file_name]['review_points'] = 'Y';

	$_replace_code[$_file_name]['review_img_list'] = '';
	$_replace_hangul[$_file_name]['review_img_list'] = '상품평첨부이미지목록';
	$_code_comment[$_file_name]['review_img_list'] = '상품평 및 본문에 삽입된 이미지목록을 출력합니다.';
	$_replace_datavals[$_file_name]['review_img_list'] = '첨부이미지:img;';

	$_replace_code[$_file_name]['review_img_cnt'] = count($attach_list);
	$_replace_hangul[$_file_name]['review_img_cnt'] = '첨부이미지개수';
	$_code_comment[$_file_name]['review_img_cnt'] = '본문 삽입 이미지를 포함한 총 첨부 이미지의 개수를 출력합니다.';
	$_auto_replace[$_file_name]['review_img_cnt'] = 'Y';

	$_replace_code[$_file_name]['review_edit_link'] = $data['edit_link_layer'];
	$_replace_hangul[$_file_name]['review_edit_link'] = '상품평수정링크태그(레이어)';
	$_code_comment[$_file_name]['review_edit_link'] = '상품평 수정링크를 출력합니다.';
	$_auto_replace[$_file_name]['review_edit_link'] = 'Y';

	$_replace_code[$_file_name]['review_del_link'] = $data['del_link'];
	$_replace_hangul[$_file_name]['review_del_link'] = '상품평삭제링크태그';
	$_code_comment[$_file_name]['review_del_link'] = '상품평 삭제링크를 출력합니다.';
	$_auto_replace[$_file_name]['review_del_link'] = 'Y';

	$_replace_code[$_file_name]['review_recommend_y'] = number_format($data['recommend_y']);
	$_replace_hangul[$_file_name]['review_recommend_y'] = '상품평추천수';
	$_code_comment[$_file_name]['review_recommend_y'] = '상품평이 추천된 숫자가 표시됩니다.';
	$_auto_replace[$_file_name]['review_recommend_y'] = 'Y';

	$_replace_code[$_file_name]['review_recommend_n'] = number_format($data['recommend_n']);
	$_replace_hangul[$_file_name]['review_recommend_n'] = '상품평비추천수';
	$_code_comment[$_file_name]['review_recommend_n'] = '상품평이 비추천된 숫자가 표시됩니다.';
	$_auto_replace[$_file_name]['review_recommend_n'] = 'Y';

	$_replace_code[$_file_name]['review_recommend_y_url'] = $data['recommend_link'];
	$_replace_hangul[$_file_name]['review_recommend_y_url'] = '상품평추천하기링크';
	$_code_comment[$_file_name]['review_recommend_y_url'] = '상품평 추천하기 링크를 출력합니다.';
	$_auto_replace[$_file_name]['review_recommend_y_url'] = 'Y';

	$_replace_code[$_file_name]['review_recommend_n_url'] = $data['disrecommend_link'];
	$_replace_hangul[$_file_name]['review_recommend_n_url'] = '상품평비추천링크';
	$_code_comment[$_file_name]['review_recommend_n_url'] = '상품평 비추천하기 링크를 출력합니다.';
	$_auto_replace[$_file_name]['review_recommend_n_url'] = 'Y';

	$_replace_code[$_file_name]['review_img_cnt'] = count($attach_list);
	$_replace_hangul[$_file_name]['review_img_cnt'] = '첨부이미지개수';
	$_code_comment[$_file_name]['review_img_cnt'] = '본문 삽입 이미지를 포함한 총 첨부 이미지의 개수를 출력합니다.';
	$_auto_replace[$_file_name]['review_img_cnt'] = 'Y';

	$_replace_code[$_file_name]['review_prd_name'] = $prd['name'];
	$_replace_hangul[$_file_name]['review_prd_name'] = '관련상품명';
	$_code_comment[$_file_name]['review_prd_name'] = '상품평 본문을 출력합니다.';
	$_auto_replace[$_file_name]['review_prd_name'] = 'Y';

	$_replace_code[$_file_name]['review_prd_img'] = $prd['prd_img'];
	$_replace_hangul[$_file_name]['review_prd_img'] = '관련상품이미지';
	$_code_comment[$_file_name]['review_prd_img'] = '상품평 본문을 출력합니다.';
	$_auto_replace[$_file_name]['review_prd_img'] = 'Y';

	$_replace_code[$_file_name]['review_prd_link'] = $prd['link'];
	$_replace_hangul[$_file_name]['review_prd_link'] = '관련상품링크';
	$_code_comment[$_file_name]['review_prd_link'] = '상품평 본문을 출력합니다.';
	$_auto_replace[$_file_name]['review_prd_link'] = 'Y';

	$_replace_code[$_file_name]['review_prd_prev_url'] = '';
	$_replace_hangul[$_file_name]['review_prd_prev_url'] = '이전상품평주소';
	$_code_comment[$_file_name]['review_prd_prev_url'] = '상품별 후기 출력시 이전 상품평 주소를 출력합니다.';
	$_auto_replace[$_file_name]['review_prd_prev_url'] = 'Y';

	$_replace_code[$_file_name]['review_prd_next_url'] = '';
	$_replace_hangul[$_file_name]['review_prd_next_url'] = '다음상품평주소';
	$_code_comment[$_file_name]['review_prd_next_url'] = '상품별 후기 출력시 다음 상품평 주소를 출력합니다.';
	$_auto_replace[$_file_name]['review_prd_next_url'] = 'Y';

	$_replace_code[$_file_name]['review_comment_form_start'] = '';
	$_replace_hangul[$_file_name]['review_comment_form_start'] = '댓글작성폼시작';
	$_code_comment[$_file_name]['review_comment_form_start'] = '상품평댓글을 작성하기위한 폼 시작 부분';
	$_auto_replace[$_file_name]['review_comment_form_start'] = 'Y';

	$_replace_code[$_file_name]['review_comment_form_end'] = '';
	$_replace_hangul[$_file_name]['review_comment_form_end'] = '댓글작성폼끝';
	$_code_comment[$_file_name]['review_comment_form_end'] = '상품평댓글을 작성하기위한 폼 끝';
	$_auto_replace[$_file_name]['review_comment_form_end'] = 'Y';

	$_replace_code[$_file_name]['review_comment_writable'] = ($cfg['product_review_comment'] == '2' || $admin['no'] > 0 || $member['level'] == 1) ? 'Y' : '';
	$_replace_hangul[$_file_name]['review_comment_writable'] = '댓글작성권한';
	$_code_comment[$_file_name]['review_comment_writable'] = '상품평 코멘트 작성이 가능할 경우 Y를 출력합니다.';
	$_auto_replace[$_file_name]['review_comment_writable'] = 'Y';

	$_replace_code[$_file_name]['review_layer_close_url'] = 'return closeReviewDetail();';
	$_replace_hangul[$_file_name]['review_layer_close_url'] = '상품후기닫기링크';
	$_code_comment[$_file_name]['review_layer_close_url'] = '상품후기 레이어를 닫는 자바스크립트를 출력합니다.';
	$_auto_replace[$_file_name]['review_layer_close_url'] = 'Y';

?>