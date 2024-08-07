<?PHP

/**
 * 상세 접속로그 엑셀 다운로드
 **/

set_time_limit(0);
ini_set('memory_limit', -1);

require_once __ENGINE_DIR__.'/_manage/intra/excel_otp.inc.php';
require_once 'member_access_log.php';

$row_style = array();
$headerType = array();
$headerStyle = array(
    'fill' => '#f2f2f2',
    'font-style' => 'bold',
    'halign'    => 'center',
    'widths' => array()
);

$col_list = array();
$col_list['member_id'] = array('아이디', 50);
$col_list['log_date'] = array('접속일시', 50);
$col_list['login_result'] = array('결과', 20);
$col_list['ip'] = array('브라우저', 20);

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

$res = $pdo->iterator($sql);
$NumTotalRec = $res->rowCount();

foreach ($res as $idx => $data) {
    $ExcelWriter->writeSheetRow(array(
        $data['member_id'],
        date('Y-m-d H:i:s', $data['log_date']),
        $_login_result[$data['login_result']],
        $data['ip']
    ), $row_style);

    // 엑셀 다운로드 기록
    if ($idx == 1) {
        addPrivacyViewLog(array(
            'page_id' => 'member_access_log',
            'page_type' => 'excel',
            'target_id' => $data['member_id'],
            'target_cnt' => $NumTotalRec
        ));
    }
}

$ExcelWriter->writeFile();