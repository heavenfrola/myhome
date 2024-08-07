<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  개별상품판매분석
	' +----------------------------------------------------------------------------------------------+*/

	$pno = numberOnly($_GET['pno']);
	$all_date = addslashes($_GET['all_date']);
	$start_date = addslashes($_GET['start_date']);
	$finish_date = addslashes($_GET['finish_date']);

// 오늘 날자
$today = date('Y-m-d');

$explodeDate = explode('-', $today);
$todayY = $explodeDate[0];
$todayM = $explodeDate[1];
$todayD = $explodeDate[2];

// 어제 날짜
$yesterDay = date('Y-m-d', strtotime($today . ' -1 days'));

// 최근 7일 시작일
$thisWeekStartDay = date('Y-m-d', strtotime($today . ' -6 days'));
$thisWeekEndDay = $today;

// 최근 30일
$last30StartDay = date('Y-m-d', strtotime($today . ' -29days'));
$last30EndDay = $today;

// 최근 2개월
$last2mStartDay = date('Y-m-d', strtotime($today . ' -2month'));
$last2mEndDay = $today;

// 최근 3개월
$last3MonthStartDay = date('Y-m-d', strtotime($today . ' -3month'));
$last3MonthEndDay = $today;

// 이번달 (1일 ~ 현재)
$thisMonthStartDay = $todayY . '-' . $todayM . '-01';
$thisMonthEndDay = $today;

// 지난달
$prev_month = strtotime('-1 month', strtotime($todayY . '-' . $todayM . '-01')); // 이번달 01일부터 계산
$lastMonthStartDay = date('Y-m-01', $prev_month);
$lastMonthEndDay = date('Y-m-t', $prev_month);


// 없거나 첫 화면은 '오늘'로 기본 세팅
if ($start_date == '') $start_date = $last30StartDay;
if ($finish_date == '') $finish_date = $today;

