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

?>
<form name="edtFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" enctype="multipart/form-data" class="pop_width">
<input type="hidden" name="body" value="design@skin.exe">
<input type="hidden" name="exec" value="skin_config">
<input type="hidden" name="edit_skin" value="<?=$_skin_name?>">
	<table class="tbl_row">
		<caption class="hidden">스킨 환경 설정</caption>
		<colgroup>
			<col style="width:20%">
			<col>
		</colgroup>
		<tr>
			<th scope="row">선택한 스킨</th>
			<td><?=$_GET['skin_name']?></td>
		</tr>
		<?php if ($_GET['type'] != 'mobile') { ?>
		<tr>
			<th scope="row">사이트 정렬</th>
			<td>
				<?php
					if ($_skin['default_layout'] == "fixed") {
						echo "고정된 사용자 레이아웃을 사용하실 경우에는 변경이 불가능합니다";
					} else {
				?>
				<input type="radio" name="site_align" value="left" id="left" <?=checked($_skin['site_align'], "left").checked($_skin['site_align'], "")?>> <label for="left" class="p_cursor">좌측 정렬</label> &nbsp;
				<input type="radio" name="site_align" value="middle" id="middle" <?=checked($_skin['site_align'], "middle")?>> <label for="middle" class="p_cursor">중앙 정렬
				<?php
					}
				?>
			</td>
		</tr>
		<?php } ?>
		<tr>
			<th scope="row">인트로 페이지</th>
			<td>
				<input type="radio" name="intro_use" value="Y" id="intro_use1" <?=checked($_skin['intro_use'], "Y")?>> <label for="intro_use1" class="p_cursor">사용</label> &nbsp;
				<input type="radio" name="intro_use" value="N" id="intro_use2" <?=checked($_skin['intro_use'], "N").checked($_skin['intro_use'], "")?>> <label for="intro_use2" class="p_cursor">사용 안함
			</td>
		</tr>
		<?php if($_GET['type'] != 'mobile') { ?>
		<tr>
			<th scope="row">스킨 배경색상</th>
			<td>
				<input type="text" name="body_color" id="body_color" size="8" class="input" maxlength="7" value="<?=$_skin['body_color']?>" style="color:#000000; background-color:<?=$_skin['body_color']?>; cursor:hand;" onkeyup="if(this.value.length == 7) this.style.backgroundColor=this.value;">
				<span class="box_btn_s"><input type="button" value="색상선택" onClick="Ccolor(this.form.body_color.value);"></span>
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
						echo "없음";
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
		<span class="box_btn_s blue"><input type="submit" value="저장하기"></span>
		<span class="box_btn_s gray"><input type="button" value="창닫기" onclick="window.close();"></span>
	</div>
</form>

<OBJECT id="Cobject" CLASSID="clsid:3050f819-98b5-11cf-bb82-00aa00bdce0b" width="0" height="0"></OBJECT>
<script type="text/javascript">
	function Ccolor(Dcolor){
		if(Dcolor == null) var oTColor = Cobject.ChooseColorDlg("#FFFFFF");
		else var oTColor = Cobject.ChooseColorDlg(Dcolor);
		oTColor = oTColor.toString(16);
		if(oTColor.length < 6){
			var sTempString = "000000".substring(0,6-oTColor.length);
			oTColor = sTempString.concat(oTColor);
		}
		oTColor=oTColor.toUpperCase();
		document.edtFrm.body_color.value="#"+oTColor;
		document.getElementById('body_color').style.backgroundColor = oTColor;
	}
	window.onload=function (){
		this.focus();
	}
</script>