<?PHP

/**
 * 스마트스토어 반품/취소 처리
 */

$nprd = $CommerceAPI->ordersQuery($prd['smartstore_ono']);
if (!$nprd->data) {
    msg('스마트스토어 상품 주문 정보를 가져올 수 없습니다.');
}
$nprd = $nprd->data[0]->productOrder;

$claim_type = $nprd->claimType;
$claim_status = $nprd->claimStatus;

switch($_REQUEST['stat']) {
    case '13' :
        if ($claim_status == 'CANCEL_REQUEST') {
            $ret = $CommerceAPI->cancelApprove($prd['smartstore_ono']);
        } else {
            $ret = $CommerceAPI->cancel(
                $prd['smartstore_ono'],
                $_REQUEST['cancelReason']
            );
        }
    break;
    case '16' :
        $ret = $CommerceAPI->returnRequest(
            $prd['smartstore_ono'],
            $_REQUEST['returnReason'],
            $_REQUEST['collectDeliveryCompany'],
            $_REQUEST['collectTrackingNumber']
        );
    break;
    case '17' :
        $ret = $CommerceAPI->returnApprove(
            $prd['smartstore_ono']
        );
    break;
    case '27' : // 반품 거부
        $ret = $CommerceAPI->returnReject(
            $prd['smartstore_ono'],
            $_REQUEST['rejectReturnReason']
        );
        break;
    case '171' : // 반품 보류
        $ret = $CommerceAPI->returnHoldback(
            $prd['smartstore_ono'],
            $_REQUEST['holdbackClassType'],
            $_REQUEST['holdbackReturnDetailReason'],
            $_REQUEST['extraReturnFeeAmount']
        );
        break;
    case '172' : // 반품 보류 해제
        $ret = $CommerceAPI->returnHoldbackRelease($prd['smartstore_ono']);
        break;
    case '25' : // 교환 수거 완료
        $ret = $CommerceAPI->exchangeCollectApprove($prd['smartstore_ono']);
    break;
    case '26' : // 교환 재배송
        $ret = $CommerceAPI->exchangeDispatch(
            $prd['smartstore_ono'],
            $_REQUEST['reDeliveryCompany'],
            $_REQUEST['reDeliveryTrackingNumber']
        );
        $stat = 4;
    break;
    case '28' : // 교환 거부
        $ret = $CommerceAPI->exchangeReject(
            $prd['smartstore_ono'],
            $_REQUEST['rejectExchangeReason']
        );
    break;
    case '191' : // 교환 보류
        $ret = $CommerceAPI->exchangeHoldback(
            $prd['smartstore_ono'],
            $_REQUEST['holdbackClassType'],
            $_REQUEST['holdbackExchangeDetailReason'],
            $_REQUEST['extraExchangeFeeAmount']
        );
    break;
    case '192' : // 교환보류 해제
        $ret = $CommerceAPI->exchangeHoldbackRelease(
            $prd['smartstore_ono']
        );
    break;
    case '401' : // 발송 지연
        $ret = $CommerceAPI->orderDelay(
            $prd['smartstore_ono'],
            $_REQUEST['dispatchDueDate'],
            $_REQUEST['delayedDispatchReason'],
            $_REQUEST['dispatchDelayedDetailedReason']
        );
    break;
}

if (!$ret) {
    $CommerceAPI->setError('스마트스토어 응답이 없습니다.');
    return;
}

if (!$CommerceAPI->getError()) {
    if (!count($ret->data->successProductOrderIds)) {
        if ($ret->message) {
            $CommerceAPI->setError($ret->message);
        } else {
            $CommerceAPI->setError($ret->data->failProductOrderInfos[0]->message);
        }
    }

    // 처리 후 원 주문 상태 다시 가져오기 (배송중, 배송완료)
    if (in_array($_REQUEST['stat'], array('27', '28', '171', '172', '401'))) {
        $stat = $CommerceAPI->getCurrentStat($prd['smartstore_ono']);
    }
}