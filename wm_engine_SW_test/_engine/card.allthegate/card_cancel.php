<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  올더게이트 PG 취소
	' +----------------------------------------------------------------------------------------------+*/

	if(function_exists('extractParam')) {
		extractParam();
	}

	$mobile_paymented = $pdo->row("select `mobile` from `{$tbl['order']}` where `ono`='{$card['wm_ono']}'");

	 require_once $engine_dir."/_engine/card.allthegate/AGSLib.php";

	$agspay = new agspay40;

	$agspay->SetValue("AgsPayHome", $engine_dir."/_engine/card.allthegate");			//올더게이트 결제설치 디렉토리 (상점에 맞게 수정)
	$agspay->SetValue("log","false");							//true : 로그기록, false : 로그기록안함.
	$agspay->SetValue("logLevel","ERROR");						//로그레벨 : DEBUG, INFO, WARN, ERROR, FATAL (해당 레벨이상의 로그만 기록됨)
	$agspay->SetValue("Type", "Cancel");						//고정값(수정불가)
	$agspay->SetValue("RecvLen", 7);							//수신 데이터(길이) 체크 에러시 6 또는 7 설정.

	$agspay->SetValue("StoreId",trim($cfg['allthegate_StoreId']));		//상점아이디
	$agspay->SetValue("AuthTy",trim($card['use_pay_method']));			//결제형태

	$agspay->SetValue("SubTy", $card['env_info']);			//서브결제형태
	$agspay->SetValue("rApprNo",trim($card['app_no']));			//승인번호
	$agspay->SetValue("rApprTm",trim($card["app_time"]));			//승인일자
	$agspay->SetValue("rDealNo",trim($card["tno"]));			//거래번호
	$agspay->SetValue("PartCancelAmt",trim($price));	//부분취소금액

	$agspay->startPay();

	$part = $agspay->GetResult("PartCancelAmt");
	$result = $agspay->GetResult("rCancelResMsg");
	if(mb_detect_encoding($result, 'UTF-8', true) === false) {
		$result = mb_convert_encoding($result, _BASE_CHARSET_, 'euckr');
	}
	$tno = $agspay->GetResult('rDealNo');

	if($agspay->GetResult("rCancelSuccYn") == "y") {
		$result = addslashes($result);
		$stat = 2;

		if(!$price) {
			$pdo->query("update $tbl[card] set stat=$stat where no='$card[no]'");
		}

		$msg = '취소 처리가 완료되었습니다';
	} else {
		$stat = 1;
		$msg = $result;
	}

	$pdo->query("insert into `$tbl[card_cc_log]` (`cno`, `stat`, `ono`, `tno`, `res_msg`, `admin_id`, `admin_no`, `ip`, `reg_date`) values('$card[no]', '$stat', '$ono', '$tno', '$result', '$admin[admin_id]', '$admin[no]', '$_SERVER[REMOTE_ADDR]', '$now')");
	makeOrderLog($ono, "card_cancel.exe.php");

    // 주문서 처리와 함께 카드 취소
    if (isset($duel_card_cancel) == true && $duel_card_cancel == true) {
        $card_cancel_result = ($agspay->GetResult("rCancelSuccYn") == 'y') ? 'success' : $result;
        return;
    }

	msg($result, 'reload', 'parent');

?>