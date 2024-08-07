<?php
/*
  payreq_crossplatform 아이프레임 close 시 해당 페이지 없으면 이동이 안됨 ㅠㅠ
*/

$payReqMap = $_POST;
?>
<html>
<head>
	<script type="text/javascript">
		function setLGDResult() {
			parent.payment_return();
			try {
			} catch (e) {
				alert(e.message);
			}
		}
		function cancel() {
            parent.layTgl3('order1', 'Y');
            parent.layTgl3('order2', 'N');
            parent.layTgl3('order3', 'Y');
		}
	</script>
</head>
<body onload="setLGDResult()">
<?php
	$LGD_RESPCODE = $_POST['LGD_RESPCODE'];
	$LGD_RESPMSG 	= $_POST['LGD_RESPMSG'];
	$LGD_PAYKEY	  = "";

	if($LGD_RESPCODE != "0000"){
?>
		<script type="text/javascript">
			cancel();
		</script>
<?
		echo "LGD_RESPCODE:" + $LGD_RESPCODE + " ,LGD_RESPMSG:" + $LGD_RESPMSG; //인증 실패에 대한 처리 로직 추가
	}
?>
<form method="post" name="LGD_RETURNINFO" id="LGD_RETURNINFO">
<?php
	foreach ($payReqMap as $key => $value) {
		echo "<input type='hidden' name='$key' id='$key' value='$value'>";
	}
?>
</form>

</body>
</html>