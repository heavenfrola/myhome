<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  KCP 결제정보 전송
	' +----------------------------------------------------------------------------------------------+*/

	if(!defined("_wisa_lib_included")) exit;

	if(empty($cfg['card_site_cd']) || empty($cfg['card_site_key'])) msg('카드 혹은 에스크로 설정이 잘못되었습니다 - 관리자게에 문의하세요.');
	if(!$cfg['escrow_deli_term']) $cfg['escrow_deli_term'] = '05';

	switch($pay_type) {
		case '4' : // 가상계좌
			$card_tbl=$tbl['vbank'];
			$pay_method="001000000000";
			$good_info=addslashes(strip_tags(str_replace(" ","", $good_info)));
		break;
		case '5' : // 실시간계좌이체
			$card_tbl=$tbl['card'];
			$pay_method="010000000000";
		break;
		case '7' : // 휴대폰결제
			$card_tbl=$tbl['card'];
			$pay_method="000010000000";
		break;
		default :
			$card_tbl=$tbl['card'];
			$pay_method="100000000000";
		break;
	}

	checkAgent();

	$os=@trim("$os_name $os_version");
	$browser=@trim("$br_name $br_version");
	$env_info="$os || $browser || ".$_SERVER['REMOTE_ADDR'];

	cardDataInsert($card_tbl, "kcp");

	$good_name=strip_tags(preg_replace("/(\#|&)/", " ", $title));

	$cfg['escrow_pay_mod']="Y";

?>
<script type="text/javascript">
	var tf = parent.document.getElementById('payFrm');
	tf.pay_method.value = '<?=$pay_method?>';
	tf.ordr_idxx.value = '<?=$ono?>';
	tf.good_name.value = '<?=$good_name?>';
	tf.good_mny.value = '<?=$pay_prc?>';
	tf.buyr_name.value = '<?=$buyer_name?>';
	tf.buyr_mail.value = '<?=$buyer_email?>';
	tf.buyr_tel1.value = '<?=$buyer_phone?>';
	tf.buyr_tel2.value = '<?=$buyer_cell?>';
	tf.site_cd.value = '<?=$cfg[card_site_cd]?>';
	tf.site_key.value = '<?=$cfg[card_site_key]?>';
	tf.site_name.value = '<?=$cfg[card_site_name]?>';
	tf.tran_cd.value = '00100000';
	tf.quotaopt.value = '<?=$cfg[card_quotaopt]?>';

	<?if($pay_type==4) {?>
	tf.escw_used.value = 'Y';
	tf.pay_mod.value = '<?=$cfg[escrow_pay_mod]?>';
	tf.deli_term.value = '<?=$cfg[escrow_deli_term]?>';
	tf.req_tx.value = 'pay';
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

	if(parent.jsf__pay(tf)) tf.submit();
</script>