<?

	class Ftp extends Filesystem {

		var $connections = array();
		var $root_dir	= array();
		var $mode;

		function Ftp() {
		}

		function connect($ftp_addr, $ftp_port = null, $ftp_id = null, $ftp_pwd = null, $root_dir = null) { // 멀티서버 대응, 인스턴스를 배열로 저장
			if(is_array($ftp_addr)) {
				$ftp_port	= $ftp_addr[1];
				$ftp_id		= $ftp_addr[2];
				$ftp_pwd	= $ftp_addr[3];
				$root_dir	= $ftp_addr[4];
				$ftp_addr	= $ftp_addr[0];
			}

			$connect = ftp_connect($ftp_addr, $ftp_port);
			ftp_login($connect, $ftp_id, $ftp_pwd);
			ftp_pasv($connect, true);
			ftp_set_option($connect, FTP_TIMEOUT_SEC, 20);

			$this->connections[] = $connect;
			$this->root_dir[] = $root_dir;
			$this->mode = FTP_BINARY;

			return $connect;
		}

		function _upload($filename, $target) {
			$updir = dirname($target);
			if($_updir == '.') $updir = '';

			$noerror = true;
			foreach ($this->connections as $no => $resource) {
				$remote = "/".$this->root_dir[$no]."/".$updir."/".basename($target);
				$remote = preg_replace("/\/+/", "/", $remote);
				$this->makeFullDir($updir);
				$return = ftp_put($resource, $remote, $filename, $this->mode);
				if (!$return) {
					echo("파일 업로드 실패 -FTP instance #$no");
					$noerror = false;
				}
				else ftp_site($resource, "CHMOD 0777 $remote");
			}

			return $noerror;
		}

		function _makeDir($dirname, $permission) {
			$permission = decoct($permission);
			foreach ($this->connections as $no => $resource) {
				$newdir = preg_replace("/^".str_replace("/", "\/", $this->root_dir[$no])."/", "", $dirname);
				$newdir = "/".$this->root_dir[$no]."/".$newdir;
				$newdir = preg_replace("/\/+/", "/", $newdir);

				$ck = ftp_chdir($resource, "/");
				if(!@ftp_chdir($resource, $newdir)) {
					if(!@ftp_mkdir($resource, $newdir)) {
						echo "디렉토리 생성 실패 - FTP Instance #$no";
					} else {
						ftp_site($resource, "CHMOD $permission $newdir");
					}
				}
			}
		}

		function chmod($file, $permission) {
			$permission = decoct($permission);
			foreach ($this->connections as $no => $resource) {
				$return = @ftp_site($resource, "CHMOD $permission $file");
			}

			return $return;
		}

		function pwd($instance = 0){
			$pwd = ftp_pwd($this->connections[$instance]);

			if ($this->root_dir[$instance] != '/') {
				$replace = preg_replace('/\//', '\/', $this->root_dir[$instance]);
				$pwd = preg_replace("/^$replace/", "", $pwd);
			}

			if(!$pwd) $pwd = '/';

			return $pwd;
		}

		function _writeLog($filename) {
			$updir = dirname($filename);

			$this->mode = FTP_ASCII;
			$this->upload($updir, $filename);
			$this->mode = FTP_BINARY;
		}

		function cd($dir = '/') {

			$result = 0;
			foreach ($this->connections as $no => $resource) {
				$dir = $this->adAddr($dir, $no);
				if(!$dir) continue;

				if(!@ftp_chdir($this->connections[$no], $dir)) return false;
			}

            return true;
		}

		function nlist($dir = '/', $instance = 0) {
			$dir = $this->adAddr($dir);
			return ftp_nlist($this->connections[$instance], $dir);
		}

		function adAddr($path, $instance = 0){
			$path = $this->root_dir[$instance].'/'.$path;
			if(preg_match('/\/../', $path)) {
				$path = preg_replace('/^(.*)\/[^\/]+\/\.\./', '/$1', $path);
			}
			$path = preg_replace('/\/+/', '/', $path);
			return $path;
		}

		function isDir($path, $instance = 0) {
			$path = $this->adAddr($path);

			$check = @ftp_chdir($this->connections[$instance], $path);
			if($check) {
				ftp_chdir($this->connections[$instance], '..');
				return true;
			}
			return false;
		}

		function filesize($path, $instance = 0) {
			$path = $this->adAddr($path);
			$size = ftp_size($this->connections[$instance], $path);
			return $size;
		}

		function rename($source, $target) {
			foreach ($this->connections as $no => $resource) {
				$_source = $this->adAddr($source);
				$_target = $this->adAddr($target);

				$result = ftp_rename($this->connections[$no], $_source, $_target);
				if(!$result) return false;
			}
			return $result;
		}

		function delete($path) {
			foreach ($this->connections as $no => $resource) {
				$_path = $this->adAddr($path);
				$isdir = $this->isDir($path, $no);

				if ($isdir == 1) $result = ftp_rmdir($this->connections[$no], $_path);
				else $result = ftp_delete($this->connections[$no], $_path);
				if(!$result) return false;
			}
			return $result;
		}

	}
?>