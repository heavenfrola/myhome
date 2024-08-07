<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  주문 사은품 선택
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/milage.lib.php";

	if(!$gift_timing) {
		// 기본 체크
		checkBasic();
		checkBlank($_SESSION['last_order'], __lang_mypage_input_ono__);
		$ord = get_info($tbl['order'],'ono',$_SESSION['last_order']);

		// 주문 존재 여부
		if(!$ord[no]) msg(__lang_mypage_error_onoNotExist__);
		if($ord[order_gift]) msg(__lang_order_error_giftSelected__);

		// 사은품 체크
		if($cfg['order_gift_multi'] != 'Y') $cfg['order_gift_multi_ea'] = 1;
		$gift_ea = count($_POST['gift']);
		if($gift_ea > 1 && $cfg['order_gift_multi_ea'] > 0 && $gift_ea > $cfg['order_gift_multi_ea']) {
			msg(sprintf(__lang_order_gift_ea__, $cfg['order_gift_multi_ea']));
		}

		$ono = $ord['ono'];
	}

	$gift_pno = '';
	if(is_array($_POST['gift'])) {
		foreach($_POST['gift'] as $key => $gno) {
			$gno = numberOnly($gno);
			$gdata = $pdo->assoc("select complex_no, name from $tbl[product_gift] where no='$gno'");
			if($gdata['complex_no'] > 0) {
                $select = '';
                if ($cfg['use_partner_shop'] == "Y") {
                    $select = ", p.partner_no";
                }
				$gcdata = $pdo->assoc("
					select
						c.pno, c.complex_no, c.opts $select
						from erp_complex_option c
						inner join $tbl[product] p on p.no=c.pno
						where c.complex_no='$gdata[complex_no]' and (c.force_soldout='N' or (force_soldout='L' and qty > 0))
				");
				if ($cfg['use_partner_shop'] == "Y") {
					$asql = ", `partner_no`";
					$asql2 = ", '$gcdata[partner_no]'";
				}
				if(!$gcdata['pno']) continue;

				$gdata['pno'] = $gcdata['pno'];
				$gdata['name'] = addslashes($gdata['name']);
				if($gcdata['opts']) {
					$gdata['opts'] = str_replace('_', ',', trim($gcdata['opts'], '_'));
					$gdata['option'] = addslashes(getComplexOptionName($gcdata['opts']));
					$gdata['option_idx'] = $pdo->row("select group_concat(concat(opno, '<split_small>', no) separator '<split_big>') from $tbl[product_option_item] where no in ($gdata[opts])");
				}
				$ostat = $pdo->row("select stat from $tbl[order] where ono='$ono'");

				$psql  = "INSERT INTO `$tbl[order_product]` (`ono`, `pno`, `name`, `sell_prc`, `buy_ea`, `total_prc`, `option`, `option_idx`, `complex_no`, `prd_type`, `stat` $asql) ";
				$psql .= "values ('$ono', '$gdata[pno]', '$gdata[name]', '0', '1', '0', '$gdata[option]', '$gdata[option_idx]', '$gdata[complex_no]', '3', '$ostat' $asql2)";
				$pdo->query($psql);

				$opno = $pdo->lastInsertId();
				$order_product_no['gift_'.$gno] = $opno;
				$gift_product_no['gift_'.$gno] = $opno;
				$gift_pno .= '@_'.$gno;
			} else {
				$gift_pno .= '@'.$gno;
			}
		}
	}
	if($gift_pno) {
		$gift_pno = trim($gift_pno, '@');
		$pdo->query("update $tbl[order] set order_gift='$gift_pno' where ono='$ono'");
	}

	if(!$gift_timing) { // 배송완료 후 지급일 경우
		$gift_pno = ltrim(str_replace('_', '', $gift_pno), '@').'@';
		$pdo->query("update $tbl[order_payment] set pno=concat(pno, '$gift_pno') where ono='$ono' and type=0");

		if(count($gift_product_no) > 0) {
			orderStock($ono, 0.99, $ord['stat'], $gift_product_no);
			if(is_object($erpListener)) {
				$erpListener->setOrder($ono);
			}
		}

		makeOrderLog($ono, "order_gift_select.exe.php");

		msg(__lang_order_info_giftOK__, $root_url."/mypage/order_list.php", "parent");
	}

	makeOrderLog($ono, "order_gift_select.exe.php");

?>