<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  U+ Xpay 결제데이터 전송
	' +----------------------------------------------------------------------------------------------+*/

	if(!defined("_wisa_lib_included")) exit();

	//임시계정
	$ono = (!$sno) ? addslashes($_POST['sno']) : $sno;
	$CST_MID = $cfg['card_auto_dacom_id'];
	$LGD_MERTKEY = $cfg['card_dacom_auto_key'];

	$CST_PLATFORM = ($cfg['autobill_test'] == 'Y') ? 'test' : 'service';

	if($_SESSION['browser_type'] == 'mobile') {
		//모바일 결제시 세션에 주문정보 입력
		$_SESSION['MOBILE_OID'] = "";
		$_SESSION['MOBILE_OID'] = $ono;
	}

	//카드 정보 입력
	/*
	$card_tbl= $tbl[card];
	list($os, $browser) = checkAgent();
	$env_info = "$os || $browser || ".$_SERVER['REMOTE_ADDR'];
	cardDataInsert($card_tbl, "dacom");
	*/
	//카드 승인 번호 및 PLATFORM 확인
	$LGD_MID = ($CST_PLATFORM == 'test') ? 't'.$CST_MID : $CST_MID;
	if(!$LGD_MID || !$LGD_MERTKEY) {
		msg("카드 설정이 잘못되었습니다 - 관리자에게 문의하세요.".$cfg['card_dacom_auto_key']);
	}

	// 응답 리턴 url 설정
	$LGD_RETURNURL = $root_url.'/main/exec.php?exec_file=card.dacom/XpayAutoBilling/returnurl.php';

?>
<script type="text/javascript">
	<? if($_SESSION['browser_type'] != 'mobile') { ?>
		var tf=parent.document.LGD_BILL_PAYINFO;
		tf.LGD_MID.value="<?=$LGD_MID?>";
		tf.LGD_OID.value="<?=$ono?>";
		tf.LGD_BUYERSSN.value="<?=$LGD_BUYERSSN?>";
		tf.LGD_CHECKSSNYN.value="<?=$LGD_CHECKSSNYN?>";
		tf.LGD_WINDOW_TYPE.value="iframe";
		tf.LGD_RETURNURL.value="<?=$LGD_RETURNURL?>";
		parent.launchCrossPlatformPC();
	<? }else{ ?>
		var tf=parent.document.LGD_BILL_PAYINFO_M;
		tf.CST_MID.value="<?=$CST_MID?>";
		tf.LGD_MID.value="<?=$LGD_MID?>";
		tf.LGD_BUYERSSN.value="<?=$LGD_BUYERSSN?>";
		tf.LGD_CHECKSSNYN.value="<?=$LGD_CHECKSSNYN?>";
		parent.launchCrossPlatformM();
	<? } ?>
</script>