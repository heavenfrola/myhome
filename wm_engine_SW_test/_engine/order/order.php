<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  주문서 작성페이지
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
    include_once __ENGINE_DIR__.'/_engine/include/MemberAddress.lib.php';

    // 구버전 설정 마이그레이션
    include_once __ENGINE_DIR__.'/_engine/include/migration/cfg_paytype.inc.php';

    $scfg->def('use_order_phone', 'N');

	$sbscr = ($_GET['sbscr']=='Y') ? 'Y':'N';
	$file_sbscr = ($sbscr=='Y') ? "sbscr_" : "";

	if($cfg['order_auth'] == 10 && $cfg['order_style'] == 'login' && $member['no'] < 1 && $_GET['guest_ord'] != $_SESSION['guest_no']) {
		$rURL = getURL();
		foreach($_POST['cno'] as $key => $val) {
			$rURL .= (strpos($rURL, '?') == false) ? '?' : '&';
			$rURL .= 'cno[]='.$val;
		}
		if($rURL) $rURL = '&rURL='.urlencode($rURL);
		msg('', $root_url.'/member/login.php?guest=true'.$rURL);
	}

	if(!$cfg['order_auth']) $cfg['order_auth'] = 10;
	if($cfg['order_auth'] < 10) {
		memberOnly();
	}

	if($_REQUEST['cno']) {
		$cart_selected = implode(',', $_REQUEST['cno']);
	} else if($_GET['cart_selected']) {
		$cart_selected = $_REQUEST['cart_selected'];
	}

	include_once $engine_dir."/_engine/include/shop.lib.php";
	trncCart(6); // 6시간초과 장바구니 비우기

	if($sbscr=='Y') {
		if($cart_selected) $sbscr_where_str .= " and no in ($cart_selected)";
		$date0 = $pdo->row("select count(*) from {$tbl['sbscr_cart']} where end_date=0 ".mwhere().$sbscr_where_str);
		$date1 = $pdo->row("select count(*) from {$tbl['sbscr_cart']} where end_date>0 ".mwhere().$sbscr_where_str);
		if($date0>0 && $date1>0) {
			msg("설정이 다른 상품은 구매할 수 없습니다.", $root_url.'/shop/cart.php?sbscr=Y');
		}
		$cart_check = $pdo->row("select count(*) from {$tbl['sbscr_cart']} where 1 ".mwhere());
		if(!$cart_check) msg(__lang_shop_error_nocart2__, $root_url.'/shop/cart.php?sbscr=Y');

        $member['milage'] = $member['emoney'] = 0;
	}else {
		$cart_check = $pdo->row("select count(*) from {$tbl['cart']} where 1 ".mwhere());
		if(!$cart_check) msg(__lang_shop_error_nocart2__, $root_url.'/shop/cart.php');
	}

	$buyer = array();
	if($member['level'] < 10) {
		$buyer = $member;
		$buyer_phone = explode('-', $buyer['phone']);
		$buyer_cell = explode('-', $buyer['cell']);
	}

	if(!$backURL) {
		$backURL = $root_url.'/shop/cart.php';
	}

	// 예치금 결제 가능 여부
	include_once $engine_dir."/_manage/manage2.lib.php";
	if($cfg['emoney_use'] != 'Y' || !$member['no']) {
		$usable_emoney=0;
	} else {
		$usable_emoney = $member['emoney']+0;
		$usable_emoney_c = number_format($usable_emoney,$cfg['currency_decimal']);
	}

	// 특별회원그룹
	if($member['attr_no_pg'] == 'Y') {
    	unset($cfg['pay_type_1'], $cfg['pay_type_4'], $cfg['pay_type_5'], $cfg['pay_type_7']);
		$cfg['use_kakaopay'] = 'N';
		$cfg['use_payco'] = 'N';
		$cfg['pay_type_5'] = 'N';
		$cfg['pay_type_7'] = 'N';
        $cfg['use_nsp'] = 'N';
	}

	// 결제 가능 적립금
	function usable_milage() {
		global $member, $cfg, $usable_milage, $usable_milage_c, $total_order_price, $cart_sum_price;
		if($member['level'] == 10) $usable_milage = 0;
		else {
			if(!$cfg['milage_use_order_min']) $cfg['milage_use_order_min']=0; // 결제가능 최소 적립금
			if($cfg['milage_use'] == 1 && $member['milage'] >= $cfg['milage_use_min'] && $cfg['milage_use_order_min'] <= $cart_sum_price) {
				$usable_milage = $member['milage'];
			}
			if($usable_milage>0 && $cfg['milage_use_max_type']) {
				$milage_use_max_won=0;
				if($cfg['milage_use_max_type'] == 1 && $cfg['milage_use_max_won'] > 0) { // 금액으로 제한
					$milage_use_max_won=$cfg['milage_use_max_won'];
				} else if($cfg['milage_use_max_type'] == 2 && $cfg['milage_use_max_per'] > 0) { // 구매금액 %으로 제한
					$milage_use_max_won = round($cart_sum_price*($cfg['milage_use_max_per']/100));
				}
				if($milage_use_max_won > 0 && $usable_milage > $milage_use_max_won) {
					$usable_milage = $milage_use_max_won;
				}
			}
			$usable_milage_c=number_format($usable_milage,$cfg['currency_decimal']);
		}
		return $usable_milage;
	}

	// 기존 배송지
	function oldAddressee() {
		global $member, $cart_weight;

        // create select Tag
        $dom = new DomDocument('1.0', _BASE_CHARSET_);
        $dom->loadHTML('<meta http-equiv="Content-Type" content="charset=utf-8">');
        $select = $dom->createElement('select');
        $select->setAttribute('name', 'old_addr_sel');
        $dom->appendChild($select);

        if ($member['no'] > 0) {
            memberAddressInit(); // 주소록이 없을 경우 생성

            // title
            $select
                ->appendChild(
                    $dom->createElement('option', __lang_order_info_newAddess__)
                )->setAttribute('value', '');

            // 주소록 읽기
            $res = memberAddressGet($member);
            $selected = false; // 기본 배송지 지정 여부
            foreach ($res as $addr) {
                $header = ($addr->is_default == 'Y') ? '['.__lang_order_info_defAddess__.'] ' : '';
                $option = $select->appendChild(
                    $dom->createElement('option', $header.$addr->name.' : '.$addr->addr1. ' '.$addr->addr2)
                );
                $option->setAttribute('value', implode('<wisamall>', (array) $addr));
                if (end($addr) == 'Y') {
                    $option->setattribute('selected', true);
                    $selected = true;
                }
            }
            if (!$selected && count($res) > 0) { // 만약 기본 배송지 미지정 상태일 경우 첫번 째 옵션을 기본으로 지정
                $select->childNodes->item(1)->setAttribute('selected', true);
            }
        }

        if (!$member['no']) {
            $select
                ->appendChild(
                    $dom->createElement('option', __lang_order_info_memAddess__)
                )->setAttribute('value', '');
        }

        return $dom->saveHTML($select);
	}

	if($mobile_browser == 'mobile' && $_SESSION['browser_type'] != 'mobile' && $cfg['mobile_pg_use'] == 'Y') {
		$mobile_pg_use = 'Y';
	}

	function orderPayType($bank_name = '', $esc = '', $bank_name_style = 'input') {
		global $tbl, $cfg, $scfg, $total_order_price, $member, $mobile_pg_use, $engine_url, $sbscr, $pdo;

		if($cfg['card_mobile_use'] == 'N' && $cfg['mobile_use'] == 'Y' && ($_SESSION['browser_type'] == 'mobile' || $mobile_pg_use == 'Y')) {
			unset($cfg['pay_type_1'], $cfg['pay_type_4'], $cfg['pay_type_5'], $cfg['pay_type_7']);
		}

		$r = '';

		// 정기배송 일괄결제 이용시
		$pay_type_prefix = ($sbscr=='Y') ? 'paytype_gr1' : '';

		// 토스결제
		if(($cfg['use_tosspayment'] == 'Y' && $cfg['tosspayment_api_key']) || ($cfg['use_tosscard'] == 'Y' && $cfg['tossc_liveApiKey'])) {
			if($_SESSION['browser_type'] == 'mobile') {
				$_tosspayment_img_file = 'm_tosspayment.png height="20"';
			} else {
				$_tosspayment_img_file = 'tosspayment.png height="18"';
			}
			$r .= '<div class="'.$pay_type_prefix.'">';
			$r .= '<label class="pay_label"><input type="radio" name="pay_type" id="pay_type22" value="22" onClick="useMilage(this.form,3)"> <img src='.$engine_url.'/_engine/card.tosspayment/image/'.$_tosspayment_img_file.' ></label>';
			$r .= '</div>';
		}

        // 네이버페이 간편결제
        if ($scfg->comp('use_nsp', 'Y') == true && ($sbscr != 'Y' || $scfg->comp('nsp_sub_partnerId2') == true)) {
            $icon = ($_SESSION['browser_type'] == 'mobile') ? 'naverpay_m.png' : 'naverpay_pc.png';
			$k  = '<div class="'.$pay_type_prefix.'">';
			$k .= '<label class="pay_label"><input type="radio" name="pay_type" id="pay_type25" value="25" onClick="useMilage(this.form,3)"> ';
            switch($scfg->get('nsp_button_type')) {
                case '1' :
                    $k .= '<img src="'.$engine_url.'/_engine/card.naverSimplePay/'.$icon.'" height="20">';
                    break;
                case '2' :
                    $k .= '네이버페이';
                    break;
                case '3' :
                    $k .= '<img src="'.$engine_url.'/_engine/card.naverSimplePay/'.$icon.'" height="20"> 네이버페이';
                    break;
            }
            $k .= '</label>';
			$k .= '</div>';

            $r .= $k;
        }

		// 카카오페이
		if($cfg['use_kakaopay'] == 'Y' && ((!$cfg['kakao_version'] && $cfg['kakao_id'] && $cfg['kaka_key']) || ($cfg['kakao_version']=='new' && $cfg['kakao_cid'] && $cfg['kaka_admin_key']))) {
			$k  = '<div class="'.$pay_type_prefix.'">';
			$k .= '<label class="pay_label"><input type="radio" name="pay_type" id="pay_type12" value="12" onClick="useMilage(this.form,3)"><img src="'.$engine_url.'/_engine/card.kakao/image/kakaopay.png"></label>';
			$k .= '</div>';

            $r .= $k;
		}

		// 페이코
		if($cfg['use_payco'] == 'Y' && $cfg['payco_sellerKey'] && $cfg['payco_CpId']) {
			if($_SESSION['browser_type'] == 'mobile') {
				$_payco_btn_url = 'https://static-bill.nhnent.com/payco/checkout/js/payco_mobile.js';
			} else {
				$_payco_btn_url = 'https://static-bill.nhnent.com/payco/checkout/js/payco.js';
			}
			$r .= '<div class="'.$pay_type_prefix.'">';
			$r .= '<label class="pay_label"><input type="radio" name="pay_type" id="pay_type17" value="17" onClick="useMilage(this.form,3)"> <span id="payco_btn_area">'.__lang_order_info_paytype17__.'</span></label>';
			$r .= "	<script src='$_payco_btn_url'></script><script>Payco.Button.register({SELLER_KEY:'$cfg[payco_sellerKey]',ORDER_METHOD:'EASYPAY',BUTTON_TYPE:'A1',BUTTON_HANDLER:null,DISPLAY_PROMOTION:'N',DISPLAY_ELEMENT_ID:'payco_btn_area','' : ''});</script>";
			$r .= "	<p class='msg_pay payco_info' style='padding: 5px 0 5px 23px; line-height: 160%;'>";
			$r .= "		PAYCO는 온/오프라인 쇼핑은 물론 송금, 멤버십 적립까지 가능한 통합 서비스입니다.<br>";
			$r .= "		휴대폰과 카드 명의자가 동일해야 결제 가능하며, 결제금액 제한은 없습니다.<br>";
			$r .= "		- 지원카드: 모든 국내 신용/체크카드<br>";
			$r .= "	</p>";
			$r .= '</div>';
		}

        // 삼성페이 결제
        if($scfg->comp('use_samsungpay', 'Y') == true && $scfg->get('samsungpay_id') && $scfg->get('samsungpay_pwd')) {
            $r .= '<div class="'.$pay_type_prefix.'">';
            $r .= '<label class="pay_label"><input type="radio" name="pay_type" id="pay_type28" value="28" onClick="useMilage(this.form,3)"> 삼성페이</label>';
            $r .= "	<p class='msg_pay' style='padding-left:15px;'>";
            $r .= "- 삼성페이를 이용할 수 있는 스마트폰이 필요합니다.";
            $r .= "	</p>";
            $r .= '</div>';
        }

		// 신용카드
		if($scfg->comp('pay_type_1', 'Y') == true) {
			// U+ Paynow
			if(($_SESSION['browser_type'] == 'pc' && $cfg['card_pg'] == 'dacom' && $cfg['xpay_use_paynow'] == 'Y') || ($_SESSION['browser_type'] == 'mobile' && $cfg['card_pg'] == 'dacom' && $cfg['sxpay_use_paynow'] == 'Y')) {
				$r .= "
				<div class='$pay_type_prefix'>
					<label class='pay_label'><input type='radio' name='pay_type' id='pay_type21' value='21' onClick='useMilage(this.form, 3)'> Paynow</label>
				</div>
				";
			}

			$r .= '<div class="'.$pay_type_prefix.'">';
			$r .= '<label class="pay_label"><input type="radio" name="pay_type" id="pay_type1" value="1" checked onClick="useMilage(this.form,3)"> '.__lang_order_info_paytype1__.'</label>';

			if($scfg->comp('card_confirm', 'N') == true && $scfg->comp('use_card')) { // 카드유의사항 및 공지사항
				$r .= '<div>&nbsp;'.stripslashes($cfg['use_card']).'</div>';
			}
			$r .= '</div>';
		}

		// 실시간 계좌이체
		if($scfg->comp('pay_type_5', 'Y') == true) {
			if($_SESSION['browser_type'] != 'mobile' || $cfg['card_mobile_pg'] == 'dacom' || $cfg['card_mobile_pg'] == 'kcp') {
				$r .= '<div class="'.$pay_type_prefix.'"><label class="pay_label"><input type="radio" name="pay_type" id="pay_type5" value="5"  onClick="useMilage(this.form,3)"> '.__lang_order_info_paytype5__.'</label></div>';
			}
		}

		// 가상계좌
		if($scfg->comp('pay_type_4', 'Y') == true) {
			$esc_msg = ($cfg['escrow_msg']) ? stripslashes($cfg['escrow_msg']) : __lang_order_info_defaultEscMsg__;

			$r .= '<div class="'.$pay_type_prefix.'">';
			$r .= '<label class="pay_label"><input type="radio" name="pay_type" id="pay_type4" value="4"  onClick="useMilage(this.form,3)"> '.__lang_order_info_paytype4__.'</label>';
			if($cfg['escrow_limit'] > 0) $r .= sprintf(__lang_order_info_escprc__, number_format($cfg['escrow_limit']));
			$r .= '<div class="msg_pay" style="padding-left:15px; color:#336699;">- '.$esc_msg.'</div>';
			$r .= '</div>';
		}

		// 무통장 입금
		if($scfg->comp('pay_type_2', 'Y') == true) {
			$r .= '<div class="'.$pay_type_prefix.'">';
			$r .= '<label class="pay_label"><input type="radio" name="pay_type" id="pay_type2" value="2" onClick="useMilage(this.form,3)"> '.__lang_order_info_paytype2__.'</label>';
			$r .= '<div class="msg_pay">';

			$res = $pdo->iterator("select * from {$tbl['bank_account']} where 1 $bank_where order by `sort`");
			$bank_list  = '<span id="bank_list_span"><select name="bank">';
			$bank_list .= '<option value="'.$data['no'].'">:: '.__lang_order_select_bank__.'::</option>';
            foreach ($res as $data) {
				$bank_list .= "<option value='$data[no]'>".$data['bank'].' '.$data['account'].' '.$data['owner'].'</option>';
			}
			$bank_list .= '</select></span>';
			if($bank_name) {
				$bank_list .= '<span class="bank_name">'.$bank_name."</span><input type='text' name='bank_name' value='' class='$bank_name_style form_input block' size='8' maxlength='30' placeholder=".$bank_name.">";
			}
			$r .= $bank_list;
			if($cfg['cash_receipt_use'] == 'Y' && $total_order_price > 0){
				$r .= '<div id="cash_reg" style="padding-left: 15px;">- <span style="color: #336699;">'.__lang_order_info_cashreceipt__.'</span> ';
				$r .= '<input name="cash_reg_num" type="text" size="15" maxlength="20" class="'.$bank_name_style.' form_input block" onkeyup="press_han(this)"></div>';
			}
			$r .= '</div>';
			$r .= '</div>';
		}

		// 휴대폰 결제
		if($cfg['pay_type_7'] == 'Y' && (($cfg['card_pg'] != 'dacom' || $cfg['pg_version'] != ''))) {
			$r .= '<div class="'.$pay_type_prefix.'"><label class="pay_label"><input type="radio" name="pay_type" id="pay_type7" value="7"  onClick="useMilage(this.form,3)"> '.__lang_order_info_paytype7__.'</label></div>';
		}
		// 알리페이 결제
		$useable_pay = unserialize($cfg['alipay_useable_pay']);
		if(is_array($useable_pay) && $cfg['use_alipay'] == 'Y') {
			$r .= '<div class="'.$pay_type_prefix.'"><label class="pay_label"><input type="radio" name="pay_type" id="use_alipay" value="10"  onClick="useMilage(this.form,3)"> '.__lang_order_info_paytype10__.'</label></div>';
		}
		// Paypal 결제
		$useable_pay = unserialize($cfg['paypal_useable_pay']);
		if(is_array($useable_pay) && $cfg['use_paypal'] == 'Y') {
			$r .= '<div class="'.$pay_type_prefix.'"><label class="pay_label"><input type="radio" name="pay_type" id="use_paypal" value="13"  onClick="useMilage(this.form,3)"> '.__lang_order_info_paytype13__.'</label></div>';
		}
		// Cyrexpay 결제
		$useable_pay = unserialize($cfg['cyrexpay_useable_pay']);
		if(is_array($useable_pay) && $cfg['use_cyrexpay'] == 'Y') {
			$r .= '<div class="'.$pay_type_prefix.'"><label class="pay_label"><input type="radio" name="pay_type" id="use_cyrexpay" value="14"  onClick="useMilage(this.form,3)"> '.__lang_order_info_paytype14__.'</label></div>';
		}
		// Exim-Econtext 결제
		$useable_pay = unserialize($cfg['econtext_useable_pay']);
		if(is_array($useable_pay) && $cfg['use_econtext'] == 'Y') {
			$r .= '<div class="'.$pay_type_prefix.'"><label class="pay_label"><input type="radio" name="pay_type" id="use_econtext" value="15"  onClick="useMilage(this.form,3)"> '.__lang_order_info_paytype15__.'</label></div>';
		}
		// Exim-paypal 결제
		$useable_pay = unserialize($cfg['eximbay_useable_pay']);
		if(is_array($useable_pay) && $cfg['use_paypal_c'] == 'Y') {
			$r .= '<div class="'.$pay_type_prefix.'"><label class="pay_label"><input type="radio" name="pay_type" id="use_paypal_c" value="16"  onClick="useMilage(this.form,3)"> '.__lang_order_info_paytype16__.'</label></div>';
		}
		// Wechat 결제
		$useable_pay = unserialize($cfg['eximbay_useable_pay']);
		if(is_array($useable_pay) && $cfg['use_wechat'] == 'Y') {
			$r .= '<div class="'.$pay_type_prefix.'"><label class="pay_label"><input type="radio" name="pay_type" id="use_wechat" value="18"  onClick="useMilage(this.form,3)"> '.__lang_order_info_paytype18__.'</label></div>';
		}
		// Alipay Eximbay 결제
		$useable_pay = unserialize($cfg['eximbay_useable_pay']);
		if(is_array($useable_pay) && $cfg['use_alipay_e'] == 'Y') {
			$r .= '<div class="'.$pay_type_prefix.'"><label class="pay_label"><input type="radio" name="pay_type" id="use_alipay_e" value="19"  onClick="useMilage(this.form,3)"> '.__lang_order_info_paytype19__.'</label></div>';
		}
		// Eximbay 해외카드 결제
		$useable_pay = unserialize($cfg['eximbay_useable_pay']);
		if(is_array($useable_pay) && $cfg['use_exim'] == 'Y') {
			$r .= '<div class="'.$pay_type_prefix.'"><label class="pay_label"><input type="radio" name="pay_type" id="use_exim" value="20"  onClick="useMilage(this.form,3)"> '.__lang_order_info_paytype20__.'</label></div>';
		}

		// 정기배송 정기결제
		if($sbscr=='Y') {
            if ($scfg->comp('use_nsp_sbscr', 'Y') == true && $GLOBALS['this_cart_cnt'] == 1) {
                $icon = ($_SESSION['browser_type'] == 'mobile') ? 'naverpay_m.png' : 'naverpay_pc.png';
                $r .= '<div class="paytype_gr2" style="display:none;">';
                $r .= '<label class="pay_label"><input type="radio" name="pay_type" value="27"> ';
                switch($scfg->get('nsp_sub_button_type')) {
                    case '1' :
                        $r .= '<img src="'.$engine_url.'/_engine/card.naverSimplePay/'.$icon.'" height="20">';
                        break;
                    case '2' :
                        $r .= '네이버페이';
                        break;
                    case '3' :
                        $r .= '<img src="'.$engine_url.'/_engine/card.naverSimplePay/'.$icon.'" height="20"> 네이버페이';
                        break;
                }
                $r .= '</label>';
                $r .= '</div>';
            }
            $r .= '<div class="paytype_gr2" style="display:none;">';
            $r .= '<label class="pay_label"><input type="radio" name="pay_type" value="23" checked="checked">신용카드</label>';
            $r .= '</div>';
		}

        // 주문 쿠폰 사용불가시 쿠폰 리스트 로딩 안함
        if (isset($_GET['pay_type']) == true && $_GET['pay_type'] == 'kakaopay') {
            $r = $k;
        }

		$r .= '<input type="hidden" name="order_full_milage" value="0">';

		return $r;
	}

	common_header();


	// PG모듈 로딩
	if($scfg->comp('pay_type_1', 'Y') == true || $scfg->comp('pay_type_4', 'Y') == true || $scfg->comp('pay_type_5', 'Y') == true) {
		if($cfg['pg_version']) $pg_version = $cfg['pg_version'].'/';
		if($cfg['pg_mobile_version']) $pg_mobile_version = $cfg['pg_mobile_version'].'/';
		if($cfg['card_pg'] != 'dacom' && $cfg['card_pg'] != 'inicis') $pg_version = '';
		if(($_SESSION['browser_type'] == 'mobile' || $mobile_pg_use == 'Y') || ($cfg['card_pg'] == 'inicis' && ($_SESSION['browser_type'] == 'mobile' || $mobile_pg_use == 'Y') && $cfg['card_inicis_mobile_id'])) {
			include_once $engine_dir.'/_engine/card.'.$cfg['card_mobile_pg'].'/'.$pg_mobile_version.'card_frm.php';
		} else {
			include_once $engine_dir.'/_engine/card.'.$cfg['card_pg'].'/'.$pg_version.'card_frm.php';
		}
		include_once $engine_dir.'/_engine/order/pay_cancel.php';
	}
	// 정기배송
	if($sbscr=='Y' && $cfg['sbscr_order_split']=='Y') {
		if(isset($cfg['autobill_pg']) == false || empty($cfg['autobill_pg']) == true) {
			$cfg['autobill_pg'] = 'dacom';
		}
		switch($cfg['autobill_pg']) {
			case 'dacom' : $pg_version = 'XpayAutoBilling/'; break;
			case 'nicepay' : $pg_version = 'autobill/'; break;
		}
		include_once $engine_dir.'/_engine/card.'.$cfg['autobill_pg'].'/'.$pg_version.'card_frm.php';
	}
	// 카카오페이
	if($cfg['use_kakaopay'] == 'Y' && ((!$cfg['kakao_version'] && $cfg['kakao_id'] && $cfg['kaka_key']) || ($cfg['kakao_version']=='new' && $cfg['kakao_cid'] && $cfg['kaka_admin_key']))) {
		if($cfg['kakao_version']) $pg_kakao_version = $cfg['kakao_version'].'/';
		include_once $engine_dir.'/_engine/card.kakao/'.$pg_kakao_version.'card_frm.php';
	}

    // 네이버페이
    if ($scfg->comp('use_nsp', 'Y') == true || $scfg->comp('use_nsp_sbscr', 'Y') == true) {
		include_once $engine_dir.'/_engine/card.naverSimplePay/card_frm.php';
    }

    // 삼성페이
    if ($scfg->comp('use_samsungpay', 'Y') == true && $scfg->get('samsungpay_id') && $scfg->get('samsungpay_pwd')) {
        include_once $engine_dir.'/_engine/card.samsungpay/card_frm.php';
    }

	// 이벤트 할인 체크
	$evnt_abl = checkEventAble();
	if(!$cfg['event_min_pay']) $cfg['event_min_pay'] = 0;
	if(!$cfg['event_per']) $cfg['event_per'] = 0;
	if($cfg['event_round']<10) $cfg['event_round'] = 10;

	// 회원별 적립
	if(!$cfg['member_event_type']) $cfg['member_event_type'] = 2;
	$tmp = checkMSaleAble();
	$msale_per = $tmp[0];
	$mmile_per = $tmp[1];
	if(!$msale_per) $msale_per = 0;
	if(!$mmile_per) $mmile_per = 0;

	$msale_type = '';
	if($msale_per > 0 || $mmile_per > 0) {
		$msale_type = $cfg['member_event_type'];
	}
	if($cfg['msale_round'] < 10) $cfg['msale_round'] = 1;

	// 오프라인쿠폰 검사
	$Cpn2 = 0;
	$today = date('Y-m-d');
	$date_q = " and ((`udate_type`='2' and `ustart_date`<='$today' and `ufinish_date`>='$today') or `udate_type`='1')";
	$offcpn = $pdo->row("select count(*) from {$tbl['coupon']} where `is_type`='B'".$date_q);
	if($offcpn && $member['no']){
		$Cpn2 = 1;
	}

	// 지역별 배송료 설정 검사
	$areaScript="var areaNum=0;\n";
	if($cfg['adddlv_type'] == 2) {
		$areaScript = 'var areaNum='.$pdo->row("select count(*) from {$tbl['delivery_area_detail']}").";\n";
	} else {
		$areaSql = $pdo->iterator("select * from {$tbl['delivery_area']} order by `no`");
		$areaNum = $areaSql->rowCount();
		if($areaNum) {
			$areaScript  = "var areaNum=$areaNum;\n";
			$areaScript .= "var dArea=new Array();\n";
			$areaScript .= "var dPrice=new Array();\n";
			$dn = 0;
            foreach ($areaSql as $area) {
				if($area['area'] && $area['price']){
					$areaScript .= "dArea[$dn]='$area[area]';\n";
					$areaScript .= "dPrice[$dn]='$area[price]';\n";
				}
				$dn++;
			}
		}
	}

	// 상품별 할인쿠폰 스크립트에 쓰여질 금액 미리 계산
	$mwhere = mwhere('c.');
	if($cart_where) $cart_where_str = $_cart_where[$cart_where];
	if($cart_selected) $cart_where_str .= " and c.no in ($cart_selected)";
	$cpncart_sql = $pdo->iterator("select p.`coupon` as `coupon` from `".$tbl['product']."` p, `".$tbl['cart']."` c where p.`no`=c.`pno` and p.`stat`='2' $mwhere $cart_where_str order by c.`no` desc");
	$cpn_array = array();
    foreach ($cpncart_sql as $cpncart) {
		if($cpncart['coupon']) {
			$ctmp = explode('@', $cpncart['coupon']);
			foreach($ctmp as $key=>$val) $cpn_array[]=$val;
		}
	}
	if(count($cpn_array)) {
		while($prd_cpnck = myCouponList(1)){
			if($prd_cpnck['stype'] == '2') {
				if(!$prdcpn_script) $prdcpn_script = "var prdCpn=new Array();\n";
				$coupon_prd = $pdo->row("select sum(p.`sell_prc`*c.`buy_ea`) from `$tbl[cart]` c, `$tbl[product]` p where p.`no`=c.`pno` and p.`stat`='2' and p.`coupon` like '%@$prd_cpnck[cno]@%' $mwhere $cart_where_str");
				$prdcpn_script .= "prdCpn[".$prd_cpnck['no']."]='$coupon_prd';\n";
			}
		}
		unset($mcouponRes);
	}

	// 주문서 추가 항목
	$total_add_info = 0;
	$_ord_add_info = array();
	$add_info_file = $root_dir.'/_config/order.php';
	if(is_file($add_info_file)) {
		include_once $add_info_file;
		$total_add_info = count($_ord_add_info);
	}

	if(!$cfg['delivery_free_limit']) $cfg['delivery_free_limit'] = 0;
	if(!$cfg['delivery_fee']) $cfg['delivery_fee'] = 0;

	$jquery_ver = ($_skin['jquery_ver']) ? $_skin['jquery_ver'] : 'jquery-1.4.min.js';
	$jquery_ver = preg_replace('/^jquery-([0-9.]+).*$/', '$1', $jquery_ver);;

	// 적립금 사용 단위
	if(!$cfg['milage_use_unit']) $cfg['milage_use_unit'] = 0;
	$cfg['milage_use_unit'] = numberOnly($cfg['milage_use_unit'], true);

