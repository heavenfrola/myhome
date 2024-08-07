<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  계산서/영수증 출력 처리
	' +----------------------------------------------------------------------------------------------+*/

	$content = addslashes(trim($_POST['receipt_footer']));

	if($pdo->row("select count(*) from $tbl[default] where code='receipt_footer'") > 0) {
		$pdo->query("update $tbl[default] set value='$content' where code='receipt_footer'");
	} else {
		$pdo->query("insert into $tbl[default] (code, value) values ('receipt_footer', '$content')");
	}

	msg('', 'reload', 'parent');
?>