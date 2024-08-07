<?PHP

	set_time_limit(0);
	define('_common_header', true);

	require_once $engine_dir.'/_engine/include/common.lib.php';
	require_once 'openapi/openapi.class.php';

	$api = new Openapi();

	$action = $_REQUEST['action'];
	if(method_exists($api, $action)) {
		$api->$action();
	}

?>