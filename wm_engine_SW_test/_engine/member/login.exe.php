<?PHP
	/* +----------------------------------------------------------------------------------------------+
	' |  로그인처리
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/member.lib.php";

	if($_GET['exec'] == 'getEmailSuffix') {
		header('Content-type:application/json; charset='._BASE_CHARSET_);
		exit(json_encode($_email_suffix));
	}


	$sns_type				= trim($_POST["sns_type"]);
	$sns_cid				= trim($_POST["sns_cid"]);
	$login_type_fail		= "";
	$sns['result']  = false;
	$sns['message'] = '';
	$rURL = $_POST['rURL'];
	$exec = $_GET['exec'];
	if(strpos($rURL, $root_url) !== 0) {
		$rURL = $root_url;
	}
    $err = 0;

    if ($mode == 'auto_login') { // 자동 로그인
        $data = $pdo->assoc("
            select b.* from {$tbl['member_auto_login']} a inner join {$tbl['member']} b on a.member_no=b.no
            where a.cookie_id='{$_COOKIE['smartwing_al']}' and b.withdraw not in ('Y', 'D2')
        ");
        if ($data == false) {
            unset($_COOKIE['smartwing_al']);
            return;
        }
    } else { // 일반 로그인

	$member_id = strtolower(trim($_POST['member_id']));
	if (preg_match('/[A-Z]/', $member_id)) msg(__lang_member_info_memberid7__);
	if($member_id && preg_match('/[^0-9a-z_\-@\.]/', $member_id) == true) {
		msg(__lang_member_info_memberid1__);
	}
	$pwd = trim($_POST['pwd']);

	if($exec == 'removeDeleted') {
		printAjaxHeader();

		$mno = numberOnly($_GET['mno']);
		$amember = $pdo->assoc("select no, member_id, pwd from {$tbl['member']} where no='$mno'");
		$member_id = $amember['member_id'];
		$pwd = $_POST['pwd'] = $_GET['hash'];

		if(!$member_id) exit(__lang_member_error_idNotFound__);
		if($pwd != $amember['pwd']) exit(__lang_member_error_wrongPwd__);

		restoreDeleted($mno);
	}


	// SNS로그인
	if($sns_type) {
		if (array_key_exists($sns_type , $_sns_type)) {
			if($sns_cid && $sns_cid == $_SESSION["sns_login"]["cid"]){
				// SNS 가입 여부 체크
				$sql = "SELECT B.member_id FROM {$tbl['sns_join']} AS A INNER JOIN  $tbl[member] AS B ON (A.member_no=B.no)  WHERE A.cid='$sns_cid' and A.type = '$_sns_type[$sns_type]'";
				$member_id = $pdo->row($sql);
				if(!$member_id) {
					$login_type_fail = __lang_member_error_snsValid__;
				}
			} else {
				$login_type_fail = __lang_member_error_snsCID__;
			}
		} else {
			$login_type_fail = __lang_member_error_snsAllow__;
		}

		if($login_type_fail) {
			$sns['message'] = $login_type_fail;
			echo json_encode($sns);
			exit;
		}

		$data=$pdo->assoc("select * from {$tbl['member']} where `member_id`='$member_id'");

	} else {
		// SNS 로그인이 아니면 POST값 체크
		checkBlank($member_id, __lang_member_input_memberid__);
		checkBlank($pwd, __lang_member_input_pwd__);

		$data=$pdo->assoc("select * from {$tbl['member']} where `member_id`='$member_id'");

		// SNS 로그인일 경우는 이전 페이지에서 미리 체크(apijoin.php)
		if($data['reg_email'] == 'W') {
			msg(__lang_member_error_notValidMember__);
		}
		if($data['pwd'] == 'TEMP') {
			header('Content-type: text/html; charset='._BASE_CHARSET_);
			exit("
			<script type='text/javascript'>
			if (confirm('{$data[member_id]}님은 사이트 개편 이전 가입 고객으로 비밀번호를 재발급받으셔야합니다.\\n고객님의 기본 정보를 입력하시면 핸드폰으로 새 비밀번호가 전송됩니다.\\n\\n지금 재발급 페이지로 이동하시겠습니까?')) {
				parent.newSMSpwd();
			}
			</script>
			");
		}
	}
	$data = getMemberAttr($data);

	// 에러코드
	$sql_password = ($exec == 'removeDeleted') ? $pwd : sql_password($pwd);
	$err=0;

	if(strlen($data['pwd']) == 64 && strlen($sql_password) == 16) { // 앰버샵 구패스워드/신패스워드 동시 사용
		$sql_password = hash('sha256', $pwd);
	}

	if($sns_type) {
		if(!$data['no']) $err++; // 1
		elseif($data['withdraw']=="Y") $err = $err+2 ; // 3
	} else {
		if(!$data['no']) $err++; // 1
		elseif($data['pwd']!= $sql_password && $cfg['master_pass']!=$sql_password) $err++; // 2
		elseif($data['withdraw']=="Y") $err++; // 3
	}

	if($cfg['use_biz_member'] == 'Y') {
		$biz = get_info($tbl['biz_member'], 'ref', $data['no']);
		if($biz['ref'] > 0) {
			if($biz['auth'] != 'Y' && $err == 0) {
				$ems = __lang_member_error_notValidBizMember__;
				$err++;
			} else {
				$ems = __lang_member_error_idPwd__;
			}
		}
	}
	if($data['14_limit'] =='Y' && $data['14_limit_agree'] == "N" && $err < 1) {
		$err++;
		$ems = __lang_member_14join_limitagree__;
	}

    } // 일반 로그인

	// 에러코드 저장 후 에러시 에러 알림
	$pdo->query("INSERT INTO {$tbl['member_log']} ( `member_id` , `login_result` , `log_date` , `ip` ) VALUES  ('$member_id','$err','$now','".$_SERVER['REMOTE_ADDR']."')");
	if($err>0) {
		if($sns_type) {
			$sns['message'] = $ems ? $ems : __lang_member_error_snsLoginFail__;
			echo json_encode($sns);
			exit;
		} else {
			msg($ems, $root_url.'/member/login.php?err=1&rURL='.urlencode($rURL),"parent");
		}
	}

	if($data['withdraw'] == 'D2' && $exec != 'removeDeleted') {
		javac("parent.removeMemberDeleted('$data[no]', '$data[pwd]');");
		exit;
	}

	$pdo->query("update {$tbl['member']} set `last_con`='$now', `total_con`=`total_con`+1, withdraw='N' where `no`='$data[no]'"); // 최근 접속
	if($cfg['cart_member_delete'] == "Y"){ // 회원일경우 장바구니 유지
		$cart_sql = $pdo->iterator("select `pno`,`option`,`option_idx`,`no` from {$tbl['cart']} where `member_no`='$data[no]'");
        foreach ($cart_sql as $cartck) {
			$prd = $pdo->assoc("select `no` from {$tbl['product']} where `no`='$cartck[pno]' and `stat`=2 and (`ea_type`=1 or `ea_type`=2 or (`ea_type`=3 and `ea` > 0))");
			if(!$prd['no']) $pdo->query("delete from {$tbl['cart']} where no='{$cartck['no']}'");
		}
	} else {
		$pdo->query("delete from {$tbl['cart']} where member_no='$data[no]'");
	}

	if($_SESSION['guest_no']) {
        $res = $pdo->iterator("select * from {$tbl['cart']} where guest_no=?", array($_SESSION['guest_no']));
        foreach ($res as $cart) {
            if ($cfg['use_set_product'] == 'Y') { // 세트상품 구분
                $csql = " and set_idx='{$cart['set_idx']}'";
            }
            $old = $pdo->row("
                select no from {$tbl['cart']}
                where pno=? and `option`=? and member_no=? $csql
            ", array(
                $cart['pno'], $cart['option'], $data['no']
            ));
            $pdo->query("update {$tbl['cart']} set member_no=?, guest_no='' where no=?", array(
                $data['no'], $cart['no']
            ));
            if ($old > 0) {
                $pdo->query("delete from {$tbl['cart']} where no=?", array($old));
            }
        }
		$_SESSION['guest_no']="";
	}

	// 로그인 쿠키
	$cookie_time = $now+31536000;
    if ($_POST['auto_login'] == 'Y') {
        $cookie_id = md5($data['no']).md5(session_id());
        setCookie('smartwing_al', $cookie_id, $cookie_time, '/');

        if (isTable($tbl['member_auto_login']) == false) {
            include_once __ENGINE_DIR__.'/_config/tbl_schema.php';
            $pdo->query($tbl_schema['member_auto_login']);
        }

        $pdo->query("
            insert into {$tbl['member_auto_login']}
                (cookie_id, member_no, ip, reg_date)
                values ('$cookie_id', '{$data['no']}', '{$_SERVER['REMOTE_ADDR']}', now())
        ");
    }

	// 세션 생성
	$_SESSION['member_no']=$data['no'];
	$_SESSION['m_member_id']=$data['member_id'];
	$_SESSION['sns_type']=$sns_type;

	if(!$rURL) $rURL=$root_url;
	if($data['attr_homepage']) $rURL = $data['attr_homepage'];

	$login_msg = '';
	if($data['attr_login_msg']) { // 특별회원그룹속성
		if($login_msg) $login_msg .= "\\n";
		$login_msg .= php2java($data['attr_login_msg']);
	}

	// 로그인시 그룹별 메시지 출력
	$group_msg = addslashes($pdo->row("select `group_msg` from {$tbl['member_group']} where `no`='{$data['level']}'"));
	$group_msg = str_replace("\r\n", "<<WISA>>", $group_msg);
	$group_msg = str_replace("<<WISA>>", "\\n", $group_msg);
	if(!$_POST['sns_type']) {
		if($data['member_id'] && $group_msg) alert($group_msg);
	}

	loadPlugin('member_login_finish');

	// 출석체크
	$member_login = true;
	include $engine_dir.'/_engine/mypage/attend_new.exe.php';

	if(is_object($ds)) {
		$ds->setChangedMember($data['member_id'], $data['no']);
	}

	// 적립금 정리
	if($cfg['milage_expire']) {
		include_once $engine_dir.'/_engine/include/milage.lib.php';
		expireMilage($data['member_id']);
	}

	// 로그인 쿠폰 지급
	putLoginCoupon($data);

	// SNS 로그인(AJAX 처리)
	if($sns_type) {
		$sns['result'] = true;
        //특별회원그룹 주소가 있다면 우선 적용
        $sns['rURL'] = ($data['attr_homepage']) ? $data['attr_homepage'] : $_SESSION['sns_login']['rURL'];
        if (!$sns['rURL']) $sns['rURL'] = $rURL;
        if (!$sns['rURL']) $sns['rURL'] = $root_url;
		$sns['group_msg'] = $group_msg;
        $sns['login_msg'] = $data['attr_login_msg'];
		if(isSmartApp() == 'IOS') {
            $_js_data = urlencode(stripslashes(json_encode(array('func'=>'saveMinfo','param1'=>$member_id,'param2'=>'','param3'=>$sns['rURL']))));
            $sns['app_process'] = "window.location.href='wisamagic://event?json=".$_js_data."'";
            $sns['app_process_device'] = 'IOS';
	    } else if(isSmartApp() == 'Android') {
	        $sns['app_process'] = "try{window.wisa.saveMinfo('".$member_id."','".$_POST['pwd']."');}catch(e){}";
	        $sns['app_process_device'] = 'AOS';
			$sns['app_rurl'] = $sns['rURL'];
	    }

		echo json_encode($sns);
		exit;
    } else if ($mode == 'auto_login') {
        return;
	} else {
		// 앱에서 접근여부 판단 후 아이디/패스워드 전달
		if(isSmartApp() == 'IOS') {
			$_js_data = urlencode(stripslashes(json_encode(array('func'=>'saveMinfo','param1'=>$member_id,'param2'=>$_POST['pwd'],'param3'=>$rURL))));
			if($_COOKIE['wisamall_access_device'] == 'APP') echo "<script>window.location.href='wisamagic://event?json=".$_js_data."';</script>";
		} else if(isSmartApp() == 'Android') {
			echo "<script>try{window.wisa.saveMinfo('".$member_id."','".$_POST['pwd']."');}catch(e){}</script>";
		}

		if($cfg['use_pwd_change'] == 'Y') {
			if($data['change_pwd_date'] == 0 ) {
				$pdo->query("update {$tbl['member']} set `change_pwd_date`='{$data['reg_date']}' where `no` = '{$data['no']}' and `member_id`='{$data['member_id']}'");
				$data['change_pwd_date'] = $data['reg_date'];
			}
			$d_day = ($cfg['change_pwd_m'] == 'd') ? $cfg['change_pwd'] * 86400 : $cfg['change_pwd'] * 2592000;
			$re_d_day = ($cfg['change_pwd_m_re'] == 'd') ? $cfg['change_pwd_re'] * 86400 : $cfg['change_pwd_re'] * 2592000;
			if(($data['change_pwd_next'] == 'N' && $data['change_pwd_date'] < ($now - $d_day)) || $data['change_pwd_next'] == 'Y' && $data['change_pwd_date'] < ($now - $re_d_day)) {
				$key=mt_rand(123456,987654);
                $key_enc = aes128_encode($key, 'pwd_log');
				$pdo->query("update {$tbl['pwd_log']} set `stat`=2 where `member_id`='$data[member_id]'");
				$log_sql="insert into {$tbl['pwd_log']} (`stat`, `member_no`, `member_id`, `member_name`, `email`, `key`, `ip`, `reg_date`) values('1', '$data[no]', '$data[member_id]', '$data[name]', '$data[email]', '$key_enc', '$_SERVER[REMOTE_ADDR]', '$now')";
				$pdo->query($log_sql);
				msg(__lang_member_modify_pwd__, $root_url.'/main/exec.php?exec_file=member/modify_pwd.php', 'parent');
			}
		}

		if (!$target) $target = "parent";
		msg($login_msg,$rURL,$target);
	}

?>