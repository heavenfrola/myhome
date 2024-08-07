<?php

	/* +----------------------------------------------------------------------------------------------+
	' |  올앳 에스크로 결과코드 접수
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/shop.lib.php";
	checkBasic();

	if(function_exists('extractParam')) {
		extractParam();
	}

	// 올앳관련 함수 Include
	include $engine_dir."/_engine/card.allat/allatutil.php";

	// 결제인터페이스의 결과값 Get : 이전 주문결제페이지에서 Request Get
	$at_cross_key = $cfg['card_cross_key'];     //설정필요
	$at_shop_id   = $cfg['card_partner_id'];       //설정필요

	$at_data = "allat_shop_id=".urlencode($at_shop_id)."&allat_enc_data=".$_POST["allat_enc_data"]."&allat_cross_key=".$at_cross_key;
	$at_txt = EscrowChkReq($at_data,"NOSSL"); // https(SSL),http(NOSSL)

	$REPLYCD   =getValue("reply_cd",$at_txt);
	$REPLYMSG  =getValue("reply_msg",$at_txt);

	if( !strcmp($REPLYCD,"0000") ) {
		$ESCROWCHECK_YMDSHMS = getValue('escrow_check_ymdhms', $at_txt);

		echo '결과코드  : '.$REPLYCD.'<br />';
		echo '결과메세지: '.$REPLYMSG.'<br />';
		echo '에스크로 배송 개시일 : '.$ESCROWCHECK_YMDSHMS.'<br />';

		msg('정상적으로 송장 번호를 올앳페이에 전달하였습니다(에스크로 배송 개시일 설정완료)', 'reload', 'parent');
	}else{
		msg(" $REPLYMSG ($REPLYCD) ");
	}

?>