?>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/order.js?ver=<?=date('YmdHi')?>"></script>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/shop.prdcpn.js?ver=<?=date('YmdHi')?>"></script>
<?php if ($sbscr=='Y') { ?>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/shop.sbscr.js"></script>
<?php } ?>
<script type="text/javascript">
$(function(){
	$('.datepicker').datepicker(date_picker_default);

	var f = document.getElementsByName('ordFrm');
	if(f.length == 1) {
		f = f[0];
		if(!f.elements['mail_send']) {
			$(f).prepend('<input type="hidden" name="mail_send" value="Y" />');
		}
	}
});

var nec_buyer_email='<?=$_use['nec_buyer_email']?>';
var nec_buyer_phone='<?=$_use['nec_buyer_phone']?>';
var nec_addressee_phone='<?=$_use['nec_addressee_phone']?>';

var escrow_limit='<?=$cfg['escrow_limit']?>';

var evnt_abl='<?=$evnt_abl?>';
var event_min_pay=<?=$cfg['event_min_pay']?>;
var event_type='<?=$cfg['event_type']?>';
var event_ptype='<?=$cfg['event_ptype']?>';
var event_per=<?=$cfg['event_per']?>;
var event_round=<?=$cfg['event_round']?>;

var msale_per=<?=$msale_per?>;
var mmile_per=<?=$mmile_per?>;
var msale_type='<?=$msale_type?>';
var msale_round=<?=$cfg['msale_round']?>;
var msale_delivery='<?=$msale_delivery?>';

