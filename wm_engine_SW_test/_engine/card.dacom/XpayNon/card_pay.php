<?php
/**
 * 데이콤 Xpay non active x
 */

if(!defined("_wisa_set_included")) exit();

function lgdFilter($str) {
	$str = preg_replace('/<|>|\+-|--|;|\'|\"|\\\/', '', $str);
	return $str;
}

$CST_MID = $cfg['card_dacom_id'];
$LGD_MERTKEY = $cfg['card_dacom_key'];
$CST_PLATFORM = $cfg['card_test'] == 'N' ? 'service' : 'test';
$LGD_MID = $CST_PLATFORM == 'test' ? 't'.$CST_MID : $CST_MID;
if(!$CST_MID || !$LGD_MERTKEY) {
	msg("카드 설정이 잘못되었습니다 - 관리자에게 문의하세요.");
}

// 결제방식
$version = "PHP_XPay_2.5";
switch ($pay_type) {
	case "1" : // 카드결제
		$pay_method = "SC0010";
		$use_escrow = 'N';
	break;
	case "4" : // 가상계좌 에스크로
		$pay_method = "SC0040";
		$use_escrow = 'Y';
	break;
	case "5" : // 실시간 계좌이체
		$pay_method = "SC0030";
		$use_escrow = 'N';
	break;
	case "7" : // 휴대폰 결제
		$pay_method = "SC0060";
		$use_escrow = 'N';
	break;
	case "21" : //페이나우(카드)
		$pay_method = "SC0010";
		$use_escrow = 'N';
		$version = "PHP_Non-ActiveX_Paynow";
	break;
}

$card_tbl=($pay_type == 4) ? $tbl[vbank] : $tbl[card];

checkAgent();
$os = trim("$os_name $os_version");
$browser = trim("$br_name $br_version");
$env_info = "$os || $browser || ".$_SERVER['REMOTE_ADDR'];
cardDataInsert($card_tbl, "dacom");

$buyer_name = lgdFilter($buyer_name);
$good_name = lgdFilter($title);
$timestamp = date('YmdHis', $now);

require_once $engine_dir.'/_engine/card.dacom/XpayNon/lgdacom/XPayClient.php';
$xpay = new XPayClient($root_dir.'/_data/XpayNon', $CST_PLATFORM);
$xpay->Init_TX($LGD_MID);

$hashdata = md5($LGD_MID.$ono.$pay_prc.$timestamp.$LGD_MERTKEY);

// 할부개월
$installrange = '';
for($i = 0; $i <= $cfg['card_quotaopt']; $i++) {
	if($i == 1) continue;
	if($installrange != '') $installrange .= ':';
	$installrange .= $i;
}

// 추가개발용 옵션
$nointinf = ''; // 특정카드 무이자할부 세팅
$cashreceipt_yn = 'Y'; // 계좌이체, 가상계좌 현금영수증 사용여부

?>
<script type="text/javascript">
	var f = parent.document.getElementById('LGD_PAYINFO');

	f.LGD_MID.value = '<?=$LGD_MID?>';
	f.LGD_OID.value = '<?=$ono?>';
	f.LGD_BUYER.value = '<?=$buyer_name?>';
	f.LGD_PRODUCTINFO.value = '<?=$good_name?>';
	f.LGD_AMOUNT.value = '<?=$pay_prc?>';
	f.LGD_TIMESTAMP.value = '<?=$timestamp?>';
	f.LGD_HASHDATA.value = '<?=$hashdata?>';
	f.LGD_BUYERID.value = '<?=$member[member_id]?>';
	f.LGD_BUYERIP.value = '<?=$_SERVER[REMOTE_ADDR]?>';
	f.LGD_CUSTOM_USABLEPAY.value = '<?=$pay_method?>';
	f.LGD_ESCROW_USEYN.value = '<?=$use_escrow?>';
	f.LGD_CUSTOM_SKIN.value = 'red';
	f.LGD_INSTALLRANGE.value = '<?=$installrange?>';
	f.LGD_TAXFREEAMOUNT.value = '<?=$taxfree_amount?>';
	f.LGD_NOINTINF.value = '<?=$nointinf?>';
	f.LGD_CASHRECEIPTYN.value = '<?=$cashreceipt_yn?>';
	f.LGD_CUSTOM_FIRSTPAY.value = '';
	f.LGD_CASNOTEURL.value = '<?=$root_url?>/main/exec.php?exec_file=card.dacom/XpayNon/cas_noteurl.php';
	f.LGD_RETURNURL.value = '<?=$root_url?>/main/exec.php?exec_file=card.dacom/XpayNon/card_return_url.php';
	f.LGD_CUSTOM_PROCESSTYPE.value = 'TWOTR';
	f.LGD_ENCODING.value = 'UTF-8';
	f.LGD_ENCODING_NOTEURL.value = 'UTF-8';
	f.LGD_ENCODING_RETURNURL.value = 'UTF-8';
	f.LGD_VERSION.value = '<?=$version?>';
	<?if($use_paynow == true) {?>
	f.LGD_EASYPAY_ONLY.value = 'PAYNOW';
	f.LGD_ONEPAY_VIEW_VERSION.value = '02';
	<?}else {?>
	f.LGD_EASYPAY_ONLY.value = '';
	f.LGD_ONEPAY_VIEW_VERSION.value = '';
	<?}?>
	f.action = '/main/exec.php?exec_file=card.dacom/XpayNon/card_pay.exe.php';

	parent.doPay_ActiveX();

</script>