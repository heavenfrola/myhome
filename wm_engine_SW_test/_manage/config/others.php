<?PHP

	if(empty($cfg['use_bs_portrait']) == true) $cfg['use_bs_portrait'] = 'N';
	if(empty($cfg['use_bs_list_addimg']) == true) $cfg['use_bs_list_addimg'] = 'N';

?>
<form method="post" action="./index.php" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="config_code" value="others">

	<div class="box_title first">
		<h2 class="title">기타 설정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">기타 설정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tr>
			<th scope="row">상품리스트에서 부가이미지 사용</th>
			<td>
				<label><input type="radio" name="use_bs_list_addimg" value="Y" <?=checked($cfg['use_bs_list_addimg'], 'Y')?>> 사용함</label>
				<label><input type="radio" name="use_bs_list_addimg" value="N" <?=checked($cfg['use_bs_list_addimg'], 'N')?>> 사용안함</label>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>