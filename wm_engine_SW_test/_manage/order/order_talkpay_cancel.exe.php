<?php

/**
 * 카카오페이구매 클레임 처리
 **/

use Wing\API\Kakao\KakaoTalkPay;

if (is_object($talkpay) == false) {
    $talkpay = new KakaoTalkPay($scfg);
}

switch($stat) {
    case '401' : // 발송 지연
        checkBlacnk($_POST['estimatedDeliveryDate'], '배송 예정일을 선택해주세요.');
        checkBlacnk($_POST['reasonType'], '배송지연 사유를 선택해주세요.');

        $ret = $talkpay->delay(
            $prd['external_id'],
            $_POST['estimatedDeliveryDate'],
            $_POST['reasonType'],
            $_POST['message']
        );

        break;
    case '15' : // 환불
        $ret = $talkpay->cancelForce($ono, array($prd['external_id']), $_POST['requestType'], trim($_POST['comment']));

        break;
    case '131' : // 취소 승인
        $ret = $talkpay->cancelApprove($ono, array($prd['external_id']));
        if ($prd['stat'] == '14') $stat = 15;

        break;
    case '132' : // 취소 불가 후 배송 처리
        $ret = $talkpay->cancelReject($ono, array($prd['external_id']), $_POST['deliveryMethod'], $_POST['dlv_no'], $_POST['dlv_code']);
        $stat = 4;

        break;
    case '16' : // 반품 요청
        checkBlank($_POST['requestReason'], '반품 요청 사유를 입력해주세요.');

        $ret = $talkpay->returnRequest(
            $ono, array($prd['external_id']), $_POST['collectMethodType'],
            $_POST['requestType'], $_POST['requestReason'],
            $_POST['dlv_no'], $_POST['dlv_code']
        );
        break;
    case '17' : // 반품 승인
        $ret = $talkpay->returnApprove($ono, array($prd['external_id']));

        break;
    case '171' : // 반품 보류
        checkBlank($_POST['holdbackReason'], '반품 보류 사유를 입력해주세요.');

        $ret = $talkpay->returnHoldback($ono, array($prd['external_id']), $_POST['holdbackReason'], $_POST['extraFee']);

        break;
    case '27' : // 반품 거부
        checkBlank($_POST['reason'], '반품 거부 사유를 입력해주세요.');

        $ret = $talkpay->returnReject($ono, array($prd['external_id']), $_POST['reason']);
        $stat = $talkpay->getCurrentStatus($prd['external_id']);

        break;
    case '18' : // 교환 요청
        checkBlank($_POST['requestReason'], '교환 요청 사유를 입력해주세요.');

        $ret = $talkpay->exchangeRequest(
            $ono, array($prd['external_id']), $_POST['collectMethodType'],
            $_POST['requestType'], $_POST['requestReason'],
            $_POST['dlv_no'], $_POST['dlv_code']
        );
        break;
    case '26' : // 교환 재배송
        $ret = $talkpay->exchangeRedelivery(
            $ono, array($prd['external_id']), $_POST['deliveryMethod'], $_POST['dlv_no'], $_POST['dlv_code']
        );
        break;
    case '191' : // 교환 보류
        checkBlank($_POST['holdbackReason'], '교환 보류 사유를 입력해주세요.');

        $ret = $talkpay->exchangeHoldback($ono, array($prd['external_id']), $_POST['holdbackReason'], $_POST['extraFee']);

        break;
    case '28' : // 교환 거부
        checkBlank($_POST['reason'], '교환 거부 사유를 입력해주세요.');

        $ret = $talkpay->exchangeReject($ono, array($prd['external_id']), $_POST['reason']);
        $stat = $talkpay->getCurrentStatus($prd['external_id']);

        break;
}