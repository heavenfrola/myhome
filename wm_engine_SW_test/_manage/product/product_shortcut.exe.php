<?PHP

	$scno = numberOnly($_POST['scno']);
	$exec = $_POST['exec'];

	switch($exec) {
		case 'remove' :
			$pdo->query("delete from $tbl[product] where no='$scno' and wm_sc>0");
			exit;
		break;
	}

?>