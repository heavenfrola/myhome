<?PHP

	set_time_limit(0);
	ini_set('memory_limit', -1);
	$no_qcheck = true;

	if($_REQUEST['exec']) {
		$_pg_type="order";
		include_once $engine_dir."/_manage/order/excel_set.php";
		msg($msg, "./?body=config@order_excel_config", "parent");
	}

	if ($admin['level'] > 2 && $admin['level'] != 4 && strchr($admin['auth'], '@auth_orderexcel') == false) {
		msg('엑셀 다운로드 권한이 없습니다.');
	}

    require_once __ENGINE_DIR__.'/_manage/intra/excel_otp.inc.php';

	$pdo->query("SET group_concat_max_len=2048");

	if($_SESSION['admin_no'] == "daum_how") include $engine_dir."/_manage/extension/daumhow/daum_list.php";
	else include $engine_dir."/_manage/order/order_list.php";

	include $engine_dir."/_manage/config/order_excel_config.php";

    if (array_search('member_group', $_ord_excel_fd_selected) > 0) { // 회원 등급 출력 사용
        $cfg['ord_list_mgroup'] = 'Y';
    }

	$xlsmode = $_POST['xlsmode'];
	if($xlsmode == "product") { // 주문상품 기준 엑셀 다운로드
		list($qry_field, $qry_where) = explode(' where 1 ', $sql);

		$add_xls_field = '';
		if($cfg['use_erp_storage'] == 'Y') { // 창고위치
			$add_xls_field  .= ", p.storage_no";
		}
		if(fieldExist($tbl['order_product'], 'prdcpn_no')) { //개별할인쿠폰
			$add_xls_field  .= ", c.prdcpn_no";
		}
		$add_xls_field .= ', '.getOrderSalesField('c');

        if ($cfg['use_set_product'] == 'Y') {
            $add_xls_field .= ", c.set_pno";
        }
        if (isset($grps) == true) {
            $add_xls_field .= ", m.level";
        }

		$qry_field = "select a.*, c.no as opno, c.pno, c.name as title, c.`option`, c.prd_type, c.buy_ea, c.sell_prc, c.stat as pstat, c.etc".
			         " , c.total_prc, c.dlv_hold, c.complex_no ".
					 " , p.seller, p.origin_name, p.origin_prc, p.code, p.big, c.repay_prc, p.name_referer".
					 " , c.r_name, c.r_zip, c.r_addr1, c.r_addr2, c.r_phone, c.r_cell, c.r_message, c.dlv_code $add_xls_field ".
					 " from $tbl[order] a $j ";
		if(!$seller_idx) $qry_field .= " left join $tbl[product] p on p.no = c.pno";

		$qry_where = str_replace($g, '', $qry_where);
		$sql  = $qry_field." where 1 ".$qry_where;
		$sql2 = "select count(*) from `$tbl[order]` a $j where 1 ".$qry_where;
	} else { // 주분번호 기준 엑셀 다운로드
		if(is_array($stat)) $prd_part .= " and stat in(".implode(',', $stat).")";
		if($addr_changed) {
			if(fieldExist($tbl['order_product'], 'addr_changed') == true) {
				$prd_part .= " and addr_changed='$addr_changed'";
			}
		}
		if(strlen($dlv_hold) == 1) $prd_part .= " and dlv_hold='$dlv_hold'";
		if(in_array('title', $_ord_excel_fd_selected)) $afield .= ", (select group_concat(concat(name,' - ',`option`,'(',buy_ea,')') separator ' / ') from $tbl[order_product] where ono = a.ono $prd_part) as `title`";
		if(in_array('option', $_ord_excel_fd_selected)) $afield .= ", (select group_concat(`option` separator ' / ') from $tbl[order_product] where ono = a.ono $prd_part) as `option`";
		if(in_array('buy_ea', $_ord_excel_fd_selected)) $afield .= ", (select sum(buy_ea) from $tbl[order_product] where ono = a.ono $prd_part) as `buy_ea`";
		if(in_array('etc', $_ord_excel_fd_selected)) $afield .= ", (select group_concat(`etc` separator ' / ') from $tbl[order_product] where ono = a.ono $prd_part) as `etc`";
        if ($cfg['use_set_product'] == 'Y') $afield .= ", c.set_pno";
        if (isset($grps) == true) {
            $afield .= ", m.level";
        }

        $getSalesFld = getOrderSalesField('', ',');
        $getSalesFld_ex = explode(',', $getSalesFld);
        $sumSales = '';
        foreach ($getSalesFld_ex as $v) {
            if (in_array($v, $_ord_excel_fd_selected)) {
                $sumSales .= ", sum(if(c.stat < 11, c.$v, 0)) as $v";
            }
        }

		list($qry_field, $qry_where) = explode(' where 1 ', $sql);
		$sql  = "select a.*, group_concat(c.no) as order_products $afield $sumSales from $tbl[order] a $j where 1 ".$qry_where;
		$xlsmode = "order";
	}

	if($xlsmode == "product") $sql = str_replace("`date1` desc","`date1` asc, a.`no`", trim($sql));
	else $sql = str_replace("`date1` desc","`date1` asc", trim($sql));

	$idx = $pdo->row($sql2);
    $NumTotalRec = $idx;

	// 만건 이상 엑셀 다운로드는 하루에 한번만 가능하도록
	if($idx >= 10000) {
		$compare_day = date('Ymd');
		$last_date = $pdo->row("select `value` from `$tbl[default]` where `code` = 'order_xls_limit'");
		if($last_date >= $compare_day) {
			msg('만건 이상의 대량 다운로드는 하루에 한번만 하실 수 있습니다.\\t');
		}
		if($last_date) $pdo->query("update `$tbl[default]` set `value`='$compare_day', `ext`='$idx' where `code`='order_xls_limit'");
		else $pdo->query("insert into `$tbl[default]` (`code`,`value`,`ext`) values ('order_xls_limit', '$compare_day','$idx')");
	}

	$res = $pdo->iterator($sql);

	$continues = array();
	if($cfg['bank_name'] != 'Y') $continues[] = 'bank_name';
	if($_use['recom_member'] != 'Y') $continues[] = 'recom_member';
	if($xlsmode != 'product') {
		$continues[] = 'big';
		$continues[] = 'seller';
		$continues[] = 'origin_name';
		$continues[] = 'origin_prc';
		$continues[] = 'code';
		$continues[] = 'storage_loc';
	} else {
		$continues[] = 'extra1';
	}

	if(headers_sent()) return;

    $headerStyle = array(
        'fill' => '#f2f2f2',
        'font-style' => 'bold',
        'halign' => 'center',
        'widths' => array()
    );
    $widths = array(
        'title' => 50,
        'addressee_addr' => 50
    );
    $exceptionColType = array(
        'prd_prc' => 'price',
        'dlv_prc' => 'price',
        'milage_prc' => 'price',
        'emoney_prc' => 'price',
        'repay_prc' => 'price',
        'sell_prc' => 'price',
        'pay_prc' => 'price',
        'total_prc' => 'price',
        'point_use' => 'price'
    );
    foreach ($_order_sales as $key => $nm) {
        $exceptionColType[$key] = 'price';
    }
    $ExcelWriter = setExcelWriter();
    // headers
    foreach($_ord_excel_fd_selected as $key => $val) {
        if(in_array($val, $continues)) continue;
        $field = $ord_excel_fd[$val];
        if($val == "1") $field = '';
        $field .= $ExcelWriter->duplicateField($_ord_excel_fd_selected, $val);
        $headerType[$field] = (!empty($exceptionColType[$val])) ? $exceptionColType[$val] : 'string';
        $headerStyle['widths'][] = (!empty($widths[$val])) ? $widths[$val] : 20;
    }
    $file_name = '주문목록';
    $ExcelWriter->setFileName($file_name);
    $ExcelWriter->setSheetName($file_name);
    $ExcelWriter->writeSheetHeader($headerType, $headerStyle);

	if(!isTable($tbl['erp_storage'])) {
		$use_storage = false;
	}

	$dlv_url_cache = array(); // 배송업체명 캐시
	$big_cache = array(); // 대분류명 캐시

	$row = 1;
    foreach ($res as $data) {
		$data['title'] = preg_replace('/<br(\s*\/)?>/', '', $data['title']);
		$data['title'] = preg_replace('/<p(\s*[^>]+)?>(.*)?<\/p>/', '$2', $data['title']);
		$data['title'] = str_replace("<split_big>", ",", $data['title']);
		$data['title'] = str_replace("<split_small>", ":", $data['title']);
		$data['title'] = str_replace("<span class=''>[무료배송]</span>", "", $data['title']);
		$data['title'] = strip_tags($data['title']);

		$btop_date = $data['date1'];

		if($xlsmode == 'product') {
			if($data['r_zip']) $data['addressee_zip'] = $data['r_zip'];
			if($data['r_name']) $data['addressee_name'] = $data['r_name'];
			if($data['r_addr1']) $data['addressee_addr1'] = $data['r_addr1'];
			if($data['r_addr2']) $data['addressee_addr2'] = $data['r_addr2'];
			if($data['r_phone']) $data['addressee_phone'] = $data['r_phone'];
			if($data['r_cell']) $data['addressee_cell'] = $data['r_cell'];
			if($data['r_message']) $data['dlv_memo'] = $data['r_message'];
			$data['sell_prc'] = parsePrice($data['sell_prc']);
			$data['pay_prc'] = $data['total_prc']-getOrderTotalSalePrc($data);
			if($data['pstat'] > 10 && in_array($data['pstat'], array(12, 14, 16, 18)) == false) {
				$data['pay_prc'] = 0;
			}
            $data['point_use'] = 0; // 주문 상품 엑셀에서 네이버포인트 미 표현
            if($data['name_referer']) $data['name_referer']; // 참고 상품명 추가
		}

		$data['prd_prc'] = parsePrice($data['prd_prc']);
		$data['total_prc'] = parsePrice($data['total_prc']);
		$data['pay_prc'] = parsePrice($data['pay_prc']+$data['point_use']);
		$data['dlv_prc'] = parsePrice($data['dlv_prc']);
		$data['milage_prc'] = parsePrice($data['milage_prc']);
		$data['emoney_prc'] = parsePrice($data['emoney_prc']);
		$data['sale1'] = parsePrice($data['sale1']);
		$data['sale2'] = parsePrice($data['sale2']);
		$data['sale3'] = parsePrice($data['sale3']);
		$data['sale4'] = parsePrice($data['sale4']);
		$data['sale5'] = parsePrice($data['sale5']);
		$data['sale6'] = parsePrice($data['sale6']);
		$data['sale7'] = parsePrice($data['sale7']);
		$data['sale8'] = parsePrice($data['sale8']);
		$data['repay_prc'] = parsePrice($data['repay_prc']);
        if ( !empty($data['date1']) ) {
            $data['year'] = date('Ym', $data['date1']); //date1 timestamp의 '년월'
            $data['day'] = date('d', $data['date1']); //date1 timestamp의 '일'
        }

		if($xlsmode == "product") {
			if($data['dlv_hold'] == 'Y') $data['dlv_hold'] = '배송보류';
			else $data['dlv_hold'] = '';
		} else {
			if($data['postpone_yn'] == 'Y') $data['dlv_hold'] = '전체보류';
			else if($data['postpone_yn'] == 'B') $data['dlv_hold'] = '부분보류';
			else $data['dlv_hold'] = '';
		}

        // 입점사 다운로드 일 경우 타사 송상번호 보이지 않도록
        if ($xlsmode == 'order' && $admin['partner_no'] > 0) {
            $xlsdata = $pdo->assoc("
                select
                    dlv_no, dlv_code from {$tbl['order_product']}
                where
                    ono='{$data['ono']}' and partner_no='{$admin['partner_no']}'
            ");
            $data['dlv_no'] = $xlsdata['dlv_no'];
            $data['dlv_code'] = $xlsdata['dlv_code'];
        }

		$blacklist = array();
		if($data['member_no']) $blacklist = $pdo->assoc("select `blacklist`, `black_reason` from `$tbl[member]` where `no` ='$data[member_no]'");
        $convert_chk = $ExcelWriter->duplicate_cnt; //데이터의 컨버팅 판단에 활용 (중복 컨버팅 방지)
        $row = array();
        $original_data = $data;//치환 필요시 활용
		foreach($_ord_excel_fd_selected as $key=>$val){

			if(in_array($val, $continues)) continue;

			if(preg_match('/^order_add_info_([0-9]+)$/', $val, $temp)) { // 주문추가항목
				$data[$val] = orderAddFrm($temp[1], 0, $data);
			}

			switch($val) {
				case 'big' :
					if($big_cache[$data['big']]) $data[$val] = $big_cache[$data['big']];
					else {
						$data[$val] = $pdo->row("select name from $tbl[category] where no = $data[big]");
						$data[$val] = stripslashes($data[$val]);
						$big_cache[$data['big']] = $data[$val];
					}
				break;
				case 'pay_type' :
					$data[pay_type]=$_pay_type[$original_data[pay_type]];
					if($data[milage_prc]>0 && $data[pay_type] != "적립금") $data[pay_type].="+적립금";
				break;
				case 'prd_prc' :
					if($xlsmode == "product") $data[prd_prc] = $data[sell_prc]*$data[buy_ea];
				break;
				case 'dlv_prc' :
					$data['dlv_prc'] = parsePrice($original_data['dlv_prc']);
					if($admin['level'] == 4) {
						$data['dlv_prc'] = parsePrice($pdo->row("select dlv_prc from $tbl[order_dlv_prc] where ono='$data[ono]' and partner_no='$admin[partner_no]'"));
					}
				break;
				case 'option' :
					if($original_data['option']){
						$data['option'] = str_replace("<split_big>", " / ", $original_data['option']);
						$data['option'] = str_replace("<split_small>", ": ", $data['option']);
					}
				break;
				case 'recom_member' :
					$data['recom_member'] = ($data['member_no']) ? $pdo->row("select `recom_member` from `$tbl[member]` where `no`='$data[member_no]'") : "";
				break;
				case 'dlv_no' :
					if($data['dlv_no']) {
						$data['dlv_no'] = ($dlv_url_cache[$original_data['dlv_no']]) ? $dlv_url_cache[$original_data['dlv_no']] : $pdo->row("select `name` from `$tbl[delivery_url]` where `no`='$original_data[dlv_no]' limit 1");
					} else {
						$data['dlv_no'] = '';
					}
				break;
				case 'date1' :
				case 'date2' :
				case 'date3' :
				case 'date4' :
				case 'date5' :
                case 'repay_date' :
					$data[$val] = ($original_data[$val] > 0) ? date("Y-m-d h:i:s A",$original_data[$val]) : '';
				break;
				case 'btop_ymd' :
					$data['btop_ymd_ap'] = (date("a", $btop_date) == 'am') ? '오전' : '오후';
					$data['btop_ymd'] = date("Y-m-d", $btop_date)." ".$data['btop_ymd_ap']." ".date("g:i:s", $btop_date);
				break;
				case 'btop_ono' :
					if($_tmp_ono == $data['ono']) $btop_num++;
					else $btop_num = 1;
					$data['btop_ono'] = $data[ono].'-'.$btop_num;
				break;
				case 'order_gift' :
					$data['order_gift'] = str_replace("@", ",", preg_replace('/^@|@$/', '', $original_data['order_gift']));
					$data['order_gift'] = preg_replace('/_[0-9]+/', '', $data['order_gift']);
					if($data['order_gift']) $data['order_gift'] = $pdo->row("select group_concat(`name`) from `$tbl[product_gift]` where `no` in ($data[order_gift])");
				break;
				case 'ymd' :
					$data['ymd'] = date('Ymd', $now);
				break;
				case 'addressee_addr' :
					$data['addressee_addr'] = $original_data['addressee_addr1'];
					if($original_data['addressee_addr3']) $data['addressee_addr'] .= " ".$original_data['addressee_addr3'];
					if($original_data['addressee_addr4']) $data['addressee_addr'] .= " ".$original_data['addressee_addr4'];
					$data['addressee_addr'] .= ' '.$original_data['addressee_addr2'];
				break;
				case 'stat' :
					$data['stat'] = ($xlsmode == 'order') ? $_order_stat[$original_data['stat']] : $_order_stat[$data['pstat']];
				break;
				case 'addressee_cell' :
				case 'addressee_phone' :
					$data[$val] = str_replace('--', '', $original_data[$val]);
				break;
				case 'delivery_type' :
					$data['delivery_type'] = ($cfg['delivery_type'] == 2) ? '착불' : '선불';
				break;
				case 'hth_pay' :
					$data['hth_pay']="신용";
				break;
				case '1' :
					$data['1'] = '1';
				break;
				case 'blacklist' :
					$data[blacklist]=($blacklist[blacklist]=='1') ? "블랙리스트회원" : "일반회원";
				break;
				case 'black_reason' :
					$data[black_reason]=$blacklist[black_reason];
				break;
				case 'etc' :
					if(trim($original_data[etc]) == '/') $data['etc']='';
				break;
				// 해외배송이 가능할때 국가/배송업체 추가
				case 'nations' :
					if(trim($original_data[nations]) && ($cfg['delivery_fee_type'] == 'O' || $cfg['delivery_fee_type'] == 'A')) $data['nations']=getCountryNameFromCode($original_data['nations']);
				break;
				case 'delivery_com' :
					if(trim($original_data[delivery_com]) && ($cfg['delivery_fee_type'] == 'O' || $cfg['delivery_fee_type'] == 'A')) $data['delivery_com']=getDeliveryNameFromNo($original_data['delivery_com']);
				break;
                case 'addressee_addr1' :
                case 'addressee_addr3' :
                case 'addressee_addr4' :
                case 'addressee_addr2' :
                    $data[$val] = (trim($original_data[$val]) && ($scfg->comp('delivery_fee_type', 'O') || $scfg->comp('delivery_fee_type', 'A')) && $original_data['nations'] != '') ? $data[$val] : '';
                    break;
				case 'coupon_name' :
					if ($xlsmode == "product") {
						$data['coupon_name'] = $pdo->row("select name from {$tbl['coupon_download']} where ono='{$data['ono']}' and stype != 5");
						if ($data['prdcpn_no']) {
							$data['coupon_name'] .= "/";
							$data['coupon_name'] .= $pdo->row("select name from {$tbl['coupon_download']} where no='{$data['prdcpn_no']}'");
						}
					} else {
						$data['coupon_name'] = $pdo->row("select group_concat(name separator '/') from {$tbl['coupon_download']} where ono='{$data['ono']}'");
					}
				break;
				case 'storage_name' :
					if ($use_storage === false) {
					    $data['storage'] = $data['storage_name'] = '';
					} else if($xlsmode == 'product') {
						$data['storage'] = getStorage($original_data);
						$data['storage_name'] = $data['storage']['name'];
					} else {
                        $data['storage_name'] = $original_data['storage_name'];
						$tmp = '';
						$tmp2 = $pdo->iterator("select op.name, op.`option`, op.buy_ea, s.name as storage from $tbl[order_product] op inner join $tbl[product] p on op.pno=p.no left join $tbl[erp_storage] s on p.storage_no=s.no where op.no in ($data[order_products])");
						if($tmp2) {
							foreach ($tmp2 as $tmp3) {
								$data['storage_name'] .= $tmp3['name'];
								$data['storage_name'] .= ' - ('.parseOrderOption($tmp3['option']).')';
								$data['storage_name'] .= " [$tmp3[buy_ea] 개]";
								if($tmp3['storage']) $data['storage_name'] .= ' [창고] '.$tmp3['storage'];
								$data['storage_name'] .= "///";
							}
						}
						$data['storage_name'] = trim($data['storage_name'], '///');
					}
				break;
				case 'storage_loc' :
					$data['storage'] = getStorage($original_data);
					$data['storage_loc'] = getStorageLocation($original_data['storage']);
				break;
				case 'barcode' :
					if($data['complex_no']) $data['barcode'] = $pdo->row("select barcode from erp_complex_option where complex_no='$data[complex_no]'");
				break;
				case 'memo' :
					$data['memo'] = '';
					if	($idx < 100) {
						$tmp2 = $pdo->iterator("select content from {$tbl['order_memo']} where ono='$data[ono]'");
							$cnt = 0;
                            foreach ($tmp2 as $tmp3) {
								$cnt++;
								if ($cnt > 1) {
									$data['memo'] .= ",".$tmp3['content'];
								} else {
								    $data['memo'] = $tmp3['content'];
								}
							}
					}
				break;
                case 'is_subscription' :
                    $data['is_subscription'] = ($data['x_order_id'] == 'subscription') ? 'Y' : '';
                break;
                case 'member_group' :
                    if ($data['level']) {
                        $data['member_group'] = getGroupName($data['level']);
                    }
                break;
			}
			$data['idx'] = $idx;
            if (is_null($data[$val]) == true) {
                $data[$val] = '';
            }

			$data[$val] = stripslashes($data[$val]);
            $row[] = $data[$val];
            unset($convert_chk[$val]); //해당 키는 데이터 컨버팅이 완료되었음으로 삭제
		}
        $ExcelWriter->writeSheetRow($row);
        unset($row);

		$_tmp_ono=$data[ono];
		$idx--;

        // 엑셀 다운로드 기록
        if ($NumTotalRec-1 == $idx) {
            addPrivacyViewLog(array(
                'page_id' => 'order',
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
            $use_yn = $pdo->row("select use_yn from {$tbl['cfg_confirm_list']} where code='order_excel'");
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