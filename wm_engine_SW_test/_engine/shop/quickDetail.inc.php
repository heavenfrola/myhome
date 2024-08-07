<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  상품상세 퀵프리뷰 프레임타입
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir.'/_engine/include/common.lib.php';
	include_once $engine_dir.'/_engine/include/design.lib.php';
	$_skin = getSkinCfg();

	if($_GET['ano']) {
		$ano = numberOnly($_GET['ano']);
		$_GET['pno'] = $pno = $pdo->row("select hash from $tbl[product] where no='$ano'");
	}

	if($_skin['qd1_scroll'] == 2) {
		$qd1_scroll = "overflow: auto; height:{$height}px;";
	}

	$type = $_GET['type'];
	$type = preg_replace('/[^a-z0-9_]/', '', $type);

	$_tmp_file_name = 'shop_detail.php';

	if($type == 'popup') {
		if(numberOnly($_skin['qd1_width']) == $_skin['qd1_width']) $qd1_unit1 = 'px';
		if(numberOnly($_skin['qd1_margin']) == $_skin['qd1_margin']) $qd1_unit2 = 'px';
		$_tmp_client_file_name = 'shop_detail_popup.wsr';
		$__force_header = "<div id='preview_popup' style='background: #fff; width: {$_skin[qd1_width]}{$qd1_unit1}; margin: 0 auto; margin-top: {$_skin[qd1_margin]}{$qd1_unit2}; $qd1_scroll'>";
		$__force_footer = "</div>";
	} else {
		$striplayout = $_GET['striplayout'] = 1;
		$_tmp_client_file_name = 'shop_detail_frame.wsr';

		$__force_footer  = "<script type='text/javascript'>var is_mobile='$is_mobile';\n$(document).ready(function(){previewResize($frameno, $user_cfg[htype])})</script>";
	}

	$__force_footer .= "<script type='text/javascript'>var is_quickDetail = true;\n$(document).ready(function(){setAnchorLink()})</script>";

	include_once $root_dir.'/shop/detail.php';

?>