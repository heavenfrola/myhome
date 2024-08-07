<?PHP

/* +----------------------------------------------------------------------------------------------+
' |  카카오페이 결제 취소
' +----------------------------------------------------------------------------------------------+*/

include_once "incKakaopayCommon.php";
include_once $engine_dir."/_engine/include/common.lib.php";

$_tmp_price = parsePrice($card[wm_price], false);

if($price>0) {//부분취소
	$CancelCode = '1';
	$CancelAmt = $price;
	$CheckRemainAmt = "";

	$sql = "select count(*) from wm_card_cc_log where ono='".$card['wm_ono']."' and stat=2";
	$cancel_cnt = $pdo->row($sql);
	if($cancel_cnt>0) {
		$CancelNo = $cancel_cnt+1;
	}else {
		$CancelNo = "1";
	}
}else {
	$CancelCode = '0';
	$CancelAmt = $_tmp_price;
	$CancelNo = "";
	$CheckRemainAmt = "";
}

?>
<form name="tranMgr" method="post" action="<?=$root_url?>/main/exec.php?exec_file=card.kakao/card_cancel.exe.php">
	<input type="hidden" name="MID" id="" value="<?=$MID?>" maxlength="30" />
	<input type="hidden" name="TID" id="" value="<?=$card[tno]?>" maxlength="30" />
	<input type="hidden" name="Amt" id="" value="<?=$CancelAmt?>" />
	<input type="hidden" name="CancelMsg" id="" value="고객 요청" /></td>
	<input type="hidden" name="PartialCancelCode" id="" value="<?=$CancelCode?>" /></td>
	<input type="hidden" name="cno" value="<?=$card[no]?>">
	<input type="hidden" name="CancelNo" value="<?=$CancelNo?>">
	<input type="hidden" name="CheckRemainAmt" value="<?=$CheckRemainAmt?>">
</form>

<script type='text/javascript'>
window.onload = function () {
	document.tranMgr.submit();
}
</script>


