<?PHP

	if($_GET['exec'] == 'delete') {
		$ono = addslashes(trim($_GET['ono']));
		$no = numberOnly($_GET['no']);

		$order_gift=$pdo->row("select `order_gift` from `$tbl[order]` where `ono`='$ono'");
		$_order_gift=explode("@", $order_gift);
		$_order_gift=array_diff($_order_gift, array($no));
		$order_gift=implode("@", $_order_gift);

		$pdo->query("update `$tbl[order]` set `order_gift`='$order_gift' where `ono`='$ono'");

		msg('사은품 삭제가 완료되었습니다.', 'reload', 'parent');
	}

	$ono = addslashes(trim($_POST['ono']));
	$no = numberOnly($_POST['no']);

    $ord = $pdo->row("select stat from {$tbl['order']} where ono=?", array($ono));
    if ($ord['stat'] > 2) {
        msg($_order_stat[3].' 이전 상태에서만 사은품을 추가할 수 있습니다.');
    }

	$pdo->query("update `$tbl[order]` set `order_gift`=concat(`order_gift`,'@$no') where `ono`='$ono'");
?>
<script type="text/javascript">
alert('사은품 추가가 완료되었습니다.');
if(parent.opener.scrollReload) {
	parent.opener.scrollReload();
} else {
	parent.opener.location.reload();
}
parent.window.close();
</script>