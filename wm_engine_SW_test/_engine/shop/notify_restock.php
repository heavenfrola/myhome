<?php

	/* +----------------------------------------------------------------------------------------------+
	' |  order 페이지 재입고요청
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";

	printAjaxHeader();

	$_tmp_file_name = 'shop_notify_restock.php';
	$striplayout = $_GET['striplayout'] = 1;

	$pno = numberOnly($_REQUEST['pno']);
	$hash = $pdo->row("select hash from $tbl[product] where no='$pno'");
	$prd = checkPrd($hash);
	$prdCart = new OrderCart();
	$prdCart->addCart($prd);
	$prdCart->complete();
	$prdCart->pay_prc -= $prdCart->dlv_prc;

	// 디자인 버전 점검 & 페이지 출력
	include_once $engine_dir."/_engine/common/skin_index.php";

?>