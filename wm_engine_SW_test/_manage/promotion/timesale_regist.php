<?PHP

	$no = numberOnly($_GET['no']);

	if($no > 0) {
		$data = $pdo->assoc("select * from {$tbl['product_timesale_set']} where no='$no'");
		$data['name'] = stripslashes($data['name']);
        if ($data['ts_datee'] == 0) {
            $ts_unlimited = 'checked';
            $data['ts_datee'] = $data['ts_dates'];
        } else {
            $ts_unlimited = '';
        }
		$ts_dates = preg_split('/ |:/', $data['ts_dates']);
		$ts_datee = preg_split('/ |:/', $data['ts_datee']);
	} else {
		$data['ts_use'] = 'Y';
		$data['ts_event_type'] = '1';
		$data['ts_saletype'] = 'price';
		$data['ts_cut'] = 1;
	}

	$_hours = $_mins = array();
	for($i = 0; $i <= 23; $i++)	$_hours[sprintf('%02d', $i)] = sprintf('%02d', $i).'시';
	for($i = 0; $i <= 59; $i++)	$_mins[sprintf('%02d', $i)] = sprintf('%02d', $i).'분';

	$_listURL = getListURL('timesale_regist');
	if(empty($_listURL) == true) $_listURL = '?body=promotion@timesale_list';

?>
<form method="post" action="./index.php" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="promotion@timesale_regist.exe">
	<input type="hidden" name="no" value="<?=$no?>">

	<div class="box_title first">
		<h2 class="title">타임세일 세트 등록</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">타임세일 세트 등록</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tr>
			<th scope="row">사용여부</th>
			<td>
				<label><input type="radio" name="ts_use" value="Y" <?=checked($data['ts_use'], 'Y')?>> 사용함</label>
				<label><input type="radio" name="ts_use" value="N" <?=checked($data['ts_use'], 'N')?>> 사용안함</label>
				<ul class="list_info">
					<li>이벤트 기간이 아닐 경우 적용 되지 않습니다.</li>
					<li>이벤트 할인 및 적립금은 할인 전 상품 판매금액 기준으로 책정됩니다.</li>
					<li>타임세일 이벤트로 등록된 상품만 타임세일이 적용됩니다.</li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row">타임세일 세트명</th>
			<td>
				<input type="text" name="name" value="<?=inputText($data['name'])?>" class="input input_full">
			</td>
		</tr>
		<tr>
			<th scope="row">기간</th>
			<td>
				<input type="text" name="ts_dates[]" value="<?=$ts_dates[0]?>" size="8" class="input datepicker">
				<?=selectArray($_hours, 'ts_dates[]', false, null, $ts_dates[1])?>
				<?=selectArray($_mins, 'ts_dates[]', false, null, $ts_dates[2])?>
				~
				<input type="text" name="ts_datee[]" value="<?=$ts_datee[0]?>" size="8" class="input datepicker">
				<?=selectArray($_hours, 'ts_datee[]', false, null, $ts_datee[1])?>
				<?=selectArray($_mins, 'ts_datee[]', false, null, $ts_datee[2])?>
                <label><input type="checkbox" name="ts_unlimited" class="ts_unlimited" <?=$ts_unlimited?>> 무제한</label>
			</td>
		</tr>
		<tr>
			<th rowspan="2" rowspan="2">할인/적립</th>
			<td>
				<label><input type="radio" name="ts_event_type" value="1" <?=checked($data['ts_event_type'], '1')?>> 할인</label>
				<label><input type="radio" name="ts_event_type" value="2" <?=checked($data['ts_event_type'], '2')?>> 적립</label>
			</td>
		</tr>
		<tr>
			<td>
				<input type="text" name="ts_saleprc" class="input right" size="5" value="<?=parsePrice($data['ts_saleprc'])?>">
				<label><input type="radio" name="ts_saletype" value="price" <?=checked($data['ts_saletype'], 'price')?>> 원</label>
				<label><input type="radio" name="ts_saletype" value="percent" <?=checked($data['ts_saletype'], 'percent')?>> %</label>
				<ul class="list_msg">
					<li>미입력 또는 0 입력 시 할인/적립이 되지 않습니다.</li>
                    <li>세트상품에는 할인/적립 이벤트를 적용할 수 없으며, 세트상품을 구성하는 일반상품에는 타임세일 적용이 가능합니다</li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row">절사단위</th>
			<td>
				<label><input type="radio" name="ts_cut" value="1" <?=checked($data['ts_cut'], '1')?>> 절사 없음</label>
				<label><input type="radio" name="ts_cut" value="10" <?=checked($data['ts_cut'], '10')?>> 10원 단위</label>
				<label><input type="radio" name="ts_cut" value="100" <?=checked($data['ts_cut'], '100')?>> 100원 단위</label>
				<label><input type="radio" name="ts_cut" value="1000" <?=checked($data['ts_cut'], '1000')?>> 1,000원 단위</label>
			</td>
		</tr>
		<tr class="timesale_N">
			<th>시간종료 후 상태</th>
			<td>
				<select name="ts_state">
                    <option value="">변경 없음</option>
					<?for($key = 3; $key <= 4; $key++) {?>
					<option value="<?=$key?>" <?=checked($data['ts_state'], $key, true)?>><?=$_prd_stat[$key]?></option>
					<?}?>
				</select>
				<ul class="list_msg">
					<li>지정시간이 종료되면 상품 상태가 변경됩니다.</li>
				</ul>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
		<span class="box_btn"><input type="submit" value="취소" onclick="location.href='<?=$_listURL?>'"></span>
	</div>
</form>
<script type="text/javascript">
(tsUnlimited = function() {
    var checked = $('.ts_unlimited').prop('checked');
    var sel = $('[name="ts_datee[]"]:gt(0)');
    var date = $('[name="ts_datee[]"]').eq(0);
    if (checked == true) {
        date.datepicker('option', 'disabled', true);
        sel.prop('disabled', true);

        date.css('background', '#f2f2f2');
        sel.css('background', '#f2f2f2');
    } else {
        date.datepicker('option', 'disabled', false);
        sel.prop('disabled', false);

        date.css('background', '');
        sel.css('background', '');
    }
})();
$('.ts_unlimited').on('click', function() {
    tsUnlimited();
});
</script>