<?PHP
	/* +----------------------------------------------------------------------------------------------+
	' |  회원분석
	' +----------------------------------------------------------------------------------------------+*/

// 오늘 날자
$today = date('Y-m-d');

// 검색프리셋
$spno = numberOnly($_GET['spno']);
unset($_GET['spno']);
if ($spno) {
    $spdata = $pdo->assoc("select * from {$tbl['search_preset']} where no='$spno'");
    if ($spdata['querystring']) {
        $_GET = array_merge($_GET, json_decode($spdata['querystring'], true));
        if ($_GET['setterm'] == 'term') {
            $firstDate = new DateTime($_GET['start_date']);
            $secondDate = new DateTime($_GET['finish_date']);
            $intvl = $firstDate->diff($secondDate);
            $diffDay = $intvl->days;

            $_GET['start_date'] = date('Y-m-d', strtotime($today . ' -' . $diffDay . 'days'));
            $_GET['finish_date'] = $today;
        }
    }
}

// 검색 데이터
$start_date = addslashes($_GET['start_date']);
$start_date = preg_replace('/[^0-9-]/', '', $start_date);
$finish_date = addslashes($_GET['finish_date']);
$finish_date = preg_replace('/[^0-9-]/', '', $finish_date);

// 오늘 날자
$explodeDate = explode('-', $today);
$todayY = $explodeDate[0];
$todayM = $explodeDate[1];
$todayD = $explodeDate[2];

// 어제 날짜
$yesterDay = date('Y-m-d', strtotime($today . ' -1days'));

// 최근 7일
$thisWeekStartDay = date('Y-m-d', strtotime($today . ' -6days'));
$thisWeekEndDay = $today;

// 최근 30일
$last30StartDay = date('Y-m-d', strtotime($today . ' -29days'));
$last30EndDay = $today;

// 최근 2개월
$last2mStartDay = date('Y-m-d', strtotime($today . ' -2month'));
$last2mEndDay = $today;

// 이번달 (1일 ~ 현재)
$thisMonthStartDay = $todayY . '-' . $todayM . '-01';
$thisMonthEndDay = $today;

// 지난달
$prev_month = strtotime('-1 month', strtotime($todayY . '-' . $todayM . '-01')); // 이번달 01일부터 계산
$lastMonthStartDay = date('Y-m-01', $prev_month);
$lastMonthEndDay = date('Y-m-t', $prev_month);

// 최근 3개월
$prev_month = strtotime('-3 month', strtotime($todayY . '-' . $todayM . '-01'));
$last3MonthStartDay = date('Y-m-d', strtotime($today . ' -3month'));
$last3MonthEndDay = $today;

// 값이 없거나 첫 화면은 '최근 7일'로 기본 세팅
if ($start_date == '') $start_date = $last3MonthStartDay;
if ($finish_date == '') $finish_date = $today;

// 최대 1년 검색 기간이 넘으면 최근 3개월로 세팅
$firstDate = new DateTime($start_date);
$secondDate = new DateTime($finish_date);
$intvl = $firstDate->diff($secondDate);
$diffDay = $intvl->days;

if ($diffDay > 365) {
    $start_date = $last3MonthStartDay;
    $finish_date = $today;
}

// 검색 데이터 explode
$ex_start_date = explode('-', $start_date);
$start_dateY = $ex_start_date[0];
$start_dateM = $ex_start_date[1];
$start_dateD = $ex_start_date[2];

$ex_finish_date = explode('-', $finish_date);
$finish_dateY = $ex_finish_date[0];
$finish_dateM = $ex_finish_date[1];
$finish_dateD = $ex_finish_date[2];

// 간편검색 array
$easySearchBtn = array(
    '오늘' => array($today, $today),
    '어제' => array($yesterDay, $yesterDay),
    '7일' => array($thisWeekStartDay, $thisWeekEndDay),
    '30일' => array($last30StartDay, $last30EndDay),
    '2개월' => array($last2mStartDay, $last2mEndDay),
    '3개월' => array($last3MonthStartDay, $last3MonthEndDay),
    '이번달 (1일~현재)' => array($thisMonthStartDay, $thisMonthEndDay),
    '지난달 (1일~말일)' => array($lastMonthStartDay, $lastMonthEndDay),
);

	$_title = array();

	// 추가항목이 존재하는지 검사
	if(@is_file($root_dir."/_config/member.php")){
		$_mbr_add_info=array();
		include_once $root_dir."/_config/member.php";
		foreach($_mbr_add_info as $key=>$val){
			$_addtype=$_mbr_add_info[$key]['type'];
			if($_addtype != "checkbox" && $_addtype != "radio") continue;
			$_title['add_info'.$key] = $_mbr_add_info[$key]['name'];
		}
	}

	/* +----------------------------------------------------------------------------------------------+
	' |  통계
	' +----------------------------------------------------------------------------------------------+*/
