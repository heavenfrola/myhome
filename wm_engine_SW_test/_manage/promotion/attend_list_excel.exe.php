<?PHP

	include 'attend_detail.php';

	$attend['name'] = stripslashes($attend['name']);

    $row_style = array();
    $headerType = array();
    $headerStyle = array(
        'fill' => '#f2f2f2',
        'font-style' => 'bold',
        'halign' => 'center',
        'widths' => array()
    );
    $widths = array(
        'name' => 30
    );
    $csv_fd = array(
        'name' => '이벤트명',
        'member_id' => '아이디',
        'member_name' => '이름',
        'check_date' => '체크일',
        'reg_date' => '체크시간',
        'prize_cpn' => '지급쿠폰',
        'prize_milage' => '지급적립금',
        'prize_point' => '지급포인트',
        'total_cnt' => '총참여수',
        'straight_cnt' => '연속참여수'
    );

    foreach ($csv_fd as $key => $val) {
        $headerType[$val] = ($key === 'prize_milage') ? 'price' : 'string';
        $headerStyle['widths'][] = (!empty($widths[$key])) ? $widths[$key] : 20;
    }

    $file_name = $attend['name'].'_출석체크내역';
    $ExcelWriter = setExcelWriter();
    $ExcelWriter->setFileName($file_name);
    $ExcelWriter->setSheetName($file_name);
    $ExcelWriter->writeSheetHeader($headerType, $headerStyle);

	$res = $pdo->iterator($sql);

    foreach ($res as $data) {
		$data['reg_date'] = date('H:i:s', $data['reg_date']);
		$data['prize_cpn'] = stripslashes(getPrizeCpn($data['prize_cno']));
        $data['name'] = $attend['name'];
		if($prize_ok == 'N') {
			$data['straight_cnt'] = $pdo->row("select straight_cnt from $tbl[attend_list] where eno='$no' and member_no='$data[member_no]' order by no desc limit 1");
			$data['prize_cpn'] = $data['total_cpn'].'개';
		}
        $data['prize_milage'] = parsePrice($data['prize_milage']);
        $row = array();
        foreach ($csv_fd as $key => $val) {
            $row[] = $data[$key];
        }
        $ExcelWriter->writeSheetRow($row);
        unset($row);
	}

    $ExcelWriter->writeFile();
