<?PHP

	include_once $engine_dir."/_engine/include/common.lib.php";
	printAjaxHeader();

	if(function_exists('extractParam')) {
		extractParam();
	}

	$ono = addslashes($_POST['ono']);
	$res_msg = mb_convert_encoding($_POST['res_msg'], _BASE_CHARSET_, 'utf-8');
	$res_msg = addslashes($res_msg);

	if(!$ono) exit('ono');

	$ord = $pdo->assoc("select ono, stat, pay_type from $tbl[order] where ono='$ono'");
	$card_tbl = ($ord['pay_type'] == 4) ? $tbl['vbank'] : $tbl['card'];
	$card = $pdo->assoc("select * from $card_tbl where wm_ono='$ord[ono]'");

	if($ord['stat'] != 11) exit('stat');

	$pdo->query("update $tbl[order] set stat=31 where ono='$ono'");
	$pdo->query("update $tbl[order_product] set stat=31 where ono='$ono'");
	$pdo->query("update $card_tbl set stat='3', res_cd='$res_cd', res_msg='$res_msg' where wm_ono='$ono'");

	include_once $engine_dir.'/_engine/include/naverMilage.class.php';
	$naverMilage = new naverMilage();
	$naverMilage->changeStatus($card['wm_ono'], 13);

	exit('OK');

?>