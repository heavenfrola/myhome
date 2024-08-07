<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  SMS 변수/함수
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_manage/manage2.lib.php";


	// SMS 카테고리
	# 고객수신 SMS
	$sms_case_title[1]="회원가입";
	$sms_case_title[2]="주문완료";
	$sms_case_title[3]=$_order_stat[2];
	$sms_case_title[4]=$_order_stat[3];
	$sms_case_title[5]=$_order_stat[4];
	$sms_case_title[6]=$_order_stat[5];
	$sms_case_title[13]="무통장 주문";
	$sms_case_title[15]="자동입금확인";
	$sms_case_title[9]="입금요청";
	$sms_case_title[26]="미입금 주문 자동취소";
	$sms_case_title[14]="부분상품발송";
	$sms_case_title[29]=$_order_stat[15];
	$sms_case_title[30]=$_order_stat[17];
	$sms_case_title[22]="인증번호발송";
	$sms_case_title[41] = '회원가입 승인(사업자/14세 미만)';
	$sms_case_title[8]="상품질문답변";
	$sms_case_title[20]="적립금소멸(정보성)";
	$sms_case_title[21]="적립금소멸(광고성)";
	$sms_case_title[38]="쿠폰발급";
	$sms_case_title[39]="적립금수동지급";
	$sms_case_title[16]="생일쿠폰발송";
	$sms_case_title[23]="광고성정보 수신여부 변경";

	// 재입고 알림
	$sms_case_title[24]="재입고 알림 신청";
	$sms_case_title[25]="재입고 알림 발송";
	$sms_case_title[28]="휴면회원 사전안내";
	$sms_case_title[31]="개인정보 이용내역 안내";

	// 정기배송
	$sms_case_title[32] = '주문 완료';
	$sms_case_title[27] = '배송시작(정기결제)';
	$sms_case_title[33] = '배송시작(일괄결제)';
	$sms_case_title[34] = '취소완료';
	$sms_case_title[35] = '회차취소';
	//$sms_case_title[36] = '품절';
	$sms_case_title[37] = '진행종료';

	# 관리자수신 SMS
	$sms_case_title[11]="회원가입";
	$sms_case_title[40] = '가입승인 요청';
	$sms_case_title[12]="주문완료";
	$sms_case_title[18]="가상계좌,자동입금확인";
	$sms_case_title[17]="신규게시글 작성";
	$sms_case_title[19] = "관리자설정변경";

	// DB저장된 케이스별 메시지
	# 기본 메시지
	$sms_def_msg[1]="[".$cfg['company_mall_name']."] {이름}님, 회원가입을 축하드립니다. 아이디:{아이디}"; // 가입
	$sms_def_msg[2]="[".$cfg['company_mall_name']."] {주문자}님의 주문정보-주문번호:{주문번호},금액:{금액}원"; // 주문완료
	$sms_def_msg[13]="[".$cfg['company_mall_name']."] 입금정보 : {계좌번호} / {금액}원"; // 주문완료
	$sms_def_msg[3]="[".$cfg['company_mall_name']."] 입금이 확인 되었습니다-주문번호:{주문번호},금액:{금액}원"; // 입금완료
	$sms_def_msg[4]="[".$cfg['company_mall_name']."] {주문자}님의 상품이 준비중입니다-주문번호:{주문번호}"; // 상품준비중
	$sms_def_msg[5]="[".$cfg['company_mall_name']."] {주문자}님의 상품이 발송되었습니다-{배송사}:{송장번호}"; // 상품발송
	$sms_def_msg[6]="[".$cfg['company_mall_name']."] {주문자}님의 상품의 배송이 완료되었습니다-주문번호:{주문번호}"; // 발송완료
	$sms_def_msg[9]="[".$cfg['company_mall_name']."] {주문자}님의 주문이 입금전입니다-주문번호:{주문번호} ({금액}원)"; // 입금요청
	$sms_def_msg[26]="[".$cfg['company_mall_name']."] {주문자}님 입금기한 만료로 {주문번호} 주문이 취소되었습니다."; // 미입금 주문 자동취소

	$sms_def_msg[7]="[".$cfg['company_mall_name']."] {이름}님의 인증번호입니다-{인증번호}"; // 임시비번
	$sms_def_msg[8]="[".$cfg['company_mall_name']."] {이름}님의 질문의 답변이 등록되었습니다"; // 상품비번답변

	$sms_def_msg[11]="[".$cfg['company_mall_name']."] {이름}님이 가입하셨습니다. 아이디:{아이디}"; // 가입 - 관리자
	$sms_def_msg[12]="[".$cfg['company_mall_name']."] {주문자}님의 주문정보-주문번호:{주문번호},금액:{금액}원"; // 주문완료
	$sms_def_msg[14]="[".$cfg['company_mall_name']."] {주문자}님의 상품이 부분발송되었습니다-{배송사}:{송장번호}"; // 부분상품발송
	$sms_def_msg[15]="[".$cfg['company_mall_name']."] 입금 확인 되었습니다-주문번호:{주문번호},금액:{금액}원"; // 자동입금확인
	$sms_def_msg[38]="[".$cfg['company_mall_name']."] {이름}님! 상품 주문 시 사용할 수 있는 쿠폰이 발급되었습니다.\n- 발급 쿠폰 : {쿠폰명}\n- 만료일 : {쿠폰만료일}";
	$sms_def_msg[39]="[".$cfg['company_mall_name']."] {이름}님 ! 고객님의 아이디({아이디})로 상품 주문 시 사용할 수 있는 적립금이 적립되었습니다.\n- 지급 적립금 : {지급적립금}\n- 지급 사유 : {사유}";
	$sms_def_msg[16]="[".$cfg['company_mall_name']."] {이름}님 생일 쿠폰이 발급되었습니다.";
	$sms_def_msg[17]="[".$cfg['company_mall_name']."] {게시판명}에 신규게시물이 등록되었습니다.";
	if($admin['level'] > 3) $sms_def_msg[17]="[".$cfg['company_mall_name']."] {상품명}에 상품문의가 등록되었습니다.";
	$sms_def_msg[18]="[".$cfg['company_mall_name']."] {주문번호} 주문의 {금액}원 입금이 확인되었습니다.";
	$sms_def_msg[19]="[".$cfg['company_mall_name']."] {관리자이름}님에 의해 {설정명}이 변경되었습니다.";
	$sms_def_msg[20]="[".$cfg['company_mall_name']."] 보유하고 계신 적립금 {소멸적립금}원이 {소멸예정일} 소멸예정입니다. 자세한 사항은 쇼핑몰 공지사항을 확인해주시기 바랍니다.";
	$sms_def_msg[21]="[".$cfg['company_mall_name']."] 보유하고 계신 적립금 {소멸적립금}원이 {소멸예정일} 소멸예정입니다. 소멸된 적립금은 복구되지 않으니 유효기간 내 사용하시기 바랍니다.";
	$sms_def_msg[22]="[".$cfg['company_mall_name']."] 인증번호는 [{인증번호}] 입니다.";
	$sms_def_msg[23]="[".$cfg['company_mall_name']."] 광고성정보 변경여부 안내 {광고성정보변경일자} {SMS이메일수신동의여부}";
	// 재입고 알림
	$sms_def_msg[24]="[".$cfg['company_mall_name']."] '{재입고상품명}({재입고상품옵션})' 상품 재입고 알림 신청이 등록되었습니다."; // 재입고 알림 신청
	$sms_def_msg[25]="[".$cfg['company_mall_name']."] {이름}님, '{재입고상품명}({재입고상품옵션})' 상품이 입고되었습니다."; // 재입고 알림 발송
	$sms_def_msg[27]="[".$cfg['company_mall_name']."]  {주문자}님의 정기배송이 시작될 예정입니다.\n\n- 결제 금액 : {금액}\n- 상품명 : {상품명}\n- 배송예정일 : {배송예정일}";
	$sms_def_msg[28]="[".$cfg['company_mall_name']."] {이름}님, {휴면처리일}에 휴면회원으로 전환될 예정입니다.";
	$sms_def_msg[29]="[".$cfg['company_mall_name']."] {주문자}님, 환불 처리가 완료되었습니다.\n\n주문번호 : {주문번호}\n환불금액 : {금액}\n\n결제수단에 따라 1~4일 영업일 이내 환불금액을 확인하실 수 있습니다.";
	$sms_def_msg[30]="[".$cfg['company_mall_name']."] {주문자}님, 반품 처리가 완료되었습니다.\n\n주문번호 : {주문번호}\n상품명 : {주문상품명}\n환불금액 : {금액}원\n\n결제수단에 따라 1~4일 영업일 이내 환불금액을 확인하실 수 있습니다.";
	$sms_def_msg[31]="[".$cfg['company_mall_name']."] {이름} 님, 개인정보보호법 제39조의8(개인정보 이용내역의 통지) 및 동법 기행령 제48조의6에 의거하여 연 1회 고객님의 개인정보 이용내역에 대해 안내드립니다. 자세한 사항은 ".$cfg['company_mall_name']." 개인정보 처리방침을 확인해 주시기 바랍니다.";
	$sms_def_msg[32]="[".$cfg['company_mall_name']."] {주문자}님의 님의 정기배송 주문이 완료되었습니다.\n- 주문번호:{주문번호}\n- 금액:{금액}원\n- 첫배송일:{첫배송일}"; // 주문완료
	$sms_def_msg[33]="[".$cfg['company_mall_name']."] {주문자}님의 정기배송이 시작될 예정입니다.\n\n- 상품명 : {상품명}\n- 배송예정일 : {배송예정일}";
	$sms_def_msg[34]="[".$cfg['company_mall_name']."] {주문자}님의 정기배송 주문이 취소되었습니다.\n- 주문번호 : {주문번호}";
	$sms_def_msg[35]="[".$cfg['company_mall_name']."] {주문자}님의 정기배송 주문 중 일부 회차가 취소되었습니다.\n- 주문번호 : {주문번호}";
	//$sms_def_msg[36]="[".$cfg['company_mall_name']."] {주문자}님의 정기배송 상품이 품절되었습니다.\n상품 입고시점 이후로 정상결제되어 배송될 예정입니다.\n- 주문번호 : {주문번호}\n- 상품명 : {상품명}";
	$sms_def_msg[37]="[".$cfg['company_mall_name']."] {주문자}님의 정기배송 주문이 진행종료 되었습니다.\n- 주문번호 : {주문번호}";
	$sms_def_msg[40]="[".$cfg['company_mall_name']."] {이름}님이 가입승인 요청을 하셨습니다.\n- 아이디 : {아이디}";
	$sms_def_msg[41]="[".$cfg['company_mall_name']."] {이름}님, 회원가입이 승인되었습니다.\n- 아이디:{아이디}";

	function SMS_send_case($case, $phone = null, $partner = null) {
		global $tbl, $cfg, $scfg, $engine_dir, $root_url, $sms_def_msg, $sms_replace, $sms_case_admin, $we_mms, $config_name, $admin, $pdo;

		$we_mms = new WeagleEyeClient($GLOBALS['_we'], $cfg['sms_module']);

		$sms_name = array('이름', '아이디', '주문자', '주문번호', '결제수단', '배송사', '송장번호', '금액', '인증번호', '계좌번호', '제목', '게시판명', '설정명', '관리자이름', '상품명', '입금자명', '소멸적립금', '소멸예정일', '배송조회링크', '광고성정보변경일자', 'SMS이메일수신동의여부', '재입고상품명', '재입고상품옵션', '주문상품명', '휴면처리일', '배송지', '배송예정일', '첫배송일', '쿠폰명', '쿠폰만료일', '지급적립금', '적립금유효기간', '사유');
		$sms_value = array('name', 'member_id', 'buyer_name', 'ono', 'pay_type', 'dlv_name', 'dlv_code', 'pay_prc', 'pwd', 'account', 'title', 'board_name', 'config_name', 'admin', 'prd_name', 'bank_name', 'amount', 'expire_date', 'dlv_link', 'agree_date', 'agree_receive', 'notify_restock_prd', 'notify_restock_opt', 'title', 'trans_date', 'address', 'dlv_date', 'first_dlv_date', 'cpn_name', 'cpn_finish_date', 'milage_amount', 'milage_expiration', 'mtitle');

		if ($partner > 0) {
			$data = $pdo->assoc("select * from `$tbl[partner_sms]` where `case`='$case' and `partner_no`='$partner'");
			$cfg['night_sms_start'] = ($partner_sms['night_sms_start']) ? $partner_sms['night_sms_start'] : $cfg['night_sms_start'];
			$cfg['night_sms_end'] = ($partner_sms['night_sms_end']) ? $partner_sms['night_sms_end'] : $cfg['night_sms_end'];
		} else {
			$data = $pdo->assoc("select * from `$tbl[sms_case]` where `case`='$case'");
		}
		$msg = $data['msg'] ? $data['msg'] : $sms_def_msg[$case];

		if($case == 22){
			$data['use_check'] = "Y";
		}

		if($phone != 'test1') {
			foreach($sms_value as $key=>$val) {
				$msg = str_replace("{".$sms_name[$key]."}",$sms_replace[$val],$msg);
			}
		}
		if($phone == 'test1' || $phone == 'test2') return $msg;

		if(($case == 21 && $cfg['milage_expire_sms_case'] == 'B') || $case == 16){
			if($cfg['use_080sms'] == 'Y') $denial  = ' 수신거부 : '.$cfg['080_number'];
			$msg = '(광고) '.$msg.$denial;
		}

		if(!$data['sms_night']) $data['sms_night'] = 'N';
		if($cfg['night_sms_start'] != '' && $cfg['night_sms_end'] != '' && $data['sms_night'] != 'N') {
			$nTime = date('H');
			if(
				($cfg['night_sms_start'] > $cfg['night_sms_end'] && ($cfg['night_sms_start'] <= $nTime || $cfg['night_sms_end'] >= $nTime)) ||
				($cfg['night_sms_start'] <= $cfg['night_sms_end'] && ($cfg['night_sms_start'] <= $nTime && $cfg['night_sms_end'] >= $nTime))
			) {
				if($data['sms_night'] == 'Y') return;
				if($data['sms_night'] == 'H') {
					for($i = $nTime; $i < ($nTime+24); $i++) {
						$hour = $i;
						$hour%=24;
						if($cfg['night_sms_start'] > $cfg['night_sms_end']) {
							if($hour < $cfg['night_sms_start'] && $hour > $cfg['night_sms_end']) {
								$s_date = strtotime(date("Y-m-d {$hour}:00:00"))+(86400*floor($i/24));
								break;
							}
						} else {
							if($hour < $cfg['night_sms_start'] || $hour > $cfg['night_sms_end']) {
								$s_date = strtotime(date("Y-m-d {$hour}:00:00"))+(86400*floor($i/24));
								break;
							}
						}
					}
				}
			}
            if ($s_date) $s_date = date('Y-m-d H:i:s', $s_date);
		}

		if($case != '19' && in_array($case, $sms_case_admin) == true && !$partner) {
			if($phone != 'test3') {
				if($cfg['config_sms_rec'] == 2) $phone = $cfg['config_sms_rec_num'];
				else {
					if ($case != 19) $phone = $cfg['admin_cell'];
				}
			}
		}
		if(!$phone) return;

		if (!$cfg['config_sms_send_num']) {
			$mms_callback = MMS_callback();
			$_mms_callback = explode('@', $mms_callback);
			$_mms_callback = array_filter($_mms_callback);

			if (count($_mms_callback) > 0) {
				$cfg['config_sms_send_num'] = $_mms_callback[count($_mms_callback)-1];
				$cfg['config_sms_send'] = $config['config_sms_send'] = 2;

				$scfg->import(array(
					'config_sms_send_num' => $cfg['config_sms_send_num'],
					'config_sms_send' => 2
				));
			}
		}

		if($cfg['config_sms_send'] == '2') $call_back = $cfg['config_sms_send_num'];
		else $call_back = $cfg['company_phone'];
		$reserved_time = ($s_date) ? date('YmdHis', strtotime($s_date)):'00000000000000';
		if(!$s_date) $s_date=date("Y-m-d H:i:", $GLOBALS['now'])."00";

		if($data['alimtalk_code'] || $phone == 'test3') {
			$alim_data = $pdo->assoc("select * from $tbl[alimtalk_template] where templateCode='$data[alimtalk_code]'");
			if($phone == 'test3') $amsg = $GLOBALS['message'];
			else $amsg = $alim_data['templateContent'];

			if($amsg) {
				foreach($sms_value as $key=>$val) {
					$amsg = str_replace("#{".$sms_name[$key]."}", $sms_replace[$val], $amsg);
				}

				if($phone == 'test3') return $amsg;
				if($data['use_check'] != 'Y') return;

				$currency_type = ($cfg['currency_type'] == "원") ? "KRW" : $cfg['currency_type'];

				for($ii=1;$ii<=5;$ii++) {
					if($alim_data['button'.$ii]) {
						$_button = json_decode($alim_data['button'.$ii], true);
						foreach($sms_value as $key=>$val) {
							if(preg_match('/redirect.wisa.co.kr/',$sms_replace[$val])) {
								$_button['linkPc'] = str_replace("http://redirect.wisa.co.kr/", "", $_button['linkPc']);
								$_button['linkMo'] = str_replace("http://redirect.wisa.co.kr/", "", $_button['linkMo']);
							}
							if(preg_match("/".$sms_name[$key]."/", $_button['linkPc'])) {
								$_button['linkPc'] = str_replace("#{".$sms_name[$key]."}", $sms_replace[$val], $_button['linkPc']);
							}
							if(preg_match("/".$sms_name[$key]."/", $_button['linkMo'])) {
								$_button['linkMo'] = str_replace("#{".$sms_name[$key]."}", $sms_replace[$val], $_button['linkMo']);
							}
						}
						${'button'.$ii} = urlencode(json_encode(array(
							'name' => $_button['name'],
							'type' => $_button['linkType'],
							'url_pc' => $_button['linkPc'],
							'url_mobile' => $_button['linkMo'],
						)));
					}
				}
				$wec_alm = new weagleEyeClient($GLOBALS['_we'], 'alimtalk');
				$wec_alm->call('alimtalk', array(
					'send_case' => $case,
					'profile_key' => $cfg['alimtalk_profile_key'],
					'message_type' => 'at',
					'template_code' => $data['alimtalk_code'],
					'send_num' => $call_back,
					'receiver_num' => $phone,
					'message' => urlencode($amsg),
					'sms_message' => urlencode($msg),
					'reserved_time' => $reserved_time,
					'root_url' => $root_url,
					'button1' => $button1,
					'button2' => $button2,
					'button3' => $button3,
					'button4' => $button4,
					'button5' => $button5,
					'price' => numberOnly($sms_replace['pay_prc']),
					'currency_type' => $currency_type
				));
				$wec_alm->result = iconv('euc-kr', 'utf-8', $wec_alm->result);
				return true;
			}
		}

		if($data['use_check'] != "Y" && $case != 'manual') return;

		if($data['mng_push'] == 'Y' || $data['mng_push'] == 'A') {
			$res = $we_mms->call('push_send', array(
				'case' => $case,
				'ono' => $sms_replace['ono'],
				'uid' => $GLOBALS['member']['member_id'],
				'no' => $GLOBALS['no'],
				'board_type' => $GLOBALS['board_type'],
				'msg' => $msg,
			));
			if($data['mng_push'] != 'A') return $res;
		}

		if(defined('__WEAGLEEYE_OUTSIDE__') == false) {
			$msg = iconv(_BASE_CHARSET_, "EUC-KR//IGNORE", $msg);
		}

        $title = mb_strimwidth(strip_tags($msg), 0, 20, '', 'EUC-KR');

		ob_start();
		$we_mms = new WeagleEyeClient($GLOBALS['_we'], $cfg['sms_module']);
		$we_mms->queue("mms_send", "wing", $GLOBALS['wec']->config['account_idx'], $phone, 1, $call_back, $title, $msg, "", $root_url, $s_date, $case);
		$we_mms->send_clean();
		ob_end_clean();

        return ($we_mms->result);
	}


	// 남은 문자 없을시 충전 안내
	function SMS_print() {
		$sms_rest = SMS_rest();
		if($sms_rest <= 0) {
			javac("
				if(confirm('현재 잔여 포인트가 없습니다. 충전하시겠습니까?')) {
					goMywisa('?body=wing@main');
				}
				self.close();
			");
			return true;
		} else {
			return false;
		}
	}


	// 남은 SMS 체크
	function SMS_rest() {
		global $engine_dir, $engine_url, $cfg, $root_url, $now, $we_mms;

		$we_mms->call("mms_get_rest", array('args1'=>'wing', 'args2' => $GLOBALS['wec']->config['account_idx'], 'args3' => $root_url, 'args4' => $now));
		$sms_rest=$we_mms->result;
		$sms_rest=($sms_rest == "OK") ? 0 : $sms_rest;

		return $sms_rest;
	}

	// 등록된 발신번호 체크
	function MMS_callback() {
		global $we_mms, $cfg;

		$we_mms->call('getMmsCallback', array('args1'=>'wing','args2'=>$GLOBALS['wec']->config['account_idx']));
		$mms_callback=$we_mms->result;
		$mms_callback=($mms_callback == "OK") ? "" : $mms_callback;

		return $mms_callback;
	}

	// 입점사 sms, email 발송
	function partnerSmsSend($ono, $case) {
		global $tbl, $cfg, $engine_dir, $pdo, $_mstr, $sms_replace, $ord, $_pay_type,
            $mail_title;

		if($cfg['use_partner_shop'] != 'Y' || !$cfg['partner_sms_config'] || $cfg['partner_sms_config'] == 3) return;

		$res = $pdo->iterator("select c.pno, c.name, b.partner_sms_use,b.corporate_name, b.partner_sms, b.partner_email, b.partner_email_use, a.partner_no from $tbl[order_product] c inner join $tbl[product] a on c.pno=a.no inner join $tbl[partner_shop] b on a.partner_no=b.no where c.ono='$ono' and a.partner_no > 0 group by b.no");
        foreach ($res as $partner) {
			if($cfg['partner_sms_config'] == 1 || ($cfg['partner_sms_config'] == 2 && $partner['partner_sms_use'] == 'Y')) {
                // case = '주문완료'일때 주문상품명, 금액 출력
                if ($case == '12') {
                    $_title = strip_tags($partner['name']);
                    $prd_cnt = $pdo->assoc("select count(*) as cnt, sum(total_prc) as pay_prc from {$tbl['order_product']} where ono='$ono' && partner_no='{$partner['partner_no']}'");
                    $sms_replace['pay_prc'] = parsePrice($prd_cnt['pay_prc'], true); // 금액
                    $sms_replace['title'] = ($prd_cnt['cnt'] > 1) ? $_title.' 외 '.($prd_cnt['cnt']-1).'건' : $_title; // 주문상품명
                }

				$partnersms = explode(',', $partner['partner_sms']);
				foreach($partnersms as $_partnersms) {
					SMS_send_case($case,$_partnersms,$partner['partner_no']);
				}
			}
			if($partner['partner_no'] && $partner['partner_email_use'] == 'Y' && $partner['partner_email'] != '' && $case == 12) {
				$mail_case = 23;
				$_mstr['입점사명'] = $partner['corporate_name'];
                include $engine_dir.'/_engine/include/mail.lib.php';
                if (!in_array('0', explode('@', trim($cfg['email_checked'], '@')))) {
                    //본사 관리자는 미발송으로 체크시에는 입점사 관리자만 별도 발송
                    sendMailContent($mail_case, stripslashes($ord['buyer_name']), $partner['partner_email']); //입점사 관리자 발송
                }
			}
		}
	}

	$we_mms = new WeagleEyeClient($GLOBALS['_we'], $cfg['sms_module']);

?>