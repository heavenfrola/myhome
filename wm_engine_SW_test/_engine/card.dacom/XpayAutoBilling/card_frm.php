<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  U+ Xpay 결제준비품 (정기배송포함)
	' +----------------------------------------------------------------------------------------------+*/

	$CST_PLATFORM = ($cfg['autobill_test'] == 'Y') ? 'test' : 'service';
	$CST_WINDOW_TYPE = ($_SESSION['browser_type'] == "pc") ? "iframe" : "submit"; //submit , iframe

?>
<form id="LGD_BILL_PAYINFO" name="LGD_BILL_PAYINFO" method="post" >
	<input type='hidden' name='LGD_WINDOW_TYPE'     value='' />
	<input type="hidden" name="LGD_MID"             value="" >				<!-- 상점아이디 -->
	<input type="hidden" name="LGD_OID"             value="" >				<!-- 주문번호 -->
	<input type="hidden" name="LGD_BUYERSSN"        value="" >				<!-- 인증요청 생년월일,사업자번호  -->
	<input type="hidden" name="LGD_CHECKSSNYN"      value="" >				<!-- 인증요청 생년월일,사업자번호 일치여부 확인 플래그 -->
	<input type="hidden" name="LGD_PAYWINDOWTYPE"   value="CardBillingAuth" >					<!-- 수정불가 -->
	<input type='hidden' name='LGD_RETURNURL' id='LGD_RETURNURL' value='' />
</form>
<form id="LGD_BILL_PAYINFO_EXE" name="LGD_BILL_PAYINFO_EXE" action="/main/exec.php" method="post" >
	<input type="hidden" name="exec_file" value="card.dacom/XpayAutoBilling/card_pay.exe.php" >
	<input type="hidden" name="LGD_OID"   value="" >
	<input type="hidden" name="LGD_RESPCODE" value="" >						<!-- 인증 성공 코드값 (성공: 0000) -->
	<input type="hidden" name="LGD_RESPMSG"  value="" >
	<input type="hidden" name="LGD_BILLKEY"  value="" >
	<input type="hidden" name="LGD_PAYDATE"  value="" >
	<input type="hidden" name="LGD_FINANCECODE" value="" >
	<input type="hidden" name="LGD_FINANCENAME" value="" >
</form>

<!--모바일-->
<form id="LGD_BILL_PAYINFO_M" name="LGD_BILL_PAYINFO_M"  action="" method="post" >
	<input type="hidden" name="CST_PLATFORM"      value="<?=$CST_PLATFORM?>" />
	<input type="hidden" name="CST_WINDOW_TYPE"   value="submit" />
	<input type="hidden" name="CST_MID"           value="" />
	<input type="hidden" name="LGD_MID"           value="" />
	<input type="hidden" name="LGD_BUYERSSN"      value="" />
	<input type="hidden" name="LGD_CHECKSSNYN"    value="" />
	<input type="hidden" name="LGD_RETURNURL"     value="<?=$root_url?>/main/exec.php?exec_file=card.dacom/XpayAutoBilling/card_pay.exe.php" />
	<input type="hidden" name="LGD_PAYWINDOWTYPE" value="CardBillingAuth_smartphone" />
	<input type="hidden" name="LGD_VERSION"       value="PHP_SmartXPayBilling_1.0" />
</form>

<?if($CST_PLATFORM == "service") {?>
	<script language="javascript" src="https://xpayvvip.uplus.co.kr/xpay/js/xpay_crossplatform.js" type="text/javascript"></script>
<?} else { ?>
	<script language="javascript" src="https://pretest.uplus.co.kr:9443/xpay/js/xpay_crossplatform.js" type="text/javascript"></script>
<?}?>

<script language="javascript">
	var LGD_window_type = '<?=$CST_WINDOW_TYPE?>';

    function launchCrossPlatformPC(){
        lgdwin = openXpay(document.getElementById('LGD_BILL_PAYINFO'), "<?=$CST_PLATFORM?>", LGD_window_type, null, "", "");
    }

    function launchCrossPlatformM(){
        lgdwin = openXpay(document.getElementById('LGD_BILL_PAYINFO_M'), "<?=$CST_PLATFORM?>", LGD_window_type, null, "", "");
    }

	function getFormObject() {
			return document.getElementById("LGD_BILL_PAYINFO");
	}
	function payment_auto_return() {
		var fDoc;
		fDoc = lgdwin.contentWindow || lgdwin.contentDocument;

		var LGD_RESPCODE        = fDoc.document.getElementById('LGD_RESPCODE').value;       //결과코드
		var LGD_RESPMSG         = fDoc.document.getElementById('LGD_RESPMSG').value;        //결과메세지

		if (fDoc.document.getElementById('LGD_RESPCODE').value == "0000") {
			var LGD_BILLKEY         = fDoc.document.getElementById('LGD_BILLKEY').value;        //추후 빌링시 카드번호 대신 입력할 값입니다
			var LGD_PAYTYPE         = fDoc.document.getElementById('LGD_PAYTYPE').value;        //인증수단
			var LGD_PAYDATE         = fDoc.document.getElementById('LGD_PAYDATE').value;        //인증일자
			var LGD_FINANCECODE     = fDoc.document.getElementById('LGD_FINANCECODE').value;    //인증기관코드
			var LGD_FINANCENAME     = fDoc.document.getElementById('LGD_FINANCENAME').value;    //인증기관이름
			var f = document.getElementById("LGD_BILL_PAYINFO_EXE");
			f.LGD_RESPCODE.value=LGD_RESPCODE;
			f.LGD_RESPMSG.value=LGD_RESPMSG;
			f.LGD_BILLKEY.value=LGD_BILLKEY;
			f.LGD_PAYDATE.value=LGD_PAYDATE;
			f.LGD_FINANCECODE.value=LGD_FINANCECODE;
			f.LGD_FINANCENAME.value=LGD_FINANCENAME;
			f.LGD_OID.value=document.getElementById('LGD_BILL_PAYINFO').LGD_OID.value;
			f.submit();
		} else {
			alert("인증이 실패하였습니다. " + LGD_RESPCODE + '//' + LGD_RESPMSG);
            layTgl3('order1', 'Y');
            layTgl3('order2', 'N');
            layTgl3('order3', 'Y');

			closeIframe();
		}
	}
</script>