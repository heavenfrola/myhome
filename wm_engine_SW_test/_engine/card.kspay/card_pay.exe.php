<?PHP

	include_once $engine_dir.'/_engine/include/common.lib.php';
	include_once $engine_dir.'/_engine/include/file.lib.php';
	include_once $engine_dir.'/_engine/card.kspay/bank_code.inc.php';
	include_once 'KSPayWebHost.inc.php';

	$ono = $_POST['sndOrdernumber'];
	$ord = $pdo->assoc("select pay_type from $tbl[order] where ono='$ono'");

	makePGLog($ono, 'kspay start');

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

	$card_tbl = $pay_type == 4 ? $tbl['vbank'] : $tbl['card'];

	$ipg = new KSPayWebHost($rcid, null);
	makePGLog($ono, 'kspay rcv complete');

	ob_start();
	print_r($ipg);
	$log = ob_get_contents();
	ob_end_clean();

	makePGLog($ono, 'kspay rcv data', $log);

	$authyn = $trno = $trddt = $trdtm = $amt = $authno = $msg1 = $msg2 = $ordno = '';
	$isscd = $aqucd = $temp_v = $result = $halbu = $cbtrno = $cbauthno = $resultcd = '';

	if($ipg->kspay_send_msg('1')) {
		$authyn = $ipg->kspay_get_value('authyn');
		$trno = $ipg->kspay_get_value('trno');
		$trddt = $ipg->kspay_get_value('trddt');
		$trdtm = $ipg->kspay_get_value('trdtm');
		$amt = $ipg->kspay_get_value('amt' );
		$authno = $ipg->kspay_get_value('authno');
		$msg1 = $ipg->kspay_get_value('msg1'  );
		$msg2 = $ipg->kspay_get_value('msg2'  );
		$ordno = $ipg->kspay_get_value('ordno' );
		$isscd = $ipg->kspay_get_value('isscd' );
		$aqucd = $ipg->kspay_get_value('aqucd' );
		$temp_v = '';
		$result = $ipg->kspay_get_value('result');
		$halbu = $ipg->kspay_get_value('halbu');
		$cbtrno = $ipg->kspay_get_value('cbtrno');
		$cbauthno = $ipg->kspay_get_value('cbauthno');
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
		$pdo->query("update $card_tbl set stat='3', res_cd='$result', res_msg='$msg' where wm_ono='$ono'");

		makePGLog($ono, 'kspay failed, no data');

		msg('결제요청이 실패되었습니다.');
	}

?>