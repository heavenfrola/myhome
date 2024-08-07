<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  관리자 로그인 쿠키 처리
	' +----------------------------------------------------------------------------------------------+*/

	define('_wisa_manage_edit_', true);

	include_once $engine_dir."/_engine/include/common.lib.php";

	if($_POST['exec'] == 'delete') {
		$_SESSION['admin_no']="";
		setcookie('session_id', '', 0, '/');
		msg("","/");
		exit;
	}

	if($session_id) {
		if($_COOKIE['session_id']) setcookie('session_id', '', 0, '/');
		setcookie('session_id', $session_id, time()+(60*60*24), '/');
	}

	$query_string = urldecode($_POST['query_string']);
	if(!$query_string) {
		$param = addslashes(strip_tags($_POST['param']));
		if(!$param) $param = 'main@main';
		$query_string = 'body='.$param;
	}

    if ($scfg->comp('use_prevent_dup_admin', 'Y') == true) {
        $db_session_handler->setDuplicate('admin_no');
    }

	header("Location: /_manage/?$query_string");

?>