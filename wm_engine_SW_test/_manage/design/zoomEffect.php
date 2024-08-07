<?PHP

	if(!$cfg['zd_use']) $cfg['zd_use'] = 'N';
	if(!$cfg['zd_width']) $cfg['zd_width'] = $cfg['thumb2_w_mng'];
	if(!$cfg['zd_height']) $cfg['zd_height'] = $cfg['thumb2_h_mng'];
	if(!$cfg['zd_margin']) $cfg['zd_margin'] = 10;
	if(!$cfg['zd_cursorc']) $cfg['zd_cursorc'] = '#666666';
	if(!$cfg['zd_cursorb']) $cfg['zd_cursorb'] = '#ffffff';
	if(!$cfg['zd_cursora']) $cfg['zd_cursora'] = '50';
	if(!$cfg['zd_drag']) $cfg['zd_drag'] = 'false';
	$_opacity = array();
	for($i = 0; $i <= 100; $i+=10) {
		$_opacity[] = $i;
	}

?>
<form method="post" action="?" target="hidden<?=$now?>" onsubmit="return formcheck(this)">
	<input type="hidden" name="body" value="config@config.exe">
	<div class="box_title first">
		<h2 class="title">상품이미지 줌이펙트 설정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">상품이미지 줌이펙트 설정</caption>
		<colgroup>
			<col style="width:15%">
			<col>
			<col style="width:550px">
		</colgroup>
		<tr>
			<th scope="row">사용여부</th>
			<td>
				<label class="p_cursor"><input type="radio" name="zd_use" value="Y" <?=checked($cfg['zd_use'], 'Y')?>> 사용함</label><br>
				<label class="p_cursor"><input type="radio" name="zd_use" value="N" <?=checked($cfg['zd_use'], 'N')?>> 사용안함</label>
			</td>
			<td rowspan="8" class="sample">
				<img src="<?=$engine_url?>/_manage/image/design/zoomeffect/sample.jpg" alt="sample">
				<p class="explain icon left">상품상세이미지에 마우스를 올릴 경우 큰이미지를 돋보기로 확대해 보듯이<br>보여주는 기능입니다.</p>
			</td>
		</tr>
		<tr>
			<th scope="row"><strong>확대창가로길이</strong></th>
			<td><input type="text" name="zd_width" class="input" size="10" value="<?=$cfg['zd_width']?>"> 픽셀</td>
		</tr>
		<tr>
			<th scope="row"><strong>확대창세로길이</strong></th>
			<td><input type="text" name="zd_height" class="input" size="10" value="<?=$cfg['zd_height']?>"> 픽셀</td>
		</tr>
		<tr>
			<th scope="row"><strong>확대창 위치</strong></th>
			<td>
				상품상세이미지의
				<select name="zd_position">
					<option value="right" <?=checked($cfg['zd_position'], 'right', 1)?>>우측</option>
					<option value="left" <?=checked($cfg['zd_position'], 'left', 1)?>>좌측</option>
					<option value="top" <?=checked($cfg['zd_position'], 'top', 1)?>>상단</option>
					<option value="bottom" <?=checked($cfg['zd_position'], 'bottom', 1)?>>하단</option>
				</select>
				<input type="text" name="zd_margin" class="input" size="10" value="<?=$cfg['zd_margin']?>"> 픽셀
			</td>
		</tr>
		<tr>
			<th scope="row">커서테두리색상</th>
			<td>
				<span id="c_color" class="box_color" style="background:<?=$cfg['zd_cursorc']?>"></span>
				<input type="text" id="c_code" name="zd_cursorc" class="input" size="10" value="<?=$cfg['zd_cursorc']?>">
				<span class="box_btn_s"><input type="button" value="색상선택" onclick="setColor('c');"></span>
			</td>
		</tr>
		<tr>
			<th scope="row">커서배경색상</th>
			<td>
				<span id="b_color" class="box_color" style="background:<?=$cfg['zd_cursorb']?>"></span>
				<input type="text" id="b_code" name="zd_cursorb" class="input" size="10" value="<?=$cfg['zd_cursorb']?>">
				<span class="box_btn_s"><input type="button" value="색상선택" onclick="setColor('b');"></span>
			</td>
		</tr>
		<tr>
			<th scope="row">커서투명도</th>
			<td>
				<?=selectArray($_opacity, 'zd_cursora', 1, '', $cfg['zd_cursora'])?> %
			</td>
		</tr>
		<tr>
			<th scope="row">작동방식</th>
			<td>
				<label class="p_cursor"><input type="radio" name="zd_drag" value="false" <?=checked($cfg['zd_drag'], 'false')?>> 마우스 이동</label><br>
				<label class="p_cursor"><input type="radio" name="zd_drag" value="true" <?=checked($cfg['zd_drag'], 'true')?>> 마우스 드래그</label>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>

<OBJECT id="colorpicker" CLASSID="clsid:3050f819-98b5-11cf-bb82-00aa00bdce0b" width="0" height="0"></OBJECT>
<script type='text/javascript'>
	function setColor(nm){
		var code = $('#'+nm+'_code').val();

		var oTColor = (code) ? colorpicker.ChooseColorDlg(code) : colorpicker.ChooseColorDlg("#FFFFFF");
		var code = oTColor.toString(16);

		if(code.length < 6){
			var sTempString = "000000".substring(0, 6-code.length);
			code = sTempString.concat(code);
		}
		code = '#'+code.toUpperCase();

		$('#'+nm+'_code').val(code);
		$('#'+nm+'_color').css('background', code);
	}

	function formcheck(f) {
		f.zd_width.value = f.zd_width.value.toNumber();
		f.zd_height.value = f.zd_height.value.toNumber();
		f.zd_margin.value = f.zd_margin.value.toNumber();

        printLoading();

		return true;
	}
</script>