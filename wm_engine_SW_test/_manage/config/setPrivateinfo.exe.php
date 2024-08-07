<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  개인정보 수집기능 변경 기능 안내 처리
	' +----------------------------------------------------------------------------------------------+*/

	header('Content-type:text/html; charset=euc-kr;');

	include_once $engine_dir."/_engine/include/design.lib.php";
	include_once $engine_dir."/_manage/design/version_check.php";
	include_once $engine_dir."/_engine/include/img_ftp.lib.php";

	$exec = $_POST['exec'];

	switch($exec) {
		case 'start' :
			$skin_name = $design['skin'].'_jedt';
			if(is_dir($root_dir.'/_skin/'.$skin_name) == false) {
				ftpMakeDir($root_dir."/_skin/", $skin_name, '0777');
				scanF($root_dir.'/_skin/'.$design['skin'], 2, $root_dir."/_skin/".$skin_name);

				$design['edit_skin'] = $skin_name;
				include $engine_dir."/_manage/design/config.exe.php";

				exit("신규스킨 '$skin_name'\n생성 후 편집스킨으로 설정하였습니다.\n설정을 다시 진행하실 경우에도 현재 생성된 스킨을 계속 편집합니다.");
			}

			$design['edit_skin'] = $skin_name;
			ob_start();
			include $engine_dir."/_manage/design/config.exe.php";
			ob_end_clean();
			exit("기존 생성된 $skin_name 스킨을 편집스킨으로 설정하였습니다.");
		break;
		case 'complete' :
			$design['skin'] = $design['skin'].'_jedt';
			if(file_exists($root_dir.'/_skin/'.$design['skin'])) {
				include $engine_dir."/_manage/design/config.exe.php";
				alert('사용스킨이 변경되었습니다.');
			}
			$no_reload_config = 'true';
			include_once $engine_dir.'/_manage/config/config.exe.php';
			msg("설정이 완료되었습니다.", '?body=config@member', 'parent');
		break;
	}
?>