<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  상품후기 상세보기
	' +----------------------------------------------------------------------------------------------+*/

	$_replace_code[$_file_name]['review_points'] = reviewStar($_prd_board_icon['star'], $data);

	$_tmp = '';
	$_line = getModuleContent('review_img_list');
	foreach($attach_list as $fn) {
		$_tmp .= lineValues('review_img_list', $_line, array('img'=>$fn));
	}
	$_replace_code[$_file_name]['review_img_list'] = listContentSetting($_tmp, $_line);
	unset($_tmp, $_line);

	// 이전/이후 상품평 주소
	$rev_idx = numberOnly($_GET['rev_idx']);
	if($rev_idx > 0 && $_SESSION['rev_qry']) {
		$rev_cnt = $pdo->row(preg_replace('/^.* from/', 'select count(*) from', $_SESSION['rev_qry']));
		$prev_idx = $rev_idx+1;
		$next_idx = $rev_idx-1;
		if($rev_cnt-$prev_idx > -1) $prev = $pdo->assoc($_SESSION['rev_qry'].' limit '.($rev_cnt-$prev_idx).', 1');
		$next = $pdo->assoc($_SESSION['rev_qry'].' limit '.($rev_cnt-$next_idx).', 1');
		$a = ($rev_cnt-$prev_idx);
		$b = ($rev_cnt-$next_idx);
	}
	$_replace_code[$_file_name]['review_prd_prev_url'] = ($prev['no'] > 0) ? "openReviewDetail($prev[no], $prev_idx, true)" : "";
	$_replace_code[$_file_name]['review_prd_next_url'] = ($next['no'] > 0) ? "openReviewDetail($next[no], $next_idx, true)" : "";

	$ra = reviewAuth('product_review_auth');

	// 댓글 작성 폼
	$_replace_code[$_file_name]['review_comment_form_start'] = '<form method="post" action="'.$root_url.'/main/exec.php" onSubmit="return checkRevCmtAjax(this)"><input type="hidden" name="exec_file" value="shop/review_comment.exe.php"><input type="hidden" name="no" value="'.$data['no'].'"><input type="hidden" name="from_ajax" value="Y">';
	$_replace_code[$_file_name]['review_comment_form_end'] = "</form><script>ra='{$ra}';</script>";

	// 댓글 목록
	$_line2 = getModuleContent('product_review_comment_list');
	$_tmp2 = '';
	$review = $data;
	while($comment = reviewCommentList()){
		$comment['del_link'] = $comment['del_link_ajax'];
		$_tmp2 .= lineValues('product_review_comment_list', $_line2, $comment, 'common_module');
	}
	$_replace_code['common_module']['product_review_comment_list'] = listContentSetting($_tmp2, $_line2);
	unset($review, $_tmp2);

?>