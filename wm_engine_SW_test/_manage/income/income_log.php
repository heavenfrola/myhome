<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  월/일/시간별매출
	' +----------------------------------------------------------------------------------------------+*/

	$log_mode = numberOnly($_GET['log_mode']);
	$stat = $_GET['stat'];
	$pay_type = $_GET['pay_type'];
	$conversion_s = $_GET['conversion_s'];

	// 년
	$min_year = $pdo->row("select from_unixtime(min(date1), '%Y') as miny from $tbl[order] where stat not in (11,31) and (x_order_id in ('', 'checkout', 'talkstore') or isnull(x_order_id))");
	if(!$min_year) $min_year = date('Y');
	$yy = numberOnly($_GET['yy']);
	if(!$yy) $yy = date('Y');

	$uunit = '%m';
	$uunit_name = '월';
	$unit_code = 'months';
	$statdate1 = strtotime(date("$yy-01-01"));
	$statdate2 = strtotime("+1 years", $statdate1)-1;
	$uunit_min = 1;
	$uunit_max = 12;

	// 월
	if($log_mode > 0) {
		$_months = array();
		for($i = 1; $i <= 12; $i++) {
			$_months[sprintf('%02d', $i)] = "{$i}월";
		}
		$mm = numberOnly($_GET['mm']);
		if(!$mm) $mm = date('m');

		$select_m = selectArray($_months, 'mm', 0, null, $mm);

		$uunit = '%d';
		$uunit_name = '일';
		$unit_code = 'days';
		$statdate1 = strtotime(date("$yy-$mm-01"));
		$statdate2 = strtotime("+1 months", $statdate1)-1;
		if($_GET['statdate1'] && $_GET['statdate2']) {
			$statdate1 = strtotime($_GET['statdate1']);
			$statdate2 = strtotime($_GET['statdate2'])+86399;
		}
		$uunit_min = 1;
		$uunit_max = date('t', $statdate2);
	}
	// 일
	if($log_mode > 1) {
		$_days = array();
		for($i = 1; $i <= 31; $i++) {
			$_days[sprintf('%02d', $i)] = "{$i}일";
		}
		$dd = numberOnly($_GET['dd']);
		if(!$dd) $dd = date('d');

		$select_d = selectArray($_days, 'dd', 0, null, $dd);

		$uunit = '%H';
		$uunit_name = '시';
		$unit_code = 'hours';
		$statdate1 = strtotime(date("$yy-$mm-$dd"));
		$statdate2 = $statdate1+86399;
		$uunit_min = 0;
		$uunit_max = 23;
	}

	// 기준일자
	$_datetype = array(
		1 => '주문일',
		2 => '입금일',
		4 => '배송일',
		5 => '배송완료일'
	);
	$datetype = numberOnly($_GET['datetype']);
	if(!$datetype) $datetype = 1;

	// 주문기준
	if(!is_array($stat)) $stat = array();

	// 결제수단
	if(!is_array($pay_type)) $pay_type = array();

    // 판매채널
    $channel = $_GET['channel'];

	$exec = 'income_detail';

	$w = '';
	if(count($stat) > 0) $w .= ' and stat in ('.implode(',', $stat).')';
	if(count($pay_type) > 0) $w .= ' and pay_type in ('.implode(',', $pay_type).')';

	if($_GET['mode'] == 'excel') {
		include $engine_dir.'/_manage/income/income_graph.exe.php';
		return;
	}

