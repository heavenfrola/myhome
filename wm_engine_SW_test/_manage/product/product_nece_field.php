<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  판매가, 소비자가 명칭 변경
	' +----------------------------------------------------------------------------------------------+*/

?>
<form name="" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" class="pop_width" onsubmit="printLoading()">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="popup" value="1">
	<div class="box_middle left">
		<p><b>[주의]</b> 상품 등록중이거나 수정중인 경우 등록폼이 새로고침됩니다.<br>작성중인 상품 정보는 먼저 저장해 주세요.</p>
	</div>
	<table class="tbl_row">
		<caption class="hidden">운영자 정보</caption>
		<colgroup>
			<col style="width:30%">
			<col>
		</colgroup>
		<tr>
			<th scope="row"><b>판매가</b> 명칭 변경</th>
			<td>
				<input type="text" name="product_sell_price_name" value="<?=inputText($cfg['product_sell_price_name'])?>" class="input">
				<span class="explain">(실제로 쇼핑몰에서 판매되는 가격)</span>
			</td>
		</tr>
		<tr>
			<th scope="row"><b>소비자가</b> 명칭 변경</th>
			<td>
				<input type="text" name="product_normal_price_name" value="<?=inputText($cfg['product_normal_price_name'])?>" class="input">
				<span class="explain">(판매가와 비교용 가격으로 필수 사항이 아님)</span>
			</td>
		</tr>
	</table>
	<div class="pop_bottom">
		<span class="box_btn_s blue"><input type="submit" value="확인"></span>
		<?=$close_btn?>
	</div>
</form>