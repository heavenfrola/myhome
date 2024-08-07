<?PHP

	if($cfg['use_cdn'] == 'Y') {
		for($i = 1; $i <= $cfg['file_server_ea']; $i++) {
			if(in_array('attach', $file_server[$i]['file_type'])) {
				$fserver = $file_server[$i];
			}
		}

		if(is_array($fserver)) {
			include_once $engine_dir."/_engine/include/classes/filesystem.class.php";
			include_once $engine_dir."/_engine/include/classes/ftp.class.php";
			$wdisk_con = new FTP();
			$wdisk_con->connect(
				$fserver['file_server'][0],
				$fserver['file_server'][3],
				$fserver['file_server'][1],
				$fserver['file_server'][2],
				$fserver['file_dirname']
			);
			return;
		}
	}

	if(file_exists($engine_dir.'/_engine/include/account/wdisk.inc.php')) {
		include_once $engine_dir.'/_engine/include/account/wdisk.inc.php';
	}

?>