// 간편검색 array
$easySearchBtn = array(
    '오늘' => array(1, $today, $today),
    '어제' => array(2, $yesterDay, $yesterDay),
    '7일' => array(3, $thisWeekStartDay, $thisWeekEndDay),
    '30일' => array(8, $last30StartDay, $last30EndDay),
    '2개월' => array(9, $last2mStartDay, $last2mEndDay),
    '3개월' => array(7, $last3MonthStartDay, $last3MonthEndDay),
    '이번달 (1일~현재)' => array(6, $thisMonthStartDay, $thisMonthEndDay),
    '지난달 (1일~말일)' => array(5, $lastMonthStartDay, $lastMonthEndDay),
);

	if($_GET['ref'] == 'income' && !$start_date && !$finish_date) {
		$start_date = date('Y-01-01');
	}
    if($start_date) {
        if ($body == 'income@income_product_detail_excel.exe') {
            $w .= " and date1 >= '".strtotime($start_date)."'";
        } else {
            $average_ago = strtotime('-7 days', strtotime($start_date));
            $w .= " and date1 >= '".$average_ago."'";
        }
    }
	if($finish_date) $w .= " and date1 <= '".(strtotime($finish_date)+86399)."'";

	if($pno > 0) {
		$prd = $pdo->assoc("select * from `$tbl[product]` where `no` = '$pno'");
		$prd = shortCut($prd);
		$prd['name'] = stripslashes(strip_tags($prd['name']));
		$prd['thumb'] = getFileDir($prd['updir'])."/$prd[updir]/$prd[upfile3]";

		$cate = makeCategoryName($prd, 1);
		$cq = getSaleField('-a.');
		$qry = "select
				from_unixtime(date1, '%Y-%m-%d') as saledate, date1,
				sum(if(b.mobile ='N', a.buy_ea,0)) as pc_ea, sum(if(b.mobile ='N', a.total_prc $cq,0)) as pc_prc,
				sum(if(b.mobile ='Y', a.buy_ea,0)) as mb_ea, sum(if(b.mobile ='Y', a.total_prc $cq,0)) as mb_prc,
				sum(if(b.mobile ='N' and a.stat between 2 and 5, a.total_prc $cq,0)) as pc_pay_prc,
				sum(if(b.mobile ='Y' and a.stat between 2 and 5, a.total_prc $cq,0)) as mb_pay_prc,
				sum(if(a.stat between 2 and 5 and b.pay_type=2, a.buy_ea,0)) as bank_ea,
				sum(if(b.mobile ='N' and a.stat=13, a.buy_ea, 0)) as pc_ea_cancel, sum(if(b.mobile ='N' and a.stat=13, a.total_prc $cq, 0)) as pc_prc_cancel,
				sum(if(b.mobile ='Y' and a.stat=13, a.buy_ea, 0)) as mb_ea_cancel, sum(if(b.mobile ='Y' and a.stat=13, a.total_prc $cq, 0)) as mb_prc_cancel,
				sum(if(b.mobile ='N' and a.stat=15, a.buy_ea, 0)) as pc_ea_cancel2, sum(if(b.mobile ='N' and a.stat=15, a.total_prc $cq , 0)) as pc_prc_cancel2,
				sum(if(b.mobile ='Y' and a.stat=15, a.buy_ea, 0)) as mb_ea_cancel2, sum(if(b.mobile ='Y' and a.stat=15, a.total_prc $cq, 0)) as mb_prc_cancel2,
				sum(if(b.mobile ='N' and a.stat in (17,19), a.buy_ea, 0)) as pc_ea_cancel3, sum(if(b.mobile ='N' and a.stat in (17,19), a.total_prc $cq, 0)) as pc_prc_cancel3,
				sum(if(b.mobile ='Y' and a.stat in (17,19), a.buy_ea, 0)) as mb_ea_cancel3, sum(if(b.mobile ='Y' and a.stat in (17,19), a.total_prc $cq, 0)) as mb_prc_cancel3,
                sum(if(b.mobile ='A' and a.stat in (17,19), a.buy_ea, 0)) as app_ea_cancel3, sum(if(b.mobile ='A' and a.stat in (17,19), a.total_prc $cq, 0)) as app_prc_cancel3,
                sum(if(b.mobile ='A', a.buy_ea,0)) as app_ea, sum(if(b.mobile ='A', a.total_prc $cq,0)) as app_prc,
                sum(if(b.mobile ='A' and a.stat between 2 and 5, a.total_prc $cq,0)) as app_pay_prc,
                sum(if(a.stat between 2 and 5 and b.pay_type=2, a.total_prc $cq,0)) as bank_prc,
                sum(if(b.mobile ='A' and a.stat=13, a.buy_ea, 0)) as app_ea_cancel, sum(if(b.mobile ='A' and a.stat=13, a.total_prc $cq, 0)) as app_prc_cancel,
                sum(if(b.mobile ='A' and a.stat=15, a.buy_ea, 0)) as app_ea_cancel2, sum(if(b.mobile ='A' and a.stat=15, a.total_prc $cq, 0)) as app_prc_cancel2,
				`option`
			from $tbl[order_product] a inner join $tbl[order] b using(ono) where b.stat not in (11,31,32) and a.pno='$pno' and (b.x_order_id='' or b.x_order_id in ('checkout', 'talkstore') or b.x_order_id is null)  $w group by saledate, `option` order by saledate, `option` asc";

		$res = $pdo->iterator($qry);

		// 전체 리스트
		$min_date = $max_date = '';
		$max_ea = 0;
		$sdata = array();
		$summ = array();
		$options = array();
        $avg_pagemode_prc_tmp = array(); // 페이지모드 - 매출
        $avg_pagemode_order_tmp = array(); // 페이지모드 - 주문
        $avg_payment_prc_tmp = array(); // 결제수단 - 매출
        $avg_payment_order_tmp = array(); // 결제수단 - 주문
        $avg_pagemode_prc = array(); // 페이지모드 - 매출
        $avg_pagemode_order = array(); // 페이지모드 - 주문
        $avg_payment_prc = array(); // 결제수단 - 매출
        $avg_payment_order = array(); // 결제수단 - 주문
        $sd = date('Y-m-d', $average_ago);
        $ed = $finish_date;
        $from = new DateTime($sd);
        $to = new DateTime($ed);
        $intvl = $from->diff($to);
        $int_e = ($intvl->days) + 1; // 날짜 차이

        // 기본세팅
        for ($int_s = 0; $int_s < $int_e; $int_s++) {
            $avg_pagemode_prc_tmp[date('Y-m-d', strtotime($int_s.'days', $average_ago))] = 0;
            $avg_pagemode_order_tmp[date('Y-m-d', strtotime($int_s.'days', $average_ago))] = 0;
            $avg_payment_prc_tmp[date('Y-m-d', strtotime($int_s.'days', $average_ago))] = 0;
            $avg_payment_order_tmp[date('Y-m-d', strtotime($int_s.'days', $average_ago))] = 0;

            if ($int_s < 7) continue;
            $avg_pagemode_prc[date('Y-m-d', strtotime($int_s.'days', $average_ago))] = 0;
            $avg_pagemode_order[date('Y-m-d', strtotime($int_s.'days', $average_ago))] = 0;
            $avg_payment_prc[date('Y-m-d', strtotime($int_s.'days', $average_ago))] = 0;
            $avg_payment_order[date('Y-m-d', strtotime($int_s.'days', $average_ago))] = 0;
        }

        foreach ($res as $data) {
            // 페이지모드 - 매출
            $avg_pagemode_prc_tmp[$data['saledate']] += round(($data['pc_prc'] + $data['mb_prc'] + $data['app_prc'])/10000);
            // 페이지모드 - 주문
            $avg_pagemode_order_tmp[$data['saledate']] += $data['pc_ea'] + $data['mb_ea'] + $data['app_ea'];
            // 결제수단 - 매출
            $avg_payment_prc_tmp[$data['saledate']] += round(($data['pc_prc'] + $data['mb_prc'] + $data['app_prc'])/10000);
            // 결제수단 - 주문
            $avg_payment_order_tmp[$data['saledate']] += $data['pc_ea'] + $data['mb_ea'] + $data['app_ea'];

            // if문 처리. 원래 시작일보다 작으면 패스
            if ($data['saledate'] < $start_date) {
                continue;
            }

			if(!$min_date) {
				if($data['pc_pay_prc'] == 0 && $data['mb_pay_prc'] == 0 && $data['app_pay_prc'] == 0) $min_date_temp = $data['date1'];
				else $min_date = $data['date1'];
			}
			$max_date = $data['date1'];

			$summ['pc_ea'] += $data['pc_ea'];
			$summ['pc_prc'] += $data['pc_prc'];
			$summ['pc_ea_cancel'] += $data['pc_ea_cancel'];
			$summ['pc_prc_cancel'] += $data['pc_prc_cancel'];
			$summ['pc_ea_cancel2'] += $data['pc_ea_cancel2'];
			$summ['pc_prc_cancel2'] += $data['pc_prc_cancel2'];
			$summ['pc_ea_cancel3'] += $data['pc_ea_cancel3'];
			$summ['pc_prc_cancel3'] += $data['pc_prc_cancel3'];
			$summ['pc_pay_prc'] += $data['pc_pay_prc'];
			$summ['mb_ea'] += $data['mb_ea'];
			$summ['mb_prc'] += $data['mb_prc'];
			$summ['mb_ea_cancel'] += $data['mb_ea_cancel'];
			$summ['mb_prc_cancel'] += $data['mb_prc_cancel'];
			$summ['mb_ea_cancel2'] += $data['mb_ea_cancel2'];
			$summ['mb_prc_cancel2'] += $data['mb_prc_cancel2'];
			$summ['mb_ea_cancel3'] += $data['mb_ea_cancel3'];
			$summ['mb_prc_cancel3'] += $data['mb_prc_cancel3'];
			$summ['mb_pay_prc'] += $data['mb_pay_prc'];
			$data['tot_ea'] = $data['pc_ea']+$data['mb_ea']+$data['app_ea'];
			$summ['app_ea'] += $data['app_ea'];
			$summ['app_prc'] += $data['app_prc'];
			$summ['app_ea_cancel'] += $data['app_ea_cancel'];
			$summ['app_prc_cancel'] += $data['app_prc_cancel'];
			$summ['app_ea_cancel2'] += $data['app_ea_cancel2'];
			$summ['app_prc_cancel2'] += $data['app_prc_cancel2'];
			$summ['app_ea_cancel3'] += $data['app_ea_cancel3'];
			$summ['app_prc_cancel3'] += $data['app_prc_cancel3'];
			$summ['app_pay_prc'] += $data['app_pay_prc'];

			if($data['option']) {
				$_temp = explode('<split_big>', $data['option']);
				foreach($_temp as $val) {
					list($optname, $optval) = explode('<split_small>', $val);
					$options[$optname][$optval] += $data['tot_ea'];
				}
			}

			$saledate = date('Ymd', $data['date1']);
			if(is_array($sdata[$saledate])) {
				foreach($sdata[$saledate] as $key => $val) {
					if(in_array($key, array('date1', 'saledate')) == true) continue;
					$sdata[$saledate][$key] += $data[$key];
				}
			} else {
				$sdata[$saledate] = $data;
			}
			$data = $sdata[$saledate];

			if($sdata[$saledate]['tot_ea'] > $max_ea) $max_ea = $sdata[$saledate]['tot_ea'];
		}

		$xls_query = makeQueryString('body');

		if($body == 'income@income_product_detail_excel.exe') {
			return;
		}

        $line_tot_order = array();
        $line_pc_order = array();
        $line_mobile_order = array();
        $line_app_order = array();
        $line_bank_order = array();
        $line_tot_prc = array();
        $line_pc_prc = array();
        $line_mobile_prc = array();
        $line_app_prc = array();
        $line_bank_prc = array();

        $g_min_date = $start_date;
        $g_max_date = $finish_date;
		if($max_date) {
			// 그래프 데이터
			if(!$min_date && $min_date_temp > 0) $min_date = $min_date_temp;
			$_min_date = strtotime('-4 weeks', $max_date)+86400;
			if($min_date < $_min_date) $_min_date = $min_date;

			if($max_date-$_min_date >= (86400*365)) {
				$_min_date = $max_date-(86400*365);
			}

			$date_array = array();
			for($i = $_min_date; $i <= $max_date; $i+=86400) {
				$data = $sdata[date('Ymd', $i)];
				$field = date('Y-m-d', $i);
				if($data) {
					$pc_ea = $data['pc_ea'];
					$mb_ea = $data['mb_ea'];
                    $app_ea = $data['app_ea'];
					$bank_ea = $data['bank_ea'];
                    $pc_prc = round($data['pc_prc']/10000);
                    $mb_prc = round($data['mb_prc']/10000);
                    $app_prc = round($data['app_prc']/10000);
                    $bank_prc = round($data['bank_prc']/10000);
				} else {
					$pc_ea = 0;
					$mb_ea = 0;
                    $app_ea = 0;
					$bank_ea = 0;
                    $pc_prc = 0;
                    $mb_prc = 0;
                    $app_prc = 0;
                    $bank_prc = 0;
				}
				$tot_ea = $pc_ea+$mb_ea+$app_ea-$bank_ea;
                $tot_prc = $pc_prc+$mb_prc+$app_prc-$bank_prc;

				$line1 .= ",['$field', $tot_ea]";
				$line2 .= ",['$field', $pc_ea]";
				$line3 .= ",['$field', $mb_ea]";
				$line4 .= ",['$field', $bank_ea]";

                $line_tot_order[$field] = $tot_ea;
                $line_pc_order[$field] = $pc_ea;
                $line_mobile_order[$field] = $mb_ea;
                $line_app_order[$field] = $app_ea;
                $line_bank_order[$field] = $bank_ea;
                $line_tot_prc[$field] = $tot_prc;
                $line_pc_prc[$field] = $pc_prc;
                $line_mobile_prc[$field] = $mb_prc;
                $line_app_prc[$field] = $app_prc;
                $line_bank_prc[$field] = $bank_prc;

			}
			$line1 = preg_replace('/^,/', '', $line1);
			$line2 = preg_replace('/^,/', '', $line2);
			$line3 = preg_replace('/^,/', '', $line3);
			$line4 = preg_replace('/^,/', '', $line4);

			// 그래프 날짜선택
			$temp = $max_date;
			while(1) {
				$edate = date('Y-m-d', $temp);
				$sdate = date('Y-m-d', strtotime('-4 weeks', $temp)+86400);

				$date_option .= "<option value='$sdate@$edate'>$sdate ~ $edate</option>";

				$temp = strtotime('-4 weeks', $temp);
				if($temp < $_min_date) break;
			}

			// 옵션 정리
			foreach($options as $key => $val) {
				arsort($options[$key]);
				$option_ea = 0;
				foreach($options[$key] as $key2 => $val2) {
					$options[$key][$key2] = $val2;
					if($option_ea > 4) {
						unset($options[$key][$key2]);
						$options[$key]['기타'] += $val2;
					}
					$option_ea++;
				}
			}
		}
	}

