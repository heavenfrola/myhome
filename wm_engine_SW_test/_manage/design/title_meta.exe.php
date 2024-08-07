<?PHP

	switch($_POST['exec']) {
		case 'removeHead' :
			$code = addslashes(trim($_POST['code']));
			$pdo->query("delete from {$tbl['default']} where code='$code' and code like 'head_%'");
			break;
		case 'removechannel' :
			$nmae = addslashes(trim($_POST['name']));
			$pdo->query("delete from {$tbl['config']} where name='$name'");
			break;
		case 'relation_channel' :
			$relation_channel = $_POST['relation_channel'];
			$scheme = $_POST['scheme'];
			if(is_array($relation_channel) == false) msg('연관채널을 설정해주세요.');
			for($key = 0; $key <= 8; $key++) {
				$fd = 'relation_channel'.($key+1);
				$tmp = parse_url($relation_channel[$key]);
				if($tmp['scheme']) {
					$relation_channel[$key] = str_replace($tmp['scheme'].'://', '', $relation_channel[$key]);
				}
				$val = addslashes(trim($scheme[$key].$relation_channel[$key]));

				if(!$relation_channel[$key]) {
					$pdo->query("delete from {$tbl['config']} where name='$fd'");
					continue;
				}

				$_POST[$fd] = $val;
			}
			unset($_POST['relation_channel']);

			include $engine_dir.'/_manage/config/config.exe.php';
			break;
		case 'robots' :
			$tmp_file = $root_dir.'/_data/robots.txt';
			$robots_content = addslashes($_POST['robots_content']);
			$robot_file = fopen($tmp_file,'w');
			fwrite($robot_file, $robots_content);
			fclose($robot_file);

			include_once $engine_dir."/_engine/include/img_ftp.lib.php";
			ftpUploadFile($root_dir, array(
				'name' => 'robots.txt',
				'tmp_name' => $tmp_file,
				'size' => strlen($robots_content)
			), 'txt');
			unlink($tmp_file);

			if(!isTable($tbl['robots_log'])) {
				include_once $engine_dir.'/_config/tbl_schema.php';
				$pdo->query($tbl_schema['robots_log']);
			}
			$pdo->query("insert into {$tbl['robots_log']} (content, admin_id, reg_date) values ('$robots_content', '{$admin['admin_id']}', '$now')");

			msg('', 'reload', 'parent');
			break;
	}
	exit;

?>