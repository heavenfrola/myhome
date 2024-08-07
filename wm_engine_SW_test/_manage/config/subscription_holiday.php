<?PHP

	$ym = (int)$_GET['ym'];
	if(!$ym) $ym = date('Ym');

	$cal_st = strtotime($ym.'01');
	$cal_ed = strtotime('+1 months', $cal_st)-1;
	$curr_day = 0;
	$last_day = date('t', $cal_ed);

	$prev_m = date('Ym', strtotime('-1 months', $cal_st));
	$next_m = date('Ym', strtotime('+1 months', $cal_st));

	if(!isTable($tbl['sbscr_holiday'])) {
		$pdo->query($tbl_schema['sbscr_holiday']);
	}

	$datas = array();
	$res = $pdo->iterator("select * from $tbl[sbscr_holiday] where timestamp between $cal_st and $cal_ed");
    foreach ($res as $data) {
		$datas[$data['timestamp']] = $data;
	}

	function parseDate(&$curr_day, $last) {
        global $cal_st, $datas, $rest;

		$curr_day++;
		$day = $curr_day;
		$month = date('m', $cal_st);
		$checker = ($day-1+ceil($last/2));
		if($last == 30) $checker += 1;
		if($month == "02" && $last == 28) {
			$checker += 1;
		}
		if($curr_day >= $last || $checker > $last) return false;

		$p1 = ($day-1);
		$p2 = ($day-1+ceil($last/2));
		$p1_date = strtotime("+$p1 days", $cal_st);
		$p2_date = strtotime("+$p2 days", $cal_st);

		for($i = 1; $i <= 2; $i++) {
			$date = ${'p'.$i.'_date'};
            $week = date('w', $date);
			$array[$i] = array(
				'str' => date('m월 d일 ', $date).constant('__lang_common_week_'.strtolower(date('D', $date)).'__').'요일',
				'day' => date('d', $date),
				'week' => $week,
				'timestamp' => $date,
				'is_holiday' => $datas[$date]['is_holiday'] ,
				'description' => $datas[$date]['description'],
				'admin_id' => $datas[$date]['admin_id'],
                'rest' => (isset($rest[$date]) == true) ? $rest[$date] : '',
                'is_rest' => (isset($rest[$date]) == true || $week == '6' || $week == '0') ? 'Y' : 'N'
			);
		}

		if($p2 >= $last) unset($array[2]);

		return array(
			'p1' => $array[1],
			'p2' => $array[2],
		);
	}

    // 휴일 정보
    $rest = array();
    $wec = new WeagleEyeClient($_we, 'Etc');
    $restInfo = $wec->call('getRestDeInfo', array(
        'solYear' => date('Y', $cal_st),
        'solMonth' => date('m', $cal_st),
    ));
    $restInfo = json_decode($restInfo);
	if ($restInfo->response->body->items->item) {
		foreach ($restInfo->response->body->items->item as $item) {
			if ($item->isHoliday == 'Y') {
				$rest[strtotime($item->locdate)] = $item->dateName;
			}
		}
	}

?>
<form id="pannel_holiday" method="post" action="./index.php" target="hidden<?=$now?>" class="subscription_holiday" onsubmit="printLoading()" style="display:none">
	<input type="hidden" name="body" value="config@subscription_holiday.exe">
	<div class="box_middle3 month">
        <?=date('Y년 m월', $cal_st)?>
        <div class="buttons">
            <span class="box_btn_s blue"><input type="button" value="&lt; 이전달" onclick="location.href='?body=<?=$_GET['body']?>&ym=<?=$prev_m?>#holiday'"></span>
            <span class="box_btn_s blue"><input type="button" value="다음달 &gt;" onclick="location.href='?body=<?=$_GET['body']?>&ym=<?=$next_m?>#holiday'"></span>
            <label style="display: inline-block; border:1px solid #d2d2d2; padding: 3px 5px; line-height: 200%; vertical-align: middle;">
                <input type="checkbox" class="rest_all"> 휴일 전체 선택
            </label>
        </div>
	</div>
	<table class="tbl_col">
		<caption class="hidden">정기배송 휴일 설정</caption>
		<colgroup>
			<col style="width:120px;">
			<col style="width:80px;">
			<col>
			<col style="width:120px;">
			<col style="width:120px;">
			<col style="width:80px;">
			<col>
			<col style="width:120px;">
		</colgroup>
		<thead>
			<tr>
				<th scope="col">날짜</th>
				<th scope="col">휴일</th>
				<th scope="col">사유</th>
				<th scope="col">최종 처리자</th>
				<th scope="col">날짜</th>
				<th scope="col">휴일</th>
				<th scope="col">사유</th>
				<th scope="col">최종 처리자</th>
			</tr>
		</thead>
		<tbody>
			<?php while($data = parseDate($curr_day, $last_day)) { ?>
			<tr>
				<?php foreach($data as $date) { ?>
				<?php if (is_array($date)){ ?>
				<th class="week_<?=$date['week']?>">
                    <?=$date['str']?>
                    <p class="p_color2"><?=$date['rest']?></p>
                </th>
				<td class="week_<?=$date['week']?>">
					<input type="hidden" name="timestamp[<?=$date['day']?>]" value="<?=$date['timestamp']?>">
					<input type="checkbox" name="is_holiday[<?=$date['day']?>]" class="is_rest_<?=$date['is_rest']?>" value="Y" style="height:25px; width:25px;" <?=checked($date['is_holiday'], 'Y')?>>
				</td>
				<td class="left week_<?=$date['week']?>"><input type="text" name="description[<?=$date['day']?>]" class="input block" value="<?=$date['description']?>"></td>
				<td class="week_<?=$date['week']?>"><?=$date['admin_id']?></td>
				<?php } else { ?>
				<td colspan="4" class="week<?=$date['week']?>">&nbsp;</td>
				<?php } ?>
				<?php } ?>
			</tr>
			<?php } ?>
		</tbody>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<style type="text/css">
.week_6 {background:#f6fbfb;}
.week_0 {background:#fff3f3;}
</style>
<script>
function restCheck()
{
    $('.is_rest_Y').prop('checked', true);
}
$(function() {
    chainCheckbox($('.rest_all'), $('.is_rest_Y'));
});
</script>