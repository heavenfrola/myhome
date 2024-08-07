<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  U+ smartXpay Submit 방식 PG DB승인
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/shop.lib.php";
	include_once $engine_dir.'/_engine/include/wingPos.lib.php';

	printAjaxHeader();

	define('__pg_card_pay.exe__', ($ono) ? $ono : $_POST['LGD_OID']);
	makePGLog(($ono) ? $ono : $_POST['LGD_OID'], 'smartXpaySubmit Start');

	$cfg['card_dacom_key'] = $cfg['card_mobile_dacom_key'];
	$cfg['card_test'] = ($cfg['card_mobile_test'] != 'N') ? 'Y' : 'N';
	$cfg['card_dacom_id'] = $cfg['card_mobile_dacom_id'];
	$_POST['LGD_CUSTOM_USABLEPAY'] = $_POST['LGD_PAYTYPE'];

	include_once $engine_dir.'/_engine/card.dacom/XpayNon/card_pay.exe.php';
	exit;

	$CST_PLATFORM = ($cfg['card_mobile_test'] != 'N') ? 'test' : 'service';
	$CST_MID = $cfg['card_mobile_dacom_id'];
	$LGD_MID = ($cfg['card_mobile_test'] == 'Y' ? 't' : '').$CST_MID;
	$LGD_OID = $ono = $_POST['LGD_OID'];
	$LGD_RESPMSG = $_POST['LGD_RESPMSG'];
	$LGD_RESPCODE = $_POST['LGD_RESPCODE'];
	$LGD_AMOUNT = $_POST['LGD_AMOUNT'];
	$LGD_TIMESTAMP = $_POST['LGD_TIMESTAMP'];
	$LGD_HASHDATA = $_POST['LGD_HASHDATA'];

	$LGD_HASHDATA2 = md5($LGD_MID.$LGD_OID.$LGD_AMOUNT.$LGD_RESPCODE.$LGD_TIMESTAMP.$cfg['card_mobile_dacom_key']);
	if($LGD_HASHDATA2 != $LGD_HASHDATA) {
		exit('HASH 검증 오류');
	}

	// 모바일APP 접근시 동기식 사용
	if((strpos($_SERVER['HTTP_USER_AGENT'],'iPhone') > -1 && strpos($_SERVER['HTTP_USER_AGENT'],'Safari') === false) || strpos($_SERVER['HTTP_USER_AGENT'],'WISAAPP') > -1){

		include_once $engine_dir."/_engine/card.dacom/smartXpaySubmit/XPayClient.php";
		$configPath = $root_dir.'/_data/smartXpay';

		$xpay = new XPayClient($configPath, $CST_PLATFORM);
		$xpay->Init_TX($LGD_MID);

		$xpay->Set("LGD_TXNAME", "PaymentByKey");
		$xpay->Set("LGD_PAYKEY", $_POST['LGD_PAYKEY']);

		if ($xpay->TX()) {
			$_POST['LGD_RESPMSG'] = $LGD_RESPMSG = $xpay->Response("LGD_RESPMSG",0);
			$_POST['LGD_RESPCODE'] = $LGD_RESPCODE = $xpay->Response("LGD_RESPCODE",0);
			$_POST['LGD_OID'] = $LGD_OID = $xpay->Response("LGD_OID",0);
			$_POST['LGD_TID'] = $LGD_TID = $xpay->Response("LGD_TID",0);

			if(!$_POST['LGD_FINANCECODE']) $_POST['LGD_FINANCECODE'] = $LGD_FINANCECODE = $xpay->Response("LGD_FINANCECODE",0);
			if(!$_POST['LGD_FINANCENAME']) $_POST['LGD_FINANCENAME'] = $LGD_FINANCENAME = $xpay->Response("LGD_FINANCENAME",0);
			if(!$_POST['LGD_ACCOUNTNUM']) $_POST['LGD_ACCOUNTNUM'] = $LGD_ACCOUNTNUM = $xpay->Response("LGD_ACCOUNTNUM",0);
			if(!$_POST['LGD_CASTAMOUNT']) $_POST['LGD_CASTAMOUNT'] = $LGD_CASTAMOUNT = $xpay->Response("LGD_CASTAMOUNT",0);
			if(!$_POST['LGD_ESCROWYN']) $_POST['LGD_ESCROWYN'] = $LGD_ESCROWYN = $xpay->Response("LGD_ESCROWYN",0);

			if(!$_POST['LGD_CARDNUM']) $_POST['LGD_CARDNUM'] = $LGD_CARDNUM = $xpay->Response("LGD_CARDNUM",0);
			if(!$_POST['LGD_CARDNOINTYN']) $_POST['LGD_CARDNOINTYN'] = $LGD_CARDNOINTYN = $xpay->Response("LGD_CARDNOINTYN",0);
			if(!$_POST['LGD_CARDINSTALLMONTH']) $_POST['LGD_CARDINSTALLMONTH'] = $LGD_CARDINSTALLMONTH = $xpay->Response("LGD_CARDINSTALLMONTH",0);
			if(!$_POST['LGD_FINANCEAUTHNUM']) $_POST['LGD_FINANCEAUTHNUM'] = $LGD_FINANCEAUTHNUM = $xpay->Response("LGD_FINANCEAUTHNUM",0);

			if(!$_POST['LGD_PAYDATE']) $_POST['LGD_PAYDATE'] = $LGD_PAYDATE = $xpay->Response("LGD_PAYDATE",0);
			if(!$_POST['LGD_CASFLAG']) $_POST['LGD_CASFLAG'] = $LGD_CASFLAG = $xpay->Response("LGD_CASFLAG",0);
		} else {
			$pdo->query("update $tbl[order] set stat=31 where ono='$ono'");
			$pdo->query("update $tbl[order_product] set stat=31 where ono='$ono'");
			$pdo->query("update $card_tbl set stat='3', res_cd='$res_cd', res_msg='결제요청이 실패하였습니다.' where wm_ono='$ono'");

			msg('결제요청이 실패하였습니다.');
		}
	}

	// 결제처리
	$ord = $pdo->assoc("select pay_type from $tbl[order] where ono='$ono'");

	$card_tbl = ($ord['pay_type'] == 4) ? $tbl['vbank'] : $tbl['card'];
	$card = $pdo->assoc("select * from `$card_tbl` where `wm_ono`='$ono'"); // 카드, 가상계좌 결제 정보

	if(!$card['no']) exit('잘못된 주문정보입니다.');
	if($card['stat'] == '2' || $card['stat'] == '3') exit('OK');

	if($card['wm_price']!=$_POST['LGD_AMOUNT']) exit('결제 금액이 잘못되었습니다');

	$_SESSION['guest_no'] = $card['guest_no'];
	$member = ($card['member_no']) ? $pdo->assoc("select * from `$tbl[member]` where `no` = '$card[member_no]'") : "";


	// 공통정보
	$authdate = $_POST['LGD_PAYDATE']; // 결제승인일시
	$tid = $_POST['LGD_TID']; // 데이콤 TID
	$amount = $_POST['LGD_AMOUNT']; // 총 결제금액
	$buyer = addslashes($_POST['LGD_BUYER']); // 구매자명
	$buyeremail = $_POST['LGD_BUYEREMAIL']; // 구매자 메일
	$receiverphone = $_POST['LGD_RECEIVERPHONE']; // 수령자 전화번호
	$paytype = $_POST['LGD_PAYTYPE']; // 결제코드

	// 카드정보
	$cardname = mb_convert_encoding($_POST['LGD_FINANCENAME'], _BASE_CHARSET_, 'euckr'); // 카드명
	$cardnumber = $_POST['LGD_CARDNUM']; // 카드 번호
	$nointerestflag = $_POST['LGD_CARDNOINTYN']; // 무이자 여부 1 : 무이자, 2 : 일반
	$cardperiod = $_POST['LGD_CARDINSTALLMONTH']; // 할부개월
	$authnumber = $_POST['LGD_FINANCEAUTHNUM']; // 결제기관 승인번호

	// 가상계좌, 계좌이체 정보
	$bankcode = $_POST['LGD_FINANCECODE']; // 은행코드(매뉴얼표기명:결제기관코드)
	$bankname = mb_convert_encoding($_POST['LGD_FINANCENAME'], _BASE_CHARSET_, 'euckr'); // 은행명(매뉴얼표기명:결제기관명)
	$sa = $_POST['LGD_ACCOUNTNUM']; // 입금할 가상계좌
	$escrow_total = $_POST['LGD_CASTAMOUNT']; // 입금된 누적금액
	$escrow_camount = $_POST['LGD_CASTAMOUNT']; // 입금된 누적금액

	if($_POST['LGD_CASFLAG'] == "R") $stat = 1;
	else $stat = 2;

	if($useescrow == 'Y') {
		if($bankname && $bankcode) {
			$cq = ", `bankname`='$bankname', `account`='$sa', `bank_code`='$bankcode'";
		}
	} else {
		$cq = ",`card_cd`='$cardnumber',`card_name`='$cardname' ,`app_time`='$authdate' ,`app_no`='$authnumber' ,`noinf`='$nointerestflag' ,`quota`='$cardperiod'";
	}

	if($tid && $_POST['LGD_RESPCODE'] == "0000") { // 결제성공
		$pdo->query("update `$card_tbl` set `stat`='$stat' $cq ,`res_cd`='$res_cd' ,`res_msg`='$res_msg' ,`ordr_idxx`='$ono' ,`tno`='$tid' ,`good_mny`='$amount' ,`good_name`='$productinfo' ,`buyr_name`='$buyer' ,`buyr_mail`='$buyeremail' ,`buyr_tel1`='$receiverphone' , `use_pay_method`='$paytype' where `wm_ono`='$ono'");

		$card_pay_ok = true;

		makePGLog($ono, 'smartXpay success');
		include_once $engine_dir."/_engine/order/order2.exe.php";

		exit('OK');
	} else {
		$pdo->query("update `$card_tbl` set `res_cd`='$res_cd' ,`res_msg`='$res_msg' $cq ,`ordr_idxx`='$ono' ,`tno`='$tid' ,`good_mny`='$amount' ,`good_name`='$productinfo' ,`buyr_name`='$buyer' ,`buyr_mail`='$buyeremail' ,`buyr_tel1`='$receiverphone' , `use_pay_method`='$paytype' where `wm_ono`='$ono'");
		makePGLog($ono, 'smartXpay fail');

		if($dacom_note_url){
			exit('OK');
		}else{
			$_POST['reason'] = "사용자가 결제를 취소했거나 ".$LGD_RESPMSG;
			$_GET['LGD_OID'] = $ono;
			$mode = 'fail';
			makePGLog($ono, 'LG DACOM pay fail');

			include_once $engine_dir.'/_engine/order/pay_cancel.php';
		}
	}

	exit('OK');

?>