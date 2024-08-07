<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  올앳 PG 취소
	' +----------------------------------------------------------------------------------------------+*/

	if(function_exists('extractParam')) {
		extractParam();
	}

	$pay_type = $pdo->row("select `pay_type` from `$tbl[order]` where `ono`='$card[wm_ono]'");
	$card = $pdo->assoc("select * from $tbl[card] where no='$cno'");
	if(!$card) $card = $pdo->assoc("select * from $tbl[vbank] where no='$cno'");
	$ord = $pdo->assoc("select * from $tbl[order] where ono='$card[wm_ono]'");
	$allat_id = ($card['pg_version'] == 'mobile') ? $cfg['mobile_card_partner_id'] : $cfg['card_partner_id'];

	switch($pay_type) {
		case '1' : $pay_method = 'CARD'; break;
		case '4' : $pay_method = 'VBANK'; break;
		case '5' : $pay_method = 'ABANK'; break;
		case '7' : $pay_method = 'HP'; break;
	}

	$price = numberOnly($_REQUEST['price']);
	if(!$price) $price = $card['wm_price'];

	$host  = ($_SERVER['HTTPS'] != 'on') ? 'http://' : 'https://';
	$host .= $_SERVER['HTTP_HOST'];

?>
<script language=JavaScript charset='euc-kr' src="https://tx.allatpay.com/common/NonAllatPayREPlus.js"></script>
<script language=Javascript>
	// 결제페이지 호출
	function ftn_approval(dfm) {
		Allat_Plus_Api(dfm);
	}

	// 결과값 반환( receive 페이지에서 호출 )
	function result_submit(result_cd,result_msg,enc_data) {
		if( result_cd != '0000' ){
			window.setTimeout(function(){alert(result_cd + " : " + result_msg);},1000);
		} else {
			fm.allat_enc_data.value = enc_data;

			fm.action = "/main/exec.php?exec_file=card.allat/card_cancel.exe.php";
			fm.method = "post";
			fm.target = "_self";
			fm.submit();
		}
	}
</script>
<form name="fm" method="post" accept-charset="euc-kr">
	<input type="hidden" name="allat_shop_id" value="<?=$allat_id?>">
	<input type="hidden" name="allat_order_no" value="<?=$card['wm_ono']?>"></td>
	<input type="hidden" name="allat_amt" value="<?=parsePrice($price)?>">
	<input type="hidden" name="allat_pay_type" value="<?=$card['use_pay_method']?>">
	<input type="hidden" name="shop_receive_url" value="<?=$host?>/main/exec.php?exec_file=card.allat/allat_receive.php">
	<input type="hidden" name="allat_enc_data" value="">
	<input type="hidden" name="allat_opt_pin" value="NOUSE">
	<input type="hidden" name="allat_opt_mod" value="APP">
	<input type="hidden" name="allat_seq_no" value="<?=$card['tno']?>">
	<input type="hidden" name="allat_test_yn" value="N">
	<input type="hidden" name="cno" value="<?=$cno?>">
</form>
<script type="text/javascript">
ftn_approval(document.fm);
</script>