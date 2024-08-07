<?PHP

	$ExcelWriter = setExcelWriter();
	include_once $engine_dir.'/_config/set.country.php'; // 국가정보
	asort($_nations);

	if(!$_POST['delivery_com']) msg('배송사를 먼저 선택하세요.');

	$sql = "select * from ${tbl['os_delivery_area']} where delivery_com='${_POST['delivery_com']}' order by `order` asc";
	$res = $pdo->query($sql);

	$file_name="배송국가샘플";
	$ExcelWriter->setFileName($file_name);
	$ExcelWriter->setSheetName($file_name);
	$max_cnt = $pdo->row("select max(cnt) from (select count(*) as cnt from `${tbl['os_delivery_country']}` where delivery_com='${_POST['delivery_com']}' group by area_no) t");

	$csv = array();
	$area_arr = array();
	$cdata = array();

	$headerStyle = array(
		'fill' => '#f2f2f2',
		'font-style' => 'bold'
	);

	if($res){
        foreach ($res as $data) {
            $csv[] = $data['name'];
            $area_arr[] = $data['no'];
        }
	}
	$ExcelWriter->writeSheetRow($csv, $headerStyle);
	unset($csv);

	$limit = 0;

	if($max_cnt > 0){
		for($i=1;$i<=$max_cnt;$i++){
			foreach($area_arr as $k=>$v){
				$sql = "select country_code from ${tbl['os_delivery_country']} where delivery_com='${_POST['delivery_com']}' and area_no='${v}' order by country_code asc limit ${limit},1";
				$country = $pdo->row($sql);

				$cdata[$k] = $country;


				$csv = $cdata;
			}
			$ExcelWriter->writeSheetRow($csv);

			$limit++;
		}
	}
	$ExcelWriter->writeFile();
