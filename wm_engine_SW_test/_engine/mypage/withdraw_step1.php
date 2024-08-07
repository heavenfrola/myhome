<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  탈퇴요청 작성
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	memberOnly();
	common_header();
?>
<script language="JavaScript" type="text/javascript" src="<?=$engine_url?>/_engine/common/member.js"></script>
<?
	// 디자인 버전 점검 & 페이지 출력
	include_once $engine_dir."/_engine/common/skin_index.php";
?>