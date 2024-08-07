<?PHP

	function makeComplexKey($val) {
		if(is_array($val)) $val = implode('_', $val);
		$val = explode('_', preg_replace('/^_|_$/', '', $val));
		sort($val);
		$str = '_'.implode('_', $val).'_';
		if($str == '__') $str = '';
		return $str;
	}

	// 새로운 복합옵션 생성
	function createComplex($pno, $opts = null, $barcode = null, $bstock = 0, $reason = '', $force = 'N', $sku = null) {
		global $tbl, $cfg, $erpListener, $pdo;

		$opts = makeComplexKey($opts);
		$complex = $pdo->assoc("select `complex_no` from `erp_complex_option` where `pno`='$pno' and `opts`='$opts' and `del_yn`='N'");
		$complex_no = $complex['complex_no'];

		if($complex_no) {
			if($opts != $data['opts']) $pdo->query("update erp_complex_option set opts='$opts' where complex_no='$complex_no'");
			return $complex_no;
		} else {
			$auto_create_yn = ($barcode === true) ? 'Y' : 'N';
			$barcode = ($barcode === true) ? barcodeGen(null) : barcodeGen($barcode);
			$dreason = $reason ? $reason : '기초재고 등록';
			$ip = $_SERVER['REMOTE_ADDR'];
			$reg_user = $GLOBALS['admin']['admin_id'];
			$prd = $pdo->assoc("select seller_idx from $tbl[product] where no='$pno'");

			// 수동 바코드 삽입시 색상 사이즈코드 매칭
			if($cfg['use_dooson'] == 'Y' && $cfg['erp_interface_name'] == 'dooson' && !$sku) {
				$erpListener->connect();
				$sku = $erpListener->db->assoc("select * from dsasp.teb_sku where sku='$barcode'");
			}

			$pdo->query(
				"insert into `erp_complex_option` (`barcode`,`pno`,qty,`opts`,`auto_create_yn`, `base_stock_qty`, `force_soldout`, `reg_user`, `reg_date`, `remote_ip`, sku, color_cd, size_cd) ".
				"values ('$barcode', '$pno', '$bstock', '$opts', '$auto_create_yn', '$bstock', '$force', '$reg_user', now(), '$ip', '$sku[SKU]', '$sku[COLOR_CD]', '$sku[SIZE_CD]')"
			);
			$complex_no = $pdo->lastInsertId();

			$pdo->query(
				"insert into `erp_inout` (`complex_no`, `inout_kind`, `qty`, `remark`, `reg_user`, `reg_date`, `remote_ip`, `sno`, `in_price`) ".
				"values ('$complex_no', 'U', '$bstock', '$dreason', '$reg_user', '1900-01-01', '$ip', '$prd[seller_idx]', '0')"
			);

			return $complex_no;
		}
	}

	// 바코드 자동생성
	function barcodeGen($barcode = null) {
		global $cfg, $pdo;

		if($barcode) {
			if($pdo->row("select count(*) from `erp_complex_option` where `barcode`='$barcode' and del_yn='N'") > 0) {
				msg("입력하신 [$barcode]는 중복된 바코드입니다.\t", 'parent', 'reload');
			}
		} else {
			if(!$cfg['barcode_type']) $cfg['barcode_type'] = 1;
			switch($cfg['barcode_type']) {
				case '1' : $barcode = date('Ymd-').strtoupper(substr(md5(time().rand(0,65536)), 0, 7)); break;
				case '2' : $barcode = date('md-').strtoupper(substr(md5(time().rand(0,65536)), 0, 11)); break;
				case '3' : $barcode = strtoupper(substr(md5(time().rand(0,65536)), 0, 16)); break;
			}
			if($pdo->row("select count(*) from `erp_complex_option` where `barcode`='$barcode'") > 0) {
				return barcodeGen();
			}
		}
		return $barcode;
	}

	// 옵션명 구하기
	function getComplexOptionName($code) {
		global $tbl, $pdo;

        if(!$code) return '';

		$code = str_replace('_', ',', preg_replace('/^_+|_+$/', '', $code));
		$name = '';

		$res = $pdo->iterator("select b.iname from $tbl[product_option_item] b inner join $tbl[product_option_set] a on b.opno=a.no where b.no in ($code) order by a.sort asc, b.sort asc");
        foreach ($res as $data) {
			if($name) $name .= ' ／ ';
			$name .= stripslashes($data['iname']);
		}

		return trim($name);
	}

	// 재고를 체크하고 그 결과를 리턴
	function stockCheck($complex_no, $buy_ea, $prdname = null) {
		global $cfg, $scfg, $pdo;

		if($cfg['erp_force_limit'] == 'Y') {
			$add_field = ", limit_qty";
		}

        $qry = "select curr_stock(a.complex_no) as stock, opts, force_soldout $add_field from erp_complex_option a where complex_no='$complex_no' and del_yn='N'";
        if ($scfg->comp('use_erp_transaction', 'Y')) { // 재고차감 트랜젝션
            $qry .= " for update";
        }
		$data = $pdo->assoc($qry);
		$data['optname'] = getComplexOptionName($data['opts']);
		if($data['limit_qty'] < 0 && $data['force_soldout'] == 'N') { // 무제한 옵션 한계 재고 체크
			if($data['stock']-$buy_ea < $data['limit_qty']) {
				$data['force_soldout'] = 'L';
				$data['stock'] = $buy_ea-(($buy_ea-$data['stock'])+$data['limit_qty']);
			}
		}

		if(!$data) return; // 옵션을 삭제한 경우에는 그냥 판매되도록 처리

		if($data['force_soldout'] == 'Y' || ($data['force_soldout'] == 'L' && $data['stock']-$buy_ea < 0)) {
			if($data['stock'] <= 0 || $data['force_soldout'] == 'Y') {
                $msg  = sprintf(__lang_shop_error_soldout__,addslashes($prdname.''.getComplexOptionName($data['opts'])));
				return trim($msg);
				return $msg;
			} else {
				if($prdname) {
					$msg = sprintf(__lang_shop_error_maxord3__, addslashes($prdname), addslashes($data['optname']), $data['stock']);
				} else {
					$msg = sprintf(__lang_shop_error_maxord2__, $data['stock']);
				}
				return $msg;
			}
		}

		return; // 아무값이 없으면 정상
	}

	// 상품 재고 변경 (주문상태 및 주문상품상태 변경 전에 실행해야함)
	function orderStock($ono, $oldstock, $newstock, $pno = null) {
		global $cfg, $scfg, $tbl, $_order_stat, $now, $pdo;

		// 오픈마켓 등록대기 예외처리
		$os = (strlen($oldstock) == 1) ? $oldstock * 100 : $oldstock;
		$ns = (strlen($newstock) == 1) ? $newstock * 100 : $newstock;
		$timing = $cfg['erp_timing'] * 100;

		if($oldstock == 40) $os = 150;
		if($newstock == 40) $ns = 150;

		$ord = $pdo->assoc("select date1, date2, date3, date4, date5, x_order_id from $tbl[order] where ono='$ono'");
		if(
            $ord['x_order_id'] &&
            $ord['x_order_id'] != 'checkout' && $ord['x_order_id'] != 'subscription' && $ord['x_order_id'] != 'talkstore'
        ) return; // 오프라인, 오픈마켓 제외

		if($cfg['erp_stock_undo'] != '') { // 주문복구설정
			$cfg['erp_stock_undo'] .= ',20';
			$undo_ck = explode(',', trim($cfg['erp_stock_undo'], ','));
			$ck1 = in_array($newstock, $undo_ck);
			$ck2 = in_array($oldstock, $undo_ck);
			if(!$ck1 && $newstock > 11 && $oldstock < 11) { // 체크 없을 경우주문복구 하지 않음
				return;
			}
			if($newstock > 11 && $oldstock > 11 && $ck1 == $ck2) {
				return;
			}

			// 일반 상태일 때로 변경 시 상태
			for($i = 1; $i <= 5; $i++) {
				if($ord['date'.$i] < 1) {
					$i--;
					break;
				}
			}
			if(!$ck2 && $oldstock > 11) $os = (strlen($i) == 1) ? $i * 100 : $i;
			if(!$ck1 && $ck2 && $oldstock > 11) $ns = (strlen($i) == 1) ? $i * 100 : $i;
		}

		$mode = false;
		if($ns > $os && $ns >= $timing && $os < $timing) { // 상태 UP
			$mode = '-';
		} elseif ($os > $ns && $os >= $timing && $ns < $timing) { // 상태 Down
			$mode = '+';
		} else {
			return;
		}

		$pw = '';
		if($pno) {
			if(is_array($pno)) $pno = implode(',', $pno);
			$pw .= " and op.`no` in ($pno)";
		}

		if($oldstock >= 1) $pw .= " and op.`stat` = '$oldstock'";
		$oldstock = ceil($oldstock);

        if ($scfg->comp('use_erp_transaction', 'Y')) { // 재고차감 트랜젝션
            $pdo->query("start transaction");
        }

		if($mode == '-') { // 재고감산 선 체크
			$prd = $pdo->iterator("select op.no, op.pno, op.name, op.stat, op.buy_ea, op.complex_no from `$tbl[order_product]` op inner join $tbl[product] p on op.pno=p.no where op.ono='$ono' and op.complex_no > 0 and p.ea_type=1 $pw");
            foreach ($prd as $data) {
				$err = stockCheck($data['complex_no'], $data['buy_ea'], $data['name']);
				if($err) {
					if($GLOBALS['exec_file'] == 'order/auto_bank.exe.php' || $GLOBALS['erp_auto_input'] == 'Y') { // 자동입금/가상계좌일 경우 바로 처리
						if($data['stat'] != 20) {
							$pdo->query("update $tbl[order_product] set `stat`=20 where ono='$ono' and `no` = '$data[no]'"); // `no` = '$prd[no]' -> `no` = '$data[no]' 변경
							ordChgPart($ono);
							if(empty($pw) == true) {
								ordStatLogw($ono, 20, 'Y');
							}
							$pdo->query("insert into `$tbl[order_memo]` (`ono`, `admin_no`, `admin_id`, `content`, `reg_date`) values ('$ono', '0', 'system', '[system] 자동입금 처리 중 일부 상품 품절 확인 - $data[name]', '$now')");
						}
                        if ($scfg->comp('use_erp_transaction', 'Y')) { // 재고차감 트랜젝션
                            $pdo->query("commit");
                        }
						return 20;
					} else {
						alert($err);
                        if ($scfg->comp('use_erp_transaction', 'Y')) { // 재고차감 트랜젝션
                            $pdo->query("rollback");
                        }
						return $err;
					}
				}
			}
		}

		// 재고체크가 통과할 경우 실제 재고 처리
		$prd = $pdo->iterator("select op.no, op.ono, op.pno, op.stat, op.buy_ea, op.complex_no, op.dlv_hold from `$tbl[order_product]` op inner join $tbl[product] p on op.pno=p.no where op.ono='$ono' and op.complex_no > 0 and p.ea_type=1 $pw");
        foreach ($prd as $data) {
			$GLOBALS['prevent_resolve'] = ($data['dlv_hold'] == 'Y') ? true : false;
			stockChange($data, $mode, $data['buy_ea'], "[$ono] 주문상태를 ".($pno > 0 ? '부분 ' : '').$_order_stat[$newstock]." 상태로 변경");
		}

        if ($scfg->comp('use_erp_transaction', 'Y')) { // 재고차감 트랜젝션
            $pdo->query("commit");
        }

		return;
	}

	function stockChange($oprd, $mode, $ea, $reason = null, $admin_id = null) {
		global $tbl, $cfg, $pdo;

		if(!$admin_id) $admin_id = $GLOBALS['admin']['admin_id'];

		if(!$oprd['complex_no']) return;

		$kind = ($mode == '-') ? 'O' : 'U';
		$reason = addslashes($reason);
		$remote_ip = $_SERVER['REMOTE_ADDR'];

		if($oprd['stat'] != 32 && stockCheck($oprd['complex_no'], $ea) && $mode == '-') {
			if($GLOBALS['exec_file'] == 'order/auto_bank.exe.php') $pdo->query("update wm_order_product set stat=20 where no = $oprd[no]");
			return 1;
		}

		$pdo->query("
			insert into `erp_inout` (`complex_no`, `inout_kind`, `qty`, `remark`, `reg_user`, `reg_date`, `remote_ip`, `order_dtl_no`)
			values ('$oprd[complex_no]', '$kind', '$ea', '$reason', '$admin_id', now(), '$remote_ip', '$oprd[no]')
		");

		$qty = $pdo->row("select curr_stock($oprd[complex_no])");
		$pdo->query("update erp_complex_option set qty='$qty' where complex_no='$oprd[complex_no]'");

		setSoldout($oprd['pno'], $kind);
		if($kind == 'U') {
			resolveHold($oprd['complex_no'], $ea);
		}

		// 자동 배송보류 처리
		if($qty < 0 && $cfg['erp_auto_hold'] == 'Y') {
			$hold_sort = $pdo->row("select max(dlv_hold_order) from $tbl[order_product] where complex_no='$oprd[complex_no]' and dlv_hold='Y'");
			$hold_sort+=1;
			$r = $pdo->query("update $tbl[order_product] set dlv_hold='Y', dlv_hold_order='$hold_sort' where no='$oprd[no]'");
			if($r != false) {
				ordChgHold($oprd['ono']);
			}
		}

		// 재입고 알림 문자 발송
        if ($kind == 'U') { // 재고가 증가할 때만 체크
    		sendNotifyRestockSMS($oprd['complex_no']);
        }

		return 0;
	}

	function setSoldout($pno, $kind = null) { // 남은 윙포스옵션 재고에 따라 상품의 정상, 품절 상태 변경
        global $pdo;

		$oprd = $pdo->assoc("select * from {$GLOBALS['tbl']['product']} where no='$pno'");
		$oprd = shortcut($oprd);

		if ($oprd['stat'] == 3 && $kind == 'O') return;

		if($oprd['stat'] != 2 && $oprd['stat'] != 3 && $oprd['ea_type'] != 1) return; // 숨김일 경우에는 복구하지 않음
		$stat = $total = 0;

		$ores = $pdo->iterator("select complex_no, force_soldout, curr_stock(x.complex_no) as `stock`, is_soldout from erp_complex_option x where pno='$oprd[parent]' and del_yn='N'");
        foreach ($ores as $data) {
			switch($data['force_soldout']) {
				case 'Y' :
					$data['stock'] = 0;
					break;
				case 'N' : // 일반재고상품 재고 변화에 따라 상품 상태가 변하지 않도록 처리
					$data['stock'] = 1;
					break;
				default :
					$data['stock'] = ($data['stock'] > 0) ? 1 : 0;
			}
			$total += $data['stock'];

			$is_soldout = ($data['stock'] < 1) ? 'Y' : 'N';
			if($data['is_soldout'] != $is_soldout) {
				$pdo->query("update erp_complex_option set is_soldout='$is_soldout' where complex_no='$data[complex_no]'");
			}
		}

		if($oprd['stat'] == 3 && $total > 0) $stat = 2;
		if($oprd['stat'] == 2 && $total < 1) $stat = 3;

		// 숨김일 경우에는 자동 상태변경 하지 않음
		if($oprd['stat'] != 4 && $stat > 0) {
            // 휴지통상태의 바로가기 상품도 제외
			$pdo->query("update {$GLOBALS[tbl][product]} set `stat`='$stat' where no='$oprd[parent]' or ( wm_sc='$oprd[parent]' AND stat != 5 ) ");
			prdStatLogw($oprd['parent'], $stat, $oprd['stat']);
		}
	}

	// 특정 상품의 옵션데이터에 따라 임시 SKU 생성
	function createTmpComplexNo($no, $force_soldout = 'N') {
		global $tbl, $pdo;

		$opt_data = array();
		$ores = $pdo->iterator("select no, name from $tbl[product_option_set] where pno='$no' and necessary in ('Y', 'C') order by sort asc");
        foreach ($ores as $oset) {
			$_temp = $opt_data;
			$res2 = $pdo->iterator("select * from $tbl[product_option_item] where pno='$no' and opno='$oset[no]' order by sort asc");
            foreach ($res2 as $odata) {
				$iname = stripslashes($odata['iname']);
				if($odata['ori_no']) $odata['no'] = $odata['ori_no'];
				if(count($opt_data) == 0) {
					$_temp[$odata['no']] = $iname;
				} else {
					foreach($opt_data as $key => $val) {
						$_temp[$key.'_'.$odata['no']] = $val.'<ss>'.$iname;
						unset($_temp[$key]);
					}
				}
			}
			$opt_data = $_temp;
		}
		if(count($opt_data) == 0) {
			$opt_data[''] = '옵션없음';
		}

		foreach($opt_data as $key => $val) {
            global $pdo;

			$key = makeComplexKey($key);
			$complex_no = $pdo->row("select complex_no from erp_complex_option where pno='$no' and opts='$key'");
			if(!$complex_no) {
				$barcode = barcodeGen(null);
				createComplex($no, $key, $barcode, 0, '상품업로드', $force_soldout, $barcode);
			}
		}
	}

	function isWingposStock($pno) {
        global $pdo;

		return $pdo->row("select sum(if(force_soldout='L',qty,9999)) from erp_complex_option where pno='$pno' and force_soldout!='Y' and del_yn='N'");
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  상품이 입고될 경우 입고 된 수량만큼 배송지연 해제
	' |  int resolveHold(int 복합재고번호, int 입고수량, boolean 취소복구여부)
	' +----------------------------------------------------------------------------------------------+*/
	function resolveHold($complex_no, $ea) {
		global $tbl, $cfg, $pdo;

		if($cfg['erp_auto_release'] != 'Y') return false;

		$input_ea = $ea;
		$total_ea = 0;

		// 여유 재고가 있을 경우 합산
		$qty = $pdo->row("select curr_stock($complex_no)");
		if($qty > 0) {
			$input_ea+=$qty;
		}

		if($GLOBALS['prevent_resolve'] == true) return false; // 보류인 상품은 취소해도 다른 상품을 보류해제 하지 않음

		$res = $pdo->iterator("select no, ono, buy_ea, dlv_hold_order from $tbl[order_product] where complex_no='$complex_no' and dlv_hold='Y' and `stat` in (1,2,3) order by dlv_hold_order asc, no asc");
        foreach ($res as $data) {
			if($data['buy_ea'] > $input_ea) continue;

			$r = $pdo->query("update $tbl[order_product] set dlv_hold='N', dlv_hold_order='0' where no='$data[no]'");
			if($r != false) {
				ordChgHold($data['ono']);
			}
			$input_ea -= $data['buy_ea'];
			if($input_ea == 0) break;

			$total_ea += $data['buy_ea'];
		}
		return $total_ea;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  상품이 수동 출고 된 경우 출고 된 수량만큼 배송지연
	' |  int resolveHold(int 복합재고번호, int 입고수량, boolean 취소복구여부)
	' +----------------------------------------------------------------------------------------------+*/
	function setOutputToHold($complex_no, $ea) {
		global $tbl, $cfg, $pdo;

		if($cfg['erp_auto_output_hold'] != 'Y') return;

		$output_ea = abs($ea);
		$total_ea = 0;

		$res = $pdo->iterator("select p.no, p.ono, p.buy_ea from $tbl[order_product] p inner join $tbl[order] o using(ono) where p.complex_no='$complex_no' and p.stat < 4 and p.dlv_hold='N' order by p.no desc");
        foreach ($res as $data) {
			if($data['buy_ea'] > $output_ea) continue;

			$add_q = '';
			$hold_sort = $pdo->row("select max(dlv_hold_order) from $tbl[order_product] where complex_no='$complex_no' and dlv_hold='Y' and `stat` in < 4");
			if($hold_sort) {
				$hold_sort++;
				$add_q = ",`sorthold`='$hold_sort'";
			}
			$pdo->query("update $tbl[order_product] set `dlv_hold`='Y' $add_w where no='$data[no]'");
			if($pdo->lastRowCount() > 0) {
				ordChgHold($data['ono']);
			}

			$output_ea -= $data['buy_ea'];
			if($output_ea < 1) break;

			$total_ea += $data['buy_ea'];
		}
		return $total_ea;
	}

?>