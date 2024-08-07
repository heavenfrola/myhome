<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  페이코 결제창 오픈
	' +----------------------------------------------------------------------------------------------+*/

	// 화폐단위
	$currency = ($cfg['currency_type']) ? $cfg['currency_type'] : 'KRW';
	if($currency == '원') $currency = 'KRW';

	// 결제모드
	if($easy_pay_direct_order == 'true') { // 바로구매
		$orderMethod = 'CHECKOUT';
		$payco_order_price = $ptnOrd->getData('total_order_price')-$ptnOrd->getData('dlv_prc');
		$payco_pay_price = parsePrice($pay_prc)-$ptnOrd->getData('dlv_prc');
	} else { // 간편결재
		$orderMethod = 'EASYPAY_F';
		$payco_order_price = $total_prc;
		$payco_pay_price = parsePrice($pay_prc);
	}

	// 인앱 결제여부
	$inAppYn = ($_SESSION['is_wisaapp'] == true) ? 'Y' : 'N';

	// 주문 채널
	if($_SESSION['browser_type'] == 'mobile' || $inAppYn == 'Y') {
		$orderChannel = 'MOBILE';
	} else {
		$orderChannel = 'PC';
	}

	// 대표상품 이미지
	$prd = $pdo->assoc("select p.updir, p.upfile3 from $tbl[order_product] o inner join $tbl[product] p on o.pno=p.no where o.ono='$ono' limit 1");
	$imgurl = getFileDir($prd['updir']).'/'.$prd['updir'].'/'.$prd['upfile3'];
	if($cfg['ssl_type'] == 'Y') {
		$imgurl = 'https:'.$imgurl;
	}

	// 주문상품 리스트 (상품별로 생성하는 것이 정석이나 payco 기술지원 에서 묶어서 보내는 방식을 추천)
	$order_products = array();
	$order_products[] = array(
		'cpId' => $cfg['payco_CpId'],
		'productId' => $cfg['payco_productId'],
		'productAmt' => $payco_order_price,
		'productPaymentAmt' => $payco_pay_price,
		'orderQuantity' => 1,
		'option' => '',
		'sortOrdering' => 1,
		'productName' => urlencode(strip_tags($title)),
		'orderConfirmUrl' => $root_url.'/mypage/order_detail.php?ono='.$ono,
		'orderConfirmMobileUrl' => $m_root_url.'/mypage/order_detail.php?ono='.$ono,
		'productImageUrl' => $imgurl,
		'sellerOrderProductReferenceKey' => $ono
	);

	// 직접 구매시 배송비를 별도의 상품 형태로 등록
	if($easy_pay_direct_order == 'true') {
		$order_products[] = array(
			'cpId' => $cfg['payco_CpId'],
			'productId' => $cfg['payco_productId2'],
			'productAmt' => $ptnOrd->getData('dlv_prc'),
			'productPaymentAmt' => $ptnOrd->getData('dlv_prc'),
			'orderQuantity' => 1,
			'option' => '',
			'sortOrdering' => 2,
			'productName' => '배송비',
			'orderConfirmUrl' => $root_url.'/mypage/order_detail.php?ono='.$ono,
			'orderConfirmMobileUrl' => $m_root_url.'/mypage/order_detail.php?ono='.$ono,
			'sellerOrderProductReferenceKey' => $ono.'_dlv'
		);
	}

	// 주문서
	function json_encode_han($arr) { // PHP 5.3 이하에서 (JSON_UNESCAPED_UNICODE 사용 불가)
		array_walk_recursive($arr, function (&$item, $key) { if (is_string($item)) $item = mb_encode_numericentity($item, array (0x80, 0xffff, 0, 0xffff), 'UTF-8'); });
		return mb_decode_numericentity(json_encode($arr), array (0x80, 0xffff, 0, 0xffff), 'UTF-8');
	}

	$extra_data = addslashes(json_encode(array(
		'cancelMobileUrl' => $m_root_url.'/shop/order.php',
	)));

	$seller_options = addslashes(json_encode(array(
		'remoteAreaDeliveryFeeYn' => 'Y',
		'remoteAreaDeliveryFeeSearchUrl' => $p_root_url.'/_data/compare/payco/delivery.php',
	)));

	$json = json_encode_han(array(
		'sellerKey' => $cfg['payco_sellerKey'],
		'sellerOrderReferenceKey' => $ono,
		'currency' => $currency,
		'totalDeliveryFeeAmt' => $dlv_prc,
		'totalPaymentAmt' => parsePrice($pay_prc),
		'totalSellerDiscountAmt' => $total_sale,
		'totalTaxfreeAmt' => 0,
		'orderTitle' => strip_tags($title),
		'serviceUrl' => $p_root_url.'/main/exec.php?exec_file=card.payco/card_pay.exe.php',
		'serviceUrlParam' => addslashes(json_encode(array('ono' => $ono, 'timestamp' => $now))),
		'returnUrl' => $root_url.'/main/exec.php?exec_file=card.payco/card_return.exe.php',
		'returnUrlParam' => addslashes(json_encode(array('ono' => $ono, 'orderChannel'=>$orderChannel))),
		'nonBankbookDepositInformUrl' => $root_url.'/main/exec.php?exec_file=card.payco/bank_pay.exe.php',
		'orderMethod' => $orderMethod,
		'orderChannel' => $orderChannel,
		'inAppYn' => $inAppYn,
		'payMode' => 'PAY1',
		'orderProducts' => $order_products,
		'ExtraData' => $extra_data,
		'sellerOptions' => $seller_options,

	));

	include 'lib/payco_config.php';
	include 'lib/payco_util.php';
	$ret = payco_reserve(urldecode(stripslashes($json)));

	makePGLog($ono, 'poyco reserve', "[req]\n".$json."\n\n[res]\n".$ret);

	$json = json_decode($ret);
	if($json->code != '0') {
		$pdo->query("delete from $tbl[order] where ono='$ono'");
		$pdo->query("delete from $tbl[order_product] where ono='$ono'");
		$pdo->query("delete from $tbl[order_stat_log] where ono='$ono'");

		if(!$json->message) {
			$json->message = 'Payment data error';
		}

		msg(php2java($json->message));
		exit;
	} else {
		$url = $json->result->orderSheetUrl;

		$pg_version = $cfg['pg_mobile_version'] = $cfg['pg_version'] = '';
		cardDataInsert($tbl['card'], 'payco');
	}

	if($orderChannel == 'PC') {
		?><script type="text/javascript">
		var win = window.open('<?=$url?>', 'paycoPopup', 'width=400px, height=400px, status=yes, scrollbars=no, resizable=yes, menubar=no');
		if(!win) {
			window.alert('브라우저의 새창열기 설정이 차단되어있습니다.\n정상적인 결제를 위해서 새창열기를 허용해 주세요.');
            parent.layTgl3('order1', 'Y');
            parent.layTgl3('order2', 'N');
            parent.layTgl3('order3', 'Y');
		} else {
			var paycocheck = setInterval(function() {
				if(win.closed == true) {
                    parent.layTgl3('order1', 'Y');
                    parent.layTgl3('order2', 'N');
                    parent.layTgl3('order3', 'Y');
                    clearInterval(paycocheck);
				}
			}, 1000);
		}
		</script><?
	} else { // 모바일일 경우 지금 페이지에서 전환
		msg('', $url, 'parent');
	}

	exit;

?>