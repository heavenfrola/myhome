<?PHP

	include_once $engine_dir."/_manage/erp/order_list_search.inc.php";

	$res = $pdo->iterator($list_sql);
    $headerType = array(
        '발주번호' => 'string',
        '발주일자' => 'string',
        '사입처' => 'string',
        '발주상태' => 'string',
        '발주상품수' => 'string',
        '총발주금액' => 'price',
    );
    $headerStyle = array(
        'fill' => '#f2f2f2',
        'font-style' => 'bold',
        'widths' => array()
    );
    $file_name = 'ERP Order';
    $ExcelWriter = setExcelWriter();
    $ExcelWriter->setFileName($file_name);
    $ExcelWriter->setSheetName($file_name);
    $ExcelWriter->writeSheetHeader($headerType, $headerStyle);


    foreach ($res as $data) {
        $row = array(
            $data['order_no'],
            $data['order_date'],
            $data['provider'],
            $data['order_stat'],
            $data['total_qty'],
            parsePrice($data['total_amt'])
        );
        $ExcelWriter->writeSheetRow($row);
        unset($row);
	}

    $ExcelWriter->writeFile();
