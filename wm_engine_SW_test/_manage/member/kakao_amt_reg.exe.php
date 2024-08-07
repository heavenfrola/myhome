<?PHP

	include_once $engine_dir."/_engine/include/common.lib.php";

	$wec = new weagleEyeClient($_we, 'alimtalk');

	$no = numberOnly($_POST['no']);
	$exec = $_POST['exec'];
	$sms_case = numberOnly($_POST['sms_case']);
	$templateName = addslashes(trim($_POST['templateName']));
	$templateContent = trim($_POST['templateContent']);
	$templateContent_q = addslashes($templateContent);
	$buttonType = ($_POST['buttonType'] == 'C') ? 'C' : 'N';
	$buttonName = addslashes(trim($_POST['buttonName']));

	if($no > 0) {
		$data = $pdo->assoc("select templateCode, use_yn from $tbl[alimtalk_template] where no='$no'");
		if(!$data['templateCode']) {
			msg('존재하지 않는 템플릿 정보입니다.');
		}
	}

	if($exec == 'remove') {
        $tno = implode(',', numberOnly($_POST['tno']));
        foreach ($_POST['tno'] as $tno) {
            $data = $pdo->assoc("select templateCode from {$tbl['alimtalk_template']} where no=?", array($tno));

            $ret = $wec->call('deleteTemplate', array('templateCode' => $data['templateCode'])); // 실제 삭제
            $ret = json_decode($ret);
            $ret = $ret[0];
            if($ret->code == 'success') { // 카카오서버에서 삭제되었을 경우
                $pdo->query("delete from {$tbl['alimtalk_template']} where no=?", array($tno));
            } else { // 카카오서버에 삭제 불가능한 상태일 경우
                $pdo->query("update {$tbl['alimtalk_template']} set reg_status='DEL' where no=?", array($tno));
            }
            $pdo->query("update {$tbl['sms_case']} set alimtalk_code='' where alimtalk_code=?", array($data['templateCode']));
        }
        msg('', 'reload', 'parent');
	}

	if($exec == 'request') {
		$ret = $wec->call('requestTemplate', array('templateCode' => $data['templateCode']));
		$ret = json_decode($ret);
		$ret = $ret[0];
		if($ret->code == 'success') {
			$pdo->query("update $tbl[alimtalk_template] set reg_status='REQ' where no='$no'");
			exit('OK');
		} else {
			if(!$ret->message) $ret->message = '심사 신청 중 오류가 발생하였습니다.';
			exit($ret->message);
		}
	}

	if($exec == 'use') {
		$use_type = ($_POST['use_type'] == 'Y') ? 'Y' : 'N';
		$data = $pdo->assoc("select templateCode, sms_case from $tbl[alimtalk_template] where no='$no'");
		addField($tbl['sms_case'], 'alimtalk_code', "varchar(50) not null default ''");

		if($use_type == 'Y') {
			$pdo->query("update $tbl[alimtalk_template] set use_yn='N' where no!='$no' and sms_case='$data[sms_case]'");
		} else {
			$data['templateCode'] = '';
		}

		$pdo->query("update $tbl[alimtalk_template] set use_yn='$use_type' where no='$no'");
		$pdo->query("update $tbl[sms_case] set alimtalk_code='$data[templateCode]' where `case`='$data[sms_case]'");

		exit('OK');
	}

	if($exec == 'preview') {
		header('Content-type:application/json; charset='._BASE_CHARSET_);
		include_once $engine_dir.'/_manage/config/sms_config.php';

		$message = $_POST['message'];
		$message = SMS_send_case($_POST['sms_case'], 'test3');
		exit(json_encode(array('message' => nl2br($message))));
	}

    if ($exec == 'recover') {
        $wec_alm = new weagleEyeClient($_we, 'alimtalk');
        $ret = $wec_alm->call('recover', array(
            'senderKey' => $cfg['alimtalk_profile_key'],
        ));
        $ret = json_decode($ret);
        if ($ret->code == 'success') {
            exit('OK');
        } else {
            exit($ret->message);
        }
    }

	if($exec == 'sample') {
		header('Content-type:application/json; charset='._BASE_CHARSET_);

		switch($_POST['sms_case']) {
			case '1' :
				$message  = "[$cfg[company_mall_name]]\n반갑습니다. #{이름}님!\n";
				$message .= "[$cfg[company_mall_name]] 에 회원으로 가입해 주셔서 정말 감사드립니다.\n";
				$message .= "<회원가입정보>\n";
				$message .= "아이디:#{아이디}\n\n";
				$message .= "고객님을 위해 준비해 둔 많은 상품\n";
				$message .= "천천히 둘러보시고\n";
				$message .= "많은 이용 부탁드립니다.\n\n";
				$message .= "  ▶[$cfg[company_mall_name]] $root_url\n";
				$message .= "  ▶고객센터 : $cfg[company_phone]";
			break;
			case '2' :
				$message  = "[$cfg[company_mall_name]]\n";
				$message .= "안녕하세요 . #{주문자}님!\n";
				$message .= "고객님의 소중한 주문이 잘 완료되었습니다.\n";
				$message .= "주문에 감사드립니다.\n\n";
				$message .= "<주문정보>\n";
				$message .= "주문번호:#{주문번호}\n";
				$message .= "금액:#{금액}원\n\n";
				$message .= "  ▶[$cfg[company_mall_name]] $root_url\n";
				$message .= "  ▶고객센터 : $cfg[company_phone]";
			break;
			case '3' :
				$message  = "[$cfg[company_mall_name]]\n";
				$message .= "안녕하세요 . #{주문자}님!\n";
				$message .= "고객님의 주문에 대한\n";
				$message .= "입금이 확인 되었습니다.\n\n";
				$message .= " <주문정보>\n";
				$message .= " -주문번호:#{주문번호}\n";
				$message .= " -금액:#{금액}원\n\n";
				$message .= "기본배송일은 입금확인 후 2~5일(주말제외)입니다.\n";
				$message .= "감사합니다.\n";
				$message .= "  ▶[$cfg[company_mall_name]] $root_url\n";
				$message .= "  ▶고객센터 : $cfg[company_phone]";
			break;
			case '4' :
				$message  = "[$cfg[company_mall_name]]\n";
				$message .= "안녕하세요 . #{주문자}님!\n\n";
				$message .= "고객님의 상품이 준비중입니다.\n";
				$message .= "빠른 상품준비로 고객님을 찾아가겠습니다.\n";
				$message .= "감사합니다.\n\n";
				$message .= "-주문번호:#{주문번호}\n\n";
				$message .= "  ▶[$cfg[company_mall_name]] $root_url\n";
				$message .= "  ▶고객센터 : $cfg[company_phone]";
			break;
			case '5' :
				$message  = "[$cfg[company_mall_name]]\n";
				$message .= "안녕하세요. #{주문자}님!\n\n";
				$message .= "기다리시던 고객님의 주문 상품이\n";
				$message .= "금일 발송되었습니다.\n";
				$message .= "<배송정보>\n";
				$message .= "-#{주문번호}\n";
				$message .= "-택배사 : #{배송사}\n";
				$message .= "-송장번호 : #{송장번호}\n\n";
				$message .= "발송후 2~3일이 소요될 수 있습니다.(영업일기준)\n";
				$message .= "상품 수령 후 마이페이지에서 수취확인을 눌러주시면\n";
				$message .= "상품후기작성이 가능합니다.\n";
				$message .= "감사합니다.\n\n";
				$message .= "  ▶[$cfg[company_mall_name]] $root_url\n";
				$message .= "  ▶고객센터 : $cfg[company_phone]";
			break;
			case '6' :
				$message  = "[$cfg[company_mall_name]]\n";
				$message .= "안녕하세요 . #{주문자}님!\n\n";
				$message .= "주문하신 상품 배송이 완료되었습니다.\n";
				$message .= "기대이상의 상품이셨기를 바랍니다.\n";
				$message .= "감사합니다\n";
				$message .= "-주문번호:#{주문번호}\n\n";
				$message .= "  ▶[$cfg[company_mall_name]] $root_url\n";
				$message .= "  ▶고객센터 : $cfg[company_phone]";
			break;
			case '7' :
				$message  = "[$cfg[company_mall_name]]\n";
				$message .= "안녕하세요. #{이름}님!\n\n";
				$message .= " 고객님의 인증번호입니다.\n";
				$message .= "-#{인증번호}\n";
				$message .= "확인 후 재로그인 부탁드립니다.\n";
				$message .= "감사합니다.\n\n";
				$message .= "  ▶[$cfg[company_mall_name]] $root_url\n";
				$message .= "  ▶고객센터 : $cfg[company_phone]";
			break;
			case '8' :
				$message  = "[$cfg[company_mall_name]]\n";
				$message .= "안녕하세요 . #{이름}님!  \n\n";
				$message .= "고객님께서 남겨주신 소중한 질문에 대해\n";
				$message .= "답변이 등록되었습니다.\n";
				$message .= "확인바랍니다.\n";
				$message .= "감사합니다.\n\n";
				$message .= "  ▶[$cfg[company_mall_name]] $root_url\n";
				$message .= "  ▶고객센터 : $cfg[company_phone]";
			break;
			case '8' :
				$message  = "[$cfg[company_mall_name]]\n";
				$message .= "안녕하세요 . #{이름}님!  \n\n";
				$message .= "고객님께서 남겨주신 소중한 질문에 대해\n";
				$message .= "답변이 등록되었습니다.\n";
				$message .= "확인바랍니다.\n";
				$message .= "감사합니다.\n\n";
				$message .= "  ▶[$cfg[company_mall_name]] $root_url\n";
				$message .= "  ▶고객센터 : $cfg[company_phone]";
			break;
			case '9' :
				$message  = "[$cfg[company_mall_name]]\n";
				$message .= "안녕하세요 . #{주문자}님!\n\n";
				$message .= "#{주문자}님의 주문이 입금을 기다리고 있습니다.\n";
				$message .= "애써서 주문하신 상품을 놓치지 마세요.\n\n";
				$message .= "<주문&입금정보>\n";
				$message .= "#{주문번호}\n";
				$message .= "#{계좌번호}\n";
				$message .= "(#{금액}원)\n";
				$message .= "  ▶[$cfg[company_mall_name]] $root_url\n";
				$message .= "  ▶고객센터 : $cfg[company_phone]";
			break;
			case '26' :
				$message  = "[$cfg[company_mall_name]]\n";
				$message .= "안녕하세요. #{주문자}님!\n\n";
				$message .= "주문하신 상품의 입금기한이 경과되어 자동 취소되었습니다.\n";
				$message .= "- 주문번호 : #{주문번호}\n\n";
				$message .= "  ▶[$cfg[company_mall_name]] $root_url\n";
				$message .= "  ▶고객센터 : $cfg[company_phone]";
				break;
			case '28' :
				$message  = "[$cfg[company_mall_name]]\n";
				$message .= "안녕하세요 . #{이름}님!\n\n";
				$message .= "정보통신망법 이용촉진 및 정보보호 등에 관한 법률에 의거하여,\n";
				$message .= "1년동안 로그인 이력이 없는 고객님들의 개인정보를 안전하게 보호하고자\n";
				$message .= "휴면회원 전환을 통해 개인정보를 분리/보관할 예정입니다.\n\n";
				$message .= "<전환예정일>\n";
				$message .= "#{휴면처리일}\n\n";
				$message .= "휴면회원 전환 이후 다시 로그인 해주시면 일반회원으로 전환가능합니다.\n\n";
				$message .= "  ▶[$cfg[company_mall_name]] $root_url\n";
				$message .= "  ▶고객센터 : $cfg[company_phone]";
				break;
			case '13' :
				$message  = "[$cfg[company_mall_name]]\n";
				$message .= "안녕하세요 . #{주문자}님!\n\n";
				$message .= "주문에 감사드리며 따로 메모하실 필요가 없는\n";
				$message .= "입금확인정보입니다.\n";
				$message .= "<입금정보>\n";
				$message .= "  #{계좌번호} / #{금액}원\n\n";
				$message .= "입금이 확인되는대로, 고객님의 주문상품을\n";
				$message .= "준비하여 발송해드리겠습니다.\n";
				$message .= "감사합니다.\n\n";
				$message .= "  ▶[$cfg[company_mall_name]] $root_url\n";
				$message .= "  ▶고객센터 : $cfg[company_phone]";
			break;
			case '14' :
				$message  = "[$cfg[company_mall_name]]\n";
				$message .= "안녕하세요 . #{주문자}님!\n\n";
				$message .= "#{주문자}님의 상품이 부분발송되었습니다.\n\n";
				$message .= "<배송정보>\n";
				$message .= "-택배사 : #{배송사}\n";
				$message .= "-송장번호 : #{송장번호}\n";
				$message .= "  ▶[$cfg[company_mall_name]] $root_url\n";
				$message .= "  ▶고객센터 : $cfg[company_phone]";
			break;
			case '15' :
				$message  = "[$cfg[company_mall_name]]\n";
				$message .= "안녕하세요 . #{주문자}님!\n\n";
				$message .= "고객님의 주문에 대한\n";
				$message .= "입금이 확인 되었습니다.\n\n";
				$message .= "<주문정보>\n";
				$message .= "-주문번호 : #{주문번호}\n";
				$message .= "-금액 : #{금액}원\n\n";
				$message .= "기본배송일은 입금확인 후 2~5일(주말제외)입니다.\n";
				$message .= "감사합니다.\n\n";
				$message .= "  ▶[$cfg[company_mall_name]] $root_url\n";
				$message .= "  ▶고객센터 : $cfg[company_phone]";
			break;
			case '16' :
				$message  = "[$cfg[company_mall_name]]\n";
				$message .= "안녕하세요. #{이름}님!\n\n";
				$message .= "더없이 소중한 고객님의 생일을 축하드립니다!!!\n\n";
				$message .= "고객님만을 위해 기분좋게 쓰시라고\n";
				$message .= "[$cfg[company_mall_name]] 에서 생일 쿠폰을 드렸습니다.\n";
				$message .= "마이페이지>나의 쿠폰함에서 확인하셔서\n";
				$message .= "다른날보다는 풍족한 생일 되시길 바랍니다.\n\n";
				$message .= "  ▶[$cfg[company_mall_name]] $root_url\n";
				$message .= "  ▶고객센터 : $cfg[company_phone]";
			break;
            case '20' :
				$message  = "[$cfg[company_mall_name]] 안녕하세요 . #{이름}님!\n\n";
				$message .= "#{이름} 님께서 보유하신 적립금이 소멸 예정 입니다.\n\n";
				$message .= "- 소멸 예정 적립금 : #{소멸적립금}\n";
				$message .= "- 소멸 예정일 : #{소멸예정일}\n\n";
				$message .= "※ 적립금 상세 내역은 '로그인> 마이페이지 > 적립금'에서 확인 가능합니다.\n\n";
				$message .= "감사합니다.\n";
				$message .= "  ▶[$cfg[company_mall_name]] $root_url\n";
				$message .= "  ▶고객센터 : $cfg[company_phone]";
            break;
			case '22' :
				$message  = "[$cfg[company_mall_name]]\n";
				$message .= "안녕하세요.\n\n";
				$message .= "인증번호는 [#{인증번호}] 입니다.\n\n";
				$message .= "  ▶[$cfg[company_mall_name]] $root_url\n";
				$message .= "  ▶고객센터 : $cfg[company_phone]";
			break;
			case '23' :
				$message  = "[$cfg[company_mall_name]]\n";
				$message .= "광고성정보 변경여부 안내\n";
				$message .= "#{광고성정보변경일자} #{SMS이메일수신동의여부}\n\n";
				$message .= "  ▶[$cfg[company_mall_name]] $root_url\n";
				$message .= "  ▶고객센터 : $cfg[company_phone]";
			break;
			case '24' :
				$message  = "[$cfg[company_mall_name]]\n";
				$message .= "'#{재입고상품명}(#{재입고상품옵션})' 상품 재입고 알림 신청이 등록되었습니다.\n\n";
				$message .= "  ▶[$cfg[company_mall_name]] $root_url\n";
				$message .= "  ▶고객센터 : $cfg[company_phone]";
				break;
			case '25' :
				$message  = "[$cfg[company_mall_name]]\n";
				$message .= "#{이름}님, '#{재입고상품명}(#{재입고상품옵션})' 상품이 입고되었습니다.\n\n";
				$message .= "  ▶[$cfg[company_mall_name]] $root_url\n";
				$message .= "  ▶고객센터 : $cfg[company_phone]";
				break;
			case '11' :
				$message  = "[$cfg[company_mall_name]]\n";
				$message .= "#{이름}님이 가입하셨습니다.\n";
				$message .= "<회원가입 정보>\n";
				$message .= "-아이디 : #{아이디}\n";
				break;
			case '12' :
				$message  = "[$cfg[company_mall_name]]\n";
				$message .= "#{주문자}님의 주문정보\n";
				$message .= "<주문 정보>\n";
				$message .= "-주문번호:#{주문번호}\n";
				$message .= "-금액:#{금액}원";
				break;
			case '18' :
				$message  = "[$cfg[company_mall_name]]\n";
				$message .= "#{주문번호} 주문의 #{금액}원 입금이 확인되었습니다.";
				break;
			case '17' :
				$message  = "[$cfg[company_mall_name]]\n";
				$message .= "'#{게시판명}' 게시판에 신규게시물이 등록되었습니다.";
				break;
			case '29' :
				$message  = "[{$cfg['company_mall_name']}]\n";
				$message .= "안녕하세요. #{주문자} 님!\n";
				$message .= "고객님이 요청하신 환불처리가 완료되었습니다.\n";
				$message .= "\n";
				$message .= "<환불정보>\n";
				$message .= "주문번호 : #{주문번호}\n";
				$message .= "환불금액 : #{금액}\n";
				$message .= "\n";
				$message .= "결제수단에 따라 1~4일 영업일 이내 환불금액을 확인하실 수 있습니다.\n";
				$message .= "\n";
				$message .= "  ▶[{$cfg['company_mall_name']}] $root_url\n";
				$message .= "  ▶고객센터 : {$cfg['company_phone']}\n";
				break;
			case '30' :
				$message  = "[{$cfg['company_mall_name']}]\n";
				$message .= "안녕하세요. #{주문자} 님!\n";
				$message .= "고객님이 요청하신 반품처리가 완료되었습니다.\n";
				$message .= "\n";
				$message .= "<반품정보>\n";
				$message .= "주문번호 : #{주문번호}\n";
				$message .= "상품명 : #{주문상품명}\n";
				$message .= "반품금액: #{금액}\n";
				$message .= "\n";
				$message .= "결제수단에 따라 1~4일 영업일 이내 환불금액을 확인하실 수 있습니다.\n";
				$message .= "\n";
				$message .= "  ▶[{$cfg['company_mall_name']}] $root_url\n";
				$message .= "  ▶고객센터 : {$cfg['company_phone']}\n";
				break;
			case '32' :
				$message  = "[$cfg[company_mall_name]]\n";
				$message .= "안녕하세요. #{주문자}님!\n";
				$message .= "고객님의 정기배송 주문이 완료되었습니다.\n";
				$message .= "주문에 감사드립니다.\n";
				$message .= "\n";
				$message .= "<주문정보>\n";
				$message .= "- 주문번호:#{주문번호}\n";
				$message .= "- 금액:#{금액}원\n";
				$message .= "- 첫배송일:#{첫배송일}\n";
				$message .= "\n";
				$message .= "  ▶[{$cfg['company_mall_name']}] $p_root_url/\n";
				$message .= "  ▶고객센터 : {$cfg['company_phone']}\n";
				break;
			case '27' :
				$message  = "[$cfg[company_mall_name]]\n";
				$message .= "안녕하세요. #{주문자}님!\n";
				$message .= "고객님의 정기배송이 시작될 예정입니다.\n";
				$message .= "\n";
				$message .= "<주문정보>\n";
				$message .= "- 결제 금액 : #{금액}원\n";
				$message .= "- 상품명 : #{상품명}\n";
				$message .= "- 배송예정일 : #{배송예정일}\n";
				$message .= "\n";
				$message .= "  ▶[{$cfg['company_mall_name']}] $p_root_url/\n";
				$message .= "  ▶고객센터 : {$cfg['company_phone']}\n";
				break;
			case '33' :
				$message  = "[$cfg[company_mall_name]]\n";
				$message .= "안녕하세요. #{주문자}님!\n";
				$message .= "고객님의 정기배송이 시작될 예정입니다.\n";
				$message .= "\n";
				$message .= "- 상품명 : #{상품명}\n";
				$message .= "- 배송예정일 : #{배송예정일}\n";
				$message .= "\n";
				$message .= "  ▶[{$cfg['company_mall_name']}] $p_root_url/\n";
				$message .= "  ▶고객센터 : {$cfg['company_phone']}\n";
				break;
			case '34' :
				$message  = "[$cfg[company_mall_name]]\n";
				$message .= "안녕하세요 #{주문자}님\n";
				$message .= "고객의 정기배송 주문이 취소되었습니다.\n";
				$message .= "\n";
				$message .= "- 주문번호 : #{주문번호}\n";
				$message .= "\n";
				$message .= "  ▶[{$cfg['company_mall_name']}] $p_root_url/\n";
				$message .= "  ▶고객센터 : {$cfg['company_phone']}\n";
				break;
			case '35' :
				$message  = "[$cfg[company_mall_name]]\n";
				$message .= "안녕하세요 #{주문자}님\n";
				$message .= "고객님의 정기배송 주문 중 일부 회차가 취소되었습니다.\n";
				$message .= "\n";
				$message .= "- 주문번호 : #{주문번호}\n";
				$message .= "\n";
				$message .= "  ▶[{$cfg['company_mall_name']}] $p_root_url/\n";
				$message .= "  ▶고객센터 : {$cfg['company_phone']}\n";
				break;
			case '37' :
				$message  = "[$cfg[company_mall_name]]\n";
				$message .= "안녕하세요 #{주문자}님\n";
				$message .= "고객님의 정기배송 주문이 진행종료 되었습니다.\n";
				$message .= "\n";
				$message .= "- 주문번호 : #{주문번호}\n";
				$message .= "\n";
				$message .= "  ▶[{$cfg['company_mall_name']}] $p_root_url/\n";
				$message .= "  ▶고객센터 : {$cfg['company_phone']}\n";
				break;
            case '38' :
				$message  = "[{$cfg['company_mall_name']}] 안녕하세요 . #{이름}님!\n\n";
				$message .= "#{이름}님을 위해 {$cfg['company_mall_name']}에서 상품 주문 시 사용할 수 있는 쿠폰이 발급되었습니다.\n\n";
				$message .= "- 발급 쿠폰 : #{쿠폰명}\n";
				$message .= "- 유효 기간 : #{쿠폰만료일}\n\n";
				$message .= "※ 유효기간 연장은 불가하오니 유의해 주세요.\n";
				$message .= "※ 쿠폰 발급 내역은 '로그인> 마이페이지 > 쿠폰'에서 확인 가능합니다.\n\n";
				$message .= "감사합니다.\n\n";
				$message .= "  ▶[{$cfg['company_mall_name']}] {$root_url}\n";
				$message .= "  ▶고객센터 : {$cfg['company_phone']}";
                break;
            case '39' :
				$message  = "[{$cfg['company_mall_name']}] 안녕하세요 . #{이름}님!\n\n";
				$message .= "#{이름}님을 위해 {$cfg['company_mall_name']}에서 상품 주문 시 사용할 수 있는 적립금이 적립되었습니다.\n\n";
				$message .= "- 지급 적립금 : #{지급적립금}\n";
				$message .= "- 유효 기간 : #{적립금유효기간}\n\n";
				$message .= "※ 유효기간 연장은 불가하오니 유의해 주세요.\n";
				$message .= "※ 적립금 상세 내역은 '로그인> 마이페이지 > 적립금'에서 확인 가능합니다.\n\n";
				$message .= "감사합니다.\n\n";
				$message .= "  ▶[{$cfg['company_mall_name']}] {$root_url}\n";
				$message .= "  ▶고객센터 : {$cfg['company_phone']}";
                break;
			case '40' :
	            $message  = "[$cfg[company_mall_name]]\n";
				$message .= "#{이름}님이 가입승인 요청을 하셨습니다.\n";
				$message .= "<회원가입 정보>\n";
				$message .= "-아이디 : #{아이디}\n";
				break;
			case '41' :
	            $message  = "[$cfg[company_mall_name]]\n";
				$message .= "반갑습니다. #{이름}님!\n";
				$message .= "[$cfg[company_mall_name]] 에 회원으로 가입해 주셔서 감사드리며,\n";
				$message .= "회원가입이 승인되어 사이트 로그인 후 이용이 가능합니다.\n\n";
				$message .= "<회원가입 정보>\n";
				$message .= "-아이디 : #{아이디}\n\n";
				$message .= "감사합니다.\n\n";
				$message .= "  ▶[{$cfg['company_mall_name']}] $p_root_url/\n";
				$message .= "  ▶고객센터 : {$cfg['company_phone']}\n";
				break;
            case '31' :
                $message  = "[$cfg[company_mall_name]]";
                $message .= " 개인정보 이용내역 안내\n\n";
                $message .= "개인정보보호법 제39조의8(개인정보 이용내역의 통지) 및 동법 시행령 제48조6에 의거하여 연 1회 고객님의 개인정보 이용내역에 대해 안내드립니다. 자세한 사항은";
                $message .= " $cfg[company_mall_name]";
                $message .= " 개인정보 처리방침을 확인해 주시기 바랍니다.\n\n";
                $message .= "  ▶[{$cfg['company_mall_name']}] $p_root_url/\n";
                $message .= "  ▶고객센터 : {$cfg['company_phone']}\n";
                break;
		}

		include_once $engine_dir.'/_manage/config/sms_config.php';

		$replace = SMS_send_case($_POST['sms_case'], 'test3');
		exit(json_encode(array('message' => $message, 'replace' => nl2br($replace))));
	}

    if ($exec == 'name_check') {
        $check = $pdo->row("select count(*) from {$tbl['alimtalk_template']} where templateName=? and no!=?", array(
            trim($_POST['value']), $_POST['no']
        ));

        header('Content-type:application/json');
        exit(json_encode(array(
            'result' => ($check > 0) ? 'overlapping' : 'success'
        )));
    }

	if(!$sms_case) msg('발송분류를 선택해주세요.');
	if(!$templateName) msg('메시지명을 입력해주세요.');
	if(!$templateContent) msg('메시지 내용을 입력해주세요.');
	if($buttonType == 'C' && !$buttonName) msg('버튼 이름을 입력해주세요.');

	$profile = $wec->call('getProfile');
	$profile = json_decode($profile);

	// 버튼 생성
	if(is_array($_POST['button_name'])) {
		foreach($_POST['button_name'] as $key => $val) {
			$btn_no = ($key+1);
			${'button'.$btn_no} = json_encode(array(
				'ordering' => $btn_no,
				'name' => $val,
				'linkType' => $_POST['button_type'][$key],
				'linkPc' => $_POST['button_purl'][$key],
				'linkMo' => $_POST['button_murl'][$key]
			));
			${'_button'.$btn_no} = addslashes(${'button'.$btn_no});
		}
	}

	if($no > 0) {
		$ret = $wec->call('updateTemplate', array(
			'senderKey' => $cfg['alimtalk_profile_key'],
			'templateCode' => $data['templateCode'],
			'newSenderKey' => $cfg['alimtalk_profile_key'],
			'newTemplateCode' => $data['templateCode'],
			'newTemplateName' => urlencode($templateName),
			'newTemplateContent' => urlencode($templateContent),
			'newbutton1' => urlencode($button1),
			'newbutton2' => urlencode($button2),
			'newbutton3' => urlencode($button3),
			'newbutton4' => urlencode($button4),
			'newbutton5' => urlencode($button5),
			'newSmsContent' => urlencode($templateConten)
		));
		$ret = json_decode($ret);
	} else {
		$templateCode  = 'ws'.$profile->account_idx.'c'.$sms_case.'_';
		$templateCode .= $pdo->row("select max(no) from $tbl[alimtalk_template]")+1;

        $amt_category_code = array(
            '1' => '001001', '2' => '002001', '3' => '002001', '4' => '006001', '5' => '006001',
            '6' => '006003', '8' => '005001', '9' => '005002', '11' => '001001', '12' => '002001',
            '14' => '006001', '13' => '002001', '15' => '005006', '16' => '009001', '17' => '005001',
            '18' => '005006', '19' => '005001', '20' => '009005', '21' => '009005', '22' => '001002',
            '23' => '007001', '24' => '002005', '25' => '002005', '26' => '002004', '27' => '002002',
            '28' => '007004', '29' => '002004', '30' => '002001', '31' => '007002', '32' => '001001',
            '33' => '002002', '34' => '002004', '35' => '002004', '36' => '002004', '37' => '002004',
            '38' => '009001', '39' => '009003', '40' => '001001', '41' => '001001'
        );

		$ret = $wec->call('createTemplate', array(
			'senderKey' => $cfg['alimtalk_profile_key'],
			'templateCode' => $templateCode,
			'templateName' => urlencode($templateName),
			'templateContent' => urlencode($templateContent),
			'button1' => urlencode($button1),
			'button2' => urlencode($button2),
			'button3' => urlencode($button3),
			'button4' => urlencode($button4),
			'button5' => urlencode($button5),
			'smsContent' => urlencode($templateContent),
            'categoryCode' => $amt_category_code[$sms_case],
		));

		$ret = json_decode($ret);
		$ret = $ret[0];
	}

	if($ret->code != 'success') {
		msg(php2java($ret->message));
		exit;
	}

	if(!fieldExist($tbl['alimtalk_template'], 'button1')) {
		addField($tbl['alimtalk_template'], 'button1', 'varchar(1000) null default ""');
		addField($tbl['alimtalk_template'], 'button2', 'varchar(1000) null default ""');
		addField($tbl['alimtalk_template'], 'button3', 'varchar(1000) null default ""');
		addField($tbl['alimtalk_template'], 'button4', 'varchar(1000) null default ""');
		addField($tbl['alimtalk_template'], 'button5', 'varchar(1000) null default ""');
	}

	if($no > 0) {
		$pdo->query("
			update $tbl[alimtalk_template] set
				sms_case='$sms_case', templateName='$templateName', templateContent='$templateContent_q', buttonType='$buttonType', buttonName='$buttonName', button1='$_button1', button2='$_button2', button3='$_button3', button4='$_button4', button5='$_button5'
			where no='$no'
		");
	} else {
		$pdo->query("
			insert into $tbl[alimtalk_template]
				(sms_case, templateCode, templateName, templateContent, buttonType, buttonName, button1, button2, button3, button4, button5, reg_date, reg_status, tmp_status)
				values
				('$sms_case', '$templateCode', '$templateName', '$templateContent_q', '$buttonType', '$buttonName', '$_button1', '$_button2', '$_button3', '$_button4', '$_button5', '$now', 'REG', 'R')
		");
	}

    $rURL=getListURL('kakao_amt_msg');
    if(!$rURL) $rURL='./?body=member@kakao_amt_msg';

    msg('', $rURL, "parent");

?>