$weekday_kor = array('일요일', '월요일', '화요일', '수요일', '목요일', '금요일', '토요일');
$whereDate = " && (reg_date >= '" . strtotime($start_date) . "' && reg_date <= '" . strtotime($finish_date . '23:59:59') . "')";

// 나이대별
$r_age = array();
if ($cfg['join_jumin_use'] != 'N') {
    $year = date('y');
    $year2 += 100;
} elseif ($cfg['join_jumin_use'] == 'N' && $cfg['join_birth_use'] == 'Y') {
    $year = date('Y');
    $year2 += 100;
}
if ($cfg['join_jumin_use'] != 'N') {
    $f = "(floor(if(left(jumin, 2) < $year, $year - left(jumin, 2), $year2 - left(jumin, 2))/10)+1)*10";
} elseif ($cfg['join_jumin_use'] == 'N' && $cfg['join_birth_use'] == 'Y') {
    $f = "(floor(if(left(birth, 4) <= $year, $year - left(birth, 4), $year2 - left(birth, 4))/10))*10";
}

$res = $pdo->iterator("select $f as age, count(*) as cnt from {$tbl['member']} where birth != '' && birth != '0000-00-00' $whereDate group by age order by null");
if (!empty($res)) {
    foreach ($res as $data) {
        $r_age[$data['age'] . '대'] = $data['cnt'];
    }
    ksort($r_age);
}

// 성별
$r_sex = array();
$f_s = "sex as sex";
if ($cfg['join_jumin_use'] != 'N') {
    $_sex = array(1 => '남', 0 => '여');
    $f_s = "(substr(jumin,8,1)%2) as sex";
} elseif ($cfg['join_jumin_use'] == 'N' && $cfg['join_sex_use'] == 'Y') {
    $_sex = array('남' => '남', '여' => '여');
    $f_s = "sex as sex";
}
if ($cfg['join_jumin_use'] != 'N' || ($cfg['join_jumin_use'] == 'N' && $cfg['join_sex_use'] == 'Y')) {
    $w = ($cfg['join_jumin_use'] != 'N') ? " and jumin!=''" : " and sex!=''";
}
$r_sex_color = array('#759EDF', '#DF7593'); // 성별 pie 색상
$res = $pdo->iterator("select $f_s, count(*) as cnt from {$tbl['member']} where 1 $w $whereDate group by sex order by null");
if (!empty($res)) {
    foreach ($res as $data) {
        if ($data['sex'] == '') continue;
        $r_sex[$data['sex']] = $data['cnt'];
    }
    ksort($r_sex);
}

// 지역별
$r_local = array();
$res = $pdo->iterator("select (left(trim(addr1), {$cfg['member_local_cut']} )) as local, count(*) as cnt from {$tbl['member']} where 1 $whereDate group by local order by cnt desc limit 13");
$local_cnt_chk = 1;
if (!empty($res)) {
    foreach ($res as $data) {
        if ($local_cnt_chk > 13) break;
        array_push($r_local, array('subject' => $data['local'], 'value_cnt' => $data['cnt']));
        $local_cnt_chk++;
    }
}

