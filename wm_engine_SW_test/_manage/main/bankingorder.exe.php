<?PHP

	// 크론 등록 이후부터는 크론에 의해서만 실행
	if($cfg['use_bankCancelCron'] == 'Y' && defined('__bank_cancel_cron__') == false) {
		return;
	}

	include_once $engine_dir."/_engine/include/milage.lib.php";
	include_once $engine_dir."/_engine/include/wingPos.lib.php";
	include_once $engine_dir."/_engine/sms/sms_module.php";

    $string_sales = getOrderSalesField('', '+');

	$cancel_cnt = 0;
	$cfg[banking_time]=numberOnly($cfg[banking_time]);
	if($cfg[banking_time]) {
		if($cfg['banking_time_std'] == 'time') {
			$del_date = strtotime("-$cfg[banking_time] days", strtotime(date('Y-m-d H:00:00')));
		} else {
			$del_date = strtotime("-".($cfg['banking_time']+1)." days", strtotime(date('Y-m-d 23:59:59')));
		}

		$pdo->query("START TRANSACTION");
		$ext=13;

        $asql = "";
		if($cfg['n_smart_store'] == 'Y') {
			$asql = " and smartstore!='Y'";
		}
		$qsql = (fieldExist($tbl['order'], 'sale7')) ? 'sale7' : '';

		$sql = "
            select no, ono, stat, pay_type, pay_prc, dlv_prc, milage_prc, emoney_prc,  title, sms, buyer_name, buyer_cell, member_no, total_milage, repay_milage, milage_recharge, milage_down $qsql
            from {$tbl['order']} where date1 < '$del_date' and stat='1' and checkout!='Y' {$asql}
        ";
		if($cfg['use_kakaoTalkStore'] == 'Y') {
			$sql .= " and talkstore!='Y'";
		}
		$res = $pdo->iterator($sql);
		if(!$res) return;
        foreach ($res as $data) {

			if($pdo->row("select count(*) from $tbl[order_product] where ono='$data[ono]' and ((stat between 2 and 6) or stat = 20)") > 0) continue;

			$asql="";
			if($data[milage_prc]>0 || $data[emoney_prc]>0) {
				$data['title'] = '주문 자동 취소';
				orderMilageChg(); // 취소되었을 경우 적립금, 예치금 재적립
			}

			orderStock($data['ono'], $data['stat'], 13); // 윙포스 재고복구

			$repay_no = $pdo->row("select group_concat(no) from $tbl[order_product] where ono='$data[ono]' and stat='1'");
			$cpnno = $pdo->row("select no from $tbl[coupon_download] where ono='$data[ono]'");
			if($cpnno > 0) {
				$pdo->query("update $tbl[coupon_download] set ono='', use_date='0' where no='$cpnno'");
				$pdo->query("update $tbl[order] set prd_nums=concat(prd_nums, '@', '$cpnno') where ono='$data[ono]'");
				ordStatLogw($data['ono'], 100, null, null, "주문 자동취소로 인한 사용쿠폰 복구 ($cpnno)");

				if(is_object($erpListener)) {
					$erpListener->setCoupon($cpnno);
				}
			}
			if($data['sale7'] > 0) {
				$pdo->query("update $tbl[coupon_download] set ono='', use_date=0, cart_no=0 where ono='$data[ono]'");
			}

			$pdo->query("update `$tbl[order_product]` set `stat`='13', repay_prc=total_prc-($string_sales), repay_date='$now' where `ono`='$data[ono]' and `stat`='1'");
			$stat2 = $pdo->row("select group_concat(stat) from $tbl[order_product] where ono='$data[ono]'");
			$stat2 = '@'.str_replace(',', '@', $stat2).'@';
			$pdo->query("update `$tbl[order]` set `stat`='13', stat2='$stat2', `ext_date`='$now', pay_prc=0, total_prc=0, dlv_prc=0, milage_prc=0, emoney_prc=0 $asql where `no`='$data[no]'");
			ordStatLogw($data[ono], 13, "Y");
			$cancel_cnt++;

			if($data['sms'] == 'Y') {
				$sms_replace = array(
					'buyer_name' => stripslashes($data['buyer_name']),
					'ono' => $data['ono'],
				);
				sms_send_case(26, $data['buyer_cell']);
			}

			// 남은 정상 상품 중 쿠폰 할인금액이 있다면 쿠폰 반환하지 않음
			if($cpnno > 0) {
				$left_sale5 = $pdo->row("select sum(sale5) from $tbl[order_product] where ono='$data[ono]' and stat between 1 and 5 and sale5 > 0");
				if($left_sale5 > 0) {
					$cpnno = 0;
				}
			}

            if ($data['pay_type'] == '2') chgCashReceipt($data['ono']); // 현금영수증 반환 처리

			createPayment(array(
				'ono' => $data['ono'],
				'pno2' => explode(',', $repay_no),
				'pay_type' => $data['pay_type'],
				'amount' => -($data['pay_prc']),
				'reason' => '입금기간 만료 자동 취소',
				'cpn_type' => 'cancel',
				'cpn_no' => $cpnno,
				'repay_milage' => $data['milage_prc'],
				'repay_emoney' => $data['emoney_prc'],
				'dlv_prc' => -($data['dlv_prc']),
			), 2);
		}

		$pdo->query("COMMIT");
	}

	$ext=$asql=$sql="";

?>