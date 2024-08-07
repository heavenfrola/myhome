<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  추가배송비 API
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir.'/_engine/include/common.lib.php';
	include_once $engine_dir.'/_engine/include/shop.lib.php';

	$fp = fopen($root_dir.'/_data/payco_addr.txt', 'w');
	fwrite($fp, $_REQUEST['response']);
	fclose($fp);

	$response = urldecode($_REQUEST['response']);
	$json = json_decode($response);
	$ono = $json->sellerOrderReferenceKey;

	$_POST['addressee_zip'] = $json->zipcode;
	$_POST['addressee_addr1'] = $json->address1;

	$ptnOrd = new OrderCart();
	$res = $pdo->iterator("select * from $tbl[order_product] where ono='$ono'");
    foreach ($res as $cart) {
		$ptnOrd->addCart($cart);
	}
	$ptnOrd->complete();

	exit(json_encode(array(
		'resultCode' => '0',
		'totalRemoteAreaDeliveryFeeAmt' => $ptnOrd->getData('add_dlv_prc')
	)));

?>