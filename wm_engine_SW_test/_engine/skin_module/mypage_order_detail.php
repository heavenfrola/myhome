<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  마이페이지 주문상세보기
	' +----------------------------------------------------------------------------------------------+*/

	$_replace_code[$_file_name]['buyer_name'] = $ord['buyer_name'];
	$_replace_code[$_file_name]['ono'] = $ord['ono'];

	$_tmp = $_tmp2 = '';
	$_line = getModuleContent('mypage_order_stat_img');
	$_line2 = getModuleContent('mypage_order_stat_img_list');
	for($wisa = 1; $wisa <= 5; $wisa++) {
		$_img['stat_img'] = orderStatOnOff($wisa);
		$_tmp  .= lineValues("mypage_order_stat_img", $_line, $_img);
		$_tmp2 .= lineValues("mypage_order_stat_img_list", $_line2, $_img);
	}
	$_replace_code[$_file_name]['mypage_order_stat_img'] = $_tmp;
	$_replace_code[$_file_name]['mypage_order_stat_img_list'] = listContentSetting($_tmp2, $_line2);
	unset($_img, $_tmp, $_tmp2, $_line, $_line2);

	if($ord['dlv_code']) {
		$_tmp = "";
		$_line = getModuleContent("mypage_dlv_trace");
		$dlv['dlv_code'] = $ord['dlv_code'];
		$dlv['url'] = str_replace('-', '', $dlv['url']);
		$_replace_code[$_file_name]['mypage_dlv_trace'] = lineValues("mypage_dlv_trace", $_line, $dlv);
	}

	$_tmp = "";
	$_line = getModuleContent("mypage_ord_cart_list");
	if(!$_skin['order_cart_list_imgw']) $_skin['order_cart_list_imgw'] = 50;
	if(!$_skin['order_cart_list_imgh']) $_skin['order_cart_list_imgh'] = 50;
	while($cart = orderCartList($_skin['mypage_ord_cart_list_btw_opt'], $_skin['mypage_ord_cart_list_opt_prc'], $_skin['mypage_ord_cart_list_opt_f'], $_skin['mypage_ord_cart_list_opt_b'], $_skin['order_cart_list_imgw'], $_skin['order_cart_list_imgh'])) {

		if ($cart['stat'] < 6) {
			$prd_prc_sum += (numberOnly($cart['sell_prc'], true) * $cart['buy_ea']);
			$sale5 += $cart['sale5'];
		}

		$cart['name'] = "<a href=\"".$cart['plink']."\">".$cart['name']."</a>";
		$cart['etc'] = stripslashes($cart['etc']);
		$cart['imgr'] = "<img src=\"".$cart['img']."\" ".$cart['imgstr']." barder=\"0\">";
		$cart['imgr_link'] = "<a href=\"".$cart['plink']."\">".$cart['imgr']."</a>";
		$cart['review_url'] = $root_url.'/shop/product_review.php?pno='.strtoupper(md5($cart['pno'])).'&ono='.$ord['ono'].'&startup=true';
		$cart['review_url_layer'] = "javascript:writeReviewWithoutRa({$cart['pno']});";
		$cart['stat'] = _getOrdStat($cart);
		$cart['prdno'] = $cart['pno'];

        // 후기 작성 권한
        $cart['review_write_perm'] = '';
        switch ($cfg['product_review_auth']) {
            case '1' :
                $cart['review_write_perm'] = ($cart['prd_type'] == '1') ? 'Y' : '';
                break;
            case '2' :
                $cart['review_write_perm'] = ($member['no'] > 0 && $cart['prd_type'] == '1') ? 'Y' : '';
                break;
            case '3' :
                if ($member['no'] > 0 && $cart['prd_type'] == '1') {
                    $prd_cnt = $pdo->row("select count(*) from {$tbl['order_product']} where ono='{$ord['ono']}' and pno='{$cart['pno']}' and stat=5");
                    if ($prd_cnt > 0) {
                        if ($cfg['product_review_many'] == '2') {
                            $cart['review_write_perm'] = 'Y';
                        } else {
                            $review_cnt = $pdo->row("select count(*) from {$tbl['review']} where member_no='{$member['no']}' and pno='{$cart['pno']}'");
                            if ($review_cnt == 0) {
                                $cart['review_write_perm'] = 'Y';
                            }
                        }
                    }
                }
                break;
            case '4' :
                if ($member['no'] > 0 & $cart['prd_type'] == '1') {
                    $prd_cnt = $pdo->row("select count(*) from {$tbl['order_product']} where ono='{$ord['ono']}' and pno='{$cart['pno']}' and stat=5");
                    $review_cnt = $pdo->row("select count(*) from {$tbl['review']} where ono='{$ord['ono']}' and member_no='{$member['no']}' and pno='{$cart['pno']}'");
                    if ($prd_cnt <= $review_cnt) {
                        $check = 0;
                    } else {
                        $check = $prd_cnt;
                    }
                    if ($check > 0) $cart['review_write_perm'] = 'Y';
                }
                break;
        }

		if ($cart['set_idx'] && $cart['set_pno'] && (isset($current_set_id) == false || $current_set_id !== $cart['set_idx'])) { // 세트 메인 상품 출력
            $set_sum = $pdo->assoc("
                select sum(total_prc) as total_prc, min(buy_ea) as buy_ea, sum(total_milage) as total_milage
                from {$tbl['order_product']}
                where ono='$ono' and set_idx='{$cart['set_idx']}'
            ");
			$_setprd = prdOneData(shortCut($pdo->assoc("select *, no as pno from {$tbl['product']} where no='{$cart['set_pno']}'")), $_skin['cart_list_imgw'], $_skin['cart_list_imgh'], 3);
			$_setprd = parseUserCart($_setprd);
			$_setprd['prdno'] = $cart['no'];
            $_setprd['stat'] = '';
            $_setprd['total_prc'] = parsePrice($set_sum['total_prc'], true);
            $_setprd['total_milage'] = parsePrice($set_sum['total_milage'], true);
			$_setprd['buy_ea'] = $set_sum['buy_ea'];

			$_tmp .= lineValues('mypage_ord_cart_list', $_line[5], $_setprd, '', 1);
            $current_set_id = $cart['set_idx'];
		}

		$_tmp .= lineValues("mypage_ord_cart_list", ($cart['set_idx'] ? $_line[6] : $_line[2]), $cart, "", 1);
	}
	$_tmp = listContentSetting($_tmp, $_line);
	$_replace_code[$_file_name]['mypage_ord_cart_list'] = $_tmp;
	$_replace_code[$_file_name]['prd_prc'] = parsePrice($prd_prc_sum, true);
	$_replace_code[$_file_name]['sale2'] = $ord['sale2'];
	$_replace_code[$_file_name]['sale3'] = $ord['sale3'];
	$_replace_code[$_file_name]['sale4'] = $ord['sale4'];
	$_replace_code[$_file_name]['sale5'] = $sale5;
	$_replace_code[$_file_name]['sale6'] = $ord['sale6'];
	$_replace_code[$_file_name]['sale7'] = parsePrice($ord['sale7'], true);
	$_replace_code[$_file_name]['total_sale_prc'] = $ord['total_sale_prc'];
	$_replace_code[$_file_name]['dlv_prc'] = $ord['dlv_prc'];
	$_replace_code[$_file_name]['total_prc'] = $ord['total_prc'];
	$_replace_code[$_file_name]['total_milage'] = $ord['total_milage'];
	$_replace_code[$_file_name]['ord_chg1_url'] = "javascript:orderCust(1,1);";
	$_replace_code[$_file_name]['ord_chg2_url'] = "javascript:orderCust(1,2);";
	$_replace_code[$_file_name]['ord_chg3_url'] = "javascript:orderCust(2,12);";
	$_replace_code[$_file_name]['ord_chg4_url'] = "javascript:orderCust(2,14);";
	$_replace_code[$_file_name]['ord_chg5_url'] = "javascript:orderCust(2,16);";
	$_replace_code[$_file_name]['ord_receipt_url'] = "javascript:printReceipt(3,'','$ord[ono]');";

	$_replace_code[$_file_name]['prd_r_prc'] = showExchangeFee($prd_prc_sum);
	$_replace_code[$_file_name]['r_sale2'] = showExchangeFee($ord['sale2']);
	$_replace_code[$_file_name]['r_sale4'] = showExchangeFee($ord['sale4']);
	$_replace_code[$_file_name]['r_sale5'] = showExchangeFee($sale5);
	$_replace_code[$_file_name]['r_sale6'] = showExchangeFee($ord['sale6']);
	$_replace_code[$_file_name]['total_r_sale_prc'] = showExchangeFee($ord['total_sale_prc']);
	$_replace_code[$_file_name]['dlv_r_prc'] = showExchangeFee($ord['dlv_prc']);
	$_replace_code[$_file_name]['total_r_prc'] = showExchangeFee($ord['total_prc']);
	$_replace_code[$_file_name]['total_r_milage'] = showExchangeFee($ord['total_milage']);

	$_replace_code[$_file_name]['tax_prc'] = $ord['tax']>0?$ord['tax']:'';
	$_replace_code[$_file_name]['tax_r_prc'] = showExchangeFee($ord['tax']);


	$_tmp = "";
	$_line = getModuleContent("mypage_ord_1to1_list");
	while($cs = counselLoop()) {
		$cs_title = $cs['title'];
		$cs['widx'] = $widx;
		$cs['title']="<a href=\"".$cs['link']."\"><b>".cutstr($cs_title, 60)."</b></a>";
		$cs['title2']="<b>".cutstr($cs_title, 60)."</b>";
		$cs['link'] = str_replace('javascript:', '', $cs['link']);
		$cs['reply_yn'] = ($cs['reply_date']) ? __lang_counsel_reply_complete : __lang_counsel_reply_ing;
		// 첨부 이미지
		$cs['atc'] = "";
		for($ii=1; $ii<=2; $ii++){
			if($cs['upfile'.$ii]){
				$cs['atc'] = "Y";
				$img = prdImg($ii, $cs, $_para1, $_para2, $def_img);
                if ( !in_array(strtolower(getExt($img[0])), array('jpg','jpeg','png','gif','webp')) ) {
                    //이미지파일이 아닌 경우
                    $cs['img'.$ii] = '<a href="'.$img[0].'" target="_blank" download="'.__lang_common_info_attachFile__.$ii.'.'.getExt($img[0]).'" ><strong>['.__lang_common_info_attachFile__.' #'.$ii.']</strong></a>';
                } else {
                    $cs['img'.$ii] = $img[0];
                    $cs['img'.$ii] = ($cs['img'.$ii]) ? "<img src=\"".$cs['img'.$ii]."\" border=\"0\" id=\"cs_img".$cs[no]."_".$ii."\">" : "";
                }
			}else{
				continue;
			}
		}
		$cs['file_icon'] = ($cs['atc']) ? $_prd_board_icon['file'] : "";
		$_tmp .= lineValues("mypage_ord_1to1_list", $_line, $cs);
	}
	$_tmp = listContentSetting($_tmp, $_line);
	$_replace_code[$_file_name]['mypage_ord_1to1_list'] = $_tmp;

	$_replace_code[$_file_name]['pay_type_str'] = nl2br($ord['pay_type_str']);

	if($ord['pay_type'] == "2" || $ord['pay_type'] == "4") {
		if (!$ord['bank']) {
			$ord2 = $pdo->assoc("select bankname, account from wm_vbank where wm_ono = '{$ord['ono']}'");
			$ord['bank'] = trim($ord2['bankname'].' '.$ord2['account']);
		}
		$_line = getModuleContent("mypage_ord_cash");
		$_replace_code[$_file_name]['mypage_ord_cash'] = lineValues("mypage_ord_cash", $_line, $ord);
	}elseif($ord['pay_type'] == "1" || $ord['pay_type'] == "5") {
		if($card['stat'] != '1') {
			$_line = getModuleContent("mypage_ord_card");
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
			$_replace_code[$_file_name]['mypage_ord_card'] = lineValues("mypage_ord_card", $_line, $card);
		}
	} else if($ord['pay_type'] == 12) {
		$hash = $cfg['kakao_cid'].$card['tno'].$ord['ono'].($ord['member_no'] > 0 ? $ord['member_id'] : $card['guest_no']);
		$hash = bin2hex(hash('sha256', $hash, true));
		$d_type = ($ord['mobile'] == 'Y') ? 'm' : 'p';
		$rcpt_link = "https://pg-web.kakao.com/v1/confirmation/{$d_type}/{$card['tno']}/{$hash}";
		$card['receipt'] = "<a href='#' onclick=\"window.open('$rcpt_link', 'kakaopay_rcpt', 'status=no, width=500px, height=800px'); return false;\">결제영수증</a>";

		$_line = getModuleContent('mypage_ord_card');
		$_replace_code[$_file_name]['mypage_ord_card'] = lineValues('mypage_ord_card', $_line, $card);
	} else if($ord['pay_type'] == 17) {
		$_line = getModuleContent("mypage_ord_card");

		$card = $pdo->assoc("select ordr_idxx from $tbl[card] where wm_ono='$ord[ono]'");
		$rcpt_link = 'https://bill.payco.com/outseller/receipt/'.$card['ordr_idxx'];
		$card['card_name'] = __lang_order_info_paytype17__;
		$card['quota_str'] = $card['ordr_idxx'];
		if($ord['bank']) $card['quota_str'] = $ord['bank'];
		else {
			$card['receipt'] = "<a href='#' onclick=\"window.open('$rcpt_link', 'allat_rcpt', 'status=no, width=200px, height=200px'); return false;\">결제영수증</a>";
		}

		$_replace_code[$_file_name]['mypage_ord_card'] = lineValues("mypage_ord_card", $_line, $card);
	} else if($ord['pay_type'] == '22') {
		$card = $pdo->assoc("select tno from {$tbl['card']} where wm_ono='{$ord['ono']}'");

		$rcpt_link = 'https://pay.toss.im/payfront/web/external/sales-check?payToken='.$card['tno'].'&transactionId=12637496-8a46-488c-bc30-febded96656f';
		$card['receipt'] = "<a href='#' onclick=\"window.open('$rcpt_link', 'kakaopay_rcpt', 'status=no, width=500px, height=800px'); return false;\">결제영수증</a>";
		$card['card_name'] = 'tosspay';
		$card['quota_str'] = 1;

		$_line = getModuleContent('mypage_ord_card');
		$_replace_code[$_file_name]['mypage_ord_card'] = lineValues('mypage_ord_card', $_line, $card);
	} elseif ($ord['pay_type'] == '28') {
        $card = $pdo->assoc("select tno, app_no, quota from {$tbl['card']} where wm_ono='{$ord['ono']}'");

        $rcpt_link = 'https://www.danalpay.com/receipt/ispay/auth?tid='.$card['tno'].'&cpgb=1';
        $card['receipt'] = "<a href='#' onclick=\"window.open('$rcpt_link', 'samsungpay_rcpt', 'status=no, width=500px, height=800px'); return false;\">결제영수증</a>";
        $card['card_name'] = '삼성페이 (승인번호 : '.$card['app_no'].')<br>';
        $card['quota_str'] = ($card['quota'] == '00') ? '일시불' : $card['quota'];

        $_line = getModuleContent('mypage_ord_card');
        $_replace_code[$_file_name]['mypage_ord_card'] = lineValues('mypage_ord_card', $_line, $card);
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
		$_replace_code[$_file_name]['mypage_ord_receipt'] = lineValues("mypage_ord_receipt", $_line, $receipt);
	}

	$_replace_code[$_file_name]['date2'] = $ord['date2'];
	$_replace_code[$_file_name]['pay_prc'] = $ord['pay_prc'];
	$_replace_code[$_file_name]['milage_prc_c'] = $ord['milage_prc_c'];
	$_replace_code[$_file_name]['emoney_prc_c'] = $ord['emoney_prc_c'];
	$_replace_code[$_file_name]['naver_milage_prc_c'] = $ord['naver_milage_prc'];
	$_replace_code[$_file_name]['naver_cash_c'] = $ord['naver_cash'];
	$_replace_code[$_file_name]['date1'] = $ord['date1'];
	$_replace_code[$_file_name]['buyer_phone'] = $ord['buyer_phone'];
	$_replace_code[$_file_name]['buyer_cell'] = $ord['buyer_cell'];
	$_replace_code[$_file_name]['addressee_name'] = $ord['addressee_name'];
    $_replace_code[$_file_name]['buyer_email'] = $ord['buyer_email'];

	$_replace_code[$_file_name]['pay_r_prc'] = showExchangeFee($ord['pay_prc']);
	$_replace_code[$_file_name]['milage_r_prc_c'] = showExchangeFee($ord['milage_prc_c']);
	$_replace_code[$_file_name]['emoney_r_prc_c'] = showExchangeFee($ord['emoney_prc_c']);
	$_replace_code[$_file_name]['naver_r_milage_prc_c'] = showExchangeFee($ord['naver_milage_prc']);
	$_replace_code[$_file_name]['naver_r_cash_c'] = showExchangeFee($ord['naver_cash']);

	$_replace_code[$_file_name]['nations'] = getCountryNameFromCode($ord['nations']);
	$_replace_code[$_file_name]['delivery_com'] = getDeliveryNameFromNo($ord['delivery_com']);

	$_replace_code[$_file_name]['addressee_zip'] = $ord['addressee_zip'];
	$_replace_code[$_file_name]['addressee_addr1'] = $ord['addressee_addr1'];
	$_replace_code[$_file_name]['addressee_addr2'] = $ord['addressee_addr2'];
	$_replace_code[$_file_name]['addressee_addr3'] = $ord['addressee_addr3'];
	$_replace_code[$_file_name]['addressee_addr4'] = $ord['addressee_addr4'];
	$_replace_code[$_file_name]['addressee_phone'] = $ord['addressee_phone'];
	$_replace_code[$_file_name]['addressee_cell'] = $ord['addressee_cell'];
	$_replace_code[$_file_name]['dlv_memo'] = stripslashes($ord['dlv_memo']);

	if($rURL) {
		$_line = getModuleContent("mypage_ord_backtolist");
		$ord['back_url'] = urldecode($rURL);
		$_replace_code[$_file_name]['mypage_ord_backtolist'] = lineValues("mypage_ord_backtolist", $_line, $ord);
	}

	$add_info_file = $root_dir.'/_config/order.php';
	if(is_file($add_info_file)) {
		include_once $add_info_file;
	}
	if(@is_array($_ord_add_info)) {
		$_tmp = "";
		$_line = getModuleContent("order_detail_addfd_list");
		foreach($_ord_add_info as $key=>$val) {//ADDINFO_DONE
			$_oaddfd['name'] = stripslashes($_ord_add_info[$key]['name']);
			$_oaddfd['value'] = orderAddFrm($key, 2, $ord);
			$_tmp .= lineValues("order_detail_addfd_list", $_line, $_oaddfd);
		}
		unset($_oaddfd);
		$_tmp = listContentSetting($_tmp, $_line);

		$_replace_code[$_file_name]['order_detail_addfd_list'] = $_tmp."<!-- ADDINFO_DONE -->";
	}

	if($ord['sale5'] > 0) {
        $_cpn = $pdo->assoc("select name, stype from $tbl[coupon_download] where ono='{$ord['ono']}'");
		$_replace_code[$_file_name]['cpn_name'] = stripslashes($_cpn['name']);
        if ($_cpn['stype'] == '3') { // 무료배송쿠폰
            $_replace_code[$_file_name]['sale5'] = $ord['sale5'];
        }
        unset($_cpn);
	}
	if ($ord['sale7'] > 0) {
		$prdcpn = '';
		$cpnres = $pdo->iterator("select name from $tbl[coupon_download] where ono='{$ord['ono']}' and stype = 5");
		foreach ($cpnres as $cpndata) {
			if ($prdcpn) $prdcpn .= ', ';
			$prdcpn .= $cpndata['name'];
		}
		$_replace_code[$_file_name]['prdcpn_name'] = stripslashes($prdcpn);
	}

	$_replace_code[$_file_name]['receive_link'] = ($ord['stat'] == 4) ? "<a href=\"#\" onclick=\"receiveProduct('$ord[ono]'); return false;\">" : '';
    $_replace_code[$_file_name]['dlv_edit'] = "javascript:editAddressee('$ord[ono]');";
    $_replace_code[$_file_name]['mypage_order_dlv_edit_new'] = ( $ord['stat'] <= 2 ? "javascript:openOrderAddress(0);" : "");

?>