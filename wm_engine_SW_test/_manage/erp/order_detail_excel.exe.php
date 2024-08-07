<?PHP

	include_once $engine_dir.'/_engine/include/wingPos.lib.php';

	$_cname_cache = getCategoriesCache(1);

	$pdo->iterator("select a.no, a.hash, a.name, a.updir, a.upfile3, a.w3, a.h3, a.prd_type, a.wm_sc, a.origin_name, c.order_dtl_no, c.sno, d.arcade, d.floor, d.provider, a.big, a.mid, a.small" .
		   "     , b.complex_no, b.barcode, opts, c.order_target_qty, c.order_qty, c.order_price, c.remark" .
		   "  from wm_product a, erp_complex_option b, erp_order_dtl c left join wm_provider d on c.sno = d.no" .
		   " where a.stat in ('2','3','4') and a.no = b.pno" .
		   "   and b.complex_no = c.complex_no and b.del_yn = 'N'" .
		   "   and c.order_no = '{$ono}'" .
		   " order by c.order_dtl_no");
    $headerType = array();
    $headerStyle = array(
        'fill' => '#f2f2f2',
        'font-style' => 'bold',
        'halign'    => 'center',
        'widths' => array()
    );
    $widths = array();
    $contentStyle= array(
        'order_qty' => array('halign' => 'right'),
        'in_qty' => array('halign'=>'right'),
        'order_price' => array('halign'=>'right'),
        'tot_price' => array('halign'=>'right'),
    );
    $field_list = array();
    $field_list['order_dtl_no'] = '발주상세번호';
    $field_list['arcade'] = '상가';
    $field_list['floor'] = '층';
    $field_list['provider'] = '사입처';
    $field_list['name'] = '상품명';
    $field_list['category_name'] = '카테고리';
    $field_list['barcode'] = '바코드';
    $field_list['opts'] = '옵션';
    $field_list['order_qty'] = '발주수량';
    $field_list['blank'] = '';
    $field_list['in_qty'] = '입고수량';
    $field_list['order_price'] = '발주단가';
    $field_list['remark'] = '비고';
    $field_list['tot_price'] = '발주금액';
    $exceptionColType = array(
        'order_price' => 'price',
        'tot_price' => 'price'
    );
    foreach ($field_list as $key => $val) {
        $headerType[$val] = (!empty($exceptionColType[$key])) ? $exceptionColType[$key] : 'string';
        $headerStyle['widths'][] = (!empty($widths[$key])) ? $widths[$key] : 25;
        $row_style[] = (!empty($contentStyle[$key])) ? $contentStyle[$key] : array('halign' => 'center');
    }
    $file_name = '발주상세';
    $ExcelWriter = setExcelWriter();
    $ExcelWriter->setFileName($file_name);
    $ExcelWriter->setSheetName($file_name);
    $ExcelWriter->writeSheetHeader($headerType, $headerStyle);

    foreach ($res as $data) {
		$category_name = $_cname_cache[$data['big']];
		if($data['mid']) $category_name .= $cate_sprit.$_cname_cache[$data['mid']];
		if($data['small']) $category_name .= $cate_sprit.$_cname_cache[$data['small']];
        $row = array();
        foreach ($field_list as $key => $val) {
            $data['opts'] = getComplexOptionName($data['opts']);
            $data['order_qty'] = number_format($data['order_qty']);
            $data['in_qty'] = number_format($data['in_qty']);
            $data['order_price'] = parsePrice($data['order_price']);
            $data['category_name'] = $category_name;
            $data['tot_price'] = parsePrice($data['order_qty']*$data['order_price']);
            $row[] = $data[$key];
        }
        $ExcelWriter->writeSheetRow($row, $row_style);
        unset($row);
	}
    $ExcelWriter->writeFile();
