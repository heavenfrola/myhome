<?PHP

	$cno = numberOnly($_POST['cno']);
	$sortingArray = explode('|', $_POST['sortingArray']);
	$deleteArray = explode('|', $_POST['deleteArray']);

	$ctype = $pdo->row("select ctype from $tbl[category] where no='$cno' and ctype in (2, 6)");
	$ename = ($ctype == 2) ? 'ebig' : 'mbig';

	foreach($deleteArray as $key => $val) {
		if(!$val) continue;

		$pno = $pdo->row("select pno from $tbl[product_link] where idx='$val'");
		$pdo->query("delete from $tbl[product_link] where idx='$val'");
		$pdo->query("update $tbl[product] set $ename=replace($ename, '@$cno', '') where no='$pno'");
	}

	foreach($sortingArray as $key => $val) {
		$pdo->query("update $tbl[product_link] set sort_big='$key'+1 where idx='$val'");
	}

	exit('OK');

?>