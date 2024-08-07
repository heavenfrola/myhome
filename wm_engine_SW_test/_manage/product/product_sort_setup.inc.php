<form method="post" action="./index.php" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="product@product_sort.exe">
	<input type="hidden" name="exec" value="initial_xy">

	<div class="box_middle3">
		<span class="icon_warning">상품데이터 재정렬이 필요합니다.</span>

		<ul class="list_msg left" style="margin-top: 15px;">
			<li><strong><?=$ctype_name?> 매장분류</strong> 정렬을 처음 사용하기 위해서는 기존 상품 데이터를 재졍렬하셔야 합니다.</li>
			<li>상품이 많은 쇼핑몰의 경우 처리시간이 오래 걸리거나 사이트가 순간적으로 느려질수 있으므로 가급적 접속자가 많은 시간을 피해 진행해 주시기 바랍니다.</li>
		</ul>
	</div>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인" /></span>
	</div>
</form>