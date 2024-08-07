<?PHP

	include_once $engine_dir.'/_engine/include/common.lib.php';

	$ono = addslashes(trim($_POST['ono']));
	$pay_type = $_POST['pay_type'];

    startOrderLog($ono, 'order_paytype.exe.php');

	// 결제방식 체크
	if(empty($pay_type)) msg(__lang_order_select_paytype__);
	if(empty($cfg['change_pay_type'])) msg(__lang_order_select_paytype__);
	$cfg['change_pay_type'] = explode('@', $cfg['change_pay_type']);
	if(in_array($pay_type, $cfg['change_pay_type']) == false) {
		msg(__lang_order_select_paytype__);
	}

	// 주문번호 체크 및 결제 결제폼에 필요한 변수 생성
	if(!$ono) msg(__lang_mypage_error_onoNotExist__);
	$ord = $pdo->assoc("select * from {$tbl['order']} where ono='$ono'");
	if($ord['stat'] != '1') msg(__lang_mypage_error_onoNotExist__);

	$buyer_name = stripslashes($ord['buyer_name']);
	$buyer_email = stripslashes($ord['buyer_email']);
	$buyer_phone = stripslashes($ord['buyer_phone']);
	$buyer_cell = stripslashes($ord['buyer_cell']);
	$addressee_name = stripslashes($ord['addressee_name']);
	$addressee_addr1 = stripslashes($ord['addressee_addr1']);
	$addressee_addr2 = stripslashes($ord['addressee_addr2']);
	$title = stripslashes($ord['title']);
	$title_code=str_replace(" ","_",md5($title));
	$title_code=str_replace(",","+",$title_code);
	$pay_prc = parsePrice($ord['pay_prc']);
	$dlv_prc = parsePrice($ord['dlv_prc']);
	$total_prc = parsePrice($ord['total_prc']);

	if($cfg['order_milage_paytype'] == '2' && $cfg['change_pay_recalc'] == 'Y' && $ord['milage_prc'] > 0) {
		msg('적립금이 사용된 주문서는\n결제수단을 변경할 수 없습니다.');
	}

	// 사용된 현금 전용 쿠폰 체크
	$cpn = $pdo->assoc("select * from {$tbl['coupon_download']} where ono='$ono'");

	// 변경 된 결제방식과 현재 설정 기준으로 주문 금액 재계산
	$asql = '';
	if($cfg['change_pay_recalc'] == 'Y') {
		if($cpn['no'] > 0 && $cpn['pay_type'] == 2) {
			$pdo->query("update {$tbl['coupon_download']} set ono='', use_date=0 where no='{$cpn['no']}'");
			unset($cpn);
		}

		$_POST['pay_type'] = $pay_type;
		$_POST['milage_prc'] = $ord['milage_prc'];
		$_POST['emoney_prc'] = $ord['emoney_prc'];
		$_POST['addressee_zip'] = $ord['addressee_zip'];
		$_POST['addressee_addr1'] = stripslashes($ord['addressee_addr1']);

		$cart = new OrderCart();
		$cart->setCoupon($cpn); // 기존 사용 중 쿠폰이 있을 경우
		$res = $pdo->iterator("
			select p.*, op.no as cno, op.`option`, op.option_prc, op.option_idx, op.complex_no, op.buy_ea
				from {$tbl['order_product']} op inner join {$tbl['product']} p on op.pno=p.no
			where op.ono='$ono' and op.stat=1
		");
        foreach ($res as $data) {
            $data['free_dlv'] = $data['free_delivery'];
			$data = shortCut($data);
            //옵션금액 존재시 추가한다
            if ($data['option_prc'] > 0) $data['sell_prc'] += $data['option_prc'];
			$cart->addCart($data);
		}
		$cart->complete();
		$pay_prc = $cart->getData('pay_prc');
		$dlv_prc = $cart->getData('dlv_prc');
		$total_prc = $cart->getData('total_order_price');
		$total_milage = $cart->getData('total_milage');
		$member_milage = $cart->getData('member_milage');
		$sum_prd_prc = $cart->getData('sum_prd_prc');

		$summary = array(
			'prd_prc' => parsePrice($sum_prd_prc, true),
			'dlv_prc' => parsePrice($dlv_prc, true),
			'pay_prc' => parsePrice($pay_prc, true),
			'total_milage' => parsePrice($total_milage, true),
		);
		foreach($_order_sales as $key => $val) {
			$summary[$key] = parsePrice($cart->getData($key), true);
		}

		// 실시간 결제 금액 출력
		if($_POST['exec'] == 'recalculation') {
			jsonReturn($summary);
		}

		// 주문상품별 할인 금액 및 적립금 업데이트
		if(fieldExist($tbl['order'], 'sale7') == false) {
			unset($_order_sales['sale7']);
		}
		while($obj = $cart->loopCart()) {
			$osql = '';
			foreach($_order_sales as $key => $val) {
				$tmp = $obj->getData($key);
				$osql .= ",$key='$tmp'";
			}
			$p_milage = $obj->getData('milage');
			$p_total_milage = $obj->getData('total_milage');
			$p_member_milage = $obj->getData('member_milage');

			$osql .= ", milage='$p_milage', total_milage='$p_total_milage', member_milage='$p_member_milage'";
			$osql = substr($osql, 1);
			$pdo->query("update {$tbl['order_product']} set $osql where no='{$obj->data['cno']}'");
		}
		$asql = ",pay_prc='$pay_prc', dlv_prc='$dlv_prc'";
		foreach($_order_sales as $key => $val) {
			$tmp = $cart->getData($key);
			$asql .= ", $key='$tmp'";
		}
		$asql .= ", total_milage='$total_milage', member_milage='$member_milage'";
	} else { // 결제 금액 재계산 하지 않음
		if($cpn['no'] > 0 && $cpn['pay_type'] == 2) {
			msg('현금 전용 쿠폰이 사용된 주문서는 결제수단을 변경할 수 없습니다.');
		}

		$summary = array(
			'prd_prc' => parsePrice($ord['prd_prc'], true),
			'dlv_prc' => parsePrice($ord['dlv_prc'], true),
			'pay_prc' => parsePrice($ord['pay_prc'], true),
			'total_milage' => parsePrice($ord['total_milage'], true),
		);
		foreach($_order_sales as $key => $val) {
			$summary[$key] = parsePrice($ord[$key], true);
		}

		// 실시간 결제 금액 출력
		if($_POST['exec'] == 'recalculation') {
			jsonReturn($summary);
		}
	}
	$pdo->query("update {$tbl['order']} set pay_type='$pay_type' $asql where ono='$ono'"); // 결제 수단 및 결제 금액 업데이트
	$pdo->query("update {$tbl['order']} set pay_type_changed='{$ord['pay_type']}' where ono='$ono'"); // 결제 수단 변경 이력

	// 지난 결제 중단 내역 삭제
	$pdo->query("delete from {$tbl['card']} where wm_ono='$ono' and stat=1 and tno=''");
	$pdo->query("delete from {$tbl['vbank']} where wm_ono='$ono' and stat=1 and tno=''");

	// 결제방식 변경 작성 시도
	if($ord['pay_type'] != $pay_type) {
		$comment = '주문자에 의해 결제방식이 「'.$_pay_type[$ord['pay_type']].'」 결제에서 「'.$_pay_type[$pay_type].'」결제로 변경되었습니다.';
		$pdo->query("insert into {$tbl['order_memo']} (ono, content, type, admin_no, admin_id, reg_date) values ('$ono', '$comment', '1', '0', 'system', '$now')");
		$pdo->query("update {$tbl['order']} set memo_cnt=memo_cnt+1 where ono='$ono'");
	}

	// 결제 시작
	include_once $engine_dir.'/_engine/order/order_paytype.exe.php';
	include_once $engine_dir."/_engine/card.{$card_pg}/{$pg_version}card_pay.php";

?>