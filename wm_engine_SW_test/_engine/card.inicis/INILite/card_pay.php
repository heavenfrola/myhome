<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  INIlite 결제 데이터 전송
	' +----------------------------------------------------------------------------------------------+*/

	if(!defined("_wisa_lib_included")) exit();

	checkAgent();
	$os = trim("$os_name $os_version");
	$browser = trim("$br_name $br_version");
	$env_info = "$os || $browser || ".$_SERVER['REMOTE_ADDR'];

	$card_tbl=($pay_type == 4) ? $tbl['vbank'] : $tbl['card'];
	cardDataInsert($card_tbl, 'inicis');

	$prd_nm = cutStr(inputText(strip_tags(addslashes($title))),90);

	if(empty($cfg['card_quotaopt'])) $cfg['card_quotaopt']="3";
	$qstr = '';
	for($ii=2; $ii<=$cfg[card_quotaopt]; $ii++) {
		$qstr.=":".$ii."개월";
	}
	if(!$buyer_phone) $buyer_phone = $buyer_cell;

	switch ($pay_type) {
		case "1" : $gopaymethod = "Card"; break;
		case "5" : $gopaymethod = "Account"; break;
		case "4" : $gopaymethod = "Vbank"; break;
		case "7" : $gopaymethod = "HPP"; break;
		default  : $gopaymethod="";
	}

?>
<script type="text/javascript">

	var tf=parent.document.ini;

	tf.goodname.value='<?=$prd_nm?>';
	tf.price.value='<?=$pay_prc?>';
	tf.buyername.value='<?=$buyer_name?>';
	tf.buyeremail.value='<?=$buyer_email?>';
	tf.parentemail.value='';
	tf.buyertel.value='<?=$buyer_phone?>';
	tf.mid.value='<?=$cfg[card_inicis_id]?>';
	tf.currency.value='WON';
	tf.nointerest.value='no';
	tf.quotabase.value='선택:일시불<?=$qstr?>';
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

	if(parent.pay(tf)) tf.submit();
</script>