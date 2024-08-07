<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  장바구니 처리
	' +----------------------------------------------------------------------------------------------+*/

	use Wing\API\Naver\Checkout;
    use Wing\API\Kakao\KakaoTalkPay;

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/shop.lib.php";
	include_once $engine_dir."/_engine/include/shop2.lib.php";
	include_once $engine_dir."/_engine/include/wingPos.lib.php";

	printAjaxHeader();

	checkBasic();

	$pno = $_POST['pno']; // 상품 hash 값
	$cno = numberOnly($_POST['cno']); // 체크 된 상품번호
	$wno = numberOnly($_POST['wno']); // 체크 된 위시리스트 번호
	$buy_ea = numberOnly($_POST['buy_ea']); // 주문 수량
	$m_buy_ea = numberOnly($_POST['m_buy_ea']); // 다중 구매시 주문 수량
	$opt_no = numberOnly($_POST['opt_no']); // 옵션의 개수
	for($i = 1; $i <= $opt_no; $i++) { // 세부 옵션
		${'option'.$i} = $_POST['option'.$i];
	}
	$rel_cart = $_REQUEST['rel_cart'];
	$multi_cart = $_REQUEST['multi_cart'];
	$set_pno = (int) $_POST['set_pno']; // 세트 상품 번호
	$exec = $_POST['exec']; // 실행 모드
	$sbscr = ($_POST['sbscr']=='Y' ? 'Y':'N'); // 일반,정기배송
	$from_ajax = $_POST['from_ajax']; // ajax 를 통한 실행 여부
	$next = $_POST['next']; // 처리 후 이동 방법
	$mwhere = mwhere(); // 현재 회원의 where 쿼리
	if(!is_array($cstr)) $cstr = addslashes($_POST['cstr']); // 옵션 추가스트링


	if($exec == 'checkout' || $exec == 'paycoCart' || $exec == 'talkpay') {
		if(is_array($cno)) { // 선택한 장바구니 상품만 체크아웃 주문
			foreach($cno as $val) {
				$_recent_no .= ",".numberOnly($val);
			}
			$_recent_no = preg_replace('/^,/', '', $_recent_no);
		}

		$_chk_dlv_alone = $pdo->assoc("select sum(if(p.dlv_alone='Y', 1, 0)) as alone, count(*) as tot from {$tbl['cart']} c inner join {$tbl['product']} p on c.pno=p.no where c.no in ($_recent_no)");
		if ($_chk_dlv_alone['alone'] > 0 && $_chk_dlv_alone['tot'] > 1) {
			msg(__lang_shop_error_dlvalone1__,  $root_url.'/shop/cart.php');
		}
		switch($exec) {
			case 'checkout' : sendCheckout(); exit;
            case 'talkpay' : sendTalkPay(); exit;
			case 'paycoCart' : sendPayco(); exit;
		}
		exit;
	}

	if(is_array($_POST['multi_option_pno']) == true && count($_POST['multi_option_pno']) > 0) { // 다중옵션 구매 체크
		$multi_cart = true;
		$pno = array();
		$buy_ea = array();
		$main_pno = is_array($_POST['pno']) ? addslashes($_POST['pno'][0]) : addslashes($_POST['pno']);
		$_cart_idx = 0;

		if (!$set_pno && !in_array($main_pno, $_POST['multi_option_pno'])) { // 부속상품 구매시 메인 옵션 없을 경우
			$_cart_idx++;
			$pno[$_cart_idx] = $main_pno;
			$buy_ea[$_cart_idx] = numberOnly($_POST['buy_ea']);
			if(!$buy_ea[$_cart_idx]) $buy_ea[$_cart_idx] = 1;
		}

		for($i = 1; $i <= $_POST['opt_no']; $i++) { // 옵션정보 다시 생성
			unset($_POST['option'.$i]);
		}

        $_POST['prdcpn_no'] = array();
		foreach($_POST['multi_option_pno'] as $idx => $_pno) {
			$_cart_idx++;
			$pno[$_cart_idx] = $_pno;
			$buy_ea[$_cart_idx] = $m_buy_ea[$idx];

			$_option = explode('<split_option>', $_POST['multi_option_vals'][$idx]);
			foreach($_option as $okey => $oval) {
				if(!is_array($_POST['option'.($okey+1)])) $_POST['option'.($okey+1)] = array();
				$_oval = explode('::', $oval);
				$comp_no = numberOnly($_oval[4]);
				if($comp_no > 0) { // 부속상품 처리
					$opts = $pdo->row("select opts from erp_complex_option where complex_no='$comp_no'");
					$_item = explode('_', trim($opts, '_'));
					$opt_tmp = array();
					foreach($_item as $_iidx => $_ino) {
						$itemdata = $pdo->assoc("select i.iname, i.add_price, o.sort from {$tbl['product_option_item']} i inner join {$tbl['product_option_set']} o on i.opno=o.no where i.no='$_ino'");
						$_oval[4] = 0;
						$_oval[3] = $_ino;
						$_oval[1] = $itemdata['add_price'];
						$_oval[0] = stripslashes($itemdata['iname']);
						$opt_tmp[$itemdata['sort']] = implode('::', $_oval);
					}
					// 옵션 세트 정렬 순서대로 재 배열
					ksort($opt_tmp);
					$opt_tmp = array_values($opt_tmp);
					foreach($opt_tmp as $_iidx => $val) {
						$_POST['option'.($_iidx+1)][$_cart_idx] = $val;
					}
				} else {
					$_POST['option'.($okey+1)][$_cart_idx] = $oval;
				}
			}
			$_POST['prdcpn_no'][$_cart_idx] = $_POST['multi_option_prdcpn_no'][$idx];
		}
		$total_buy_ea = array_sum($buy_ea);
	}

	// 골라담기 세트 수량 체크
	if ($set_pno > 0) {
		$set = $pdo->assoc("select prd_type, set_rate, sell_prc from {$tbl['product']} where no='$set_pno'");
        if ($set['prd_type'] == '4') { // 일반세트상품 구성에 숨김상품이 있는 경우 구매제한
            if (isset($_POST['hideprd']) && $_POST['hideprd'] > 0) msg(__lang_shop_error_nopurchase__);
        }
		if ($set['prd_type'] == '5' || $set['prd_type'] == '6') {
			if($total_buy_ea == 0) {
				msg('세트 구성 상품을 선택해주세요.');
			}
			$set_rate = json_decode($set['set_rate'], true);
			$set_rate = array_keys($set_rate['data']);
			$set_min = min($set_rate);
			$set_max = max($set_rate);

			if(($set_max > 0 && $total_buy_ea > $set_max) || ($set_min > 0 && $total_buy_ea < $set_min)) {
				if($set_min == $set_max) {
					msg("골라담기 구성품을 {$set_min}개 선택해주세요.");
				} else {
					msg("골라담기 구성품을 {$set_min}~{$set_max}개 선택해주세요.");
				}
			}

            // 가격 체크
            $cart = new OrderCart();
            $cart->skip_dlv = 'Y';
            foreach ($pno as $key => $val) {
                $prd = checkPrd(addslashes($val), true);
                $prd['buy_ea'] = $buy_ea[$key];
                $cart->addCart($prd);
            }
            $cart->complete();
            $_pay_prc = $cart->getData('pay_prc');
            if ($_pay_prc < $set['sell_prc']) {
                msg(__lang_shop_error_set_price__);
            }
		}
	}

	// 같이 구매
	if($rel_cart || $multi_cart) {
		if(!is_array($pno)) msg (__lang_shop_error_insertCart__);

		//  장바구니 담기전 체크
		foreach($pno as $key=>$val) {
			if(!$val) continue;

			$prd = checkPrd(addslashes($val),true);

			if($cfg['use_prc_consult'] == 'Y' && $prd["sell_prc_consultation"]!="") {
				if(!$prd["sell_prc_consultation_msg"]) $prd["sell_prc_consultation_msg"] = "주문 전 협의가 필요한 상품입니다.";
				msg(php2java($prd["sell_prc_consultation_msg"]));
			}
		}
		/*
		if($sbscr == 'Y' && $cfg['sbscr_cart_type'] == 'S') {
			$sbscr_count = $pdo->row("select count(*) from `".$tbl['sbscr_cart']."` where 1 ".$mwhere);
			if($sbscr_count>0) {
				msg("장바구니에 정기배송 상품이 담겨있습니다.");
			}
		}
		*/

        //정기배송
		$pdo->query('start transaction');
		if($sbscr == 'Y') {
			$sbscrData = array();
			foreach($_POST as $key => $val) {
				if(preg_match('/sbscr/', $key)) {
					if($key=='sbscr_week') {
						$sbscr_week = "";
						foreach($val as $key2=>$val2) {
							$sbscr_week .= ($sbscr_week) ? "|".$val2:$val2 ;
						}
						$sbscrData[$key] = $sbscr_week;
					} else {
						$sbscrData[$key] = $val;
					}
				}
			}

			foreach($pno as $key => $val) {
				include_once $engine_dir."/_plugin/subScription/sbscr.lib.php";
				$ret = insertSbscrCart(addslashes($val), $key, $sbscrData);
			}
            $pdo->query('commit');

            header('Content-type: application/json;');
            exit(json_encode(array(
                'result' => 'OK',
                'cart_no' => $_recent_no
            )));
		} else {//일반
            $set_idx = ($set_pno > 0) ? createSetIdx() : '';

            if ($set_idx && $set['prd_type'] == '4') { // 상품 및 옵션 구성이 완전히 동일한 세트가 있을 경우 병합
                insertSetCart($set_idx, $set_pno, $pno, $buy_ea);
            } else { // 일반 장바구니
                foreach($pno as $key => $val) {
                    if($val) $ret = insertCart(addslashes($val), $key);
                }
            }


            if ($set_pno > 0) {
                ctrlPrdHit($set_pno, 'hit_cart', '+1');
            }
		}
        $pdo->query('commit');

		if($next==2) ckInterest($prd);
		else if ($next == 'checkout') sendCheckout();
        else if ($next == 'talkpay') sendTalkPay();
		else if ($next == 'payco') sendPayco();
		else askGoCart();
	}

	switch($exec) {
		case "add" :
			$ori_cno = numberOnly($_POST['ori_cno']);
			/*
			if($sbscr == 'Y' && $cfg['sbscr_cart_type']=='S') {
				$sbscr_count = $pdo->row("select count(*) from `".$tbl['sbscr_cart']."` where 1 ".$mwhere);
				if($sbscr_count>0) {
					msg("장바구니에 정기배송 상품이 담겨있습니다.");
				}
			}
			*/
			if($sbscr == 'Y') {
				$sbscrData = array();
				foreach($_POST as $key => $val) {
					if(preg_match('/sbscr/', $key)) {
						if($key=='sbscr_week') {
							$sbscr_week = "";
							foreach($val as $key2=>$val2) {
								$sbscr_week .= ($sbscr_week) ? "|".$val2:$val2 ;
							}
							$sbscrData[$key] = $sbscr_week;
						} else {
							$sbscrData[$key] = $val;
						}
					}
				}
				include_once $engine_dir."/_plugin/subScription/sbscr.lib.php";
				$ret = insertSbscrCart(addslashes($pno), null, $sbscrData);
                header('Content-type: application/json;');
				exit(json_encode(array(
                    'result' => 'OK',
                    'cart_no' => $_recent_no
                )));
			} else {
				$ret = insertCart($pno, null, $ori_cno);
			}
			if(count($ret['anx'])) { // 부속상품
				$_buy_ea = $buy_ea;
				unset($buy_ea);
				for($i = 1; $i <= $_POST['opt_no']; $i++) { // 옵션정보 다시 생성
					unset($_POST['option'.$i]);
				}
				$aaa = 1;
				foreach($ret['anx'] as $odx => $val) {
					$tmp = explode('::', $val);
					$comp_no = numberOnly($tmp[4]);
					$_buy_ea = numberOnly($tmp[5]);
					$hash = $pdo->row("select p.hash from erp_complex_option e inner join {$tbl['product']} p on e.pno=p.no where e.complex_no='$comp_no'");
					$_POST['option1'][$odx] = $val;
					$buy_ea[$odx] = $_buy_ea;
					insertCart($hash, $odx);
				}
			}

            // 페이스북 픽셀 API
            if ($scfg->comp('use_fb_conversion', 'Y') == true && $scfg->comp('fb_pixel_id') == true) {
                require_once __ENGINE_DIR__.'/_engine/promotion/fd_conversion_cart.inc.php';
            }

			if($from_ajax) {
                exit(endCart());
			}

			if ($next==2) {
				ckInterest($prd);
			} else if($next == 'checkout') {
				sendCheckout();
            } else if ($next == 'talkpay') {
                sendTalkPay();
			} else if($next == 'payco') {
				sendPayco();
			} else {
				askGoCart();
			}
			close();
			exit;
		break;

		case "from_wish" :
			if($from_ajax) {
				function encodeArgs($args, $s, $t) {
					foreach($args as $key => $val) {
						if(is_array($val)) {
							$args[$key] = encodeArgs($val, $s, $t);
						} else {
							$args[$key] = iconv($s, $t, $val);
						}
					}
					return $args;
				}
				$_POST = encodeArgs($_POST, 'utf-8', 'euc-kr');
			}

			//  장바구니 담기전 체크
			$wno = $_POST['wno'];
			$pno = $_POST['pno'];
			foreach($wno as $key=>$val){
				if(!$val) continue;

				$prd=checkPrd(addslashes($pno[$val]), true);
				if ($prd['prd_type'] == '4' || $prd['prd_type'] == '5' || $prd['prd_type'] == '6') {
					msg('세트상품은 직접 장바구니에 담을 수 없습니다.');
				}
				if($cfg['use_prc_consult'] == 'Y' && $prd["sell_prc_consultation"]!="") {
					if(!$prd["sell_prc_consultation_msg"]) $prd["sell_prc_consultation_msg"] = "주문 전 협의가 필요한 상품입니다.";
					msg(php2java($prd["sell_prc_consultation_msg"]));
				}
			}

			$cart_no=$pdo->row("select max(`no`) from `".$tbl['cart']."`");

			foreach($wno as $key=>$val){
				$option=$option_prc=$option_idx = '';
				$prd=checkPrd(addslashes($pno[$val]), true);
				$msg = getPrdBuyLevel($prd);
				if($msg) {
					msg(php2java($msg));
				}
				$chk = prdCheckStock($prd, 1, $val);
				$option = $chk['option'];
				$option_idx = $chk['option_idx'];
				$option_prc = $chk['option_prc'];
				$complex_no = $chk['complex_no'];
				$_cstr = addslashes($_POST['cstr'][$val]);
				if(!$complex_no && $cfg['use_dooson'] == 'Y' && $prd['ea_type'] == 1) {
					msg(__lang_shop_error_unregistOption__);
				}

                if($prd['max_ord'] > 0 || $prd['max_ord_mem'] > 0) {
                    checkMaxOrd($prd, 1);
                }

				$old=$pdo->assoc("select * from `{$tbl['cart']}` where `pno`='{$prd['parent']}' and `price_no`='$_price[0]' and `option`='$option'".$mwhere);
				if(!$old) {
					$cart_no++;
					$sql[]="INSERT INTO `".$tbl['cart']."` ( `no`, `pno` , `option` , `option_idx`, `complex_no`, `buy_ea` , `reg_date` , `member_no` , `guest_no` , `option_prc` , `etc` , `etc2` , `price_no`) VALUES ( '$cart_no','$prd[parent]', '$option', '$option_idx', '$complex_no', '1', '$now', '$member[no]', '$guest_no', '$option_prc','$_cstr','$sample_no', '0')";
					ctrlPrdHit($prd['parent'],"hit_cart","+1");
				} elseif($next == 'checkout' || $next == 'payco') {
					$pdo->query("update {$tbl['cart']} set `buy_ea`='{$buy_ea[$key]}' where `no`='{$old['no']}'");
				}

				//$pdo->query("update {$tbl['wish']} set `cart_date`='$now' where `no`='$val' limit 1");
			}

			for($ii=0; $ii<count($sql); $ii++){
				$pdo->query($sql[$ii]);
			}

			if($from_ajax) exit(endCart());

			if($next == 2) ckInterest($prd);
			else if($next == 'checkout') sendCheckout();
            else if($next == 'talkpay') sendTalkPay();
			else if($next == 'payco') sendPaco();
			else askGoCart();

			close();
			exit;
		break;

		case "update" :
			$total = count($cno);
			if($cno < 1) msg(__lang_shop_error_cannotChgEa__);

			for($ii=0; $ii < $total; $ii++) {
				$tmp_ea = numberOnly($buy_ea[$ii]);
				if($tmp_ea < 1) $tmp_ea = 1;

				$cart = get_info($tbl['cart'],"no",$cno[$ii]);
				$prd = get_info($tbl['product'],"no",$cart['pno']);

				if($prd['min_ord'] > 0 && $tmp_ea < $prd['min_ord']) {
					msg(sprintf(__lang_shop_error_minord__, addslashes($prd['name']), $prd['min_ord']));
				}

				checkMaxOrd($prd, $tmp_ea, $cno[$ii]);

				if($prd['ea_type'] == 1 && $cart['complex_no'] > 0) {
					$err = stockCheck($cart['complex_no'], $tmp_ea, $prd['name']);
					if($err) msg($err);
				}
				$pdo->query("update {$tbl['cart']} set  `buy_ea`='$tmp_ea' where `no`='{$cno[$ii]}' ".$mwhere);
			}
			if($order_submit=='Y') ckInterest($prd);
		break;

		case "delete" :
			$total=count($cno);
			if($cno<1) msg(__lang_mypage_error_rmProduct__);

			$del_tbl = ($sbscr == 'Y') ? $tbl['sbscr_cart']:$tbl['cart'];

            $_cno_all = implode(',', $cno);
            $res = $pdo->iterator("
                select p.hash, p.name, p.sell_prc, c.buy_ea
                from {$tbl['product']} p inner join $del_tbl c on c.pno=p.no
                where c.no in ($_cno_all) ".mwhere('c.')
            );

			for($ii=0; $ii<$total; $ii++) {
				$_cno = numberOnly($cno[$ii]);

				// 세트 구성 상품 삭제 시 전체 세트 구성 요소 같이 삭제
				$_cart = $pdo->assoc("select * from $del_tbl where no='$_cno'");
				if ($_cart['set_idx']) {
					$pdo->query("delete from $del_tbl where set_idx='{$_cart['set_idx']}'");
				} else {
					$pdo->query("delete from $del_tbl where no='$_cno' ".$mwhere);
				}
			}

            if ($from_ajax) {
                header('Content-type:application/json;');
                $cart = array();
                foreach ($res as $data) {
                    $data['sell_prc'] = parsePrice($data['sell_prc']);
                    $cart[] = $data;
                }
                exit(json_encode($cart));
            }
		break;

		case 'set_delete' :
			$set_idx = addslashes(trim($_POST['set_idx']));
			if ($set_idx) {
				$pdo->query("delete from $tbl_tbl where set_idx='$set_idx'");
			}
		break;

		case "truncate" :
			$del_tbl = ($sbscr == 'Y') ? $tbl['sbscr_cart']:$tbl['cart'];
			$pdo->query("delete from $del_tbl where 1 ".$mwhere);
		break;

	}

	$is_quickcart = numberOnly($_POST['is_quickcart']);
	if($is_quickcart > 0) {
		javac("parent.openQuickCart($is_quickcart, 'reload')");
		exit;
	}

	if($exec!="add") {
		msg($ems,"reload","parent");
	}

	function cartPrdCheck($buy_ea){
		global $tbl, $prd, $mwhere, $opt_no, $option, $option_prc, $option_idx, $cfg, $pdo;

		// 1회 최대 주문 한도
		$_pname="\\'".$prd['name']."\\'";

		if($prd['stat'] == 3) msg(sprintf(__lang_shop_error_soldout__, $_pname));
		if($prd['max_ord']>0 && $buy_ea>$prd['max_ord']) {
			msg(sprintf(__lang_shop_error_maxord__, $_pname, $prd['max_ord']));
		}

		// 한정 - 상품의 재고 파악
		$tmp_ea=$pdo->row("select sum(`buy_ea`) from {$tbl['cart']} where `pno`='{$prd['parent']}' ".$mwhere);
		if($prd['ea_type'] == '3' && $tmp_ea+$buy_ea>$prd['ea']) { // 한정일 경우
			if($prd['ea']==0) msg(sprintf(__lang_shop_error_soldout__, $_pname));
			else msg(sprintf(__lang_shop_error_maxord__, $_pname, $prd['ea']));
		}

		if(!$_GET['opt_no'] && !$_POST['opt_no']) $opt_no = $pdo->row("select count(*) from `$tbl[product_option_set]` where `stat`='2' and `pno`='$prd[parent]'");
		if($_POST['exec'] == 'from_wish') {
			$wish_opno = $pdo->row("select group_concat(no) from wm_product_option_set where pno='{$prd['parent']}'");
			$wish_opno = explode(',', $wish_opno);
		}

		$copt = array();
		$option_no_ck = array();
		if($opt_no > 0) {
			for($ii=1; $ii<=$opt_no; $ii++) {
				$_opt = ($_POST['exec'] == 'from_wish') ? $_POST['option'.$wish_opno[$ii-1]] : $_POST['option'.$ii];
				$_opt = explode('::', $_opt);

				$opt_info		= $pdo->assoc("select a.*, b.`no` as `ino`, b.`iname`, b.`add_price`, b.`ea` from {$tbl['product_option_set']} a inner join {$tbl['product_option_item']} b on b.`opno` = a.`no` where b.`no`='$_opt[3]'");
				$_OPT_NAME		= stripslashes($opt_info['name']);
				$_OPT_CAL		= $opt_info['how_cal'];
				$_OPT_NCS		= $opt_info['necessary']; // 옵션선택여부
				$_OPT_SELECTED	= stripslashes($opt_info['iname']);
				$option_no_ck[] = $opt_info['no'];

				if($prd['ea_type'] == 1 && $_OPT_NCS == 'C') { // wingPOS 복합재고 옵션
					$copt[] = $_opt[3];
				}

				if($_OPT_NCS == "Y" && !$_OPT_SELECTED) msg(sprintf(__lang_shop_select_prdopt__, $_pname, $_OPT_NAME));

				if(!$_OPT_SELECTED) continue;

				if($opt_info['ea_ck'] == 'Y' && $prd['ea_type'] == '3') {
					if(!$_OPT_SELECTED) msg(sprintf(__lang_shop_select_prdopt__, $_pname, $_OPT_NAME));
				}

				if($opt_info['ea_ck'] == "Y" && $prd['ea_type'] == 3){
					if($opt_info['ea'] < 1) msg(__lang_shop_error_soldoutOption__);
					if($opt_info['ea'] < $buy_ea) msg(sprintf(__lang_shop_error_maxord3__, $_pname, $_OPT_SELECTED, $opt_info['ea']));
				}

				$option.="<split_big>".$_OPT_NAME."<split_small>".$_OPT_SELECTED;
				$option_prc.="<split_big>".$opt_info['add_price']."<split_small>".$_OPT_CAL;
				$option_idx.="<split_big>".$opt_info['no']."<split_small>".$opt_info['ino'];
			}

			if($option) $option = substr($option,11);
			if($option_prc) $option_prc=substr($option_prc,11);
			if($option_idx) $option_idx=substr($option_idx,11);

		}

		# 필수옵션 체크
		$ness_type = ($prd['ea_type'] == 1) ? "'Y','C'" : "'Y'";
		$onec = $pdo->iterator("select `no`,`name` from {$tbl['product_option_set']} where `pno` = '{$prd['parent']}' and `necessary` in ($ness_type) order by `sort` asc");
        foreach ($onec as $necessary) {
			if(!in_array($necessary['no'],$option_no_ck)) msg (sprintf(__lang_shop_select_prdopt__, $prd['name'], $necessary['name']));
		}

		# 복합옵션 품절처리
		if(is_array($copt) && $prd['ea_type'] == 1) {
			sort($copt);
			if(!$copt[0]) $copt[0] = 0;
			if(!$copt[1]) $copt[1] = 0;

			$complex_no = $pdo->row("select `complex_no` from `erp_complex_option` where `pno`='{$prd['parent']}' and `opt1`='$copt[0]' and `opt2`='$copt[1]' and `del_yn`='N'");
			if($ret = stockCheck($complex_no, $buy_ea)) {
				msg($ret);
			}
		}

		return $complex_no;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  네이버 체크아웃
	' +----------------------------------------------------------------------------------------------+*/
	function sendCheckout() {
		global $cfg, $cwith, $_recent_no, $root_dir;

		$direct_no = $_recent_no;

		$checkout = new Checkout();
		$order_id = $checkout->order($direct_no);
	}

    function sendTalkPay()
    {
		global $scfg, $_recent_no;

        $talkpay = new KakaoTalkPay($scfg);
        $createSheet = $talkpay->order($_recent_no);

        header('Content-type:application/json');
        exit(json_encode($createSheet));
    }

	function sendPayco() {
		$cart_selected = $GLOBALS['_recent_no'];
		alert($cart_selected);
		msg("",$root_url."/main/exec.php?exec_file=order/order.exe.php&cart_selected=$cart_selected&is_payco=true");
	}

	function ckInterest($prd) {
		global $_use,$root_url,$cash_cate, $cTarget, $cfg, $_recent_no;

		$cwith = $_REQUEST['cwith'];
		if($_use['no_interest']=="Y") {
			if($prd['no_interest']=="Y") $cart_where=1;
			else $cart_where=2;
			$aq="?cart_where=".$cart_where;
		}

		if(($cfg['cart_direct_order'] == 'D' && (!$cwith || $cwith == 'false')) || $cfg['cart_direct_order'] == 'Y') {
			$aq .= ($aq) ? '&' : '?';
			$aq .= 'cart_selected='.$_recent_no;
		}

		if(!$cTarget) $cTarget = "parent";
		if($_POST['qd']) $cTarget = 'parent.parent';
		msg("",$root_url."/shop/order.php".$aq, $cTarget);
	}

	function askGoCart() {
		global $tbl, $root_url, $cTarget, $pdo, $_recent_no, $scfg;

		if(!$cTarget) $cTarget = "parent";
		if($_POST['qd']) $cTarget = 'parent.parent';

        if ($GLOBALS['next'] == 'talkpay_direct') {
            header('Content-type: application/json');
            exit(json_encode(array(
                'redirect_url' => $root_url.'/shop/order.php?pay_type=kakaopay&cart_selected='.$_recent_no
            )));
        }

		$cart_rows = number_format($pdo->row("select count(*) from {$tbl['cart']} where 1 ".mwhere()));
        if ($scfg->comp('use_set_product', 'Y') == true) { // 세트 사용 시 세트당 장바구니 1개로 표현
            $cart_rows -= $pdo->row("select count(*)-count(distinct set_idx) from {$tbl['cart']} where set_idx!='' ".mwhere());
        }

        if($_REQUEST['accept_json']=="Y"){
            exit(json_encode(array(
                'result' => 'OK',
                'cart_rows' => $cart_rows,
                'message' => str_replace("\n", "\\n", __lang_shop_info_cartOK__),
                'url' => $root_url.'/shop/cart.php'
            )));
        }
		?>
		<script type="text/javascript">
		var pr = <?=$cTarget?>;
		top.$('.front_cart_rows').html("<?=$cart_rows?>");
		if(parent.is_option_change) {
			if(parent.browser_type == 'mobile') {
				parent.location.reload();
			} else {
				parent.opener.location.reload();
				parent.close();
			}
		} else {
			if(parent.browser_type == 'mobile') {
				if(confirm('<?=str_replace("\n", "\\n", __lang_shop_info_cartOK__)?>')) {
					<?=$cTarget?>.location.href='<?=$root_url?>/shop/cart.php';
				} else {
					<?=$cTarget?>.openQuickCart(9, 'reload');
					//location.href='about:blank';
				}
			} else {
				parent.dialogConfirm(null, '<?=str_replace("\n", "\\n", __lang_shop_info_cartOK__)?>', {
					Ok: function() {
						pr.location.href='<?=$root_url?>/shop/cart.php';
					},
					Cancel: function() {
						pr.openQuickCart(9, 'reload');
						//location.href='about:blank';
						parent.dialogConfirmClose();
					}
				});
			}
		}
		</script>
		<?php
		exit();
	}

    function endCart()
    {
        global $pdo, $tbl;

        $cart_rows = number_format($pdo->row("select count(*) from {$tbl['cart']} where 1 ".mwhere()));

        header('Content-type: application/json;');
        exit(json_encode(array(
            'result' => 'OK',
            'cart_rows' => $cart_rows
        )));
    }

?>