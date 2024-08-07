<form name="btpg_form" method="post" accept-charset="euc-kr" >
	<input type='hidden' name="P_MID" value="">
	<input type='hidden' name="P_OID" value="">
	<input type='hidden' name="P_AMT" value="">
	<input type='hidden' name="P_NOTI_URL" value="">
	<input type='hidden' name="P_NEXT_URL" value="">
	<input type='hidden' name="P_RETURN_URL" value="">
	<input type='hidden' name="P_UNAME" value="">
	<input type='hidden' name="P_EMAIL" value="">
	<input type='hidden' name="P_MOBILE" value="">
	<input type='hidden' name="P_GOODS" value="">
	<input type='hidden' name='P_HPP_METHOD' value='2' />
	<input type='hidden' name='P_TAX' value='' />
	<input type='hidden' name='P_TAXFREE' value='' />
	<input type='hidden' name="P_NOTI" value="">
	<input type="hidden" name="P_VBANK_DT" value="<?=$cfg['banking_time']?>">
	<input type="hidden" name="P_RESERVED" value="twotrs_isp=Y&block_isp=Y&twotrs_isp_noti=N&apprun_check=Y&app_scheme=wisa<?=str_replace('-','',$_we['wm_key_code'])?>://">
</form>