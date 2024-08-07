<?PHP

	if(!$cfg['ts_use']) $cfg['ts_use'] = 'N';
	if(!$cfg['ts_datetype']) $cfg['ts_datetype'] = '1';

?>
<form method="post" action="./index.php" target="hidden<?=$now?>">
	<input type="hidden" name='body' value="promotion@timesale.exe">
	<input type="hidden" name="ts_datetype" value="1">
	<table class="tbl_row">
		<caption>한정시간판매/타임 세일 설정</caption>
		<colgroup>
			<col style="width:150px;">
			<col>
			<col style="width:40%;">
		</colgroup>
		<tr>
			<th scope="row">사용여부</th>
			<td colspan="2">
				<label><input type="radio" name="ts_use" value="Y" <?=checked($cfg['ts_use'], 'Y')?>> 사용함</label>
				<label><input type="radio" name="ts_use" value="N" <?=checked($cfg['ts_use'], 'N')?>> 사용안함</label>
			</td>
		</tr>
		<tr>
			<th scope="row">타이머 설정</th>
			<td>
				<label>
					<input type="checkbox" name="use_ts_mark_1" value="Y" <?=checked($cfg['use_ts_mark_1'], 'Y')?> class="timesale">
					일 <input type="text" name="ts_mark_1" value="<?=inputText($cfg['ts_mark_1'])?>" class="input timesale" size="4">
				</label>
				<label>
					<input type="checkbox" name="use_ts_mark_2" value="Y" <?=checked($cfg['use_ts_mark_2'], 'Y')?> class="timesale">
					시 <input type="text" name="ts_mark_2" value="<?=inputText($cfg['ts_mark_2'])?>" class="input timesale" size="4">
				</label>
				<label>
					<input type="checkbox" name="use_ts_mark_3" value="Y" <?=checked($cfg['use_ts_mark_3'], 'Y')?> class="timesale">
					분 <input type="text" name="ts_mark_3" value="<?=inputText($cfg['ts_mark_3'])?>" class="input timesale" size="4">
				</label>
				<label>
					<input type="checkbox" name="use_ts_mark_4" value="Y" <?=checked($cfg['use_ts_mark_4'], 'Y')?> class="timesale">
					초 <input type="text" name="ts_mark_4" value="<?=inputText($cfg['ts_mark_4'])?>" class="input timesale" size="4">
				</label>
				<ul class="list_info tp">
					<li>등록한 타이머 노출설정은 우측 미리보기로 확인하실 수 있습니다.</li>
					<li>타이머를 노출 시킬 위치에 {{$타임세일타이머}} 코드가 삽입되어있어야 합니다.</li>
				</ul>
			</td>
			<td class="lb">
				<h4>타이머 미리보기</h4>
				<span class="timer_sample"></span>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" id="stpBtn" value="확인"></span>
	</div>
</form>
<script type="text/javascript">
	(setTimerSample = function() {
		var str = '';
		for(var key = 1; key <= 4; key++) {
			if($(':checked[name=use_ts_mark_'+key+']').length == 1) {
				str += '10'+$('input[name=ts_mark_'+key+']').val();
			}
			$('.timer_sample').html(str);
		}
	})();
	$('.timesale').bind({
		'change' : setTimerSample,
		'keyup' : setTimerSample
	});
</script>