<?php

/**
 * 네이버페이 결제 승인
 **/

use Wing\API\Naver\NaverSimplePay;

include_once $engine_dir."/_engine/include/common.lib.php";
include_once $engine_dir."/_engine/include/shop.lib.php";

$pay = new NaverSimplePay($scfg, $_SESSION['nsp_sbscr']);

$redir_qry = array();
if ($_SESSION['nsp_sbscr'] == 'Y') {
    $redir_qry['sbscr'] = 'Y';
    unset($_SESSION['nsp_sbscr']);
}
if ($_SESSION['nsp_cart_selected']) {
    $redir_qry['cart_selected'] = $_SESSION['nsp_cart_selected'];
    unset($_SESSION['nsp_cart_selected']);
}
if (count($redir_qry) > 0) {
    $redir_qry = http_build_query($redir_qry);
}
$redir_url = $root_url.'/shop/order.php';
if ($redir_qry) {
    $redir_url .= '?'.$redir_qry;
}

// 결제 창으로 부터 결과 수신
if ($_GET['resultCode'] != 'Success') {
    msg($pay->resultMessage($_GET['resultMessage']), $redir_url);
}

// 결제 승인
$paymentId = $_GET['paymentId'];
$ret = $pay->payment($paymentId);
if (gettype($ret) != 'object') {
    msg('결제승인이 실패되었습니다.', $redir_url, 'parent');
}
$detail = $ret->body->detail;

// 로그 저장
startOrderLog($detail->merchantPayKey, 'card_pay.exe.php');

// 데이터 처리
$paymentId = $detail->paymentId;
$ono = $detail->merchantPayKey;
$tradeConfirmYmdt = $detail->tradeConfirmYmdt;
$totalPayAmount = $detail->totalPayAmount;
$primaryPayMeans = $detail->primaryPayMeans; // CARD or BANK
if ($primaryPayMeans == 'CARD') {
    $card_cd = $detail->cardCorpCode;
    $card_name = $pay->getCardName($detail->cardCorpCode);
    $card_no = $detail->cardNo;
    $card_inst = $detail->cardInstCount;
} else if ($primaryPayMeans == 'BANK') {
    $card_cd = $detail->bankCorpCode;
    $card_name = $pay->getCardName($detail->bankCorpCode);
    $card_no = $detail->bankAccountNo;
    $card_inst = 0;
}

if ($detail->admissionState == 'SUCCESS') { // 결제 성공
    // 주문 데이터 체크
    $ord = $pdo->assoc("select pay_prc, stat from {$tbl['order']} where ono='$ono'");
    if ($ord == false) {
        $ord = $pdo->assoc("select s_total_prc as pay_prc, stat from {$tbl['sbscr']} where sbono='$ono'");
        if ($ord == false) {
            msg(__lang_mypage_error_orderNotExist__, $redir_url);
        }
        $sbscr = 'Y';
        $sno = $ono;
    }
    if (parsePrice($ord['pay_prc']) != $totalPayAmount) {
        msg('결제 금액이 일치하지 않습니다.', $redir_url);
    }
    if ($ord['stat'] != 1 && $ord['stat'] != 11) {
        msg('이미 결제 승인 된 주문입니다.', '/');
    }

    // 결제 로그 저장
    $pdo->query("
        update {$tbl['card']} set
            stat='2', res_cd='0000', res_msg=?, ordr_idxx=?, tno=?, good_mny=?, use_pay_method=?,
            card_cd=?, card_name=?, quota=?, app_time=?, app_no=?
        where wm_ono=?
    ", array(
        $detail->admissionState, $ono, $paymentId, $totalPayAmount, $primaryPayMeans,
        $card_cd, $card_name, $card_inst, $tradeConfirmYmdt, $detail->cardAuthNo, $ono
    ));

    $card_pay_ok = true;
    include_once $engine_dir."/_engine/order/order2.exe.php";

    exit('OK');
} else { // 결제 실패
    $message = '';
    if ($ret->message) {
        $message = php2java($ret->message);
    }
    msg("결제가 실패되었습니다.\\n".$message, $redir_url, 'parent');
}