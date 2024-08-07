<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  출석체크 현황(달력) 출력
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/common.lib.php";
	include_once $engine_dir."/_engine/include/shop.lib.php";

	if($cfg['use_attend'] != 'Y') {
		$attend = $pdo->assoc("select * from $tbl[attend_new] where check_use='Y' and start_date <= '$now' and finish_date >= '$now' ORDER BY no DESC LIMIT 0, 1");
		$result = $pdo->assoc("select count(*) as cnt from $tbl[attend_list] where eno='$attend[no]' and member_no='$member[no]'");
		$total_prize = $pdo->row("select sum(prize_milage+prize_point) from $tbl[attend_list] where eno='$attend[no]' and member_no='$member[no]' and (prize_milage > 0 or prize_point > 0)");

		if(!$attend['no']) msg('출석체크 기능이 사용중이 아닙니다.', 'back');
	}

	memberOnly(1,"");
	$rURL = urlencode($this_url);

	if($_GET['cur_Yn']) {
		$cur_Yn = numberOnly(explode("-", $_GET['cur_Yn']));
		$cur_y = $cur_Yn[0];
		$cur_m = $cur_Yn[1];
	}

	//넘어오는 년월정보가 없으면 현재 년월을 초기화 시킨다.
	if(!$cur_y) $cur_y = date(Y);
	if(!$cur_m) $cur_m = date(m);

	//윤년
	$leap_year=false;
	if($cur_y%4==0) $leap_year=true;
	if($cur_y%100==0) $leap_year=false;
	if($cur_y%400==0) $leap_year=true;

	//해당 년월의 timestamp값을 구한다.(1일 0시0분0초)
	$tstamp = mktime(0,0,0,$cur_m,1,$cur_y);

	//해당 월의 총 날짜수를 구한다.
	$tot_days = date("t",$tstamp);

	//요일을 구한다.(0~6)
	$week = date("w",$tstamp);

	$day_end = false;
	$dayno = 0;


	// 총 출석 포인트
	if ($cfg['attendMP']=='M') {
		$_attend_tbl = $tbl[milage];
	} else {
		$_attend_tbl = $tbl[point];
	}
	$attendPoint = $pdo->assoc("SELECT sum(`amount`) as total_amount FROM `$_attend_tbl` WHERE `title` like '%출석%' and `ctype`='+' and `member_no`='$member[no]' group by `member_id`");


	/* +----------------------------------------------------------------------------------------------+
	' |  달력 리스트 생성
	' +----------------------------------------------------------------------------------------------+*/
	while(!$day_end) {
		$check_list .= "<tr>";

		for($j=0; $j<7; $j++) {

			if($dayno==0 && $week==$j) $dayno = 1;

			if($dayno>0 && ($dayno<=$tot_days)){

				$todaystamp = date("Y-m-d",mktime(0,0,0,$cur_m,$dayno,$cur_y));
				if($cfg['use_attend'] == 'Y') { // 구 출석체크
					$s_data = $pdo->assoc("
                        select * from {$tbl['attend_day']} where member_no='{$member['no']} and _date='$todaystamp'
                    ");
				} else {
					$s_data = $pdo->assoc("select count(*) as cnt
						from $tbl[attend_list] a inner join $tbl[attend_new] b on a.eno=b.no
						where a.member_no='$member[no]' and a.check_date='$todaystamp' ");
				}
				$s_data_yn = 0;
				if ($s_data[cnt]) $s_data_yn = 1;

				$Aclass = getDayClass($todaystamp, $s_data_yn);
				$colorstr = getDayColor($j, $dayno);

				$check_list .= "<td valign='top' $Aclass $Alink>$colorstr</td>";
				$dayno++;

			} else {
				$check_list .= "<td valign='top'>&nbsp;</td>";
			}
		}
		if($dayno>$tot_days)  $day_end = true;
		$check_list .= "</tr>";
	}

	common_header();

	//토,일요일을 색상으로 구분해준다.
	function getDayColor($day, $date) {
		$cstr = "";
		switch($day) {
			case(0) :
				$cstr = "<font color='FF7820'>$date</font>";
				break;
			default :
				$cstr = $date;
				break;
		}
		return $cstr;
	}

	function getDayClass($todaystamp, $s_data_yn="") {
		global $now,$Alink;
		$Alink = "";

		$str = ($s_data_yn)? "class='attenY'" : "class='attenN'";
		if($todaystamp==date("Y-m-d",$now) && $s_data_yn != '1') {
			$str = "class='attenR'";
			$Alink = "style='cursor:pointer;' onclick=\"$('#attendFrm').submit()\"";
		}



		if($todaystamp > date("Y-m-d",$now)) $str = "";
		return $str;
	}

	// 디자인 버전 점검 & 페이지 출력
	include_once $engine_dir."/_engine/common/skin_index.php";

?>