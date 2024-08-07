<?PHP

	if($admin['level'] != 4) {
		msg('입점업체 관리자만 접속할 수 있습니다.');
	}

	$pwd = trim($_POST['pwd']);
	$pwd_confirm = trim($_POST['pwd_confirm']);
	$name = addslashes(trim($_POST['name']));
	$cell = addslashes(trim($_POST['cell']));
	$phone = addslashes(trim($_POST['phone']));
	$email = addslashes(trim($_POST['email']));
	$address = addslashes(trim($_POST['address']));
	$birth = implode('-', numberOnly($_POST['birth']));

	checkBlank($name, '이름을 입력해주세요.');
	checkBlank($cell, '휴대폰번호를 입력해주세요.');
	checkBlank($email, '이메일 주소를 입력해주세요.');

	$asql = '';
	if(empty($pwd) == false) { // 비밀번호 변경
        if (preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[$@$!%*#?&])[A-Za-z\d$@$!%*#?&]{8,}$/', $pwd) == false) {
            msg('비밀번호는 영문, 숫자, 특수문자가 조합하여 8자 이상으로 입력해주세요.');
        }
        if (preg_match('/(.)\1\1\1/', $pwd) == true) {
            msg('비밀번호는 동일한 문자를 4회 이상 연속 입력할 수 없습니다.');
        }
		if(strcmp($pwd, $pwd_confirm) !== 0) {
			msg('확인 패스워드가 일치하지 않습니다.');
		}
		$pwd = sql_password($pwd);
		$asql .= ", pwd='$pwd'";

        // 비밀번호 기간 만료
        if ($scfg->comp('mng_pass_expire') == true && $pwd != $admin['pwd']) {
            $expire_pwd = date('Y-m-d', strtotime("+{$cfg['mng_pass_expire']} months"));
            $asql .= ", expire_pwd='$expire_pwd'";
        }
	}

	$pdo->query("
		update {$tbl['mng']} set
			name='$name', cell='$cell', phone='$phone', email='$email', address='$address', birth='$birth'
			$asql
		where no='{$admin['no']}'
	");

	msg('관리자 정보가 수정되었습니다.', 'reload', 'parent');

?>