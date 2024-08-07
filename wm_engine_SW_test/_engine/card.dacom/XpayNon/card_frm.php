<?php

	/**
	 * 데이콤 Xpay non active x
	 */

	$CST_PLATFORM = $cfg['card_test'] == 'N' ? 'service' : 'test';

	if($cfg['banking_time'] > 0) {
		$LGD_CLOSEDATE = date('Ymd235959', strtotime("+$cfg[banking_time] days"));
	}

?>
<script type="text/javascript">

function doPay_ActiveX() {
	lgdwin = openXpay(document.getElementById('LGD_PAYINFO'), '<?=$CST_PLATFORM?>', 'iframe', null, "", "");
}

function getFormObject() {
	return document.getElementById("LGD_PAYINFO");
}

function payment_return() {
	var fDoc;

	fDoc = lgdwin.contentWindow || lgdwin.contentDocument;

	if (fDoc.document.getElementById('LGD_RESPCODE').value == "0000") {
		document.getElementById("LGD_PAYKEY").value = fDoc.document.getElementById('LGD_PAYKEY').value;
		document.getElementById("LGD_RESPCODE").value = fDoc.document.getElementById('LGD_RESPCODE').value;
		document.getElementById("LGD_RESPMSG").value = fDoc.document.getElementById('LGD_RESPMSG').value;

		document.getElementById("LGD_PAYINFO").target = "_self";
		document.getElementById("LGD_PAYINFO").action = "<?=$root_url?>/main/exec.php?exec_file=card.dacom/XpayNon/card_pay.exe.php";
		document.getElementById("LGD_PAYINFO").submit();
	} else {
		closeIframe();
	}
}

function cancelXPay(res_cd, res_msg, alert_msg){
	var ono = document.getElementById('LGD_OID').value;
	$.post('/main/exec.php?exec_file=order/pay_cancel.php', {'ono': ono, 'reason':res_msg}, function(r) {
		if(r == 'stat') {
			location.href = '/shop/order_finish.php?ono='+ono;
		} else {
			window.alert(alert_msg);

            layTgl3('order1', 'Y');
            layTgl3('order2', 'N');
            layTgl3('order3', 'Y');
		}
	});
}
</script>

<form name="mainForm" method="post" id="LGD_PAYINFO">
	<!-- 공통 정보 -->
	<input type="hidden" name="LGD_MID" value="">                		<!-- 상점아이디 -->
	<input type="hidden" name="LGD_OID" value="">                		<!-- 주문번호 -->
	<input type="hidden" name="LGD_AMOUNT" value="">   		<!-- 결제금액 -->
	<input type="hidden" name="LGD_BUYER" value=""> 			<!-- 구매자 -->
	<input type="hidden" name="LGD_PRODUCTINFO" value="">     			<!-- 상품정보 -->
	<input type="hidden" name="LGD_TIMESTAMP" value="">          		<!-- 타임스탬프 -->
	<input type="hidden" name="LGD_HASHDATA" value=""> 		<!-- 해쉬값 -->
	<input type="hidden" name="LGD_CUSTOM_USABLEPAY" value=""> 			<!-- 결제가능수단 -->
	<input type="hidden" name="LGD_CUSTOM_SKIN" value="">        		<!-- 결제창 SKIN -->
	<input type="hidden" name="LGD_CUSTOM_FIRSTPAY" value="">    		<!-- 첫번째 결제수단 -->
	<input type="hidden" name="LGD_INSTALLRANGE" value=""> 		<!-- 신용카드 할부 -->
	<input type="hidden" name="LGD_BUYERIP" id="LGD_BUYERIP" value="">
	<input type="hidden" name="LGD_BUYERID" id="LGD_BUYERID" value="">
	<input type="hidden" name="LGD_CASNOTEURL" value="<?=$root_url?>/main/exec.php?exec_file=card.dacom/XpayNon/card_pay.exe.php">	<!-- 무통장 결제결과처리_URL(LGD_NOTEURL) -->
	<input type="hidden" name="LGD_ESCROW_USEYN" value="N">    		<!-- 에스크로 적용 여부 -->
	<input type="hidden" name="LGD_TAXFREEAMOUNT" value="0">    		<!-- //면세금액/ -->
	<input type="hidden" name="LGD_NOINTINF" id="LGD_NOINTINF" value="">
	<input type="hidden" name="LGD_CASHRECEIPTYN" id="LGD_CASHRECEIPTYN" value="">
	<input type="hidden" name="LGD_WINDOW_VER"    value="2.5"> 				<!-- 결제창버전정보 (삭제하지 마세요) -->
	<input type="hidden" name="LGD_VERSION"      value="">							<!-- 버전정보 (삭제하지 마세요) -->
	<input type="hidden" name="LGD_RESPCODE" id="LGD_RESPCODE" value='<?= $LGD_RESPCODE ?>' />
	<input type="hidden" name="LGD_RESPMSG" id="LGD_RESPMSG" value='<?= $LGD_RESPMSG ?>' />
	<input type="hidden" name="LGD_PAYKEY" id="LGD_PAYKEY" value='<?= $LGD_PAYKEY ?>' />
	<input type="hidden" name="LGD_RETURNURL" value="<?= $LGD_RETURNURL ?>">					<!-- 응답 수신 페이지 -->
	<input type="hidden" name="LGD_CUSTOM_PROCESSTYPE" value="">              <!-- 트랜잭션 처리방식 -->
	<input type="hidden" name="LGD_ENCODING" value="">
	<input type="hidden" name="LGD_ENCODING_NOTEURL" value="">
	<input type="hidden" name="LGD_ENCODING_RETURNURL" value="">
	<input type="hidden" name="LGD_CLOSEDATE" value="<?=$LGD_CLOSEDATE?>">
	<input type="hidden" name="LGD_EASYPAY_ONLY" value="">
	<input type="hidden" name="LGD_ONEPAY_VIEW_VERSION" value="">
	<input type="hidden" name="LGD_CUSTOM_SWITCHINGTYPE" value="IFRAME">
</form>

<script language="javascript" src="<?= $_SERVER['SERVER_PORT']!=443?"http":"https" ?>://xpay.uplus.co.kr/xpay/js/xpay_crossplatform.js" type="text/javascript"></script>
