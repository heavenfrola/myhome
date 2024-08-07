<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  올더게이트 모바일 Form
	' +----------------------------------------------------------------------------------------------+*/

	$strAegis = "https://www.allthegate.com";
	$strCsrf = "csrf.real.js";

	if($cfg['banking_time'] > 0) {
		$VIRTUAL_DEPODT = date('Ymd', strtotime("+$cfg[banking_time] days"));
	}

?>
<script type="text/javascript" charset="euc-kr" src="<?=$strAegis?>/payment/mobilev2/csrf/<?=$strCsrf?>"></script>
<script type="text/javascript" charset="euc-kr">
	function doPay(form) {
		AllTheGate.pay(document.agsFrm);
		return false;
	}
</script>

<form method="post" action="<?=$strAegis?>/payment/mobilev2/intro.jsp" name="agsFrm" id='agsFrm'>
	<input type='hidden' name='OrdNo' value=''/>
	<input type='hidden' name='ProdNm' value=''/>
	<input type='hidden' name='Amt' value=''/>
	<input type='hidden' name='DutyFree' value=''/>
	<input type='hidden' name='Job' value=''/>
	<input type='hidden' name='OrdNm' value=''/>
	<input type='hidden' name='StoreNm' value=''/>
	<input type='hidden' name='OrdPhone' value=''/>
	<input type='hidden' name='UserEmail' value=''/>
	<input type='hidden' name='StoreId' value=""/>
	<input type='hidden' name='MallUrl' value=""/>
	<input type='hidden' name='UserId' value="">
	<input type='hidden' name='OrdAddr' value="">
	<input type='hidden' name='RcpNm' value="">
	<input type='hidden' name='RcpPhone' value="">
	<input type='hidden' name='DlvAddr' value="">
	<input type='hidden' name='Remark' value="">
	<input type='hidden' name='RtnUrl' value="">
	<input type='hidden' name='CancelUrl' value="">
	<input type='hidden' name='MallPage' value="">
	<input type='hidden' name='VIRTUAL_DEPODT' value="<?=$VIRTUAL_DEPODT?>">

	<!-- 핸드폰결제 -->
	<input type='hidden' name='HP_ID' value="">
	<input type='hidden' name='HP_PWD' value="">
	<input type='hidden' name='HP_SUBID' value="">
	<input type='hidden' name='ProdCode' value="">
	<input type='hidden' name='HP_UNITType' value="2">

	<input type="hidden" name='DeviId' value="">
	<input type="hidden" name='QuotaInf' value="2">
	<input type="hidden" name='NointInf' value="NONE">
</form>