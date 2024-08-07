<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  다날 결제 데이터 전송
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir.'/_engine/card.danal/inc/function.php';

	$card_tbl = $pay_type == 4 ? $tbl['vbank'] : $tbl['card'];
	cardDataInsert($card_tbl, 'danal');

	if(!$cfg['danal_subcp_id']) msg('휴대폰 결제를 위한 상점아이디가 입력되어있지 않습니다.');

	// 필수 데이터
	$TransR = array();

	// 아래의 데이터는 고정값입니다.( 변경하지 마세요 )
	$TransR["Command"] = "ITEMSEND2";
	$TransR["SERVICE"] = "TELEDIT";
	$TransR["ItemType"] = "Amount";
	$TransR["ItemCount"] = "1";
	$TransR["OUTPUTOPTION"] = "DEFAULT";

	// CP 정보
	$TransR["ID"] = $cfg['danal_cp_id'];
	$TransR["PWD"] = $cfg['danal_cp_pwd'];
	//$TransR["SUBCP"] = $cfg['danal_subcp_id'];
	$CPName = stripslashes($cfg['company_mall_name']);

	// 상품정보
	$ItemAmt = $pay_prc;
	$ItemName = cutstr(preg_replace("/(;|=|'|\|)/", '', strip_tags($title)), 30);
	$ItemCode = $cfg['danal_item_code'];
	$ItemInfo = MakeItemInfo($ItemAmt,$ItemCode,mb_convert_encoding($ItemName, 'euckr', 'utf8'));

	$TransR["ItemInfo"] = $ItemInfo;

	// 선택사항
	$TransR["SUBCP"] = $cfg['danal_subcp_id'];
	$TransR["USERID"] = $member['member_id'] ? $member['member_id'] : $ord['buyer_name'];
	$TransR["ORDERID"] = $ono;
	$TransR["IsPreOtbill"] = "N"; // 자동결제 여부


	/* +----------------------------------------------------------------------------------------------+
	' | CPCGI에 HTTP POST로 전달되는 데이터
	' +----------------------------------------------------------------------------------------------+*/
	// 필수 데이터
	$ByPassValue = array();

	$ByPassValue['ItemAmt'] = $pay_prc;
	$ByPassValue['ItemName'] = $ItemName;
	$ByPassValue["TargetURL"] = $root_url."/main/exec.php?exec_file=card.danal/card_pay.exe.php";
	//$ByPassValue["BackURL"] = $root_url."/main/exec.php?exec_file=card.danal/Cancel.php&ono=$ono";
	//$ByPassValue["BackURL"] = urlencode("close");
	$ByPassValue['BackURL'] = $root_url."/main/exec.php?exec_file=card.danal/Error.php";
	$ByPassValue["IsUseCI"] = "N"; // CP의 CI 사용 여부( Y or N )
	$ByPassValue["CIURL"] = "ci.gif"; // CP의 CI FULL URL
	$ByPassValue["IsPreOtbill"] = "N";
	$ByPassValue["BgColor"] = "00";
	$ByPassValue["IsCharSet"] = "utf-8";

	// 선택 사항
	$ByPassValue["ByBuffer"] = "";
	$ByPassValue["ByAnyName"] = "";
	$ByPassValue["ono"] = $ono;

	$Res = CallTeledit($TransR,false);
	$btype = $mobile_browser == 'mobile' ? 'FlexMobile' : 'Web';
	if($Res["Result"] == "0") {
		?>
		<form id="Ready" action="https://ui.teledit.com/Danal/Teledit/<?=$btype?>/Start.php" method="post">
			<?
			MakeFormInput($Res,array("Result","ErrMsg"));
			MakeFormInput($ByPassValue);
			?>
			<input type="hidden" name="CPName"      value="<?=$CPName?>">
			<!--
			<input type="hidden" name="ItemName"    value="<?=$ItemName?>">
			<input type="hidden" name="ItemAmt"     value="<?=$ItemAmt?>">
			-->
			<input type="hidden" name="IsPreOtbill" value="<?=$TransR['IsPreOtbill']?>">
		</form>
		<script type='text/javascript'>
			<?if($mobile_browser != 'mobile') {?>
			var w = window.open("", "popUpWin","width=500px, height=676px, status=yes, scrollbars=no,resizable=yes, menubar=no");
			if(w) {
				var f = document.getElementById('Ready');
				f.target = 'popUpWin';
				f.submit();
			} else {
				window.alert('브라우저의 새창열기 설정이 차단되어있습니다.\n정상적인 결제를 위해서 새창열기를 허용해 주세요.');
                parent.layTgl3('order1', 'Y');
                parent.layTgl3('order2', 'N');
                parent.layTgl3('order3', 'Y');
			}
			<?} else {?>
			var f = document.getElementById('Ready');
			parent.$('#Ready').remove();
			parent.$('body').append(f);
			parent.$('#Ready').submit();
			<?}?>
		</script>
		<?
	} else {
		$Result		= $Res["Result"];
		$ErrMsg		= addslashes($Res["ErrMsg"]);
		$AbleBack	= false;
		$BackURL	= $ByPassValue["BackURL"];
		$IsUseCI	= $ByPassValue["IsUseCI"];
		$CIURL		= $ByPassValue["CIURL"];
		$BgColor	= $ByPassValue["BgColor"];

        $layer1 = 'order1';
        $layer2 = 'order2';
        $layer3 = 'order3';
        msg("결제가 실패하였습니다.\\n$ErrMsg");
	}

?>