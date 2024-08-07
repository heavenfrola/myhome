<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  디자인관리 이미지FTP 라이브러리
	' +----------------------------------------------------------------------------------------------+*/

	function ftpCon($ftp_id="", $ftp_pwd="", $ftp_port=""){
		global $ftp_ftp_con,$ftp_login_result,$root_dir,$root_url,$ftp_info,$engine_dir;
		if(!$ftp_ftp_con || !$ftp_login_result) {
			$ck_src=$root_dir."/_config/ftpcon.php";
			if(@is_file($ck_src)) $file_src=$ck_src;
			else $file_src=$engine_dir."/../weagleEye/common/ftpcon.php";
			$ftp_info=@file($file_src);
			for($ii=0; $ii<count($ftp_info); $ii++){
				$ftp_info[$ii]=trim($ftp_info[$ii]);
			}

			$ftp_url='localhost';
			$ftp_id=($ftp_id) ? $ftp_id : $ftp_info[1];
			$ftp_pwd=($ftp_pwd) ? $ftp_pwd : $ftp_info[2];
			$ftp_port=($ftp_port) ? $ftp_port : $ftp_info[3];

			if(!$ftp_port) $ftp_port=21;

			$ftp_ftp_con=@ftp_connect($ftp_url, $ftp_port);
			$ftp_login_result=@ftp_login($ftp_ftp_con, $ftp_id, $ftp_pwd);

			if(!$ftp_ftp_con || !$ftp_login_result){
				return 0;
			}
			@ftp_pasv($ftp_ftp_con, FALSE);
			@ftp_set_option($ftp_ftp_con, FTP_TIMEOUT_SEC, 10);
		}
		return $ftp_login_result;
	}

	function ftpUploadFile($updir, $files, $extstr="jpg|jpeg|gif|bmp|png|flv|swf|png", $unlink=""){
		global $ftp_ftp_con, $matched_server, $fs_ftp_con, $file_server, $root_dir, $engine_dir;
		if(!$ftp_ftp_con) ftpCon();
		$ext=getExt($files['name']);
		$ext=strtolower($ext);
		if(!@stristr($extstr,$ext)){
			if(!$unlink) @unlink($files['tmp_name']);
			msg("업로드할 수 없는 확장자입니다 - ".$files['name']);
		}

		if(file_exists($engine_dir.'/include/account/getHspec.inc.php')) {
			$upload_limit_mb = 3;
		} else {
			$upload_limit_mb = 20;
		}
		if($files['size'] > $upload_limit_mb*1024*1024){
			if(!$unlink) @unlink($files['tmp_name']);
			msg("20MB 이상의 파일은 업로드하실 수 없습니다 - ".$files['name']);
		}

		$use_multi_server = fsConFolder($updir);
		$servers = ($use_multi_server) ? $use_multi_server : 1;

		for ($i = 0; $i < $servers; $i++) {
			if ($use_multi_server) {
				$fs_ftp_con = "";
				$file_server_num = $matched_server[$i];
				fileServerCon($file_server_num);
				$file_dirname=$file_server[$file_server_num]['file_dirname'] ? "/".$file_server[$file_server_num]['file_dirname'] : "";
				$_updir = $file_dirname."/".str_replace($root_dir, "", $updir);
				$ftp_ftp_con = $fs_ftp_con;
				$_updir = str_replace($root_dir, $file_dirname, $updir);
			} else {
				$_updir = $updir;
			}
			ob_start();
			ftpChangeDir($_updir);
			$files['name']=stripslashes($files['name']);
			if(!ftp_put($ftp_ftp_con, $files['name'], $files['tmp_name'], FTP_BINARY)){
				$err = str_replace("\n","\\n",addslashes(ftp_pwd($ftp_ftp_con)));
				ob_end_clean();
				msg(ftp_pwd($ftp_ftp_con)."] FTP 업로드 실행 실패! \\n -".$files['name']." ".$err);
				echo("FTP 업로드 실행 실패! \\n -".$files['name']."<br>");
				return;
			}
		}
	}

	function reChmodFile($dir, $file, $p=1){
		if($p == 1){
			chmod($dir, 0777);
			if(is_file($dir."/".$file)) chmod($dir."/".$file, 0777);
		}else{
			chmod($dir, 0755);
			if(is_file($dir."/".$file)) chmod($dir."/".$file, 0644);
		}
	}

	function ftpMakeDir($updir, $_fname="", $perm = null){
		global $ftp_ftp_con, $fs_ftp_con, $root_dir, $matched_server, $file_server;

		$use_multi_server = fsConFolder($updir);
		$servers = ($use_multi_server) ? $use_multi_server : 1;

		for ($i = 0; $i < $servers; $i++) {
			if ($use_multi_server) {
				$fs_ftp_con = "";
				$file_server_num = $matched_server[$i];
				fileServerCon($file_server_num);
				$file_dirname=$file_server[$file_server_num]['file_dirname'] ? "/".$file_server[$file_server_num]['file_dirname'] : "";
				$_updir = str_replace($root_dir,$file_dirname, $updir);
				$ftp_ftp_con = $fs_ftp_con;
			} else {
				$_updir = $updir;
			}

			if(!$ftp_ftp_con) ftpCon();
			ftpChangeDir($_updir);
			$_fname=$_fname ? $_fname : "_".date("ymdHis");

			if(!@ftp_mkdir($ftp_ftp_con,$_fname)) {
				echo("error-3");
				return;
			} else {
				if($perm) ftp_site($ftp_ftp_con, "CHMOD $perm $_fname");
			}
		}
	}

	function ftpRename($updir, $oriname, $rename, $extstr="jpg|jpeg|gif|bmp|png|flv|swf"){
		global $ftp_ftp_con, $fs_ftp_con, $root_dir, $matched_server, $file_server;

		$use_multi_server = fsConFolder($updir);
		$servers = ($use_multi_server) ? $use_multi_server : 1;
		for ($i = 0; $i < $servers; $i++) {
			if ($use_multi_server) {
				$fs_ftp_con = "";
				$file_server_num = $matched_server[$i];
				fileServerCon($file_server_num);
				$file_dirname=$file_server[$file_server_num]['file_dirname'] ? "/".$file_server[$file_server_num]['file_dirname'] : "";
				$_updir = str_replace($root_dir,$file_dirname, $updir);
				$ftp_ftp_con = $fs_ftp_con;
			} else {
				$_updir = $updir;
			}

			if(!$ftp_ftp_con) ftpCon();
			if($oriname == $rename) return;
			ftpChangeDir($_updir);

			if(!@ftp_chdir($ftp_ftp_con,$oriname)){
				$ext=getExt($rename);
				if($ext && !@strchr($extstr,$ext)){
					echo("error-51");
					return;
				}
			}else ftp_chdir($ftp_ftp_con, "../");
			if(!@ftp_rename($ftp_ftp_con, $oriname, stripslashes($rename))){
				echo("error-5");
				return;
			}
		}
	}

	function ftpDeleteFile($updir, $filename){
		global $ftp_ftp_con, $fs_ftp_con, $root_dir, $matched_server, $file_server;

		$use_multi_server = fsConFolder($updir);
		$servers = ($use_multi_server) ? $use_multi_server : 1;
		for ($i = 0; $i < $servers; $i++) {
			if ($use_multi_server) {
				$fs_ftp_con = "";
				$file_server_num = $matched_server[$i];
				fileServerCon($file_server_num);
				$file_dirname=$file_server[$file_server_num]['file_dirname'] ? "/".$file_server[$file_server_num]['file_dirname'] : "";
				$_updir = str_replace($root_dir,$file_dirname, $updir);

				$ftp_ftp_con = $fs_ftp_con;
			} else {
				$_updir = $updir;
			}

			if(!$ftp_ftp_con) ftpCon();
			ftpChangeDir($_updir);
			$filename=stripslashes($filename);
			if(!@ftp_delete($ftp_ftp_con,$filename) && !@ftp_rmdir($ftp_ftp_con,$filename)){
				echo("error-6");
				return;
			}
		}
	}

	function ftpChmod($src, $chmod){
		global $ftp_ftp_con;
		$use_multi_server = fsConFolder($updir);
		$servers = ($use_multi_server) ? $use_multi_server : 1;
		for ($i = 0; $i < $servers; $i++) {
			if ($use_multi_server) {
				$fs_ftp_con = "";
				$file_server_num = $matched_server[$i];
				fileServerCon($file_server_num);
				$file_dirname=$file_server[$file_server_num]['file_dirname'] ? "/".$file_server[$file_server_num]['file_dirname'] : "";
				$_updir = str_replace($root_dir,$file_dirname, $updir);

				$ftp_ftp_con = $fs_ftp_con;
			} else {
				$_updir = $updir;
			}

			if(!$ftp_ftp_con) ftpCon();
			$fname=basename($src);
			$_src=str_replace($fname, "", $src);
			ftpChangeDir($_src);
			$r=ftp_site($ftp_ftp_con, "CHMOD $chmod $fname");
		}
	}

	function ftpChangeDir($updir){
		global $ftp_ftp_con;
		if(!$ftp_ftp_con) ftpCon();
		ftp_chdir($ftp_ftp_con, "/");
		$updir = preg_replace("/\/+/","/",$updir);
		$_dir=explode("/",$updir);
		$_realdir="";
		foreach($_dir as $key=>$val){
			if(!$val) continue;
			if(!@ftp_chdir($ftp_ftp_con,$val)) {

			}
		}
	}

	function dirFileExist($dir, $ext="jpg|gif|bmp|png"){
		$re=0;
		$odir=@opendir($dir);
		if($odir){
			while($arr=@readdir($odir)){
				$file=$dir."/".$arr;
				if(@is_file($file)){
					if($ext == "all"){ $re=1; break;
					}else{
						$file_ext=strtolower(getExt($arr));
						if(@strchr($ext,$file_ext)){ $re=1; break; }
					}
				}
			}
		}
		return $re;
	}

?>