<?PHP

	$mid = ($cfg['pg_version'] == 'INILite') ? $cfg['card_inicis_id'] : $cfg['escrow_mall_id'];

?>
<div style="display:none">
	<script language=javascript src="//plugin.inicis.com/pay60_escrow.js"></script>
	<script language="Javascript">
		StartSmartUpdate();

		var openwin;
		function pay(frm) {
			if(document.ini.clickcontrol.value == "enable") {
				if((navigator.userAgent.indexOf("MSIE") >= 0 || navigator.appName == 'Microsoft Internet Explorer') && (document.INIpay == null || document.INIpay.object == null)) {

					alert("플러그인을 설치 후 다시 시도 하십시오.");
					return false;
				} else {

					if(MakePayMessage(frm)) return true;
					else return false;
				}
			} else {

				return false;
			}
		}

		function enable_click() {
			document.ini.clickcontrol.value = "enable"
		}

		function disable_click() {
			document.ini.clickcontrol.value = "disable"
		}

		function focus_control() {
			if(document.ini.clickcontrol.value == "disable") openwin.focus();
		}
	</script>
	<form name="ini" method="post" target="hidden<?=$now?>" action="<?=$root_url?>/main/exec.php?exec_file=card.inicis/INILite/escrow.exe.php" onSubmit="return pay(this)" style="display:none">
		<input type="hidden" name="oid" value="">
		<input type="hidden" name="tid" value="">
		<input type="hidden" name="mid" value="<?=$mid?>">
		<input type="hidden" name="escrow_type" value="confirm">
		<input type="hidden" name="paymethod" value="">
		<input type="hidden" name="encrypted" value="">
		<input type="hidden" name="sessionkey" value="">
		<input type="hidden" name="version" value="5000">
		<input type="hidden" name="clickcontrol" value="">
		<input type="hidden" name="acceptmethod" value=" ">
	</form>
</div>