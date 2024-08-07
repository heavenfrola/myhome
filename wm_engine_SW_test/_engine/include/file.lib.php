<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  파일관련 라이브러리
	' +----------------------------------------------------------------------------------------------+*/

	include_once __ENGINE_DIR__."/_engine/include/ftp.lib.php";

	function uploadFile($files, $up_filename, $updir,$ableExt="", $upfileSize=0, $txtUp=false) {
		global $root_dir,$_use,$file_server,$file_server_num;

		$ofilename=$files["name"];
		if(strpos(preg_replace('/^[^\.]+\./', '', $ofilename), '.') !== false) {
			msg(__lang_file_error_filename__);
		}
		if(!$txtUp) if(left($files["type"],5) == "text/") msg(__lang_file_error_filetype__);
		if(left($ofilename,1)==".") msg(__lang_file_error_filename__);
		$ext=getExt($ofilename);
		if(!$ext) msg(__lang_file_error_noext__);
		if(preg_match("/php|php3|html|htm|cgi|inc|asp|wisa/", $ext) && $GLOBALS['ext_unlimit'] != "Y") msg(__lang_file_error_ilext__);
		if($ableExt && !preg_match("/$ableExt/i",$ext)) msg(__lang_file_error_ext__);
		if(preg_match('/%00|%zz/', $ofilename)) msg(__lang_file_error_filename__);

		if (preg_match("/^(gif|jpg|jpeg|png|bmp)$/", $ext)) {
			$imgtest = getimagesize ($files['tmp_name']);
			if (!preg_match("/^image\//",$imgtest['mime'])) msg (__lang_file_error_brokenimg__);
		}

		if($files["size"]==0) msg(__lang_file_error_zerobyte__);
		print '<xmp>';
		print_r($files['size'].' / '.$upfileSize*1024);
		print '</xmp>';

		if($upfileSize && $files["size"]>$upfileSize*1024) msg(sprintf(__lang_file_error_fsizeover__, $upfileSize));

		$up_filename.=".".$ext;
		if($_use['file_server'] == "Y" && fsConFolder($updir)){
			fsUploadFile($updir, $files['tmp_name'], $up_filename);
			$up_filename_full=$file_server[$file_server_num]['url']."/".$updir."/".$up_filename;
			return array($up_filename,$ofilename,$up_filename_full);
		}
		$up_filename_full=preg_replace("/\/+/","/",$root_dir."/".$updir."/".$up_filename);
		if ($files['tmp_name'] != $up_filename_full) {
			if(!copy($files["tmp_name"],$up_filename_full)) msg(__lang_file_error_upload__.'-'.$files['error']);
			unlink($files["tmp_name"]);
		}

		return array($up_filename,$ofilename,$up_filename_full);
	}

	function downloadFile($updir, $filename, $dest) {
		global $root_dir;

		if(fsConFolder($updir)) {
			return fsFileDown($updir, $filename, $root_dir.'/'.$dest);
		} else {
			return @copy($root_dir.'/'.$updir.'/'.$filename, $root_dir.'/'.$dest.'/'.$filename);
		}
		return false;
	}

	function makeDir($up_dir) {
		global $_use,$file_server,$file_server_num,$file_server_forder;
		if($_use['file_server'] == "Y" && $file_server_forder) {
			fsMakeDir($up_dir);
			return;
		}
		if(!is_dir($up_dir)) {
			if(!mkdir($up_dir,0777)) msg(__lang_file_error_mkdir__.' -'.$up_dir);
		}
		@chmod($up_dir,0777);
	}

	function makeFullDir($fulldir) {
		$_dir=explode("/",$fulldir);
		global $root_dir,$_use,$file_server,$file_server_forder,$matched_server,$file_server_num, $fs_ftp_con;
		$file_server_forder=fsConFolder($fulldir);

		if($_use['file_server'] == "Y" && $file_server_forder){

			if (!is_array($matched_server) || count($matched_server) < 1) return;
			foreach ( $matched_server as $file_server_num) {
				$str=$file_server[$file_server_num]['file_dirname'];
				$fs_ftp_con = "";
				fileServerCon($file_server_num);
				foreach($_dir as $key=>$val) {
					$val=trim($val);
					if($val=="") continue;
					$str.="/".$val;
					makeDir($str);
				}
			}
		} else {
			$str=$root_dir;
			foreach($_dir as $key=>$val) {
				$val=trim($val);
				if($val=="") continue;
				$str.="/".$val;
				makeDir($str);
			}
		}
	}

	function delAllFile($mydir) {
		if(!is_dir($mydir)) return;
		$dir = opendir($mydir);
		while((false!==($file=readdir($dir))))
			if($file!="." and $file !="..")
				unlink($mydir.'/'.$file);
		closedir($dir);
	}

?>