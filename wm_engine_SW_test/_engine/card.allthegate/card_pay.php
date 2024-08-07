<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  올더게이트 결제데이터 전송
	' +----------------------------------------------------------------------------------------------+*/

	$card_tbl		= $pay_type == 4 ? $tbl['vbank'] : $tbl['card'];
	cardDataInsert($card_tbl, 'allthegate');

	$company_name		= alltheValue($cfg['company_name']);
	$title				= alltheValue($title, 100);
	$buyer_name			= alltheValue($buyer_name, 20);
	$addr				= alltheValue($addressee_addr1.' '.$addressee_addr2, 100);
	$phone				= alltheValue($buyer_phone, 21);
	$addressee_name		= $addressee_name ? alltheValue($addressee_name, 20) : $buyer_name;
	$addressee_phone	= $addressee_phone ? alltheValue($addressee_phone, 21) : $phone;
	$addressee_addr		= $addressee_addr1 ?  alltheValue($addressee_addr1.' '.$addressee_addr2, 100) : $addr;
	$UserId				= substr($member['member_id'], 0, 20);

	if(!$addr) $addr = '배송지 없음';
	if(!$addressee_addr) $addressee_addr = '배송지 없음';

	$mall_page = "/main/exec.php?exec_file=card.allthegate/card_pay.exe.php";
	switch($pay_type) {
		case '1' : $pay_method = 'onlycardselfnormal'; break;
		case '5' : $pay_method = 'onlyicheselfnormal'; break;
		case '4' :
			$pay_method = 'onlyvirtualselfescrow';
			$mall_page = "/main/exec.php?exec_file=card.allthegate/vbank.exe.php";
		break;
		case '7' : $pay_method = 'onlyhp'; break;
	}

	$QuotaInf = array(0);
	for($i = 1; $i <= $cfg['card_quotaopt']; $i++) {
		$QuotaInf[] = $i;
	}
	$QuotaInf = implode(':', $QuotaInf);
	if($pay_type == '4') $remark = 'escrow';
	if(!$member['member_id']) $member['member_id'] = '비회원';

	function alltheValue($str, $length = 0) {
		$str = strip_tags(trim($str));
		$str = str_replace('|', '', $str);
		$str = str_replace('"', '', $str);
		if($length > 0) $str = cutstr($str, $length, '');
		$str = addslashes($str);

		return $str;
	}

	// 세션검증
	require ($engine_dir.'/_engine/card.allthegate/AGSLib41.php');

	$StoreId = trim($cfg['allthegate_StoreId']);
	$OrdNo = $ono;
	$amt = parsePrice($pay_prc);

	$agsData = new agspay40;
	$paramNames = array("StoreId", "OrdNo", "Amt");
	$paramValues = array($StoreId, $OrdNo, $amt);

	$agsData->SetValue("store_propPath", $engine_dir."/_engine/card.allthegate/store_config.ini");
	$agsData->checkConfig($paramNames, $paramValues);
	$propList = array();
	$propList = $agsData->checkConfig($paramNames, $paramValues);

	header('Content-type: text/html; charset=euc-kr');
?>
<script type="text/javascript">
var f = parent.document.getElementById('frmAGS_pay');
if(f) {

	f.StoreId.value = "<?=trim($cfg['allthegate_StoreId'])?>";
	f.StoreNm.value = "<?=mb_convert_encoding($company_name, 'euckr', _BASE_CHARSET_)?>";
	f.OrdNo.value = "<?=$ono?>";
	f.ProdNm.value = "<?=mb_convert_encoding($title, 'euckr', _BASE_CHARSET_)?>";
	f.Amt.value = "<?=$amt?>";
	f.Job.value = "<?=$pay_method?>";
	f.UserId.value = "<?=$UserId?>";
	f.UserEmail.value = "<?=$buyer_email?>";
	f.MallUrl.value = "<?=$root_url?>";
	f.OrdNm.value = "<?=mb_convert_encoding($buyer_name, 'euckr', _BASE_CHARSET_)?>";
	f.OrdAddr.value = "<?=mb_convert_encoding($addr, 'euckr', _BASE_CHARSET_)?>";
	f.OrdPhone.value = "<?=$phone?>";
	f.QuotaInf.value = "<?=$QuotaInf?>";
	f.RcpNm.value = "<?=mb_convert_encoding($addressee_name, 'euckr', _BASE_CHARSET_)?>";
	f.RcpPhone.value = "<?=$addressee_phone?>";
	f.DlvAddr.value = "<?=mb_convert_encoding($addressee_addr, 'euckr', _BASE_CHARSET_)?>";
	f.Remark.value = "<?=mb_convert_encoding($remark, 'euckr', _BASE_CHARSET_)?>";
	f.MallPage.value = "<?=$mall_page?>";

	parent.Pay(f);

} else {
	window.alert('Load Error');
}
</script>