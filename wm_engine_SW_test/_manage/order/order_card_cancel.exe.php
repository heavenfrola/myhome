<?PHP

	checkBasic(2);

	if($admin['level'] > 2 && strchr($admin['auth'], '@cardcc') == false) {
		msg('카드 취소 권한이 없습니다.');
	}

	$cno = numberOnly($_GET['cno']);
	$card_cancel_type = numberOnly($_GET['card_cancel_type']);
	$card=get_info($tbl[card],"no",$cno);
	if(!$card[no]) msg("해당 결제정보가 존재하지 않습니다");

	$price=numberOnly($_GET['price'], $_currency_decimal[$card['currency']]);
	$price = ($card_cancel_type == 1) ? 0 : $price;
	if($price > 0 && $price > $card[wm_price]) msg("금액을 확인해주시기 바랍니다.");
	if(($card['pg'] != 'kcp' && $card['pg'] != 'nicepay' && $card['pg'] != 'inicis') && $price == $card['wm_price']) $price=0;
	$card['wm_price'] = parsePrice($card['wm_price']);
	if(!$card[pg]) $card[pg]="kcp";
	if($card[pg] == "allat" && !$price) $price=$card[wm_price];
	if($card['pg']=='kakaopay') $card[pg] = 'kakao';
	if($card['pg']=='tosspay') $card[pg] = 'tosspayment';
	if($card['pg']=='wechat') $card[pg] = 'eximbay';
	if($card['pg']=='alipay_e') $card[pg] = 'eximbay';

	if($card['pg'] == 'inicis' && $card['pg_version']) $pg_version = $card['pg_version']."/";
	if($card['pg'] == 'kcp' && $card['pg_version']) $pg_version = $card['pg_version']."/";
	if($card['pg'] == 'kakao' && $card['pg_version']) $pg_version = $card['pg_version']."/";

	if($card['pg'] == 'dacom' && $price > 0) {
		include $engine_dir.'/_engine/card.dacom/card_pcancel.php';
    } else if ($card['pg'] == 'nsp') {
		include $engine_dir.'/_engine/card.naverSimplePay/card_cancel.php';
	} else {
		include $engine_dir."/_engine/card.".$card[pg]."/".$pg_version."card_cancel.php";
	}
	exit;

?>