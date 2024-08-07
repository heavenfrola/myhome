<?PHP
	$ord_input_fd_selected = (!$cfg[ord_input_fd_selected]) ? "no,ono,dlv_no,dlv_code" : $cfg[ord_input_fd_selected];
	$okey = explode(',', $ord_input_fd_selected);
    $file_name = '실패내역';
    $ExcelWriter = setExcelWriter();
    $ExcelWriter->setFileName($file_name);
    $ExcelWriter->setSheetName($file_name);

	$csv = $_POST['csv'];

	foreach ($csv as $key=> $val) {
		$col++;
		$value = explode(',', $val);
        $row = array();
		foreach($okey as $keyy => $val) {
            $row[] = $value[$keyy];
		}
        $ExcelWriter->writeSheetRow($row);
        unset($row);
	}
    $ExcelWriter->writeFile();
