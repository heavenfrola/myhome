<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  재고관리 기본값 설정
	' +----------------------------------------------------------------------------------------------+*/

?>
<form name="" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" >
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="popup" value="1">
	<table class="tbl_row">
		<caption class="hidden">재고관리</caption>
		<colgroup>
			<col style="width:30%">
			<col>
		</colgroup>
		<tr>
			<th scope="row">재고관리</br>기본설정</th>
			<td>
				<label class="p_cursor"><input type="radio" name="basic_ea_type" id="basic_ea_type" value="1" <?=checked($cfg['basic_ea_type'], 1)?> > 사용</label>
				<label class="p_cursor"><input type="radio" name="basic_ea_type" id="basic_ea_type" value="2" <?=checked($cfg['basic_ea_type'], 2)?> > 사용안함</label>
			</td>
		</tr>
	</table>
	<div class="pop_bottom">
		<span class="box_btn_s blue"><input type="submit" value="확인"></span>
		<?=$close_btn?>
	</div>
</form>