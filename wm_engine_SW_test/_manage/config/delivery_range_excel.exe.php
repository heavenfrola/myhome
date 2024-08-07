<?php

/**
 * 배송 불가 지역 설정 엑셀 다운로드
 **/

$type = $_GET['type'];
$filename = ($type == 'D') ? '배송불가지역' : '배송허용지역';

$ExcelWriter = setExcelWriter();
$ExcelWriter->setFileName($filename.'_'.date('Ymd'));
$ExcelWriter->setSheetName($filename.'_'.date('Ymd'));

// header
$list = array(
    'no' => '번호',
    'name' => '배송지별칭',
    'sido' => '시/도',
    'gugun' => '구/군',
    'dong' => '동',
    'ri' => '리',
    'reason' => '사유',
);
$headerType = array();
$headerStyle = array(
    'fill' => '#f2f2f2',
    'font-style' => 'bold',
    'widths' => array()
);
foreach ($list as $val) {
    $headerType[$val] = 'string';
    $headerStyle['widths'][] = 30;
}
$headerStyle['widths'][0] = 10;
$ExcelWriter->writeSheetHeader($headerType, $headerStyle);

// body
$res = $pdo->iterator("select * from {$tbl['delivery_range']} where type=? and partner_no=? order by name asc", array(
    $type, (int) $admin['partner_no']
));
foreach ($res as $data) {
    $row = array();
    foreach($list as $key => $val) {
        $row[] = stripslashes($data[$key]);
    }
    $ExcelWriter->writeSheetRow($row);
    unset($row);
}

// output
$ExcelWriter->writeFile();