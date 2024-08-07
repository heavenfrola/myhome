<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  INIweb 결제 데이터 전송
	' +----------------------------------------------------------------------------------------------+*/

	if(!defined("_wisa_lib_included")) exit();

	checkAgent();
	$os = trim("$os_name $os_version");
	$browser = trim("$br_name $br_version");
	$env_info = "$os || $browser || ".$_SERVER['REMOTE_ADDR'];

	$card_tbl=($pay_type == 4) ? $tbl['vbank'] : $tbl['card'];
	cardDataInsert($card_tbl, 'inicis');

	$prd_nm = cutStr(inputText(strip_tags(addslashes($title))),90);

	if(empty($cfg['card_quotaopt'])) $cfg['card_quotaopt']="3";
	$qstr = '0';
	for($ii=2; $ii<=$cfg[card_quotaopt]; $ii++) {
		$qstr .= (strlen($qstr) > 0) ? ":":"";
		$qstr .= $ii;
	}

	if(!$buyer_phone) $buyer_phone = $buyer_cell;

	switch ($pay_type) {
		case "1" : $gopaymethod = "Card"; break;
		case "5" : $gopaymethod = "DirectBank"; break;
		case "4" : $gopaymethod = "Vbank"; break;
		case "7" : $gopaymethod = "HPP"; break;
		default  : $gopaymethod="";
	}

	$cfg['card_mall_id'] = ($cfg['card_test'] == 'Y') ? 'INIpayTest' : $cfg['card_web_id'];
	$acceptmethod = "HPP(2):va_receipt:";
	if($pay_type == 4){
		$cfg['card_mall_id'] = ($cfg['escrow_web_id']) ? $cfg['escrow_web_id'] : $cfg['card_web_id'];
		$acceptmethod = "HPP(2):useescrow:va_receipt:";
	}

	$mid = $cfg['card_mall_id'];

	require_once($engine_dir."/_engine/card.inicis/INIweb/libs/INIStdPayUtil.php");

	$SignatureUtil = new INIStdPayUtil();

	//인증
	if($pay_type == 4){
		$signKey = ($cfg['escrow_web_key']) ? $cfg['escrow_web_key'] : $cfg['card_web_key']; // 가맹점에 제공된 웹 표준 사인키(가맹점 수정후 고정)
	}else {
		$signKey = $cfg['card_web_key']; // 가맹점에 제공된 웹 표준 사인키(가맹점 수정후 고정)
	}
	if(empty($cfg['inicis_GID']) == false) $signKey = 'wisa1234';
	$timestamp = $SignatureUtil->getTimestamp();   // util에 의해서 자동생성

	//###################################
	// 2. 가맹점 확인을 위한 signKey를 해시값으로 변경 (SHA-256방식 사용)
	//###################################
	$mKey = $SignatureUtil->makeHash($signKey, "sha256");

	$params = array(
		"oid" => $ono,
		"price" => $pay_prc,
		"timestamp" => $timestamp
	);
	$sign = $SignatureUtil->makeSignature($params, "sha256");

?>
<script type="text/javascript">

	var tf=parent.document.inipay;

	tf.version.value='1.0';
	tf.mid.value='<?=$cfg[card_mall_id]?>';
	tf.oid.value='<?=$ono?>';
	tf.goodname.value='<?=$prd_nm?>';
	tf.price.value='<?=$pay_prc?>';
	tf.buyername.value='<?=$buyer_name?>';
	tf.buyeremail.value='<?=$buyer_email?>';
	tf.buyertel.value='<?=$buyer_phone?>';
	tf.timestamp.value='<?=$timestamp?>';
	tf.signature.value='<?=$sign?>';
	tf.mKey.value='<?=$mKey?>';
	tf.acceptmethod.value='<?=$acceptmethod?>';
	tf.currency.value='WON';
	tf.quotabase.value='<?=$qstr?>';
	tf.gopaymethod.value='<?=$gopaymethod?>';
	tf.tax.value='<?=parsePrice(floor(($pay_prc-$taxfree_amount)/11))?>';
	tf.taxfree.value='<?=parsePrice($taxfree_amount)?>';

	parent.payaction();
</script>