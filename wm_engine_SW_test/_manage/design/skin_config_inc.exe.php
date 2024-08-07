<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  스킨 환경 설정
	' +----------------------------------------------------------------------------------------------+*/

	include_once $engine_dir."/_engine/include/design.lib.php";
	include_once $engine_dir."/_manage/design/version_check.php";
	$_skin_name=$_GET['skin_name'];
	$_skin_dir=$root_dir."/_skin/".$_skin_name;
	$_skin_url=$root_url."/_skin/".$_skin_name;
	if(!file_exists($_skin_dir."/skin_config.".$_skin_ext['g'])) msg("현재 해당 스킨이 존재하지 않습니다", "close");
	include_once $_skin_dir."/skin_config.".$_skin_ext['g'];

	if(!$_skin['jquery_ver']) $_skin['jquery_ver'] = 'jquery-1.4.min.js';

	$jquery_list = '';
	$fp = opendir($engine_dir.'/_engine/common/jquery');
	while($file = readdir($fp)) {
		if(is_file($engine_dir.'/_engine/common/jquery/'.$file) == false) continue;
		$selected = ($_skin['jquery_ver'] == $file) ? 'selected' : '';
		if(strpos($file, 'ui-') == true) continue;

		$jquery_list .= "<option value='$file' $selected>$file</option>";
	}

	if(empty($_skin['intro_use']) == true) $_skin['intro_use'] = 'N';

?>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/colorpicker/colorpicker.js"></script>
<link rel="stylesheet" href="<?=$engine_url?>/_engine/common/colorpicker/colorpicker.css.php?engine_url=<?=$engine_url?>" type="text/css">
<div id="popupContent" class="popupContent layerPop" style="width:700px;">
	<div id="header" class="popup_hd_line">
		<h1 id="logo"><img src="<?=$engine_url?>/_manage/image/wisa.gif" alt="WISA."></h1>
		<div id="mngTab_pop">스킨설정</div>
	</div>
	<div id="popupContentArea">
		<form name="edtFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" enctype="multipart/form-data" onsubmit="this.target=hid_frame">
		<input type="hidden" name="body" value="design@skin.exe">
		<input type="hidden" name="exec" value="skin_config">
		<input type="hidden" name="edit_skin" value="<?=$_skin_name?>">
			<table class="tbl_row">
				<caption class="hidden">스킨 환경 설정</caption>
				<colgroup>
					<col style="width:28%">
					<col>
				</colgroup>
				<tr>
					<th scope="row">스킨명</th>
					<td>
						<?=$_GET['skin_name']?>
					</td>
				</tr>
				<tr>
					<th scope="row">인트로 페이지</th>
					<td>
						<label class="p_cursor"><input type="radio" name="intro_use" value="Y" id="intro_use1" <?=checked($_skin['intro_use'], 'Y')?>>사용함</label>
						<label class="p_cursor"><input type="radio" name="intro_use" value="N" id="intro_use2" <?=checked($_skin['intro_use'], 'N')?>>사용 안함</label>
						<label class="p_cursor"><input type="radio" name="intro_use" value="M" id="intro_use3" <?=checked($_skin['intro_use'], 'M')?>>회원전용(로그인페이지)</label>
						<label class="p_cursor"><input type="radio" name="intro_use" value="R" id="intro_use4" <?=checked($_skin['intro_use'], 'R')?>>수동지정</label>
					</td>
				</tr>
				<tr>
					<th scope="row">인트로 페이지 리다이렉트 URL</th>
					<td>
						<input type="text" name="intro_url" value="<?=$_skin['intro_url']?>" class="input" size="50">
						<div class="list_info">
							<p>인트로페이지 리다이렉트의 경우 쇼핑몰 내 링크만 가능합니다.</p>
						</div>
					</td>
				</tr>
				<?php if ($_GET['type'] != 'mobile') { ?>
				<tr>
					<th scope="row">스킨 배경색상</th>
					<td style="position:relative;">
						<input type="text" name="body_color" size="8" class="input" maxlength="7" autocomplete="off" value="<?=$_skin['body_color']?>" style="color:#000000; background-color:<?=$_skin['body_color']?>;">
						<div class="colorpicker"></div>
					</td>
				</tr>
				<tr>
					<th scope="row">스킨 배경이미지</th>
					<td>
						<input type="checkbox" name="background_use" id="background_use" value="Y" <?=checked($_skin['background_use'], "Y")?>> <label for="background_use" class="p_cursor"><span class="p_color2">배경이미지 사용</span></label>
						<br>
						<input type="checkbox" name="background_fixed" id="background_fixed" value="Y" <?=checked($_skin['background_fixed'], "Y")?>> <label for="background_fixed" class="p_cursor">배경이미지 고정 (스크롤되지 않음)</label>
						<br>
						<input type="radio" name="background_type" id="background_type1" value="1" <?=checked($_skin['background_type'], "1").checked($_skin['background_type'], "")?>> <label for="background_type1" class="p_cursor">전체 반복</label>
						<br>
						<input type="radio" name="background_type" id="background_type2" value="2" <?=checked($_skin['background_type'], "2")?>> <label for="background_type2" class="p_cursor">세로 반복</label>
						<br>
						<input type="radio" name="background_type" id="background_type3" value="3" <?=checked($_skin['background_type'], "3")?>> <label for="background_type3" class="p_cursor">가로 반복</label>
						<br>
						<input type="radio" name="background_type" id="background_type4" value="4" <?=checked($_skin['background_type'], "4")?>> <label for="background_type4" class="p_cursor">전체 반복 안함</label>
						<br>
						- 저장된 이미지 :
						<?php
							if($_skin['background'] && file_exists($_skin_dir."/img/bg/".$_skin['background'])){
								echo "<a href=\"$_skin_url/img/bg/{$_skin['background']}\" target=\"_blank\">보기</a> ";
							}else{
								echo "없음 ";
							}
						?>
						<input type="file" name="background" class="input">
					</td>
				</tr>
				<?php } ?>
				<tr>
					<th scope="row">jQuery 버전설정</th>
					<td>
						<select name='jquery_ver'>
							<?=$jquery_list?>
						</select>
					</td>
				</tr>
			</table>
			<div class="pop_bottom">
				<span class="box_btn blue"><input type="submit" value="확인"></span>
				<span class="box_btn gray"><input type="button" value="취소" onclick="skinconfig.close();"></span>
			</div>
		</form>

	</div>
</div>
<style type="text/css">
.colorpicker {
	position:absolute;
	top: 0;
	left: 100px;
}
.farbtastic, .farbtastic .wheel {
	top: 0 !important;
}
</style>
<script type="text/javascript">
(checkIntroURL = function() {
	if($(':checked[name=intro_use]').val() == 'R') {
		$('input[name=intro_url]').prop('disabled', false);
	} else {
		$('input[name=intro_url]').prop('disabled', true);
	}
})();

$(document).ready(function() {
	var bg = $('input[name=body_color]');
	bg.bind({
		'focus' : function() {
			if(this.value == '') this.value = '#ffffff';
			$('.colorpicker').show();
			$('.colorpicker').farbtastic(bg);
		},
		'blur' : function() {
			$('.colorpicker').hide();
		},
		'change' : function() {
			$(this).css('background-color', this.value);
		}
	});
});

$(':radio[name=intro_use]').change(checkIntroURL);
</script>
