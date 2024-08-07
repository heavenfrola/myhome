<script type='text/javascript'>
	function _pay(_frm) {
 		_frm.sndReply.value = root_url+'/main/exec.php?exec_file=card.kspay/card_rcv.exe.php';

		var agent = navigator.userAgent;
		var midx = agent.indexOf("MSIE");
		var out_size = (midx != -1 && agent.charAt(midx+5) < '7');

		var width_ = 500;
		var height_ = out_size ? 568 : 518;
		var left_ = screen.width;
		var top_ = screen.height;

		left_ = left_/2-(width_/2);
		top_ = top_/2-(height_/2);

		op = window.open('about:blank', 'AuthFrmUp',
		        'height='+height_+',width='+width_+',status=yes,scrollbars=no,resizable=no,left='+left_+',top='+top_+'');

		if(op == null) {
			window.alert(_lang_pack.pg_error_popup);
			return false;
		}

		_frm.target = 'AuthFrmUp';
		_frm.action ='https://kspay.ksnet.to/store/KSPayFlashV1.3/KSPayPWeb.jsp';
		//_frm.action ='http://210.181.28.116/store/KSPayFlashV1.3/KSPayPWeb.jsp';

		_frm.submit();
    }

	function getLocalUrl(mypage) {
		var myloc = location.href;
		return myloc.substring(0, myloc.lastIndexOf('/')) + '/' + mypage;
	}

	function goResult(){
		document.KSPayWeb.target = '';
		document.KSPayWeb.action = root_url+'/main/exec.php?exec_file=card.kspay/card_pay.exe.php';
		document.KSPayWeb.submit();
	}

	function eparamSet(rcid, rctype, rhash){
		document.KSPayWeb.reWHCid.value 	= rcid;
		document.KSPayWeb.reWHCtype.value   = rctype  ;
		document.KSPayWeb.reWHHash.value 	= rhash  ;
	}
</script>

<form id='KSPayWeb' name='KSPayWeb' action = '/main/exec.php' method='post'>
	<input type='hidden' name='sndPaymethod' value='' />
	<input type='hidden' name='sndStoreid' value='' />
	<input type='hidden' name='sndOrdernumber' value='' />
	<input type='hidden' name='sndGoodname' value='' />
	<input type='hidden' name='sndAmount' value='' />
	<input type='hidden' name='sndOrdername' value='' />
	<input type='hidden' name='sndEmail' value='' />
	<input type='hidden' name='sndMobile' value='' />
	<input type='hidden' name='sndServicePeriod'  value='' />

	<input type='hidden' name='sndReply' value='' />
	<input type='hidden' name='sndGoodType' value='1' />
	<input type='hidden' name='sndShowcard' value="I,M" /> <!-- I(ISP), M(안심결제), N(일반승인:구인증방식), A(해외카드), W(해외안심)-->
	<input type='hidden' name='sndCurrencytype' value="WON" />
	<input type='hidden' name='sndInstallmenttype' value='' />
	<input type='hidden' name='sndInteresttype' value='NONE' />

	<input type='hidden' name='sndEscrow' value='0'>
    <input type='hidden' name='sndCashReceipt' value='1' />

	<input type='hidden' name='reWHCid' />
	<input type='hidden' name='reWHCtype' />
	<input type='hidden' name='reWHHash' />
</form>