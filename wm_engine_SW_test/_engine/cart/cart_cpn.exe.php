<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  상품별 쿠폰 설정
	' +----------------------------------------------------------------------------------------------+*/

	header('Content-type:application/json; charset='._BASE_CHARSET_);

	include_once $engine_dir.'/_engine/include/common.lib.php';

	$cart_no = numberOnly($_POST['cart_no']);
	$prdCpn = $_POST['prdCpn'];
	$attach_mode = numberOnly($_POST['attach_mode']);

	foreach($cart_no as $_cart_no) {
		$_cart_no = numberOnly($_cart_no);
		$prdcpn_no = (is_array($prdCpn[$_cart_no]) == true) ? implode(',', numberOnly($prdCpn[$_cart_no])) : '';

		// 권한체크
		$cart = $pdo->assoc("select member_no from $tbl[cart] where no='$_cart_no'");
		if(!$member['no'] || $cart['member_no'] != $member['no']) {
			exit(json_encode(array('result'=>'faild', 'message'=>__lang_common_error_modifyperm__)));
		}

		$pdo->query("update $tbl[coupon_download] set cart_no='0' where member_no='$member[no]' and cart_no='$_cart_no' and ono=''"); // 해제
        if ($prdcpn_no){
            $pdo->query("update $tbl[coupon_download] set cart_no='$_cart_no' where member_no='$member[no]' and ono='' and no in ($prdcpn_no)"); // 추가
        }
	}

	exit(json_encode(array('result'=>'success', 'message'=>'OK')));

?>