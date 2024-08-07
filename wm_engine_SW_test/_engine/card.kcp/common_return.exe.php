<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  KCP 에스크로 가상계좌 입금정보 접수
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir.'/_engine/include/common.lib.php';

	if($_POST['tx_cd'] == 'TX00') {
		$ono = $_POST['order_no'];
		$stat = $_POST['op_cd'] == 13 ? 1 : 2;

		$ord = $pdo->assoc("select * from `$tbl[order]` where `ono`='$ono'");
		$vbank = $pdo->assoc("select * from `$tbl[vbank]` where `wm_ono`='$ono'");

		if(!$ord['no']) exit("주문번호 $ono 가 존재하지 않거나 삭제되었습니다");
		if($vbank['tno'] != $_POST['tno']) exit('결제 승인코드(tno)가 일치하지 않습니다');

		if($stat == 2 && ($ord['stat']==1 || $ord['stat'] == 11)) { // 입금
			if($ord['pay_prc'] <= $_POST['totl_mnyx']) { // 전체 금액을 모두 입금 했을 때,
				$str .= "Phase3\n";
				$pdo->query("update `$tbl[order]` set `stat`=2, `date2`='$now' where `no`='$ord[no]'");
				ordStatLogw($ono, 2, 'Y');

				include_once $engine_dir.'/_engine/sms/sms_module.php';
				$sms_replace['buyer_name'] = $ord['buyer_name'];
				$sms_replace['ono'] = $ord['ono'];
				$sms_replace['pay_prc'] = number_format($ord['pay_prc']);
				SMS_send_case(3, $ord['buyer_cell']);
				SMS_send_case(18);

				if($cfg['partner_sms_config'] == 1 || $cfg['partner_sms_config'] == 2) {
					partnerSmsSend($ord['ono'], 18);
				}
			}
		}

		if($stat == 1 && $ord['stat'] != 1 && ($ord['stat'] > 1 && $ord['stat'] <= 5)) { // 취소
			$pdo->query("update `$tbl[order]` set `stat`=1, `date2`='0' where `no`='$ord[no]'");
			ordStatLogw($ono, 1, 'Y');

			$pdo->query("insert into $tbl[order_memo] (ono, content, reg_date) values ('$ono', 'PG사에 의한 가상계좌 자동 취소', '$now')");
		}
	}

	exit('0000');

?>