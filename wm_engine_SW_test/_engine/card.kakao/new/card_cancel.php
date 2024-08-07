<?PHP

/* +----------------------------------------------------------------------------------------------+
' |  카카오페이 결제 취소
' +----------------------------------------------------------------------------------------------+*/

include_once $engine_dir."/_engine/include/common.lib.php";

$_tmp_price = parsePrice($card['wm_price'], false);

if ($price > 0) { //부분취소
	$CancelCode = '1';
	$CancelAmt = $price;
} else {
	$CancelCode = '0';
	$CancelAmt = $_tmp_price;
}

//관리자 결제 취소
$cno = numberOnly($card['no']);
$amt = numberOnly($CancelAmt);
$code = $CancelCode;
$card = get_info($tbl[card], "no", $cno);
$adminkey = $cfg['kaka_admin_key']; // admin 키
$cid = $cfg['kakao_cid']; // cid

$_chk_price = parsePrice($card['wm_price'], false);
$confirm_price = $_chk_price - $amt;

$req_auth = 'Authorization: KakaoAK '.$adminkey;
$req_cont = 'Content-type: application/x-www-form-urlencoded;charset=utf-8';

$kakao_header = array($req_auth, $req_cont);

$kakao_params = array(
	'cid' => $cid, // 가맹점코드 10자
	'tid' => $card['tno'], // 결제 고유번호. 결제준비 API의 응답에서 얻을 수 있음
	'cancel_amount' => $CancelAmt, // 가맹점 주문번호. 결제준비 API에서 요청한 값과 일치해야 함
	'cancel_tax_free_amount' => '0' //취소 비과세 금액
);

$Result = comm('https://kapi.kakao.com/v1/payment/cancel', http_build_query($kakao_params), '', $kakao_header);
$result_json = json_decode($Result, true);

$respcode = $result_json['code'];
$respmsg = addslashes($result_json['msg']);
$stat = 1;
$rtn_etc = array();

if ($result_json['aid']) { //취소성공
	if($confirm_price == 0) {
		$pdo->query("update `$tbl[card]` set `stat`='3' where `no`='$card[no]'");
	}
	else {
		$pdo->query("update `$tbl[card]` set `wm_price`='$confirm_price' where `no`='$card[no]'");
	}
	$msg = "거래취소성공!";
	$stat = 2;
}else{
	$msg = "거래취소실패! (".$respcode."/".$respmsg.")";
}

$pdo->query("
	insert into `$tbl[card_cc_log]` (`cno`, `stat`, `ono`, `tno`, `price`, `res_cd`, `res_msg`, `admin_id`, `admin_no`, `ip`, `reg_date`)
	values ('$card[no]', '$stat', '$card[wm_ono]', '$card[tno]', '$CancelAmt', '$respcode', '$respmsg', '$admin[admin_id]', '$admin[no]', '$_SERVER[REMOTE_ADDR]', '$now')
");

// 주문서 처리와 함께 카드 취소
if (isset($duel_card_cancel) == true && $duel_card_cancel == true) {
    $card_cancel_result = ($stat == 2) ? 'success' : $respmsg;
    return;
}

msg($msg, "reload", "parent");