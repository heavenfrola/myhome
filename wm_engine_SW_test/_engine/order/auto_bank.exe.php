<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  자동입금확인서비스
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/shop.lib.php";
	include_once $engine_dir."/_manage/manage2.lib.php";
	include_once $engine_dir.'/_engine/include/wingPos.lib.php';
	include_once $engine_dir.'/_engine/sms/sms_module.php';

	// 키코드
	$key_code = $_GET['key_code'];
	$exec = $_GET['exec'];
	$bkno = numberOnly($_GET['bkno']);
	$ono = addslashes(trim($_GET['ono']));

	if(!$key_code) {
		exit('NO KEY CODE');
	}
	if(!$cfg['bank_key_code']) {
		exit('KEY CODE NOT SET');
	}
	if($key_code!=$cfg['bank_key_code']) {
		exit('WRONG KEY CODE');
	}

	if($exec=='update') {
		if(!$ono) {
			exit('NO ONO');
		}
		if(!$bkno) {
			exit('NO BK NO');
		}

        if ($_GET['system'] == 'N') {
            $pdo->query("
                insert into {$tbl['order_memo']}
                (admin_id, ono, content, type, reg_date) values
                ('wingstore', ?, '윙스토어를 통해 강제 입금 매칭되었습니다.', '1', unix_timestamp())
             ", array(
                 $ono
            ));
        }

        if (preg_match('/^SS/', $ono) == true) { // subscription 일괄결제
            require_once __ENGINE_DIR__.'/_plugin/subScription/sbscr.lib.php';
            $ord = sbscrInput($ono);
            if ($ord == false) {
                return;
            }
        } else {
            $ord = $pdo->assoc("select stat, ono, buyer_name, buyer_cell, pay_prc from $tbl[order] where ono='$ono'");
            if($ord['stat'] > 1 && $ord['stat'] != 41) exit('OK');

            $wingpos_check = orderStock($ono, $ord['stat'], 2);
            if($wingpos_check) {
                exit('OK');
            } else {
                $sql="update `$tbl[order]` set `stat`='2', `bk_no`='$bkno', `date2`='".time()."' where `ono`='$ono'";
                $r=$pdo->query($sql);

                $add_q="";
                if($cfg[repay_part] == "Y") $add_q=" and `repay_date`=0";
                $pdo->query("update `$tbl[order_product]` set `stat`='2' where `ono`='$ono' and stat in (1, 41)".$add_q);

                $pdo->query("update $tbl[order_payment] set stat=2 where ono='$ono' and type=0");
                ordChgPart($ono);
                ordStatLogw($ono, 2, "Y");
            }
        }

		$sms_replace[buyer_name] = $ord['buyer_name'];
		$sms_replace[ono] = $ord['ono'];
		$sms_replace[pay_prc] = number_format($ord['pay_prc']);

		SMS_send_case(15, $ord['buyer_cell']);
		SMS_send_case(18);

		if($cfg['partner_sms_config'] == 1 || $cfg['partner_sms_config'] == 2) {
			partnerSmsSend($ord['ono'], 18);
		}

        // 현금영수증 자동 발급
        cashReceiptAuto($ono, 2);

		exit('OK');
	} else if ($exec == 'overlap') {
        $pdo->query("update {$tbl['order_product']} set stat=41 where ono=? and stat=1", array($ono));
        ordChgPart($ono);

        exit('OK');
    }

	$split="@wisamall@";

	// 미입금 내역 출력
	$sql="select * from `$tbl[order]` where `pay_type`='2' and (`stat`='1') and `pay_prc`>0 and `bk_no`='' order by `no`";
	$res=$pdo->iterator($sql);
	$r="";
    foreach ($res as $data) {
		if(!$data[bank_name]) {
			$data[bank_name]=$data[buyer_name];
		}
		$_bank=explode(" ", preg_replace('/ +/', ' ', $data['bank']));
		$_bank[1]=numberOnly($_bank[1]);
		$data[date1]=date("Ymd",$data[date1]);
		$r.=$data[ono].$split.parsePrice($data['pay_prc']).$split.$_bank[0].$split.$_bank[1].$split.$_bank[2].$split.$data[bank_name].$split.$data[date1]."<br>";
	}

    // 정기 배송 일괄결제 주문
    if ($cfg['use_sbscr'] == 'Y') {
        $res = $pdo->iterator("
            select sbono, s_pay_prc, bank, bank_name, buyer_name, date1
            from {$tbl['sbscr']} where stat=1 and pay_type=2 and s_pay_prc>0
        ");
        foreach ($res as $data) {
            if(!$data['bank_name']) {
                $data['bank_name'] = $data['buyer_name'];
            }
            $_bank = explode(' ', $data['bank']);
            $_bank[1] = (int) $_bank[1];
	    	$data['date1'] = date('Ymd', $data['date1']);
    		$r .= $data['sbono'].
                $split.parsePrice($data['s_pay_prc']).
                $split.$_bank[0].
                $split.$_bank[1].
                $split.$_bank[2].
                $split.$data['bank_name'].
                $split.$data['date1']."<br>";
        }
    }

	echo $r;

?>