<?PHP

	if(!$cfg['ts_use']) $cfg['ts_use'] = 'N';
	if(!$cfg['ts_datetype']) $cfg['ts_datetype'] = '1';

?>
<form method="post" action="./index.php" target="hidden<?=$now?>">
	<input type="hidden" name='body' value="product@product_timesale.exe">
	<input type="hidden" name="ts_datetype" value="1">

	<table class="tbl_row">
		<caption>한정시간판매/타임세일</caption>
		<colgroup>
			<col style="width: 150px;">
			<col>
		</colgroup>
		<tr>
			<th>사용여부</th>
			<td>
				<label><input type="radio" name="ts_use" value="N" <?=checked($cfg['ts_use'], 'N')?>> 사용안함</label>
				<label><input type="radio" name="ts_use" value="Y" <?=checked($cfg['ts_use'], 'Y')?>> 사용함</label>
			</td>
		</tr>
		<!--
		<tr>
			<th>남은시간 표시방법</th>
			<td>
				<ul>
					<li>
						<label><input type="radio" name="ts_datetype" value="1" <?=checked($cfg['ts_datetype'], '1')?>> 남은 날짜 표시</label>
						<p class="p_color" style="padding-left: 27px">
							상품별 판매시간 안내 <input type="text" name="ts_datetype1_msg" class="input" size="50" value="<?=inputText($cfg['ts_datetype1_msg'])?>">
						</p>
						<ul class="list_msg" style="padding-left: 27px">
							<li>ex) 종료 {{$종료시간}} 전</li>
							<li>ex) {{$종료시간}} 후 종료됩니다.</li>
							<li>{{$타임세일타이머}} 코드를 이용해 쇼핑몰에 상품별로 출력할수 있습니다.</li>
						</ul>
					</li>
					<li>
						<label><input type="radio" name="ts_datetype" value="2" disabled> 실시간 타이머</label>
						<ul class="list_msg">
							<li>ex) 00:29:30</li>
							<li>타이머가 실시간으로 변경됩니다.</li>
						</ul>
					</li>
				</ul>
			</td>
		</tr>
		-->
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" id="stpBtn" value="확인"></span>
	</div>
</form>