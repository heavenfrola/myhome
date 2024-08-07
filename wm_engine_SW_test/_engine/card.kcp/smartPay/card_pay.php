<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  KCP smartPay 주문데이터 전송
	' +----------------------------------------------------------------------------------------------+*/

	if(!defined("_wisa_lib_included")) exit();

	$site_cd=$cfg[card_mobile_site_cd];
	$site_key=$cfg[card_mobile_site_key];

	if(!$site_cd || !$site_key) msg('카드 설정이 잘못되었습니다 - 관리자에게 문의하세요.');

	$pay_method="100000000000";

	if($pay_type == 7) { // 휴대폰
		$pay_method = 'MOBX';
		$card_tbl = $tbl['card'];
	} else if($pay_type == 4) { // 가상계좌
		$pay_method = 'VCNT';
		$ActionResult = 'vcnt';
		$card_tbl = $tbl['vbank'];
		$escw_used = 'Y';
		$good_info = addslashes(strip_tags(str_replace(" ","", $good_info)));
	} else if($pay_type == 5) { // 계좌이체
		$pay_method = 'BANK';
		$ActionResult = 'acnt';
		$card_tbl = $tbl['card'];
		$good_info = addslashes(strip_tags(str_replace(" ","", $good_info)));
	} else { // 신용카드
		$pay_method="CARD";
		$card_tbl = $tbl['card'];
	}

	checkAgent();
	$os=@trim("$os_name $os_version");
	$browser=@trim("$br_name $br_version");
	$env_info="$os || $browser || ".$_SERVER['REMOTE_ADDR'];

	cardDataInsert($card_tbl, "kcp"); // 2007-05-31 : card 테이블에 넣는 정보 함수로 변경 - Han

    $good_name=strip_tags(preg_replace("/(\#|&)/", " ", $title));

	$cfg['escrow_pay_mod']="Y";
    $_SESSION['cart_cache'] = $_POST['cart_selected'];

?>
<script type="text/javascript">
	var tf=parent.sm_form;

	tf.good_name.value='<?=$good_name?>';
	tf.good_mny.value='<?=$pay_prc?>';
	tf.buyr_name.value="<?=$buyer_name?>";
	tf.buyr_tel1.value='<?=$buyer_phone?>';
	tf.buyr_tel2.value='<?=$buyer_cell?>';
	tf.buyr_mail.value='<?=$buyer_email?>';
	tf.req_tx.value='pay';
	tf.site_cd.value="<?=$site_cd?>";
	tf.site_key.value='<?=$site_key?>';
	tf.shop_name.value="<?=$cfg['card_mobile_site_name']?>";
	tf.pay_method.value="<?=$pay_method?>";
	tf.ordr_idxx.value="<?=$ono?>";
	tf.quotaopt.value="<?=$cfg[card_mobile_quotaopt]?>";
	tf.currency.value="410";
	tf.Ret_URL.value="<?=$root_url?>/main/exec.php?exec_file=card.kcp/smartPay/card_pay.exe.php";
	tf.ActionResult.value='<?=strtolower($pay_method)?>';

	<?if($pay_type==4) {?>
	tf.escw_used.value = 'Y';
	tf.pay_mod.value = '<?=$cfg[escrow_pay_mod]?>';
	tf.deli_term.value = '<?=$cfg[escrow_deli_term]?>';
	tf.bask_cntx.value = '<?=$c_idx?>';
	tf.good_info.value = '<?=$good_info?>';
	tf.rcvr_name.value = '<?=$addressee_name?>';
	tf.rcvr_tel1.value = '<?=$addressee_phone?>';
	tf.rcvr_tel2.value = '<?=$addressee_cell?>';
	tf.rcvr_zipx.value = '<?=$addressee_zip?>';
	tf.rcvr_add1.value = '<?=$addressee_addr1?>';
	tf.rcvr_add2.value = '<?=$addressee_addr2?>';
	<?} else if($pay_type==5) {?>
	tf.rcvr_name.value = '<?=$addressee_name?>';
	tf.rcvr_tel1.value = '<?=$addressee_phone?>';
	tf.rcvr_tel2.value = '<?=$addressee_cell?>';
	tf.rcvr_zipx.value = '<?=$addressee_zip?>';
	tf.rcvr_add1.value = '<?=$addressee_addr1?>';
	tf.rcvr_add2.value = '<?=$addressee_addr2?>';
	<?}?>
	<?if($cfg['kcp_use_taxfree'] == 'Y' && $taxfree_amount > 0) {?>
	tf.tax_flag.value = 'TG03';
	tf.comm_tax_mny.value = '<?=($pay_prc-$taxfree_amount)?>';
	tf.comm_free_mny.value = '<?=$taxfree_amount?>';
	tf.comm_vat_mny.value = '<?=floor(($pay_prc-$taxfree_amount)/11)?>';
	<?}?>

	parent.kcp_AJAX();
	parent.document.body.scrollTop = 0;
</script>