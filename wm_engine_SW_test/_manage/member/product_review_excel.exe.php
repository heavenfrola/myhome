<?PHP

	$no_qcheck = true;
	ini_set('memory_limit', '-1');
	set_time_limit(0);

	require 'product_review.php';

	$csv_fd = array(
		'stat' => '상태',
		'cate' => '분류',
		'pname' => '상품명',
		'title' => '제목',
		'content' => '내용',
		'rev_pt' => '평점',
		'recommend_y' => '추천',
		'recommend_n' => '비추천',
		'name' => '작성자명',
		'member_id' => '작성자아이디',
		'reg_date' => '등록일시',
		'buy_date' => '구매일시',
		'milage' => '지급적립금',
		'milage_date' => '적립일시',
		'hit' => '조회수'
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
        'member_id' => 30,
        'rev_pt' => 10,
        'recommend_y' => 10,
        'recommend_n' => 10,
        'hit' => 10,
    );
    $headerType = array();
	foreach($csv_fd as $key => $val){
        $headerType[$val] = (in_array($key, array('milage'))) ? 'price' : 'string';
        $headerStyle['widths'][] = (!empty($widths[$key])) ? $widths[$key] : 20;
	}
    $file_name = '상품후기';
    $ExcelWriter = setExcelWriter();
    $ExcelWriter->setFileName($file_name);
    $ExcelWriter->setSheetName($file_name);
    $ExcelWriter->writeSheetHeader($headerType, $headerStyle);

	$res = $pdo->iterator($sql);
    foreach ($res as $data) {
		$data['stat'] = $_review_stat[$data['stat']];
		if($data['pno'] > 0) $data['pname'] = stripslashes($pdo->row("select name from $tbl[product] where no='$data[pno]'"));
		$data['reg_date'] = date('Y-m-d H:i:s', $data['reg_date']);
		$data['buy_date'] = ($data['buy_date'] > 0) ? date('Y-m-d H:i:s', $data['buy_date']) : '';
		$data['milage_date'] = ($data['milage_date'] > 0) ? date('Y-m-d H:i:s', $data['milage_date']) : '';
		$data['recommend_y'] = number_format($data['recommend_y']);
		$data['recommend_n'] = number_format($data['recommend_n']);
        $data['milage'] = parsePrice($data['milage']);
        $row = array();
		foreach($csv_fd as $key => $val) {
			$row[] = $data[$key];
		}
        $ExcelWriter->writeSheetRow($row);
        unset($row);
	}

    $ExcelWriter->writeFile();
