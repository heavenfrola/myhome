<?PHP

	include_once $engine_dir.'/_engine/include/common.lib.php';
	include_once $engine_dir.'/_engine/include/member.lib.php';

	$nm = $_POST['nm'];
	$err = array();

	// 패스워드 체크
	if($nm == 'all' || $nm == 'pwd1') {
		$err['pwd1'] = '';

        if ($_POST['pwd'][0] || $_POST['pwd'][1]) {
            checkPwd(array(
                $_POST['pwd'][0],
                $_POST['pwd'][0],
            ));
            $err['pwd1'] = $ajax_return_message;
        }
	}
	if($nm == 'all' || $nm == 'pwd1' || $nm == 'pwd2') {
		$err['pwd2'] = '';

		if(empty($_POST['pwd'][0]) == false) {
			if(empty($_POST['pwd'][1]) == true) $err['pwd2'] = __lang_member_input_cpwd__;
			else if(strcmp($_POST['pwd'][0], $_POST['pwd'][1]) != 0) $err['pwd2'] = __lang_member_error_cpwd__;
		}

	}

	// 아이디 체크
	if($nm == 'all' || $nm == 'member_id') {
		$err['member_id'] = '';

		$ret = checkID($_POST['member_id']);
		if($ret == 1) $err['member_id'] = __lang_member_info_memberid5__;
		if($ret == 2) $err['member_id'] = __lang_member_error_existsid__;
		if($ret == 4) $err['member_id'] = __lang_member_info_memberid2__;
		if($ret == 5) $err['member_id'] = __lang_member_info_memberid4__;
		if($ret == 6) $err['member_id'] = __lang_member_info_memberid5__;
		if($ret == 7) $err['member_id'] = __lang_member_info_memberid6__;
		if($ret == 8) $err['member_id'] = __lang_member_info_memberid7__;
		if($ret == 9) $err['member_id'] = __lang_member_info_memberid8__;
	}

	// 이름 체크
    if (($nm == 'all' || $nm == 'first_name' || $nm == 'family_name') && (isset($_POST['first_name']) || isset($_POST['family_name']))) {
        $err['name'] = '';
        if (empty($_POST['first_name']) == true || empty($_POST['family_name']) == true) {
            $err['name'] = __lang_member_input_name__;
        }
        $_POST['name'] = trim($_POST['first_name'].' '.$_POST['familay_name']);
    }
	if(($nm == 'all' || $nm == 'name') && isset($_POST['name'])) {
        $err['name'] = '';
		if(empty($_POST['name']) == true) $err['name'] = __lang_member_input_name__;
	}

	// 닉네임 체크
	if($nm == 'all' || $nm == 'nick') {
		$err['nick'] = '';

		if(strlen($_POST['nick']) > 0) {
			if(strlen($_POST['nick']) > 24) $err['nick'] = __lang_member_error_nick__;
			else if(checkNameFilter($_POST['nick']) == false) $err['nick'] = __lang_member_error_nick2__;
			else if($pdo->row("select * from {$tbl['member']} where nick='{$_POST['nick']}' and no!='{$member['no']}'") > 0) $err['nick'] = __lang_member_error_existsNick__;
		}

		if($cfg['member_join_nickname'] == 'Y' && $cfg['nickname_essential'] == 'Y') {
			if(empty($_POST['nick']) == true) $err['nick'] = __lang_member_input_nickname__;
		}
	}

	// 성별 체크
	if($nm == 'all' || $nm == 'sex') {
		$err['sex'] = '';

		if($cfg['join_sex_use'] == 'Y' && $cfg['member_join_sex'] == 'Y') {
			if(empty($_POST['sex']) == true) $err['sex'] = __lang_member_input_gender__;
		}
	}

	// 생년월일 체크
	if($nm == 'all' || $nm == 'birth') {
		$err['birth'] = '';

		if($cfg['join_birth_use'] == 'Y' && $cfg['member_join_birth'] == 'Y') {
			if(empty($_POST['birth1']) == true || empty($_POST['birth2']) == true || empty($_POST['birth3']) == true) $err['birth'] = __lang_member_input_birthday__;
		}
	}

	// 주소 체크
	if($nm == 'all' || $nm == 'addr') {
		$err['addr'] = $err['addr1'] = $err['addr2'] = $err['zip'] = '';

		if($cfg['join_addr_use'] == 'Y' && $cfg['member_join_addr'] == 'Y') {
			if(empty($_POST['addr2']) == true) $err['addr'] = $err['addr2'] =__lang_member_input_addr2__;
			if(empty($_POST['addr1']) == true) $err['addr'] = $err['addr1'] =__lang_member_input_addr1__;
			if(empty($_POST['zip']) == true) $err['addr'] = $err['zip'] =__lang_member_input_zipcode__;
		}
	}

	// 휴대폰번호 체크
	if($nm == 'all' || $nm == 'cell') {
		$err['cell'] = '';

		$cell = numberOnly($_POST['cell']);
		if(strlen($cell) > 0) {
			if($cfg['join_check_cell'] == 'Y') {
				if($pdo->row("select * from {$tbl['member']} where cell='{$cell}' and member_id!='{$member['member_id']}'") > 0) $err['cell'] = __lang_member_error_existsCell__;
			}
		} else {
			$err['cell'] = __lang_member_input_cell__;
		}
	}

	// 이메일주소 체크
	if($nm == 'all' || $nm == 'email') {
		$err['email'] = '';

		$email = $_POST['email'];
		if($_POST['email'] && $_POST['email2']) $email = $_POST['email1'].'@'.$_POST['email2'];

		if(strlen($email) > 0) {
			if(preg_match('/^[0-9a-zA-Z]([-.]?[0-9a-zA-Z_])*@[0-9a-zA-Z]([-.]?[0-9a-zA-Z_])*.[a-zA-Z]{2,3}$/i', $email) == false) {
				$err['email'] = __lang_member_input_email2__;
			}
			if($cfg['join_check_email'] == 'Y') {
				if($pdo->row("select * from {$tbl['member']} where email='{$email}' and member_id!='{$member['member_id']}'") > 0) $err['email'] = __lang_member_error_existsEmail__;
			}
		}
		if($cfg['member_join_id_email'] != 'A') {
			if(empty($email) == true) $err['email'] = __lang_member_input_email__;
		}
	}


	header('Content-type:application/json; charset=utf8;');
	exit(json_encode($err));

?>