<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  이니시스 결제 폼
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir.'/_engine/card.inicis/card_frm_openweb.php';
	return;

?>
<div style="display:none">
<script language=javascript src="//plugin.inicis.com/pay40.js"></script>
<script language=javascript>
StartSmartUpdate();

var openwin;

function pay(frm)
{
	if(document.ini.clickcontrol.value == "enable")
	{
		if(document.ini.goodname.value == "") {
			window.alert("상품명이 빠졌습니다. 필수항목입니다.");
			return false;
		} else if(document.ini.price.value == "") {
			alert("상품가격이 빠졌습니다. 필수항목입니다.");
			return false;
		} else if(document.ini.buyername.value == "") {
			alert("구매자명이 빠졌습니다. 필수항목입니다.");
			return false;
		} else if(document.ini.buyeremail.value == "") {
			alert("구매자 이메일주소가 빠졌습니다. 필수항목입니다.");
			return false;
		} else if(document.ini.buyertel.value == "") {
			alert("구매자 전화번호가 빠졌습니다. 필수항목입니다.");
			return false;
		} else if(document.INIpay == null || document.INIpay.object == null) {
			alert(_lang_pack.pg_error_install);
			return false;
		} else {
			if (MakePayMessage(frm)) {
				disable_click();
				return true;
			} else {
				alert(_lang_pack.pg_error_cancel);
				cancelf=document.pay_cFrm;
				cancelf.ono.value=frm.oid.value;
				cancelf.submit();
                layTgl3('order1', 'Y');
                layTgl3('order2', 'N');
                layTgl3('order3', 'Y');
				return false;
			}
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
	if(document.ini.clickcontrol.value == "disable")
		openwin.focus();
}
</script>
</div>

<form name="ini" method="POST" target="hidden<?=$now?>" action="<?=$root_url?>/main/exec.php?exec_file=card.inicis/card_pay.exe.php" onSubmit="return pay(this)">
<input type=hidden name=goodname size=20 value="">
<input type=hidden name=price size=20 value="">
<input type=hidden name=buyername size=20 value="">
<input type=hidden name=buyeremail size=20 value="">
<input type=hidden name=parentemail size=20 value="">
<input type=hidden name=buyertel size=20 value="">

<input type=hidden name=gopaymethod value="Card">
<input type=hidden name=mid value="<?=$cfg['card_mall_id']?>">
<input type=hidden name=currency value="WON">
<input type=hidden name=nointerest value="no">

<input type=hidden name=quotabase value="선택:일시불:3개월:4개월:5개월:6개월:7개월:8개월:9개월:10개월:11개월:12개월">
<input type=hidden name=acceptmethod value="SKIN(ORIGINAL):HPP(2):OCB">

<input type=hidden name=oid size=40 value="">

<input type=hidden name=quotainterest value="">
<input type=hidden name=paymethod value="">
<input type=hidden name=cardcode value="">
<input type=hidden name=cardquota value="">
<input type=hidden name=rbankcode value="">
<input type=hidden name=reqsign value="DONE">
<input type=hidden name=encrypted value="">
<input type=hidden name=sessionkey value="">
<input type=hidden name=uid value="">
<input type=hidden name=sid value="">
<input type=hidden name=version value=4000>
<input type=hidden name=clickcontrol value="">
</form>
<!-- INIpayTest -->