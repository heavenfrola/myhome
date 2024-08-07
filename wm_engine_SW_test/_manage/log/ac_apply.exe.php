<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  에이스카운터 신청서 작성
	' +----------------------------------------------------------------------------------------------+*/

	checkBasic();

	$id = addslashes(trim($_GET['id']));
	$dam_name = addslashes(trim($_GET['dam_name']));
	$email = addslashes(trim($_GET['email']));
	$com_phone = addslashes(trim($_GET['com_phone']);
	$com_name = addslashes(trim($_GET['com_name']));
	$domain = addslashes(trim($_GET['domain']));
	$basic_page = addslashes(trim($_GEt['basic_page']));
	$pwd = addslashes($_GET['pwd']);

	checkBlank($id, "아이디를 입력해주세요.");
	checkBlank($dam_name, "담당자 이름을 입력해주세요.");
	checkBlank($email, "이메일 주소를 입력해주세요.");
	checkBlank($com_phone, "연락처(직장)을 입력해주세요.");
	checkBlank($com_name, "회사명을 입력해주세요.");
	checkBlank($domain, "도메인을 입력해주세요.");
	checkBlank($basic_page, "기본페이지를 입력해주세요.");

	// 에이스카운터에 가입신청
	$pwd = 1234;
	$pwd = str_replace("-","",$pwd);

	$company = $com_name;
	$ret_url = 'http://'.$_SERVER['HTTP_HOST']."/main/exec.php?exec_file=log.acecounter/gcode.exe.php";

	$wea = new weagleEyeClient($_we, 'account');
	$wea->queue('acecounter', $wea->config['account_idx'], $id, $pwd, $dam_name, $email, $com_phone, $cell, $com_name, $mall_name, $domain, $basic_page, $sort, $root_url);
	$wea->send_clean();
	$wea->result = trim($wea->result);
	if(preg_match('/^에이스카운터에러/', $wea->result)) msg($wea->result);
	$gcode_script = $wea->result;


	// 에이스카운터에 가입정보 전송
	$result = $wec->get(500, '&id='.$id);
	if ($result != "OK") {
		$wea->queue('aceStatus', $wea->config['account_idx'], 4, $msg);
		$wea->send_clean();
		msg($result);
	}

	$wea->queue('aceStatus', $wea->config['account_idx'], 2);
	$wea->send_clean();
	if($wea->result != 'OK') msg($wea->result);


	// config 수정
	$_POST['ace_counter_id']=$id;
	$_POST['ace_counter_pwd']=$pwd;
	$no_reload_config=1;
	include $engine_dir."/_manage/config/config.exe.php";


	echo $gcode_script;
	msg("신청되었습니다. 확인 후 계정이 등록되면 바로 사용가능합니다", "reload", "parent");

?>