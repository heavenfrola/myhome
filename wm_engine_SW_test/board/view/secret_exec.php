<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  비밀글 패스워드 체크
	' +----------------------------------------------------------------------------------------------+*/
	$no = numberOnly($_POST['no']);
	$db = addslashes($_POST['db']);
	$pwd = addslashes($_POST['pwd']);
	$spwd = addslashes($_POST['spwd']);

	if(!defined("_lib_inc")) exit;
	if(!$no) msg(__lang_common_error_required__, '/');
	$data = $pdo->assoc("select * from `$mari_set[mari_board]` where `no`='$no' and `db`='$db'");
	if(!$data['no']) msg(__lang_common_error_nodata__);

	if($data['secret'] == 'Y' && $data['secret'] == 'D') {
		msg(__lang_board_error_notSecret__);
	}

	$auth = getDataAuth($data);
	if($data['secret'] == 'Y' && $auth != 3) {
		msg(__lang_board_error_memberSecret__);
	}
	if($pwd == ''   && $spwd == '') {
		msg(__lang_member_input_pwd__);
	}

	if($data['secret'] == 'Y') {
		if($data['level'] > 0){ // 답글일경우 부모의 비번일 경우도 허용
			$rep_no = str_replace($data['no'].'|', '', $data['rep_no']);
			$pa_pwd = $pdo->row("select `pwd` from `$mari_set[mari_board]` where `ref`='$data[ref]' and `level`=0");
			if(strcmp(sql_password($pwd), stripslashes($pa_pwd)) == 0) { $Rpd = 'Y'; }
		}
		if(strcmp(sql_password($pwd), stripslashes($data[pwd])) == 0) { $Rpd = 'Y'; }
		if($Rpd != 'Y') msg(__lang_member_error_wrongPwd__);
		$_SESSION['pwd'.$data['no']] = 'OK';
	}
	elseif($data['secret'] == 'D') { // 시안확인 게시판
		if(strcmp($spwd,$data['spwd']) != 0) {
			msg(__lang_member_error_wrongPwd__);
		}
		$_SESSION['spwd'.$data['no']] = 'OK';
	}

	msg(__lang_board_info_correctPwd__, 'reload', 'parent');

?>