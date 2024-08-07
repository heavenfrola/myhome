<?PHP

	$pno = numberOnly($_GET['pno']);

	if($pno) {
		$data = $pdo->assoc(sprintf("select * from `%s` where `no`='%d'", $tbl['product'], $pno));
		if(!$data['no']) msg('삭제되었거나 만료된 상품데이터입니다.', 'back');
		$data = array_map('stripslashes', $data);
        if (empty($data['prd_type']) == true) {
            $data['prd_type'] = '1';
        }

        if (is_null($_SESSION['partner_login_no']) == true && $admin['level'] == '4' && $admin['partner_no'] != $data['partner_no']) {
            msg('접근 권한이 없습니다.', 'back');
        }

		if($data['content2'] == 'wm_sc') {
			msg('', './?body='.$body.'&pno='.$data['content1']);
		}

		// 세트상품 체크
		if ($body == 'product@product_register' && ($data['prd_type'] == '4' || $data['prd_type'] == '5' || $data['prd_type'] == '6')) {
			msg('', '?body=product@set_register&pno='.$data['no']);
		} elseif ($body == 'product@set_register' && $data['prd_type'] == '1') {
			msg('', '?body=product@product_register&pno='.$data['no']);
		}
		if ($data['prd_type'] == '4' || $data['prd_type'] == '5') { // 세트 가격 갱신
			$data = array_merge($data, getSetPrice($pno, true));
		}
        if ($data['prd_type'] == '6') {
            $set_rate = json_decode($data['set_rate']);
            foreach ($set_rate->data as $key => $val) {
                $set_pick_ea = $key;
            }
        }
		if(isset($data['set_sale_prc']) == true && $data['set_sale_type'] == 'm') {
			$data['set_sale_prc'] = parsePrice($data['set_sale_prc']);
		}

		if(($data['stat'] == 1 && !$data['ori_no']) || !$data['stat']) {
			$data['stat'] = 2;
			if($data['ea_type'] == '1' && isWingposStock($data['no']) < 1) $data['stat'] = 3;
		}
		$stat = $data['stat'];
		$hash = $data['hash'];
		$data['sell_prc'] = parsePrice($data['sell_prc']);
		$data['normal_prc'] = parsePrice($data['normal_prc']);
		$use_ts_set = ($data['ts_set'] > 0) ? 'Y' : 'N';

		if($data['ts_dates']) list($ts_dates1, $ts_dates2) = explode(' ', date('Y-m-d H', $data['ts_dates']));
		if($data['ts_datee']) list($ts_datee1, $ts_datee2) = explode(' ', date('Y-m-d H', $data['ts_datee']));

		// 파트너 수정시 임시데이터
		if($cfg['partner_prd_accept'] == 'Y' && $data['partner_stat'] == 1) {
			$req_data = $pdo->assoc("select stat, content, content2 from $tbl[partner_product_log] where pno='$pno' and stat=1 order by no desc");
			$partner_cmt = stripslashes($req_data['content']);
			$manager_cmt = stripslashes($req_data['content2']);
			$req_stat = $req_data['stat'];
		}
		$prd_type = $data['prd_type'];

        if ($data['ts_use'] == 'Y' && $data['ts_datee'] == 0) {
            $ts_unlimited = 'checked';
            $data['ts_datee'] = $data['ts_dates'];
        } else {
            $ts_unlimited = '';
        }
	} else {
		$stat = '1'; // 등록전 임시

		$pno = $pdo->row("select max(`no`) from `".$tbl['product']."`");
		$pno++;

		$hash = strtoupper(md5($pno));
		if($pdo->row("select count(*) from `".$tbl['product']."` where `hash` = '$hash'")) $hash = strtoupper(md5(time()));

		$pdo->query("insert into `".$tbl['product']."` ( `no` , `hash` , `stat` , `reg_date` , `prd_type`) VALUES ('$pno', '$hash', '1', '$now', '1')");
		if ($cfg['use_partner_shop'] == 'Y' && $admin['level'] == 4) {
			$pdo->query("update $tbl[product] set partner_no='{$admin['partner_no']}' where no='{$pno}'");
		}
		$data['stat'] = 2;
		$data['ea_type'] = ($cfg['basic_ea_type']) ? $cfg['basic_ea_type'] : 2;
		$data['content3_default'] = $data['content4_default'] = $data['content5_default'] = 'Y';
		$data['checkout'] = 'Y';
		$data['use_talkpay'] = 'Y';
		$data['dlv_type'] = '0';

		$use_ts_set = 'N';
	}

	if (empty($data['prd_type']) == true) $data['prd_type'] = '1';
	if (isset($prd_type) == false) $prd_type = '1';
    if (isset($data['dlv_type']) == true && strlen($data['dlv_type']) == 0) $data['dlv_type'] = '0';
    if (isset($data['no_ep']) == false || $data['no_ep'] != 'Y') $data['no_ep'] = 'N';
    if (empty($data['sub_use']) == true) $data['sub_use'] = 'N';
    if (empty($data['ts_use']) == true) $data['ts_use'] = 'N';
	//trncPrd(24);

	if($admin['level'] == 4) $req_stat = 1;

	// 기획전 선택 목록
	if ($code_type) {
		$_ebig = explode("@", $data['ebig']);
	} else {
		$_ebig2 = $pdo->iterator("select nbig from {$tbl['product_link']} where pno = '{$pno}' and ctype = '2'");
		foreach ($_ebig2 as $__ebig) {
			$_ebig[] = $__ebig['nbig'];
		}
	}
	$ebig_str = "<ul class='ebig_strx'>";
	$ebig_res = $pdo->iterator("select * from `$tbl[category]` where `ctype`='2' order by `sort`");
    foreach ($ebig_res as $ebig) {
		if (is_array($_ebig)) {
			$ce = (in_array($ebig['no'], $_ebig)) ? 'checked' : '';
		} else {
			$ce = array();
		}
		$ebig['name'] = strip_tags(stripslashes($ebig['name']));
		$ebig_str .= "<li><label class=\"p_cursor\"><input type=\"checkbox\" name=\"ebig[]\" value=\"$ebig[no]\" $ce> $ebig[name]</label></li>";
	}
	$ebig_str .= "</ul>";

	// 모바일 기획전 선택 목록
	if($cfg['mobile_use'] == 'Y') {
		$mbig_str = "<ul class='ebig_strx'>";
		if ($code_type) {
			$_mbig = explode("@", $data['mbig']);
		} else {
		    $_mbig2 = $pdo->iterator("select nbig from {$tbl['product_link']} where pno = '{$pno}' and ctype = '6'");
			foreach ($_mbig2 as $__mbig) {
				$_mbig[] = $__mbig['nbig'];
			}
		}
		$mbig_res = $pdo->iterator("select * from `$tbl[category]` where `ctype`='6' order by `sort`");
        foreach ($mbig_res as $mbig) {
			if (is_array($_mbig)) {
				$ce = (in_array($mbig['no'], $_mbig)) ? 'checked' : '';
			} else {
			    $ce = array();
			}
			$mbig['name'] = strip_tags(stripslashes($mbig['name']));
			$mbig_str .= "<li><label class=\"p_cursor\"><input type=\"checkbox\" name=\"mbig[]\" value=\"$mbig[no]\" $ce> $mbig[name]</label></li>";
		}
		$mbig_str .= "</ul>";
	}

	for($ii=3; $ii<=5; $ii++) {
		if($data['content'.$ii]=="wisamall_default") {
			$data['content'.$ii.'_default']="Y";
			$data['content'.$ii]="";
		}
	}

	if($data['storage_no'] > 0) {
		$storage = $pdo->assoc("select big, mid, small, depth4 from $tbl[erp_storage] where no='$data[storage_no]'");
		$data['sbig'] = $storage['big'];
		$data['smid'] = $storage['mid'];
		$data['ssmall'] = $storage['small'];
		$data['sdepth4'] = $storage['depth4'];
	}

	foreach(array(1, 4, 5, 9) as $ct) {
		if($ct == 1 || $ct == 9 || $_use[$_cate_colname[$ct][1]] == 'Y') {
			$cw = '';
			for($i = 1; $i < $cfg['max_cate_depth']; $i++) {
				$val = numberOnly($data[$_cate_colname[$ct][$i]]);
				if($val) $cw .= " or (`level`='".($i+1)."' and {$_cate_colname[1][$i]}='$val')";
			}
			$sql = $pdo->iterator("select no, name, ctype, level from $tbl[category] where ctype='$ct' and (level='1' $cw) order by level, sort");
            foreach ($sql as $cate) {
				$cl = $_cate_colname[$ct][$cate['level']];
				$sel = ($data[$cl] == $cate['no']) ? 'selected' : '';
				${'item_'.$cate['ctype'].'_'.$cate['level']} .= "\n<option value='$cate[no]' $sel>".stripslashes($cate['name'])."</option>";
			}
		}
	}

	$pslrow = $pdo->row("select count(*) from `$tbl[product_stat_log]` where `pno`='$data[no]' order by `no`");

	// 추가항목 가져오기
	$_fieldsets = array();
	$cres = $pdo->iterator("select no, name from $tbl[category] where ctype='3' order by sort asc");
    foreach ($cres as $fdata) {
		$_fieldsets[$fdata['no']] = stripslashes($fdata['name']);
	}
	include 'product_field_inc.exe.php';

    if ($scfg->comp('use_talkpay', 'Y') == true) {
        if (isTable($tbl['product_talkstore_announce']) == false) {
            include_once $engine_dir.'/_config/tbl_schema.php';
            $pdo->query($tbl_schema['product_talkstore_announce']);
        }
        if (isTable($tbl['kakaopaybuy_info']) == false) {
            include_once $engine_dir.'/_config/tbl_schema.php';
            $pdo->query($tbl_schema['kakaopaybuy_info']);
        }

        $kakao_info = $pdo->assoc("select * from {$tbl['kakaopaybuy_info']} where pno='$pno'");

        $_kakao_annoucements = array();
        $tres = $pdo->iterator("select idx, title from {$tbl['product_talkstore_announce']} order by idx asc");
        foreach ($tres as $tdata) {
            $_kakao_annoucements[$tdata['idx']] = stripslashes($tdata['title']);
        }
    }

	if($cfg['add_prd_img'] < 3) $cfg['add_prd_img'] = 3;
	for($i = 1; $i <= $cfg['add_prd_img']; $i++) {
		if(!$data['upfile'.$i]) $no_img[$i] = 'hidden';
	}

	// 입점사 정보
	if($cfg['use_partner_shop'] == 'Y') {
		if($admin['level'] == 4){
			$tmpasql = " and no='$admin[partner_no]'";
			$data['partner_no'] = $admin['partner_no'];
		}
		$_partners = $_partner_rate = array();
		$pres = $pdo->iterator("select no, corporate_name, partner_rate from $tbl[partner_shop] where `stat` between 2 and 4 $tmpasql order by corporate_name asc");
        foreach ($pres as $pdata) {
			$_partners[$pdata['no']] = stripslashes($pdata['corporate_name']);
			if($data['partner_no'] == $pdata['no']) {
				$tmp = explode(',', $pdata['partner_rate']);
				foreach($tmp as $val) {
					$val = numberOnly($val, true);
					if (strlen($val) > 0) {
						$_partner_rate[] = $val;
						$sel = ($data['partner_rate'] == $val) ? "selected" : "";
						$partner_rate_select .= "<option value='$val' $sel>".$val."</option>";
					}
				}
			}
		}
		unset($tmpasql, $pres, $pdata, $tmp, $val);
	}
	$_sellers = array();
	$pres = $pdo->iterator("select * from $tbl[provider]");
    foreach ($pres as $pdata) {
		$_sellers[$pdata['no']] = stripslashes($pdata['provider']);
	}

	// 오픈마켓 정보
	if($cfg['opmk_api']) {
		$opmkres = $pdo->iterator("select a.name, a.api_code, b.sell_prc from $tbl[openmarket_cfg] a left join (select sell_prc, api_code from $tbl[product_openmarket] where pno='$pno') b using(api_code) where a.is_active='Y' order by a.sort asc");
		$has_opmks = $opmkres->rowCount();

		function parseOPMK($res) {
			$data = $res->current();
            $res->next();
			if ($data == false) return false;

			if($data['is_active'] != 'Y') $data['is_active'] = 'N';

			return $data;
		}
	}

	if($data['ts_dates']) {
		list($ts_dates, $ts_times, $ts_mins) = explode(' ', date('Y-m-d H i', $data['ts_dates']));
	}
	if($data['ts_datee']) {
		list($ts_datee, $ts_timee, $ts_mine) = explode(' ', date('Y-m-d H i', $data['ts_datee']));
	}
	if(empty($data['ts_event_type']) == true) {
		$data['ts_event_type'] = '1';
	}
	if(empty($data['ts_cut']) == true) {
		$data['ts_cut'] = '1';
	}
	$ts_field_exists = (fieldExist($tbl['product'], 'ts_set') == true) ? true : false;

	// 정기배송 세트
	if($cfg['use_sbscr']=='Y') {
		$spdata = $pdo->assoc("select * from $tbl[sbscr_set_product] where pno='$pno'");
		if($spdata['no']) {
			$data['sub_use'] = $spdata['use'];
			$data['setno'] = $spdata['setno'];
			$data['sub_ea'] = $spdata['ea'];
			$data['sub_percent'] = $spdata['percent'];
		}
		$_sub_set = array();
		$sres = $pdo->iterator("select * from $tbl[sbscr_set] order by no");
        foreach ($sres as $sdata) {
			$_sub_set[$sdata['no']] = stripslashes($sdata['name']);
		}
	}

	$data['perm_lst'] = ($data['perm_lst'] == 'N') ? 'N' : 'Y';
	$data['perm_dtl'] = ($data['perm_dtl'] == 'N') ? 'N' : 'Y';
	$data['perm_sch'] = ($data['perm_sch'] == 'N') ? 'N' : 'Y';

	// 상품별 배송타입
	if($data ['free_delivery'] == 'Y') $delivery_type = 'free_delivery';
	if(isset($cfg['use_prd_dlvprc']) == true && $cfg['use_prd_dlvprc'] == 'Y') {
		$_delivery_sets = array();
		if($data ['delivery_set'] > 0) $delivery_type = 'product';
		$tres = $pdo->iterator("select * from {$tbl['product_delivery_set']} where partner_no='{$admin['partner_no']}' or no='{$data ['delivery_set']}' order by set_name asc");
        foreach ($tres as $tset) {
			$_delivery_sets[$tset['no']] = stripslashes($tset['set_name']);
		}
	}
	if(isset($delivery_type) == false) $delivery_type = 'basic';

	// 타임세일 세트정보
	$_ts_set = array();
	if(isTable($tbl['product_timesale_set']) == true) {
		$tres = $pdo->iterator("select no, name, ts_dates, ts_datee from {$tbl['product_timesale_set']} where ts_use='Y' order by ts_dates asc");
        foreach ($tres as $tdata) {
            if($tdata['ts_datee'] == '0000-00-00 00:00:00') $tdata['name'] = '[무제한] '.$tdata['name'];
            else {
                if(strtotime($tdata['ts_datee']) < $now) $tdata['name'] = '[종료] '.$tdata['name'];
                if(strtotime($tdata['ts_dates']) > $now) $tdata['name'] = '[시작전] '.$tdata['name'];
            }
			$_ts_set[$tdata['no']] = stripslashes($tdata['name']);
		}
		if($data['ts_set'] > 0) {
			$ts_set = $pdo->assoc("select * from {$tbl['product_timesale_set']} where no='{$data['ts_set']}'");
			$ts_set['desc'] = parsePrice($ts_set['ts_saleprc'], true)
				.($ts_set['ts_saletype'] == 'percent' ? '%' : $cfg['currency_type']).' '
				.($ts_set['ts_event_type'] == '1' ? '할인' : '적립');

			$ts_set['ts_dates'] = date('Y-m-d H:i', strtotime($ts_set['ts_dates']));
			$ts_set['ts_datee'] = ($ts_set['ts_datee'] > 0) ? date('Y-m-d H:i', strtotime($ts_set['ts_datee'])) : '무제한';
            $ts_set['ts_state_str'] = ($ts_set['ts_state'] == 2 || $ts_set['ts_state'] == 0) ? '변경 없음' : $_prd_stat[$ts_set['ts_state']];
		}
	}

    // standard 등급 업그레이드 안내
    $service_upgrade_info = '';
    if ($asvcs[0]->mall_goods_idx[0] == '3' || $asvcs[0]->mall_goods_idx[0] == '4' || $asvcs[0]->use_cdn[0] != 'Y') {
        $mall_goods_pro_img = filesizeStr($asvcs[0]->mall_goods_pro_img[0]*1024);
        $service_upgrade_info = '
        <a href="#" class="tooltip_trigger" data-child="tooltip_pro-master"></a>
        <div class="info_tooltip tooltip_pro-master" style="white-space:nowrap">
            Pro, Master 요금제 이용 시, 1장 당 최대 '.$mall_goods_pro_img.'까지 업로드할 수 있습니다.
            <a href="https://www.wisa.co.kr/products/pricing" target="_blank">자세히보기</a>
        </div>
        ';
    }

?>