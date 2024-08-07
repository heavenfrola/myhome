<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  주문완료
	' +----------------------------------------------------------------------------------------------+*/

	$_replace_code[$_file_name]['order_no']=$ord['ono'];
	$_replace_code[$_file_name]['order_bemail']=$ord['buyer_email'];
	$_replace_code[$_file_name]['order_gift_form_start']="<form method=\"post\" action=\"".$root_url."/main/exec.php\" target=\"hidden".$now."\" onSubmit=\"return checkOrdGift(this)\" style=\"margin:0px\">
	<input type=\"hidden\" name=\"exec_file\" value=\"order/order_gift_select.exe.php\">";
	$_replace_code[$_file_name]['order_gift_form_end']="</form>";
	
	if ($sbscr != 'Y') {
		$_tmp="";
		$_line=getModuleContent("order_gift_list");

		$order_gift_cnt = $pdo->row("select count(*) from {$tbl['order']} where member_no='{$member['no']}' and ono!='{$ord['ono']}' and stat < 6");

		// 쿠폰 목록
		while($gift=ordGift()){
			if($gift['order_gift_member'] == "Y" && !$member['no']) continue;
			if($gift['order_gift_first'] == 'Y' && $member['no'] > 0) {
				if(0 < $order_gift_cnt) continue;
			}
			$_tmp .= lineValues("order_gift_list", $_line, $gift);
		}
	}

	// 주문 상품 리스트
	if($ord['ono']) {
        $_sale_fd = getOrderSalesField('o', '-');
        if ($_sale_fd) $_sale_fd = '-'.$_sale_fd;

		if($sbscr=='Y') {
			$tbl_where = " and o.`sbono`='$ord[ono]'";
			$tbl_op = $tbl['sbscr_product'];
		}else {
			$tbl_where = " and o.`ono`='$ord[ono]'";
			$tbl_op = $tbl['order_product'];
		}
		$sql = "select p.*, o.*, p.no as no, p.sell_prc as sell_prc FROM `$tbl_op` o inner join $tbl[product] p on o.pno=p.no where 1 $tbl_where";
        if ($sbscr != 'Y' && $scfg->comp('use_set_product', 'Y') == true) {
            $sql .= " order by o.set_idx asc, o.no asc";
        }
		$res = $pdo->iterator($sql);

		$_ord_lsit = getModuleContent("order_finish_prd_list");
		if(!$_skin['order_finish_product_img_fd']) $_skin['order_finish_product_img_fd'] = 3;
		if(!$_skin['order_finish_prd_list_imgw']) $_skin['order_finish_prd_list_imgw'] = 80;
		if(!$_skin['order_finish_prd_list_imgh']) $_skin['order_finish_prd_list_imgh'] = 80;

		$_mkt_data_list = array();
        foreach ($res as $oprd) {
			$_mkt_data_list[] = $oprd;
		}
		foreach($_mkt_data_list as $_midx => $pdata) {
			$pdata = prdOneData($pdata, $_skin['order_finish_prd_list_imgw'], $_skin['order_finish_prd_list_imgh'], $_skin['order_finish_product_img_fd']);
			$pdata['nidx'] = $_midx;
			$pdata['milage'] = parsePrice($pdata['total_milage'], true);
			$pdata['total_prc'] = parsePrice($pdata['total_prc']-getOrderTotalSalePrc($pdata), true);
			$pdata['option'] = str_replace('<split_big>', '<br>', str_replace('<split_small>', ' : ', $pdata['option']));

            // 세트 메인 상품
            if ($pdata['set_idx'] && $_set_div != $pdata['set_idx']) {
                $_setprd = prdOneData(
                    shortCut($pdo->assoc("
                        select
                            p.name, p.hash,
                            p.updir, p.upfile{$_skin['order_finish_product_img_fd']}, p.w{$_skin['order_finish_product_img_fd']}, p.h{$_skin['order_finish_product_img_fd']},
                            sum(total_prc $_sale_fd) as total_prc, sum(total_milage) as total_milage
                        from {$tbl['product']} p inner join {$tbl['order_product']} o on p.no=o.set_pno where p.no='{$pdata['set_pno']}' and o.ono='{$ord['ono']}' and o.set_idx='{$pdata['set_idx']}'
                    ")),
                    $_skin['order_finish_prd_list_imgw'],
                    $_skin['order_finish_prd_list_imgh'],
                    $_skin['order_finish_product_img_fd']
                );
                $_setprd['total_prc'] = parsePrice($_setprd['total_prc'], true);
                $_setprd['milage'] = parsePrice($_setprd['total_milage'], true);
                $_setprd['buy_ea'] = '';
                $_setprd = parseUserCart($_setprd);

    			$_ord_prd_list .=  lineValues("order_finish_prd_list", $_ord_lsit[5], $_setprd);
                $_set_div = $pdata['set_idx'];
            }
			$_ord_prd_list .=  lineValues("order_finish_prd_list", ($pdata['set_idx'] && $_ord_lsit[6]) ? $_ord_lsit[6] : $_ord_lsit[2], $pdata);
		}

		$_ord_prd_list = listContentSetting($_ord_prd_list, $_ord_lsit);
		$_replace_code[$_file_name]['order_finish_prd_list'] = $_ord_prd_list;
	}

	$_tmp=listContentSetting($_tmp, $_line);
	$_replace_code[$_file_name]['order_gift_list']=$_tmp;
	// 2009-09-25 : 은행 계좌 정보 - Han
	if($ord['pay_type'] == 4){ // 무통장 에스크로일 경우 계좌번호를 vbank 에서 가져오기
		$_order_account=$sms_replace['account'] ? $sms_replace['account'] : $pdo->row("select concat(`bankname`,' ',`account`) from `$tbl[vbank]` where `wm_ono` = '$ord[ono]'");
	}elseif($ord['pay_type'] == 2 || $ord['pay_type'] == 17) {
		 $_order_account=$ord['bank'];
	}else{
		 $_order_account="";
	}
	$_replace_code[$_file_name]['order_account'] = trim($_order_account);
	if($cfg['banking_time'] > 0) {
		if($cfg['banking_time_std'] == 'time') {
			$_bank_limit = strtotime("+$cfg[banking_time] days +1 hours", strtotime(date('Y-m-d H:00:00')));
		} else {
			$_bank_limit = strtotime("+$cfg[banking_time] days", strtotime(date('Y-m-d 23:59:59')));
		}

		$_replace_code[$_file_name]['order_bank_limit'] = date('Y-m-d H:i', $_bank_limit);
	}

	// 2011-01-05 : 주문서 금액 정보 추가 - Han
	$_replace_code[$_file_name]['order_cart_sum'] = parsePrice($ord['prd_prc'], true);
	$_replace_code[$_file_name]['order_r_cart_sum'] = showExchangeFee($ord['prd_prc']);
	$_replace_code[$_file_name]['order_delivery_fee'] = parsePrice($ord['dlv_prc'], true);
	$_replace_code[$_file_name]['order_r_delivery_fee'] = showExchangeFee($ord['dlv_prc']);
	$_replace_code[$_file_name]['tax_prc'] = $ord['tax'] > 0?parsePrice($ord['tax'],true):'';
	$_replace_code[$_file_name]['tax_r_prc'] = showExchangeFee($ord['tax']);
	$_replace_code[$_file_name]['order_order_sum'] = parsePrice($ord['pay_prc'], true);
	$_replace_code[$_file_name]['order_r_order_sum'] = showExchangeFee($ord['pay_prc']);
	$_replace_code[$_file_name]['total_prc'] = parsePrice($ord['total_prc'], true);
	$_replace_code[$_file_name]['total_r_prc'] = showExchangeFee($ord['total_prc']);

    $_replace_code[$_file_name]['order_finish_date'] = date('Y년 m월 d일', $ord['date1']);

    $ord_finish_addr = array();
    $ord_finish_addr['addressee_name'] = $ord['addressee_name'];
    $ord_finish_addr['addressee_phone'] = $ord['addressee_phone'];
    $ord_finish_addr['addressee_cell'] = $ord['addressee_cell'];
    $ord_finish_addr['addressee_zip'] = $ord['addressee_zip'];
    $ord_finish_addr['addressee_addr1'] = $ord['addressee_addr1'];
    $ord_finish_addr['addressee_addr2'] = $ord['addressee_addr2'];
    $ord_finish_addr['addressee_addr3'] = $ord['addressee_addr3'];
    $ord_finish_addr['addressee_addr4'] = $ord['addressee_addr4'];
    $ord_finish_addr['dlv_memo'] = $ord['dlv_memo'];
    $ord_finish_addr['dlv_addr_change'] = ( $ord['stat'] <= 2 ? 'openOrderAddress(0);' : "");

    $_line = getModuleContent('order_finish_address');
    $_tmp = lineValues('order_finish_address', $_line, $ord_finish_addr);
    $_tmp = listContentSetting($_tmp, $_line);
    $_replace_code[$_file_name]['order_finish_address'] = $_tmp;
    unset($_line, $tmp);

    $ord['pay_type_str'] = (defined('__lang_order_info_paytype'.$ord['pay_type'].'__')) ? constant('__lang_order_info_paytype'.$ord['pay_type'].'__') : $_pay_type[$ord['pay_type']];
    $_replace_code[$_file_name]['pay_type_str'] = nl2br($ord['pay_type_str']);

    if($ord['pay_type'] == '1' || $ord['pay_type'] == '5' || $ord['pay_type'] == '12') {
        $card = get_info($tbl['card'], "wm_ono", $ono);
        if($card['quota'] == "00" || empty($card['quota']) == true) $card['quota_str'] = __lang_mypage_info_paystyle1__;
        else $card['quota_str'] = sprintf(__lang_mypage_info_paystyle2__, $card['quota']);
    }
    elseif($ord['pay_type'] == 4) {
        $card = get_info($tbl['vbank'], "wm_ono", $ono);
        $ord['bank'] = trim($card['bankname'].' '.$card['account'].' '.$card['depositor']);
    }

    if($ord['pay_type'] == "2" || $ord['pay_type'] == "4") {
        if (!$ord['bank']) {
            $ord2 = $pdo->assoc("select bankname, account from wm_vbank where wm_ono = '{$ord['ono']}'");
            $ord['bank'] = trim($ord2['bankname'].' '.$ord2['account']);
        }
        $ord['bank_limit'] = ($_bank_limit ? date('Y-m-d H:i', $_bank_limit) : "");
        $_line = getModuleContent("order_finish_ord_cash");
        $_replace_code[$_file_name]['order_finish_ord_cash'] = lineValues("order_finish_ord_cash", $_line, $ord);
    }elseif($ord['pay_type'] == "1" || $ord['pay_type'] == "5") {
        if($card['stat'] != '1') {
            $_line = getModuleContent("order_finish_ord_card");
            // 영수증정보
            if($cfg['card_pg'] == "kcp") $card['receipt'] = "<a href=\"javascript:;\" onclick=\"kcpCardReceipt('".$card['tno']."');\">거래영수증</a>";
            elseif($cfg['card_pg'] == "inicis") $card[receipt]="<a href=\"javascript:;\" onclick=\"inicisCardReceipt('".$card['tno']."');\">거래영수증</a>";
            elseif($cfg['card_pg'] == "dacom") {
                $card['authdata'] = md5($cfg['card_dacom_id'].$card['tno'].$cfg['card_dacom_key']);
                $card['receipt'] = "<script type='text/javascript' src=\"https://pgweb.dacom.net/WEB_SERVER/js/receipt_link.js\"></script><a href=\"javascript:;\" onclick=\"showReceiptByTID('".$cfg['card_dacom_id']."','".$card['tno']."','".$card['authdata']."');\">거래영수증</a>";
            }
            elseif($cfg['card_pg'] == 'kspay') {
                $card['receipt'] = "<a href='#' onclick='ksnetReceipt(\"$card[tno]\"); return false;'>거래영수증</a>";
                $card['receipt'] .= "<script type='text/javascript'>function ksnetReceipt(tr_no) {var receiptWin='http://pgims.ksnet.co.kr/pg_infoc/src/bill/credit_view.jsp?tr_no='+tr_no;window.open(receiptWin, '' , 'scrollbars=no,width=434,height=640');}</script>";
            }
            elseif($cfg['card_pg'] == 'allat') {
                $allat_id = $data['mobile'] == 'Y' ? $cfg['mobile_card_partner_id'] : $cfg['card_partner_id'];
                if($ord['pay_type'] == 4 || $ord['pay_type'] == 5) {
                    $bank_pay_method = ($data['pay_type'] == 5) ? 'ABANK' : 'VBANK';
                    $rcpt_link = "http://www.allatpay.com/servlet/AllatBizPop/member/pop_tx_receipt.jsp?shop_id=$allat_id&order_no=$ono&pay_type=$bank_pay_method";
                } else if($ord['pay_type'] != 7) {
                    $rcpt_link = "http://www.allatpay.com/servlet/AllatBizPop/member/pop_card_receipt.jsp?shop_id=$allat_id&order_no=$ono";
                }
                if($rcpt_link) {
                    $card['receipt'] = "<a href='#' onclick=\"window.open('$rcpt_link', 'allat_rcpt', 'status=no'); return false;\">거래영수증</a>";
                }
            }
            elseif($card['pg'] == 'nicepay') {
                $rcpt_link = 'https://npg.nicepay.co.kr/issue/IssueLoader.do?TID='.$card['tno'].'&type=0';
                $card['receipt'] = "<a href='#' onclick=\"window.open('$rcpt_link', 'nicepay_rcpt', 'status=no'); return false;\">거래영수증</a>";
            }
            $_replace_code[$_file_name]['order_finish_ord_card'] = lineValues("order_finish_ord_card", $_line, $card);
        }
    } else if($ord['pay_type'] == 12) {
        $hash = $cfg['kakao_cid'].$card['tno'].$ord['ono'].($ord['member_no'] > 0 ? $ord['member_id'] : $card['guest_no']);
        $hash = bin2hex(hash('sha256', $hash, true));
        $d_type = ($ord['mobile'] == 'Y') ? 'm' : 'p';
        $rcpt_link = "https://pg-web.kakao.com/v1/confirmation/{$d_type}/{$card['tno']}/{$hash}";
        $card['receipt'] = "<a href='#' onclick=\"window.open('$rcpt_link', 'kakaopay_rcpt', 'status=no, width=500px, height=800px'); return false;\">결제영수증</a>";

        $_line = getModuleContent('order_finish_ord_card');
        $_replace_code[$_file_name]['order_finish_ord_card'] = lineValues('order_finish_ord_card', $_line, $card);
    } else if($ord['pay_type'] == 17) {
        $_line = getModuleContent("order_finish_ord_card");

        $card = $pdo->assoc("select ordr_idxx from $tbl[card] where wm_ono='$ord[ono]'");
        $rcpt_link = 'https://bill.payco.com/outseller/receipt/'.$card['ordr_idxx'];
        $card['card_name'] = __lang_order_info_paytype17__;
        $card['quota_str'] = $card['ordr_idxx'];
        if($ord['bank']) $card['quota_str'] = $ord['bank'];
        else {
            $card['receipt'] = "<a href='#' onclick=\"window.open('$rcpt_link', 'allat_rcpt', 'status=no, width=200px, height=200px'); return false;\">결제영수증</a>";
        }

        $_replace_code[$_file_name]['order_finish_ord_card'] = lineValues("order_finish_ord_card", $_line, $card);
    } else if($ord['pay_type'] == '22') {
        $card = $pdo->assoc("select tno from {$tbl['card']} where wm_ono='{$ord['ono']}'");

        $rcpt_link = 'https://pay.toss.im/payfront/web/external/sales-check?payToken='.$card['tno'].'&transactionId=12637496-8a46-488c-bc30-febded96656f';
        $card['receipt'] = "<a href='#' onclick=\"window.open('$rcpt_link', 'kakaopay_rcpt', 'status=no, width=500px, height=800px'); return false;\">결제영수증</a>";
        $card['card_name'] = 'tosspay';
        $card['quota_str'] = 1;

        $_line = getModuleContent('order_finish_ord_card');
        $_replace_code[$_file_name]['order_finish_ord_card'] = lineValues('order_finish_ord_card', $_line, $card);
    }

    $receipt = $pdo->assoc("select * from $tbl[cash_receipt] where ono='$ord[ono]'");
    if($ord['pay_type'] == 2) {
        $_line = getModuleContent("mypage_ord_receipt");
        if($receipt['stat'] == 2) {
            if(defined('use_cash_receipt_prefix') == true) {
                $_cash['date1'] = $pdo->row("select date1 from {$tbl['order']} where ono='{$ord['ono']}'");
                $_cash['ono'] = $_cash['date1'].'_'.$ord['ono'];
            } else {
                $_cash['ono'] = $ord['ono'];
            }
            $receipt['link'] = cashReceiptView($_cash['ono']);
            $receipt['link_addr'] = cashReceiptView($_cash['ono'], true);
        }
        $_order_cash_stat[''] = '미신청';
        $receipt['stat'] = $_order_cash_stat[$receipt['stat']];
        $_replace_code[$_file_name]['order_finish_ord_receipt'] = lineValues("order_finish_ord_receipt", $_line, $receipt);
    }

    // 정기결제 첫결제금액
    if($sbscr=='Y' && $ord['pay_type'] == "23" ) {
        $_replace_code[$_file_name]['sbscr_firsttime_pay_prc'] = parsePrice($pdo->row("SELECT total_prc FROM {$tbl['sbscr_schedule']} WHERE sbono=? ORDER BY date ASC LIMIT 1", array($ord['ono'])), true);
    }


?>