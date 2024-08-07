<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  토스계좌결제 결제창 오픈
	' +----------------------------------------------------------------------------------------------+*/

	// 토스 카드
	if($cfg['use_tosscard'] == 'Y') {
		$cfg['tosspayment_api_key'] = $cfg['tossc_liveApiKey'];
	}

	if(empty($cfg['tosspayment_api_key'])) msg('토스결제 설정이 잘못되었습니다 - 관리자게에 문의하세요.');

	// 인앱 결제여부
	$inAppYn = ($_SESSION['is_wisaapp'] == true) ? 'Y' : 'N';

	// 주문 채널
	if($_SESSION['browser_type'] == 'mobile' || $inAppYn == 'Y') {
		$orderChannel = 'MOBILE';
	} else {
		$orderChannel = 'PC';
	}

	// 주문서
	function json_encode_han($arr) { // PHP 5.3 이하에서 (JSON_UNESCAPED_UNICODE 사용 불가)
		array_walk_recursive($arr, function (&$item, $key) { if (is_string($item)) $item = mb_encode_numericentity($item, array (0x80, 0xffff, 0, 0xffff), 'UTF-8'); });
		return mb_decode_numericentity(json_encode($arr), array (0x80, 0xffff, 0, 0xffff), 'UTF-8');
	}

	$json = json_encode_han(array(
		'orderNo'			=> $ono,
		'amount'			=> parsePrice($pay_prc),
		'amountTaxFree'		=> 0,
		'productDesc'		=> strip_tags($title),
		'apiKey'			=> $cfg['tosspayment_api_key'],
		'retUrl'			=> $root_url.'/main/exec.php?exec_file=card.tosspayment/card_pay.return.php',
		'retCancelUrl'		=> $root_url.'/main/exec.php?exec_file=card.tosspayment/card_pay.cancel.php'
	));



	makePGLog($ono, 'tosspayment reserve', "[req]\n".$json."\n\n[res]\n".$ret);


	// 결제생성
	$ch = curl_init('https://pay.toss.im/api/v1/payments');
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/json',
		'Content-Length: ' . strlen($json))
	);
	$result = curl_exec($ch);
	curl_close($ch);


	// 로그
	makePGLog($ono, 'tosspayment reserve', "[req]\n".$json."\n\n[res]\n".$result);


	// 생성여부 확인
	$json = json_decode($result);
	if($json->code != '0') {
		$pdo->query("delete from $tbl[order] where ono='$ono'");
		$pdo->query("delete from $tbl[order_product] where ono='$ono'");
		$pdo->query("delete from $tbl[order_stat_log] where ono='$ono'");

		if(!$json->msg) {
			$json->msg = 'Payment data error';
		}

        msg(php2java($json->msg));
		exit;
	} else {
		$url = $json->checkoutPage;

		$pg_version = $cfg['pg_mobile_version'] = $cfg['pg_version'] = '';
		cardDataInsert($tbl['card'], 'tosspay');
	}

	// 인증창 생성
	if($orderChannel == 'PC') {
		?><script type="text/javascript">
		var win = window.open('<?=$url?>', 'tospaymentPopup', 'width=400px, height=400px, status=yes, scrollbars=no, resizable=yes, menubar=no');
		if(!win) {
			window.alert('브라우저의 새창열기 설정이 차단되어있습니다.\n정상적인 결제를 위해서 새창열기를 허용해 주세요.');
            parent.layTgl3('order1', 'Y');
            parent.layTgl3('order2', 'N');
            parent.layTgl3('order3', 'Y');
		} else {
			var tosspayment_check = setInterval(function() {
				if(win.closed == true) {
                    parent.layTgl3('order1', 'Y');
                    parent.layTgl3('order2', 'N');
                    parent.layTgl3('order3', 'Y');
					clearInterval(tosspayment_check);
				}
			}, 1000);
		}
		</script><?
	} else { // 모바일일 경우 지금 페이지에서 전환
		msg('', $url, 'parent');
	}

	exit;

?>