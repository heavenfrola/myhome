<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  INIpay mobile 결제정보 전송
	' +----------------------------------------------------------------------------------------------+*/

	if(!defined("_wisa_lib_included")) exit;

	checkAgent();
	$os = trim("$os_name $os_version");
	$browser = trim("$br_name $br_version");
	$env_info = "$os || $browser || ".$_SERVER['REMOTE_ADDR'];

	$card_tbl=($pay_type == 4) ? $tbl['vbank'] : $tbl['card'];
	cardDataInsert($card_tbl, 'inicis');

	$mid = ($cfg['card_test'] == "Y") ? 'INIpayTest' : $cfg['card_inicis_mobile_id'];
	$$title = cutStr(inputText(preg_replace('/\?|\&|:/', '', strip_tags(addslashes($title)))),90);
	if(!$buyer_phone) $buyer_phone = $buyer_cell;

	switch($pay_type) {
		case '1' : $pay_method = 'wcard'; break;
		case '5' : $pay_method = 'bank'; break;
		case '4' : $pay_method = 'vbank'; break;
		case '7' : $pay_method = 'mobile'; break;
	}

    $_SESSION['cart_cache'] = $_POST['cart_selected'];

?>
<script language="javascript">
	var f = parent.document.btpg_form;

	f.P_MID.value = '<?=$mid?>';
	f.P_OID.value = '<?=$ono?>';
	f.P_AMT.value = '<?=$pay_prc?>';
	f.P_UNAME.value = '<?=$buyer_name?>';
	f.P_NOTI_URL.value = '<?=$root_url?>/main/exec.php?exec_file=card.inicis/INIpayMobileWeb/noti_url.exe.php';
	f.P_NEXT_URL.value = '<?=$root_url?>/main/exec.php?exec_file=card.inicis/INIpayMobileWeb/card_pay.exe.php&';
	f.P_RETURN_URL.value = '<?=$root_url?>/shop/order_finish.php?ono=<?=$ono?>';
	f.P_GOODS.value = '<?=$title?>';
	f.P_MOBILE.value = '<?=$buyer_cell?>';
	f.P_EMAIL.value = '<?=$buyer_email?>';
	f.P_NOTI.value = '<?=$ono?>';
	f.P_TAX.value='<?=parsePrice(floor(($pay_prc-$taxfree_amount)/11))?>';
	f.P_TAXFREE.value='<?=parsePrice($taxfree_amount)?>';
    f.P_RESERVED.value = f.P_RESERVED.value.replace('useescrow=Y&', '');

    <?php if ($pay_type == '4') { ?>
    f.P_RESERVED.value = 'vbank_receipt=Y&useescrow=Y&'+f.P_RESERVED.value;
    <?php }  ?>

	f.action = "https://mobile.inicis.com/smart/<?=$pay_method?>/";

	f.submit();
</script>