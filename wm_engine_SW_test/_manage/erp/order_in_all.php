<form name="joinFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="">
	<div class="box_title first">
		<h2 class="title">일괄 발주서 엑셀파일 정보</h2>
	</div>
	<div class="box_bottom top_line">
		<ul class="list_msg left">
			<li>발주한 발주 상세 화면에서 [입고처리용 엑셀다운]한 파일에서 입고수량란에 입고수량을 입력하시고 저장하시기 바랍니다.</li>
			<li>저장한 파일을 아래 [파일첨부] 란에 등록하시고 [확인] 버튼을 클릭하면 일괄 업로드가 처리됩니다.</li>
			<li><span class="p_color2">'발주수량 - 기입고수량'</span>을 초과한 입고를 처리하지 않으니 주의하시기 바랍니다.</li>
			<li>예시 .<br><img src="<?=$engine_url?>/_manage/image/erp/order_detail.jpg"></li>
			<li>엑셀 저장시 반드시 <span class="p_color2">'97-2003 통합문서'</span> 형식으로 저장 해 주십시오.</li>
		</ul>
	</div>
</form>
<form name="joinFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" enctype="multipart/form-data" onsubmit="return excel_send(this);">
	<input type="hidden" name="body" value="erp@order_in_all.exe">
	<div class="box_title">
		<h2 class="title">일괄 발주서 입고</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">일괄 발주서 입고</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">엑셀 파일 첨부</th>
			<td><input type="file" name="xls" class="input input_full"></td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="입고처리"></span>
	</div>
</form>
<form id="overFrm" target="hidden<?=$now?>" style="display:none;">
	<input type="hidden" name="body" value="erp@order_list_dexcel.exe">
	<input type="hidden" name="set" value="in">
	<input type="hidden" name="order_dtl_no" value="">
	<div class="box_title">
		<h2 class="title">초과입고 엑셀 다운로드</h2>
	</div>
	<div class="box_middle left">
		<div class="p_color2">입고수량이 발주수량을 초과하여 처리되지 못한 내역들을 다운로드 합니다.</div>
		<ul class="list_msg">
			<li>
				1. 다운로드 된 엑셀파일을 참조로 발주서의 발주수량을 수정해 주십시오.<br>
				수정은 발주내역 상세보기 좌측 하단의 발주수정이나 일괄수정 버튼을 이용하시면 됩니다.
			</li>
			<li>2. 발주서 수정이 완료된 후 해당 내역들만 다시 '일괄발주서 입고' 하시면 실패된 건들에 대한 입고가 완료됩니다.</li>
		</ul>
	</div>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="초과입고 엑셀 다운로드"></span>
	</div>
</form>

<script type="text/javascript">
	function excel_send(f){
		if(!f.xls.value){ alert("엑셀 파일을 첨부하세요"); return false; }
	}
</script>