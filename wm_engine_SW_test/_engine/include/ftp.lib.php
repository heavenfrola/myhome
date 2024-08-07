<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  FTP서버 라이브러리
	' +----------------------------------------------------------------------------------------------+*/

	function fileServerCon($file_server_num){
		global $fs_ftp_con,$fs_login_result,$_use,$file_server;
		if(!$file_server_num) return;
		if(!$_use['file_server']) return;

		if(!$fs_ftp_con || !$fs_login_result) {
			$fs_ftp_con=ftp_connect($file_server[$file_server_num]['file_server'][0], $file_server[$file_server_num]['file_server'][3]);
			$fs_login_result=ftp_login($fs_ftp_con, $file_server[$file_server_num]['file_server'][1], $file_server[$file_server_num]['file_server'][2]);
			if(!$fs_ftp_con || !$fs_login_result){
				msg(__lang_file_error_ftpcon__."[$file_server_num]");
			}
			ftp_pasv($fs_ftp_con, true);
			ftp_set_option($fs_ftp_con, FTP_TIMEOUT_SEC, 20);
		}

	}

	function fsUploadFile($updir, $file, $filename){
		global $fs_ftp_con,$fs_login_result,$file_server,$matched_server;

		for ($i = 0; $i < count($matched_server); $i++) {
			$fs_ftp_con = "";
			$val = $matched_server[$i];
			fileServerCon($val);

			$file_dirname=$file_server[$val]['file_dirname'] ? "/".$file_server[$val]['file_dirname'] : "";
			$file_dir=$file_dirname."/".$updir."/".$filename;

			if (!@ftp_chdir($fs_ftp_con, dirname($file_dir))) {
				makeFullDir($updir);
			}

			if(!ftp_put($fs_ftp_con, $file_dir, $file, FTP_BINARY)){
				msg(__lang_file_error_transfer__."[$val]");
			}
		}
	}

	function fsMakeDir($dir){
		global $fs_ftp_con, $file_server_num, $file_server;
		if (!$fs_ftp_con) fileServerCon($file_server_num);

		$d = ftp_chdir($fs_ftp_con,"/");

		if(!@ftp_chdir($fs_ftp_con,$dir)) {
			if(!@ftp_mkdir($fs_ftp_con,$dir)) {
				echo ($dir."실패");
				echo "폴더만들기 실패<br>";
			} else {
				@ftp_site($fs_ftp_con, "CHMOD 0777 $dir");
			}
		}
	}

	function fsDeleteFile($updir, $filename){
		global $cfg, $fs_ftp_con,$fs_login_result,$_use,$file_server,$matched_server, $engine_dir;

		if($_use['file_server'] <> "Y" || !$file_server) return;
		for ($i = 0; $i < count($matched_server); $i++) {
			$fs_ftp_con = "";
			$val = $matched_server[$i];

			fileServerCon($val);
			$file_dirname=$file_server[$val]['file_dirname'] ? "/".$file_server[$val]['file_dirname'] : "";
			$file_dir=$file_dirname."/".$updir."/".$filename;
			$ftp = @ftp_delete($fs_ftp_con,$file_dir);
		}

		if($cfg['product_upload_debug'] == 'Y' && $filename) {
			$log  = "[_SERVER]\n";
			$log .= print_r($_SERVER, true)."\n";
			$log .= "[_POST]\n";
			$log .= print_r($_POST, true)."\n";
			$log .= "[_GET]\n";
			$log .= print_r($_GET, true)."\n";
			$log .= "[_SESSION]\n";
			$log .= print_r($_SESSION, true);

			include_once $engine_dir.'/_engine/include/file.lib.php';
			makeFullDir('_data/productUploadExcel/delete');
			fwriteTo('_data/productUploadExcel/delete/'.date('Ymd_His').'_'.$filename.'.txt', $log);
		}
	}

	function fsFileDown($downdir, $filename, $where){
		global $fs_ftp_con,$fs_login_result,$_use,$file_server,$file_server_num;

		if($_use['file_server'] <> "Y" || !$file_server) return;
		fileServerCon($file_server_num);

		ftp_chdir($fs_ftp_con,"/");
		if(!is_dir($where)){
			mkdir($where);
			chmod($where,0777);
		}
		$remote_dir = $file_server[$file_server_num]['file_dirname'];
		if(preg_match('@^/__manage__/product_[0-9]+@', $downdir) == true) {
			$remote_dir = preg_replace('@/basic$@', '/imghosting', $remote_dir);
		}
		$file_dirname=$remote_dir ? "/".$remote_dir : "";
		$ftp_file=$file_dirname."/".$downdir."/".$filename;
		$local_file=$where."/".$filename;
		if(!ftp_get($fs_ftp_con, $local_file, $ftp_file, FTP_BINARY)){
			echo(__lang_file_error_download__);
		}
	}


	function ftp_is_dir($dir) {
		global $fs_ftp_con;
		if (@ftp_chdir($fs_ftp_con, $dir)) {
			@ftp_chdir($fs_ftp_con, '..');
			return true;
		} else {
			return false;
		}
	}

?>