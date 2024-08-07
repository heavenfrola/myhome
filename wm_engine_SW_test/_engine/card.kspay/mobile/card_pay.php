<?PHP

	if(!defined("_wisa_lib_included")) exit;

	checkAgent();
	$os = trim("$os_name $os_version");
	$browser = trim("$br_name $br_version");
	$env_info = "$os || $browser || ".$_SERVER['REMOTE_ADDR'];
	$card_tbl = $pay_type == 4 ? $tbl['vbank'] : $tbl['card'];
	cardDataInsert($card_tbl, 'kspay');

	// 결제방식
	switch($pay_type) {
		case '1' : $pay_method = '1000000000'; break;
		case '4' : $pay_method = '0100000000'; break;
		case '5' : $pay_method = '0010000000'; break;
		case '7' : $pay_method = '0000010000'; break;
	}

	// 할부기간
	$installrange = '0';
	for($i = 2; $i <= $cfg['kspay_m_installrange']; $i++) {
		$installrange .= ':'.$i;
	}

	$use_escrow = $pay_type == 4 ? '1' : '0';

	$data = array();
	$data['sndStoreid'] = $cfg['kspay_storeid'];
	$data['sndOrdernumber'] = $ono;
	$data['sndGoodname'] = cutstr(strip_tags($title), 25);
	$data['sndAmount'] = $pay_prc;
	$data['sndOrdername'] = $buyer_name;
	$data['sndEmail'] = $buyer_email;
	$data['sndMobile'] = str_replace('-', '', $buyer_cell);
	$data['sndInstallmenttype'] = $installrange;
	$data['sndEscrow'] = $use_escrow;
	$data['sndPaymethod'] = $pay_method;

?>
<script type="text/javascript">
	var f = parent.document.getElementById('authFrmFrame');
	<?foreach($data as $key => $val) {?>
	f.<?=$key?>.value = "<?=$val?>";
	<?}?>
	parent._pay(f);
</script>