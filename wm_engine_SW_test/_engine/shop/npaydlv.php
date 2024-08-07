<?PHP

	include_once $engine_dir.'/_engine/include/common.lib.php';

	$cfg['free_delivery_area'] = 'Y'; // 무료배송 시에도 지역별 추가배송비 부과 무조건 사용 (네이버 정책 미지원)

	$zipcode = $_POST['addressee_zip'] = $_GET['zipcode'];
    $address1 = str_replace(array('-','_'), array('+','/'), $_GET['address1']);
    $mod4 = strlen($address1) % 4;
    if ($mod4) {
        $address1 .= substr('====', $mod4);
    }
	$address1 = base64_decode($address1);
    if (preg_match('/^제주특별자치도/', $address1) == true) {
        $address1 = preg_replace('/1동$/', '일동', $address1);
        $address1 = preg_replace('/2동$/', '이동', $address1);
        $address1 = preg_replace('/3동$/', '삼동', $address1);
    }

    $_POST['addressee_addr1'] = $address1;
	$productId = $_POST['productId'] = $_GET['productId'];

	$xml = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"utf-8\" ?><additionalFees/>");

	if(is_array($productId)) {
		foreach($productId as $pno) {
			$res = $pdo->iterator("select * from $tbl[product] where no=$pno");

			$ptnOrd = new OrderCart();
            foreach ($res as $cart) {
				$cart['pno'] = $cart['no'];
				$cart['buy_ea'] = 1;

				$ptnOrd->addCart($cart);
			}
			$ptnOrd->complete();

			while($obj = $ptnOrd->loopCart()) {
				$cart = $obj->data;

				if(!$obj->parent->add_dlv_prc) {
					$obj->parent->add_dlv_prc = 0;
				}

				$tmp = $xml->addChild('additionalFee');
				$tmp->addChild('id', $cart['pno']);
				//$tmp->addChild('groupId', $cart['partner_no']);
				$tmp->addChild('surprice', $obj->parent->add_dlv_prc);
			}
		}
	}

	$fp = fopen($GLOBALS['root_dir'].'/_data/cache/npaydlv.txt', 'w');
	fwrite($fp, print_r($_SERVER, true));
	fclose($fp);
	$xml->asXML($GLOBALS['root_dir'].'/_data/cache/npaydlv.xml');

	header('Content-type:text/xml; charset=utf-8');
	exit($xml->asXML());

?>