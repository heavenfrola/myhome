<?PHP
    $headerType = array();
    $headerStyle = array(
        'fill' => '#f2f2f2',
        'font-style' => 'bold',
        'halign' => 'center',
        'valign' => 'center',
        'widths' => array(),
        'suppress_row' => true
    );
    $widths = array(
        'saledate' => 20
    );
    $contentStyle= array(
        'saledate' => array('halign' => 'center')
    );
    $header_row = array();
    $header_row[0] = array(
        '구분', '주문', 'emptyCell', '입금전 취소',
        'emptyCell', '배송전 취소', 'emptyCell', '반품/교환',
        'emptyCell', '실 결제액'
    );
    $header_row[1] = array(
        'emptyCell', '건수', '금액', '건수',
        '금액', '건수', '금액', '건수',
        '금액', 'emptyCell'
    );
    $col_list = array(
        'saledate', 'ea', 'prc', 'ea_cancel',
        'prc_cancel', 'ea_cancel2', 'prc_cancel2','ea_cancel3',
        'prc_cancel3', 'pay_prc'
    );
    $row_style = array();

    foreach ($col_list as $key) {
        $headerType[$key] = 'string';
        $headerStyle['widths'][] = (!empty($widths[$key])) ? $widths[$key] : 20;
        $row_style[] = (!empty($contentStyle[$key])) ? $contentStyle[$key] : array('halign' => 'right');
    }

    $file_name = '개별상품판매분석';
	$ExcelWriter = setExcelWriter();
    $ExcelWriter->setFileName($file_name);
    $ExcelWriter->setSheetName($file_name);
    $ExcelWriter->writeSheetHeader($headerType, $headerStyle); //제목줄은 데이터 형만 정의하고 숨김

    foreach ($header_row as $title_arr) {
        $ExcelWriter->writeSheetRow($title_arr, $headerStyle);
    }

    $ExcelWriter->merge(0, 0, 1, 0); //구분
    $ExcelWriter->merge(0, 1, 0, 2); //주문
    $ExcelWriter->merge(0, 3, 0, 4); // 입금전 취소
    $ExcelWriter->merge(0, 5, 0, 6); //배송전 취소
    $ExcelWriter->merge(0, 7, 0, 8); //반품/교환
    $ExcelWriter->merge(0, 9, 1, 9); //실 결제액

	$income = ($sadmin == "Y") ? '/_partner/income' : '/_manage/income';

	include_once $engine_dir.$income.'/income_product_detail.php';

    $res = $pdo->iterator($qry);
    
    foreach ($res as $data) {
        $data['ea'] = number_format($data['pc_ea']+$data['mb_ea']);
        $data['prc'] = parsePrice($data['pc_prc']+$data['mb_prc']);
        $data['ea_cancel'] = number_format($data['pc_ea_cancel']+$data['mb_ea_cancel']);
        $data['prc_cancel'] = parsePrice($data['pc_prc_cancel']+$data['mb_prc_cancel'], $cfg['currency_decimal']);
        $data['ea_cancel2'] = number_format($data['pc_ea_cancel2']+$data['mb_ea_cancel2']);
        $data['prc_cancel2'] = parsePrice($data['pc_prc_cancel2']+$data['mb_prc_cancel2'], $cfg['currency_decimal']);
        $data['ea_cancel3'] = number_format($data['pc_ea_cancel3']+$data['mb_ea_cancel3']);
        $data['prc_cancel3'] = parsePrice($data['pc_prc_cancel3']+$data['mb_prc_cancel3'], $cfg['currency_decimal']);
        $data['pay_prc'] = parsePrice($data['pc_pay_prc']+$data['mb_pay_prc'], $cfg['currency_decimal']);
        $row = array();
        foreach ($col_list as $key) {
            $row[] = $data[$key];
        }
        $ExcelWriter->writeSheetRow($row, $row_style);
        unset($row);
    }
    $ExcelWriter->writeFile();
