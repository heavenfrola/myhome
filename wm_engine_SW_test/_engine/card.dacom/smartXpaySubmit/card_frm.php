<?PHP

	$platform = ($cfg['card_mobile_test'] != 'N') ? 'test' : 'service';

?>
<?if($cfg['card_mobile_test'] != 'N') {?>
	<script language="javascript" src="https://pretest.uplus.co.kr:9443/xpay/js/xpay_crossplatform.js" type="text/javascript"></script>
<?}else {?>
	<script language="javascript" src="https://xpayvvip.uplus.co.kr/xpay/js/xpay_crossplatform.js" type="text/javascript"></script>
<?}?>
<script type="text/javascript">
	var LGD_window_type = 'submit';
	function launchCrossPlatform(){
		var lgdwin = open_paymentwindow(document.getElementById('LGD_PAYINFO'), '<?=$platform?>', LGD_window_type);
	}

	function getFormObject() {
		return document.getElementById("LGD_PAYINFO");
	}

	function cancelPay(msg) {
		var mode = 'fail';
		if(!msg) {
			msg = _lang_pack.pg_error_cancel;
			momde = '';
		}
		window.alert(_lang_pack.pg_error_faild+'\n'+msg);

		var f = document.getElementById('LGD_PAYINFO');
		$.post(root_url+'/main/exec.php?exec_file=order/pay_cancel.php', {'ono':f.LGD_OID.value, 'mode':mode, 'reason':msg}, function(r) {
			if(r == 'stat') {
				location.href = '/shop/order_finish.php?ono='+f.LGD_OID.value;
			} else {
				$('#payLayer').hide();
				$('form[name=ordFrm]').show();

                layTgl3('order1', 'Y');
                layTgl3('order2', 'N');
                layTgl3('order3', 'Y');
			}
		});
	}
</script>
<form method="post" name="LGD_PAYINFO" id="LGD_PAYINFO" action="">
</form>