<script type='text/javascript' src="https://tx.allatpay.com/common/allatpayX.js"></script>
<script type='text/javascript' >
	function ftn_escrowcheck(dfm) {
		var ret = invisible_eschk(dfm);
		if(ret.substring(0,4)!="0000" && ret.substring(0,4)!="9999") {
			window.alert(ret.substring(4,ret.length));
		}
		if(ret.substring(0,4)=="9999") {
			window.alert(ret.substring(8,ret.length));
		}
	}
</script>

<form name="fm"  method="POST" action="<?=$root_url?>/main/exec.php">
<input type="hidden" name="exec_file" value="card.allat/escrow.exe.php">
<input type="hidden" name="allat_shop_id" value="<?=$cfg['card_partner_id']?>">
<input type="hidden" name="allat_order_no" value="<?=$data['ono']?>">
<input type="hidden" name="allat_escrow_send_no" value="<?=$deli_numb?>">
<input type="hidden" name="allat_escrow_express_nm" value="<?=$deli_corp?>">
<input type="hidden" name="allat_pay_type" value="ABANK">
<input type="hidden" name="allat_enc_data" value=''>
<input type="hidden" name="allat_opt_pin" value="NOVIEW">
<input type="hidden" name="allat_opt_mod" value="WEB">
<input type="hidden" name="allat_seq_no" value="<?=$card['tno']?>">
<input type="hidden" name="allat_test_yn" value="<?=$cfg['card_test']?>">
</form>

<script type='text/javascript' >
	ftn_escrowcheck(document.fm);
</script>