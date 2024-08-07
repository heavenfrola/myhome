<?PHP

    $headerType = array();
    $headerStyle = array(
        'fill' => '#f2f2f2',
        'font-style' => 'bold',
        'widths' => array()
    );
    $widths = array();

	include_once $engine_dir.'/_engine/include/wingPos.lib.php';
	include_once $engine_dir."/_manage/erp/in_list_search.inc.php";

	$_cname_cache = getCategoriesCache(1);

	$field_list = array(
		'reg_date' => '입고일자',
		'arcade' => '상가',
		'floor' => '층',
		'location' => '위치',
		'provider' => '사입처',
		'pceo' => '대표자',
		'ptel' => '전화번호',
		'pcell' => '휴대번호',
		'account1_full' => '계좌번호1',
		'account1_nm' => '계좌번호1(은행명)',
		'account1_bank' => '계좌번호1(은행코드)',
		'account1' => '계좌번호1(번호)',
		'account1_name' => '계좌번호1(예금주)',
		'account2_full' => '계좌번호2',
		'account2_nm' => '계좌번호2(은행명)',
		'account2_bank' => '계좌번호2(은행코드)',
		'account2' => '계좌번호2(번호)',
		'account2_name' => '계좌번호2(예금주)',
		'name' => '상품명',
		'origin_name' => '장기명',
		'category' => '카테고리',
		'barcode' => '바코드',
		'complex_option_name' => '옵션',
		'qty' => '입고수량',
		'in_price' => '입고단가',
		'order_price' => '입고금액',
		'space' => '공란',
	);
    $exceptionColType = array(
        'in_price' => 'price',
        'order_price' => 'price'
    );


	$_set['default'] = array('reg_date', 'arcade', 'floor', 'provider', 'name', 'origin_name','category', 'barcode', 'complex_option_name', 'qty', 'in_price', 'order_price');
	if(!$set) {
		if($cfg['erp_input_excel']) {
			eval("\$_set[config] = array($cfg[erp_input_excel]);");
			$set = 'config';
		} else {
			$set = 'default';
		}
	}

	$field = $_set[$set];
	if($excel_config) return;

	$res = $pdo->iterator($list_sql);

	$cate_sprit = ' < ';
	$file_name = '입고내역';
    $ExcelWriter = setExcelWriter();
    foreach ($field as $key => $val) {
        $field_text = $field_list[$val];
        $field_text .= $ExcelWriter->duplicateField($field, $val);
        $headerType[$field_text] = (!empty($exceptionColType[$val])) ? $exceptionColType[$val] : 'string';
        $headerStyle['widths'][] = (!empty($widths[$key])) ? $widths[$key] : 20;
    }

    $ExcelWriter->setFileName($file_name);
    $ExcelWriter->setSheetName($file_name);
    $ExcelWriter->writeSheetHeader($headerType, $headerStyle);

    foreach ($res as $data) {
		$data['order_price'] = $data['qty']*$data['in_price'];

		$data['category'] = $_cname_cache[$data['big']];
		if($prd['mid']) $data['category'] .= $cate_sprit.$_cname_cache[$data['mid']];
		if($prd['small']) $data['category'] .= $cate_sprit.$_cname_cache[$data['small']];

		$data['account1_nm'] = $bank_codes[$data['account1_bank']];
		$data['account2_nm'] = $bank_codes[$data['account2_bank']];
		$data['account1_name'] = stripslashes($data['account1_name']);
		$data['account2_name'] = stripslashes($data['account2_name']);
		$data['account1_full'] = $data['account1_nm'].' '.$data['account1'].' '.$data['account1_name'];
		$data['account2_full'] = $data['account2_nm'].' '.$data['account2'].' '.$data['account2_name'];
		$data['complex_option_name'] = getComplexOptionName($data['opts']);
        $row = array();
        foreach ($field as $val) {
            $row[] = (!empty($exceptionColType[$val]) && $exceptionColType[$val] === 'price') ? parsePrice($data[$val]) : $data[$val];
        }
        $ExcelWriter->writeSheetRow($row);
        unset($row);
    }

    $ExcelWriter->writeFile();

