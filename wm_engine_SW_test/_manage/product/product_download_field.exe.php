<?PHP

	include_once $engine_dir."/_manage/product/product_search.inc.php";

	$_category = array('0' => '공통');
	$res = $pdo->iterator("select no, name from $tbl[category] where ctype='3'");
    foreach ($res as $data) {
		$_category[$data['no']] = stripslashes($data['name']);
	}
    $headerStyle = array();
    $headerStyle[0] = array(
        'widths' => array(16, 41, 16),
        'suppress_row' => true
    );//제목행 숨김
    $headerStyle[1] = array(
        'fill' => '#333333',
        'color' => '#ffffff',
        'font-style' => 'bold',
        'height' => 16
    ); //1행
    $headerStyle[2] = array(
        'fill' => '#f2f2f2',
        'font-style' => 'bold',
        'height' => 16
    ); //2행
    $headerStyle[3] = array(
        'fill' => '#f2f2f2',
        'font-style' => 'bold',
        'height' => 16
    ); //3행
    $cellStyle = array(
        2 => array(0 => array('fill' => '#333333', 'color' => '#ffffff', 'font-style' => 'bold')),
        3 => array(0 => array('fill' => '#333333', 'color' => '#ffffff', 'font-style' => 'bold'))
    );//특정 셀 스타일 ( headerStyle[key] => array ( 열번호 => array(스타일) ) )
	// 헤더
    $header_row[0] = array(0 => 'string', 1 => 'string', 2 => 'string');
    $header_row[1] = array(
        '시스템ID',
        '상품명',
        '고시종류'
    );
    $header_row[2] = array(
        '필드명',
        'emptyCell',
        'emptyCell'
    );
    $header_row[3] = array(
        '필드코드',
        'emptyCell',
        'emptyCell'
    );
    $default_col_cnt = count($header_row[1]); //커스텀영역이 아닌 기본 열 갯수

	$divline = $fno = array();
	$res = $pdo->iterator("select no, category, name from $tbl[product_field_set] order by category asc");
    foreach ($res as $idx => $data) {
		$fno[($idx+$default_col_cnt)] = $data['no'];

        $header_row[0][] = 'string';
        $headerStyle[0]['widths'][] = 30;

        // 라벨
        if (empty($divline[$data['category']])) {
            //해당 카테고리의 첫번째 데이터인 경우 카테고리명을 1행에 추가
            $header_row[1][] = stripslashes($_category[$data['category']]);
        } else {
            //병합을 위한 공백셀 추가
            $header_row[1][] = 'emptyCell';
        }
        // 필드코드
        $header_row[2][] = $data['name'];
        $header_row[3][] = $data['no'];
        $divline[$data['category']]++;
	}

    $file_name = '상품추가항목양식';
    $ExcelWriter = setExcelWriter();
    $ExcelWriter->setFileName($file_name);
    $ExcelWriter->setSheetName($file_name);
    foreach ($header_row as $key => $title_arr) {
        if ($key > 0) {
            $default_style = $headerStyle[$key];
            foreach ($title_arr as $col => $value) {
                $headerStyle[$key][$col] = (is_array($cellStyle[$key][$col])) ? $cellStyle[$key][$col] : $default_style;
            }
            $ExcelWriter->writeSheetRow($title_arr, $headerStyle[$key]);
        } else {
            $ExcelWriter->writeSheetHeader($title_arr, $headerStyle[$key]);
        }
    }
    // 셀병합
    $col = $default_col_cnt;
    foreach ($divline as $val) {
        $ExcelWriter->merge(0, $col, 0, $col+$val-1);
        $col+=$val;
    }
    $ExcelWriter->merge(0, 1, 2, 1); //상품명
    $ExcelWriter->merge(0, 2, 2, 2); //고시종류

	// 본문
	$_category[0] = '';
	$res = $pdo->iterator("select p.no, p.name, p.fieldset from $tbl[product] p $prd_join where p.stat in (2,3,4) $w order by no asc");
    foreach ($res as $data) {
        // 상품 정보
        $row = array(
            $data['no'],
            stripslashes($data['name']),
            $_category[$data['fieldset']],
        );

        // 추가항목
		$tmp = array();
		$fres = $pdo->iterator("select fno, value, pno from $tbl[product_field] where pno='$data[no]'");
        foreach ($fres as $fdata) {
			$tmp[$fdata['fno']] = stripslashes($fdata['value']);
		}
        foreach ($fno as $key => $val) {
            $row[] = $tmp[$val];
        }
        $ExcelWriter->writeSheetRow($row, array('height' => 16));
        unset($row);
	}

    $ExcelWriter->writeFile();

