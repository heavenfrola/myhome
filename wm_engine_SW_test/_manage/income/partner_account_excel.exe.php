<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  입점사 정산 정보 엑셀저장 처리
	' +----------------------------------------------------------------------------------------------+*/
	$income = ($sadmin == "Y") ? '/_partner/income' : '/_manage/income';

	include_once $engine_dir.$income.'/partner_account_edt.php';

    $headerType = array();
    $headerStyle = array(
        'fill' => '#f2f2f2',
        'font-style' => 'bold',
        'halign' => 'center',
        'widths' => array()
    );
    $col_list = array(
        'ono' => '주문번호',
        'name' => '상품명',
        'stat' => '주문상태',
        'sell_prc' => '단가',
        'buy_ea' => '주문수량',
        'total_prc' => '총상품금액',
        'sale_prc' => '할인금액',
        'sale5' => '쿠폰할인',
        'cpn_rate' => '쿠폰정산비율',
        'pay_prc' => '실결제금액',
        'fee_rate' => '수수료율',
        'fee_prc' => '입점수수료',
		'cpn_fee' => '쿠폰 업체부담',
		'account_prc' => '총정산금액'
    );
    $contentStyle = array(
        'total_prc' => array('halign'=>'right'),
        'cpn_fee' => array('halign'=>'right'),
        'pay_prc' => array('halign'=>'right'),
        'account_prc' => array('halign'=>'right'),
        'sell_prc' => array('halign'=>'right'),
		'fee_prc' => array('halign'=>'right')
    );
    $row_style = array();
    $exceptionColType = array(
		'total_prc' => 'price',
	    'cpn_fee' => 'price',
        'pay_prc' => 'price',
        'account_prc' => 'price',
		'sell_prc' => 'price',
		'sale5' => 'price',
		'sale_prc' => 'price',
		'fee_prc' => 'price'
    );

    foreach($col_list as $key => $val){
        $headerType[$val] = (!empty($exceptionColType[$key])) ? $exceptionColType[$key] : 'string';
        $headerStyle['widths'][] = (!empty($widths[$key])) ? $widths[$key] : 20;
        $row_style[] = (!empty($contentStyle[$key])) ? $contentStyle[$key] : array('halign' => 'center');
    }

    $file_name = '상품별매출';
    $ExcelWriter = setExcelWriter();
    $ExcelWriter->setFileName($file_name);
    $ExcelWriter->setSheetName($file_name);
    $ExcelWriter->writeSheetHeader($headerType, $headerStyle);

    foreach ($res as $data) {
		$row = array();
		if($ono != $data['ono']) {
			$data2['name'] = "배송비";
			$data2['ono'] = $data['ono'];
			$data2['pay_prc'] = $data['dlv_prc']; 
			$data2['account_prc']  = $data['dlv_prc'];
			$sum['pay_prc'] += $data['dlv_prc'];
			$sum['account_prc'] += $data['dlv_prc'];
			foreach($col_list as $key => $val) {
				$row[] = (!empty($exceptionColType[$key]) && $exceptionColType[$key] === 'price' && $data2[$key] != 0) ? parsePrice($data2[$key]) : $data2[$key];
			}
			 $ExcelWriter->writeSheetRow($row, $row_style);
			 unset($row);
		} 
        $data['stat'] = $_order_stat[$data['stat']];
		$data['fee_rate'] = $data['fee_rate']."%";
		$data['cpn_rate'] = ($data['cpn_fee'] > 0) ? floor(($data['cpn_fee']/$data['sale5'])*100).'%' : '0%';
		$data['name'] = trim(stripslashes(strip_tags($data['name'])));
		$data['pay_prc'] = $data['total_prc']-$data['sale_prc']-$data['sale5'];
		$data['account_prc'] = $data['total_prc']-$data['cpn_fee']-$data['fee_prc'];

		$ono = $data['ono'];

		$sum['buy_ea'] += $data['buy_ea'];
		$sum['total_prc'] += $data['total_prc'];
		$sum['fee_prc'] += $data['fee_prc'];
		$sum['cpn_fee'] += $data['cpn_fee'];
		$sum['pay_prc'] += $data['pay_prc'];
		$sum['sale_prc'] += $data['sale_prc'];
		$sum['sale5'] += ($data['sale5']);
		$sum['account_prc'] += $data['account_prc'];
		$sum['dlv_prc'] += $data['dlv_prc'];


        foreach($col_list as $key => $val) {
            $row[] = (!empty($exceptionColType[$key]) && $exceptionColType[$key] === 'price' && $data[$key] == "se") ? parsePrice($data[$key]) : $data[$key];
        }
        $ExcelWriter->writeSheetRow($row, $row_style);
        unset($row);
	}
	$sum['ono'] = "합계"; 
	foreach ($col_list as $key => $val) {
		$row[] = (!empty($exceptionColType[$key]) && $exceptionColType[$key] === 'price' && $key != "sell_prc") ? parsePrice($sum[$key]) : $sum[$key];
     }
     $ExcelWriter->writeSheetRow($row, $row_style);

    $ExcelWriter->writeFile();
