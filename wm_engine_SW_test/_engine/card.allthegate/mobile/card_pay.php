<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  올더게이트 모바일 결제데이터 전송
	' +----------------------------------------------------------------------------------------------+*/

	function alltheValue($str, $length=0) {
		$str = strip_tags(trim($str));
		$str = str_replace('|', '', $str);
		$str = str_replace('"', '', $str);
		if($length > 0) $str = cutstr($str, $length, '');
		$str = addslashes($str);

		return $str;
	}

	$card_tbl = ($pay_type == 4) ? $tbl['vbank'] : $tbl['card'];
	cardDataInsert($card_tbl, 'allthegate');

	$StoreNm	= alltheValue($cfg['company_name']);
	$ProdNm		= alltheValue($title, 100);
	$OrdNm		= alltheValue($buyer_name, 20);
	$OrdAddr	= alltheValue($addressee_addr1.' '.$addressee_addr2, 100);
	$phone		= alltheValue($buyer_phone, 21);
	$RcpNm		= $addressee_name ? alltheValue($addressee_name, 20) : $OrdNm;
	$RcpPhone	= $addressee_phone ? alltheValue($addressee_phone, 21) : $phone;
	$DlvAddr	= $addressee_addr1 ?  alltheValue($addressee_addr1.' '.$addressee_addr2, 100) : $OrdAddr;
	$UserId		= substr($member['member_id'], 0, 20);
	if(!$OrdAddr) $OrdAddr='배송지 없음';
	if(!$DlvAddr) $DlvAddr='배송지 없음';

	switch($pay_type) {
		case '1' : $pay_method = 'cardnormal'; break;
		case '4' : $pay_method = 'virtualnormal'; break;
		case '7' : $pay_method = 'hp'; break;
	}

	$QuotaInf = array(0);
	for($i = 1; $i <= $cfg['card_quotaopt']; $i++) {
		$QuotaInf[] = $i;
	}
	$QuotaInf = implode(':', $QuotaInf);

	if($pay_type == '4') $remark='escrow';
	if(!$member['member_id']) $member['member_id']='비회원';

?>
<script type="text/javascript">
	var f = parent.document.getElementById('agsFrm');

	f.StoreId.value = "<?=$cfg['mobile_allthegate_StoreId']?>";
	f.StoreNm.value = "<?=$StoreNm?>";
	f.OrdNo.value = "<?=$ono?>";
	f.ProdNm.value = "<?=$ProdNm?>";
	f.Amt.value = "<?=$pay_prc?>";
	f.DutyFree.value= "";
	f.Job.value = "<?=$pay_method?>";
	f.UserId.value = "<?=$UserId?>";
	f.UserEmail.value = "<?=$buyer_email?>";
	f.MallUrl.value = "<?=$root_url?>";
	f.OrdNm.value = "<?=$buyer_name?>";
	f.OrdAddr.value = "<?=$addr?>";
	f.OrdPhone.value = "<?=$phone?>";
	f.RcpNm.value = "<?=$RcpNm?>";
	f.RcpPhone.value = "<?=$RcpPhone?>";
	f.DlvAddr.value = "<?=$DlvAddr?>";
	f.RtnUrl.value = "<?=$root_url?>/main/exec.php?exec_file=card.allthegate/mobile/card_pay.exe.php";
	f.CancelUrl.value = "<?=$root_url?>/main/exec.php?exec_file=card.allthegate/mobile/card_cancel.exe.php";
	f.MallPage.value = "<?=$root_url?>/main/exec.php?exec_file=card.allthegate/vbank.exe.php";
	f.VIRTUAL_DEPODT.value = "<?=date('Ymd', strtotime('+5 days'))?>";
	f.QuotaInf.value = "<?=$QuotaInf?>";
	f.DeviId.value = "9000400001";

	parent.doPay(f);
</script>