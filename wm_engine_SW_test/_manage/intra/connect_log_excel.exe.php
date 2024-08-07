<?PHP

/**
 * 개인정보 접속기록 내역 엑셀 다운로드
 **/

$row_style = array();
$headerType = array();
$headerStyle = array(
    'fill' => '#f2f2f2',
    'font-style' => 'bold',
    'halign' => 'center',
    'widths' => array()
);

require $engine_dir.'/_manage/intra/connect_log.php';

$res = $pdo->iterator($sql);

// table header
$col_list = array(
    'member_name' => array('접속일시', 20, 'center'),
    'member_id' => array('관리자', 20, 'center'),
    'mtype' => array('아이디', 20, 'center'),
    'title' => array('구분', 20, 'left'),
    'amount' => array('접속페이지', 40, 'center'),
    'member_emoney' => array('수행업무', 40, 'center'),
    'reg_date' => array('접속아이피', 20, 'center')
);

foreach ($col_list as $key => $val) {
    $headerType[$val[0]] = 'string';
    $headerStyle['widths'][] = $val[1];
    $row_style[] = array('halign' => $val[2]);
}

$file_name = 'connect_log_'.date('Ymd');
$ExcelWriter = setExcelWriter();
$ExcelWriter->setFileName($file_name);
$ExcelWriter->setSheetName($file_name);
$ExcelWriter->writeSheetHeader($headerType, $headerStyle);

// table body
foreach ($res as $data) {
    switch($data['page_type']) {
        case 'list' : $page_type = '리스트'; break;
        case 'view' : $page_type = '상세'; break;
        case 'excel' : $page_type = '엑셀 다운로드'; break;
        default :
            $page_type = $data['page_type'];
    }

    $ExcelWriter->writeSheetRow(array(
        date('Y-m-d H:i:s', $data['reg_date']),
        $data['name'],
        $data['admin_id'],
        $_page_name[$data['page_id']],
        $page_type,
        getTarget($data),
        $data['ip']
    ), $row_style);
    unset($row);
}
$ExcelWriter->writeFile();