<?PHP

    use Wing\API\Naver\NaverSimplePay;

	checkBasic();
	include_once $engine_dir."/_engine/include/milage.lib.php";
	include_once $engine_dir."/_engine/include/shop.lib.php";
	include_once $engine_dir.'/_engine/sms/sms_module.php';

	$exec = $_POST['exec'];

	//예약내역 취소
	if($exec=='cancel') {
		$sno = addslashes($_POST['sno']);
		$type = numberOnly($_POST['type']);
		$schno = numberOnly($_POST['schno']);
		if($type==3) {//정기배송 주문상세에서 스케쥴 취소
			$bno=array();
			$cdate = $pdo->row("select `date` from $tbl[sbscr_schedule] where no='$schno'");
			$bno[] = $cdate;
		}else if($type==2) {// 정기배송 주문상세에서 선택주문서취소
			$bno = $_POST['bno'];
			$all_bno=count($bno);
			if($all_bno<1) msg("하나 이상의 예약을 선택하세요.");
		}else {// 정기배송 주문 상세에서 주문서 전체취소
			$bno=array();
			$res = $pdo->iterator("select * from $tbl[sbscr_schedule] where sbono='$sno'");
            foreach ($res as $data) {
				$bno[] = $data['date'];
			}
			$all_bno=count($bno);
			if($all_bno<1) msg("취소할 예약이 없습니다.");
		}
/*
		//결제 승인 전 - 상태확인
		$cancel_cnt=0;
		foreach($bno as $key=>$val) {
			$booking_now=$pdo->row("select ssp.no from `$tbl[sbscr_schedule]` as ss inner join $tbl[sbscr_schedule_product] as ssp on ss.no=ssp.schno where ss.`sbono`='$sno' and ss.`date`='$val'");
			if(!$booking_now) msg("주문생성 전 예약만 취소가능합니다.");
		}
*/
		// 취소
		foreach($bno as $key=>$val) {
			$sch = $pdo->iterator("select no from {$tbl['sbscr_schedule']} where sbono='$sno' and `date`='$val'");
            foreach ($sch as $schdata) {
				$sql="update `$tbl[sbscr_schedule_product]` set `stat`='13' where `sbono`='$sno' and schno='{$schdata['no']}' and ono=''";
				$r = $pdo->query($sql);
				if($r) {
					$ssdata = $pdo->assoc("select total_prc, prd_prc, dlv_prc from $tbl[sbscr_schedule] where no='$schno'");
					$cancel_total_prc += $ssdata['total_prc'];
					$cancel_prd_prc += $ssdata['prd_prc'];
					$cancel_dlv_prc += $ssdata['dlv_prc'];
				}
			}
		}

		//부킹테이블이 전체 취소 될 경우 subscription 상태 값 취소로 변경
		$stat_count = $pdo->row("select count(*) from `$tbl[sbscr_schedule_product]` where `stat`=13 and `sbono`='$sno'");
		$all_bk_count = $pdo->row("select count(*) from `$tbl[sbscr_schedule_product]` where `sbono`='$sno'");
		if($stat_count==$all_bk_count) {
			$sql="update `$tbl[sbscr]` set `stat`='13' where `sbono`='$sno'";
			$r = $pdo->query($sql);

			$sql="update `$tbl[sbscr_product]` set `stat`='13' where `sbono`='$sno'";
			$r = $pdo->query($sql);

            // 네이버페이 정기결제 해지
            $sbkey = $pdo->assoc("select * from {$tbl['subscription_key']} where ono='$sno'");
            if ($sbkey && $sbkey['pg'] == 'nsp') {
                $pay = new NaverSimplePay($scfg);
                $ret = $pay->recurrentExpire($sbkey['recurrentId']);
            } else {
                if (function_exists('expireBillKey') == true) {
                    expireBillKey($sbono);
                }
            }
		}

		$sql="update `$tbl[sbscr]` set `s_total_prc`='$cancel_total_prc', s_pay_prc='$cancel_total_prc', s_prd_prc='$cancel_prd_prc', s_dlv_prc='$cancel_dlv_prc' where `sbono`='$sno'";
		$r = $pdo->query($sql);

		$sb = $pdo->assoc("select no, sbono, buyer_name, buyer_cell from {$tbl['sbscr']} where sbono='$sno' and sms_send='Y'");
		if($sb['no'] > 0) {
			$sms_replace['buyer_name'] = stripslashes($sb['buyer_name']);
			$sms_replace['ono'] = $sb['sbono'];
			if($type == 1) {
				SMS_send_case(34, $sb['buyer_cell']);
			} else {
				SMS_send_case(35, $sb['buyer_cell']);
			}
		}

        // 네이버페이 정기결제 해지
        $sbkey = $pdo->assoc("select * from {$tbl['subscription_key']} where ono='$sno'");
        if ($sbkey && $sbkey['pg'] == 'nsp') {
            $pay = new NaverSimplePay($scfg);
            $ret = $pay->recurrentExpire($sbkey['recurrentId']);
        } else {
            if (function_exists('expireBillKey') == true) {
                expireBillKey($sbono);
            }
        }

		msg("예약이 취소 되었습니다.","reload","parent");
	}else if($exec=='delete') { //정기배송 리스트에서 선택삭제
        $rurl = 'reload';
        if ($_POST['ono']) {
            $_POST['check_pno'] = array($_POST['ono']);
            $rurl = 'popup';
        }
		$check_pno = $_POST['check_pno'];
		$all_ord=count($check_pno);
		if($all_ord<1) msg("하나 이상의 주문을 선택하세요");
		foreach($check_pno as $key=>$val) {
			$pdo->query("delete from `$tbl[sbscr_product]` where `sbono`='$val'");
			$pdo->query("delete from `$tbl[sbscr_schedule]` where `sbono`='$val'");
			$pdo->query("delete from `$tbl[sbscr_schedule_product]` where `sbono`='$val'");
			$pdo->query("delete from `$tbl[sbscr]` where `sbono`='$val'");

			delete_log("sbscr", "$val", "$val 정기배송 주문 삭제");
		}
		msg("삭제되었습니다", $rurl, 'parent');
	}else if($exec=='stop') {// 정기배송 주문상세에서 스케쥴 stop
		$sno = addslashes($_POST['sno']);
		$r = $pdo->query("update $tbl[sbscr_product] set `stop`='Y' where sbono='$sno'");
		if($r) {
			msg("해당 주문의 예약내역이 모두 중지되었습니다.","reload","parent");
		}
	} else if($exec == 'stat') {
		$ext1 = numberOnly($_POST['ext1']);
		$ext2 = numberOnly($_POST['ext2']);
		$check_pno = $_POST['check_pno'];
		if(count($check_pno) < 1) msg('변경할 주문번호를 선택해주세요.');

		$w = '';
		if($ext1 > 0) $w .= " and stat='$ext1'";

		$ono = '';
		foreach($check_pno as $val) {
			$val = addslashes($val);
			if($ono) $ono .= ',';
			$ono .= "'$val'";
		}

		$pdo->query("update {$tbl['sbscr']} set stat='$ext2' where sbono in ($ono) $w");
        if ($ext2 == 2) {
            $pdo->query("update {$tbl['sbscr']} set date2='$now' where sbono in ($ono) $w and date2=0");
        }
		$pdo->query("update {$tbl['sbscr_product']} set stat='$ext2' where sbono in ($ono) $w");
		$pdo->query("update {$tbl['sbscr_schedule_product']} set stat='$ext2' where sbono in ($ono) $w and ono=''");

        // 네이버페이 정기결제 해지
        if ($ext2 == '5') {
            $sbkey = $pdo->assoc("select * from {$tbl['subscription_key']} where ono='$sbono'");
            if ($sbkey && $sbkey['pg'] == 'nsp') {
                $pay = new NaverSimplePay($scfg);
                $ret = $pay->recurrentExpire($sbkey['recurrentId']);
            } else {
                expireBillKey($sbono);
            }
        }

		msg('주문상태가 변경되었습니다.', 'reload', 'parent');
	} else if ($exec == 'edit_addressee') {
        checkBlank($_POST['addressee_name'], '받는 분 이름을 입력해주세요.');
        checkBlank($_POST['addressee_zip'], '받는 분 우편번호를 입력해주세요.');
        checkBlank($_POST['addressee_addr1'], '받는 분 주소를 입력해주세요.');
        checkBlank($_POST['addressee_addr2'], '받는 분 상세주소를을 입력해주세요.');
        checkBlank($_POST['addressee_cell'], '받는 분 휴대폰번호를 입력해주세요.');

        $pdo->query("
            update {$tbl['sbscr']} set addressee_name=?, addressee_zip=?, addressee_addr1=?, addressee_addr2=?, addressee_cell=?
            where sbono=?
            ", array(
                $_POST['addressee_name'], $_POST['addressee_zip'], $_POST['addressee_addr1'], $_POST['addressee_addr2'], $_POST['addressee_cell'],
                $_POST['ono']
            )
        );
        msg('', 'reload', 'parent');
    }

?>