// 가입월, 가입일 초기화
$r_joinMonth = array(); // 가입월
$r_joinDay = array(); // 가입일
for ($init_y = $start_dateY; $init_y <= $finish_dateY; $init_y++) {
    $init_m_start = $init_y == $start_dateY ? $start_dateM : 1;
    $init_m_end = $init_y == $finish_dateY ? $finish_dateM : 12;
    for ($init_m = $init_m_start; $init_m <= $init_m_end; $init_m++) {
        $init_m = sprintf('%02d', $init_m);
        $k_m = $init_y . '년 ' . $init_m . '월';
        $r_joinMonth[$k_m] = 0; // 가입월 삽입
        $init_d_start = $init_m == $start_dateM ? $start_dateD : 1;
        $init_d_end = $init_m == $finish_dateM ? $finish_dateD : date('t', strtotime($init_y . '-' . $init_m));
        for ($init_d = $init_d_start; $init_d <= $init_d_end; $init_d++) {
            $init_d = sprintf('%02d', $init_d);
            $dateset = $init_y . '-' . $init_m . '-' . $init_d;
            $k_d = $init_y . '년 ' . $init_m . '월 ' . $init_d . '일 ' . $weekday_kor[date('w',strtotime($dateset))];
            $r_joinDay[$k_d] = 0; // 가입일 삽입
        }
    }
}

// 가입월, 가입월 평균
$month_avg = array(); // 가입월 평균 배열
$month_avg_tmp = array(); // 가입월 평균 임시 배열
$start_avg = strtotime('-3 month',strtotime($start_date));
$s_avg = explode('-', date('Y-m-d', $start_avg));
$e_avg = explode('-', date('Y-m-d', strtotime($finish_date)));
$init_s = 0; // start 일자로부터 +N월
// 1. 총 개월수 만큼 tmp 배열 생성
for ($s_y = $s_avg[0]; $s_y <= $e_avg[0]; $s_y++) {
    if ($s_y == $s_avg[0]) {
        $start_m = $s_avg[1];
        $end_m = 12;
    }
    if ($s_y == $e_avg[0]) {
        $start_m = 1;
        $end_m = $e_avg[1];
    }
    for ($s_m = $start_m; $s_m <= $end_m; $s_m++) {
        $month_avg_tmp[date('Y-m', strtotime($init_s.'months', strtotime(date('Y-m-d', $start_avg))))] = 0;
        array_push($month_avg, 0);
        $init_s++;
    }
}

// 2. month_avg_tmp, 데이터 세팅
$whereDate_3Mago = " && (reg_date >= '" . $start_avg . "' && reg_date <= '" . strtotime($finish_date . '23:59:59') . "')";
$res = $pdo->iterator("select count(*) as cnt, from_unixtime(reg_date, '%Y-%m') as dt from {$tbl['member']} where 1 $whereDate_3Mago group by dt order by null");
if (!empty($res)) {
    foreach ($res as $data) {
        $month_avg_tmp[$data['dt']] = $data['cnt']; // 평균 tmp 세팅
        if ($data['dt'] < date('Y-m', strtotime($start_date))) continue; // 실제 데이터 세팅은 start_date 부터 세팅
        $ex = explode('-', $data['dt']);
        $k = $ex[0] . '년 ' . $ex[1] . '월';
        $r_joinMonth[$k] = $data['cnt']; // 가입월별 데이터
    }
}

// 3. 평균 구하기
for ($chkCnt = 0; $chkCnt < count($month_avg); $chkCnt++) {
    $month_avg[$chkCnt] = round(array_sum(array_slice($month_avg_tmp, $chkCnt, 3))/3);
}
// 4. 검색기간의 첫번째 데이터만 다시 출력. 월단위의 경우 1일부터 뽑히기 때문에 시작일부터 데이터 나오도록 하기 위해서
$start_search = explode('-', $start_date);
$r_joinMonth[$start_search[0].'년 ' .$start_search[1].'월'] = $pdo->row("select count(*) as cnt from {$tbl['member']} where 1 && (reg_date >= '" . strtotime($start_date) . "' && reg_date <= '" . strtotime(date('Y-m-t', strtotime($start_date)) . '23:59:59') . "')");

// 가입일
$day_avg = array(); // 가입일 평균 배열
$day_avg_tmp = array(); // 가입일 평균 임시 배열
$sd = date('Y-m-d', strtotime('-7 days',strtotime($start_date)));
$ed = $finish_date;
$from = new DateTime($sd);
$to = new DateTime($ed);
$intvl = $from->diff($to);
$int_e = $intvl->days; // 날짜 차이
// 1. 총 일수 만큼 tmp 배열 생성
for ($int_s = 0; $int_s < $int_e; $int_s++) {
    $day_avg_tmp[date('Y-m-d', strtotime($int_s.'days', strtotime('-7 days',strtotime($start_date))))] = 0;
    array_push($day_avg, 0);
}

