<?PHP

    include_once $engine_dir.'/_engine/include/file.lib.php';
    include_once __ENGINE_DIR__.'/_engine/sms/sms_module.php';
    include_once $engine_dir."/_engine/include/member.lib.php";

	/* +----------------------------------------------------------------------------------------------+
	' |  회원 처리
	' +----------------------------------------------------------------------------------------------+*/

	$exec = $_POST['exec'];
	$zip = addslashes(trim($_POST['zip']));
	$addr1 = addslashes(trim($_POST['addr1']));
	$addr2 = addslashes(trim($_POST['addr2']));
	$email1 = addslashes($_POST['email1']);
	$email2 = addslashes($_POST['email2']);
    if (isset($_POST['first_name']) == true && isset($_POST['first_name']) == true) {
        $first_name = addslashes($_POST['first_name']);
        $family_name = addslashes($_POST['family_name']);
        $name = $_POST['name'] = trim($first_name.' '.$family_name);
    } else {
        $name = addslashes($_POST['name']);
    }
	$recom_member = addslashes($_POST['recom_member']);
	$biz_type1 = addslashes($_POST['biz_type1']);
	$biz_type2 = addslashes($_POST['biz_type2']);
	$owner = addslashes($_POST['owner']);
	$dam = addslashes($_POST['dam']);
	$biz_num = $_POST['biz_num'];
	$pwd = $_POST['pwd'];
	$biz_birthday = addslashes($_POST['biz_birthday']);
	$phone = addslashes($_POST['phone']);
	$cell = addslashes($_POST['cell']);
	$mng_memo = addslashes($_POST['mng_memo']);
	$auth = addslashes($_POST['auth']);
	$limit_agree = addslashes($_POST['limit_agree']);
	$sms = ($_POST['sms'] == 'Y') ? 'Y' : 'N';
	$mailing = ($_POST['mailing'] == 'Y') ? 'Y' : 'N';
    $level = (int) $_POST['level'];
	$whole_mem = addslashes($_POST['whole_mem']);
	$birth1 = sprintf('%04d', $_POST['birth1']);
	$birth2 = sprintf('%02d', $_POST['birth2']);
	$birth3 = sprintf('%02d', $_POST['birth3']);
    $birth = ($birth1 && $birth2 && $birth3) ? $birth1.'-'.$birth2.'-'.$birth3 : '';
	$birth_type = addslashes($_POST['birth_type']);
	$sex = addslashes($_POST['sex']);

    if ($_POST['exec'] == 'register') { // 수동 등록
        $member_id = trim($_POST['member_id']);
        $email = trim($_POST['email']);
        $join_ref = (isset($_POST['editable']) == true) ? 'mng' : 'mng2';
        if (empty($email) == true) $email = '';
        if (empty($cell) == true) $cell = '';
        $pwd = (empty($pwd) == true) ? '' : sql_password($pwd);

        if (strlen($member_id) < 4) exit(alert('회원아이디는 4자 이상으로 입력해주세요.'));
        if ($pdo->row("select count(*) from {$tbl['member']} where member_id=?", array($member_id)) > 0) {
            exit(alert('이미 등록된 회원아이디입니다.'));
        }
        if (empty($member_id) == true) exit(alert('회원아이디를 입력해주세요.'));
        if (empty($name) == true) exit(alert('회원이름을 입력해주세요.'));
        if (empty($cell) == true && empty($email) == true) {
            exit(alert('휴대전화번호 또는 이메일을 입력해주세요.'));
        }
        // 이메일 형식 체크
        if (empty($email) == false) {
            if (preg_match("/^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$/i", $email) == false)  {
                exit(alert('이메일 형식을 확인해주세요.'));
            }
        }
        // 이메일 중복체크
        if ($scfg->comp('join_check_email', 'Y') == true) {
            $exists = $pdo->row("select count(*) from {$tbl['member']} where email=?", array($email));
            if ($exists > 0) {
                exit(alert('이미 사용중인 이메일주소입니다.'));
            }
        }
        // 휴대폰번호 중복 체크
        if ($scfg->comp('join_check_cell', 'Y') == true) {
            $exists = $pdo->row("select count(*) from {$tbl['member']} where cell=?", array($cell));
            if ($exists > 0) {
                exit(alert('이미 사용중인 휴대폰번호입니다.'));
            }
        }

        $pdo->query("
            insert into {$tbl['member']}
            (member_id, pwd, name, email, cell, join_ref, reg_date, reg_email, reg_sms, phone, sms, mailing, level, whole_mem, birth, sex, zip, addr1, addr2, ip)
            values (?, ?, ?, ?, ?, ?, ?, 'Y', 'Y', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ", array(
            $member_id, $pwd, $name, $email, $cell, $join_ref, $now, $phone, $sms, $mailing, $level, $whole_mem, $birth, $sex, $zip, $addr1, $addr2, $_SERVER['REMOTE_ADDR']
        ));
        $no = $pdo->lastInsertId();
        if (!$no) {
            alert(php2java($pdo->geterror()));
            msg('생성 중 오류가 발생하였습니다.');
        }

        // 추가 정보
        $add_sql = '';
        if ($limit_agree) {
            $add_sql .= ", 14_limit_agree='$limit_agree'";
        }
        if ($add_sql) {
            $pdo->query("update {$tbl['member']} set no='$no' $add_sql where no='$no'");
        }

        javac("parent.viewMember('$no', '$member_id'); parent.location.reload();");
        exit;
    }

	if($_POST['exec'] == 'mchecker') {
		$asql = '';
		foreach($_POST['mc'] as $key => $val) {
			if($asql) $asql .= ',';
			$asql .= "`checker_{$key}`='$val'";
		}

		$mno = numberOnly($_POST['mno']);
		$pdo->query("update $tbl[member] set $asql where no='$mno'");

		$res = $pdo->iterator("select no from $tbl[member_checker]");
        foreach ($res as $data) {
			$pdo->query("update $tbl[member_checker] set members=(select count(*) from $tbl[member] where checker_$data[no]='Y') where no='$data[no]'");
		}

		exit('OK');
	}

	checkBasic();

	$mno = numberOnly($_POST['mno']);
	checkBlank($mno,"회원번호를 입력해주세요.");
	$amember=get_info($tbl['member'],"no",$mno);
	if(!$amember['no']) msg("존재하지 않는 회원입니다","close","");

	if($exec=="milage" || $exec=="emoney") {
		$exec2 = $_POST['exec2'];
		$exec3 = ($exec2 == '+') ? '지급' : '반환';
		$mtitle = addslashes(trim($_POST['mtitle']));
		$mprc = parsePrice($_POST['mprc']);
		$mprc2 = parsePrice($_POST['mprc'], true);
        $milage_sms = (isset($_POST['milage_sms']) == true) ? 'Y' : 'N';

		if($exec2!="+" && $exec2!="-") msg("지급/반환을 선택하세요");
		checkBlank($mtitle,"사유를 입력해주세요.");
		checkBlank($mprc,"금액을 숫자로 입력해주세요.");
		include $engine_dir."/_engine/include/milage.lib.php";

		if($exec=="milage") {
			ctrlMilage($exec2,3,$mprc,$amember,$mtitle,"",$admin['admin_id']);
			$ems="적립금";

            if ($exec2 == '+' && $milage_sms == 'Y') {
                $sms_replace['name'] = stripslashes($amember['name']);
                $sms_replace['member_id'] = stripslashes($amember['member_id']);
                $sms_replace['milage_amount'] = parsePrice($mprc, true);
                $sms_replace['milage_expiration'] = ($cfg['milage_expire']) ? date('Y-m-d', strtotime("+ {$cfg['milage_expire']}")) : '무제한';
                $sms_replace['mtitle'] = $mtitle;

                SMS_send_case(39, $amember['cell']);
            }
		}
		elseif($exec=="emoney") {
			ctrlEmoney($exec2,3,$mprc,$amember,$mtitle,"",$admin['admin_id']);
			$ems="예치금";
		}

		$ems=$amember['name']." 회원님께 $ems (".$mprc2.$cfg['currency_type'].")을 ".$exec3."하였습니다";
		msg($ems,"reload","parent");
	}

	$add_sql="";

	$name = addslashes(trim($_POST['name']));
	$nick = addslashes(trim($_POST['nick']));
	$email1 = addslashes(trim($_POST['email1']));
	$email2 = addslashes(trim($_POST['email2']));
	$email = $email1.'@'.$email2;
	$pwd = $_POST['pwd'];
	$recom_member = addslashes(trim($_POST['recom_member']));
	$phone = addslashes(trim($_POST['phone']));
	$cell = addslashes(trim($_POST['cell']));
	$mng_memo = addslashes(trim($_POST['mng_memo']));

	checkBlank($name,"이름을 입력해주세요.");
    if (isset($_POST['first_name']) == true) checkBlank($first_name, '이름을 입력해주세요.');
    if (isset($_POST['family_name']) == true) checkBlank($family_name, '이름을 입력해주세요.');

	if($cfg['join_birth_use'] == "Y"){
		if($cfg['member_join_birth']=="Y") {
			checkBlank($birth1,"생년월일을 입력해주세요.");
			checkBlank($birth2,"생년월일을 입력해주세요.");
			checkBlank($birth3,"생년월일을 입력해주세요.");
		}
		$_birth=($birth1 && $birth2 && $birth3) ? $birth1."-".$birth2."-".$birth3 : "";
		$add_sql .= " , `birth`='$_birth', `birth_type`='$birth_type'";
	}
	if($cfg['join_sex_use'] == "Y"){
		if($cfg['member_join_sex']=="Y") checkBlank($sex,"성별을 선택해주세요.");
		$add_sql .= " , `sex`='$sex'";
	}
	if($cfg['use_whole_mem'] == "Y"){
		$add_sql .= " , `whole_mem`='$whole_mem'";
	}

	if($cfg['member_join_nickname']=="Y") {
		if($cfg['nickname_essential']=="Y") checkBlank($nick,"닉네임을 입력해주세요.");
		if (strlen(strlen(iconv('utf-8', 'euckr//IGNORE', $nick))) > 16) msg("닉네임은 한글 8자 영문 16자 이내로 입력해 주시기 바랍니다");
		if ($nick && $pdo->row("select * from `$tbl[member]` where `nick` = '$nick' and `no` != '$mno'")) msg("이미 존재하는 닉네임입니다");
		$nick = addslashes($nick);
	}
	include_once $engine_dir."/_engine/include/member.lib.php";

	/*
	checkBlank($phone,'전화번호를 입력해주세요.');
	checkBlank($cell,'휴대전화번호를 입력해주세요.');
	checkBlank($zip,"우편번호를 입력해주세요.");
	checkBlank($addr1,"주소를 입력해주세요.");
	checkBlank($addr2,"상세주소를 입력해주세요.");
	*/
	checkBlank($email1,"이메일을 입력해주세요.");
	checkBlank($email2,"이메일을 입력해주세요.");
	checkBlank($email,"이메일을 정확히 입력해주세요.");

	if($pwd[0]) {
		$c_pwd=checkPwd($pwd);
		$add_sql.=$c_pwd[1];

        addPrivacyViewLog(array(
            'page_id' => 'member',
            'page_type' => 'password',
            'target_id' => $amember['member_id'],
            'target_cnt' => 1
        ));
	}

	// 추가 정보
	$add_info_file=$root_dir."/_config/member.php";
	if(is_file($add_info_file)) {
		include_once $add_info_file;
		$total_add_info=count($_mbr_add_info);
		if($total_add_info>0) {
			$aisql3="";
			foreach($_mbr_add_info as $key=>$val) {
				addField($tbl['member'],"add_info".$key,"varchar(100) NULL");
				if($val['type'] == "checkbox" && sizeof($_POST["add_info".$key]) > 0){
					$_addval="@";
					foreach($_POST["add_info".$key] as $key2=>$val2){
						$_addval .= $val2."@";
					}
					$_POST["add_info".$key]=addslashes(trim($_addval));
				}
                if ($val['type'] == 'file') {
                    $_file = $_FILES['add_info'.$key];
                    if ($_file['size'] > 0) {
                        $filename = md5(rand(0,9999).$now.$key);
                        $upload = uploadFile($_file, $filename, '_data/member/add_info'.$key, implode('|', $val['ext']));
                        $_POST["add_info".$key] = $upload[0];

                        $aisql3.=",`add_info".$key."`='".addslashes(trim($_POST["add_info".$key]))."'";
                    }
                } else {
				    $aisql3.=",`add_info".$key."`='".addslashes(trim($_POST["add_info".$key]))."'";
                }
			}
		}
	}

	if($_use['recom_member']){
		$add_sql.=",`recom_member`='$recom_member'";
	}

	$member_id = $pdo->row("select member_id from `${tbl['member']}` where no='${mno}'");

	// 아이디를 이메일로 사용시
	if($cfg['member_join_id_email'] == 'Y' && $email != $member_id){
		if(checkID($email)) msg(__lang_member_error_wrongId__);
		$add_sql .= ", `member_id`='$email'";
	}

	if($limit_agree) {
		$add_sql .= ", 14_limit_agree='$limit_agree'";
	}

	if($cfg['use_edit_receive']=="Y") {
		$mail_text = "";
		$sms_text = "";
		if($amember['mailing']!=$mailing) {
			$mail_text = ($mailing=="Y") ? __lang_agree_email_receiving__ : __lang_agree_email_optout__;
		}
		if($amember['sms']!=$sms) {
			$sms_text  = ($sms == 'Y') ? __lang_agree_sms_receiving__ : __lang_agree_sms_optout__;
			$sms_text .= ($mail_text) ? ',' : '';
		}

		$receive_text = $sms_text.$mail_text;
		if($receive_text) {
			if($cfg['edit_receive_type']=='sms') {
				$sms_replace['agree_date'] = date("Y/m/d", $now);
				$sms_replace['agree_receive'] = sprintf(__lang_agree_sms_email_yn__, $receive_text);
				$r = SMS_send_case(23, $cell);
			}else {
				$mail_case = 21;
				$marketing_regdate = date("Y/m/d", $now);
				$sms_email_yn = sprintf(__lang_agree_sms_email_yn__, $receive_text);
                $member_name = $name;
				include_once $engine_dir.'/_engine/include/mail.lib.php';
				$r = sendMailContent($mail_case, $amember['name'], $amember['email']);
			}
		}
	}

    if ($first_name || $familay_name) {
        $aisql3 .= ", first_name='$first_name', family_name='$family_name'";
    }

	$sql="update $tbl[member] set `name`='$name',`email`='$email',`phone`='$phone',`cell`='$cell',`zip`='$zip',`addr1`='$addr1',`addr2`='$addr2',`mailing`='$mailing',`sms`='$sms',`mng_memo`='$mng_memo', `nick`='$nick' $add_sql $aisql3 where `no`='$mno'";
	$pdo->query($sql);

    // 회원 수정 로그
    if ($pdo->lastRowCount() == 1) {
        addPrivacyViewLog(array(
            'page_id' => 'member',
            'page_type' => 'update',
            'target_id' => $amember['member_id'],
            'target_cnt' => 1
        ));
    }

	setAdvInfoDate($amember['no'], $amember['mailing'], $amember['sms'], true);

	$url="";
	// 아이디를 이메일로 사용시 DB 전체 테이블 아이디 변환
	if($cfg['member_join_id_email'] == 'Y' && $email != $member_id){
		updateMemberIdField($email,$member_id);
		$url = "/_manage/?body=member@member_view.frm&smode=info&mno=${mno}&mid=${email}";
	}

	if($cfg['use_biz_member']=="Y") {
		$abiz=get_info($tbl['biz_member'], 'ref', $amember['no']);

		if($abiz['ref']){
			$biz_num_sum=addBar($biz_num);

			$sql="update `$tbl[biz_member]` set `auth`='$auth', `biz_num`='$biz_num_sum', `dam`='$dam',`owner`='$owner',`biz_type1`='$biz_type1',`biz_type2`='$biz_type2',`biz_birthday`='$biz_birthday' where `ref`='$mno'";
			$pdo->query($sql);
		}
	}

    // 사업자 및 14세 미만 가입 승인 알림
    if (
        (empty($limit_agree) == false || empty($auth) == false) &&
        ($amember['14_limit_agree'] != 'Y' || $abiz['auth'] != 'Y') &&
        (empty($limit_agree) == true || ($amember['14_limit'] == 'Y' && $limit_agree == 'Y')) &&
        (empty($auth) == true || $auth == 'Y')
    ) {
        $sms_replace['name'] = $name;
        $sms_replace['member_id'] = $amember['member_id'];
        SMS_send_case(41, $cell);
    }

	if(is_object($erpListener)) {
		$erpListener->setChangedMember($amember['member_id']);
	}

	if($url) msg("회원정보가 정상적으로 수정되었습니다",$url,"parent");
	else msg("회원정보가 정상적으로 수정되었습니다","reload","parent");

?>