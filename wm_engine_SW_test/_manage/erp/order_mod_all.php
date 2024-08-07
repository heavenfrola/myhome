<?PHP

	$order = $pdo->assoc("select order_stat from erp_order where order_no='$ono'");

?>
<form name="joinFrm" method="post" action="./index.php" target="hidden<?=$now?>">
	<div class="box_title first">
		<h2 class="title">일괄 재고 조정 엑셀파일 정보</h2>
	</div>
	<div class="box_bottom top_line">
		<ul class="list_msg left">
			<li>발주상세 메뉴의 <span class="p_color2">'입고처리용 엑셀다운'</span>한 파일에서 발주수량, 발주금액을 수정 해 주시비 바랍니다.</li>
			<li>발주상세번호 란의 내용을 훼손하시면 발주서 일괄수정이 처리 되지 않습니다.</li>
			<li>발주수량과 발주단가는 반드시 숫자로 입력해 주셔야 하며, 발주수량이 입고수량보다 적을경우 처리되지 않습니다.</li>
			<li>예시 .<br><img src="<?=$engine_url?>/_manage/image/erp/order_mod.png"></li>
			<li>엑셀 저장시 반드시 <span class="p_color2">'97-2003 통합문서'</span> 형식으로 저장 해 주십시오.</li>
		</ul>
	</div>
</form>
<form name="joinFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" enctype="multipart/form-data" onsubmit="return excel_send(this);">
	<input type="hidden" name="body" value="erp@order_mod_all.exe">
	<input type="hidden" name="order_no" value="<?=$ono?>">
	<div class="box_title">
		<h2 class="title">발주서 일괄수정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">발주서 일괄수정</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">엑셀 파일 첨부</th>
			<td><input type="file" name="xls" class="input input_full"></td>
		</tr>
	</table>
	<div class="box_bottom">
		<span id="field_btn" class="box_btn blue"><input type="submit" value="확인"></span>
		<span id="field_wait" style="display:none;">처리중입니다...</span>
	</div>
</form>

<script type="text/javascript">
	function excel_send(f){
		if(!f.xls.value){
			window.alert("엑셀 파일을 첨부하세요");
			return false;
		}

		$('#field_btn').hide();
		$('#field_wait').show();
	}
</script>