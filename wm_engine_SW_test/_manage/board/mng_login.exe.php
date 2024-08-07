<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  게시판 관리 - 게시판 관리자 로그인 처리
	' +----------------------------------------------------------------------------------------------+*/

	// 관리자 세션 검증
	$ssid = addslashes($_GET['ssid']);
	$mng_no = numberOnly($_GET['mng_no']);

	$session = $db_session_handler->parse($ssid);
	if (empty($session['admin_no']) == true) msg('비정상적인 접속이거나 로그인이 해제 되었습니다.', 'close');

	// 로그인 처리
	if(!$mng_no) {
		msg("필수값이 없습니다", 'close');
	}

	$data=get_info($tbl[member],"no",$mng_no);

	$_SESSION['member_no']=$data['no'];
	$_SESSION['m_member_id']=$data['member_id'];

	msg('', $root_url);

?>