<?PHP

	$goodsName  = cutstr($title, 20, '');
	$buyerName  = $buyer_name;
	$buyerTel   = (empty($buyer_phone) == false) ? $buyer_phone : $buyer_cell;
	$buyerEmail = $buyer_email;
	$mid        = $cfg['card_auto_nicepay_mid'];
	$price      = parsePrice($pay_prc);
	$goodsCl    = '1';
	$returnUrl  = $root_url.'/main/exec.php?exec_file=card.nicepay/autobill/card_pay.exe.php';

?>
<script type="text/javascript">
function keyRequest(){
	var f = parent.document.nicepay_autobill;
	f.GoodsName.value="<?=$goodsName?>";
	f.BuyerName.value="<?=$buyerName?>";
	f.BuyerTel.value="<?=$buyerTel?>";
	f.BuyerEmail.value="<?=$buyerEmail?>";
	f.MID.value="<?=$mid?>";
	f.Moid.value="<?=$sno?>";
	f.Amt.value="<?=$price?>";
	f.GoodsCl.value="<?=$goodsCl?>";
    f.ReturnUrl.value="<?=$returnUrl?>"

    document.charset = "euc-kr";
	f.action = 'https://web.nicepay.co.kr/billing/step1.jsp';

	<?php if($mobile_browser == 'mobile') { ?>
	f.target = '_parent';
	f.submit();
	<?php } else { ?>
    var left = (screen.Width - 545)/2;
    var top = (screen.Height - 573)/2;
    var winopts = "left="+left+",top="+top+",width=545,height=573,toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=no,resizable=no";
    var win = window.open("", "billWindow", winopts);
	if(!win) {
		window.alert('팝업차단을 해제해주세요.');
        parent.layTgl3('order1', 'Y');
        parent.layTgl3('order2', 'N');
        parent.layTgl3('order3', 'Y');
	} else {
		f.target = "billWindow";
		f.submit();
	}
	<?php } ?>
}
keyRequest();
</script>
