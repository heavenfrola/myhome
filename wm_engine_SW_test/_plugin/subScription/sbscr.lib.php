<?PHP

	include_once __ENGINE_DIR__."/_engine/sms/sms_module.php";
	include_once __ENGINE_DIR__."/_engine/include/wingPos.lib.php";
    include_once __ENGINE_DIR__.'/_plugin/subScription/set.common.php';

    use Wing\API\Naver\NaverSimplePay;

	/* +----------------------------------------------------------------------------------------------+
	' |  정기배송 세트 설정 가져오기
	' +----------------------------------------------------------------------------------------------+*/
	function getsbscrCfg($pno) {
		global $cfg, $tbl, $pdo;

		$sdata = array();
		if($cfg['use_sbscr']!='Y') return;

		if($cfg['sbscr_type']=='P') {
			$sdata['sbscr_end_yn'] = "";
			$sbscr_data = $pdo->assoc("select ssp.no, ssp.setno, ss.* from $tbl[sbscr_set_product] as ssp inner join $tbl[sbscr_set] as ss on ssp.setno=ss.no where pno='$pno'");
			if(!$sbscr_data['no']) return;

			$sdata['sbscr_dlv_week'] =  explode('|', $sbscr_data['dlv_week']);
			$sdata['sbscr_dlv_period'] = explode('|', $sbscr_data['dlv_period']);

			if($sbscr_data['dlv_type']=='Y') {//최대기간설정
				$sdata['sbscr_end_yn'] = "Y";
				$sdata['sbscr_dlv_end'] = $sbscr_data['dlv_end'];
			}

			if($sbscr_data['sale_use']=='Y') {
				$sdata['sale_use'] = "Y";
				$sdata['sale_ea'] = $sbscr_data['sale_ea'];
				$sdata['sale_percent'] = $sbscr_data['sale_percent'];
			}
		}else if($cfg['sbscr_type']=='A') {//공통
			$sdata['sbscr_dlv_week'] = explode('|', $cfg['sbscr_dlv_week']);
			$sdata['sbscr_dlv_period'] = explode('|', $cfg['sbscr_dlv_period']);

			if($cfg['sbscr_dlv_type']=='Y') {//최대기간설정
				$sdata['sbscr_end_yn'] = "Y";
				$sdata['sbscr_dlv_end'] = $cfg['sbscr_dlv_end'];
			}

			if($cfg['sbscr_sale_use']=='Y') {
				$sdata['sale_use'] = "Y";
				$sdata['sale_ea'] = $cfg['sbscr_sale_ea'];
				$sdata['sale_percent'] = $cfg['sbscr_sale_percent'];
			}
		}

		$sdata['sbscr_min_period'] = min($sdata['sbscr_dlv_period']);
		$sdata['sbscr_min_dlv_week'] = min($sdata['sbscr_dlv_week']);

		return $sdata;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  정기배송 계산
	' +----------------------------------------------------------------------------------------------+*/
	function getsbscrCal($data) {
		global $cfg, $tbl, $pdo;

		$start_date = $data['detail_sbscr_start_date'];
		while(1) {
			$_week = date('N', $start_date);
			if($data['week_all']=='Y') {
				if(!in_array($_week, $data['sbscr_dlv_week'])) { //ajax 처리
					$start_date = strtotime('+1 days', $start_date);
					continue;
				}
			}else {
                /*
				if($_week!=$data['sbscr_min_dlv_week']) {
					$start_date = strtotime('+1 days', $start_date);
					continue;
				}
                */
			}
			if($pdo->row("select count(*) from $tbl[sbscr_holiday] where timestamp='$start_date' and is_holiday='Y'") > 0) {
				$start_date = strtotime('+1 days', $start_date);
				continue;
			}
			break;
		}
		$calc_data['start_date'] = $start_date;

		$end_date = $data['detail_sbscr_end_date'];
		if($end_date) {
			while(1) {
				$_week = date('N', $end_date);
				if($data['week_all']=='Y') {
					if(!in_array($_week, $data['sbscr_dlv_week'])) { //ajax 처리
						$end_date = strtotime('+1 days', $end_date);
						continue;
					}
				}else {
					if($_week!=$data['sbscr_min_dlv_week']) {
						$end_date = strtotime('+1 days', $end_date);
						continue;
					}
				}
				if($pdo->row("select count(*) from $tbl[sbscr_holiday] where timestamp='$end_date' and is_holiday='Y'") > 0) {
					$end_date = strtotime('+1 days', $end_date);
					continue;
				}
				break;
			}
			$calc_data['end_date'] = $end_date;
		}

		$option_text = "";
		if($data['sbscr_option_val']) {
			$_option_val = explode("|", $data['sbscr_option_val']);
			$_buy_ea = explode("|", $data['sbscr_buy_ea']);
			foreach($_option_val as $key=>$val) {
				$_val = explode("::", $val);
				$option_text .= ($option_text) ? " / ".$_val[0]: $_val[0];
			}
		}

		$calc_data['option_text'] = $option_text;
		$calc_data['total_sell_prc'] = $data['total_sell_prc'];//옵션가포함+수량
		$calc_data['total_dlv_prc'] = $data['dlv_prc'];//배송비
		$calc_data['total_ea_pay_prc'] = $data['total_sell_prc']+$data['dlv_prc'];

		if($data['week_all']=='Y') {
			$_dlv_week = $data['sbscr_dlv_week'];
		}else {
			$_dlv_week = array(''=>$data['sbscr_min_dlv_week']);
		}

		//배송주기
		$period = $data['sbscr_min_period'];

		// 날짜 계산
		if($end_date) {
			$calc_data['date_list'] = getsbscrDate($start_date, $end_date, $period, $_dlv_week);
		}else {
			$calc_data['date_list'][] = $start_date;
		}
		// 총 배송 횟수
		$calc_data['total_dlv_cnt'] = count($calc_data['date_list']);
		$calc_data['total_pay_prc'] = $calc_data['total_ea_pay_prc'] * $calc_data['total_dlv_cnt']; // 총 주문금액 = 주문금액 * 배송횟수

		return $calc_data;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  정기배송 날짜 계산
	' +----------------------------------------------------------------------------------------------+*/
	function getsbscrDate($start_date, $finish_date, $period, $week) {
		global $tbl, $pdo, $scfg;

        if (preg_match('/^[0-9]+$/', $period) == true) {
            $period .= 'weeks';
        }

        $_this_date = $start_date;
        $date_list = array();
        while($_this_date <= $finish_date) {
            $_tmp_date = $_this_date;

            // 배송 가능일 체크
            while(1) {
                if (in_array(date('w', $_tmp_date), $week) == false && count($week) > 0) {
                    $_tmp_date += 86400;
                } else {
                    $is_holiday = $pdo->row("select count(*) from {$tbl['sbscr_holiday']} where timestamp='$_tmp_date' and is_holiday='Y'");
                    if ($is_holiday) {
                        $_tmp_date += 86400;
                    } else {
                        $date_list[] = $_tmp_date;
                        break;
                    }
                }

                // 일단위 배송의 경우 차일로 연기하지 않음. (다음 날 분량 묶여서 배송될수 있음)
                if (preg_match('/days$/', $period) == true) {
                    break;
                }
            }
            $_this_date = strtotime('+'.$period, $_this_date);

        }
        return $date_list;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  장바구니에 상품 넣기
	' +----------------------------------------------------------------------------------------------+*/
	function insertSbscrCart($hash, $key = null, $data = null) {
		global $tbl, $cfg, $member, $now, $buy_ea, $mwhere, $_recent_no, $pdo;

		if(!$hash) return false;
		if(!$data) return false;

		$_buy_ea = (is_array($buy_ea)) ? $buy_ea[$key] : $buy_ea;

		$prd = checkPrd($hash, true);

		if($cfg['use_prc_consult'] == 'Y' && $prd["sell_prc_consultation"]!="") {
			if(!$prd["sell_prc_consultation_msg"]) $prd["sell_prc_consultation_msg"] = "주문 전 협의가 필요한 상품입니다.";
			msg(php2java($prd["sell_prc_consultation_msg"]));
		}

		$chk = prdCheckStock($prd, $_buy_ea, $key);
		$option = $chk['option'];
		$option_q =  addslashes($option);
		$option_idx = $chk['option_idx'];
		$option_prc = $chk['option_prc'];
		$complex_no = $chk['complex_no'];
		$prdcpn_no = $chk['prdcpn_no'];
		$anx = $chk['anx'];

		if(!$complex_no && $cfg['use_dooson'] == 'Y' && $prd['ea_type'] == 1) {
			msg(__lang_shop_error_unregistOption__);
		}

		$start_date = strtotime($data['sbscr_start_date']);
		if($data['sbscr_end_date']) {
            $tmp = explode('|', $data['sbscr_date_list']);
			$end_date = end($tmp);
		}else {
			$end_date = 0;
			$data['sbscr_dlv_cnt'] = $data['sbscr_period'];
		}

		if($prd['prd_type'] == 3) return;

        $old = $pdo->assoc("
            select no, buy_ea from {$tbl['sbscr_cart']}
            where
                pno=? and complex_no=? and start_date=? and end_date=? and date_list=? and member_no=? and guest_no=?
        ", array(
            $prd['parent'], $complex_no, $start_date, $end_date, $data['sbscr_date_list'], $member['no'], $_SESSION['guest_no']
        ));
        if ($old) {
            // 중복 장바구니 저장 시 옵션
            switch($cfg['cart_dup']) {
                case '2' : // 새로운 수량으로 변경
                    $tmp_ea = $_buy_ea;
                    break;
                case '3' : // 새로운 수량만큼 추가
                    $tmp_ea = $old['buy_ea']+$_buy_ea;
                    break;
            }

            // 구매 수량 및 재고 다시 체크
            prdCheckStock($prd, $tmp_ea, $key);

            // 구매 수량 업데이트
            $pdo->query("update {$tbl['sbscr_cart']} set buy_ea=? where no=?", array(
                $tmp_ea, $old['no']
            ));
        } else {
            $sql = "insert into `$tbl[sbscr_cart]` (`pno`, `option`, `option_prc`, `option_idx`, `complex_no`, `buy_ea`, `dlv_cnt`, `period`, `week`, `start_date`, `end_date`, `date_list`, `member_no`, `guest_no`, `reg_date`) values ('$prd[parent]', '$option_q', '$option_prc', '$option_idx','$complex_no', '$_buy_ea', '$data[sbscr_dlv_cnt]', '$data[sbscr_period]', '$data[sbscr_week]', '$start_date', '$end_date', '$data[sbscr_date_list]', '$member[no]', '$_SESSION[guest_no]', '$now')";
            $pdo->query($sql);
        }

		$old['no'] = $pdo->lastInsertId();
		$_recent_no = $old['no'];

		ctrlPrdHit($prd['parent'], 'hit_cart', '+1'); // 장바구니 입력 횟수 증가

		return array(
			'option' => $option,
			'option_idx' => $option_idx,
			'option_prc' => $option_prc,
			'complex_no' => $complex_no,
			'anx' => $anx
		);
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  ordChgPart 부분 주문상태 저장
	' +----------------------------------------------------------------------------------------------+*/
	function sbscrChgPart($sno) {
		global $tbl, $cfg, $now, $pdo;

		$sbscr = $pdo->assoc("select date1, date2, stat from $tbl[sbscr] where sbono='$sno'");

		$stat2 = $pdo->row("select group_concat(stat) from $tbl[sbscr_product] where sbono='$sno'");
		$stat2 = explode(',', $stat2);
		$stat = min($stat2);
		$stat2 = '@'.implode('@', $stat2).'@';

		if($sbscr['stat'] != $stat && $stat <= 2) {
			for($i = 2; $i <= $stat; $i++) {
				if(!$sbscr['date'.$stat]) {
					$asql .= ", date$stat='$now'";
				}
			}
		}
		$pdo->query("update $tbl[sbscr] set stat='$stat' $asql where sbono='$sno'");

		return $stat;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  mypage 주문내역
	' +----------------------------------------------------------------------------------------------+*/
	function orderSbscrList() {
		global $member,$_order_stat, $dlv,$_pay_type, $tbl, $cfg, $root_url, $pdo;

		if(!$GLOBALS['ordersbscrRes']) {
			$GLOBALS['ordersbscrRes'] = $pdo->iterator("select * from `".$GLOBALS['tbl']['sbscr']."` where `stat`  not in (11, 31, 32) and `member_no`='$member[no]' and `member_id`='$member[member_id]' order by `date1` desc");
			$GLOBALS['osidx'] = $pdo->row("select count(*) from `".$GLOBALS['tbl']['sbscr']."` where `stat`  not in (11, 31, 32) and `member_no`='$member[no]' and `member_id`='$member[member_id]'")+1;
		}
		$data = $GLOBALS['ordersbscrRes']->current();
        $GLOBALS['ordersbscrRes']->next();
		if($data == false) {
			unset($GLOBALS['ordersbscrRes']);
			return false;
		}

		$data['stat'] = _getOrdStat($data);

		$data['title'] = strip_tags($data['title']);
		$data['date1'] = date("Y/m/d",$data['date1']);
		$data['o_total_prc'] = $data['s_total_prc'];
		$data['total_prc'] = parsePrice($data['s_total_prc'], true);
		$data['link'] = $root_url."/mypage/order_detail.php?sbono=".$data['sbono'];
		$data['pay_type_str'] = $_pay_type[$data['pay_type']];

		$GLOBALS['osidx']--;
		return $data;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  mypage 주문 상세 내역
	' +----------------------------------------------------------------------------------------------+*/
	function sbscrCartList($split_big = '/', $split_small = ':', $opt_deco1 = '', $opt_deco2 = '', $w = '', $h = '') {
		global $ord, $pdo;
		if(!$GLOBALS['sbscrCartRes']) {
			$GLOBALS['sbscrCartRes'] = $pdo->iterator("select * from `".$GLOBALS['tbl']['sbscr_product']."` where `sbono`='$ord[sbono]'");
		}
		$data = $GLOBALS['sbscrCartRes']->current();
        $GLOBALS['sbscrCartRes']->next();
		if($data == false) return false;

		$data['name'] = stripslashes($data['name']);
		$prd = get_info($GLOBALS['tbl']['product'], 'no', $data['pno']);
		if($prd['no'] &&  ($prd['stat'] == '2' || $prd['stat'] == '3')) {
			$data['plink'] = $GLOBALS['root_url'].'/shop/detail.php?pno='.$prd['hash'];

			$img = prdImg(3, $prd, $w, $h);
			$data['img'] = $img[0];
			$data['imgstr'] = $img[1];
		}
		else {
			$data['plink'] = 'javascript:noPrd();';
			$data['imgstr'] = 'width="0" height="0"';
		}

		$data['option_str'] = ''; //초기화
		if($data['option']) {
			$data['option_str'] = str_replace('<split_big>', $split_big, $data['option']);
			$data['option_str'] = str_replace('<split_small>', $split_small, $data['option_str']);
			$data['option_str'] = $opt_deco1.$data['option_str'].$opt_deco2;
		}

		$data['milage'] = parsePrice($data['milage'], true);

		$data['sell_prc'] = parsePrice($data['sell_prc'], true);
		$data['total_prc'] = parsePrice($data['total_prc'], true);

		return $data;
	}

	function parseUserSbscr($cart) {
        global $_sbscr_periods_unit;

		if(!$cart['dlv_cnt']) return $cart;

		$yoil = array("","월","화","수","목","금","토","일");
        $cart['week'] = date('N', $cart['start_date']);
		$cart['start_date'] = date('Y-m-d', $cart['start_date']);
		$cart['end_date'] = ($cart['end_date']==0) ? '':date('Y-m-d', $cart['end_date']);
		$cart['dlv_date'] = $cart['start_date']." ~ ".$cart['end_date'];
		$_cart_week = explode('|', $cart['week']);
		$_cart_week_text = '';
		foreach($_cart_week as $key=>$val) {
			if($val) $_cart_week_text .= ($_cart_week_text)? "/".$yoil[$val]:$yoil[$val];
		}
		$cart['week'] = $_cart_week_text;
		$cart['dlv_cnt'] = ($cart['end_date']) ? $cart['dlv_cnt']:'-';

        preg_match('/^([0-9]+)(.*)$/', $cart['period'], $_period);
        $period = $_period[1];
        $unit = $_period[2];
        $cart['period_text'] = $period.$_sbscr_periods_unit[$unit];
        if ($cart['dlv_cnt'] > 1) {
            $cart['period_text'] .= " ({$cart['dlv_cnt']})";
        }

		return $cart;
	}

	function sbscrmakeOrdNo($opm_no = 0) {
		global $now, $tbl, $ono, $pdo;
		$ono1=date("Ymd",$now);

		$mr=mt_rand();

		$ono2=strtoupper(substr(md5($now+$mr+$opm_no),1,5));
		$tmp=$pdo->row("select `no` from `$tbl[order_no]` where `ono1`='$ono1' and `ono2`='$ono2'");
		if($tmp) {
			return false;
		}
		else {
			$pdo->query("insert into `$tbl[order_no]` (`ono1`,`ono2`) values ('$ono1','$ono2')");
			return $ono1."-".$ono2;
		}
	}

	function autoSbscrCreate($schno="") {
		global $now, $cfg, $tbl, $engine_dir, $_order_sales, $sms_replace, $pdo;

		$GLOBALS['erp_auto_input'] = 'Y';
		$cfg['erp_auto_hold'] = 'Y'; // 품절상품 처리를 위해 자동 배송보류 기능 강제 사용

		$order_make_day = $cfg['sbscr_order_create'];

		$booking_data = array();
		$tot_order_prc = "0";

		$make_sdate = mktime(0,0,0,date("n"),date("j"),date("Y"));
		$make_edate = $now + ($order_make_day*86400);

		for($j=$make_sdate;$j<$make_edate;$j+=86400) {
			if(in_array(date('w',$j),array(0,6))) $make_edate += 86400;
			if($pdo->row("select count(*) from $tbl[sbscr_holiday] where timestamp='$make_edate' and is_holiday='Y'") > 0) {
				if($cfg['sbscr_holiday_after']=='Y') {
					$make_edate = strtotime('+'.$cfg['sbscr_holiday_create'].' days', $make_edate);
					continue;
				}else {
					$make_edate = strtotime('-'.$cfg['sbscr_holiday_create'].' days', $make_edate);
					continue;
				}
			}
		}
		$make_sdate = date('Y-m-d', $make_sdate);
		$make_edate = date('Y-m-d', $make_edate);

		$create_where = '';
		if($schno) {
			$bv = $pdo->assoc("select sbono, date from {$tbl['sbscr_schedule']} where no='$schno'");
			$create_where .= " and ss.sbono='{$bv['sbono']}'";
			$make_sdate = $make_edate = $bv['date'];
		}
		$create_where .= "and ss.`date` >= '".$make_sdate."' and ss.`date` <= '".$make_edate."'";

		$add_fd = '';
		if($cfg['use_prd_dlvprc'] == 'Y') {
			$add_fd .= ', sp.prd_dlv_prc';
		}

		$_sale_fd =  getOrderSalesField('sp');

		$sql = "select
					ssp.*,
					s.member_no, s.member_id, s.buyer_name, s.buyer_email, s.buyer_phone, s.buyer_cell,
					s.addressee_name, s.addressee_phone,
					s.addressee_cell, s.addressee_zip, s.addressee_addr1, s.addressee_addr2, s.sbono,
					s.pay_type, s.conversion, s.title, s.date1, s.date2, s.stat,
					s.sms_send, s.mobile, s.bank, s.bank_name, s.dlv_memo, s.mng_memo,
					sp.no as spno, sp.pno, sp.complex_no, sp.name, sp.sell_prc, sp.buy_ea, sp.total_prc,
					sp.partner_no, sp.fee_rate, sp.fee_prc,
					$_sale_fd, sp.`option`, sp.option_idx, sp.milage, ss.date, ss.total_prc as ss_total_prc, ss.dlv_prc, ss.no as ss_no $add_fd
				from
					".$tbl['sbscr_schedule_product']." as ssp
				left join
					".$tbl['sbscr_schedule']." as ss
				on ss.no = ssp.schno
				left join
					".$tbl['sbscr']." as s
				on ssp.sbono = s.sbono
				LEFT JOIN
					".$tbl['sbscr_product']." as sp
				on ssp.sbpno = sp.no
				where
					ssp.stat = 2
					and ssp.ono = ''
					and ssp.opno = 0
					$create_where
				order by ss.`date`";
		$res = $pdo->iterator($sql);

        foreach ($res as $_booking) {
			// [서브스크립션번호][날짜][회원번호] 로 배열을 묶어줌
			$booking_data[$_booking['sbono']][$_booking['date']][] = $_booking;
		}
		if($type=='json') {//수동생성
			if(count($booking_data)==0) {
				exit('생성할 주문이 존재하지 않습니다.');
			}
		}

		//묶어준 배열을 돌면서 주문서 생성
		foreach($booking_data as $sno => $b_data) {
            $order_sales = array();
			foreach($b_data as $bkdate => $b_data2) {
				include_once $engine_dir."/_manage/manage2.lib.php";

				$before_sno= "";
				$ord['title']= "";
				$tot_prd_prc = $tot_pay_prc = $tot_dlv_prc = 0;
				$total_milage = 0;
				foreach($b_data2 as $oprd) {
					$oprd = array_map('addslashes', $oprd);
					if($before_sno != $oprd['sbono'] || !$before_sno) {
						$ono = "";
						while(!$ono) {
							$ono = sbscrmakeOrdNo();
						}
					}
					$before_sno = $oprd['sbono'];

					if(!$ord['title']) {
						$oprd_cnt = count($booking_data[$sno][$bkdate]);
						$ord['title'] = addslashes($oprd['name'])." (".$oprd['buy_ea'].")";
						if($oprd_cnt>1) $ord['title'] .= ' 外 '.($oprd_cnt-1);
					}

					$tot_prd_prc += $oprd['total_prc'];
                    $tot_pay_prc += $oprd['total_prc'];
                    $tot_dlv_prc = $oprd['dlv_prc']; // sbscr_schedule 에서 가져오는 값이므로 더하면 안됨

					$pasql1 = $pasql2 = '';
					foreach($_order_sales as $key => $val) {
						$tmp = numberOnly($oprd[$key], true);
						if($tmp) {
							$pasql1 .= ", $key";
							$pasql2 .= ", '$tmp'";
						}
                        if ($tmp > 0) {
                            $tot_pay_prc -= $tmp;

                            if (isset($order_sales[$key]) == false) $order_sales[$key] = 0;
                            $order_sales[$key] += $tmp;
                        }
					}
					if($oprd['option']) {
						$pasql1 .= ", `option`, option_idx";
						$pasql2 .= ", '$oprd[option]', '$oprd[option_idx]'";
					}
					$_milage = ($oprd['milage']/$oprd['buy_ea']);
					$total_milage += $oprd['milage'];

					if($cfg['use_partner_shop'] == 'Y') {
						$pasql1 .= ", partner_no, fee_rate, fee_prc, dlv_type";
						$pasql2 .= ", '$oprd[partner_no]', '$oprd[fee_rate]', '$oprd[fee_prc]', '$oprd[dlv_type]'";
					}
					if($cfg['use_prd_dlvprc'] == 'Y') { // 개별 배송비
						$pasql1 .= ", prd_dlv_prc";
						$pasql2 .= ", '{$oprd['prd_dlv_prc']}'";
					}

					if(stockCheck($oprd['complex_no'], $oprd['buy_ea'])) {
						$pasql1 .= ", dlv_hold";
						$pasql2 .= ", 'Y'";
					}

					$sql  = "INSERT INTO `$tbl[order_product]` (`ono`, `pno`, `name`, `sell_prc`, `buy_ea`, `total_prc`,  stat, milage, total_milage, complex_no $pasql1) ";
					$sql .= "VALUES ('$ono', '$oprd[pno]', '".addslashes($oprd['name'])."', '$oprd[sell_prc]', '$oprd[buy_ea]', '$oprd[total_prc]', '2', '$_milage', '$oprd[milage]', '{$oprd['complex_no']}' $pasql2)";
					$pdo->query($sql);

					$opno = $pdo->lastInsertId();
					$order_product_no[] = $opno;

					$sql = "UPDATE
								".$tbl['sbscr_schedule_product']."
							SET
								ono = '".$ono."',
								opno = '".$opno."',
								make_date = '".date('Y-m-d H:i:s', $now)."'
							WHERE
								no = '".$oprd['no']."'
							";
					$pdo->query($sql);

				}
				$tot_order_prc = $tot_prd_prc+$tot_dlv_prc;
                $tot_pay_prc = $tot_pay_prc+$tot_dlv_prc;

                $asql1 = $asql2 = '';
                foreach ($order_sales as $key => $val) {
                    $asql1 .= ", $key";
                    $asql2 .= ", '$val'";
                }

				$sql = "INSERT INTO `$tbl[order]` (`ono`, `mobile`, `date1`, `date2`, `stat`, `stat2`, `member_no`, `member_id`,`buyer_name`, `buyer_email`, `buyer_phone`, `buyer_cell`, `addressee_name`, `addressee_phone`, `addressee_cell`, `addressee_zip`, `addressee_addr1`, `addressee_addr2`, `dlv_memo`, `mng_memo`, `total_prc`, `milage_prc`, `pay_prc`, `prd_prc`, `dlv_prc`, `pay_type`, `bank`, `milage_down`, `milage_down_date`, `title`, `dlv_type`, `sms`, `mail_send`, `ip`, `bank_name`, `cart_where`, `conversion`, total_milage, x_order_id, s_order_id $asql1) ";
				$sql .= "VALUES ('$ono', '$oprd[mobile]', '$now', '$now', '$oprd[stat]', '$oprd[stat2]', '$oprd[member_no]', '$oprd[member_id]', '$oprd[buyer_name]', '$oprd[buyer_email]', '$oprd[buyer_phone]', '$oprd[buyer_cell]', '$oprd[addressee_name]', '$oprd[addressee_phone]', '$oprd[addressee_cell]', '$oprd[addressee_zip]', '$oprd[addressee_addr1]', '$oprd[addressee_addr2]', '$oprd[dlv_memo]', '$oprd[mng_memo]', '$tot_order_prc', '0', '$tot_pay_prc', '$tot_prd_prc', '$tot_dlv_prc', '$oprd[pay_type]', '$oprd[bank]', '', '', '$ord[title]', '', '$oprd[sms]', '$oprd[mail_send]', '$_SERVER[REMOTE_ADDR]', '$oprd[bank_name]', '$oprd[cart_where]', '$oprd[conversion]', '$total_milage', 'subscription', '{$b_data2[0]['sbono']}' $asql2)";

				$r = $pdo->query($sql);
				if($pdo->lastRowCount() > 0) {
					orderStock($ono, 0, 2);
					ordChgPart($ono);
					ordChgHold($ono);
					ordStatLogw($ono, 2, "Y");

					$left_cnt = $pdo->row("select count(*) from {$tbl['sbscr_schedule_product']} where sbono='{$b_data2[0]['sbono']}' and ono='' and stat in (1, 2)");
                    $stat = ($left_cnt == 0) ? 5 : 3;
                    $pdo->query("
                        update {$tbl['sbscr']} set stat='$stat', date{$stat}=unix_timestamp(now())
                        where sbono='{$b_data2[0]['sbono']}' and stat < 10 and stat!='$stat'
                    ");
                    $sms_replace['ono'] = $ono;
                    if ($stat == '5') {
                        SMS_send_case(37, $oprd['buyer_cell']);

                        expireBillKey($b_data2[0]['sbono']); // 빌키 만료 처리
                    }

                    // 현금영수증 체크
                    $rcpt = $pdo->assoc("select * from {$tbl['cash_receipt']} where ono='$sno' and stat in (1, 2)");
                    if ($rcpt['no'] > 0) {
                        $taxfree_amt = 0;
                        $ores = $pdo->iterator("select op.* from {$tbl['order_product']} op inner join {$tbl['product']} p on op.pno=p.no where ono='$ono' and op.stat<10 and p.tax_free='Y'");
                        foreach ($ores as $odata) {
                            $taxfree_amt += ($odata['total_prc']-getOrderTotalSalePrc($odata));
                        }
                        $amt4 = round(($tot_pay_prc-$taxfree_amt)/11); // 부가세
                        $amt1 = $tot_pay_prc;
                        $amt2 = ($amt1-$amt4); // 공급가액
                        $amt3 = 0; // 봉사료
                        $pdo->query("
                            insert into {$tbl['cash_receipt']}
                            (ono, cash_reg_num, pay_type, reg_date, amt1, amt2, amt3, amt4, taxfree_amt, b_num, prod_name, cons_name, cons_tel, cons_email)
                            values
                            (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                        ", array(
                            $ono, $rcpt['cash_reg_num'], $oprd['pay_type'], $now, $amt1, $amt2, $amt3, $amt4, $taxfree_amt, $rcpt['b_num'], $ord['title'], $rcpt['cons_name'], $rcpt['cons_tel'], $rcpt['cons_email']
                        ));
                        cashReceiptAuto($ono, 2); // 자동 발급
                    }
				}

				$payment_no = createPayment(array(
					'type' => 0,
					'ono' => $ono,
					'pno' => $order_product_no,
					'pay_type' => $oprd['pay_type'],
					'amount' => $tot_pay_prc,
            		'dlv_prc' => $tot_dlv_prc,
					'bank' => $oprd['bank'],
					'bank_name' => $oprd['bank_name'],
				), 1);

				if($exec=='json') {
					makeOrderLog($ono, "sub_order_make.exe.php:수동");
				} else {
					makeOrderLog($ono, "sub_order_make.exe.php:크론");
				}

				$sms_replace['ono'] = $ono;
				$sms_replace['buyer_name'] = $oprd['buyer_name'];
				$sms_replace['prd_name'] = $ord['title'];
				$sms_replace['dlv_date'] = $bkdate;

				if($b_data2['pay_type'] == '23') {
					$sms_replace['pay_prc'] = $tot_order_prc;
					SMS_send_case(27, $oprd['buyer_cell']);
				} else {
					SMS_send_case(33, $oprd['buyer_cell']);
				}

				if($oprd['member_no']) $pdo->query("update `$tbl[member]` set `total_ord`=`total_ord`+1, last_order='$now' where `no`='$oprd[member_no]'");
			}
		}
	}

    /**
     * 일괄 결제 주문에 대한 자동입금 및 가상계좌 입금완료 처리
     **/
    function sbscrInput($sbono)
    {
        global $tbl, $pdo;

        $param = array($sbono);

        $ord = $pdo->assoc("select stat, pay_type, s_pay_prc from {$tbl['sbscr']} where sbono=?", $param);
        $ord['pay_prc'] = parsePrice($ord['s_pay_prc']);

        if ($ord['stat'] > 1) return false;
        if ($ord['pay_type'] != '2' && $ord['pay_type'] != '4') return false;

		$pdo->query("update {$tbl['sbscr']} set stat='2', date2=unix_timestamp(now()) where sbono=? and stat='1'", $param);
		$pdo->query("update {$tbl['sbscr_product']} set stat='2' where sbono=? and stat='1'", $param);
		$pdo->query("update {$tbl['sbscr_schedule_product']} set stat='2' where sbono=? and ono='' and stat='1'", $param);

        return $ord;
    }

    /**
     * 빌키 만료 처리
     **/
    function expireBillKey($sbono)
    {
        global $pdo, $tbl, $scfg, $log_instance;

        $ret = null;
        $data = $pdo->assoc("select stat, pay_type, billing_key from {$tbl['sbscr']} where sbono=?", array($sbono));
        if ($data['stat'] == '5' && $data['billing_key']) {
            // 일반 PG
            if ($data['pay_type'] == '23') {
                switch($scfg->get('autobill_pg')) {
                    case 'dacom  ' : $pg_version = 'XpayAutoBilling/'; break;
                    case 'nicepay' : $pg_version = 'autobill/'; break;
                }
                include_once __ENGINE_DIR__.'/_engine/card.'.$scfg->get('autobill_pg').'/'.$pg_version.'card_bill_pay.inc.php';
                if (function_exists('recurrentExpire') == true) {
                    $ret = recurrentExpire($data['billing_key']);
                }
            // 네이버페이 정기 결제
            } else if ($data['pay_type'] == '27') {
                $sbkey = $pdo->assoc("select * from {$tbl['subscription_key']} where ono=?", array($sbono));
                if ($sbkey && $sbkey['pg'] == 'nsp') {
                    $pay = new NaverSimplePay($scfg);
                    $ret = $pay->recurrentExpire($sbkey['recurrentId']);
                }
            }

            if (is_object($log_instance) == true) {
                $log_instance->writeln(print_r($ret, true), 'recurrent Result');
            }
            $pdo->query("update {$tbl['subscr']} set billing_key='expired' where sbono=?", array($sbono));

            return true;
        }
        return false;
    }

?>