<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  네이버 페이 주문 수집
	' +----------------------------------------------------------------------------------------------+*/

	set_time_limit(0);

	use Wing\API\Naver\CheckoutApi4;

	include_once $engine_dir.'/_engine/include/common.lib.php';
	include_once $engine_dir.'/_engine/include/wingPos.lib.php';

	printAjaxHeader();

	$exec = 'OrderGetCheckout';
	$svc_name = 'checkout';
	$naver_api = new CheckoutApi4();

    // 실패건 재전송
    $naver_api->createLogTable();
    $res = $pdo->iterator("
        select idx, operation, args
        from {$tbl['npay_api_log']}
        where errors like '%xml parsing error%' and retry_date='0000-00-00 00:00:00'
        order by idx asc
    ");
    foreach ($res as $data) {
        $r = call_user_func_array(array($naver_api, 'api'), json_decode($data['args'], true));
        $pdo->query("update {$tbl['npay_api_log']} set retry_date=now() where idx='{$data['idx']}'");
    }

	if(!$_REQUEST['force_check']) {
		$_REQUEST['o1'] = date('Y-m-d', strtotime('-2 hours')).'T'.date('H:i:s', strtotime('-2 hours'));
	}
	if(!$_REQUEST['o2'] && $_REQUEST['o1']) $_REQUEST['o2'] = date('Y-m-d');

	include 'order_get_naver_common.inc.php';

	// 상품평 수집
	include $engine_dir.'/_engine/shop/review_get_checkout.exe.php';

    header('Content-type: application/json');
	exit(json_encode(array(
		'start_date' => $_REQUEST['o1'],
		'end_date' => $_REQUEST['o2'],
		'order' => array(
			'total' => $totcnt,
			'new' => $newcnt,
			'changed' => $upcnt
		)
	)));

?>