$chkCnt = 0; // 몇번째 부터 시작할건지
if (is_array($avg_pagemode_prc)) {
    foreach ($avg_pagemode_prc as $k => $v) {
        // 페이지모드 - 매출
        $result3 = array_slice($avg_pagemode_prc_tmp, $chkCnt, 7);
        $avg_pagemode_prc[$k] = round(array_sum($result3)/7);

        // 페이지모드 - 주문
        $result3 = array_slice($avg_pagemode_order_tmp, $chkCnt, 7);
        $avg_pagemode_order[$k] = round(array_sum($result3)/7, 1);

        // 결제수단 - 매출
        $result3 = array_slice($avg_payment_prc_tmp, $chkCnt, 7);
        $avg_payment_prc[$k] = round(array_sum($result3)/7);

        // 결제수단 - 주문
        $result3 = array_slice($avg_payment_order_tmp, $chkCnt, 7);
        $avg_payment_order[$k] = round(array_sum($result3)/7, 1);

        $chkCnt++;
    }
}

?>
<script src="<?=$engine_url?>/_engine/common/chart/chart.js"></script>
<script src="<?=$engine_url?>/_engine/common/chart/chartjs-function.js"></script>

<form id="search" method="get" onsubmit="return chkDateTerm();">
	<input type="hidden" name="body" value="<?=$_GET['body']?>">
	<div class="box_title first">
		<h2 class="title">개별상품검색</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">개별상품검색</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row" rowspan="2">상품정보</th>
			<td id="search_prd">
				<?if($pno > 0) {?>
				<input type="hidden" name="pno" value="<?=$prd['no']?>">
				<table class="tbl_mini full">
					<colgroup>
						<col>
						<col>
						<col Style="width:80px;">
						<col Style="width:70px;">
						<col Style="width:70px;">
						<col Style="width:100px;">
					</colgroup>
					<tr>
						<th scope="col">상품명</th>
						<th scope="col">카테고리</th>
						<th scope="col">상태</th>
						<th scope="col">이용후기</th>
						<th scope="col">상품문의</th>
						<th scope="col">등록일</th>
					</tr>
					<tr>
						<td class="left">
							<div class="box_setup">
								<div class="thumb"><a href="<?=$root_url?>/shop/detail.php?pno=<?=$prd['hash']?>" target="_blank"><img src="<?=$prd['thumb']?>" width="50px;"></a></div>
								<dl>
									<dt class="title"><a href="?body=product@product_register&pno=<?=$prd['no']?>" target="_blank"><?=$prd['name']?></a></dt>
									<dd class="cstr"><?=$prd['origin_name']?></dd>
								</dl>
							</div>
						</td>
						<td><?=$cate?></td>
						<td><?=$_prd_stat[$prd['stat']]?></td>
						<td><?=number_format($prd['rev_cnt'])?></td>
						<td><?=number_format($prd['qna_cnt'])?></td>
						<td><?=date('Y-m-d', $prd['reg_date'])?></td>
					</tr>
				</table>
				<?}?>
			</td>
		</tr>
		<tr>
			<td><span class="box_btn_s blue"><input type="button" value="상품찾기" onclick="psearch.open()"></span></td>
		</tr>
		<tr>
			<th scope="row">판매기간</th>
			<td>
                <input type="text" name="start_date" id="start_date" value="<?=$start_date?>" size="10" class="input datepicker">
                ~
                <input type="text" name="finish_date" id="finish_date" value="<?=$finish_date?>" size="10" class="input datepicker">
                <?php
                foreach ($easySearchBtn as $k => $v) {
                    $class_on = ($start_date == $v[1] && $finish_date == $v[2]) ? 'on' : '';
                    ?>
                    <span class="box_btn_d <?=$class_on?>"><input type="button" value="<?=$k?>" onclick="searchDateIncome('<?=$v[1]?>','<?=$v[2]?>');"></span>
                <?php } ?>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="검색"></span>
		<span class="box_btn"><input type="button" value="초기화" onclick="location.href='?body=<?=$_GET['body']?>'"></span>
	</div>
