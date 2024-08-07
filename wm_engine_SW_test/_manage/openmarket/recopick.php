<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  레코픽
	' +----------------------------------------------------------------------------------------------+*/

	if(!$cfg['recopick_use']) $cfg['recopick_use'] = '2';

?>
<form name="recopickFrm" method="post" target="hidden<?=$now?>" onsubmit="return criFrm(this);">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="config_code" value="recopick_linkage">
	<div class="box_title first">
		<img src="<?=$engine_url?>/_manage/image/promotion/mm_recopick.png" class="img">
	</div>
	<table class="tbl_row">
		<caption class="hidden">recopick</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">사용여부</th>
			<td><input type="radio" name="recopick_use" id="recopick_use_1" value="1" <?=checked($cfg['recopick_use'],1)?>> <label for="recopick_use_1">사용함</label> <input type="radio" name="recopick_use" id="recopick_use_2" value="2" <?=checked($cfg['recopick_use'],2)?>> <label for="recopick_use_2">사용안함</label></td>
		</tr>
		<tr>
			<th scope="row">URL</th>
			<td>
				<input type="text" name="recopick_url" value="<?=$cfg["recopick_url"]?>" class="input" style="ime-mode:disabled;"> <span class="p_color2">*</span> ex) wisa.co.kr (http제외)
			</td>
		</tr>
		<tr>
			<th scope="row">모바일URL</th>
			<td>
				<input type="text" name="m_recopick_url" value="<?=$cfg["m_recopick_url"]?>" class="input" style="ime-mode:disabled;"> <span class="p_color2">*</span> ex) m.wisa.co.kr (http제외)
			</td>
		</tr>
		<tr>
			<th scope="row">위젯 ID</th>
			<td>
				<input type="text" name="recopick_id" value="<?=$cfg["recopick_id"]?>" class="input" style="ime-mode:disabled;"> <span class="p_color2">*</span> 레코픽관리자->이용가이드의 위젯적용 부분에 div 테그에 해당하는 아이디입니다.
			</td>
		</tr>
		<tr>
			<th scope="row">속성 ID</th>
			<td>
				<input type="text" name="recopick_widget_id" value="<?=$cfg["recopick_widget_id"]?>" class="input" style="ime-mode:disabled;"> <span class="p_color2">*</span> 레코픽관리자->이용가이드의 위젯적용 부분에 data-widget_id의 설정 값입니다.
			</td>
		</tr>
		<tr>
			<th scope="row">모바일 속성 ID</th>
			<td>
				<input type="text" name="m_recopick_widget_id" value="<?=$cfg["m_recopick_widget_id"]?>" class="input" style="ime-mode:disabled;"> <span class="p_color2">*</span> 레코픽관리자->이용가이드의 위젯적용 부분에 data-widget_id의 설정 값입니다.
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<script type="text/javascript">
	function criFrm(f){
		if(f.recopick_use.value == '1') {
			if(!checkBlank(f.recopick_url, '운영사이트 URL을 입력해주세요.')) return false;
			if(!checkBlank(f.recopick_id, '레코픽에서 전달받은 위젯 ID를 입력해주세요.')) return false;
			if(!checkBlank(f.recopick_widget_id, '레코픽에서 전달받은 속성 ID를 입력해주세요.')) return false;
		}

		return true;
	}
</script>