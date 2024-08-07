<?php

/**
 * 재입고 알림 신청 내역 엑셀 출력
 **/

include 'notify_restock.php';

$res = $pdo->iterator($sql);

// 엑셀 세팅
$headerStyle = array(
    'fill' => '#f2f2f2',
    'font-style' => 'bold',
    'halign' => 'center',
    'widths' => array()
);

$field = array(
    'idx' => array('번호', 10, 'string'),
    'product_name' => array('상품명', 50, 'string'),
    'option' => array('옵션', 30, 'string'),
    'sell_prc' => array('상품금액', 20, 'price'),
    'member_id' => array('회원아이디', 25, 'string'),
    'buyer_cell' => array('신청번호', 20, 'string'),
    'reg_date' => array('신청일시', 25, 'string'),
    'send_date' => array('발송일시', 25, 'string'),
    'stat' => array('상태', 10, 'string'),
);

$ExcelWriter = setExcelWriter();
$headerType = array();
foreach ($field as $key => $val) {
    $headerType[$val[0]] = $val[2];
    $headerStyle['widths'][] = $val[1];
}

$file_name = '재입고_알림_신청_내역';
$ExcelWriter->setFileName($file_name);
$ExcelWriter->setSheetName($file_name);
$ExcelWriter->writeSheetHeader($headerType, $headerStyle);

// 셀 데이터 세팅
$i = 0;
foreach ($res as $data) {
    $data['idx'] = ++$i;
    $data['reg_date'] = date('Y-m-d H:i:s', $data['reg_date']);
    $data['send_date'] = ($data['send_date']) ? date('Y-m-d H:i:s', $data['send_date']) : '-';
    $data['stat'] = $stat_array[$data['stat']];

    $row = array();
    foreach ($field as $key => $val) {
        $row[] = $data[$key];
    }
    $ExcelWriter->writeSheetRow($row);
}
$ExcelWriter->writeFile();