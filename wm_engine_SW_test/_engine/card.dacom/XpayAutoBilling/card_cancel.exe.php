<?PHP

	include_once $engine_dir."/_engine/include/common.lib.php";
	printAjaxHeader();

	$sno = addslashes($_POST['sno']);
	$res_cd = numberOnly($_POST['res_cd']);
	$res_msg = mb_convert_encoding($res_msg, _BASE_CHARSET_, 'utf-8');
	$res_msg = addslashes($res_msg);

	if(!$sno) exit('sno');

	$ordsub = $pdo->assoc("select sbono, stat, pay_type from $tbl[sbscr] where sbono='$sno'");

	if($ordsub['stat'] != 11) exit('stat');

	$pdo->query("update $tbl[sbscr] set stat=31 where sbono='$sno'");
	$pdo->query("update $tbl[sbscr_product] set stat=31 where sbono='$sno'");
	$pdo->query("update $tbl[sbscr_schedule_product] set stat=31 where sbono='$sno'");

	makePGLog($sno, 'xpay card_cancel.exe.php');

	exit('OK');

?>