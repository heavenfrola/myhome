<?PHP

	$no_qcheck = true;
	ini_set('memory_limit', '-1');
	set_time_limit(0);

	require 'product_qna.php';

	$csv_fd = array(
		'cate' => array('분류', 100),
		'pname' => array('상품명', 200),
		'title' => array('제목', 200),
		'content' => array('질문내용', 200),
		'answer' => array('답변내용', 200),
		'mng_memo' => array('관리자메모', 200),
		'name' => array('작성자명', 100),
		'member_id' => array('작성자아이디', 100),
		'reg_date' => array('등록일시', 100),
		'answer_date' => array('답변일시', 100),
		'answer_id' => array('답변자', 100),
	);

    $headerStyle = array(
        'fill' => '#f2f2f2',
        'font-style' => 'bold',
        'halign' => 'center',
        'widths' => array()
    );
    $widths = array(
        'pname' => 30,
        'title' => 50,
        'content' => 50,
        'answer' => 50,
        'mng_memo' => 50,
        'member_id' => 30,
    );

	foreach($csv_fd as $key => $val){
        $headerType[$val[0]] = 'string';
        $headerStyle['widths'][] = (!empty($widths[$key])) ? $widths[$key] : 20;
	}
    $file_name = '상품문의';
    $ExcelWriter = setExcelWriter();
    $ExcelWriter->setFileName($file_name);
    $ExcelWriter->setSheetName($file_name);
    $ExcelWriter->writeSheetHeader($headerType, $headerStyle);
	$col = 1;
	$res = $pdo->iterator($sql);
    foreach ($res as $idx => $data) {
		if($data['pno'] > 0) $data['pname'] = stripslashes($pdo->row("select name from $tbl[product] where no='$data[pno]'"));
		$data['reg_date'] = date('Y-m-d H:i:s', $data['reg_date']);
		$data['answer_date'] = ($data['answer_date'] > 0) ? date('Y-m-d H:i:s', $data['answer_date']) : '';
        $row = array();
		foreach($csv_fd as $key => $val) {
			$row[] = $data[$key];
		}
        $ExcelWriter->writeSheetRow($row);
        unset($row);

        // 엑셀 다운로드 기록
        if ($idx == 0) {
            addPrivacyViewLog(array(
                'page_id' => 'qna',
                'page_type' => 'excel',
                'target_id' => $data['member_id'],
                'target_cnt' => $NumTotalRec
            ));
        }
	}

    $ExcelWriter->writeFile();