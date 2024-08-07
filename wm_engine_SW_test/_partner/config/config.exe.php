<?PHP

	$config_code = $_POST['config_code'];

	if($config_code == 'email') {
		$partner_email = addslashes(trim($_POST['partner_email']));
		$partner_email_use = ($_POST['partner_email_use'] == "Y") ? "Y" : "N";
		if(!$partner_email) {
			msg('관리자 메일주소를 입력해주세요', 'reload', 'parent');
		}
		$pdo->query("update $tbl[partner_shop] set partner_email='$partner_email', partner_email_use='$partner_email_use' where no='$admin[partner_no]'");
		msg('', 'reload', 'parent');
	}

	// 국내배송 설정
	if($config_code == 'delivery') {
		$delivery_type = numberOnly($_POST['delivery_type']);
		$delivery_fee = numberOnly($_POST['delivery_fee'], true);
		$dlv_fee2 = numberOnly($_POST['dlv_fee2'], true);
		$dlv_fee3 = numberOnly($_POST['dlv_fee3'], true);
		$delivery_base = numberOnly($_POST['delivery_base'], true);
		$delivery_free_limit = numberOnly($_POST['delivery_free_limit'], true);
		$delivery_free_milage = ($_POST['delivery_free_milage'] == 'Y') ? 'Y' : 'N';
		$delivery_prd_free = ($_POST['delivery_prd_free'] == 'Y') ? 'Y' : 'N';
		$partner_no = numberOnly($admin['partner_no']);

        addField($tbl['partner_delivery'], 'dlv_fee3', 'double(8, 2) not null default 0.00 after dlv_fee2');

		$no = $pdo->row("select no from $tbl[partner_delivery] where partner_no='$partner_no'");
		if($no > 0) {
			$pdo->query("
			update $tbl[partner_delivery] set
				partner_no='$partner_no', delivery_type='$delivery_type', delivery_fee='$delivery_fee', dlv_fee2='$dlv_fee2', dlv_fee3='$dlv_fee3',
				delivery_base='$delivery_base', delivery_free_limit='$delivery_free_limit', delivery_free_milage='$delivery_free_milage', delivery_prd_free='$delivery_prd_free'
				where no='$no'
			");
		} else {
			$pdo->query("
			insert into $tbl[partner_delivery]
				(partner_no, delivery_type, delivery_fee, dlv_fee2, dlv_fee3, delivery_base, delivery_free_limit, delivery_prd_free)
				values ('$partner_no', '$delivery_type', '$delivery_fee', '$dlv_fee2', '$dlv_fee3', '$delivery_base', '$delivery_free_limit', '$delivery_prd_free')
			");
		}
		msg('', 'reload', 'parent');
	}
		if($config_code == 'partner_order') {
			if(!$_POST['auto_stat3']) $_POST['auto_stat3']="";
			if(!$_POST['auto_stat3_2']) $_POST['auto_stat3_2']="";
			if($_POST['product_restore_use'] != ""){
				$_prstat="@";
				foreach($product_restore_stat as $key=>$val){
					$_prstat .= $val."@";
				}
				$_POST['product_restore_stat']=$_prstat;
			}
			if(!$_POST['bank_name2']) $_POST['bank_name2'] = '';
			if(!$_POST['recipient']) $_POST['recipient'] = '';
			if(!$_POST['bank_price']) $_POST['bank_price'] = '';
			if(!$_POST['ord_list_phone']) $_POST['ord_list_phone']='';
			if(!$_POST['ord_list_memo_icon']) $_POST['ord_list_memo_icon']='';
			elseif($_POST['ord_list_memo_icon'] == 'Y' && !fieldExist($tbl['order'], 'memo_cnt')) {
				addField($tbl['order'], "memo_cnt", "int(3) NOT NULL default 0");
				$res = $pdo->iterator("SELECT ono, count(*) as cnt FROM `wm_order_memo` group by ono");
                foreach ($res as $mdata) {
					$pdo->query("update $tbl[order] set `memo_cnt`='$mdata[cnt]' where `ono`='$mdata[ono]'");
				}
			}
			if(!$_POST['ord_list_postpone']) $_POST['ord_list_postpone']='';
			if(!$_POST['ord_list_first_prc']) $_POST['ord_list_first_prc']='';

			foreach($_POST as $key => $val) {
				if(is_array($val)) continue;
				if($key == 'body' || $key == 'config_code' || $key == 'exec') continue;
				$val = addslashes($val);

				if($pdo->row("select count(*) from $tbl[partner_config] where name='$key' and `partner_no` = '$admin[partner_no]'") > 0) {
					$pdo->query("update $tbl[partner_config] set value='$val', edt_date='$now', admin_id='$admin[admin_id]' where name='$key' and `partner_no` = '$admin[partner_no]'");
				} else {
					$pdo->query("insert into $tbl[partner_config] (name, value, reg_date, edt_date, admin_id, partner_no) values ('$key', '$val', '$now', '$now', '$admin[admin_id]', '$admin[partner_no]')");
				}
			}
			msg($cfg_msg, 'reload', 'parent');
		}

	// 배송비 무료일 경우에도 지역별 추가배송비 부과
	if($_POST['free_delivery_area']) {
		$partner_no = numberOnly($admin['partner_no']);
		$free_delivery_area = addslashes($_POST['free_delivery_area']);

		$no = $pdo->row("select no from $tbl[partner_delivery] where partner_no='$partner_no'");
		if($no > 0) {
			$pdo->query("update $tbl[partner_delivery] set free_delivery_area='$free_delivery_area' where no='$no'");
		} else {
			$pdo->query("insert into $tbl[partner_delivery] (partner_no, free_delivery_area) values ('$admin[partner_no]', '$free_delivery_area')");
		}
        if ($_POST['from_ajax'] == 'true') {
            exit;
        } else {
            msg('', 'reload', 'parent');
        }
	}

	if ($config_code == 'sms_config') {
		$partner_sms = addslashes(trim($_POST['partner_sms']));
		$night_sms_start = numberOnly($_POST['night_sms_start']);
		$night_sms_end = numberOnly($_POST['night_sms_end']);
		if(!$partner_sms) {
			msg('입점사 수신번호를 입력해주세요', 'reload', 'parent');
		}
		$pdo->query("update {$tbl['partner_shop']} set partner_sms = '$partner_sms', night_sms_start = '$night_sms_start', night_sms_end = '$night_sms_end' where no='{$admin['partner_no']}'");
		msg('', 'reload', 'parent');
	}

	if($_POST['adddlv_type']) {
		$partner_no = numberOnly($admin['partner_no']);
		$adddlv_type = $_POST['adddlv_type'];

        addField($tbl['partner_delivery'], 'partner_adddlv_type', 'char(1) not null default 1');
		$no = $pdo->row("select no from {$tbl['partner_delivery']} where partner_no = '$partner_no'");
		if ($no > 0) {
		    $pdo->query("update {$tbl['partner_delivery']} set partner_adddlv_type='$adddlv_type' where partner_no='$partner_no'");
		} else {
		    $pdo->query("insert into {$tbl['partner_delivery']} (partner_no, partner_adddlv_type) values ('{$admin['partner_no']}', '$adddlv_type')");
		}

		exit;
	}

    // 주소별 배송 제한 설정
    if ($_POST['dlv_possible_type']) {
        $key = 'dlv_possible_type';
        $val = $_POST['dlv_possible_type'];
        if ($val != 'D' && $val != 'A' &&$val != 'N') {
            msg('사용할 수 없는 설정입니다.');
        }

        $config_exists = $pdo->row("select count(*) from {$tbl['partner_config']} where name=? and partner_no=?", array(
            $key, $admin['partner_no']
        ));
        if($config_exists > 0) {
            $pdo->query("update {$tbl['partner_config']} set value=?, edt_date=?, admin_id=? where name=? and partner_no=?", array(
                $val, $now, $admin['admin_id'], $key, $admin['partner_no']
            ));
        } else {
            $pdo->query("insert into {$tbl['partner_config']} (name, value, reg_date, edt_date, admin_id, partner_no) values (?, ?, ?, ?, ?, ?)", array(
                $key, $val, $now, $now, $admin['admin_id'], $admin['partner_no']
            ));
        }
        msg('', 'reload', 'parent');
    }

?>