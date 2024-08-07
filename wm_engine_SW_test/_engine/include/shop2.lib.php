<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  장바구니/주문처리 라이브러리
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir.'/_engine/include/wingPos.lib.php';
	include_once $engine_dir.'/_engine/include/cart.class.php';

	$_cart_where = array(""," and p.`no_interest`='Y'"," and p.`no_interest`!='Y'", " and p.`checkout`='Y'", " and p.`use_talkpay`='Y'");
	if(isset($cfg['use_qty_discount']) == true && $cfg['use_qty_discount'] == 'Y') {
		$_cart_where[3] .= " and p.qty_rate=''";
	}

    // 개별 사용 쿠폰 세팅 여부
    if(fieldExist($tbl['coupon_download'], 'cart_no') == true) {
        define('__USE_PRODUCT_COUPON__', true);
    }

	function cartList($split_big="/",$split_small=":",$opt_deco1="",$opt_deco2="",$w=0,$h=0,$cart_where="") {
		global
			$tbl, $pdo,
			$cfg,
			$cartRes,
			$cart_rows,
			$total_cart_rows,
			$total_dlv_alone_rows,
			$mwhere,
			$cpn_cart,
			$_cart_where,
			$cart_selected,
			$delivery_fee_type,
			$ptnOrd,
			$sbscr_yn;

		if(isset($cartRes) == false) {
			getTsPrd();

			$cart_selected = preg_replace('/^[^0-9]+|[^0-9]+$/', '', $cart_selected);
			if ($cart_selected) {
				$selected_where = " and c.`no` in ($cart_selected)";
				if ($cfg['use_set_product'] == 'Y') { // 선택 상품 내 세트 검색 후 구성품 하나만 선택 주문해도 전체 세트 호출
					$_tmp = array();
					$_res = $pdo->iterator("select distinct set_idx from {$tbl['cart']} c where set_idx!='' and c.no in ($cart_selected) ".mwhere('c.'));
                    foreach ($_res as $_data) {
						$_tmp[] = "'".$_data['set_idx']."'";
					}
					$_tmp = implode(',', $_tmp);
					if ($_tmp) {
						$selected_where = " and (c.no in ($cart_selected) or c.set_idx in ($_tmp))";
					}
				}
			}

			$mwhere = mwhere('c.');
			if($cart_where) {
				$cart_where_str = $_cart_where[$cart_where];
				if(!$cart_where_str) {
					msg(__lang_common_error_ilconnect__, '/', 'parent');
				}
				if($cart_where == 3) { // 네이버페이에서 개인결제창 제외
					$chk_private = array();
					$res = $pdo->iterator("select no from {$GLOBALS['tbl']['category']} where private='Y'");
                    foreach ($res as $data) {
						$chk_private[] = $data['no'];
					}
					if(count($chk_private) > 0) {
						$_temp = implode(',', $chk_private);
						$cart_where_str .= " and big not in ($_temp) and mid not in ($_temp) and small not in ($_temp)";
					}
				}
			}

			$cart_tbl = ($sbscr_yn=='Y') ? $tbl['sbscr_cart']:$tbl['cart'];

			// 장바구니에 삭제된 옵션이 있는지 필터
            $cres = $pdo->iterator("select no, option_idx from $cart_tbl c where 1 $mwhere");
            foreach ($cres as $chk) {
                if ($chk['option_idx']) {
                    $_oidx = explode('<split_big>', $chk['option_idx']);
                    foreach ($_oidx as $val) {
                        list ($_opno, $_ino) = explode('<split_small>', $val);
                        $exists = $pdo->row("select count(*) from {$tbl['product_option_set']} where no='$_opno'"); // 옵션세트 체크
                        if ($exists > 0 && (int) $_ino > 0) {
                            $exists = $pdo->row("select count(*) from {$tbl['product_option_item']} where no='$_ino'"); // 옵션아이템 체크
                        }
                        if ($exists == 0) {
                           $pdo->query("delete from {$tbl['cart']} where no={$chk['no']}");
                        }
                    }
                }
            }

			if($cfg['use_partner_delivery'] == 'Y') {
				$orderby = "partner_no asc, c.no asc";
			} else {
				$orderby = "c.no asc";
			}

			// 텍스트 필드를 제외한 상품 필드 select
			$add_field = array();
			$_tmp = $pdo->iterator("show columns from {$tbl['product']}");
            foreach ($_tmp as $_tdata) {
				if(preg_match('/text$/i', $_tdata['Type']) == true) continue;
				$add_field[] = "p.`{$_tdata['Field']}`";
			}
			$add_field = implode(', ', $add_field);

			if($cfg['use_no_mile/cpn'] == 'Y') {
				$add_field .= ", p.no_milage, p.no_cpn";
			}

            if ($cfg['use_talkpay'] == 'Y') {
                $add_field .= ", p.use_talkpay";
            }

			// 세트 상품
			if ($sbscr_yn != 'Y' && $cfg['use_set_product'] == 'Y') {
				$add_field .= ", c.set_pno, c.set_idx";
                $orderby = ' c.set_idx asc, '.$orderby;
			}

			$add_field2 = ", c.`etc`,c.`etc2`, c.`price_no`, c.`anx_no` as anx_no, c.etc";
			if($sbscr_yn=='Y') {
				$add_field2 = ", c.`date_list`, c.`dlv_cnt`, c.period, c.week, c.start_date, c.end_date";
			}
			$cart_tbl = ($sbscr_yn=='Y') ? $tbl['sbscr_cart']:$tbl['cart'];

			$sql = "select $add_field, c.no as cno, c.option_idx, c.complex_no, c.no as cno, c.pno, c.buy_ea, c.member_no, c.guest_no, c.reg_date,c.option,c.option_prc, c.option_idx, c.complex_no, p.`free_delivery` as free_dlv $add_field2 from `".$tbl['product']."` p, `".$cart_tbl."` c where p.`no`=c.`pno` $mwhere $cart_where_str $selected_where order by $orderby";
			$cartRes = $pdo->iterator($sql);
			$cart_rows = $cartRes->rowCount();
			$total_cart_rows += $cart_rows;

			if(!$cart_rows) {
				if($no_cart_alert) {
					msg(__lang_shop_error_nocart2__, $root_url.'/shop/cart.php', 'parent');
				}
			}
            $total_dlv_alone_rows = 0;
		}

		$cart = $cartRes->current();
        $cartRes->next();
		if(!$cart['no']) {
			if($_SERVER['SCRIPT_NAME'] != '/shop/order.php') {
				$cartRes = null;
			}
			return;
		}
		$cart = shortCut($cart);
		$cpn_cart[] = $cart;

		// 정기배송(무기한) 일 경우 세일 회차 제외된 상태 , 추가로 고려해야할 사항
		if($sbscr_yn=='Y') {
            if($cfg['sbscr_type'] == 'P') {
                $sbscr_data = $pdo->assoc("select ss.sale_use, ss.sale_ea, ss.sale_percent from $tbl[sbscr_set_product] as ssp inner join $tbl[sbscr_set] as ss on ssp.setno=ss.no where pno='$cart[pno]' and `use`='Y'");
                if($sbscr_data['sale_use']=='Y') {
                    $cart['sale_use'] = 'Y';
                    $cart['sale_ea'] = $sbscr_data['sale_ea'];
                    $cart['sale_percent'] = $sbscr_data['sale_percent'];
                }
            } else {
				$cart['sale_use'] = "Y";
				$cart['sale_ea'] = $cfg['sbscr_sale_ea'];
				$cart['sale_percent'] = $cfg['sbscr_sale_percent'];
            }
		}

		$cart['name'] = stripslashes($cart['name']);
		$cart['prd_prc_str'] = parsePrice($cart['sell_prc'], true); // 옵션 제외 개별 상품가격
		$cart['prd_r_prc_str'] = showExchangeFee($cart['sell_prc']);
		$cart['sum_prd_prc_str'] = parsePrice($cart['sell_prc']*$cart['buy_ea'], 2); // 옵션 제외 총상품가격
		$cart['sum_r_prd_prc_str'] = showExchangeFee($cart['sell_prc']*$cart['buy_ea']);
		for($i = 1; $i <= 3; $i++) {
			if($cfg['use_prd_etc'.$i] == 'Y') $cart['etc'.$i] = stripslashes($cart['etc'.$i]);
		}

		// 옵션이 있을 경우
		$cart['option_str'] = $ostr_tmp = ''; // 상품 옵션 정보
		$cart['option_str2'] = $ostr2_tmp = ''; // 상품 옵션 정보(추가 금액 포함)
		if($cart['option']) {
			$_oprc = 0;
			$tmp0 = explode("<split_big>", $cart['option']); // 옵션
			$tmp1 = explode("<split_big>", $cart['option_prc']); // 옵션가격
			$oidx = explode("<split_big>", $cart['option_idx']); // 옵션키
            $cart['option'] = '';
			foreach ($oidx as $key => $val) {
                list($opno, $ino) = explode('<split_small>', $val);
                list($oname, $iname) = explode('<split_small>', $tmp0[$key]);
				$_oprc += $tmp2[0];

                // 옵션 가격 정보
                $_idata = $pdo->assoc("
                    select iname, add_price from {$tbl['product_option_item']} where opno=? and no=?
                ", array(
                    $opno, $ino
                ));
                $_add_price = $_idata['add_price'];
                if ($_idata['iname']) $iname = stripslashes($_idata['iname']);

                // 텍스트옵션 체크
                if ($ino == '0') {
                    $_odata = $pdo->assoc("select name, otype from {$tbl['product_option_set']} where no=?", array($opno));
                    $oname = stripslashes($_odata['name']);
                    if ($_odata['otype'] == '4B') {
                        $topt = getTextOptionPrc($opno, $iname);
                        $_add_price = $topt['price'];
                    }
                }
                if ($cart['option']) $cart['option'] .= '<split_big>';
                $cart['option'] .= $oname.'<split_small>'.$iname;

                // 추가 금액 합산
                if ($_add_price != 0) {
                    list($_add_price2, $how_cal) = explode('<split_small>', $tmp1[$key]);
                    if ($how_cal == '4') { // 면적
                        $_add_price = $_add_price2;
                        $cart['sell_prc'] = $_add_price;
                    } else if ($how_cal == '3') { // 면적 합산
                        $_add_price = $_add_price2;
                        $cart['sell_prc'] += $_add_price;
                    } else if ($how_cal == '2') {
                        $cart['sell_prc'] *= $_add_price;
                    } else { // 일반 합산
                        $cart['sell_prc'] += $_add_price;
                    }
                    $_oprc += $_add_price;
				}

				// 출력되는 옵션명
				$str = stripslashes($oname.$split_small.$iname);
                if ($ostr2_tmp) {
                    $str = $split_big.$str;
                }
                $ostr2_tmp .= $str;
                $ostr_tmp .= $str;
				if($_add_price != 0) {
    				$_plus = ($_add_price > 0) ? '+' : '';
                    $ostr2_tmp .= " <span class='option_add_prc'>($_plus".parsePrice($_add_price, true).")</span>";
                }
			}
			$cart['option_str'] = $opt_deco1.$ostr_tmp.$opt_deco2;
			$cart['option_str2'] = $opt_deco1.$ostr2_tmp.$opt_deco2;
			$cart['option_prc'] = $_oprc;
			$cart['option_prc_str'] = parsePrice($_oprc, true);
		}

		// 기타메세지 추가
		 $cart['etc'] = ($cart['etc']?stripslashes($cart['etc']):'') ;

		$cart['sum_sell_prc'] = $cart['sell_prc']*$cart['buy_ea']; // 현 상품 총액
		$cart['link'] = $root_url.'/shop/detail.php?pno='.$cart['hash']; // 상품 개별 링크

		// 상품 이미지
		$img = prdImg(3, $cart, $w, $h);
		$cart['img'] = $img[0];
		$cart['imgstr'] = $img[1];

		// 무료배송 상품 표시
		if($cart['free_dlv'] == 'Y' && $cfg['delivery_type'] == 3 && $cfg['delivery_prd_free'] == 'Y') {
			$cart['name'] .= " <span class='cart_free_dlv_info'>[".__lang_shop_info_freedlv__."]</span>";
			$free_delivery .= $cart['no']."@";
		}

		// 개별상품 쿠폰
		if(defined('__USE_PRODUCT_COUPON__') == true) {
			$cart['prdcpn_no'] = $pdo->row("select group_concat(no separator '@') from $tbl[coupon_download] where ono='' and cart_no='$cart[cno]'");
		}

		if($cart['dlv_alone'] == 'Y') $total_dlv_alone_rows++;

		$cart['today_dlv'] = "";
		if($cfg['compare_today_start_use'] == 'Y') {
			$cart['today_dlv'] = ($cart['compare_today_start']=='Y') ? 'Y':'';
			$cart['today_time'] = $cfg['compare_today_time'].":00";
		}

        // 세트 부모로부터 속성 상속받기
        if ($cfg['use_set_product'] == 'Y') {
            global $setparent;

            if ($cart['set_idx'] && $cart['set_pno'] > 0) {
                if (is_array($setparent) == false) {
                    $setparent = array();
                }
                if ($setparent[$cart['set_pno']]) {
                    $_setparent = $setparent[$cart['set_pno']];
                } else {
                    $setparent[$cart['set_pno']] = $_setparent =$pdo->assoc("select no_milage, no_cpn from {$tbl['product']} where no='{$cart['set_pno']}'");
                }
                if ($_setparent['no_milage'] == 'Y') $cart['no_milage'] = 'Y';
                if ($_setparent['no_cpn'] == 'Y') $cart['no_cpn'] = 'Y';
            }
        }

		return $cart;
	}

	// 주문 총결제액
	function totalOrderPrice($event_check_level=2) {
		return;
	}

	function offCouponUse(){
		global $tbl,$member,$offcpn,$ono,$now,$cpn_auth_code, $pdo;
		if(!$member[no]) return;
		if(!$cpn_auth_code) return;

		$cpn_auth_code=trim(strtoupper($cpn_auth_code));
		$q="insert into `$tbl[coupon_download]`(`member_no`, `member_name`, `member_id`, `cno`, `code`, `name`, `sale_prc`, `prc_limit`, `sale_limit`, `udate_type`, `ustart_date`, `ufinish_date`, `sale_type`, `use_date`, `ono`, `stype`, `is_type`, `auth_code`) values('$member[no]', '$member[name]', '$member[member_id]', '$offcpn[no]', '$offcpn[code]', '$offcpn[name]', '$offcpn[sale_prc]', '$offcpn[prc_limit]', '$offcpn[sale_limit]', '$offcpn[udate_type]', '$offcpn[ustart_date]', '$offcpn[ufinish_date]', '$offcpn[sale_type]', '$now', '$ono', '1', '$offcpn[is_type]', '$cpn_auth_code')";
		$pdo->query($q);

		if(is_object($erpListener)) {
			$no = $pdo->row("select no from $tbl[coupon_download] where auth_code='$cpn_auth_code'");
			$erpListener->setCoupon($no);
		}

		return 1;
	}

	// 이벤트 가능 여부 체크
	function checkEventAble($amember = null) {
		global $cfg, $now, $cpn_sale_only,$tbl, $pdo, $scfg;
		$r = 0;

        if (is_array($amember) == false) {
            $amember = $GLOBALS['member'];
        }

		//다중 이벤트 등록 기능 사용 여부
		if(isTable($tbl['event'])) {
			$_evt_cfg = $pdo->assoc("SELECT * FROM $tbl[event]
							WHERE event_use = 'Y'
							 AND event_begin <= '$now'
							 AND event_finish >= '$now' ORDER BY event_begin LIMIT 0,1 ");
			$cfg['event_use']     = $_evt_cfg['event_use'];
			$cfg['event_begin']   = date('Y/m/d/H/i',$_evt_cfg['event_begin']);
			$cfg['event_finish']  = date('Y/m/d/H/i',$_evt_cfg['event_finish']);
			$cfg['event_min_pay'] = $_evt_cfg['event_min_pay'];
			$cfg['event_obj']     = $_evt_cfg['event_obj'];
			$cfg['event_type']    = $_evt_cfg['event_type'];
			$cfg['event_milage_addable']  = $_evt_cfg['event_milage_addable'];
			$cfg['event_milage_addable2'] = $_evt_cfg['event_milage_addable2'];
			$cfg['event_ptype']   = $_evt_cfg['event_ptype'];
			$cfg['event_per']     = $_evt_cfg['event_per'];
			$cfg['event_round']   = $_evt_cfg['event_round'];
		}


		$now_YMD = date("YmdHi",$now);
		if($cfg['event_use'] == 'Y') { // 사용여부
			if($cfg['event_obj'] == 1 || ($cfg['event_obj'] == 2 && $amember['no']) || ($cfg['event_obj'] == 3 && $amember['level'] == '8' && $scfg->comp('use_biz_member', 'Y') == true)) { // 적용대상
				$cfg['event_begin'] = str_replace('/', '', $cfg['event_begin']);
				$cfg['event_finish'] = str_replace('/', '', $cfg['event_finish']);
				if($now_YMD >= $cfg['event_begin'] && $now_YMD <= $cfg['event_finish']) { // 적용시간
					$r = $cfg['event_type'];
				}
			}
		}
		if($cpn_sale_only == true) return 0; // 쿠폰할인 충돌
		return $r;
	}

	function checkMSaleAble() {
		global $cfg,$member,$tbl,$msale_milage_cash,$msale_delivery, $real_order_mode, $pay_type, $cpn_sale_only, $pdo;

		$r = array();
		if($cfg['member_event_use']=="Y" && $member['no']) {
			$tmp=$pdo->assoc("select milage, milage2, milage_cash, free_delivery from $tbl[member_group] where no='$member[level]'");
			if($tmp['milage']>0 || $tmp['milage2'] > 0) {
				if($cfg['member_event_type'] == 1) $tmp['milage'] = 0;
				if($cfg['member_event_type'] == 2) $tmp['milage2'] = 0;

				$r = array($tmp['milage'], $tmp['milage2']);
				$msale_milage_cash=$tmp['milage_cash']; // 할인/적립 현금결제만
			}
			if($cfg['msale_round']<10) $cfg['msale_round']=1;
			if($tmp['free_delivery'] && $cfg['mgroup_free_delivery'] == 'Y') {
				$mgroup = $pdo->assoc("select `free_delivery` from `$tbl[member_group]` where `no`='$member[level]'");
				if($mgroup['free_delivery'] == "Y") {
					$msale_delivery = 'Y';
				}
			}
		}
		if($real_order_mode == true && $msale_milage_cash == 'Y' && $pay_type != 2) {
			return array(0, 0);
		}

		if($cpn_sale_only == true) return array(0, 0);
		return $r;
	}

	// 배송비
	function deliveryPrc() {

	}

	// 추가배송비 세부설정시 계산
	function getAddPrcd($addr, $partner_no = 0) {
		global $tbl, $cfg, $_sido_mapping, $pdo;

		if(empty($addr) == true) return 0;

        if (fieldExist($tbl['delivery_area_detail'], 'partner_no') == true) {
            if($cfg['use_partner_delivery'] == 'Y') {
                if(!$partner_no) {
                    $w = " and (partner_no='0' || partner_no='')";
                } else {
                    $w = " and partner_no='$partner_no'";
                }
            } else {
                $w = " and (partner_no='0' || partner_no='')";
            }
        }

        if ($GLOBALS['juso_cache'])  {
            $res = $GLOBALS['juso_cache'];
        } else {
            $addr = urlencode(preg_replace('/\([^\)]+\)$/', '', $addr));
            if($cfg['juso_api_server'] == 2) {
                $addr .= "&confmKey=".$cfg['juso_api_key'];
            }
            $res = comm($cfg['juso_api_url'].'?currentPage=1&countPerPage=1&resultType=json&keyword='.$addr);
            $res = json_decode($res);
            $res = $res->results->juso[0];

            $GLOBALS['juso_cache'] = $res;
        }
		$sido = $_sido_mapping[$res->siNm];
		$gugun = trim($res->sggNm);
		$dong = trim($res->emdNm);
		$ri = trim($res->liNm);

		$res = $pdo->iterator("select * from $tbl[delivery_area_detail] where sido='$sido' and (gugun='$gugun' or gugun='') $w order by sort asc");
        foreach ($res as $data) {
			if(!$data['dong']) return $data['addprc']; // 동 전체

			$_dong = explode(',', $data['dong']);
			foreach($_dong as $val) {
				if($dong == $val) {
                    if ($data['ri']) {
                        $_ri = explode(',', $data['ri']);
                        foreach ($_ri as $val2) {
                            if($ri == $val2) return $data['addprc'];
                        }
                    } else {
                        return $data['addprc'];
                    }
                }
			}
		}

		return 0;
	}

	function getEMSprc($nations, $weight, $delivery_com) {
		global $cfg, $ems_nation, $ems_prc, $root_dir, $engine_dir, $pdo;

		if(!$weight) return 0;
		if(!$delivery_com) return 0;
		// 기본 box 무게 필요하면 추가해야함


		// 지역 구분
		$area_no = $pdo->row("select area_no from wm_os_delivery_country where delivery_com='${delivery_com}' and country_code='${nations}'");

		// 금액
		$_delivery_prc = $pdo->row("select price from wm_os_delivery_prc where delivery_com='${delivery_com}' and area_no='${area_no}' and weight >= '${weight}' order by weight asc limit 1");

		return $_delivery_prc;
	}

	// 배송비 문자값
	function deliveryStr($tail = '') {
		global $cfg, $ptnOrd;

		if(is_object($ptnOrd) == false) return;

		if($ptnOrd->dlv_prc > 0) {
			$r = number_format($ptnOrd->dlv_prc).$tail;
		} else if($ptnOrd->dlv_prc > 0) {
			$r = sprintf(__lang_shop_info_dlvtype__, number_format($ptnOrd->cod_prc));
		} else {
			$r = 0;
		}

		return $r;
	}

    /**
     * 배송 제한 설정
     **/
    function checkDeliveryRange($addr, $partner_no) {
        global $pdo, $tbl, $cfg, $scfg, $_sido_mapping;

        if (!$addr) {
            return array(true, null);
        }

        // 입점사별 배송 제한 타입
        if ($partner_no == 0) {
            $config = $scfg->get('dlv_possible_type');
        } else {
            $config = $pdo->row("select value from {$tbl['partner_config']} where name=? and partner_no=?", array(
                'dlv_possible_type', $partner_no
            ));
        }
        if (!$config) $config = 'N';

        if ($GLOBALS['juso_cache'])  {
            $res = $GLOBALS['juso_cache'];
        } else {
            if (gettype($addr) == 'string') {
                $addr = urlencode(preg_replace('/\([^\)]+\)$/', '', $addr));
                if ($cfg['juso_api_server'] == 2) {
                    $addr .= "&confmKey=".$cfg['juso_api_key'];
                }
                $res = comm($cfg['juso_api_url'].'?currentPage=1&countPerPage=1&resultType=json&keyword='.$addr);
                $res = json_decode($res);
                $res = $res->results->juso[0];

                $GLOBALS['juso_cache'] = $res;
            } else {
                $res = (object) $addr;
            }
        }

        $sido = $_sido_mapping[$res->siNm];
        $gugun = trim($res->sggNm);
        $dong = trim($res->emdNm);
        $ri = gettype($rss->liNm) == 'string' ? trim($res->liNm) : '';
        $reason = '';

        if (!isTable($tbl['delivery_range'])) {
            return array(true, null);
        }

        $find = 0;
        $res = $pdo->iterator("select * from {$tbl['delivery_range']} where type=? and partner_no=?", array($config, $partner_no));
        if ($res->rowCount() == 0) { // 설정 데이터 없을 경우 통과
            return array(true, null);
        }
        foreach ($res as $data) {
            $data['dong'] = $data['dong'] ? explode(',', $data['dong']) : array();
            $data['ri'] = ($data['ri']) ? explode(',', $data['ri']) : array();

            if ($data['sido'] && $data['sido'] != $sido) continue; // 시도 불일치
            if ($data['gugun'] && $data['gugun'] != $gugun) continue; // 구 불일치
            if (count($data['dong']) > 0 && in_array($dong, $data['dong']) == false) continue; // 동 불일치
            if (count($data['ri']) > 0 && in_array($ri, $data['ri']) == false) continue; // 리 불일치

            if ($config == 'D') $reason = $data['reason'];

            $find++;
        }

        if ($config == 'D' && $find > 0) return array(false, $reason);
        if ($config == 'A' && $find == 0) return array(false, $scfg->get('dlv_possible_d_msg'));

        return array(true, null);
    }

	function cardDataInsert($card_tbl="", $pg=""){ // 2007-05-31
		global $cfg, $tbl,$ono,$pay_prc,$now,$member,$env_info,$cpn,$off_cpn_use,$cpn_auth_code,$_pg_charge,$_pg_charge_fee,$mobile_browser, $pdo;
		if(!$card_tbl) $card_tbl=$tbl[card];
		$cpn_auth_code=($off_cpn_use == "Y") ? $cpn_auth_code : "";

		if($pg != 'danal') {
			$pg_version=($_SESSION['browser_type'] == 'mobile' && $cfg['mobile_use'] == 'Y') ? $cfg['pg_mobile_version'] : $cfg['pg_version'];
		} else {
			$pg_version=($mobile_browser == 'mobile' ) ? "mobile" : "pc";
		}

        $exists = $pdo->row("select no from $card_tbl where wm_ono=?", array(
            $ono
        ));
        if ($exists > 0) {
            return $pdo->query("update $card_tbl set stat=1 where wm_ono=?", array(
                $ono
            ));
        }

		$r = $pdo->query("INSERT INTO $card_tbl ( `wm_ono`, `wm_price`, `stat`, `reg_date`, `member_no`, `guest_no`, `pg`, `pg_version`, `env_info`, `cpn_no`, `cpn_auth_code`) VALUES ( '$ono', '$pay_prc', '1', '$now' , '$member[no]', '$_SESSION[guest_no]', '$pg', '$pg_version', '$env_info', '$cpn[no]', '$cpn_auth_code')");
		// PG 결제시 가격 인상 정보 업데이트
		if($_pg_charge != "" && $card_tbl == $tbl[card]){
			$pdo->query("update $card_tbl set `pg_charge`='$_pg_charge:$_pg_charge_fee' where `wm_ono`='$ono' limit 1");
		}
		return $r;
	}

	// 상품 재고 체크
	function prdCheckStock($prd, $buy_ea, $multi = 0) {
		global $engine_dir, $tbl, $pdo;

		if(is_array($prd) == false && numberOnly($prd) > 0) $prd = $pdo->assoc("select * from $tbl[product] where no='$prd'");
		$prd = shortCut($prd);
		$prd['name'] = php2java(strip_tags(stripslashes($prd['name'])));

		// 주문수량 체크
		if($prd['max_ord'] > 0 && $buy_ea > $prd['max_ord'] ) {
			if ($_REQUEST['from_ajax'] == 'Y') {
                exit(sprintf(__lang_shop_error_maxord2__, $prd['max_ord']));
            }
            javac("
            if (typeof parent.dialogConfirmClose == 'function') {
                parent.dialogConfirmClose();
            }
            ");
            msg(sprintf(__lang_shop_error_maxord2__, $prd['max_ord']));
        }
		if($buy_ea < $prd['min_ord']) msg(sprintf(__lang_shop_error_minord2__, $prd['min_ord']));

		// 상품 한정수량 체크
		if($prd['stat'] == 3) msg(sprintf(__lang_shop_error_soldout__, $prd['name']));
		if($prd['ea_type'] == 3 && $buy_ea >$prd['ea']) {
			if($prd['ea'] == 0) msg(sprintf(__lang_shop_error_soldout__, $prd['name']));
			else msg(sprintf(__lang_shop_error_maxord__, $prd['name'], $prd['ea']));
		}

		$prdcpn_no = $_POST['prdcpn_no'];
		if($multi) {
			$prdcpn_no = $prdcpn_no[$multi];
		}

		// 필수 옵션 체크
		$odx = 0;
		$copt = array();
		$anx = array();
		$ores = $pdo->iterator("select no, name, necessary, ea_ck, how_cal, unit, otype from $tbl[product_option_set] where stat=2 and pno=$prd[parent] order by necessary='P' asc, necessary='N' asc, sort asc");

        foreach ($ores as $odata) {
			$odx++;

			$odata['name'] = stripslashes($odata['name']);
			$postdata = $_POST['option'.$odx];
			if($multi) {
				$postdata = $postdata[$multi];
			}

			if($odata['necessary'] == 'P') {
				$anx[$odx] = $postdata;
				continue;
			}

			if($postdata) {
				if($odata['how_cal'] == 3 || $odata['how_cal'] == 4) {
					$result = getAreaOptionData($postdata, $_POST['option_area'.$odx], $odat);
					if($result['errmsg']) msg(addslashes($result['errmsg']));

					$option		.= $result['option'];
					$option_prc	.= $result['option_prc'];
					$option_idx	.= $result['option_idx'];
				} else if($odata['otype'] == '4B') {
					$postdata = trim($postdata);
					$topt = getTextOptionPrc($odata['no'], $postdata);
					$o_prc = $topt['price'];
					$topt = $topt['data'];

					// 양식 체크
					$_len = mb_strlen($postdata, _BASE_CHARSET_);
					$attr1 = explode(',', $topt['deco1']);
					$attr2 = str_split($topt['deco2'], 1);
					if($topt['min_val'] > 0 && $_len < $topt['min_val']) {
						msg(sprintf('"%s" 옵션은 %s자 이상 입력하셔야 합니다.', $odata['name'], $topt['min_val']));
					}
					if($topt['max_val'] > 0 && $_len > $topt['max_val']) {
						msg(sprintf('"%s" 옵션은 %s자 까지 입력 가능합니다.', $odata['name'], $topt['max_val']));
					}
					if(in_array('1', $attr1) == true) { // 영문 입력 금지
						if(preg_match('/[a-z]/i', $postdata) == true) {
							msg(sprintf('"%s" 옵션에는 영문 알파벳을 입력하실 수 없습니다.', $odata['name']));
						}
					}
					if(in_array('2', $attr1) == true) { // 한글 입력 금지
						if(preg_match('/[ㄱ-힣]/', $postdata) == true) {
							msg(sprintf('"%s" 옵션에는 한글을 입력하실 수 없습니다.', $odata['name']));
						}
					}
					if(in_array('3', $attr1) == true) { // 스페이스 입력 불가
						if(preg_match('/ /', $postdata) == true) {
							msg(sprintf('"%s" 옵션에는 공백을 입력하실수 없습니다.', $odata['name']));
						}
					}
					if(in_array('4', $attr1) == true) { // 특수문자 입력 불가
						$_postdata = $postdata;
						foreach($attr2 as $val) $_postdata = str_replace($val, '', $_postdata);
						if(preg_match('/[[:punct:]]/', $_postdata) == true) {
							msg(sprintf('"%s" 옵션에는 특수문자를 입력하실수 없습니다.', $odata['name']));
						}
					}
					if(in_array('5', $attr1) == true) { // 숫자 입력 금지
						if(preg_match('/[0-9]/i', $postdata) == true) {
							msg(sprintf('"%s" 옵션에는 숫자를 입력하실 수 없습니다.', $odata['name']));
						}
					}

					$option		.= '<split_big>'.$odata['name'].'<split_small>'.$postdata;
					$option_prc .= "<split_big>$o_prc<split_small>1";
					$option_idx .= "<split_big>$odata[no]<split_small>0";
				} else {
					list($o_name, $o_prc, $o_ea, $o_idx, $comp_no) = explode('::', $postdata);

					// 부가상품 추가가격 재계산
					$comp_no = numberOnly($comp_no);
					if($comp_no > 0) {
						$o_prc = 0;
						$copt = $pdo->row("select opts from erp_complex_option where complex_no='$comp_no'");
						$copt = explode('_', trim($copt, '_'));
						$opts = implode(',', $copt);
						if($opts) {
							$o_prc = $pdo->row("select sum(add_price) from $tbl[product_option_item] where pno='$prd[no]' and no in ($opts)");
							$o_idx = $pdo->row("select no from $tbl[product_option_item] where opno='$odata[no]' and no in ($opts)");
						}
					}

					$option_prc	.= "<split_big>$o_prc<split_small>$odata[how_cal]";
					$option_idx	.= "<split_big>$odata[no]<split_small>$o_idx";
					$data = $pdo->assoc("select s.name, i.iname from $tbl[product_option_item] i inner join $tbl[product_option_set] s on i.opno=s.no where i.opno=$odata[no] and i.no=$o_idx");
					$option .= '<split_big>'.$data['name'].'<split_small>'.$data['iname'];
				}
			}

			if($odata['necessary'] == 'N') continue; // 필수 옵션이 아니면 패스
			if(!$postdata) msg(sprintf(__lang_shop_select_opt__, $odata['name']));

			if(!$comp_no && $odata['necessary'] == 'Y' && $odata['otype'] != '4B') $copt[] = $o_idx;
		}

		// 윙포스 수량 체크
		if($prd['ea_type'] == 1) {
			$opts = makeComplexKey(implode('_', $copt));

			include_once $engine_dir.'/_engine/include/wingPos.lib.php';
			$complex_no = $pdo->row("select complex_no from erp_complex_option where pno='$prd[parent]' and opts='$opts' and del_yn='N'");
			$ret = stockCheck($complex_no, $buy_ea, $prd['name']);
			if($ret) msg($ret);
		}

		$option			= substr($option,11);
		$option_prc		= substr($option_prc,11);
		$option_idx		= substr($option_idx,11);

		return array(
			'option' => $option,
			'option_prc' => $option_prc,
			'option_idx' => $option_idx,
			'complex_no' => $complex_no,
			'anx' => $anx,
			'prdcpn_no' => addslashes($prdcpn_no),
		);
	}

	function getAreaOptionData($postdata, $option_area, $odata=null) {
		global $tbl, $pdo;

		$o_name = $o_idx = $total = null;
		foreach($postdata as $key => $val) {
			$item_no = $option_area[$key];

			$item = $pdo->assoc("select * from $tbl[product_option_item] where no='$item_no'");
			$item['iname'] = stripslashes($item['iname']);

			if(!$odata) $odata = $pdo->assoc("select * from $tbl[product_option_set] where no='$item[opno]'");

			$val = numberOnly($val, true);
			if(!$val) $error = sprintf(__lang_shop_input_prdopt__, $odata['name'], $item['iname']);
			if($item['min_val'] > 0 && $val < $item['min_val']) $error = sprintf(__lang_shop_input_maxArea__, $odata['name'], $item['iname'], $item['min_val']);
			if($item['max_val'] > 0 && $val > $item['max_val']) $error = sprintf(__lang_shop_input_minArea__, $odata['name'], $item['iname'], $item['min_val']);

			$o_name .= $o_name ? ' x '.$val.' '.$odata['unit'] : $val.' '.$odata['unit'];
			$o_idx .= $o_idx ? "<split_area>$item[no]@$val" : "$item[no]@$val";

			if($error) $val = 0;
			$total = $total > 0 ? $total*$val : $val;
		}

		if($total > 0  && $item['min_area'] > 0 && $item['min_area'] > $total) {
			$total = 0;
			if($data['min_area_option'] == 'N') $error = sprintf(__lang_shop_errora_totalArea__, $item['min_area'], $item['unit']);
			else $total = $item['min_area'];
		}

		$o_prc = $total*$item['add_price'];
		$o_prc = floor($o_prc);
		if($item['add_price_option'] > 0) {
			$minus = $o_prc % ($item['add_price_option']*10);
			$o_prc -= $minus;
		}


		return array(
			'option'		=> "<split_big>$odata[name]<split_small>$o_name",
			'option_prc'	=> "<split_big>$o_prc<split_small>".$odata['how_cal'],
			'option_idx'	=> "<split_big>$odata[no]<split_small>$o_idx",
			'how_cal'		=> $odata['how_cal'],
			'opno'			=> $item['opno'],
			'price'			=> $o_prc,
			'errmsg'		=> $error
		);
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  상품별/카테고리별 쿠폰 할인 가능 여부 확인
	' +----------------------------------------------------------------------------------------------+*/
	function isCpnAttached($cpn, $prd) {
		global $cfg;

        //세트상품은 쿠폰적용 제한
        if ($prd['set_pno'] > 0) return false;

		$rule = preg_replace('/^\[|\]$/', '', $cpn['attach_items']);
		$rule = explode('][', $rule);

		// 상품, 카테고리 체크
		switch($cpn['attachtype']) {
			case 1 :
				$check_cate = getPrdAllCates($prd);
				if(in_array2($check_cate, $rule) == false) return false;
			break;
			case 2 :
				if (!in_array($prd['pno'], $rule)) {
                    if (isset($prd['set_pno']) == false || $prd['set_pno'] < 1 || in_array($prd['set_pno'], $rule) == false) {
                        return false;
                    }
                }
			break;
			case 3 :
				$check_cate = getPrdAllCates($prd);
				if(in_array2($check_cate, $rule) == true) return false;
			break;
			case 4 :
				if(in_array($prd['pno'], $rule)) return false;
				if ($prd['set_pno'] > 0 && in_array($prd['set_pno'], $rule)) return false;
			break;
		}

		// 입점 파트너 체크
		if($cfg['use_partner_shop'] == 'Y' && $cpn['partner_type'] != 0) {
			if($cpn['partner_type'] == 1 && $prd['partner_no'] != 0) return false;
			if($cpn['partner_type'] == 2 && $prd['partner_no'] != $cpn['partner_no']) return false;
			if($cpn['partner_type'] == 3 && $prd['partner_no'] != 0 && $prd['partner_no'] != $cpn['partner_no']) return false;
		}

		return true;
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  장바구니에 상품 넣기
	' +----------------------------------------------------------------------------------------------+*/
	function insertCart($hash, $key = null, $ori_cno = 0) {
		global $tbl, $cfg, $member, $now, $buy_ea, $mwhere, $from_wish, $cstr, $_recent_no, $next, $pdo;

		if(!$hash) return false;

		$_buy_ea = (is_array($buy_ea)) ? $buy_ea[$key] : $buy_ea;

		if (empty($_buy_ea) == true) {
			$_buy_ea = 1;
		}

		$prd = checkPrd($hash, true);
		$msg = getPrdBuyLevel($prd);
        if (isset($_REQUEST['from_ajax']) == true && $prd['prd_type'] != '1') { // 세트 상품을 ajax 장바구니 넣기 실행 시 상품 레이어 열기
            $msg = $prd['no'];
        }
		if($msg) {
			if($_REQUEST['from_ajax']) {
				echo $msg;
			} elseif($_REQUEST['accept_json'] == 'Y') {
                exit(json_encode(array(
                    'result' => 'fail',
                    'message' => $msg
                )));
            } else {
				alert($msg);
			}
			exit;
		}

		if($cfg['use_prc_consult'] == 'Y' && $prd["sell_prc_consultation"]!="") {
			if(!$prd["sell_prc_consultation_msg"]) $prd["sell_prc_consultation_msg"] = "주문 전 협의가 필요한 상품입니다.";
			msg(php2java($prd["sell_prc_consultation_msg"]));
		}

		if($_REQUEST['from_ajax']) {
			$optcnt = $pdo->row("select count(*) from $tbl[product_option_set] where pno='$prd[parent]'");
			if($optcnt > 0) {
				echo $prd['no'];
				exit;
			}
		}

		/*
		if(!$prd['tax_free']) $prd['tax_free'] = 'N';
		$other_tax = $pdo->row("select count(*) from $tbl[cart] c inner join $tbl[product] p on c.pno = p.no where tax_free != '$prd[tax_free]' ".mwhere('c.'));
		if($other_tax > 0) msg(__lang_shop_error_mixTax2__);
		*/

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

		// 저장
		if ($cfg['use_set_product'] == 'Y') { // 세트상품 구분
			$set_pno = $GLOBALS['set_pno'];
			$set_idx = $GLOBALS['set_idx'];
			$csql = " and set_idx='$set_idx'";
		}
		$old = $pdo->assoc("select * from `$tbl[cart]` where `pno`='{$prd['parent']}' and `price_no`='' and `option`='$option_q' and no !='$ori_cno' $csql ".$mwhere);
		if($old) {
			if($ori_cno > 0) msg(__lang_shop_error_alreadySelected__);
			if($_recent_no) $_recent_no .= ',';
			$_recent_no .= $old['no'];
		}

		if(!$old) { // 신규
			if($prd['max_ord'] > 0 || $prd['max_ord_mem'] > 0) {
				checkMaxOrd($prd, $_buy_ea, $ori_cno);
			}
			if ($prd['prd_type'] == '4' || $prd['prd_type'] == '5' || $prd['prd_type'] == '6') return; // 세트 상품 본체는 장바구니에 들어가지 않음.
			if($ori_cno > 0) {
				$ori_data = $pdo->assoc("select * from {$tbl['cart']} where no='$ori_cno'");
				if ($ori_data['set_idx'] && $ori_data['buy_ea'] != $_buy_ea) {
					msg('세트상품은 수량 변경이 불가능합니다.');
				}

				$sql = "update {$tbl['cart']} set `option`='$option_q', buy_ea='$_buy_ea', option_prc='$option_prc', option_idx='$option_idx', complex_no='$complex_no', etc='$cstr' where no='$ori_cno'";
			} else {
				$asql1 = $asql2 = '';
				if ($cfg['use_set_product'] == 'Y') {
					$asql1 .= ", set_pno, set_idx";
					$asql2 .= ", '$set_pno', '$set_idx'";
				}
				$sql = "insert into {$tbl['cart']} (`pno`,`option`,`buy_ea`,`reg_date`,`member_no`,`guest_no`,`option_prc`,`option_idx`,`complex_no`,`etc` $asql1) values ('$prd[parent]', '$option_q', '$_buy_ea', '$now', '$member[no]', '$_SESSION[guest_no]', '$option_prc','$option_idx','$complex_no', '$cstr' $asql2)";
			}
			if($pdo->query($sql) == false) {
                msg('장바구니 저장 중 오류가 발생하였습니다.');
            }

			$old['no'] = $pdo->lastInsertId();
			if($_recent_no) $_recent_no .= ',';
            if($old['no'] > 0) {
    			$_recent_no .= $old['no'];
            }

			if($ori_cno < 1) {
				ctrlPrdHit($prd['parent'], 'hit_cart', '+1'); // 장바구니 입력 횟수 증가
			}
		} elseif($next == 'checkout' || $next == 'talkpay' || $next == 'talkpay_direct') {
			if($prd['max_ord'] > 0 || $prd['max_ord_mem'] > 0) {
				checkMaxOrd($prd, $_buy_ea, $old['no']);
			}
			$pdo->query("update `$tbl[cart]` set `buy_ea`='$_buy_ea' where `no`='$old[no]'");
		} else {
			if($cfg['cart_dup'] > 1) {
				if($cfg['cart_dup'] == 3) { // 새로운 수량만큼 추가
					$tmp_ea = $old['buy_ea'] + $_buy_ea;
				} else if($cfg['cart_dup'] == 2) { // 새로운 수량으로 변경
					$tmp_ea = $_buy_ea;
				}
				if($prd['max_ord'] > 0 || $prd['max_ord_mem'] > 0) {
					checkMaxOrd($prd, $_buy_ea, $old['no']);
				}

				prdCheckStock($prd, $tmp_ea, $key); // 구매수량 및 재고 다시 체크

				$pdo->query("update $tbl[cart] set buy_ea='$tmp_ea' where no='$old[no]'");
				ctrlPrdHit($prd['parent'], 'hit_cart', '+1');
			}

			$_recent_no .= ','.$old['no'];
		}

		// 개별 상품 쿠폰 장바구니와 연결
        if (defined('__USE_PRODUCT_COUPON__') == true) {
            if (is_array($old) == true && $old['no'] > 0) {
                $pdo->query("update {$tbl['coupon_download']} set cart_no='' where cart_no='{$old['no']}' and ono=''");
                if($prdcpn_no && $old['no'] > 0) {
                    $tmp = preg_replace('/[^0-9,]/', '', str_replace('@', ',', trim($prdcpn_no, '@')));
                    $pdo->query("update {$tbl['coupon_download']} set cart_no='{$old['no']}' where no in ($tmp) and ono=''");
                }
            }
        }

		return array(
			'option' => $option,
			'option_idx' => $option_idx,
			'option_prc' => $option_prc,
			'complex_no' => $complex_no,
			'anx' => $anx
		);
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  최대 구매 수량 체크
	' +----------------------------------------------------------------------------------------------+*/
	function checkMaxOrd($prd, $buy_ea, $cno = 0) {
		global $tbl, $member, $now, $pdo;

		$where = '';
		if($cno > 0) {
			$where .= " and no!='$cno'";
		}
		$cart_cnt = $pdo->row("select sum(buy_ea) from {$tbl['cart']} where pno='{$prd['no']}' $where ".mwhere());
		$cart_cnt += $buy_ea;

		// 1회 주문한도
		if($prd['max_ord'] > 0 && $prd['max_ord'] < $cart_cnt) {
			$pdo->query("delete from {$tbl['cart']} where reg_date='$now' ".mwhere());
			msg(sprintf(__lang_shop_error_maxord2__, $prd['max_ord']));
		}

		// 최대 구매수량
		if($prd['max_ord_mem'] > 0) {
			if(!$member['no']) {
				msg(__lang_shop_error_maxord5__, $root_url.'/member/login.php?rURL='.urlencode($_SERVER['HTTP_REFERER']), 'parent');
			}
			$buy_cnt = $pdo->row("select sum(buy_ea) from {$tbl['order']} o inner join {$tbl['order_product']} op using(ono) where o.member_no='{$member['no']}' and op.pno='{$prd['no']}' and op.stat<10");
			if($buy_cnt+$cart_cnt > $prd['max_ord_mem']) {
				$pdo->query("delete from {$tbl['cart']} where reg_date='$now' ".mwhere());
				msg(sprintf($prd['name']."\\n".__lang_shop_error_maxord4__, $prd['max_ord_mem']));
			}
		}
	}

    /**
     * 세트상품을 장바구니 추가
     *
     * @param set_idx 세트 일련 번호
     * @param set_idx 세트 상품 번호
     * @param pno     장바구니에 추가 될 상품 번호
     * @param buy_ea  주문 수량
     **/
    function insertSetCart($set_idx, $set_pno, $pno, $buy_ea) {
        global $pdo, $tbl, $cfg;

        $compare_res = $pdo->iterator("select set_idx, count(*) as cnt from {$tbl['cart']} where set_pno='$set_pno' ".mwhere().' group by set_idx');
        foreach ($compare_res as $set_grp) {
            if ($set_grp['cnt'] != count($pno)-1) {
                continue;
            }

            $equal_check = 0; // 기존 장바구니와 새로 추가된 장바구니 상품/옵션 일치 갯수
            foreach($pno as $key => $val) {
                $_prd = checkPrd($val);
                if ($_prd['prd_type'] != '1') continue;

                $chk = prdCheckStock($_prd, $buy_ea, $key);
                $chkcart = $pdo->row("select count(*) from {$tbl['cart']} where set_idx=? and pno=? and option_idx=? ".mwhere(), array(
                    $set_grp['set_idx'], $_prd['parent'], $chk['option_idx']
                ));
                if ($chkcart == 1) {
                    $equal_check++;
                }
            }
            if ($equal_check == count($pno)-1) { // 장바구니에 있는 세트와 모든 구성 및 옵션이 일치
                $set_idx = $set_grp['set_idx'];

                if ($cfg['cart_dup'] == '1') { // 현재 수량 유지
                    //
                }
                else if ($cfg['cart_dup'] == '2') { // 새로운 수량으로 변경
                    $pdo->query("update {$tbl['cart']} set buy_ea='$buy_ea' where set_idx='$set_idx' ".mwhere());
                }
                else if ($cfg['cart_dup'] == '3') { // 새로운 수량만큼 추가
                    $pdo->query("update {$tbl['cart']} set buy_ea=buy_ea+$buy_ea where set_idx='$set_idx' ".mwhere());
                }
                return;
            }
        }

        // 중복 세트 없음
        foreach($pno as $key => $val) {
            if ($val) insertCart(addslashes($val), $key);
        }
        return;
    }

    /**
     * 세트 번호 생성
     **/
    function createSetIdx()
    {
        // 회원 정보
        $set_idx  = ($_SESSION['m_member_id']) ? $_SESSION['m_member_id'] : $_SESSION['guest_no'];
        $set_idx .= microtime();
        $set_idx .= rand(0, 999999);

        return md5($set_idx);
    }

?>