<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' | 카카오페이 결제정보 전송
	' +----------------------------------------------------------------------------------------------+*/

	if(!defined("_wisa_lib_included")) exit;
	include_once "incKakaopayCommon.php";
	include_once "lgcns_CNSpay.php";
	include_once $engine_dir."/_engine/include/common.lib.php";

	if(empty($MID)) msg('카카오페이 설정이 잘못되었습니다 - 관리자게에 문의하세요.');
	
	$card_tbl=$tbl[card]; 

	checkAgent();
	$os = trim("$os_name $os_version");
	$browser = trim("$br_name $br_version");
	$env_info = "$os || $browser || ".$_SERVER['REMOTE_ADDR'];

	cardDataInsert($card_tbl, 'kakaopay');

	if($_SESSION['browser_type']=='pc') {
		$prType = "WPM";
	}else {
		$prType = "MPM";
	}
	
	$pay_prc = parsePrice($pay_prc, false);

	$good_name=strip_tags(preg_replace("/(\"|'|&)/", " ", $title));

	$ediDate = date("YmdHis");  // 전문생성일시

	////////위변조 처리/////////
	//결제요청용 키값
	$cnspay_lib = new CnsPayWebConnector($LogDir);
	$md_src = $ediDate.$MID.$pay_prc;
	$salt = hash("sha256",$merchantKey.$md_src,false);
	$hash_input = $cnspay_lib->makeHashInputString($salt);
	$hash_calc = hash("sha256", $hash_input, false);
	$hash_String = base64_encode($hash_calc);

	//기본값
	$remoteaddr = $_SERVER['REMOTE_ADDR'];
	$serveraddr = $_SERVER['SERVER_ADDR'];
	$requestDealApproveUrl = $targetUrl.$msgName;
	//$merchantTxnNum = "13".date("YmdHis",time());
	$merchantTxnNum = $ono;

?>
<script type="text/javascript">
	var tf = parent.document.getElementById('payForm');
	tf.PayMethod.value = 'KAKAOPAY';
	tf.TransType.value = '0';
	tf.GoodsName.value = '<?=$good_name?>';
	tf.Amt.value = '<?=$pay_prc?>';
	tf.GoodsCnt.value = '<?=$buy_ea?>';
	tf.MID.value = '<?=$cfg[kakao_id]?>';
	tf.CERTIFIED_FLAG.value = 'CN';
	tf.AuthFlg.value = '10';
	tf.currency.value = 'KRW';
	tf.merchantEncKey.value = '<?=$cfg[kakao_enc_key]?>';
	tf.merchantHashKey.value = '<?=$cfg[kakao_hash_key]?>';
	tf.requestDealApproveUrl.value = '<?=$requestDealApproveUrl?>';
	tf.prType.value = '<?=$prType?>';
	tf.channelType.value = '4';
	tf.merchantTxnNum.value = '<?=$merchantTxnNum?>';
	tf.BuyerName.value = '<?=$buyer_name?>';
	tf.EdiDate.value = '<?=$ediDate?>';
	tf.EncryptData.value = '<?=$hash_String?>';

	parent.getTxnId();
</script>