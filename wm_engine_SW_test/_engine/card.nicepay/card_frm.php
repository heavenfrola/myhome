<script src="https://web.nicepay.co.kr/v3/webstd/js/nicepay-2.0.js" type="text/javascript"></script>
<script type="text/javascript">
function nicepayStart() {
	goPay(document.querySelector('#nicepayFrm'));
}

function nicepaySubmit(){
	document.querySelector('#nicepayFrm').submit();
}

function nicepayClose(msg){
	if(!msg) msg = '결제가 취소 되었습니다';
	window.alert(msg);

	parent.$('#bg_layer').remove();
	parent.$('#nice_layer').remove();
    layTgl3('order1', 'Y');
    layTgl3('order2', 'N');
    layTgl3('order3', 'Y');
}
</script>

<form id="nicepayFrm" method="post" action="">
</form>