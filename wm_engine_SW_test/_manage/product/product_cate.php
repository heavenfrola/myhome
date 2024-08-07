<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  추가매장분류 설정
	' +----------------------------------------------------------------------------------------------+*/

	if ($_use[xbig] && !$cfg[xbig_mng]) $cfg[xbig_mng] = $_use[xbig];
	if ($_use[ybig] && !$cfg[ybig_mng]) $cfg[ybig_mng] = $_use[ybig];
	if ($cfg[xbig_name] && !$cfg[xbig_name_mng]) $cfg[xbig_name_mng] = $cfg[xbig_name];
	if ($cfg[ybig_name] && !$cfg[ybig_name_mng]) $cfg[ybig_name_mng] = $cfg[ybig_name];

?>
<form name="" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="config@config.exe">
	<div class="box_title first">
		<h2 class="title">추가분류 설정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">추가분류 설정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
		</colgroup>
		<tr>
			<th scope="row">2차 분류</th>
			<td>
				<ul class="list_msg">
					<li>일반 카테고리 분류를 추가하여 한 상품 당 총 세가지의 분류를 가질수 있습니다</li>
					<li>사이트에서의 사용법은 일반 분류와 같습니다.</li>
				</ul>
				<label class="p_cursor"><input type="radio" name="xbig_mng" value="Y" <?=checked($cfg[xbig_mng],'Y')?>> 사용</label>
				<label class="p_cursor"><input type="radio" name="xbig_mng" value="N" <?=checked($cfg[xbig_mng],'N')?>> 사용안함</label><br>
				<label class="p_cursor">2차분류 명 <input type="text" name="xbig_name_mng" class="input" value="<?=$cfg[xbig_name_mng]?>"></label>
			</td>
		</tr>
		<tr>
			<th scope="row">3차 분류</th>
			<td>
				<label><input type="radio" name="ybig_mng" value="Y" <?=checked($cfg[ybig_mng],'Y')?>> 사용</label>
				<label><input type="radio" name="ybig_mng" value="N" <?=checked($cfg[ybig_mng],'N')?>> 사용안함</label><br>
				3차분류 명 <input type="text" name="ybig_name_mng" class="input" value="<?=$cfg[ybig_name_mng]?>">
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>