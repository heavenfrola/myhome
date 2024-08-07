<?php
    include_once $engine_dir.'/_config/set.country.php';

    $address_type = addslashes(trim($_GET['address_type']));  // 노출될 배송지타입값 (나의주소록, 최근배송지, 신규입력)
    $delivery_type = addslashes(trim($_POST['delivery_type']));  // 국내, 해외배송 여부
    $prev_page = addslashes(trim($_POST['prev_page']));
    $addr_no = addslashes(trim($_POST['addr_no']));
    $ono = addslashes(trim($_POST['ono']));
    $sbono = addslashes(trim($_POST['sbono']));

    $ono = ($sbono ? "" : $ono);
    $sbono = ($ono ? "" : $sbono);

    $_replace_code[$_file_name]['address_type_view'] = $address_type;
    $_replace_code[$_file_name]['form_start'] = "<form name=\"nAddrFrm\" id=\"nAddrFrm\" method=\"post\" ><input type=\"hidden\" name=\"ono\" value=\"".$ono."\"><input type=\"hidden\" name=\"sbono\" value=\"".$sbono."\"><input type=\"hidden\" name=\"addr_no\" value=\"".$addr_no."\">";
    $_replace_code[$_file_name]['form_end'] = "";

    switch ($address_type) {
        case '0': // 나의 주소록
            $where = "";
            if( $delivery_type == 'O' ) {
                $where = " AND addr3!='' ";
                $where .= " AND ifnull(nations, '') != '' ";
            } else $where = " AND ifnull(nations, '') = '' ";
            $_tmp = "";
            $_line = getModuleContent('member_address_list');

            $addr_res = memberAddressGet($member, $where, 'order by is_default desc, sort asc');
            foreach ($addr_res as $addr) {
                $addr->title = replaceEntities($addr->title, true);
                $addr->name = replaceEntities($addr->name, true);
                $addr->addr1 = replaceEntities($addr->addr1, true);
                $addr->addr2 = replaceEntities($addr->addr2, true);
                $addr->addr3 = replaceEntities($addr->addr3, true);
                $addr->addr4 = replaceEntities($addr->addr4, true);
                $addr->is_default = ($addr->is_default == 'Y' ? 'Y' : '');
                $addr->is_default_no = ($addr->is_default == 'Y' ? '' : 'Y');
                $addr->checked = ($addr->is_default == 'Y' ? 'checked' : '');
                $_tmp .= lineValues('member_address_list', $_line, (array) $addr);
            }

            $_tmp = listContentSetting($_tmp, $_line);
            $_replace_code[$_file_name]['member_address_list'] = $_tmp;
        break;

        case '1': // 최근배송지
            $where = "";
            $fd = 'addressee_name, addressee_zip, addressee_addr1, addressee_addr2, addressee_cell, addressee_phone';
            if ($delivery_type == "O") {
                $where = " AND ifnull(nations, '') != '' ";
                $fd .= ', addressee_addr3, addressee_addr4, nations ';
            } else $where = " AND ifnull(nations, '') = '' ";

            $addr_res = $pdo->iterator("select distinct $fd from {$tbl['order']} where `stat` != 11 and `member_no`='{$member['no']}' and `member_id`='{$member['member_id']}' $where order by `no` desc limit 5 ");

            $cnt = 0;
            $_tmp = "";
            $_line = getModuleContent('order_lately_addr_list');
            foreach ($addr_res as $addr){
                $cnt++;
                $addr['cnt'] = $cnt;
                $addr['addressee_name'] = replaceEntities($addr['addressee_name'], true);
                $addr['addressee_zip'] = replaceEntities($addr['addressee_zip'], true);
                $addr['addressee_addr1'] = replaceEntities($addr['addressee_addr1'], true);
                $addr['addressee_addr2'] = replaceEntities($addr['addressee_addr2'], true);
                if ($delivery_type == "O") {
                    $addr['addressee_addr3'] = replaceEntities($addr['addressee_addr3'], true);
                    $addr['addressee_addr4'] = replaceEntities($addr['addressee_addr4'], true);
                }
                $_tmp .= lineValues('order_lately_addr_list', $_line, $addr);
            }
            $_tmp = listContentSetting($_tmp, $_line);
            $_replace_code[$_file_name]['order_lately_addr_list'] = $_tmp;
        break;

        case '2': // 신규입력
            if ($delivery_type == "O") {
                $_line_name = "order_new_address_oversea";
            } else {
                $_line_name = "order_new_address";
            }
            $addressinfo = array();

            if ( $addr_no > 0 ) {
                if ($prev_page == 'myaddress') {
                    $addr_res = memberAddressGet($member, "AND idx='$addr_no' ", '');
                    foreach ($addr_res as $addr) {
                        $addressinfo = (array) $addr;
                    }
                    $addressinfo['title'] = replaceEntities($addressinfo['title'], true);
                    $addressinfo['name'] = replaceEntities($addressinfo['name'], true);
                    $addressinfo['addr1'] = replaceEntities($addressinfo['addr1'], true);
                    $addressinfo['addr2'] = replaceEntities($addressinfo['addr2'], true);
                    $addressinfo['addr3'] = replaceEntities($addressinfo['addr3'], true);
                    $addressinfo['addr4'] = replaceEntities($addressinfo['addr4'], true);
                    $addressinfo['addressupdate'] = 'Y';
                } elseif ( $prev_page == 'recent' ) {
                    $where = "";
                    $fd = 'addressee_name, addressee_zip, addressee_addr1, addressee_addr2, addressee_cell, addressee_phone';
                    if($delivery_type == "O") {
                        $where = "AND addressee_addr3!='' ";
                        $where .= "AND ifnull(nations, '') != '' ";
                        $fd .= ', addressee_addr3, addressee_addr4, nations ';
                    } else $where = " AND ifnull(nations, '') = '' ";
                    $addr_res = $pdo->iterator("select distinct $fd from {$tbl['order']} where `stat` != 11 and `member_no`='{$member['no']}' and `member_id`='{$member['member_id']}' $where order by `no` desc limit 5 ");
                    $cnt = 0;
                    foreach ($addr_res as $addr) {
                        $cnt++;
                        $addr['cnt'] = $cnt;
                        if ($addr_no == $cnt) {
                            $addressinfo['no'] = $cnt;
                            $addressinfo['title'] = replaceEntities($addr['addressee_name'], true);
                            $addressinfo['name'] = replaceEntities($addr['addressee_name'], true);
                            $addressinfo['phone'] = $addr['addressee_phone'];
                            $addressinfo['cell'] = $addr['addressee_cell'];
                            $addressinfo['zip'] = replaceEntities($addr['addressee_zip'], true);
                            $addressinfo['addr1'] = replaceEntities($addr['addressee_addr1'], true);
                            $addressinfo['addr2'] = replaceEntities($addr['addressee_addr2'], true);
                            $addressinfo['addr3'] = replaceEntities($addr['addressee_addr3'], true);
                            $addressinfo['addr4'] = replaceEntities($addr['addressee_addr4'], true);
                        }
                    }
                    $addressinfo['addressadd'] = 'Y';
                }
            } else {
                $addressinfo['addressadd'] = 'Y';
            }

            $_line = getModuleContent($_line_name);
            $_tmp = lineValues($_line_name, $_line, $addressinfo);
            $_tmp = listContentSetting($_tmp, $_line);
            $_replace_code[$_file_name][$_line_name] = $_tmp;
        break;
    }

    if (
        (
            $cfg['delivery_fee_type'] == 'A'
            && $delivery_type == 'O'
        )
        ||
        $cfg['delivery_fee_type'] == 'O'
    ) {
		// 국가 및 국가 번호
		$_tmp = "<option value=\"\">:: ".__lang_order_info_nations__." ::</option>\n";
        $_tmp_p = $nations_id = $delivery_com_id = "";
		$_nations_arr = getDeliveryPossibleCountry(); //배송 가능한 국가만(해외배송 업체에 세팅된 국가만)
        $nations_name = ($address_type == '2') ? 'n_nations' : 'nations';
        $delivery_com_name = ($address_type == '2') ? 'n_delivery_com' : 'delivery_com';
		foreach ($_nations_arr as $k => $v) {
			$_tmp .= "<option value='${v['code']}' data-phone='${v['phone']}'>${v['name']}</option>\n";
			$_tmp_p .= "<option value='${v['phone']}'> +${v['phone']}</option>\n";
		}

		$_replace_code[$_file_name]['delivery_nations'] = "<select name='$nations_name' onchange=\"onChangePhoneCode(this);getIntShipping(this, '$cart_weight',document.all.delivery_com);\">$_tmp</select>";
		$_replace_code[$_file_name]['order_oversea_phone'] = "<select name='addressee_phone_code'>$_tmp_p</select>";
		$_replace_code[$_file_name]['order_oversea_cell'] = "<select name='addressee_cell_code'>$_tmp_p</select>";

		// 해외배송업체
		$_tmp_arr = getOverseaDeliveryComList();
		$_tmp = "<option value=\"\">:: ".__lang_order_info_delivery_com__." ::</option>\n";

		if ($_tmp_arr['cnt'] > 1) {
			foreach ($_tmp_arr['list'] as $k => $v) {
				$_tmp .= "<option value='${v['no']}'>${v['name']}</option>\n";
			}
			$_replace_code[$_file_name]['delivery_com_list'] = "<select name='$delivery_com_name' onchange=\"getIntShipping(document.all.nations, '$cart_weight',this);\">$_tmp</select>";
		} else {
			$_replace_code[$_file_name]['delivery_com_list'] = "<input type='hidden' value='".$_tmp_arr['list'][0]['no']."' name='$nations_name'/>";
			$_replace_code[$_file_name]['delivery_com_display'] = "style=\"display:none;\"";
		}
	}
