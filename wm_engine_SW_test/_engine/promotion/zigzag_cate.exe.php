<?PHP

	include_once $engine_dir.'/_engine/include/common.lib.php';

	if($_REQUEST['apiKey'] != $cfg['zigzag_apikey']) {
		exit(json_encode(array(
			'error' => 'wrong apiKey'
		)));
	}

	$name_cache = array();
	$res = $pdo->iterator("select no, name from {$tbl['category']} where ctype=1 and private!='Y'");
    foreach ($res as $data) {
		$name_cache[$data['no']] = stripslashes($data['name']);
	}

	function printZCategory($parent = 0, $level = 1, &$array) {
		global $tbl, $_cate_colname, $name_cache, $pdo;

		$pname = $_cate_colname[1][($level-1)];
		$asql = ($pname) ? "and $pname='$parent'" : "";

		$res = $pdo->iterator("select * from {$tbl['category']} where ctype=1 and private!='Y' $asql");
        foreach ($res as $data) {
			$full_category_no = $full_category_name = array();
			foreach(array('big', 'mid', 'small') as $val) {
				if($data[$val]) {
					$full_category_no[] = $data[$val];
					$full_category_name[] = $name_cache[$data[$val]];
				}
			}
			$full_category_no[] = $data['no'];
			$full_category_name[] = stripslashes($data['name']);

			$array[] = array(
				'code' => $data['no'],
				'name' => stripslashes($data['name']),
				'level' => $data['level'],
				'parent' => $parent,
				'full_category_no' => $full_category_no,
				'full_category_name' => $full_category_name
			);
            if ($data['level'] < 4) {
    			printZCategory($data['no'], $data['level']+1, $array);
            }
		}
	}

	$array = array();
	printZCategory(0, 1, $array);

	$json_options = null;
	if(defined('JSON_PRETTY_PRINT')) {
		$json_options = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE;
	}

	exit(json_encode($array, $json_options));

?>