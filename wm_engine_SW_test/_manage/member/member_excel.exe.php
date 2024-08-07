<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  회원 엑셀저장 처리
	' +----------------------------------------------------------------------------------------------+*/

	set_time_limit(0);
	ini_set('memory_limit', -1);
	$pdo->query("set session wait_timeout = 300;");

	if($_REQUEST['exec']) {
		$_pg_type = 'member';
		include_once $engine_dir.'/_manage/order/excel_set.php';
		msg($msg, './?body=member@member_excel_config', 'parent');
	}

	if ($admin['level'] > 2 && strchr($admin['auth'], '@auth_memberexcel') == false) {
		msg('엑셀 다운로드 권한이 없습니다.');
	}

    require_once __ENGINE_DIR__.'/_manage/intra/excel_otp.inc.php';

    if (fieldExist($tbl['member_xls_log'], 'reason') == false) {
        $pdo->query("
            alter table {$tbl['member_xls_log']}
                add column reason varchar(500) not null default '',
                add column ip varchar(15) not null default ''
        ");
    }

	$pdo->query("
        insert into {$tbl['member_xls_log']}
            (admin_no, admin_id, admin_level, reason, ip, reg_date)
            values (?, ?, ?, ?, ?, ?)
    ", array(
        $admin['no'], $admin['admin_id'], $admin['level'], trim($_GET['xls_reason']), $_SERVER['REMOTE_ADDR'], $now
    ));

	include $engine_dir."/_manage/member/member_excel_config.php";
	if(in_array('last_ord', $_mbr_excel_fd_selected)) {
		$add_f = ", (select date1 from $tbl[order] where member_no=x.no and stat between 1 and 5 order by date1 desc limit 1) as last_ord";
	}
	include $engine_dir."/_manage/member/member_list.php";

	$res = $pdo->iterator($sql);
	$idx = $pdo->row($sql_t);

	$_group = getGroupName();

    $headerStyle = array(
        'fill' => '#f2f2f2',
        'font-style' => 'bold',
        'widths' => array(),
        'halign' => 'center'
    );
    $widths = array(
        'idx' => 10,
        'member_id' => 30,
        'email' => 30,
        'addr1' => 40,
        'addr2' => 40,
    );
    $contentStyle = array(
        'milage' => array('halign' => 'right'),
        'emoney' => array('halign' => 'right'),
        'total_prc' => array('halign' => 'right')
    );
    $headerType = array();
    $row_style = array();
    $ExcelWriter = setExcelWriter();

    foreach($_mbr_excel_fd_selected as $val){

        if($cfg['join_jumin_use'] == "N"){
            if($val == 'jumin') continue;
            if($val == 'sex' && $cfg['join_sex_use'] != 'Y') continue;
            if($val == 'age' && $cfg['join_birth_use'] != 'Y') continue;
        }
        $field = $mbr_excel_fd[$val];
		if (!empty($field)) {
			//항목명이 존재하는 필드만 출력
			$field .= $ExcelWriter->duplicateField($_mbr_excel_fd_selected, $val);
			$headerType[$field] = (in_array($val, array('milage', 'emoney', 'total_prc'))) ? 'price' : 'string';
            if (in_array($val, array('total_ord', 'total_con'))) {
                $headerType[$field] = 'integer';
            }
			$headerStyle['widths'][] = (!empty($widths[$val])) ? $widths[$val] : 20;
			$row_style[] = (!empty($contentStyle[$val])) ? $contentStyle[$val] : array('halign' => 'center');
		}
    }
    $file_name = '회원목록';
    $ExcelWriter->setFileName($file_name);
    $ExcelWriter->setSheetName($file_name);
    $ExcelWriter->writeSheetHeader($headerType, $headerStyle);

    foreach ($res as $data) {
		$row = array();
        $original_data = $data;//치환 필요시 활용
		foreach($_mbr_excel_fd_selected as $val){


			if($cfg['join_jumin_use'] == 'N'){
				if($val == 'jumin') continue;
				if($val == 'sex' && $cfg['join_sex_use'] != 'Y') continue;
				if($val == 'age' && $cfg['join_birth_use'] != 'Y') continue;
				else $data['age'] = getAge('', $data['birth']);
			} else {
				if($val == 'jumin' && $data['jumin']) {
					$_jumin = explode('-', $data['jumin']);
					$_jumin_md5 = md5($_jumin[1]);
					$_jumin_21 = substr(7, 1, $data['jumin']);
					$data['jumin'] = $_jumin[0].'-'.$_jumin_21.$_jumin_md5;
				}
				if($val == 'sex') $data['sex'] = getSex($data['jumin']);
				if($val == 'age') $data['age'] = getAge($data['jumin']);
			}
			if($val == 'last_con') $data[$val] = date('Y-m-d', $original_data[$val]);
			if($val == 'reg_date_a') $data[$val] = date('Y-m-d', $original_data['reg_date']);
			if($val == 'reg_date_b') $data[$val] = date('Y-m-d H:i', $original_data['reg_date']);
			if($val == 'birth'){
				if(!$data['birth'] && $data['jumin']){
					$data['birth_y'] = (substr(7, 1, $data['jumin']) < 3) ? '19'.substr(0, 2, $data['jumin']) : '20'.substr(0, 2, $data['jumin']);
					$data['birth_m'] = substr(2, 2, $data['jumin']);
					$data['birth_d'] = substr(4, 2, $data['jumin']);
					$data['birth'] = $data['birth_y'].'-'.$data['birth_m'].'-'.$data['birth_d'];
				}
			}
			if($val == 'mailing' || $val == 'sms'){
				$data[$val] = ($original_data[$val] == 'Y') ? '수신함' : '수신안함';
			}
			if($val == 'level') $data[$val]=$_group[$original_data['level']];
			if(@strchr($val, "add_info") && $original_data[$val]!=""){
				$_add_fd = str_replace('add_info', '', $val);
				$_add_type = $_mbr_add_info[$_add_fd]['type'];
				if($_add_type == 'radio' || $_add_type == 'checkbox'){
					$tmp = '';
					$spt = explode('@', $original_data[$val]);
					foreach($spt as $val2) {
						if($val2 === '') continue;
						$tmp .= ','.trim($_mbr_add_info[$_add_fd]['text'][$val2]);
					}
					$data[$val] = preg_replace('/^,/', '', $tmp);
				}
			}
			if($val == 'milage' || $val == 'emoney' || $val == 'total_prc') $data[$val] = parsePrice($original_data[$val]);
			if($val == 'last_ord') {
				$data[$val] = $original_data[$val] > 0 ? date('Y-m-d H:i:s', $original_data[$val]) : '-';
			}
			$data['idx'] = $idx;
			$data[$val] = stripslashes($data[$val]);

			if ($mbr_excel_fd[$val]) {
				//항목명이 존재하는 필드만 출력
	            $row[] = $data[$val];
			}
		}

        $ExcelWriter->writeSheetRow($row, $row_style);
        unset($row);
		$idx--;

        // 엑셀 다운로드 기록
        if ($NumTotalRec == $idx) {
            addPrivacyViewLog(array(
                'page_id' => 'member',
                'page_type' => 'excel',
                'target_id' => $data['member_id'],
                'target_cnt' => $NumTotalRec
            ));
        }
	}

    if ($excel_auth_type == 'email' || $excel_auth_type == 'sms') {
        $filepath = $ExcelWriter->writeFile($root_dir.'/_data/'.$file_name);
        downloadArchive($filepath, $rand);

        // 엑셀 다운로드 시 관리자에게 문자 발송
        if ($scfg->comp('admin_set_confirm', 'Y') == true) {
            $use_yn = $pdo->row("select use_yn from {$tbl['cfg_confirm_list']} where code='member_excel'");
            if ($use_yn == 'Y') {
                $sms_res = $pdo->iterator("select cell, name, admin_id from {$tbl['mng']} where cfg_receive='Y'");
                foreach ($sms_res as $sdata) {
                    if ($sdata['cell']) {
                        $config_name = $pdo->row("select name from {$tbl['cfg_confirm_list']} where code='member_excel'");
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