// 2. day_avg_tmp, 데이터 세팅
$res = $pdo->iterator("select count(*) as cnt, from_unixtime(reg_date , '%Y-%m-%d') as dt from {$tbl['member']} where 1 && (reg_date >= '" . strtotime('-7 days',strtotime($start_date)) . "' && reg_date <= '" . strtotime($finish_date . '23:59:59') . "') group by dt order by null");
if (!empty($res)) {
    foreach ($res as $data) {
        $day_avg_tmp[$data['dt']] = $data['cnt']; // 평균 tmp 세팅
        if ($data['dt'] < date('Y-m-d', strtotime($start_date))) continue; //실제값은 앞에 3개 빼고 값이고,
        $ex = explode('-', $data['dt']);
        $k = $ex[0] . '년 ' . $ex[1] . '월 ' . $ex[2] . '일 ' . $weekday_kor[date('w', strtotime($data['dt']))];
        $r_joinDay[$k] = $data['cnt']; // 가입일별 데이터
    }
}

// 3. 평균 구하기
for ($chkCnt = 0; $chkCnt < count($day_avg); $chkCnt++) {
    $day_avg[$chkCnt] = round(array_sum(array_slice($day_avg_tmp, $chkCnt, 7))/7);
}

// 가입요일
$r_joinWeek = array();
$res = $pdo->iterator("select count(*) as cnt, from_unixtime(reg_date , '%w') as dt from {$tbl['member']} where 1 $whereDate group by dt order by null");
foreach ($weekday_kor as $wk => $wv) {
    $r_joinWeek[$wv] = 0;
}
if (!empty($res)) {
    foreach ($res as $data) {
        $r_joinWeek[$weekday_kor[$data['dt']]] = $data['cnt'];
    }
}

// 추가항목
$r_add = array();
foreach ($_title as $title => $v2) {
    $sql = "select $title as fname, count(*) as cnt from {$tbl['member']} where 1 $whereDate group by fname order by null";
    if (fieldExist($tbl['member'], $title)) {
        $_num = str_replace('add_info', '', $title);
        $_addarr = $_mbr_add_info[$_num]['text'];
        if (@count($_addarr)) {
            $r_add[$title]['title'] = trim($_mbr_add_info[$_num]['name']);
            foreach ($_addarr as $infoKey => $infoVal) {
                $r_add[$title]['data']['name'][$infoKey] = trim($_addarr[$infoKey]);
                $r_add[$title]['data']['total'][$infoKey] = 0;
            }
            $res = $pdo->iterator($sql);
            foreach ($res as $data) {
                $data['name'] = stripslashes($data['fname']);
                if ($data['name'] === '') continue;
                $_add_names = explode('@', preg_replace('/^@|@$/', '', $data['fname']));
                foreach ($_add_names as $val) {
                    if ($val === '' || ! trim($_addarr[$val])) continue;
                    $r_add[$title]['data']['name'][$val] = trim($_addarr[$val]);
                    $r_add[$title]['data']['total'][$val] += $data['cnt'];
                }
            }
        }
    }
}
?>

