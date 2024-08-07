<?PHP

	use Wing\API\Naver\Checkout;
    use Wing\API\Kakao\KakaoTalkPay;

	/* +----------------------------------------------------------------------------------------------+
	' |  상품상세보기
	' +----------------------------------------------------------------------------------------------+*/

	if($_POST['exec'] == 'getMultiOption') return;

	// 상품 QNA
	if(!$_GET['single_module'] || $_GET['single_module'] == 'detail_qna_list' || $_GET['single_module'] == 'qna_list') {
		if(CheckCodeUsed($_replace_hangul[$_file_name]['detail_qna_list'])) {
			$_detail_qna = array();
			$_para1 = (!$_skin['detail_qna_list_titlecut']) ? 50 : $_skin['detail_qna_list_titlecut'];
			$_para2 = (!$_skin['detail_qna_list_rows']) ? 10 : $_skin['detail_qna_list_rows'];
			while($qna = qnaList($_skin['detail_qna_list_connext'], "Y/m/d", $_para2)){
				$qna['qna_idx'] = $qna_idx;
				$_detail_qna[] = $qna;
			}
			$_replace_code[$_file_name]['detail_qna_pageres'] = $qna_pageRes;
			$_replace_code[$_file_name]['detail_qna_url'] = $root_url."/shop/product_qna.php?pno=".$pno;

			$_tmp = '';
			$_line = getModuleContent('detail_qna_list', null, $_file_name);
			foreach($_detail_qna as $qna) {
				$qna['qna_idx'] = $qna['qna_idx'];
				$qna['secret_i'] = ($qna['secret']=="Y") ? $_prd_board_icon['secret'] : "";
				$qna['new_i'] = ($qna['new_check']) ? $_prd_board_icon['new'] : "";
				$qna['reply_i'] = (strip_tags($qna['answer'], '<img>') || $qna['upfile3'] || $qna['upfile4']) ? $_prd_board_icon['reply'] : "";
				$qna['reply_b_i']=(strip_tags($qna['answer'], '<img>') || $qna['upfile3'] || $qna['upfile4']) ? '' : $_prd_board_icon['reply_b'];
				$qna['file_icon'] = ($qna['atc']) ? $_prd_board_icon['file'] : "";
				$qna['link2'] = "javascript:prdBoardView('qna','".$qna['no']."');";
				$qna['title_nolink'] = cutstr($qna['title'], $_para1);
				$qna['title'] = "<a href=\"".$qna['link2']."\">".cutstr($qna['title'], $_para1)."</a>";
				$_tmp .= lineValues("detail_qna_list", $_line, $qna, "", 2);
			}
			$_tmp = listContentSetting($_tmp, $_line);
			$_replace_code[$_file_name]['detail_qna_list'] = $_tmp;

			unset($_detail_qna, $_tmp);
		}

		if($_GET['single_module']) {
			return;
		}
	}

	// 상품 후기
	if(!$_GET['single_module'] || $_GET['single_module'] == 'detail_review_list' || $_GET['single_module'] == 'review_list') {
		if(CheckCodeUsed($_replace_hangul[$_file_name]['detail_review_list'])) {
			require 'shop_detail_review.inc.php';

			$_detail_rev = array();
			$_para1 = (!$_skin['detail_review_list_imgw']) ? 1000 : $_skin['detail_review_list_imgw'];
			$_para2 = (!$_skin['detail_review_list_imgh']) ? 1000 : $_skin['detail_review_list_imgh'];
			$_para3 = (!$_skin['detail_review_list_titlecut']) ? 100 : $_skin['detail_review_list_titlecut'];
			$_para4 = (!$_skin['detail_review_list_rows']) ? 10 : $_skin['detail_review_list_rows'];
			if($_GET['full_reload'] != 'true' && $_more_page > 0) {
				$rev_page = 1;
				$_para4 = ($_para4*$_more_page);
			}
			$_oprd = ($prd['no']) ? "r." : "";
			$rev_order = ($_skin['detail_review_list_best_use'] == "Y") ? " ".$_oprd."`stat` desc, ".$_oprd."`reg_date` desc " : " ".$_oprd."`reg_date` desc ";
			while($review = reviewList($_para1, $_para2, $_para4)) {
				$_detail_rev[] = $review;
			}
			$_replace_code[$_file_name]['detail_review_pageres'] = $rev_pageRes;
			$_replace_code[$_file_name]['detail_review_url'] = $root_url."/shop/product_review.php?pno=".$pno;

			$_tmp = "";
			$_line = getModuleContent('detail_review_list', null, $_file_name);
			foreach($_detail_rev as $review) {
                if ($prd['prd_type'] != '1') {
                    $review['components_name'] = $review['prd_name'];
                    $review['components_link'] = $review['prd_link'];
                }
				$review['link2'] = "javascript:prdBoardView('review','".$review['no']."');";
				$review['title_nolink'] = cutstr($review['title'], $_para3);
				$review['title'] = "<a href=\"".$review['link2']."\">".cutstr($review['title'], $_para3)."</a>";
				$review['file_icon'] = ($review['atc']) ? $_prd_board_icon['file'] : "";
				$review['new_i'] = ($review['new_check']) ? $_prd_board_icon['new'] : "";
				$review['star'] = reviewStar($_prd_board_icon['star']);
				$_tmp .= lineValues("detail_review_list", $_line, $review, "", 2);
			}
			$_tmp = listContentSetting($_tmp, $_line);
			$_replace_code[$_file_name]['detail_review_list'] = $_tmp;

			unset($_detail_qna, $_tmp);
		}

		if($_GET['single_module']) {
			return;
		}
	}

	$movie_exists = preg_match('/\[\[동영상삽입영역[^]]*\]\]/', $prd['content2'], $sampled);
	while($movie_exists) {
		$movie = explode('@', preg_replace('/^\[+|\]+$/', '', $sampled[0]));
		$tag  = "<div id='$movie[1]' style='width:$movie[3]px; height:$movie[4]px'></div>\n";
		$tag .= "<script type='text/javascript'>\n";
		$tag .= "	flashMovie('$movie[1]','/_skin/$design[skin]/img/flash/flvPlayer.swf', '$movie[3]px', '$movie[4]px','flvPath=$movie[2]&volumValue=$movie[5]','transparent');\n";
		$tag .= "</script>\n";

		$prd['content2'] = str_replace($sampled[0], $tag, $prd['content2']);

		$movie_exists = preg_match('/\[\[동영상삽입영역[^]]*\]\]/', $prd['content2'], $sampled);
	}

	$_replace_code[$_file_name]['detail_form_start']="<form name=\"prdFrm\" method=\"post\" style=\"margin:0px\" accept-charset=\""._BASE_CHARSET_."\" data-prd_type=\"{$prd['prd_type']}\">
