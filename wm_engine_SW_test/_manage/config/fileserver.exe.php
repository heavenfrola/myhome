<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  파일서버 설정 처리
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/ftp.lib.php";

	if($cfg['file_server_option'] == 2 && $cfg['file_server_ea'] > 0){
		$_use['file_server']="Y";

		for($ii=1; $ii<=$cfg['file_server_ea']; $ii++){
			$_POST["ftp_addr".$ii]=str_replace("http://", "", $_POST["ftp_addr".$ii]);
			checkBlank($_POST["ftp_addr".$ii], "IP 또는 도메인주소를 입력해주세요.");
			checkBlank($_POST["ftp_id".$ii], "ID 를 입력해주세요.");
			checkBlank($_POST["ftp_pwd".$ii], "Password 를 입력해주세요.");
			checkBlank($_POST["url".$ii], "웹접속 URL을 입력해주세요.");
			if(!$_POST["ftp_port".$ii]) $_POST["ftp_port".$ii]="21";
			$file_server[1]['file_server']=array($_POST["ftp_addr".$ii],$_POST["ftp_id".$ii],$_POST["ftp_pwd".$ii],$_POST["ftp_port".$ii]);
			$fs_login_result="";
			fileServerCon(1);

			$ftp_category = "";
			if (is_array($_POST['fs_category'][$ii])) {
				foreach($_POST['fs_category'][$ii] as $val) {
					$ftp_category .= ", '$val'";
				}
				$ftp_category = preg_replace("/^,/","",$ftp_category);
			} else {
				msg("파일서버 $ii 의 서버용도를 체크해 주십시오");
			}

			$content .= "
			// 파일서버 #$ii
			\$file_server[$ii]['name'] = '".$_POST['ftp_name'.$ii]."';
			\$file_server[$ii]['file_server'] = array('".$_POST['ftp_addr'.$ii]."', '".$_POST['ftp_id'.$ii]."', '".$_POST['ftp_pwd'.$ii]."', '".$_POST['ftp_port'.$ii]."');
			\$file_server[$ii]['url'] = '".$_POST['url'.$ii]."';
			\$file_server[$ii]['file_type'] = array($ftp_category);
			\$file_server[$ii]['file_dirname'] = '".$_POST['file_dirname'.$ii]."';";
		}
	}

	$content = "<?php\n".str_replace("\t", '', $content);

	// 설정파일을 로컬에 임시 저장
	$_tmp_dir=$root_dir."/_data/fileserver_tmp.php";
	$of=fopen($_tmp_dir, "w");
	$fw=fwrite($of, $content);
	if(!$fw) msg("권한설정문제로 저장이 실패하였습니다. 1:1고객센터 문의 글로 접수 바랍니다.");
	fclose($of);
	chmod($_tmp_dir, 0777);

	include $_tmp_dir;

	// 설정파일을 실계정에 저장
	$file['name']="file_server.php";
	$file['tmp_name']=$_tmp_dir;
	$file['size']=filesize($_tmp_dir);
	include_once $engine_dir."/_engine/include/img_ftp.lib.php";

	$GLOBALS['ext_unlimit'] = "Y";

	if (fsConFolder("_config")) uploadFile($file,"file_server","/_config");
	else if(!copy($file['tmp_name'], $root_dir."/_config/file_server.php")) msg("저장이 실패하였습니다");

	unlink($file['tmp_name']);

	if(!$no_reload_config) {
		msg("저장이 완료되었습니다", "reload", "parent");
	}

?>