?>
<form id="search" method="get" action="./" onsubmit="return checkLogFrm(this)">
	<input type="hidden" name="body" value="<?=$_GET['body']?>">
	<input type="hidden" name="log_mode" value="<?=$log_mode?>">
	<div class="box_title first">
		<h2 class="title">검색</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">검색</caption>
		<colgroup>
			<col style="width:15%">
			<col style="width:35%">
			<col style="width:15%">
			<col style="width:35%">
		</colgroup>
		<tr>
			<th scope="row">기간</th>
			<td>
				<?if($log_mode == 1) {?>
				<input type="text" name="statdate1" value="<?=date('Y-m-d', $statdate1)?>" class="datepicker input" size="10"> ~
				<input type="text" name="statdate2" value="<?=date('Y-m-d', $statdate2)?>" class="datepicker input" size="10">
				<?} else {?>
				<select name="yy">
				<?for($i = $min_year; $i <= date('Y'); $i++){?>
					<option value="<?=$i?>" <?=checked($i, $yy, 1)?>><?=$i?>년</option>
				<?}?>
				</select>
				<?=$select_m?>
				<?=$select_d?>
				<?}?>
			</td>
			<th scope="row">기준일자</th>
			<td>
				<?=selectArray($_datetype, 'datetype', 0, null, $datetype)?>
			</td>
		</tr>
		<tr>
			<th scope="row">주문기준</th>
			<td>
				<?for($i = 1; $i <= 5; $i++) {?>
				<label class="p_cursor"><input type="checkbox" name="stat[]" value="<?=$i?>" <?=checked(in_array($i, $stat), true)?>> <?=$_order_stat[$i]?></label>
				<?}?>
			</td>
			<th scope="row">결제수단</th>
			<td>
				<?foreach($_pay_type as $i => $val) {?>
				<label class="p_cursor" style="white-space:nowrap;"><input type="checkbox" name="pay_type[]" value="<?=$i?>" <?=checked(in_array($i, $pay_type), true)?>> <?=$val?></label>
				<?}?>
			</td>
		</tr>
        <?if(empty($cfg['checkout_id']) == false || $cfg['use_kakaoTalkStore'] == 'Y' || $scfg->comp('n_smart_store', 'Y')) {?>
        <tr>
            <th scope="row">판매채널</th>
            <td colspan="3">
                <label><input type="radio" name="channel" value=""  <?=checked($channel, '')?>> 전체</label>
                <label><input type="radio" name="channel" value="N" <?=checked($channel, 'N')?>> 쇼핑몰</label>
                <label><input type="radio" name="channel" value="Y" <?=checked($channel, 'Y')?> <?if($cfg['checkout_id'] == '') {?>disabled<?}?>> 네이버페이</label>
                <label><input type="radio" name="channel" value="K" <?=checked($channel, 'K')?> <?if($cfg['use_kakaoTalkStore'] != 'Y') {?>disabled<?}?>> 카카오톡 스토어</label>
                <label><input type="radio" name="channel" value="S" <?=checked($channel, 'S')?> <?if(!$scfg->comp('n_smart_store', 'Y')) {?>disabled<?}?>> 네이버 스마트스토어</label>
            </td>
        </tr>
        <?}?>
		<tr>
			<th scope="row">
				유입경로 <a href="http://help.wisa.co.kr/manual/index/C0130" target="_blank" class="sclink">광고코드 등록안내</a>
			</th>
			<td class="bcol2" colspan="3">
				<?=selectArrayConv("conversion_s")?>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="검색"></span>
		<span class="box_btn"><input type="button" value="초기화" onclick="location.href='?body=<?=$_GET['body']?>'"></span>
	</div>
</form>
<div id="statSummary">
	<?include $engine_dir.'/_manage/income/income_graph.exe.php';?>
