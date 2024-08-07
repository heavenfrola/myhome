<?PHP

	$cfg['cart_gift_list'] = ($cfg['cart_gift_list']) ? $cfg['cart_gift_list'] : "N";

	/* +----------------------------------------------------------------------------------------------+
	' |  사은품 설정
	' +----------------------------------------------------------------------------------------------+*/

?>
<form method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" id="search">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="config_code" value="gift_config">
	<div class="box_title first">
		<h2 class="title">사은품 설정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">사은품 설정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
			<tr>
				<th scope="row">사은품 복수선택</th>
				<td>
					<label><input type="radio" name="order_gift_multi" value="N" <?=checked($cfg['order_gift_multi'],"N").checked($cfg['order_gift_multi'],"")?>> 불가능</label>
					<label><input type="radio" name="order_gift_multi" value="Y" <?=checked($cfg['order_gift_multi'],"Y")?>> 가능</label>
					( <input type="text" name="order_gift_multi_ea" class="input right" size="3" value="<?=$cfg['order_gift_multi_ea']?>"> 개까지 선택가능 )
					<div class="list_info tp">
						<p>개수 미입력 시 제한없이 사은품 선택이 가능합니다.</p>
					</div>
				</td>
			</tr>
			<tr>
				<th scope="row">사은품 선택시점</th>
				<td>
					<label><input type="radio" name="order_gift_timing" value="order" <?=checked($cfg['order_gift_timing'], 'order')?>> 주문 시 선택</label>
					<label><input type="radio" name="order_gift_timing" value="" <?=checked($cfg['order_gift_timing'], '')?>> 주문완료 후 선택</label>
					<div class="list_info tp">
						<p>사은품 선택시점에 따라 주문서 및 주문 완료 페이지 내 {{$사은품리스트}} 디자인코드 삽입 및 편집유무를 확인해 주세요.</p>
					</div>
				</td>
			</tr>
			<tr>
				<th scope="row">장바구니 페이지 내<br>사은품목록 표시</th>
				<td>
					<label><input type="radio" name="cart_gift_list" value="A" <?=checked($cfg['cart_gift_list'],"A")?>> 전체 노출</label>
					<label><input type="radio" name="cart_gift_list" value="Y" <?=checked($cfg['cart_gift_list'],"Y").checked($cfg['cart_gift_list'],"")?>> 선택가능 사은품 노출</label>
					<label><input type="radio" name="cart_gift_list" value="N" <?=checked($cfg['cart_gift_list'],"N")?>> 사용안함</label>
					<div class="list_info tp">
						<p>사용 전 장바구니 페이지 내 {{$사은품리스트}} 디자인코드 삽입 및 편집유무를 확인해 주세요.</p>
					</div>
				</td>
			</tr>
			<?if($cfg['order_gift_term'] == 'Y') {?>
			<tr>
				<th scope="row">증정기간</th>
				<td>
					<label><input type="radio" name="order_gift_term" value="N" <?=checked($cfg['order_gift_term'],"N").checked($cfg['order_gift_term'],"")?>> 무제한</label>
					<label><input type="radio" name="order_gift_term" value="Y" <?=checked($cfg['order_gift_term'],"Y")?>> 기간설정</label>
					<input type="text" name="order_gift_sdate" size="10" maxlength="10" value="<?=$cfg['order_gift_sdate']?>" class="input datepicker"> ~ <input type="text" name="order_gift_edate" size="10" maxlength="10" value="<?=$cfg[order_gift_edate]?>" class="input datepicker">
				</td>
			</tr>
		<?}?>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>

<script type="text/javascript">
	function orderGiftPrd(exec,mode){
		if(!confirm("실행하시겠습니까?")) return;
		hidden<?=$now?>.window.location='./?body=promotion@product_gift_register.exe&exec='+exec+'&mode='+mode;
	}
	function checkJoinConfigFrm(f) {
		if(f.order_gift_member.value == 'N') {
			f.order_gift_first.disabled = true;
		} else {
			f.order_gift_first.disabled = false;
		}

		if(check_method == 1 && join_limit != "A") {
			$('[name="join_birth_use"]').eq(1).attr('disabled' , true);
			f.member_join_birth[1].disabled = true;
		}
	}

	$('.giftConfigEvent', document.getElementById('search')).click(function() {
		checkJoinConfigFrm(this.form);
	});

	checkJoinConfigFrm(document.getElementById('search'));
</script>