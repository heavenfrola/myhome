<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  생일자 자동쿠폰 발송
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir.'/_engine/include/common.lib.php';

	$use_check = $pdo->row("select use_check from $tbl[sms_case] where `case`=16");
	if($use_check == 'Y') {
		include_once $engine_dir."/_engine/sms/sms_module.php";
	}

	$today = date('Y-m-d');
	$success = 0;

	$res = $pdo->iterator("select * from `$tbl[coupon]` where (`rdate_type`=1 or (`rdate_type`=2 and `rstart_date` <= '$today' and `rfinish_date` >= '$today')) and `is_type`='A' and is_birth='Y'");
	if($res == false) {
		exit('0');
	}

	if(!$cfg['auto_birth_cpn_type']) {
		$cfg['auto_birth_cpn_type'] = 1;
		$cfg['auto_birth_cpn_date'] = 0;
	}

	$base_date = $now;
	$year = date('Y', $base_date);
	$members = array();

	if($cfg['auto_birth_cpn_type'] == 1) {
		$base_date = strtotime('+ '.$cfg['auto_birth_cpn_date'].' days');
	}

	if($cfg['auto_birth_cpn_type'] == 2) {
		if(date('j') != $cfg['auto_birth_cpn_date']) exit('0');
	}

    foreach ($res as $cpn) {
		$w = '';
		if($cpn['down_type'] == "B") { // 권한체크
			$w .= $cpn['down_gradeonly'] == "Y" ? " and `level`='$cpn[down_grade]'" : " and `level` <= '$cpn[down_grade]'";
		}

		if($cfg['join_birth_use'] == 'Y') {
			if($cfg['auto_birth_cpn_type'] == 2) {
				$birth = date('-m-', $base_date);
				$w .= " and substr(birth, 5, 4) = '$birth'";
			} else {
				$birth = date('m-d', $base_date);
				$w .= " and substr(birth, 6, 5) = '$birth'";
			}
		} else {
			if($cfg['auto_birth_cpn_type'] == 2) {
				$birth = date('m', $base_date);
				$w .= " and substr(jumin, 3, 2) = '$birth'";
			} else {
				$birth = date('md', $base_date);
				$w .= " and substr(jumin, 3, 4) = '$birth'";
			}
		}

		$mres = $pdo->iterator("select no, name, member_id, cell, level, sms, email from `$tbl[member]` where birthcpn < '$year' $w and withdraw in ('N', 'D1')");
        foreach ($mres as $mem) {
			if(putCoupon($cpn, $mem) == true) {
				$members[] = $mem['no'];

/*
				if($_REQUEST['email_use'] == "Y") {
					$_mstr['회원이름'] = $mem['name'];
					include $engine_dir.'/_engine/include/mail.lib.php';
					sendMailContent(22, $mem['name'], $mem['email']);
				}
*/
				if($use_check == 'Y' && $mem['sms'] == 'Y') {
					$sms_replace['name'] = $mem['name'];
					$sms_replace['member_id'] = $mem['member_id'];
					SMS_send_case(16, $mem['cell']);
				}
			}
		}
	}

	// 1년에 생일쿠폰 한번만 받을수 있도록 처리
	foreach($members as $val) {
		$pdo->query("update $tbl[member] set birthcpn='$year' where no='$val'");
	}

	$success = count($members);
	exit("$success");

?>