<?PHP

	if(empty($cfg['counsel_use_editor']) == true) $cfg['counsel_use_editor'] = 'N';

?>
<form name="" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="ckQnaConfig(this)">
<input type="hidden" name="body" value="config@config.exe">
<input type="hidden" name="config_code" value="product_qna">
	<table class="tbl_row">
		<caption class="hidden">1:1상담 설정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tr>
			<th scope="row">에디터</th>
			<td>
				<label class="p_cursor"><input type="radio" name="counsel_use_editor" value="N" <?=checked($cfg['counsel_use_editor'], 'N')?>> 사용안함</label><br>
				<label class="p_cursor"><input type="radio" name="counsel_use_editor" value="Y" <?=checked($cfg['counsel_use_editor'], 'Y')?>> 사용함</label>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>