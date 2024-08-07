<?PHP

	if(file_exists($engine_dir.'/_engine/include/account/common.inc.php')) {
		include_once $engine_dir.'/_engine/include/account/common.inc.php';
	} else {
		include_once $engine_dir.'/_engine/include/common.lib.php';
		$array = array(
			'company_biz_num',
			'company_owner',
			'company_addr1',
			'company_biz_type1',
			'company_biz_type2',
			'company_name'
		);
		foreach($array as $val) {
			$cfg[$val] = mb_convert_encoding($cfg[$val], 'utf8', _BASE_CHARSET_);
			echo $val."^c1^".$cfg[$val]."^c2^\n";
		}
	}

?>