<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  상품 등록 처리
	' +----------------------------------------------------------------------------------------------+*/

    use Wing\API\Naver\CommerceAPI;
	use Wing\API\Kakao\KakaoTalkPay;
    use Wing\common\WorkLog;

	printAjaxHeader();

	$layer1 = 'bodyLay2';
	$layer2 = 'bodyLay3';

	// 기본 체크
	$pno = numberOnly($_POST['pno']);
	checkBlank($pno, $big. $name. '필수값(PNO)을 입력해주세요.');
	$data = get_info($tbl['product'], 'no', $pno);
	checkBlank($data['no'], '자료값을 입력해주세요.');

    if (is_null($_SESSION['partner_login_no']) == true && $admin['level'] == '4' && $admin['partner_no'] != $data['partner_no']) {
        msg('접근 권한이 없습니다.');
    }

	// 스마트스토어
	if(isset($_POST['n_store_check']) == true && $_POST['n_store_check'] == 'Y') {
        $_POST['name'] = strip_tags($_POST['name']);
		checkBlank($_POST['n_category_big'], '스마트스토어 카테고리 대분류를 선택해주세요.');
		checkBlank($_POST['n_category_mid'], '스마트스토어  카테고리 중분류를 선택해주세요.');

		checkBlank($_POST['n_origin_big'], '스마트스토어 원산지 대분류를 선택해주세요.');
        if (!in_array($_POST['n_origin_big'], array('03', '04', '05'))) {
            checkBlank($_POST['n_origin_mid'], '스마트스토어 원산지 중분류를 선택해주세요.');
            checkBlank($_POST['n_origin_small'], '스마트스토어 원산지 소분류를 선택해주세요.');
        }

		checkBlank($_POST['n_as_tel'], '스마트스토어 A/S전화번호를 입력해주세요.');
		if(preg_match('/[^0-9-]/', $_POST['n_as_tel']) == true) {
			msg('스마트스토어 A/S전화번호는 숫자와 하이픈(-)만 입력할 수 있습니다.');
		}

		checkBlank($_POST['n_as_comment'], '스마트스토어 A/S안내를 입력해주세요.');
		checkBlank($_POST['n_summary_no'], '스마트스토어 상품정보고시를 선택해주세요.');

		// 옵션 최대 갯수
        $optioncount = $pdo->row("select count(*) from {$tbl['product_option_set']} where pno='$pno'");
		if($optioncount > 3) msg($optioncount.'스마트스토어 사용 시 옵션은 최대 3개까지만 등록할 수 있습니다.');

        // 반품/교환지 주소
		if(!$_POST['n_delivery_parcel']) {
			msg('반품/교환지 주소를 선택해주세요.');
		}
	}

	$prd_type = numberOnly($_POST['prd_type']);
	if(!$prd_type) $prd_type = 1;
	$tax_free = (!$_POST['tax_free']) ? 'N' : 'Y';
	$import_flag = (!$_POST['import_flag']) ? 'N' : 'Y';

	$addslashes_key = array('name', 'content1', 'content2', 'content3', 'content4', 'content5', 'keyword', 'seller', 'origin_name', 'name_referer', 'etc1', 'etc2','sell_prc_consultation','sell_prc_consultation_msg', 'm_content');
	$addslashes_key = array('name', 'content1', 'content2', 'content3', 'content4', 'content5', 'keyword', 'seller', 'origin_name', 'name_referer', 'etc1', 'etc2','sell_prc_consultation','sell_prc_consultation_msg', 'm_content', 'set_sale_type');
	$number_key = array('sell_prc', 'normal_prc','m_sell_prc', 'm_normal_prc', 'milage', 'origin_prc', 'min_ord', 'max_ord', 'max_ord_mem', 'weight', 'big', 'mid', 'small', 'depth4', 'set_sale_prc');
	$ncs_key = array('name', 'sell_prc', 'big');
	for($i = 2; $i <= 9; $i++) {
		$number_key[] = 'sell_prc'.$i;
	}

    // 세트상품 체크
    if ($prd_type == '4' || $prd_type == '5' || $prd_type == '6') {
        $set_rows = $pdo->row("select count(*) from {$tbl['product_refprd']} where pno='$pno' and `group`=99");
        if ($set_rows < 1) {
            msg('세트 구성상품을 선택해주세요.');
        }
    }

	if($admin['level'] == 4 && $cfg['partner_prd_accept'] == 'Y') {
		if(!trim($_POST['partner_cmt'])) msg('상품 변경 내용 및 사유를 입력해 주세요.');
	}

	if($_POST['req_stat'] == 2 && !numberOnly($_POST['stat'])) {
		msg('상품 등록 승인 시 지정될 상품 상태를 선택해 주세요.');
	}

	foreach($_POST as $key=>$val) {
		if(is_array($val)) continue;
		if(in_array($key, $number_key)) $val = numberOnly($val, true);
		if(in_array($key, $ncs_key)) checkBlank($val, "필수값($key)을 입력해주세요.");
		if(in_array($key, $addslashes_key)) $val = addslashes($val);

		${$key}=strip_script($val);
	}
    $no_milage = ($_POST['no_milage'] == 'Y') ? 'Y' : 'N';
    $no_cpn = ($_POST['no_cpn'] == 'Y') ? 'Y' : 'N';
    $no_ep = ($_POST['no_ep'] == 'N') ? 'N' : 'Y';
    $dlv_alone = ($_POST['dlv_alone'] == 'Y') ? 'Y' : 'N';

	if($_POST['kko_useYn'] == 'Y' && $_POST['ea_type'] != '1') {
		msg('재고관리 사용 상태에서만 카카오톡스토어 연동이 가능합니다.');
	}

	if($max_ord && $min_ord > $max_ord) msg('최소주문은 최대주문보다 적어야합니다.');
	if($min_ord < 1) $min_ord = 1;
	$asql = '';


	// 기획전/모바일 기획전
	$ectype = array(2 => 'ebig', 6 => 'mbig');
	foreach($ectype as $ctype => $cfield) {
		$tmp = $_POST[$cfield];

		// 제거
		$old = explode('@', trim($_POST[$cfield.'_old'], '@'));
		foreach($old as $cno) {
			if($cno > 0 && in_array($cno, $tmp) == false) {
				$pdo->query("delete from $tbl[product_link] where pno='$pno' and (nbig='$cno' or nmid='$cno' or nsmall='$cno')");
			}
		}

		// 추가
		if(is_array($tmp)) {
			$tmp_str = '';
			foreach($tmp as $cno) {
				$cate = $pdo->assoc("select no, big, mid, level from $tbl[category] where no='$cno'");
				if($cate['level'] == 1) $cate['big'] = $cate['no'];
				if($cate['level'] == 2) $cate['mid'] = $cate['no'];
				if($cate['level'] == 3) $cate['small'] = $cate['no'];
				if(!$cate['mid']) $cate['mid'] = 0;
				if(!$cate['small']) $cate['small'] = 0;
				$sort1 = $sort2 = $sort3 = 1;

				if(!$pdo->row("select count(*) from $tbl[product_link] where pno='$pno' and nbig='$cate[big]' and nmid='$cate[mid]' and nsmall='$cate[small]'")) {
					if(${$cfield.'_first'} != 'Y') {
						if($cate['big']) $sort1 = $pdo->row("select max(sort_big) from $tbl[product_link] where nbig='$cate[big]'")+1;
						if($cate['mid']) $sort2 = $pdo->row("select max(sort_big) from $tbl[product_link] where nmid='$cate[mid]'")+1;
						if($cate['small']) $sort3 = $pdo->row("select max(sort_big) from $tbl[product_link] where nsmall='$cate[small]'")+1;
					} else {
						if($cate['big']) $pdo->query("update $tbl[product_link] set sort_big=sort_big+1 where nbig='$cate[big]'");
						if($cate['mid']) $pdo->query("update $tbl[product_link] set sort_mid=sort_mid+1 where nmid='$cate[mid]'");
						if($cate['small']) $pdo->query("update $tbl[product_link] set sort_mid=sort_mid+1 where nsmall='$cate[small]'");
					}
					$pdo->query("
						insert into $tbl[product_link] (pno, ctype, nbig, nmid, nsmall, sort_big, sort_mid, sort_small)
						values ('$pno', '$ctype', '$cate[big]', '$cate[mid]', '$cate[small]', '$sort1', '$sort2', '$sort3')
					");
				}
				$tmp_str .= '@'.$cno;
			}
			${$cfield.'_str'} = $tmp_str.'@';
		}
	}


	// 완료처리
	if($data['stat']==1 || $_POST['req_stat'] == 1) {
		$asql .= " , `reg_date`='$now'";
		$ems = '정상적으로 상품이 등록되었습니다.';
		$rURL = ($data['partner_no'] > 0 && $cfg['partner_prd_accept'] == 'Y') ? './?body=product@product_rev' : './?body=product@product_list';
	} else {
		$ems = '정상적으로 상품 정보가 수정되었습니다';
		$rURL = 'reload';
	}

	// 추가항목
    $field_category = array(0); // 추가항목
    if ($fieldset > 0) array_push($field_category, $fieldset); // 정보고시
    $field_category = implode(',', $field_category);
	$sql = "select no, category from `".$tbl['product_filed_set']."` where category in ($field_category) order by `no` desc"; //
	$res = $pdo->iterator($sql);
    foreach ($res as $fsdata) {
		$fno = $fsdata['no']; // 항목 번호
		$fvalue = addslashes(${"field".$fno});
		$fdata = $pdo->assoc("select * from `".$tbl['product_filed']."` where `pno`='$pno' and `fno`='$fno'");

		if($fvalue || $fsdata['category'] == $_POST['fieldset'] || ($cfg['partner_prd_accept'] == 'Y' && $data['ori_no'] > 0)) {
			if($fdata['no']) $fsql = "update `".$tbl['product_filed']."` set `value`='$fvalue' where `no`='$fdata[no]'";
			else $fsql = "INSERT INTO `".$tbl['product_filed']."` ( `pno` , `fno` , `value` ) VALUES ( '$pno', '$fno', '$fvalue')";
		} else {
			$fsql = "delete from `".$tbl['product_filed']."` where `no`='$fdata[no]'";
		}
		$pdo->query($fsql);
	}

    // 카카오페이구매 정보고시
    if ($scfg->comp('use_talkpay', 'Y') == true && $_POST['use_talkpay'] == 'Y') {
        $kko = new KakaoTalkPay($scfg);
        $kko->setAnnoucement($pno, $_POST['kakao_annoucement_idx']);
        $r = $kko->syncProduct(array($data['hash'])); // 상품 정보 싱크
    }

	// 정렬순서
	if($data['sortbig'] < 1) {
		$_sort_info = $pdo->assoc("select max(`sortbig`) as sortbig, max(`sortmid`) as sortmid, max(`sortsmall`) as sortsmall from `$tbl[product]`");
		$_sortbig = $_sort_info['sortbig']+1;
		$_sortmid = $_sort_info['sortmid']+1;
		$_sortsmall = $_sort_info['sortsmall']+1;
		$asql .= " , `sortbig`='$_sortbig', `sortmid`='$_sortmid', `sortsmall`='$_sortsmall'";
	}

	// 2분류 3분류 정렬 사용
	if($cfg['use_new_sortxy'] == 'Y') {
		for($ct = 4; $ct <= 5; $ct++) {
			$nbig = numberOnly($_POST[$_cate_colname[$ct][1]]);
			$nmid = numberOnly($_POST[$_cate_colname[$ct][2]]);
			$nsmall = numberOnly($_POST[$_cate_colname[$ct][3]]);
			$ndepth4 = numberOnly($_POST[$_cate_colname[$ct][4]]);

			createProductLink($pno, $ct, $nbig, $nmid, $nsmall, $ndepth4);
		}
	}

	$sbig = numberOnly($_POST['sbig']);
	$smid = numberOnly($_POST['smid']);
	$ssmall = numberOnly($_POST['ssmall']);
	$sdepth4 = numberOnly($_POST['sdepth4']);
	if(isset($_POST['sbig'])) {
		if($sbig > 0) {
			$storage_no = $pdo->row("select no from $tbl[erp_storage] where big='$sbig' and mid='$smid' and small='$ssmall' and depth4='$sdepth4'");
			if($storage_no < 1) msg('입력하신 창고정보가 존재하지 않습니다.');
		} else {
			$storage_no = 0;
		}
		$asql .= ", storage_no='$storage_no'";
	}

	// 상품이미지 용량 제한
	include_once $GLOBALS['engine_dir'].'/_config/set.upload.php';
	$upfile_size_limit = $up_cfg['prdBasic']['filesize']; // KB
	if(defined('__USE_UNLIMITED_DISK__') == true) {
		unset($upfile_size_limit);
	}
	wingUploadRule($_FILES, 'prdBasic');

	// 대중소 이미지 업로드
	$_img_changed = array();
	$updir=$data['updir'];
	if(!$cfg['add_prd_img']) $cfg['add_prd_img'] = 3;
	$_img_fd_name = array(1 => '큰사진보기 출력용(대)', 2 => '상세설명 출력용(중)', 3 => '상품리스트 출력용(소)');
	for($ii = 0; $ii <= $cfg['add_prd_img']; $ii++) {
		$chg_file = '';
		if($updir && ($_POST['delfile'.$ii]=="Y" || $_FILES['upfile'.$ii]['tmp_name'])) {
			deletePrdImage($data, $ii, $ii);
			$up_filename = $width = $height = '';
			$chg_file = 1;
		}
		if($_FILES['upfile'.$ii]['tmp_name']) {
			if(!$updir) {
				if($cfg['use_icb_storage'] == 'Y') {
					$dir['upload'] = $cfg['current_icb_updir'];
					$asql .= ", upurl='$cfg[current_icb_upurl]'";
				}

				$updir = $dir['upload'].'/'.$dir['product'].'/'.date('Ym', $now).'/'.date('d',$now);
				makeFullDir($updir);
				$asql .= " , `updir`='$updir'";
			}

			$up_filename = md5($ii + time());
			list($width, $height) = getimagesize($_FILES["upfile".$ii]['tmp_name']);
			if($upfile_size_limit && $_FILES['upfile'.$ii]['size'] > $upfile_size_limit*1024){
				@unlink($_FILES['upfile'.$ii]['tmp_name']);
				msg($_img_fd_name[$ii]."파일(\"".$_FILES["upfile".$ii]['name']."\")이 ".$upfile_size_limit."KB를 초과하여 업로드가 중지되었습니다.     \\n\\n이미지 용량을 줄이신 뒤 다시 업로드해 주십시오.");
			}
			$up_info=uploadFile($_FILES["upfile".$ii],$up_filename,$updir,"jpg|jpeg|gif|png|swf",$upfile_size_limit);

			$up_filename=$up_info[0];
			$chg_file=1;
			$_img_changed[$ii] = true;
		}

		if($ii>3) {
			addField($tbl[product],'upfile'.$ii,'varchar(36) default NULL');
			addField($tbl[product],'w'.$ii,'int(3) default NULL');
			addField($tbl[product],'h'.$ii,'int(3) default NULL');
		}

		if($chg_file) $asql.=" , `upfile".$ii."`='".$up_filename."' , `w".$ii."`='".$width."' , `h".$ii."`='".$height."'";
	}

	if(!$event_sale) $event_sale="N";
	if(!$member_sale) $member_sale="N";

	for($ii=3; $ii<=5; $ii++) {
		if($_POST['content'.$ii.'_default']) {
			${'content'.$ii}='wisamall_default';
		}
	}

	// 상품코드
	if($auto_code) {
		unset($_big, $_mid, $_small);
		include_once $engine_dir."/_engine/include/shop.lib.php";
		if($big) $_big=getCateInfo($big);
		if($mid) $_mid=getCateInfo($mid);
		if($small) $_small=getCateInfo($small);
		$pre_code=$_big[code].$_mid[code].$_small[code];
		if(!$pre_code) $pre_code=substr($data[hash],0,4);
		if(!$cfg[prd_post_code]) $cfg[prd_post_code]=3;
		$post_code=addZero($data[no],$cfg[prd_post_code]);
		$code=$pre_code."-".$post_code;
	}

	$icons = $_POST['icons'];
	$asql .= ", `mng_memo`='$mng_memo'";
	$seller = addslashes($pdo->row("select `provider` from `$tbl[provider]` where `no` = '$seller_idx'"));
	if(is_array($icons)) {
		$icons = implode('@', $icons);
		$icons = preg_replace('/^@|@$/', '', $icons);
		$icons = '@'.$icons.'@';
	}

	if(!$stat) $stat = 1;
	if(trim($name) == '' || $stat < 1) msg('상품 저장 시 오류가 발생하였습니다.\\t\\n다시 저장 해 주십시오.');
	if($cfg['partner_prd_accept'] == 'Y' && $admin['partner_no'] > 0) {
		if($req_stat != 2) $stat = 1;
	}
	if($cfg['use_partner_shop'] == 'Y') {
		$partner_no = numberOnly($_POST['partner_no']);
		if(empty($partner_no) == true) $partner_no = (isset($admin['partner_no']) == true) ? $admin['partner_no'] : 0;
		$partner_rate = numberOnly($_POST['partner_rate'], true);
		$dlv_type = numberOnly($_POST['dlv_type']);
		$asql .= ", partner_rate='$partner_rate', dlv_type='$dlv_type'";
		if($admin['level'] < 4) $asql .= ", partner_no='$partner_no'";
		else $asql .= ", partner_no='$admin[partner_no]'";
	}

    // 어떤 세트에 포함되어 있을 시 입점사와 배송처를 변경할 수 없음
    $is_set_components = $pdo->row("select count(*) from {$tbl['product_refprd']} where refpno='$pno' and `group`=99");
    if ($is_set_components > 0) {
        if ((int) $data['partner_no'] != (int) $partner_no) msg('세트에 포함된 상품은 입점사를 변경할 수 없습니다.');
        if ((int) $data['dlv_type'] != $dlv_type) msg('세트에 포함된 상품은 배송처를 변경할 수 없습니다.');
        if ($dlv_alone == 'Y') msg('세트에 포함된 상품은 단독배송으로 설정할 수 없습니다.');
    }

	if($cfg['mobile_use']=='Y') $asql.=", `mbig`='$mbig_str'";
	$name = strip_tags($name, '<font><br><b><em><i><kbd><code><tt><u>');

	if($cfg['delivery_fee_type'] == "O" || $cfg['delivery_fee_type'] == "A") $asql .= ",`weight`='$weight'";

	$show_mobile = $_POST['show_mobile'] == 'Y' ? 'Y' : 'N';
	$checkout = $_POST['checkout'] == 'Y' ? 'Y' : 'N';
	$use_talkpay = $_POST['use_talkpay'] == 'Y' ? 'Y' : 'N';

    if ($use_talkpay == 'Y' && $ea_type != '1') {
        msg ('카카오 페이구매는 재고관리 사용 시에만 설정할 수 있습니다.');
    }
    if ($use_talkpay == 'Y' && $delivery_set > 0) {
        msg ('카카오 페이구매는 개별배송비 정책을 설정하실수 없습니다.');
    }

	for($mi=1; $mi <= 9; $mi ++) { // 회원그룹별 가격
		if($cfg['group_price'.$mi] == 'Y') $asql.=", `sell_prc$mi`='".${'sell_prc'.$mi}."'";
	}

	$_content2 = addslashes($pdo->row("select content2 from $tbl[product] where no='$pno'"));
	if($content2 && $content2 != $_content2) {
		$content2 = str_replace(chr(194).chr(160), "&nbsp;", $content2);
		$pdo->query("update $tbl[product] set content2='$content2' where no='$pno'");
		if($_content2) {
			$pdo->query("INSERT INTO `".$tbl['product_content_log']."` (`pno`, `content2`, `admin_id`, `mobile`, `reg_date`) VALUES ('$pno', '$_content2', '".$admin['admin_id']."', 'P', '$now')");
			$content_log = $pdo->assoc("select count(*) as `count`, min(`no`) as `no` from `".$tbl['product_content_log']."` where `pno` = '$pno' and `mobile` = 'P'");
			if($content_log['count'] > 10) {
				$pdo->query("delete from `".$tbl['product_content_log']."` where `no` = '".$content_log['no']."'");
			}
		}
	}
	if($cfg['use_m_content_product'] == 'Y') {
		$use_m_content = ($_POST['use_m_content'] == 'Y') ? 'Y' : 'N';
		$asql .= ",use_m_content='$use_m_content'";

		if($m_content == '<p>&nbsp;</p>') $m_content = ''; // 크롬/IE 에서 내용 모두 지울 경우
		$_m_content = addslashes($pdo->row("select m_content from $tbl[product] where no='$pno'"));
		if($m_content && $m_content != $_m_content) {
			$pdo->query("update $tbl[product] set m_content='$m_content' where no='$pno'");
			if($_m_content) {
				$pdo->query("INSERT INTO `".$tbl['product_content_log']."` (`pno`, `content2`, `admin_id`, `mobile`, `reg_date`) VALUES ('$pno', '$_m_content', '".$admin['admin_id']."', 'M', '$now')");
				$m_content_log = $pdo->assoc("select count(*) as `count`, min(`no`) as `no` from `".$tbl['product_content_log']."` where `pno` = '$pno' and `mobile` = 'M'");
				if($m_content_log['count'] > 10) {
					$pdo->query("delete from `".$tbl['product_content_log']."` where `no` = '".$m_content_log['no']."'");
				}
			}
		}
	}

	// 한정시간 판매
	if($cfg['ts_use'] == 'Y' && $admin['level'] != '4') {
		$ts_use = ($_POST['ts_use'] == 'Y') ? 'Y' : 'N';
		$ts_set = ($_POST['use_ts_set'] == 'Y' && $_POST['ts_use'] == 'Y') ? numberOnly($_POST['ts_set']) : 0;
		$ts_dates = strtotime($_POST['ts_dates'].' '.$_POST['ts_times'].':'.$_POST['ts_mins'].':00');
		$ts_datee = strtotime($_POST['ts_datee'].' '.$_POST['ts_timee'].':'.$_POST['ts_mine'].':59');
		if($ts_set > 0) {
			$_ts_set = $pdo->assoc("select ts_dates, ts_datee, ts_state from {$tbl['product_timesale_set']} where no='$ts_set'");
			$ts_dates = $_POST['ts_dates'] = strtotime($_ts_set['ts_dates']);
			$ts_datee = $_POST['ts_datee'] = strtotime($_ts_set['ts_datee']);
			$ts_state = $_POST['ts_state'] = $_ts_set['ts_state'];
            if ($_ts_set['ts_datee'] == 0) {
                $_POST['ts_unlimited'] = 'Y';
            }
		}
        if (isset($_POST['ts_unlimited']) == true) {
            $ts_datee = 0;
        }
		$ts_names = addslashes(trim($_POST['ts_names']));
		$ts_namee = addslashes(trim($_POST['ts_namee']));
		$ts_event_type = ($_POST['ts_event_type'] == '1') ? '1' : '2';
		$ts_saleprc = numberOnly($_POST['ts_saleprc']);
		$ts_saletype = ($_POST['ts_saletype'] == 'price') ? 'price' : 'percent';
		$ts_cut = numberOnly($_POST['ts_cut']);
		$ts_state = numberOnly($_POST['ts_state']);
		$ts_ing = ($ts_use == 'Y' && $ts_dates <= $now && ($ts_datee == 0 || $ts_datee >= $now)) ? 'Y' : 'N';

        if ($prd_type != '1') { // 세트 상품일 경우 할인 적립기능 미사용
            $ts_event_type = '1';
            $ts_saletype = 'price';
            $ts_saleprc = 0;
        }

		if($ts_use == 'Y') {
			checkBlank($_POST['ts_dates'], '한정판매 시작일을 입력해주세요.');
			if (isset($_POST['ts_unlimited']) == false) checkBlank($_POST['ts_datee'], '한정판매 종료일을 입력해주세요.');
		}

		if(fieldExist($tbl['product'], 'ts_set') == false) {
			addField($tbl['product'], 'ts_set', 'int(5) not null default "0" after ts_use');
			addField($tbl['product'], 'ts_event_type', 'enum("1","2") not null default "1" after ts_namee');
			addField($tbl['product'], 'ts_cut', 'int(4) not null default "1" after ts_saletype');

			include_once $engine_dir.'/_config/tbl_schema.php';
			$pdo->query($tbl_schema['product_timesale_set']);
		}

		$asql .= ", ts_use='$ts_use', ts_set='$ts_set', ts_dates='$ts_dates', ts_datee='$ts_datee', ts_names='$ts_names', ts_namee='$ts_namee', ts_event_type='$ts_event_type', ts_cut='$ts_cut', ts_saleprc='$ts_saleprc', ts_saletype='$ts_saletype', ts_state='$ts_state', ts_ing='$ts_ing'";
	}

	if($hs_code){
		addField($tbl['product'], 'hs_code', 'varchar(15) comment "HS CODE"');
		$asql .= ", `hs_code`='$hs_code'";
	}

	if($name_referer){
		addField($tbl['product'], 'name_referer', 'varchar(150) comment "참고용 상품명"');
	}
	$asql .= ", name_referer='$name_referer'";

	$asql .= ", `m_normal_prc`='$m_normal_prc', `m_sell_prc`='$m_sell_prc'";

	if($cfg['use_prd_etc1'] == 'Y') $asql .= ", etc1='$etc1'";
	if($cfg['use_prd_etc2'] == 'Y') $asql .= ", etc2='$etc2'";
	if($cfg['use_prd_etc3'] == 'Y') $asql .= ", etc3='$etc3'";

	if(!fieldExist($tbl['product'], 'dlv_alone')) {
		addField($tbl['product'], 'dlv_alone', "enum('N','Y') not null default 'N' comment '단독배송여부' after free_delivery");
		$pdo->query("alter table $tbl[product] drop no_interest");
	}

	if($cfg['delivery_fee_type'] == 'O' || $cfg['delivery_fee_type'] == 'A'){
		if(!fieldExist($tbl['product'], 'oversea_free_delivery')) {
			addField($tbl['product'], 'oversea_free_delivery', "enum('N','Y') not null default 'N' comment '해외무료배송여부' after free_delivery");
		}

		$_POST['oversea_free_delivery'] = !$_POST['oversea_free_delivery']?'N':$_POST['oversea_free_delivery'];
		$asql.= ", oversea_free_delivery='".$_POST['oversea_free_delivery']."'";
	}


	if($cfg['use_prc_consult'] == 'Y') {
		$asql .= ", `sell_prc_consultation`='$sell_prc_consultation', `sell_prc_consultation_msg`='$sell_prc_consultation_msg'";
	}
	if($cfg['import_flag_use'] == 'Y') {
		$asql .= ", `import_flag`='$import_flag'";
	}
	if($cfg['compare_today_start_use'] == 'Y') {
		if(!$compare_today_start) $compare_today_start = "N";
		$asql .= ", `compare_today_start`='$compare_today_start'";
	}

	for($i = 4; $i <= $cfg['max_cate_depth']; $i++) {
		foreach(array(1, 4, 5) as $_ctype) {
			$_nm = $_cate_colname[$_ctype][$i];
			$_val = numberOnly($_POST[$_nm]);
			$asql .= ", $_nm='$_val'";
		}
	}


	if($cfg['use_kakaoTalkStore'] == 'Y') {
		$kko_useYn = ($_POST['kko_useYn'] == 'Y') ? 'Y' : 'N';
		$asql .= ", use_talkstore='$kko_useYn'";
	}

	// 노출여부
	if($cfg['use_prd_perm'] != 'Y') {
		$pdo->query("
		ALTER TABLE `wm_product`
			ADD COLUMN `perm_lst` ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT '상품 목록 노출 여부',
			ADD COLUMN `perm_dtl` ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT '상품 상세 노출 여부',
			ADD COLUMN `perm_sch` ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT '상품 검색 노출 여부';
		");
		$pdo->query("insert into {$tbl['config']} (name, value, reg_date) values ('use_prd_perm', 'Y', '$now')");
	}
	$perm_lst = ($_POST['perm_lst'] == 'Y') ? 'Y' : 'N';
	$perm_dtl = ($_POST['perm_dtl'] == 'Y') ? 'Y' : 'N';
	$perm_sch = ($_POST['perm_sch'] == 'Y') ? 'Y' : 'N';
	$perm_asql = ", perm_lst='$perm_lst', perm_dtl='$perm_dtl', perm_sch='$perm_sch'";
    $asql .= $perm_asql;

	// 회원별 주문한도
	addField($tbl['product'], 'max_ord_mem', 'tinyint(3) not null default "0" after max_ord');

	$free_delivery = ($_POST['delivery_type'] == 'free_delivery') ? 'Y' : 'N';

    // 개별배송비 정책
	if($cfg['use_prd_dlvprc'] == 'Y') {
		if($delivery_type == 'product') {
            if ($is_set_components > 0) {
                msg('세트상품의 구성상품은 개별배송으로 설정할 수 없습니다.');
            }
			if(empty($delivery_set) == true) {
				msg('개별 배송비 세트를 선택해주세요.');
			}
			$checkout = 'N';
            $use_talkpay = 'N';
            if ($scfg->comp('use_partner_delivery', 'Y') == true && $dlv_type != '1') {
                $_ptn_no = $pdo->row("select partner_no from {$tbl['product_delivery_set']} where no='$delivery_set'");
                if($admin['level'] == 4) $partner_no = $admin['partner_no'];
                if(empty($partner_no)) $partner_no = 0;

                if($_ptn_no != $partner_no) {
                    msg('입점사 상품에는 해당 입점사의 개별 배송비 정책만 적용할 수 있으며, 입점사 개별 배송비 정책의 경우 입점사 관리자 페이지에서 적용할 수 있습니다.');
                }
            }
		} else {
			$delivery_set = 0;
		}
		$asql .= ",delivery_set='$delivery_set'";
	}

	// 적립금, 쿠폰 사용불가
	if($cfg['use_no_mile/cpn'] == 'Y') {
		$asql .= ",`no_milage`='$no_milage', `no_cpn`='$no_cpn'";
	}
    if ($scfg->comp('compare_explain', 'Y') == true) {
        if ($no_ep != 'N') $no_ep = 'Y';
        $asql .= ", no_ep='$no_ep'";
    }

    if ($scfg->comp('use_talkpay', 'Y') == true) {
        $asql .= ", use_talkpay='$use_talkpay'";
    }

	// 성인인증 필요 상품
	if ($scfg->comp('use_kcb', 'Y')) {
		$adult = $_POST['adult'] == 'Y' ? 'Y' : 'N';
		$asql .= " , adult='$adult'";
	}

    // 도서 상품 추가 정보
    if ($scfg->comp('use_navershopping_book', 'Y') == true && $prd_type == '1') {
        $is_book = $_POST['is_book'];
        if (array_key_exists($is_book, $_is_book_type) == false) $is_book = 'N';
        $asql .= ", is_book='$is_book'";

        if (empty($_POST['is_book']) == false && $_POST['is_book'] != 'N') {
            if (fieldExist($tbl['product_book'], 'size') == false) {
                addField($tbl['product_book'], 'size', 'varchar(50) not null default ""');
                addField($tbl['product_book'], 'pages', 'varchar(20) not null default ""');
                addField($tbl['product_book'], 'description', 'text not null default ""');
            }

            foreach ($_POST as $key => $val) {
                if (preg_match('/^book_/', $key) == true) ${$key} = trim($val);
            }
            if (empty($book_title) == true) msg('도서명을 입력해주세요.');
            if (empty($book_publish_day) == true) msg('도서 출간일을 입력해주세요.');

            if ($pdo->row("select count(*) from {$tbl['product_book']} where no='$pno'") > 0) {
                $pdo->query("
                    update {$tbl['product_book']} set
                        is_used=?, isbn=?, title=?, number=?, version=?, subtitle=?, original_title=?,
                        author=?, publisher=?, publish_day=?, size=?, pages=?, description=?
                    where no=?
                ", array(
                    $book_is_used, $book_isbn, $book_title, $book_number, $book_version, $book_subtitle, $book_original_title,
                    $book_author, $book_publisher, $book_publish_day, $book_size, $book_pages, $book_description, $pno
                ));
            } else {
                $pdo->query("
                    insert into {$tbl['product_book']}
                        (no, is_used, isbn, title, number, version, subtitle, original_title, author, publisher, publish_day, size, pages, description)
                        values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ", array(
                    $pno, $book_is_used, $book_isbn, $book_title, $book_number, $book_version, $book_subtitle, $book_original_title,
                    $book_author, $book_publisher, $book_publish_day, $book_size, $book_pages, $book_description
                ));
            }
        }
    }

	// 세트상품
	if ($prd_type == '4') {
		$asql .= ", set_sale_prc='$set_sale_prc', set_sale_type='$set_sale_type'";
	} else if ($prd_type == '5') {
		if(empty($data['set_rate']) == true) msg('세트 할인 조건을 입력해주세요.');
	} else if ($prd_type == '6') {
        $set_pick_qty = (int) $set_pick_qty;
        if ($set_pick_qty < 2) {
            msg('골라 담기 상품 개수를 2개 이상으로 입력해 주세요.');
        }
        $set_rate = json_encode(array(
            'sale_type' => 'm',
            'data' => array($set_pick_qty => 0)
        ));
        $asql .= ", set_rate='$set_rate'";
    }
	if ($prd_type == '4' || $prd_type == '5' || $prd_type == '6') {
		$ptn_check = $pdo->rowCount("
            select distinct if (p.dlv_type=1, '0', partner_no)
                from {$tbl['product']} p inner join {$tbl['product_refprd']} r on p.no=r.refpno
                where r.pno='$pno' and `group`=99
        ");
		if ($ptn_check > 1) {
			msg('여러 입점사의 상품을 한 세트에 추가할 수 없습니다.');
		}
	}

	// 네이버 지식쇼핑, 수정일 갱신 전 데이터
	$old_data2=$pdo->assoc("select a.hash, a.name, a.stat, a.keyword, a.sell_prc, a.upfile1,a.big, a.mid, a.small,a.code,a.milage, b.value as origi from `".$tbl['product']."` a left join `".$tbl['product_filed']."` b on a.`no`=b.`pno` and b.`fno`=3 where a.`no`='$pno'");

	$sql = "update `".$tbl['product']."` set `name`='$name',`seller_idx`='$seller_idx',`seller`='$seller',`origin_name`='$origin_name',`stat`='$stat',`sell_prc`='$sell_prc',`normal_prc`='$normal_prc',`milage`='$milage',`origin_prc`='$origin_prc',`ea_type`='$ea_type',`min_ord`='$min_ord',`max_ord`='$max_ord', max_ord_mem='$max_ord_mem', `content1`='$content1',`content3`='$content3',`content4`='$content4',`content5`='$content5', fieldset='$fieldset', `icons`='$icons',`big`='$big',`mid`='$mid',`small`='$small',`obig`='$obig',`omid`='$omid',`ebig`='$ebig_str',`xbig`='$xbig',`xmid`='$xmid',`xsmall`='$xsmall',`ybig`='$ybig',`ymid`='$ymid',`ysmall`='$ysmall',`code`='$code',`etc1`='$etc1',`etc2`='$etc2',`event_sale`='$event_sale',`keyword`='$keyword',`dlv_alone`='$dlv_alone',`tax_free`='$tax_free',`member_sale`='$member_sale',`free_delivery`='$free_delivery',`checkout`='$checkout',`show_mobile`='$show_mobile',`edt_date`='$now', prd_type='$prd_type' $asql $giftq where `no`='$pno'";
	$_r = $pdo->query($sql);
	if($_r == false) {
		$fp = fopen($root_dir.'/_data/product_regist_error.txt', 'a+');
		fwrite($fp, $pdo->getError()."\n\n");
		fclose($fp);

		msg("DB 실행에러! 1:1고객센터 문의 글로 접수 바랍니다.");
	}
	if($_r && $data['stat'] != $stat) prdStatLogw($pno, $stat, $data['stat']);
	$_log_stat=($new_prd == "1") ? "1" : "2";
	if($_r) productLogw($pno,$name,$_log_stat);

    $log = new WorkLog();
    $log->createLog(
        $tbl['product'],
        (int) $pno,
        'name',
        $data,
        $pdo->assoc("select * from {$tbl['product']} where no=?", array($pno)),
        array('edt_date', 'updir')
    );

    // 재고관리 방식 변경 시 장바구니 삭제
    if ($data['ea_type'] != $_POST['ea_type']) {
        $pdo->query("delete from {$tbl['cart']} where pno='$pno'");
    }

	if(is_object($erpListener)) {
		$erpListener->setProduct($pno, $data['stat']);
	}

	//수정일 갱신후 데이터
	$ep_stat=null;
	$icnt=0;

	$new_data2=$pdo->assoc("select a.hash, a.name, a.stat, a.keyword, a.sell_prc, a.upfile1,a.big, a.mid, a.small,a.code,a.milage, b.value as origi from `".$tbl['product']."` a left join `".$tbl['product_filed']."` b on a.`no`=b.`pno` and b.`fno`=3 where a.`no`='$pno'");
	foreach($old_data2 as $key => $val) {
		$icnt++;
		if($val != $new_data2[$key]) {
			if($key == "origi") $key="origi";
			if($key == "sell_prc") $key="sell_prc@deliv";
			if($icnt %2 ==0) $ep_stat.="@".$key;
		}
	}

	if($ep_stat) {
		$sql1="update `".$tbl['product']."` set  `edt_date2`='$now', `ep_stat`='$ep_stat' where `no`='$pno'";
		$_r1=$pdo->query($sql1);
	}

	//바로가기 상태값
	if($data['stat'] != $stat) {
		$pdo->query("update `{$tbl['product']}` set `stat`='$stat' where wm_sc='$pno' and `stat` != 5");
	}

	// 바로가기수정
	if($pno) $r=$pdo->query("update `".$tbl['product']."` set `xbig`='$xbig', `xmid`='$xmid', `xsmall`='$xsmall', `ybig`='$ybig', `ymid`='$ymid', `ysmall`='$ysmall', `name`='$name', `code`='$code', `keyword`='$keyword', `sell_prc`='$sell_prc', `event_sale`='$event_sale', `dlv_alone`='$dlv_alone', `member_sale`='$member_sale', `free_delivery`='$free_delivery',`checkout`='$checkout' $giftq $perm_asql where `wm_sc`='$pno'");

	// 입점사 상품 신청
	if($cfg['partner_prd_accept'] == 'Y') {
		if($_POST['req_stat'] > 0 && ($_POST['req_stat'] == 1 || $_POST['reg_stat'] != $data['partner_stat'])) {
			include_once $engine_dir.'/_partner/lib/partner_product.class.php';
			$pp = new PartnerProduct();
			$pp->setLog(array(
				'pno' => $pno,
				'req_stat' => $_POST['req_stat'],
				'name' => $name,
				'content' => $partner_cmt,
				'content2' => $manager_cmt,
			));
		}
	}
	// 입점사 관련상품 저장
	for($refkey = 1; $refkey <= $cfg['refprds']; $refkey++) {
		if($_POST['refhead_'.$refkey]) {
			$_refhead = explode(",", $_POST['refhead_'.$refkey]);
			$sort = 0;
			foreach($_refhead as $key=>$val) {
				$sort++;
				$hno = $pdo->row("select no from `$tbl[product_refprd]` where `pno`='$pno' and refpno='$val' and `group`='$refkey'");
				if($hno) {
					$pdo->query("update $tbl[product_refprd] set sort='$sort' where `no`='$hno'");
				}else {
					$pdo->query("insert into $tbl[product_refprd] (`pno`, `group`, `refpno`, `sort`, `reg_date`) values ('$pno', '$refkey', '$val', '$sort', '$now')");
				}
			}
			$pdo->query("delete from `$tbl[product_refprd]` where `pno`='$pno' and `group`='$refkey' and `refpno` not in (".implode(",",$_refhead).")");
		}
	}

	// 정기배송
	if($cfg['use_sbscr']=='Y') {
		$setno = numberOnly($_POST['setno']);
		$sub_use = ($_POST['sub_use'] == 'Y') ? 'Y' : 'N';
		$spno = $pdo->row("select no from $tbl[sbscr_set_product] where pno='$pno'");
		if($cfg['sbscr_type']=='P') {
			$sub_sql1 = ", setno='$setno'";
			$sub_sql2 = ", setno";
			$sub_sql3 = ", '$setno'";

			if($sub_use == 'Y' && empty($setno) == true) {
				msg("정기배송 설정 세트를 선택해주세요.");
			}
		}
		if($spno) {
			$pdo->query("update $tbl[sbscr_set_product] set `use`='$sub_use' $sub_sql1 where `pno`='$pno'");
		}else {
			$pdo->query("insert into $tbl[sbscr_set_product] (`pno`, `use`, `reg_date` $sub_sql2) values ('$pno', '$sub_use', '$now' $sub_sql3)");
		}
	}

	// 등록대기 상태의 옵션과 이미지 등록상태로 변경
	$pdo->query("update `".$tbl['product_option_set']."` set `stat`='2' where `pno`='$pno'");
	$pdo->query("update `".$tbl['product_image']."` set `stat`='2' where `pno`='$pno'");

    if ($kko_useYn == 'Y' || $data['use_talkstore'] != $kko_useYn) {
        if($cfg['use_kakaoTalkStore'] == 'Y') {
            include 'product_register_kakaoTalkStore.exe.php';
        }
    }

	if($auto_thumb=="Y" && $_FILES['upfile1']['size'] > 0) {
		unset($data);
		$data=get_info($tbl['product'],"no",$pno);
		$updir=$root_dir."/".$data[updir];

		if($_use[file_server] == "Y" && fsConFolder($data[updir]) && $data[upfile1]){
			$updir=$root_dir."/".$dir[upload]."/auto_thumb";
			fsFileDown($data[updir], $data[upfile1], $updir);
			$fsThumb="Y";
		}

		if(!$data[upfile1] || !is_file($updir."/".$data[upfile1])) $ems=$updir."/".$data[upfile1]."\\n 썸네일 생성을 위해선 원본 파일이 존재해야합니다      \\n\\n 썸네일 생성 작업에 실패했습니다\\n";

		$ext = getExt($data['upfile1']);
		if(!preg_match("/jpg|jpeg|gif|png/i", $ext)) msg("썸네일을 만들수 없는 이미지 형식입니다");
		$asql="";

		for($ii=2; $ii<=3; $ii++) {
			$up_filename=md5($ii*time()).".".$ext;
			$thumb = makeThumb($updir.'/'.$data['upfile1'], $updir.'/'.$up_filename,$cfg['thumb'.$ii.'_w'], $cfg['thumb'.$ii.'_h']);
			$width = $thumb['width'];
			$height = $thumb['height'];
			$_img_changed[$ii] = true;

			if($ii==3) $asql.=",";

			$asql.="`upfile".$ii."`='".$up_filename."' , `w".$ii."`='".$width."' , `h".$ii."`='".$height."'";
			if($fsThumb == "Y"){
				if(is_file($updir."/".$up_filename)){
					fsUploadFile($data[updir], $updir."/".$up_filename, $up_filename);
					fsDeleteFile($data[updir],$data['upfile'.$ii]);
					@unlink($updir."/".$up_filename);

					$_img_changed[$ii] = true;
				}
			}else{
				@unlink($updir."/".$data['upfile'.$ii]);
			}
			unset($GD);
		}
		if($fsThumb == "Y" && is_file($updir."/".$data[upfile1])) @unlink($updir."/".$data[upfile1]);

		$sql="update `".$tbl['product']."` set $asql where `no`='$pno'";
		$pdo->query($sql);
	}

	// 스마트스토어
	if(getSmartStoreState() == true && empty($admin['partner_no']) == true) {
        if ($_POST['n_store_check'] == 'Y' || $data['n_store_check'] == 'Y') {
            $pdo->query("update {$tbl['product']} set n_store_check=? where `no`=?", array(
                $_POST['n_store_check'],
                $pno
            ));

            $commerceAPI = new CommerceAPI();
            try {
                $commerceAPI->saveProduct($_POST); // 스마트스토어 테이블에 정보 저장
                $commerceAPI->products($pno); // 상품 등록 요청
            } catch (\Exception $e) {
                msg(php2java(str_replace("'", '\'', $e->getMessage())));
            }
        }
	}

	$layer1 = $layer2 = '';

	$cookie_time=$now+31536000;  //60*60*24*365
	$after_list_p=$_POST[after_list];
	if(!$after_list_p) {
		$after_list_p="";
	}
	setcookie("after_list", $after_list_p, $cookie_time, "/");

	if($after_list_p=="Y") {
		$rURL=getListURL('prdList');
		if(!$rURL) $rURL='./?body=product@product_list';
	}

	// 다음 쇼핑하우 엔진 업데이트
	if($cfg[show_use] == "Y" && $cfg[show_make_default] == "1"){
		$no_return=1;
		include $engine_dir."/_manage/openmarket/show.exe.php";
	}

	javac("parent.inputchanged = 0;");

	loadPlugin('product_register_finish');

	if($ea_type != 1 || $ori_no > 0) msg($ems,$rURL,"parent");

?>
<script type="text/javascript">
	var fr = parent.document.getElementsByName('optFrame')[0].contentWindow;
	var erpFrm = fr.document.getElementById('erp_baseFrm');
	if(erpFrm) {
		erpFrm.rurl.value = '<?=$rURL?>';
		erpFrm.submit();
	}
</script>