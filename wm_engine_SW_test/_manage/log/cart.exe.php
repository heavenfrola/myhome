<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  장바구니 삭제
	' +----------------------------------------------------------------------------------------------+*/

	$check_pno = implode(',', $_POST['check_pno']);
	if(!$check_pno) {
		msg('삭제할 장바구니를 선택해 주세요.');
	}

	switch($_POST['exec']) {
		case 'delete' :
			$pdo->query("delete from $tbl[cart] where no in ($check_pno)");
			msg('', 'reload', 'parent');
		break;
		case 'deleteWish' :
			$pdo->query("delete from $tbl[wish] where no in ($check_pno)");
			msg('', 'reload', 'parent');
		break;
	}

?>