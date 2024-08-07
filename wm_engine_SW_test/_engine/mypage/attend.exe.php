<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  출석체크
	' +----------------------------------------------------------------------------------------------+*/

	if($cfg['use_attend'] != 'Y') {
		include $engine_dir.'/_engine/mypage/attend_new.exe.php';
		return;
	}

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/milage.lib.php";
	memberOnly();
	checkBasic();
	if(empty($member_no) && $member_no != $member[no]) msg("정상적인 접속이 아닙니다.");
	if(!$cfg[attendMilage] || !$cfg[attendNum]) msg("출석체크 기능이 정상적으로 설정되어있지 않습니다");
	if($_date) {
		$_c = $pdo->assoc("select * from `$tbl[attend_day]` where `member_no`='$member[no]' and `_date`='$_date'");
		if ($_c[no]) msg("정상적인 출석체크가 아닙니다. 부정적인 출석체크 적발시 기존 보유 적립금 전부 회수 됩니다.");
		$sql1="INSERT INTO `$tbl[attend_day]` ( `member_no` , `_date`) VALUES ('$member[no]','$_date')";
		if ($cfg[attendType]=='1') {
			if ($cfg['all_attend_date']=='Y') {
				$sql2="update `$tbl[member]` set `attend`=`attend`+1 where `no`='$member[no]'";
				$rm = "출석체크 되었습니다.";
			} else {
				if(strtotime($_date) >= strtotime($cfg['attend_start']) && strtotime($_date) <= strtotime($cfg['attend_finish'])) {
					$sql2="update `$tbl[member]` set `attend`=`attend`+1 where `no`='$member[no]'";
					$rm = "출석체크 되었습니다.";
				} else {
					$rm = "출석체크기간이 아닙니다.";
				}
			}
		} else {
			if ($cfg['all_attend_date']=='Y') {
				$sql3="select * from `$tbl[attend_day]` where `member_no`='$member[no]' and `charge`='N' order by `_date`";
			} else {
				$sql3="select * from `$tbl[attend_day]` where `member_no`='$member[no]' and `charge`='N' and `_date`>='$cfg[attend_start]' order by `_date`";
			}

			$res3=$pdo->iterator($sql3);
			$attend_ea=0;
			$cnt=0;
            foreach ($res3 as $attenddata) {

				if($cnt==0){
					$_attend_chk = $attenddata[_date] ;
					$attend_ea++;
				} else {
					$_attend_chk = date("Y-m-d",strtotime("+1 day",strtotime($_attend_chk)));

					if($attenddata[_date] == $_attend_chk ) {
						$attend_ea++;
					} else {
						$attend_ea=0;
						$_attend_chk = $attenddata[_date];
					}
				}
				if (date("Y-m-d",$now) == $_attend_chk) break;
				$cnt++;
			}
			$_yester = date("Y-m-d",strtotime("-1 day",$now));
			$_yester_attend = $pdo->assoc("select * from `$tbl[attend_day]` where `member_no`='$member[no]' and `_date`='$_yester'");
			if (!$_yester_attend[no]) $attend_ea = 0;

			if ($attend_ea > 0) {
				//$_attend = $attend_ea+1;
				//$sql2="update `$tbl[member]` set `attend`='$_attend' where `no`='$member[no]'";
				$rm = "연속 출석체크 하셨습니다.";
			} else {
				//$sql2="update `$tbl[member]` set `attend`='1' where `no`='$member[no]'";
				$pdo->query("update `$tbl[attend_day]` set `charge`='Y' where `member_no`='$member[no]'");
				$rm = "출석체크 되었습니다.";
			}
		}
		$pdo->query($sql1);
		$_attend = $pdo->row("select count(*) from `$tbl[attend_day]` where `member_no`='$member[no]' and `charge`='N'");
		$sql2="update `$tbl[member]` set `attend`='$_attend' where `no`='$member[no]'";
		$pdo->query($sql2);
		$attend_member=get_info($tbl['member'], "no", $member[no]);
		if ($attend_member[attend]%$cfg[attendNum] == 0 || $attend_member[attend] > $cfg[attendNum]) {
			//msg ("10번출석했음");
			if($attend_member[attend] > $cfg[attendNum]){
				$_attend_ = $attend_member[attend] - $cfg[attendNum];
				$pdo->query("update `$tbl[member]` set `attend`='$_attend_' where `no`='$member[no]'");
				$_attend_chk_ = date("Y-m-d",strtotime("-".$_attend_." day",$now));
				$pdo->query("update `$tbl[attend_day]` set `charge`='Y' where `member_no`='$member[no]' and  `_date` <='$_attend_chk_'");
				$attendMilage = $cfg[attendMilage] * (ceil($attend_member[attend]/$cfg[attendNum])-1);
				if ($cfg['attendMP']=='M') {
					ctrlMilage("+",3,$attendMilage,$attend_member,"출석체크이벤트");
				} else {
					ctrlPoint($attendMilage,4,$attend_member[no],'',"+",'',"출석체크이벤트");
				}
			} else {
				$pdo->query("update `$tbl[attend_day]` set `charge`='Y' where `member_no`='$member[no]'");
				if ($cfg['attendMP']=='M') {
					ctrlMilage("+",3,$cfg[attendMilage],$attend_member,"출석체크이벤트");
				} else {
					ctrlPoint($cfg[attendMilage],4,$attend_member[no],'',"+",'',"출석체크이벤트");
				}
			}
			$rm = $cfg[attendNum]."번 출석도장으로으로 포인트가 ".$cfg[attendMilage]."포인트 지급되었습니다.";
		}
	}
	msg($rm,"reload","parent");
?>