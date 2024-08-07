<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  예치금 설정
	' +----------------------------------------------------------------------------------------------+*/

	if(!$cfg['is_emoney_cash']) $cfg['is_emoney_cash'] = 'Y';

?>
<form method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="printLoading()">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="config_code" value="emoney_config">
	<div class="box_title first">
		<h2 class="title">예치금 설정</h2>
	</div>
	<div class="box_middle left">
		<dl class="list_msg">
			<dt class="p_color2">예치금이란</dt>
			<dd>1. 주문시 현금처럼 사용가능한 하나의 결제 수단입니다</dd>
			<dd>2. 적립금과 같이 주문 취소시 주문에 사용한 예치금은 자동으로 재충전됩니다</dd>
			<dd>
				3. 적립금과 달리 사용에 따른 <u>제약(결제가능 최소예치금, 최소구매금액등)이 없습니다.</u><br>
				따라서, 현금으로 환불이 불가능할 경우 적립금이 아닌, 예치금으로 충전해줄 것을 권장합니다.
			</dd>
		</dl>
	</div>
	<table class="tbl_row">
		<caption class="hidden">예치금 설정</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">사용 여부</th>
			<td>
				<label class="p_cursor"><input type="radio" name="emoney_use" value="Y" <?=checked($cfg['emoney_use'],"Y")?>> 사용</label><br>
				<label class="p_cursor"><input type="radio" name="emoney_use" value="N" <?=checked($cfg['emoney_use'],"N").checked($cfg['emoney_use'],"")?>> 사용안함</label>
			</td>
		</tr>
		<tr>
			<th scope="row">현금할인 적용</th>
			<td>
				<ul>
					<li><label><input type="radio" name="is_emoney_cash" value="Y" <?=checked($cfg['is_emoney_cash'], 'Y')?>> 예치금을 사용해도 현금 전용 할인이 적용됩니다.</label></li>
					<li><label><input type="radio" name="is_emoney_cash" value="N" <?=checked($cfg['is_emoney_cash'], 'N')?>> 예치금 사용시 현금 전용 할인이 적용되지 않습니다.</label></li>
				</ul>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>