<link rel="stylesheet" href="<?=$engine_url?>/_manage/css/log.css"/>
<script src="<?=$engine_url?>/_engine/common/chart/chart.js"></script>
<script src="<?=$engine_url?>/_engine/common/chart/chartjs-function.js"></script>
<script src="<?=$engine_url?>/_engine/common/chart/chartjs-plugin-datalabels.js"></script>
<script src="<?=$engine_url?>/_engine/common/chart/chartjs-chart-treemap.js"></script>
<script type="text/javascript">
    // 막대그래프 기본 옵션 (성별, 지역별 제외)
    function chartOptions() {
        return {
            type: 'bar',
            data: {
                labels: [],
                datasets: [
                    {
                        type: 'bar',
                        label: '가입자수',
                        data: [],
                        borderRadius: 30,
                        backgroundColor: '#7FCFF3',
                        maxBarThickness: 8,
                        sort: 1,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
                plugins: {
                    legend: {
                        display: false,
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 8
                        },
                    },
                    title: {
                        display: true,
                        text: '',
                        align: 'start',
                        color: '#000',
                        padding: {
                            bottom: 30
                        },
                        font: {
                            size: 15,
                            weight: 'bold'
                        },
                    },
                    tooltip: {
                        enabled: false,
                        external: externaltooltip,
                        position: 'custom',
                        callbacks: {
                            title: function (tooltipItems) {
                                return tooltipItems[0].label;
                            },
                            label: function (tooltipItems) {
                                return [tooltipItems.dataset.label, tooltipItems.formattedValue];
                            },
                        },
                    },
                },
                scales: {
                    x: {
                        display: true,
                        grid: {
                            display: false,
                        },
                        ticks: {
                            color: '#999999',
                            callback: function(v, i) {
                                let labelText = this.getLabelForValue(v);
                                if (labelText.length > 7) {
                                    labelText = labelText.substr(0, 7) + '...';
                                }
                                return labelText;
                            },
                        },
                    },
                    y: {
                        display: true,
                        min: 0,
                        afterFit: function (scaleInstance) {
                            scaleInstance.width = 50;
                        },
                        ticks: {
                            color: '#999999',
                        },
                    }
                },
            },
        }
    }

    // 데이터 없으면 'nodatas' class 추가
    function noDataAddClass(id, op) {
        var nodata_check = 'N'; // 데이터 없음. N없음 Y있음

        if (id === 'chartLocal') { // 지역별은 length로만 체크
            if (op.data.datasets[0].data.length > 0) {
                nodata_check = 'Y';
            }
        } else {
            var optionData = op.data.datasets[0].data;
            if (id === 'chartMonth' || id === 'chartDay') {
                optionData = op.data.datasets[1].data;
            }
            for (var i = 0; i < optionData.length; i++) {
                if (optionData[i] > 0) {
                    nodata_check = 'Y';
                    break;
                }
            }
        }

        if (nodata_check === 'N') {
            $('#'+id).parent().addClass('nodatas');
            op.options.plugins.tooltip.external = ''; // 툴팁 삭제
        }
    }
</script>

<div class="box_title first"><h2 class="title">회원분석</h2></div>
<form name="memberAnalysisFrm" id="memberAnalysisFrm" onsubmit="return chkDateTerm();">
    <input type="hidden" name="body" value="<?=$body?>">
    <table class="tbl_row box_search">
        <colgroup>
            <col style="width:150px">
            <col>
        </colgroup>
        <tr>
            <th scope="row">기간</th>
            <td>
                <input type="text" name="start_date" id="start_date" value="<?=$start_date?>" size="10" class="input datepicker" readonly>
                ~
                <input type="text" name="finish_date" id="finish_date" value="<?=$finish_date?>" size="10" class="input datepicker" readonly>
                <?php
                foreach ($easySearchBtn as $k => $v) {
                    $class_on = ($start_date == $v[0] && $finish_date == $v[1]) ? 'on' : '';
                    ?>
                    <span class="box_btn_d <?=$class_on?>"><input type="button" value="<?=$k?>" onclick="searchDate('<?=$v[0]?>', '<?=$v[1]?>');"></span>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <th scope="row">단축검색</th>
            <td>
                <?php
                $preset_menu = 'memAnalysis';
                $sp_count = $pdo->row("select count(*) from {$tbl['search_preset']} where menu='$preset_menu'");
                if ($admin['level'] < 4) {
                    ?>
                    <ul class="list_info" style="display: none;">
                        <li>자주 검색하는 기간은 [#단축검색등록]을 통해 편하게 검색할 수 있습니다.</li>
                    </ul>
                    <ul class="quick_search left">
                        <?php
                        include_once $engine_dir . "/_manage/config/quicksearch.inc.php"; ?>
                    </ul>
                <?php } ?>
            </td>
        </tr>
    </table>
    <div class="box_bottom">
        <span class="box_btn blue"><input type="submit" value="검색" id="search"></span>
        <span class="box_btn"><input type="button" value="초기화" onclick="location.href='?body=member@member_analysis'"></span>
        <span class="box_btn quicksearch" style="display: inline;"><a onclick="memAntviewQuickSearch('memberAnalysisFrm', 'memAnalysis');">#단축검색등록</a></span>
    </div>
</form>

<div class="box_middle4 memanalysisBox">
    <div class="memanalysis_row">
        <div class="memanalysis_row_box">
            <canvas id="chartMonth"></canvas>
            <div class="nodataTextBox">
                <span class="icon">가입한 회원이 없습니다.</span>
            </div>
        </div>

        <div class="memanalysis_row_box">
            <canvas id="chartDay"></canvas>
            <div class="nodataTextBox">
                <span class="icon">가입한 회원이 없습니다.</span>
            </div>
        </div>

        <div class="memanalysis_row_box">
            <canvas id="chartWeek"></canvas>
            <div class="nodataTextBox">
                <span class="icon">가입한 회원이 없습니다.</span>
            </div>
        </div>
    </div>

    <div class="memanalysis_row">
        <div class="memanalysis_row_box">
            <canvas id="chartAge"></canvas>
            <div class="nodataTextBox">
                <span class="icon">나이를 선택한 회원이 없습니다.</span>
            </div>
        </div>

        <div class="memanalysis_row_box">
            <canvas id="chartSex"></canvas>
            <div class="nodataTextBox">
                <span class="icon">성별을 선택한 회원이 없습니다.</span>
            </div>
        </div>

        <div class="memanalysis_row_box">
            <canvas id="chartLocal"></canvas>
            <div class="nodataTextBox">
                <span class="icon">지역을 선택한 회원이 없습니다.</span>
            </div>
        </div>
    </div>

    <?php
    $r_add_count = 1; // 추가항목 개수 카운트
    foreach ($r_add as $addK => $addV) {
        $chartDiv_s = ''; // 한줄 div tag open
        $chartDiv_e = ''; // 한줄 div tag close
        $chartColor = '#7FCFF3'; // 하늘색
        if ($r_add_count % 3 == 1) $chartDiv_s = '<div class="memanalysis_row">';
        if ($r_add_count % 3 == 2) $chartColor = '#759EDF'; // 파란색
        if ($r_add_count % 3 == 0 || $r_add_count == count($r_add)) $chartDiv_e = '</div>';
        echo $chartDiv_s;
        ?>
        <div class="memanalysis_row_box">
            <canvas id="chart_<?=$addK?>"></canvas>
            <div class="nodataTextBox">
                <span class="icon"><?=$addV['title']?>을 선택한 회원이 없습니다.</span>
            </div>
        </div>

        <?php if ($r_add_count  == count($r_add) && $r_add_count % 3 == 1) { // start 한개만 있을때 하나 더 추가 ?>
        <div class="memanalysis_row_box">
            <canvas></canvas>
        </div>
        <?php } ?>

        <script type="text/javascript">
            let op_<?=$addK?> = chartOptions();
            op_<?=$addK?>.data.labels = <?=json_encode(array_values($addV['data']['name']))?>;
            op_<?=$addK?>.data.datasets[0].data = <?=json_encode(array_values($addV['data']['total']))?>;
            op_<?=$addK?>.data.datasets[0].backgroundColor = '<?=$chartColor?>';
            op_<?=$addK?>.options.plugins.title.text = '<?=$addV['title']?>';
            let chart_<?=$addK?> = chartMake('chart_<?=$addK?>', op_<?=$addK?>);

            noDataAddClass('chart_<?=$addK?>', op_<?=$addK?>);
        </script>
        <?php
        echo $chartDiv_e;
        $r_add_count++;
    }
    ?>
</div>
<div class="box_middle2 left">
    <ul class="list_info">
        <li>가입된 회원수가 많은 경우 조회시간이 길어질 수 있습니다.</li>
        <li>분석 로딩이 잘 되지 않을 경우 <a href="#" onclick="goMywisa('?body=customer@cs_reg'); return false;">[고객센터]</a> 문의 글로 접수 바랍니다.
        </li>
    </ul>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        // 단축검색 없으면 단축검색 info 출력
        if ($('.quick_search')[0].children.length === 0) {
            $('.list_info').css('display', 'block');
        }
    });

    // 나이대별
    let age_op = chartOptions();
    age_op.data.labels = <?=json_encode(array_keys($r_age))?>;
    age_op.data.datasets[0].data = <?=json_encode(array_values($r_age))?>;
    age_op.data.datasets[0].backgroundColor = '#759EDF';
    age_op.options.plugins.title.text = '나이대별';
    let chartAge = chartMake('chartAge', age_op);
    noDataAddClass('chartAge', age_op);

    // 월별
    let month_op = chartOptions();
    month_op.data.labels = <?=json_encode(array_keys($r_joinMonth))?>;
    month_op.data.datasets[0].data = <?=json_encode(array_values($r_joinMonth))?>;
    month_op.data.datasets.unshift({
        type: 'line',
        label: '지난 3개월 평균',
        data: <?=json_encode($month_avg)?>,
        borderColor: '#DFE1E6',
        backgroundColor: '#DFE1E6',
        pointRadius: 2,
        sort: 2,
    });
    month_op.options.plugins.title.text = '월별';
    month_op.options.plugins.legend.reverse = true;
    month_op.options.plugins.tooltip.itemSort = function (a, b) {
        return a.dataset.sort - b.dataset.sort;
    };
    month_op.options.scales.x.ticks.callback = function (v, i) {
        let ex = this.getLabelForValue(v).split(' ');
        return ex[1];
    };
    let chartMonth = chartMake('chartMonth', month_op);
    noDataAddClass('chartMonth', month_op);

    // 일별
    let day_op = chartOptions();
    day_op.data.labels = <?=json_encode(array_keys($r_joinDay))?>;
    day_op.data.datasets[0].data = <?=json_encode(array_values($r_joinDay))?>;
    day_op.data.datasets[0].backgroundColor = '#759EDF';
    day_op.data.datasets.unshift({
        type: 'line',
        label: '지난 7일 평균',
        data: <?=json_encode($day_avg)?>,
        borderColor: '#DFE1E6',
        backgroundColor: '#DFE1E6',
        pointRadius: 2,
        sort: 2,
    });
    day_op.options.plugins.title.text = '일별';
    day_op.options.plugins.legend.reverse = true;
    day_op.options.plugins.tooltip.itemSort = function (a, b) {
        return a.dataset.sort - b.dataset.sort;
    };
    day_op.options.scales.x.ticks.callback = function (v, i) {
        let ex = this.getLabelForValue(v).split(' ');
        return ex[2];
    };
    let chartDay = chartMake('chartDay', day_op);
    noDataAddClass('chartDay', day_op);

    // 요일별
    let week_op = chartOptions();
    week_op.data.labels = <?=json_encode(array_keys($r_joinWeek))?>;
    week_op.data.datasets[0].data = <?=json_encode(array_values($r_joinWeek))?>;
    week_op.options.plugins.title.text = '요일별';
    week_op.options.plugins.legend.reverse = true;
    let chartWeek = chartMake('chartWeek', week_op);
    noDataAddClass('chartWeek', week_op);

    // 성별
    let sex_op = {
        plugins: [ChartDataLabels],
        type: 'pie',
        data: {
            labels: <?=json_encode(array_keys($r_sex))?>,
            datasets: [
                {
                    type: 'pie',
                    label: '가입자수',
                    data: <?=json_encode(array_values($r_sex))?>,
                    datalabels: {
                        align: 'middle',
                        anchor: 'middle',
                        display: true,
                        labels: {
                            name: {
                                align: 'top',
                                color: 'white',
                                formatter: function (value, ctx) {
                                    return ctx.active ? 'name' : ctx.chart.data.labels[ctx.dataIndex];
                                }
                            },
                            value: {
                                align: 'bottom',
                                color: 'white',
                                formatter: function (value, ctx) {
                                    if (ctx.active) {
                                        return 'value';
                                    } else {
                                        return value.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                                    }
                                },
                                padding: 4,
                            },
                        },
                    },
                    backgroundColor: <?=json_encode(array_values($r_sex_color))?>,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index',
            },
            animation: {
                startAngle: {
                    from: Math.PI * 2
                },
                endAngle: {
                    from: Math.PI * 2
                }
            },
            plugins: {
                legend: {
                    display: false,
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        boxWidth: 8
                    },
                },
                title: {
                    display: true,
                    text: '성별',
                    align: 'start',
                    color: '#000',
                    padding: {
                        bottom: 30
                    },
                    font: {
                        size: 15,
                        weight: 'bold'
                    },
                },
                datalabels: {
                    color: function (context) {
                        return '#EFEFEF';
                    },
                    font: {
                        weight: 'bold'
                    },
                    padding: {
                        right: 20,
                    },
                    formatter: function (value, context) {
                        return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                    },
                },
                tooltip: {
                    enabled: false,
                    external: externaltooltip,
                    position: 'custom',
                    callbacks: {
                        title: function (tooltipItems) {
                            return tooltipItems[0].label;
                        },
                        label: function (tooltipItems) {
                            return [tooltipItems.dataset.label, tooltipItems.formattedValue];
                        },
                    },
                },
            },
        },
    };
    let chartSex = chartMake('chartSex', sex_op);
    noDataAddClass('chartSex', sex_op);

    // 지역별
    let main_treemap_graph_colors = ['#ECC661', '#DF7593', '#364648', '#75BEDF', '#DF75D2', '#9CBB6D', '#FF8179', '#5F4D89', '#DF9775', '#149374', '#28B4C8', '#95504E', '#759EDF'];
    let local_op = {
        type: 'treemap',
        data: {
            datasets: [
                {
                    tree: <?=json_encode($r_local)?>,
                    key: 'value_cnt',
                    spacing: 0,
                    borderWidth: 1,
                    backgroundColor: function (ctx) {
                        if (ctx.type == 'data') {
                            return main_treemap_graph_colors[ctx.dataIndex];
                        }
                        return '';
                    },
                    borderColor: function (ctx) {
                        if (ctx.type == 'data') {
                            return main_treemap_graph_colors[ctx.dataIndex];
                        }
                        return '';
                    },
                    labels: {
                        display: true,
                        align: 'left',
                        position: 'top',
                        color: '#EFEFEF',
                        formatter: function (ctx) {
                            return ctx.type === 'data' ? [ctx.raw._data.subject, ctx.raw.v.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',')] : [];
                        },
                        font: {
                            weight: 'bold',
                        }
                    },
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false,
                },
                title: {
                    display: true,
                    text: '지역별',
                    align: 'start',
                    color: '#000',
                    padding: {
                        bottom: 30
                    },
                    font: {
                        size: 15,
                        weight: 'bold'
                    },
                },
                tooltip: {
                    enabled: false,
                    external: externaltooltip,
                    position: 'custom',
                    callbacks: {
                        title: function (tooltipItems) {
                            return tooltipItems[0].raw._data.subject;
                        },
                        label: function (tooltipItem) {
                            return ['가입자수', tooltipItem.raw._data[tooltipItem.dataset.key].replace(/\B(?=(\d{3})+(?!\d))/g, ',')];
                        }
                    },
                },
            },
        },
    };
    let chartLocal = chartMake('chartLocal', local_op);
    noDataAddClass('chartLocal', local_op);

    // 단축검색 리스트 - 감시 인스턴스 생성
    var observer = new MutationObserver(function() {
        if ($('.quick_search')[0].children.length === 0) {
            $('.list_info').css('display', 'block');
        }
    });

    // 단축검색 리스트 - 감시 시작
    observer.observe(document.getElementsByClassName('quick_search')[0], {
        attributes: false,
        childList: true,
        characterData: false,
    });

    // 단축검색등록 팝업
    function memAntviewQuickSearch(frm, m) {
        // 검색기간 1년 체크
        var limitDateChk = chkDateTerm();
        if (!limitDateChk) {
            return false;
        }

        viewQuickSearch(frm, m);

        window.quicksearch.close = function() {
            $('.layerPop').fadeOut('fast', function() {
                $(this).remove();
                removeDimmed();
                $('body').off('keyup');
            });

            if ($('.quick_search')[0].children.length > 0) {
                $('.list_info').css('display', 'none');
            }
        }
    }

    // 최대 1년 검색 체크
    function chkDateTerm() {
        var sdArr = $('#start_date').val().split('-');
        var fdArr = $('#finish_date').val().split('-');

        var sDate = new Date(parseInt(sdArr[0]), parseInt(sdArr[1]), parseInt(sdArr[2]));
        var fDate = new Date(parseInt(fdArr[0]), parseInt(fdArr[1]), parseInt(fdArr[2]));

        var btMs = fDate.getTime() - sDate.getTime();
        var btDay = btMs / (1000*60*60*24);

        if (btDay > 365) {
            alert('최대 검색 기간은 1년입니다.');
            return false;
        }
        return true;
    }

    // 데이터 검색
    function searchDate(s, e) {
        $('#start_date').val(s);
        $('#finish_date').val(e);
        $('form[name="memberAnalysisFrm"]').submit();
    }
</script>