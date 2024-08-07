<?php

/**
 * 정기배송 엑셀 출력
 **/

set_time_limit(0);
ini_set('memory_limit', -1);

include 'sbscr_list.php';

$headerType = array();
$headerStyle = array(
    'fill' => '#f2f2f2',
    'font-style' => 'bold',
    'halign' => 'center',
    'widths' => array()
);
$widths = array(
    'addressee_addr1' => 80,
    'addressee_addr2' => 80
);



$file_name = '정기배송 목록';

// field
$fields = array(
    'sbono' => '주문번호', 'date1' => '주문일자', 'date2' => '입금일자', 'stat_s' => '주문상태', 'pay_type_s' => '결제방식',
    'title' => '상품명', 's_total_prc' => '주문금액', 's_pay_prc' => '결제금액', 's_dlv_prc' => '배송비', 's_sale_prc' => '할인금액',
    'buyer_name' => '주문자명', 'member_id' => '회원아이디',
    'buyer_cell' => '휴대폰번호', 'buyer_phone' => '전화번호', 'buyer_email' => '이메일',
    'addressee_name' => '수령자명', 'addressee_zip' => '배송지 우편번호',
    'addressee_addr1' => '배송지 주소', 'addressee_addr2' => '배송지 상세주소', 'dlv_memo' => '배송메모'
);

$col = 0;
foreach($fields as $key => $val) {
    $headerType[$val] = (preg_match('/_prc$/', $key) == true) ? 'price' : 'string';
    $headerStyle['widths'][] = (!empty($widths[$key])) ? $widths[$key] : 30;
}
$ExcelWriter = setExcelWriter();
$ExcelWriter->setFileName($file_name);
$ExcelWriter->setSheetName($file_name);
$ExcelWriter->writeSheetHeader($headerType, $headerStyle);

// values
$row = 1;
foreach ($res as $data) {
    $data['date1'] = date('Y-m-d H:i', $data['date1']);
    $data['date2'] = ($data['date2']) ? date('Y-m-d H:i', $data['date2']) : '';
    $data['stat_s'] = $_sbscr_order_stat[$data['stat']];
    $data['pay_type_s'] = $_pay_type[$data['pay_type']];
    $data['s_total_prc_s'] = parsePrice($data['s_total_prc']);
    foreach($fields as $key => $val) {
        if (preg_match('/_prc$/', $key) == true) $data[$key] = parsePrice($data[$key]);
        $row = array();
        foreach($fields as $key => $val){
            $row[] = $data[$key];
        }
    }
    $ExcelWriter->writeSheetRow($row);
    unset($row);
}

$ExcelWriter->writeFile();
