<?
	class FileLocal extends Filesystem {

		var $root_dir;

		function FileLocal($root_dir = null) {
			$this->root_dir = $root_dir;
		}

		function _makeDir($dirname, $permission) {
			if(!is_dir($this->root_dir.'/'.$dirname)) {
				$dirname = $this->root_dir."/".$dirname;
				$res = mkdir($dirname, $permission);
				if(!$res) exit("디렉토리 생성 실패 - ".$dirname);
			}
			@chmod($dirname, $permission);
		}

		function _upload($source, $target) {
			$this->makeFullDir(dirname($target));
			$target = $this->root_dir.'/'.$target;

			if(is_array($source)) {
				$result = move_uploaded_file($source['tmp_name'], $target);
			} else {
				$result = copy($source, $target);
			}
			if(!$result) return false;

			chmod($target, 0777);

			return true;
		}

		function delete($filename) {
			if(is_dir($filename)) {
				rmdir($this->root_dir.'/'.$filename);
			} else {
				unlink($this->root_dir.'/'.$filename);
			}
			return true;
		}

		function _writeLog($filename) {
			//
		}

	}
?>