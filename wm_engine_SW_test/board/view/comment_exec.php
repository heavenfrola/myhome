<?PHP

	if($ajax_comment == "Y") {
		header("Content-type:text/html; charset=euc-kr");
		$_popup_list[] = "board_index.php";
		$_this_ajax_page = 1;
	}
	$mari_mode = "view@view";
	include_once $engine_dir."/_engine/common/skin_index.php";
	include "comment.php";

?>