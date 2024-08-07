<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  마이페이지 주문상세보기
	' +----------------------------------------------------------------------------------------------+*/

	$_replace_code[$_file_name]['buyer_name'] = $ord['buyer_name'];
	$_replace_code[$_file_name]['ono'] = $ord['sbono'];
    $_replace_code[$_file_name]['buyer_email'] = $ord['buyer_email'];

	$_tmp = $_tmp2 = '';
	$_line = getModuleContent('mypage_order_stat_img');
	$_line2 = getModuleContent('mypage_order_stat_img_list');
	for($wisa = 1; $wisa <= 5; $wisa++) {
		$_img['stat_img'] = sbscrStatOnOff($wisa);
		$_tmp  .= lineValues("mypage_order_stat_img", $_line, $_img);
		$_tmp2 .= lineValues("mypage_order_stat_img_list", $_line2, $_img);
	}
	$_replace_code[$_file_name]['mypage_order_stat_img'] = $_tmp;
	$_replace_code[$_file_name]['mypage_order_stat_img_list'] = listContentSetting($_tmp2, $_line2);
	unset($_img, $_tmp, $_tmp2, $_line, $_line2);

	$yoil = array("일","월","화","수","목","금","토");

	$_tmp = "";
	$_line = getModuleContent("mypage_sbscr_cart_list");
	if(!$_skin['order_cart_list_imgw']) $_skin['order_cart_list_imgw'] = 50;
	if(!$_skin['order_cart_list_imgh']) $_skin['order_cart_list_imgh'] = 50;
	while($cart = sbscrCartList($_skin['mypage_ord_cart_list_btw_opt'], $_skin['mypage_ord_cart_list_opt_prc'], $_skin['mypage_ord_cart_list_opt_f'], $_skin['mypage_ord_cart_list_opt_b'], $_skin['order_cart_list_imgw'], $_skin['order_cart_list_imgh'])) {
		$cart['name'] = "<a href=\"".$cart['plink']."\">".$cart['name']."</a>";
		$cart['imgr'] = "<img src=\"".$cart['img']."\" ".$cart['imgstr']." barder=\"0\">";
		$cart['imgr_link'] = "<a href=\"".$cart['plink']."\">".$cart['imgr']."</a>";
		$cart['review_url'] = $root_url.'/shop/product_review.php?pno='.strtoupper(md5($cart['pno'])).'&startup=true';

		$prd_cnt = $pdo->assoc("select count(*) as tot, sum(if(stat=2 and ono='', 1, 0)) as ing, sum(if(stat=2 and ono!='', 1, 0)) as complete, sum(if(stat in (12, 13), 1, 0)) as cancel from $tbl[sbscr_schedule_product] where sbono='$cart[sbono]' and sbpno='$cart[no]'");
		if($prd_cnt['tot'] > 0 && $prd_cnt['ing'] > 0) {
			$cart['stat'] = "진행중";
		}else if($prd_cnt['tot'] > 0 && $prd_cnt['tot'] == $prd_cnt['cancel']) {
			$cart['stat'] = "취소완료";
		}else if($prd_cnt['tot'] > 0 && $prd_cnt['tot'] == ($prd_cnt['complete']+$prd_cnt['cancel'])) {
			$cart['stat'] = "진행완료";
		}else if($prd_cnt['ing'] == 0) {
			$cart['stat'] = "진행전";
		}
		$cart['prdno'] = $cart['pno'];

		if($cart['dlv_finish_date']=='0000-00-00') $cart['dlv_finish_date'] = '';

		$cart['dlv_date'] = $cart['dlv_start_date']." ~ ".$cart['dlv_finish_date'];
		$cart['week'] = '';
		$cart['period_text'] = ($cart['dlv_finish_date']) ? $cart['dlv_total_cnt']."회":"";

		//할인
		$total_sale2_prc += $cart['sale2'];
		$total_sale4_prc += $cart['sale4'];
		$total_sale8_prc += $cart['sale8'];

		$_tmp .= lineValues("mypage_sbscr_cart_list", $_line, $cart, "", 1);
	}
	$_tmp = listContentSetting($_tmp, $_line);
	$_replace_code[$_file_name]['mypage_sbscr_cart_list'] = $_tmp;

	$_tmp = "";
	$_line = getModuleContent("mypage_sbscr_schedule_list");
	$sbsql = "select no, date, sum(total_prc) as s_total_prc from $tbl[sbscr_schedule] where sbono='$ord[sbono]' group by date order by date asc";
	$sbcount = $pdo->row("select count(distinct date) from $tbl[sbscr_schedule] where sbono='$ord[sbono]'");

	// 페이징
	include_once $engine_dir."/_engine/include/paging.php";

	$page = numberOnly($_GET['page']);
	$row = numberOnly($_GET['row']);
	if($page <= 1) $page = 1;
	if(!$row) $row = 5;
	if($row > 100) $row = 100;
	$block=10;

	$PagingInstance = new Paging($sbcount, $page, $row, $block);
	$PagingInstance->addQueryString(makeQueryString('page'));
	$PagingResult = $PagingInstance->result($pg_dsn);

	$sbsql .= $PagingResult['LimitQuery'];

	$pageRes = $PagingResult['PageLink'];
	$sbres = $pdo->iterator($sbsql);
	$idx = 1;
	$sort = ($row*($page-1))+1;
	$schno = $pdo->row("select schno from $tbl[sbscr_schedule_product] where sbono='$ord[sbono]' and stat=2 and ono!='' order by no desc limit 1");
	$next = '';
	$last_ea = '';
    foreach ($sbres as $ssdata) {
		$ssdata['idx'] = (($page-1)*$row)+$idx;
		if($next=='Y') {
			$last_ea = $idx;
		}
		if($ssdata['no']==$schno) {
			$next = 'Y';
		}
		$spdata = $pdo->assoc("select s.ono, s.pno, if(o.stat, o.stat, s.stat) as stat from $tbl[sbscr_schedule_product] s left join {$tbl['order']} o using(ono) where s.sbono='$ord[sbono]' and s.schno='$ssdata[no]'");
		$ssdata['new_ono'] = ($spdata['ono']) ? $spdata['ono']:"-";
        if ($spdata['ono'] && $member['no'] > 0) {
    		$ssdata['new_ono_link'] = "<a href='$root_url/mypage/order_detail.php?ono={$spdata['ono']}'>{$spdata['ono']}</a>";
        }
		$ssdata['name'] = $pdo->row("select name from $tbl[product] where no='$spdata[pno]'");
		$ssdata['stat'] = $_order_stat[$spdata['stat']];
		$ssdata['s_total_prc'] = parsePrice($ssdata['s_total_prc'], true);

        // 배송 요일
        $week_const = '__lang_common_week_'.strtolower(date('D', strtotime($ssdata['date']))).'__';
        $ssdata['week'] = (defined($week_const) == true) ? constant($week_const) : '';

		$idx++;
		$_tmp .= lineValues("mypage_sbscr_schedule_list", $_line, $ssdata, "", 1);
	}
	$_tmp = listContentSetting($_tmp, $_line);
	$_replace_code[$_file_name]['mypage_sbscr_schedule_list'] = $_tmp;
	$_replace_code[$_file_name]['mypage_sbscr_schedule_page'] = $pageRes;

	$_replace_code[$_file_name]['prd_prc'] = $ord['prd_prc'];
	$_replace_code[$_file_name]['sale2'] = $total_sale2_prc;
	$_replace_code[$_file_name]['sale4'] = $total_sale4_prc;
	$_replace_code[$_file_name]['sale8'] = parsePrice($total_sale8_prc, true);
	$_replace_code[$_file_name]['total_sale_prc'] = $ord['total_sale_prc'];
	$_replace_code[$_file_name]['dlv_prc'] = $ord['dlv_prc'];
	$_replace_code[$_file_name]['total_prc'] = $ord['total_prc'];
	$_replace_code[$_file_name]['total_milage'] = $ord['total_milage'];
	$_replace_code[$_file_name]['ord_chg1_url'] = "javascript:orderCust(1,1);";
	$_replace_code[$_file_name]['ord_chg2_url'] = "javascript:orderCust(1,2);";
	$_replace_code[$_file_name]['ord_chg3_url'] = "javascript:orderCust(1,12);";
	$_replace_code[$_file_name]['ord_chg4_url'] = "javascript:orderCust(2,14);";
	$_replace_code[$_file_name]['ord_chg5_url'] = "javascript:orderCust(2,16);";

	$_replace_code[$_file_name]['mypage_delivery_stop'] = "javascript:mypageSbscr('$ord[sbono]', 'stop');";
	if(!$last_ea) $last_ea = 1;
	$_replace_code[$_file_name]['mypage_sbscr_cancel'] = "javascript:mypageSbscr('$ord[sbono]', 'cancel', '$last_ea');";

	$_replace_code[$_file_name]['mypage_card_edit'] = ($ord['pay_type']==23) ? "<form target='hidden$now' method='POST' action='/main/exec.php'><input type='hidden' name='exec_file' value='mypage/mypage_reorder.php' /><input type='hidden' name='sno' value='$ord[sbono]' /><input type='submit' value='카드정보수정' /></form>" : "";

	$_replace_code[$_file_name]['mypage_sbscr_dlv_edit'] = "javascript:mypageSbscr('$ord[sbono]', 'edit');";
    $_replace_code[$_file_name]['mypage_sbscr_dlv_edit_new'] = ( $ord['stat'] <= 2 ? "javascript:openOrderAddress(0);" : "");

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

	if($ord['pay_type'] == "2") {
		$_line = getModuleContent("mypage_ord_cash");
		$_replace_code[$_file_name]['mypage_ord_cash'] = lineValues("mypage_ord_cash", $_line, $ord);
	}elseif($ord['pay_type'] == "1") {
		$_line = getModuleContent("mypage_ord_card");
		// 영수증정보
		if($cfg['card_pg'] == "kcp") $card['receipt'] = "<a href=\"javascript:;\" onclick=\"kcpCardReceipt('".$card['tno']."');\">카드영수증</a>";
		elseif($cfg['card_pg'] == "inicis") $card[receipt]="<a href=\"javascript:;\" onclick=\"inicisCardReceipt('".$card['tno']."');\">카드영수증</a>";
		elseif($cfg['card_pg'] == "dacom") {
			$card['authdata'] = md5($cfg['card_dacom_id'].$card['tno'].$cfg['card_dacom_key']);
			$card['receipt'] = "<script type='text/javascript' src=\"https://pgweb.dacom.net/WEB_SERVER/js/receipt_link.js\"></script><a href=\"javascript:;\" onclick=\"showReceiptByTID('".$cfg['card_dacom_id']."','".$card['tno']."','".$card['authdata']."');\">카드영수증</a>";
		}
		elseif($cfg['card_pg'] == 'kspay') {
			$card['receipt'] = "<a href='#' onclick='ksnetReceipt(\"$card[tno]\"); return false;'>카드영수증</a>";
			$card['receipt'] .= "<script type='text/javascript'>function ksnetReceipt(tr_no) {var receiptWin='http://pgims.ksnet.co.kr/pg_infoc/src/bill/credit_view.jsp?tr_no='+tr_no;window.open(receiptWin, '' , 'scrollbars=no,width=434,height=640');}</script>";
		}
		elseif($cfg['card_pg'] == 'allat') {
			if($ord['pay_type'] == 4 || $ord['pay_type'] == 5) {
				$rcpt_link = "http://www.allatpay.com/servlet/AllatBizPop/member/pop_tx_receipt.jsp?shop_id=$allat_id&order_no=$ono&pay_type=$bank_pay_method";
			} else if($ord['pay_type'] != 7) {
				$rcpt_link = "http://www.allatpay.com/servlet/AllatBizPop/member/pop_card_receipt.jsp?shop_id=$allat_id&order_no=$ono";
			}
			if($rcpt_link) {
				$card['receipt'] = "<a href='#' onclick=\"window.open('$rcpt_link', 'allat_rcpt', 'status=no'); return false;\">카드영수증</a>";
			}
		}
		$_replace_code[$_file_name]['mypage_ord_card'] = lineValues("mypage_ord_card", $_line, $card);
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
	}

	$receipt = $pdo->assoc("select * from $tbl[cash_receipt] where ono='$ord[ono]'");
	if($receipt['no'] > 0) {
		$_line = getModuleContent("mypage_ord_receipt");
		if($receipt['stat'] == 2) $receipt['link'] = cashReceiptView($ord['ono']);
		$receipt['stat'] = $_order_cash_stat[$receipt['stat']];
		$_replace_code[$_file_name]['mypage_ord_receipt'] = lineValues("mypage_ord_receipt", $_line, $receipt);
	}

	$_replace_code[$_file_name]['date2'] = $ord['date2'];
	$_replace_code[$_file_name]['pay_prc'] = $ord['pay_prc'];
	$_replace_code[$_file_name]['date1'] = $ord['date1'];
	$_replace_code[$_file_name]['buyer_phone'] = $ord['buyer_phone'];
	$_replace_code[$_file_name]['buyer_cell'] = $ord['buyer_cell'];
	$_replace_code[$_file_name]['addressee_name'] = $ord['addressee_name'];
	$_replace_code[$_file_name]['nations'] = $ord['nations'];
	$_replace_code[$_file_name]['addressee_zip'] = $ord['addressee_zip'];
	$_replace_code[$_file_name]['addressee_addr1'] = $ord['addressee_addr1'];
	$_replace_code[$_file_name]['addressee_addr2'] = $ord['addressee_addr2'];
	$_replace_code[$_file_name]['addressee_addr3'] = $ord['addressee_addr3'];
	$_replace_code[$_file_name]['addressee_addr4'] = $ord['addressee_addr4'];
	$_replace_code[$_file_name]['addressee_phone'] = $ord['addressee_phone'];
	$_replace_code[$_file_name]['addressee_cell'] = $ord['addressee_cell'];
	$_replace_code[$_file_name]['dlv_sbscr_memo'] = stripslashes($ord['dlv_memo']);

	if($rURL) {
		$_line = getModuleContent("mypage_sbscr_backtolist");
		$ord['back_url'] = urldecode($rURL);
		$_replace_code[$_file_name]['mypage_sbscr_backtolist'] = lineValues("mypage_sbscr_backtolist", $_line, $ord);
	}

    if( $ord['pay_type'] == 23 ) {
        $first_prc = $pdo->row("select total_prc from {$tbl['sbscr_schedule']} where sbono=? order by no asc limit 1", array($ord['sbono']));
    }
    $_replace_code[$_file_name]['mypage_sbscr_first_total_prc'] = ( $first_prc ? parsePrice($first_prc, true) : "" );
?>