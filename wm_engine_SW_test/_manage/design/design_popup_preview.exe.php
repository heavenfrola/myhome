<?PHP

	include_once $engine_dir.'/_engine/include/popup.lib.php';
	include_once $engine_dir."/_engine/include/common.lib.php";

	$popno = numberOnly($_GET['popno']);

	common_header();

?>
<style type="text/css">
html {overflow:hidden;}
</style>
<div id='wm_popup_<?=$popno?>'></div>
<?=stripslashes(generate_popup($popno));?>
<script type="text/javascript">
$(window).load(function() {
	var w = h = 0;
	window.resizeTo(100, 100);

	setTimeout(function() {
		var ori_width = document.documentElement.scrollWidth;
		var ori_height = document.documentElement.scrollHeight;

		var w1 = (w) ? w : document.documentElement.scrollWidth;
		var h1 = (h) ? h : document.documentElement.scrollHeight;
		if(w1 < 100) w1 = 100;
		if(h1 < 100) h1 = 100;
		window.resizeTo(w1, h1);

		setTimeout(function() {
			window.resizeBy((w1-document.documentElement.clientWidth), (h1-document.documentElement.clientHeight));
		}, 50);
	}, 50);

});
</script>
</body>
</html>