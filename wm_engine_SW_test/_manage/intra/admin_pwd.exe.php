<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  관리자 아이디 변경 처리
	' +----------------------------------------------------------------------------------------------+*/

	adminCheck(2);

	checkBasic();
	checkBlank($pwd[0],"현재비밀번호를 입력해주세요.");
	checkBlank($pwd[1],"새비밀번호를 입력해주세요.");
	checkBlank($pwd[2],"새비밀번호를 한번더 입력해주세요.");

	$data=get_info($tbl[mng],"level",2);

	if(strlen($data[pwd]) < 64) {
		$data[pwd]=sql_password($data[pwd]);
	}

	if($data[pwd]!=sql_password($pwd[0])) msg("현재 비밀번호가 다릅니다");
	if($pwd[1]!=$pwd[2]) msg("새 비밀번호와 확인 비밀번호가 다릅니다");
	if($pwd[1] == sql_password($admin_default_pwd)) msg("다른 비밀번호를 설정해주시기 바랍니다");

	if($admin_id2) $asql=", `admin_id`='$admin_id2'";

	$pwd[1]=sql_password($pwd[1]);

	$pdo->query("update `$tbl[mng]` set `pwd`='$pwd[1]' $asql where `level`='2'");

	if($_SESSION['admin_mng_pwd_needed'] == "Y") $_SESSION['admin_mng_pwd_needed']="";
	msg("변경되었습니다","./","parent");

?>