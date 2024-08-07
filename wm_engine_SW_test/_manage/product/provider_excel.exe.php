<?PHP


	/* +----------------------------------------------------------------------------------------------+
	' |  사입처 엑셀저장 처리
	' +----------------------------------------------------------------------------------------------+*/
    $ExcelWriter = setExcelWriter();
    $headerType = array();
    $headerStyle = array(
        'fill' => '#f2f2f2',
        'font-style' => 'bold'
    );
	$mode = 'xls'; //provider.php내 파일처리를 위한 플래그
	include $engine_dir.'/_manage/product/provider.php';

	$res = $pdo->iterator($sql);

	$filename = '사입처목록';

    $col_list = array(
        'no' => '시스템코드',
        'arcade' => '상가',
        'floor' => '층',
        'plocation' => '위치',
        'provider' => '사입처명',
        'ptel' => '전화번호',
        'pcell' => '휴대폰',
        'pceo' => '대표자명',
        'account1_bank' => '계좌1 은행코드',
        'account1_nm' => '계좌1 은행명',
        'account1' => '계좌1 계좌번호',
        'account1_name' => '계좌1 예금주',
        'account2_bank' => '계좌2 은행코드',
        'account2_nm' => '계좌2 은행명',
        'account2' => '계좌2 계좌번호',
        'account2_name' => '계좌2 예금주',
        'content' => '메모'
       );
    foreach ($col_list as $key => $val) {
        $headerType[$val] = 'string';
        $headerStyle['widths'][] = 20;
    }
    $ExcelWriter->setFileName($filename);
    $ExcelWriter->setSheetName($filename);
    $ExcelWriter->writeSheetHeader($headerType, $headerStyle);
    foreach ($res as $data) {
        $data['account1_nm'] = $bank_codes[$data['account1_bank']];
        $data['account2_nm'] = $bank_codes[$data['account2_bank']];

        $data = array_map('stripslashes', $data);
        $row = array();
        foreach ($col_list as $key => $val) {
            $row[] = $data[$key];
        }
        $ExcelWriter->writeSheetRow($row);
        unset($row);
	}
    $ExcelWriter->writeFile();
