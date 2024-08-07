<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  상품별매출 엑셀저장 처리
	' +----------------------------------------------------------------------------------------------+*/
	$income = ($sadmin == "Y") ? '/_partner/income' : '/_manage/income';

	include_once $engine_dir.$income.'/income_product.php';

    $headerType = array();
    $headerStyle = array(
        'fill' => '#f2f2f2',
        'font-style' => 'bold',
        'halign' => 'center',
        'widths' => array()
    );
    $col_list = array(
        'idx' => '순번',
        'stat' => '상품상태',
        'name' => '상품명',
        'seller' => '사입처',
        'origin_name' => '장기명',
        'normal_prc' => $cfg['product_normal_price_name'],
        'sell_prc' => $cfg['product_sell_price_name'],
        'origin_prc' => '사입원가',
        'hit_view' => '조회',
        'amount' => '주문',
        'price' => '실판매가',
        'reg_date' => '등록일'
    );
    $contentStyle = array(
        'hit_view' => array('halign'=>'right'),
        'amount' => array('halign'=>'right'),
        'price' => array('halign'=>'right'),
        'normal_prc' => array('halign'=>'right'),
        'origin_prc' => array('halign'=>'right'),
        'sell_prc' => array('halign'=>'right')
    );
    $row_style = array();
    $exceptionColType = array(
        'normal_prc' => 'price',
        'sell_prc' => 'price',
        'origin_prc' => 'price',
        'price' => 'price'
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

	$idx = 0;
    foreach ($res as $data) {
        $row = array();
		$idx++;
        $data['idx'] = $idx;
        $data['stat'] = $_prd_stat[$data['stat']];
		$data['name'] = trim(stripslashes(strip_tags($data['name'])));
		$data['seller'] = trim(stripslashes(strip_tags($data['seller'])));
		$data['origin_name'] = trim(stripslashes(strip_tags($data['origin_name'])));
		$data['hit_view'] = number_format($data['hit_view']);
        $data['amount'] = number_format($data['amount']);
		$data['reg_date'] = date('Y-m-d H:i:s', $data['reg_date']);
        foreach($col_list as $key => $val) {
            $row[] = (!empty($exceptionColType[$key]) && $exceptionColType[$key] === 'price') ? parsePrice($data[$key]) : $data[$key];
        }
        $ExcelWriter->writeSheetRow($row, $row_style);
        unset($row);
	}

    $ExcelWriter->writeFile();
