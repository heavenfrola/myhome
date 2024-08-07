<?PHP

	include_once $engine_dir.'/_engine/include/wingPos.lib.php';
	include_once $engine_dir.'/_manage/erp/order_list_search.inc.php';

	$field_list = array(
		'order_dtl_no' => '발주상세번호',
		'arcade' => '상가',
		'floor' => '층',
		'plocation' => '위치',
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
		'sell_prc' => '판매가격',
		'curr_stock' => '현재고',
		'order_qty' => '발주수량',
		'in_qty' => '기입고수량',
		'qty' => '입고수량',
		'order_price' => '발주단가',
		'remark' => '비고',
		'space' => '공란',
		'order_total' => '발주금액',
	);

	$_set['default'] = array('order_dtl_no', 'arcade', 'floor', 'provider', 'name', 'origin_name', 'category', 'barcode', 'complex_option_name', 'order_qty', 'in_qty', 'order_price', 'remark', 'order_total');
	$_set['in'] = array('order_dtl_no', 'arcade', 'floor', 'provider', 'name', 'origin_name', 'barcode', 'complex_option_name', 'order_qty', 'in_qty', 'qty', 'order_price', 'remark', 'order_total', 'curr_stock');

	$set = $_GET['set'];
	$ono = addslashes($_GET['ono']);
	if(!$set) {
		if($cfg['erp_order_excel']) {

			eval("\$_set[config] = array($cfg[erp_order_excel]);");
			$set = 'config';
		} else {
			$set = 'default';
		}
	}
	$field = $_set[$set];
	if($excel_config) return;



	if($set == 'in') $w .= " and o.order_stat in (1,2) having order_qty > in_qty";
	if($order_dtl_no) $w .= " and a.order_dtl_no in($order_dtl_no)";
	if($ono) $w .= " and o.order_no='$ono'";
	$orderby = $set == 'in' ? 'order by a.order_dtl_no asc' : 'order by arcade, floor, provider';

	$list_sql = "select a.order_dtl_no, b.arcade, b.floor, b.plocation, b.provider, b.pceo, b.ptel, b.pcell, b.account1_bank, b.account1, b.account1_name, b.account2_bank, b.account2, b.account2_name, c.barcode ".
				"      , p.name, p.sell_prc, p.origin_name, p.origin_prc, a.order_qty, a.order_price, a.remark, a.order_no" .
				"      ,(select name from $tbl[category] x where p.big = x.no) as big" .
				"      ,(select name from $tbl[category] x where p.mid = x.no) as mid" .
				"      ,(select name from $tbl[category] x where p.small = x.no) as small" .
				"      ,ifnull((select sum(qty) from erp_inout x where inout_kind = 'I' and x.order_dtl_no = a.order_dtl_no and x.complex_no = a.complex_no),0) as in_qty".
				" ,opts, c.complex_no".
				"  from erp_order_dtl a left join erp_order o using(order_no)" .
				"	   left join $tbl[provider] b on a.sno = b.no" .
				"      inner join erp_complex_option c on a.complex_no = c.complex_no" .
				"      inner join $tbl[product] p on c.pno = p.no" .
				" where 1 $w " .
			   $h.$orderby;

	$res = $pdo->iterator($list_sql);

	$use_dexcel_curr_stock = false;

    $headerStyle = array(
        'fill' => '#f2f2f2',
        'font-style' => 'bold',
        'halign' => 'center',
        'widths' => array()
    );
    $widths = array(
        'provider' => 30,
        'name' => 50,
        'origin_name' => 50
    );
    $exceptionColType = array(
        'order_price' => 'price',
        'order_total' => 'price'
    );
    $ExcelWriter = setExcelWriter();
	foreach($field as $val){
		if($val == 'curr_stock') $use_dexcel_curr_stock = true;
        $field_text = $field_list[$val];
        $field_text .= $ExcelWriter->duplicateField($field, $val);
        $headerType[$field_text] = (!empty($exceptionColType[$val])) ? $exceptionColType[$val] : 'string';
        $headerStyle['widths'][] = (!empty($widths[$val])) ? $widths[$val] : 20;
	}
	if($set == 'in') $file_name = '입고처리용_발주목록';
	else $file_name = '일괄발주목록';	

	$ExcelWriter->setFileName($file_name);
	$ExcelWriter->setSheetName($file_name);
	$ExcelWriter->writeSheetHeader($headerType, $headerStyle);
    unset($headerStyle);

    foreach ($res as $data) {
		$data['category'] = $data['big'];
		if($data['mid']) $data['category'] .= $cate_sprit.$data['mid'];
		if($data['small']) $data['category'] .= $cate_sprit.$data['small'];
		$total_amt = number_format($data['order_price']*$data['order_qty']);

		$data['name']			= stripslashes(strip_tags($data['name']));
		$data['origin_name']	= stripslashes(strip_tags($data['origin_name']));
		$data['order_qty']		= number_format($data['order_qty']);
		$data['in_qty']			= number_format($data['in_qty']);
		$data['order_price']	= $data['order_price'] >0 ? $data['order_price'] : $data['origin_prc'];
		$data['remark']			= stripslashes($data['remark']);
		$data['account1_nm']	= $bank_codes[$data['account1_bank']];
		$data['account2_nm']	= $bank_codes[$data['account2_bank']];
		$data['account1_name']	= stripslashes($data['account1_name']);
		$data['account2_name']	= stripslashes($data['account2_name']);
		$data['account1_full']	= $data['account1_nm'].' '.$data['account1'].' '.$data['account1_name'];
		$data['account2_full']	= $data['account2_nm'].' '.$data['account2'].' '.$data['account2_name'];
		$data['order_total']	= parsePrice($data['order_price'] * $data['order_qty']);
		$data['order_price']	= parsePrice($data['order_price']);
		$data['provider']		= stripslashes($data['provider']);
		$data['complex_option_name'] = getComplexOptionName($data['opts']);
		if($use_dexcel_curr_stock) $data['curr_stock'] = $pdo->row("select curr_stock($data[complex_no])");

		$data['qty']			= 0;

        $row = array();
		foreach($field as $val){
            $row[] = $data[$val];
		}
		$ExcelWriter->writeSheetRow($row);
        unset($row);
	}

	$ExcelWriter->writeFile();
