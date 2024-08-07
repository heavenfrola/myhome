<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  1:1 고객센터 작성
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	checkBasic();
	include_once $engine_dir."/_engine/include/shop.lib.php";
	include_once $engine_dir.'/_engine/include/wingPos.lib.php';
	include_once $engine_dir.'/_engine/include/milage.lib.php';
	include_once $engine_dir.'/_manage/manage2.lib.php';

	$cate1 = numberOnly($_POST['cate1']);
	$cate2 = numberOnly($_POST['cate2']);
	$name = trim($_POST['name']);
	$title = addslashes(strip_script(trim($_POST['title'])));
	$content = addslashes(strip_script(trim($_POST['content'])));
	$ono = addslashes(trim($_POST['ono']));
	$sbscr = addslashes(trim($_POST['sbscr']));
	$repay_no = $_POST['repay_no'];
	$phone = addslashes(trim($_POST['phone']));
	$email = addslashes(trim($_POST['email']));
	$reason = addslashes(trim($_POST['reason']));

	if(!$cate1 || !$cate2) {
		$cate1 = $cate2 = '0';
	}

	if(!$member['no']) {
		checkBlank($name, __lang_member_input_name__);
	}
	else {
		$name = $member['name'];
	}
	checkBlank($title, __lang_common_input_title__);
	checkBlank($content,__lang_common_input_content__);

    if (
        $cfg['usecap_to'] == "Y"
        && $cfg['captcha_key']
        && (
            ($member['no'] && $cfg['usecap_member_to']=="Y")
            || (!$member['no'] && $cfg['usecap_nonmember_to']=="Y")
        )
        && $member['level']!=1
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

	// 두번 등록방지
	$tmp = $pdo->assoc("select * from `$tbl[cs]` order by `reg_date` desc limit 1");
	if($tmp['member_no'] == $member['no'] && $tmp['title'] == stripslashes($title) && $tmp['content'] == stripslashes($content)) {
		msg(__lang_shop_error_duplicatePost__, 'reload', 'parent');
	}

	if($ono) {
		if($sbscr=='Y') {
			$ord=get_info($tbl['sbscr'],'sbono',$ono);
			$ord['ono'] = $ono;
		}else {
			$ord=get_info($tbl['order'],'ono',$ono);
		}
		if($ord['checkout']  == 'Y') {
			msg('네이버페이 주문의 문의 및 요청은 네이버페이에서 진행하실수 있습니다.');
		}
		if($ord['talkstore']  == 'Y') {
			msg('카카오톡 스토어 주문의 취소/반품 요청은 카카오톡 스토어에서 진행하실수 있습니다.');
		}

		if($ord['stat'] == 3 && $cate2 == 12) $cate2 = 14;
		if($ord['stat'] == 2 && $cate2 == 12) $cate2 = 14;
		if($ord['stat'] == 1 && $cate2 == 14) $cate2 = 12;

		checkBlank($ord['no'], __lang_mypage_input_orderdata__);

		// 취소 신청일 경우
		if($cate1==2 && $ord['stat']<10) {
			if($cfg['deny_decided_cancel'] != 'N' && $ord['stat'] == 5) {
				msg(__lang_mypage_error_order_decided__);
			}

			if(is_array($repay_no) ==  false) {
				if($cate2 == 12 || $cate2 == 14) {
					$repay_stat = '1, 2';
					if($cfg['deny_placeorder_cancel'] == 'N' || empty($cfg['deny_placeorder_cancel']) == true) $repay_stat .= ', 3';
				} else if($cate2 == 16) {
					$repay_stat = '4';
					if($cfg['deny_decided_cancel'] == 'N') $repay_stat .= ', 5';
				}
				if($repay_stat) {
					$repay_no = array();
                    $repay_milage = $repay_member_milage = array();
					$tres = $pdo->iterator("select no, total_milage, member_milage from $tbl[order_product] where ono='$ord[ono]' and stat in ($repay_stat)");
                    foreach ($tres as $tmp) {
						$repay_no[] = $tmp['no'];
                        $repay_milage[] = ($tmp['total_milage']-$tmp['member_milage']);
                        $repay_member_milage[] = $tmp['member_milage'];
					}
				}
			} else {
                $_repay_no = implode(',', $repay_no);
                $repay_milage = $repay_member_milage = array();
                $tres = $pdo->iterator("select no, total_milage, member_milage from {$tbl['order_product']} where ono='{$ord['ono']}' and no in ($_repay_no)");
                foreach ($tres as $tmp) {
                    $repay_milage[] = ($tmp['total_milage']-$tmp['member_milage']);
                    $repay_member_milage[] = $tmp['member_milage'];
                }
            }

			if($cate2 == 12 || $cate2 == 14) {
				$_tmp = implode(',', $repay_no);
				$cnt1 = $pdo->row("select count(*) from $tbl[order_product] where ono='$ord[ono]' and stat=1 and no in ($_tmp)");
				$cnt2 = $pdo->row("select count(*) from $tbl[order_product] where ono='$ord[ono]' and stat in (2, 3) and no in ($_tmp)");

				if($cnt1 > 0 && $cnt2 > 0) {
					msg(__lang_mypage_error_mixedstats1__);
				}
			}

			$exec = $_POST['exec'] = 'process';
			$stat = ($cate2 == 12 && $cfg['order_cancel_type_1'] == 'Y') ? 13 : $cate2;

            // 신용 카드 즉시 취소 가능 체크
            $duel_card_cancel = false;
            $total_prd_cnt = $pdo->row("select count(*) from {$tbl['order_product']} where ono=? and stat < 10", array($ono));
            if ($total_prd_cnt == count($repay_no)) {
                if ($cate2 == 14 && $scfg->comp('deny_placeorder_cancel', 'C') == true) {
                    $pay_type = $ord['pay_type'];
                    require __ENGINE_DIR__.'/_engine/order/order_paytype.exe.php';
                    if ($card_pg == 'dacom') unset($pg_version);
                    $cancel_path = __ENGINE_DIR__.'/_engine/card.'.$card_pg.'/'.$pg_version.'card_cancel.php';
                    if (strpos(file_get_contents($cancel_path), 'duel_card_cancel') > 0) { // 모듈별 개발 여부 체크
                        $cate2 = $stat = $auto_cancel_stat = 15;
                        $duel_card_cancel = true;
                    }
                }
            }

			$_POST['stat'] = $stat;
			$_POST['repay_no'] = $repay_no;
            if ($ord['milage_down'] == 'Y') {
                $_POST['repay_milage'] = $repay_milage;
                $_POST['repay_member_milage'] = $repay_member_milage;
            }
			if($stat == 13 || $stat == 15) {
				$_POST['cpn_no'] = $cpn_no = $pdo->row("select no from $tbl[coupon_download] where ono='$ono' and stype != 5"); // 전체상품 쿠폰만 조회
				$_POST['emoney_repay'] = $emoney_repay = $ord['emoney_prc'];
				$_POST['milage_repay'] = $milage_repay = $ord['milage_prc'];
				$_POST['repay_dlv_prc'] = $repay_dlv_prc = $ord['dlv_prc']-($ord['sale2_dlv']+$ord['sale4_dlv']);
				$_POST['total_repay_prc'] = $total_repay_prc = $ord['pay_prc'];
			}
            if ($stat == 15) {
                if ($duel_card_cancel == true) {
                    $_POST['pay_type'] = $ord['pay_type'];
                }
            }
			$is_counsel = true;

			// 취소 상품 금액 및 적립금
			/*
			if($stat%2 == 0) {
				$tres = $pdo->iterator("select * from $tbl[order_product] where ono='$ord[ono]' and no in (".implode(',', $repay_no).")");
                foreach ($tres as $tmp) {
					$repay_prc[] = $tmp['total_prc']-getOrderTotalSalePrc($tmp);
					$repay_milage[] = $tmp['total_milage']-$tmp['member_milage'];
					$repay_member_milage[] = $tmp['member_milage'];
				}
				$_POST['repay_prc'] = $repay_prc;
				$_POST['repay_milage'] = $repay_milage;
				$_POST['repay_member_milage'] = $repay_member_milage;
			}
			*/

			// 고객 취소 사유 선택
			if($_POST['reason']) {
			$is_reason = $pdo->row("select count(*) from {$tbl['claim_reasons']} where admin_only='N'");
				if($is_reason > 0) {
					checkBlank($reason, '요청 사유를 선택해주세요.');
				} else {
					$_POST['reason'] = $reason = '사용자 취소';
				}
			} else {
				$_POST['reason'] = $reason = '사용자 취소';
			}

            // 실제 카드 취소
            if ($duel_card_cancel == true) {
                $card = $pdo->assoc("select * from {$tbl['card']} where wm_ono=?", array($ono));

                $cno = $card['no'];
                $price = parsePrice($_POST['total_repay_prc']);
                $card_cancel_result = false;

                require $cancel_path;

                if ($card_cancel_result === false) {
                    msg('취소 모듈을 호출하지 못했습니다.');
                }
                if ($card_cancel_result != 'success') {
                    msg($card_cancel_result);
                }
            }

            // 주문서 취소 처리
			include $engine_dir.'/_manage/order/order_prd_stat.exe.php';
		}
	}
	// 파일업로드
	include_once $engine_dir."/_engine/include/file.lib.php";
	$updir = $data['updir'];
	$asql = "";

	addField($tbl['cs'], 'updir', 'varchar(50) not null default ""');
	addField($tbl['cs'], 'upfile1', 'varchar(100) not null default ""');
	addField($tbl['cs'], 'upfile2', 'varchar(100) not null default ""');
	addField($tbl['cs'], 'reason', 'varchar(200) not null default ""');

	for($ii=1; $ii<=2; $ii++) {
		$chg_file = "";
		// 파일 삭제 또는 덮어 쓰기
		if($updir && ($_POST['delfile'.$ii]=="Y" || $_FILES['upfile'.$ii]['tmp_name'])) {
			deletePrdImage($data,$ii,$ii);
			$up_filename = $width = $height = "";
			$chg_file = 1;
		}
		if($_FILES['upfile'.$ii]['tmp_name']) {
			// 파일업디렉토리
			if(!$updir) {
				$updir = $dir['upload']."/".$dir['qna']."/".date("Ym",$now)."/".date("d",$now);
				makeFullDir($updir);
				$asql.=" , `updir`='$updir'";
			}

			$up_filename = md5($ii+time()); // 새파일명
			$up_info = uploadFile($_FILES["upfile".$ii],$up_filename,$updir,"jpg|jpeg|gif|png|bmp|xls|xlsx|hwp|doc|pdf");
			$up_filename = $_up_filename[$ii] = $up_info[0];
			$chg_file = 1;
		}

		if($chg_file) $asql .= " , `upfile".$ii."`='".$up_filename."'";
	}

	$sql="INSERT INTO `$tbl[cs]` ( `member_no` , `member_id` , `name` , `ono` , `cate1` , `cate2` , `title` , `content` , phone, email, `reg_date` ,`reply_date`,`updir`, `upfile1`, `upfile2`, reason ) ";
	$sql.="VALUES ( '$member[no]', '$member[member_id]', '$name', '$ono', '$cate1', '$cate2', '$title', '$content', '$phone', '$email', '$now' , '', '$updir', '$_up_filename[1]', '$_up_filename[2]', '$reason')";

	switch($cfg['mypage_board']) {
		case 'board' :
			$no = $pdo->row("select max(no) from mari_board")+1;
			$sql = "insert into mari_board (no, ref, db, cate, member_no, member_id, member_level , name, title, content, reg_date, secret, ip, link1) values ('$no', '$no', '$cfg[mypage_board2]', '0', '$member[no]', '$member[member_id]', '$member[level]', '$name', '$title', '$content', '$now', 'Y', '$_SERVER[REMOTE_ADDR]', '$ono')";
		break;
		case 'qna' :
			addField($tbl['qna'], 'ono', 'varchar(30) not null default ""');
			$sql = "insert into $tbl[qna] (member_no, member_id, name, title, content, reg_date, secret, ono) values ('$member[no]', '$member[member_id]', '$name', '$title', '$content', '$now', 'Y', '$ono')";
		break;
	}

	$pdo->query($sql);

	$no = $pdo->lastInsertId();
	$editor_code = addslashes($_POST['editor_code']);
	$pdo->query("update {$tbl['neko']} set neko_id='counsel_$no' where neko_id='$editor_code'");

	if($member['level'] > 1) {
		if($cfg['1to1_scallback'] == 'Y') {
			include_once $engine_dir.'/_engine/sms/sms_module.php';
			$sms_replace['name'] = $name;
			$sms_replace['title'] = $title;
			$sms_replace['board_name'] = '1대1문의';
			$sms_replace['member_id'] = ($member['member_id']) ? $member['member_id'] : 'Guest';
			$board_type = 'counsel';
			SMS_send_case(17);
		}

		if($cfg['1to1_mcallback'] == 'Y') {
			$mail_case = 10;
			$member_name = $name;
			$board_name = '1대1문의 ';
			$title = '<p style="text-align:center;">'.$name.'님의 1대1 문의가 등록되었습니다.</p><p style="text-align:center;">'.$title.'</p>';
			include_once $engine_dir.'/_engine/include/mail.lib.php';
			$r = sendMailContent($mail_case, $name, $to_mail);
		}
	}

	msg("",$root_url."/mypage/counsel_step2.php","parent");

?>