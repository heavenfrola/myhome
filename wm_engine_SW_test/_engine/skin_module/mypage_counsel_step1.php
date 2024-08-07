<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  주문 1:1 게시판 작성
	' +----------------------------------------------------------------------------------------------+*/

	$_replace_code[$_file_name]['form_start']="<form method=\"post\" action=\"".$root_url."/main/exec.php\" target=\"hidden".$now."\" enctype='multipart/form-data' onSubmit=\"return checkCounselFrm(this)\" style=\"margin:0px;text-align:center;\" >
<input type=\"hidden\" name=\"exec_file\" value=\"mypage/counsel.exe.php\">
<input type=\"hidden\" name=\"ono\" value=\"".$ord['ono']."\">
<input type=\"hidden\" name=\"cate1\" value=\"".$cate1."\">
<input type=\"hidden\" name=\"cate2\" value=\"".$cate2."\">
<input type=\"hidden\" name=\"sbscr\" value=\"".$sbscr."\">
<input type=\"hidden\" name=\"editor_code\" value=\"".$editor_code."\">
";
	if(!$member['no']){
		$_line=getModuleContent("mypage_1to1_logout");
		$_replace_code[$_file_name][mypage_1to1_logout]=lineValues("mypage_1to1_logout", $_line, $ord);
	}
	$_line=getModuleContent("mypage_1to1_cate");
	$_cate[cate_str]=$cate_str ? $cate_str : __lang_mypage_info_ctype3__;
	$_replace_code[$_file_name][mypage_1to1_cate]=lineValues("mypage_1to1_cate", $_line, $_cate);
	// 2009-10-09 : 주문 관련 문의는 주문 관련 정보 숨김 - Han
	if($ono){
		$_line=getModuleContent("mypage_1to1_ordinfo");
		$_replace_code[$_file_name][mypage_1to1_ordinfo]=lineValues("mypage_1to1_ordinfo", $_line, $ord);
	}
	$_replace_code[$_file_name][form_end]="</form>";
	if($cfg['usecap_to']=="Y" && $cfg['captcha_key']) {
		$_replace_code[$_file_name][form_end] .=
			"<script type='text/javascript'>
				if($('#grecaptcha_element').size()==1) {
					var onloadCallback = function() {
						grecaptcha.render('grecaptcha_element', {
							'sitekey' : '$cfg[captcha_key]'
						});
					};
				}
			</script>
			<script src='https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit' async defer></script>";
	}

	// 취소사유 선택
	if($ono && $cate2 >= 12 && $cate2 <= 17) {
		if(fieldExist($tbl['claim_reasons'], 'admin_only') == true) {
			$_line = getModuleContent("mypage_claimreason_list");
			$_tmp = '';
			$rres = $pdo->iterator("select reason from {$tbl['claim_reasons']} where admin_only='N' order by no asc");
            foreach ($rres as $rdata) {
				$rdata['reason'] = stripslashes($rdata['reason']);
				$_tmp .= lineValues('mypage_claimreason_list', $_line, $rdata);
			}
			$_replace_code[$_file_name]['mypage_claimreason_list'] = ($_tmp) ? listContentSetting($_tmp, $_line) : '';
			unset($_tmp);
		}
	}

	if($cate2 == 12 || $cate2 == 14 || $cate2 == 16) {
		if($cate2 == 12 || $cate2 == 14) {
			$repay_stat = '1, 2';
			if($cfg['deny_placeorder_cancel'] != 'Y' && $cfg['deny_placeorder_cancel'] != 'C') $repay_stat .= ', 3';
		} else if($cate2 == 16) {
			$repay_stat = '4';
			if(empty($cfg['deny_decided_cancel']) == true || $cfg['deny_decided_cancel'] == 'N') $repay_stat .= ', 5';
		}

		if($repay_stat && ($ord['stat'] != 1 || $cfg['order_cancel_type_1'] != 'Y')) {
            $has_set = ($scfg->comp('use_set_product', 'Y') == true) ? $pdo->row("select has_set from {$tbl['order']} where ono='$ono'") : 'N'; // 세트 포함 여부
			$_return_milage = 0; // 반품 시 발생되는 회수 적립금
			if(!numberOnly($_skin['mypage_counsel_product_w'])) $_skin['mypage_counsel_product_w'] = 50;
			if(!numberOnly($_skin['mypage_counsel_product_h'])) $_skin['mypage_counsel_product_h'] = 50;
			$_tmp = '';
			$_line = getModuleContent('mypage_1to1_prd_list');
			$pres = $pdo->iterator("select o.*, p.name, p.updir, p.upfile3 from $tbl[order_product] o inner join $tbl[product] p on o.pno=p.no where o.ono='$ono' and o.stat in ($repay_stat) order by o.stat asc, o.no");
			if($cate2 == 16 && $pres->rowCount() == 0) {
				msg(__lang_mypage_error_aleadyDecided__, 'back');
			}
            foreach ($pres as $pdata) {
				if($pdata['stat'] ==  5) {
					$_return_milage += $pdata['total_milage'];
				}

				$img = prdImg(3, $pdata, $_skin['mypage_counsel_product_w'], $_skin['mypage_counsel_product_h']);
				$pdata['checkbox'] = ($has_set != 'Y') ? "<input type='checkbox' name='repay_no[]' value='$pdata[no]' checked>" : '';
				$pdata['name'] = stripslashes($pdata['name']);
				$pdata['option_str'] = str_replace('<split_big>', ' / ', stripslashes($pdata['option']));
				$pdata['option_str'] = str_replace('<split_small>', ' : ', $pdata['option_str']);
				$pdata['img'] = $img[0];
				$pdata['img_str'] = $img[1];
				$pdata['pay_prc'] = parsePrice($pdata['total_prc']-getOrderTotalSalePrc($pdata), true);
				$pdata['total_prc'] = parsePrice($pdata['total_prc'], true);
				$pdata['buy_ea'] = number_format($pdata['buy_ea']);
				$pdata['stat'] = $_order_stat[$pdata['stat']];

				$_tmp .= lineValues('mypage_1to1_prd_list', $_line, $pdata);
			}
			$_replace_code[$_file_name]['mypage_1to1_prd_list'] = listContentSetting($_tmp, $_line);

			if($_return_milage > $member['milage']) {
				msg(__lang_mypage_error_repay_milage1__, 'back');
			}
		}
	}

	//캡차
	if($cfg['captcha_key'] && $member['level']!=1) {
		$_cap_use = "<div style='display: inline-block;display: inline-block;text-align:left;width:100%;' id='grecaptcha_element'></div>";
		if($member['no']){
			if($cfg['usecap_to']=="Y" && $cfg['usecap_member_to']=="Y") {
				$_replace_code[$_file_name]['mypage_1to1_cap_use'] = $_cap_use;
			}
		}else {
			if($cfg['usecap_to']=="Y" && $cfg['usecap_nonmember_to']=="Y") {
				$_replace_code[$_file_name]['mypage_1to1_cap_use'] = $_cap_use;
			}
		}
	}

    // 뒤로가기 버튼 링크
    if ($_GET['ono']) $rurl = $root_url.'/mypage/order_detail.php?ono='.$_GET['ono'];
    else if ($_POST['ono']) $rurl = 'javascript:history.back();';
    else $rurl = $root_url.'/mypage/counsel_list.php';
    $_replace_code[$_file_name]['mypage_previous_url'] = $rurl;

?>