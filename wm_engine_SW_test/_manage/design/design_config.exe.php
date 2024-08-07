<?PHP

    use Wing\Design\BannerGroup;
    use Wing\HTTP\CurlConnection;

    set_time_limit(0);

	/* +----------------------------------------------------------------------------------------------+
	' |  스킨 관리 처리
	' +----------------------------------------------------------------------------------------------+*/
	$no_reload = $_POST['no_reload'];
	$exec = addslashes($_POST['exec']);
	$mode = $_POST['mode'];

	if(!$no_reload) checkBasic();

	include_once $engine_dir."/_engine/include/design.lib.php";
	include_once $engine_dir."/_manage/design/version_check.php";
	include_once $engine_dir."/_engine/include/img_ftp.lib.php";

	$_bak_dir=$root_dir."/".$dir['upload']."/skin_backup";
	$_bak_url=$root_url."/".$dir['upload']."/skin_backup";

	function skinBackUp($skin_name, $_bak_dir="", $file_src="", $file_list="*"){
		global $engine_dir,$root_dir,$dir,$design, $now;
		include_once $engine_dir."/_engine/include/file.lib.php";
		if(!$_bak_dir){
			$_bak_dir=$dir['upload']."/skin_backup";
			makeFullDir($_bak_dir);
		}
		$filename=$skin_name."_".date("ymdHis", $now).".tgz";
		$_file['name']=$filename;
		$_file['tmp_name']=$root_dir."/".$dir['upload']."/".$filename;
		// 임시로 만듦 (nobody)
		$file_src=$file_src ? $file_src : $root_dir."/_skin/".$skin_name;
		chdir($file_src);
		shell_exec("/bin/tar cfzp {$_file['tmp_name']} $file_list");

		// 2009-07-03 일로 백업할 경우 다이렉트 PC 저장으로 변경
		if(strstr($_SERVER['HTTP_USER_AGENT'], "MSIE 5.5")){
			header("Content-Type: doesn/matter");
			header("Content-Disposition: filename=$filename");
			header("Content-Transfer-Encoding: binary");
			header("Pragma: no-cache");
			header("Expires: 0");
		}else{
			Header("Content-type: file/unknown");
			Header("Content-Disposition: attachment; filename=$filename");
			Header("Content-Description: PHP3 Generated Data");
			header("Pragma: no-cache");
			header("Expires: 0");
		}

		if(is_file($_file['tmp_name'])){
			$fp=fopen($_file['tmp_name'], "r");
			if(!fpassthru($fp)) fclose($fp);
		}

		unlink($_file['tmp_name']);
		return $filename;
	}
	// 첨부파일로 스킨 생성
	function fileRestore($fd_name="restore_skin", $type=""){
        return false;
		global $root_dir, $dir, $_efile, $skin_name;
		checkBlank($_FILES[$fd_name]['size'], "복구하실 파일을 입력해주세요.");

		$ext=getExt($_FILES[$fd_name]['name']);
		$ext=strtolower($ext);
		if($ext != "tgz" && $ext != "zip"){
			unlink($_FILES[$fd_name]['tmp_name']);
			msg("압축파일외에는 실행이 불가능합니다");
		}

		// 임시로 풀어서 파일검사
		$_tmp_fname=mt_rand();
		$_tmp_dir=$root_dir."/".$dir['upload']."/".$_tmp_fname;

		makeFullDir($dir['upload']."/".$_tmp_fname);
		$_file_name="scan.".$ext;
		copy($_FILES[$fd_name]['tmp_name'], $_tmp_dir."/".$_file_name);
		unlink($_FILES[$fd_name]['tmp_name']);

		chdir($_tmp_dir);
		releaseFile($_tmp_dir."/".$_file_name);
		unlink($_tmp_dir."/".$_file_name);

		$_efile=array();
		scanF($_tmp_dir);
		// 가장 상위에 파일이 존재하는지 체크
		$root_odir=opendir($_tmp_dir);
		while($root_ck=readdir($root_odir)){
			if(is_file($_tmp_dir."/".$root_ck)){
				$_root_folder_ck=1;
			}
		}
		$skin_ck=($type == "board") ? 1 : skinFormatChk($_tmp_dir, 1);
		chdir($root_dir."/".$dir['upload']);
		if(count($_efile) > 0 || !$skin_ck || !$_root_folder_ck){
			shell_exec("rm -fr ./".$_tmp_fname);
			if(!$skin_ck) $errmsg="[스킨 파일 및 폴더의 구성이 정확하지 않습니다]     \\n\\n/COMMON\\n/CORE\\n/MODULE\\n/skin_config.cfg\\n\\n";
			if(!$_root_folder_ck) $errmsg="[스킨 파일 및 폴더의 구성이 정확하지 않습니다]     \\n\\n- 압축 파일 내부에 사용 가능한 파일이 존재하지 않음      \\n\\n";
			if(count($_efile) > 0) $errmsg .= "[내용에 입력금지된 코드가 존재합니다 - 업로드가 금지된 파일목록]      \\n\\n- ".implode("\\n- ", $_efile);
			msg("\\n 스킨 파일의 생성이 실패하였습니다            \\n\\n".$errmsg);
		}

		$_dir=($type == "board") ? $root_dir."/board" : $root_dir;

		$tmp_name = ($_GET['type'] == 'mobile') ? "m_user" : "user";
		$skin_name = skinNaming($tmp_name, $_dir);

		ftpMakeDir($_dir."/_skin/", $skin_name);
		scanF($_tmp_dir, 2, $_dir."/_skin/".$skin_name);

		shell_exec("rm -fr ./".$_tmp_fname);
	}
	// 삭제파일명에 디렉토리 문자'/' 있는지 체크(보안)
	function deleteNameChk($file_name){
		if(strchr($file_name, "/")) msg("삭제하실 파일명이 잘못되었습니다. 1:1고객센터 문의 글로 접수 바랍니다.");
	}
	// 스킨폴더 삭제
	function skinFolderDelete($fname, $src=""){
		global $root_dir, $ftp_ftp_con,$_del_dir;
		$src=$src ? $src : "_skin";
		$_del_dir[]=$root_dir."/".$src."/".$fname;
		scanF($root_dir."/".$src."/".$fname, 1);
		// 폴더는 배열로 담아 가장 긴 경로부터 거꾸로 삭제
		$_total_f=count($_del_dir)-1;
		for($ii=$_total_f; $ii>=0; $ii--){
			//echo $_del_dir[$ii]."<hr>";
			$_del_fname=basename($_del_dir[$ii]);
			$_del_src=str_replace($_del_fname, "", $_del_dir[$ii]);
			ftpDeleteFile($_del_src, $_del_fname);
		}
	}
	// 새로운 스킨명 생성
	function skinNaming($skin_name, $dir=""){
		global $root_dir;
		$tmp=1;
		$_dir=$dir ? $dir : $root_dir;
		while($tmp){
			if(is_dir($_dir."/_skin/".$skin_name."_".$tmp)){
				$tmp++;
			}else{
				$skin_name=$skin_name."_".$tmp;
				$tmp=0;
			}
		}
		return $skin_name;
	}
	// 압축파일풀기
	function releaseFile($file){
		$ext=getExt(basename($file));
		$ext=strtolower($ext);
		if($ext == "zip"){
			shell_exec("/usr/bin/unzip ".$file);
		}else{
			shell_exec("/bin/tar xfzp ".$file);
		}
	}

	$_skin_dir=$root_dir."/_skin/".$design['skin'];
	if($exec == "version"){

		$design_version=$_POST['design_version'];
		if($design_version == $cfg['design_version']) msg("현재 사용중인 버전과 같습니다");
		if(!$design_upgrade) msg("업그레이드가 불가능합니다");
		$no_reload_config=1;
		include_once $engine_dir."/_manage/config/config.exe.php";
		msg("디자인 관리가 업그레이드 되었습니다", "reload", "parent");


	}elseif($exec == "backup"){

		$selected_skin=$_POST['selected_skin'];
		checkBlank($selected_skin, "백업할 스킨을 입력해주세요.");
		$file_name=skinBackUp($selected_skin);

	}elseif($exec == "bak_delete"){

		$file_name=$_POST['file_name'];
		checkBlank($file_name, "삭제하실 파일을 입력해주세요.");
		deleteNameChk($file_name);
		ftpDeleteFile($_bak_dir, $file_name);
		$msg="";

	}elseif($exec == "bak_restore"){

		$file_name=trim($_POST['file_name']);
		checkBlank($file_name, "복구할 파일을 선택해주세요.");
		if(file_exists($_bak_dir."/".$file_name)){

			// 복구파일명에서 스킨명 구하기
			if(strlen($file_name) <= 17) msg("파일명이 잘못되었습니다");
			$_name_tmp=substr($file_name,-17);
			$skin_name=str_replace($_name_tmp, "", $file_name);

			// 일단 기존에 있는 스킨이면 스킨명을 임시로 바꾸로 삭제처리
			if(is_dir($root_dir."/_skin/".$skin_name)){
				skinBackUp($skin_name);
				$_rename="_del_".date("ymdHis", $now);
				ftpRename($root_dir."/_skin/", $skin_name, $_rename);
				skinFolderDelete($_rename);
			}

			$_tmp_fname=mt_rand();
			$_tmp_dir=$root_dir."/".$dir['upload']."/".$_tmp_fname;

			makeFullDir($dir['upload']."/".$_tmp_fname);
			$_file_name="temp.".$ext;
			copy($_bak_dir."/".$file_name, $_tmp_dir."/".$_file_name);

			chdir($_tmp_dir);
			releaseFile($_tmp_dir."/".$_file_name);
			unlink($_tmp_dir."/".$_file_name);

			ftpMakeDir($root_dir."/_skin/", $skin_name);
			scanF($_tmp_dir, 2, $root_dir."/_skin/".$skin_name);

			chdir($root_dir."/".$dir['upload']);
			shell_exec("rm -fr ./".$_tmp_fname);

			$msg="\\'".$skin_name."\\' 스킨이 복구되었습니다";

		}else{

			$msg="해당 백업파일이 존재하지 않아 복구에 실패하였습니다";

		}

	}elseif($exec == "skin_copy"){
		$selected_skin = trim($_POST['selected_skin']);
		$skin_name = trim($_POST['skin_name']);
        $type = $_POST['type'];

		checkBlank($selected_skin, '복사할 스킨을 선택해주세요.');
		checkBlank($skin_name, '생성할 스킨명을 입력해주세요.');
		if(preg_match('/[^a-zA-Z0-9_-]/', $skin_name) == true) {
			msg('스킨명으로 사용할 수 없는 특수문자가 포함되어 있습니다.');
		}
		if(is_dir($root_dir.'/_skin/'.$selected_skin) == false) {
			msg('원본 스킨이 존재하지 않습니다.');
		}
		if(skinFormatChk($skin_name) == true) {
			msg('이미 존재하는 스킨명입니다.');
		}

		// 스킨 생성
		ftpMakeDir($root_dir.'/_skin/', $skin_name, 777);
		for($i = 1; $i <= $cfg['file_server_ea']; $i++) {
			if(is_array($file_server[$i]) == true && in_array('image_ftp', $file_server[$i]['file_type']) == true) {
				makeFullDir('/_skin/'.$skin_name.'/img');
				break;
			}
		}
		scanF($root_dir."/_skin/".$selected_skin, 2, $root_dir."/_skin/".$skin_name);

        // 그룹배너 복사
        include_once $root_dir.'/_skin/'.$selected_skin.'/user_code.cfg';
        foreach ($_user_code as $code => $data) {
            if ($data['code_type'] == 'is') {
                $_updir = '_data/banner/user_group_banner/'.$skin_name.'/'.$code;
                makeFullDir($_updir);

                $grp = new BannerGroup($type, $code);
                while($bn = $grp->parse()) {
                    foreach (array('front_image_url', 'rollover_image_url') as $nm) {
                        if (!$bn[$nm]) continue;

                        $curl = new CurlConnection($bn[$nm]);
                        $curl->exec();

                        $tmp_name = '_data/'.basename($bn[$nm]);
                        fwriteTo($tmp_name, $curl->getResult(), 'w');

                        $info = getimagesize($tmp_name);
                        $file = array(
                            'name' => basename($bn[$nm]),
                            'tmp_name' => $root_dir.'/'.$tmp_name,
                            'type' => $info['mime'],
                            'size' => filesize($root_dir.'/'.$tmp_name)
                        );
                        $_name = preg_replace('/\.'.getExt($file['name']).'$/', '', $file['name']);

                        uploadFile(array(
                            'name' => $file['name'],
                            'type' => $file['type'],
                            'tmp_name' => $file['tmp_name'],
                            'size' => $file['size'],
                        ), $_name, $_updir);
                    }
                }
            }
        }

		// 스킨 코멘트
		$design['sn_'.$skin_name] = trim($_POST['skin_comment']);
		include $engine_dir."/_manage/design/config.exe.php";

		msg('스킨 생성이 완료되었습니다.', 'reload', 'parent');

	}elseif($exec == "skin_delete"){

		$selected_skin = trim($_POST['selected_skin']);
		$pwd = trim($_POST['pwd']);

		checkBlank($selected_skin, '삭제할 스킨을 선택해주세요.');
		checkBlank($pwd, '최고 관리자 비밀번호를 입력해 주세요.');

        if(file_exists($engine_dir.'/_engine/include/account/ssoLogin.inc.php')) {
            $admin_pwd = hash('sha256', $pwd);
            $result = $wec->comm("http://redirect.wisa.co.kr/pwck/".$admin_pwd);
            if($result != 'true') {
                msg("최고관리자 비밀번호가 다릅니다");
            }
        } else {
            $pwd = sql_password($pwd);
            $cnt = $pdo->row("select count(*) from {$tbl['mng']} where level in (1, 2) and pwd='$pwd'");
            if($cnt < 1) msg('최고 관리자 비밀번호가 정확하지 않습니다.');
        }

		if($selected_skin == $design['skin']) msg('현재 사용중인 스킨이므로 삭제가 불가능합니다.');
		deleteNameChk($selected_skin);

		skinFolderDelete($selected_skin);
		skinFolderDelete("skin_".$selected_skin."_bak", $dir['upload']);

		// 스킨 설정 관련 삭제
		if($design['sn_'.$selected_skin] != "") unset($design['sn_'.$selected_skin]);
		if($design['edit_skin'] == $selected_skin) unset($design['edit_skin']);
		include $engine_dir.'/_manage/design/config.exe.php';

		$msg='';

	}elseif($exec == "file_restore"){

		fileRestore();

		$msg="\\'".$skin_name."\\' 으로 새로운 스킨이 생성되었습니다";

	}elseif($exec == "board_skin"){

		function bskinRecent($skin_name){
            global $pdo;

			$row=$pdo->row("select count(*) from `mari_config` where `skin`='$skin_name'");
			if($row) msg("현재 사용중인 스킨이므로 실행이 불가능합니다    ");
		}

		$_skin_src=$root_dir."/board/_skin";
		$_skin_name=trim($_POST['bskin_name']);
		if($mode != "file_restore") checkBlank($_skin_name, "실행할 스킨을 선택해주세요.");
		if($mode == "copy"){
			$skin_name=skinNaming($_skin_name);
			ftpMakeDir($_skin_src, $skin_name);
			$_allow_ext="|jpg|jpeg|gif|bmp|png|swf|php|css|xml|ini|" . $_skin_ext['g']."|".$_skin_ext['c']."|".$_skin_ext['p']."|".$_skin_ext['m'].'|';
			scanF($_skin_src."/".$_skin_name, 2, $_skin_src."/".$skin_name);
		}elseif($mode == "modify"){
			bskinRecent($_skin_name);
			$_rename=trim($_POST['bskin_text'][$_skin_name]);
			checkBlank($_rename, "수정할 스킨명을 입력해주세요.");
			if(preg_match("/^[^0-9^a-z^A-Z^_]/", $_rename)) msg("스킨명은 공백 없는 영문, 숫자로 구성되어야 합니다    ");
			ftpRename($_skin_src, $_skin_name, $_rename);
		}elseif($mode == "delete"){
			deleteNameChk($_skin_name);
			bskinRecent($_skin_name);
			skinFolderDelete($_skin_name, "board/_skin");
		}elseif($mode == "backup"){
			skinBackUp($_skin_name, "", $_skin_src."/".$_skin_name);
		}elseif($mode == "file_restore"){
			$_allow_ext="|jpg|jpeg|gif|bmp|png|swf|php|css|xml|".$_skin_ext['g']."|";
			fileRestore("restore_skin", "board");
			$msg="\\'".$skin_name."\\' 으로 새로운 스킨이 생성되었습니다";
		}

	}elseif($exec == "converter_backup"){

		if($type == 1){
			skinBackUp("v2", "", $root_dir, "_template _include _image");
		}elseif($type == 2){
			if(@is_dir($tmp_dir)){
				skinBackUp("wing", "", $tmp_dir, "*");
			}else{
				msg("변환된 디렉토리가 더이상 존재하지 않습니다. 1단계부터 다시 실행하여 주시기 바랍니다");
			}
		}

	}

	$msg=$msg ? $msg."       " : "";

	if(!$no_reload) msg($msg, "reload", "parent");

?>