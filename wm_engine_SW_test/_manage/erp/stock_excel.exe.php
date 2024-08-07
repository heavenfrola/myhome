<?PHP

	include_once $engine_dir.'/_engine/include/wingPos.lib.php';
	include_once $engine_dir."/_manage/erp/stock_search.inc.php";

	$res = $pdo->iterator($list_sql);

	$_cname_cache = getCategoriesCache(1);

    $headerStyle = array(
        'fill' => '#f2f2f2',
        'font-style' => 'bold',
        'widths' => array(
            50, 30, 20, 30, 10,
            10, 10, 10, 10, 30,
            20, 20, 20, 20, 20,
            20
        )
    );
    $headerType = array(
        '상품명' => 'string',
        '카테고리' => 'string',
        '바코드' => 'string',
        '옵션명' => 'string',
        '전일재고' => 'string',
        '입고' => 'string',
        '출고' => 'string',
        '현재고' => 'string',
        '원가' => 'price',
        '장기명' => 'string',
        '사입처' => 'string',
        '상가' => 'string',
        '위치' => 'string',
        '층' => 'string',
        '전화번호' => 'string',
        '창고' => 'string'
    );
    $file_name = '실시간재고 조회';
    $ExcelWriter = setExcelWriter();
    $ExcelWriter->setFileName($file_name);
    $ExcelWriter->setSheetName($file_name);
    $ExcelWriter->writeSheetHeader($headerType, $headerStyle);

    foreach ($res as $data) {
		$category_name = $_cname_cache[$data['big']];
		if($data['mid']) $category_name .= ' > '.$_cname_cache[$data['mid']];
		if($data['small']) $category_name .= ' > '.$_cname_cache[$data['small']];
		$data['in_qty'] = $pdo->row("select sum(qty) from erp_inout where complex_no='$data[complex_no]' and reg_date >= '$_todate' and inout_kind in ('I', 'U')");
		$data['in_qty'] = $data['in_qty'];

		if($data['storage_no'] > 0) {
			$data['storage_name'] = $pdo->row("select name from $tbl[erp_storage] where no='$data[storage_no]'");
			$data['storage_name'] = stripslashes($data['storage_name']);
		}

        $row = array(
            stripslashes($data['name']),
            $category_name,
            $data['barcode'],
            getComplexOptionName($data['opts']),
            $data['prev_qty'],
            $data['in_qty'],
            $data['out_qty'],
            ($data['prev_qty'] + $data['in_qty'] - $data['out_qty']),
            parsePrice($data['origin_prc']),
            stripslashes($data['origin_name']),
            $data['provider'],
            $data['arcade'],
            $data['plocation'],
            $data['floor'],
            $data['ptel'],
            $data['storage_name']
        );
        $ExcelWriter->writeSheetRow($row);
        unset($row);
	}

    $ExcelWriter->writeFile();
