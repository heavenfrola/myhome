<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  위시리스트 엑셀저장 처리
	' +----------------------------------------------------------------------------------------------+*/

    $headerType = array();
    $headerStyle = array(
        'fill' => '#f2f2f2',
        'font-style' => 'bold',
        'widths' => array()
    );

	if($_GET['search_on']) {
		include_once $engine_dir.'/_manage/log/wish_list.php';
	} else {
		include_once $engine_dir.'/_manage/member/member_view_wishlist.inc.php';
	}

    $col_list = array();
    $col_list['idx'] = '번호';
    $col_list['name'] = '상품명';
    $col_list['sell_prc_str'] = '상품가격';
    $col_list['member_name'] = '회원명';
    $col_list['member_id'] = '회원아이디';
    $col_list['email'] = '이메일';
    $col_list['cell'] = '휴대폰';
    $col_list['reg_date'] = '등록일';

    foreach ($col_list as $key => $val) {
        $headerType[$val] = 'string';
        $headerStyle['widths'][] = 30;
    }
    $file_name = '위시리스트통계';
    $ExcelWriter = setExcelWriter();
    $ExcelWriter->setFileName($file_name);
    $ExcelWriter->setSheetName($file_name);
    $ExcelWriter->writeSheetHeader($headerType, $headerStyle);

    $idx = 1;

    while ($data = wishList(3, 50, 50)) {
        $data['idx'] = $idx++;
        $data['name'] = trim(stripslashes(strip_tags($data['name'])));
        $data['member_name'] = trim(stripslashes(strip_tags($data['member_name'])));
        $data['member_id'] = trim(stripslashes(strip_tags($data['member_id'])));
        $data['email'] = trim(stripslashes(strip_tags($data['email'])));
        $data['cell'] = trim(stripslashes(strip_tags($data['cell'])));
        $data['reg_date'] = date('Y-m-d H:i:s', $data['reg_date']);
        $row = array();
        foreach ($col_list as $key => $val) {
            $row[] = $data[$key];
        }
        $ExcelWriter->writeSheetRow($row);
        unset($row);
    }

    $ExcelWriter->writeFile();
