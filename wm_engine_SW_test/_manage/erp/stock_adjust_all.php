<form name="joinFrm" method="post" action="./index.php" target="hidden<?=$now?>">
	<div class="box_title first">
		<h2 class="title">일괄 재고 조정 엑셀파일 정보</h2>
	</div>
	<div class="box_bottom top_line">
		<ul class="list_msg left">
			<li>재고조정 화면에서 [엑셀다운]한 파일에서 조정재고, 조정사유를 입력하시고 저장하시기 바랍니다.</li>
			<li>재고번호, 조정재고, 조정사유가 빈 공란일 경우 또는 숫자가 아닐 경우 제외하고 처리됩니다.</li>
			<li>저장한 파일을 아래 [파일첨부] 란에 등록하시고 [확인] 버튼을 클릭하면 일괄 업로드가 처리됩니다.</li>
			<li>엑셀 저장시 반드시 <span class="p_color2">'97-2003 통합문서'</span> 형식으로 저장 해 주십시오.</li>
		</ul>
	</div>
</form>
<form name="joinFrm" method="post" action="./index.php" target="hidden<?=$now?>" enctype="multipart/form-data" onsubmit="return excel_send(this);">
	<input type="hidden" name="body" value="erp@stock_adjust_all.exe">
	<div class="box_title">
		<h2 class="title">일괄 재고 조정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">일괄 재고 조정</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">엑셀 파일 첨부</th>
			<td><input type="file" name="xls" class="input input_full"></td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>

<script type="text/javascript">
	function excel_send(f){
		if(!f.xls.value){ alert("엑셀 파일을 첨부하세요."); return false; }
        printLoading();
	}
</script>