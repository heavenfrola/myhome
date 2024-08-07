<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  스크립트 편집
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/design.lib.php";
	include_once $engine_dir."/_manage/design/version_check.php";

	$_edt_mode="script";
	$_skin_name=editSkinName();
	$file_dir=$root_dir."/_skin/".$_skin_name."/";

	$_pg_title=$_skin_name." 스킨 자바 스크립트";
	$_edit_pg=$edit_pg="script.js";

	versionChk("V3");

?>
<div class="box_title first">
	<h2 class="title">스크립트 편집</h2>
</div>
<?php
	include $engine_dir."/_manage/design/editor.php";

?>