<?PHP

	checkBasic();
	$total1=0;
	$ext=5;
	include_once $engine_dir."/_engine/include/shop.lib.php";
	include_once $engine_dir."/_engine/include/milage.lib.php";
	include_once $engine_dir.'/_engine/include/wingPos.lib.php';

	foreach($_POST['ono'] as $key=>$val) {
		$data=get_info($tbl['order'], 'no', numberOnly($val));
		if(!$data[no] || ($cfg[dlv_part]!="Y" && $data['stat']>4) || ($cfg[dlv_part]=="Y" && !preg_match("/@4@/",$data[stat2]))) {
			continue;
		}

		$x=0;

		$delivery_pno = array();
		$total2=$pdo->row("select count(`no`) from `$tbl[order_product]` where `ono`='$data[ono]' and `stat`=4");
		$total2++;
		for($ii=1; $ii<=$total2; $ii++) {
			$prd=$_POST['dlv_prd'.$data[no].$ii];
			if(!$prd) {
				continue;
			}

			orderStock($data['ono'], 4, 5, $prd);

			$sql="update `$tbl[order_product]` set `stat`='5' where `no`='$prd' and `stat`='4'";
			$pdo->query($sql);
			$x=1;
			$delivery_pno[] = $prd;

		}

		if($x==0) {
			continue;
		}

		$all_chg=$asql="";
		if($cfg[dlv_part]=="Y") {
			$all_chg=ordChgPart($data[ono],1);
		}
		else {
			$all_chg=1;
		}

		if($all_chg) {
			$ono=$data[ono];
			orderMilageChg();
			if($all_chg == '5') {
				cashReceiptAuto($data, 5);
				ordStatLogw($data[ono], 5);
			}

			setMemOrd($data[member_no],1, $ono);
			if($asql) {
				$asql=substr($asql,1);
				$r=$pdo->query("update `$tbl[order]` set $asql where `ono`='$ono'");
			}

			if($data['send_mail'] == 'Y') {
				$ord = $data;
				$mail_case = 4;
				include $engine_dir.'/_engine/include/mail.lib.php';
				sendMailContent($mail_case, $member_name, $to_mail);
			}
		}
		$total1++;
	}

	msg($total1." 개의 주문을 배송 처리하였습니다","reload","parent");

?>