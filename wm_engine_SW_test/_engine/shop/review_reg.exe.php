<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  상품후기 처리
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	checkBasic();
	include_once $engine_dir."/_engine/include/shop.lib.php";
	include_once $engine_dir."/_engine/include/shop_detail.lib.php";
	include_once $engine_dir."/_manage/manage2.lib.php";
	include_once $engine_dir."/_engine/include/file.lib.php";

	$no = numberOnly($_POST['no']);
	$pno = addslashes(trim($_POST['pno']));
	$ano = numberOnly($_POST['ano']);
	$exec = $_POST['exec'];
	$pwd = $_POST['pwd'];

	if($no) {
		$data = $pdo->assoc("select * from $tbl[review] where no='$no'");
		if(!$data['no']) msg(__lang_common_error_nodata__);
		if($data['pno'] > 0) {
			$ano = $data['pno'];
		}
	}
	if(empty($pno) && $ano > 0) {
		$pno = $pdo->row("select hash from {$tbl['product']} where no='$ano'");
	}

	if($pno) {
		$prd = checkPrd($pno, 1);
		$prd['no'] = $prd['parent'];
	}

	if($exec == "view"){
		$now = $hid_now ? numberOnly($_REQUEST['hid_now']) : $now;

		include_once $engine_dir."/_engine/include/design.lib.php";
		include_once $engine_dir."/_manage/design/version_check.php";
		$_skin = getSkinCfg();

		$pdo->query("update `$tbl[review]` set `hit`=`hit`+1 where `no`='$data[no]'");

		// 모듈 변수 선언
		ob_start();
		$_file_name = "shop_product_review.php";
		include_once $engine_dir."/_manage/skin_module/_skin_module.php";
		include_once $engine_dir."/_engine/skin_module/_skin_module.php";
		ob_end_clean();

		$data = reviewOneData($data);
		$ocontent = $data['ocontent'];

		$this_cate = outPutCate("review", $data['cate']);
		$Rev_pt = selectArray(array("","★","★★","★★★","★★★★","★★★★★"), "rev_pt", "", "", $data['rev_pt']);

		$frm_geturl = $root_dir."/_skin/".$GLOBALS['design']['skin']."/CORE/shop_product_review_mod_frm.".$GLOBALS['_skin_ext']['p'];
		$str_a = array("{{\$상품번호}}", "{{\$비밀번호폼시작}}", "{{\$비밀번호폼끝}}", "{{\$수정폼시작}}", "{{\$수정폼끝}}", "{{\$평점}}", "{{\$제목}}", "{{\$내용}}", "{{\$카테고리}}");
		$_pform_start = "<form name=\"review_pfrm".$data['no']."\" target=\"hidden".$now."\" action=\"".$root_url."/main/exec.php\" method=\"post\" enctype=\"multipart/form-data\" onsubmit=\"return checkReviewpwdFrm(this);\">
<input type=\"hidden\" name=\"exec_file\" value=\"shop/review_edit.php\">
<input type=\"hidden\" name=\"no\" value=\"".$data['no']."\">
<input type=\"hidden\" name=\"exec\">";
		$_pform_end = "</form>";
		$_mform_start = "<form name=\"review_mfrm".$data['no']."\" target=\"hidden".$now."\" action=\"".$root_url."/main/exec.php\" method=\"post\" enctype=\"multipart/form-data\" onsubmit=\"return checkReviewModiFrm(this)\">
<input type=\"hidden\" name=\"exec_file\" value=\"shop/review_reg.exe.php\">
<input type=\"hidden\" name=\"no\" value=\"".$data['no']."\">";
		if($cfg['product_review_use_editor'] == "Y") {
			$_mform_start .= "<input type=\"hidden\" name=\"neko_id\" value=\"product_review_".$data['no']."\">";
		}
		$_mform_end = "</form>";
		$str_b = array($data['no'], $_pform_start, $_pform_end, $_mform_start, $_mform_end, $Rev_pt, $data['title'], $ocontent, $this_cate);

		// 제한제목목록
		if($member['level'] > 1 && $cfg['review_fsubject'] == 'Y') $_tmp_title = getForceSubject('review', $data['title']);
		if(!$_tmp_title) $_tmp_title = str_replace('{{$글제목}}', inputText($data['title']), getModuleContent('review_title_sel'));
		$str_a[] = '{{$제한제목목록}}';
		$str_b[] = $_tmp_title;

		$Frm_content = @file_get_contents($frm_geturl);
		foreach($str_a as $ri => $val) {
			$Frm_content = str_replace($str_a[$ri], $str_b[$ri], $Frm_content);
		}
		$data['content'] .= $Frm_content;

		// 첨부 이미지
		for($ii=1; $ii<=2; $ii++){
			if($data['upfile'.$ii]){
				$img = prdImg($ii, $data, $_para1, $_para2, $def_img);
				$data['img'.$ii] = $img[0];
				$data['imgstr'.$ii] = $img[1];
			}else{
				continue;
			}
			$data['img'.$ii] = ($data['img'.$ii]) ? "<img src=\"".$data['img'.$ii]."\" border=\"0\" id=\"review_img".$data['no']."_".$ii."\">" : "";
		}

		$_line2 = getModuleContent("product_review_comment_list");
		$_tmp2 = "";
		$review = $data;
		while($comment = reviewCommentList()){
			$_tmp2 .= lineValues("product_review_comment_list", $_line2, $comment, "common_module");
		}

		if($member['no']) {
			if($cfg['product_review_comment'] == "1" && $member['level'] != "1") {
				$data['comment_form_login'] .= getModuleContent("");
			} else {
				$data['comment_form_login'] = "<form method=\"post\" action=\"".$root_url."/main/exec.php\" target=\"hidden".$now."\" onSubmit=\"return checkRevCmt(this)\">
	<input type=\"hidden\" name=\"exec_file\" value=\"shop/review_comment.exe.php\">
	<input type=\"hidden\" name=\"no\" value=\"".$data['no']."\">
	";
				$data['comment_form_login'] .= getModuleContent("product_review_commment_login_form");
				$data['comment_form_login'] .= "</form>";
			}
		} else {
			if($cfg['product_review_comment'] == "1") {
				$data['comment_form_logout'] = getModuleContent("");
			} else {
				$data['comment_form_logout'] = getModuleContent("product_review_commment_logout_form");
			}
		}

		$_tmp2 = listContentSetting($_tmp2, $_line2);
		$data['comment_list'] = $_tmp2;

		$_line = getModuleContent("review_list");
		//$_line = preg_replace("/(.*)(id=\"revContent)(.*)(display:none[^>]*>)(.*)(<\/div>)(.*)/is", "$5", $_line[2]);
		$_tmp = lineValues("review_list", $_line, $data);
		$_tmp = contentReset($_tmp);
		$_tmp = preg_replace('/\{\{([^}]+)\}\}/', '', $_tmp);

		printAjaxHeader();

		exit($_tmp);
	}

	if($exec == "delete") {
		$auth = getDataAuth2($data, 1);
		if($auth != 1 && $cfg['product_review_del'] != "Y") {
			msg(__lang_common_error_noperm__);
		}
		if($auth == 3) {
			if($_POST['from_ajax'] == 'true' && empty($pwd) == true) { // 레이어 상세에서 삭제로 진입
				define('_LOAD_AJAX_PAGE_', true);

				$rno = $no;
				$mode = 'guest_delete';
				$_tmp_file_name = 'shop_product_review_pwd.php';
				include_once $engine_dir."/_engine/common/skin_index.php";
				return;
			}
			if(!$pwd) msg(__lang_member_input_pwd__);
			if(sql_password($pwd) != stripslashes($data['pwd'])) msg(__lang_member_error_wrongPwd__);
		}
		include_once $engine_dir."/_engine/include/milage.lib.php";

		if($cfg['use_trash_rev'] == 'Y') { // 휴지통
			$ret = insertTrashBox($data, array(
				'tbl' => $tbl['review'],
				'title' => $data['title'],
				'name' => $data['name'],
				'reg_date' => $data['reg_date'],
				'del_qry' => "delete from $tbl[review] where no='$no'",
			));
		} else {
			reviewMilage($data['no'], 1);
			$r = $pdo->query("delete from `$tbl[review]` where `no`='$no'");
			if($r){
				$pdo->query("delete from `$tbl[review_comment]` where `ref`='$no'");
				deletePrdImage($data, 1, 2);

				// 에디터 이미지 삭제
				$res = $pdo->iterator("select no, updir, filename from $tbl[neko] where neko_id='product_review_$no'");
                foreach ($res as $upfile) {
					deleteAttachFile($upfile['updir'], $upfile['filename']);
				}
				$pdo->query("delete from $tbl[neko] where neko_id='product_review_$no''");

				// 추천 데이터 삭제
				$pdo->query("delete from {$tbl['review_recommend']} where rno='$no'");
			}
		}
		setRevPt($prd['no']);

		msg(__lang_common_error_deleted__, 'reload', "parent");
	}

	// 수정폼 ajax 출력
	if($exec == 'write_form') {
		define('_LOAD_AJAX_PAGE_', true);

		$rno = numberOnly($_POST['rno']);
        $ono = addslashes($_POST['ono']);
		$data = $pdo->assoc("select * from {$tbl['review']} where no='$rno'");

		if($rno > 0 && getDataAuth2($data) == 3) { // 레이어 상세에서 비밀번호 입력 폼
			$mode = 'guest_edit';
			$_tmp_file_name = 'shop_product_review_pwd.php';
		} else {
			$_GET['single_module'] = $single_module = 'product_review_form';
			$_tmp_file_name = 'shop_product_review.php';
			$_single_module_code = ($member['no'] > 0 || (empty($_SESSION['review_auth']) == false && $_SESSION['review_auth'] == $rno)) ? '상품평회원등록폼' : '상품평비회원등록폼';
		}

		include_once $engine_dir."/_engine/common/skin_index.php";
		exit;
	}

	// 추천/비추천 처리
	if($exec == 'recommend') {
		header('Content-type:application/json;');

		if(isTable($tbl['review_recommend']) == false) {
			include_once $engine_dir.'/_config/tbl_schema.php';
			$pdo->query($tbl_schema['review_recommend']);

			addField($tbl['review'], 'recommend_y', 'int(5) not null');
			addField($tbl['review'], 'recommend_n', 'int(5) not null');
		}

		$rno = numberOnly($_POST['rno']);
		$val = ($_POST['val'] == 'true') ? 'Y' : 'N';

		if(!$member['no']) exit(json_encode(array('status'=>'error', 'message'=>__lang_common_error_memberOnly__)));
		if(!$rno)  exit(json_encode(array('status'=>'error', 'message'=>'추천할 상품평을 선택해주세요.')));

		$data = $pdo->assoc("select no, value from {$tbl['review_recommend']} where rno='$rno' and member_no='$member[no]'");
		if($val == $data['value']) {
			$pdo->query("delete from {$tbl['review_recommend']} where no='$data[no]'");
		} else {
			if($data['no']) {
				$r = $pdo->query("update {$tbl['review_recommend']} set value='$val', reg_date=now() where no='$data[no]'");
			} else {
				$r = $pdo->query("insert into {$tbl['review_recommend']} (member_no, rno, value, reg_date) values ('$member[no]', '$rno', '$val', now())");
			}
			if($r == false) {
                exit(json_encode(array('status'=>'error', 'message'=>$pdo->getError())));
            }
		}

		// summary
		$recommend = $pdo->assoc("select sum(if(value='Y', 1, 0)) as Y, sum(if(value='N', 1, 0)) as N from $tbl[review_recommend] where rno='$rno'");
		$pdo->query("update {$tbl['review']} set recommend_y='$recommend[Y]', recommend_n='$recommend[N]' where no='$rno'");

		exit(json_encode(array('status'=>'success')));
	}

	if($exec == 'getRa') {
		exit(reviewAuth('product_review_auth', $_POST['rno']));
	}

	$title = addslashes(strip_tags(trim($_POST['title'])));
	$name = addslashes(strip_tags(trim($_POST['name'])));
	$pwd = trim($_POST['pwd']);
	$rev_pt = numberOnly($_POST['rev_pt']);
	$cate = addslashes(trim($_POST['cate']));
	$content = addslashes(trim($_POST['content']));
	$content = strip_script($content);
	$neko_id = addslashes($_POST['neko_id']);
    $ono = addslashes($_POST['ono']);

	if ($cfg['boardFilter']) {
		if ($filterword = filterContent($cfg['boardFilter'], $content)) msg(__lang_common_error_bannedWords__);
	}

	// 한글 필수 체크
	if ($cfg['board_chk_Korean'] == "Y") if (!preg_match("/\p{Hangul}/u", $content)) msg(__lang_common_error_hangul__);

	// 차단 아이피 체크
	if ($cfg['boardDenyIP']) {
		$filters = explode(",", $cfg['boardDenyIP']);
		foreach ( $filters as $key => $val) {
			if ($_SERVER['REMOTE_ADDR'] == trim($val)) msg(__lang_common_error_bannedIP__);
		}
	}

	if($ra = reviewAuth('product_review_auth', $no)) {
        if ($ra == '3') {
            msg(__lang_common_error_noperm__);
        } else {
            msg(__lang_shop_error_secondReview__);
        }
    }
	checkBlank($title, __lang_common_input_title__);
	if($content == "") msg(__lang_common_input_content__);
	if(strlen($pwd) > 20) msg(sprintf(__lang_member_error_pwd4__, 20));

    if (
        $cfg['usecap_review'] == "Y"
        && $cfg['captcha_key']
        && (
            ($member['no'] && $cfg['usecap_member_review']=="Y")
            || (!$member['no'] && $cfg['usecap_nonmember_review']=="Y")
        )
        && $member['level']!=1
        && !$no
    ) {
        //비회원 또는 회원 글쓰기에 캡차 사용시
        if (!$_POST['g-recaptcha-response']) {
            //캡차 응답코드 누락시
            msg(__lang_board_capcha_cannot__);
        } elseif ($cfg['captcha_secret_key']) {
            //캡차 체크
            if (!recaptchaVerify($_POST['g-recaptcha-response'])) {
                //캡챠 인증 실패
                msg(__lang_board_capcha_cannotPass__);
            }
        }
    }

	// 수정
	if($no) {
		$data = get_info($tbl['review'], "no", $no);
		$auth = getDataAuth2($data, 1);
		if($auth == 3) {
			if(empty($_SESSION['review_auth']) == true || $_SESSION['review_auth'] != $data['no']) {
				if(sql_password($pwd) != $data['pwd']) msg(__lang_member_error_wrongPwd__);
			}
		}
	}
	else {
		if($member['no']) {
			if ($member['level'] == "1" && $_use['name_write'] == "Y") {
				$name = $_POST['name'];
			} else {
				$name = $member[name];
			}
			if($cfg['product_review_auth'] != '4' && $cfg['product_review_many'] == 1 && $prd['no']) {
				$tmp = $pdo->assoc("select * from `$tbl[review]` where `pno`='$prd[no]' and `member_no`='$member[no]'");
				if($tmp['no']) msg(__lang_shop_error_secondReview__);
			}
		}
		else {
			checkBlank($name, __lang_member_input_name__);
			checkBlank($pwd, __lang_member_input_pwd__);
		}
	}

	if($rev_pt < 1) $rev_pt = 5;
	if($rev_pt > $cfg['product_review_max']) {
		msg(__lang_shop_error_reviewPts__);
	}

	$stat = ($cfg['product_review_atype'] == 1) ? 2 : 1;
	$stat_msg = ($cfg['product_review_atype_detail'] == 'Y') ? 'N' : 'Y';

	$updir = $data['updir'];
	$asql = "";
	for($ii=1; $ii<=2; $ii++) {
		$chg_file = "";
		if($updir && ($_POST['delfile'.$ii] == "Y" || $_FILES['upfile'.$ii]['tmp_name'])) {
			deletePrdImage($data, $ii, $ii);
			$up_filename = $width = $height = "";
			$chg_file = 1;
		}
		if($_FILES['upfile'.$ii]['tmp_name']) {
			if(!$updir) {
				$updir = $dir['upload']."/".$dir['review']."/".date("Ym",$now)."/".date("d",$now);
				makeFullDir($updir);
				$asql.=" , `updir`='$updir'";
			}

			$up_filename = md5($ii+time());
			$up_info = uploadFile($_FILES["upfile".$ii],$up_filename,$updir, 'jpg|jpeg|gif|png');
			$up_filename = $_up_filename[$ii] = $up_info[0];
			$chg_file = 1;
		}

		if($chg_file) $asql .= " , `upfile".$ii."`='".$up_filename."'";
	}

	if($cfg['product_review_cate']){
		$asql .= ", `cate`='$cate'";
		$asql1 = ", `cate`";
		$asql2 = ", '$cate'";
	}

	// 두번 등록방지
	if(!$no) {
		$tmp = $pdo->assoc("select * from `$tbl[review]` order by `reg_date` desc limit 1");
		if($tmp['ip'] == $_SERVER['REMOTE_ADDR'] && $tmp['title'] == stripslashes($title) && $tmp['content'] == stripslashes($content)) {
			msg(__lang_shop_error_duplicatePost__, 'reload', 'parent');
		}
	}

	if($data['no']) {
		$sql = "update `$tbl[review]` set `rev_pt`='$rev_pt' , `title`='$title' , `content`='$content' $asql where `no`='$data[no]'";
		$pdo->query($sql);
		unset($_SESSION['review_auth']);
	}
	else {
		if($member['no']) {
            if ($ono) {
                $buy_date = $pdo->row("select date1 from {$tbl['order']} where ono='$ono'");
            } else {
                $_buy = $pdo->assoc("select a.`ono`,a.`member_no`,a.`date1`,b.`pno`,b.`ono` from {$tbl['order']} a,{$tbl['order_product']} b where a.`ono`=b.`ono` and b.`pno`='{$prd['no']}' and a.`member_no`='{$member['no']}' and b.stat < 10 order by a.`date1` desc");
                $buy_date = $_buy['date1'];
                $ono = $_buy['ono'];
            }
		}
		$pwd = sql_password($pwd);

		$sql = "INSERT INTO `$tbl[review]` (`pno`, `member_no`, `member_id`, `name`, `pwd`, `rev_pt`, `title`, `content`, `reg_date`, `buy_date`, `stat`, `updir`, `upfile1`, `upfile2`, `ono`, `ip` $asql1) VALUES ('$prd[no]', '$member[no]', '$member[member_id]', '$name', '$pwd', '$rev_pt', '$title', '$content', '$now', '$buy_date', '$stat', '$updir','$_up_filename[1]','$_up_filename[2]', '$ono', '{$_SERVER['REMOTE_ADDR']}' $asql2)";
		$r = $pdo->query($sql);
		if(!$r) msg(__lang_common_error_insert__);

		$no = $pdo->lastInsertId();

		if($stat == 1) {
			if($stat_msg == 'Y') $ems = __lang_shop_info_confirm__;
		}
		else {
			if($cfg['milage_review_auto'] == "Y" && $member['no']){
				include_once $engine_dir."/_engine/include/milage.lib.php";
				$rdata = $pdo->assoc("select `no`,`member_no`,`member_id` from `$tbl[review]` order by `no` desc limit 1");
				if($rdata['member_no'] == $member['no'] && $rdata['member_id'] == $member['member_id']){
					reviewMilage($rdata['no']);
				}
			}
		}

		if($member['level'] > 1) {
			if($cfg['product_review_scallback'] == 'Y') {
				include_once $engine_dir.'/_engine/sms/sms_module.php';
				$sms_replace['name'] = $name;
				$sms_replace['title'] = $title;
				$sms_replace['board_name'] = '상품후기';
				$sms_replace['member_id'] = ($member['member_id']) ? $member['member_id'] : __lang_common_info_grp10__;
				$board_type = 'review';
				SMS_send_case(17);
			}

			if($cfg['product_review_mcallback'] == 'Y') {
				$mail_case = 10;
				$member_name = $name;
				$board_name = '상품후기 ';
				$url = $root_url.'/shop/product_review.php?rno='.$no;
				$title = '<p style="text-align:center;">'.$name.'님의 상품후기가 등록되었습니다.</p><p style="text-align:center;">"'.$title.'"</p><p style="text-align:center;"><a href="'.$url.'" target="_blank">[확인하기]</a></p>';
				include_once $engine_dir.'/_engine/include/mail.lib.php';
				$r = sendMailContent($mail_case, $name, $to_mail);
			}
		}
	}
	if($neko_id) $pdo->query("update {$tbl['neko']} set neko_id='product_review_$no' where neko_id='$neko_id'");
	setRevPt($prd['no']);

    // 상품 후기 이미지 수 업데이트
    if ($scfg->comp('use_review_image_cnt', 'Y') == true) {
        reviewImageCount(
            $pdo->assoc("select no, upfile1, upfile2, content from {$tbl['review']} where no='$no'")
        );
    }

	if($ems) alert($ems);

    if ($_POST['startup'] && $pno) {
        msg('', '/shop/product_review.php?pno='.$pno, 'parent');
    }
	msg('', 'reload', 'parent');

?>