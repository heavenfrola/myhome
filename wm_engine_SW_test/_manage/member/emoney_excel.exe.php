<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  예치금 내역 엑셀저장 처리
	' +----------------------------------------------------------------------------------------------+*/

    require_once __ENGINE_DIR__.'/_manage/intra/excel_otp.inc.php';

    $row_style = array();
    $headerType = array();
    $headerStyle = array(
        'fill' => '#f2f2f2',
        'font-style' => 'bold',
        'halign' => 'center',
        'widths' => array()
    );
    $widths = array(
        'member_name' => 30,
        'member_id' => 30,
        'mtype' => 25,
        'title' => 70,
        'amount' => 25,
        'member_emoney' => 25,
        'reg_date' => 25
    );
    $contentStyle = array(
        'title' => array('halign' => 'left'),
        'amount' => array('halign'=>'right'),
        'member_emoney' => array('halign'=>'right'),
    );

	include $engine_dir.'/_manage/member/emoney_list.php';

	switch($type) {
		case '1' : $file_name="예치금_지급내역".date("Y_m_d",$now); break;
		case '2' : $file_name="예치금_사용내역".date("Y_m_d",$now); break;
		default  : $file_name="예치금_내역".date("Y_m_d",$now); break;
	}

	$res = $pdo->iterator($sql);

    $col_list = array();
    $col_list['member_name'] = '이름';
    $col_list['member_id'] = '아이디';
    $col_list['mtype'] = '구분';
    $col_list['title'] = '적요';
    $col_list['amount'] = '예치금';
    $col_list['member_emoney'] = '회원소계';
    $col_list['reg_date'] = '날짜';

    foreach ($col_list as $key => $val) {
        $headerType[$val] = (in_array($key, array('amount', 'member_emoney'))) ? 'price' : 'string'; //해더데이터 형 선언 (key는 필드텍스트)
        $headerStyle['widths'][] = (!empty($widths[$key])) ? $widths[$key] : 20;
        $row_style[] = (!empty($contentStyle[$key])) ? $contentStyle[$key] : array('halign' => 'center');
    }

    $ExcelWriter = setExcelWriter();
    $ExcelWriter->setFileName($file_name);
    $ExcelWriter->setSheetName($file_name);
    $ExcelWriter->writeSheetHeader($headerType, $headerStyle);

    foreach ($res as $data) {
        $data['mtype'] = $milage_title[$data['mtype']];
        $data['title'] = stripslashes($data['title']);
        $data['amount'] *= ($data['ctype']=='-') ? -1 : 1;
        $data['amount'] = parsePrice($data['amount']);
        $data['member_emoney'] = parsePrice($data['member_emoney']);
        $data['reg_date'] = date('Y/m/d', $data['reg_date']);
        $row = array();
        foreach ($col_list as $key => $val) {
            $row[] = $data[$key];
        }
        $ExcelWriter->writeSheetRow($row, $row_style);
        unset($row);
    }

    if ($excel_auth_type == 'email' || $excel_auth_type == 'sms') {
        $filepath = $ExcelWriter->writeFile($root_dir.'/_data/'.$file_name);
        downloadArchive($filepath, $rand);
    } else {
        $ExcelWriter->writeFile();
    }