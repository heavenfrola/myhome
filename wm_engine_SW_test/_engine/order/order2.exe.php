<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  주문서 작성페이지 2
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/milage.lib.php";
	include_once $engine_dir.'/_engine/include/wingPos.lib.php';

	if($cfg['use_sbscr'] == 'Y') {
		include_once $engine_dir."/_plugin/subScription/sbscr.lib.php";
	}

	loadPlugIn('order2_start');

	if($sbscr=='Y') {
		$ono = $sno;
		$tbl_where = " and sbono='$ono'";
		$tbl_cart = $tbl['sbscr_cart'];
		$tbl_order = $tbl['sbscr'];
		$tbl_order_product = $tbl['sbscr_product'];
		$sbscr_yn = 'Y';
	} else {
		$tbl_where = " and ono='$ono'";
		$tbl_cart = $tbl['cart'];
		$tbl_order = $tbl['order'];
		$tbl_order_product = $tbl['order_product'];
	}

    startOrderLog($ono, 'order2.exe.php');

	$ord = $pdo->assoc("select * from `$tbl_order` where 1 $tbl_where order by `no` desc limit 1");
	if($ord['pay_type'] != 2 && $ord['stat'] == 1) {
		$pdo->query("update $tbl[order] set stat=11 where ono='$ono' and stat=1");
		$pdo->query("update $tbl[order_product] set stat=11 where ono='$ono' and stat=1");
	}

	$pay_type=$ord['pay_type'];
	if(!$ono || !$pay_type) msg(__lang_order_info_wrongOrderdata__);
	$title=addslashes($ord['title']);
	$milage_prc=$ord['milage_prc'];
	$emoney_prc=$ord['emoney_prc'];
	$cart_where=$ord['cart_where'];
	$cart_selected = $ord['prd_nums'];
    if ($ord['member_no'] > 0 && !$member) {
        $member = $pdo->assoc("select * from {$tbl['member']} where no=?", array(
            $ord['member_no']
        ));
    }

	if($pay_type==1 || $pay_type==4 || $pay_type==5 || $pay_type==7 || $pay_type==10 || $pay_type==11 || $pay_type==12 || $pay_type==13 || $pay_type==14 || $pay_type==15 || $pay_type==16 || $pay_type==17 || $pay_type==18 || $pay_type==19 || $pay_type==20 || $pay_type==21 || $pay_type==22 || $pay_type==25 || $pay_type==27 || $pay_type==28){
		$stat = 2;
		$date2_q = ", `date2`='$now'";

		if($pay_type == 4) {
			$card_tbl = $tbl['vbank'];
			if($cflag == 'I' && $tamount == $ord['pay_prc']) $stat = 2;
			else {
				$stat = 1;
				$date2_q = '';
			}
		} else if($pay_type == 17 && $json->paymentCompletionYn != 'Y') { // 페이코 가상계좌
			$stat = 1;
			$date2_q = '';
			$card_tbl = $tbl['card'];
        } else if ($pay_type == '15') { // eContext 일본 결제
            if ($_POST['status'] == 'Sale') {
                $stat = '2';
            } else {
                $stat = '1';
            }
		} else {
			$card_tbl = $tbl['card'];
			if($ESCROW_YN == 'Y'){
				$stat = 1;
                $date2_q = ", `pay_type`='4'";
            }
		}

		$card = $pdo->assoc("select * from `$card_tbl` where `wm_ono`='$ono'");
		if(!$card[no]) {
			if ($pay_type != 4 && $card[stat] != 2) {
				msg(__lang_common_error_ilconnect__, $root_url, 'parent');
			}
		}
		if($card['member_no'] > 0 && $_SESSION['member_no'] < 1) {
			$_SESSION['member_no'] = $card['member_no'];
			$member = $pdo->assoc("select * from $tbl[member] where no='$card[member_no]'");
		}

		$real_order_mode=true;
		$cart_del_where="";
		$member_total_prc=$event_total_prc=0;
		if($sbscr=='Y') $sbscr_yn = 'Y';
		while($cart=cartList("","","","","","",$cart_where)) {
			$prd_log[] = $cart;
			$cart_del_where.=" or `no`='$cart[cno]'";
			$cart[option]=addslashes($cart[option]);
		}
		$cpn[no]=$card[cpn_no];
		$cpn_auth_code=$card[cpn_auth_code];
		if($cpn_auth_code) $offcpn=offCouponAuth($cpn_auth_code);
		$r=$pdo->query("update `$tbl_order` set `stat`='$stat' $date2_q where 1 $tbl_where");
		if($r) ordStatLogw($ono, $stat, "Y");
		else msg(__lang_order_error_update__);

		$stock_err = orderStock($ono, 11, $stat);
	} else {
		$stock_err = orderStock($ono, 0.99, $stat);
	}

    // 주문서 자동 취소
    if ($stock_err && $scfg->comp('use_erp_transaction', 'Y')) {
        if ($stat == '1') $auto_cancel_stat = '13';
        else if ($stat == '2') $auto_cancel_stat = '14';

        require __ENGINE_DIR__.'/_engine/order/order_auto_cancel.exe.php';
        exit;
    }

    if (!$ord['pay_type_changed']) {
        // 적립금 사용
        if($milage_prc>0) {
            ctrlMilage("-",11,$milage_prc,$member,$title);
            $total_milage=0;
            $no_prd_milage=true;
        }

        // 예치금 사용
        if($emoney_prc>0) {
            ctrlEmoney("-",11,$emoney_prc,$member,$title);
        }
    }

	if($pay_type != '23' && $pay_type != '27') {
        if (($ord['pay_type'] == '3' || $ord['pay_type'] == '6') || ($sbscr != 'Y' && $ord['pay_prc'] == 0)) {
            $stat = 2;
        }
		$pdo->query("update $tbl_order_product set stat='$stat' where stat in (1, 11, 31) $tbl_where");
		if($stat == 2) {
			$pdo->query("update $tbl[order_payment] set stat=2 where ono='$ono' and type=0");
		}
		ordChgPart($ono, true);
		if($sbscr=='Y' && $stat == 2) {
			$pdo->query("update $tbl[sbscr_schedule_product] set stat=2 where sbono='$ono' and stat in (1, 11)");
		}
	} else {
		$stat = ($pay_type == 2) ? 1 : 2;
		$pdo->query("update $tbl_order_product set stat='$stat' where 1 $tbl_where");
		sbscrChgPart($sno);
	}

	if($sbscr!='Y') {
		$ord = $pdo->assoc("select * from $tbl[order] where ono='$ono'");

		if(is_object($erpListener)) {
			$erpListener->setOrder($ono);
		}

		// 쿠폰사용
		if($cpn_auth_code) unset($cpn);
		if($cpn['no']) {
			$pdo->query("update `$tbl[coupon_download]` set `use_date`='$now',`ono`='$ono' where `no`='$cpn[no]'");
			if(is_object($erpListener)) {
				$erpListener->setCoupon($cpn['no']);
			}
		}

		// 오프라인 쿠폰 사용
		if($offcpn['no'] && ($ord['stat'] < 2 || $stat == 2)) {
			offCouponUse();
		}

		// 개별상품쿠폰 사용
		if($ord['sale7'] > 0) {
			$pres = $pdo->iterator("select no, prdcpn_no from $tbl[order_product] where ono='$ono' and sale7 > 0");
            foreach ($pres as $pdata) {
				$pdo->query("update $tbl[coupon_download] set ono='$ono', cart_no='$data[no]', use_date='$now' where ono='' and no in ($pdata[prdcpn_no])");
			}
		}
	} else {
		$ord = $pdo->assoc("select * from {$tbl['sbscr']} where sbono='$ono'");
        $ord['ono'] = $ord['sbono'];
        $ord['prd_prc'] = $ord['s_prd_prc'];
        $ord['dlv_prc'] = $ord['s_dlv_prc'];
        $ord['pay_prc'] = $ord['s_pay_prc'];
        $ord['sale8'] = $ord['s_sale_prc'];
        $ord['total_milage'] = $ord['s_total_milage'];
    }

	// 장바구니 비우기
	$cart_del_where=substr($cart_del_where,4);
    if ($sbscr != 'Y' && $scfg->comp('use_set_product', 'Y') == true) { // 세트 본체 주문 수 추가
        $res = $pdo->iterator("select set_pno from $tbl_cart where ($cart_del_where) and set_idx>0 group by set_idx");
        if (is_array($res) == true) {
            foreach ($res as $set) {
                ctrlPrdHit($set['set_pno'], 'hit_order', '+1');
            }
        }
    }
	if($cart_del_where) {
		$res = $pdo->iterator("select * from $tbl_cart where ($cart_del_where) ".mwhere());
        foreach ($res as $cart) {
			ctrlPrdHit($cart['pno'], 'hit_order', '+'.$cart['buy_ea']);
		}
		$pdo->query("delete from `$tbl_cart` where ($cart_del_where) ".mwhere());
	}

	// 회원일 경우 최종 주문일 추가 (주문금액, 주문수는 배송완료 후 증가)
	if($member['no']) $pdo->query("update `$tbl[member]` set last_order='$now' where `no`='$member[no]'");

	// 결제방식 변경
	if(isset($ord['pay_type_changed']) == true && (int)$ord['pay_type_changed'] > 0) {
		$order_product_no = explode(',', $pdo->row("select group_concat(no) from $tbl[order_product] where ono='$ono' and stat=1"));
		$payment_no = createPayment(array(
			'type' => 3,
			'ono' => $ono,
			'pno' => $order_product_no,
			'pay_type' => $pay_type,
			'amount' => 0,
			'dlv_prc' => 0,
			'emoney_prc' => 0,
			'milage_prc' => 0,
		), $stat);
		$pdo->query("update $tbl[order] set card_fail='' where ono='$ono'");
	}

	$_SESSION['last_order']=$ono;

	if($ord['pay_type'] == 4) $ord[bank] = $pdo->row("select concat(`bankname`,' ',`account`) from `$tbl[vbank]` where `wm_ono` = '$ord[ono]'");


    // 정기배송 당일 결제
    if ($sbscr == 'Y') {
        $schedule_f = $pdo->assoc("select no, date from {$tbl['sbscr_schedule']} where sbono=?", array(
            $ono
        ));
        if ($schedule_f['date'] == date('Y-m-d')) {
            $ret = comm(
                $root_url.'/main/exec.php',
                array(
                    'exec_file' => 'cron/auto_sbscr_pay.exe.php',
                    'schno' => $schedule_f['no'],
                )
            );
            makePGLog($ono, '정기주문 당일 결제', $ret);
        }
    }

	// 메일, SMS 발송
	include_once $engine_dir.'/_engine/sms/sms_module.php';
    $sms_replace['ono'] = $ono;
    $sms_replace['buyer_name'] = stripslashes($ord['buyer_name']);
    $sms_replace['pay_prc'] = parsePrice($ord['pay_prc'], true);
    $sms_replace['address'] = stripslashes($ord['addressee_addr1'].' '.$ord['addressee_addr2']);
    if ($ord['pay_type'] == 4 || $ord['pay_type'] == 2) {
        $sms_replace['account'] = stripslashes($ord['bank']);
    } else {
        $sms_replace['account'] = __lang_order_info_payed__;
    }
	if(setSmsHistory($ono, $stat+1)) {
		$use_ord_sms = $pdo->row("select count(*) from {$tbl['sms_case']} where `case` in (2, 13, 12, 32) and use_check='Y'");
		if($use_ord_sms > 0) {
			$sms_replace['pay_type'] = $_pay_type[$ord['pay_type']];
			$_title = strip_tags($pdo->row("select name from {$tbl['order_product']} where ono='{$ono}' order by no asc limit 1"));
			$prd_cnt = $pdo->row("select count(*) from {$tbl['order_product']} where ono='{$ono}'");
			$sms_replace['title'] = ($prd_cnt > 1) ? $_title.' 외 '.($prd_cnt-1).'건' : $_title;
			if($ord['sms'] == 'Y' || $ord['sms_send'] == 'Y') {
				if($sbscr == 'Y') {
					$sms_replace['first_dlv_date'] = $pdo->row("select date from {$tbl['sbscr_schedule']} where sbono='$ono' order by no asc limit 1");
					SMS_send_case(32, $ord['buyer_cell']);
				} else {
					SMS_send_case(2, $ord['buyer_cell']); // 고객 주문 SMS
				}
				if($ord['pay_type'] == 2 || $ord['pay_type'] == 4) {
                    if ($ord['bank']) {
    					SMS_send_case(13, $ord['buyer_cell']); // 무통장/가상계좌 주문 SMS
                    }
				}
			}
			SMS_send_case(12); // 운영자 주문 SMS
		}
	}
	if(in_array('2', explode('@', trim($cfg['email_checked'], '@'))) == true && $ord['mail_send'] == 'Y') {
		$mail_case = 2;
		include $engine_dir.'/_engine/include/mail.lib.php'; //관리자도 체크시 자동발송
		sendMailContent($mail_case, stripslashes($ord['buyer_name']), $ord['buyer_email']); //고객 이메일 발송
	} elseif (in_array('0', explode('@', trim($cfg['email_checked'], '@')))) {
        //본사 관리자 주문내역확인 이메일 발송
        $mail_case = 2;
        include $engine_dir.'/_engine/include/mail.lib.php'; //관리자만 메일 발송
    }
	partnerSmsSend($ord['ono'], '12'); // 입점사 주문 SMS 및 이메일

    // 페이스북 전환 API
    if ($scfg->comp('use_fb_conversion', 'Y') == true) {
        include __ENGINE_DIR__.'/_engine/promotion/fd_conversion.inc.php';
    }

	if($dacom_note_url == true || $danal_pay == 'Y' || $alipay_note_url == true || $new_kakao_pay == 'Y' || $pg_note_url == true) return;

?>
<form name="orderFinish" target="_parent" method="post" action="<?=$root_url?>/shop/order_finish.php">
<?php if($sbscr=='Y') { ?>
	<input type="hidden" name="sno" value="<?=$ono?>">
<?php } else { ?>
	<?php foreach($ord as $key=>$val){ ?>
	<input type="hidden" name="ord[<?=$key?>]" value="<?=$val?>">
	<?php } ?>
<?php } ?>
</form>
<script type='text/javascript'>
	document.orderFinish.submit();
</script>