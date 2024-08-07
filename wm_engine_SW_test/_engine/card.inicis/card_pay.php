<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  이니시스 결제데이터 전송
	' +----------------------------------------------------------------------------------------------+*/

	if(!defined("_wisa_lib_included")) exit();

	checkAgent();
	$os = trim("$os_name $os_version");
	$browser = trim("$br_name $br_version");
	$env_info = "$os || $browser || ".$_SERVER['REMOTE_ADDR'];

	$card_tbl=($pay_type == 4) ? $tbl[vbank] : $tbl[card]; // 2007-08-23 : 에스크로 추가 - Han

	cardDataInsert($card_tbl, 'inicis'); // 2007-05-31 : card 테이블에 넣는 정보 함수로 변경 - Han

	$allat_pmember_id = ($member['no']) ? $member['member_id'] : '비회원';

	if($total_order_price >= $cfg['escrow_limit']) {
		$allat_bank_yn = 'Y';
	} else {
		$allat_bank_yn = 'N';
	}

	$title_code = str_replace(' ', '_', $title_code);
	$title_code = str_replace(',', '+', $title_code);
	$prd_nm = cutStr(inputText(strip_tags(addslashes($title))),90);
	if(!$buyer_phone) $buyer_phone = $buyer_cell;

	if(!$cfg['card_quotaopt']) {
		$cfg['card_quotaopt'] = '3';
	}
	$qstr = '';
	for($ii=2; $ii<=$cfg['card_quotaopt']; $ii++) {
		$qstr .= ':'.$ii.'개월';
	}

	switch ($pay_type) {
		case '1' : $gopaymethod = 'Card'; break;
		case '5' : $gopaymethod = 'Account'; break;
		case '4' : $gopaymethod = 'Vbank'; break;
		case '7' : $gopaymethod = 'HPP'; break;
		default :  $gopaymethod='';
	}

	$cfg['card_mall_id'] = ($cfg['card_test'] == 'Y') ? 'INIpayTest' : $cfg['card_mall_id'];
	if($pay_type == 4){
		$cfg['card_mall_id'] = ($cfg['escrow_mall_id']) ? $cfg['escrow_mall_id'] : $cfg['card_mall_id'];
	}

	if($taxfree_amount > 0) {
		addField(
			$card_tbl,
			'wm_free_price',
			'double(10,2) NULL DEFAULT "0.00" COMMENT "면세 금액" after wm_price'
		);
		$pdo->query("update $card_tbl set wm_free_price='$taxfree_amount' where wm_ono='$ono'");
	}

?>
<script type="text/JavaScript">
var tf=parent.ini;
	tf.goodname.value='<?=$prd_nm?>';
	tf.price.value='<?=$pay_prc?>';
	tf.buyername.value='<?=$buyer_name?>';
	tf.buyeremail.value='<?=$buyer_email?>';
	tf.parentemail.value='';
	tf.buyertel.value='<?=$buyer_phone?>';
	tf.mid.value='<?=$cfg[card_mall_id]?>';
	tf.currency.value='WON';
	tf.nointerest.value='no';
	tf.quotabase.value='선택:일시불<?=$qstr?>';
	//tf.acceptmethod.value=''; 2007-10-18 휴대폰 결제 오류로 인한 수정(HPP 값이 없어지면 안됨) by zardsama
	tf.oid.value='<?=$ono?>';
	tf.quotainterest.value='';
	tf.gopaymethod.value='<?=$gopaymethod?>';
	tf.paymethod.value='';
	tf.cardcode.value='';
	tf.cardquota.value='';
	tf.rbankcode.value='';
	tf.reqsign.value='DONE';
	tf.encrypted.value='';
	tf.sessionkey.value='';
	tf.uid.value='';
	tf.sid.value='';
	tf.version.value=4000;
	tf.clickcontrol.value='';

	parent.enable_click();
	parent.focus_control();


	if (parent.pay(tf)) tf.submit();
</script>