</form>
<script type="text/javascript">
	layerWindow.prototype.psel = function(pno) {
		this.multi = (!this.multi) ? 1 : this.multi+1;
		document.location.href = '?body=income@income_product_detail&pno='+pno;

		this.close();
	}
	var psearch = new layerWindow('product@product_inc.exe');

    // 데이터 검색
    function searchDateIncome(s, e, n) {
        $('#start_date').val(s);
        $('#finish_date').val(e);
        $('form[id="search"]').submit();
    }

    // 최대 1년만 검색 가능
    function chkDateTerm() {
        var sd = $('#start_date').val();
        var fd = $('#finish_date').val();

        var sdArr = sd.split('-');
        var fdArr = fd.split('-');

        var sDate = new Date(parseInt(sdArr[0]), parseInt(sdArr[1]), parseInt(sdArr[2]));
        var fDate = new Date(parseInt(fdArr[0]), parseInt(fdArr[1]), parseInt(fdArr[2]));

        var btMs = fDate.getTime() - sDate.getTime();
        var btDay = btMs / (1000*60*60*24);

        if (btDay > 365) {
            alert("최대 검색 기간은 1년입니다.");
            return false;
        }

        return true;
    }
</script>
<?if(!$pno) return;?>
<?if(!$max_date) {?>
<div class="box_middle">
	해당 상품의 판매내역이 없습니다.
</div>
<?return;}?>
<!-- 개별상품판매분석 -->
<div class="box_title">
	<h2 class="title">개별상품판매분석</h2>
