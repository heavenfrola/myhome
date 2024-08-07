<?PHP

	include_once $engine_dir.'/_engine/include/common.lib.php';
	include_once $engine_dir.'/_engine/include/file.lib.php';
	include_once $engine_dir.'/_engine/card.kspay/bank_code.inc.php';
	include_once 'KSPayWebHost.inc.php';

    $rcid = $_POST["reCommConId"];
    $rctype = $_POST["reCommType"];
    $rhash = $_POST["reHash"];

	$ipg = new KSPayWebHost($rcid, null);
	$sendmsg = $ipg->send_msg('1');
	if($sendmsg) {
		$ono = $ipg->getValue('ordno');
	} else {
		$ono = $_POST['reOrderNumber'];
	}
	$ord = $pdo->assoc("select pay_type from $tbl[order] where ono='$ono'");

	ob_start();
	print_r($ipg);
	$log = ob_get_contents();
	ob_end_clean();

	makePGLog($ono, 'kspay mobilercv data', $log);

	if(function_exists('mb_http_input')) mb_http_input('euc-kr');
	if(function_exists('mb_http_output')) mb_http_output('euc-kr');

    $rcid = $_POST["reWHCid"];
    $rctype = $_POST["reWHCtype"];
    $rhash = $_POST["reWHHash"];

	switch($_POST['sndPaymethod']) {
		case '1000000000' : $pay_type = 1; break;
		case '0100000000' : $pay_type = 4; break;
		case '0010000000' : $pay_type = 5; break;
		case '0000010000' : $pay_type = 7; break;
	}

	$authyn = $trno = $trddt = $trdtm = $amt = $authno = $msg1 = $msg2 = $ordno = '';
	$isscd = $aqucd = $temp_v = $result = $halbu = $cbtrno = $cbauthno = $resultcd = '';

	if($sendmsg) {
		$authyn	 = $ipg->getValue("authyn");
		$trno	 = $ipg->getValue("trno"  );
		$trddt	 = $ipg->getValue("trddt" );
		$trdtm	 = $ipg->getValue("trdtm" );
		$amt	 = $ipg->getValue("amt"   );
		$authno	 = $ipg->getValue("authno");
		$msg1	 = $ipg->getValue("msg1"  );
		$msg2	 = $ipg->getValue("msg2"  );
		$ordno	 = $ipg->getValue("ordno" );
		$isscd	 = $ipg->getValue("isscd" );
		$aqucd	 = $ipg->getValue("aqucd" );
		$msg = trim($msg1.' '.$msg2);

		if(!empty($authyn) && 1 == strlen($authyn)) {
			if ($authyn == 'O') {
				$resultcd = "0000";
			} else {
				$resultcd = trim($authno);
			}
		}

		if($ord['pay_type'] == 4) {
			$stat = 1;
			$cq = ", `account`='$isscd', `bank_code`='$authno', `bankname`='$_bank_code[$authno]'";
			$card_tbl = $tbl['vbank'];
		} else {
			$stat = 2;
			$cq = ",`card_cd`='$isscd', `app_time`='$trddt$trdtm'";
			$card_tbl = $tbl['card'];
		}

		$_msg = addslashes($msg);
		$pdo->query("update `$card_tbl` set `stat`='$stat' $cq ,`res_cd`='$resultcd' ,`res_msg`='$_msg' ,`ordr_idxx`='$ono' ,`tno`='$trno' ,`good_mny`='$amt', `use_pay_method`='$pay_type' where `wm_ono`='$ono'");

		if($authyn == 'O') {
			include $engine_dir.'/_engine/order/order2.exe.php';
			return;
		} else {
			$pdo->query("update $tbl[order] set stat=31 where ono='$ono'");
			$pdo->query("update $tbl[order_product] set stat=31 where ono='$ono'");
			$pdo->query("update $card_tbl set stat='3', res_cd='$result', res_msg='$msg' where wm_ono='$ono'");

			makePGLog($ono, 'kspay failed');
			msg('결제요청이 실패되었습니다.('.$_msg.')', '/shop/order.php');
		}
	} else {
		$pdo->query("update $tbl[order] set stat=31 where ono='$ono'");
		$pdo->query("update $tbl[order_product] set stat=31 where ono='$ono'");
		$pdo->query("update $card_tbl set stat='3', res_cd='사용자 취소', res_msg='$msg' where wm_ono='$ono'");

		makePGLog($ono, 'kspay mobile failed, no data');

		msg('결제요청이 실패되었습니다.', '/shop/order.php');
	}

?>