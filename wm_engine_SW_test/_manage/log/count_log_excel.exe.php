<?php

	/* +----------------------------------------------------------------------------------------------+
	' |  방문자수 엑셀 다운로드
	' +----------------------------------------------------------------------------------------------+*/

	$y = addslashes($_GET['y']); // 시작일 연
	$m = addslashes($_GET['m']); // 시작일 월
	$d = addslashes($_GET['d']); // 시작일 일

	$y2 = addslashes($_GET['y2']); // 종료일 연
	$m2 = addslashes($_GET['m2']); // 종료일 월
	$d2 = addslashes($_GET['d2']); // 종료일 일

	$excelDownView = addslashes($_GET['view']); // 시간/일/주/월

	include_once 'count_log.php';

	$headerType = array();
	$headerStyle = array('fill' => '#f2f2f2', 'font-style' => 'bold', 'widths' => array());
	$widths = array('startday' => 30, 'endday' => 30, 'year' => 20, 'month' => 20, 'day' => 20, 'time' => 20, 'week' => 20, 'cnt' => 30);

	$value = array();
	foreach ($excelData as $k => $v) {
		$value[] = $excelData[$k];
	}

	foreach ($col_list as $key => $val) {
		$headerType[$val] = 'string';
		$headerStyle['widths'][] = (!empty($widths[$key])) ? $widths[$key] : 50;
	}

	$file_name = str_replace(' ', '', $title);
	$ExcelWriter = setExcelWriter();
	$ExcelWriter->setFileName($file_name);
	$ExcelWriter->setSheetName($file_name);
	$ExcelWriter->writeSheetHeader($headerType, $headerStyle);

	$data = array();
	foreach ($value as $key => $val) {
		$row = array();
		foreach ($col_list as $k => $v) {
			$row[] = $val[$k];
		}
		$ExcelWriter->writeSheetRow($row);
		unset($row);
	}

	$ExcelWriter->writeFile();