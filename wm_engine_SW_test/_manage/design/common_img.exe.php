<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  이미지 관리 처리
	' +----------------------------------------------------------------------------------------------+*/
	if(!$exec) $exec = addslashes($_POST['exec']);
	if($exec != "upload") checkBasic();

	include_once $engine_dir."/_engine/include/img_ftp.lib.php";
	include_once $engine_dir.'/_config/set.upload.php';

	if($_POST['_folder_dir']) $folder_dir = $_POST['_folder_dir'];
	if($_POST['folder_dir']) $folder_dir = $_POST['folder_dir'];
	$_max_size = 500; // KB

	// 멀티 파일서버
	$_dir = str_replace($root_dir, '', $folder_dir);
	$use_multi_server = fsConFolder($_dir);
	if($use_multi_server) {
		include_once $engine_dir."/_engine/include/file.lib.php";
	}

	$_allow_ext = "jpg|jpeg|gif|bmp|png|swf|ttc|TTC|ttf|TTF|flv";

	if($exec){
		if($exec == "modify"){
			$_failure_img['size'] = array();
			$_failure_img['ext'] = array();
			$ori_img = $_POST['ori_img'];
			foreach($_FILES['replace_img']['name'] as $key=>$val){
				if($_FILES['replace_img']['size'][$key]){
					$_file_name = basename($ori_img[$key]);
					$_ori_ext = getExt($_file_name);
					$_img_name = $_FILES['replace_img']['name'][$key];
					$_edt_ext = getExt($_img_name);
					$file['name'] = $_file_name;

					// 타이틀 이미지일 경우 모든 이미지 확장자 호환
					if($_ori_ext != $_edt_ext && $folder == "title"){
						$file['name'] = str_replace(".".$_ori_ext, "", $_file_name).".".$_edt_ext;
						ftpDeleteFile($folder_dir, $_file_name);
					}

					$file['tmp_name'] = $_FILES['replace_img']['tmp_name'][$key];
					$file['size'] = $_FILES['replace_img']['size'][$key];
					if(($file['size']/1024) > $_max_size){
						$_failure_img['size'][] = $_img_name;
					}elseif($_ori_ext != $_edt_ext && $folder != "title"){
						$_failure_img['ext'][]=$_img_name;
					}else{
						if($use_multi_server) {
							$ext = getExt($file['name']);
							$filename = preg_replace("/\.$ext$/", '', $file['name']);
							uploadFile($file, $filename, $_dir, $_allow_ext);
						} else {
							ftpUploadFile($folder_dir, $file, $_allow_ext);
						}
					}
					@unlink($_FILES['replace_img']['tmp_name'][$key]);
				}
			}
			if(count($_failure_img['size']) > 0){
				$msg = "\\n\\n";
				$msg .= implode(", ", $_failure_img['size'])." 는 용량초과로 업로드가 실패되었습니다         \\n\\n";
			}
			if(count($_failure_img['ext']) > 0){
				$msg .= "\\n\\n";
				$msg .= implode(", ", $_failure_img['ext'])." 는 기존 파일과의 확장자 불일치로 업로드가 실패되었습니다             \\n\\n";
			}
			if(!$no_reload) $default_msg = "업로드가 완료되었습니다";
			$msg = $msg ? $msg : $default_msg;

		}elseif($exec == "delete"){
			$img_num = $_POST['img_num'];
			$_del_file = $_POST['ori_img'][$img_num];
			$_file_name = basename($_del_file);
			if($_file_name == "") msg("잘못된 파일 형식입니다");
			if($use_multi_server) {
				fsDeleteFile($folder_dir, $_file_name);
			} else {
				ftpDeleteFile($folder_dir, $_file_name);
			}
			$msg = "";

		}elseif($exec == "upload"){
			foreach($_FILES as $upfile) {
				if(!$upfile['size']) continue;

				if(($upfile['size']/1024) > $_max_size){
					@unlink($upfile['tmp_name']);
					msg("사이트 최적화를 위하여 500KB 미만의 파일만을 업로드하실 수 있습니다");
				}

				// 한글파일명 체크
				for($p = 0; $p <= strlen($upfile['name']); $p++) {
					if(ord($upfile['name'][$p]) > 128) {
						$upfile['name'] = md5($upfile['name']).'.'.getExt($upfile['name']);
						break;
					}
				}

				if($use_multi_server) {
					$ext = getExt($upfile['name']);
					$filename = preg_replace("/\.$ext$/", '', $upfile['name']);
					uploadFile($upfile, $filename, $_dir, $_allow_ext);
				} else {
					ftpUploadFile($folder_dir, $upfile, $_allow_ext);
					@unlink($upfile['tmp_name']);
				}
			}
			if($_REQUEST['from_ajax'] == 'true') exit('OK');
			else {
				msg('', 'reload', 'parent');
			}
		}

		if(!$no_reload) msg($msg, "reload", "parent");
	}

?>