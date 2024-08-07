<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  해외배송비 리턴
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir.'/_engine/include/common.lib.php';
	include_once $engine_dir.'/_engine/include/shop2.lib.php';

	$cart_weight = $_POST['weight'];
	$nations = $_POST['nations'];
	$delivery_com = $_POST['delivery'];

	$delivery_prc = deliveryPrc();
	$delivery_prc = parsePrice($delivery_prc,false,false);

	if($nations && $delivery_com && $cart_weight && $delivery_prc == 0) exit("F");

	if(!$delivery_prc) $delivery_prc = 0;
	exit("$delivery_prc");

?>