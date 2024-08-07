<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  팝업 미리보기
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/popup.lib.php";

	$no = numberOnly($_REQUEST['no']);
	if($no > 0) {
		$data = $pdo->assoc("select * from {$tbl['popup']} where no='$no'");
	}

	foreach($_POST as $key => $val) {
		if($_POST[$key]) $data[$key] = $_POST[$key];
	}

	if(empty($data['w']) == true) $data['w'] = 500;
	if(empty($data['h']) == true) $data['h'] = 500;
	if(!$data['frame']){
		$data['frame'] = $pdo->row("select no from {$tbl['popup_frame']} order by no asc");
	}

	$preview = 'Y';

?>
	<style type="text/css" title="">
	html {overflow:hidden;}
	body {background:none;}
	</style>
	<div style="width:<?=$data['w']?>px; height:<?=$data['h']?>px;">
		<?php generate_popup($data); ?>
	</div>
	<?php if ($data['layer'] == 'Y') { ?>
	<script type="text/javascript">
		$(function() {
			selfResize();
		});
	</script>
	<?php } ?>
</body>
</html>