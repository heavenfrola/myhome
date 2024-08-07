<?php

set_time_limit(0);

use Wing\API\Naver\CommerceAPI;

require_once __ENGINE_DIR__ . '/_engine/include/common.lib.php';

$CommerceAPI = new CommerceAPI();

// 변경된 주문번호 체크
$microtime = microtime(true);
$from = ($_GET['from']) ? $_GET['from'] : date('Y-m-d H:i:s', strtotime('-3 hours'));
$to = ($_GET['to']) ? $_GET['to'] : '';
$moreSequence = null;

$orders = [];
if ($_GET['productOrderId']) {
    $orders[] = (object) array(
        'productOrderId' => $_GET['productOrderId']
    );
} else {
    while (1) {
        $ret = $CommerceAPI->ordersChanged(
            $from,
            $to,
            $moreSequence
        );
        foreach ($ret->data->lastChangeStatuses as $data) {
            array_push($orders, $data);
        }
        if (!$ret->data->more) break;

        $from = $ret->data->more->moreFrom;
        $moreSequence = $ret->data->more->moreSequence;
    }
}

// 주문서 갱신
$result = $CommerceAPI->orderSave($orders);

header('Content-type: application/json');
exit(json_encode_pretty(array(
    'started' => date('Y-m-d H:i:s', $microtime),
    'elapsed' => (microtime(true) - $microtime),
    'count' => count($orders),
    'updated' => $result
)));