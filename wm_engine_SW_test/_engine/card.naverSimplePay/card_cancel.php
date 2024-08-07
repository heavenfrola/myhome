<?php

/**
 * 네이버페이 결제 취소
 **/

use Wing\API\Naver\NaverSimplePay;

$subscription = ($card['pg_version'] == 'autobill') ? 'Y' : 'N';
if ($subscription == 'N') { // 정기-일괄 결제
    $pay_type = $pdo->row("select pay_type from {$tbl['sbscr']} where sbono=?", array($card['wm_ono']));
    if ($pay_type == '25') {
        $subscription = 'A';
    }
}

// 복합과세 적용
if (preg_match('/[^0-9]/', $_GET['price'])) msg('취소 금액에는 숫자만 입력할 수 있습니다.');
if (preg_match('/[^0-9]/', $_GET['taxScopeAmount'])) msg('취소 금액에는 숫자만 입력할 수 있습니다.');
if (preg_match('/[^0-9]/', $_GET['taxExScopeAmount'])) msg('취소 금액에는 숫자만 입력할 수 있습니다.');
if (
    ($subscription == 'N' && $scfg->comp('nsp_use_tax', 'Y') == true)
    || ($subscription == 'Y' && $scfg->comp('nsp_sub_use_tax', 'Y') == true)
) {
    $taxScopeAmount = (int) $_GET['taxScopeAmount'];
    $taxExScopeAmount = (int) $_GET['taxExScopeAmount'];
    $price = ($taxScopeAmount + $taxExScopeAmount);
} else {
    $taxScopeAmount = (int) $_GET['price'];
    $taxExScopeAmount = 0;
    if (!$taxScopeAmount) $taxScopeAmount = $card['wm_price'];
}

// 마이페이지 전체 취소
if (isset($duel_card_cancel) == true && $taxScopeAmount+$taxExScopeAmount == 0) {
    $taxScopeAmount = parsePrice($card['wm_price']-$card['wm_free_price']);
    $taxExScopeAmount = parsePrice($card['wm_free_price']);
}

startOrderLog($cart['wm_ono'], 'card_cancel.php');

$pay = new NaverSimplePay($scfg, $subscription);
$ret = $pay->cancel($card['tno'], '2', $taxScopeAmount, $taxExScopeAmount);

// 로그 저장
$tno = ($ret->body->paymentId) ? $ret->body->paymentId : '';
$restAmount = $ret->body->primaryPayRestAmount + $ret->body->npointRestAmount;
$free_restAmount = $ret->body->taxExScopeRestAmount;

if ($ret->code == 'Success') {
    $asql = '';
    if ($restAmount == 0) {
        $asql .= ", stat=3";
    }
    $cancel_stat = 2;

    // 카드 로그 갱신
    $pdo->query("update {$tbl['card']} set wm_price=?, wm_free_price=? $asql where no=?", array(
        $restAmount, $free_restAmount, $card['no']
    ));
} else {
    $cancel_stat = 1;
}

$pdo->query("
    insert into {$tbl['card_cc_log']}
    (cno, price, stat, ono, tno, res_cd, res_msg, admin_id, admin_no, ip, reg_date)
    values
    (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
", array(
    $card['no'], $price, $cancel_stat, $card['wm_ono'], $tno, $ret->code, $ret->message,
    $admin['admin_id'], $admin['no'], $_SERVER['REMOTE_ADDR'], $now
));

// 주문서 처리와 함께 카드 취소
if (isset($duel_card_cancel) == true && $duel_card_cancel == true) {
    $card_cancel_result = ($ret->code == 'Success') ? 'success' : $ret->message;
    return;
}

msg($ret->message, 'reload', 'parent');