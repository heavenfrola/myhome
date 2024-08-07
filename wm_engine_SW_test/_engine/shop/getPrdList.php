<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  윙모바일 더보기 처리용 ajax 페이지
	' +----------------------------------------------------------------------------------------------+*/

	define("_common_header",true);

	$module = addslashes($_GET['module']);

	include_once $engine_dir.'/_engine/include/common.lib.php';
	printAjaxHeader();

	include_once $engine_dir.'/_engine/common/skin_index.php';

?>