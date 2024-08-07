<?PHP

	include_once $engine_dir.'/_engine/include/common.lib.php';

	if(isset($cfg['use_zigzag']) == false || $cfg['use_zigzag'] != 'Y') {
		exit(json_encode(array(
			'status' => 'available'
		)));
	}

	if($cfg['use_zigzag'] == 'Y') {
		exit(json_encode(array(
			'status' => 'running'
		)));
	}

?>