<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  사원 등록/관리 처리
	' +----------------------------------------------------------------------------------------------+*/

	adminCheck(2);

	checkBasic();

	$no = numberOnly($_POST['no']);
	$exec = $_POST['exec'];
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
	if(!$admin_id) $admin_id = addslashes($_POST['admin_id']);
	$level = numberOnly($_POST['level']);

	if(preg_match('/[^a-zA-Z-0-9@._-]/', $admin_id)) {
		msg('관리자 아이디는 영문,숫자, 공백 및 일부 특수문자(-,_)만 사용하실수 있습니다');
	}

	if($no) {
		$data=get_info($tbl[mng],"no",$no);
		if(!$data[no]) msg("존재하지 않는 자료입니다");
		if($data[level]==1) msg("사원이 아닙니다");

        if (file_exists($engine_dir.'/_engine/include/account/ssoLogin.inc.php') == true) {
            if ($data['level'] == '2' && $level != '2') {
                $wec = new weagleEyeClient($_we, 'account');
                $owner = json_decode($wec->call('getAccoutOwner'));
                if ($owner->member_id == $data['admin_id']) {
                    msg('계정 소유자의 등급을 변경할 수 없습니다.');
                }
            }
        }
        if ($admin['no'] == $no) {
            if ($data['level'] != $level) {
                msg('현재 접속중인 아이디의 등급은 변경하실 수 없습니다.');
            }
        }
	}

	foreach($_POST as $key => $val) {
		$_POST[$key] = ${$key} = addslashes($val);
	}

	$is_wm = file_exists($engine_dir.'/_engine/include/account/ssoLogin.inc.php');

	if($exec=="delete") {
		if($data['level'] < 3) msg("최고관리자는 삭제가 불가능합니다");

		if($is_wm) {
			include $engine_dir.'/_engine/include/account/staffDelete.exe.php';
		}

		$sql="delete from `$tbl[mng]` where `no`='$data[no]' limit 1";
		$pdo->query($sql);

		msg("삭제하였습니다","reload","parent");
	}

    $asql = $asql1 = $asql2 = '';

	if(file_exists($engine_dir.'/_engine/include/account/ssoLogin.inc.php')) {
		$admin_pwd = hash('sha256', $root_pwd);
		$result = $wec->comm("http://redirect.wisa.co.kr/pwck/".$admin_pwd);
		if($result != 'true') {
			msg("최고관리자 비밀번호를 잘못 입력했습니다.\\n다시 확인해주세요.");
		}
	} else {
		$admin_pwd = sql_password($root_pwd);
		if($pdo->row("select count(*) from $tbl[mng] where level in (1,2) and pwd='$admin_pwd'") < 1) {
			msg("최고관리자 비밀번호를 잘못 입력했습니다.\\n다시 확인해주세요.");
		}

		if(!$no) checkBlank($password, "사원 비밀번호를 입력해주세요.");
		if($password) {
            if (preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[$@$!%*#?&])[A-Za-z\d$@$!%*#?&]{8,}$/', $password) == false) {
                msg('비밀번호는 영문, 숫자, 특수문자가 조합하여 8자 이상으로 입력해주세요.');
            }
            if (preg_match('/(.)\1\1\1/', $password) == true) {
                msg('비밀번호는 동일한 문자를 4회 이상 연속 입력할 수 없습니다.');
            }
			$pwd = sql_password($password);
			$asql .= ", `pwd`='$pwd'";

            // 비밀번호 기간 만료
            if ($scfg->comp('mng_pass_expire') == true && (isset($data['pwd']) == false || $pwd != $data['pwd'])) {
                $expire_pwd = date('Y-m-d', strtotime("+{$cfg['mng_pass_expire']} months"));
                $asql .= ", expire_pwd='$expire_pwd'";
                $asql1 .= ", expire_pwd";
                $asql2 .= ", '$expire_pwd'";
            }
		}
	}

	checkBlank($name, "성명을 입력해주세요.");

	if($team){ // 팀소속
		list($team1, $team2)=explode("/", $team);
	}
	if($birth_y && $birth_m && $birth_d){ // 생년월일
		$birth=$birth_y."-".$birth_m."-".$birth_d;
	}

	if(!$level) $level = 3;
	if($admin['level'] > 2) $level = $admin['level'];

	if($cfg['staffs_access_limit']=="Y") {
		if($_POST['access_lock']!="Y") {
			$asql .= ", `access_lock`='N', `access_count`=0";
		}else {
			$asql .= ", `access_lock`='Y', `access_count`='$cfg[access_lock]'";
		}
	}

	if(!fieldExist($tbl['mng'], 'partner_no')) {
		addField($tbl['mng'], 'partner_no', 'varchar(20) not null default ""');
		$pdo->query("alter table $tbl[mng] change level level char(1) not null default '3'");
		$pdo->query("alter table $tbl[mng] add index partner_no (partner_no)");
	}

	if($level != 4) {
		$partner_no = 0;
	}

	if($data['no']) {
		if(file_exists($engine_dir.'/_engine/include/account/ssoLogin.inc.php')) {
			$wec = new weagleEyeClient($_we, 'account');
			$r = $wec->call('mngEdt', array(
				'mng_id' => $admin_id,
				'mng_lv' => $level
			));
		}
		$sql = "update `$tbl[mng]` set `name`='$name', `team1`='$team1', `team2`='$team2', `position`='$position', `birth`='$birth', `phone`='$phone', `cell`='$cell', `email`='$email', `address`='$address', `ver`='$is_wm', level='$level', partner_no ='$partner_no' $asql where `no`='$data[no]'";
		$pdo->query($sql);

        // 등급 변경 체크
        if ($data['level'] != $level) {
            $pdo->query("
                insert into {$tbl['mng_auth_log']}
                (admin_no, admin_id, target_no, target_id, category, auth1, auth2, auth_d1, auth_d2, remote_addr, reg_date)
                values
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, now())
            ", array(
                $admin['no'], $admin['admin_id'], $data['no'], $data['admin_id'], 'level', $data['level'], $level, '', '', $_SERVER['REMOTE_ADDR']
            ));
        }

		msg("수정하였습니다","./?body=intra@staffs_edt","parent");
	}else{
		if(file_exists($engine_dir.'/_engine/include/account/ssoLogin.inc.php')) {
			include_once $engine_dir.'/../weagleEye/weagleEyeClient.class.php';
			$wec = new weagleEyeClient($_we, 'account');

			$wec->queue('mngConv', $wec->config['account_idx'], $level, $data['admin_id'], $admin_id);
			$wec->send_clean();
			$result = mb_convert_encoding($wec->result, _BASE_CHARSET_, array('utf8', 'euckr'));
			if($result != 'OK') {
				alert($result);
				exit;
			}
		}

		checkBlank($admin_id, "아이디를 입력해주세요.");
		$check = $pdo->assoc("select `no` from `$tbl[mng]` where `admin_id`='$admin_id'");
		if($check[no]) {
			msg("$admin_id - 사용할 수 없는 아이디입니다");
		}

        if ($level == '3') {
            $asql1 .= ", auth";
            $asql2 .= ", '@main'";
        }

		$sql = "INSERT INTO `$tbl[mng]` (`level`, `admin_id`, `pwd`, `name`, `team1`, `team2`, `position`, `birth`, `phone`, `cell`, `email`, `address`, `reg_date`,`ver` ,`partner_no` $asql1) VALUES ('$level', '$admin_id', '$pwd', '$name', '$team1', '$team2', '$position', '$birth', '$phone', '$cell', '$email', '$address', '$now', '$is_wm', '$partner_no' $asql2)";
		$pdo->query($sql);

		//로그인 제한된 아이디처리
		if($cfg['staffs_access_limit']=="Y") {
			$pdo->query("update $tbl[mng_log] set login_result='2' where member_id='$admin_id' and login_result='9'");
		}

		msg("등록되었습니다","reload","parent");
	}

?>