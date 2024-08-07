<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  할인 적용
	' +----------------------------------------------------------------------------------------------+*/

	printAjaxHeader();

	if($cfg['use_sbscr'] != 'Y') unset($_order_sales['sale8']);
	foreach($_order_sales as $fn => $fv) {
		if(preg_match("/^sale[0-9]+$/", $fn)) {
			${$fn} = parsePrice($_GET[$fn]);
		}
	}

	$price = numberOnly($_GET['price']);
	if(!$price) $price = 0;

?>
<div id="popupContent" class="popupContent layerPop" style="width:500px;">
	<div id="header" class="popup_hd_line">
		<h1 id="logo"><img src="<?=$engine_url?>/_manage/image/wisa.gif" alt="WISA."></h1>
		<div id="mngTab_pop">주문상품 할인 적용</div>
	</div>
	<div id="popupContentArea">
		<form method="post" class="salePrcFrm" onsubmit="addSaleLayer.confirm(); return false;">
			<input type="hidden" name="idx" value="<?=numberOnly($_GET['idx'])?>">
			<table class="tbl_row">
				<caption>선택 상품금액 <?=number_format($price)?> <?=$cfg['currency']?></caption>
				<colgroup>
					<col style="width:150px;">
					<col>
					<col style="width:120px;">
				</colgroup>
				<tbody>
					<?foreach($_order_sales as $key => $val) { $readOnly = ($key == 'sale9') ? 'readOnly' : '';?>
					<tr>
						<th><?=$val?></th>
						<td><input type="text" name="<?=$key?>" class="input right <?=$readOnly?>" size="7" value="<?=${$key}?>" <?=$readOnly?>> <?=$cfg['currency']?></td>
						<td><input type="text" name="per_<?=$key?>" class="input right calc_saleper <?=$readOnly?>" size="3" maxlength="2" <?=$readOnly?>> %</td>
					</tr>
					<?}?>
				</tbody>
			</table>

			<div class="pop_bottom">
				<span class="box_btn_s blue"><input type="submit" value="확인"></span>
				<span class="box_btn_s gray"><input type="button" value="창닫기" onclick="addSaleLayer.close()"></span>
			</div>
		</form>
	</div>
</div>
<script type="text/javascript">
$('.calc_saleper').keyup(function() { // 퍼센트로 할인금액 계산
	this.value = this.value.replace(/[^0-9]/, '');
	var per = parseInt(this.value);
	if(isNaN(per)) return;
	var sale_prc = (<?=$price?>/100*per).toFixed(currency_decimal);

	// 출력
	var field = this.form.elements[this.name.replace('per_', '')];
	field.value = sale_prc;

});
$('.salePrcFrm input').focus(function() {
	this.select();
});
</script>