<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  주문상품 수량옵션 통계 엑셀출력
	' +----------------------------------------------------------------------------------------------+*/

	$field_list = array(
		'provider' => '사입처',
		'arcade' => '상가',
		'floor' => '층',
		'plocation' => '위치',
		'name' => '상품명',
		'origin_name' => '장기명',
		'category' => '분류',
		'option' => '옵션',
		'buy_ea' => '주문수량',
		'sell_prc' => '상품단가(현재가격)',
		'origin_prc' => '사입원가',
		'stock' => '옵션별재고',
		'account1' => '계좌1',
		'account2' => '계좌2',
		'ptel' => '사입처전화번호',
		'pcel' => '사입처휴대번호',
	);
    $exceptionColType = array(
        'sell_prc' => 'price'
    );
    $headerStyle = array(
        'fill' => '#f2f2f2',
        'font-style' => 'bold',
        'halign' => 'center',
        'widths' => array()
    );
    $widths = array();
	$default = array('provider', 'arcade', 'floor', 'plocation', 'name', 'origin_name', 'category', 'option', 'buy_ea', 'sell_prc', 'stock');
	if(!$cfg['order_product_config']) $field = $default;
	else $field = explode(',', $cfg['order_product_config']);
	if($excel_config) return;

	include_once $engine_dir.'/_manage/order/order_product.php';

    $headerType = array();
    foreach($field as $val){
		$headerType[$field_list[$val]] = (!empty($exceptionColType[$key])) ? $exceptionColType[$key] : 'string';
		$headerStyle['widths'][] = (!empty($widths[$field_list[$val]])) ? $widths[$field_list[$val]] : 20;
    }
    $file_name = '주문상품수량통계';
    $ExcelWriter = setExcelWriter();
    $ExcelWriter->setFileName($file_name);
    $ExcelWriter->setSheetName($file_name);
    $ExcelWriter->writeSheetHeader($headerType, $headerStyle);
    foreach ($res as $data) {
		$data['provider']	= strips($data['provider']);
		$data['arcade']		= strips($data['arcade']);
		$data['floor']		= strips($data['floor']);
		$data['plocation']	= strips($data['plocation']);
		$data['name']		= strips($data['name']);
		$data['origin_name']= strips($data['origin_name']);
		$data['stock']		= getOptionStock($data);
		$data['option']		= parseOrderOption($data['option']);
		$data['category']	= strips($data['big']);
		$data['sell_prc']	= parsePrice($data['sell_prc']);
		$data['account1']	= strips($data['account1']);
		$data['account2']	= strips($data['account2']);
		if($data['mid']) $data['category'] .= ' > '.strips($data['mid']);
		if($data['small']) $data['category'] .= ' > '.strips($data['small']);
        $row = array();
        foreach($field as $val){
            $row[] = $data[$val];
        }
        $ExcelWriter->writeSheetRow($row);
        unset($row);
    }

    $ExcelWriter->writeFile();
