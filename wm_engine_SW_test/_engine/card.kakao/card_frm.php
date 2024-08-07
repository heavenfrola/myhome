<?
include "incKakaopayCommon.php";
include "lgcns_CNSpay.php";

if($_SESSION['browser_type']=='mobile') {
	$style = "style=\"display: none;width:100%;\"";
}else {
	$style = "style=\"display: none;\"";
}
?>
<!-- JQuery에 대한 부분은 site마다 버전이 다를수 있음 -->
<script src="<?php echo ($CnsPayDealRequestUrl) ?>/dlp/scripts/lib/easyXDM.min.js" type="text/javascript"></script>
<script src="<?php echo ($CnsPayDealRequestUrl) ?>/dlp/scripts/lib/json3.min.js" type="text/javascript"></script>

<link href="https://pg.cnspay.co.kr:443/dlp/css/kakaopayDlp.css" rel="stylesheet" type="text/css" />

<!-- DLP창에 대한 KaKaoPay Library -->
<script type="text/javascript" src="<?php echo ($CNSPAY_WEB_SERVER_URL) ?>/js/dlp/client/kakaopayDlpConf.js" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo ($CNSPAY_WEB_SERVER_URL) ?>/js/dlp/client/kakaopayDlp.min.js" charset="utf-8"></script>
<script type="text/javascript">
	/**
	cnspay	를 통해 결제를 시작합니다.
	*/
	function cnspay() {
		// TO-DO : 가맹점에서 해줘야할 부분(TXN_ID)과 KaKaoPay DLP 호출 API
		// 결과코드가 00(정상처리되었습니다.)
		if(document.payForm.resultCode.value == '00') {
			// TO-DO : 가맹점에서 해줘야할 부분(TXN_ID)과 KaKaoPay DLP 호출 API
			kakaopayDlp.setTxnId(document.payForm.txnId.value);
				kakaopayDlp.setChannelType('WPM', 'TMS');
				kakaopayDlp.addRequestParams({ MOBILE_NUM : '010-1234-5678'});
			kakaopayDlp.callDlp('kakaopay_layer', document.payForm, submitFunc);
		} else {
			alert('[RESULT_CODE] : ' + document.payForm.resultCode.value + '\n[RESULT_MSG] : ' + document.payForm.resultMsg.value);
            parent.removeFLoading();
            parent.layTgl3('order1', 'Y');
            parent.layTgl3('order2', 'N');
            parent.layTgl3('order3', 'Y');
        }
	}

	function getTxnId() {
		// form에 iframe 주소 세팅
		document.payForm.target = "txnIdGetterFrame";
		document.payForm.action = root_url+"/main/exec.php?exec_file=card.kakao/getTxnId.php";
		document.payForm.acceptCharset = "utf-8";
		if (document.payForm.canHaveHTML) { // detect IE
			document.charset = payForm.acceptCharset;
		}
		// post로 iframe 페이지 호출
		document.payForm.submit();
		// payForm의 타겟, action을 수정한다
		document.payForm.target = hid_frame;
		document.payForm.action = root_url+"/main/exec.php?exec_file=card.kakao/card_pay.exe.php";
		document.payForm.acceptCharset = "utf-8";
		if (document.payForm.canHaveHTML) { // detect IE
			document.charset = payForm.acceptCharset;
		}
		// getTxnId.jsp의 onload 이벤트를 통해 cnspay() 호출
	}

	var submitFunc = function cnspaySubmit(data){

		if(data.RESULT_CODE === '00') {

			// 부인방지토큰은 기본적으로 name="NON_REP_TOKEN"인 input박스에 들어가게 되며, 아래와 같은 방법으로 꺼내서 쓸 수도 있다.
			// 해당값은 가군인증을 위해 돌려주는 값으로서, 가맹점과 카카오페이 양측에서 저장하고 있어야 한다.
			// var temp = data.NON_REP_TOKEN;
			document.payForm.submit();

		} else if(data.RESLUT_CODE === 'KKP_SER_002') {
			// X버튼 눌렀을때의 이벤트 처리 코드 등록
			alert('[RESULT_CODE] : ' + data.RESULT_CODE + '\n[RESULT_MSG] : ' + data.RESULT_MSG);
		} else {
			alert('[RESULT_CODE] : ' + data.RESULT_CODE + '\n[RESULT_MSG] : ' + data.RESULT_MSG);
		}
	};
</script>

<form id='payForm' name='payForm' method='post' action="<?=$root_url?>/main/exec.php?exec_file=card.kakao/card_pay.exe.php">
	<input type='hidden' name='PayMethod' value='KAKAOPAY' />
	<input type='hidden' name='TransType' value='0' />

	<input type='hidden' name='GoodsName' value='' />
	<input type='hidden' name='Amt' value='' />
	<input type='hidden' name='GoodsCnt' value='' />
	<input type='hidden' name='MID' value='' />
	<input type='hidden' name='CERTIFIED_FLAG' value='CN' />
	<input type='hidden' name='AuthFlg' value='10' />
	<input type='hidden' name='currency' value='KRW' />
	<input type='hidden' name='merchantEncKey' value='' />
	<input type='hidden' name='merchantHashKey' value=''/>
	<input type='hidden' name='requestDealApproveUrl' value='' />
	<input type='hidden' name='prType' value='WPM' /><!--MPM-->
	<input type='hidden' name='channelType' value='4' />
	<input type='hidden' name='merchantTxnNum' value='' />
	<input type='hidden' name='BuyerName' value='' />

	<input type='hidden' id='resultCode' name='resultCode' value='' />
	<input type='hidden' id='resultMsg' name='resultMsg' value='' />
	<input type='hidden' id='txnId' name='txnId' value='' />
	<input type='hidden' id='prDt' name='prDt' value='' />
	<input type='hidden' name='SPU' value='' />
	<input type='hidden' name='SPU_SIGN_TOKEN' value='' />
	<input type='hidden' name='MPAY_PUB' value='' />
	<input type='hidden' name='NON_REP_TOKEN' value='' />
	<input type='hidden' name='EdiDate' value='' />
	<input type='hidden' name='EncryptData' value='' />

	<!-- TODO :  LayerPopup의 Target DIV 생성 -->
	<div id="kakaopay_layer" <?=$style?>></div>
</form>
<iframe name="txnIdGetterFrame" id="txnIdGetterFrame" src="" style="display:none;"></iframe>