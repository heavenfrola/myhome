<?PHP

	include_once $engine_dir.'/_engine/include/common.lib.php';
	include_once $engine_dir."/_engine/include/member.lib.php";
	include $engine_dir."/_engine/include/milage.lib.php";

	$corp_cd = addslashes(trim($_REQUEST['corp_cd']));
	$member_id = addslashes(trim($_REQUEST['member_id']));
	$pwd = trim($_REQUEST['pwd']);
	$name = addslashes(trim($_REQUEST['name']));
	$email = addslashes(trim($_REQUEST['email']));
	$cell = addslashes(trim($_REQUEST['cell']));
	$zip = addslashes(trim($_REQUEST['zip']));
	$addr1 = addslashes(trim($_REQUEST['addr1']));
	$addr2 = addslashes(trim($_REQUEST['addr2']));
	$gender = ($_REQUEST['gender'] == '남') ? '남' : '여';
	$regist_shpcd = addslashes(trim($_REQUEST['regist_shpcd']));

	if($cfg['use_dooson'] != 'Y') $error = '두손ERP 를 이용중이 아닙니다.';
	else if($cfg['erp_interface_param']['dooson_corp_id'] != $corp_cd) $error = 'corp_cd 가 일치하지 않습니다.';
	else if(!$member_id) $error = '회원아이디를 입력해주세요.';
	else if(!$pwd) $error = '비밀번호를 입력해주세요.';
	else if(!$name) $error = '회원이름을 입력해주세요.';
	else if(!$cell) $error = '휴대폰 번호를 입력해주세요.';

	$ck_exists = $pdo->row("select count(*) from $tbl[member] where member_id='$member_id'");
	if($ck_exists > 0) {
		$error = '이미 가입된 회원아이디입니다.';
	}

	if($error) {
		exit(json_encode(array(
			'result' => 'error',
			'message' => $error,
		)));
	}

	$pwd = sql_password($pwd);
	$r = $pdo->query("
		insert into $tbl[member] (member_id, pwd, name, email, cell, zip, addr1, addr2, ip, reg_date, level, sex)
		values ('$member_id', '$pwd', '$name', '$email', '$cell', '$zip', '$addr1', '$addr2', 'doosoun', '$now', 9, '$gender')
	");
	$no = $pdo->lastInsertId();
	setAdvInfoDate($no, 'N', 'N');

	$amember = $pdo->assoc("select * from $tbl[member] where member_id='$member_id'");

	// 가입 적립금 지급
	$milage = ($cfg['milage_join'] > 0 && $no_join_milage == 0) ? $milage = $cfg['milage_join'] : $milage = 0;
	if($milage > 0) {
		ctrlMilage('+', 1, $milage, $amember, '회원 가입 적립금');
	}

	// 회원가입 발급쿠폰
	$today = date('Y-m-d');
	$cpn_load = $pdo->iterator("select * from $tbl[coupon] where (rdate_type=1 or (rdate_type='2' and rstart_date<='$today' and rfinish_date>='$today')) and down_type='C'");
    foreach ($cpn_load as $cpn) {
		if(putCoupon($cpn, $amember) == true) {
			$pdo->query("update $tbl[coupon] set down_hit=down_hit+1 where no='$cpn[no]'");
		}
	}

	// 두손 전송
	if(is_object($erpListener)) {
		$erpListener->regist_shpcd = $regist_shpcd;
		$erpListener->setChangedMember($member_id);
	}

	if($r) {
		exit(json_encode(array(
			'result' => 'success',
		)));
	} else {
		exit(json_encode(array(
			'result' => 'error',
			'message' => $pdo->getError()
		)));
	}

?>