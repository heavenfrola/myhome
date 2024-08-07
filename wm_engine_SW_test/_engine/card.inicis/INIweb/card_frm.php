<script language="javascript" type="text/javascript" src="//stdpay.inicis.com/stdjs/INIStdPay.js" charset="UTF-8"></script>
<script type="text/javascript">
function payaction() {
	INIStdPay.pay('inipay');
}
</script>

<form id="inipay" name="inipay" method="POST">
	<input type="hidden" name="currency" value="WON">
	<input type="hidden" name="mKey" value="">
	<input type="hidden" name="quotabase" value="">
	<input type="hidden" name="acceptmethod" value="">
	<input type="hidden" name="version" value="1.0">
	<input type="hidden" name="closeUrl" value="<?=$root_url?>/main/exec.php?exec_file=card.inicis/INIweb/close.php">
	<input type="hidden" name="returnUrl" value="<?=$root_url?>/main/exec.php?exec_file=card.inicis/INIweb/card_pay.exe.php">
	<input type="hidden" name="mid" value="">
	<input type="hidden" name="oid" value="">
	<input type="hidden" name="goodname" value="">
	<input type="hidden" name="price" value="">
	<input type="hidden" name="signature" value="">
	<input type="hidden" name="timestamp" value="">
	<input type="hidden" name="buyername" value="">
	<input type="hidden" name="buyeremail" value="">
	<input type="hidden" name="buyertel" value="">
	<input type="hidden" name="gopaymethod" value="">
	<input type="hidden" name="charset" value="UTF-8">
	<input type="hidden" name="payViewType" value="">
	<input type="hidden" name="languageView" value="">
	<input type="hidden" name="merchantData" value="">
	<input type="hidden" name="vbankRegNo" value="">
	<input type="hidden" name="tax" value="">
	<input type="hidden" name="taxfree" value="">
</form>