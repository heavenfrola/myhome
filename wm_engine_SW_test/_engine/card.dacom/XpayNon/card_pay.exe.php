<?php
/**
 * 데이콤 Xpay non active x
 * 결제 실행 페이지
 */

include_once $engine_dir."/_engine/include/common.lib.php";
include_once $engine_dir."/_engine/include/shop.lib.php";

define('__pg_card_pay.exe__', $_POST['LGD_OID']);
makePGLog($_POST['LGD_OID'], 'xpay Start');

$layer1 = 'order1';
$layer2 = 'order2';
$layer3 = 'order3';

$ono = addslashes($_POST['LGD_OID']);
if(!$ono) msg('필수값이 없습니다.');

if(preg_match("/SS/", $ono)) {
	$sbscr = 'Y';
	$sno = $ono;
	$tbl_order = $tbl['sbscr'];
	$tbl_order_product = $tbl['sbscr_product'];
	$order_where = " and sbono='$ono'";
	$order_select = ", sbono, s_pay_prc";
}else {
	$tbl_order = $tbl['order'];
	$tbl_order_product = $tbl['order_product'];
	$order_where = " and ono='$ono'";
	$order_select = ", ono, pay_prc";
}
$ord = $pdo->assoc("select pay_type, date1 $order_select from $tbl_order where 1 $order_where");
if(preg_match("/SS/", $ono)) {
	$ord['ono'] = $ord['sbono'];
	$ord['pay_prc'] = $ord['s_pay_prc'];
}
$ord['pay_prc'] = parsePrice($ord['pay_prc']);
$card_tbl = ($ord['pay_type'] == 4) ? $tbl['vbank'] : $tbl['card'];
$card = $pdo->assoc("select * from $card_tbl where wm_ono='$ord[ono]'");
if(!$card['no']) msg('결제데이터가 없습니다.');

if($card['stat'] == '2' || $card['stat'] == '3') {
	makePGLog($ono, 'xpay return');
	echo "OK";
	exit;
}

$CST_MID = $cfg['card_dacom_id'];
$LGD_MERTKEY = $cfg['card_dacom_key'];
$CST_PLATFORM = $cfg['card_test'] == 'N' ? 'service' : 'test';
$LGD_MID = $CST_PLATFORM == 'test' ? 't'.$CST_MID : $CST_MID;

if($_SESSION['browser_type']=="mobile") {
	$configPath = $root_dir.'/_data/smartXpay';
}else {
	$configPath = $root_dir.'/_data/XpayNon';
}

$chk_pay_method = true;
if(($ord['pay_type'] == 1 || $ord['pay_type'] == 21) && $_POST['LGD_CUSTOM_USABLEPAY'] != 'SC0010') $chk_pay_method = false;
if($ord['pay_type'] == 4 && $_POST['LGD_CUSTOM_USABLEPAY'] != 'SC0040') $chk_pay_method = false;
if($ord['pay_type'] == 5 && $_POST['LGD_CUSTOM_USABLEPAY'] != 'SC0030') $chk_pay_method = false;
if($ord['pay_type'] == 7 && $_POST['LGD_CUSTOM_USABLEPAY'] != 'SC0060') $chk_pay_method = false;
if($ord['pay_type'] == 21 && $_POST['LGD_CUSTOM_USABLEPAY'] != 'SC0010') $chk_pay_method = false;
if($chk_pay_method == false) {
	msg('결제방식이 일치하지 않습니다.', $root_url.'/shop/order.php');
}

require_once $engine_dir.'/_engine/card.dacom/XpayNon/lgdacom/XPayClient.php';
$xpay = new XPayClient($configPath, $CST_PLATFORM);
$xpay->Init_TX($LGD_MID);
$xpay->Set("LGD_TXNAME", "PaymentByKey");
$xpay->Set("LGD_PAYKEY", $_POST['LGD_PAYKEY']);
$xpay->Set("LGD_AMOUNTCHECKYN", "Y");
$xpay->Set("LGD_AMOUNT", $ord['pay_prc']);

if ($xpay->TX()) {
	$res_cd = $xpay->Response("LGD_RESPCODE",0);
	$res_msg = addslashes($xpay->Response("LGD_RESPMSG",0));
	$tid = $xpay->Response("LGD_TID",0);
	$amount = $xpay->Response("LGD_AMOUNT",0);
	$pay_method = $xpay->Response("LGD_PAYTYPE",0);
	$hashdata = $xpay->Response('LGD_HASHDATA',0);
	$hashdata2 = md5($LGD_MID.$ono.$ord['pay_prc'].$res_cd.$xpay->Response("LGD_TIMESTAMP",0).$LGD_MERTKEY);
} else {
	$pdo->query("update $tbl_order set stat=31 where 1 $order_where");
	$pdo->query("update $tbl_order_product set stat=31 where 1 $order_where");
	$pdo->query("update $card_tbl set stat='3', res_cd='$res_cd', res_msg='결제요청이 실패하였습니다.' where wm_ono='$ono'");

	if($_SESSION['browser_type'] != 'mobile') {
		msg('결제요청이 실패하였습니다.');
	}else {
		msg('결제요청이 실패하였습니다.', $root_url.'/shop/order.php');
	}
}
/*
 * [상점 결제결과처리(DB) 페이지]
 *
 * 1) 위변조 방지를 위한 hashdata값 검증은 반드시 적용하셔야 합니다.
 *
 */

