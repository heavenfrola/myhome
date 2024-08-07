<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  소셜쿠폰 코드 엑셀저장 처리
	' +----------------------------------------------------------------------------------------------+*/
    $ExcelWriter = setExcelWriter();
	if($_GET['code_detail']) {
		include_once 'sccoupon_code_list.php';
		
		$list = array(
			'cnt' => '번호',
			'code' => '코드',
			'use_str' => '사용여부'
		);
        $headerType = array();
        $headerStyle = array(
            'fill' => '#f2f2f2',
            'font-style' => 'bold'
        );
        foreach ($list as $key => $val) {
            $headerType[$val] = ($key === 'cnt') ? 'number' : 'string';
            $headerStyle['widths'][] = 20;
        }
        $file_name = '소셜쿠폰코드목록';
        $ExcelWriter->setFileName($file_name);
        $ExcelWriter->setSheetName($file_name);
        $ExcelWriter->writeSheetHeader($headerType, $headerStyle);

		$res = $pdo->iterator($sql);
		$cnt = 0;

        foreach ($res as $data) {
			$cnt++;
			$data = array_map('stripslashes', $data);
			$value = array();
			$data['use_str']=($data['use'] == 1) ? '미사용' : date('Y-m-d', $data['use_date']);
			foreach($list as $key => $val) {
				$value[]  = ($key == 'cnt') ? $cnt : $data[$key];
			}
            $ExcelWriter->writeSheetRow($value);
            unset($value);
		}
	} else {
        $headerType = array('code'=>'string');
        $headerStyle = array('widths'=>array(30), 'suppress_row' => true); //제목줄 비노출, 너비만 사용
		$no = numberOnly($_GET['no']);
		$data=get_info($tbl['sccoupon'], "no", $no);
		$fileName = '소셜쿠폰코드_'.$no;

        $ExcelWriter->setFileName($fileName);
        $ExcelWriter->setSheetName($fileName);
        $ExcelWriter->writeSheetHeader($headerType, $headerStyle);

		if(!$data[no]) msg("해당 소셜쿠폰이 존재하지 않습니다");

		$sql="select `code` from `$tbl[sccoupon_code]` where `scno`='$data[no]'";
		$res = $pdo->iterator($sql);
        foreach ($res as $cpn) {
            $ExcelWriter->writeSheetRow(array($cpn['code']));
		}
	}
    $ExcelWriter->writeFile();
