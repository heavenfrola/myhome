<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  스킨모듈 공통코드
	' +----------------------------------------------------------------------------------------------+*/

	use Wing\Design\BannerGroup;


	// 공통 모듈 재선언
    $__total_cart = $pdo->row("select count(*) from {$tbl['cart']} where 1 ".mwhere());
    if ($scfg->comp('use_set_product', 'Y') == true) { // 세트 사용 시 세트당 장바구니 1개로 표현
        $__total_cart -= $pdo->row("select count(*)-count(distinct set_idx) from {$tbl['cart']} where set_idx!='' ".mwhere());
    }

	if($cfg['use_sbscr'] == 'Y') {
		$__total_cart += $pdo->row("select count(*) from `{$tbl['sbscr_cart']}` where `guest_no`='{$_SESSION['guest_no']}' and `member_no`='{$member['no']}'");
	}
	$_replace_code['common']['total_cart'] = $__total_cart;
	$_replace_code['common']['total_wish'] = ($member['no'] > 0) ? number_format($pdo->row("select count(*) from ".$tbl['product']." p inner join ".$tbl['wish']." w on p.no = w.pno where w.member_no = '{$member['no']}' and p.stat in (2, 3)")) : 0;

	// 에스크로 가입 확인
	if($cfg['card_pg'] != ''){
		if($cfg['card_pg'] == "dacom") $_replace_code['common']['escrow_confirm_url'] = "javascript:goValidEscrow('".$cfg['card_dacom_id']."');";
		if($cfg['card_pg'] == "kcp") $_replace_code['common']['escrow_confirm_url'] = "https://admin.kcp.co.kr/Modules/escrow/kcp_pop.jsp?site_cd=".$cfg['card_site_cd'];
		if($cfg['card_pg'] == "allat") $_replace_code['common']['escrow_confirm_url'] = "http://allatpay.com/servlet/AllatBiz/svcinfo/si_escrowview.jsp?menu_id=S0305&business_no=".numberOnly($cfg['company_biz_num']);
		if($cfg['card_pg'] == "inicis") {
            switch($cfg['pg_version']) {
                case 'INILite' :
                    $_inicis_mid = $cfg['card_inicis_id'];
                    break;
                case 'INIweb' :
                    $_inicis_mid = $cfg['card_web_id'];
                    break;
                default :
                    $_inicis_mid = $cfg['card_mall_id'];
            }
            $_replace_code['common']['escrow_confirm_url'] = 'https://mark.inicis.com/mark/escrow_popup.php?mid='.$_inicis_mid;
        }
		if($cfg['card_pg'] == 'allthegate') $_replace_code['common']['escrow_confirm_url'] = "http://www.allthegate.com/hyosung/paysafe/escrow_check.jsp?service_id=".$cfg['allthegate_StoreId']."&biz_no=".numberOnly($cfg['company_biz_num']);
		if($cfg['card_pg'] == 'kspay') $_replace_code['common']['escrow_confirm_url'] = "http://pg.ksnet.co.kr/index.php?mid=b5-4-4&entr_numb=2118760782".numberOnly($cfg['company_biz_num']);
		if ($cfg['card_pg'] == 'nicepay') {
			$cono = explode('-', $cfg['company_biz_num']);
			$_replace_code['common']['escrow_confirm_url'] = "https://www.nicepay.co.kr/apply/progress/escrow.do?coNo1=".$cono[0]."&coNo2=".$cono[1]."&coNo3=".$cono[2];
		}
	}

	$root_domain_name = preg_replace('@.*://@', '', $root_url);
	$_replace_code['common']['root_domain_name'] = $root_domain_name;
	for($i = 1; $i <= 8; $i++) {
		$_replace_code['common']['hostingby'.$i] = "<a href='https://by.wisa.co.kr/$root_domain_name/hosting' target='_blank'><img src='$engine_url/_engine/common/hostingby/hostingby_0$i.gif' style='vertical-align: middle;' /></a>";
	}
	for($i = 1; $i <= 3; $i++) {
		$_replace_code['common']['hostingbyt'.$i] = "<a href='https://by.wisa.co.kr/$root_domain_name/hosting' target='_blank'>".$_replace_code['common']['hostingbyt'.$i]."</a>";
	}

	$_png24 = @preg_match("/png$/", $_title_img_name) ? ' class="png24"' : '';
	$_replace_code['common_module']['title_img'] = $_title_img_name ? '<img src="'.$_skin['url'].'/img/title/'.$_title_img_name.'"'.$_png24.'>' : '';
	$_replace_code['common_module']['product_box'] = getModuleContent("product_box");
	for($i = 1; $i <= 5; $i++) {
		$_replace_code['common_module']['product_box'.$i] = getModuleContent("product_box".$i);
	}

	if($member['no']){
		for($ii=1; $ii<11; $ii++) {
			$_replace_code['common_module']['member_login'.$ii] = getModuleContent("member_login".$ii);
		}
	}else{
		if(!$common_return_url) {
			if($cfg['member_return_page'] == '3' && $cfg['member_return_page_custom']) $common_return_url = $cfg['member_return_page_custom'];
			elseif($cfg['member_return_page'] == '1'){
				$common_return_url = ($_file_name == 'member_login.php') ? $_SERVER['HTTP_REFERER'] : getURL();
			}
			if(!$common_return_url) $common_return_url = $root_url;
		}

		$_replace_code['common_module']['common_login_form_start'] = '<form name="commonLoginFrm" method="post" action="'.$root_url.'/main/exec.php" target="hidden'.$now.'" onSubmit="return checkLoginFrm(this)"><input type="hidden" name="exec_file" value="member/login.exe.php"><input type="hidden" name="rURL" value="'.$common_return_url.'">';
		$_replace_code['common_module']['common_login_form_end'] = '</form>';

		$_replace_code['common_module']['common_ord_form_start'] = '<form name="commonOrdFrm" method="post" action="'.$root_url.'/main/exec.php" target="hidden'.$now.'" onSubmit="return checkGuestOrderFrm(this)" style="margin:0px"><input type="hidden" name="exec_file" value="mypage/order_detail.php"><input type="hidden" name="exec" value="orderDetail" />';
		$_replace_code['common_module']['common_ord_form_end'] = "</form>";

		for($ii=1; $ii<11; $ii++) {
			$_replace_code['common_module']['member_logout'.$ii] = getModuleContent("member_logout".$ii);
		}
	}
	$_replace_code['common_module']['prd_search_form_start'] = '<form method="get" action="'.$root_url.'/shop/search_result.php">';
	$_replace_code['common_module']['prd_search_form_end'] = '</form>';

	// 최근 본 상품
	if($cfg['today_click_ok']){
		$_line = getModuleContent('recent_view_list');
		if(!$_skin['recent_view_imgw']) $_skin['recent_view_imgw'] = $cfg['today_click_img_width'];
		if(!$_skin['recent_view_imgh']) $_skin['recent_view_imgh'] = $cfg['today_click_img_height'];
		if(!$_skin['recent_view_total']) $_skin['recent_view_total'] = $cfg['today_cilck_limit'];
		if(!$_skin['recent_view_namecut']) $_skin['recent_view_namecut'] = $cfg['today_click_title_cut'];
		$_tmp = '';
		while($clickprd = clickPrdLoop($_skin['recent_view_namecut'], $_skin['recent_view_imgw'], $_skin['recent_view_imgh'], $_skin['recent_view_total'], 3)) {
            if (!($clickprd['stat'] >= '2' && $clickprd['stat'] <= '3')) continue; // 숨김, 휴지통 상품은 패스
			$_tmp .= lineValues("recent_view_list", $_line, $clickprd, "common_module");
		}
		$_tmp = listContentSetting($_tmp, $_line);
		if($_skin['recent_view_box_use'] == 'Y' && $_skin['recent_view_boxw'] && $_skin['recent_view_boxy'] && $_skin['recent_view_box_scroll']) {
			if(!@defined("__auto_scroll_included__")) {
				$_defer_scripts .= "<script type=\"text/javascript\" src=\"".$engine_url."/_engine/common/auto_scroll.js\"></script>\n";
				@define("__auto_scroll_included__", 1);
			}
			$_tmp = "<script type=\"text/javascript\">wing_recent_view_scroll=".$_skin['recent_view_box_scroll'].";</script>
<div style=\"position:relative; width:".$_skin['recent_view_boxw']."px; height:".$_skin['recent_view_boxy']."px; overflow:hidden;\">
<div id=\"wing_recent_view_box\" style=\"position:absolute; top:0px; left:0px;\">".$_tmp."</div>
</div>";
		}
		$_replace_code['common_module']['recent_view_list'] = $_tmp;
	}

	// 상품평 등록폼
	if($_file_name == "shop_detail.php" || $_file_name == "shop_product_qna.php" || $_file_name == "shop_product_qna_list.php" || $_file_name == "shop_product_review.php" || $_file_name == "shop_product_review_list.php" || $_file_name == 'mypage_qna_list.php' || $_file_name == 'mypage_review_list.php'){
		$_review_form_tmp['form_start'] = "<div id=\"revWriteDiv\" style=\"display:none\">
<script>
var ra='{$ra}';
var review_strlen='{$cfg['product_review_strlen']}';
var review_con_strlen='{$cfg['product_review_con_strlen']}';
</script>
<form name=\"revFrm\" method=\"post\" action=\"".$root_url."/main/exec.php\" target=\"hidden".$now."\" onSubmit=\"return checkRevFrm(this)\" enctype=\"multipart/form-data\">
<input type=\"hidden\" name=\"exec_file\" value=\"shop/review_reg.exe.php\">
<input type=\"hidden\" name=\"pno\" value=\"".$pno."\">
<input type=\"hidden\" name=\"no\" value=\"".$rno."\">
<input type=\"hidden\" name=\"ono\" value=\"".$ono."\">
<input type=\"hidden\" name=\"startup\" value=\"".$_GET['startup']."\">
";
		if($cfg['product_review_use_editor'] == "Y") {
			$neko_id = ($rno > 0) ? 'product_review_'.$rno : "product_review_temp_".$now;
			$_review_form_tmp['form_start'] .= "<input type=\"hidden\" name=\"neko_id\" value=\"".$neko_id."\">";
		}
		$_review_form_tmp['form_end']="
</form>
</div>

<form name=\"reviewDelFrm\" method=\"post\" action=\"".$root_url."/main/exec.php\" target=\"hidden".$now."\">
<input type=\"hidden\" name=\"exec_file\" value=\"\">
<input type=\"hidden\" name=\"no\" value=\"\">
<input type=\"hidden\" name=\"exec\" value=\"delete\">
<input type=\"hidden\" name=\"pno\" value=\"".$pno."\">
</form>
";
		if($rno > 0) {
			$rdata = $pdo->assoc("select * from {$tbl['review']} where no='$rno'");
			if(empty($rdata['no']) == false) {
				$rdata = array_map('stripslashes', $rdata);
				$_review_form_tmp = array_merge($_review_form_tmp, $rdata);
			}
		}

		if($cfg['product_review_use_editor'] == "Y") {
			$_review_form_tmp['form_end'] .= "
			<script type=\"text/javascript\" src=\"".$engine_url."/_engine/R2Na/R2Na.js\"></script>
			<script type=\"text/javascript\" src=\"".$engine_url."/_engine/smartEditor/js/HuskyEZCreator.js\"></script>
			<script type=\"text/javascript\">
				$('form[name=\"revFrm\"] textarea[name=\"content\"]:eq(0)').removeAttr('id').attr('id','revContent');
				var editor_code = \"".$neko_id."\";
				var editor_gr = \"product_review\";
			</script>
			";
		}

		if($cfg['usecap_review']=="Y" && $cfg['captcha_key']) {
			$_review_form_tmp['form_end'] .= "
			<script type='text/javascript'>
				if($('#grecaptcha_element').size()==1) {
					var onloadCallback = function() {
						grecaptcha_element_id = grecaptcha.render('grecaptcha_element', {
							'sitekey' : '$cfg[captcha_key]'
						});
					};
				}
			</script>
			<script src='https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit' async defer></script>";
		}

		$_review_form_tmp['cate'] = ($cfg['product_review_cate']) ? outPutCate("review", $rdata['cate']) : "";


		for($ii=1; $ii<3; $ii++) {
			if($rdata["upfile".$ii]) {
				$_replace_code['common_module']["product_review_img".$ii] = "<a href=\"".getFileDir($rdata['updir']).'/'.$rdata['updir'].'/'.$rdata['upfile'.$ii]."\" target=\"blank\"><b>".$rdata["upfile".$ii]."</b></a>";
				$_replace_code['common_module']["product_review_delimg".$ii] = "<input type=\"checkbox\" name=\"delfile$ii\" value=\"Y\">";
			}
		}

		// 제목 강제지정
		$_tmp = '';
		if($member['level'] > 1 && $cfg['review_fsubject'] == 'Y') $_tmp = getForceSubject('review', $rdata['title']);
		if(!$_tmp) $_tmp = getModuleContent('review_title_sel');
		$_review_form_tmp['review_title_sel'] = lineValues('review_title_sel', $_tmp, $rdata);
		unset($fsubject, $_tmp);

		//캡차
		$_review_cap = "<div style='display: inline-block' id='grecaptcha_element'></div>";
		if($member['no']){
			$_review_form_tmp['writer'] = reviewName($member,'review');
			if($cfg['usecap_review']=="Y" && $cfg['captcha_key'] && $cfg['usecap_member_review']=="Y" && $member['level']!=1) {
				$_review_form_tmp['review_cap'] = $_review_cap;
			}
		}else{
			if($cfg['usecap_review']=="Y" && $cfg['captcha_key'] && $cfg['usecap_nonmember_review']=="Y" && $member['level']!=1) {
				$_review_form_tmp['review_cap'] = $_review_cap;
			}
		}

		if($member['no'] || $_single_module_code == '상품평회원등록폼'){
			$_line1 = getModuleContent("product_review_login_form");
			$_replace_code['common_module']['product_review_login_form'] = lineValues('product_review_login_form', $_line1, $_review_form_tmp, 'common_module');
		}
		if(!$member['no']){
			$_line2 = getModuleContent("product_review_logout_form");
			$_replace_code['common_module']['product_review_logout_form'] = lineValues('product_review_logout_form', $_line2, $_review_form_tmp, 'common_module');
		}
		unset($_line1, $_line2, $_review_form_tmp);

	// 상품질답 등록폼
		$_qna_form_tmp['form_start'] = "<div id=\"qnaWriteDiv\" style=\"display:none\">
<form name=\"qnaFrm\" method=\"post\" action=\"".$root_url."/main/exec.php\" target=\"hidden".$now."\" onSubmit=\"return checkQnaFrm(this)\" style=\"margin:0px\" enctype=\"multipart/form-data\">
<input type=\"hidden\" name=\"exec_file\" value=\"shop/qna_reg.exe.php\">
<input type=\"hidden\" name=\"pno\" value=\"".$pno."\">
<input type=\"hidden\" name=\"no\" value=\"\">
<input type=\"hidden\" name=\"exec\" value=\"\">
";
		if($cfg['product_qna_use_editor'] == "Y") {
			$neko_id = "product_qna_temp_".$now;
			$_qna_form_tmp['form_start'] .= "<input type=\"hidden\" name=\"neko_id\" value=\"".$neko_id."\">";
		}
		$_qna_form_tmp['form_end']="
</form>
</div>
";
		if($cfg['product_qna_use_editor'] == "Y") {
			seVerchk();
			$_qna_form_tmp['form_end'] .= "
			<script type=\"text/javascript\" src=\"".$engine_url."/_engine/R2Na/R2Na.js\"></script>
			<script type=\"text/javascript\" src=\"".$engine_url."/_engine/smartEditor/js/HuskyEZCreator.js\"></script>
			<script type=\"text/javascript\">
				$('form[name=\"qnaFrm\"] textarea[name=\"content\"]:eq(0)').removeAttr('id').attr('id','qnaContent');
				var editor_code = \"".$neko_id."\";
				var editor_gr = \"product_qna\";
			</script>
			";
		}

		if($cfg['usecap_qna']=="Y" && $cfg['captcha_key']) {
			$_qna_form_tmp['form_end'] .= "
				<script type='text/javascript'>
					if($('#grecaptcha_element2').size()==1) {
						var onloadCallback2 = function() {
							grecaptcha_element_2_id = grecaptcha.render('grecaptcha_element2', {
								'sitekey' : '$cfg[captcha_key]'
							});
						};
					}
				</script>
				<script src='https://www.google.com/recaptcha/api.js?onload=onloadCallback2&render=explicit' async defer></script>";
		}

		$_qna_form_tmp['cate'] = ($cfg['product_qna_cate']) ? outPutCate("qna",$data['cate']) : "";
		$_qna_form_tmp['secret'] = ($cfg['product_qna_secret']=="D") ? "<input type=\"checkbox\" name=\"secret\" value=\"Y\"> ".__lang_board_name_privatePost__ : "<input type=\"hidden\" name=\"secret\" value=\"".$cfg['product_qna_secret']."\">";

		for($ii=1; $ii<3; $ii++) {
			if($data["upfile".$ii]) {
				$_replace_code['common_module']["product_qna_img".$ii] = "<a href=\"".getFileDir($data['updir']).'/'.$data['updir'].'/'.$data['upfile'.$ii]."\" target=\"blank\"><b>".$data["upfile".$ii]."</b></a>";
				$_replace_code['common_module']["product_qna_delimg".$ii] = "<input type=\"checkbox\" name=\"delfile$ii\" value=\"Y\">";
			}
		}

		// 제목 강제지정
		$_tmp = '';
		if($member['level'] > 1 && $cfg['qna_fsubject'] == 'Y') $_tmp = getForceSubject('qna');
		if(!$_tmp) $_tmp = getModuleContent('qna_title_sel');
		$_qna_form_tmp['qna_title_sel'] = $_tmp;
		unset($fsubject, $_tmp);

		$_qna_cap = "<div style='display: inline-block' id='grecaptcha_element2'></div>";
		if($member['no']){
			$_qna_form_tmp['writer'] = reviewName($member,'qna');
			if($cfg['usecap_qna']=="Y" && $cfg['captcha_key'] && $cfg['usecap_member_qna']=="Y" && $member['level']!=1) {
				$_qna_form_tmp['qna_cap'] = $_qna_cap;
			}
			$_line = getModuleContent("product_qna_login_form");
			$_replace_code['common_module']['product_qna_login_form'] = lineValues("product_qna_login_form", $_line, $_qna_form_tmp, "common_module");
		}else{
			if($cfg['usecap_qna']=="Y" && $cfg['captcha_key'] && $cfg['usecap_nonmember_qna']=="Y" && $member['level']!=1) {
				$_qna_form_tmp['qna_cap'] = $_qna_cap;
			}
			$_line = getModuleContent("product_qna_logout_form");
			$_replace_code['common_module']['product_qna_logout_form'] = lineValues("product_qna_logout_form", $_line, $_qna_form_tmp, "common_module");
		}
		unset($_qna_form_tmp);
	}
	$rev_target_pno = 0;
	if(empty($prd['no']) == false && empty($prd['hash']) == false) {
		$rev_target_pno = $prd['no'];
	}
	$_replace_code['common']['detail_review_write2_url'] = "writeReviewWithoutRa($rev_target_pno, '');";

	if($_file_name == "shop_product_qna.php" || $_file_name == "shop_product_review.php"){
		// 상품게시판상품정보
		if($pno){
			$_line = getModuleContent("content_prd_info");
			$prd['pno'] = $pno;
			$prd['img'] = mainImg(150, 150);
			$prd['simg'] = mainImg($prd['w3'], $prd['h3'], 3);
			$prd['mimg'] = mainImg($prd['w2'], $prd['h2'], 2);
			$prd['icons'] = prdIcons();
			$_tmp = lineValues("content_prd_info", $_line, $prd, "common_module");
			$_replace_code['common_module']['content_prd_info'] = $_tmp;
		}
	}

	// 진행 중 설문조사
	$mainPollChk = mainPoll();
	$_line = getModuleContent("common_poll_list");
	$_tmp = "";
	if(is_array($mainPollChk) && $mainPollChk['no'] && $mainPollChk['fdate'] >= date("Y-m-d", $now)){
		$mainPollChk['result'] = $root_url."/shop/poll_list.php?no=".$mainPollChk['no'];
		while($mainPollItem = mainPollItem()){
			$mainPollItem['title'] = stripslashes($mainPollItem['title']);
			$mainPollItem['item'] = "<input type=\"radio\" name=\"poll\" value=\"".$mainPollItem['no']."\"> ".$mainPollItem['title'];
			$percent = ($mainPollItem['total']) ? ($mainPollItem['total']/$mainPollChk['total_vote'])*100 : 0;
			$mainPollItem['per'] = number_format($percent, 2);
			$_tmp .= lineValues("common_poll_list", $_line, $mainPollItem, "common_module");
		}
	}

	// 진행중설문조사
	$_tmp = listContentSetting($_tmp, $_line);
	$_tmp = lineValues("common_poll_list", $_tmp, $mainPollChk, "common_module");
	if(is_array($mainPollChk) && $mainPollChk['no']){
		$_tmp="<form method=\"post\" action=\"".$root_url."/main/exec.php\" target=\"hidden".$now."\" onSubmit=\"return checkCB(this.poll,'항목을')\">
<input type=\"hidden\" name=\"exec_file\" value=\"shop/poll.exe.php\">
<input type=\"hidden\" name=\"no\" value=\"".$mainPollChk['no']."\">".$_tmp."</form>";
	}

	$_replace_code['common_module']['common_poll_list'] = $_tmp;

	// 페이지별 모듈 재선언
	if(is_file($engine_dir."/_engine/skin_module/".$_file_name)) include_once $engine_dir."/_engine/skin_module/".$_file_name;

	// 퀵카트
	for($i = 1; $i <= 2; $i++) {
		if($cfg['use_quick_cart'.$i] == 'Y') {
			$quick_cart_interval = ($cfg['quick_cart_print'.$i] == 'N' && $cfg['quick_cart_interval'.$i] > 0) ? $cfg['quick_cart_interval'.$i] : 0;
			$_cartdata['quick_cart_list'] = "<div class='wing_quick_cart_layer$i' interval='$quick_cart_interval'></div>";
			$_cartdata['quick_cart_click_link'] = ($cfg['quick_cart_event'.$i] == 'c') ? "openQuickCart($i, 'click'); return false;" : 'return false;';

			$_tmp = getModuleContent('quick_cart_load'.$i);
			$_tmp = lineValues('quick_cart_load'.$i, $_tmp, $_cartdata, "common_module");
			$_replace_code['common_module']['quick_cart_load'.$i] = "<div class='quick_cart_parent quick_cart_parent$i' $_script>$_tmp</div>";
			unset($_tmp, $_cartdata, $_script);
		}
	}

	// 사용자 생성 모듈 선언
	if(is_array($_user_code)){
		$_chk_cate1 = $_chk_cate2 = array(); // 출력 분류 조건
		for($i = 1; $i <= 3; $i++) {
			$_tmp = ${'_cno'.$i};
			$_chk_cate1[] = $_tmp['no'];
			if(is_array($_tmp) == true) {
				for($x = 1; $x <= 4; $x++) {
					if($_tmp[100+$x] > 0) {
						$_chk_cate2[] = $_tmp[100+$x];
					}
				}
			}
		}
		$ori_cno1 = $_cno1;
		foreach($_user_code as $key=>$val){
			$current_user_code = $key;

			// 사용자코드별 롤오버 이미지 필드 선택
			$_over_img_fd = ($_user_code[$key]['over_product_img_fd'] != "N") ? $_user_code[$key]['over_product_img_fd'] : "N";
			$_over_img_fd = (!$_user_code[$key]['over_product_img_fd']) ? $_skin['over_product_img_fd'] : $_user_code[$key]['over_product_img_fd'];
			$_rollover = ($_over_img_fd != "N") ? $_over_img_fd : "";

			// 출력 분류 조건 필터
			$_pr_ebig = trim($_user_code[$key]['pr_ebig']);
			if($_pr_ebig) {
				$_pr_ebig = explode(',', $_pr_ebig);
				$_tmp_grp = ($_user_code[$key]['pr_ck_depth'] == 'Y') ? $_chk_cate2 : $_chk_cate1;
				if(in_array2($_tmp_grp, $_pr_ebig) == false) {
					continue;
				}
			}

			$_code_type = $_user_code[$key]['code_type'];
			$_code_name = userCodeName($key);

			$_hangul_code_name = userCodeName($key, 1);
			$_replace_hangul[$_file_name][$_code_name] = userCodeName($key, 1);

            if ($_module_name && $_module_name != $_code_name && $_module_name != "detail_addon_set_list" && $_module_name != "detail_addon_choice_list") {
                continue;
            }

			$_ctype = $_user_code[$key]['ctype'];
			$_page_type = $_user_code[$key]['page_type'];
			$_page_list = $_user_code[$key]['page_list'];
			$_orderby = $_user_code[$key]['orderby'];

			// 자동 스크롤
			$_auto_scroll = $_user_code[$key]['auto_scroll'];
			$_style_filter = $_user_code[$key]['style_filter'];
			$_scroll_content = $_user_code[$key]['scroll_content'];
			$_scroll_content = str_replace("\n", "", $_scroll_content);
			$_scroll_content = stripslashes($_scroll_content);
			$_as_unum = 0;
			$_page_list = str_replace(".".$_skin_ext['p'], ".php", $_page_list);

			if(!($_page_type == "a" || ($_page_type == "p" && @strchr($_page_list, "@".$_file_name."@")) || ($_page_type == 'p' && @strchr($_page_list, '@content_content_'.$cont.'.php@')))) continue;

			if($_code_type == "p"){
				$_replace_datavals[$_file_name][$_code_name] = $_replace_datavals['common_module']['product_box'];
				for($i = 1; $i <= 5; $i++) {
					$_replace_datavals[$_file_name][$_code_name] = $_replace_datavals['common_module']['product_box'.$i];
				}
			}
			$_line = ($_auto_scroll == "Y") ? $_scroll_content : getModuleContent($_code_name, "", $_file_name);

			if($_GET['single_module']) {
				if($_GET['single_module'] == $_code_name) {
					$_line[1] = $_line[3] = $_line[4] = '';
					${'page'.$key} = numberOnly($_GET['module_page']);
				} else {
					continue;
				}
			}

			$_replace_code[$_file_name][$_code_name]=""; // 초기화
			if($_code_type == "p"){
				list($b, $m, $s, $d4) = explode(",",$_user_code[$key]['cate']);
				$_user_cno = $b;
				if($m) $_user_cno = $m;
				if($s) $_user_cno = $s;
				if($d4) $_user_cno = $d4;
				$_user_cate = $pdo->assoc("select `no`, `cols`, `rows`, `cut_title`, `ctype`, `level` from `".$tbl['category']."` where `no`='$_user_cno' limit 1");;
				if(!$_user_cate['no']) continue;
				$_tmp="";

				ob_start();
				if($_auto_scroll == "Y") echo "<script type=\"text/javascript\" defer='defer'>\n"."var user_code_".$key."=new Array();\n";
				$_para1 = (!$_user_code[$key]['product_list_imgw']) ? $cfg['thumb3_w'] : $_user_code[$key]['product_list_imgw'];
				$_para2 = (!$_user_code[$key]['product_list_imgh']) ? $cfg['thumb3_h'] : $_user_code[$key]['product_list_imgh'];
				$_para3 = (!$_user_code[$key]['product_list_cols']) ? $_user_cate['cols'] : $_user_code[$key]['product_list_cols'];
				$_para3_2 = (!$_user_code[$key]['product_list_rows']) ? $_user_cate['rows'] : $_user_code[$key]['product_list_rows'];
                $_para4_maxcnt = (!$_user_code[$key]['product_list_maxcnt']) ? '' : $_user_code[$key]['product_list_maxcnt'];
				// 각 줄마다 상품 수 다를 경우
				$_para3_list = $_para3_line="";
				if(strpos($_para3, "/")){
					$_para3_list = @explode("/", $_para3);
					$_para3_line = @count($_para3_list);
					$_para3 = @array_sum($_para3_list);
				}
				$rows = $_para3*$_para3_2;
				$_para4 = (!$_user_code[$key]['product_list_namecut']) ? $_user_cate['cut_title'] : $_user_code[$key]['product_list_namecut'];
				// 상품이미지 선택 가능
				$_img_fd = (!$_user_code[$key]['product_img_fd']) ? 3 : $_user_code[$key]['product_img_fd'];
				$_uprd_where = " and `stat` in (2,3)";
				$prdlist_disable_tr = ($_user_code[$key]['product_disable_tr'] == 'Y') ? true : false;

				$sortxy = ($cfg['use_new_sortxy'] == 'Y' && in_array($_user_code[$key]['ctype'], array(4, 5))) ? true : false; // 2/3분류 정렬 사용 여부
				if($_user_code[$key]['ctype'] != 2 && $_user_code[$key]['ctype'] != 6 && $sortxy != true) {
					$_fname = $_cate_colname[1][$_user_cate['level']];
					$_uprd_sort = "`sort{$_fname}` desc";
				} else {
					if( $_user_code[$key]['ctype'] == "4" ) {
						$_fname = $_cate_colname[1][$_user_cate['level']];
						$_uprd_sort = "`sort_{$_fname}` asc";
					} else {
						$_uprd_sort = 'l.sort_big';
					}
				}

				if($_ctype == "2"){
					$_ecate = explode(",", $_user_code[$key]['cate']);
					$_uprd_where .= " and (";
					for($ii=0; $ii<count($_ecate); $ii++){
						if(!$_ecate[$ii]) continue;
						if($ii > 0) $_uprd_where .= " or ";
						$_uprd_where .= " l.nbig='$_ecate[$ii]'";
					}
					$_uprd_where .= ")";
					$_uprd_sort = "l.sort_big asc";
				}else{
					$_uprd_where .= prdWhereByCate($_user_cate);
				}
				if($_file_name == "shop_big_section.php"){
					$_uprd_where .= ($prdWhere != "" && $_user_code[$key]['use_cate_info'] == "Y") ? $prdWhere : "";
				}
				$_uprd_sort = $_orderby ? $_orderby : $_uprd_sort;
				if($_user_code[$key]['prd_sort_soldout'] == 'Y') {
					$_uprd_sort = " p.stat asc, ".$_uprd_sort;
				}
				if($_user_code[$key]['prd_hide_soldout'] == 'Y') {
					$_uprd_where .= " and stat!=3";
				}

				// 기획전 상품 출력 (이미지가로,세로,조건절,제목줄임,총데이터,한줄당데이터,이미지번호,정렬,TR사이꾸밈)
				if($_auto_scroll == "Y") $_para3 = 0;
				$_tmp_cno1 = $_cno1;
				$_cno1 = $_user_cate;
				$_line_tmp1 = $_line_tmp3=1;
				$_line_tmp2 = 0;

				$_paging_code = ($_user_code[$key]['paging_use'] == "Y" || defined('__MODULE_LOADER__') == true) ? $key : 0;

				if($_more_page > 0) {
					${'page'.$_paging_code} = 1;
					$rows = ($rows*$_more_page);
				} else if($module_page > 0) {
					$_paging_code = $key;
					${'page'.$_paging_code} = $_GET['page'.$_paging_code] = $page;
				}
				if($cfg['use_prd_perm'] == 'Y') {
					$_uprd_where .= " and perm_lst='Y'";
				}
				${'page'.$key} = $_GET['page'.$key];
				while($sprd = prdListSpecial($_para1,$_para2,$_uprd_where,$_para4,$rows,$_para3,$_img_fd,$_uprd_sort,"","","",$_para4_maxcnt)){
					$sprd['nidx'] = $nidx;
					$_line_value = lineValues($_code_name, $_line, $sprd, "", 1);
					if($_auto_scroll == "Y") echo "user_code_".$key."[".$_as_unum."]='".scriptOutFormat($_line_value)."';\n";
					else{
						echo $_line_value;
						// 각 줄마다 상품 수 다를 경우
						if($_para3_list && $_para3_line){
							$_line_tmp2 = $_line_tmp2 ? $_line_tmp2 : $_para3_list[0];
							if($_line_tmp1 % $_line_tmp2 == 0){
								if(isset($_spt_cnt) == false) $_spt_cnt = 0;
								$_spt_cnt++;
								if($NumTotalRec > $nidx) {
									echo "</tr></table><table class='tbl_prd_normal tbl_prd_spt tbl_prd_spt$_spt_cnt'><tr>";
								}
								if($_line_tmp3 == $_para3_2){
									echo "</tr>";
									break;
								}
								$_line_tmp2 = next($_para3_list);
								if(!$_line_tmp2) $_line_tmp2 = reset($_para3_list);
								$_line_tmp1 = 0;
								$_line_tmp3++;
							}
							$_line_tmp1++;
						}
					}
					$_as_unum++;
				}

				$_replace_code['common_module']['user_paging_'.$key]=${"page".$key."_result"}; // 기본방향
				$_replace_hangul['common_module']['user_paging_'.$key]="페이지선택(".$key.")";
				$_cno1 = $_tmp_cno1;
				if($_auto_scroll == "Y") echo "</script>\n";
				$_tmp = ob_get_contents();
				ob_end_clean();

				prdListRESET();
				$_tmp = listContentSetting($_tmp, $_line);

				$prdlist_disable_tr = false;
			}elseif($_code_type == "c"){
				$_replace_datavals[$_file_name][$_code_name] = $_user_code_typec['c'];
				$_tmp = "";
				$_min_category = (isset($_user_code[$key]['min_category']) == true) ? $_user_code[$key]['min_category'] : 1;
				$_max_category = $_user_code[$key]['max_category'];
				$_use_cate_info = $_user_code[$key]['use_cate_info'];
				$_use_cate_info2 = $_user_code[$key]['use_cate_info2'];
				$_use_cate_info2_child = $_user_code[$key]['use_cate_info2_child'];

				$_cate_joint = $_user_code[$key]['cate_joint'];
				$_child_cate_chk = $_user_code[$key]['child_cate_chk'];
				if(!function_exists("userCateData")){
					function userCateData($user_data){
						global $root_url, $_skin, $cno1, $root_url, $_skin_name, $_total_prd_count;
						if(!$_skin['url']) $_skin['url'] = $root_url."/_skin/".$_skin_name;
						$user_data['link'] = $root_url."/shop/big_section.php?cno1=".$user_data['no'];
						$user_data['link2'] = ($_GET['cno1'] > 0) ? $root_url."/shop/big_section.php?cno1=$_GET[cno1]&cno2=".$user_data['no'] : $user_data['link'];
						$user_data['img_src'] = $_skin['url']."/img/shop/".$user_data['no'].".gif";
						$user_data['imgr_src'] = (@file_exists($_skin['folder']."/img/shop/".$user_data['no']."r.gif")) ? $_skin['url']."/img/shop/".$user_data['no']."r.gif" : $user_data['img_src'];
						if($cno1 == $user_data['no']) $user_data['img_src'] = $user_data['imgr_src'];
						$user_data['imgr'] = "<img src=\"".$user_data['img_src']."\" border=\"0\" alt=\"".$user_data['name']."\">";
						$user_data['name_link'] = "<a href=\"".$user_data['link']."\">".$user_data['name']."</a>";
						$user_data['name_link2'] = "<a href=\"".$user_data['link2']."\">".$user_data['name']."</a>";
						$user_data['imgr_link'] = "<a href=\"".$user_data['link']."\">".$user_data['imgr']."</a>";
						$user_data['level'] = !$user_data['level'] ? 3 : $user_data['level'];
						$user_data['ctype'] = !$user_data['ctype'] ? 1 : $user_data['ctype'];
						if($_total_prd_count){
							$_prd_sql = prdWhereByCate($user_data);
							$user_data['total_prd'] = totalCatItem($_prd_sql);
							$user_data['total_prd'] = $user_data['total_prd'] ? $user_data['total_prd'] : 0;
						}
						return $user_data;
					}
				}
				$_user_cate_sql = "";
				$_level = 1;
				// 최대 출력 값 설정 시 해당 카테고리로 고정 출력
				if($_use_cate_info == "Y" && $_cno1['no']){
					$_level = $_cno1['level']+1;
                    if($_level > 1 && $_level <= $cfg['max_cate_depth']) {
						$_pname = $_cate_colname[1][$_cno1['level']];
						$_user_cate_sql = " and $_pname='$_cno1[no]'";
					}
					if($_level > $_max_category && $_level < $_max_category+1){
						$_level = $_max_category;
						if($_max_category > 1) {
							$_pname = $_cate_colname[1][($_max_category-1)];
							$_pval = $_cno1[$_pname];
							$_user_cate_sql = " and $_pname='$_pval'";
						}
					}
				} else if ($_use_cate_info2  == 'Y' && $_cno1['no']) {
                    if ($_use_cate_info2_child == 'Y') { // 하위 카테고리가 없을 경우 사용하지 않음
                        $_pname = $_cate_colname[1][$_cno1['level']];
                        if ($pdo->row("select count(*) from {$tbl['category']} where $_pname='{$_cno1['no']}'") == 0) {
                            continue;
                        }
                    }
                    $_level = $_cno1['level'];
                    $_user_cate_sql .= " and level='{$_cno1['level']}'";
                    if ($_cno1['level'] > 1) {
                        $_cpname = $_cate_colname[1][($_cno1['level']-1)];
                        $_cpno = $_cno1[$_cpname];
                        $_user_cate_sql .= " and $_cpname='$_cpno'";
                    }
                }

                if ($_use_cate_info == 'Y' || $_use_cate_info2 == 'Y') {
                    // 최대 출력 카테고리
                    if ($_max_category > 0) {
                        $_user_cate_sql .= " and level <='$_max_category'";
                    }

                    // 최소 출력 카테고리
                    if ($_min_category > 0) {
                        $_user_cate_sql .= " and level >='$_min_category'";
                    }
                }

				// 하위 카테고리가 없을 경우 현재 카테고리 출력
				if($_child_cate_chk == 'Y' && $_level > 2 && $pdo->row("select count(*) from $tbl[category] where ctype='$_ctype' and level='$_level' $_user_cate_sql") == 0) {
					$_level--;
					$_pname = $_cate_colname[1][($_level-1)];
					$_tmpcno = $_cno1[99+$_level];
					$_user_cate_sql = " and $_pname='$_tmpcno'";
				}

				list($b, $m, $s) = explode(",",$_user_code[$key]['cate']);
				if($b){
					$_level = 2;
					$_user_cate_sql .= " and `big`='$b'";
				}
				if($m){
					$_level = 3;
					$_user_cate_sql .= " and `mid`='$m'";
				}
				if($s && $_max_category > 3){
					$_level = 4;
					$_user_cate_sql .= " and `small`='$s'";
				}
				$_total_prd_count = 0;
				if(@strpos(".".$_line[2], "{{\$분류상품수}}")){
					$_total_prd_count = 1;
				}

				if($_user_code[$key]['cate_type2'] == 'mobile') $_user_cate_sql .= " and `mobile_hidden`='N' ";
				else $_user_cate_sql .= " and  `hidden`='N' ";

				// 윙모바일 카테고리 별도숨김
				$_user_cate_sql .= ($_SESSION['browser_type'] == 'mobile') ? " and `mobile_hidden`='N' " : " and  `hidden`='N' ";
                $_tmp = __cateModuleLoop($_ctype, $_level, $_user_cate_sql); // Recursive

				if($_auto_scroll == "Y") $_tmp = "<script type=\"text/javascript\">\n"."var user_code_".$key."=new Array();\n".$_tmp."\n</script>\n";
				else $_tmp = listContentSetting($_tmp, $_line);
			}elseif($_code_type == 'b'){
				$_board_w = '';
				$_replace_datavals[$_file_name][$_code_name] = $_user_code_typec['b'];
				$_board_name = $_user_code[$key]['board_name'];
				$_board_type = $_user_code[$key]['board_type'];
				$_board_cate = addslashes($_user_code[$key]['board_cate']); //분류추가
				$_limit = $_user_code[$key]['board_list_total'] ? $_user_code[$key]['board_list_total'] : 10;
				$_titlecut = $_user_code[$key]['board_list_titlecut'] ? $_user_code[$key]['board_list_titlecut'] : 0;
				$_contentcut = $_user_code[$key]['board_list_contentcut'] ? $_user_code[$key]['board_list_contentcut'] : 0;
				$_orderby = $_orderby ? $_orderby : "`no` desc";
				$_board_is_notice = $_user_code[$key]['board_is_notice'];
				if($_board_is_notice == 'Y') {
					$_board_w .= " and `notice`='Y'";
				}elseif($_board_is_notice == 'N') {
					$_board_w .= " and `notice`='N'";
				}elseif($_board_name == "prd:review" && $_board_is_notice == 'B') {
					$_board_w .= " and `stat`='3'";
				} elseif ($_board_name == "prd:review" && $_board_is_notice == 'I') {
					$_board_w .= " and (upfile1!='' or upfile2!='' or content like '%<img %')";
				} elseif ($_board_name == "prd:review" && $_board_is_notice == 'I2') {
					if($_file_name != 'shop_detail.php') continue;
					$_board_w .= " and pno='{$prd['parent']}' and (upfile1!='' or upfile2!='' or content like '%<img %')";
				}
				if($_board_type == 'img' && $_board_is_notice!= 'I' && $_board_is_notice != 'I2') $_board_w .= " and (`upfile1` != '' or `upfile2` != '')";
				$_upfileimgw = (!$_user_code[$key]['board_list_imgw']) ? 50 : $_user_code[$key]['board_list_imgw'];
				$_upfileimgh = (!$_user_code[$key]['board_list_imgh']) ? 50 : $_user_code[$key]['board_list_imgh'];
				$_upfile_ori_dir = "";
				$_new_check = "";
				if($_board_name == "prd:review"){
					$_boardq = "select `no`, `pno`, `rev_pt`, `title`, `name`, `member_id`, `member_no`, `cate`, `reg_date`, `updir`, `upfile1`, `upfile2`, `content`,`hit` from `$tbl[review]` where `stat` not in (1) $_board_w order by notice asc, $_orderby limit $_limit";
					$_new_check = $cfg['product_review_new_time'] ? $cfg['product_review_new_time'] : 2;
				}elseif($_board_name == "prd:qna"){
					$_boardq = "select `no`, `pno`, `title`, `name`, `member_id`, `member_no`, `cate`, `reg_date`, `content`, `hit` from `$tbl[qna]` where 1 $_board_w order by notice asc, $_orderby limit $_limit";
					$_new_check = $cfg['product_qna_new_time'] ? $cfg['product_qna_new_time'] : 2;
				}else{
					if($_board_cate) {
						$_board_w .= " and `cate`='$_board_cate'";
					}
					if(fieldExist('mari_board', 'hidden')) {
						$_board_w .= " and hidden='N'";
					}
					$config = $pdo->assoc("select * from mari_config where db='$_board_name'");
					$_upfile_ori_dir = "board/";
					$_boardq = "select * from `mari_board` where `db`='$_board_name'  $_board_w order by notice='Y' desc, $_orderby limit $_limit";
					$_new_check = 2;
				}
				$_new_check = $now-$_new_check*60*60;
				$_sql = $pdo->iterator($_boardq);
				$_tmp = "";

				if(is_array($_board_cate) == false) {
					$_board_cate = array();
					$bdctres = $pdo->iterator("select no, name from mari_cate");
                    foreach ($bdctres as $bdctdata) {
						$_board_cate[$bdctdata['no']]['name'] = stripslashes($bdctdata['name']);
					}
				}

				include_once $engine_dir.'/_engine/include/shop_detail.lib.php';
                foreach ($_sql as $user_data) {
					$user_data['title'] = stripslashes($user_data['title']);
					if($_titlecut > 0) $user_data['title'] = cutStr($user_data['title'], $_titlecut);
					if($_board_name != 'prd:review' && $_board_name != 'prd:qna') {
						$user_data['cate'] = $_board_cate[$user_data['cate']]['name'];
					}
					switch($_board_name) { // 이름 마스킹 처리
						case 'prd:review' :
							$user_data = reviewOneData($user_data);
						break;
						case 'prd:qna' :
							$user_data['name'] = reviewName($user_data, 'qna');
						break;
						default :
                            include_once __ENGINE_DIR__.'/board/include/lib.php';

							$cfg['board_protect_name'] = $config['protect_name'];
							$cfg['board_protect_name_strlen'] = $config['protect_name_strlen'];
							$cfg['board_protect_name_suffix'] = $config['protect_name_suffix'];
							$user_data['name'] = getWriterName($user_data);
						break;
					}
					$user_data['content'] = $user_data['content2'] = stripslashes($user_data['content']);
					$user_data['content'] = strip_tags($user_data['content']);
					if($_contentcut > 0) $user_data['content'] = cutStr($user_data['content'], $_contentcut);
					if($user_data['pno']) {
						$user_data_prd = $pdo->assoc("select hash, name, updir, upfile3, w3, h3 from `".$tbl['product']."` where `no`='".$user_data['pno']."'");
						$user_data['pno'] = $user_data_prd['hash'];
						if($user_data_prd['updir']) {
							list($_w, $h, $_size_str) = setImageSize($user_data_prd['w3'], $user_data_prd['h3'], $_upfileimgw, $_upfileimgh);
							$user_data['prdimg_path'] = getFileDir($user_data_prd['updir']).'/'.$user_data_prd['updir'].'/'.$user_data_prd['upfile3'];
							$user_data['prdimg'] = "<img src=\"$user_data[prdimg_path]\" $_size_str>";
							$user_data['prdimg_link'] = "<a href=\"$root_url/shop/detail.php?pno=$user_data[pno]\">$user_data[prdimg]</a>";
							$user_data['prdname'] = stripslashes($user_data_prd['name']);
							$user_data['prdname_link'] = "<a href=\"$root_url/shop/detail.php?pno=$user_data[pno]\">$user_data[prdname]</a>";
						}
					}
					if($_board_name == "prd:review") $user_data['link'] = $root_url."/shop/product_review.php?rno=".$user_data['no']."&pno=".$user_data['pno']."#revTitle".$user_data['no'];
					elseif($_board_name == "prd:qna") $user_data['link'] = $root_url."/shop/product_qna.php?rno=".$user_data['no']."&pno=".$user_data['pno']."#qnaTitle".$user_data['no'];
					else $user_data['link'] = $root_url."/board/?db=".$user_data['db']."&no=".$user_data['no']."&mari_mode=view@view&module=true";
					$user_data['layer_link'] = ($_board_name == "prd:review") ? $user_data['title_layer_link'] : $user_data['link'];
					if($_board_name == "prd:review") {
						$user_data['ymd'] = $user_data['symd'] = $user_data['reg_date'];
					} else {
						$user_data['ymd'] = $config['date_type_user'] ? parseDateType($config['date_type_user'], $user_data['reg_date']) : date("Y-m-d", $user_data['reg_date']);
						$user_data['symd'] = date("Y-m-d H:i", $user_data['reg_date']);
					}
					$user_data['title_link'] = "<a href=\"".$user_data['link']."\">".$user_data['title']."</a>";
					$user_data['updir'] = $user_data['updir'] ? $user_data['updir'] : $user_data['up_dir'];
					$user_data['updir'] = $_upfile_ori_dir.$user_data['updir'];
					if($user_data['reg_date'] >= $_new_check) {
						$user_data['new_i'] = $_prd_board_icon['new'];
					}

					for($i = 5; $i >= 2; $i--) {
						if(empty($user_data['upfile'.($i-1)]) == true && empty($user_data['upfile'.$i]) == false) {
							$user_data['upfile'.($i-1)] = $user_data['upfile'.$i];
							unset($user_data['upfile'.$i]);
						}
					}

					if($_board_name != 'prd:review') $user_data['img_cnt'] = 0;
					for($i = 1; $i <= 4; $i++) {
						if($user_data['upfile'.$i] && preg_match("/\.(gif|jpg|jpeg|bmp|png)/i", $user_data['upfile'.$i])){
							$user_data['file_dir'] = ($user_data['updir']) ? getFileDir($user_data['updir']).'/'.$user_data['updir'].'/' : '';
							if(preg_match('/^http/', $user_data['upfile'.$i]) == false) {
								if($_board_name != 'prd:review') $user_data['img_cnt']++;
								$user_data['upfile'.$i] = $user_data['file_dir'].$user_data['upfile'.$i];
							}
							$user_data['upfile'.$i] = "<img src=\"".$user_data['upfile'.$i]."\">";
							$user_data['upfile'.$i.'_link'] = "<a href=\"".$user_data['link']."\">".$user_data['upfile'.$i]."</a>";
						}
					}

					// 상품평점수
					$user_data['star'] = '';
					if($_board_name == 'prd:review') {
						include_once $engine_dir.'/_engine/include/shop_detail.lib.php';
						if($user_data['no'] && $user_data['rev_pt']) {
							$user_data['rev_pt_icon'] = reviewStar($_prd_board_icon['star'], array(
								'no' => $user_data['no'],
								'rev_pt' => $user_data['rev_pt']
							));
						}
					}

					// 관련상품링크
					$user_data['prd_link'] = '';
					if($_board_name == 'prd:qna' || $_board_name == 'prd:review') {
						if($user_data['pno']) {
							$user_data['prd_link'] = $root_url.'/shop/detail.php?pno='.$user_data['pno'];
						}
					}

					$_line_value = lineValues($_code_name, $_line, $user_data, "", 2);
					$_tmp .= ($_auto_scroll == "Y") ? "user_code_".$key."[".$_as_unum."]='".scriptOutFormat($_line_value)."';\n" : $_line_value;
					$_as_unum++;
				}

				if($_auto_scroll == "Y") $_tmp = "<script type=\"text/javascript\">\n"."var user_code_".$key."=new Array();\n".$_tmp."\n</script>\n";
				else $_tmp = listContentSetting($_tmp, $_line);

			}elseif($_code_type == "n" || $_code_type == 'bs'){
				$_tmp = getModuleContent($_code_name, "", $_file_name);
				$_user_app_page = $_file_name;
				if($cno1 && $_file_name == "shop_big_section.php") $_user_app_page = $_file_name.":".$cno1;
				elseif($cont) $_user_app_page = "content_content.php:".$cont;
				elseif($db) $_user_app_page = $_file_name.":".$db;
				$_tmp1 = getPageAppContent("common", $_tmp);
				$_tmp = getPageAppContent($_user_app_page, $_tmp);
				$_tmp = $_tmp1.$_tmp;

				if(is_array($_page_sub_title) == false) { // 페이지 타이틀 정보
					include $root_dir.'/_config/title_name.php';
				}

				if($_code_type == 'bs') { // 게시판 검색
					$_search_column = $_user_code[$key]['search_column'];
					switch($_user_code[$key]['board_name']) {
						case 'prd:review' :
							$_action = $root_url.'/shop/product_review_list.php';
							$_frm  = '<input type="hidden" name="search_column" value="'.$_search_column.'">';
							$_title = $_page_sub_title['shop/product_review'];
							break;
						case 'prd:qna' :
							$_action = $root_url.'/shop/product_qna_list.php';
							$_frm  = '<input type="hidden" name="search_column" value="'.$_search_column.'">';
							$_title = $_page_sub_title['shop/product_qna'];
							break;
						default :
							if($_search_column == 3) $_search_column = 'title';
							if($_search_column == 4) $_search_column = 'content';

							$_action = $root_url.'/board/';
							$_frm  = '<input type="hidden" name="db" value="'.$_user_code[$key]['board_name'].'">';
							$_frm .= '<input type="hidden" name="search" value="'.$_search_column.'">';
							$_title = stripslashes($pdo->row("select title from mari_config where db='".$_user_code[$key]['board_name']."'"));
					}

					$_replace_datavals[$_file_name][$_code_name] = $_user_code_typec['bs'];
					$_tmp = lineValues($_code_name, $_tmp, array(
						'bs_form_start' => '<form method="GET" action="'.$_action.'">'.$_frm,
						'bs_form_end' => '</form>',
						'board_name' => $_title
					), '', 2);

					unset($_action, $_frm, $_search_column);
				}
			}elseif($_code_type == "i"){

				$_tmp = "";
				$_ucode_image_link = ucodeImageLink(1, $_user_code[$key]['image_link']);
				$_skin['url'] = $_skin['url'] ? $_skin['url'] : $root_url."/_skin/".$_skin_name;
				$_ucode_image_src = $_skin['url']."/img/banner";
				$img_sum = $_user_code[$key]['img_sum'] ? $_user_code[$key]['img_sum'] : $_user_img_default_number;
				for($ii=0; $ii<$img_sum; $ii++){
					$_ucode_image_file = titleIMGName("ucode_".$key."_".$ii, "banner");
					$_ucode_image_file2 = titleIMGName("ucode_".$key."_".$ii."r", "banner");
					if($_ucode_image_file){
						$_ucode_link = "";
						if($_ucode_image_link[$ii]){
							$_ucode_target = ucodeImageLink("", $_ucode_image_link[$ii], 2);
							$_ucode_link = ucodeImageLink("", $_ucode_image_link[$ii], 4);
						}
						$_ucode_link1 = $_ucode_link ? "<a href=\"".$_ucode_link."\" target=\"".$_ucode_target."\">" : "";
						$_ucode_link2 = $_ucode_link ? "</a>" : "";
						$_ucode_mover = $_ucode_image_file2 ? " onmouseover=\"this.src='".$_ucode_image_src."/".$_ucode_image_file2."';\"  onmouseout=\"this.src='".$_ucode_image_src."/".$_ucode_image_file."';\"" : "";
						$_ucode_image_file = $_ucode_link1."<img src=\"".$_ucode_image_src."/".$_ucode_image_file."\"".$_ucode_mover.">".$_ucode_link2;
						if($_style_filter == 'revealtrans_22') $_ucode_image_file = "<li>$_ucode_image_file</li>";
						$_tmp .= ($_auto_scroll == "Y" && $_style_filter != 'revealtrans_22') ? "user_code_".$key."[".$_as_unum."]='".scriptOutFormat($_ucode_image_file)."';\n" : $_ucode_image_file;
						$_as_unum++;
					}
					if($_auto_scroll != "Y" && $ii == 0) break;
				}
				if($_auto_scroll == "Y") {
					if($_style_filter == 'revealtrans_22') {
						$_scroll_speed = $_user_code[$key]['scroll_speed'] ? $_user_code[$key]['scroll_speed'] : 1;
						$_scroll_speed *= 1000;
						$_tmp = "<script type='text/javascript' src='$engine_url/_engine/common/swipe.js'></script>\n<ul id='sliderWrap_$key'>$_tmp</ul>";
						$_defer_scripts .= "<script type='text/javascript'>
							$('.slides_container').hide();
							$(window).load(function(){
								$('.slides_container').show();
								var swipe_opt = new Array();
								swipe_opt.auto = $_scroll_speed;
								swipe_opt.page_idx = 1;
								sliderObj_$key = new Swipe('sliderWrap_$key', 'sliderPage_$key', swipe_opt);
							});
							</script>";
					} else {
						$_tmp="<script type=\"text/javascript\" defer='defer'>\n"."var user_code_".$key."=new Array();\n".$_tmp."\n</script>\n";
					}
				}
			} elseif ($_code_type == 'is') { // 그룹배너
				if ($_user_code[$key]['use_yn'] != 'Y') continue;
				if ($_user_code[$key]['is_datetype'] != 'Y') {
					if ($now < $_user_code[$key]['start_date']) continue;
					if ($now > $_user_code[$key]['finish_date']) continue;
				}

				$_replace_datavals[$_file_name][$_code_name] = $_user_code_typec['is'];

				$_tmp = '';
				$bngrp = new BannerGroup($_SESSION['browser_type'], $key);
				while ($bndata = $bngrp->parse()) {
					if ($bndata['hidden'] == 'Y') continue;

					$bndata['full'] = '<img src="'.$bndata['front_image_url'].'" class="_banner_group_img" data-rollover="'.$bndata['rollover_image_url'].'">';
					if ($bndata['link']) {
						$bndata['full'] = '<a href="'.$bndata['link'].'" target="'.$bndata['target'].'">'.$bndata['full'].'</a>';
					}

					$_tmp .= lineValues($_code_name, $_line, array(
						'id' => $key.'_'.$bndata['id'],
						'banner_full' => $bndata['full'],
						'front_url' => $bndata['front_image_url'],
						'rollover_url' => $bndata['rollover_image_url'],
						'link' => $bndata['link'],
						'target' => $bndata['target'],
                        'text' => $bndata['text']
					), '', 2);
				}
				$_tmp = listContentSetting($_tmp, $_line);
			}elseif($_code_type == 'd') {
				$_use['user_frame'] = 'Y';
				$_preview = $_user_code[$key];
				if($_preview['htype'] == 1) $qd2_tmp .= "scrolling='no'";
				$_tmp = "<iframe
					id='preview_frame_$key'
					class='preview_frame'
					frameborder='0'
					$qd2_tmp
					src='$root_url/main/exec.php?exec_file=shop/quickDetail.exe.php&type=frame&cno1=$cno1&startup=true&frameno=$key'
					style='width:$_preview[width]px; height:$_preview[height]px;'
					></iframe>";
			} elseif($_code_type == 'instagram') {
				include_once $engine_dir.'/_engine/api/instagram/get.inc.php';

				$instagram_cache = $root_dir.'/_data/cache/instagram.cache.php';
				if(!file_exists($instagram_cache)) continue;

				include_once $instagram_cache;
				$_replace_datavals[$_file_name][$_code_name] = $_user_code_typec['instagram'];

				$_tmp = $_line[0];
				for($i = 0; $i < $_user_code[$key]['instagram_cnt']; $i++) {
					if(!$_instagram_data[$i]) break;
					$_instagram_data[$i]['idx'] = ($i+1);
                    if ($_instagram_data[$i]['media_type'] == 'VIDEO') {
                        $_instagram_data[$i]['media_tag'] =
                            "<video controls>".
                            "<source src=\"{$_instagram_data[$i]['image']}\" type=\"video/mp4\">".
                            "</video>";
                    } else {
                        $_instagram_data[$i]['media_tag'] = "<img src='{$_instagram_data[$i]['image']}'>";
                    }
					$_tmp .= lineValues($_code_name, $_line, $_instagram_data[$i], $_file_name);
				}
				$_tmp = listContentSetting($_tmp, $_line);
			}

			if($_auto_scroll == "Y" && $_style_filter && $_style_filter != 'revealtrans_22'){
				if(!@defined("__auto_scroll_included__")){
					$_defer_scripts .= "<script type=\"text/javascript\" src=\"".$engine_url."/_engine/common/auto_scroll.js\"></script>\n";
					@define("__auto_scroll_included__", 1);
				}
				$_scroll_box_w = $_user_code[$key]['scroll_box_w'] ? $_user_code[$key]['scroll_box_w'] : 100;
				$_scroll_box_h = $_user_code[$key]['scroll_box_h'] ? $_user_code[$key]['scroll_box_h'] : 100;
				$_scroll_speed = $_user_code[$key]['scroll_speed'] ? $_user_code[$key]['scroll_speed'] : 1;
				$_scroll_direction = $_user_code[$key]['scroll_direction'] ? $_user_code[$key]['scroll_direction'] : 1;

				if($_user_code[$key]['style_filter'] == 'rollv2') {
					$_pause_type = $_user_code[$key]['pause_type'];
					$_pause_time = $_user_code[$key]['pause_time'];
					$_scauto_start = $_user_code[$key]['scauto_start'];
				} else {
					if($_scroll_direction == 1 || $_scroll_direction == 2){
						$_scroll_direction_txt="top";
						if($_code_type == "b"){
							$_scroll_box_h = $_user_code[$key]['board_list_imgh'] ? $_user_code[$key]['board_list_imgh'] : $_scroll_box_h;
						}else{
							$_scroll_box_h = $_user_code[$key]['product_list_imgh'] ? $_user_code[$key]['product_list_imgh'] : $_scroll_box_h;
						}
					}else{
						$_scroll_direction_txt="left";
						if($_code_type == "b"){
							$_scroll_box_w = $_user_code[$key]['board_list_imgw'] ? $_user_code[$key]['board_list_imgw'] : $_scroll_box_w;
						}else{
							$_scroll_box_w = $_user_code[$key]['product_list_imgw'] ? $_user_code[$key]['product_list_imgw'] : $_scroll_box_w;
						}
					}
				}
				$_line = $_user_code[$key]['product_list_cols'] ? $_user_code[$key]['product_list_cols'] : 1;
				$_line = $_user_code[$key]['board_line'] ? $_user_code[$key]['board_line'] : $_line;
				$_delay = $_user_code[$key]['scroll_time'] ? $_user_code[$key]['scroll_time'] : 1;
				if($_style_filter == "scroll" || $_style_filter == "escroll"){
					$_delay *= 50;
					$_no_gap = ($_style_filter == "scroll") ? "Y" : "";
					$_direct_turn = ($_scroll_direction == 2 || $_scroll_direction == 4) ? "uscroll_".$key.".direct(-1);" : "";
					$_direct_turn1 = ($_scroll_direction == 2 || $_scroll_direction == 4) ? "-1" : "1";
					$_direct_turn2 = ($_scroll_direction == 2 || $_scroll_direction == 4) ? "1" : "-1";
					// 스크롤 방향 변경 링크 제공
					$_replace_code['common_module']['direct_turn_url1_'.$key] = "javascript:uscroll_".$key.".direct(".$_direct_turn1.");"; // 기본방향
					$_replace_hangul['common_module']['direct_turn_url1_'.$key] = "스크롤기본방향".$key;
					$_replace_code['common_module']['direct_turn_url2_'.$key] = "javascript:uscroll_".$key.".direct(".$_direct_turn2.");"; // 역방향
					$_replace_hangul['common_module']['direct_turn_url2_'.$key] = "스크롤역방향".$key;
					$_tmp .= "
<script type=\"text/javascript\" src=\"".$engine_url."/_engine/common/auto_scroll.js\"></script>\n
<script type='text/javascript' defer='defer'>
<!--
var uscroll_".$key."=new userEScroll;
uscroll_".$key.".id='user_scroll_code".$key."';
uscroll_".$key.".mode='".$_scroll_direction_txt."';
uscroll_".$key.".line=".$_line.";
uscroll_".$key.".width=".$_scroll_box_w.";
uscroll_".$key.".height=".$_scroll_box_h.";
uscroll_".$key.".delay=".$_delay.";
uscroll_".$key.".speed=".$_scroll_speed.";
uscroll_".$key.".align='center';
uscroll_".$key.".valign='middle';
uscroll_".$key.".no_gap='".$_no_gap."';
uscroll_".$key.".contents=new Array();
".$_direct_turn."

if(typeof user_code_".$key." != 'undefined'){
for(ii=0; ii<user_code_".$key.".length; ii++){
	uscroll_".$key.".add(user_code_".$key."[ii]);
}
}

uscroll_".$key.".exec();
//-->
</script>
";
				}elseif($_style_filter == 'rollv2') {
					$_tmp .= "<ul id='wingscroll_$key'></ul>";
					$_tmp .= "<script type='text/javascript' defer='defer' src='$engine_url/_engine/common/jquery-wingscroll.js'></script>\n";
					$_tmp .= preg_replace("/\t|\n|\r/s", '', "
					<script type='text/javascript' defer='defer'>
						var wingscroll$key = null;
						$(window).load(function() {
							wingscroll$key = \$(user_code_$key).wingscroll({
								'oid':'wingscroll_$key',
								'width':'$_scroll_box_w',
								'height':'$_scroll_box_h',
								'direction': '$_scroll_direction',
								'speed': '$_scroll_speed',
								'pause_type': '$_pause_type',
								'pause_time': '$_pause_time',
								'auto_start': '$_scauto_start'
							});
							$('#wingscroll_$key').find('a').each(function() {
								if(/#[0-9]+_frame$/.test(this.href) == true) {
									var temp = this.href.split('#')[1].split('_')[0];
									this.href = '#';
									$(this).click(function() {
										quickDetailFrame(this, temp, '$_cno1[no]');
										return false;
									});
								}
							});
						});
					</script>")."\n";

					$_replace_code['common_module']['direct_turn_url1_'.$key] = "javascript:wingscroll$key.chdir(2);"; // 기본방향
					$_replace_hangul['common_module']['direct_turn_url1_'.$key] = "스크롤기본방향".$key;
					$_replace_code['common_module']['direct_turn_url2_'.$key] = "javascript:wingscroll$key.chdir(1);"; // 역방향
					$_replace_hangul['common_module']['direct_turn_url2_'.$key] = "스크롤역방향".$key;
					$_replace_code['common_module']['direct_stop_url_'.$key] = "javascript:wingscroll$key.setAuto(0);";
					$_replace_hangul['common_module']['direct_stop_url_'.$key] = "자동스크롤멈춤".$key;
					$_replace_code['common_module']['direct_start_url_'.$key] = "javascript:wingscroll$key.setAuto(1);";
					$_replace_hangul['common_module']['direct_start_url_'.$key] = "자동스크롤시작".$key;
					$_replace_code['common_module']['direct_toggle_url_'.$key] = "javascript:wingscroll$key.setAuto();";
					$_replace_hangul['common_module']['direct_toggle_url_'.$key] = "자동스크롤토글".$key;

					$_line2 = getModuleContent($_code_name, "", $_file_name);
					$_tmp2 = '';
					for($i = 0; $i < $_as_unum; $i++) {
						$_tmp2 .= lineValues($_code_name, $_line2, null);
					}
					$_tmp2 = listContentSetting($_tmp2, $_line2);
					if($_pause_type) {
						$_replace_code['common_module']['user_code'.$key.'_2'] = '<div id="wingscroll_'.$key.'_2">'.$_tmp2.'</div>';
						$_replace_hangul['common_module']['user_code'.$key.'_2'] = '사용자리스트'.$key.'_2';
					}
					unset($_tmp2, $_line2);
				}else{
					$_delay *= 1000;
					$_filter = explode("_", $_style_filter);
					$_duration = $_user_code[$key]['duration'] ? $_user_code[$key]['duration'] : 1;
					$_transition = $_filter[1] ? $_filter[1] : "";
					$_newtype = ($_transition == 13 || $_transition == 9 || $_transition == 10 || $_transition == 5 || $_transition == 3 || $_transition == 21 || $_transition == 12) ? "" : "Cross";
					$_tmp .= preg_replace("/\t|\n|\r/s", '', "
<script type='text/javascript' defer='defer'>
var uscroll_".$key."=new userStyleFilter".$_newtype.";
uscroll_".$key.".id='uscroll_".$key."';
uscroll_".$key.".obj_id='user_scroll_code".$key."';
uscroll_".$key.".width=".$_scroll_box_w.";
uscroll_".$key.".height=".$_scroll_box_h.";
uscroll_".$key.".interval=".$_delay.";
uscroll_".$key.".faden_images=new Array();
uscroll_".$key.".filterStyle='".$_filter[0]."()';
uscroll_".$key.".duration='".$_duration."';
uscroll_".$key.".transition='".$_transition."';

if(typeof user_code_".$key." != 'undefined'){
for(ii=0; ii<user_code_".$key.".length; ii++){
	uscroll_".$key.".faden_images[ii]=user_code_".$key."[ii];
}
}

uscroll_".$key.".rotateStart();
</script>
")."\n";
				}
			}
			$_replace_code[$_file_name][$_code_name] = $_tmp;
		}
	}
	unset($current_user_code);

    // 스킨배너
    getSkinBanner(basename($_skin['folder']));
    foreach ($skinbanner_cfg as $key => $val) {
        $_replace_hangul['common_module']['wing_skin_banner'.$key] = '스킨배너'.$key;
        $_replace_code['common_module']['wing_skin_banner'.$key] = getSkinBanner(basename($_skin['folder']), $key, 'html');
    }

	// 입금 계좌 정보
	if(CheckCodeUsed($_replace_hangul['common_module']['bank_account_list'])) {
		$_tmp = '';
		$_line = getModuleContent('bank_account_list');
		$res = $pdo->iterator("select * from $tbl[bank_account] where type!='int' order by sort asc");
		foreach($res as $bdata) {
			$_tmp .= lineValues("bank_account_list", $_line, $bdata, 'common_module');
		}
		$_tmp = listContentSetting($_tmp, $_line);
		$_replace_code['common_module']['bank_account_list'] = $_tmp;
		unset($_tmp, $_line, $res);
	}

	$_replace_code['common']['company_privacy_items'] = $cfg['company_privacy_items'];
	$_replace_code['common']['company_privacy_get'] = $cfg['company_privacy_get'];

	// 자동 슬라이드 설정
	if(!$striplayout && !$_REQUEST['from_ajax']) {
		$_tmp = getModuleContent("auto_slide");
		if($_skin['auto_slide'] == "Y" && !$_GET['striplayout']){
			if(!@defined("__auto_scroll_included__")){
				$_defer_scripts .= "<script type=\"text/javascript\" src=\"".$engine_url."/_engine/common/auto_scroll.js\" defer='defer'></script>\n";
				@define("__auto_scroll_included__", 1);
			}
			$_slide_top = $_skin['auto_slide_top'] ? $_skin['auto_slide_top'] : 0;
			$_slide_position = $_skin['auto_slide_right'] ? "right:".$_skin['auto_slide_right']."px;" : "right:0px";
			$_slide_position = $_skin['auto_slide_left'] ? "left:".$_skin['auto_slide_left']."px;" : $_slide_position;
			$_slide_speed = $_skin['auto_slide_speed'] ? $_skin['auto_slide_speed'] : 20;
			$_slide_limittop = $_skin['auto_slide_limittop'] ? $_skin['auto_slide_limittop'] : 0;
			$_slide_limitbottom = $_skin['auto_slide_limitbottom'] ? $_skin['auto_slide_limitbottom'] : 0;
			$_tmp="<div id=\"wing_auto_slide\" style=\"position:absolute; top:".$_slide_limittop."px;".$_slide_position."\">".$_tmp."</div>";
			echo "<script type=\"text/javascript\" defer='defer'>$(document).ready(function(){setInterval(\"wingQuickSlide(".$_slide_limittop.", ".$_slide_top.", ".$_slide_limitbottom.", ".$_slide_speed.")\", 10);});</script>";
		}
		$_replace_code['common_module']['auto_slide'] = $_tmp;
	}

	// iPay
	if($cfg['ipay_logo']){
		$_replace_code['common_module']['ipay_logo']="<img src='".$engine_url."/_manage/image/service/logo_ipay0".$cfg['ipay_logo'].".gif'>";
	}

	// 페이스북 좋아요
	if($_skin['fb_data']) $_skin['fb_data'] = '200';
	if($_skin['fb_layout'] == 'N') $_fb_layout = "data-layout='button_count'";

	$_replace_code['common_module']['fb_like'] = "<script type='text/javascript'>(function(d, s, id) {var js, fjs = d.getElementsByTagName(s)[0];if (d.getElementById(id)) {return;}js = d.createElement(s); js.id = id;js.src = '//connect.facebook.net/ko_KR/all.js#xfbml=1'; fjs.parentNode.insertBefore(js, fjs); }(document, 'script', 'facebook-jssdk'));</script><div class='fb-like' data-href='$root_url' data-width='$_skin[fb_data]' $_fb_layout data-show-faces='false'></div>";

	// 재입고 알림 사용 설정
	$_notify_restock_use = "";
	if($cfg['notify_restock_use'] == "Y") {
		// 재입고 알림 대상 설정
		switch($cfg['notify_restock_target']) {
			case "1": // 전체
				$_notify_restock_use = "Y";
				break;
			case "2": // 회원
				if($member['no']) $_notify_restock_use = "Y";
				break;
			case "3": // 비회원
				if(!$member['no']) $_notify_restock_use = "Y";
				break;
			default:
				$_notify_restock_use = "";
				break;
		}
	}
	// 위 조건절에서 사용설정이 Y인경우 상품상세 페이지에서 재고 상태에 따른 재입고 알림 사용 유무 오버라이드
	if($_file_name == "shop_detail.php" && $_notify_restock_use == "Y") {
		$_notify_restock_use = "";
		// 상품의 재고관리가 사용함 일때만
		if($prd['ea_type'] == 1) {
			// 품절방식 설정값에 따른 품절옵션만 나오게
			if(!$cfg['notify_restock_type_l']) $cfg['notify_restock_type_l'] = "Y";
			if(!$cfg['notify_restock_type_f']) $cfg['notify_restock_type_f'] = "Y";
			$_soldout_where = "";
			if($cfg['notify_restock_type_l'] == "Y") {
				$_soldout_where .= " (eco.`is_soldout`='Y' OR (eco.force_soldout='L' AND eco.qty<1)) AND eco.force_soldout!='Y' ";
			}
			if($cfg['notify_restock_type_f'] == "Y") {
				if($_soldout_where != "") $_soldout_where .= " OR ";
				$_soldout_where .= " eco.`force_soldout`='Y' ";
			}

			// 텍스트 옵션이 포함되어있는지 체크, 없는경우만 진행
            if ($_soldout_where) {
                $_sql = "SELECT count(pos.`no`) FROM $tbl[product_option_set] pos WHERE pos.pno='$prd[parent]' AND pos.otype IN ('4A', '4B')";
                $_result = $pdo->row($_sql);
                if ($_result == 0) {
                    $_sql = "SELECT count(complex_no) cnt FROM (
					SELECT
						eco.complex_no
					FROM
						erp_complex_option eco LEFT JOIN wm_product_option_set pos ON eco.pno=pos.pno
					WHERE
						eco.pno='$prd[parent]' and eco.del_yn='N'
						AND ( $_soldout_where )
					GROUP BY eco.complex_no
				) t";
                    $_result = $pdo->row($_sql);
                    if ($_result > 0) $_notify_restock_use = "Y";
                }
            }
		}
	}
	$_replace_code['common_module']['notify_restock_use'] = $_notify_restock_use;
	unset($_sql, $_result, $_soldout_where, $_notify_restock_use);

	if (checkCodeUsed('전체쿠폰리스트') == true) {
		$_tmp = "";
		$_cpn_cnt = 0;
		$_line = getModuleContent("common_cpn_list");
		while($coupon = couponList()){
			$_tmp .= lineValues("common_cpn_list", $_line, $coupon, "common_module");
			$_cpn_cnt++;
		}
		$_tmp = listContentSetting($_tmp, $_line);
		$_replace_code['common_module']['common_cpn_list'] = $_tmp;
		unset($_tmp);
	}

	// 아래부터는 추가페이지 로딩되지 않는 영역입니다.
	if($_GET['cont']) return;

	// SNS 로그인
	$_SESSION["sns_login"]["rURL"] = ($rURL) ? $rURL : getURL();
	if($_SESSION["sns_login_state"]) {
		$state = $_SESSION["sns_login_state"];
	} else {
		$state = md5(microtime().mt_rand());
		$_SESSION["sns_login_state"] = $state;
	}
	$sns_login_use_cnt = 0;
	if($cfg['naver_login_use']=='Y' && $cfg['naver_login_client_id']) {
		$naverlogin = "
		<script type='text/javascript'>
		function naverLogin() {
			var url = \"https://nid.naver.com/oauth2.0/authorize?client_id=".$cfg['naver_login_client_id']."&response_type=code&redirect_uri=$root_url/main/exec.php?exec_file=promotion/naver_callback.exe.php&state=$state\";
			if(mobile_browser == 'mobile') {
				window.location.href=url;
			} else {
				window.open(url, \"naver_login_pop\", \"width=500,height=500,scrollbars=no\");
			}
		}
		</script>";

		$_replace_code['common_module']['naver_login_script'] = $naverlogin;
		$_replace_code['common_module']['naver_login'] = 'true';
		$sns_login_use_cnt++;
		unset($naverlogin, $tmp_redir);
	}

	if($cfg['facebook_login_use']=='Y' && $cfg['facebook_id']) {
		$fb_redir = urlencode($root_url.'/main/exec.php?exec_file=promotion/naver_callback.exe.php&sns_type=fb_token');
		$facebooklogin = "
		<script type=\"text/javascript\">
			(function(d, s, id) {
			  var js, fjs = d.getElementsByTagName(s)[0];
			  if (d.getElementById(id)) return;
			  js = d.createElement(s); js.id = id;
			  js.src = \"//connect.facebook.net/ko_KR/sdk.js#xfbml=1&version=v2.5&appId=$cfg[facebook_id]\";
			  fjs.parentNode.insertBefore(js, fjs);
			}(document, 'script', 'facebook-jssdk'));

			function facebookLogin() {
				FB.getLoginStatus(function(response) {
					if (response.status === 'connected') {
						facebookGetMe();
					}else {
						if(browser_type == 'mobile' || navigator.userAgent.indexOf('Trident') > -1) {
							location.href='https://www.facebook.com/dialog/oauth?client_id=$cfg[facebook_id]&redirect_uri=$fb_redir&response_type=token';
						} else {
							FB.login(function(response) {
								if (response.authResponse) {
									facebookGetMe();
								}
							}, {scope: 'public_profile,email'} );
						}
					}
				});
			}
			function facebookGetMe() {
				FB.api('/me?fields=name,email', function(res) {
					var fbID = res.id;
					var fbNM = \"\";
					var fbEM = \"\";
					if(res.name  != undefined)fbNM = res.name;
					if(res.email != undefined)fbEM = res.email;
					$.post(\"/main/exec.php?exec_file=promotion/naver_callback.exe.php\",
						{sns_type:\"FB\",
						 state:\"$state\",
						 cid:fbID,
						 name:fbNM,
						 email:fbEM
					}).done(function (rtn) {
						var arrayValue = rtn.split(\"**@\");
						if(arrayValue[1] != \"N\"){
							window.location.href=\"/member/apijoin.php?rURL=$rURL\";
						} else {
							alert(\"허용 접속 시간을 초과 하였습니다.\");
						}
						return false;
					});
				});
			}
		</script>";

		$_replace_code['common_module']['facebook_login_script'] = $facebooklogin;
		$_replace_code['common_module']['facebook_login'] = 'true';
		$sns_login_use_cnt++;
		unset($facebooklogin);
	}

    $scfg->def('kakao_login_y_type', '');
	if(($cfg['kakao_login_use']=='Y' && $cfg['kakao_login_y_type'] == '') && $cfg['kakao_sns_id'] && !$_GET['from_ajax']) {
		$kakaologin = "
		<script type=\"text/javascript\" src=\"//developers.kakao.com/sdk/js/kakao.min.js\"></script>
		<script type=\"text/javascript\">
		Kakao.init('" .  $cfg['kakao_sns_id'] . "');
		function loginWithKakao(){
			Kakao.Auth.login({
				throughTalk: false,
				success: function(authObj) {
					Kakao.API.request({
						url: '/v2/user/me',
						success: function(res) {
							var kaID = res.id;
							var kaNM = \"\";
							if(res.properties != undefined)kaNM = res.properties.nickname;
							var kaEMAIL = (res.kakao_account.email) ? res.kakao_account.email : \"\";

							$.post(\"/main/exec.php?exec_file=promotion/naver_callback.exe.php\",
								{sns_type:\"KA\",
								 state:\"$state\",
								 cid:kaID,
								 name:kaNM,
								 email:kaEMAIL
							}).done(function (rtn) {
									var arrayValue = rtn.split(\"**@\");
									if(arrayValue[1] != \"N\"){
										window.location.href=\"/member/apijoin.php?rURL=$rURL\";
									} else {
										alert(\"허용 접속 시간을 초과 하였습니다.\");
									}
									return false;
							});
						},
						fail: function(error) {
							alert(JSON.stringify(error));
						}
					});

				},fail: function(err) {
					alert(JSON.stringify(err));
				}
			});
		}
        function kakaoLogin() {
            loginWithKakao();
        }
		</script>";

		$_replace_code['common_module']['kakao_login_script'] = $kakaologin;
		$_replace_code['common_module']['kakao_login'] = 'true';
		$sns_login_use_cnt++;
		unset($kakaologin);
	}

    if(($cfg['kakao_login_use']=='S' || ($cfg['kakao_login_use']=='Y' && $cfg['kakao_login_y_type'] == 'self'))&& $cfg['kakao_sns_id']) {
        $redirectUri  = $p_root_url.'/_data/compare/kakao/kakao_login_auth.php';
		$kakaologin = "
		<script type=\"text/javascript\" src=\"https://developers.kakao.com/sdk/js/kakao.min.js\"></script>
		<script type=\"text/javascript\">
		Kakao.init('" .  $cfg['kakao_sns_id'] . "');
        function loginWithKakao() {
            Kakao.Auth.authorize({
                redirectUri: '$redirectUri',
                state: '$state'
            })
        }
        function kakaoLogin() {
            loginWithKakao();
        }
        </script>
        ";
		$_replace_code['common_module']['kakao_login_script'] = $kakaologin;
		$_replace_code['common_module']['kakao_login'] = 'true';
		$sns_login_use_cnt++;
		unset($kakaologin);
    }

	if($cfg['payco_login_use'] == 'Y' && $cfg['payco_login_client_id']) {
		$paycologin = "
		<script type='text/javascript'>
		function paycoLogin() {
			var url = \"https://id.payco.com/oauth2.0//authorize?response_type=code&client_id=".$cfg['payco_login_client_id']."&redirect_uri=$root_url/main/exec.php?exec_file=promotion/payco_callback.exe.php&state=$state&serviceProviderCode=FRIENDS&userLocale=ko_KR\";
			if(browser_type == 'mobile') {
				window.location.href=url;
			} else {
				window.open(url, \"payco_login_pop\", \"width=500,height=500,scrollbars=no\");
			}
		}
		</script>";

		$_replace_code['common_module']['payco_login_script'] = $paycologin;
		$_replace_code['common_module']['payco_login'] = 'true';
		$sns_login_use_cnt++;
		unset($paycologin);
	}

	if($cfg['wonder_login_use'] == 'Y' && $cfg['wonder_login_client_id'] && $cfg['wonder_login_client_secret']) {
		$wn_redir = urlencode($root_url.'/main/exec.php?exec_file=promotion/wonder_callback.exe.php&sns_type=WN');
		$wonderlogin = "
		<script type='text/javascript'>
		function wonderLogin() {
			var url ='https://login.wonders.app/wauth/authorize?response_type=code&client_id=$cfg[wonder_login_client_id]&state=$state&redirect_uri=$wn_redir&scope=public_profile';
			if(browser_type == 'mobile') {
				window.location.href=url;
			} else {
				window.open(url, \"wonder_login_pop\", \"width=500,height=500,scrollbars=no\");
			}
		}
		</script>";

		$_replace_code['common_module']['wonder_login_script'] = $wonderlogin;
		$_replace_code['common_module']['wonder_login'] = 'true';
		$_replace_code['common_module']['wemarkeprice_login_script'] = $wonderlogin;
		$_replace_code['common_module']['wemarkeprice_login'] = 'true';
		$sns_login_use_cnt++;
		unset($wonderlogin);
	}

	if(isset($cfg['apple_login_client_id']) == true && $cfg['apple_login_use'] == 'Y') {
		$_replace_code['common_module']['apple_login'] = 'true';
		$sns_login_use_cnt++;
	}

	$_replace_code['common_module']['sns_login_use_cnt'] = ($sns_login_use_cnt > 0) ? $sns_login_use_cnt : '';
	if($sns_login_use_cnt > 0) {
		$_replace_code['common_module']['sns_login_button_use'] = "true";
	}

	$partner_dlv_free_limit ='';
	if($prd['partner_no']) $partner_dlv_free_limit = $pdo->row("select a.`delivery_free_limit` from `$tbl[partner_delivery]` a inner join `$tbl[product]` b on a.partner_no = b.partner_no where b.no='$prd[no]'");
	if($partner_dlv_free_limit && $cfg['use_partner_delivery'] == "Y") {
		$delivery_free_limit = $partner_dlv_free_limit;
	} else {
		$delivery_free_limit = $cfg['delivery_free_limit'];
	}

	$_replace_code['common']['delivery_free_limit'] = $cfg['delivery_fee_type'] == 'O' || $delivery_fee_type == 'O' ? '' : number_format($delivery_free_limit);

	$password_special = ($cfg['password_special'] == 'Y') ? "특수문자" : '';
	$password_engnum = ($cfg['password_engnum'] == 'Y') ? "알파벳, 숫자" : '';
	if($password_engnum && $cfg['password_special'] == 'Y') {
		$password_special = ", 특수문자";
	} else if (!$password_engnum && $cfg['password_special'] == 'Y') {
	    $password_special = "특수문자";
	} else {
	    $password_special = "";
	}

	$_replace_code['common']['join_pwd_basic']=$password_engnum.$password_special;

	$_tmp = '';
	if ($cfg['ipin_use'] == 'Y') $_tmp .= '<a href="JavaScript:join19limit(\'ipin\');" class="ipinuse" name="cprvd" value="ipin"><span>아이핀인증</span></a>';
	if ($cfg['ipin_checkplus_use'] == 'Y') $_tmp .= '<a href="JavaScript:join19limit(\'ipinCheckPlus\');" class="ipincheckplususe" name="cprvd" value="ipinCheckPlus"><span>휴대폰인증</span></a>';
	$_replace_code['common_module']['select_cp'] = $_tmp;
	$_replace_code['common_module']['select_cp_ok'] = (isset($_SESSION['ipin_res']) == true) ? '' : 'Y';
	$_replace_code['common_module']['limit_19'] = (isset($_SESSION['ipin_res']) == false && $scfg->comp('limit_19', 'Y') == true) ? 'Y' : '';
	unset($_tmp);


?>