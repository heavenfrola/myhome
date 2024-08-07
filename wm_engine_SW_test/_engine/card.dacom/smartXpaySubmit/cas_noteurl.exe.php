<?PHP

    $urlfix = 'Y';
	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/shop.lib.php";
	include_once $engine_dir.'/_engine/include/wingPos.lib.php';

	$ono = $_POST['LGD_OID'];

	define('__pg_card_pay.exe__', $ono);
	makePGLog($ono, 'smartXpay cas_noteurl');

	$CST_PLATFORM = ($cfg['card_mobile_test'] != "N") ? "test" : "service";
	$CST_MID = $cfg['card_mobile_dacom_id'];
	$LGD_MID = (("test" == $CST_PLATFORM)?"t":"").$CST_MID;
	$LGD_OID = $_POST['LGD_OID'];
	$LGD_AMOUNT = $_POST['LGD_AMOUNT'];
	$LGD_RESPCODE = $_POST['LGD_RESPCODE'];
	$LGD_TIMESTAMP = $_POST['LGD_TIMESTAMP'];
	$LGD_CASFLAG = $_POST['LGD_CASFLAG'];
	$LGD_HASHDATA = $_POST['LGD_HASHDATA'];

	$ono = $_POST['LGD_OID'];
	$dacom_note_url = true;

	$LGD_HASHDATA2 = md5($LGD_MID.$LGD_OID.$LGD_AMOUNT.$LGD_RESPCODE.$LGD_TIMESTAMP.$cfg['card_mobile_dacom_key']);

	if($LGD_HASHDATA != $LGD_HASHDATA2) {
		exit('hash 오류');
	}

	switch($LGD_CASFLAG) {
		case 'R' :
			$bankname = mb_convert_encoding($_POST['LGD_FINANCENAME'], _BASE_CHARSET_, 'euckr');
			$bankcode = $_POST['LGD_FINANCECODE'];
			$sa = $_POST['LGD_ACCOUNTNUM'];
			if($bankname && $sa) {

				// 현재 주문서 상태가 1 또는 11이 아닌경우 처리 안함
				$ord = $pdo->assoc("select * from `$tbl[order]` where `ono`='$ono'");
				if($ord['stat'] != 1 && $ord['stat'] != 11){
					makePGLog($ono, "escrow R - stat number is not 1 and 11!!! now stat is $ord[stat]");
					exit('OK');
				}

				$pdo->query("update $tbl[vbank] set `bankname`='$bankname', `account`='$sa', `bank_code`='$bankcode' where wm_ono='$ono'");;
			}
			makePGLog($ono, 'escrow R');
		break;
		case 'C' :
			$card = $pdo->assoc("select * from `$tbl[vbank]` where `wm_ono`='$ono'");
			if(!$card['no']) exit('잘못된 주문 정보입니다');

			$pdo->query("update `$tbl[order]` set `stat`='13' where `ono`='$ono'");
			$pdo->query("update `$tbl[order_product]` set `stat`='13' where `ono`='$ono'");
			ordStatLogw($ono, 13, 'Y');

			makePGLog($ono, 'escrow cancel');
		break;
		case 'I' :
			$card = $pdo->assoc("select * from `$tbl[vbank]` where `wm_ono`='$ono'");
			if(!$card['no']) exit("잘못된 주문 정보입니다");

			// 현재 주문서 상태가 1 또는 11이 아닌경우 처리 안함
			$ord = $pdo->assoc("select * from `$tbl[order]` where `ono`='$ono'");
			if($ord['stat'] != 1 && $ord['stat'] != 11){
				makePGLog($ono, "smartXpay escrow input - stat number is not 1 and 11!!! now stat is $ord[stat]");
				exit('OK');
			}

			if($_POST['LGD_CASTAMOUNT'] < $card['good_mny']) {
				 // 일부 미입금
			} else {
				$pdo->query("update `$tbl[vbank]` set `stat`='2' where `wm_ono`='$ono'");
				$pdo->query("update `$tbl[order]` set `stat`='2', `stat2`='@2@', `date2`='$now' where `ono`='$ono'");
				$pdo->query("update `$tbl[order_product]` set `stat`='2' where `ono`='$ono'");

				ordStatLogw($ono, 2, 'Y');
				orderStock($ono, 11, 2);

				include_once $engine_dir.'/_engine/sms/sms_module.php';
				$sms_replace['buyer_name'] = $ord['buyer_name'];
				$sms_replace['ono'] = $ord['ono'];
				$sms_replace['pay_prc'] = number_format($ord['pay_prc']);
				SMS_send_case(3, $ord['buyer_cell']);
				SMS_send_case(18);

				if($cfg['partner_sms_config'] == 1 || $cfg['partner_sms_config'] == 2) {
					partnerSmsSend($ord['ono'], 18);
				}
			}
			makePGLog($ono, 'smartXpay escrow input');
		break;
	}

	exit("OK");

?>