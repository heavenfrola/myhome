<?PHP

/**
 * 네이버페이 정기결제 실 결제
 **/

use Wing\API\Naver\NaverSimplePay;

function nspBillPay($paydata, $ono) {
    global $pdo, $scfg;

    $pay = new NaverSimplePay($scfg, 'Y');

    // 정기결제 예약
    $ret = $pay->recurrentReserve($paydata, $ono);
    if ($ret->code != 'Success') return array('result' => false);

    // 정기결제 승인
    $ret = $pay->recurrentApproval($ret->body->recurrentId, $ret->body->paymentId);

    return array(
        'result' => ($ret->code == 'Success') ? true : false,
        'tid' => $ret->body->detail->paymentId,
        'card_cd' => $ret->body->detail->cardCorpCode,
        'card_name' => $pay->getCardName($ret->body->detail->cardCorpCode),
        'app_no' => $ret->body->detail->cardAuthNo,
        'res_cd' => $ret->code,
        'res_msg' => $ret->message,
        'quota' => $ret->body->detail->cardInstCount,
        'amount' => $ret->body->detail->totalPayAmount,
    );
}