</div>
<table class="tbl_mini full income_prc_align">
	<col style="width:150px">
	<tr>
		<th scope="col" rowspan="2">구분</th>
		<th scope="colgroup" colspan="2">주문</th>
		<th scope="colgroup" colspan="2">입금전 취소</th>
		<th scope="colgroup" colspan="2">배송전 취소</th>
		<th scope="colgroup" colspan="2">반품/교환</th>
		<th scope="colgroup" colspan="2" rowspan="2">실 결제액</th>
	</tr>
	<tr>
		<th scope="col">건수</th>
		<th scope="col">금액</th>
		<th scope="col">건수</th>
		<th scope="col">금액</th>
		<th scope="col">건수</th>
		<th scope="col">금액</th>
		<th scope="col">건수</th>
		<th scope="col">금액</th>
	</tr>
	<tr>
		<th>PC</th>
		<td><?=nformat($summ['pc_ea'])?></td>
		<td class="right"><?=nformat($summ['pc_prc'])?></td>
		<td><?=nformat($summ['pc_ea_cancel'])?></td>
		<td class="right"><?=nformat($summ['pc_prc_cancel'])?></td>
		<td><?=nformat($summ['pc_ea_cancel2'])?></td>
		<td class="right"><?=nformat($summ['pc_prc_cancel2'])?></td>
		<td><?=nformat($summ['pc_ea_cancel3'])?></td>
		<td class="right"><?=nformat($summ['pc_prc_cancel3'])?></td>
		<td class="right"><?=nformat($summ['pc_pay_prc'])?></td>
	</tr>
	<tr>
		<th>Mobile</th>
		<td><?=nformat($summ['mb_ea'])?></td>
		<td class="right"><?=nformat($summ['mb_prc'])?></td>
		<td><?=nformat($summ['mb_ea_cancel'])?></td>
		<td class="right"><?=nformat($summ['mb_prc_cancel'])?></td>
		<td><?=nformat($summ['mb_ea_cancel2'])?></td>
		<td class="right"><?=nformat($summ['mb_prc_cancel2'])?></td>
		<td><?=nformat($summ['mb_ea_cancel3'])?></td>
		<td class="right"><?=nformat($summ['mb_prc_cancel3'])?></td>
		<td class="right"><?=nformat($summ['mb_pay_prc'])?></td>
	</tr>
    <tr>
        <th>App</th>
        <td><?=nformat($summ['app_ea'])?></td>
        <td class="right"><?=nformat($summ['app_prc'])?></td>
        <td><?=nformat($summ['app_ea_cancel'])?></td>
        <td class="right"><?=nformat($summ['app_prc_cancel'])?></td>
        <td><?=nformat($summ['app_ea_cancel2'])?></td>
        <td class="right"><?=nformat($summ['app_prc_cancel2'])?></td>
        <td><?=nformat($summ['app_ea_cancel3'])?></td>
        <td class="right"><?=nformat($summ['app_prc_cancel3'])?></td>
        <td class="right"><?=nformat($summ['app_pay_prc'])?></td>
    </tr>
	<tr>
		<th>합계</th>
		<td><?=nformat($summ['pc_ea']+$summ['mb_ea']+$summ['app_ea'])?></td>
		<td class="right"><?=nformat($summ['pc_prc']+$summ['mb_prc']+$summ['app_prc'])?></td>
		<td><?=nformat($summ['pc_ea_cancel']+$summ['mb_ea_cancel']+$summ['app_ea_cancel'])?></td>
		<td class="right"><?=nformat($summ['pc_prc_cancel']+$summ['mb_prc_cancel']+$summ['app_prc_cancel'])?></td>
		<td><?=nformat($summ['pc_ea_cancel2']+$summ['mb_ea_cancel2']+$summ['app_ea_cancel2'])?></td>
		<td class="right"><?=nformat($summ['pc_prc_cancel2']+$summ['mb_prc_cancel2']+$summ['app_prc_cancel2'])?></td>
		<td><?=nformat($summ['pc_ea_cancel3']+$summ['mb_ea_cancel3']+$summ['app_ea_cancel3'])?></td>
		<td class="right"><?=nformat($summ['pc_prc_cancel3']+$summ['mb_prc_cancel3']+$summ['app_prc_cancel3'])?></td>
		<td class="right"><?=nformat($summ['pc_pay_prc']+$summ['mb_pay_prc']+$summ['app_pay_prc'])?></td>
	</tr>
