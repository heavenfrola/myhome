<?
	include_once $engine_dir.'/_config/set.country.php'; // 국가정보
	asort($_nations);

	if(!$_POST['delivery_com']) msg('배송사를 먼저 선택하세요.');

	$sql = "select * from ${tbl['os_delivery_area']} where delivery_com='${_POST['delivery_com']}' order by `order` asc";
	$res = $pdo->iterator($sql);

	$file_name= '해외배송비설정';

	$transfer_price = $pdo->row("select transfer_price from ${tbl['delivery_url']} where no='${_POST['delivery_com']}'");

	$currency_decimal=0;
	if($transfer_price == 'N'){
		$currency_decimal = $cfg['m_currency_decimal'];
	}else if($transfer_price == 'Y'){
		$currency_decimal = $cfg['currency_decimal'];
	}

    $headerStyle = array(
        'fill' => '#f2f2f2',
        'font-style' => 'bold',
        'widths' => array()
    );

    $ExcelWriter = setExcelWriter();
    $ExcelWriter->setFileName($file_name);
    $ExcelWriter->setSheetName($file_name);
    $notice_text = array('중량 추가 및 금액 수정은 가능하나, 지역(열)은 추가할 수 없습니다. 현재 셀은 삭제하지 말고 업로드해 주세요.');
    $notice_style = array('font-style' => 'bold', 'halign' => 'left', 'wrap_text' => false);
    $ExcelWriter->writeSheetRow($notice_text, $notice_style);

	if($res){
        $csv = array();
		if($res->rowCount() > 0) {
            $csv[0] = '중량';
            foreach ($res as $data) {
                $csv[] = strip_tags($data['name']);
			}
		}
        $ExcelWriter->writeSheetRow($csv, $headerStyle);
		unset($csv);

		$sql = "select weight from ${tbl['os_delivery_prc']} where delivery_com='${_POST['delivery_com']}' group by weight order by `order` asc";
		$wres = $pdo->iterator($sql);

		if($wres){
            foreach ($wres as $wdata) {
				$sql = "select price from ${tbl['os_delivery_prc']} where delivery_com='${_POST['delivery_com']}' and weight='${wdata['weight']}' order by `order` asc, area_no asc";
				$pres = $pdo->iterator($sql);
				$csv = array();
				$csv[] = $wdata['weight'];
                foreach ($pres as $pdata) {
					$csv[] = number_format($pdata['price'], $currency_decimal);
				}
                $ExcelWriter->writeSheetRow($csv);
                unset($csv);
			}
		}
	}
    $ExcelWriter->writeFile();
