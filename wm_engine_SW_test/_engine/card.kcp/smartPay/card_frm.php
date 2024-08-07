<div style="display:none">
<script type="text/javascript" src="<?=$engine_url?>/_engine/card.kcp/smartPay/approval_key.js"></script>
<script type="text/javascript">
	function call_pay_form() {
		self.name = 'tar_opener';
		var v_frm = document.sm_form;

		$('#payLayer').append('<iframe id="payFrm" name="payFrm" frameborder="0" border="0" width="100%" height="600px" scrolling="auto"></iframe>');
		$('#payLayer').show();
		$('form[name=ordFrm]').hide();

		<?if(_BASE_CHARSET_ == 'euc-kr') {?>
		v_frm.action = PayUrl;
		<?} else {?>
		v_frm.action = PayUrl.substring(0, PayUrl.lastIndexOf('/'))+'/jsp/encodingFilter/encodingFilter.jsp';
		v_frm.PayUrl.value = PayUrl;
		<?}?>

		<? if((strpos($_SERVER['HTTP_USER_AGENT'],'iPhone') > -1 && strpos($_SERVER['HTTP_USER_AGENT'],'Safari') === false)){?>
			v_frm.target = '_parent';
			v_frm.action = v_frm.action+"?AppUrl=wisa<?=str_replace('-','',$_we['wm_key_code'])?>://";
		<?}else{?>
			//v_frm.target = 'payFrm';
		<?}?>
		v_frm.submit();
	}

	function cancelPay() {
		window.alert(_lang_pack.pg_error_cancel);

		var f = document.getElementById('sm_form');
		$.post(root_url+'/main/exec.php?exec_file=order/pay_cancel.php', {'ono':f.ordr_idxx.value, 'mode':'', 'reason':_lang_pack.pg_error_cancel}, function() {
			$('#payLayer').hide();
			$('#payFrm').remove();
			$('form[name=ordFrm]').show();

            layTgl3('order1', 'Y');
            layTgl3('order2', 'N');
            layTgl3('order3', 'Y');
		});
	}
</script>
<form id='sm_form' name="sm_form" method="POST">
	<input type="hidden" name="good_name" value="">
	<input type="hidden" name="good_mny" value="" >
	<input type="hidden" name="buyr_name" value="">
	<input type="hidden" name="buyr_tel1" value="">
	<input type="hidden" name="buyr_tel2" value="">
	<input type="hidden" name="buyr_mail" value="">
	<input type="hidden" name="req_tx" value="pay">
	<input type="hidden" name="site_cd" value="">
	<input type="hidden" name="site_key" value="">
	<input type="hidden" name="shop_name" value="">
	<input type="hidden" name="pay_method" value="CARD">
	<input type="hidden" name="ordr_idxx" value="">
	<input type="hidden" name="quotaopt" value="">
	<input type="hidden" name="currency" value="410">
	<input type="hidden" name='tax_flag' value="">
	<input type="hidden" name='comm_tax_mny' value="">
	<input type="hidden" name='comm_free_mny' value="">
	<input type="hidden" name='comm_vat_mny' value="">
	<input type="hidden" name="approval_key" id="approval" value="">
	<input type="hidden" name="Ret_URL" value="">
	<input type="hidden" name="ActionResult" value="card">
	<input type="hidden" name="escw_used" value="N">
	<input type="hidden" name="pay_mod" value="">
	<input type="hidden" name="deli_term" value="">
	<input type="hidden" name="vcnt_expire_term" value="<?=$cfg['banking_time']?>">
	<input type="hidden" name="bask_cntx" value="">
	<input type="hidden" name="good_info" value="">
	<input type="hidden" name="rcvr_name" value="">
	<input type="hidden" name="rcvr_tel1" value="">
	<input type="hidden" name="rcvr_tel2" value="">
	<input type="hidden" name="rcvr_zipx" value="">
	<input type="hidden" name="rcvr_add1" value="">
	<input type="hidden" name="rcvr_add2" value="">
	<input type="hidden" name="param_opt_1" value="">
	<input type="hidden" name="param_opt_2" value="">
	<input type="hidden" name="param_opt_3" value="">
	<input type="hidden" name="tablet_size" value="1.0">
	<input type="hidden" name="encoding_trans" value="<?=_BASE_CHARSET_?>">
	<input type="hidden" name="PayUrl">
</form>

<form id='pay_form' name="pay_form" method="POST" >
    <input type="hidden" name="req_tx"         value="<?=$req_tx?>">      <!-- 요청 구분          -->
    <input type="hidden" name="res_cd"         value="<?=$res_cd?>">      <!-- 결과 코드          -->
    <input type="hidden" name="tran_cd"        value="<?=$tran_cd?>">     <!-- 트랜잭션 코드      -->
    <input type="hidden" name="ordr_idxx"      value="<?=$ordr_idxx?>">   <!-- 주문번호           -->
    <input type="hidden" name="good_mny"       value="<?=$good_mny?>">    <!-- 휴대폰 결제금액    -->
    <input type="hidden" name="good_name"      value="<?=$good_name?>">   <!-- 상품명             -->
    <input type="hidden" name="buyr_name"      value="<?=$buyr_name?>">   <!-- 주문자명           -->
    <input type="hidden" name="buyr_tel1"      value="<?=$buyr_tel1?>">   <!-- 주문자 전화번호    -->
    <input type="hidden" name="buyr_tel2"      value="<?=$buyr_tel2?>">   <!-- 주문자 휴대폰번호  -->
    <input type="hidden" name="buyr_mail"      value="<?=$buyr_mail?>">   <!-- 주문자 E-mail      -->
    <input type="hidden" name="enc_info"       value="<?=$enc_info?>">    <!-- 암호화 정보        -->
    <input type="hidden" name="enc_data"       value="<?=$enc_data?>">    <!-- 암호화 데이터      -->
    <input type="hidden" name="use_pay_method" value="100000000000">      <!-- 요청된 결제 수단   -->
</form>
</div>

<div id="payLayer" style='width:100%; display: none; position:absolute; top: 0; left:0; z-index:9999; background: #fff;'>
	<input type='button' value='결제방법 변경/취소' style='width: 100%; padding: 10px 0; border: 0; background: #008080; color: #fff;' onclick='cancelPay();' />
</div>