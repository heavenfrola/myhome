<?PHP

	include_once $engine_dir.'/_engine/include/common.lib.php';

	$resultCode = $_POST['resultCode'];
	$billkey = addslashes($_POST['billkey']);
	$sno = addslashes($_POST['Moid']);

	if($resultCode != 'F100') {
		$pdo->query("UPDATE {$tbl['sbscr']} set stat=31 where sbono='$sno'");
		$pdo->query("UPDATE {$tbl['sbscr_product']} set stat=31 where sbono='$sno'");
		$pdo->query("UPDATE {$tbl['sbscr_schedule_product']} set stat=31 where sbono='$sno'");

		makePGLog($sno, 'NICE PAY billkey fail');

		javac("
			opener.parent.layTgl3('order1', 'Y');
			opener.parent.layTgl3('order2', 'N');
			opener.parent.layTgl3('order3', 'Y');
		");
		msg('신용카드 빌키 발급이 실패되었습니다.', 'close');
	}

	$old_billing_key = $pdo->row("select billing_key from {$tbl['sbscr']} where sbono='$sno'");
	$pdo->query("UPDATE {$tbl['sbscr']} SET billing_key='$billkey' WHERE sbono='$sno'");

	makePGLog($sno, 'NICE PAY billkey success');
	if($old_billing_key) {
		msg('카드수정이 완료되었습니다.','close');
		exit;
	}
	$sbscr = 'Y';
	$pg_note_url = true;
	include_once $engine_dir."/_engine/order/order2.exe.php";

?>
<form id="orderfinish" method="post" action="<?=$root_url?>/shop/order_finish.php">
	<input type="hidden" name="sno" value="<?=$sno?>">
</form>
<script type="text/javascript">
var f = document.getElementById('orderfinish');
<?php if($mobile_browser != 'mobile') { ?>
f.target = (opener.parent.closed == true) ? '_blank' : opener.parent;
<?php } ?>
f.submit();
self.close();
</script>