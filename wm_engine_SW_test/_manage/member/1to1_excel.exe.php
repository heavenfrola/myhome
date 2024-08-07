<?PHP
	$no_qcheck = true;
	ini_set('memory_limit', '-1');
	set_time_limit(0);

	require '1to1.php';

    $row_style = array();
    $headerType = array();
    $headerStyle = array(
        'fill' => '#f2f2f2',
        'font-style' => 'bold',
        'halign' => 'center',
        'widths' => array()
    );
    $widths = array(
        'title' => 50,
        'content' => 50,
        'reply' => 50,
        'mng_memo' => 50
    );
	$csv_fd = array(
		'cate' => '분류',
		'ono' => '주문번호',
		'title' => '제목',
		'content' => '질문내용',
		'reply' => '답변내용',
		'mng_memo' => '관리자메모',
		'name' => '작성자명',
		'member_id' => '작성자아이디',
		'reg_date' => '등록일시',
		'reply_date' => '답변일시',
		'reply_id' => '답변자'
	);

	foreach($csv_fd as $key => $val){
        $headerType[$val] = 'string';
        $headerStyle['widths'][] = (!empty($widths[$key])) ? $widths[$key] : 50;
	}

    $file_name = '1대1 문의';
    $ExcelWriter = setExcelWriter();
    $ExcelWriter->setFileName($file_name);
    $ExcelWriter->setSheetName($file_name);
    $ExcelWriter->writeSheetHeader($headerType, $headerStyle);

	$res = $pdo->iterator($sql);

    foreach ($res as $idx => $data) {
		$data['cate'] = $_cust_cate[$data['cate1']][$data['cate2']];
		if($data['pno'] > 0) $data['pname'] = stripslashes($pdo->row("select name from $tbl[product] where no='$data[pno]'"));
		$data['reg_date'] = date('Y-m-d H:i:s', $data['reg_date']);
		$data['reply_date'] = ($data['reply_date'] > 0) ? date('Y-m-d H:i:s', $data['reply_date']) : '';
        $row = array();
		foreach($csv_fd as $key => $val) {
            $row[] = $data[$key];
		}
        $ExcelWriter->writeSheetRow($row);
        unset($row);

        // 엑셀 다운로드 기록
        if ($idx == 0) {
            addPrivacyViewLog(array(
                'page_id' => '1to1',
                'page_type' => 'excel',
                'target_id' => $data['member_id'],
                'target_cnt' => $NumTotalRec
            ));
        }
	}

    $ExcelWriter->writeFile();