var usable_emoney=<?=$usable_emoney?>;
var total_emoneys=0;
var cash_receipt_use='<?=$cfg['cash_receipt_use']?>';

var order_cpn_paytype='<?=$cfg['order_cpn_paytype']?>';
var order_milage_paytype='<?=$cfg['order_milage_paytype']?>';
var order_cpn_milage='<?=$cfg['order_cpn_milage']?>';
var msale_milage_cash='<?=$msale_milage_cash?>';
var use_order_phone='<?=$cfg['use_order_phone']?>';

var cart_selected='<?=$cart_selected?>';

var pg_charge_1='<?=trim($cfg['pg_charge_1'])?>';
var pg_charge_4='<?=trim($cfg['pg_charge_4'])?>';
var pg_charge_5='<?=trim($cfg['pg_charge_5'])?>';
var pg_charge_7='<?=trim($cfg['pg_charge_7'])?>';
var pg_charge_E='<?=trim($cfg['pg_charge_E'])?>';

var total_add_info=<?=$total_add_info?>;
var skip_add_info=new Array();

<?=$prdcpn_script?>

var remote_addr = "<?=$_SERVER['REMOTE_ADDR']?>";
var adddlv_type = '<?=$cfg['adddlv_type']?>';
<?=$areaScript?>
var free_delivery_area='<?=$cfg['free_delivery_area']?>';
var delivery_type = '<?=$cfg['delivery_type']?>';
var delivery_free_limit = <?=$cfg['delivery_free_limit']?>;
var delivery_fee = <?=$cfg['delivery_fee']?>;
var delivery_base = '<?=$cfg['delivery_base']?>';
var delivery_free_milage = '<?=$cfg['delivery_free_milage']?>';
var use_cpn_milage = '<?=$cfg['use_cpn_milage']?>';
var use_cpn_milage_msg = '<?=$cfg['use_cpn_milage_msg']?>';
var milage_use_unit = <?=$cfg['milage_use_unit']?>;

