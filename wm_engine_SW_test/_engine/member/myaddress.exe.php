<?php

    /* +----------------------------------------------------------------------------------------------+
    ' |  나의주소록 처리
    ' +----------------------------------------------------------------------------------------------+*/

    include_once $engine_dir."/_engine/include/common.lib.php";
    include_once __ENGINE_DIR__.'/_engine/include/MemberAddress.lib.php';

    $exec = addslashes(trim($_POST['exec']));
    $addr_no = numberOnly($_POST['addr_no']);

    if ( $exec == 'delete' ) {
        $chk_addr = $pdo->assoc("SELECT idx, is_default, member_no FROM {$tbl['member_address']} WHERE idx=? AND member_no=?", array($addr_no, $member['no']));
        if ( !$chk_addr['idx'] ) exit(json_encode(array('result'=> false, 'msg'=>'존재하지 않는 배송지 입니다.')));
        if ($chk_addr['is_default'] == 'Y')  exit(json_encode(array('result'=> false, 'msg'=>'기본배송지는 삭제 불가능 합니다.')));
        $pdo->query("delete from {$tbl['member_address']} where idx=?", array(
            $chk_addr['idx']
        ));
        exit(json_encode(array('result'=> true, 'msg'=>'')));
    }

    // 주문완료 후 배송지 변경
    if ( $exec == 'change' ) {
        $ono = addslashes(trim($_POST['ono']));
        $sbono = addslashes(trim($_POST['sbono']));

        if ( $ono ) {
            $no_fd = "ono";
            $order_type = "order";
            $order_product_type = "order_product";
        } else {
            $no_fd = "sbono";
            $order_type = "sbscr";
            $order_product_type = "sbscr_product";
            $ono = $sbono;
        }

        $title = addslashes(html_entity_decode(trim($_POST['title'])));
        $name = addslashes(html_entity_decode(trim($_POST['name'])));
        $zipcode = addslashes(html_entity_decode(trim($_POST['zipcode'])));
        $addr1 = addslashes(html_entity_decode(trim($_POST['addr1'])));
        $addr2 = addslashes(html_entity_decode(trim($_POST['addr2'])));
        $addr3 = addslashes(html_entity_decode(trim($_POST['addr3'])));
        $addr4 = addslashes(html_entity_decode(trim($_POST['addr4'])));
        $nations = addslashes(trim($_POST['nations']));

        $cell = addslashes(trim($_POST['cell']));
        $phone = addslashes(trim($_POST['phone']));
        $addr_add = addslashes(trim($_POST['addr_add']));
        $addr_update = addslashes(trim($_POST['addr_update']));
        $addr_default = addslashes(trim($_POST['addr_default']));

        $_POST['addressee_zip'] = $zipcode;
	    $_POST['addressee_addr1'] = $addr1;

        if (
            isTable($tbl['order_addr_log']) == false
            && $order_type == "order"
        ) {
			include_once $engine_dir.'/_config/tbl_schema.php';
			$pdo->query($tbl_schema['order_addr_log']);

			addField($tbl['order_product'], 'addr_changed', 'enum("N", "Y") default "N"');
			$pdo->query("alter table {$tbl['order_product']} add index addr_changed(addr_changed)");
		}

        if ( $order_type == "order" ) {
            $ord = $pdo->assoc("SELECT * FROM {$tbl['order']} WHERE ono=?", array($ono));
            $ord_dlv_prc = $ord['dlv_prc'];// 현재 배송비
        } else {
            $ord = $pdo->assoc("SELECT * FROM {$tbl['sbscr']} WHERE sbono=?", array($ono));
            $ord_dlv_prc = $pdo->row("SELECT dlv_prc FROM {$tbl['sbscr_schedule']} WHERE sbono=? order by no asc limit 1", array($ono));
        }

        if ( $ord['stat'] > 2 ) {
            exit(json_encode(array('result'=> false, 'msg'=>'배송지 변경이 불가능한 상태입니다.')));
        }

        $ptnOrd = new OrderCart();
        $res = $pdo->iterator("select * from {$tbl[$order_product_type]} where $no_fd=? ", array($ono));
        foreach ($res as $cart) {
            $prd = $pdo->assoc("select code, seller, seller_idx, origin_name, set_rate, qty_rate, m_sell_prc, weight, normal_prc, m_normal_prc, origin_prc, set_sale_prc, set_sale_type, set_each, ea_type, ea, stock_yn, min_ord, max_ord, max_ord_mem, fieldset, ref_prd, big, mid, small, depth4, obig, omid, ebig, mbig, xbig, xmid, xsmall, xdepth4, ybig, ymid, ysmall, ydepth4, rev_cnt, rev_avg, rev_total, top_prd, qna_cnt, hit_view, hit_order, hit_sales, hit_search, hit_cart, hit_wish, event_sale, etc1, keyword, checkout, use_talkpay, use_talkstore, show_mobile, member_sale, coupon, free_delivery, oversea_free_delivery, dlv_alone, tax_free, no_ep, no_milage, no_cpn, gift_use, mng_memo, wm_sc, sortbig, sortmid, sortsmall, sortdepth4, ep_stat, hs_code, name_referer, ts_use, ts_set, ts_dates, ts_datee, ts_names, ts_namee, ts_event_type, ts_saleprc, ts_saletype, ts_cut, ts_state, ts_ing, delivery_set, nstoreId, n_store_check, compare_today_start, import_flag, sell_prc_consultation, sell_prc_consultation_msg, m_content, use_m_content, partner_stat, partner_rate, ori_no, perm_lst, perm_dtl, perm_sch, storage_no from {$tbl['product']} where `no` = ".$cart['pno']);
            if ($prd) $cart = array_merge($cart, $prd);
            $ptnOrd->addCart($cart);
        }
        $ptnOrd->complete();
        $dlv_prc = (int) $ptnOrd->getData('dlv_prc');  // 변경배송비

        if ( $ord_dlv_prc != $dlv_prc ) {
            exit(json_encode(array('result'=> false, 'msg'=>'배송비가 일치하지 않습니다. 고객센터로 문의해 주세요.')));
        }

        if (
            $ord['addressee_name'] == $name
            && $ord['addressee_zip'] == $zipcode
            && $ord['addressee_addr1'] == $addr1
            && $ord['addressee_addr2'] == $addr2
        ) {
            exit(json_encode(array('result'=> false, 'msg'=>'기존 주소와 동일합니다.')));
        }

        if ( $order_type == "order" ) {
            $r = $pdo->query("update {$tbl['order_product']} set r_name=?, r_zip=?, r_addr1=?, r_addr2=?, r_phone=?, r_cell=?, addr_changed='Y' where ono=? ", array($name, $zipcode, $addr1, $addr2, $phone, $cell, $ono));
        }

        $r = $pdo->query("update {$tbl[$order_type]} set addressee_name=?, addressee_zip=?, addressee_addr1=?, addressee_addr2=?, addressee_phone=?, addressee_cell=? where $no_fd=?", array($name, $zipcode, $addr1, $addr2, $phone, $cell, $ono));

        if (
            empty($addr3) == false
            || empty($addr4) == false
        ) {
			$pdo->query("update {$tbl[$order_type]} set addressee_addr3=?, addressee_addr4=?, nations=? where $no_fd=?", array($addr3, $addr4, $nations, $ono));
		}

        if ($r) {
            // 주소록 정보 저장
            if (
                $member['no'] > 0
                && (
                    $addr_add == 'Y'
                    || $addr_update > 0
                )
            ) {
                // 차후 배송지 관리 기능 생기면 삭제하고 '현재 주문 주소를 주소록에 추가' 형태로 변경
                $addr_no = memberAddressSet(
                    $title, 'order', $name, $phone, $cell,
                    $zipcode, $addr1, $addr2, $addr3, $addr4, '', (int) $addr_update, $nations
                );
            }

            // 현재 주소를 기본배송지로 지정
            if (
                $member['no'] > 0
                && !empty($addr_no)
                && isset($_POST['addr_default'])
                && $_POST['addr_default'] == 'Y'
            ) {
                memberAddressDefault($addr_no, $nations);
            }

            $_memo = "주문 후 배송지 변경\n [기존] ".$ord['addressee_zip']." ".$ord['addressee_addr1']." ".$ord['addressee_addr2']."\n[변경] ".$zipcode." ".$addr1." ".$addr2;
            $pdo->query("insert into {$tbl['order_memo']} (admin_no, admin_id, ono, content, reg_date) values ('0', 'system', ?, ?, '$now')", array($ono, $_memo) );

            if( $order_type == "order" ) {
                if (is_object($erpListener)) {
                    $erpListener->setOrder($ord['ono']);
                }
                $pdo->query("
                    insert into {$tbl['order_addr_log']} (ono, opno, org_name, org_zip, org_addr1, org_addr2, org_phone, org_cell, new_name, new_zip, new_addr1, new_addr2, new_phone, new_cell, admin_id, reg_date)
                    values
                    (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, '$now' )
                ", array($ono, '', $ord['addressee_name'], $ord['addressee_zip'], $ord['addressee_addr1'], $ord['addressee_addr2'], $ord['addressee_phone'], $ord['addressee_cell'], $name, $zipcode, $addr1, $addr2, $phone, $cell, 'user' ) );
            }
        }

        exit(json_encode(array('result'=> true, 'addr_no'=>$addr_no)));

    }

    if( $exec == 'setdefault' ) {
        $nations = addslashes(trim($_POST['nations']));
        $is_default = ($_POST['addr_default'] == 'Y') ? 'Y' : 'N';
        if ($is_default) {
            memberAddressDefault($addr_no, $nations);
            exit(json_encode(array('result'=> true)));
        }
        exit(json_encode(array('result'=> false, 'msg'=>'')));
    }
