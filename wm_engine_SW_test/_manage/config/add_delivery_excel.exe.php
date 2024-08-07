<?PHP
    $ExcelWriter = setExcelWriter();
	$file_name = ($cfg['adddlv_type'] == 2) ? "국내배송비_세부설정" : "국내배송비_간편설정";
    $ExcelWriter->setFileName($file_name);
    $ExcelWriter->setSheetName($file_name);
    $headerType = array();
    $headerStyle = array(
        'fill' => '#f2f2f2',
        'font-style' => 'bold',
        'widths' => array()
    );

	$where = ($admin['partner_no']) ? "partner_no = {$admin['partner_no']}" : " partner_no = '0' || partner_no = ''";

	if($cfg['adddlv_type'] == 2) {
		$sql = "select * from ${tbl['delivery_area_detail']} where $where order by `sort` asc";

		$list = array(
			'cnt' => '번호',
			'name' => '배송지별칭',
			'sido' => '시/도',
			'gugun' => '구/군',
			'dong' => '동',
			'ri' => '리',
			'addprc' => '추가배송비',
			'sort' => '우선순위',
		);
        foreach ($list as $key => $val) {
            $headerType[$val] = 'string';
            $headerStyle['widths'][] = 30;
        }
        $ExcelWriter->writeSheetHeader($headerType, $headerStyle);

		$res = $pdo->iterator($sql);
		$cnt = 0;

        foreach ($res as $data) {
			$cnt++;
			$value = array();
			$data['cnt'] = $cnt;
			foreach($list as $key => $val) {
				$value[] = stripslashes($data[$key]);
			}
            $ExcelWriter->writeSheetRow($value);
            unset($value);
		}
	} else {
		$sql = "select * from ${tbl['delivery_area']} where $where order by `no` asc";
		$list = array(
			'cnt' => '번호',
			'area' => '지역명',
			'price' => '추가배송비',
		);
        foreach ($list as $key => $val) {
            $headerType[$val] = 'string';
            $headerStyle['widths'][] = 30;
        }
        $ExcelWriter->writeSheetHeader($headerType, $headerStyle);
		$res = $pdo->iterator($sql);
		$cnt = 0;

        foreach ($res as $data) {
			$cnt++;
			$arealen=strlen($data['area']);
			if($arealen > 2) $data['area']=substr($data['area'], 1, $arealen-2);
			$value = array();
			$data['cnt'] = $cnt;
			foreach($list as $key => $val) {
				$value[] = stripslashes($data[$key]);
			}
            $ExcelWriter->writeSheetRow($value);
            unset($value);
		}
	}
    $ExcelWriter->writeFile();
