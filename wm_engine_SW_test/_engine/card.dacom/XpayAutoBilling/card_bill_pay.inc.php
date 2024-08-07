<?PHP

	require_once $engine_dir.'/_engine/include/file.lib.php';

	function BillPay($data, $ono, $pay_prc) {
		global $cfg, $tbl, $engine_dir, $root_dir, $pdo;

		$CST_PLATFORM = ($cfg['card_test'] == 'N') ? 'service' : 'test';
		$CST_MID = $cfg['card_auto_dacom_id'];
		$LGD_MID = (($CST_PLATFORM == 'test') ? 't' : '').$CST_MID;
		$LGD_MERTKEY = $cfg['card_dacom_auto_key'];
		$LGD_OID = $ono;
		$LGD_AMOUNT = $pay_prc;
		$LGD_PAN = $data['billing_key'];
		$LGD_INSTALL = '00';
		$LGD_BUYERPHONE = str_replace("-", "", $data['buyer_cell']);
		$VBV_ECI = "010";

		//결제모듈 진행
		$configPath = $root_dir."/_data/Xpay";
		$log_dir    = $root_dir."/_data/Xpay/log";
		require_once __DIR__."/lgdacom/XPayClient.php";

		$xpay = new XPayClient($configPath, $CST_PLATFORM, $log_dir);
		$xpay->Init_TX($LGD_MID);
		$xpay->Set("LGD_TXNAME", "CardAuth");
		$xpay->Set("LGD_OID", $LGD_OID);
		$xpay->Set("LGD_AMOUNT", $LGD_AMOUNT);
		$xpay->Set("LGD_PAN", $LGD_PAN);
		$xpay->Set("LGD_INSTALL", $LGD_INSTALL);
		$xpay->Set("LGD_BUYERPHONE", $LGD_BUYERPHONE);
		$xpay->Set("LGD_BUYERIP", $_SERVER["REMOTE_ADDR"]);
		$xpay->Set("VBV_ECI", $VBV_ECI);
		$xpay->TX();

		$sno = $data['sbono'].$data['no'];
		$card = $pdo->assoc("select * from {$tbl['card']} where wm_ono='$sno' order by no limit 1");

		return array(
			'result' => ($xpay->Response_Code() == '0000') ? true : false,
			'tid' => $xpay->Response("LGD_TID",0),
			'card_cd' => $xpay->Response("LGD_FINANCECODE",0),
			'card_name' => $xpay->Response("LGD_FINANCENAME",0),
			'app_no' => $xpay->Response("LGD_FINANCEAUTHNUM",0),
			'rec_cd' => $xpay->Response_Code(),
			'res_msg' => $xpay->Response("LGD_RESPMSG",0),
			'quota' => $xpay->Response("LGD_INSTALL",0),
			'amount' => $xpay->Response("LGD_AMOUNT",0)
		);
	}

    function recurrentExpire($billkey)
    {
        // 기능 제공 안됨
    }

?>