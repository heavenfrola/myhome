<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  주문상품별옵션 엑셀설정
	' +----------------------------------------------------------------------------------------------+*/

	$excel_config = 1;
	include $engine_dir.'/_manage/order/order_product.exe.php';

?>
<form id="configFrm" name="frm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="erp@order_config.exe">
	<div class="box_title first">
		<h2 class="title">주문 상품수량 엑셀설정</h2>
	</div>
	<div class="box_middle2">
		<ul class="list_msg left">
			<li>원하시는 EXCEL 파일의 내용을 하단 오른쪽필드로 순서를 지정해주시기 바랍니다.</li>
			<li>주문 상품수량 통계 내 <u>엑셀로 저장</u> 버튼 클릭 시 다운로드 되는 엑셀의 양식을 설정할 수 있습니다.</li>
		</ul>
	</div>
	<div class="box_middle2 add_fld">
		<div class="fld_list">
			<h3>추가할 필드 선택</h3>
			<select id="sel1" class="select_n" name="list_left" size="16" style="width:100%;" multiple>
				<?php
					foreach($field_list as $key=>$val){
						echo "<option value='$key'>$val</option>";
					}
				?>
			</select>
		</div>
		<div class="add">
			<span class="box_btn_s blue"><input type="button" value="추가하기" onclick="select2.addFromSelect(select1);"></span>
		</div>
		<div class="add_list">
			<h3>파일내용</h3>
			<select id="sel2" class="select_n" name="list_right" size="16" style="width:100%;" multiple>
				<?php
					foreach($field as $key=>$val){
						echo "<option value='$val'>$field_list[$val]</option>";
					}
				?>
			</select>
			<span class="box_btn_s icon delete"><input type="button" value="삭제" onclick="select2.remove();"></span>
			<span class="box_btn_s icon up"><input type="button" value="위로" onclick="select2.move(-1);"></span>
			<span class="box_btn_s icon down"><input type="button" value="아래로" onclick="select2.move(1);"></span>
		</div>
	</div>
</form>
<form id="setFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="return valueSet(this);">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="order_product_config" value="">
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/R2Select.js?ver=20200821"></script>
<script type="text/javascript">
	var select1 = new R2Select('sel1');
	var select2 = new R2Select('sel2');

	function valueSet(f) {
		if(!confirm("현재 순서를 저장하시겠습니까?")) return false;

		var cf = document.getElementById('configFrm');
		var sel = cf.list_right;
		var len = sel.options.length;

		f.order_product_config.value = '';
		for(i = 0; i < len; i++) {
			if(f.order_product_config.value) f.order_product_config.value += ','+sel.options[i].value;
			else f.order_product_config.value += sel.options[i].value;
		}
        printLoading();
		f.submit();
		return false;
	}
</script>