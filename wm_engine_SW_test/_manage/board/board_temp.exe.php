<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  게시판 추가항목 처리
	' +----------------------------------------------------------------------------------------------+*/

	addField('mari_config', 'tmp_name', 'text not null default ""');
	for($i = 4; $i <= $_POST['board_add_temp']; $i++) {
		addField('mari_board', 'temp'.$i, 'varchar(200) not null default ""');
	}

	$no = numberOnly($_POST['no']);
	if(!$pdo->row("select no from mari_config where no='$no'")) {
		msg('존재하지 않는 게시판입니다.');
	}

	$tempp = array();
	for($i = 0; $i <= $cfg['board_add_temp']; $i++) {
		$tempp['temp'.$i]  = trim($_POST['temp'.$i]);
	}
	$tmp_name = addslashes(serialize($tempp));
	$pdo->query("update `mari_config` set `tmp_name`='$tmp_name' where `no`='$no'");

	msg('적용되었습니다.', 'reload', 'parent');

?>