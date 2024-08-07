<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  올앳 PG결제 폼
	' +----------------------------------------------------------------------------------------------+*/

	if($cfg['card_test']!='Y' && $cfg['card_test']!='N') {
		$cfg['card_test']='Y';
	}

	if($_use['no_interest']=='Y') {
		if($cart_where==1) $cfg['card_no_interest']='Y';
		else $cfg['card_no_interest']='N';
	}
	elseif(!$cfg['card_no_interest']) {
		$cfg['card_no_interest']='D';
	}

?>
<script language=JavaScript charset='euc-kr' src="https://tx.allatpay.com/common/NonAllatPayRE.js"></script>
<script type='text/javascript'>
function ftn_approval(dfm) {
	AllatPay_Approval(dfm);
	// 결제창 자동종료 체크 시작
	AllatPay_Closechk_Start();
}

// 결과값 반환( receive 페이지에서 호출 )
function result_submit(result_cd,result_msg,enc_data) {

	// 결제창 자동종료 체크 종료
	AllatPay_Closechk_End();

	if( result_cd != '0000' ){
		window.setTimeout(function(){
			window.alert(result_cd + " : " + result_msg);
            layTgl3('order1', 'Y');
            layTgl3('order2', 'N');
            layTgl3('order3', 'Y');

			var cancelf = byName('pay_cFrm');
			if(cancelf) {
				cancelf.ono.value = dfm.allat_order_no.value;
				cancelf.submit();
			}
		},1000);
	} else {
		var fm = document.getElementsByName('fm');
		if(fm[0]) {
			fm[0].allat_enc_data.value = enc_data;
			fm[0].action = '<?=$root_url?>/main/exec.php?exec_file=card.allat/card_pay.exe.php';
			fm[0].target = hid_frame;
			fm[0].submit();
		}
	}
}
</script>

<form name="fm" method="post" target="hidden<?=$now?>" style="display:none;">
<input type="hidden" name="allat_shop_id" value="">
<input type="hidden" name="allat_order_no" value="">
<input type="hidden" name="allat_amt" value="">
<input type="hidden" name="allat_pmember_id" value="">
<input type="hidden" name="allat_product_cd" value="">
<input type="hidden" name="allat_product_nm" value="">
<input type="hidden" name="allat_buyer_nm" value="">
<input type="hidden" name="allat_recp_nm" value="">
<input type="hidden" name="allat_recp_addr" value="">
<input type="hidden" name="shop_receive_url" value="<?=$root_url?>/main/exec.php?exec_file=card.allat/allat_receive.php">
<input type="hidden" name="allat_tax_yn" value="Y">
<input type="hidden" name="allat_sell_yn" value="Y">
<input type="hidden" name="allat_enc_data" value="">
<input type="hidden" name="allat_card_yn" value="Y">
<input type="hidden" name="allat_bank_yn" value="N">
<input type="hidden" name="allat_vbank_yn" value="N">
<input type="hidden" name="allat_hp_yn" value="N">
<input type="hidden" name="allat_ticket_yn" value="N">
<input type="hidden" name="allat_account_key" value="">
<input type="hidden" name="allat_zerofee_yn" value="N">
<input type="hidden" name="allat_cardcert_yn" value="N">
<input type="hidden" name="allat_sanction_yn" value="D">
<input type="hidden" name="allat_bonus_yn" value="N">
<input type="hidden" name="allat_cash_yn" value="D">
<input type="hidden" name="allat_product_img" value="">
<input type="hidden" name="allat_email_addr" value="">
<input type="hidden" name="allat_test_yn" value="N">
<input type="hidden" name="allat_real_yn" value="Y">
<input type="hidden" name="allat_cardes_yn" value="N">
<input type="hidden" name="allat_bankes_yn" value="N">
<input type="hidden" name="allat_vbankes_yn" value="Y">
<input type="hidden" name="allat_hpes_yn" value="N">
<input type="hidden" name="allat_encode_type" value="U">
</form>