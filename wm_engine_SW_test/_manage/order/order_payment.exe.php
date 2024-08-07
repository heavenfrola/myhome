<?PHP

    include_once __ENGINE_DIR__.'/_engine/include/wingPos.lib.php';

	printAjaxHeader();

	if($_POST['exec'] == 'rollback') {
		include_once $engine_dir.'/_engine/include/milage.lib.php';

		$no = numberOnly($_POST['no']);
		$payment = $pdo->assoc("select * from $tbl[order_payment] where no='$no'");
		if(!$payment['no']) exit('취소정보가 없습니다.');

		$ono = $payment['ono'];
		$amount = $payment['amount']; // 환불금액
		$ex_dlv_prc = $payment['ex_dlv_prc']; // 교환 반품 배송비
		$add_dlv_prc = $payment['add_dlv_prc']; // 추가 배송비
		$repay_emoney = $payment['repay_emoney'];
		$repay_milage = $payment['repay_milage'];
		$cpn_no = $payment['cpn_no'];
		$pno2 = explode('@', trim($payment['pno2'], '@'));

		if($cpn_no > 0) {
			/*
			$pdo->query("update $tbl[coupon_download] set ono='$ono', use_date='$now' where no='$cpn_no'");
			$pdo->query("update $tbl[order] set prd_nums=replace('@$cpn_no', '') where ono='$ono'");
			if(is_object($erpListener)) {
				$erpListener->setCoupon($cpn_no);
			}
			*/
		}

		foreach($pno2 as $_pno) {
			$prd = $pdo->assoc("select ostat from  $tbl[order_product] where no='$_pno'");
			$pdo->query("update $tbl[order_product] set stat='$prd[ostat]', repay_prc=0, repay_milage=0, repay_date=0 where no='$_pno'");
		}

		$pdo->query("update $tbl[order_payment] set stat=3 where no='$no'");

		$payment_no = createPayment(array(
			'ono' => $ono,
			'pno2' => $payment['pno2'],
			'type' => 4,
			'pay_type' => $payment['pay_type'],
			'amount' => $amount,
			'reason' => "상태복구 ($no)",
			'ex_dlv_prc' => $ex_dlv_prc,
			'ex_dlv_type' => $payment['ex_dlv_type'],
			'add_dlv_prc' => $add_dlv_prc,
			'dlv_prc' => $payment['dlv_prc'],
			'emoney_prc' => $repay_emoney,
			'milage_prc' => $repay_milage,
		), 2);

		ordStatLogw($ono, 100, null, null,
				array(
					'payment_no' => $no,
					'repay_no' => $payment['pno2'],
					'pno' => array(),
					'content' => date('Y-m-d H:i', $payment['reg_date']).' 취소건 복구'
				)
		);

		$ord = $pdo->assoc("select ono, member_no, member_id from $tbl[order] where ono='$ono'");
		if($ord['member_id']) $amember = $pdo->assoc("select * from $tbl[member] where member_id='$ord[member_id]'");

		if($amember['no'] && $repay_emoney > 0) {
			ctrlEmoney('-', 0, $repay_emoney, $amember, "[$ono] 취소상태에서 복구", false, $admin['admin_id'], $ono);
			$pdo->query("update $tbl[order] set emoney_prc=emoney_prc+'$repay_emoney' where ono='$ono'");
		}

		if($amember['no'] && $repay_milage > 0) {
			ctrlMilage('-', 0, $repay_milage, $amember, "[$ono] 취소상태에서 복구", false, $admin['admin_id'], $ono);
			$pdo->query("update $tbl[order] set milage_prc=milage_prc+'$repay_milage' where ono='$ono'");
		}

		getOrdStat2($ono, true);

		exit;
	}

	$no = numberOnly($_POST['no']);
	$payment = $pdo->assoc("select * from $tbl[order_payment] where no='$no'");
	if(!$payment) exit(json_encode(array('result'=>'error', 'msg'=>'존재하지 않는 결제정보입니다.')));
	if($payment['stat'] == 2) exit(json_encode(array('result'=>'error', 'msg'=>'이미 처리된 내역입니다.')));

	$type = $_order_payment_type[$payment['type']];

	$ono = $payment['ono'];
	$ostat = $pdo->row("select stat from $tbl[order] where ono='$ono'");
	if($ostat == 11) {
		msg('승인대기 복구 버튼을 이용해 주세요.');
	}

	$pno = str_replace('@', ',', preg_replace('/^@|@$/', '', $payment['pno']));
	if($pno) $where = "and (no in ($pno) or prd_type=3)";
	$res = $pdo->iterator("select no, stat from $tbl[order_product] where ono='$ono' and stat in (1, 11) $where");
    $rows = $res->rowCount();
	if($rows > 0) {
        // 재고 처리
        ob_start();
        $pdo->query('start transaction');
        foreach ($res as $prd) {
            if ($err = orderStock($ono, $prd['stat'], 2, $prd['no'])) {
                $pdo->query('rollback');
                ob_end_clean();

                exit(json_encode(array(
                    'msg' => $err
                )));;
            }
        }
        $pdo->query('commit');
        ob_end_clean();

		$pdo->query("update $tbl[order_product] set stat=2 where ono='$ono' and (no in ($pno) or prd_type=3) and stat in (1, 11)");
		$addmsg .= "\n$rows 건의 상품이 {$_order_stat[2]}상태로 변경되었습니다.";

		$ord = $pdo->assoc("select ono, stat, pay_prc, pay_type, buyer_name, buyer_cell, sms, addressee_addr1, addressee_addr2 from $tbl[order] where ono='$payment[ono]'");
		if($ord['sms'] == 'Y' && $ord['stat']  == '1' && $ord['pay_type'] == '2' && setSmsHistory($ord['ono'], 3)) {
			include_once $engine_dir.'/_engine/sms/sms_module.php';
			$sms_replace['buyer_name'] = $ord['buyer_name'];
			$sms_replace['ono'] = $ord['ono'];
			$sms_replace['pay_prc'] = number_format($ord['pay_prc']);
			$sms_replace['address'] = stripslashes($ord['addressee_addr1'].' '.$ord['addressee_addr2']);
			SMS_send_case(3, $ord['buyer_cell']);
		}

		$stat2 = $pdo->row("select group_concat(stat) from $tbl[order_product] where ono='$payment[ono]'");
		$stat2 = '@'.str_replace(',', '@', $stat2).'@';
		$pdo->query("update $tbl[order] set stat2='$stat2' where ono='$payment[ono]'");

		ordChgPart($payment['ono']);
		ordStatLogw($payment['ono'], 2);
		chgCashReceipt($ono);
        orderStock($payment['ono'], $ostat, 2);
	}

	$pdo->query("update $tbl[order_payment] set stat=2, confirm_id='$admin[admin_id]', confirm_date='$now' where no='$no'");

	exit(json_encode(array(
		'result' => 'success',
		'msg' => $type."확인이 완료되었습니다.".$addmsg
	)));

?>