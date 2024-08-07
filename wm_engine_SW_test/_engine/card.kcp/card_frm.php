<?PHP

	$kcp_pay_url = ($cfg['card_test']) ? 'testpay' : 'pay';

?>
<script type="text/javascript">
	function m_Completepayment(FormOrJson, closeEvent) {
		var frm = document.querySelector('#payFrm');

		GetField(frm, FormOrJson);
		if(frm.res_cd.value == "0000") {
			frm.submit();
		} else {
			window.alert("[" + frm.res_cd.value + "] " + frm.res_msg.value);
			closeEvent();

			$.post(root_url+'/main/exec.php?exec_file=order/pay_cancel.php', {'ono':frm.ordr_idxx.value, 'mode':'', 'reason':frm.res_msg.value}, function() {
                layTgl3('order1', 'Y');
                layTgl3('order2', 'N');
                layTgl3('order3', 'Y');
			});
		}
	}

	function jsf__pay(form) {
		try {
			KCP_Pay_Execute(form);
		} catch (e)	{

		}
	}
</script>
<script type="text/javascript" src='https://<?=$kcp_pay_url?>.kcp.co.kr/plugin/payplus_web.jsp'></script>
<form id='payFrm' method='post' target='hidden<?=$now?>' action='<?=$root_url?>/main/exec.php?exec_file=card.kcp/card_pay.exe.php'>
	<input type='hidden' name='req_tx' value='pay' />
	<input type='hidden' name='site_name' value='' />
	<input type='hidden' name='site_cd' value='' />
	<input type='hidden' name='site_key' value='' />
	<input type='hidden' name='ordr_idxx' value='' />
	<input type='hidden' name='pay_method' value='' />
	<input type='hidden' name='good_name' value='' />
	<input type='hidden' name='good_mny' value='' />
	<input type='hidden' name='buyr_name' value='' />
	<input type='hidden' name='buyr_tel1' value='' />
	<input type='hidden' name='buyr_tel2' value='' />
	<input type='hidden' name='buyr_mail' value='' />
	<input type='hidden' name='site_logo' value='' />
	<input type='hidden' name='currency' value='WON' />
	<input type='hidden' name='module_type' value='01'/>
	<input type='hidden' name='eng_flag' value='' />
	<input type='hidden' name='tax_flag' value='' />
	<input type='hidden' name='comm_tax_mny' value='' />
	<input type='hidden' name='comm_free_mny' value='' />
	<input type='hidden' name='comm_vat_mny' value='' />
	<input type='hidden' name='skin_indx' value='1' />

	<!-- Card -->
	<input type='hidden' name='kcp_noint' value='' />
	<input type='hidden' name='kcp_noint_quota' value='' />
	<input type='hidden' name='quotaopt' value='0' />
	<input type='hidden' name='fix_inst' value='' />
	<input type='hidden' name='not_used_card' value='' />
	<input type='hidden' name='save_ocb' value='' />

	<!-- Vbank -->
	<input type='hidden' name='wish_vbank_list' value='' />
	<input type='hidden' name='vcnt_expire_term' value='<?=$cfg['banking_time']?>' />
	<input type='hidden' name='vcnt_expire_term_time' value='' />

	<!-- escrow -->
	<input type='hidden' name='escw_used' value='' />
	<input type='hidden' name='pay_mod' value='' />
	<input type='hidden' name='deli_term' value='' />
	<input type='hidden' name='bask_cntx' value='' />
	<input type='hidden' name='good_info' value='' />
	<input type='hidden' name='rcvr_name' value='' />
	<input type='hidden' name='rcvr_tel1' value='' />
	<input type='hidden' name='rcvr_tel2' value='' />
	<input type='hidden' name='rcvr_mail' value='' />
	<input type='hidden' name='rcvr_zipx' value='' />
	<input type='hidden' name='rcvr_add1' value='' />
	<input type='hidden' name='rcvr_add2' value='' />

	<!-- Point -->
	<input type='hidden' name='complex_pnt_yn' value=''/>

	<!-- Giftcard -->
	<input type='hidden' name='tk_shop_id' value='' />

	<!-- Cash Receipt -->
	<input type='hidden' name='disp_tax_yn' value='Y'/>

	<!-- readonly -->
	<input type='hidden' name='res_cd' value='' />
	<input type='hidden' name='res_msg' value='' />
	<input type='hidden' name='tno' value='' />
	<input type='hidden' name='trace_no' value='' />
	<input type='hidden' name='enc_info' value='' />
	<input type='hidden' name='enc_data' value='' />
	<input type='hidden' name='use_pay_method'  value='' />
	<input type='hidden' name='ret_pay_method'  value='' />
	<input type='hidden' name='tran_cd' value='' />
	<input type='hidden' name='bank_issu' value='' />
	<input type='hidden' name='bank_name' value='' />
	<input type='hidden' name='bank_code' value='' />
	<input type='hidden' name='app_time' value='' />
	<input type='hidden' name='epnt_issu' value='' />
	<input type='hidden' name='cash_yn' value='' />
	<input type='hidden' name='cash_tsdtime'    value='' />
	<input type='hidden' name='cash_authno' value='' />
	<input type='hidden' name='cash_id_info' value='' />
</form>