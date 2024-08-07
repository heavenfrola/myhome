<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  회원가입/수정 DB처리
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/member.lib.php";
	include_once $engine_dir."/_engine/include/file.lib.php";
	include_once $engine_dir."/_manage/manage2.lib.php";

	// 아이핀 체크 플러스 사용 시 휴대폰 중복가입 금지
	if ($cfg['ipin_checkplus_use'] == 'Y' || $scfg->comp('use_kcb', 'Y')) {
		$cfg['join_check_cell'] = 'Y';
	}

	// SNS관련
    $_POST = array_map('strip_script', $_POST);
	$sns_type = addslashes(trim($_POST["sns_type"]));
	$sns_cid = addslashes(trim($_POST["sns_cid"]));
	$sns_name = addslashes(trim($_POST["sns_name"]));
	$sns_email = addslashes(trim($_POST["sns_email"]));
	$sns_data = addslashes(trim($_POST["sns_data"]));
	$snsCnt = 0;
	$cid = addslashes(trim($_POST['cid']));

	// 일반 변수
    if (isset($_POST['first_name']) == true && isset($_POST['family_name']) == true) {
        $first_name = addslashes(trim($_POST['first_name']));
        $family_name = addslashes(trim($_POST['family_name']));
        $name = trim($first_name.' '.$family_name);
    } else {
	    $name = addslashes(trim($_POST['name']));
    }
	$member_id = addslashes(trim($_POST['member_id']));
	$email1 = addslashes(trim($_POST['email1']));
	$email2 = addslashes(trim($_POST['email2']));
	$email = addslashes(trim($_POST['email']));
	if($email) {
		list($email1, $email2) = explode('@', $email);
	}
	$phone = $_POST['phone'];
	$cell = $_POST['cell'];
	$recom_member = addslashes(trim($_POST['recom_member']));
	$birth1 = numberOnly($_POST['birth1']);
	$birth2 = numberOnly($_POST['birth2']);
	$birth3 = numberOnly($_POST['birth3']);
	$birth_type = addslashes(trim($_POST['birth_type']));
	if(empty($birth_type)) $birth_type = '양';
	$nick = addslashes(trim($_POST['nick']));
	$pwd = $_POST['pwd'];
	$zip = trim(addslashes($_POST['zip']));
	$addr1 = trim(addslashes($_POST['addr1']));
	$addr2 = trim(addslashes($_POST['addr2']));
	$whole_mem = addslashes($_POST['whole_mem']);
	$sex = addslashes(trim($_POST['sex']));
	$member_type = numberOnly($_POST['member_type']);

	$rURL = $_POST['rURL'];

    // 폼을 통하지 않고 SNS 직접 가입
    if ($_POST['sns_simplified'] == 'Y') {
        $sns_type = $_SESSION['sns_login']['sns_type'];
        $sns_cid = $_SESSION['sns_login']['cid'];
        $email = $_SESSION['sns_login']['email'];
        $member_id = $sns_type.'_'.$sns_cid;

        $name = $_SESSION['sns_login']['name'];
        $cell = $_SESSION['sns_login']['cell'];
        list($birth1, $birth2, $birth3) = explode('-', $_SESSION['sns_login']['birth']);
        $sex = $_SESSION['sns_login']['gender'];
        $zip = $_SESSION['sns_login']['addr_zip'];
        $addr1 = $_SESSION['sns_login']['addr_addr1'];
        $addr2 = $_SESSION['sns_login']['addr_addr2'];

        // 일부 항목이 미비해도 가입시 걸리지 않도록 처리
        if ($cfg['member_join_birth'] == 'Y') $cfg['member_join_birth'] = 'N';
        if ($cfg['member_join_sex'] == 'Y') $cfg['member_join_sex'] = 'N';
        if ($cfg['member_join_addr'] == 'Y') $cfg['member_join_addr'] = 'N';

        $_SERVER['REQUEST_METHOD'] = 'POST';

        // 중복 이메일 체크
        if (isset($_POST['sns_integrate']) == false) {
            $email_check = $pdo->row("select count(*) from {$tbl['member']} where email=? or member_id=?", array($email, $email));
            if ($email_check > 0) {
                exit(json_encode(array(
                    '',
                    'url' => $root_url.'/member/apijoin.php?check_mail=true'
                )));
            }
            if ($cell) {
                $cell_check = $pdo->row("select count(*) from {$tbl['member']} where cell=?", array(str_replace('-', '', $cell)));
                if ($cell_check > 0) {
                    exit(json_encode(array(
                        '',
                        'url' => $root_url.'/member/apijoin.php?check_mail=true'
                    )));
                }
            }
        } else {
            $member_id = $email;
            if ($_POST['member_id']) {
                $member_id = $_POST['member_id'];
            }
        }

        $cfg['member_join_nm_spc'] = 'Y'; // 이름 특수문자 일시 해제
        $cfg['nickname_essential'] = 'N'; // 닉네임 필수 일시 해제
    }

	if($sns_type) {
		if (!$email) $email = $member_id;
		if (!array_key_exists($sns_type , $_sns_type)) {
			msg(__lang_member_error_snsSevice__);
		}
		if(!$sns_cid) {
			msg(__lang_member_error_snsEssential__);
		}
        $cell = str_replace('-', '', $cell);

		// SNS 가입 여부 체크
		$sql = "SELECT COUNT(*) FROM $tbl[sns_join] AS A INNER JOIN  $tbl[member] AS B ON (A.member_no=B.no)  WHERE A.cid='$cid' and A.type = '$_sns_type[$sns_type]'";
		$snsCnt = $pdo->row($sql);
		if($snsCnt) {
			msg(__lang_member_error_snsIdJoin__);
		}

        if (isset($_POST['cell']) && $scfg->comp('join_check_cell', 'Y')) { // 중복 휴대폰번호 가입 금지
            if ($cell == '') msg(__lang_member_input_cell__);
            if ($pdo->row("select count(*) from $tbl[member] where replace(cell, '-', '')='$cell' and no!='$member[no]'") > 0) {
                msg(__lang_member_error_existsCell__);
            }

            if ($pdo->row("select count(*) from $tbl[member_deleted] where replace(cell, '-', '')='$cell'") > 0) {
                msg(__lang_member_error_existsCell__);
            }
        }
	} else {
		if($cfg['member_join_id_email'] == 'Y' && !$sns_type) $member_id = $email1."@".$email2;
	}

	checkBasic();

	$aisql1=$aisql2=$aisql3="";
	if(is_array($_SESSION['ipin_res'])) {
		$name = $_SESSION['ipin_res']['name'];
		$birth1 = substr($_SESSION['ipin_res']['birth'], 0, 4);
		$birth2 = substr($_SESSION['ipin_res']['birth'], 4, 2);
		$birth3 = substr($_SESSION['ipin_res']['birth'], 6, 2);
		$sex = $_SESSION['ipin_res']['gender'] == 1 ? '남' : '여';
		$aisql1 .= " , `reg_sms`";
		$aisql2 .= " , 'Y'";
		$age = floor((date('Ymd') - ($birth1.$birth2.$birth3))/10000);
		if ($cfg['limit_19'] == 'Y' && $age < 19){
			msg(__lang_member_19join_limit__);
		}
	}

	$dam = addslashes(trim($_POST['dam']));
	$owner = addslashes(trim($_POST['owner']));
	$biz_type1 = addslashes(trim($_POST['biz_type1']));
	$biz_type2 = addslashes(trim($_POST['biz_type2']));
	$biz_birthday = addslashes(trim($_POST['biz_birthday']));
	$biz_num = (is_array($_POST['biz_num'])) ? array_map('numberOnly', $_POST['biz_num']) : addslashes($_POST['biz_num']);

	$unique = numberOnly($_POST['unique']);
	$reg_data = $_POST['reg_data'];

	$sms = ($_POST['sms'] == 'Y') ? 'Y' : 'N';
	$mailing = ($_POST['mailing'] == 'Y') ? 'Y' : 'N';

	// SNS 가입이 아니면 유효성 검사
	if(!$sns_type) {

		if(!$member['no'] || (($member['reg_sms'] == 'Y' && numberOnly($member['cell']) != numberOnly($_POST['cell'])) || ($member['reg_email'] == 'Y' && $member['email'] != $_POST['email']))) {
			if($cfg['member_confirm_email'] == 'Y' || $cfg['member_confirm_sms'] == 'Y') {
				if($reg_data) {
					$rdata = $pdo->assoc("select * from $tbl[join_sms_new] where no='$reg_data'");
					if($rdata['type'] == 1) {
						$cell = explode(preg_replace('/^([0-9]{3})([0-9]+)([0-9]{4})$/', '$1-$2-$3', $rdata['phone']));
					} elseif($rdata['type'] == 2) {
						list($email1, $email2) = explode('@', $rdata['phone']);
					}
				} else {
					if(!$_POST['unique']) msg(__lang_member_select_authtype__);
				}
			}
		}

		if(is_array($phone)) {
			checkBlank($phone[0], __lang_member_input_phone__);
			checkBlank($phone[1], __lang_member_input_phone__);
			checkBlank($phone[2], __lang_member_input_phone__);
		} else {
			if(isset($_POST['phone'])) {
				checkBlank($phone, __lang_member_input_phone__);
			}
		}

		if(is_array($cell)) {
			checkBlank($cell[0], __lang_member_input_cell__);
			checkBlank($cell[1], __lang_member_input_cell__);
			checkBlank($cell[2], __lang_member_input_cell__);
		} else {
			checkBlank($cell, __lang_member_input_cell__);
		}
		//checkBlank($cell, __lang_member_input_cell__);

		if(is_array($phone)) $phone=addBar($phone);
		if(is_array($cell)) $cell=addBar($cell);
		$phone = addslashes($phone);
		$cell = addslashes($cell);

		if($cfg['member_join_addr'] != 'N') {
			if(array_key_exists('zip',$_POST)) checkBlank($zip, __lang_member_input_zipcode__);
			if(array_key_exists('addr1',$_POST)) checkBlank($addr1, __lang_member_input_addr1__);
			if(array_key_exists('addr2',$_POST)) checkBlank($addr2, __lang_member_input_addr1__);
		}


		if($cfg['member_join_id_email'] != 'A') {
			checkBlank($email1, __lang_member_input_email__);
			checkBlank($email2, __lang_member_input_email__);
			$email=$email1."@".$email2;
		}
		//checkBlank($email, __lang_member_input_email2__);

		if($cell && $cfg['join_check_cell'] == 'Y') { // 중복 휴대폰번호 가입 허용
            $_cell = str_replace('-', '', $cell);
			if($pdo->row("select count(*) from $tbl[member] where replace(cell, '-', '')='$_cell' and no!='$member[no]'") > 0) {
				msg(__lang_member_error_existsCell__);
			}

			if($pdo->row("select count(*) from $tbl[member_deleted] where  replace(cell, '-', '')='$_cell'") > 0) {
				msg(__lang_member_error_existsCell__);
			}
		}

		if($email && $cfg['join_check_email'] == 'Y') { // 중복 이메일 가입 허용
			if($pdo->row("select count(*) from $tbl[member] where email='$email' and no!='$member[no]'") > 0) {
				msg(__lang_member_error_existsEmail__);
			}

			if($pdo->row("select count(*) from $tbl[member_deleted] where email='$email'") > 0) {
				msg(__lang_member_error_existsEmail__);
			}
		}
        // 이메일 형식 체크
        if(preg_match('/^[0-9a-zA-Z]([-.]?[0-9a-zA-Z_])*@[0-9a-zA-Z]([-.]?[0-9a-zA-Z_])*.[a-zA-Z]{2,3}$/i', $email) == false) {
            msg(__lang_member_input_email2__);
        }


		$reg_code = numberOnly(trim($_POST['reg_code']));
        $reg_code_enc = aes128_encode($reg_code, 'join');
		if($unique == 2 && !$reg_code) {
			if(($member['reg_sms'] == 'Y' && str_replace('-', '', $member['cell']) != str_replace('-', '', $cell)) || ($member['reg_sms'] != 'Y' && $member['no'])) {
				alert(php2java(__lang_member_error_checkAuthnum__));
				exit(javac("parent.openCertFrm();"));
			}

			if(!$member['no']) {
				exit(javac("parent.openCertFrm();"));
			}
		}

		if((!$member['no'] || $member['reg_sms'] != 'Y' || ($member['reg_sms'] == 'Y' && (str_replace('-', '', $member['cell']) != str_replace('-', '', $cell)))) && $unique == 2) {
			if($pdo->row("select count(*) from $tbl[member] where cell='$cell' and no!='$member[no]'") > 0) {
				msg(__lang_member_error_existsCell__);
			}

			if($pdo->row("select count(*) from $tbl[member_deleted] where cell='$cell'") > 0) {
				msg(__lang_member_error_existsCell__);
			}
			$data = $pdo->assoc("select * from wm_join_sms where reg_code='$reg_code_enc'");

			if(!$data['phone']) {
				msg(__lang_member_error_cellAuth__);
			}

			if(str_replace('-', '', $data['phone']) != str_replace('-', '', $cell)) {
				msg(__lang_member_error_diffCellAuth__);
			}

			$reg_sql = ",`reg_sms`='Y', `reg_email`='N'";
		}

		if($unique == 1) {
			if(($member['reg_email'] == 'Y' && $member['email'] != $email) || $member['reg_email'] != 'Y') {
				$key = md5(rand(1000,9999).time());
				$reg_sql = ",`reg_sms`='N', `reg_email`='W', reg_code='$key'";

				if($pdo->row("select count(*) from $tbl[member] where email='$email' and no!='$member[no]'") > 0) {
					msg(__lang_member_error_existsEmail__);
				}

				if($pdo->row("select count(*) from $tbl[member_deleted] where email='$email'") > 0) {
					msg(__lang_member_error_existsEmail__);
				}

				if($member['no'] > 0) {
					$rmsg = $member['reg_email'] == 'Y' ? __lang_member_info_changedEmail__ : __lang_member_info_changedAuth__;
					alert($rmsg."\\n".__lang_member_info_authCompleted__);
				}

				if($member['no'] > 0) {

					$mail_case = 14;
					$name = $member['name'];

					include_once $engine_dir.'/_engine/include/mail.lib.php';
					$r = sendMailContent($mail_case, $name, $email);

					if(!$r){
						$mail_case = 6;
						$mail_title[6] = "[{쇼핑몰이름}] $member[name]님, 메일주소를 다시 인증해 주세요.";
						$content1 = "
							{$member[name]}님의 메일주소가 변경되었습니다.<br /><br />
							본 메일에 포함된 하단 링크를 클릭하시면 개인정보 수정절차가 완료되어 정상적으로 이메일이 변경됩니다.<br />
							정보수정은 신청 이후 한시간 이내에 받으셔야 합니다.<br /><br />
							<a href='$root_url/main/exec.php?exec_file=member/join_finish.exe.php&key=$key&type=re' target='_blank'>정보수정 완료하기</a>
						";

						$_mstr['메일내용']=$content1;
						$_mstr['수신거부링크']='{수신거부링크}';
						sendMailContent($mail_case, $data['name'], $email);

                        alert(__lang_member_email_change__);
					}

                    // 인증 이후에 실제로 이메일 변경 2020-12-22
                    $reg_sql .= ", email_reserve='$email', reg_email='Y'";
                    $_POST['email'] = $email = $member['email'];

                    addField(
                        $tbl['member'],
                        'email_reserve',
                        'varchar(100) not null default "" after email'
                    );
				}
			}
		} else {
            // 이메일 인증 없이 이메일을 아이디로 사용할 경우 이메일을 변경할 때
            if($member['no'] > 0 && $cfg['member_join_id_email'] == 'Y' && $member['member_id'] != $email) {
                $pdo->query("update {$tbl['member']} set member_id=? where no=?", array(
                    $email, $member['no']
                ));
                updateMemberIdField($email, $member['member_id']);
                $member['member_id'] = $email;
            }
        }

		// 추천인
		if(!$member['no'] && $_use['recom_member']) {
			$rmsql1=",`recom_member`";
			$rmsql2=",'$recom_member'";
			if($_use['recom_member']=="Y") {
				$rmsql3=",`recom_member`='$recom_member'";
			}

			if($recom_member) {
				$isrecom = $pdo->row ("select count(*) from `$tbl[member]` where `member_id` = '$recom_member'");
				if($isrecom) {
					if($cfg['recom_limit']) {
						$cntrecom = $pdo->row ("select count(*) from `$tbl[member]` where `recom_member` = '$recom_member'");
						if($cntrecom >= $cfg['recom_limit']) msg(sprintf(__lang_member_error_maxrecom__, $cfg['recom_limit']));
					}
				} else {
					msg(__lang_member_error_recomNotExists__);
				}
			}
		}

		$no_join_milage=0;

		// 추가 정보
		$add_info_file=$root_dir."/_config/member.php";
		if(is_file($add_info_file)) {
			include_once $add_info_file;
			$total_add_info=count($_mbr_add_info);
			if($total_add_info>0) {
				if($cfg['milage_join_add_info']=="Y") {
					$no_join_milage=$total_add_info-$_mbr_add_info_milage_except_count;
				}
				foreach($_mbr_add_info as $key=>$val) {
					if($val['ncs']=="Y" && $_POST["add_info".$key]=="" && $val['type'] != 'file') {
						msg(sprintf(__lang_member_input_required__, $val['name']));
					}
					addField($tbl['member'],"add_info".$key,"varchar(100) NULL");

                    // 첨부파일
                    if ($val['type'] == 'file') {
                        $_file = $_FILES['add_info'.$key];

                        if ($_file['size'] == 0) {
                            if (isset($member['no']) == false) { // 신규 가입
                                if ($val['ncs'] == 'Y') {
            						msg(sprintf(__lang_member_input_required__, $val['name']));
                                }
                            }
                            continue;
                        }

                        makeFullDir('_data/member/add_info'.$key);
                        $filename = md5(rand(0,9999).$now.$key);
                        $upload = uploadFile($_file, $filename, '_data/member/add_info'.$key, implode('|', $val['ext']));
                        $_POST["add_info".$key] = $upload[0];
                    }

					$aisql1.=",`add_info".$key."`";
					if(($val['type'] == "checkbox" || $val['type'] == "selectarray") && sizeof($_POST["add_info".$key]) > 0){
						$_addval="@";
						foreach($_POST["add_info".$key] as $key2=>$val2){
							$_addval .= $val2."@";
						}
						$_POST["add_info".$key] = addslashes($_addval);
					}
					$aisql2.=",'".$_POST["add_info".$key]."'";
					$aisql3.=",`add_info".$key."`='".$_POST["add_info".$key]."'";

					if($_POST["add_info".$key]!="" && $no_join_milage>0) {
						$no_join_milage--;
					}
				}
			}
		}

	}
	if(is_array($cell) == true) $cell = '';


	if($cfg['join_jumin_use'] == "Y"){
		// 주민등록번호 지원 제거
	}else{
		if($cfg['join_birth_use']=='Y') {
			if($cfg['member_join_birth'] == 'Y') {
				if($cfg['birth_modify_use'] != 'Y' || !$member['no']) {
				    checkBlank($birth1, __lang_member_input_birthday__);
					checkBlank($birth2, __lang_member_input_birthday__);
					checkBlank($birth3, __lang_member_input_birthday__);
				}
			}
		}
		if($birth1 && $birth2 && $birth3) {
			$age = floor((date('Ymd')-($birth1.$birth2.$birth3))/10000);
			$_birth = implode('-', array($birth1, $birth2, $birth3));
			$aisql1 .= " , `birth`, `birth_type`";
			$aisql2 .= " , '$_birth', '$birth_type'";
			if($cfg['birth_modify_use'] != "Y" || ($cfg['birth_modify_use'] == "Y" && !trim($member['birth']))) $aisql3 .= " , `birth`='$_birth', `birth_type`='$birth_type'";

			if($cfg['join_14_limit'] == "B" && ($cfg['join_14_limit_method'] == 1 || ($cfg['join_14_limit_method'] == 2 && $cfg['member_join_birth'] == "Y")) && $age < 14) {
				alert(__lang_member_14join_agree__);
			}
			if($cfg['join_14_limit'] == "C" && ($cfg['join_14_limit_method'] == 1 || ($cfg['join_14_limit_method'] == 2 && $cfg['member_join_birth'] == "Y")) && $age < 14) {
				msg(__lang_member_14join_impossible__);
			}

			if($age < 14) {
				$limit = "Y";
				$limit_agree = ($cfg['join_14_limit'] == "A") ? "Y" : "N";
			} else {
				$limit = "N";
				$limit_agree = "N";
			}
			if($cfg['join_14_limit'] == "B" || $cfg['join_14_limit'] == "C") {
				$aisql1 .= " , `14_agree_type`";
				$aisql2 .= " , '$cfg[join_14_limit_method]'";
			}
		}
		if ($cfg['member_join_sex']=='Y' && $cfg['join_sex_use'] == 'Y') checkBlank($sex, __lang_member_input_gender__);
		if($sex) {
			$aisql1 .= " , `sex`";
			$aisql2 .= " , '$sex'";
			$aisql3 .= " , `sex`='$sex'";
		}

		if($cfg['use_whole_mem'] == "Y"){
			if(!$whole_mem) $whole_mem = 'N';
			$aisql1 .= " , `whole_mem`";
			$aisql2 .= " , '$whole_mem'";
			$aisql3 .= " , `whole_mem`='$whole_mem'";
		}
	}

	if($nick) {
		if($member['no']) $modify = " and `no` != '$member[no]'"; // 회원정보 수정시

		if(strlen($nick) > 24) msg(__lang_member_error_nick__);
		if(checkNameFilter($nick) == false) msg(__lang_member_error_nick2__);
		if($pdo->row("select * from `$tbl[member]` where `nick` = '$nick' $modify")) msg(__lang_member_error_existsNick__);

		$nick = addslashes($nick);
		$aisql1 .= ", `nick`";
		$aisql2 .= ", '$nick'";
		$add_sql .= ", `nick` = '$nick'";
	}else {
		if($cfg['member_join_nickname']=='Y' && $cfg['nickname_essential']=='Y') checkBlank($nick, __lang_member_input_nickname__);
	}

    // 해외몰 이름 필드 분리 (first/family)
    if (isset($_POST['first_name']) == true) {
        checkBlank($first_name, __lang_member_input_name__);
        checkBlank($family_name, __lang_member_input_name__);

        if ($scfg->comp('use_name_two_block', 'Y') == false) {
            addField($tbl['member'], 'family_name', "varchar(50) not null default '' after name");
            addField($tbl['member'], 'first_name', "varchar(50) not null default '' after name");

            $scfg->import(array(
                'use_name_two_block' => 'Y'
            ));
        }
        $aisql1 .= ", first_name, family_name";
        $aisql2 .= ", '$first_name', '$family_name'";
        $aisql3 .= ", first_name='$first_name', family_name='$family_name'";
    }

	// 정보수정
	if($member['no']) {
		if($pwd[0]) {
			$c_pwd=checkPwd($pwd);
			$add_sql .= $c_pwd[1];
			if($cfg['use_pwd_change'] == "Y"){
				$aisql3 .= ", `change_pwd_date` = '$now', `change_pwd_next` ='N'";
			}
		}

		// 아이디를 이메일로 사용시
		if($cfg['member_join_id_email'] == 'Y' && $member_id != $member['member_id']){
			/*
			if(checkID($member_id)) msg(__lang_member_error_wrongId__);
			$aisql3 .= ", `member_id`='$member_id'";
			*/
		}

        // 수동 가입 회원 아이디 변경
        if ($member['join_ref'] == 'mng' && $member_id != $member['member_id']) {
            $checkid = checkID($member_id);
            if($checkid){
                if($checkid == 2) {
                    msg(__lang_member_error_existsid__);
                } else {
                    msg(__lang_member_error_wrongId__);
                }
            }
            $add_sql .= ", member_id='$member_id', join_ref='mng2'";
            $_SESSION['m_member_id'] = $member_id;
        }

		$email = $email ? $email : addslashes(trim($_POST['email']));
		if($email) {
			$add_sql .= ", email='$email'";
		}

        if (isset($name) == true && empty($name) == false) { // 회원 정보 수정 시 이름 변경
            $add_sql .= ", name='$name'";
        }

		$sql="update `$tbl[member]` set `phone`='$phone',`cell`='$cell',`zip`='$zip',`addr1`='$addr1',`addr2`='$addr2',`mailing`='$mailing',`sms`='$sms' $rmsql3 $add_sql $aisql3 $reg_sql where `no`='$member[no]' and `member_id`='$member[member_id]'";
		$pdo->query($sql);

		$agree_chk = setAdvInfoDate($member['no'], $member['mailing'], $member['sms']);

		// 아이디를 이메일로 사용시 DB 전체 테이블 아이디 변환
		if($cfg['member_join_id_email'] == 'Y' && $member_id != $member['member_id']){
			//updateMemberIdField($member_id,$member['member_id']);
		}

		if(is_object($erpListener)) {
			$erpListener->setChangedMember($member['member_id']);
		}

		$_SESSION['pwd_check']=2;

		if($reg_sql && $unique == 1) {
			$_SESSION['member_no'] = 0;
		}
?>
<form name="joineditFrm" target="_parent" action="<?=$root_url?>/member/edit_step3.php" method="post">
<input type="hidden" name="mail_chg_yn" value="<?=$agree_chk[0]?>">
<input type="hidden" name="sms_chg_yn" value="<?=$agree_chk[1]?>">
</form>
<?php
	javac("document.joineditFrm.submit();");
	}
	else {

		// SNS 통합
		if($sns_type) {
            if ($_SESSION['sns_login']['term_sms']) $sms = 'Y';
            if ($_SESSION['sns_login']['term_email']) $mailing = 'Y';

			checkBlank($member_id, __lang_member_input_memberid__);
			$member_id=strtolower(trim($member_id));
			$sql = "SELECT * FROM " . $tbl['member'] . " WHERE  member_id='" . $member_id . "'";
			$tmpMember=$pdo->assoc($sql);
			if($tmpMember['no'] && isset($pwd[0]) == true) {
				// SNS 회원검증
				if($pwd[0]!=$pwd[1]) msg(__lang_member_error_idPwd__);
                if (isset($_POST['member_id_checked']) && !$_POST['member_id_checked']) {
                    msg('회원아이디 중복 체크를 진행해주세요.');
                }
				if($tmpMember['pwd']!=sql_password($pwd[1])) msg(__lang_member_error_idPwd__);
				if($tmpMember["withdraw"]=="Y") msg(__lang_member_info_w​ithdrawal__);
				if($tmpMember["withdraw"]=="D2") msg(__lang_member_info_sleep__);

                $s_asql = '';
                if (!$tmpMember['zip'] && $zip) {
                    $s_asql .= " , zip='$zip', addr1='$addr1', addr2='$addr2'";
                }

				// SNS INSERT
				$sql="INSERT INTO `$tbl[sns_join]` ( `type` , `cid` , `member_no` , `member_id` , `name` , `email` , `reg_date` , `data` ) VALUES ( '$_sns_type[$sns_type]','$sns_cid','$tmpMember[no]','$tmpMember[member_id]','$sns_name','$sns_email','$now','$sns_data')";
				$pdo->query($sql);

				// SNS UPDATE
				$sql = "SELECT GROUP_CONCAT(type ORDER BY type SEPARATOR '@' ) FROM " . $tbl['sns_join'] . " WHERE member_id='" . $member_id . "'";
				$udpate_sns_type=$pdo->row($sql);
				$sql = "UPDATE " . $tbl['member'] . " SET login_type='@" . $udpate_sns_type . "@' $s_asql WHERE no='" . $tmpMember['no'] . "'";
				$pdo->query($sql);

				// 최근 접속
				$sql="UPDATE `$tbl[member]` set `last_con`='$now', `total_con`=`total_con`+1, withdraw='N' where `no`='" . $tmpMember['no'] . "'";
				$pdo->query($sql);

				$_SESSION['member_no']  =$tmpMember['no'];
				$_SESSION['m_member_id']=$tmpMember['member_id'];
				$_SESSION['sns_type']   =$sns_type;

                if ($sms == 'Y') $pdo->query("update {$tbl['member']} set sms='Y' where no='{$tmpMember['no']}'");
                if ($mailing == 'Y') $pdo->query("update {$tbl['member']} set mailing='Y' where no='{$tmpMember['no']}'");

				loadPlugIn('member_join_finish');

				if(!$target) $target = "parent";
				msg(__lang_member_join_successSns__, "/", $target);
				exit();
			} else if(!$pwd[0]) {
				if(!$cfg['password_max']) $cfg['password_max'] = '12';
				$pwd[0] = $pwd[1] = substr(md5($sns_cid), 0,  $cfg['password_max']-1).'@';
			}
		}


		if($namecheck_num)	$name = $pdo->row("select `name` from `$tbl[namecheck_log]` where `no`='$namecheck_num'");

		checkBlank($name, __lang_member_input_name__);
		if($cfg['member_join_nm_num'] != 'Y') {
			if(preg_match('/[0-9]/', $name)) msg(__lang_member_error_name_num__);
		}
		if($cfg['member_join_nm_spc'] != 'Y') {
			if(preg_match("/[^\p{Hangul}a-z0-9\(\)]/ui", str_replace(' ', '', $name))) msg(__lang_member_error_name_spc__);
		}
		if(checkNameFilter($name) == false) msg(__lang_member_error_name__);

		if($member_type==2 && $cfg['use_biz_member']=="Y") {
			checkBlank($biz_num[0], __lang_member_input_biznum__);
			checkBlank($biz_num[1], __lang_member_input_biznum__);
			checkBlank($biz_num[2], __lang_member_input_biznum__);
			if(!checkBizNum($biz_num[0].$biz_num[1].$biz_num[2])) msg(__lang_member_error_wrongBiznum__);
			$biz_num_sum=addBar($biz_num);
			$level=8;
		}

		$c_pwd=checkPwd($pwd);
		$pass=$c_pwd[0];

		checkBlank($member_id, __lang_member_input_memberid__);
		$member_id=strtolower(trim($member_id));
		$checkid = checkID($member_id);
		if($checkid){
			if($checkid == 2) {
				msg(__lang_member_error_existsid__);
			} else {
				msg(__lang_member_error_wrongId__);
			}
		}

		$addr2=del_html($addr2);

		if(!$level) {
			$level=9; // 기본 등급
		}

		addField($tbl['member'], '14_limit', "enum('N','Y') DEFAULT 'N'");
		addField($tbl['member'], '14_limit_agree', "enum('N','Y') DEFAULT 'N'");
		addField($tbl['member'], 'mobile', "varchar(10) not null default ''");

		$mobile_access = ($_SESSION['browser_type'] == "mobile") ? "Y" : "N";
		if ($_COOKIE['wisamall_access_device'] == 'APP') $mobile_access = 'A';

		$sql="INSERT INTO `$tbl[member]` (`member_id` , `pwd` , `name` , `jumin` , `email` , `phone` , `cell` , `zip` , `addr1` , `addr2` , `mailing` , `sms` , `ip` , `reg_date` , `total_con` , `total_ord` , `milage` , `withdraw`, `level`, `join_ref`,`last_con`,`conversion`, `14_limit`, `14_limit_agree`, `mobile` $rmsql1 $aisql1) VALUES ('$member_id', '$pass', '$name', '$jumin', '$email', '$phone', '$cell', '$zip', '$addr1', '$addr2', '$mailing', '$sms', '$_SERVER[REMOTE_ADDR]', '$now' , '1', '0', '$milage' ,'N','$level','$_SESSION[log_ref]','$now','$_SESSION[conversion]', '$limit', '$limit_agree', '$mobile_access' $rmsql2 $aisql2)";

		$r = $pdo->query($sql);
		if(!$r){
			msg(addslashes($pdo->getError()));
		}
		$no = $pdo->lastInsertId();
		setAdvInfoDate($no, 'N', 'N');

		if($_POST['member_type']==2 && $cfg['use_biz_member']=="Y") {
			$sql="INSERT INTO `$tbl[biz_member]` ( `ref` , `dam` , `biz_num` , `auth` , `owner` , `biz_type1` , `biz_type2` , `biz_birthday` ) VALUES ( '$no',  '$dam','$biz_num_sum','N','$owner','$biz_type1','$biz_type2','$biz_birthday')";
			$pdo->query($sql);
		}

		// SNS 가입
		if($sns_type) {
			$pdo->query("update $tbl[member] set login_type='@$_sns_type[$sns_type]@' where no='$no'");
			$sql="INSERT INTO `$tbl[sns_join]` ( `type` , `cid` , `member_no` , `member_id` , `name` , `email` , `reg_date` , `data` ) VALUES ( '$_sns_type[$sns_type]','$sns_cid','$no','$member_id','$sns_name','$sns_email','$now','$sns_data')";
			$pdo->query($sql);
		}

		include $engine_dir."/_engine/include/milage.lib.php";

		// 가입 적립금
		$milage=($cfg['milage_join']>0 && $no_join_milage==0 && $cfg['milage_use'] == "1") ? $milage=$cfg['milage_join'] : $milage=0;
		$milage1=($cfg['milage_recom1']>0 && $cfg['recom_first_order1'] != 'Y' && $cfg['milage_use'] == "1") ? $milage1=$cfg['milage_recom1'] : $milage1=0;
		$milage2=($cfg['milage_recom2']>0 && $cfg['recom_first_order2'] != 'Y' && $cfg['milage_use'] == "1") ? $milage2=$cfg['milage_recom2'] : $milage2=0;

		$member = $pdo->assoc("select * from $tbl[member] where member_id='$member_id'");
		if($milage>0) {
			ctrlMilage('+', 1, $milage, $member, '');
			$member['milage'] = $milage;
		}

		if(($milage1>0 || $milage2>0) && $_use['recom_member']=="B") {

			$rmem=get_info($tbl['member'],"member_id",$recom_member);
			if($rmem['no']) {
				if($milage1>0) {
					ctrlMilage("+",4,$milage1,$member,$recom_member);
				}
				if($milage2>0) {
					if($member['level'] > 9) {
						$member=array();
						$member['member_id']=$member_id;
						$member['name']=$name;
						$member['no']=$no;
						$member['milage']=0;
					}
					ctrlMilage("+",5,$milage2,$rmem,$member_id);
				}
			}
		}

		// 가입 축하 메일
		if($unique != 1) {
			$mail_case = 1;
			include_once $engine_dir.'/_engine/include/mail.lib.php';
			sendMailContent($mail_case, $member_name, $to_mail);
		}

		// 가입 축하 SMS
		include_once $engine_dir."/_engine/sms/sms_module.php";
		$sms_replace['name']=$name;
		$sms_replace['member_id']=$member_id;
		$member['member_id'] = $member_id;
		if($sms=="Y") SMS_send_case(1,$cell);

		if ($limit == 'Y' || ($_POST['member_type']==2 && $cfg['use_biz_member']=="Y")) { // 관리자 가입 승인 요청 알림
            SMS_send_case(40);
        } else { // 관리자 회원 가입 알림
            SMS_send_case(11);
        }

		$tmp=get_info($tbl['member'],"member_id",$member_id);

		// 회원가입 발급쿠폰
		$today=date("Y-m-d");
		$cpn_sql = ($_SESSION['is_wisaapp'] == true) ? " and down_type in ('C','E')" : " and down_type='C'";
		$res = $pdo->iterator("select * from `$tbl[coupon]` where (`rdate_type`=1 or (`rdate_type`='2' and `rstart_date`<='$today' and `rfinish_date`>='$today')) $cpn_sql");
        foreach ($res as $cpn) {
			if(putCoupon($cpn, $tmp) == true) {
				$pdo->query("update `$tbl[coupon]` set `down_hit`=`down_hit`+1 where `no`='$cpn[no]'");
			}
		}

		// 회원가입시 지급되는 로그인 쿠폰
		putLoginCoupon($tmp, 'join');

		if(is_object($erpListener)) {
			$erpListener->setChangedMember($tmp['member_id']);
		}

		if ($scfg->comp('use_kcb', 'Y')) {
			require_once __ENGINE_DIR__ . '/_engine/member/kcb/lib.php';
			setMemberCert();
		}
		unset($_SESSION['ipin_res']);

		// 로그인 처리
		if($unique == 1) {
			$key = md5(rand(1000,9999).time());
			$pdo->query("update $tbl[member] set reg_email='W', reg_code='$key' where no='$no'");


			$mail_case = 13;
			include_once $engine_dir.'/_engine/include/mail.lib.php';
			$r = sendMailContent($mail_case, $name, $email);

			if(!$r){
				$mail_case = 6;
				$mail_title[6] = "[{쇼핑몰이름}] {$name}님, 가입해 주셔서 감사합니다.";
				$content1 = "
					{$name}님 가입해 주셔서 감사드립니다.<br /><br />
					본 메일에 포함된 하단 링크를 클릭하시면 모든 가입절차가 승인완료되어 정상적으로 사이트 이용이 가능합니다.<br />
					가입승인은 신청 이후 한시간 이내에 받으셔야 합니다.<br /><br />
					<a href='$root_url/main/exec.php?exec_file=member/join_finish.exe.php&key=$key' target='_blank'>가입승인 완료하기</a>
				";
				$_mstr['메일내용']=$content1;
				$_mstr['수신거부링크']='{수신거부링크}';
				$r = sendMailContent($mail_case, $data['name'], $email);
			}

			msg(sprintf(__lang_member_info_joinCompleted__, $email), $root_url, 'parent');
		} elseif($member_type==2 && $cfg['use_biz_member']=="Y") {
			if($unique == 2) {
				$pdo->query("update $tbl[member] set reg_sms='Y' where no='$no'");
			}

			msg(__lang_member_info_joinProgress__, $root_url,"parent"); // 가입 완료 페이지
		}
		else {
			if($unique == 2) {
				$pdo->query("update $tbl[member] set reg_sms='Y' where no='$no'");
			}

			$ems="";
			$_SESSION['member_no']=$tmp['no'];
			$_SESSION['m_member_id']=$tmp['member_id'];
			$_SESSION['sns_type']=$sns_type;
			$_SESSION['just_join']=1;

			if($_SESSION['guest_no']) {
				$pdo->query("update `$tbl[cart]` set `member_no`='$tmp[no]',`guest_no`='' where `guest_no`='$_SESSION[guest_no]'");
				$_SESSION['guest_no']="";
			}

			loadPlugIn('member_join_finish');

			// 앱에서 접근여부 판단 후 아이디/패스워드 전달
			$cookie_time = $now+31536000;

			if((strpos($_SERVER['HTTP_USER_AGENT'],'iPhone') > -1 && strpos($_SERVER['HTTP_USER_AGENT'],'Safari') === false)){
				$_js_data = urlencode(stripslashes(json_encode(array('func'=>'saveMinfo','param1'=>$member_id,'param2'=>$pwd[0],'param3'=>$root_url))));
				if($_COOKIE['wisamall_access_device'] == 'APP') echo "<script>window.location.href='wisamagic://event?json=".$_js_data."';</script>";

			}else if(strpos($_SERVER['HTTP_USER_AGENT'],'WISAAPP') > -1){
				echo "<script>try{window.wisa.saveMinfo('".$member_id."','".$pwd[0]."');}catch(e){}</script>";

			}
            if ($_SESSION['sns_login']['rURL'] && $sns_type) {
                $rURL = $_SESSION['sns_login']['rURL'];
                unset($_SESSION['sns_login']['rURL']);
            }

			if(!$target) $target = "parent";
			if(!$rURL) $rURL = $root_url."/member/join_step3.php";
			msg("", $rURL, $target);
		}
	}

?>