</table>
<!-- //개별상품판매분석 -->
<!-- 옵션별 판매량 -->
<?php
	if(count($options) > 0) { // 옵션별 판매 그래프
		echo "<div class='box_title'><h2 class='title'>옵션별 판매량</h2></div>";
		$oidx = 0;
        $no = 0;
        $resData = array();
        $active = 'active';
		foreach($options as $key => $val) {
            if ($no >= 3) $active = '';
            $resData[$no] = array();
            $datas = array();
            $datas['group1'] = $key;
			$data = '';
			foreach($val as $oname => $ocnt) {
                if ($oname == '') {
                    $datas['oname'] = $key;
                } else {
                    $datas['oname'] = $oname;
                }
                $datas['val'] = $ocnt;
				if($data) $data .= ",";
				$oname = cutstr($oname, 16);
				$data .= "['$oname',$ocnt]";
                array_push($resData[$no], $datas);
			}
		}
?>
        <table class="tbl_mini full">
            <caption class="hidden">정기결제 주문조회 목록</caption>
            <colgroup>
                <col style="width:150px;">
                <col>
                <col>
                <col style="width:150px;">
            </colgroup>
            <thead>
            <tr>
                <th class="center">번호</th>
                <th class="center">옵션명</th>
                <th class="center">옵션항목</th>
                <th class="center">수량</th>
            </tr>
            </thead>
            <tbody>
            <?php

            foreach ($options as $key => $val) {
                $oidx++;
                $rowspan_num = count($val) + 1;
                ?>
                <tr>
                    <th rowspan="<?=$rowspan_num?>" class="line_r center"><?=$oidx?></th>
                    <td rowspan="<?=$rowspan_num?>" class="line_r center">
                        <?=$key?>
                    </td>
                </tr>
                <?php foreach ($val as $oname => $ocnt) { ?>
                    <tr>
                        <td class="line_r"><?=$oname?></td>
                        <td class="line_r center"><?=number_format($ocnt)?></td>
                    </tr>
                <?php } ?>
            <?php } ?>
            </tbody>
        </table>
<?php } ?>
<!-- //옵션별 판매량 -->
<!-- 기간별 판매량 -->
<div class="box_title">
	<h2 class="title">기간별 판매량</h2>
</div>
<div class="box_middle sort">
    <ul class="tab_sort">
        <li class="pmode active" data-type="pmode"><a href="javascript:void(0);" onclick="graphTabType('pmode'); return false;">페이지모드</a></li>
        <li class="payment" data-type="payment"><a href="javascript:void(0);" onclick="graphTabType('payment'); return false;">결제수단</a></li>
    </ul>
</div>
<div class="box_middle4 left">
    <div style="margin-bottom:20px;display:flex;justify-content: flex-end;">
        <span class="income box_btn_group">
            <span class="box_btn_s active"><input type="button" value="매출" onclick="orderOrPriceView(this, 'prc');" data-view="prc"></span>
            <span class="box_btn_s"><input type="button" value="주문" onclick="orderOrPriceView(this, 'order');" data-view="order"></span>
        </span>
    </div>
    <div style="width:100%;">
        <canvas id="periodChart" style="width:100%;height:300px;"></canvas>
    </div>
</div>
<!-- //기간별 판매량 -->
<!-- 상세내역 -->
<div class="box_title">
	<h2 class="title">상세내역</h2>
	<span class="box_btn_s btns icon excel"><a href="./?body=income@income_product_detail_excel.exe<?=$xls_query?>">엑셀다운</a></span>
</div>
<table class="tbl_mini full">
	<caption class="hidden">상세내역</caption>
	<colgroup>
		<col style="width:100px;">
		<col style="width:80px;">
	</colgroup>
	<tr>
		<th scope="colgroup" scope="col" rowspan="2" colspan="2">구분</th>
		<th scope="colgroup" colspan="2">주문</th>
		<th scope="colgroup" colspan="2">입금전 취소</th>
		<th scope="colgroup" colspan="2">배송전 취소</th>
		<th scope="colgroup" colspan="2">반품/교환</th>
		<th scope="colgroup" colspan="2" rowspan="2">실 결제액</th>
	</tr>
	<tr>
		<th scope="col">건수</th>
		<th scope="col">금액</th>
		<th scope="col">건수</th>
		<th scope="col">금액</th>
		<th scope="col">건수</th>
		<th scope="col">금액</th>
		<th scope="col">건수</th>
		<th scope="col">금액</th>
	</tr>
	<?foreach($sdata as $key => $data) {?>
	<?
		$max_day = $max_ea == $data['tot_ea'] ? 'max' : '';
        $weekend_kor = array('0' => '일요일', '1' => '월요일', '2' => '화요일', '3' => '수요일', '4' => '목요일', '5' => '금요일', '6' => '토요일');
	?>
	<tr class="<?=$max_day?>">
		<th rowspan="4" style="border-bottom: double 3px #ddd"><?=date('Y-m-d', $data['date1']).'<br>'.$weekend_kor[date('w', $data['date1'])]?></th>
		<th>PC</th>
		<td><?=nformat($data['pc_ea'])?></td>
		<td class="right"><?=nformat($data['pc_prc'])?></td>
		<td><?=nformat($data['pc_ea_cancel'])?></td>
		<td class="right"><?=nformat($data['pc_prc_cancel'])?></td>
		<td><?=nformat($data['pc_ea_cancel2'])?></td>
		<td class="right"><?=nformat($data['pc_prc_cancel2'])?></td>
		<td><?=nformat($data['pc_ea_cancel3'])?></td>
		<td class="right"><?=nformat($data['pc_prc_cancel3'])?></td>
		<td class="right"><?=nformat($data['pc_pay_prc'])?></td>
	</tr>
	<tr class="<?=$max_day?>">
		<th>Mobile</th>
		<td><?=nformat($data['mb_ea'])?></td>
		<td class="right"><?=nformat($data['mb_prc'])?></td>
		<td><?=nformat($data['mb_ea_cancel'])?></td>
		<td class="right"><?=nformat($data['mb_prc_cancel'])?></td>
		<td><?=nformat($data['mb_ea_cancel2'])?></td>
		<td class="right"><?=nformat($data['mb_prc_cancel2'])?></td>
		<td><?=nformat($data['mb_ea_cancel3'])?></td>
		<td class="right"><?=nformat($data['mb_prc_cancel3'])?></td>
		<td class="right"><?=nformat($data['mb_pay_prc'])?></td>
	</tr>
    <tr class="<?=$max_day?>">
        <th>App</th>
        <td><?=nformat($data['app_ea'])?></td>
        <td class="right"><?=nformat($data['app_prc'])?></td>
        <td><?=nformat($data['app_ea_cancel'])?></td>
        <td class="right"><?=nformat($data['app_prc_cancel'])?></td>
        <td><?=nformat($data['app_ea_cancel2'])?></td>
        <td class="right"><?=nformat($data['app_prc_cancel2'])?></td>
        <td><?=nformat($data['app_ea_cancel3'])?></td>
        <td class="right"><?=nformat($data['app_prc_cancel3'])?></td>
        <td class="right"><?=nformat($data['app_pay_prc'])?></td>
    </tr>
	<tr class="stat_total_line <?=$max_day?> double">
		<th>합계</th>
		<td><?=nformat($data['pc_ea']+$data['mb_ea']+$data['app_ea'])?></td>
		<td class="right"><?=nformat($data['pc_prc']+$data['mb_prc']+$data['app_prc'])?></td>
		<td><?=nformat($data['pc_ea_cancel']+$data['mb_ea_cancel']+$data['app_ea_cancel'])?></td>
		<td class="right"><?=nformat($data['pc_prc_cancel']+$data['mb_prc_cancel']+$data['app_prc_cancel'])?></td>
		<td><?=nformat($data['pc_ea_cancel2']+$data['mb_ea_cancel2']+$data['app_ea_cancel2'])?></td>
		<td class="right"><?=nformat($data['pc_prc_cancel2']+$data['mb_prc_cancel2']+$data['app_prc_cancel2'])?></td>
		<td><?=nformat($data['pc_ea_cancel3']+$data['mb_ea_cancel3']+$data['app_ea_cancel3'])?></td>
		<td class="right"><?=nformat($data['pc_prc_cancel3']+$data['mb_prc_cancel3']+$data['app_prc_cancel3'])?></td>
		<td class="right"><?=nformat($data['pc_pay_prc']+$data['mb_pay_prc']+$data['app_pay_prc'])?></td>
	</tr>
	<?}?>
