<?PHP

	use Wing\API\Kakao\KakaoTalkStore;

	set_time_limit(0);
	$no_qcheck = true;

	include_once $engine_dir."/_engine/include/common.lib.php";
	$kts = new KakaoTalkStore();

	switch($_GET['exec']) {
		case 'getOrders' :
			$finish_date = date('YmdHis');
			$start_date = date('YmdHis', strtotime('-1 hours'));
			$kts->getOrders($start_date, $finish_date);

			if($cfg['use_talkstore_qna'] == 'Y') {
				$ret = $kts->getStoreQna();
			}
			break;
		case 'setAllproducts' :
			$res = $pdo->iterator("select productId from $tbl[product] p inner join $tbl[product_talkstore] k on p.no=k.pno where k.useYn='Y'");
            foreach ($res as $data) {
				$kts->getProductByProductId($data['productId']);
				echo $data['productId'].'<br>';
			}
			break;
	}

	exit('OK');

?>