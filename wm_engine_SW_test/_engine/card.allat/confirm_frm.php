<script language=JavaScript charset='euc-kr' src="https://tx.allatpay.com/common/NonAllatPayREPlus.js"></script>
<script language=Javascript>
function ftn_app(dfm) {
	Allat_Plus_Escrow(dfm, "0", "0");
}

function result_submit(result_cd,result_msg,enc_data) {
	Allat_Plus_Close();

	if(result_cd != '0000') {
		alert(result_cd + " : " + result_msg);
	} else {
		sendFm.allat_enc_data.value = enc_data;

		sendFm.action = "allat_escrowconfirm.jsp";
		sendFm.method = "post";
		sendFm.target = "_self";
		sendFm.submit();
	}
}
</script>
<form id="allat_confirm_form" name="sendFm" method="POST">
	<input type="hidden" name="allat_shop_id" value="">
	<input type="hidden" name="allat_order_no" value="">
	<input type="hidden" name="allat_pay_type" value="VBANK">
	<input type="hidden" name="shop_receive_url" value="<?=$root_url?>/main/exec.php?exec_file=mypage/receive.exe.php">
	<input type="hidden" name="allat_enc_data" value="">
	<input type="hidden" name="allat_opt_pin" value="NOUSE">
	<input type="hidden" name="allat_opt_mod" value="APP">
</form>