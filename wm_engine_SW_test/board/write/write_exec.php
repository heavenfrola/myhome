<?PHP

	if(!defined("_lib_inc")) exit();

	checkBasic();

	if($exec!="edit") {
		checkWriteLimit("write",1);
	}

    if (
        $db
        && $cfg['usecap_'.$db] == "Y"
        && $cfg['captcha_key']
        && (
            ($member['no'] && $cfg['usecap_member_'.$db]=="Y")
            || (!$member['no'] && $cfg['usecap_nonmember_'.$db]=="Y")
        )
        && $member['level']!=1
        && !$_POST['no']
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

	foreach($_POST as $key => $val) {
		if(!is_array($val)) {
			if($member['level'] > $mari_set['mng_level'] || ($member['level'] <= $mari_set['mng_level'] && $key != 'title')) {
				${$key} = del_html($_POST[$key]);
			}
			${$key} = addslashes($val);
		}
	}

	if($cfg[boardFilter]) {
		if($filterword = filterContent($cfg[boardFilter], $content)) msg(__lang_common_error_bannedWords__);
	}

	$no = numberOnly($_POST['no']);
	$content = addslashes(strip_script($content));
	$pno = preg_replace('/[^,0-9]/', '', $pno);

	// 한글 필수 체크
	if($cfg[board_chk_Korean] == "Y") if(!preg_match("/\p{Hangul}/u", $content)) msg(__lang_common_error_hangul__);

	// 차단 아이피 체크
	if($cfg[boardDenyIP]) {
		$filters = explode(",", $cfg[boardDenyIP]);
		foreach($filters as $key => $val) {
			if($_SERVER[REMOTE_ADDR] == trim($val)) msg(__lang_common_error_bannedIP__);
		}
	}

	$secret = ($_POST['secret'] == 'Y') ? 'Y' : 'N';
	if($config['auto_secret'] == 'Y' && $member['level'] > 1) {
		$secret = 'Y';
	}
	if($notice== 'Y') $secret = 'N';

	// 두번 등록방지
	if(!$no){
		$tmp = $pdo->assoc("select * from {$mari_set['mari_board']} order by `reg_date` desc limit 1");
		if($tmp['ip'] == $_SERVER['REMOTE_ADDR'] && $tmp['title'] == stripslashes($title) && $tmp['content'] == stripslashes($content)) {
			msg(__lang_shop_error_duplicatePost__, 'reload', 'parent');
		}
	}

	$pwd=sql_password($pwd);

	if($exec=="reply" || $exec=="edit") {
		if(!$no) msg(__lang_common_error_required__);

		$data=$pdo->assoc("select * from {$mari_set['mari_board']} where `no`='$no' and `db`='$db'");
		if(!$data[no]) msg(__lang_common_error_nodata__);

		if($exec=="edit") {
			$auth=getDataAuth($data,1);
		}
	}

	if($exec!="edit") {
		$no=$pdo->row("select max(no) from {$mari_set['mari_board']}");
		$no++;
	}

	$add_que="";

	// 필수값 체크
	$req = array('title', 'content');
	$req_msg = array(__lang_common_input_title__, __lang_common_input_content__);

	if($member[level]==10 || $auth==3) {
		array_push($req,"name","pwd");
		array_push($req_msg, __lang_member_input_name__, __lang_member_input_pwd__);
		$add_que.=", `name`='$name'";
	}
	elseif($auth==1) {
		array_push($req,"name");
		array_push($req_msg, __lang_member_input_name__);
		$add_que.=", `name`='$name'";
	}
	else {
		if($member[level]=='1' && $_use[name_write]=='Y') {
			$name=$name;
			$now = strtotime($_POST[reg_date]);
			if(!$now) $now=time();
		} else {
			$name=$member[name];
		}
	}

	if($config[use_cate]=="Y") {
		if($exec=="reply") {
			$cate=$data[cate];
		}
		array_push($req,"cate");
		array_push($req_msg, __lang_board_select_cate__);
	}

	for($ii=0; $ii<count($req); $ii++) {
		if(${$req[$ii]}=="") {
			msg($req_msg[$ii]);
		}
	}

	if((!preg_match("@http://@",$homepage))&&$homepage){
		$homepage="http://".$homepage;
	}

	if(!$secret) $secret="N";
	if(!$notice) $notice="N";
	if(!$html) $html="1";

	if($member[level]>$mari_set[mng_level]) {
		if($notice=="Y") {
			msg(__lang_board_error_cannotNotice__);
		}

		//관리자가 외 html 사용일 경우 부분 허용
		if($html_mode>0) {
			$content=str_replace("<","&lt;",$content);
			$tag=explode(",",$config[tag]);
			for($i=0;$i<count($tag);$i++) {
				if(!isblank($tag[$i])) {
					$content=preg_replace("@&lt;".$tag[$i]." @","<".$tag[$i]." ",$content);
					$content=preg_replace("@&lt;".$tag[$i].">@","<".$tag[$i].">",$content);
					$content=preg_replace("@&lt;/".$tag[$i]."@","</".$tag[$i],$content);
				}
			}
		}
	}

	if($html==1) {
		$content=del_html($content);
	}

	if($notice=="Y" && $member[level]>1) msg(__lang_board_error_cannotNotice__);
	if($member[level]==1 && $hit) {
		$add_que.=", `hit`='$hit'";
	}


	// 파일 업로드
	$uf="upfile";
	$up_dir=$data[up_dir];

	if(!$cfg['mari_board_m_content']) {
		if($_FILES['upfile3']['size'] > 0 || $_FILES['upfile4']['size'] > 0) {
			addField($_tbl, 'upfile3', 'varchar(50) after upfile2');
			addField($_tbl, 'upfile4', 'varchar(50) after upfile3');
			addField($_tbl, 'ori_upfile3', 'varchar(200) after ori_upfile2');
			addField($_tbl, 'ori_upfile4', 'varchar(200) after ori_upfile3');
			addField($_tbl, 'w3', 'int(5) after h2');
			addField($_tbl, 'h3', 'int(5) after w3');
			addField($_tbl, 'w4', 'int(5) after h3');
			addField($_tbl, 'h4', 'int(5) after w4');
		}
	}

	for($ii=0; $ii<5; $ii++) {
		$kk=$ii+1;
		if($_FILES[$uf]["tmp_name"][$ii]) {

			if($member[level]>$config[auth_upload]) {
				msg(__lang_board_error_cannotUpload__);
			}

			if(!is_uploaded_file($_FILES[$uf]["tmp_name"][$ii])) msg(__lang_board_error_);

			$ext=getExt($_FILES[$uf]["name"][$ii]);
			if(!$ext) msg(__lang_board_error_noExt__);
			if(preg_match("/php|php3|html|htm|cgi|wisa/i", $ext)) msg(__lang_board_error_bannedExt__);
			if(!preg_match("/$config[upfile_ext]/i",$ext)) msg(__lang_board_error_selectedExt__);

			// 사이즈 체크
			if($_FILES[$uf]["size"][$ii]==0) msg(__lang_board_error_emptyFile__);
			if($_FILES[$uf]["size"][$ii]>$config[upfile_size]*1024) msg(sprintf(__lang_board_error_filesize__, $config['upfile_size'].' KB'));

			$up_filename[$ii] = $db."_".$ii."_".$now.".".$ext;
			$ori_up_filename[$ii]=$_FILES[$uf]["name"][$ii];

			// 업로드 디렉토리 생성
			if(!$up_dir) {
				$up_dir = '_data/'.$db.'/'.date('Ym/d/');
				makeFullDir('board/'.$up_dir);
				$add_que .= ", `up_dir`='$up_dir'";
			}
			$up_abs_dir=$root_dir."/board/".$up_dir; // 절대경로

			if($exec=="edit") { // 수정
				if($data["upfile".$kk]) deleteAttachFile("/board/".$up_dir, $data["upfile".$kk]);
				$add_que.=", `upfile$kk`='$up_filename[$ii]', `ori_upfile$kk`='$ori_up_filename[$ii]'";
			}

			$_file[tmp_name] = $_FILES[$uf][tmp_name][$ii];
			$_file[name] = $_FILES[$uf][name][$ii];
			$_file[size] = $_FILES[$uf][size][$ii];

			list($w, $h) = getimagesize($_file['tmp_name']);
			if($w > 0 && $h > 0) {
				$upno = $ii+1;
				$add_que .= ", `w$upno`='$w', `h$upno`='$h'";
				$fsql1 .= ", `w$upno`, `h$upno`";
				$fsql2 .= ", '$w', '$h'";
			}
			$upinfo = uploadFile($_file, preg_replace("/\.$ext/i", "", $up_filename[$ii]),"/board/".$up_dir);
		}
		if($_POST["delfile".$kk]=="Y" && !$up_filename[$ii]) {
			deleteAttachFile("board/".$up_dir, $data["upfile".$kk]);
			 $add_que.=", `upfile$kk`='', `ori_upfile$kk`=''";
		}
	}

	for($i = 1; $i <= $cfg['board_add_temp']; $i++) {
		if(isset($_POST['temp'.$i]) == true) {
			$fsql3 .= ", `temp".$i."`";
			$fsql4 .= ", '".addslashes(trim(${'temp'.$i}))."'";
			$fsql5 .=", `temp".$i."`='".addslashes(trim(${'temp'.$i}))."'";
		}
	}

	if($exec=="edit") {
        $content_field = 'content';
        if($_SESSION['browser_type'] == 'mobile' && $data['use_m_content'] == 'Y' && $data['m_content']) {
            $data['content'] = $data['m_content'];
            $content_field = 'm_content';
        }
		$pdo->query("update {$mari_set['mari_board']} set `homepage`='$homepage' , `email`='$email' , `phone`='$phone' , `title`='$title' , `$content_field`='$content' , `secret`='$secret' , `html`='$html' , `notice`='$notice', `cate`='$cate', `link1`='$link1', `link2`='$link2', `pno`='$pno' $add_que $zata_q $fsql5 where `no`='$no'");
	}
	else {
		$auto_increment = isAutoIncrement($mari_set['mari_board']);
		if($auto_increment > 0) {
			$no = $auto_increment;
		}

		if($exec=="reply") {
			$ref=$data[ref];
			$level=$data[level];
			$step=$data[step];

			$pdo->query("update {$mari_set['mari_board']} set `step`=`step`+1 where `ref`=$ref and `step`> $step");

			$level=$level+1;
			$step=$step+1;
		}
		else {
			$ref=$no;
			$step=0;
			$level=0;
		}

		if(is_array($up_filename)) {
			foreach($up_filename as $key=>$val) {
				$nkey=$key+1;
				if($key>1) {
					addField($mari_set[mari_board],"upfile".$nkey,"VARCHAR(50) NOT NULL");
					addField($mari_set[mari_board],"ori_upfile".$nkey,"VARCHAR(200) NOT NULL");
				}
				$fsql1.=", `upfile".$nkey."`, `ori_upfile".$nkey."`";
				$fsql2.=", '".$up_filename[$key]."', '".$ori_up_filename[$key]."'";
			}
		}

		$sql="INSERT INTO {$mari_set['mari_board']} (`no`, `db` , `cate` , `ref` , `step` , `level` , `member_id` , `name` , `homepage` , `member_no` , `member_level` , `email` , `phone` , `ip` , `title` , `content` , `pwd` , `reg_date` , `hit` , `total_comment` , `secret` , `html` , `notice` , `up_dir` , `link1` , `link2` , `link_hit1`, `link_hit2`, `pno` $fsql1 $fsql3) ";
		$sql.="VALUES ('$no', '$db' , '$cate' ,'$ref' , '$step' , '$level' , '$member[member_id]' , '$name' , '$homepage' , '$member[no]' , '$member[level]' , '$email' , '$phone' , '{$_SERVER['REMOTE_ADDR']}' , '$title' , '$content' , '$pwd' , '$now' , '0' , '0' , '$secret' , '$html' , '$notice' , '$up_dir' , '$link1', '$link2', '0', '0', '$pno' $fsql2 $fsql4 )";

		$r = $pdo->query($sql);
		if($r){
			if($level > 0){
				if(!$data[rep_no]) $data[rep_no]=$data[no]."|";
				$rep_no=$data[rep_no].$no."|";
			}else{
				$rep_no=$no."|";
			}
			$pdo->query("update {$mari_set['mari_board']} set `rep_no`='$rep_no' where `no`='$no'");

			if($member['level'] > 1) {
				if($config['use_scallback'] == 'Y') {
					include_once $engine_dir.'/_engine/sms/sms_module.php';
					$sms_replace['name'] = $name;
					$sms_replace['title'] = $title;
					$sms_replace['board_name'] =  stripslashes($config['title']);
					$sms_replace['member_id'] = ($member['member_id']) ? $member['member_id'] : '비회원';
					$board_type = 'board';
					SMS_send_case(17);
				}

				if($config['use_mcallback'] == 'Y') {
					$mail_case = 10;
					$member_name = $name;
					$board_name = stripslashes($config['title']).' ';
					$url = $root_url.'/board/?db='.$db.'&no='.$no.'&mari_mode=view@view';
					$title = '<p style="text-align:center;">'.$name.'님의 게시물이 등록되었습니다.</p><p style="text-align:center;">"'.$title.'"</p><p style="text-align:center;"><a href="'.$url.'" target="_blank">[확인하기]</a></p>';
					include_once $engine_dir.'/_engine/include/mail.lib.php';
					$r = sendMailContent($mail_case, $name, $to_mail);
				}
			}
		}

		checkWriteInput("write");
		putBBSPoint("write");
	}

	$real_neko_id = $db."_".$no;
	if(!$_POST['no']) $pdo->query("update {$tbl['neko']} set `neko_id` = '$real_neko_id' where `neko_id` = '$neko_id'");
	neko_lock($real_neko_id);

	$listURL = $_SESSION['bbs_rURL'];
	if(!$listURL) $listURL=$PHP_SELF.$db_que2."&cate=$cate";
	if($config[use_view]=="N") {
		$rURL=$listURL;
	}
	else {
		$rURL=$PHP_SELF."?mari_mode=view@view&no=$no&cate=$cate".$db_que1;
	}

	if($config['load_url'] == '2') {
		$rURL = $listURL;
	} else if($config['load_url'] == '3') {
		$rURL = $config['loading_url'];
	}
	msg("",$rURL,"parent");

?>