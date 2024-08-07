<?PHP

	$ssid = addslashes($_GET['ssid']);
	$sdata = $db_session_handler->parse($ssid);
	if(empty($sdata['admin_no']) == true) {
		msg('접근 권한이 없습니다.');
	}

	$mno = numberOnly($_GET['mno']);
	$mid = addslashes($_GET['mid']);
	$amember = $pdo->assoc("select * from $tbl[member] where no='$mno' and member_id='$mid'");

	if($_SESSION['guest_no']) {
		$pdo->query("update {$tbl['cart']} set member_no='$mno', guest_no='' where guest_no='{$_SESSION['guest_no']}'");
		unset($_SESSION['guest_no']);
	}

	$_SESSION['member_no'] = $amember['no'];
	$_SESSION['m_member_id'] = $amember['member_id'];

    msg('', $root_url);

?>