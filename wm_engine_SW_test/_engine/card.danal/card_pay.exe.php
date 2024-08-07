<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  다날 결제 결과 DB처리
	' +----------------------------------------------------------------------------------------------+*/

	header("Pragma: No-Cache");

	include $engine_dir."/_engine/include/common.lib.php";
	include $engine_dir."/_engine/card.danal/inc/function.php";

	$BillErr = false;
	$TransR = array();

	$ServerInfo = $_POST["ServerInfo"];

	$nConfirmOption = 1;
	$TransR["Command"] = "NCONFIRM";
	$TransR["OUTPUTOPTION"] = "DEFAULT";
	$TransR["ServerInfo"] = $ServerInfo;
	$TransR["ConfirmOption"] = $nConfirmOption;


	if($nConfirmOption == 1) {
		$TransR["CPID"] = $cfg['danal_cp_id'];
		$TransR["AMOUNT"] = $_POST['ItemAmt'];
	}

	$Res = CallTeledit($TransR,false);

	$ono = addslashes($Res["ORDERID"]);
	if(!$ono && $_POST['ono']) $ono = addslashes($_POST['ono']);
	if($Res["Result"] == "0") {
		$TransR = array();
		$TransR["Command"] = "NBILL";
		$TransR["OUTPUTOPTION"] = "DEFAULT";
		$TransR["ServerInfo"] = $ServerInfo;
		$TransR["BillOption"] = 0;

		$Res2 = CallTeledit( $TransR,false );

		if($Res2["Result"] != "0") {
			$BillErr = true;
		}
	}

	if($Res["Result"] == "0" && $Res2["Result"] == "0") {
		$_info = Parsor($Info, "|");
		$good_name = addslashes($pdo->row("select `name` from `$tbl[order_product]` where `ono`='$ono' order by `no` asc limit 1"));
		$ord = $pdo->assoc("select * from `$tbl[order]` where `ono`='$ono'");
		$danal_pay = "Y";

		// 성공 로그 업데이트
		$pdo->query("update `$tbl[card]` set `stat`='2', `card_name`='$Carrier' , `app_no`='$OTP', `res_cd`='$Res[Result]' ,`res_msg`='거래성공|$_info[TelNum]|$_info[Iden]' ,`ordr_idxx`='$ono' ,`tno`='$Res2[TID]' ,`good_mny`='$Res[TotalAmount]' ,`good_name`='$good_name' ,`buyr_name`='$ord[buyer_name]' ,`buyr_mail`='$ord[buyer_email]' ,`buyr_tel1`='$ord[buyer_phone]' ,`buyr_tel2`= '$ord[buyer_cell]',`use_pay_method`='danalpay' where `wm_ono`='$ono'");

		include_once $engine_dir."/_engine/order/order2.exe.php";

		if($card['pg_version'] != 'mobile') {
		?>
		<script type="text/javascript">
			opener.parent.location.replace("<?=$root_url?>/shop/order_finish.php");
			self.close();
		</script>
		<?
		} else {
			header("Location:".$root_url."/shop/order_finish.php");
		}

		exit;
	} else {
		if($BillErr) $Res=$Res2;

		$ErrorCode		= $Res["Result"];
		$ErrorMessage	= $Res["ErrMsg"];
		$BackURL		= $root_url."/main/exec.php?exec_file=card.danal/cancel.php";
		$AbleBack		= false;

		// 실패 로그 업데이트
		if($ono) {
			$stat = $pdo->row("select stat from $tbl[order] where ono='$ono'");
			if($stat == 11) {
				$pdo->query("update `$tbl[card]` set `res_cd`='".$ErrorCode."', `res_msg`='".addSlashes($ErrorMessage)."' where `wm_ono`='$ono'");
				$pdo->query("update `$tbl[order]` set `stat`=31 where `ono`='$ono' and stat != 2");
				$pdo->query("update `$tbl[order_product]` set `stat`=31 where `ono`='$ono' and stat != 2");

				ordStatLogw($ono, 31, 'Y');
				makeOrderLog($ono, "order2.exe.php");
			}
		}

		if($Res["OrderID"]) {
		?>
		<script type="text/javascript">
		cancelf=opener.parent.document.pay_cFrm;
		cancelf.ono.value='<?=$Res[OrderID]?>';
		cancelf.mode.value='fail';
		cancelf.submit();
		</script>
		<?
		}
		include $engine_dir."/_engine/card.danal/Error.php";
	}

?>