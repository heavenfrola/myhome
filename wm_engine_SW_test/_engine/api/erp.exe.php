<?PHP

	set_time_limit(0);
	define("_common_header",true);

	include_once $engine_dir.'/_engine/include/common.lib.php';
	include_once $engine_dir.'/_engine/api/WmAPI.api.php';

	function printr($array, $depth = 0) {
		for($i = 0; $i <= $depth; $i++) {
			$blank.= "&nbsp;&nbsp;&nbsp;";
		}

		foreach($array as $key => $val) {
			if(is_object($val) || is_array($val)) {
				echo $blank."- $key<br />";
				printr($val, ($depth+1));
			} else {
				echo $blank."[$key] => $val<br />";
			}
		}
	}

	$hash = addslashes($_REQUEST['key']);
	$action = addslashes($_REQUEST['action']);
	$param = array(addslashes($_REQUEST['param']));
	if(!is_array($param)) $param = array();

	$cfg['erp_bendor'] = 'ezAdmin';

	include_once $engine_dir.'/_engine/api/class/erpAPI.api.php';

	if(!$action || !method_exists($erpAPI, $action)) {
		$erpAPI->result(false, '지원하지 않는 액션입니다.');
	}

	$return = call_user_func_array(array($erpAPI, $action), $param);

?>