<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  스킨 Ajax 출력
	' +----------------------------------------------------------------------------------------------+*/

	define("_common_header",true);

	include_once $engine_dir.'/_engine/include/common.lib.php';
	printAjaxHeader();

	$obj_id = $_GET['obj_id'];
	$_tmp_file_name = $_GET['_tmp_file_name'];
	$single_module = $_GET['single_module'];
	$document_url = $_GET['document_url'];
	$ajaxSkin = $_GET['ajaxSkin'];
	$module_page = numberOnly($_GET['module_page']);
	$_more_page = numberOnly($_GET['more_page']);

	if($_more_page > 0) {
		if(is_array($_SESSION[$single_module]) == false) return;
		foreach($_SESSION[$single_module] as $key => $val) {
			${$key} = $_GET[$key] = $val;
		}
		$module_page = $_GET['module_page'] = $_more_page;
	} else {
		$_SESSION['b_'.$single_module] = $_GET;
	}

	$module = addslashes($_GET['module']);
	include_once $engine_dir.'/_engine/common/skin_index.php';

?>