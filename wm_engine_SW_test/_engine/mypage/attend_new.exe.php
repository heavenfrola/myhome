<?PHP

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/milage.lib.php";


	// 회원 로그인 페이지에서 인클루드
	$landing=" and check_type='1'";
	if($member_login) {
		$member = $pdo->assoc("select * from {$tbl['member']} where no='$_SESSION[member_no]'");
		$landing=" and check_type='2'";
	}

	$attend_msg = 0;
	$prize_msg = "";
	$res = $pdo->iterator("select * from {$tbl['attend_new']} where check_use='Y' and start_date <= '$now' and finish_date >= '$now' $landing order by no asc ");
	$date = addslashes(date('Y-m-d', $now));
    foreach ($res as $attend) {
		$attend_no = $attend['no'];


		//오늘 해당 출석체크에 출석체크 했을 경우
		if($pdo->row("select count(*) from {$tbl['attend_list']} where eno='$attend_no' and member_no='$member[no]' and check_date='$date'") > 0) {
			continue;
		}


		//데이터 세팅
		$prev_date = date('Y-m-d', strtotime('-1 days', strtotime($date)));
		$cnt = $pdo->row("select count(*) from {$tbl['attend_list']} where member_no='{$member['no']}' and eno='$attend_no'");
		$straight_cnt = $pdo->row("select straight_cnt from {$tbl['attend_list']} where member_no='{$member['no']}' and eno='$attend_no' and check_date='$prev_date'");
		$cnt++;
        if (!$straight_cnt) $straight_cnt = 0;
		$straight_cnt++;

		$prize = 0;
		$casql = "";
		$prize_cno = 0;
		$prize_milage = 0;
		$prize_point = 0;
		if($attend['event_type'] == 1 && $attend['complete_day'] == $cnt) $prize++; // 누적 참여횟수 충족
		if($attend['event_type'] == 2 && $attend['complete_day'] == $straight_cnt) $prize++; // 연속 참여쇳수 충족
		if($attend['event_type'] == 1 && $attend['repeat_type'] == 2 && $cnt%$attend['complete_day'] == 0) $prize++; // 누적 참여횟수 충족 연속지급
		if($attend['event_type'] == 2 && $attend['repeat_type'] == 2 && $straight_cnt > $attend['complete_day'] && $straight_cnt%$attend['complete_day'] == 0) $prize++; // 누적 참여횟수 충족 연속지급

		if($prize > 0) {
			if($attend['prize_cno'] > 0) { // 쿠폰지급
				$cpn = $pdo->assoc("select * from {$tbl['coupon']} where no='{$attend['prize_cno']}'");
				if(putCoupon($cpn, $member)) {
					$prize_cno = $attend['prize_cno'];
					$prize_msg = "- [$cpn[name]] ".__lang_attend_info_prizeCpn__."\n";
				}
			}

			if($cfg['milage_use'] == 1 && $attend['prize_milage'] > 0) { // 적립금 지급
				ctrlMilage('+', 3, $attend['prize_milage'], $member, __lang_attend_info_title__."($attend[name])");
				$prize_milage = $attend['prize_milage'];
				$prize_msg = "- ".sprintf(__lang_attend_info_prizeMil2__, $prize_milage)."\n";
			}

			if($cfg['point_use'] == 'Y' && $attend['prize_point'] > 0) { // 포인트 지급
				ctrlPoint($attend['prize_point'], 1, $member['no'], false, '+', 3, __lang_attend_info_title__."($attend[name])", '+');
				$prize_point = $attend['prize_point'];
				$prize_msg = "- ".sprintf(__lang_attend_info_prizePts2__, $prize_point)."\n";
			}
			$casql = ",prize_cnt=prize_cnt+1";
		}

		$pdo->query("
			insert into {$tbl['attend_list']}
				(eno, member_no, check_date, total_cnt, straight_cnt, reg_date, prize_cno, prize_milage, prize_point)
				values
				('$attend_no', '$member[no]', '$date', '$cnt', '$straight_cnt', '$now', '$prize_cno', '$prize_milage', '$prize_point')
		");
		$pdo->query("update {$tbl['attend_new']} set check_cnt=check_cnt+1 $casql where no='$attend_no'");
		$attend_msg++;
	}


	if(!$attend_msg){
		return;
	}else{
		$msg  = __lang_attend_info_checked__."\n";
		if($member_login) {
			if($prize_msg) alert(php2java(__lang_attend_info_prize__."\n".$prize_msg));
			return;
		}
		msg(php2java($msg.$prize_msg), 'reload', 'parent');
	}
?>