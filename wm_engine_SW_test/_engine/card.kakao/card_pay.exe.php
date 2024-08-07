<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  카카오페이 결제완료 DB처리
	' +----------------------------------------------------------------------------------------------+*/

	set_time_limit(0);

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/shop.lib.php";

	include_once "incKakaopayCommon.php";
	include_once "lgcns_CNSpay.php";

	$card_tbl=$tbl['card'];

	// 로그 저장 위치 지정
	$connector = new CnsPayWebConnector($LogDir);
	$connector->CnsActionUrl($CnsPayDealRequestUrl);
	$connector->CnsPayVersion($phpVersion);

	// 요청 페이지 파라메터 셋팅
	$connector->setRequestData($_REQUEST);

	// 추가 파라메터 셋팅
	$connector->addRequestData("actionType", "PY0");  						// actionType : CL0 취소, PY0 승인, CI0 조회
	$connector->addRequestData("MallIP", $_SERVER['REMOTE_ADDR']);	// 가맹점 고유 ip
	$connector->addRequestData("CancelPwd", $cancelPwd);

	//가맹점키 셋팅 (MID 별로 틀림)
	$connector->addRequestData("EncodeKey", $merchantKey);

	// 4. CNSPAY Lite 서버 접속하여 처리
	$connector->requestAction();

	// 5. 결과 처리
	$buyerName = $_REQUEST["BuyerName"];   						// 구매자명
	$goodsName = $_REQUEST["GoodsName"]; 						// 상품명
	// $buyerName = iconv("euc-kr", "utf-8", $connector->getResultData("BuyerName"));		// 구매자명
	// $goodsName = iconv("euc-kr", "utf-8", $connector->getResultData("GoodsName"));		// 상품명

	$resultCode = $connector->getResultData("ResultCode"); 		// 결과코드 (정상 :3001 , 그 외 에러)
	$resultMsg = $connector->getResultData("ResultMsg");   		// 결과메시지
	$authDate = $connector->getResultData("AuthDate");   			// 승인일시 YYMMDDHH24mmss
	$authCode = $connector->getResultData("AuthCode");   		// 승인번호
	$payMethod = $connector->getResultData("PayMethod");  		// 결제수단
	$mid = $connector->getResultData("MID");  						// 가맹점ID
	$tid = $connector->getResultData("TID");  							// 거래ID
	$moid = $connector->getResultData("Moid");  					// 주문번호
	$amt = $connector->getResultData("Amt");  						// 금액
	$cardCode = $connector->getResultData("CardCode");			// 카드사 코드
	$cardName = $connector->getResultData("CardName");  	 	// 결제카드사명
	$cardQuota = $connector->getResultData("CardQuota"); 		// 00:일시불,02:2개월
	$cardInterest = $connector->getResultData("CardInterest"); 		// 무이자 여부 (0:일반, 1:무이자)
	$cardCl = $connector->getResultData("CardCl");             		// 체크카드여부 (0:일반, 1:체크카드)
	$cardBin = $connector->getResultData("CardBin");           		// 카드BIN번호
	$cardPoint = $connector->getResultData("CardPoint");       		// 카드사포인트사용여부 (0:미사용, 1:포인트사용, 2:세이브포인트사용)
	$paySuccess = false;													// 결제 성공 여부

	$nonRepToken =$_REQUEST["NON_REP_TOKEN"];		//부인방지토큰값


	$resultMsg = iconv("euc-kr", "utf-8", $resultMsg);
	$cardName = iconv("euc-kr", "utf-8", $cardName);

	$ono = $moid;

	/** 위의 응답 데이터 외에도 전문 Header와 개별부 데이터 Get 가능 */
	if($payMethod == "CARD"){	//신용카드
		if($resultCode == "3001") $paySuccess = true;				// 결과코드 (정상 :3001 , 그 외 에러)
	}

	if($paySuccess) {
	   // 결제 성공시 DB처리 하세요.
		$pdo->query("update `$card_tbl` set `stat`='2' ,`card_cd`='$cardCode' ,`card_name`='$cardName' ,`app_time`='$app_time' ,`app_no`='$app_no' ,`noinf`='$noinf' ,`quota`='$cardQuota' ,`res_cd`='$resultCode' ,`res_msg`='$resultMsg' ,`ordr_idxx`='$ono' ,`tno`='$tid' ,`good_mny`='$amt' ,`good_name`='$goodsName' ,`buyr_name`='$buyername' ,`buyr_mail`='$buyeremail' ,`buyr_tel1`='$buyertel' ,`buyr_tel2`= '$buyr_tel2',`use_pay_method`='$payMethod'  where `wm_ono`='$ono'");

		makePGLog($ono, 'kakaopay success');
	}else{
	   // 결제 실패시 DB처리 하세요.
		$pdo->query("update `$card_tbl` set `card_cd`='$cardCode' ,`card_name`='$cardName' ,`app_time`='$app_time' ,`app_no`='$app_no' ,`noinf`='$noinf' ,`quota`='$cardQuota' ,`res_cd`='$resultCode' ,`res_msg`='$resultMsg' ,`ordr_idxx`='$ono' ,`tno`='$tid' ,`good_mny`='$amt' ,`good_name`='$goodsName' ,`buyr_name`='$buyername' ,`buyr_mail`='$buyeremail' ,`buyr_tel1`='$buyertel' ,`buyr_tel2`= '$buyr_tel2',`use_pay_method`='$payMethod'  where `wm_ono`='$ono'");

		// 카드결제 실패 처리
		$pdo->query("update `$tbl[order]` set `stat`=31 where `ono`='$ono' and stat != 2");
		$pdo->query("update `$tbl[order_product]` set `stat`=31 where `ono`='$ono' and stat != 2");

		makePGLog($ono, 'kakaopay failed');

		?><script type="text/javascript">
			window.alert("\n 결제가 실패하였습니다 : <?=$resultCode?> ( <?=$resultMsg?> )             \n\n 다시 결제하기를 클릭하세요             \n");
		/*
			cancelf = parent.document.pay_cFrm;
			cancelf.ono.value = '<?=$ono?>';
			cancelf.mode.value = 'fail';
			cancelf.submit();
		*/
            parent.layTgl3('order1', 'Y');
            parent.layTgl3('order2', 'N');
            parent.layTgl3('order3', 'Y');
		</script><?
		exit;
	}

	$card_pay_ok=true;
	include_once $engine_dir."/_engine/order/order2.exe.php";
	exit;

?>
<script type="text/javascript">
	var of=parent.document.ordFrm;
	of.ono.value='<?=$ono?>';
	window.onload=new function(){
		of.submit();
	}
</script>