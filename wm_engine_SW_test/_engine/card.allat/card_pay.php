<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  올앳 PG결제 폼 데이터 전송
	' +----------------------------------------------------------------------------------------------+*/

	if(!defined("_wisa_lib_included")) exit;

	// 카드테이블 생성
	checkAgent();
	$os = trim("$os_name $os_version");
	$browser = trim("$br_name $br_version");
	$env_info = "$os || $browser || ".$_SERVER['REMOTE_ADDR'];

	$card_tbl = ($pay_type == 4) ? $tbl['vbank'] : $tbl['card'];
	cardDataInsert($card_tbl, 'allat');

	if($member['no']) $allat_pmember_id = $member['member_id'];
	else $allat_pmember_id = '비회원';
	$allat_pmember_id = cutstr($allat_pmember_id, 20);

	// 결제방식 선택
	$allat_card_yn = 'N';
	$allat_bank_yn = 'N';
	$allat_hp_yn = 'N';
	$allat_vbank_yn = 'N';
	switch($pay_type) {
		case '5' : $allat_bank_yn = 'Y'; break;
		case '7' : $allat_hp_yn = 'Y'; break;
		default  : $allat_card_yn = 'Y'; break;
	}

	if($pay_type == 4) {
		$allat_card_yn = 'N';
		$allat_bank_yn = 'N';
		$allat_hp_yn = 'N';
		$allat_vbank_yn = 'Y';
	}

	$title_code = str_replace(' ' ,'_', $title_code);
	$title_code = str_replace(',' ,'+', $title_code);

	$title = str_replace("\"", "", $title);
	$title = str_replace("'", "", $title);
	$prd_nm = cutStr(strip_tags($title), 30);
	$buyer_name=cutStr(strip_tags($buyer_name),20);
    $addressee_name = cutStr(strip_tags($addressee_name),20);
	$_SESSION['allat_ono'] = $ono;
/*
	$prd_nm = iconv(_BASE_CHARSET_, 'euc-kr', $prd_nm);
	$buyer_name = iconv(_BASE_CHARSET_, 'euc-kr', $buyer_name);
	$addressee_name = iconv(_BASE_CHARSET_, 'euc-kr', $addressee_name);
	$addressee_addr1 = iconv(_BASE_CHARSET_, 'euc-kr', $addressee_addr1);
	$addressee_addr2 = iconv(_BASE_CHARSET_, 'euc-kr', $addressee_addr2);
*/
?>
<script type='text/javascript'>
	var dfm=parent.fm;

	dfm.allat_shop_id.value='<?=$cfg['card_partner_id']?>';
	dfm.allat_order_no.value='<?=$ono?>';
	dfm.allat_amt.value='<?=parsePrice($pay_prc)?>';
	dfm.allat_pmember_id.value='<?=$allat_pmember_id?>';
	dfm.allat_product_cd.value='<?=$title_code?>';
	dfm.allat_product_nm.value='<?=$prd_nm?>';
	dfm.allat_buyer_nm.value='<?=$buyer_name?>';
	dfm.allat_recp_nm.value='<?=$addressee_name?>';
	dfm.allat_recp_addr.value='<?=$addressee_addr1?> <?=$addressee_addr2?>';
	dfm.allat_tax_yn.value='Y';
	dfm.allat_card_yn.value='<?=$allat_card_yn?>'; <!-- 카드여부:에스크로 선택시 N 2009-07-09 - Han  -->
	dfm.allat_bank_yn.value='<?=$allat_bank_yn?>'; <!-- 실시간 계좌이체 여부 2010-04-15 by zardsama -->
	dfm.allat_hp_yn.value='<?=$allat_hp_yn?>'; <!-- 휴대폰결제 2011-01-05 by zardsama -->
	dfm.allat_vbank_yn.value='<?=$allat_vbank_yn?>'; <!-- 가상계좌:에스크로사용 2009-07-09 - Han -->
	dfm.allat_email_addr.value='<?=$buyer_email?>';
	dfm.allat_test_yn.value='<?=$cfg['card_test']?>';
	dfm.allat_product_img.value='<?=$title_img?>';

	parent.ftn_approval(dfm);
</script>