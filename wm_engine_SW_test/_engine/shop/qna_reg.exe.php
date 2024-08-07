<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  상품QNA 처리
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	checkBasic(1);
	include_once $engine_dir."/_engine/include/shop.lib.php";
	include_once $engine_dir."/_engine/include/shop_detail.lib.php";
	include_once $engine_dir."/_manage/manage2.lib.php";

	$no = numberOnly($_POST['no']);
	$pno = addslashes(trim($_POST['pno']));
	$exec = $_POST['exec'];
	$title = addslashes(strip_tags(trim($_POST['title'])));
	$name = addslashes(strip_tags(trim($_POST['name'])));
	$pwd = trim($_POST['pwd']);
	$cate = trim($_POST['cate']);
	$content = addslashes(trim($_POST['content']));
	$content = strip_script($content);
	$sms = addslashes(trim($_POST['sms']));
	$email = addslashes(trim($_POST['email']));
	$secret = ($_POST['secret'] == 'Y') ? 'Y' : 'N';
	$neko_id = addslashes(trim($_POST['neko_id']));

	if($pno) {
		$prd = checkPrd($pno, 2);
		$prd['no'] = $prd['parent'];
	}
	$data = $pdo->assoc("select * from $tbl[qna] where no='$no'");

	if($exec == "view"){
		$now = ($_REQUEST['hid_now']) ? numberOnly($_REQUEST['hid_now']) : $now;

		include_once $engine_dir."/_engine/include/design.lib.php";
		include_once $engine_dir."/_manage/design/version_check.php";
		$_skin['folder'] = $root_dir."/_skin/".$design['skin'];
		$_skin['url'] = $root_url."/_skin/".$design['skin'];
		$_skin = getSkinCfg();

		// 모듈 변수 선언
		ob_start();
		$_file_name="shop_product_qna.php";
		include_once $engine_dir."/_manage/skin_module/_skin_module.php";
		include_once $engine_dir."/_engine/skin_module/_skin_module.php";
		ob_end_clean();

		$view_auth = "";
		if($data['secret'] == "Y") {
			if(($data['member_no'] && $data['member_no'] == $member['no']) || ($_SESSION['view_qna_secret'] == $data['no'])) {
				$view_auth="Y";
			}
			if($member['level'] == 1) $view_auth = 'Y'; // 게시판관리자 권한으로 비밀글 열람
		}
		else {
			$view_auth = "Y";
		}
		if($view_auth == "Y") {
			$ocontent = stripslashes($data['content']);
			$data['content'] = nl2br(stripslashes($data['content']));
			$data['cell'] = stripslashes($data['cell']);
			if(!$data['cell'] && $member['cell']) $data['cell'] = $member['cell'];
			$cell = explode('-', $data['cell']);

			$pdo->query("update `$tbl[qna]` set `hit`=`hit`+1 where `no`='$data[no]'");

			// 첨부 파일
			for($ii=1; $ii <= 4; $ii++) {

				if(!$data['upfile'.$ii]) continue;
				$ext = strtolower(getExt($data['upfile'.$ii]));
				if(in_array($ext, array('png', 'jpg', 'jpeg', 'gif'))) {
					$img = prdImg($ii,$data,$_para1,$_para2,$def_img);
					$data['img'.$ii] = $img[0];
					$data['imgstr'.$ii] = $img[1];

					$data['img'.$ii] = ($data['img'.$ii]) ? "<img src=\"".$data['img'.$ii]."\" border=\"0\" id=\"qna_img".$data[no]."_".$ii."\">" : "";
				} else {
					$file_url = $root_url."/".$data['updir']."/".$data['upfile'.$ii];
					$data['img'.$ii] = "<p>".__lang_common_info_attachFile__." : <a href=\"{$file_url}\" target=\"_blank\">".$data['upfile'.$ii]."</a></p>";
				}
			}

			if($data['answer'] || $data['upfile3'] || $data['upfile4']) {
				$data['answer_str'] = $re_title.nl2br(stripslashes($data['answer']));
			} else {
                $data['answer_str'] = __lang_mypage_info_answerStandby__;
            }
			$this_cate = outPutCate("qna", $data['cate']);
			$checked = ($data['secret'] == "Y") ? " checked" : "";

			$frm_geturl = $root_dir."/_skin/".$design['skin']."/CORE/shop_product_qna_mod_frm.".$_skin_ext['p'];
			$str_a = array("{{\$상품번호}}", "{{\$비밀번호폼시작}}", "{{\$비밀번호폼끝}}", "{{\$수정폼시작}}", "{{\$수정폼끝}}", "{{\$카테고리}}", "{{\$제목}}", "{{\$비공개체크}}", "{{\$내용}}", '{{$작성자전화번호}}', '{{$작성자전화번호1}}', '{{$작성자전화번호2}}', '{{$작성자전화번호3}}');
			$_pform_start = "<form name=\"qna_pfrm".$data['no']."\" target=\"hidden".$now."\" action=\"".$root_url."/main/exec.php\" method=\"post\" enctype='multipart/form-data' onsubmit=\"return checkQnapwdFrm(this);\">
<input type=\"hidden\" name=\"exec_file\" value=\"shop/qna_edit.php\">
<input type=\"hidden\" name=\"no\" value=\"".$data['no']."\">
<input type=\"hidden\" name=\"exec\">";
			$_pform_end = "</form>";
			$_mform_start = "<form name=\"qna_mfrm".$data['no']."\" target=\"hidden".$now."\" action=\"".$root_url."/main/exec.php\" method=\"post\" enctype='multipart/form-data' onsubmit=\"return checkQnaModiFrm(this)\">
<input type=\"hidden\" name=\"exec_file\" value=\"shop/qna_reg.exe.php\">
<input type=\"hidden\" name=\"no\" value=\"".$data['no']."\">";
			if($cfg['product_qna_use_editor'] == "Y") {
				$_mform_start .= "<input type=\"hidden\" name=\"neko_id\" value=\"product_review_".$data['no']."\">";
			}
			$_mform_end = "</form>";
			$str_b = array($data['no'], $_pform_start, $_pform_end, $_mform_start, $_mform_end, $this_cate, $data['title'], $checked, $ocontent, $data['cell'], $cell[0], $cell[1], $cell[2]);

			// 제한제목목록
			if($member['level'] > 1 && $cfg['qna_fsubject'] == 'Y') $_tmp_title = getForceSubject('qna', $data['title']);
			if(!$_tmp_title) $_tmp_title = str_replace('{{$글제목}}', inputText($data['title']), getModuleContent('qna_title_sel'));
			$str_a[] = '{{$제한제목목록}}';
			$str_b[] = $_tmp_title;

			$Frm_content = @file_get_contents($frm_geturl);
			foreach($str_a as $ri => $val) {
				$Frm_content = str_replace($str_a[$ri], $str_b[$ri], $Frm_content);
			}
			$data['content'] .= $Frm_content;

		}
		else {
			$data['content'] = "";

			if($cfg['design_version'] == "V3"){
				$secret_file = $root_dir."/_skin/".$design['skin']."/CORE/shop_product_qna_secret.".$_skin_ext['p'];
				$_form_s = "{{\$폼시작}}";
				$_form_e = "{{\$폼끝}}";
			}else{
				$secret_file = $root_dir."/_template/shop/product_qna_secret.php";
				$_form_s = "{폼시작}";
				$_form_e = "{폼끝}";
			}

			if(is_file($secret_file)) {
				$tmp_file = file($secret_file);
				foreach($tmp_file as $key=>$val) {
					$data['content'] .= $val;
				}
				$data['content'] = str_replace($_form_s,"<form name=\"\" method=\"post\" action=\"".$root_url."/main/exec.php?exec_file=shop/qna_secret.exe.php\" target=\"hidden$now\" onSubmit=\"return checkQnaSecret(this)\"><input type=\"hidden\" name=\"no\" value=\"$data[no]\">",$data['content']);
				$data['content'] = str_replace($_form_e,"</form>",$data['content']);
			}
			else {
				$data['content'] = __lang_shop_info_secretQuestion__;
			}
		}

		$data['del_link'] = $data['edit_link'] = "<a href=\"#\" style=\"display:none;\">";
		$auth = getDataAuth2($data);
		if($auth && $view_auth=="Y" && $data['notice'] != "Y") {
			if($auth==1 || $cfg['product_qna_del'] == "Y" || ($cfg['product_qna_del'] == "A" && $data['answer_ok'] == 'N')) {
				if($auth == 3) $data['del_link'] = "<a href=\"javascript:conDelQna($data[no])\">";
				else $data['del_link'] = "<a href=\"javascript:delQna($data[no])\">";
			}
			if($auth==1 || $cfg['product_qna_edit'] == "Y" || ($cfg['product_qna_edit'] == "A" && $data['answer_ok'] == 'N')) {
				$data['edit_link'] = "<a href=\"javascript:editQna($data[no])\">";
			}
		}
		$_line = getModuleContent("qna_list");
		//$_line = preg_replace("/(.*)(id=\"revQna)(.*)(display:none[^>]*>)(.*)(<\/div>)(.*)/is", "$5", $_line[2]);
		$_tmp = lineValues("qna_list", $_line, $data);
		$_tmp = contentReset($_tmp);

		$_tmp = mb_convert_encoding($_tmp, 'utf-8', _BASE_CHARSET_);
		$_tmp = preg_replace('/\{{2}([^}]+)\}{2}/', '', $_tmp);

		exit($_tmp);
	}

	if(reviewAuth('product_qna_auth')) msg(__lang_common_error_noperm__);

	if($exec == 'delete') {
		$auth = getDataAuth2($data,1);
		if(($auth != 1 && $cfg['product_qna_del'] == "N") || ($auth != 1 && ($cfg['product_qna_del'] == "A" && $data['answer_ok'] == 'Y'))) {
			msg(__lang_common_error_noperm__);
		}
		if($auth == 3) {
			if(!$pwd) msg(__lang_member_input_pwd__);
			if(sql_password($pwd) != stripslashes($data['pwd'])) msg(__lang_member_error_wrongPwd__);
		}

		if($cfg['use_trash_qna'] == 'Y') { // 휴지통
			$ret = insertTrashBox($data, array(
				'tbl' => $tbl['qna'],
				'title' => $data['title'],
				'name' => $data['name'],
				'reg_date' => $data['reg_date'],
				'del_qry' => "delete from $tbl[qna] where no='$data[no]'",
			));
		} else { // 일반삭제
			$r = $pdo->query("delete from {$tbl['qna']} where `no`='$no'");
            if ($r == true) {
                deletePrdImage($data, 1, 2);

                // 에디터 이미지 삭제
                $res = $pdo->iterator("select no, updir, filename from {$tbl['neko']} where neko_id=? or neko_id=?", array(
                    'product_review_'.$no, 'product_review_'.$no.'_a'
                ));
                foreach ($res as $upfile) {
                    deleteAttachFile($upfile['updir'], $upfile['filename']);
                }
            }
		}

		if($data['pno'] > 0) {
			$tmp = $pdo->row("select count(*) from {$tbl['qna']} where pno='{$data['pno']}'");
			$pdo->query("update {$tbl['product']} set qna_cnt='$tmp' where `no`='{$data['pno']}'");
		}

		if($data['pno']) $msg_url = "reload";
		else $msg_url = "back";

		msg(__lang_common_error_deleted__, $msg_url, 'parent');
	}

	if($cfg['boardFilter']) {
		if($filterword = filterContent($cfg['boardFilter'], $content)) msg(__lang_common_error_bannedWords__);
	}

	// 한글 필수 체크
	if($cfg['board_chk_Korean'] == "Y") if(!preg_match("/\p{Hangul}/u", $content)) msg(__lang_common_error_hangul__);

	// 차단 아이피 체크
	if($cfg['boardDenyIP']) {
		$filters = explode(",", $cfg['boardDenyIP']);
		foreach ( $filters as $key => $val) { // ipv6 적용이후 문제가 생길수 있으므로 대역대 차단은 추후지원
			if($_SERVER['REMOTE_ADDR'] == trim($val)) msg(__lang_common_error_bannedIP__);
		}
	}

	checkBlank($title, __lang_common_input_title__);
	if($content == "") msg(__lang_common_input_content__);

    if (
        $cfg['usecap_qna'] == "Y"
        && $cfg['captcha_key']
        && (
            ($member['no'] && $cfg['usecap_member_qna']=="Y")
            || (!$member['no'] && $cfg['usecap_nonmember_qna']=="Y")
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

	if($no) {
		$auth = getDataAuth2($data, 1);
		if($auth) {
			if($auth > 1 && $cfg['product_qna_edit'] == "N") {
				msg(__lang_common_error_modifyperm__);
			}
		}
	}
	else {
		if($member['no']) {
			if($member['level'] == '1' && $_use['name_write'] == 'Y') {
				$name = $_POST['name'];
			} else {
				$name = $member['name'];
			}
		}
		else {
			checkBlank($name, __lang_member_input_name__);
			checkBlank($pwd, __lang_member_input_pwd__);
		}
	}

	// 파일업로드
	include $engine_dir."/_engine/include/file.lib.php";
	$updir=$data['updir'];
	$asql = $asql1 = $asql2 = '';
	for($ii=1; $ii<=2; $ii++) {
		$chg_file="";
		// 파일 삭제 또는 덮어 쓰기
		if($updir && ($_POST['delfile'.$ii]=="Y" || $_FILES['upfile'.$ii][tmp_name])) {
			deletePrdImage($data,$ii,$ii);
			$up_filename=$width=$height="";
			$chg_file=1;
		}
		if($_FILES['upfile'.$ii][tmp_name]) {
			// 파일업디렉토리
			if(!$updir) {
				$updir=$dir['upload']."/".$dir['qna']."/".date("Ym",$now)."/".date("d",$now);
				makeFullDir($updir);
				$asql.=" , `updir`='$updir'";
			}

			$up_filename=md5($ii+time()); // 새파일명
			$up_info=uploadFile($_FILES["upfile".$ii],$up_filename,$updir, ($cfg['product_qna_able_ext'] ? $cfg['product_qna_able_ext'] : 'jpg|jpeg|gif|png'));
			$up_filename=$_up_filename[$ii]=$up_info[0];
			$chg_file=1;
		}

		if($chg_file) $asql .= " , `upfile".$ii."`='".$up_filename."'";
	}

	// 두번 등록방지
	if(!$no) {
		$tmp = $pdo->assoc("select * from `$tbl[qna]` order by `reg_date` desc limit 1");
		if($tmp['ip'] == $_SERVER['REMOTE_ADDR'] && $tmp['title'] == stripslashes($title) && $tmp['content'] == stripslashes($content)) {
			msg(__lang_shop_error_duplicatePost__, 'reload', 'parent');
		}
	}

	$pwd = sql_password($pwd);

	if($cfg['product_qna_secret'] == "Y") $secret = "Y"; // 비공개
	elseif($cfg['product_qna_secret'] == "N") $secret = "N"; // 공개
	else checkBlank($secret, __lang_shop_select_secretLevel__); // 선택

	$prdno = ($spno) ? $pdo->row("select `no` from `$tbl[product]` where `hash`='$spno'") : $prd['no'];

	$cell = $_POST['cell'];
	if($cell) {
		addField($tbl['qna'], 'cell', 'varchar(20) not null');

		if(is_array($cell) == true) $cell = implode('-', $cell);
		$cell = trim(trim(addslashes($cell)), '-');

		$asql1 .= ", cell";
		$asql2 .= ", '$cell'";
		$asql .= ", cell='$cell'";
	}

	if($data['no']) {
		$sql = "update `$tbl[qna]` set `cate`='$cate' , `title`='$title' , `content`='$content', `secret`='$secret' {$asql} where `no`='$data[no]'";
		$pdo->query($sql);
	}
	else {
		$no = $pdo->row("select max(`no`) from `{$tbl['qna']}`");
		$no++;

		if($cate) {
			include_once $engine_dir."/_manage/manage2.lib.php";

			$asql1 .= ",`cate`";
			$asql2 .= ",'".$cate."'";
		}

		$sql = "INSERT INTO `$tbl[qna]` (`pno`, `member_no`, `member_id`, `name`, `pwd`, `title`, `content`, `reg_date`, `buy_date`, `secret`, `ip`, `updir`, `upfile1`, `upfile2`, `email`, `sms` $asql1) VALUES ('$prdno', '$member[no]', '$member[member_id]', '$name', '$pwd', '$title', '$content', '$now', '$buy_date', '$secret', '{$_SERVER['REMOTE_ADDR']}', '$updir', '$_up_filename[1]', '$_up_filename[2]', '$email', '$sms' $asql2)";

		$pdo->query($sql);
		$no = $pdo->lastInsertId();

		$pdo->query("update $tbl[neko] set neko_id='product_qna_$no' where neko_id='$neko_id'");

		if($member['level'] > 1) {
			include_once $engine_dir.'/_engine/sms/sms_module.php';
			$sms_replace['name'] = stripslashes($name);
			$sms_replace['title'] = stripslashes($title);
			$sms_replace['board_name'] = '상품문의';
			$sms_replace['member_id'] = ($member['member_id']) ? $member['member_id'] : __lang_common_info_grp10__;
			$sms_replace['prd_name'] = $pdo->row("select `name` from `$tbl[product]` where `no`='$prdno'");
			$board_type = 'qna';

			if($cfg['product_qna_scallback'] == 'Y') {
				SMS_send_case(17);
			}

			if($cfg['partner_sms_config'] == 1 || $cfg['partner_sms_config'] == 2){
				$partner = $pdo->assoc("select b.partner_sms_use, b.partner_sms, a.partner_no from wm_product a inner join wm_partner_shop b on a.partner_no=b.no where a.no=$prdno");
				if($partner['partner_no'] && ($cfg['partner_sms_config'] == 1 || ($cfg['partner_sms_config'] == 2 && $partner['partner_sms_use'] == 'Y'))){
					SMS_send_case(17,$partner['partner_sms'],$partner['partner_no']);
				}
			}

			if($cfg['product_qna_mcallback'] == 'Y') {
				$mail_case = 10;
				$member_name = stripslashes($name);
				$board_name = '상품문의 ';
				$url = $root_url.'/shop/product_qna.php?rno='.$no;
				$title = '<p style="text-align:center;">'.$name.'님의 상품문의가 등록되었습니다.</p><p style="text-align:center;">"'.$title.'"</p><p style="text-align:center;"><a href="'.$url.'" target="_blank">[확인하기]</a></p>';
				include_once $engine_dir.'/_engine/include/mail.lib.php';
				$r = sendMailContent($mail_case, $name, $to_mail);
			}
		}
	}

	if($prd['no']) {
		$tmp = $pdo->row("select count(*) from `$tbl[qna]` where `pno`='$prd[no]'");
		$pdo->query("update `$tbl[product]` set `qna_cnt`='$tmp' where `no`='$prd[no]'");
	}

	javac("
		if(parent.document.querySelector('#detail_qna_ajax_list')) {
            parent.removeFLoading();
			parent.reloadProductBoard('qna');
		} else {
			parent.location.reload();
		}
	");

?>