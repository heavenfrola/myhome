<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  나이스페이
	' +----------------------------------------------------------------------------------------------+*/

	if(!defined("_wisa_lib_included")) exit;

	checkAgent();
	$os = trim($os_name.' '.$os_version);
	$browser = trim($br_name.' '.$br_version);
	$env_info = $os.' || '.$browser.' || '.$_SERVER['REMOTE_ADDR'];

	switch($pay_type) {
		case '1' :
			$PayMethod = 'CARD';
			$card_tbl = $tbl['card'];
			$TransType = 0;
			break;
		case '4' :
			$PayMethod = 'VBANK';
			$card_tbl = $tbl['vbank'];
			$TransType = 1;
			break;
		case '5' :
			$PayMethod = 'BANK';
			$card_tbl = $tbl['card'];
			$TransType = 0;
			break;
		case '7' :
			$PayMethod = 'CELLPHONE';
			$card_tbl = $tbl['card'];
			$TransType = 0;
			break;
	}
	if(empty($cfg['banking_time'])) $cfg['banking_time'] = 5;

	cardDataInsert($card_tbl, 'nicepay');

	$formdata = array(
		'PayMethod' => $PayMethod,
		'GoodsName' => mb_strimwidth(strip_tags($title), 0, 20),
		'GoodsCnt' => 1,
		'Amt' => parsePrice($pay_prc),
		'BuyerName' => $buyer_name,
		'BuyerTel' => ($buyer_cell) ? $buyer_cell : $buyer_phone,
		'Moid' => $ono,
		'MID' => $cfg['nicepay_mid'],
		'UserIP' => $_SERVER['REMOTE_ADDR'],
		'VbankExpDate' => date('Ymd', strtotime("+{$cfg['banking_time']} days")),
		'ReturnURL' => $root_url.'/main/exec.php?exec_file=card.nicepay/mobile/card_pay.exe.php',
		'CharSet' => 'utf-8',
		'BuyerEmail' => $buyer_email,
		'TransType' => $TransType,
		'GoodsCl' => '1',
		'EdiDate' => date("YmdHis"),
		'EncryptData' => bin2hex(hash('sha256', date("YmdHis").$cfg['nicepay_mid'].parsePrice($pay_prc).$cfg['nicepay_licenseKey'], true)),
		'AcsNoIframe' => 'Y',
	);

	if($cfg['nice_use_taxfree'] == 'Y' && $taxfree_amount > 0) {
		$vat = round(($pay_prc-$taxfree_amount)/11);
		$formdata['SupplyAmt'] = ($pay_prc-$taxfree_amount-$vat);
		$formdata['GoodsVat'] = $vat;
		$formdata['ServiceAmt'] = 0;
		$formdata['TaxFreeAmt'] = $taxfree_amount;
	}

?>
<script type="text/javascript">
var f = parent.$('#nicepayFrm');
f.html('');

<?foreach($formdata as $key => $val) {?>
f.append("<input type='hidden' name='<?=$key?>' value='<?=$val?>'>");
<?}?>

parent.nicepayStart();
</script>