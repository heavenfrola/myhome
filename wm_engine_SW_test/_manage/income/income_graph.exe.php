<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  매출통계 그래프 출력
	' +----------------------------------------------------------------------------------------------+*/

	if(!headers_sent() && $_GET['mode'] != 'excel') {
		header('Content-type:text/html; charset=euc-kr;');
	}

	if($_GET['statdate1'] && $_GET['statdate2']) {
		list($yy, $mm) = explode('-', date('Y-m-d', strtotime($_GET['statdate1'])));
	} else {
		if($_GET['yy']) $yy = numberOnly($_GET['yy']);
		if($_GET['mm']) $mm = numberOnly($_GET['mm']);
		if($_GET['dd']) $dd = numberOnly($_GET['dd']);
	}
	if($_GET['datetype']) $datetype = addslashes($_GET['datetype']);
	if($_GET['stat']) $stat = $_GET['stat'];
	if($_GET['pay_type']) $pay_type = $_GET['pay_type'];


	if(!$datetype) $datetype = 1;

	$range = '1 years';
	$tickinterval = '1 month';
	$title = '';
    $title_total = '';
	$suffix = '%m';
	$unit = '월';
	$gs = 1;
	$ge = 12;
	if($mm) {
		$range = '1 months';
		$tickinterval = '1 day';
		$title .= " {$mm}월";
        $title_total .= ' '.$mm.'월';
		$suffix = '%d';
		$unit = '일';
		$gs = 1;
		$ge = date('t', strtotime("$yy-$mm-01"));
	}
	if($dd) {
		$range = '1 days';
		$tickinterval = '1 hour';
		$title .= " {$dd}일";
        $title_total .= ' '.$dd.'일';
		$suffix = '%H';
		$unit = '시';
		$gs = 0;
		$ge = 23;
	}

	if($dd > 0) $title .= ' 시간별';
	else if($mm > 0) $title .= ' 일별';
	else $title .= ' 월별';

	if(!$yy) $yy = date('Y');
	if(!$mm) $mm = '01';
	if(!$dd) $dd = '01';
	if($_GET['statdate1'] && $_GET['statdate2']) {
		$statdate1 = strtotime($_GET['statdate1']);
		$statdate2 = strtotime($_GET['statdate2'])+86399;
	} else {
		$statdate1 = strtotime(date("$yy-$mm-$dd"));
		$statdate2 = strtotime($range, $statdate1)-1;
	}
	$title = "{$yy}년 ".$title;
    $title_total = $yy.'년 '.$title_total;

	$grw = '';
	if(count($stat) > 0) $grw .= ' and stat in ('.implode(',', $stat).')';
	if(count($pay_type) > 0) $grw .= ' and pay_type in ('.implode(',', $pay_type).')';
	if(is_array($_GET['conversion_s'])) {
		$_grw = array();
		foreach($_GET['conversion_s'] as $key => $val) {
			array_push($_grw, "`conversion` like '%@$val%'");
		}
		$grw .= " and (".implode(" or ", $_grw).")";
	}

    $channel = $_GET['channel'];
	if($channel == 'Y') $grw .= " and `checkout`='Y'";
	else if($channel == 'N') {
		$grw .= " and `checkout`!='Y'";
		if($cfg['use_kakaoTalkStore'] == 'Y') $grw .= " and talkstore='N'";
		if($cfg['n_smart_store'] == 'Y') $grw .= " and smartstore='N'";
	}
	else if($channel == 'K') $grw .= " and talkstore='Y'";
	else if($channel == 'S') $grw .= " and smartstore='Y'";

	include_once $engine_dir.'/_manage/income/income.inc.php';
    // 결제수단 - 주문
    $income_field_payment_order = "
        count(if(stat between 1 and 5 and pay_type=2, 1,null)) as bank_cnt,
        count(if(stat between 1 and 5, 1, null)) as cnt
    ";
    $average_ago = $statdate1;
    $groupbyUnit = $suffix;
    if ($unit == '월') {
        $average_ago = strtotime('-3 months', $statdate1); // 3개월전
        $groupbyUnit = '%Y%m';
        $label_name = '지난 3개월 평균';
    }
    if ($unit == '일') {
        $average_ago = strtotime('-7 days', $statdate1); // 7일전
        $groupbyUnit = '%Y%m%d';
        $label_name = '지난 7일 평균';
    }

	$res = $pdo->iterator("
		select from_unixtime(date$datetype, '$suffix') as unit,
            from_unixtime(date$datetype, '$groupbyUnit') as dated,
			sum(if(mobile ='N', pay_prc+point_use,0)) as pc,
			sum(if(mobile ='Y', pay_prc+point_use,0)) as mobile,
			sum(if(mobile ='A', pay_prc+point_use,0)) as app,
			sum(if(pay_type=2, pay_prc+point_use,0)) as bank,
			sum(if(pay_type=2 and stat between 1 and 5, pay_prc+point_use,0)) as bank_pay_prc,
			sum(if(pay_type=2 and stat between 2 and 5, ($string_sales),0)) as bank_sale_prc,
			sum(if(pay_type=2 and stat between 2 and 5, sale5,0)) as bank_cpn_prc,
			sum(if(pay_type=2 and stat between 2 and 5, milage_prc,0)) as bank_milage_prc,
			sum(if(pay_type=2 and stat between 2 and 5, emoney_prc,0)) as bank_emoney_prc,
			sum(if(pay_type=2 and stat between 2 and 5, repay_prc,0)) as bank_part_repay_prc,
			$income_field,
            $income_field_payment_order
		from $tbl[order] where date$datetype between $average_ago and $statdate2 and (x_order_id in ('', 'checkout', 'talkstore') or x_order_id is null) and stat not in (11, 31) $grw group by dated");
    $avg_pagemode_sales_tmp = array(); // 페이지모드 - 매출
    $avg_pagemode_order_tmp = array(); // 페이지모드 - 주문
    $avg_payment_sales_tmp = array(); // 결제수단 - 매출
    $avg_payment_order_tmp = array(); // 결제수단 - 주문
    $sd = date('Y-m-d', $average_ago);
    $ed = date('Y-m-d', $statdate2);
    $from = new DateTime($sd);
    $to = new DateTime($ed);
    $intvl = $from->diff($to);
    $int_e = ($intvl->y * 12) + $intvl->m; // 날짜 차이 - 개월수
    $formatString = 'Ym';
    $agoUnit = 'months';
    if ($unit == '월') {
        $int_e = ($intvl->y * 12) + $intvl->m;
        $formatString = 'Ym';
        $agoUnit = 'months';
    }
    if ($unit == '일') {
        $int_e = ($intvl->days) + 1;
        $formatString = 'Ymd';
        $agoUnit = 'days';
    }

    // 평균값 tmp 세팅
    for ($int_s = 0; $int_s < $int_e; $int_s++) {
        $avg_pagemode_sales_tmp[date($formatString, strtotime($int_s.$agoUnit, $average_ago))] = 0;
        $avg_pagemode_order_tmp[date($formatString, strtotime($int_s.$agoUnit, $average_ago))] = 0;
        $avg_payment_sales_tmp[date($formatString, strtotime($int_s.$agoUnit, $average_ago))] = 0;
        $avg_payment_order_tmp[date($formatString, strtotime($int_s.$agoUnit, $average_ago))] = 0;
    }

	$tmp = array();
	foreach ($res as $sdata) {
		foreach (array('pc', 'mb', 'ap') as $_dev) {
            $sdata[$_dev.'_sale_prc'] -= $sdata[$_dev.'_cpn_prc'];
			$sdata[$_dev.'_order_prc'] = $sdata[$_dev.'_pay_prc']+$sdata[$_dev.'_1_prc']+$sdata[$_dev.'_sale_prc']+$sdata[$_dev.'_cpn_prc']+$sdata[$_dev.'_milage_prc']+$sdata[$_dev.'_emoney_prc']+$sdata[$_dev.'_part_repay_prc'];
		}
		$sdata['bank_order_prc'] = $sdata['bank_pay_prc']+$sdata['bank_sale_prc']+$sdata['bank_cpn_prc']+$sdata['bank_milage_prc']+$sdata['bank_emoney_prc']+$sdata['bank_part_repay_prc'];

		$tot = ($sdata['pc_order_prc']+$sdata['mb_order_prc']+$sdata['ap_order_prc']-$sdata['bank_order_prc'])/10000;

        // 평균데이터 세팅
        if ($unit != '시') {
            // 페이지모드 - 매출
            $avg_pagemode_sales_tmp[$sdata['dated']] = round($sdata['pc_order_prc'] / 10000) + round($sdata['mb_order_prc'] / 10000) + round($sdata['ap_order_prc'] / 10000);
            // 페이지모드 - 주문
            $avg_pagemode_order_tmp[$sdata['dated']] = $sdata['pc_order_cnt'] + $sdata['mb_order_cnt'] + $sdata['ap_order_cnt'];
            // 결제수단 - 매출
            $avg_payment_sales_tmp[$sdata['dated']] = round($tot) + round($sdata['bank_order_prc'] / 10000);
            // 결제수단 - 주문
            $avg_payment_order_tmp[$sdata['dated']] = ($sdata['cnt'] - $sdata['bank_cnt']) + $sdata['bank_cnt'];

            // if문 처리. 원래 시작일보다 작으면 패스
            if ($sdata['dated'] < date($formatString, $statdate1)) {
                continue;
            }
        }

		$tmp[$sdata['unit']] = array(
			'tot' => round($tot),
			'bank' => round($sdata['bank_order_prc']/10000),
			'pc' => round($sdata['pc_order_prc']/10000),
			'mobile' => round($sdata['mb_order_prc']/10000),
			'app' => round($sdata['ap_order_prc']/10000),
            'cnt' => $sdata['cnt'],
            'tot_cnt' => ($sdata['cnt'] - $sdata['bank_cnt']),
            'bank_cnt' => $sdata['bank_cnt'],
            'pc_cnt' => $sdata['pc_order_cnt'],
            'mobile_cnt' => $sdata['mb_order_cnt'],
            'app_cnt' => $sdata['ap_order_cnt'],
		);

		$udata[preg_replace('/^0([0-9])$/', '$1', $sdata['unit'])] = $sdata;

		foreach($sdata as $key => $val) {
			$data[$key] += $val;
		}
	}

	if($_GET['mode'] == 'excel') return;

    $line_tot = array();
    $line_tot_cnt = array();
    $line_pc = array();
    $line_pc_cnt = array();
    $line_mobile = array();
    $line_mobile_cnt = array();
    $line_bank = array();
    $line_bank_cnt = array();
    $line_app = array();
    $line_app_cnt = array();

    $labels = array();

    $avg_pagemode_sales = array(); // 페이지모드 - 매출
    $avg_pagemode_order = array(); // 페이지모드 - 주문
    $avg_payment_sales = array(); // 결제수단 - 매출
    $avg_payment_order = array(); // 결제수단 - 주문
	for($i = $gs; $i <= $ge; $i++) {
		$sdata = $tmp[sprintf('%02d',$i)];
		if(!$sdata) $sdata = array('tot'=>0, 'bank'=>0, 'pc'=>0, 'moblle'=>0);

		if(!$sdata['tot']) $sdata['tot'] = 0;
		if(!$sdata['bank']) $sdata['bank'] = 0;
		if(!$sdata['pc']) $sdata['pc'] = 0;
		if(!$sdata['mobile']) $sdata['mobile'] = 0;
		if(!$sdata['app']) $sdata['app'] = 0;

		switch($suffix) {
			case '%H' : $field = date('Y-m-d h:iA', strtotime("$yy-$mm-$dd $i:00:00")); break;
			case '%d' : $field = "$yy-$mm-$i"; break;
			case '%m' :
				$field = date('Y-m-d', strtotime('-1 days', strtotime("$yy-$i-01")));
			break;
		}

		$line1 .= ",['$field', $sdata[tot]]";
		$line2 .= ",['$field', $sdata[pc]]";
		$line3 .= ",['$field', $sdata[mobile]]";
		$line4 .= ",['$field', $sdata[bank]]";
		$line5 .= ",['$field', $sdata[app]]";

        array_push($line_tot,$sdata['tot']);
        array_push($line_tot_cnt,$sdata['tot_cnt']);
        array_push($line_pc,$sdata['pc']);
        array_push($line_pc_cnt,$sdata['pc_cnt']);
        array_push($line_mobile,$sdata['mobile']);
        array_push($line_mobile_cnt,$sdata['mobile_cnt']);
        array_push($line_bank,$sdata['bank']);
        array_push($line_bank_cnt,$sdata['bank_cnt']);
        array_push($line_app,$sdata['app']);
        array_push($line_app_cnt,$sdata['app_cnt']);

        array_push($labels, $i.$unit);

        // 실제 평균데이터 초기화
        array_push($avg_pagemode_sales, 0);
        array_push($avg_pagemode_order, 0);
        array_push($avg_payment_sales, 0);
        array_push($avg_payment_order, 0);
	}

    // 데이터 평균 구하기
    $chkCnt = 0; // 몇번째 부터 시작할건지
    if ($unit == '월') {
        $chkC = 3; // 배열 몇개 자를지
    }
    if ($unit == '일') {
        $chkC = 7;
    }
    if ($unit !== '시') {
        foreach ($avg_pagemode_sales as $k => $v) {
            // 페이지모드 - 매출
            $slice_tmp = array_slice($avg_pagemode_sales_tmp, $chkCnt, $chkC);
            $avg_pagemode_sales[$k] = round(array_sum($slice_tmp)/$chkC);

            // 페이지모드 - 주문
            $slice_tmp = array_slice($avg_pagemode_order_tmp, $chkCnt, $chkC);
            $avg_pagemode_order[$k] = round(array_sum($slice_tmp)/$chkC);

            // 결제수단 - 매출
            $slice_tmp = array_slice($avg_payment_sales_tmp, $chkCnt, $chkC);
            $avg_payment_sales[$k] = round(array_sum($slice_tmp)/$chkC);

            // 결제수단 - 주문
            $slice_tmp = array_slice($avg_payment_order_tmp, $chkCnt, $chkC);
            $avg_payment_order[$k] = round(array_sum($slice_tmp)/$chkC);

            $chkCnt++;
        }
    }

	$line1 = preg_replace('/^,/', '', $line1);
	$line2 = preg_replace('/^,/', '', $line2);
	$line3 = preg_replace('/^,/', '', $line3);
	$line4 = preg_replace('/^,/', '', $line4);
	$line5 = preg_replace('/^,/', '', $line5);

?>
<script src="<?=$engine_url?>/_engine/common/chart/chart.js"></script>
<script src="<?=$engine_url?>/_engine/common/chart/chartjs-function.js"></script>

<?if($exec != 'income_detail'){?>
<div class="box_title">
	<h2 class="title"><?=$title?> 통계 그래프</h2>
</div>
<div class="box_middle sort">
    <ul class="tab_sort">
        <li class="pmode active" data-type="pmode"><a href="#" onclick="graphViewType(this, 'pmode'); return false;">페이지모드</a></li>
        <li class="payment" data-type="payment"><a href="#" onclick="graphViewType(this, 'payment'); return false;">결제수단</a></li>
    </ul>
</div>
<div class="box_middle4">
    <div style="text-align: right;margin-bottom: 10px;">
        <span class="income box_btn_group">
            <span class="box_btn_s active"><input type="button" value="매출" onclick="orderOrPriceView(this, 'prc');" data-view="prc"></span>
            <span class="box_btn_s"><input type="button" value="주문" onclick="orderOrPriceView(this, 'order');" data-view="order"></span>
        </span>
    </div>
    <div style="width:100%;">
        <canvas id="incomeChart" style="width:100%;height:300px;"></canvas>
    </div>
</div>
<?}?>
<div class="box_title">
	<h2 class="title"><?=$title_total?> 매출집계</h2>
</div>
<table class="tbl_mini full income_prc_align">
	<caption class="hidden"><?=$title_total?> 매출집계</caption>
	<thead>
		<tr>
			<th scope="col" rowspan="2">구분</th>
			<th scope="colgroup" colspan="2">주문</th>
			<th scope="col" colspan="2">미입금</th>
			<th scope="colgroup" colspan="7">결제</th>
			<th scope="colgroup">입금전취소</th>
			<th scope="colgroup">배송전취소</th>
			<th scope="colgroup">반품/교환</th>
		</tr>
		<tr>
			<th scope="col">건수</th>
			<th scope="col">주문금액</th>
			<th scope="col">건수</th>
			<th scope="col">금액</th>
			<th scope="col">배송비</th>
			<th scope="col">할인가</th>
			<th scope="col">쿠폰</th>
			<th scope="col">적립금</th>
			<th scope="col">예치금</th>
			<th scope="col">부분취소</th>
			<th scope="col">실결제금액</th>
			<th scope="col">건수</th>
			<th scope="col">건수</th>
			<th scope="col">건수</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<th>PC</th>
			<td><?=nformat($data['pc_order_cnt'])?></td>
			<td class="right"><?=nformat($data['pc_order_prc'])?></td>
			<td><?=nformat($data['pc_1_cnt'])?></td>
			<td class="right"><?=nformat($data['pc_1_prc'])?></td>
			<td class="right"><?=nformat($data['pc_dlv_prc'])?></td>
			<td class="right"><?=nformat($data['pc_sale_prc'])?></td>
			<td class="right"><?=nformat($data['pc_cpn_prc'])?></td>
			<td class="right"><?=nformat($data['pc_milage_prc'])?></td>
			<td class="right"><?=nformat($data['pc_emoney_prc'])?></td>
			<td class="right"><?=nformat($data['pc_part_repay_prc'])?></td>
			<td class="right"><?=nformat($data['pc_pay_prc'])?></td>
			<td><?=nformat($data['pc_cancel1_cnt'])?></td>
			<td><?=nformat($data['pc_cancel2_cnt'])?></td>
			<td><?=nformat($data['pc_cancel3_cnt'])?></td>
		</tr>
		<tr>
			<th>Mobile</th>
			<td><?=nformat($data['mb_order_cnt'])?></td>
			<td class="right"><?=nformat($data['mb_order_prc'])?></td>
			<td><?=nformat($data['mb_1_cnt'])?></td>
			<td class="right"><?=nformat($data['mb_1_prc'])?></td>
			<td class="right"><?=nformat($data['mb_dlv_prc'])?></td>
			<td class="right"><?=nformat($data['mb_sale_prc'])?></td>
			<td class="right"><?=nformat($data['mb_cpn_prc'])?></td>
			<td class="right"><?=nformat($data['mb_milage_prc'])?></td>
			<td class="right"><?=nformat($data['mb_emoney_prc'])?></td>
			<td class="right"><?=nformat($data['mb_part_repay_prc'])?></td>
			<td class="right"><?=nformat($data['mb_pay_prc'])?></td>
			<td><?=nformat($data['mb_cancel1_cnt'])?></td>
			<td><?=nformat($data['mb_cancel2_cnt'])?></td>
			<td><?=nformat($data['mb_cancel3_cnt'])?></td>
		</tr>
		<tr>
			<th>App</th>
			<td><?=nformat($data['ap_order_cnt'])?></td>
			<td class="right"><?=nformat($data['ap_order_prc'])?></td>
			<td><?=nformat($data['ap_1_cnt'])?></td>
			<td class="right"><?=nformat($data['ap_1_prc'])?></td>
			<td class="right"><?=nformat($data['ap_dlv_prc'])?></td>
			<td class="right"><?=nformat($data['ap_sale_prc'])?></td>
			<td class="right"><?=nformat($data['ap_cpn_prc'])?></td>
			<td class="right"><?=nformat($data['ap_milage_prc'])?></td>
			<td class="right"><?=nformat($data['ap_emoney_prc'])?></td>
			<td class="right"><?=nformat($data['ap_part_repay_prc'])?></td>
			<td class="right"><?=nformat($data['ap_pay_prc'])?></td>
			<td><?=nformat($data['ap_cancel1_cnt'])?></td>
			<td><?=nformat($data['ap_cancel2_cnt'])?></td>
			<td><?=nformat($data['ap_cancel3_cnt'])?></td>
		</tr>
		<tr>
			<th>합계</th>
			<td><?=nformat($data['pc_order_cnt']+$data['mb_order_cnt']+$data['ap_order_cnt'])?></td>
			<td class="right"><?=nformat($data['pc_order_prc']+$data['mb_order_prc']+$data['ap_order_prc'])?></td>
			<td><?=nformat($data['pc_1_cnt']+$data['mb_1_cnt']+$data['ap_1_cnt'])?></td>
			<td class="right"><?=nformat($data['pc_1_prc']+$data['mb_1_prc']+$data['ap_1_prc'])?></td>
			<td class="right"><?=nformat($data['pc_dlv_prc']+$data['mb_dlv_prc']+$data['ap_dlv_prc'])?></td>
			<td class="right"><?=nformat($data['pc_sale_prc']+$data['mb_sale_prc']+$data['ap_sale_prc'])?></td>
			<td class="right"><?=nformat($data['pc_cpn_prc']+$data['mb_cpn_prc']+$data['ap_cpn_prc'])?></td>
			<td class="right"><?=nformat($data['pc_milage_prc']+$data['mb_milage_prc']+$data['ap_milage_prc'])?></td>
			<td class="right"><?=nformat($data['pc_emoney_prc']+$data['mb_emoney_prc']+$data['ap_emoney_prc'])?></td>
			<td class="right"><?=nformat($data['pc_part_repay_prc']+$data['mb_part_repay_prc']+$data['ap_part_repay_prc'])?></td>
			<td class="right"><?=nformat($data['pc_pay_prc']+$data['mb_pay_prc']+$data['ap_pay_prc'])?></td>
			<td><?=nformat($data['pc_cancel1_cnt']+$data['mb_cancel1_cnt']+$data['ap_cancel1_cnt'])?></td>
			<td><?=nformat($data['pc_cancel2_cnt']+$data['mb_cancel2_cnt']+$data['ap_cancel2_cnt'])?></td>
			<td><?=nformat($data['pc_cancel3_cnt']+$data['mb_cancel3_cnt']+$data['ap_cancel3_cnt'])?></td>
		</tr>
	</tbody>
</table>
<div class="box_bottom">
	<ul class="list_msg left">
		<li>주문 : <?=$_order_stat[1]?>~<?=$_order_stat[5]?> 주문 및 주문.<br>■ 주문금액 = 총주문금액 - (부분)취소금액</li>
		<li>결제 : <?=$_order_stat[2]?>~<?=$_order_stat[5]?> 주문.<br>■ 실결제금액 = 주문금액 - <?=$_order_stat[1]?> - 할인가 - 쿠폰 - 적립금 - 예치금 - 부분취소</li>
	</ul>
</div>
<?if($exec == 'income_detail'){?>
<div class="box_title">
	<h2 class="title"><?=$title?> 매출통계</h2>
	<span class="box_btn_s btns icon excel"><a href="<?=str_replace('income_log', 'income_log_excel.exe', $_SERVER['REQUEST_URI'])?>&mode=excel">엑셀다운</a></span>
</div>
<div class="box_middle sort">
    <ul class="tab_sort">
        <li class="pmode active" data-type="pmode"><a href="#" onclick="graphViewType(this, 'pmode'); return false;">페이지모드</a></li>
        <li class="payment" data-type="payment"><a href="#" onclick="graphViewType(this, 'payment'); return false;">결제수단</a></li>
    </ul>
</div>
<div class="box_middle4">
    <div style="text-align: right;margin-bottom: 10px;">
        <span class="income box_btn_group">
            <span class="box_btn_s active"><input type="button" value="매출" onclick="orderOrPriceView(this, 'prc');" data-view="prc"></span>
            <span class="box_btn_s"><input type="button" value="주문" onclick="orderOrPriceView(this, 'order');" data-view="order"></span>
        </span>
    </div>
    <div style="width:100%;">
        <canvas id="incomeChart" style="width:100%;height:300px;"></canvas>
    </div>
</div>
<?}?>

<script type="text/javascript">
    // 페이지모드 - 매출
    var datasetsType_pmode_prc = [
        {
            type: 'bar',
            label: 'PC',
            data: <?=json_encode($line_pc)?>,
            borderColor: '#D183E2',
            backgroundColor: '#D183E2',
            borderRadius: 30,
            maxBarThickness: 8,
            sum: 'Y',
            sort: 1,
        },
        {
            type: 'bar',
            label: 'Mobile',
            data: <?=json_encode($line_mobile)?>,
            borderColor: '#83A7E2',
            backgroundColor: '#83A7E2',
            borderRadius: 30,
            maxBarThickness: 8,
            sum: 'Y',
            sort: 2,
        },
        {
            type: 'bar',
            label: 'App',
            data: <?=json_encode($line_app)?>,
            borderColor: '#9783E2',
            backgroundColor: '#9783E2',
            borderRadius: 30,
            maxBarThickness: 8,
            sum: 'Y',
            sort: 3,
        },
    ];

    // 페이지모드 - 주문
    var datasetsType_pmode_order = [
        {
            type: 'bar',
            label: 'PC',
            data: <?=json_encode($line_pc_cnt)?>,
            borderColor: '#D183E2',
            backgroundColor: '#D183E2',
            borderRadius: 30,
            maxBarThickness: 8,
            sum: 'Y',
            sort: 1,
        },
        {
            type: 'bar',
            label: 'Mobile',
            data: <?=json_encode($line_mobile_cnt)?>,
            borderColor: '#83A7E2',
            backgroundColor: '#83A7E2',
            borderRadius: 30,
            maxBarThickness: 8,
            sum: 'Y',
            sort: 2,
        },
        {
            type: 'bar',
            label: 'App',
            data: <?=json_encode($line_app_cnt)?>,
            borderColor: '#9783E2',
            backgroundColor: '#9783E2',
            borderRadius: 30,
            maxBarThickness: 8,
            sum: 'Y',
            sort: 3,
        },
    ];

    // 결제수단 - 매출
    var datasetsType_payment_prc = [
        {
            type: 'bar',
            label: '무통장결제',
            data: <?=json_encode($line_bank)?>,
            borderColor: '#E2A183',
            backgroundColor: '#E2A183',
            borderRadius: 30,
            maxBarThickness: 8,
            sum: 'Y',
            sort: 1,
        },
        {
            type: 'bar',
            label: 'PG결제',
            data: <?=json_encode($line_tot)?>,
            borderColor: '#E2C583',
            backgroundColor: '#E2C583',
            borderRadius: 30,
            maxBarThickness: 8,
            sum: 'Y',
            sort: 2,
        },
    ];

    // 결제수단 - 주문
    var datasetsType_payment_order = [
        {
            type: 'bar',
            label: '무통장결제',
            data: <?=json_encode($line_bank_cnt)?>,
            borderColor: '#E2A183',
            backgroundColor: '#E2A183',
            borderRadius: 30,
            maxBarThickness: 8,
            sum: 'Y',
            sort: 1,
        },
        {
            type: 'bar',
            label: 'PG결제',
            data: <?=json_encode($line_tot_cnt)?>,
            borderColor: '#E2C583',
            backgroundColor: '#E2C583',
            borderRadius: 30,
            maxBarThickness: 8,
            sum: 'Y',
            sort: 2,
        },
    ];

    // 월 || 일이면 평균 라벨 추가
    if ('<?=$unit?>' === '월' || '<?=$unit?>' === '일') {
        datasetsType_pmode_prc.unshift({
            type: 'line',
            label: '<?=$label_name?>',
            data: <?=json_encode($avg_pagemode_sales)?>,
            borderColor: '#DFE1E6',
            backgroundColor: '#DFE1E6',
            sum: 'N',
            sort: 4,
        });
        datasetsType_pmode_order.unshift({
            type: 'line',
            label: '<?=$label_name?>',
            data: <?=json_encode($avg_pagemode_order)?>,
            borderColor: '#DFE1E6',
            backgroundColor: '#DFE1E6',
            sum: 'N',
            sort: 4,
        });
        datasetsType_payment_prc.unshift({
            type: 'line',
            label: '<?=$label_name?>',
            data: <?=json_encode($avg_payment_sales)?>,
            borderColor: '#DFE1E6',
            backgroundColor: '#DFE1E6',
            sum: 'N',
            sort: 3,
        });
        datasetsType_payment_order.unshift({
            type: 'line',
            label: '<?=$label_name?>',
            data: <?=json_encode($avg_payment_order)?>,
            borderColor: '#DFE1E6',
            backgroundColor: '#DFE1E6',
            sum: 'N',
            sort: 3,
        });
    }

    const incomeChart = chartMake('incomeChart', {
        type: 'line',
        data: {
            labels: <?=json_encode($labels)?>,
            datasets: datasetsType_pmode_prc,
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
                    display: true,
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        boxWidth: 8,
                        sort: function(a, b, data) {
                            var aD = '';
                            var bD = '';
                            for (var i=0; i < data.datasets.length; i++) {
                                if (a.text === data.datasets[i].label) {
                                    aD = data.datasets[i].sort;
                                }
                                if (b.text === data.datasets[i].label) {
                                    bD = data.datasets[i].sort;
                                }
                            }
                            return aD - bD;
                        }
                    },
                },
                tooltip: {
                    enabled: false,
                    external: externaltooltip,
                    position: 'custom',
                    callbacks: {
                        title: function(tooltipItems) {
                            return tooltipItems[0].label;
                        },
                        label: function(tooltipItems) {
                            return [tooltipItems.dataset.label, tooltipItems.formattedValue + '만', ['총 금액', '만', tooltipItems.dataset.sum]];
                        },
                    },
                },
            },
            scales: {
                x: {
                    stacked: true,
                    grid: {
                        display: false,
                    },
                    ticks: {
                        color: '#999999',
                    },
                },
                y: {
                    stacked: true,
                    ticks: {
                        color: '#999999',
                        callback: function(label, index, labels) {
                            return label.toLocaleString() + '만';
                        }
                    },
                },
            },
        },
    });

    // 페이지모드 or 결제수단
    function graphViewType(e, type = 'pmode') {
        var tab = $('.tab_sort > li');
        tab.removeClass('active');
        tab.filter('.' + type).addClass('active');

        let orderOrPrc = $('.income.box_btn_group .active input').data('view');
        incomeChart.data.datasets = eval('datasetsType_' + type + '_' + orderOrPrc);
        incomeChart.update();
    }

    // 매출 or 주문
    function orderOrPriceView(e, type = 'prc') {
        $('.income.box_btn_group .active').removeClass('active');
        $(e).parent().addClass('active');

        if (type === 'prc') { // 매출
            incomeChart.options.scales.y.ticks.callback = function(label) {
                return label.toLocaleString() + '만';
            };
            incomeChart.options.plugins.tooltip.callbacks.label = function(tooltipItems) {
                return [tooltipItems.dataset.label, tooltipItems.formattedValue + '만', ['총 금액', '만', tooltipItems.dataset.sum]];
            };
        } else { // 주문
            incomeChart.options.scales.y.ticks.callback = function(label) {
                return label.toLocaleString();
            };
            incomeChart.options.plugins.tooltip.callbacks.label = function(tooltipItems) {
                return [tooltipItems.dataset.label, tooltipItems.formattedValue + '건', ['총 주문', '건', tooltipItems.dataset.sum]];
            };
        }
        let viewType = $('.tab_sort li.active').data('type');
        incomeChart.data.datasets = eval('datasetsType_' + viewType + '_' + type);
        incomeChart.update();
    }
</script>