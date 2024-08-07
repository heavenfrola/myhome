<?PHP

	include_once $engine_dir.'/_engine/include/common.lib.php';

	$ono = addslashes(trim($_POST['ono']));
	$pay_type = $_POST['pay_type'];

	// 결제방식 체크
	if(empty($pay_type)) msg(__lang_order_select_paytype__);
	if(empty($cfg['change_pay_type'])) msg(__lang_order_select_paytype__);
	$cfg['change_pay_type'] = explode('@', $cfg['change_pay_type']);
	if(in_array($pay_type, $cfg['change_pay_type']) == false) {
		msg(__lang_order_select_paytype__);
	}

	// 주문번호 체크 및 결제 변수 생성
	if(!$ono) msg(__lang_mypage_error_onoNotExist__);
	$ord = $pdo->assoc("select * from $tbl[order] where ono='$ono'");
	if($ord['stat'] != '1') msg(__lang_mypage_error_onoNotExist__);
	$buyer_name = stripslashes($ord['buyer_name']);
	$title = stripslashes($ord['title']);
	$pay_prc = parsePrice($ord['pay_prc']);

	$pdo->query("update {$tbl['order']} set pay_type='$pay_type', card_fail='Y' where ono='$ono'");

	include_once $engine_dir.'/_engine/order/order_paytype.exe.php';
	include_once $engine_dir."/_engine/card.{$card_pg}/{$pg_version}card_pay.php";

?>