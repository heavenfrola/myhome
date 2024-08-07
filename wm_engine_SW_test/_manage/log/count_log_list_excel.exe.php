<?PHP

/**
 * 상세 접속로그 엑셀 다운로드
 **/

set_time_limit(0);
ini_set('memory_limit', -1);

require_once 'count_log_list.php';

$row_style = array();
$headerType = array();
$headerStyle = array(
    'fill' => '#f2f2f2',
    'font-style' => 'bold',
    'halign'    => 'center',
    'widths' => array()
);

$col_list = array();
$col_list['ip'] = array('아이피', 20);
$col_list['referer'] = array('접속경로', 70);
$col_list['os'] = array('os', 20);
$col_list['browser'] = array('브라우저', 20);
$col_list['time'] = array('접속일시', 20);
$col_list['conversion'] = array('유입경로', 55);

foreach ($col_list as $val) {
    $headerType[$val[0]] = 'string';
    $headerStyle['widths'][] = $val[1];
    $row_style[] = array('halign' => 'left');
}

$file_name = '상세접속로그';
$ExcelWriter = setExcelWriter();
$ExcelWriter->setFileName($file_name);
$ExcelWriter->setSheetName($file_name);
$ExcelWriter->writeSheetHeader($headerType, $headerStyle);

$res = $pdo->iterator("select * from $log_table where 1 $w order by time asc");
foreach ($res as $data) {
    $ExcelWriter->writeSheetRow(array(
        $data['ip'],
        $data['referer'],
        $data['os'],
        $data['browser'],
        date('Y-m-d H:i:s', $data['time']),
        dispConversionText($data['conversion'])
    ), $row_style);
}

$ExcelWriter->writeFile();