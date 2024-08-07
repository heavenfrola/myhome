<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  이니시스 결제 취소
	' +----------------------------------------------------------------------------------------------+*/

	$ord = $pdo->assoc("select ono, mobile from {$tbl['order']} where ono='{$card['wm_ono']}'");
	$mid = ($ord['mobile'] == 'Y' && $cfg['card_inicis_mobile_id']) ? $cfg['card_inicis_mobile_id'] : $cfg['card_mall_id'];

    // 주문서 처리와 함께 카드 취소
    if (isset($duel_card_cancel) == true && $duel_card_cancel == true) {
        $stat = 14;

        if (defined('_wisa_manage_edit_') == true) {
            msg('주문서 처리와 함께 취소가 불가능한 결제방식입니다.');
        }
        return;
    }

?>
<script type='text/javascript' src="//plugin.inicis.com/pay40.js"></script>
<script type='text/javascript'>
StartSmartUpdate();
</script>
<form name="ini" action="/main/exec.php?exec_file=card.inicis/card_cancel.exe.php" method="post">
	<input type="hidden" name="urlfix" value="Y">
	<input type="hidden" name="mid" value="<?=$mid?>">
	<input type="hidden" name="tid" value="<?=$card[tno]?>">
	<input type="hidden" name="cno" value="<?=$card[no]?>">
	<input type="hidden" name="price" value="<?=$price?>">
</form>
<script type='text/javascript'>
window.onload = function () {
	document.ini.submit();
}
</script>
