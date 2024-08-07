<?PHP

    $mno = numberOnly($_GET['mno']);
    $smode = $_GET['smode'];
    $mid = addslashes($_GET['mid']);

	include_once 'coupon_down_list.php';

    $row_style = array();
    $headerType = array();
    $headerStyle = array(
        'fill' => '#f2f2f2',
        'font-style' => 'bold',
        'halign' => 'center',
        'widths' => array()
    );
	if($is_type == 'A' ){
		$list = array(
				'cnt' => '번호',
				'member_name' => '회원이름',
				'member_id' => '회원아이디',
				'cell' => '휴대폰번호',
				'sms' => '휴대폰수신여부',
				'name' => '쿠폰명',
				'sale_prc' => '할인금액(율)',
				'prc_limit' => '사용제한',
				'sale_limit' => '최대할인',
				'down_date' => '발급일',
				'use_date' => '사용일',
				'ono' => '사용 주문번호',
			);
        $file_name = '온라인';
	} else {
			$list = array(
				'cnt' => '번호',
				'member_name' => '회원이름',
				'member_id' => '회원아이디',
				'cell' => '휴대폰번호',
				'sms' => '휴대폰수신여부',
				'name' => '쿠폰명',
				'sale_prc' => '할인금액(율)',
				'prc_limit' => '사용제한',
				'auth_code' => '인증코드',
				'use_date' => '사용일',
				'ono' => '사용 주문번호',
			);
        $file_name = '시리얼';
	}
    $exceptionColType = array(
        'prc_limit' => 'price',
        'sale_limit' => 'price'
    );
    foreach ($list as $key => $val) {
        $headerType[$val] = (!empty($exceptionColType[$key])) ? $exceptionColType[$key] : 'string';
        $headerStyle['widths'][] = 20;
    }
    $file_name .= '_쿠폰목록';
    $ExcelWriter = setExcelWriter();
    $ExcelWriter->setFileName($file_name);
    $ExcelWriter->setSheetName($file_name);
    $ExcelWriter->writeSheetHeader($headerType, $headerStyle);
	$res = $pdo->iterator($sql);
	$cnt = 0;

    foreach ($res as $data) {
		$cnt++;
        $data['cnt'] = $cnt;
		$data = array_map('stripslashes', $data);
		$value = array();
		$data['down_date'] = ($data['down_date'])  ? date('Y-m-d H:i:s', $data['down_date']) : '';
		$data['use_date'] = ($data['use_date'])  ? date('Y-m-d H:i:s', $data['use_date']) : '';
		foreach($list as $key => $val) {
            if ($key === 'sale_prc' && $data['sale_type'] === 'm') {
                $value[] = parsePrice($data['sale_prc'], true);
            }else {
                $value[] = (!empty($exceptionColType[$key]) && $exceptionColType[$key] === 'price') ? parsePrice($data[$key]) : $data[$key];
            }
		}
        $ExcelWriter->writeSheetRow($value);
        unset($value);
	}
    $ExcelWriter->writeFile();
