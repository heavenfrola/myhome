<?PHP

	if(!defined("_wisa_lib_included")) exit;

	$CST_PLATFORM = ($cfg['card_mobile_test'] != 'N') ? 'test' : 'service';
	$CST_MID = $cfg['card_mobile_dacom_id'];
	$LGD_MID = (("test" == $CST_PLATFORM)?"t":"").$CST_MID;
	$LGD_OID = $ono;
	$LGD_AMOUNT = $pay_prc;
	$LGD_BUYER = $buyer_name;
	$LGD_BUYERID = $member['member_id'];
	$LGD_PRODUCTINFO = $title;
	$LGD_BUYEREMAIL = $buyer_email;
	$LGD_TIMESTAMP = date('YmdHis');
	$LGD_CUSTOM_SKIN = "SMART_XPAY2";

	// 결제방식
	switch ($pay_type) {
		case "1" : $LGD_CUSTOM_FIRSTPAY = "SC0010"; break; // 카드결제
		case "4" : $LGD_CUSTOM_FIRSTPAY = "SC0040"; break; // 가상계좌 에스크로
		case "5" : $LGD_CUSTOM_FIRSTPAY = "SC0030"; break; // 실시간 계좌이체
		case "7" : $LGD_CUSTOM_FIRSTPAY = "SC0060"; break; // 휴대폰 결제
	}

	$configPath = $root_dir.'/_data/smartXpay';

	// 가상계좌 통보 URL
	$LGD_CASNOTEURL = $root_url.'/main/exec.php?exec_file=card.dacom/XpayNon/cas_noteurl.php';

	// 응답 수신 페이지 URL
	$LGD_RETURNURL = $root_url.'/main/exec.php?exec_file=card.dacom/smartXpaySubmit/card_pay.exe.php';

	if(!$CST_PLATFORM || !$cfg['card_mobile_dacom_key']) {
		msg("카드 설정이 잘못되었습니다 - 관리자에게 문의하세요");
	}

	$card_tbl = ($pay_type == 4) ? $tbl['vbank'] : $tbl['card'];

	addField($card_tbl,"env_info","VARCHAR(50) NOT NULL");

	checkAgent();
	$os = trim("$os_name $os_version");
	$browser = trim("$br_name $br_version");
	$env_info = "$os || $browser || ".$_SERVER['REMOTE_ADDR'];

    // 할부개월
    $installrange = '';
    for($i = 0; $i <= $cfg['card_mobile_quotaopt']; $i++) {
        if($i == 1) continue;
        if($installrange != '') $installrange .= ':';
        $installrange .= $i;
    }

	cardDataInsert($card_tbl, 'dacom');

	require_once $engine_dir.'/_engine/card.dacom/smartXpaySubmit/XPayClient.php';
	$xpay = new XPayClient($configPath, $LGD_PLATFORM);
   	$xpay->Init_TX($LGD_MID);
	$LGD_HASHDATA = md5($LGD_MID.$LGD_OID.$LGD_AMOUNT.$LGD_TIMESTAMP.$xpay->config[$LGD_MID]);
	$LGD_CUSTOM_PROCESSTYPE = "TWOTR";

	$CST_WINDOW_TYPE = "submit";
	$payReqMap['CST_PLATFORM'] = $CST_PLATFORM;
	$payReqMap['CST_WINDOW_TYPE'] = $CST_WINDOW_TYPE;
	$payReqMap['CST_MID'] = $CST_MID;
	$payReqMap['LGD_MID'] = $LGD_MID;
	$payReqMap['LGD_OID'] = $LGD_OID;
	$payReqMap['LGD_BUYER'] = $LGD_BUYER;
	$payReqMap['LGD_BUYERID'] = $LGD_BUYERID;
	$payReqMap['LGD_PRODUCTINFO'] = strip_tags($title);
	$payReqMap['LGD_AMOUNT'] = $LGD_AMOUNT;
	$payReqMap['LGD_BUYEREMAIL'] = $LGD_BUYEREMAIL;
	$payReqMap['LGD_CUSTOM_SKIN'] = $LGD_CUSTOM_SKIN;
	$payReqMap['LGD_CUSTOM_PROCESSTYPE'] = $LGD_CUSTOM_PROCESSTYPE;
	$payReqMap['LGD_TIMESTAMP'] = $LGD_TIMESTAMP;
	$payReqMap['LGD_HASHDATA'] = $LGD_HASHDATA;
	$payReqMap['LGD_RETURNURL'] = $LGD_RETURNURL;
	$payReqMap['LGD_VERSION'] = "PHP_Non-ActiveX_SmartXPay";
	$payReqMap['LGD_CUSTOM_FIRSTPAY'] = $LGD_CUSTOM_FIRSTPAY;
	$payReqMap['LGD_CUSTOM_SWITCHINGTYPE']  = "SUBMIT";
	$payReqMap['LGD_ENCODING'] = 'UTF-8';
    $payReqMap['LGD_INSTALLRANGE'] = $installrange;

	if($cfg['banking_time'] > 0) {
		$payReqMap['LGD_CLOSEDATE'] = date('Ymd235959', strtotime("+$cfg[banking_time] days"));
	}

	//신용카드 ISP(국민/BC)결제에만 적용 - BEGIN
	$payReqMap['LGD_KVPMISPWAPURL'] = $LGD_KVPMISPWAPURL;
	$payReqMap['LGD_KVPMISPCANCELURL'] = $LGD_KVPMISPCANCELURL;
	//계좌이체 결제에만 적용 - BEGIN
	$payReqMap['LGD_MTRANSFERWAPURL'] = '';
	$payReqMap['LGD_MTRANSFERCANCELURL'] = '';

	$payReqMap['LGD_KVPMISPAUTOAPPYN'] = "Y";
	$payReqMap['LGD_MTRANSFERAUTOAPPYN'] = "Y";

	// 모바일APP 접근시 동기식 사용
	if(strpos($_SERVER['HTTP_USER_AGENT'],'iPhone') > -1) {
		$payReqMap['LGD_KVPMISPAUTOAPPYN'] = "N";
		$payReqMap['LGD_MTRANSFERAUTOAPPYN'] = "N";
	}else {
		$payReqMap['LGD_KVPMISPAUTOAPPYN'] = "A";
		$payReqMap['LGD_MTRANSFERAUTOAPPYN'] = "A";
	}

	$payReqMap['LGD_CASNOTEURL'] = $LGD_CASNOTEURL;
	$payReqMap['LGD_TAXFREEAMOUNT'] = $taxfree_amount;

	$payReqMap['LGD_RESPCODE'] = "";
	$payReqMap['LGD_RESPMSG'] = "";
	$payReqMap['LGD_PAYKEY'] = "";

	if($use_paynow == 'true') {
		$payReqMap['LGD_EASYPAY_ONLY'] = "PAYNOW";
	}

	$_SESSION['PAYREQ_MAP'] = $payReqMap;

?>
<script type="text/javascript">
parent.$('#LGD_PAYINFO').html('');
<?foreach ($payReqMap as $key => $value) {?>
parent.$('#LGD_PAYINFO').append("<input type='hidden' name='<?=$key?>' id='<?=$key?>' value='<?=$value?>'>");
<?}?>
parent.launchCrossPlatform();
</script>