/*
 * 상점 처리결과 리턴메세지
 *
 * OK  : 상점 처리결과 성공
 * 그외 : 상점 처리결과 실패
 *
 * ※ 주의사항 : 성공시 'OK' 문자이외의 다른문자열이 포함되면 실패처리 되오니 주의하시기 바랍니다.
 */
$resultMSG = "결제결과 상점 DB처리 결과값을 입력해 주시기 바랍니다.";

foreach($xpay->response_array["LGD_RESPONSE"][0] as $key => $val) {
	$val = mb_convert_encoding($val, _BASE_CHARSET_, 'utf8');
	$response_array .= "[$key] $val\n";
}

if($tid && $hashdata == $hashdata2) { //해쉬값 검증이 성공이면
	if ( "0000" == $res_cd ) { //결제가 성공이면
		$cardname = $xpay->Response('LGD_FINANCENAME',0);
		$cardnumber = $xpay->Response('LGD_CARDNUM',0);
		$nointerestflag = $xpay->Response('LGD_CARDNOINTYN',0);
		$cardperiod = $xpay->Response('LGD_CARDINSTALLMONTH',0);
		$authnumber = $xpay->Response('LGD_FINANCEAUTHNUM',0);
		$bankcode = $xpay->Response('LGD_FINANCECODE');
		$bankname = $xpay->Response('LGD_FINANCENAME');
		$sa = $xpay->Response('LGD_ACCOUNTNUM');

		if($ord['pay_type'] == 4) {
			$cq = ", `bankname`='$bankname', `account`='$sa', `bank_code`='$bankcode'";
		} else {
			$cq = ",`card_cd`='$cardnumber',`card_name`='$cardname' ,`app_time`='$authdate' ,`app_no`='$authnumber' ,`noinf`='$nointerestflag' ,`quota`='$cardperiod'";
		}

		$pdo->query("update `$card_tbl` set `stat`='2', `res_cd`='$res_cd', `res_msg`='$res_msg', `ordr_idxx`='$ono', `tno`='$tid', `good_mny`='$amount', `use_pay_method`='$pay_method' $cq where `wm_ono`='$ono'");
		$card_pay_ok = true;

		makePGLog($ono, 'xpay success', $response_array);
		include_once $engine_dir."/_engine/order/order2.exe.php";

		exit('OK');
	}else {
		$pdo->query("update $tbl_order set stat=31 where 1 $order_where");
		$pdo->query("update $tbl_order_product set stat=31 where 1 $order_where");
		$pdo->query("update $card_tbl set stat='3', res_cd='$res_cd', res_msg='$res_msg' where wm_ono='$ono'");

		makePGLog($ono, 'xpay fail', $response_array);


		if($_SESSION['browser_type'] != 'mobile') {
			msg('결제 승인 실패 : '.php2java($xpay->Response("LGD_RESPMSG",0)));
		}else {
			msg('결제 승인 실패 : '.php2java($xpay->Response("LGD_RESPMSG",0)), $root_url.'/shop/order.php');
		}

		exit;
	}
}
else { //해쉬값이 검증이 실패이면

	$xpay->Rollback('hashdata not mached!');

	$res_cd = $xpay->Response_Code();
	$res_msg = addslashes($xpay->Response_Msg());

	$pdo->query("update $tbl_order set stat=31 where 1 $order_where");
	$pdo->query("update $tbl_order_product set stat=31 where 1 $order_where");
	$pdo->query("update $card_tbl set stat='3', res_cd='$res_cd', res_msg='$res_msg' where wm_ono='$ono'");
	$pdo->query("insert into `$tbl[card_cc_log]` (`cno`, `stat`, `ono`, `price`, `tno`, `res_cd`, `res_msg`, `admin_id`, `admin_no`, `ip`, `reg_date`) values('$card[no]', '2', '$card[wm_ono]', '$amount', '$tid', '$res_cd', '해쉬불일치 : $res_msg', '시스템', '0', '$_SERVER[REMOTE_ADDR]', '$now')");
	makePGLog($ono, "xpay fail", $response_array);

	if($_SESSION['browser_type'] != 'mobile') {
		msg('결제 승인 실패 : 승인데이터 불일치');
	}else {
		msg('결제 승인 실패 : 승인데이터 불일치', $root_url.'/shop/order.php');
	}

	/*
	 * hashdata검증 실패 로그를 처리하시기 바랍니다.
	 */
}

?>