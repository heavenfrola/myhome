<?PHP

	include_once $engine_dir.'/_engine/include/wingPos.lib.php';

	$multi = 1;

	if($_GET['exec'] == 'prd' || $body == 'order@order_exchg.frm') {
		printAjaxHeader();

		ob_start();
		$GLOBALS['pop'] = null;
		$GLOBALS['exec'] = 'prd';
		if($_GET['multi']) $multi = $_GET['multi'];
		if($_GET['mode'] != 'getEqualProducts') $disable = 'disable';
		if($_GET['opno']) $opno = preg_replace('/[^0-9,]/', '', $_GET['opno']);

		if(empty($opno) == true) return;

		$oprdcache = array();
		$sale5 = $prd_dlv_prc = $exception_prc = $non_prd_dlv = 0;
		$ores = $pdo->iterator("select * from $tbl[order_product] where no in ($opno)");
        foreach ($ores as $oprd) {
			$pno = $oprd['pno'];
			$ono = $oprd['ono'];

			// 동일 상품 추가용 옵션데이터 재구성
			if($_GET['mode'] == 'getEqualProducts') {
				$opt_idx_tmp = explode('<split_big>', $oprd['option_idx']);
				$opt_nm_tmp = explode('<split_big>', $oprd['option']);
				foreach($opt_idx_tmp as $okey => $tmp) {
					$item_tmp = explode('<split_small>', $tmp);
					$name_tmp = explode('<split_small>', $opt_nm_tmp[$okey]);

					// option value 복제
					$_item = $pdo->assoc("select no, iname, add_price from {$tbl['product_option_item']} where no='$item_tmp[1]'");
					$_GET['option'.($okey+1)] = array(
						0 => ($_item['no'] > 0) ? sprintf('%s::%s::0::%s', $_item['iname'], $_item['add_price'], $_item['no']) : $name_tmp[1]
					);
				}
				$_GET['buy_ea'] = $oprd['buy_ea'];
			}

			include 'order_admin.exe.php';
            $multi++;

			$oprd['pay_prc'] = ($oprd['total_prc']-getOrderTotalSalePrc($oprd)+$oprd['prd_dlv_prc']);
			$prd_prc += $oprd['pay_prc']; // 총 상품 금액
			$rev_prc += $oprd['pay_prc']; // 교환 전 상품 금액

			$oprdcache[] = $oprd;
			if($oprd['ex_type']) $expay++;
			if($oprd['stat'] == 1) $nopay++;
			else $pay++;
			$all_stats[$oprd['stat']]++;

			$sale5 += $oprd['sale5'];
			$prd_dlv_prc +=  $oprd['prd_dlv_prc'];
			if(empty($oprd['prd_dlv_prc']) == true || (int)$oprd['prd_dlv_prc'] == 0) {
				$non_prd_dlv++; // 개별 배송비 대상이 아닌 상품 수
			}
		}
		$preload_products = ob_get_contents();
		ob_end_clean();

		if($_GET['exec'] == 'prd') {
			exit($preload_products);
		}

		return;
	}

	define('_mode_exchange_', true);

	$ono = addslashes(trim($_POST['parent']));
	$opno = addslashes(trim($_POST['opno']));
	$m = numberOnly($_POST['m']);
	$pno = numberOnly($_POST['pno']);
	$stat = numberOnly($_POST['stat']);
	$ex_pno = (empty($opno) == true) ? 'add' : '@'.str_replace(',', '@', $_POST['opno']).'@';
	$parents = $_POST['parent'];
	$sell_prc = numberOnly($_POST['sell_prc'], true);
	$buy_ea = numberOnly($_POST['buy_ea']);
	$ex_dlv_type = numberOnly($_POST['ex_dlv_type']);
	$ex_dlv_prc = numberOnly($_POST['ex_dlv_prc']);
	$milage_prc = numberOnly($_POST['milage_prc'], true); // 사용적립금
	$emoney_prc = numberOnly($_POST['emoney_prc'], true); // 사용예치금
	$pay_type = numberOnly($_POST['pay_type']);
	$bank = addslashes(trim($_POST['bank']));
	$bank_account = addslashes(trim($_POST['bank_account']));
	$bank_name = addslashes(trim($_POST['bank_name']));
	$input_bank = numberOnly($_POST['input_bank']);
	$total_repay_prc = str_replace(',', '', $_POST['total_repay_prc']);
	$cpn_sale = $cpn_sale_all = numberOnly($_POST['cpn_repay'], true);
	$new_opnos = array();
	$prd_disable = $_POST['prd_disable'];
	$milage = numberOnly($_POST['milage'], true); // 지급 적립금
	$member_milage = numberOnly($_POST['member_milage'], true); // 지급 회원 적립금
	$order_product_no = numberOnly($_POST['order_product_no']);
	$prd_dlv_prc = numberOnly($_POST['prd_dlv_prc'], true); // 개별 배송비

    startOrderLog($ono, 'order_exchg.exe.php'); // 주문 로그 작성

	$ord = $pdo->assoc("select * from $tbl[order] where ono='$ono'");
	$amember = $pdo->assoc("select * from $tbl[member] where no='$ord[member_no]'");

	$cnt = 0;
	foreach($pno as $key => $val) {
		if($prd_disable[$key] == 'disable') continue;
		$cnt++;
	}
	if(!$cnt) msg('상품을 선택해주세요.');
	if($ord['member_no'] < 1 && $milage_prc > 0) msg('비회원 주문은 적립금을 사용하실수 없습니다.');
	if($ord['member_no'] < 1 && $emoney_prc > 0) msg('비회원 주문은 예치금을 사용하실수 없습니다.');
	if($milage_prc > 0 && $milage_prc > $amember['milage']) msg('보유 적립금보다 사용 적립금이 많습니다.');
	if($emoney_prc > 0 && $emoney_prc > $amember['emoney']) msg('보유 예치금보다 사용 예치금이 많습니다.');

	if($total_repay_prc > 0) { // 추가입금
		$pay_type = $_POST['input_pay_type'];
		if($pay_type == 2) {
			if(!$input_bank) msg('입금 은행을 선택해 주세요.');
			$account = $pdo->assoc("select * from $tbl[bank_account] where no='$input_bank'");
			$bank = $account['bank'];
			$bank_account = $account['account'];
			$bank_name = $account['owner'];
		}
	}
	if($total_repay_prc < 0) { // 환불
		if($pay_type == 2) {
			if(!$bank) msg('환불 은행을 선택해 주세요.');
			if(!$bank_account) msg('환불 계좌번호를 입력해 주세요.');
			if(!$bank_name) msg('환불계좌의 예금주명을 입력해 주세요.');
		}
	}

	$add_dlv_prc = numberOnly($_POST['add_dlv_prc'], true);
	$add_dlv_mode = $_POST['add_dlv_mode'];
	if($_POST['add_dlv_prc'] == 1) {
		$add_dlv_prc = 0;
	} else {
		if($add_dlv_mode == '-') $add_dlv_prc *= -1;
	}

	if(!$_POST['reason']) {
		if($ex_pno == 'add') msg('상품 추가 사유를 선택해주세요.');
		msg('상품 교환 사유를 선택해주세요.');
	}

	$source = $pdo->assoc("select * from $tbl[order_product] where no in ($opno)");
    $ori_stat = $source['stat'];
	switch($ori_stat) {
		case 1 : $nstat = 13; break;
		case 2 : $nstat = 15; break;
		case 3 : $nstat = 15; break;
		default : $nstat = 19; break;
	}

	$is_disabled = 0;
	foreach($pno as $key => $val) {
		if($prd_disable[$key] == 'disable') $is_disabled++;
	}

	$t_milage = $t_m_milage = $total_prd_dlv_prc = 0;
	foreach($pno as $key => $val) {
		$pasql1 = $pasql2 = '';

		if($prd_disable[$key] == 'disable') {
			$total_prd_dlv_prc -= $prd_dlv_prc[$key]; // 개별 배송비 차감
			continue;
		}

		// 입력된 상품별 할인가격
		$total_sale_prc = 0;
		foreach($_order_sales as $fn => $fv) {
			${$fn} = $tmp = numberOnly($_POST[$fn][$key]);
			if($tmp != 0) {
				$pasql1 .= ", $fn";
				$pasql2 .= ", '$tmp'";
				$total_sale_prc += $tmp;
			}
		}

		$ea = $buy_ea[$key];
		$prc = $sell_prc[$key];
		$total_milage = ($milage[$key]+$member_milage[$key]);
		$mil = $milage[$key]/$ea;
		$mem_mil = $member_milage[$key];
		$multi = $m[$key];
		$total_prc = $prc*$ea;

		$prd = $pdo->assoc("select * from $tbl[product] where no='$val'");
		$prd = shortCut($prd);
		$prd['name'] = addslashes(strip_tags(stripslashes($prd['name'])));
        $prd['buy_ea'] = $ea;

		$opt = prdCheckStock($prd, $ea, $multi);
		$option			= addslashes($opt['option']);
		$option_prc		= $opt['option_prc'];
		$option_idx		= $opt['option_idx'];
		$complex_no		= $opt['complex_no'];

		if(!$title) $title = "$prd[name] ($ea)";

		if($cfg['use_partner_shop'] == 'Y') { // 입점사 정보 추가 및 상품 수수료 계산
			include_once $engine_dir.'/_engine/include/cart.class.php';
			$addCart = new OrderCart();
			if($_POST['sale5'][$key] > 0) { // 전체 쿠폰 할인 가 반영
				$addCart->setCoupon(array(
					'sale_type' => 'm',
					'sale_prc' => $_POST['sale5'][$key]
				), 'sale5');
			}
			if($_POST['sale7'][$key] > 0) { // 개별쿠폰 할인 가 반영
				$addCart->setCoupon(array(
					'sale_type' => 'm',
					'sale_prc' => $_POST['sale7'][$key]
				), 'sale7');
			}
			$addCart->addCart($prd);
			$addCart->complete();
			$fee_prc = $addCart->getData('fee_prc');

			$pasql1 .= ", partner_no, fee_rate, fee_prc, dlv_type";
			$pasql2 .= ", '$prd[partner_no]', '$prd[partner_rate]', '$fee_prc', '$prd[dlv_type]'";
		}

		if(empty($_REQUEST['exchange_same_prd_only']) == false) {
			$oprd = $pdo->assoc("select * from {$tbl['order_product']} where no='{$order_product_no[$key]}'");
			$pasql1 .= ", cpn_rate, cpn_fee";
			$pasql2 .= ", '{$oprd['cpn_rate']}', '{$oprd['cpn_fee']}'";
		}

		if($cfg['use_prd_dlvprc'] == 'Y') {
			$pasql1 .= ", prd_dlv_prc";
			$pasql2 .= ", '{$prd_dlv_prc[$key]}'";
			$total_prd_dlv_prc += $prd_dlv_prc[$key];
		}

        if ($scfg->comp('use_set_product', 'Y') == true) {
            $pasql1 .= ", set_idx, set_pno";
            $pasql2 .= ", '{$source['set_idx']}', '{$source['set_pno']}'";
        }

		$insert_qry[$key] = "insert into `$tbl[order_product]` ".
				  "(`ono`, `pno`, `name`, `sell_prc`, `milage`, `buy_ea`, `total_prc`, `total_milage`, member_milage, `option`, `option_prc`, `option_idx`, `complex_no`, `stat`, ex_pno, ex_type $pasql1) ".
				  " values ('$ono', '$prd[no]', '$prd[name]', '$prc', '$mil', '$ea', '$total_prc', '$total_milage', '$mem_mil', '$option', '$option_prc', '$option_idx', '$complex_no', '$stat', '$ex_pno', '$nstat' $pasql2)";

		$prd_prc += $prc;
		$t_milage += $total_milage;
		$t_m_milage += $mem_mil;

		// 입점사 개별 배송처리
		if($cfg['use_partner_shop'] == 'Y' && $cfg['use_partner_delivery'] == 'Y') {
			$partner_exchange = $prd['partner_no'];
		}
	}

	foreach($insert_qry as $key => $qry) {
		$pdo->query($qry);
		$insert_no = $pdo->lastInsertId();
		$new_opnos[] = $insert_no;

		if(!is_object($erpListener) && $prd['ea_type'] == 1) {
			$oprd = $pdo->assoc("select * from $tbl[order_product] where no='$insert_no'");
			orderStock($ono, 0.99, $stat, $oprd['no']);
		}
	}

	$prd_count = $pdo->row("select count(*) from $tbl[order_product] where ono='$ono'");
	if($prd_count > 1) $title .= " 外 ".($prd_count-1);

    if ($opno) {
        if (!is_object($erpListener)) {
            $res = $pdo->iterator("select o.* from $tbl[order_product] o inner join $tbl[product] p on o.pno=p.no where p.ea_type=1 and o.no in ($opno)");
            foreach ($res as $oprd) {
                orderStock($ono, $oprd['stat'], $nstat, $oprd['no']);
            }
        }

        $ssql = getOrderSalesField(null, '-');

        $pdo->query("update $tbl[order_product] set repay_prc=(total_prc - $ssql), repay_milage=total_milage where no in ($opno) and repay_prc=0");
        $pdo->query("update $tbl[order_product] set ostat=stat, stat='$nstat', repay_date='$now', ex_pno='pno2' where no in ($opno)");
        if ($cfg['use_prd_dlvprc'] == 'Y') { // 원 상품의 개별 배송비 취소
            $pdo->query("update $tbl[order_product] set repay_prd_dlv_prc=prd_dlv_prc where no in ($opno)");
        }

        $cancel_sale5 = $pdo->row("select sum(sale5) from $tbl[order_product] where no in ($opno)");
    }

	include_once $engine_dir.'/_engine/include/milage.lib.php';
	if($emoney_prc > 0) {
		ctrlEmoney('-', 3, $emoney_prc, $amember, "[$ono] 상품교환시 사용", false, $admin['admin_id'], $ono);
	}
	if($milage_prc > 0) {
		ctrlMilage('-', 3, $milage_prc, $amember, "[$ono] 상품교환시 사용", false, $admin['admin_id'], $ono);
	}

	if($total_repay_prc < 0) { // 환불
		if($pay_type == 3) {
			ctrlMilage('+', 3, abs($total_repay_prc), $amember, "[$ono] 교환차액 ".$opno, false, $admin['admin_id'], $ono);
		}
		if($pay_type == 6) {
			ctrlEmoney('+', 3, abs($total_repay_prc), $amember, "[$ono] 교환차액 ".$opno, false, $admin['admin_id'], $ono);
		}
	}

	$ord = $pdo->assoc("select * from $tbl[order] where ono='$ono'");

	if(!$total_sale5_prc) $total_sale5_prc = 0;
	if(!$cancel_sale5) $cancel_sale5 = 0;

	// 전체 주문서 내 할인 종류별 금액 재계산
	$ssql = '';
	foreach($_order_sales as $fn => $fv) {
		$prc = $pdo->row("select sum($fn) from $tbl[order_product] where ono='$ono' and stat<11");
        if($prc > 0) {
			$ssql .= ", $fn='$prc'";
		}
	}

	$pdo->query("
		update $tbl[order] set
			title='$title',
			milage_prc=milage_prc+'$milage_prc',
			emoney_prc=emoney_prc+'$emoney_prc',
			repay_prc='$repay_prc',
			repay_date='$now'
			$ssql
		where ono='$ono'
	");

	if($_POST['cpn_repay'] != 'N') unset($_POST['cpn_repay']);

	$payment_no = createPayment(array(
		'ono' => $ono,
		'pno' => $new_opnos,
		'pno2' => explode(',', $opno),
		'pay_type' => $pay_type,
		'amount' => $total_repay_prc,
		'dlv_prc' => $total_prd_dlv_prc+$add_dlv_prc,
		'add_dlv_prc' => 0,
		'emoney_prc' => $emoney_prc,
		'milage_prc' => $milage_prc,
		'reason' => $_POST['reason'],
		'comment' => $_POST['comment'],
		'bank' => $bank,
		'bank_account' => $bank_account,
		'bank_name' => $bank_name,
		'ex_dlv_prc' => $ex_dlv_prc,
		'ex_dlv_type' => $POST['ex_dlv_type'],
		'cpn_no' => $_POST['cpn_repay'],
		'copytomemo' => $_POST['copytomemo']
	));

	if(isset($partner_exchange) == true) { // 입점사 배송비 정산 테이블 갱신
		$change_dlv_prc = ($add_dlv_prc+$ex_dlv_prc+$total_prd_dlv_prc);
		if($change_dlv_prc != 0) {
			$pdo->query("update $tbl[order_dlv_prc] set dlv_prc=dlv_prc+$change_dlv_prc where ono='$ono' and partner_no='$partner_exchange'");
		}
	}

	ordStatLogw($ono, 100, null, null,
		array(
			'payment_no' => $payment_no,
			'pno' => explode(',', $opno),
			'content' => $_exchange_before_stat[(($ex_pno == 'add') ? '0' : $nstat)].' 실행'
		)
	);

	ordChgPart($ono);

	// 배송완료에서 상태 변경시 적립금 처리
	$data = $pdo->assoc("select * from $tbl[order] where ono='$ono'");
	if($ord['stat'] == 5 && $data['stat'] != 5) {
		$ext = $data['stat'];
		$asql = '';
		orderMilageChg();
		if($asql) $pdo->query("update $tbl[order] set stat='$ext' $asql where ono='$ono'");
		reloadOrderMilage($ono);
	}


	// 관련 ERP 재고 다시 파악
	if(is_object($erpListener)) {
		$tpno = $pdo->row("select group_concat(distinct complex_no) from $tbl[order_product] where ono='$ono' and complex_no > 0");
		if($tpno) {
			$res = $pdo->iterator("select sku from erp_complex_option where complex_no in ($tpno)");
            foreach ($res as $data) {
				$erpListener->getStock($data['sku']);
			}
		}
	}

	// 현금영수증 재계산
	$cash = $pdo->assoc("select * from `$tbl[cash_receipt]` where `ono`='$ono'");
	if($cash['no']) {
		$ord = $pdo->assoc("select * from $tbl[order] where ono='$ono'");
		cashReceiptAuto($ord, 13);

		$amt1 = $ord['pay_prc'];
		if($amt1 > 0) {
			$amt2 = round($amt/1.1);
			$amt4 = $amt1-$amt2;
			$pdo->query("update `$tbl[cash_receipt]` set `stat`=1, `amt1`='$amt1', `amt2`='$amt2', amt4='$amt4', `prod_name`='$_title' where `ono`='$ono' limit 1");

			cashReceiptAuto($ord, 2);
		} else {
			$pdo->query("update `$tbl[cash_receipt]` set `stat`=3 where `ono`='$ono' and stat=1");
		}
	}

	if($ord['member_no'] > 0) {
		setMemOrd($ord['member_no'], 1);
	}

	makeOrderLog($ono, "order_exchg.exe.php");

	javac("parent.opener.location.reload();");

	msg('', 'popup', 'parent');

?>