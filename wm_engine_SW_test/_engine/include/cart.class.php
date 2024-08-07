<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  주문서
	' +----------------------------------------------------------------------------------------------+*/
	class OrderCart {

        protected $pdo;
		private $conf;
		private $ptns;
		private $loop;
		private $completed = false;
		private $sum_prd_prc = 0; // 총 업체 주문금액
		private $sum_normal_prc = 0; // 총 소비자가
		private $sum_sell_prc = 0; // 총 업체 실 결제금액
		public  $dlv_prc = 0; // 총 주문배송비
		public  $add_dlv_prc = 0; // 총 지역별 배송비
		public  $total_order_price = 0; // 총 주문금액(상품+배송비)
		public  $pay_prc = 0; // 실 결제 금액
		public  $free_dlv_prc = 0; // 총 할인된 배송비
		private $basic_dlv_prc = 0; // 일반 배송비 합계
		private $prd_dlv_prc = 0; // 개별상품 배송비 합계
		public  $total_sale4_prc; // 총 회원혜택 대상 금액
		public  $total_sale2_prc; // 총 이벤트 대상 금액
		public  $total_sale3_prc; // 총 타임세일 대상 금액
		public  $total_sale5_prc; // 총 쿠폰할인 대상 금액
		public  $total_milage = 0; // 상품적립금+이벤트적립금+회원적립금
		public  $member_milage = 0;
		public  $event_milage = 0;
		public  $sale0 = 0;
		public  $sale1 = 0;
		public  $sale2 = 0;
		public  $sale3 = 0;
		public  $sale4 = 0;
		public  $sale5 = 0;
		public  $sale6 = 0;
		public  $sale7 = 0;
		public  $sale8 = 0;
		public  $sale9 = 0;
		public  $sbscr_sale0 = 0;
		public  $sbscr_sale2 = 0;
		public  $sbscr_sale3 = 0;
		public  $sbscr_sale4 = 0;
		public  $sbscr_sale5 = 0;
		public  $sbscr_sale6 = 0;
		public  $sbscr_sale7 = 0;
		public  $sbscr_sale8 = 0;
		public  $sbscr_sale9 = 0;
		public  $sbscr_total_milage = 0;
		public  $sbscr_member_milage = 0;
		public  $sbscr_event_milage = 0;
		public  $sale2_dlv = 0;
		public  $sale4_dlv = 0;
		private	$total_sale = 0; // 총 할인금액 합계
		public  $milage_prc = 0;
		public  $emoney_prc = 0;
		public  $fee_prc = 0;
		public  $skip_dlv = 'N'; // 배송비계산 패스(상품 리스트)
		public  $oversea_free_dlv_stat = 'N';
		public  $default_delivery_fee = 0;
		public  $tax_prc = 0;
		public  $tax_use_delivery_com = 'N'; //세금 부과 배송사 여부
		public  $remain_cpn_prc = 0;
		private $taxfree_amount = 0; // 비과세 상품 결제금액
        private $non_taxfree_amount = 0; // 과세 상품 결제금액
		private $is_cash = false; // 현금 결제 여부
		private $total_sale_per1 = 0; // 할인금액/판매가 기준 할인율
		private $total_sale_per2 = 0; // 소비자가/판매가 기준 할인율
		private $total_sale_per3 = 0; // 할인금액+(소비자가-판매가)/판매가 기준 할인율
		private $set_prdcpn_no = '';
		private $sbscr_delivery_cnt = 0; // 정기배송 배송횟수
		private $sbscr_firsttime_pay_prc = 0; // 정기배송 최초결제일 결제금액
		private $no_milage = 0; // 적립금 사용 불가 상품 수
		private $no_cpn = 0; // 쿠폰 사용 불가 상품 수
        private $member; // 회원 정보

		public function __construct($options = null) {
			global $tbl, $cfg, $member, $_cart_cache, $delivery_fee_type, $scfg;

			if(is_array($options) == true) {
				foreach($options as $key => $val) {
					$this->{'opt_'.$key} = $val;
				}
			}

            $this->pdo = $GLOBALS['pdo'];
            $this->member = $member;
            if (isset($this->opt_guest) == true && $this->opt_guest == true) {
                $this->member = array(
                    'no' => 0,
                    'level' => 10
                );
            }

			$this->emoney_prc = numberOnly($_POST['emoney_prc'], true);
			$this->milage_prc = numberOnly($_POST['milage_prc'], true);
			if(!$this->emoney_prc) $this->emoney_prc = 0;
			if(!$this->milage_prc) $this->milage_prc = 0;

			if(is_array($_cart_cache)) {
				$this->conf['msale'] = $_cart_cache['msale'];
				$this->conf['esale'] = $_cart_cache['esale'];
				$this->conf['edlv'] = $_cart_cache['edlv'];
				return;
			}
			$_cart_cache = array();

			if($this->member['attr_no_sale'] == 'Y') return; // 특별회원그룹 속성
            if ($this->member['attr_no_discount'] == 'Y') return; // 특별회원그룹 속성

			// 회원할인 정보
			if(defined('__DISABLE_ORDERCART_MSALE__') == false) {
				if($cfg['member_event_use'] == 'Y' && $this->member['no'] > 0) {
					$tmp = $this->pdo->assoc("select milage, milage2, milage_cash, free_delivery from $tbl[member_group] where no=?", array(
                        $this->member['level']
                    ));
					if($cfg['member_event_type'] == 1) $tmp['milage'] = 0;
					if($cfg['member_event_type'] == 2) $tmp['milage2'] = 0;
					if($tmp['free_delivery'] != 'Y' || $cfg['mgroup_free_delivery'] != 'Y') {
						$tmp['free_delivery'] = 'N';
					}
					if($cfg['delivery_fee_type'] == 'O' || $delivery_fee_type == 'O'){
						$tmp['free_delivery'] = 'N';
					}

					$this->conf['msale'] = $_cart_cache['msale'] = array(
						'sname' => 'sale4',
						'milage_per' => $tmp['milage2'],
						'sale_per' => $tmp['milage'],
						'round' => $cfg['msale_round'],
						'cash_only' => $tmp['milage_cash'],
						'free_dlv' => $tmp['free_delivery'],
					);
				}
			}

			// 이벤트할인 정보
			if(defined('__DISABLE_ORDERCART_EVENT__') == false) {
				if(checkEventAble($this->member) > 0) {
					 if($cfg['event_obj'] == 1 || ($cfg['event_obj'] == 2 && $this->member['level'] > 0 && $this->member['level'] < 10) || ($cfg['event_obj'] == 3 && $this->member['level'] == 8 && $scfg->comp('use_biz_member', 'Y') == true)) {
						$this->conf['esale'] = $_cart_cache['esale'] = array(
							'sname' => 'sale2',
							'milage_per' => ($cfg['event_type'] == 1) ? $cfg['event_per'] : 0,
							'sale_per' => ($cfg['event_type'] == 2) ? $cfg['event_per'] : 0,
							'round' => $cfg['event_round'],
							'cash_only' => ($cfg['event_ptype'] == 2) ? 'Y' : 'N',
							'min_prc' => $cfg['event_min_pay'],
							'no_milage' => ($cfg['event_type'] == 1) ? $cfg['event_milage_addable'] : $cfg['event_milage_addable2']
						);
					}
				}

				// 무료배송 이벤트 정보
				if($cfg['freedeli_event_use'] == 'Y') {
					if($cfg['freedeli_event_obj'] == 1 || ($cfg['freedeli_event_obj'] == 2 && $this->member['level'] > 0 && $this->member['level'] < 10)) {
                        $cfg['freedeli_event_begin'] = (int) date('YmdHi', strtotime($cfg['freedeli_event_begin']));
                        $cfg['freedeli_event_finish'] = (int) date('YmdHi', strtotime($cfg['freedeli_event_finish']));
                        $now_YMD = (int) date('YmdHi');

						if($now_YMD >= numberOnly($cfg['freedeli_event_begin']) && $now_YMD <= numberOnly($cfg['freedeli_event_finish'])) {

							$event_free_dlv = 'Y';
							if($cfg['delivery_fee_type'] == 'O' || $delivery_fee_type == 'O'){
								$event_free_dlv = 'N';
							}

							$this->conf['edlv'] = $_cart_cache['edlv'] = array(
								'sname' => 'sale2',
								'min_prc' => $cfg['freedeli_event_min_pay'],
								'free_dlv' => $event_free_dlv
							);
						}
					}
				}
			}

			// 결제 방식별 할증 처리
			$this->setPGCharge();

			// 현금결제 여부
			if($_POST['pay_type'] == 2) $this->is_cash = true;
			if($_POST['milage_prc'] > 0 && $cfg['is_milage_cash'] == 'N') $this->is_cash = false;
			if($_POST['emoney_prc'] > 0 && $cfg['is_emoney_cash'] == 'N') $this->is_cash = false;
		}

		public function addCart($cart) {
			global $cfg;

			$partner_no = $cart['partner_no'];
			if($cfg['use_partner_delivery'] != 'Y') { // 본사 배송 전용 몰
				$partner_no = 0;
			}
			if($cart['dlv_type'] == 1) { // 본사 배송 전용 상품
				$partner_no = 0;
			}

			if(!$this->ptns[$partner_no]) {
				$this->ptns[$partner_no] = new PartnerCart($partner_no, $this);
			}
			$ptn = $this->ptns[$partner_no];
			return $ptn->addCart($cart);
		}

		public function setCoupon($cpndata, $sname = 'sale5') {
			if(!is_array($cpndata)) return false;

			if($cpndata['stype'] == 3) {
				$cpndata['sale_prc'] = 0;
			}

			$res = array(
				'sname' => $sname,
				'sale_nm' => 'coupon',
				'sale_per' => ($cpndata['sale_type'] == 'p') ? $cpndata['sale_prc'] : 0,
				'sale_prc' => ($cpndata['sale_type'] == 'm') ? $cpndata['sale_prc'] : 0,
				'sale_prc_over' => $cpndata['sale_prc_over'],
				'sale_limit' => $cpndata['sale_limit'],
				'round' => 1,
				'cash_only' => ($cpndata['pay_type'] == 2) ? 'Y' : 'N',
				'min_prc' => $cpndata['prc_limit'],
				'cpn_use_limit' => $cpndata['use_limit'],
				'free_dlv' => ($cpndata['stype'] == 3) ? 'Y' : 'N',
				'oversea_free_dlv' => ($cpndata['stype'] == 4) ? 'Y' : 'N',
				'idx' => $cpndata['no'],
				'data' => $cpndata,
			);

			if($sname == 'sale5') $this->conf['cpn'] = $res;
			else {
				return $res;
			}
		}

		public function setPrdPrcSale() {
			global $cfg, $tbl;

			if($cfg['prdprc_sale_use'] != 'Y') return;
			if($cfg['prdprc_sale_ptype'] == 2 && $_POST['pay_type'] != 2) return;
			if($cfg['prdprc_sale_mtype'] == 2 && !$this->member['no']) return;

			$data = $this->pdo->assoc("select no, per, unit, prd_prc from $tbl[order_config_prdprc] where prd_prc<=$this->sum_prd_prc order by prd_prc desc limit 1");

			$this->conf['prdprc'] = array(
				'sname' => 'sale6',
				'sale_per' => ($data['unit'] == 'p') ? $data['per'] : 0,
				'sale_prc' => ($data['unit'] == 'm') ? $data['per'] : 0,
				'round' => 1,
				'cash_only' => ($cfg['prdprc_sale_ptype'] == 2) ? 'Y' : 'N',
				'idx' => $data['no'],
			);
		}

		private function setPGCharge() {
			global $cfg;

			if($_POST['pay_type'] == 2) return;
			if($_POST['pay_type'] == '') return;

			$pay_type = $_POST['pay_type'];
			if(in_array($pay_type, array(1, 4, 5, 7)) == false) {
				$pay_type = 'E';
			}

			$charge = numberOnly($cfg['pg_charge_'.$pay_type]);
			if($charge > 0) {
				$this->conf['pgcharge'] = array(
					'sname' => 'sale0',
					'sale_per' => -($charge),
					'round' => 1,
				);
			}
		}

		public function loopCart() {
			if(!$this->loop) {
				$this->loop = array();
				if(is_array($this->ptns)) {
					foreach($this->ptns as $ptn) {
						foreach($ptn->cartprd as $cartprd) {
							$this->loop[] = $cartprd;
						}
					}
				}
			}

			$cart = current($this->loop);
            next($this->loop);
			if($cart == false) {
                reset($this->loop);
                return false;
            }

			return $this->parseCartData($cart);
		}

		private function parseCartData($cart) {
			$cart->data['sum_option_prc_str'] = parsePrice((int) $cart->data['option_prc'] * (int) $cart->data['buy_ea'], true);
			$cart->data['sum_milage'] = parsePrice($cart->data['total_milage'], true);
			$cart->data['sell_prc_c'] = parsePrice($cart->data['sell_prc'], true);
			$cart->data['sum_prd_prc_c'] = parsePrice($cart->getData('sum_prd_prc'), true);
			$cart->data['sum_r_option_prc_str'] = showExchangeFee((int) $cart->data['option_prc'] * (int) $cart->data['buy_ea']);//참조가격
			$cart->data['sum_r_milage'] = showExchangeFee($cart->data['total_milage']);//참조가격
			$cart->data['sum_r_sell_prc_c'] = showExchangeFee($cart->data['sum_sell_prc']); //참조가격
			$cart->data['sell_r_prc_c'] = showExchangeFee($cart->data['sell_prc']);//참조가격
			$cart->data['sum_sell_prc_c'] = parsePrice($cart->getData('sum_sell_prc'), true); // 할인 후 판매가격
			$cart->data['discount_prc'] = parsePrice($cart->getData('discount_prc'), true); // 총 할인 금액
			$cart->data['prd_dlv_prc'] = parsePrice($cart->getData('prd_dlv_prc')); // 총 개별 배송비

			return $cart;
		}

		public function complete() {
			global $cfg, $tbl, $member, $nations, $delivery_com, $delivery_fee_type;

			if($this->completed == true) return;
			$this->completed = true;

			$this->sum_sell_prc = 0;
			$this->setPrdPrcSale();

			// 총 상품금액보다 쿠폰 할인 금액이 더 클때
			if($this->conf['cpn']['sale_prc'] > $this->total_sale5_prc) {
				$this->remain_cpn_prc = ($this->conf['cpn']['sale_prc']-$this->total_sale5_prc);
				if($this->conf['cpn']['sale_prc_over'] == '') {
					$this->conf['cpn'] = null;
				} else {
					$this->conf['cpn']['sale_prc'] = $this->total_sale5_prc;
				}
				if(is_array($this->ptns)) {
					foreach($this->ptns as $_ptn) {
						$_ptn->setConf('cpn', $this->conf['cpn']);
						foreach($_ptn->cartprd as $_cart) {
							$_cart->setConf('cpn', $this->conf['cpn']);
						}
					}
				}
			}

			if(is_array($this->ptns)) {
				foreach($this->ptns as $obj) {
					$obj->complete($this->skip_dlv);
				}
			}

            // 상품 상세일 경우 이벤트, 회원 배송비를 할인으로 처리 하지 않음
            if ($this->opt_is_detail == true) {
                if ($this->sale2_dlv > 0) $this->dlv_prc -= $this->sale2_dlv;
                if ($this->sale4_dlv > 0) $this->dlv_prc -= $this->sale4_dlv;
            }

			if($this->sbscr_delivery_cnt > 0) {
				$prefix = 'sbscr';
			}
			if($prefix) {
				foreach($GLOBALS['_order_sales'] as $fn => $fv) {
					$this->{$fn} = $this->{$prefix.'_'.$fn};
				}
			}
			$this->total_sale = getOrderTotalSalePrc($this);
			$this->pay_prc = $this->total_order_price-$this->total_sale-$this->milage_prc-$this->emoney_prc;

            // 사용 적립금 및 예치금은 과세상품에서 우선 제외
            // 총 사용 적립금 및 예치금이 과세상품 금액보다 많다면 비과세 상품에서도 차감
            if ($this->taxfree_amount > 0) {

                $point_prc  = ($this->milage_prc+$this->emoney_prc); // 예치금+적립금 합계

                /*과세금액 추가 계산*/
                $this->non_taxfree_amount += $this->dlv_prc; //상품의 과세금액 총합 + 배송비(과세)
                if ($this->free_dlv_prc) {
                    //할인된 배송비가 존재한다면
                    $this->non_taxfree_amount -= $this->free_dlv_prc; //과세금액에서 차감
                } elseif ( ($this->sale2_dlv+$this->sale4_dlv) > 0 ) {
                    //이벤트나 회원혜택으로 할인된 배송비가 존재한다면
                    $this->non_taxfree_amount -= ($this->sale2_dlv+$this->sale4_dlv); //과세금액에서 차감
                }

                //과세금액과 예치금+적립금의 차액 계산
                $diff = $this->non_taxfree_amount - $point_prc;
                if ($diff<0) {
                    //차액이 음수인경우, (차감할 예치금+적립금이 남은경우) 면세 금액에서 추가 차감
                    $this->taxfree_amount -= abs($diff);
                    $this->non_taxfree_amount = 0; //과세에서 예치금+적립금을 뺀값이 음수라면, 과세 금액은 0원으로 적용
                }
            }

			if($cfg['delivery_fee_type'] == 'O' || $delivery_fee_type == 'O') {

				if(fieldExist($tbl['os_delivery_area'],"oversea_dlv_free") && fieldExist($tbl['os_delivery_area'],"oversea_dlv_free_limit")) {
					$query = "select da.oversea_dlv_free, da.oversea_dlv_free_limit from ".$tbl['os_delivery_country']." as dc inner join ".$tbl['os_delivery_area']." as da";
					$query.= " on dc.area_no=da.no where dc.country_code='$nations' and dc.delivery_com='$delivery_com'";
					$odata = $this->pdo->assoc($query);

					if($odata['oversea_dlv_free'] == 'Y' && $odata['oversea_dlv_free_limit'] > 0 && ($this->total_order_price-$this->dlv_prc) >= $odata['oversea_dlv_free_limit']){
						$this->pay_prc -= $this->dlv_prc;
						$tax_pay_dlv_prc = $this->dlv_prc;
						$this->dlv_prc = 0;
					}
				}

				if(fieldExist($tbl['delivery_url'],"tax_use")) {
					$use_tax = $this->pdo->row("select tax_use from `$tbl[delivery_url]` where no='$delivery_com'"); //배송사별 세금 과세 여부
					$this->tax_use_delivery_com = $use_tax;
					if($use_tax == 'Y'){
						if(($this->pay_prc+$tax_pay_dlv_prc) > $cfg['tax_add_limit'.$nations] && $cfg['tax_add_per'.$nations] > 0){
							$this->tax_prc = round((($this->pay_prc+$tax_pay_dlv_prc) * ($cfg['tax_add_per'.$nations]/100)),$cfg['currency_decimal']);
							$this->pay_prc += $this->tax_prc;
						}
					}
				}
			}

			// 지급적립금
			if(is_array($this->ptns)) {
				foreach($this->ptns as $obj) {
					$obj->setMilage();
				}
				if($prefix) {
					$this->member_milage = $this->{$prefix.'_member_milage'};
					$this->event_milage = $this->{$prefix.'_event_milage'};
				}
			}

			$this->total_sale_per1 = 0;
			$this->total_sale_per2 = 0;
			$this->total_sale_per3 = 0;
			if($this->total_sale > 0 && $this->sum_prd_prc > 0) {
				$this->total_sale_per1 = floor(($this->total_sale/$this->sum_prd_prc)*100);
			}
			if($this->sum_normal_prc > $this->sum_prd_prc && $this->sum_prd_prc > 0) {
				$this->total_sale_per2 = (int) (string) ((($this->sum_normal_prc-$this->sum_prd_prc)/$this->sum_normal_prc)*100);
			}
			if($this->total_sale_per1 > 0 || $this->total_sale_per2 > 0) {
				$this->total_sale_per3 = ($this->total_sale_per2) ? (int) (string) ((($this->total_sale+($this->sum_normal_prc-$this->sum_prd_prc))/$this->sum_normal_prc)*100) : $this->total_sale_per1;
			}
		}

		public function getconf($str = null) {
			if($str) {
				return $this->conf[$str];
			}
			return $this->conf;
		}

		public function addPrice($nm, $prc) {
			if(!$prc) return;

			$this->{$nm} += $prc;
		}

		public function getData($nm, $is_str = false) {
			$val = $this->{$nm};
			if($is_str == true) {
				$val = parsePrice($val, true);
			}
			return $val;
		}

		public function unsetSale($key) {
			$conf = $this->conf[$key];
			if(!$conf) return;

			$this->{$conf['sname']} = 0;
			$this->{'total_'.$conf['sname'].'_prc'} = 0;
			$this->conf[$key] = array();

			foreach($this->ptns as $_ptn) {
				$_ptn->unsetSale($key);
			}
		}

		public function setPrdCpnNo($prd_cpn_no) {
			$this->set_prdcpn_no .= $prd_cpn_no;
		}

	}


	/* +----------------------------------------------------------------------------------------------+
	' |  업체별 주문서
	' +----------------------------------------------------------------------------------------------+*/
	class PartnerCart {

        protected $pdo;
		public  $parent;
		private $partner_no = 0;
		private $conf; // 업체별 배송정책
		public  $cartprd; // 업체 소속 장바구니 객체(array)
		private $sum_prd_prc; // 총 업체 주문금액
		private $sum_normal_prc = 0; // 총 소비자가
		private $sum_sell_prc; // 총 업체 실 결제금액
		public  $dlv_prc = 0; // 업체별 총 배송비
		private $total_order_price = 0; // 총 주문금액(상품+배송비)
		private $free_dlv_prc = 0; // 총 할인된 배송비
		private $is_freedlv; // 무료배송여부
		private $free_dlv_type; // 무료배송 종류
		private $is_cod; // 착불배송여부
		private $delivery_set_cnt = 0; // 업체 상품 중 개별 배송비 상품의 수
		private $basic_dlv_prc = 0; // 업체 일반 배송비 합계
		private $prd_dlv_prc = 0; // 업체 개별 배송비 합계
		private $cart_weight; // 총 상품 무게
		private $cart_min_weight; // 무료처리될 상품 무게
		private $total_sale4_prc; // 총 회원혜택 대상 금액
		private $total_sale2_prc; // 총 이벤트 대상 금액
		private $total_sale3_prc; // 총 타임세일 대상 금액
		private $total_sale5_prc; // 총 쿠폰할인 대상 금액
		private $total_milage = 0; // 상품적립금+회원적리금
		private $member_milage = 0;
		private $event_milage = 0;
		public  $sale1 = 0;
		public  $sale2 = 0;
		public  $sale3 = 0;
		public  $sale4 = 0;
		public  $sale5 = 0;
		public  $sale6 = 0;
		public  $sale0 = 0;
		public  $sale7 = 0;
		public  $sale8 = 0;
		public  $sale9 = 0;
		public  $sale2_dlv = 0;
		public  $sale4_dlv = 0;
		public  $fee_prc = 0;
		private $taxfree_amount = 0; // 비과세 상품 결제금액
		private $is_cash = false; // 현금 결제 여부
		private $sbscr_dlv_date = array(); // 배송일자별 주문금액
		public  $sbscr_dlv_prc = array(); // 배송일자별 배송비
		public  $sbscr_prd_cnt = array(); // 배송일자별 상품수
		private $sbscr_prd = array(); // 업체별 기본 배송비
		public  $sbscr_pay_prc = array(); // 배송일자별 결제금액
		public  $productSets = array(); // 세트상품 관리
        private $member; // 회원 정보

		public function __construct($partner_no = 0, &$parent = null) {
			global $tbl, $cfg, $delivery_fee_type;

			$this->parent = $parent;
            $this->pdo = $GLOBALS['pdo'];
            $this->member = $parent->getData('member');

			$this->orderinfo = $orderinfo;
			$this->partner_no = $partner_no;
			$this->conf = $parent->getconf();
			if($this->parent->skip_dlv != 'Y') {
				$tmp = ($partner_no > 0) ? $this->pdo->assoc("select * from $tbl[partner_delivery] where partner_no='$partner_no'") : $GLOBALS['cfg'];

				if(!$tmp['delivery_type']) $tmp['delivery_type'] = 1; // 배송정책 미설정시 무료배송을 기본 값으로
				$this->conf['delivery_type'] = $tmp['delivery_type'];
				$this->conf['delivery_fee'] = ($tmp['delivery_type'] == 3) ? numberOnly($tmp['delivery_fee'], true) : 0;
				$this->conf['dlv_fee2'] = numberOnly($tmp['dlv_fee2'], true);
				$this->conf['dlv_fee3'] = numberOnly($tmp['dlv_fee3'], true);
				$this->conf['delivery_base'] = $tmp['delivery_base'];
				$this->conf['delivery_free_limit'] = numberOnly($tmp['delivery_free_limit'], true);
				$this->conf['delivery_free_milage'] = $tmp['delivery_free_milage'];
				$this->conf['delivery_prd_free'] = $delivery_fee_type != 'O' && $tmp['delivery_fee_type'] != 'O'?$tmp['delivery_prd_free']:'';
				$this->conf['delivery_prd_free2'] = $delivery_fee_type != 'O' && $tmp['delivery_fee_type'] != 'O' ? $tmp['delivery_prd_free2'] : '';
				$this->conf['adddlv_type'] = ($partner_no > 0) ? $tmp['partner_adddlv_type'] : $tmp['adddlv_type'];
				$this->conf['free_delivery_area'] = $tmp['free_delivery_area'];

				$this->is_freedlv = ($tmp['delivery_type'] == 1 && $delivery_fee_type != 'O' && $tmp['delivery_fee_type'] != 'O') ? 'Y' : 'N'; // 무료배송
				$this->is_cod = ($tmp['delivery_type'] == 2) ? 'Y' : 'N'; // 착불
			}

			$this->is_cash = $parent->getData('is_cash');
		}

		public function addCart($cart) {
			$this->cartprd[$cart['cno']] = new Cart($cart, $this);
		}

		public function complete($skip_dlv = 'N') {

			foreach($this->cartprd as $obj) {
				$obj->complete();
			}

			foreach($this->cartprd as $obj) { // 쿠폰만 다시 계산
				$obj->complete('cpn');
				if($obj->getData('free_dlv_type')) $this->free_dlv_type = $obj->getData('free_dlv_type');

				$_delivery_cnt = $obj->getData('sbscr_dlv_cnt');
				if($_delivery_cnt>0) {
					$_sum_prd_prc = $obj->getData('sum_prd_prc');
					$_sum_sale_prc = getOrderTotalSalePrc($obj);
					$_sum_sell_prc = ($_sum_prd_prc-$_sum_sale_prc);

					$_subscr_dlv_date = $obj->getData('sbscr_dlv_date');
					$_sbscr_prd = $obj->getData('sbscr_prd');
					if(is_array($_subscr_dlv_date)) {
						foreach($_subscr_dlv_date as $date) {
							if(!$this->sbscr_dlv_date[$date]) $this->sbscr_dlv_date[$date] = 0;
							$this->sbscr_dlv_date[$date] += (($this->conf['delivery_base'] == 1) ? $_sum_prd_prc : $_sum_sell_prc);
							$this->sbscr_pay_prc[$date] += $_sum_sell_prc;
							if($this->sbscr_prd_cnt[$date]==0 || $date==$tmp_date || $this->sbscr_prd_cnt[$date]) {
								$this->sbscr_prd_cnt[$date]++;
							}
							if(is_array($_sbscr_prd[$date])) {
								foreach($_sbscr_prd[$date] as $cnok => $pnov) {
									$this->sbscr_prd[$date][$cnok]['partner_no'] = $this->partner_no;
									$this->sbscr_prd[$date][$cnok]['delivery_free_limit'] = $this->conf['delivery_free_limit'];
									$this->sbscr_prd[$date][$cnok]['delivery_base'] = $this->conf['delivery_base'];
									$this->sbscr_prd[$date][$cnok]['delivery_type'] = $this->conf['delivery_type'];
									$this->sbscr_prd[$date][$cnok]['delivery_fee'] = $this->conf['delivery_fee'];
								}
							}
							$tmp_date = $date;
						}
					}
				}
			}
			$this->addPrice('sbscr_delivery_cnt', count($this->sbscr_prd));

			// 실결제 금액
			if($this->sbscr_delivery_cnt > 0) {
				foreach($GLOBALS['_order_sales'] as $fn => $fv) {
					$this->{$fn} = $this->{'sbscr_'.$fn};
				}
			}
            $this->total_sale = getOrderTotalSalePrc($this);
			$this->addPrice('sum_sell_prc', $this->sum_prd_prc-$this->total_sale);

			// 배송비
			if($skip_dlv != 'Y') {
				if(count($this->sbscr_dlv_date) > 0) { // 정기배송
					$dlv_prc = 0;
					foreach($this->sbscr_dlv_date as $date => $prc) {
						$_dlv_prc = $this->getDeliveryPrc($prc); // 장바구니 배송바
						foreach($this->cartprd as $_cart) { // 개별 배송비
							$prd_dlv_prc = $_cart->getDeliveryPrc();
							$_dlv_prc += $prd_dlv_prc;
							$_cart->addPrice('prd_dlv_prc', $prd_dlv_prc);
						}
						$dlv_prc += $_dlv_prc; // -> 총 배송비
						$this->sbscr_dlv_prc[$date] = $_dlv_prc;
						$datee = date('Y-m-d', $date);
					}
				} else {
					$dlv_prc = 0;
					$dlv_prc += $this->getDeliveryPrc(); // 장바구니 배송바
					foreach($this->cartprd as $_cart) { // 개별 배송비
						$_dset = $_cart->getData('delivery_set');
						if(empty($_dset) == false) {
							$_cart->partner_no = $this->getData('partner_no');
							$prd_dlv_prc = $_cart->getDeliveryPrc();
							$dlv_prc += $prd_dlv_prc;
							$_cart->addPrice('prd_dlv_prc', $prd_dlv_prc);
						}
					}
				}
				$this->addPrice('dlv_prc', $dlv_prc);
			} else {
				$dlv_prc = 0;
			}
			$this->addPrice('basic_dlv_prc', $this->dlv_prc-$this->prd_dlv_prc);

			// 정기배송 최초결제일 결제 금액
			$sbscr_pay_prc_sorted = $this->sbscr_pay_prc;
			if(is_array($sbscr_pay_prc_sorted) && count($sbscr_pay_prc_sorted) > 0){
				ksort($sbscr_pay_prc_sorted);
				$sbscr_firsttime_pay_prc = 0;
				foreach($sbscr_pay_prc_sorted as $key => $value){
					$sbscr_firsttime_pay_prc += $value;
					$sbscr_firsttime_pay_prc += $this->sbscr_dlv_prc[$key];
					break;
				}
				$this->addPrice('sbscr_firsttime_pay_prc', $sbscr_firsttime_pay_prc);
			}

			$this->addPrice('total_order_price', $this->sum_prd_prc+$dlv_prc);
		}

		public function setMilage() {
			foreach($this->cartprd as $obj) {
				$obj->setMilage();
			}
		}

		public function setFreeDelivery() {
			$this->is_freedlv = 'Y';
		}
		public function setFreeDeliveryOversea() {
			$this->is_oversea_freedlv = 'Y';
		}
		public function getconf() {
			return $this->conf;
		}

		public function setconf($field, $data) {
			$this->conf[$field] = $data;
		}

		public function getData($nm) {
			return $this->{$nm};
		}

		public function addPrice($nm, $prc) {
			if(!$prc) return;

			$this->{$nm} += $prc;
			$this->parent->addPrice($nm, $prc);
		}

		// 업체별 배송비 계산
		private function getDeliveryPrc($prc = null) {
			global $cfg, $tbl, $member, $nations, $delivery_com, $delivery_fee_type;

			// 상품 개별 배송 정책
			if($this->delivery_set > 0) {
				$tmp = $this->pdo->assoc("select * from {$tbl['product_delivery_set']} where no='$this->delivery_set'");

				$this->conf['delivery_type'] = $tmp['delivery_type'];
				$this->conf['delivery_fee'] = ($tmp['delivery_type'] == 3) ? numberOnly($tmp['delivery_fee'], true) : 0;
				$this->conf['dlv_fee2'] = $tmp['dlv_fee2'];
				$this->conf['dlv_fee3'] = $tmp['dlv_fee3'];
				$this->conf['delivery_base'] = $tmp['delivery_base'];
				$this->conf['delivery_free_limit'] = $tmp['delivery_free_limit'];
				$this->conf['adddlv_type'] = $cfg['adddlv_type'];
				$this->conf['free_delivery_area'] = $tmp['free_delivery_area'];
				$this->conf['delivery_loop_type'] = $tmp['delivery_loop_type'];

				$this->is_freedlv = ($tmp['delivery_type'] == 1 && $delivery_fee_type != 'O' && $tmp['delivery_fee_type'] != 'O') ? 'Y' : $this->getData('is_freedlv'); // 무료배송
				$this->is_cod = ($tmp['delivery_type'] == 2) ? 'Y' : 'N'; // 착불
				if($tmp['free_yn'] == 'Y') {
					$this->free_dlv_type = $this->getData('free_dlv_type');
				}
			}

			// 착불배송비
			if($this->is_cod == 'Y') {
				$this->addPrice('cod_prc', $this->conf['dlv_fee2']);
			}

			// 상품금액별 배송비
			if($this->is_cod != 'Y') {
				$pay_type = $_POST['pay_type'];
				$compare_prc = ($this->conf['delivery_base'] == 1) ? $this->getData('sum_prd_prc') : $this->getData('sum_sell_prc');
				if(is_null($prc) == false) {
					$compare_prc = $prc;
				}
				// 개별 배송비 상품이 있을 경우 해당 상품은 총 배송비 계산에서 제외
				if($this->delivery_set_cnt > 0 && count($this->cartprd) > 0 && $cfg['use_prd_dlvprc'] == 'Y') {
					if($this->delivery_set_cnt == count($this->cartprd)) {
						return 0;
					} else {
						foreach($this->cartprd as $_cart) {
							if($_cart->delivery_set > 0) {
								$compare_prc -= ($this->conf['delivery_base'] == 1) ? $_cart->getData('sum_prd_prc') : $_cart->getData('sum_sell_prc');
								$tmp++;
							}
						}
					}
				}

				// 배송비 계산시 적립금 사용금액을 결제금액으로 인정 안함
				if($this->conf['delivery_base'] == 2 && $cfg['delivery_free_milage'] != 'Y' && isset($this->delivery_set) == false) {
                    if ($_REQUEST['milage_prc'] > 0) {
    					$compare_prc -= numberOnly($_REQUEST['milage_prc']);
                    }
				}

				// 무료배송 주문 금액 체크
				if($cfg['delivery_fee_type'] != 'O' && $delivery_fee_type != 'O') {
					switch($this->conf['delivery_type']) {
						case '3' : // 결제금액별 배송
							if($this->conf['delivery_free_limit'] > 0 && $this->conf['delivery_free_limit'] > $compare_prc) {
								$dlv_prc = parsePrice($this->conf['delivery_fee']);
							}
							break;
						case '4' : // 결제 금액별 차등 배송
							$json = json_decode($this->conf['delivery_free_limit']);
							$json = array_reverse($json);
							if($this->conf['delivery_loop_type'] == 'Y') { // 반복형
								$dlv_prc = @floor($compare_prc/$json[0][0])*$json[0][2];
							} else {
								$dlv_prc = 0;
								foreach($json as $tmp) {
									list($limit, $_dummy, $prc) = $tmp;
									if($compare_prc >= $limit) {
										$dlv_prc = $prc;
										break;
									}
								}
							}
							break;
						case '5' : // 주문 수량별 차등 배송
							$json = json_decode($this->conf['delivery_free_limit']);
							$json = array_reverse($json);
							$buy_ea = $this->getData('buy_ea');
							if($this->conf['delivery_loop_type'] == 'Y') { // 반복형
								$dlv_prc = @ceil($buy_ea/$json[0][0])*$json[0][2];
							} else {
								$dlv_prc = 0;
								foreach($json as $tmp) {
									list($limit, $_dummy, $prc) = $tmp;
									if($buy_ea >= $limit) {
										$dlv_prc = $prc;
										break;
									}
									$dlv_prc = $prc;
								}
							}
							break;
						case '6' : // 고정배송
							$dlv_prc = ($this->delivery_set > 0) ? $this->conf['delivery_free_limit'] : $this->conf['dlv_fee3'];
                            $this->conf['delivery_fee'] = $dlv_prc;
                            $this->conf['delivery_free_limit'] = 0;
							break;
					}
				}
			}

			// 해외배송일 경우
			if($cfg['delivery_fee_type'] == 'O' || $delivery_fee_type == 'O') {
				$nations = $nations ? $nations : $this->member['nations'];
				if($nations && $delivery_com) {
					if($nations == 'KR') $nations = '';
					if($nations) {
						if($this->cart_min_weight > 0) $dlv_min_prc = getEMSprc($nations, ($this->cart_weight-$this->cart_min_weight), $delivery_com);

						$dlv_prc = getEMSprc($nations, $this->cart_weight, $delivery_com);
						$is_oversea = true;
					}
				}
			}

			if($this->is_oversea_freedlv == 'Y'){
				if($this->free_dlv_type == 'sale5') {
					if($this->cart_weight == $this->cart_min_weight){
						$this->addPrice('sale5', $dlv_prc);
					}else{
						if($dlv_min_prc > 0 && $dlv_prc >= $dlv_min_prc) $this->addPrice('sale5', ($dlv_prc - $dlv_min_prc));
						else $this->addPrice('sale5', 0);
					}
					$this->parent->oversea_free_dlv_stat = 'Y';
				}
			}

			// 이벤트/회원 무료배송 금액
			if($this->is_freedlv == 'Y' && $dlv_prc > 0) {
				$_target = ($this->delivery_set > 0) ? $this->parent : $this;
				if($this->free_dlv_type) { // 이벤트
					$_target->addPrice($this->free_dlv_type.'_dlv', $dlv_prc);
					$_target->addPrice($this->free_dlv_type, $dlv_prc);
				} else {
					$this->free_dlv_prc = $this->addPrice('free_dlv_prc', $dlv_prc);
					$dlv_prc = 0;
				}
			}

			// 지역별 추가배송비
			if(get_class($this) != 'Cart' && $is_oversea != true && ($this->is_freedlv != 'Y' || $this->conf['free_delivery_area'] == 'Y') && $this->conf['free_delivery_area'] != 'X') {
				if(!$this->conf['adddlv_type']) $this->conf['adddlv_type'] = 2;
				if($this->conf['adddlv_type'] == 2) {
					$add_dlv_prc = getAddPrcd($_POST['addressee_addr1'], $this->partner_no);
					$dlv_prc += $add_dlv_prc;
				} else {
					$Address = explode(' ', $_POST['addressee_addr1']);
					for($ii=0; $ii < count($Address); $ii++) {
						if(!$Address[$ii]) continue;
						$where = ($this->partner_no > 0) ? " and partner_no = '$this->partner_no'" : " and (partner_no = '0' || partner_no = '')";
						$area_prc = $this->pdo->row("select price from $tbl[delivery_area] where `area` like '%,".$Address[$ii].",%' $where");
						if($area_prc != '') {
							$add_dlv_prc = $area_prc;
							$dlv_prc += $area_prc;
							break;
						}
					}
				}
				if($add_dlv_prc > 0) {
					$this->addPrice('add_dlv_prc', $add_dlv_prc);
				}
			}

			return $dlv_prc;
		}

		function getPartnerName() {
			global $tbl, $cfg;

			if($this->partner_no > 0) {
				$partner_name = $this->pdo->row("select corporate_name from $tbl[partner_shop] where no='$this->partner_no'");
				$partner_name = stripslashes($partner_name);

				return $partner_name;
			} else {
				return $cfg['company_mall_name'];
			}
		}

		// 업체의 기본 배송비 정보를 리턴
		function getDlvBaseFee() {
			switch($this->conf['delivery_type']) {
				case '1' : return 0;
				case '2' : return $this->conf['dlv_fee2'];
				case '3' : return $this->conf['delivery_fee'];
				case '6' : return $this->conf['dlv_fee3'];
			}
			exit('test');
		}

		public function unsetSale($key) {
			$conf = $this->conf[$key];
			if(!$conf) return;

			$this->{$conf['sname']} = 0;
			$this->{'total_'.$conf['sname'].'_prc'} = 0;
			$this->conf[$key] = array();

			foreach($this->cartprd as $obj) {
				$obj->unsetSale($key);
			}
		}
	}


	/* +----------------------------------------------------------------------------------------------+
	' |  장바구니 상품
	' +----------------------------------------------------------------------------------------------+*/
	class Cart extends PartnerCart {

        protected $pdo;
		public  $parent; // 소속 업체 object
		private $partner_no = 0;
		private $dlv_partner_no = 0; // 배송 처리사 파트너 코드
		private $conf; // 소속 업체 배송정책
		public  $data;
		private $buy_ea;
		private $sum_prd_prc;
		private $sum_normal_prc = 0; // 총 소비자가
		private $sum_sell_prc = 0; // 할인 후 실 판매가
		private $discount_prc = 0; // 총 할인되는 가격
		private $is_freedlv;
		private $total_sale1_prc = 0;
		private $total_sale4_prc = 0;
		private $total_sale2_prc = 0;
		private $total_sale3_prc = 0;
		private $total_sale5_prc = 0;
		private $total_sale7_prc = 0;
		private $total_sale8_prc = 0;
		private $total_sale9_prc = 0;
		private $total_milage = 0; // 상품적립금+회원적리금
		private $member_milage = 0;
		private $event_milage = 0;
		public  $sale0 = 0;
		public  $sale1 = 0;
		public  $sale2 = 0;
		public  $sale3 = 0;
		public  $sale4 = 0;
		public  $sale5 = 0;
		public  $sale6 = 0;
		public  $sale7 = 0;
		public  $sale8 = 0;
		public  $sale9 = 0;
		private $prd_dlv_prc = 0; // 상품 개별 배송비
		public  $fee_prc = 0; // 입점사 수수료
		public  $cpn_promo_rate = 0; // 쿠폰 프로모션 부담 비율
		public  $cpn_promo_fee = 0; // 업체 부담 쿠폰금액
		private $tax_free = 'N'; // 상품별 비과세 여부
		private $is_cash = false; // 현금 결제 여부
		private $set_prdcpn_no = ''; // 적용 된 개별상품 쿠폰 번호
		private $sbscr_dlv_cnt = 1; // 정기배송 상품 배송 횟수
		private $sbscr_dlv_date = null;
		private $sbscr_prd = array();
		private $ts; // 타임세일 구조체
        private $member; // 회원 정보

		public function __construct($cart, &$parent) {
            $this->pdo = $GLOBALS['pdo'];
			$this->parent = $parent;
			$this->partner_no = $cart['partner_no'];
			$this->dlv_partner_no = $parent->getData('partner_no');
			$this->conf = $parent->getconf();
			$this->is_cash = $parent->getData('is_cash');
            $this->member = $parent->getData('member');

			$this->addCart($cart);
		}

		public function addCart($cart) {
			global $cfg;

			if($cart['dlv_cnt'] > 0) {
				$cart['date_list'] = explode("|", $cart['date_list']);
				if(is_array($cart['date_list'])) {
					$this->sbscr_dlv_date = $cart['date_list'];
					$this->sbscr_dlv_cnt = count($cart['date_list']);
					foreach($cart['date_list'] as $key=>$val) {
						$this->sbscr_prd[$val][$cart['cno']]['partner_no'] = $cart['partner_no'];
					}
				}
			}

			$cart['sell_prc'] = parsePrice($cart['sell_prc']);
			if(!$cart['buy_ea']) $cart['buy_ea'] = 1;
			$cart['milage'] = numberOnly($cart['milage'], true);
			$this->data = $cart;

			$this->addPrice('buy_ea', $cart['buy_ea']);
			$this->prd_prc = $cart['sell_prc'];
			$this->sum_prd_prc = ($cart['sell_prc']*$cart['buy_ea']);
			$this->sum_sell_prc = $this->sum_prd_prc;
			$this->cart_weight = ($cart['weight']*$cart['buy_ea']);
            if ($cart['prd_type'] == '1') {
			    $this->ts = $this->getTimeSaleData();
            }

			// 상품별 무료배송 마킹
			if($cart['free_dlv'] == 'Y' && ($this->conf['delivery_type'] == 3 || ($this->conf['delivery_type'] == 6 && $this->conf['delivery_prd_free2'] == 'Y'))) {
				$this->is_freedlv = 'Y';
				$this->parent->setFreeDelivery();
			}

			$this->addPrice('total_salem_prc', $this->sum_prd_prc);
			$this->addPrice('total_salee_prc', $this->sum_prd_prc);

			// 정기배송할인
			if($cart['sale_use'] == 'Y') {
				if($this->sbscr_dlv_cnt>=$cart['sale_ea']) {
					$this->sale_percent = $cart['sale_percent'];
					$this->addPrice('total_sale8_prc', $this->sum_prd_prc);
				}
			}

            if (!is_array($this->conf['cpn']) || $this->conf['cpn']['cpn_use_limit'] != '3') { // 적용한 쿠폰이 존재하지 않거나, 적용쿠폰의 제한조건이 3 (회원 할인/이벤트 할인을 취소하고 쿠폰 할인만 적용)이 아닌경우
                // 회원혜택 대상 상품 금액 업데이트
                if($cart['member_sale'] == 'Y' && $this->member['no'] > 0) {
                    $this->addPrice('total_sale4_prc', $this->sum_prd_prc);
                }

                // 이벤트 대상 상품 금액 업데이트
                if($cart['event_sale'] == 'Y') {
                    $this->addPrice('total_sale2_prc', $this->sum_prd_prc);
                }
            }

			// 타임세일 대상 상품 금액 업데이트
			if($this->ts->use == 'Y' && $this->ts->saleprc > 0) {
				$this->addPrice('total_sale3_prc', $this->sum_prd_prc);
			}

			// 쿠폰 할인 대상 금액 업데이트
			if(is_array($this->conf['cpn']) == true) {
				if(isCpnAttached($this->conf['cpn']['data'], $cart)) {
					$this->addPrice('total_sale5_prc', $this->sum_prd_prc);
					if($this->conf['cpn']['oversea_free_dlv'] == 'Y') $this->cart_min_weight = $this->cart_weight;
				}
			}

			// 주문상품금액별 할인 대상
			$this->addPrice('total_sale6_prc', $this->sum_prd_prc);

			// 개별상품 쿠폰 적용 대상
			if($this->data['prdcpn_no']) {
				$this->setProductCoupon($this->data['prdcpn_no']);
			}

			// 세트 할인 대상
			if($this->data['set_idx']) {
				$this->set_idx = $this->data['set_idx'];
				$this->set_pno = $this->data['set_pno'];
				if(is_object($this->parent->productSets[$this->set_idx]) == false) {
					$this->parent->productSets[$this->set_idx] = new ProductSet($this->set_idx, $this->set_pno);
				}
				$this->productSets = &$this->parent->productSets[$this->set_idx];
				$this->productSets->add($this->sum_prd_prc, $this->buy_ea);
				$this->set_order = $this->productSets->get('cnt');

				$this->addPrice('total_sale1_prc', $this->sum_prd_prc);
			}

            // 세트 할인 (세트 본체 표시용)
            if ($cart['prd_type'] == '4') {
                $this->addPrice('total_sale1_prc', $this->sum_prd_prc);
            }

			// 상품별 파트너 fee
			if($cart['partner_rate'] > 0) {
				$this->addPrice('fee_prc', getPercentage($this->sum_prd_prc, $cart['partner_rate']));
			}

			//해외 무료 배송 무게 차감
			if($cart['oversea_free_delivery'] == 'Y'){
				$this->cart_weight = 0;
				$this->cart_min_weight = 0;
			}

			// 수량할인 선처리
			if(empty($cart['qty_rate']) == false) {
				$this->addPrice('qty_sale_'.$cart['pno'], $cart['buy_ea']);
				$this->addPrice('total_sale9_prc', $this->sum_prd_prc);
			}

			if($cart['tax_free'] == 'Y') {
				$this->tax_free = 'Y';
			}

			// 개별 배송
			$this->delivery_set = $this->data['delivery_set'];
			if($this->delivery_set > 0) {
				$this->addPrice('delivery_set_cnt', 1);
			}

			// 적립금 사용 불가
			if($cart['no_milage'] == 'Y') {
				$this->addPrice('no_milage',  1);
			}

			// 쿠폰 사용 불가
			if($cart['no_cpn'] == 'Y') {
				$this->addPrice('no_cpn',  1);
			}

			$this->addPrice('sum_normal_prc', numberOnly($cart['normal_prc'], true)*$cart['buy_ea']);
			if($this->sbscr_dlv_cnt>0) {
				$this->parent->addPrice('sum_prd_prc', $this->sum_prd_prc*$this->sbscr_dlv_cnt);
			} else {
				$this->parent->addPrice('sum_prd_prc', $this->sum_prd_prc);
			}
			$this->parent->addPrice('cart_weight', $this->cart_weight);
			$this->parent->addPrice('cart_min_weight', $this->cart_min_weight);
		}

		public function complete($complete_mode = null) {
			global $cfg, $_order_sales;

			// 쿠폰 실제 계산
			if($complete_mode == 'cpn') {
				if($this->total_sale5_prc > 0) {
					$cpn_sale = $this->setSale($this->conf['cpn'], $this->sum_prd_prc);
                    if($this->tax_free == 'Y') {
                        $this->addPrice('taxfree_amount', -($cpn_sale[0]));
                    } else {
                        $this->addPrice('non_taxfree_amount', -($cpn_sale[0]));
                    }
				}
				return;
			}

			// 세트할인
			if($this->total_sale1_prc > 0 && is_object($this->productSets) == true) {
                $set_rules = $this->productSets->getSaleRule();
                $this->setSale($set_rules, $this->sum_prd_prc);

                if ($set_rules['prd_type'] == '5' || $set_rules['prd_type'] == '6') { // 다른 할인 적용 불가
                    foreach ($_order_sales as $key => $val) {
                        if ($key == 'sale1') continue;

                        $this->addPrice($key, -($this->{$key}));
                        $this->addPrice('total_'.$key.'_prc', -($this->{'total_'.$key.'_prc'}));
                    }
                }
			}

			// 이벤트
			if($this->total_sale2_prc > 0) {
				$this->setSale($this->conf['esale'], $this->sum_prd_prc);
				$this->setSale($this->conf['edlv']);
			}

			// 회원
			if($this->total_sale4_prc > 0) {
				$this->setSale($this->conf['msale'], $this->sum_prd_prc);
			}

			// 정기배송
			if($this->total_sale8_prc > 0) {
				$this->setSale(array(
					'sname' => 'sale8',
					'sale_per' => $this->sale_percent,
					'round' => 1,
				), $this->sum_prd_prc);
			}

			// 쿠폰 예외 처리
			if($this->total_sale5_prc > 0) {
				if($this->sale2 > 0 || $this->sale4 > 0) {
					if($this->conf['cpn']['cpn_use_limit'] == 1) { // 할인 된 상품은 쿠폰할인 하지 않음
						$this->addPrice('total_sale5_prc', -($this->sum_prd_prc));
					}
					if($this->conf['cpn']['cpn_use_limit'] == 2) { // 할인 된 상품이 하나라도 있으면 쿠폰 사용 불가
						$this->parent->parent->unsetSale('cpn');
					}
				}
				if($this->total_sale5_prc > 0 && $this->conf['cpn']['cpn_use_limit'] == 3) { // 회원할인/이벤트할인을 취소하고 쿠폰 할인만 적용
					$this->parent->parent->unsetSale('esale');
					$this->parent->parent->unsetSale('edlv');
					$this->parent->parent->unsetSale('msale');
				}

				if($this->conf['cpn']['free_dlv'] ==  'Y') {
					$this->setSale($this->conf['cpn'], $this->sum_prd_prc);
				}
			}

			// 주문상품금액별 할인
			if($cfg['prdprc_sale_use'] == 'Y' && $cfg['use_partner_shop'] != 'Y') {
				if($cfg['prdprc_sale_add'] == 1 || ($this->sale2 == 0 && $this->sale4 == 0 && $this->sale5 == 0)) {
					if(!$this->conf['prdprc']) $this->getconf('prdprc');
					$this->setSale($this->conf['prdprc'], $this->sum_prd_prc);
				}
			}

			// 타임세일
			if($this->ts->use == 'Y' && $this->ts->saleprc > 0 && $this->ts->event_type != '2') {
				$this->setSale(array(
					'sname' => 'sale3',
                    'idx' => $this->data['cno'],
					'sale_prc' => ($this->ts->saletype == 'price') ? $this->ts->saleprc*$this->buy_ea : 0,
					'sale_per' => ($this->ts->saletype == 'percent') ? $this->ts->saleprc : 0,
					'round' => ($this->ts->cut > 1) ? $this->ts->cut : 1
				), $this->sum_prd_prc);
			}

			// 개별상품 쿠폰
			if($this->total_sale7_prc > 0) {
				foreach($this->conf['sale7'] as $_conf) {
					$cpn = $_conf['data'];
					if($cpn['use_limit'] == 1 && ($this->sale2 > 0 || $this->sale4 > 0)) continue; // 회원 할인/이벤트 할인 된 상품은 쿠폰사용 불가

					$sale7 = $this->setSale($_conf, $this->total_sale7_prc, 1);
					if($sale7[0] > 0) { // 사용 된 쿠폰 번호 저장 (db 처리용)
						if($this->set_prdcpn_no) $this->set_prdcpn_no .= ',';
						$this->set_prdcpn_no .= $cpn['no'];
					}
				}
				if($this->set_prdcpn_no) {
					$this->parent->parent->setPrdCpnNo($this->set_prdcpn_no);
				}
			}

            // 세트 할인 (세트 본체 가격 표시용)
            if ($this->total_sale1_prc > 0) {
				$this->setSale(array(
					'sname' => 'sale1',
					'sale_prc' => ($this->data['set_sale_type'] == 'm') ? $this->data['set_sale_prc'] : 0,
					'sale_per' => ($this->data['set_sale_type'] == 'p') ? $this->data['set_sale_prc'] : 0,
					'round' => ($this->ts->cut > 1) ? $this->ts->cut : 1
				), $this->sum_prd_prc);
            }

			// 사용 적립금 계산
			$milage_prc = $this->parent->parent->getData('milage_prc');
			if($milage_prc > 0) {
				$this->setSale(array(
					'sname' => 'salem',
					'sale_nm' => 'milage',
					'sale_prc' => $milage_prc,
				), $this->total_salem_prc);
			}

			// 사용 예치금 계산
			$emoney_prc = $this->parent->parent->getData('emoney_prc');
			if($emoney_prc > 0) {
				$this->setSale(array(
					'sname' => 'salee',
					'sale_nm' => 'emoney',
					'sale_prc' => $emoney_prc,
				), $this->total_salee_prc);
			}

			// 결제방식별 할증
			if(is_array($this->conf['pgcharge'])) {
				$this->setSale($this->conf['pgcharge'], $this->sum_prd_prc);
			}

			if($this->sum_sell_prc < 0 && $this->sale7 > 0) { // 다른 할인+원단위 할인으로 인해 결제금액이 마이너스가 된 경우
				if(abs($this->sum_sell_prc) <= $this->sale7) {
					$this->addPrice('sale7', $this->sum_sell_prc);
					$this->discount_prc += $this->sum_sell_prc;
					$this->sum_sell_prc = 0;
				}
			}
			if($this->sale7 == 0) {
				$this->cart['prdcpn_no'] = null;
			}

			// 상품별 수량할인
			$qty_sale_ea = $this->parent->{'qty_sale_'.$this->data['pno']};
			if($qty_sale_ea > 0) {
				$tmp1 = json_decode($this->data['qty_rate']);
				$tmp2 = array_reverse((array)$tmp1->data, true);
				$_sale_prc = 0;
				foreach($tmp2 as $key => $val) {
					if($qty_sale_ea >= $key) {
						$_sale_prc = $val;
						break;
					}
				}
				if($_sale_prc > 0) {
					$this->setSale(array(
						'sname' => 'sale9',
						'sale_prc' => ($tmp1->sale_type == 'm') ? ($_sale_prc*$this->buy_ea) : 0,
						'sale_per' => ($tmp1->sale_type == 'p') ? $_sale_prc : 0,
						'round' => 1
					), $this->sum_prd_prc);
				}
			}

			if($this->tax_free == 'Y') {
				$this->addPrice('taxfree_amount', $this->sum_sell_prc);
                if ($this->sbscr_dlv_cnt > 0) {
    				$this->addPrice('taxfree_amount_sbscr', ($this->taxfree_amount*$this->sbscr_dlv_cnt));
                }
			} else {
                $this->addPrice('non_taxfree_amount', $this->sum_sell_prc);
                if ($this->sbscr_dlv_cnt > 0) {
                    $this->addPrice('non_taxfree_amount_sbscr', ($this->taxfree_amount*$this->sbscr_dlv_cnt));
                }
            }

			foreach($GLOBALS['_order_sales'] as $key => $val) {
				if(isset($this->data[$key]) == true) {
					$this->addPrice($key, $this->data[$key]);
					$this->discount_prc += $this->data[$key];
					$this->sum_sell_prc -= $this->data[$key];
				}
			}

			if (is_object($this->productSets) == true) { // 장바구니 출력용 세트별 실 판매금액 합산 (모든 할인 처리 후 처리)
				$this->productSets->addPrice('discount_prc', $this->discount_prc);
				$this->productSets->addPrice('pay_prc', $this->sum_sell_prc);
			}
		}

		// 실 적립금 계산
		public function setMilage() {
			global $cfg;

			if($this->member['no'] < 1) return;
			if($cfg['milage_use_give'] == 'N' && $this->parent->parent->milage_prc > 0) return;
			if($cfg['use_cpn_milage'] == 'N' && $this->sale5+$this->sale7 > 0) return;
			if($this->member['attr_no_milage'] == 'Y') return; // 특별회원그룹속성

			$pmile = $mmile = $emile = $tmile = 0;

			if($cfg['milage_use'] != 1) $this->milage = 0;
			else {
				// 이벤트 적립금
				$config = $this->conf['esale'];
				if($this->total_sale2_prc > 0 && $config['milage_per'] > 0 && ($config['cash_only'] != 'Y' || $this->is_cash == true)) {
					$prc = $this->total_sale2_prc;
					$emile = getPercentage($prc, $config['milage_per'], $config['round'], $cfg['currency_decimal']);
					$this->addPrice('event_milage', $emile);

					if(($prc > 0 || $emile > 0) && $config['no_milage'] == 'N') {
						$this->no_prd_milage = true;
					}
				}

				// 회원 적립금
				$config = $this->conf['msale'];
				if($this->total_sale4_prc > 0 && $config['milage_per'] > 0 && ($config['cash_only'] != 'Y' || $this->is_cash == true)) {
					$prc = $this->total_sale4_prc;
					if($cfg['msale_mile_type'] == 2) $prc-=(getOrderTotalSalePrc($this)+$this->salem+$this->salee);
					$mmile = getPercentage($prc, $config['milage_per'], $config['round'], $cfg['currency_decimal']);
					$this->addPrice('member_milage', $mmile);
					if($cfg['member_milage_type'] == 1) {
						$this->no_prd_milage = true;
					}
				}

				// 타임세일 적립금
				if($this->total_sale3_prc > 0 && $this->ts->event_type == '2') {
					$prc = $this->total_sale3_prc;
					$tmile = ($this->ts->saletype == 'percent') ? getPercentage($prc, $this->ts->saleprc, $this->ts->cut, $cfg['currency_decimal']) : $this->ts->saleprc;
					$this->addPrice('time_milage', $tmile);
					$this->no_prd_milage = true;
				}

				// 상품 적립금
				$is_detail = $this->parent->parent->getData('opt_is_detail');
				$pay_prc = $this->parent->parent->getData('pay_prc');
				if($is_detail == true || empty($cfg['milage_save_min']) == true || $pay_prc >= (int)$cfg['milage_save_min']) { // 적립 가능한 결제 금액 설정
					if($this->no_prd_milage != true) {
						if($cfg['milage_type'] == 2) {
							$prc = $this->sum_sell_prc-$this->salem-$this->salee;
							$pmile = getPercentage($prc, $cfg['milage_type_per'], 1, $cfg['currency_decimal']);
							$this->milage = parsePrice($pmile/$this->buy_ea, $cfg['currency_decimal']);
						} else {
							$this->milage = $this->data['milage'];
							$pmile = ($this->data['milage']*$this->buy_ea);
						}
					}
				}
			}

			if($this->sbscr_dlv_cnt > 0) {
				if($this->member_milage > 0) $this->addPrice('sbscr_member_milage', ($this->member_milage*$this->sbscr_dlv_cnt));
				if($this->event_milage > 0) $this->addPrice('sbscr_event_milage', ($this->event_milage*$this->sbscr_dlv_cnt));
				if($this->time_milage > 0) $this->addPrice('sbscr_time_milage', ($this->time_milage*$this->sbscr_dlv_cnt));
				if($total_milage > 0) $total_milage *= $this->sbscr_dlv_cnt;
			}

			$total_milage = $pmile+$mmile+$emile+$tmile;

			if ($this->set_idx) { // 장바구니 출력용 세트별 총 적립금
				$this->productSets->addPrice('milage', $total_milage);
			}

			$this->data['milage'] = $this->milage;
			$this->data['total_milage'] = $total_milage;
			$this->data['member_milage'] = $this->member_milage;
			$this->data['event_milage'] = $this->event_milage;
			$this->data['time_milage'] = $this->time_milage;
			$this->addPrice('total_milage', $total_milage);
		}

		public function setconf($field, $data) {
			$this->conf[$field] = $data;
		}

		public function getData($nm) {
			return $this->{$nm};
		}

		public function addPrice($nm, $prc) {
			if(!$prc) return;

			$this->{$nm} += $prc;
			$this->parent->addPrice($nm, $prc);
		}

		private function setProductCoupon($prdcpn_no) {
			global $tbl, $now;

			$prdcpn_no = explode('@', trim($prdcpn_no, '@'));
			$prdcpn_no = numberOnly($prdcpn_no);
			foreach($prdcpn_no as $key => $_no) {
				$cpn = $this->pdo->assoc("select d.*, c.attachtype,c.attach_items from `$tbl[coupon_download]` as d inner join `$tbl[coupon]` as c on d.cno=c.no where d.member_no='{$this->member['no']}' and d.stype=5 and d.`ono`='' and d.`no`='$_no'");

                // 상품별 쿠폰의 쿠폰 사용 만료일 체크
                if ($cpn['udate_type'] == '2') {
                    $st = strtotime($cpn['ustart_date']);
                    $ed = strtotime($cpn['ufinish_date'])+86399;
                    if ($now < $st || $now > $ed) {
                        $this->pdo->query("update {$tbl['coupon_download']} set cart_no=0 where no='{$cpn['no']}'");
                        continue;
                    }
                } else if ($cpn['udate_type'] == '3') {
                    $ed = strtotime($cpn['ufinish_date'])+86399;
                    if ($now > $ed) {
                        $this->pdo->query("update {$tbl['coupon_download']} set cart_no=0 where no='{$cpn['no']}'");
                        continue;
                    }
                }

				if($cpn['use_limit'] == 4 && $key > 0) break; // 다른 개별상품 할인 쿠폰과 같이 사용 불가
				if(isCpnAttached($cpn, $this->data) == false) continue;
				if($cpn['sale_type'] == 'm' && $cpn['sale_prc'] > $this->sum_prd_prc) {
					if($cpn['sale_prc_over'] != 'Y') continue;
					if($cpn['sale_prc_over'] == 'Y') $cpn['sale_prc'] = $this->sum_prd_prc;
				}

				if($cpn['no']) {
					if(is_array($this->conf['sale7']) == false) $this->conf['sale7'] = array();
					$this->conf['sale7'][] = $this->parent->parent->setCoupon($cpn, 'sale7');
					if($this->total_sale7_prc == 0) {
						$this->addPrice('total_sale7_prc', $this->prd_prc);
					}
				}

				if($cpn['use_limit'] == 4) break; // 다른 개별상품 할인 쿠폰과 같이 사용 불가
			}
		}

		// 개별상품 할인 계산
		public function setSale($config, $prc = 0, $sale_ea = null) {
			global $cfg, $delivery_fee_type;

            // 골라담기, 담을수록 할인 사용 시 다른 할인 적용 불가
            if (isset($this->set_rules) == true && ($this->set_rules['prd_type'] == '5' || $this->set_rules['prd_type'] == '6')) {
                if ($config['sname'] != 'sale1') return;
            }

			if(isset($this->data[$config['sname']]) == true) { // 수동 할인 입력
				return;
			}

			if(!$config['sname']) return;
			//if($config['data']['partner_no'] > 0 && $this->partner_no != $config['data']['partner_no']) return 0;

            // 특별회원그룹속성
			if ($this->member['attr_no_sale'] == 'Y') return array(0, 0);
            if ($config['sname'] == 'sale5' || $config['sname'] == 'sale7') {
                if ($this->member['attr_no_coupon'] == 'Y') return array(0, 0);
            } else {
                if ($config['sname'] != 'sale1' && $this->member['attr_no_discount'] == 'Y') return array(0, 0);
            }

			$target_prc = $this->parent->parent->{'total_'.$config['sname'].'_prc'}; // 주문서 내 총 할인 대상 상품 금액
			if($config['sname'] == 'sale7' || $config['sname'] == 'sale9') {
				$target_prc = $prc;
			}
			if ($config['sname'] == 'sale1' && is_object($this->productSets) == true) {
				$target_prc = $this->productSets->get('total_prc');
			}

			if(($config['min_prc'] > 0 && $target_prc < $config['min_prc']) || ($config['cash_only'] == 'Y' && $this->is_cash == false)) return 0;
			else {
				if($config['sale_per'] && $config['sale_per'] != 0) { // 할인
					$pre_sale = getPercentage($target_prc, $config['sale_per'], $config['round'], $cfg['currency_decimal']);
					if($config['sale_limit'] > 0 && $pre_sale > $config['sale_limit']) {
						//$config['sale_per'] = 0;
						$config['sale_prc'] = $config['sale_limit'];
					} else {
                        if (is_null($sale_ea) == true) { // 상품별 쿠폰은 주문수량에 관계없이 1개만 할인, 그 외에는 전체 수량 할인
                            $sale_ea = $this->buy_ea;
                        }
						$sale_prc = getPercentage($prc/$sale_ea, $config['sale_per'], $config['round'], $cfg['currency_decimal']);
                        $sale_prc *= $sale_ea;
						$this->addPrice($config['sname'], $sale_prc);
						if($this->sbscr_dlv_cnt>0) $this->addPrice('sbscr_'.$config['sname'], $sale_prc*$this->sbscr_dlv_cnt);
					}
				}

				if($config['sale_prc'] > 0) { // 원단위 할인
					if($config['sname'] == 'sale3') {
						$sale_prc = $config['sale_prc'];
					} else if($cfg['currency_decimal'] > 0){
						$sale_prc = $config['sale_prc']*($prc/$target_prc);
					} else {
						$sale_prc = getPercentage($config['sale_prc'], ($prc/$target_prc)*100, $config['round'], $cfg['currency_decimal']);
					}

					// 최종 나머지 처리
					$log = &$this->parent->parent->sale_left[$config['sname'].' '.$config['idx']];
					$saled = &$this->parent->parent->sale_prc[$config['sname'].' '.$config['idx']];
					$saled += $prc;
                    if(is_null($log) == true) $log = $config['sale_prc'];
					$log -= $sale_prc;
					if(abs($log) > 0 && $saled == $target_prc) {
						$sale_prc += $log;
					}

					if($config['sname'] == 'sale5' && $config['sale_prc'] > 0) {
						if($sale_prc > $this->sum_sell_prc) {
							$sale_prc = $this->sum_sell_prc;
						}
					}

					$this->addPrice($config['sname'], $sale_prc);
					if($this->sbscr_dlv_cnt>0) $this->addPrice('sbscr_'.$config['sname'], $sale_prc*$this->sbscr_dlv_cnt);
				}

				if($cfg['delivery_fee_type'] != 'O' && $delivery_fee_type != 'O'){
					if($config['free_dlv'] == 'Y') {
						$this->free_dlv_type = $config['sname'];
						$this->is_freedlv = 'Y';
						$this->parent->setFreeDelivery();
					}
				}

				if($config['oversea_free_dlv'] == 'Y') {
					$this->free_dlv_type = $config['sname'];
					$this->is_oversea_freedlv = 'Y';
					$this->parent->setFreeDeliveryOversea();
				}

				// 입점업체 프로모션 수수료
				if($sale_prc > 0 && $config['sale_nm'] == 'coupon' && $config['data']['partner_fee'] > 0 && $this->partner_no > 0) {
					$this->cpn_promo_rate = $config['data']['partner_fee'];
					if($config['sale_per'] > 0) {
						$cpn_promo_fee = getPercentage($sale_prc, $config['data']['partner_fee'], 1, $cfg['currency_decimal']);
					} else if($config['sale_prc'] > 0) {
						$cpn_promo_fee = $config['data']['partner_fee'];
					}
					$this->addPrice('cpn_promo_fee', $cpn_promo_fee);
				}

				// $config['no_milage'] 가 N 일 경우 해당 상품 적립금 지급하지 않음
				if(($sale_prc > 0 || $milage > 0) && $config['no_milage'] == 'N') {
					$this->no_prd_milage = true;
				}

				if(in_array($config['sname'], array('salem', 'salee')) == false) {
					$this->discount_prc += $sale_prc;
					$this->sum_sell_prc -= $sale_prc;
				}

				return array($sale_prc, $milage);
			}
		}

		public function unsetSale($key) {
			$conf = $this->conf[$key];
			if(!$conf) return;

			$this->{$conf['sname']} = 0;
			$this->{'total_'.$conf['sname'].'_prc'} = 0;
			$this->conf[$key] = array();
		}

		public function getconf($str = null) {
			if($str) {
				$this->conf[$str] = $this->parent->parent->getconf($str);
			} else {
				return $this->parent->parent->getconf();
			}
		}

		private function getTimeSaleData() {
			global $tbl, $cfg, $now;

			if($cfg['ts_use'] != 'Y') return false;

			$data = $this->data;
			if($data['ts_set'] > 0) { // 프리셋 설정
				$ts = $this->pdo->assoc("select ts_use, ts_dates, ts_datee, ts_event_type, ts_saleprc, ts_saletype, ts_cut from {$tbl['product_timesale_set']} where no='{$data['ts_set']}'");
				$ts['ts_dates'] = strtotime($ts['ts_dates']);
				$ts['ts_datee'] = ($ts['ts_datee'] == "0000-00-00 00:00:00") ? 0 : strtotime($ts['ts_datee']);
                $data = array_merge($data, $ts);
			}

			return (object)array(
				'use' => ($data['ts_use'] == 'Y' && $data['ts_dates'] <= $now && ($data['ts_datee'] == 0 || $data['ts_datee'] >= $now)) ? 'Y' : '',
				'dates' => (int)$data['ts_dates'],
				'datee' => (int)$data['ts_datee'],
				'event_type' => ($data['ts_event_type'] == '2') ? '2' : '1',
				'saleprc' => (int)$data['ts_saleprc'],
				'saletype' => $data['ts_saletype'],
				'cut' => (int)$data['ts_cut']
			);
		}

	}

	// 세트상품 구조체
	class ProductSet {
		private $idx;
		private $pno;
		private $cnt = 0;
		private $buy_ea = 0;
		private $total_prc = 0;
		private $pay_prc = 0;
		private $discount_prc = 0;
		private $milage = 0;

		public function __construct($set_idx, $set_pno)
        {
			$this->idx = $set_idx;
			$this->pno = $set_pno;
            $this->pdo = $GLOBALS['pdo'];
		}

		public function add($prc, $buy_ea)
        {
			$this->cnt++;
			$this->buy_ea += $buy_ea;
			$this->total_prc += $prc;
		}

		public function addPrice($nm, $num)
        {
			$this->{$nm} += $num;
		}

		public function get($var)
        {
			return $this->{$var};
		}

		public function getSaleRule()
        {
			global $tbl;

			$prd = $this->pdo->assoc("select prd_type, set_rate, set_sale_prc, set_sale_type, sell_prc from {$tbl['product']} where no='$this->pno'");

			if ($prd['prd_type'] == '5') { // 담을수록 할
				$rule = json_decode($prd['set_rate'], true);
				$prd['set_sale_type'] = $rule['sale_type'];
				$rule['data'] = array_reverse($rule['data'], true);
				foreach($rule['data'] as $key => $val) {
					if($this->buy_ea >= $key) {
						$prd['set_sale_prc'] = $val;
						break;
					}
				}
			}

            if ($prd['prd_type'] == '6') { // 골라담기
                $prd['set_sale_type'] = 'm';
                $prd['set_sale_prc'] = ($this->total_prc-$prd['sell_prc']);
            }

			$sale_per = $sale_prc = 0;
			if($prd['set_sale_type'] == 'p') $sale_per = $prd['set_sale_prc'];
			else if($prd['set_sale_type'] == 'm') {
                $sale_prc = $prd['set_sale_prc'];
                if ($prd['prd_type'] == '4') {
                    $sale_prc *= ($this->buy_ea / $this->cnt);
                }
            }

			return array(
				'idx' => $this->idx,
				'sname' => 'sale1',
				'sale_per' => $sale_per,
				'sale_prc' => $sale_prc,
                'prd_type' => $prd['prd_type'],
				'round' => 10,
			);
		}
	}

?>