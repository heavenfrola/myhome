<?PHP

	include_once 'sccoupon_list.php';
    $ExcelWriter = setExcelWriter();
    $headerType = array();
    $headerStyle = array(
        'fill' => '#f2f2f2',
        'font-style' => 'bold'
    );
	$list = array(
		'cnt' => '번호',
		'member_name' => '회원이름',
		'member_id' => '회원아이디',
		'code' => '쿠폰코드',
		'scname' => '쿠폰명',
		'milage_prc' => '교환적립금',
		'cname' => '교환쿠폰명',
		'reg_date' => '교환일자',
	);

    $exceptionColType = array(
        'milage_prc' => 'price'
    );

    foreach ($list as $key => $val) {
        $headerType[$val] = (!empty($exceptionColType[$key])) ? $exceptionColType[$key] : 'string';
        $headerStyle['widths'][] = 20;
    }
    $file_name = '소셜쿠폰교환내역';
    $ExcelWriter->setFileName($file_name);
    $ExcelWriter->setSheetName($file_name);
    $ExcelWriter->writeSheetHeader($headerType, $headerStyle);
	$res = $pdo->iterator($sql);
	$cnt = 0;

    foreach ($res as $data) {
		$cnt++;
		$data = array_map('stripslashes', $data);
		$value = array();
		$data['reg_date'] = ($data['reg_date'])  ? date('Y-m-d H:i:s', $data['reg_date']) : '';
		foreach($list as $key => $val) {
            $data[$key] = (!empty($exceptionColType[$key]) && $exceptionColType[$key] === 'price') ? parsePrice($data[$key]) : $data[$key];
			$value[]  = ($key == 'cnt') ? $cnt : $data[$key];
		}
        $ExcelWriter->writeSheetRow($value);
        unset($value);
	}
    $ExcelWriter->writeFile();
