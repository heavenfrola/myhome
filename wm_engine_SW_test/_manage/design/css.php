<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  스타일 시트 편집
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/design.lib.php";
	include_once $engine_dir."/_manage/design/version_check.php";

	$_edt_mode="css";
	$_skin_name=editSkinName();
	$file_dir=$root_dir."/_skin/".$_skin_name."/";

	$_pg_title=$_skin_name." 스킨 스타일시트 (CSS)";
	$_edit_pg=$edit_pg="style.css";

	versionChk("V3");

	include $engine_dir."/_manage/design/editor.php";

?>