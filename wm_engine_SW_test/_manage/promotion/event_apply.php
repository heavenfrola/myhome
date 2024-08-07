<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  상품 일괄 적용
	' +----------------------------------------------------------------------------------------------+*/

?>
<form method="post" action="<?=$_SERVER['PHP_SELF']?>" onSubmit="return checkApplyEvent(this)" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="promotion@event_apply.exe">
	<div class="box_title">
		<h2 class="title">상품 일괄 적용</h2>
	</div>
	<div class="box_middle left">
		<p class="explain">상품관리에서 상품별로 이벤트에서 제외 여부를 설정할 수 있습니다</p>
		<div>
			<b><u>모든 상품</u></b>을 할인/적립/무료배송 이벤트로
			<label class="p_cursor"><input type="radio" name="exec" id="exec" value="1" checked> 적용</label>
			<label class="p_cursor"><input type="radio" name="exec" id="exec" value="2"> 적용해제</label>
		</div>
	</div>
	<div class="box_bottom top_line">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>

<script type="text/javascript">
function checkApplyEvent(f){
	if (f.exec[0].checked) ems='적용';
	else ems='제외';
	if (!confirm('정말로 모든 상품을 이벤트에 '+ems+'시키겠습니까?         ')) return false;
}
</script>