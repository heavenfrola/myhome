<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  윙Mobile 레이아웃
	' +----------------------------------------------------------------------------------------------+*/

?>
<style type="text/css">
.contentFrm table {
	width: 100% !important;
}
</style>

<?
	$type = $_GET['type'] = 'mobile';
	include $engine_dir.'/_manage/design/layout.frm.php';
?>