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
        'unit_name' => 10,
        'cancel1_cnt' => 10,
        'cancel2_cnt' => 10,
        'cancel3_cnt' => 10
    );
    $contentStyle = array(
        'unit_name' => array('halign' => 'center')
    );
    $header_row = array();
    $header_row[0] = array(
        '구분', '주문', 'emptyCell', '미입금',
        'emptyCell', '결제', 'emptyCell', 'emptyCell',
        'emptyCell', 'emptyCell', 'emptyCell', 'emptyCell',
        'emptyCell', 'emptyCell', '입금전취소', '배송전취소',
        '반품/교환'
    );
    $header_row[1] = array(
        'emptyCell', '건수', '주문금액', '건수',
        '금액', '배송비', '할인가', '쿠폰',
        '적립금', '예치금', '부분취소', '실결제금액',
        'emptyCell', 'emptyCell', '건수', '건수',
        '건수'
    );
    $header_row[2] = array(
        'emptyCell', 'emptyCell', 'emptyCell', 'emptyCell',
        'emptyCell', 'emptyCell', 'emptyCell', 'emptyCell',
        'emptyCell', 'emptyCell', 'emptyCell', '현금',
        'PG', '합계', 'emptyCell', 'emptyCell', 'emptyCell'
    );
    $col_list = array(
        'unit_name',
        'order_cnt',
        'order_prc',
        'cnt',
        'prc',
        'dlv_prc',
        'sale_prc',
        'cpn_prc',
        'milage_prc',
        'emoney_prc',
        'part_repay_prc',
        'bank_prc',
        'pg_prc',
        'pay_prc',
        'cancel1_cnt',
        'cancel2_cnt',
        'cancel3_cnt'
    );

	include 'income_log.php';

	$title = $yy;
	if($mm) $title .= '_'.$mm;
	if($dd) $title .= '_'.$dd;

    $row_style = array();

    foreach ($col_list as $key) {
        $headerStyle['widths'][] = (!empty($widths[$key])) ? $widths[$key] : 15;
        $row_style[] = (!empty($contentStyle[$key])) ? $contentStyle[$key] : array('halign' => 'right');
        $headerType[$key] = 'string';
    }

    $file_name = '매출통계';
    $ExcelWriter = setExcelWriter();
    $ExcelWriter->setFileName($file_name);
    $ExcelWriter->setSheetName($file_name);
    //제목줄은 데이터 형과 스타일만 정의하고 숨김 ($headerStyle['suppress_row'] = true)
    $ExcelWriter->writeSheetHeader($headerType, $headerStyle);

    foreach ($header_row as $title_arr) {
        //writeSheetRow내에서 $headerStyle['suppress_row']옵션은 무시된다
       $ExcelWriter->writeSheetRow($title_arr, $headerStyle);
    }

    $ExcelWriter->merge(0, 0, 2, 0); //구분
    $ExcelWriter->merge(0, 1, 0, 2); //주문
    $ExcelWriter->merge(0, 3, 0, 4); //미입금
    $ExcelWriter->merge(0, 5, 0, 13); //결제
    $ExcelWriter->merge(1, 1, 2, 1); //주문 건수
    $ExcelWriter->merge(1, 2, 2, 2); //주문 금액
    $ExcelWriter->merge(1, 3, 2, 3); //미입금 건수
    $ExcelWriter->merge(1, 4, 2, 4); //미입금 금액
    $ExcelWriter->merge(1, 5, 2, 5); //배송비
    $ExcelWriter->merge(1, 6, 2, 6); //할인가
    $ExcelWriter->merge(1, 7, 2, 7); //쿠폰
    $ExcelWriter->merge(1, 8, 2, 8); //적립금
    $ExcelWriter->merge(1, 9, 2, 9); //예치금
    $ExcelWriter->merge(1, 10, 2, 10); //부분취소
    $ExcelWriter->merge(1, 11, 1, 13); //실결제금액
    $ExcelWriter->merge(1, 14, 2, 14); //입금전취소 > 건수
    $ExcelWriter->merge(1, 15, 2, 15); //배송전취소 > 건수
    $ExcelWriter->merge(1, 16, 2, 16); //반품/교환 > 건수

    for ($i = $uunit_min; $i <= $uunit_max; $i++) {
        $data = $udata[$i];
        $data['unit_name'] = $i.$uunit_name;
		$data['order_cnt'] = number_format($data['pc_order_cnt']+$data['mb_order_cnt']+$data['ap_order_cnt']);
        $data['order_prc'] = parsePrice($data['pc_order_prc']+$data['mb_order_prc']+$data['ap_order_prc'], true);
        $data['cnt'] = number_format($data['pc_1_cnt']+$data['mb_1_cnt']+$data['ap_1_cnt']);
        $data['prc'] = parsePrice($data['pc_1_prc']+$data['mb_1_prc']+$data['ap_1_prc'], true);
        $data['dlv_prc'] = parsePrice($data['pc_dlv_prc']+$data['mb_dlv_prc']+$data['ap_dlv_prc'], true);
        $data['sale_prc'] = parsePrice($data['pc_sale_prc']+$data['mb_sale_prc']+$data['ap_sale_prc'], true);
        $data['cpn_prc'] = parsePrice($data['pc_cpn_prc']+$data['mb_cpn_prc']+$data['ap_cpn_prc'], true);
        $data['milage_prc'] = parsePrice($data['pc_milage_prc']+$data['mb_milage_prc']+$data['ap_milage_prc'], true);
        $data['emoney_prc'] = parsePrice($data['pc_emoney_prc']+$data['mb_emoney_prc']+$data['ap_emoney_prc'], true);
        $data['part_repay_prc'] = parsePrice($data['pc_part_repay_prc']+$data['mb_part_repay_prc']+$data['ap_part_repay_prc'], true);
        $data['bank_prc'] = parsePrice($data['pc_bank_prc']+$data['mb_bank_prc']+$data['ap_bank_prc'], true);
        $data['pg_prc'] = parsePrice(($data['pc_pay_prc']+$data['mb_pay_prc']+$data['ap_pay_prc'])-($data['pc_bank_prc']+$data['mb_bank_prc']+$data['ap_bank_prc']), true);
        $data['pay_prc'] = parsePrice($data['pc_pay_prc']+$data['mb_pay_prc']+$data['ap_pay_prc'], true);
        $data['cancel1_cnt'] = number_format($data['pc_cancel1_cnt']+$data['mb_cancel1_cnt']+$data['ap_cancel1_cnt']);
        $data['cancel2_cnt'] = number_format($data['pc_cancel2_cnt']+$data['mb_cancel2_cnt']+$data['ap_cancel2_cnt']);
        $data['cancel3_cnt'] = number_format($data['pc_cancel3_cnt']+$data['mb_cancel3_cnt']+$data['ap_cancel3_cnt']);
        $row = array();
        foreach ($col_list as $key) {
            $row[] = $data[$key];
        }
        $ExcelWriter->writeSheetRow($row, $row_style);
        unset($row);
    }
    unset($row_style);
    $ExcelWriter->writeFile();
