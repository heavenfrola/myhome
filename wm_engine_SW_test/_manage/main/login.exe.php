<?PHP

	checkBasic(1);
	checkBlank($admin_id,"관리자 아이디를 입력해주세요.");
	checkBlank($pwd,"비밀번호를 입력해주세요.");


	$data=get_info($tbl[mng],"admin_id",$admin_id);
	$_opwd=$pwd;

	$pwd=sql_password($pwd);

	$err=0;
	if(!$data[no]) $err++;
	if($data[pwd]!=$pwd) $err++;

	mngLoginLog($admin_id,$err);

	if($err>0) {
		alert('아이디가 비밀번호가 맞지 않습니다.');
		javac("history.go(-2);");
		exit;
	}

	$_SESSION['admin_no']=$data[no];
	if($data[level]==1) {
		$ems="위사 시스템 관리자";
	}
	elseif($data[level]==2) {
		$ems="최고 관리자";
	}
	else {
		$ems="부관리자";
	}

	$cookie_time=$now+31536000;

	if($_POST[autologin]=="Y") {
		$autologin_id=$data['admin_id'];
		$autologin_code=md5($data['no'].$_opwd);
		setcookie("autologin_id", $autologin_id, $cookie_time, "/");
		setcookie("autologin_code", $autologin_code, $cookie_time, "/");
	}
	else {
		setcookie("autologin_id", "", $cookie_time, "/");
		setcookie("autologin_code", "", $cookie_time, "/");
	}

	alert($ems.'로 로그인 하였습니다.');
	javac("history.go(-2);");

?>