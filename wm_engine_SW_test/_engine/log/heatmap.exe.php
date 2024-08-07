<?PHP

	include_once $engine_dir.'/_engine/include/common.lib.php';
	include_once $engine_dir.'/_engine/include/file.lib.php';

	foreach($_GET as $key => $val) {
		$log .= "[GET] $key => $val\n";
	}
	foreach($_POST as $key => $val) {
		$log .= "[POST] $key => $val\n";
	}

	$fp = fopen($root_dir.'/_data/heatmnap_'.$now.'.txt', 'w');
	fwrite($fp, $log);
	fclose($fp);

	switch($exec) {
		case 'regist' :
			$_POST['logger_heatmap_HM_U'] = $_POST['HM_U'];
			$_POST['logger_heatmap_PASSWORD'] = $_POST['PASSWORD'];
			$_POST['logger_heatmap_cusId'] = $_POST['cusId'];

			$no_reload_config = true;
			include $engine_dir.'/_manage/config/config.exe.php';
		break;
		default :
			if(!$cfg['logger_heatmap_cusId']) {
				exit('{"RESULT_CODE":"0001", "RESULT_MSG":"히트맵 서비스 미가입", "_HM_IDX":"'.$_HM_IDX.'", "_HM_URL":"'.$_HM_URL.'", "_HM_STAT":"'.$_HM_STAT.'"}');
			}

			$_HM_IDX = $_GET['_HM_IDX'];
			$_HM_URL = $_GET['_HM_URL'];
			$_HM_STAT = $_GET['_HM_STAT'];

			if($cfg['logger_heatmap_cusId'] != $_GET['cusId']) {
				exit('{"RESULT_CODE":"0005", "RESULT_MSG":"히트맵 아이디 불일치"}');
			}

			if(!$_HM_IDX) {
				exit('{"RESULT_CODE":"0002", "RESULT_MSG":"_HM_IDX 값 미전송"}');
			}

			if(!$_HM_URL) {
				exit('{"RESULT_CODE":"0003", "RESULT_MSG":"_HM_URL 값 미전송"}');
			}

			if(!$_HM_STAT) {
				exit('{"RESULT_CODE":"0004", "RESULT_MSG":"_HM_STAT 값 미전송"}');
			}

			$_HM_URL = preg_replace('/\/$/', '', $_HM_URL);
			$_root_url = preg_replace('/https?:\/\//', '', $root_url);
			if($_HM_URL == $_root_url || $_HM_URL == $_root_url.'/index.php') {
				$_HM_URL = $_root_url.'/main/index.php';
			}

			$data = $pdo->assoc("select * from $tbl[default] where code='heatmap_$_HM_IDX'");
			if(!$data['code']) $pdo->query("insert into $tbl[default] (code, value, ext) values ('heatmap_$_HM_IDX', '$_HM_URL', '$_HM_STAT')");
			else {
				$pdo->query("update $tbl[default] set value='$_HM_URL', ext='$_HM_STAT' where code='heatmap_$_HM_IDX'");
			}

			exit('{"RESULT_CODE":"0000", "RESULT_MSG":"데이터수신성공", "_HM_IDX":"'.$_HM_IDX.'", "_HM_URL":"'.$_HM_URL.'", "_HM_STAT":"'.$_HM_STAT.'"}');
		break;
	}

?>