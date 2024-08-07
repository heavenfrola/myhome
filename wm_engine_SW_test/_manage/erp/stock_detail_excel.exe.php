<?PHP

	$pno = numberOnly($_GET['pno']);

	$sql = "select reg_date, inout_kind, qty, (select name from `$tbl[mng]` b where a.reg_user = b.admin_id) as reg_user, remark, curr_stock(complex_no) as cqty" .
		   "  from erp_inout a" .
		   " where a.complex_no = {$pno}" .
		   " order by reg_date desc";

	$res = $pdo->iterator($sql);

    $headerStyle = array(
        'fill' => '#f2f2f2',
        'font-style' => 'bold',
        'widths' => array(
            20, 10, 10, 10, 20, 50
        )
    );
    $headerType = array(
        '처리일시' => 'string',
        '입·출고' => 'string',
        '처리수량' => 'string',
        '누적재고' => 'string',
        '변경자' => 'string',
        '비고' => 'string',
    );
    $file_name = '상세 재고조정 내역';
    $ExcelWriter = setExcelWriter();
    $ExcelWriter->setFileName($file_name);
    $ExcelWriter->setSheetName($file_name);
    $ExcelWriter->writeSheetHeader($headerType, $headerStyle);

    foreach ($res as $data) {
		if($data['inout_kind'] == 'O' || $data['inout_kind'] == 'P') $data['qty'] *= -1;
		$data['reg_date'] = ($data['reg_date'] == '1900-01-01 00:00:00') ? '기초재고 등록' : $data['reg_date'];
		$data['inout_kind'] = ($data['inout_kind'] == 'I') ? '입고' : '출고';

        $row = array(
            $data['reg_date'],
            $data['inout_kind'],
            $data['qty'],
            $data['cqty'],
            $data['reg_user'],
            $data['remark'],
        );
        $ExcelWriter->writeSheetRow($row);
        unset($row);
	}

    $ExcelWriter->writeFile();
