<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  U+ xpayLite DB처리
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/shop.lib.php";

	if($_SESSION['browser_type'] != 'mobile') {
		$LGD_OID         = addslashes(trim($_POST['LGD_OID']));
	} else {
		$LGD_OID         = $_SESSION['MOBILE_OID'];
		$_SESSION['MOBILE_OID'] = '';
	}
	define('__pg_card_pay.exe__', $LGD_OID);
	makePGLog($LGD_OID, 'xpay billkey start');

	//파라미터 확인
	$LGD_RESPCODE    = addslashes(trim($_POST['LGD_RESPCODE']));
	$LGD_RESPMSG     = addslashes(trim($_POST['LGD_RESPMSG']));
	$LGD_BILLKEY     = addslashes(trim($_POST['LGD_BILLKEY']));
	$LGD_PAYDATE     = addslashes(trim($_POST['LGD_PAYDATE']));
	$LGD_FINANCECODE = addslashes(trim($_POST['LGD_FINANCECODE']));
	$LGD_FINANCENAME = addslashes(trim($_POST['LGD_FINANCENAME']));

	//필수값 체크
	$sno = $LGD_OID;
	if(!$sno) msg('필수값이 없습니다.','back');

	$old_billing_key = $pdo->row("select billing_key from $tbl[sbscr] where sbono='$sno'");

	//성공
	if("0000" == $LGD_RESPCODE) {
		$pdo->query("UPDATE $tbl[sbscr] SET billing_key='$LGD_BILLKEY' WHERE sbono='$sno'");

		makePGLog($sno, 'xpay billkey success');
		if($old_billing_key) {
			msg('카드수정이 완료되었습니다.','back');
			exit;
		}
		$sbscr='Y';
		include_once $engine_dir."/_engine/order/order2.exe.php";

	//실패
	} else {
		$pdo->query("update $tbl[sbscr] set stat=31 where sbono='$sno'");
		$pdo->query("update $tbl[sbscr_product] set stat=31 where sbono='$sno'");
		$pdo->query("update $tbl[sbscr_schedule_product] set stat=31 where sbono='$sno'");

		makePGLog($sno, 'xpay billkey fail');
        $layer1 = 'order1';
        $layer2 = 'order2';
        $layer3 = 'order3';
		msg('카드 결제에 실패하였습니다.','back');
	}

?>