<input type=\"hidden\" name=\"pno\" value=\"".$prd['hash']."\">
<input type=\"hidden\" name=\"product_name\" value=\"".inputText(strip_tags($prd['name']))."\">
<input type=\"hidden\" name=\"stat\" value=\"".$prd['stat']."\">
<input type=\"hidden\" name=\"ea_type\" value=\"".$prd['ea_type']."\">
<input type=\"hidden\" name=\"min_ord\" value=\"".$prd['min_ord']."\">
<input type=\"hidden\" name=\"max_ord\" value=\"".$prd['max_ord']."\">
<input type=\"hidden\" name=\"ea\" value=\"".$prd['ea']."\">
<input type=\"hidden\" name=\"next\" value=\"\">
<input type=\"hidden\" name=\"exec\" value=\"\">
<input type=\"hidden\" name=\"rURL\" value=''>
<input type=\"hidden\" name=\"total_prc\" value=\"".$prd['sell_prc']."\">
<input type=\"hidden\" name=\"pay_prc\" value=\"".$prdCart->getData('pay_prc')."\">
<input type=\"hidden\" name=\"new_total_prc\" value=\"".$prd['sell_prc']."\">
<input type=\"hidden\" name=\"sell_prc_consultation\" value=\"".$prd['sell_prc_consultation']."\">
<input type=\"hidden\" name=\"qd\" value=\"".addslashes(strip_tags($_GET['type']))."\">
<input type=\"hidden\" name=\"prdcpn_no\" value=''>
";
	if ($prd['prd_type'] == '4' || $prd['prd_type'] == '5' || $prd['prd_type'] == '6') {
		$_replace_code[$_file_name]['detail_form_start'] .= "<input type=\"hidden\" name=\"set_pno\" value=\"{$prd['parent']}\"><input type=\"hidden\" name=\"rel_cart\" value=\"Y\">";
	}

	$_check_ctype = $pdo->row("select `ctype` from `{$tbl['category']}` where `no`='{$_cno1['no']}'");
	if($_check_ctype == 6 && !empty($_GET['pno'])) {
        $_cno1['name'] = $pdo->row("select a.`name` from `{$tbl['category']}` a left join `{$tbl['product']}` b on a.`no`=b.`big` where b.`hash`=:hash", array(
            ':hash' => $_GET['pno']
        ));
    }

	$_replace_code[$_file_name]['cate_name'] = $_cno1['name'];
	$_replace_code[$_file_name]['cate_cno1'] = $_cno1['no'];
	$_replace_code[$_file_name]['cate_ctype'] = $_cno1['ctype'];
	$_replace_code[$_file_name]['cate_level'] = $_cno1['level'];
	if($_SESSION['browser_type'] == 'mobile')  $_replace_code[$_file_name]['detail_prd_img'] = mainImg(100,100);
	else $_replace_code[$_file_name]['detail_prd_img'] = mainImg($cfg['thumb2_w'], $cfg['thumb2_h']);
	$_replace_code[$_file_name]['detail_zoom_url'] = "javascript:zoomView('".$pno."', '$_skin[zoom_width]', '$_skin[zoom_height]');";
	$_replace_code[$_file_name]['detail_preprd_url'] = prdNextPrev(2);
	$_replace_code[$_file_name]['detail_nextprd_url'] = prdNextPrev(1);
	$_popup_sizew = $_skin['recom_mail_sizew'];
	$_popup_sizew = $_popup_sizew ? $_popup_sizew : 600;
	$_popup_sizeh = $_skin['recom_mail_sizeh'];
	$_popup_sizeh = $_popup_sizeh ? $_popup_sizeh : 500;
	$_replace_code[$_file_name]['detail_prd_name'] = $prd['name'];
	$_replace_code[$_file_name]['detail_referer_name'] = stripslashes($prd['name_referer']);
	$_replace_code[$_file_name]['detail_prd_icons'] = prdIcons();
	$_replace_code[$_file_name]['detail_prd_code'] = $prd['code'];
	$_replace_code[$_file_name]['detail_sell_prc_name'] = $cfg['product_sell_price_name'];
	$_replace_code[$_file_name]['detail_sell_prc'] = $prd['sell_prc_str'];
	$_replace_code[$_file_name]['detail_r_sell_prc'] = $prd['sell_prc_str']>0?showExchangeFee($prd['sell_prc_str']):'';
	$_replace_code[$_file_name]['detail_nml_prc_name'] = $cfg['product_normal_price_name'];
	$_replace_code[$_file_name]['detail_nml_prc'] = $prd['normal_prc']>0?$prd['normal_prc']:'';
	$_replace_code[$_file_name]['detail_r_nml_prc'] = $prd['normal_prc']>0?showExchangeFee($prd['normal_prc']):'';
	$_replace_code[$_file_name]['detail_msale_prc2'] = ($prd['member_sale'] == 'Y' && $prdCart->sale4 > 0) ? number_format($prd['sell_prc']-$prdCart->sale4) : '';
	$_replace_code[$_file_name]['detail_r_msale_prc2'] = ($prd['member_sale'] == 'Y' && $prdCart->sale4 > 0) ? number_format(showExchangeFee($prd['sell_prc']-$prdCart->sale4)) : '';
	$_replace_code[$_file_name]['detail_pay_prc'] = parsePrice($prdCart->pay_prc, true);
	$_replace_code[$_file_name]['total_sale_per1'] = $prdCart->getData('total_sale_per1');
	$_replace_code[$_file_name]['total_sale_per2'] = $prdCart->getData('total_sale_per2');
	$_replace_code[$_file_name]['total_sale_per3'] = $prdCart->getData('total_sale_per3');
	$_replace_code[$_file_name]['detail_r_pay_prc'] = $prdCart->pay_prc>0?showExchangeFee($prdCart->pay_prc):'';
	$_replace_code[$_file_name]['detail_is_sale'] =  ($prd['sell_prc'] > $prdCart->pay_prc) ? 'Y' : '';
	$_replace_code[$_file_name]['detail_prd_milage'] = $prd['milage'];
	$_replace_code[$_file_name]['detail_r_prd_milage'] = $prd['milage']>0?showExchangeFee($prd['milage']):'';
	$_replace_code[$_file_name]['detail_prd_milage2'] = parsePrice($prd['prd_milage'], true);
	$_replace_code[$_file_name]['detail_member_milage'] = parsePrice($prd['member_milage'], true);
	$_replace_code[$_file_name]['detail_event_milage'] = parsePrice($prd['event_milage'], true);
	$_replace_code[$_file_name]['detail_all_milage'] = parsePrice($prd['all_milage'], true);
	$_replace_code[$_file_name]['detail_event_prc'] = ($prd['event_sale'] == 'Y' && $prdCart->sale2 > 0) ? number_format($prd['sell_prc']-$prdCart->sale2) : '';
	$_replace_code[$_file_name]['detail_msale_prc'] = ($prdCart->sale4 > 0) ? parsePrice($prdCart->sale4, true) : '';
	$_replace_code[$_file_name]['detail_r_msale_prc'] = ($prdCart->sale4 > 0) ? showExchangeFee($prdCart->sale4) : '';
	$_replace_code[$_file_name]['detail_cod_prc'] = parsePrice($prdCart->getData('cod_prc'), true);
	$_replace_code[$_file_name]['detail_min_num'] = $prd['min_ord'];
	$_replace_code[$_file_name]['detail_max_num'] = $prd['max_ord'];
	$_replace_code[$_file_name]['detail_content1'] = prdContent(1);
	if($cfg['mobile_use'] == 'Y' && $_SESSION['browser_type'] == 'mobile' && $prd['use_m_content'] == 'Y' && trim(strip_tags($prd['m_content'], '<img><iframe><video><embed><object>'))) {
		$_replace_code[$_file_name]['detail_content2'] = stripslashes($prd['m_content']);
	}else{
		$_replace_code[$_file_name]['detail_content2'] = prdContent(2);
	}
    $_replace_code[$_file_name]['prd_link_url'] = $root_url."/shop/detail.php?pno=".$prd['hash'];

    // 택배사 정보
    $content_replace = array();
    $delivery_qry = "select group_concat(name separator ', ') from {$tbl['delivery_url']} where 1 ";
    if ($cfg['use_partner_delivery'] == 'Y') { // 입점사별 배송 사용 시
        $delivery_qry .= " and partner_no='{$prd['partner_no']}'";
    }
    $delivery_name = $pdo->row($delivery_qry);
    $content_replace['배송업체명'] = $delivery_name;

	$_replace_code[$_file_name]['detail_content3'] = prdContent(3, $content_replace);
	$_replace_code[$_file_name]['detail_content4'] = prdContent(4, $content_replace);
	$_replace_code[$_file_name]['detail_content5'] = prdContent(5, $content_replace);
	$_replace_code[$_file_name]['detail_content6'] = prdContent(3, $content_replace);
	$_replace_code[$_file_name]['detail_content7'] = prdContent(4, $content_replace);
	$_replace_code[$_file_name]['detail_content8'] = prdContent(5, $content_replace);
	$_replace_code[$_file_name]['detail_total_qna'] = $prd['qna_cnt'];
	$_replace_code[$_file_name]['detail_product_no'] = $prd['no'];

    if ($_GET['d_preview'] == 'Y') {
        $_replace_code[$_file_name]['detail_content2'] = '<div class="__wing_d_preview_area">'.$_replace_code[$_file_name]['detail_content2'].'</div>';
    }

	// 더보기형 상품상세 설명
	if(checkCodeUsed('더보기형상품상세설명') == true) {
		$_more_height = ($_skin['detail_more_height'] > 0) ? $_skin['detail_more_height'] : 500;
		$_line = getModuleContent('detail_content2_more');
		$_tmp = lineValues("detail_content2_more", $_line, array(
			'content2' => '<link rel="stylesheet" type="text/css" href="'.$engine_url.'/_engine/skin_module/default/CSS/shop_detail_more.css">'.
						  '<div class="wing-detail-more-area" data-height="'.$_more_height.'" data-state="closed">'.
						  '<div class="wing-detail-more-contents" style="height:'.$_more_height.';">'.$_replace_code[$_file_name]['detail_content2'].'</div>'.
						  '<div class="wing-detail-more-cover"></div></div>',
			'more_view_class' => 'wing-detail-more-view',
			'more_hide_class' => 'wing-detail-more-hide',
			'more_open_link' => "openMoreDetail();",
		));

        if ($_GET['d_preview'] == 'Y') { // 미리보기 사용 시 더보기 해제
            $_tmp = lineValues("detail_content2_more", $_line, array(
                'content2' => '<div class="wing-detail-more-contents" style="height:'.$_more_height.';">'.$_replace_code[$_file_name]['detail_content2'].'</div>',
                'more_view_class' => 'wing-detail-more-hide',
                'more_hide_class' => 'wing-detail-more-hide',
            ));
        }

		$_replace_code[$_file_name]['detail_content2_more'] = listContentSetting($_tmp, $_line);
		unset($_line, $_tmp);
	}

		// 구매자 설명기입란 여부
	$_replace_code[$_file_name]['purchaser_explanation_yn'] = ($prd['purchaser_explanation_yn'] == 'Y') ? 'Y' : '';

	$_replace_code[$_file_name]=designTextEdit($_replace_code[$_file_name], 1);




	// 타임세일
	$_ts = $objCart->getData('ts');
	$_replace_code[$_file_name]['detail_timesale_ing'] = $_ts->use;
	$_replace_code[$_file_name]['detail_timesale_prc'] = ($prdCart->sale3 > 0) ? parsePrice($prdCart->sale3, true) : '';
	$_replace_code[$_file_name]['detail_r_timesale_prc'] = ($prdCart->sale3 > 0) ? showExchangeFee($prdCart->sale3) : '';

	if($_ts->use == 'Y' && $_ts->datee > 0) {
		$_replace_code[$_file_name]['detail_timesale_timer'] = "<span class='_timesale_timer _timesale_{$data['parent']}' data-timestamp='$_ts->datee'><script>printTimeSale($('._timesale_{$data['parent']}'));</script></span>";
	}

	$_replace_code[$_file_name]['detail_sell_prc_consultation'] = stripslashes($prd['sell_prc_consultation']);
	if($_replace_code[$_file_name]['detail_sell_prc_consultation']!="") $_replace_code[$_file_name]['detail_sell_prc'] = $_replace_code[$_file_name]['detail_sell_prc_consultation'];
	if($_replace_code[$_file_name]['detail_sell_prc_consultation']!="") $_replace_code[$_file_name]['detail_nml_prc'] = "";

	// 이/삼분류
	for($i = 4; $i <= 5; $i++) {
		$_colname1 = $_cate_colname[$i][1];
		if($cfg[$_colname1.'_mng'] == 'Y') {
			foreach($_cate_colname[$i] as $val) {
				$_colname = $val;
				$_replace_code[$_file_name][$val] = $prd[$val];
				if($prd[$val] > 0) $_replace_code[$_file_name][$val.'_name'] = stripslashes($pdo->row("select name from {$tbl['category']} where no='{$prd[$val]}'"));
			}
		}
	}

	if(checkCodeUsed('총상품재고')) {
		if($prd['stat'] == 3) $prd_qty = 0;
		else {
			if($prd['ea_type'] == 1) {
				$prd_qty = $pdo->assoc("select sum(if(qty < 0, 0, qty)) as qty, group_concat(if(force_soldout='N','Y','')) as unlimited from erp_complex_option where pno='{$prd['parent']}' and del_yn='N' and force_soldout!='Y'");
				if(strstr($prd_qty['unlimited'], 'Y')) {
					$prd_qty = '무제한';
				} else {
					$prd_qty = number_format($prd_qty['qty']);
				}
			} else {
				$prd_qty = '무제한';
			}
		}
		$_replace_code[$_file_name]['detail_stock_qty'] = $prd_qty;
	}

	$_tmp = "";
	$_line = getModuleContent("detail_fd_list");
	while($filed = prdFiledList($prd['no'])) {
		$filed['fd_img'] = $filed['upfile1'] ? "<img src=".getFileDir($filed['updir'])."/".$filed['updir']."/".$filed['upfile1'].">" : $filed['name'];
		$_tmp .= lineValues("detail_fd_list", $_line, $filed);
	}
	$_tmp = listContentSetting($_tmp, $_line);
	$_replace_code[$_file_name]['detail_fd_list'] = $_tmp;

	// 상품 정보 고시
	if($prd['fieldset'] > 0) {
		$_tmp = '';
		$_line = getModuleContent("detail_fdinfo_list");
		while($filed = prdFiledList($prd['no'], $prd['fieldset'])) {
			$filed['fd_img'] = $filed['upfile1'] ? "<img src=".getFileDir($filed['updir'])."/".$filed['updir']."/".$filed['upfile1'].">" : $filed['name'];
			$_tmp .= lineValues("detail_fdinfo_list", $_line, $filed);
		}
		$_tmp = listContentSetting($_tmp, $_line);
		$_replace_code[$_file_name]['detail_fdinfo_list'] = $_tmp;
	}

	// option
	$essential_opt_cnt = 0; // 필수 옵션의 개수
	if ($prd['prd_type'] != '4' && $prd['prd_type'] != '5' && $prd['prd_type'] != '6') {
		$_line_detail = getModuleContent('detail_opt_list');
		$_line_normal = getModuleContent('normal_opt_list');
		$_line_area = getModuleContent('area_opt_list');
		$_line_imgc = getModuleContent('detail_opt_img_list');
		$_line_txtc = getModuleContent('detail_opt_txt_list');

		$_make_detail_option = true;
		$_detail_option = $_normal_opt_list = $_area_opt_list = array();
		while($opt=prdOptionList(null, true)){
			$opt['area_option_id'] = 'area_opid_'.$opt['no'];

			$_detail_opt_list[] = $opt;
			if($opt['how_cal'] != 3 && $opt['how_cal'] != 4) $_normal_opt_list[] = $opt;
			else $_area_opt_list[] = $opt;

			if($opt['necessary'] == 'Y') {
				$essential_opt_cnt++;
			}
		}

		for($i = 0; $i <= 2; $i++) {
			switch($i) {
				case '0' : $suffix = 'detail'; break;
				case '1' : $suffix = 'normal'; break;
				case '2' : $suffix = 'area'; break;
			}

			$_tmp = '';
			$array = ${'_'.$suffix.'_opt_list'};
			$_line = ${'_line_'.$suffix};
			if(is_array($array)) {
				foreach($array as $opt) {
					$_tmp .= $opt['hidden_str'];
					$_tmp .= lineValues($suffix.'_opt_list', $_line, $opt);
				}
			}
			$_tmp = listContentSetting($_tmp, $_line);
			$_replace_code[$_file_name][$suffix.'_opt_list'] = $_tmp;
			unset($_tmp);
		}
		$_make_detail_option = false;
	}

	$_multi_page_module = getModuleContent('detail_multi_option_list');
	if($essential_opt_cnt == 0) {
		$_replace_code[$_file_name]['detail_multi_option_prc'] = $prdCart->getData('pay_prc', true);
	}
	if(!$prd['sell_prc_consultation']) {
		$_replace_code[$_file_name]['detail_multi_option_list'] = $_multi_page_module[1].$_multi_page_module[3];
	}

	$_replace_code[$_file_name]['detail_form_end'] = "<input type=\"hidden\" name=\"opt_no\" value=\"".$opt_no."\"></form>";
	if($opt_no < 1 && !$prd['sell_prc_consultation']) $_replace_code[$_file_name]['detail_multi_option_list'] = $_multi_page_module[4];
	for($refkey = 1; $refkey <= $cfg['refprds']; $refkey++) {
		$_refkey = $refkey == 1 ? '' : $refkey;

		$_tmp = "";
		$_line = getModuleContent('detail_ref'.$_refkey.'_list');
		ob_start();
		$_para1 = (!$_skin['detail_ref'.$_refkey.'_list_imgw']) ? $cfg['thumb3_w'] : $_skin['detail_ref'.$_refkey.'_list_imgw'];
		$_para2 = (!$_skin['detail_ref'.$_refkey.'_list_imgh']) ? $cfg['thumb3_w'] : $_skin['detail_ref'.$_refkey.'_list_imgh'];
		$_para3 = (!$_skin['detail_ref'.$_refkey.'_list_namecut']) ? 100 : $_skin['detail_ref'.$_refkey.'_list_namecut'];
		$_para4 = (!$_skin['detail_ref'.$_refkey.'_list_cols']) ? 5 : $_skin['detail_ref'.$_refkey.'_list_cols'];
		$_para5 = (!$_skin['ref'.$_refkey.'_product_img_fd']) ? 3 : $_skin['ref'.$_refkey.'_product_img_fd'];
		//관련상품,이미지W,이미지H,글자줄임,한줄,이미지번호
		$multi_idx = 0;
		$bak_prd = $prd;
		while($prd3 = refPrdList($bak_prd['parent'], $_para1, $_para2, $_para3, $_para4, $_para5, $refkey)){
			$multi_idx++;
			$prd3['buy_ea_n'] = "buy_ea[".$multi_idx."]";
			$prd3['nidx'] = $multi_idx;
			unset($prd_refOptionRes, $opt_no, $prdOptionRes);
			$prd3['option_select'] = "";
			$prd = $prd3;
			$opt_no = "";
			while($ref_opt = prdOptionList(null,null,null,null,$multi_idx)) {
				$prd3['option_select'] .= $ref_opt['hidden_str'].$ref_opt['option_str'];
			}
			$prd3['ck_box']="<input type=\"checkbox\" name=\"pno[".$multi_idx."]\" value=\"".$prd3['hash']."\">";
			echo lineValues("detail_ref_list", $_line, $prd3);
		}
		$prd = $bak_prd;
		$_tmp = ob_get_contents();
		ob_end_clean();

		$_tmp = listContentSetting($_tmp, $_line);
		// 장바구니 담기 폼
		if($_tmp) {
		$_tmp = "<form name=\"refFrm$refkey\" method=\"post\" action=\"".$root_url."/main/exec.php\" target=\"hidden".$now."\">
<input type=\"hidden\" name=\"exec_file\" value=\"cart/cart.exe.php\">
<input type=\"hidden\" name=\"rel_cart\" value=\"Y\">
<input type=\"hidden\" name=\"next\" value=\"1\">
<input type=\"hidden\" name=\"qd\" value=\"$_GET[type]\">
<input type=\"hidden\" name=\"refparent\" value=\"{$prd['parent']}\">
<input type=\"hidden\" name=\"refkey\" value=\"$refkey\">
".$_tmp."</form>";
		}

		$_replace_code[$_file_name]['detail_ref'.$_refkey.'_cart_url']="javascript:refPrdCart($refkey, 1);";
		$_replace_code[$_file_name]['detail_ref'.$_refkey.'_buy_url']="javascript:refPrdCart($refkey, 2);";
		$_replace_code[$_file_name]['detail_ref'.$_refkey.'_list']=$_tmp;
		unset($refPrdRes);
	}

	// 상품 옵션 이미지
	$_tmp = "";
	for($ii=1; $ii<=2; $ii++){
		$_line = getModuleContent("detail_opt_img".$ii."_list");
		if($_line[2]){
			while($opt_img = prdOptionImgList($prd['parent'])){
				$_tmp .= lineValues("detail_opt_img".$ii."_list", $_line, $opt_img);
			}
			$_tmp = listContentSetting($_tmp, $_line);
			$_replace_code[$_file_name]["detail_opt_img".$ii."_list"]=$_tmp;
		}
	}

	// 위시리스트 담김
	if(is_array($member['wishlist'])) {
		$_replace_code[$_file_name]['detail_is_wish'] = $prd['is_wish'];
	}

	$_tmp = "";
	$_cpn_cnt = 0;
	$_line = getModuleContent("detail_cpn_list");
    if ($prd['prd_type'] == '1') {
        $_line = getModuleContent("detail_cpn_list");
        while($coupon = couponList()){
            $_tmp .= lineValues("detail_cpn_list", $_line, $coupon);
            $_cpn_cnt++;
        }
    }
	$_tmp = listContentSetting($_tmp, $_line);
	$_replace_code[$_file_name]['detail_cpn_list'] = $_tmp;
	$_replace_code[$_file_name]['detail_cpn_cnt'] = $_cpn_cnt;

	$prd_upurl = ($cfg['use_icb_storage'] == 'Y' && $prd['upurl']) ? $prd['upurl'] : getFileDir($prd['updir']);
	$_replace_code[$_file_name]['detail_img1'] = $prd['upfile1'] ? $prd_upurl."/$prd[updir]/$prd[upfile1]" : $cfg['noimg1'];
	$_replace_code[$_file_name]['detail_img2'] = $prd['upfile2'] ? $prd_upurl."/$prd[updir]/$prd[upfile2]" : $cfg['noimg2'];
	$_replace_code[$_file_name]['detail_img3'] = $prd['upfile3'] ? $prd_upurl."/$prd[updir]/$prd[upfile3]" : $cfg['noimg3'];

	$_replace_code[$_file_name]['detail_today_dlv'] = "";
	if($cfg['compare_today_start_use'] == 'Y') {
		$_replace_code[$_file_name]['detail_today_dlv'] = ($prd['compare_today_start']=='Y') ? 'Y':'';
		$_replace_code[$_file_name]['detail_today_time'] = $cfg['compare_today_time'].":00";
	}

	if(CheckCodeUsed($_replace_hangul[$_file_name]['product_add_image_list'])) {
		$_replace_code[$_file_name]['detail_img2'] .= "#addimg";
		$_tmp = "";
		$_line = getModuleContent("product_add_image_list");
		if($cfg['up_aimg_sort'] == "Y" && fieldExist($tbl['product_image'], "sort")) {
			$orderby = "order by `sort` asc, `no` desc";
		}
		$img_res = $pdo->iterator("select * from `{$tbl['product_image']}` where `filetype` in (2, 8) and `pno`='$prd[parent]' $orderby");
        foreach ($img_res as $img_data) {
			if($img_data['filetype'] == 8 || $img_data['filetype'] == 9) {
				if($cfg['use_cdn'] == 'Y') {
					$file_dir = getFileDir('_data/product');
				} else {
                    if ($cfg['ssl_type'] == 'Y') {
                        $file_dir = 'https://img.mywisa.com/img/'.$cache_account['account_id'].'/';
                    } else {
    					$file_dir = 'http://'.$cache_account['account_id'].'.img.mywisa.com';
                    }
				}
			}
			else $file_dir = ($img_data['upurl']) ? $img_data['upurl'] : getFileDir($img_data['updir']);

			$img_data['add_img'] = $file_dir.preg_replace('/\/+/', '/', "/".$img_data['updir']."/".rawurlencode($img_data['filename'])).'#addimg';
			$_tmp .= lineValues("product_add_image_list", $_line, $img_data);
		}
		$_line[1] = contentReset($_line[1], $_file_name);
		$_tmp = listContentSetting($_tmp, $_line);
		$_replace_code[$_file_name]['product_add_image_list'] = "<div id='product_add_image_list'>$_tmp</div>";
		$_replace_code[$_file_name]['detail_img2'] = str_replace('#addimg', '', $_replace_code[$_file_name]['detail_img2']);
	}

	// 정상판매시/품절시 표시내용 변화
	$prd['cname'] = $_cno1['name'];
	if($prd['stat'] == 2) {
		if(checkCodeUsed('정상판매시내용')) {
			$_replace_code[$_file_name]['detail_not_soldout'] = getModuleContent('detail_not_soldout');
		}
		if(checkCodeUsed('정상판매시내용2')) {
			$_replace_code[$_file_name]['detail_not_soldout2'] = getModuleContent('detail_not_soldout2');
		}
	}
	if($prd['stat'] == 3) {
		if(checkCodeUsed('품절시내용')) {
			$_replace_code[$_file_name]['detail_soldout'] = getModuleContent('detail_soldout');
		}
		if(checkCodeUsed('품절시내용2')) {
			$_replace_code[$_file_name]['detail_soldout2'] = getModuleContent('detail_soldout2');
		}
	}

	// Naver Checkout
	if($cfg['checkout_id'] && $cfg['checkout_key'] && ($cfg['checkout_auth'] == 'Y' || $member['no'] == 0) && !$prd['qty_rate']) {
		$allcates = array();
		for($i = 1; $i <= 3; $i++) {
			$_cfield = $_cate_colname[1][$i];
			if($prd[$_cfield]) $allcates[] = $prd[$_cfield];
		}
		$_allcates = implode(',', $allcates);
		$_is_private = $pdo->row("select count(*) from `$tbl[category]` where `no` in ($_allcates) and `private` = 'Y'"); // 개인결제창 카테고리는 제외
		if($_is_private == 0) {
			list($btn_type, $btn_color) = explode('_', $cfg['checkout_detail_btn']);
			$checkout_btn_enable = ($prd['stat'] == 2) ? 'Y' : 'N';
			$checkout_btn_prdable = ($prd['checkout'] == 'Y' && $prd['sell_prc'] > 0) ? 'Y' : 'N';
			if($checkout_btn_prdable != 'Y') $checkout_btn_enable = 'N';
			if($prd['max_ord_mem'] > 0) $checkout_btn_enable = 'N';

			$is_mobile = ($mobile_browser == 'mobile' || $_SESSION['browser_type'] == 'mobile') ? 'mobile' : 'pc';

			$checkout_button = 'naverPayButton.js';
			if($is_mobile == 'mobile') {
				$checkout_button = 'mobile/naverPayButton.js';
				if(!$cfg['m_checkout_detail_btn']) $cfg['m_checkout_detail_btn'] = 'MA_1';
				list($btn_type, $btn_color) = explode('_', $cfg['m_checkout_detail_btn']);
			}

			$checkout = new Checkout();

			$_replace_code[$_file_name]['detail_checkout_url'] = "
			<div id='naver_checkout_buttons'>
				<script type='text/javascript' src='//{$checkout->testplug}pay.naver.com/customer/js/$checkout_button' charset='UTF-8'></script>
				<script type='text/javascript'>
					var is_mobile = '$is_mobile';
					var npay_target = '$cfg[npay_target]';
					naver.NaverPayButton.enable = '$checkout_btn_enable';
					naver.NaverPayButton.prdable = '$checkout_btn_prdable';
					naver.NaverPayButton.apply(
						{
							BUTTON_KEY: '$cfg[checkout_btn_key]',
							TYPE : '$btn_type',
							COLOR : $btn_color,
							COUNT : $cfg[checkout_wish],
							ENABLE : '$checkout_btn_enable',
							BUY_BUTTON_HANDLER : buy_nc,
							WISHLIST_BUTTON_HANDLER : wishlist_nc,
							'':''
						}
					);
				</script>
			</div>
			";
		}
	}

    // 카카오페이 구매
    if ($scfg->comp('use_talkpay') == true && $scfg->comp('talkpay_authkey') == true) {
        $talkpay = new KakaoTalkPay($scfg);
        $_replace_code[$_file_name]['detail_talkpay_url'] = $talkpay->printButton($prd);
    }

	// Payco
	if($prd['prd_type'] == '1' && $cfg['use_payco'] == 'Y' && $cfg['payco_sellerKey']) {
		if($_SESSION['browser_type'] == 'mobile') {
			$_payco_btn_url = 'https://static-bill.nhnent.com/payco/checkout/js/payco_mobile.js';
			$_payco_btn_type = $cfg['payco_type3_sel'];
		} else {
			$_payco_btn_url = 'https://static-bill.nhnent.com/payco/checkout/js/payco.js';
			$_payco_btn_type = $cfg['payco_type1_sel'];
		}
		$_replace_code[$_file_name]['detail_payco_url'] = "
		<div id='payco_detail_btn'></div>
		<script type='text/javascript' src='$_payco_btn_url'></script>
		<script type='text/javascript'>
		Payco.Button.register({
			SELLER_KEY : '$cfg[payco_sellerKey]',
			ORDER_METHOD : 'CHECKOUT',
			BUTTON_TYPE : '$_payco_btn_type',
			BUTTON_HANDLER : buy_payco,
			BUTTON_HANDLER_ARG : ['param1', 'param2'],
			DISPLAY_PROMOTION : 'Y',
			DISPLAY_ELEMENT_ID : 'payco_detail_btn',
			\"\" : \"\"
		});
		</script>
		";
	}

	// SNS 연동
	$_sns_title = urlencode(iconv(_BASE_CHARSET_, "UTF-8", $prd['name']." → ".number_format($prd['sell_prc'])."원"));
	$_sns_link = urlencode($root_url."/shop/detail.php?pno=".$prd['hash']);

	$_sns_links[1] = "http://twitter.com/intent/tweet?text=".$_sns_title."&url=".$_sns_link;
	$_sns_links[2] = "http://www.facebook.com/sharer/sharer.php?u=".urlencode($root_url."/shop/detail.php?pno=".$prd['hash']);
	$tmp = urlencode('"'.urldecode($_sns_title).'":');
	$_sns_links[3] = "http://me2day.net/posts/new?new_post[body]=".$tmp.$_sns_link;
	$_sns_links[4] = "http://yozm.daum.net/api/popup/post?prefix=".$_sns_title."&link=".$_sns_link;

	$_replace_code[$_file_name]['sns_twitter_url']=$_sns_links[1];
	$_replace_code[$_file_name]['sns_facebook_url']=$_sns_links[2];
	$_replace_code[$_file_name]['sns_me_url']=$_sns_links[3];
	$_replace_code[$_file_name]['sns_yozm_url']=$_sns_links[4];

	// 페이스북 좋아요
	if($_skin['fb_data_detail']) $_skin['fb_data_detail'] = '200';
	if($_skin['fb_layout_detail'] == 'N') $_fb_layout_detail = "data-layout='button_count'";

	$_replace_code[$_file_name]['fb_like_detail'] = "<script type='text/javascript'>(function(d, s, id) {var js, fjs = d.getElementsByTagName(s)[0];if (d.getElementById(id)) {return;}js = d.createElement(s); js.id = id;js.src = '//connect.facebook.net/ko_KR/all.js#xfbml=1'; fjs.parentNode.insertBefore(js, fjs); }(document, 'script', 'facebook-jssdk'));</script><div class='fb-like' data-href='$root_url/shop/detail.php?pno=$pno' data-width='$_skin[fb_data_detail]' $_fb_layout_detail data-show-faces='false'></div>";

	if($prd['upfile3']) {
		$_replace_code[$_file_name]['fb_like_detail'] .= "<link rel='image_src' href='$prd_upurl/$prd[updir]/$prd[upfile3]' />";
	}

	$jquery_ver = ($_skin['jquery_ver']) ? $_skin['jquery_ver'] : 'jquery-1.4.min.js';
	$jquery_ver = preg_replace('/^jquery-([0-9.]+).*$/', '$1', $jquery_ver);;

	if($cfg['zd_use'] == 'Y' && !$_GET['single_module']) {
		$cursor_a = ($cfg['zd_cursora'] / 100);
		if($prd['upfile1']) {
			if($prd['upfile1']) $upfile1 = $prd_upurl."/$prd[updir]/$prd[upfile1]";
		}
		echo "
		<script type='text/javascript' src='$engine_url/_engine/common/jquery-zoom.js'></script>
		<script type='text/javascript'>
		$(window).ready(function() {
			$('#product_add_image_list').find('img[src$=\"#addimg\"]').each(function(idx) {
				var t = $(this);
				if(idx == 0) t.attr('upfile1', '$upfile1');
				else t.attr('upfile1', t.attr('src'));
				t.mouseover(function() {
					attatchAddImage(this,$('#mainImg').width());
				});
			});

			$('#mainImg').mouseover(function() {
				$(this).pzoom(
					$('#mainImg').attr('upfile1'), {
						width: $cfg[zd_width],
						height: $cfg[zd_height],
						position: '$cfg[zd_position]',
						margin: $cfg[zd_margin],
						cursorc: '$cfg[zd_cursorc]',
						cursorb: '$cfg[zd_cursorb]',
						cursora: '$cursor_a',
						drag: $cfg[zd_drag]
					});
			});
		});
		</script>";
	}

	// 카카오링크 API(https://dev.kakao.com/docs/js/kakaotalklink)
	if($cfg['kakaolink_use']=="Y") {
		$_replace_code[$_file_name]['kakao_url'] = "<a id='kakao-link-btn' href='javascript:;'>";
		$_replace_code[$_file_name]['kakao_url_use'] = "Y";
	}else {
		$_replace_code[$_file_name]['kakao_url'] = "<a style='display:none;'>";
		$_replace_code[$_file_name]['kakao_url_use'] = "";
	}
	// 카카오스토리링크 API(https://dev.kakao.com/docs/js/kakaostory-share)
	if($cfg['kakaostory_use']=="Y") {
		$_replace_code[$_file_name]['kakaostory_url'] = "<a href='javascript:kakaostory();'>";
		$_replace_code[$_file_name]['kakaostory_url_use'] = "Y";
	}else {
		$_replace_code[$_file_name]['kakaostory_url'] = "<a style='display:none;'>";
		$_replace_code[$_file_name]['kakaostory_url_use'] = "";
	}

	if($cfg['recopick_use']==1) {
		if(($_SESSION['browser_type'] == 'pc')) {
			$_replace_code[$_file_name]['recopick']="<div id='".$cfg['recopick_id']."' data-widget_id='".$cfg['recopick_widget_id']."'></div>";
		}else {
			$_replace_code[$_file_name]['m_recopick']="<div id='".$cfg['recopick_id']."' data-widget_id='".$cfg['m_recopick_widget_id']."'></div>";
		}
	}

	// 주문 쿠폰 사용불가시 쿠폰 리스트 로딩 안함
	if($cfg['order_cpn_paytype'] != 3 && $prd['prd_type'] == '1') {
		// 개별상품쿠폰 존재 여부
		$_tmp = '';

		if($member['no'] > 0) {
			while($__cpn = myCouponList(1, null, array('is_prdcpn' => true))) {
				if($__cpn['prc_limit'] > 0 && $__cpn['prc_limit'] > $prdCart->pay_prc) continue;
				if($__cpn['sale_type'] == 'm' && $__cpn['sale_prc'] > $prdCart->pay_prc) continue;
				if(isCpnAttached($__cpn, $prd) == true) {
					$_tmp = 'usable';
					break;
				}
			}
		}
		$_replace_code[$_file_name]['detail_cpn_use_list'] = ($member['no']) ? $_tmp : '';
		unset($_tmp);
	}

	if($cfg['use_sbscr']=='Y' && $prd['prd_type'] == '1') {
		$sbscr_set_use = $pdo->row("select `use` from {$tbl['sbscr_set_product']} where pno='{$prd['parent']}'");
		if($sbscr_set_use == 'Y') {
			$_replace_code[$_file_name]['detail_sbscr_yn'] = 'Y';
		}
	}

	// 개별 배송비 상세 정보
	$_delivery_type = '기본 배송비';
	if(isset($cfg['use_prd_dlvprc']) == true && $cfg['use_prd_dlvprc'] == 'Y' && $prd['delivery_set'] > 0) {
		$_delivery_type = '개별 배송비';

		$dlv_data = $pdo->assoc("select * from {$tbl['product_delivery_set']} where no='{$prd['delivery_set']}'");
		$dlv_loop = json_decode($dlv_data['delivery_free_limit']);

		$_tmp = '';
		$_module = ($dlv_data['delivery_loop_type'] ==  'Y') ? 'detail_delivery_loop_list' : 'detail_delivery_type_list';
		$_line = getModuleContent($_module);
		if($dlv_data['delivery_type'] == 6) {
			$_tmp .= lineValues("detail_delivery_type_list", $_line, array(
				'price' => parsePrice($dlv_data['delivery_free_limit'], true)
			));
		} else {
			foreach($dlv_loop as $key =>$val) {
				$val['condition_s'] = parsePrice($val[0], true);
				$val['condition_e'] = ($key+1 == count($dlv_loop)) ? '' : parsePrice($val[1]-1, true);
				$val['price'] = parsePrice($val[2], true);
				$val['unit'] = ($dlv_data['delivery_type'] == 4) ? $cfg['currency_type'] : '개';
				if($dlv_data['delivery_loop_type'] == 'Y') {
					$val['unit'] = ($dlv_data['delivery_type'] == 4) ? sprintf(__lang_shop_prd_dlv_detail2__, $val['condition_s']) : sprintf(__lang_shop_prd_dlv_detail1__, $val['condition_s']);
				}

				$_tmp .= lineValues("detail_delivery_type_list", $_line, $val);
			}
		}

		$_tmp = listContentSetting($_tmp, $_line);
		$_replace_code[$_file_name]['detail_delivery_type_list'] = $_tmp;
	}
	$_replace_code[$_file_name]['detail_delivery_type'] = $_delivery_type;

	/* 아래 코드는 shop_detail 의 가장 아래에 배치해야 합니다. */
    if ($prd['prd_type'] == '1') {
			$_replace_code[$_file_name]['detail_addon_normal'] = contentReset(getModuleContent('detail_addon_normal'), $_file_name);
    } else {
        $_module_name = ($prd['prd_type'] == '4') ? 'detail_addon_set_list' : 'detail_addon_choice_list';
        $multi_idx = 1;
        $_line = getModuleContent($_module_name);
        $_tmp = '';
        $_prd = $prd;

        $spw = '';
		if ($scfg->comp('use_prd_perm', 'Y') == true) {
			$spf .= ", p.perm_dtl";
		}
        $sres = $pdo->iterator("
            select
                p.no, p.hash, p.name, p.updir, p.upfile3, p.w3, p.h3, p.sell_prc, p.ea_type, p.stat $spf
            from {$tbl['product']} p inner join {$tbl['product_refprd']} r on p.no=r.refpno
            where r.pno='{$prd['parent']}' and r.`group`=99 and p.stat in (2, 3, 4) order by r.sort asc
        ");
        $hideprd = 0;
        foreach ($sres as $set) {
            if ($set['stat'] == '4') {
                $hideprd++;
                continue;
            }
            $multi_idx++;
            $prd = $set;
            $set = parseSetPrd($set, $multi_idx);
            $prd = $_prd;
            $_tmp .= lineValues($_module_name, $_line, $set);
            $_tmp .= "<input type='hidden' name='pno[$multi_idx]' value='{$set['hash']}'>";
        }
        if ($_module_name == 'detail_addon_set_list') { // 일반세트상품인 경우에만
            $_tmp .= "<input type='hidden' name='hideprd' value='{$hideprd}'>";
        }
        $_replace_code[$_file_name][$_module_name] =
            "<input type='hidden' name='pno[1]' value='{$prd['hash']}'>".
            contentReset(listContentSetting($_tmp, $_line), $_file_name);
	}

?>