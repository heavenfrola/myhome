<?PHP

	$cfg['pg_version'] = $_POST['pg_version'];
	$_dirname = ($_POST['pg_version'] == 'INIweb') ? 'INIweb' : 'INIpay41';
	$inicis_home_dir = $root_dir.'/_data/'.$_dirname;

	if($cfg['pg_version'] == 'INIweb') {
		$inipay_id = ($_POST['card_web_id']) ? $_POST['card_web_id'] : $cfg['card_web_id'];
		$iniesc_id = ($_POST['escrow_web_id']) ? $_POST['escrow_web_id'] : $cfg['escrow_web_id'];
	} else if($cfg['pg_version'] == '') {
		$inipay_id = ($_POST['card_mall_id']) ? $_POST['card_mall_id'] : $cfg['card_mall_id'];
		$iniesc_id = ($_POST['escrow_mall_id']) ? $_POST['escrow_mall_id'] : $cfg['escrow_mall_id'];
	}
	$inimob_id = ($_POST['card_inicis_mobile_id']) ? $_POST['card_inicis_mobile_id'] : $cfg['card_inicis_mobile_id'];
	$id_list = array($inipay_id, $iniesc_id, $inimob_id);

	// 이니시스 키파일 설치 모듈
	$unzipInstalled = shell_exec('/usr/bin/unzip');
	if(preg_match('/Unzip/i', $unzipInstalled) == false) msg('zip 압축해제 모듈이 설치되어있지 않습니다');
	$ext = strtolower(getExt($_FILES['inicis_key']['name']));
	if($ext != 'zip') msg('업로드 하신 파일 형식이 정확하지 않습니다');


	// 이니페이 홈디렉토리 생성
	if(!is_dir($inicis_home_dir)) {
		mkdir($inicis_home_dir);
		chmod($inicis_home_dir, 0777);
		chdir($inicis_home_dir);
		shell_exec("/bin/tar -xvzpf $engine_dir/_engine/card.inicis/INIpay.tar.gz");
	}


	// 디렉토리 퍼미션 수정 (계정 이동 등으로 디렉토리 퍼미션이 문제가 생기는 경우가 있으므로 업로드시마다 처리)
	if(!file_exists($inicis_home_dir)) msg('모듈 설치가 실패되었습니다.');

	umask(0);
	chdir($inicis_home_dir);
	chmod('phpexec', 0755);
	chmod('phpexec/INIcancel.phpexec', 0755);
	chmod('phpexec/INIreceipt.phpexec', 0755);
	chmod('phpexec/INIsecurepay.phpexec', 0755);
	chmod('log', 0777);
	chmod('key', 0755);


	// 키파일 업로드
	include_once $engine_dir.'/_engine/include/file.lib.php';
	$up_filename = preg_replace('/\.[a-z0-9]+$/i', '', $_FILES['inicis_key']['name']);
	if(in_array($up_filename, $id_list) == false) {
		msg('키파일 이름과 입력된 PG계정 아이디가 일치하지 않습니다.');
	}
	$updir = '_data/'.$_dirname.'/key/'.$up_filename.'/';
	mkdir('key/'.$up_filename, 0755);
	$upload = uploadFile($_FILES['inicis_key'], $up_filename, $updir, 'zip');


	// 키파일 압축해제
	chdir($root_dir.'/'.$updir);
	shell_exec('/usr/bin/unzip '.$_FILES['inicis_key']['name']);
	chmod('keypass.enc', 0644);
	chmod('mcert.pem', 0644);
	chmod('mpriv.pem', 0644);
	unlink($_FILES['inicis_key']['name']);


	// 최종확인 (파일 점검 및 불법 스크립트 업로드 체크)
	$file_error = 0;
	$keyfiles = array('keypass.enc','mcert.pem','mpriv.pem','readme.txt');
	$dir = opendir($root_dir.'/'.$updir);
	while($filename = readdir($dir)) {
		if(is_file($root_dir.'/'.$updir.'/'.$filename)) {
			if(!in_array($filename, $keyfiles)) {
				$file_error++;
				unlink($filename);
			}
		}
	}
	if($file_error > 0 ) msg("비정상적인 파일이 포함되어있습니다\\t\\t\\n\\n비정상적인 파일들은 삭제되었으며,\\n키코드 파일을 다시 확인해주시기 바랍니다");

	if(file_exists($root_dir.'/'.$updir.'/keypass.enc') && file_exists($root_dir.'/'.$updir.'/mcert.pem') && file_exists($root_dir.'/'.$updir.'/mpriv.pem')) {
		alert('이니시스 상점키파일 설치가 완료되었습니다');
	} else {
		alert('키코드 파일 일부 혹은 전체가 업로드 되지 않았습니다\\t\\n\\n이니시스 상점키파일 설치가 실패되었습니다');
	}

?>