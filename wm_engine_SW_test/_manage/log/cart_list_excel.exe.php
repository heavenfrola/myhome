<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  장바구니 엑셀저장 처리
	' +----------------------------------------------------------------------------------------------+*/
    $ExcelWriter = setExcelWriter();
    $headerType = array();
    $headerStyle = array(
        'fill' => '#f2f2f2',
        'font-style' => 'bold',
        'widths' => array()
    );
    $widths = array(
        'idx' => 10,
        'name' => 30,
        'option' => 30,
        'sell_prc_str' => 30,
        'member_name' => 30,
        'member_id' => 30,
        'email' => 30,
        'cell' => 30,
        'reg_date' => 30
    );
    $col_list = array();
    $col_list['idx'] = '번호';
    $col_list['name'] = '상품명';
    $col_list['option'] = '옵션';
    $col_list['sell_prc'] = '상품가격';
    $col_list['member_name'] = '회원명';
    $col_list['member_id'] = '회원아이디';
    $col_list['email'] = '이메일';
    $col_list['cell'] = '휴대폰';
    $col_list['reg_date'] = '등록일';

    $exceptionColType = array(
        'sell_prc' => 'price'
    );

    foreach ($col_list as $key => $val) {
        $headerType[$val] = (!empty($exceptionColType[$key])) ? $exceptionColType[$key] : 'string';
        $headerStyle['widths'][] = (!empty($widths[$key])) ? $widths[$key] : 20;
    }

	if($_GET['search_on']) {
		include_once $engine_dir.'/_manage/log/cart_list.php';
	} else {
		include_once $engine_dir.'/_manage/member/member_view_cart.inc.php';
	}

    $file_name = '장바구니통계';
    $ExcelWriter = setExcelWriter();
    $ExcelWriter->setFileName($file_name);
    $ExcelWriter->setSheetName($file_name);
    $ExcelWriter->writeSheetHeader($headerType, $headerStyle);
    
    $idx = 1;
    
    while ($data = cartListview(3, 50, 50)) {
        $data['idx'] = $idx++;
        $data['name'] = trim(stripslashes(strip_tags($data['name'])));
        $data['option'] = trim(stripslashes(strip_tags($data['option'])));
        $data['member_name'] = trim(stripslashes(strip_tags($data['member_name'])));
        $data['member_id'] = trim(stripslashes(strip_tags($data['member_id'])));
        $data['email'] = trim(stripslashes(strip_tags($data['email'])));
        $data['cell'] = trim(stripslashes(strip_tags($data['cell'])));
        $data['reg_date'] = date('Y-m-d H:i:s', $data['reg_date']);
        $row = array();
        foreach ($col_list as $key => $val) {
            $row[] = (!empty($exceptionColType[$key]) && $exceptionColType[$key] === 'price') ? parsePrice($data[$key]) : $data[$key];
        }
        $ExcelWriter->writeSheetRow($row);
        unset($row);
    }
    $ExcelWriter->writeFile();
