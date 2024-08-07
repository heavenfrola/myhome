<?PHP

/* +----------------------------------------------------------------------------------------------+
' |  토스계좌결제 환불처리
' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";

	if($cno == ''){
		msg('환불 정보가 없습니다.');
	}

	$card = get_info($tbl['card'], 'no', $cno);

	//결제내역확인
	$card_detail = $pdo->assoc("select * from `$tbl[card]` where `no`='".$card['no']."' and `stat` = 2 limit 1");
	if($card_detail['no'] == '' ||  $card_detail['no'] < 1){
		msg('이미 환불 완료된 건 입니다.');
		exit;
	}

	function json_encode_han($arr) { // PHP 5.3 이하에서 (JSON_UNESCAPED_UNICODE 사용 불가)
		array_walk_recursive($arr, function (&$item, $key) { if (is_string($item)) $item = mb_encode_numericentity($item, array (0x80, 0xffff, 0, 0xffff), 'UTF-8'); });
		return mb_decode_numericentity(json_encode($arr), array (0x80, 0xffff, 0, 0xffff), 'UTF-8');
	}

	$_chk_price = parsePrice($card[wm_price], false);

	if($price>0) {//부분환불
		$CancelAmt = $price;
	}else{ // 전체환불
		$CancelAmt = $_chk_price;
	}

	// 토스 카드
	if($cfg['use_tosscard'] == 'Y') {
		$cfg['tosspayment_api_key'] = $cfg['tossc_liveApiKey'];
	}

	$json = json_encode_han(array(
		'amount'			=> $CancelAmt,
		'amountTaxFree'		=> 0,
		'payToken'			=> trim($card_detail['tno']),
		'apiKey'			=> $cfg['tosspayment_api_key']
	));

	$sql = "select count(*) from wm_card_cc_log where ono='".$card['wm_ono']."' and stat=2";
	$cancel_cnt = $pdo->row($sql);
	if($cancel_cnt>0) {
		$CancelNo = $cancel_cnt+1;
	}else {
		$CancelNo = "1";
	}

	// 환불처리
	$ch = curl_init('https://pay.toss.im/api/v1/refunds ');
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/json',
		'Content-Length: ' . strlen($json))
	);
	$result = json_decode(curl_exec($ch));
	curl_close($ch);

	$resultCode = $result->code;
	$resultMsg = $result->msg;
	$errorCD = $result->errorCode;
	$refundNo = $result->refundNo;

	if($resultCode == 0){ //환불성공
		$confirm_price = $_chk_price - $CancelAmt;
		if($confirm_price == 0) {
			$pdo->query("update `$tbl[card]` set `stat`='3', `wm_price`='$confirm_price' where `no`='$card[no]'");
		}
		else {
			$pdo->query("update `$tbl[card]` set `wm_price`='$confirm_price' where `no`='$card[no]'");
		}
		$stat=2;
		$msg="거래취소성공!";

		if($refundNo) { // 부분취소시 tid 업데이트 by zardsama
			$pdo->query("update $tbl[card] set env_info='$refundNo' where no='$card[no]'");
		}

		$resultMsg = '환불거래가 성공하였습니다.';

	}else{
		$msg="거래취소실패! (".$resultCode."/".$errorCD." : ".$resultMsg.")";
	}

	$pdo->query("
		insert into `$tbl[card_cc_log]` (`cno`, `stat`, `ono`, `tno`, `price`, `res_cd`, `res_msg`, `admin_id`, `admin_no`, `ip`, `reg_date`)
		values ('$card[no]', '$stat', '$card[wm_ono]', '$refundNo', '$CancelAmt', '$resultCode', '$resultMsg', '$admin[admin_id]', '$admin[no]', '$_SERVER[REMOTE_ADDR]', '$now')
	");

    // 주문서 처리와 함께 카드 취소
    if (isset($duel_card_cancel) == true && $duel_card_cancel == true) {
        $card_cancel_result = ($resultCode == 0) ? 'success' : $resultMsg;
        return;
    }

	msg($msg, "reload", "parent");

?>