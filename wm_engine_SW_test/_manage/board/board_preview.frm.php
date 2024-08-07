<style type="text/css" title="">
body {background:#fff;}
</style>
<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  게시판 스킨 미리보기
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/design.lib.php";

	$board_dir=$root_dir."/board/_skin";
	if(!$skin || !is_dir($board_dir."/".$skin)){
		echo "해당 스킨이 존재하지 않습니다";
		return;
	}

	function mngPreviewConfig(){
		global $db, $config, $skin, $member;
		$config[db]=$db;
		$config[skin]=$skin;
		$config[skin]=$skin;
		$config[title]="스킨 미리보기";
		$config[page_row]=10;
		$config[page_block]=10;
		$config[gallery_cols]=2;
		$config[auth_list]=10;
		$config[auth_write]=10;
		$config[auth_view]=10;
		$config[auth_reply]=10;
		$config[auth_comment]=10;
		$config[auth_upload]=10;
	}

	$_tmp_file_name="board_index.php";
	$mng_preview=1;
	$_popup_list[]="board_index.php";
?>
<script language="JavaScript">
	document.onclick=function (){
		return false;
	}
</script>
<?
	include $engine_dir."/board/index.php";
?>