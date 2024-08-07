<?php

	/* +----------------------------------------------------------------------------------------------+
	' |  U+ Xpay 결제데이터 전송받음, 크로스플래폼
	' +----------------------------------------------------------------------------------------------+*/

include_once $engine_dir."/_engine/include/common.lib.php";
include_once $engine_dir."/_engine/include/shop.lib.php";

/*
  if(!isset($_SESSION['PAYREQ_MAP'])){
  	echo "<script>alert('세션이 만료 되었거나 유효하지 않은 요청 입니다.'); parent.closeIframe();</script>";
  	return;
  }
*/
  $payReqMap = $_SESSION['PAYREQ_MAP'];//결제 요청시, Session에 저장했던 파라미터 MAP

  $LGD_RESPCODE = mb_convert_encoding($_POST['LGD_RESPCODE'], _BASE_CHARSET_, 'euckr');
  $LGD_RESPMSG 	= mb_convert_encoding($_POST['LGD_RESPMSG'], _BASE_CHARSET_, 'euckr');
?>
<html>
<head>
	<script type="text/javascript">
	
		function setLGDautoResult() {
			parent.payment_auto_return();
			try {
			} catch (e) {
				alert(e.message);
			}
		}
		
	</script>
</head>
<body onload="setLGDautoResult()">
<?php
  
  $LGD_BILLKEY			= "";	
  $LGD_PAYTYPE			= "";	
  $LGD_PAYDATE			= "";	
  $LGD_FINANCECODE		= "";	
  $LGD_FINANCENAME		= "";	

  $payReqMap['LGD_RESPCODE']= $LGD_RESPCODE;
  $payReqMap['LGD_RESPMSG']	= $LGD_RESPMSG;

  if($LGD_RESPCODE == "0000"){	  
	  $LGD_BILLKEY 	= mb_convert_encoding($_POST['LGD_BILLKEY'], _BASE_CHARSET_, 'euckr');         //추후 빌링시 카드번호 대신 입력할 값입니다.
	  $LGD_PAYTYPE 	= mb_convert_encoding($_POST['LGD_PAYTYPE'], _BASE_CHARSET_, 'euckr');         //인증수단
	  $LGD_PAYDATE 	= mb_convert_encoding($_POST['LGD_PAYDATE'], _BASE_CHARSET_, 'euckr');         //인증일시
	  $LGD_FINANCECODE 	= mb_convert_encoding($_POST['LGD_FINANCECODE'], _BASE_CHARSET_, 'euckr'); //인증기관코드
	  $LGD_FINANCENAME 	= mb_convert_encoding($_POST['LGD_FINANCENAME'], _BASE_CHARSET_, 'euckr'); //인증기관이름


	  $payReqMap['LGD_BILLKEY']		= $LGD_BILLKEY;
	  $payReqMap['LGD_PAYTYPE']		= $LGD_PAYTYPE;
	  $payReqMap['LGD_PAYDATE']		= $LGD_PAYDATE;
	  $payReqMap['LGD_FINANCECODE'] = $LGD_FINANCECODE;
	  $payReqMap['LGD_FINANCENAME'] = $LGD_FINANCENAME;
  }
  else{
	  //echo "LGD_RESPCODE:" + $LGD_RESPCODE + " ,LGD_RESPMSG:" + $LGD_RESPMSG; //인증 실패에 대한 처리 로직 추가
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