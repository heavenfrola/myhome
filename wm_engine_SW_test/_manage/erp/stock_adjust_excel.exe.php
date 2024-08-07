<?PHP

	include_once $engine_dir.'/_engine/include/wingPos.lib.php';
	include_once $engine_dir."/_manage/erp/stock_adjust_search.inc.php";

	$_cname_cache = getCategoriesCache(1);

	$res = $pdo->iterator($list_sql);
    $headerType = array();
    $headerStyle = array(
        'fill' => '#f2f2f2',
        'font-style' => 'bold',
        'widths' => array()
    );
    $widths = array(
        'name' => 40,
        'adjust_reason' => 40,
        'origin_name' => 40,
        'force_soldout' => 40
    );
    $contentStyle = array(
        'barcode' => array('fill' => '#ffffcc'),
        'force_soldout' => array('fill'=>'#ffffcc','halign'=>'center'),
        'current_qty' => array('halign'=>'right'),
        'adjust_qty' => array('fill'=>'#ffffcc','halign'=>'right'),
        'adjust_reason' => array('fill'=>'#ffffcc', 'halign'=>'left')

    );
    $col_list = array(
        'complex_no' => '재고번호',
        'name' => '상품명',
        'category_name' => '카테고리',
        'opts' => '옵션',
        'barcode' => '바코드',
        'force_soldout' => '품절방식(무제한/한정/강제품절)',
        'current_qty' => '현재고',
        'adjust_qty' => '조정재고',
        'adjust_reason' => '조정사유',
        'code' => '상품코드',
        'origin_name' => '장기명',
        'provider' => '사입처',
        'arcade' => '상가',
        'plocation' => '위치',
        'floor' => '층',
        'ptel' => '전화번호',
        'storage_name' => '창고'
    );
    $row_style = array();
    foreach ($col_list as $key => $val) {
        $headerType[$val] = 'string'; //해더데이터 형 선언 (key는 필드텍스트)
        $headerStyle['widths'][] = (!empty($widths[$key])) ? $widths[$key] : 20;
        $row_style[] = (!empty($contentStyle[$key])) ? $contentStyle[$key] : array('halign' => 'center');
    }

    $file_name = '재고조정';
    $ExcelWriter = setExcelWriter();
    $ExcelWriter->setFileName($file_name);
    $ExcelWriter->setSheetName($file_name);
    $ExcelWriter->writeSheetHeader($headerType, $headerStyle);

    $cate_sprit = ' > ';

    foreach ($res as $data) {
		$category_name = $_cname_cache[$data['big']];
		if($data['mid']) $category_name .= $cate_sprit.$_cname_cache[$data['mid']];
		if($data['small']) $category_name .= $cate_sprit.$_cname_cache[$data['small']];
		$data['name'] = stripslashes($data['name']);
		$data['origin_name'] = stripslashes($data['origin_name']);
        $data['storage_name'] = '';
		if($data['storage_no'] > 0) {
			$data['storage_name'] = $pdo->row("select name from $tbl[erp_storage] where no='$data[storage_no]'");
            if (!empty($data['storage'])) {
                $data['storage_name'] = stripslashes($data['storage_name']);
            }
		}
        $data['category_name'] = $category_name;
        $data['opts'] = getComplexOptionName($data['opts']);
        $data['force_soldout'] = $_erp_force_stat[$data['force_soldout']];
        $data['adjust_qty'] = '0';
        $data['adjust_reason'] = '';
        foreach ($col_list as $key => $val) {
            $row[] = $data[$key];
        }
        $ExcelWriter->writeSheetRow($row, $row_style);
        unset($row);
	}

    $ExcelWriter->writeFile();
