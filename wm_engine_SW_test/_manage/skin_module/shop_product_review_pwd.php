<?PHP

	$_replace_code[$_file_name]['password_form_start'] = '<form method="post" action="/main/exec.php" onsubmit="this.target=hid_frame"><input type="hidden" name="exec_file" value="shop/review_edit.php"><input type="hidden" name="exec" value="'.$mode.'"><input type="hidden" name="no" value="'.$rno.'"><input type="hidden" name="rev_idx" value="'.$_POST['rev_idx'].'">';
	$_replace_hangul[$_file_name]['password_form_start'] = '비밀번호폼시작';
	$_auto_replace[$_file_name]['password_form_start'] = 'Y';

	$_replace_code[$_file_name]['password_form_finish'] = '</form>';
	$_replace_hangul[$_file_name]['password_form_finish'] = '비밀번호폼끝';
	$_auto_replace[$_file_name]['password_form_finish'] = 'Y';

	$_replace_code[$_file_name]['password_form_close'] = "closeReviewDetail('$rno', '{$_REQUEST['rev_idx']}');";
	$_replace_hangul[$_file_name]['password_form_close'] = '비밀번호입력창닫기링크';
	$_auto_replace[$_file_name]['password_form_close'] = 'Y';

?>