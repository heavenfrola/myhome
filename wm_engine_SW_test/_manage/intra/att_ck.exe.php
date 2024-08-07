<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  출석체크설정 처리
	' +----------------------------------------------------------------------------------------------+*/

	$today=date("Y-m-d", $now);
	$data = $pdo->assoc("select * from `$tbl[intra_day_check]` where `member_no`='$admin[no]' and `date`='$today' limit 1");

	$mode = numberOnly($_POST['mode']);
	$late_detail = addslashes($_POST['late_detail']);
	$re_modi = $_POST['re_modi'];

	if($cfg[intra_day_check] == "Y"){
		if($mode == 1){ // 출근
			if(!$data[no]){
				$late=(date("H:i", $now) > $cfg[intra_day_check_start]) ? "Y" : "N";
				if($late == "Y"){
					checkBlank($late_detail, "지각사유를 입력해주세요.");
				}
				$pdo->query("insert into `$tbl[intra_day_check]`(`member_no`, `date`, `stime`, `ip`, `late`, `re_modi`, `late_detail`) values('$admin[no]', '$today', '$now', '$_SERVER[REMOTE_ADDR]', '$late', '$re_modi', '$late_detail')");
			}else msg("이미 출근체크가 완료되었습니다.");
		}elseif($mode == 2){ // 퇴근
			if(!$data[no]) msg("출석체크를 먼저 하셔야 합니다");
			if($data[etime]) msg("이미 퇴근하셨습니다");
			$pdo->query("update `$tbl[intra_day_check]` set `etime`='$now' where `no`='$data[no]'");
		}
	}

	msg("","reload","parent");

?>