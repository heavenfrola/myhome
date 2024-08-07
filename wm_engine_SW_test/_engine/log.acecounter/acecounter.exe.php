<?PHP

	foreach($_GET as $key => $val) {
		$log .= "[GET] $key => $val\n";
	}
	foreach($_POST as $key => $val) {
		$log .= "[POST] $key => $val\n";
	}

	$fp = fopen($root_dir.'/_data/acecounter.txt', 'w');
	fwrite($fp, $log);
	fclose($fp);



?>