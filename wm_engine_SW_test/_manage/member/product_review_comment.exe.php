<?PHP

	$m_id = addslashes(trim($_POST['member_id']));
	$ref  = numberOnly($_POST['ref']);

	$member_data = $pdo->assoc("select no, member_id, name from $tbl[member] where member_id='$m_id'");
	$member_no   = $member_data['no'];
	$member_id   = $member_data['member_id'];
	$member_name = addslashes($member_data['name']);
	$content     = strip_tags(addslashes(trim($_POST['content'])));


	checkBlank($ref, '잘못된 경로로 접근하였습니다.');
	checkBlank($member_id, '잘못된 경로로 접근하였습니다.');
	checkBlank($content, '내용은 필수 입력사항입니다.');


	//댓글 입력
	$pdo->query("
		insert into $tbl[review_comment] (ref, name, member_id, member_no, content, ip, reg_date)
		values ('$ref', '$member_name', '$member_id', '$member_no', '$content', '$_SERVER[REMOTE_ADDR]', '$now')
	");


	//본문 댓글 카운트 업데이트
	$pdo->query("update $tbl[review] set total_comment=total_comment+1 where no='$ref'");
	msg('관리자 후기가 등록되었습니다.', 'popup');

?>