</table>
<!-- //상세내역 -->

<script type='text/javascript'>
    // 페이지모드 - 매출
    var datasetsType_pmode_prc = [
        {
            type: 'line',
            label: '지난 7일 평균',
            data: [],
            dataname: 'avg_prc',
            borderColor: '#DFE1E6',
            backgroundColor: '#DFE1E6',
            sum: 'N',
            sort: 4,
        },
        {
            label: 'PC',
            data: [],
            borderRadius: 30,
            borderColor: '#D183E2',
            backgroundColor: '#D183E2',
            maxBarThickness: 8,
            dataname: 'pc_prc',
            sort: 1,
        },
        {
            label: 'Mobile',
            data: [],
            borderRadius: 50,
            borderColor: '#83A7E2',
            backgroundColor: '#83A7E2',
            maxBarThickness: 8,
            dataname: 'mobile_prc',
            sort: 2,
        },
        {
            label: 'App',
            data: [],
            borderRadius: 30,
            borderColor: '#9783E2',
            backgroundColor: '#9783E2',
            maxBarThickness: 8,
            dataname: 'app_prc',
            sort: 3,
        },
    ];

    // 페이지모드 - 주문
    var datasetsType_pmode_order = [
        {
            type: 'line',
            label: '지난 7일 평균',
            data: [],
            dataname: 'avg_order',
            borderColor: '#DFE1E6',
            backgroundColor: '#DFE1E6',
            sum: 'N',
            sort: 4,
        },
        {
            label: 'PC',
            data: [],
            borderRadius: 30,
            borderColor: '#D183E2',
            backgroundColor: '#D183E2',
            maxBarThickness: 8,
            dataname: 'pc_order',
            sort: 1,
        },
        {
            label: 'Mobile',
            data: [],
            borderRadius: 50,
            borderColor: '#83A7E2',
            backgroundColor: '#83A7E2',
            maxBarThickness: 8,
            dataname: 'mobile_order',
            sort: 2,
        },
        {
            label: 'App',
            data: [],
            borderRadius: 30,
            borderColor: '#9783E2',
            backgroundColor: '#9783E2',
            maxBarThickness: 8,
            dataname: 'app_order',
            sort: 3,
        },
    ];

    // 결제수단 - 매출
    var datasetsType_payment_prc = [
        {
            type: 'line',
            label: '지난 7일 평균',
            data: [],
            dataname: 'avg_prc',
            borderColor: '#DFE1E6',
            backgroundColor: '#DFE1E6',
            sum: 'N',
            sort: 3,
        },
        {
            label: '무통장결제',
            data: [],
            borderRadius: 30,
            borderColor: '#E2A183',
            backgroundColor: '#E2A183',
            maxBarThickness: 8,
            dataname: 'bank_prc',
            sort: 1,
        },
        {
            label: 'PG결제',
            data: [],
            borderRadius: 30,
            borderColor: '#E2C583',
            backgroundColor: '#E2C583',
            maxBarThickness: 8,
            dataname: 'tot_prc',
            sort: 2,
        },
    ];

    // 결제수단 - 주문
    var datasetsType_payment_order = [
        {
            type: 'line',
            label: '지난 7일 평균',
            data: [],
            dataname: 'avg_order',
            borderColor: '#DFE1E6',
            backgroundColor: '#DFE1E6',
            sum: 'N',
            sort: 3,
        },
        {
            label: '무통장결제',
            data: [],
            borderRadius: 30,
            borderColor: '#E2A183',
            backgroundColor: '#E2A183',
            maxBarThickness: 8,
            dataname: 'bank_order',
            sort: 1,
        },
        {
            label: 'PG결제',
            data: [],
            borderRadius: 30,
            borderColor: '#E2C583',
            backgroundColor: '#E2C583',
            maxBarThickness: 8,
            dataname: 'tot_order',
            sort: 2,
        },
    ];

    const periodChart = chartMake('periodChart', {
        type: 'bar',
        data: {
            labels: [],
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
                    title: {
                        display: true
                    },
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
                title: {
                    display: false,
                },
                datalabels: {
                    color: function(context) {
                        return '#8C8C8C';
                    },
                    display: function(context) {
                        return context.dataset.data[context.dataIndex] > 0;
                    },
                    font: {
                        weight: 'bold'
                    },
                    formatter: function(value, context) {
                        return Math.ceil(value).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
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
                    display: true,
                    stacked: true,
                    grid: {
                        display: false,
                    },
                    title: {
                        display: false,
                    },
                    ticks: {
                        color: '#999999',
                        callback: function(v, i) {
                            var exString =  this.getLabelForValue(v).split(' ');
                            return `${exString[2]}`;
                        },
                    },
                },
                y: {
                    display: true,
                    stacked: true,
                    min : 0,
                    ticks: {
                        color: '#999999',
                    },
                }
            },
        },
    });

    graphLabelsDatasSet('<?=$g_min_date?>', '<?=$g_max_date?>', 'pmode', 'prc');

    function graphLabelsDatasSet(min_date, max_date, tabType = 'pmode', orderOrPrc = 'prc') {
        // 페이지모드일때 데이터세팅
        if (tabType === 'pmode') {
            if (orderOrPrc === 'order') {
                var pc_order = <?=json_encode($line_pc_order)?>;
                var mobile_order = <?=json_encode($line_mobile_order)?>;
                var app_order = <?=json_encode($line_app_order)?>;
                var avg_order = <?=json_encode($avg_pagemode_order)?>;
            } else {
                var pc_prc = <?=json_encode($line_pc_prc)?>;
                var mobile_prc = <?=json_encode($line_mobile_prc)?>;
                var app_prc = <?=json_encode($line_app_prc)?>;
                var avg_prc = <?=json_encode($avg_pagemode_prc)?>;
            }

            eval('datasetsType_' + tabType + '_' +orderOrPrc).forEach(function(e, i) {
                datasetsType_pmode_prc[i].data = [];
            });
        } else { // 결제수단일때 데이터세팅
            if (orderOrPrc === 'order') {
                var tot_order = <?=json_encode($line_tot_order)?>;
                var bank_order = <?=json_encode($line_bank_order)?>;
                var avg_order = <?=json_encode($avg_payment_order)?>;
            } else {
                var tot_prc = <?=json_encode($line_tot_prc)?>;
                var bank_prc = <?=json_encode($line_bank_prc)?>;
                var avg_prc = <?=json_encode($avg_payment_prc)?>;
            }
            eval('datasetsType_' + tabType + '_' + orderOrPrc).forEach(function(e, i) {
                datasetsType_payment_prc[i].data = [];
            });
        }

        let labels = []; // 라벨 초기화

        var min = new Date(min_date);
        min_date = toStringByFormatting(min);

        var max = new Date(max_date);
        max_date = toStringByFormatting(max);

        while (min_date <= max_date) {
            min_date = toStringByFormatting(min);
            var dataTextLabel = min_date.split('-');
            labels.push(dataTextLabel[0] + '년 ' + dataTextLabel[1] + '월 ' + dataTextLabel[2] + '일');

            eval('datasetsType_' + tabType+ '_' + orderOrPrc).forEach(function(e, i) {
                e.data.push(eval(e.dataname)[min_date]);
            });
            min.setDate(min.getDate() + 1);
            min_date = toStringByFormatting(min);
        }

        periodChart.clear();

        // 매출/주문일때 차트 세팅 변경
        if (orderOrPrc === 'prc') {
            periodChart.options.scales.y.ticks.callback = function(label) {
                return label.toLocaleString() + '만';
            };
            periodChart.options.plugins.tooltip.callbacks.label = function(tooltipItems) {
                return [tooltipItems.dataset.label, tooltipItems.formattedValue + '만', ['총 금액', '만', tooltipItems.dataset.sum]];
            };
        } else {
            periodChart.options.scales.y.ticks.callback = function(label) {
                return label.toLocaleString();
            };
            periodChart.options.plugins.tooltip.callbacks.label = function(tooltipItems) {
                return [tooltipItems.dataset.label, tooltipItems.formattedValue + '건', ['총 주문', '건', tooltipItems.dataset.sum]];
            };
        }

        periodChart.data.labels = labels;
        periodChart.data.datasets = eval('datasetsType_' + tabType + '_' + orderOrPrc);
        periodChart.update();
    }

    // 페이지모드 or 결제수단
    function graphTabType(tabtype = 'pmode') {
        var tab = $('.tab_sort li');
        tab.removeClass('active');
        tab.filter('.' + tabtype).addClass('active');

        let orderOrPrc = $('.income.box_btn_group .active input').data('view');
        graphLabelsDatasSet('<?=$start_date?>', '<?=$finish_date?>', tabtype, orderOrPrc);
    }

    // 매출 or 주문
    function orderOrPriceView(e, type = 'prc') {
        $('.income.box_btn_group .active').removeClass('active');
        $(e).parent().addClass('active');

        let viewType = $('.tab_sort li.active').data('type');
        graphLabelsDatasSet('<?=$start_date?>', '<?=$finish_date?>', viewType, type);
    }

    function toStringByFormatting(source, delimiter = '-') {
        var year = source.getFullYear();
        var month = source.getMonth() + 1;
        var day = source.getDate();

        if (month < 10) {
            month = '0'+month;
        }

        if (day < 10) {
            day = '0'+day;
        }

        return [year, month, day].join(delimiter);
    }
</script>