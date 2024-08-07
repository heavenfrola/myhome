<?PHP

	include_once $engine_dir.'/_engine/include/wingPos.lib.php';
	include_once $engine_dir."/_manage/erp/stock_adjust_list_search.inc.php";

	$_cname_cache = getCategoriesCache(1);

	$res = $pdo->iterator($list_sql);

    $headerStyle = array(
        'fill' => '#f2f2f2',
        'font-style' => 'bold',
        'widths' => array(
            20, 50, 20, 20, 30,
            10, 10, 50, 20
        )
    );
    $headerType = array(
        '조정일자' => 'string',
        '상품명' => 'string',
        '카테고리' => 'string',
        '바코드' => 'string',
        '옵션명' => 'string',
        '조정전재고' => 'string',
        '차이' => 'string',
        '조정사유' => 'string',
        '처리자' => 'string'
    );
    $file_name = '재고조정 내역';
    $ExcelWriter = setExcelWriter();
    $ExcelWriter->setFileName($file_name);
    $ExcelWriter->setSheetName($file_name);
    $ExcelWriter->writeSheetHeader($headerType, $headerStyle);

    $cate_sprit = ' > ';

    foreach ($res as $data) {
		$data = array_map('stripslashes', $data);
		$category_name = $_cname_cache[$data['big']];
		if($data['mid']) $category_name .= $cate_sprit.$_cname_cache[$data['mid']];
		if($data['small']) $category_name .= $cate_sprit.$_cname_cache[$data['small']];

		$data['qty'] = ($data['inout_kind'] == 'U' || $data['inout_kind'] == 'I') ? '+'.$data['qty'] : '-'.$data['qty'];
        $data['prev_qty'] = (!empty($data['prev_qty'])) ?  $data['prev_qty'] : 0; //prev_qty empty 체크
		$data['stock_qty'] = $data['prev_qty']+$data['qty'];

        $row = array(
            $data['reg_date'],
            $data['name'],
            $category_name,
            $data['barcode'],
            getComplexOptionName($data['opts']),
            $data['prev_qty'],
            $data['qty'],
            $data['remark'],
            $data['reg_user']
        );
        $ExcelWriter->writeSheetRow($row);
        unset($row);
	}

    $ExcelWriter->writeFile();
