<?

	/* +----------------------------------------------------------------------------------------------+
	' |  파일 클래스
	' +----------------------------------------------------------------------------------------------+*/
	class Filesystem {

		function FileSystem() {
		}

		// 디렉토리 생성
		function makeDir($dirname, $permission = 0777) {
			$this->_makeDir($dirname, $permission);
		}

		function makeFullDir($fulldir, $permission = 0777) {
			$fulldir = preg_replace("/\/+/", "/", $fulldir);
			$_dir = explode("/", $fulldir);

			foreach($_dir as $key=>$val) {
				$str.="/".$val;
				$this->makeDir($str, $permission);
			}
		}

		function upload($source, $target, $allow = null) {
			if(is_array($allow) && count($allow) > 0) {

				$name = is_array($source) ? $source['name'] : $source;
				$ext = $this->getExt($name);
				if(in_array($ext, $allow) == false) {
					return false;
				}
			}

			return $this->_upload($source, $target);
		}

		function delete($filename) {
			$this->_delete($filename);
		}

		// 로그파일 작성
		function writeLog($filename, $content, $mode = "a+") {
			$fp = fopen($filename, $mode);
			fwrite($fp, $content."\n");
			fclose($fp);
			chmod($filename, 0777);

			$this->_writeLog($filename);
		}

		function makeFileName($file) {
			if(is_array($file)) $file = $file['name'];
			$file = md5($file.time().rand(0,12345)).'.'.$this->getExt($file);

			return $file;
		}

		// 파일 확장자 구하기
		function getExt($filename) {
			if (!preg_match("/\./", $filename)) return null;

			$ext = preg_replace("/^.*\.([^.]+)$/", "$1", $filename);
			$ext = strtolower($ext);
			return $ext;
		}

		// 파일 사이즈를 문자형식으로 구하기
		function filesizeStr($size, $comma = 2) {
			$size = preg_replace("/[^0-9]/", "", $size);

			$unit = 0;
			$unit_array = array("B", "KB", "MB", "GB", "TB", "PB");

			while ($size / 1024 >= 1) {
				$size = $size / 1024;
				$unit++;
			}

			$size = number_format($size, $comma);
			$size = preg_replace("/\.?0+$/", "", $size);
			$size = $size." ".$unit_array[$unit];

			return rtrim($size);
		}
	}
?>