</div>
<table class="tbl_mini full income_prc_align">
	<caption class="hidden">매출통계</caption>
	<thead>
		<tr>
			<th rowspan="3" colspan="2">구분</th>
			<th colspan="2">주문</th>
			<th colspan="2">미입금</th>
			<th colspan="9">결제</th>
			<th>입금전취소</th>
			<th>배송전취소</th>
			<th>반품/교환</th>
		</tr>
		<tr>
			<th rowspan="2">건수</th>
			<th rowspan="2">주문금액</th>
			<th rowspan="2">건수</th>
			<th rowspan="2">금액</th>
			<th rowspan="2">배송비</th>
			<th rowspan="2">할인가</th>
			<th rowspan="2">쿠폰</th>
			<th rowspan="2">적립금</th>
			<th rowspan="2">예치금</th>
			<th rowspan="2">부분취소</th>
			<th colspan="3">실결제금액</th>
			<th rowspan="2">건수</th>
			<th rowspan="2">건수</th>
			<th rowspan="2">건수</th>
		</tr>
		<tr>
			<th>현금</th>
			<th>PG</th>
			<th>합계</th>
		</tr>
	</thead>
	<tbody>
		<?PHP
            $weekend_kor = array('0' => '일요일', '1' => '월요일', '2' => '화요일', '3' => '수요일', '4' => '목요일', '5' => '금요일', '6' => '토요일');
            $dateFormatText = 'Y-m'; // 날짜 포맷 형식 (월별)
            if ($log_mode > 0) $dateFormatText = 'Y-m-d'; // (일별, 시간별)
			$s = $statdate1;
			while($s < $statdate2) {
				$i = (int)date(str_replace('%', '', $uunit), $s);
                $dateText = date($dateFormatText, $s); // 날짜
                $dateText_week = $weekend_kor[date('w', $s)]; // 요일
				$s = strtotime('+1 '.$unit_code, $s);
				$data=$udata[$i];
		?>
			<tr>
			<th rowspan="4">
                <?php
                if ($log_mode == 1) $dateText .= '<br>'.$dateText_week;
                if ($log_mode == 2) $dateText .= '<br>'.$i.$uunit_name;
                echo $dateText;
                ?>
            </th>
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
			<td class="right"><?=nformat($data['pc_bank_prc'])?></td>
			<td class="right"><?=nformat($data['pc_pay_prc']-$data['pc_bank_prc'])?></td>
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
			<td class="right"><?=nformat($data['mb_bank_prc'])?></td>
			<td class="right"><?=nformat($data['mb_pay_prc']-$data['mb_bank_prc'])?></td>
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
			<td class="right"><?=nformat($data['ap_bank_prc'])?></td>
			<td class="right"><?=nformat($data['ap_pay_prc']-$data['ap_bank_prc'])?></td>
			<td class="right"><?=nformat($data['ap_pay_prc'])?></td>
			<td><?=nformat($data['ap_cancel1_cnt'])?></td>
			<td><?=nformat($data['ap_cancel2_cnt'])?></td>
			<td><?=nformat($data['ap_cancel3_cnt'])?></td>
		</tr>
		<tr class='stat_total_line'>
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
			<td class="right"><?=nformat($data['pc_bank_prc']+$data['mb_bank_prc']+$data['ap_bank_prc'])?></td>
			<td class="right"><?=nformat(($data['pc_pay_prc']+$data['mb_pay_prc']+$data['ap_pay_prc'])-($data['pc_bank_prc']+$data['mb_bank_prc']+$data['ap_bank_prc']))?></td>
			<td class="right"><?=nformat($data['pc_pay_prc']+$data['mb_pay_prc']+$data['ap_pay_prc'])?></td>
			<td><?=nformat($data['pc_cancel1_cnt']+$data['mb_cancel1_cnt']+$data['ap_cancel1_cnt'])?></td>
			<td><?=nformat($data['pc_cancel2_cnt']+$data['mb_cancel2_cnt']+$data['ap_cancel2_cnt'])?></td>
			<td><?=nformat($data['pc_cancel3_cnt']+$data['mb_cancel3_cnt']+$data['ap_cancel3_cnt'])?></td>
		</tr>
		<?}?>
	</tbody>
</table>

<script type="text/javascript">
	$('.tbl_mini').find('tr').bind({
		'mouseover' : function() {
			this.style.background = '#ffffcc';
		},
		'mouseout' : function() {
			this.style.background = '';
		}
	})

	function checkLogFrm(f) {
		if(f.log_mode.value == '1') {
			var statdate1 = Date.parse(f.statdate1.value+' 00:00:00')/1000;
			var statdate2 = Date.parse(f.statdate2.value+' 23:59:59')/1000;
			if(statdate2-statdate1 > (86400*40)) {
				window.alert('일별매출은 최대 40일 이내로만 검색하실수 있습니다.');
				return false;
			}
		}
		return true;
	}
</script>