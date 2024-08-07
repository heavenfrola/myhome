<?PHP

/* +----------------------------------------------------------------------------------------------+
' | 카카오페이 결제 취소
' +----------------------------------------------------------------------------------------------+*/
include_once $engine_dir."/_engine/include/common.lib.php";
include_once "incKakaopayCommon.php";
include_once "lgcns_CNSpay.php";

$card = get_info($tbl[card], "no", $cno);

// 로그 저장 위치 지정
$connector = new CnsPayWebConnector($LogDir);
$connector->CnsActionUrl($CnsPayDealRequestUrl);
$connector->CnsPayVersion($phpVersion);
$connector->setRequestData($_REQUEST);
$connector->addRequestData("actionType", "CL0");
$connector->addRequestData("CancelPwd", $cancelPwd);
$connector->addRequestData("CancelIP", $_SERVER['REMOTE_ADDR']);

//가맹점키 셋팅 (MID 별로 틀림)
$connector->addRequestData("EncodeKey", $merchantKey);

// 4. CNSPAY Lite 서버 접속하여 처리
$connector->requestAction();

// 5. 결과 처리
$resultCode = $connector->getResultData("ResultCode"); 	// 결과코드 (정상 :2001(취소성공), 2002(취소진행중), 그 외 에러)
$resultMsg = $connector->getResultData("ResultMsg");   	// 결과메시지
$cancelAmt = $connector->getResultData("CancelAmt");   	// 취소금액
$cancelDate = $connector->getResultData("CancelDate"); 	// 취소일
$cancelTime = $connector->getResultData("CancelTime");   	// 취소시간
$payMethod = $connector->getResultData("PayMethod");   // 취소 결제수단
$mid = 	$connector->getResultData("MID");             		// 가맹점 ID
$tid = $connector->getResultData("TID");               		// TID
$errorCD = $connector->getResultData("ErrorCD");        	// 상세 에러코드
$errorMsg = $connector->getResultData("ErrorMsg");      	// 상세 에러메시지
$authDate = $cancelDate . $cancelTime;						// 거래시간
$ccPartCl = $connector->getResultData("CcPartCl");         	// 부분취소 가능여부 (0:부분취소불가, 1:부분취소가능)
$stateCD = $connector->getResultData("StateCD");         	// 거래상태코드 (0: 승인, 1:전취소, 2:후취소)
$authDate = $connector->makeDateString($authDate);
$errorMsg = iconv("euc-kr", "utf-8", $errorMsg);
$resultMsg = iconv("euc-kr", "utf-8", $resultMsg);

$_chk_price = parsePrice($card[wm_price], false);

$confirm_price = $_chk_price - $cancelAmt;


if($resultCode == "2001"){//취소성공
	if($confirm_price == 0) {
		$pdo->query("update `$tbl[card]` set `stat`='3' where `no`='$card[no]'");
	}
	else {
		$pdo->query("update `$tbl[card]` set `wm_price`='$confirm_price' where `no`='$card[no]'");
	}
	$msg="거래취소성공!";
	$stat=2;

	if($tid) { // 부분취소시 tid 업데이트 by zardsama
		$pdo->query("update $tbl[card] set env_info='$tid' where no='$card[no]'");
	}
}else{
	$msg="거래취소실패! (".$resultCode."/".$errorCD." : ".$resultMsg."/".$errorMsg.")";
}

$pdo->query("
	insert into `$tbl[card_cc_log]` (`cno`, `stat`, `ono`, `tno`, `price`, `res_cd`, `res_msg`, `admin_id`, `admin_no`, `ip`, `reg_date`)
	values ('$card[no]', '$stat', '$card[wm_ono]', '$tid', '$cancelAmt', '$resultCode', '$resultMsg', '$admin[admin_id]', '$admin[no]', '$_SERVER[REMOTE_ADDR]', '$now')
");

msg($msg, "reload", "parent");

?>