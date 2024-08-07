<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  장바구니 설정
	' +----------------------------------------------------------------------------------------------+*/

	if(!$cfg['cart_direct_order']) $cfg['cart_direct_order']="D";
	if(!$cfg['cart_member_delete']) $cfg['cart_member_delete']="N";
	if(!$cfg['cart_delete_term']) $cfg['cart_delete_term'] = "7";

	$cart_delete_term = array(
		'1' => '1일(24시간)동안',
		'2' => '2일(48시간) 동안',
		'3' => '3일(72시간) 동안',
		'4' => '4일(96시간) 동안',
		'5' => '5일(120시간) 동안',
		'6' => '6일(144시간) 동안',
		'7' => '7일(168시간) 동안',
		'N' => '직접 삭제할 때까지',
	);

?>
<form method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="printLoading()">
	<input type="hidden" name="body" value="config@config.exe">
	<div class="box_title first">
		<h2 class="title">장바구니 설정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">장바구니 설정</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">바로구매 설정</th>
			<td>
				<label class="p_cursor"><input type="radio" name="cart_direct_order" value="Y" <?=checked($cfg['cart_direct_order'],'Y')?>> 선택한 상품만 구매합니다.</label><br>
				<label class="p_cursor"><input type="radio" name="cart_direct_order" value="N" <?=checked($cfg['cart_direct_order'],'N')?>> 장바구니 상품과 함께 구매합니다.</label><br>
				<label class="p_cursor"><input type="radio" name="cart_direct_order" value="D" <?=checked($cfg['cart_direct_order'],'D')?>> 장바구니 상품과 함께 구매여부를 선택합니다.</label>
				<ul class="list_info">
					<li>네이버페이로 결제 시 설정과 관계 없이 선택한 상품만 구매됩니다.</li>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row">장바구니 보관기간</th>
			<td>
				<label class="p_cursor"><input type="radio" name="cart_member_delete" value="N" <?=checked($cfg['cart_member_delete'],'N')?>> 로그인 시 기존 장바구니를 비웁니다.(추천)</label><br>
				<label class="p_cursor"><input type="radio" name="cart_member_delete" value="Y" <?=checked($cfg['cart_member_delete'],'Y')?>> 회원의 경우
					<?=selectArray($cart_delete_term, 'cart_delete_term', null, false, $cfg['cart_delete_term'])?>
					상품을 보관합니다.</label><br>
			</td>
		</tr>
		<tr>
			<th scope="row">중복상품 저장 시</th>
			<td>
				<label class="p_cursor"><input type="radio" name="cart_dup" value="1" <?=checked($cfg['cart_dup'],0)?> <?=checked($cfg['cart_dup'],'1')?>> 기존 수량 유지</label><br>
				<label class="p_cursor"><input type="radio" name="cart_dup" value="2" <?=checked($cfg['cart_dup'],'2')?>> 새로운 수량으로 변경</label><br>
				<label class="p_cursor"><input type="radio" name="cart_dup" value="3" <?=checked($cfg['cart_dup'],'3')?>> 새로운 수량만큼 추가</label><br>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>