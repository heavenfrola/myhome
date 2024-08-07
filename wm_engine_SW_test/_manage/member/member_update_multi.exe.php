<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  회원 일괄 처리
	' +----------------------------------------------------------------------------------------------+*/

    include_once __ENGINE_DIR__.'/_engine/include/member.lib.php';
    include_once __ENGINE_DIR__.'/_engine/sms/sms_module.php';

	checkBasic();

	@set_time_limit(0);
	@flush();
	@ob_flush();

	$exec = $_POST['exec'];
	$check_pno = numberOnly($_POST['check_pno']);

	if($exec=="delete") {
		$r=0;
		$w1=$w2=$w3=$w4=$w="";
		for($ii=0,$total=count($check_pno); $ii<$total; $ii++) {
			$no=$check_pno[$ii];

			$w1.=" or `member_no`='$no'";
			$w2.=" or `no`='$no'";
			$w3.=" or `ref`='$no'";

			$pdo->query("insert into $tbl[delete_log] (type, deleted, title, admin, deldate) values ('M', '$no', '$no 회원 삭제', '$admin[admin_id]', '$now')");
		}

		$w1=substr($w1,3);
		$w2=substr($w2,3);
		$w3=substr($w3,3);

		if($w1) {
			if($_POST['del_option1'] == '1') $pdo->query("delete from `$tbl[review]` where $w1"); // 상품평
			if($_POST['del_option2'] == '1') $pdo->query("delete from `$tbl[qna]` where $w1"); // 상품질문 - 답변도 지워야하는디
			if($_POST['del_option3'] == '1') $pdo->query("delete from `$tbl[order]` where $w1"); // 주문 내역
			if($_POST['del_option4'] == '1') $pdo->query("delete from `$tbl[cs]` where $w1"); // 상담 내역
			$pdo->query("delete from `$tbl[wish]` where $w1"); // 위시리스트
			$pdo->query("delete from `$tbl[sns_join]` where $w1"); // SNS통합회원
		}

		if($w2) {
            $mids = $pdo->iterator("select member_id from {$tbl['member']} where $w2");
            $member_ids = '';
            foreach ($mids as $mid) {
                $member_ids .= '\''.$mid['member_id'].'\',';
            }
            $member_ids = substr($member_ids, 0, -1);

            if ($member_ids) {
                $pdo->query("delete from {$tbl['order_memo']} where ono in ($member_ids) and type='2'"); // 회원메모 삭제
            }

			$pdo->query("delete from `$tbl[member]` where $w2");

			if($w1) {
				$pdo->query("delete from `$tbl[milage]` where $w1");
			}
		}

		if($w3 && $cfg[use_biz_member]=="Y") {
			$pdo->query("delete from `$tbl[biz_member]` where $w3");
		}

		msg($total." 명의 회원 정보가 성공적으로 삭제되었습니다","reload","parent");
	}
	elseif($exec=="point") {
		//
	}
	elseif($exec=="milage") {
		$mtitle = addslashes(trim($_POST['mtitle']));
		$mprc = numberOnly($_POST['mprc'], $cfg['currency_decimal']);

		checkBlank($mtitle,"사유를 입력해주세요.");
		checkBlank($mprc,"지급 금액을 입력해주세요.");
		include_once $engine_dir."/_engine/include/milage.lib.php";
		for($ii=0,$total=count($check_pno); $ii<$total; $ii++) {
			$no=$check_pno[$ii];
			$amember=get_info($tbl[member],"no",$no);
			ctrlMilage("+",3,$mprc,$amember,$mtitle,"",$admin[admin_id]);

            if (isset($_POST['milage_sms']) == true && $amember['cell']) {
                $sms_replace['name'] = stripslashes($amember['name']);
                $sms_replace['member_id'] = stripslashes($amember['member_id']);
                $sms_replace['milage_amount'] = parsePrice($mprc, true);
                $sms_replace['milage_expiration'] = ($cfg['milage_expire']) ? date('Y-m-d', strtotime("+ {$cfg['milage_expire']}")) : '무제한';
                $sms_replace['mtitle'] = $mtitle;

                SMS_send_case(39, $amember['cell']);
            }
		}
		$mprc2=number_format($mprc,$cfg['currency_decimal']);
		msg($total." 명의 회원에게 적립금(".$mprc2.$cfg['currency_type']." )을 지급하였습니다","reload","parent");
	}
	elseif($exec=="milage_minus") {
		$mtitle = addslashes(trim($_POST['mtitle']));
		$mprc = numberOnly($_POST['mprc'], $cfg['currency_decimal']);

		checkBlank($mtitle,"사유를 입력해주세요.");
		checkBlank($mprc,"반환 금액을 입력해주세요.");
		include $engine_dir."/_engine/include/milage.lib.php";
		for($ii=0,$total=count($check_pno); $ii<$total; $ii++) {
			$no=$check_pno[$ii];
			$amember=get_info($tbl[member],"no",$no);
			ctrlMilage("-",3,$mprc,$amember,$mtitle,"",$admin[admin_id]);
		}
		$mprc2=number_format($mprc,$cfg['currency_decimal']);
		msg($total." 명의 회원에게서 적립금(".$mprc2.$cfg['currency_type'].")을 반환하였습니다","reload","parent");
	}
	elseif($exec=="emoney") {

		if($cfg[emoney_use]!='Y') {
			msg("예치금을 사용하고 계시지 않습니다");
		}

		$etitle = addslashes(trim($_POST['etitle']));
		$eprc = numberOnly($_POST['eprc'], $cfg['currency_decimal']);

		checkBlank($etitle,"사유를 입력해주세요.");
		checkBlank($eprc,"지급 금액을 입력해주세요.");
		include_once $engine_dir."/_engine/include/milage.lib.php";
		for($ii=0,$total=count($check_pno); $ii<$total; $ii++) {
			$no=$check_pno[$ii];
			$amember=get_info($tbl[member],"no",$no);
			ctrlEmoney("+",3,$eprc,$amember,$etitle,"", $admin[admin_id]);
		}
		$eprc2=number_format($eprc,$cfg['currency_decimal']);
		msg($total." 명의 회원에게 예치금(".$eprc2.$cfg['currency_type'].")을 지급하였습니다","reload","parent");
	}
	elseif($exec=="emoney_minus") {

		if($cfg[emoney_use]!='Y') {
			msg("예치금을 사용하고 계시지 않습니다");
		}

		$etitle = addslashes(trim($_POST['etitle']));
		$mprc = numberOnly($_POST['eprc'], $cfg['currency_decimal']);

		checkBlank($etitle,"사유를 입력해주세요.");
		checkBlank($mprc,"반환 금액을 입력해주세요.");
		include $engine_dir."/_engine/include/milage.lib.php";
		for($ii=0,$total=count($check_pno); $ii<$total; $ii++) {
			$no=$check_pno[$ii];
			$amember=get_info($tbl[member],"no",$no);
			ctrlEmoney("-",3,$mprc,$amember,$etitle,"", $admin[admin_id]);
		}
		$mprc2=number_format($mprc,$cfg['currency_decimal']);
		msg($total." 명의 회원에게서 예치금(".$mprc2.$cfg['currency_type'].")을 반환하였습니다","reload","parent");
	}
	elseif($exec=="group") {
		$pdo->query("SET @member_chg_ref='manage';");
		$m_group = numberOnly($_POST['m_group']);

		checkBlank($m_group,"이동할 그룹을 입력해주세요.");
		$new_group=get_info($tbl[member_group],"no",$m_group);
		$w="";
		foreach($check_pno as $key=>$val) {
			$w.=" or `no`='$val'";
		}
		$w=trim(substr($w,4));
		if($w) {
			$sql="update `$tbl[member]` set `level`='$m_group' where $w";
			$pdo->query($sql);
			$total = $pdo->lastRowCount();

			if(is_object($erpListener)) {
				$res = $pdo->iterator("select member_id from $tbl[member] where $w");
                foreach ($res as $m) {
					$erpListener->setChangedMember($m['member_id']);
				}
			}
		}
		msg("$total 명의 회원을 $new_group[name] 그룹으로 이동하였습니다 ","reload","parent");

	}
	elseif($exec == 'coupon') {
		$cpnno = numberOnly($_POST['cpnno']);
		$cpmode = numberOnly($_POST['cpmode']);
		checkBlank($cpnno, '지급할 쿠폰을 선택해주세요.');
		$today = date('Y-m-d');
		$cpn = $pdo->assoc("select * from `$tbl[coupon]` where `no`='$cpnno' and (`rdate_type`=1 or (`rdate_type`=2 and `rstart_date` <= '$today' and `rfinish_date` >= '$today')) and `is_type`='A'");
		if(!$cpn['no']) msg('발급가능한 쿠폰이 아닙니다.');

        if (isset($_POST['use_cpn_sms']) == false) {
            define('__NO_CPN_SMS__', true);
        }

		if($cpmode == 3) $w = " and `withdraw`!='Y'";
		else if($cpmode == 2) {
			if(count($check_pno) < 1) msg('쿠폰을 지급할 회원을 선택해주세요.');
			$ckw = implode(',', $check_pno);
			$w = " and withdraw!='Y' and no in ($ckw)";
		}
		if($cpn['down_type'] == 'B') {
			if($cpn['down_gradeonly'] == 'Y') $w .= " and `level`='$cpn[down_grade]'";
			else $w .= " and `level` <= '$cpn[down_grade]'";
		}
		$q = "select x.no, x.name, x.member_id, x.level, x.cell, x.sms from $tbl[member] x where 1 $w order by x.no";

		if($cpmode == 4) {
			$query_string = unserialize(urldecode($_POST['query_string']));
			$_GET = array_merge($_GET, $query_string);
			extract($query_string);

			require_once 'member_list_search.inc.php';
			$q = $sql;
		}
		$msql = $pdo->iterator($q);

		$dn = 0;
		$err = 0;
        foreach ($msql as $mem) {
			if(putCoupon($cpn, $mem) == true){
				$dn++;
				$cpn['down_hit']++;
			} else {
				$err++;
			}
		}
		$pdo->query("update $tbl[coupon] set down_hit='$cpn[down_hit]' where no='$cpn[no]'");

		if($err > 0) {
			$errmsg = "다운로드 권한이 없는 $err 명의 회원을 제외한\\n";
		}
		msg($errmsg."$dn 명의 회원에게 $cpn[name] 쿠폰이 지급되었습니다.", 'reload', 'parent');
	}
	elseif($exec=="cancel_withdraw") { // 탈퇴요청회원 복구
		$i=0;
		foreach($check_pno as $key=>$val) {
			$sql="update `$tbl[member]` set `withdraw`='N', `withdraw_content`='' where `no`='$val'";
			$pdo->query($sql);
			$i++;

			if(is_object($erpListener)) {
				$erpListener->setChangedMember('', $val);
			}
		}
		msg($i."명의 탈퇴요청이 취소되었습니다.", "reload", "parent");
	}
	elseif($exec=="ch_withdraw") { // 탈퇴요청회원/일반회원 상태변경
		$withdraw = $_POST['withdraw'];
		foreach($check_pno as $key => $val) {
			if($withdraw!='Y') $sql="update `$tbl[member]` set `withdraw`='Y', `withdraw_content`=':::::$now' where `no`='$val'";
			else $sql="update `$tbl[member]` set `withdraw`='N', `withdraw_content`='' where `no`='$val'";

			if(is_object($erpListener)) {
				$erpListener->setChangedMember('', $val);
			}
		}
		$pdo->query($sql);
		msg("회원의 상태가 변경되었습니다.","reload","parent");
	}
	elseif($exec=="chg_BlackList"){ // 블랙리스트 회원으로 등록
		$blackList = ($_POST['blackList'] == '') ? 0 : 1;
		$black_reason = ($_POST['blackList'] == '') ? '' : addslashes(trim($_POST['black_reason']));
		$mid = $pdo->row("select member_id from {$tbl['member']} where no = '$check_pno'");
		$pdo->query("update `$tbl[member]` set `blacklist`='$blackList', `black_reason`='$black_reason' where `no`='$check_pno'");
		$pdo->query("insert into `$tbl[blacklist_log]` (`member_no`, `member_id`, `admin_id`, `blacklist`, `black_reason`, `log_date`) values('$check_pno', '$mid', '$admin[admin_id]', '$blackList', '$black_reason', '$now')");

		if(is_object($erpListener)) {
			$erpListener->setChangedMember('', $check_pno);
		}

		msg("회원의 상태가 변경되었습니다.","reload","parent");
	}
	elseif($exec=="chg_regEmail") {
        $asql = '';

        $amember = $pdo->assoc("select * from {$tbl['member']} where no=?", array($check_pno));
        if (isset($amember['email_reserve']) == true && $amember['email_reserve']) {
            $asql .= ", email='{$amember['email_reserve']}', email_reserve=''";
            if ($cfg['member_join_id_email'] == 'Y') {
                // 이메일 선점 체크
                $check = $pdo->row("select count(*) from {$tbl['member']} where email=? and no!=?", array(
                    $amember['email_reserve'], $amember['no']
                ));
                if ($check > 0) {
                    $pdo->query("update {$tbl['member']} set email_reserve='' where no=?", array($amember['no']));
                    msg('이미 사용중인 이메일입니다. 이메일 변경 요청이 취소됩니다.', 'reload', 'parent');
                }

                $asql .= ", member_id='{$amember['email_reserve']}'";
                updateMemberIdField($amember['email_reserve'], $amember['member_id']);
                $amember['member_id'] = $amember['email_reserve'];
            }
        }

		$pdo->query("update `$tbl[member]` set `reg_email`='Y' $asql where `no`='$check_pno'");

		msg(
            '회원의 인증상태가 변경되었습니다.',
            '?body=member@member_view.frm&mno='.$amember['no'].'&mid='.$amember['member_id'],
            'parent'
        );
	}
	elseif($exec == 'mchecker') {
		switch($_POST['cmode']) {
			case 3 :
				$w = " and `withdraw` != 'Y'";
				$sql = "select no from $tbl[member] x where 1 $w";
			break;
			case 2 :
				if(count($check_pno) < 1) msg('변경할 회원을 선택해 주세요.');
				$w = " and withdraw!='Y' and x.no in (".implode(',', $check_pno).")";
				$sql = "select no from $tbl[member] x where 1 $w";
			break;
			case 4 :
				$query_string = unserialize(urldecode($_POST['query_string']));
				$_GET = array_merge($_GET, $query_string);
				extract($query_string);

				require_once 'member_list_search.inc.php';
			break;
		}

		$mchecker = numberOnly($_POST['mchecker']);
		$cvalue = addslashes($_POST['cvalue']);
		$res = $pdo->iterator($sql);
        foreach ($res as $data) {
			$pdo->query("update `$tbl[member]` set checker_$mchecker='$cvalue' where no='$data[no]'");
		}
		$pdo->query("update `$tbl[member_checker]` set members=(select count(*) from $tbl[member] where checker_$mchecker='Y') where no='$mchecker'");

		msg('특별회원그룹이 지정/해제 되었습니다.', 'reload', 'parent');
	}
?>