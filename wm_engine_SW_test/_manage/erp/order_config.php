<?PHP

	$excel_config = 1;
	include $engine_dir.'/_manage/erp/order_list_dexcel.exe.php';

?>
<form id="configFrm" name="frm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="erp@order_config.exe">
	<div class="box_title first">
		<h2 class="title">엑셀양식</h2>
	</div>
	<div class="box_middle left">
		<ul class="list_msg">
			<li>원하시는 EXCEL 파일의 내용을 하단 오른쪽필드로 순서를 지정해주시기 바랍니다.</li>
			<li>저장된 형식은 윙POS 발주내역 조회 <u>"엑셀다운"</u> 버튼을 클릭하셔서 다운받으실 수 있습니다.</li>
		</ul>
	</div>
	<div class="box_middle add_fld">
		<div class="fld_list">
			<h3>추가할 필드 선택</h3>
			<select id="sel1" class="select_n" name="list_left" size="25" ondblclick="addSelect()">
				<?
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
			<select id="sel2" class="select_n" name="list_right" size="25">
				<?
					foreach($field as $key=>$val){
						echo "<option value='$val'>$field_list[$val]</option>";
					}
				?>
			</select>
			<span class="box_btn_s gray"><input type="button" value="삭제" onclick="select2.remove();"></span>
			<span class="box_btn_s"><input type="button" value="위로" onclick="select2.move(-1);"></span>
			<span class="box_btn_s"><input type="button" value="아래로" onclick="select2.move(1);"></span>
		</div>
	</div>
</form>
<form id="setFrm" method="post" action="./index.php" target="hidden<?=$now?>" onsubmit="return valueSet(this);">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="erp_order_excel" value="">
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>

<script type="text/javascript" src="<?=$engine_url?>/_engine/common/R2Select.js"></script>
<script type="text/javascript">
	var select1 = new R2Select('sel1');
	var select2 = new R2Select('sel2');

	function valueSet(f) {
		if(!confirm("현재 순서를 저장하시겠습니까?")) return false;

		var cf = document.getElementById('configFrm');
		var sel = cf.list_right;
		var len = sel.options.length;

		f.erp_order_excel.value = '';
		for(i = 0; i < len; i++) {
			if(f.erp_order_excel.value) f.erp_order_excel.value += ','+sel.options[i].value;
			else f.erp_order_excel.value += sel.options[i].value;
		}

		f.submit();
		return false;
	}
</script>