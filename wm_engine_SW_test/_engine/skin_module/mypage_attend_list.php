<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  출첵 리스트(달력)
	' +----------------------------------------------------------------------------------------------+*/

	if($cfg['use_attend'] == 'Y') { // 구 출석체크
		$_replace_code[$_file_name]['mypage_member_attend']=$member['attend'];
		$_replace_code[$_file_name]['mypage_attend_point']=number_format($attendPoint['total_amount']);
		$_replace_code[$_file_name]['mypage_attendNum']=$cfg['attendNum'];
		$_replace_code[$_file_name]['mypage_attendMilage']=number_format($cfg['attendMilage']);
		$_replace_code[$_file_name]['mypage_attendPoint']=number_format($cfg['attendMilage']);

		$_replace_code[$_file_name]['mypage_check_calendar']=$check_list;
	} else { // 출석체크 3.0
		$cnt = $pdo->row("select count(*) from $tbl[attend_new] where check_use='Y' and start_date <= '$now' and finish_date >= '$now'");
		if($cnt < 1) alert(__lang_attend_error_noevent__);
		else {
			
			$_tmp="";
			$res = $pdo->iterator("select * from $tbl[attend_new] where check_use='Y' and start_date <= '$now' and finish_date >= '$now' ORDER BY no ASC");
			$_line=getModuleContent("mypage_attend_reward_list");
            foreach ($res as $attend) {
				$my_attend_day = $pdo->assoc("select count(*) as cnt
						from $tbl[attend_list] where eno = '$attend[no]' and member_no = '$member[no]' ");
				$attend['check_cnt'] = $my_attend_day['cnt'];
				if($attend['prize_cno']){
					$attend['prize_cno'] = $pdo->row("select name from $tbl[coupon] where no='$attend[prize_cno]'");
				}
				$_tmp .= lineValues("mypage_attend_reward_list", $_line, $attend);

				//출석체크(구) 스킨 데이터
				$attend_prize = array();
				if($attend['prize_cno'] > 0) $attend_prize[] = __lang_attend_info_prizeCpn__;
				if($attend['prize_milage'] > 0) $attend_prize[] = __lang_attend_info_prizeMil__;
				if($attend['prize_point'] > 0) $attend_prize[] = __lang_attend_info_prizePts__;
				$attend_prize = implode('/', $attend_prize);

				$_replace_code[$_file_name]['mypage_member_attend'] = number_format($result['cnt']);
				$_replace_code[$_file_name]['mypage_attend_point'] = number_format($total_prize);
				$_replace_code[$_file_name]['mypage_attendNum'] = number_format($attend['complete_day']);
				$_replace_code[$_file_name]['mypage_attendMilage'] = number_format($attend['prize_milage']);
				$_replace_code[$_file_name]['mypage_attendPoint'] = number_format($attend['prize_point']);
				$_replace_code[$_file_name]['mypage_attend_type'] = $attend_prize;
				//출석체크(구) 스킨 데이터
			
			}
			$_tmp = listContentSetting($_tmp, $_line);
			$_replace_code[$_file_name]['mypage_attend_reward_list'] = $_tmp;
		}
	}

	$_replace_code[$_file_name]['mypage_this_year']=date("Y",$tstamp);
	$_replace_code[$_file_name]['mypage_this_month']=date("m",$tstamp);
	$_replace_code[$_file_name]['mypage_pre_year_url']=$PHP_SELF."?cur_Yn=".date("Y",strtotime("-1 year", $tstamp));
	$_replace_code[$_file_name]['mypage_next_year_url']=$PHP_SELF."?cur_Yn=".date("Y",strtotime("+1 year", $tstamp));
	$_replace_code[$_file_name]['mypage_pre_month_url']=$PHP_SELF."?cur_Yn=".date("Y-m",strtotime("-1 month", $tstamp));
	$_replace_code[$_file_name]['mypage_next_month_url']=$PHP_SELF."?cur_Yn=".date("Y-m",strtotime("+1 month", $tstamp));
	$_replace_code[$_file_name]['mypage_check_calendar']=$check_list;
	$_replace_code[$_file_name]['mypage_attend_form_start']="<form id='attendFrm' name=\"attendFrm\" method=\"post\" action=\"$root_url/main/exec.php\" target=\"hidden$now\">
<input type=\"hidden\" name=\"exec_file\" value=\"mypage/attend.exe.php\">
<input type=\"hidden\" name=\"_date\" value=\"".date("Y-m-d",$now)."\">
<input type=\"hidden\" name=\"member_no\" value=\"".$member[no]."\">
<input type=\"hidden\" name=\"attend_no\" value=\"".$attend['no']."\">

";
	$_replace_code[$_file_name]['mypage_attend_form_end']="</form>";

?>