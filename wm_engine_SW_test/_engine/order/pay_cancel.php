<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  PG 결제 실패처리
	' +----------------------------------------------------------------------------------------------+*/

	if($_SERVER['SCRIPT_NAME'] != '/mypage/order_detail.php') {
		$ono = $_POST['ono'];
		if($_GET['LGD_OID']) {
			$ono = $_GET['LGD_OID'];
			$_POST['reason'] = $_POST['reason'] ? $_POST['reason']:__lang_order_error_userPayCancel__;
		}

		$ono = addslashes(trim($ono));
		if($ono) {
			include_once $engine_dir."/_engine/include/common.lib.php";

			$ord = $pdo->assoc("select stat,nations from $tbl[order] where ono='$ono'");

			if($ord['stat'] != 11) {
				exit('stat');
			}

			$pdo->query("update `$tbl[order]` set `stat`='31', `card_fail`='Y' where stat=11 and `ono`='$ono'");
			$pdo->query("update `$tbl[order_product]` set `stat`=31 where stat=11 and `ono`='$ono'");
			ordStatLogw($ono, 31, 'Y');

			if(is_object($erpListener)) {
				$erpListener->setOrder($ono);
			}

			$reason = addslashes(trim($_POST['reason']));
			$charset = mb_detect_encoding($reason);
			if($charset && $charset != _BASE_CHARSET_) {
				$reason = mb_convert_encoding($reason, _BASE_CHARSET_, $charset);
			}
			if($reason) $pdo->query("update $tbl[card] set res_msg='$reason' where wm_ono='$ono'");
			makeOrderLog($ono, 'pay_cancel.php');

            $pg = $_POST['pg'];
			if($pg == 'alipay' || $pg == 'alipay_e' || $pg == 'paypal' || $pg == 'cyrexpay' || $pg == 'paypal_c' || $pg == 'wechat' || $pg == 'alipay_e'){
				if($cfg['delivery_fee_type'] == 'A' && trim($ord['nations'])) $delivery_fee_type = "O";
				$query = "select group_concat(t2.no) from ${tbl['order_product']} as t1 inner join ${tbl['cart']} as t2 on t1.pno=t2.pno and t1.complex_no=t2.complex_no";
				$query.= " where t1.ono='${ono}'".mwhere();
				$cart_no = $pdo->row($query);

				$url = $root_url.'/shop/order.php';
				if($delivery_fee_type) $url .= "?delivery_fee_type=".$delivery_fee_type."&cart_selected=".$cart_no;
				else $url .= "?cart_selected=".$cart_no;

				msg('', $url);
			}

			if($_GET['LGD_OID']) {
				msg($reason, $root_url.'/shop/order.php');
			}

			return;
		}
	}

?>
<form name="pay_cFrm" action="<?=$root_url?>/main/exec.php" target="hidden<?=$now?>" method="post" style="display:none;">
	<input type="hidden" name="exec_file" value="order/pay_cancel.php">
	<input type="hidden" name="ono">
	<input type="hidden" name="mode">
</form>