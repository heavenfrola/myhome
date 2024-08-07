<?PHP

	$no = numberOnly($_POST['no']);
	$exec = $_POST['exec'];

	if($exec == "getOftenComment") {
		header('Content-type:application/json; charset='._BASE_CHARSET_.';');

		$result = $pdo->row("select `content` from `$tbl[often_comment]` where `no`='$no'");
		if($_POST['use_editor'] == 'Y') {
			$result = nl2br($result);
		}
		exit(json_encode(array('result'=>stripslashes($result))));
	}

	if($exec == "delete") {
		$check_pno = implode(',', numberOnly($_POST['check_pno']));
		$pdo->query("delete from `$tbl[often_comment]` where `no` in ($check_pno)");

		msg($pdo->lastRowCount().'개의 댓글이 삭제되었습니다.', 'reload', 'parent');
	}

	$cate = addslashes(trim($_POST['cate']));
	$title  = addslashes($_POST['title']);
	$content  = addslashes($_POST['content']);

	checkBlank($cate, '분류가 선택되지 않습니다.');
	checkBlank($title, '제목은 필수 입력사항입니다.');
	checkBlank($content, '내용은 필수 입력사항입니다.');

	if($no) {
		$pdo->query("update $tbl[often_comment] set title='$title', content='$content', cate='$cate' where no='$no'");
	} else {
	   	$pdo->query("insert into $tbl[often_comment] (title, content, cate, reg_date) values ('$title', '$content', '$cate', '$now')");
	}
	msg('자주쓰는 댓글이 등록되었습니다.', 'popup');

?>