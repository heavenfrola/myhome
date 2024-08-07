<?PHP

    $headerType = array();
    $headerStyle = array(
        'fill' => '#f2f2f2',
        'font-style' => 'bold',
        'halign' => 'center',
        'widths' => array()
    );
    $widths = array(
        'ono' => 25,
        'reg_date' => 25,
        'cash_reg_num' => 25,
        'mtrsno' => 25,
        'b_num' => 25,
        'cons_name' => 25,
        'amt1' => 25,
        'ostat' => 25,
        'stat' => 20
    );

    require_once __ENGINE_DIR__.'/_manage/intra/excel_otp.inc.php';

	$version="new";
	include_once $engine_dir.'/_manage/order/order_cash_receipt.php';

	$res = $pdo->iterator($sql);

    $idx = $pdo->lastRowCount();
    $NumTotalRec = $idx;

	function parseReceipt($res) {
		global $_order_cash_stat, $_order_stat, $tbl, $pdo;

		$data = $res->current();
        $res->next();
		if($data == false) return false;

		$data['reg_date'] = date('Y-m-d	', $data['reg_date']);
		$data['stat'] = $_order_cash_stat[$data['stat']];
		$data['ostat'] = $_order_stat[$pdo->row("select stat from $tbl[order] where ono='$data[ono]'")];

		return $data;
	}

    $col_list = array();
    $col_list['idx'] = '번호';
    $col_list['ono'] = '주문번호';
    $col_list['reg_date'] = '신청일';
    $col_list['cash_reg_num'] = '신청번호';
    $col_list['mtrsno'] = '승인번호';
    $col_list['b_num'] = '사업자번호';
    $col_list['cons_name'] = '주문자';
    $col_list['amt1'] = '총결제액';
    $col_list['ostat'] = '주문상태';
    $col_list['stat'] = '발급상태';

    $exceptionColType = array(
        'amt1' => 'price'
    );

    foreach ($col_list as $key => $val) {
        $headerType[$val] = (!empty($exceptionColType[$key])) ? $exceptionColType[$key] : 'string';
        $headerStyle['widths'][] = (!empty($widths[$key])) ? $widths[$key] : 20;
    }

    $file_name = '현금영수증내역';
    $ExcelWriter = setExcelWriter();
    $ExcelWriter->setFileName($file_name);
    $ExcelWriter->setSheetName($file_name);
    $ExcelWriter->writeSheetHeader($headerType, $headerStyle);

    while ($data = parseReceipt($res)) {
        $row = array();
        $data['idx'] = $idx--;
        foreach ($col_list as $key => $val) {
            $row[] = (!empty($exceptionColType[$key]) && $exceptionColType[$key] === 'price') ? parsePrice($data[$key]) : $data[$key];
        }
        $ExcelWriter->writeSheetRow($row);
        unset($row);

        // 엑셀 다운로드 기록
        if ($NumTotalRec-1 == $idx) {
            addPrivacyViewLog(array(
                'page_id' => 'cash',
                'page_type' => 'excel',
                'target_id' => ($data['member_id']) ? $data['member_id'] : $data['buyer_name'],
                'target_cnt' => $NumTotalRec
            ));
        }
    }

    if ($excel_auth_type == 'email' || $excel_auth_type == 'sms') {
        $filepath = $ExcelWriter->writeFile($root_dir.'/_data/'.$file_name);
        downloadArchive($filepath, $rand);

        // 엑셀 다운로드 시 관리자에게 문자 발송
        if ($scfg->comp('admin_set_confirm', 'Y') == true) {
            $use_yn = $pdo->row("select use_yn from {$tbl['cfg_confirm_list']} where code='cash_excel'");
            if ($use_yn == 'Y') {
                $sms_res = $pdo->iterator("select cell, name, admin_id from {$tbl['mng']} where cfg_receive='Y'");
                foreach ($sms_res as $sdata) {
                    if ($sdata['cell']) {
                        $config_name = $pdo->row("select name from {$tbl['cfg_confirm_list']} where code='order_excel'");
                        include_once $engine_dir."/_engine/sms/sms_module.php";
                        $sms_replace['config_name'] = $config_name;
                        $sms_replace['admin'] = $sdata['name']."(".$sdata['admin_id'].")";
                        SMS_send_case(19, $sdata['cell']);
                    }
                }
            }
        }
    } else {
        $ExcelWriter->writeFile();
    }
