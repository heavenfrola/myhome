<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  EMS배송비 설정 처리
	' +----------------------------------------------------------------------------------------------+*/

	if($exec == 'grade') {
		foreach($grade as $key => $val) {
			if ($val) $config .= "\t".'$ems_nation[\''.addslashes($key).'\'] = "'.$val."\";\n";
		}
		$config_file = $root_dir."/_data/config/ems_nation.php";
	}

	if($exec == 'prc') {
		foreach($_POST as $key => $val) {
			if($val && preg_match("/^emsp?_/", $key)) {
				$config .= '$ems_prc[\''.$key.'\'] = "'.$val."\";\n";
			}
		}
		$config_file = $root_dir."/_data/config/ems_prc.php";
	}

	$config = "<?\n$config?>";
	$fp = fopen($config_file, "w");
	fwrite($fp, $config);
	fclose($fp);
	chmod($root_dir."/_data/config/ems_prc.php", 0777);

	msg("저장되었습니다", "reload", "parent");

?>