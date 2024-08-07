<?PHP

	$no = numberOnly($_GET['no']);
	$data = get_info($tbl['coupon'], 'no', $no);
	$fileName = '쿠폰코드'.$no;

	if(!$data['no']) msg('잘못된 쿠폰 번호입니다.');

    $headerType = array('인증코드' => 'string');
    $headerStyle = array(
        'fill' => '#f2f2f2',
        'font-style' => 'bold',
        'halign' => 'center',
        'widths' => array(30)
    );
    $ExcelWriter = setExcelWriter();
    $ExcelWriter->setFileName($fileName);
    $ExcelWriter->setSheetName($fileName);
    $ExcelWriter->writeSheetHeader($headerType, $headerStyle);

	$res = $pdo->iterator("select * from $tbl[coupon_auth_code] where cno='$no'");
    foreach ($res as $data) {
        $ExcelWriter->writeSheetRow(array($data['auth_code']));
	}
    $ExcelWriter->writeFile();
