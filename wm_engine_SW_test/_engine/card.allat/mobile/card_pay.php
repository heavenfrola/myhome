<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  올앳 모바일 결제데이터 전송
	' +----------------------------------------------------------------------------------------------+*/

	if(!defined("_wisa_lib_included")) exit();


	checkAgent();
	$os=@trim("$os_name $os_version");
	$browser=@trim("$br_name $br_version");
	$env_info="$os || $browser || ".$_SERVER['REMOTE_ADDR'];

	$card_tbl=($pay_type == 4) ? $tbl['vbank'] : $tbl['card'];

	cardDataInsert($card_tbl, 'allat');

	$allat_pmember_id = ($member['no']) ? $member['member_id'] : '비회원';
	$allat_pmember_id = cutstr($allat_pmember_id, 20);

	// 2009-07-09 : 에스크로일경우 카드/가상계좌 사용 여부 설정 - Han
	$allat_card_yn="Y";
	$allat_bank_yn="N";
	$allat_hp_yn="N";
	$allat_vbank_yn="N";

	if($pay_type == 5) {
		$allat_card_yn="N";
		$allat_bank_yn="Y";
		$allat_hp_yn="N";
		$allat_vbank_yn="N";
	}

	if($pay_type == 7) {
		$allat_card_yn="N";
		$allat_bank_yn="N";
		$allat_hp_yn="Y";
		$allat_vbank_yn="N";
	}
	if($pay_type == 4) {
		$allat_card_yn="N";
		$allat_bank_yn="N";
		$allat_hp_yn="N";
		$allat_vbank_yn="Y";
	}

	$title_code=str_replace(" ","_",md5($title));
	$title_code=str_replace(",","+",$title_code);

	$title=str_replace("\"","",$title);
	$title=str_replace("'","",$title);

	$prd_nm=cutStr(strip_tags($title),30);
	$buyer_name=cutStr(strip_tags($buyer_name),20);

?>
<script type='text/javascript'>
	var dfm = parent.document.getElementById('sendFm');

	dfm.allat_shop_id.value = '<?=$cfg['mobile_card_partner_id']?>';
	dfm.allat_order_no.value ='<?=$ono?>';
	dfm.allat_amt.value = '<?=$pay_prc?>';
	dfm.allat_pmember_id.value = '<?=$allat_pmember_id?>';
	dfm.allat_product_cd.value = '<?=$title_code?>';
	dfm.allat_product_nm.value = '<?=$prd_nm?>';
	dfm.allat_buyer_nm.value = '<?=$buyer_name?>';
	dfm.allat_recp_nm.value = '<?=$addressee_name?>';
	dfm.allat_recp_addr.value = '<?=$addressee_addr1?> <?=$addressee_addr2?>';
	dfm.allat_tax_yn.value = 'Y';
	dfm.allat_card_yn.value = '<?=$allat_card_yn?>';
	dfm.allat_abank_yn.value = '<?=$allat_bank_yn?>';
	dfm.allat_hp_yn.value = '<?=$allat_hp_yn?>';
	dfm.allat_vbank_yn.value = '<?=$allat_vbank_yn?>';
	dfm.allat_email_addr.value = '<?=$buyer_email?>';
	dfm.allat_test_yn.value = 'n';

	parent.approval(dfm);
</script>