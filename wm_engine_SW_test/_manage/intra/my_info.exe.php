<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  내정보수정 처리
	' +----------------------------------------------------------------------------------------------+*/

	checkBasic();

	if(!$admin['no']) msg('필수정보가 없습니다');

	$name = addslashes($_POST['name']);
	$birth_y = numberOnly($_POST['birth_y']);
	$birth_m = numberOnly($_POST['birth_m']);
	$birth_d = numberOnly($_POST['birth_d']);
	$phone = addslashes($_POST['phone']);
	$cell = addslashes($_POST['cell']);
	$email = addslashes($_POST['email']);
	$address = addslashes($_POST['address']);
	$password  = addslashes($_POST['password']);
	$team = addslashes($_POST['team']);
	$team1 = addslashes($_POST['team1']);
	$team2 = addslashes($_POST['team2']);
	$position = addslashes($_POST['position']);

	checkBlank($name, "성명을 입력해주세요.");

	$addq = '';
	if(!file_exists($engine_dir.'/_engine/include/account/ssoLogin.inc.php') && $password) {
        if (preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[$@$!%*#?&])[A-Za-z\d$@$!%*#?&]{8,}$/', $password) == false) {
            msg('비밀번호는 영문, 숫자, 특수문자가 조합하여 8자 이상으로 입력해주세요.');
        }
        if (preg_match('/(.)\1\1\1/', $password) == true) {
            msg('비밀번호는 동일한 문자를 4회 이상 연속 입력할 수 없습니다.');
        }
		$password = sql_password($password);
		$addq = ",`pwd`='$password'";

        // 비밀번호 기간 만료
        if ($scfg->comp('mng_pass_expire') == true && $password != $admin['pwd']) {
            $expire_pwd = date('Y-m-d', strtotime("+{$cfg['mng_pass_expire']} months"));
            $addq .= ", expire_pwd='$expire_pwd'";
        }

		// 비밀번호 변경 후 현재 세션 외의 다른 세션 만료 처리(독립형)
		$pdo->query("delete from {$tbl['session']} where admin_no='{$admin['no']}' and session_id!='".session_id()."'");
	}

	if($admin['level'] == 1 || $admin['level'] == 2){ // 관리자는 자신의 소속과 직급 수정 가능
		if($team){ // 팀소속
			list($team1, $team2)=explode("/", $team);
		}
		$addq .= ", `team1`='$team1', `team2`='$team2'";
		$addq .= ", `position`='$position'";
	}
	if($birth_y && $birth_m && $birth_d){ // 생년월일
		$birth=$birth_y."-".$birth_m."-".$birth_d;
	}

	$sql="update `$tbl[mng]` set `name`='$name', `birth`='$birth', `phone`='$phone', `cell`='$cell', `email`='$email', `address`='$address' $addq where `no`='$admin[no]'";
	$pdo->query($sql);
	msg("수정하였습니다","reload","parent");

?>