var freedeli_event_min_pay = '<?=$cfg['freedeli_event_min_pay']?>';
var delivery_fee_type = '<?=$cfg['delivery_fee_type']?>';

var sbscr = '<?=$sbscr?>';
var sbscr_order_all = '<?=$cfg['sbscr_order_all']?>';
var sbscr_order_split = '<?=$cfg['sbscr_order_split']?>';

var ono = '';
var sbono = '';
var ord_delivery_type = '<?= addslashes($_GET['delivery_fee_type']); ?>';

var f = document.ordFrm;
window.onload = function() {
    if ($(':checked[name=pay_type]').length == 0) {
        if ($('#pay_type1').length > 0) {
            var p = $('#pay_type1');
        } else {
            var p = $(':radio[name=pay_type]').eq(0);
        }
        p.click();

		if(p.val()=='2'){
			$('#bank_info').show();
            $('.order_cancel_msg').show();
		}
    }

    // 기존 배송지 선택
    const addressee_select = document.querySelector(`select[name="old_addr_sel"]`)
    if (addressee_select) {
        addressee_select.addEventListener('change', function() {
            putOldAddressee(this, '<?=$cart_weight?>')
            useMilage(document.ordFrm, 3)
        });
        putOldAddressee(addressee_select, '<?=$cart_weight?>');
    }

	useMilage(document.ordFrm,3);

	//도로명주소를 Default로 설정
	if($('#zip_mode1').length) {
		if(typeof $.prop == 'function') {
			$('#zip_mode1').prop('checked',true);
		} else {
			$('#zip_mode1').attr('checked',true);
		}
	}
	var f = document.ordFrm;
	if(sbscr=='Y' && typeof(f.sbscr_all)!='undefined') {
		sbscrTypeChk(f.sbscr_all.value , sbscr_order_all, sbscr_order_split);
	}

	// 결제수단 변경
    $('[name=pay_type]').on('click', function (){
        var ptype = $(this).val();
        // 무통장시 입금은행 입력
        if ( ptype == 2 ) {
            $('#bank_info').show();
            $('.order_cancel_msg').show();
        } else {
            $('#bank_info').hide();
            $('.order_cancel_msg').hide();
        }
    });

    if (ord_delivery_type == 'O' && f.nations.value) onChangePhoneCode(f.nations);
}
</script>
<?php include_once $engine_dir.'/_engine/common/skin_index.php'; ?>