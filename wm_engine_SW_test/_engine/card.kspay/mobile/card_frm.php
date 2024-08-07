<script type='text/javascript'>
	function _pay(_frm) {
		_frm.sndReply.value = root_url+'/main/exec.php?exec_file=card.kspay/mobile/card_pay.exe.php';

		var agent = navigator.userAgent;
		var midx = agent.indexOf('MSIE');
		var out_size = (midx != -1 && agent.charAt(midx+5) < '7');
		_frm.action ='http://kspay.ksnet.to/store/mb2/KSPayPWeb.jsp';
		_frm.submit();
	}

	function getLocalUrl(mypage) {
		var myloc = location.href;
		return myloc.substring(0, myloc.lastIndexOf('/')) + '/' + mypage;
	}
</script>

<form id='authFrmFrame' name='authFrmFrame' method='post'>
	<input type='hidden' name='sndPaymethod' value='' />
	<input type='hidden' name='sndStoreid' value='' />
	<input type='hidden' name='sndCurrencytype' value='WON' />
	<input type='hidden' name='sndOrdernumber' />
	<input type='hidden' name='sndInstallmenttype' /></td>
	<input type='hidden' name='sndInteresttype' value='NONE'></td>
	<input type='hidden' name='sndShowcard' value='I,M' /> <!-- I(ISP), M(안심결제), N(일반승인:구인증방식), A(해외카드), W(해외안심)-->
	<input type='hidden' name='sndGoodname' />
	<input type='hidden' name='sndAmount'/></td>
	<input type='hidden' name='sndOrdername' />
	<input type='hidden' name='sndEmail' />
	<input type='hidden' name='sndMobile' />

	<input type='hidden' name='sndReply' value=''>
	<input type='hidden' name='sndEscrow' value='0'>
	<input type='hidden' name='sndVirExpDt' value=''>
	<input type='hidden' name='sndVirExpTm' value=''>
	<input type='hidden' name='sndStoreName' value='<?=$cfg['company_mall_name']?>'>
	<input type='hidden' name='sndStoreNameEng' value='<?=$root_url?>'>
	<input type='hidden' name='sndStoreDomain' value='<?=$root_url?>'>
	<input type='hidden' name='sndGoodType' value='1' />
	<input type='hidden' name='sndUseBonusPoint' />
	<input type='hidden' name='sndRtApp' />
</form>