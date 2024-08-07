<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  레이아웃 편집
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/design.lib.php";
	include_once $engine_dir."/_manage/design/version_check.php";
	$_skin_name=editSkinName();

	$part = addslashes($_GET['part']);

	$_edt_mode="common";
	$file_dir=$root_dir."/_skin/".$_skin_name."/COMMON/";
	$_insert_file_arr=array(
	"{{T}}"=>"header.".$_skin_ext['c'],
	"{{L}}"=>"leftmenu.".$_skin_ext['c'],
	"{{M}}"=>"content_frame.".$_skin_ext['c'],
	"{{Q}}"=>"quick.".$_skin_ext['c'],
	"{{B}}"=>"footer.".$_skin_ext['c']
	);

	if(!$part) msg("편집하실 부분을 선택하세요", "close");
	$_pg_title=$_layout_name[$part]." 내용";
	$_edit_pg=$edit_pg=$_insert_file_arr[$part];

?>
<div class="box_title first">
	<h2 class="title"><?=$_layout_name[$part]?> 공통페이지 편집</h2>
</div>
<?php
	include $engine_dir."/_manage/design/editor.php";
?>