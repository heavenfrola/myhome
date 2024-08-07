<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  페이지선택 편집
	' +----------------------------------------------------------------------------------------------+*/

	$_skin['pageres_design_use'] = $_skin['pageres_design_use'] ? $_skin['pageres_design_use'] : "N";

?>
<div class="contentFrm">
	<ul class='desc1 square'>
		<li><?=editSkinNotice()?></li>
	</ul>
</div>

<form name="editFrm" action="<?=$PHP_SELF?>" method="post" target="hidden<?=$now?>" class="register">
<input type="hidden" name="body" value="design@editor.exe">
<input type="hidden" name="exec" value="skin_vals">

	<table cellpadding='0' cellspacing='0' style="border-left:1px solid #D4D4D4;">
		<tr>
			<th>페이지 선택 디자인 변경</th>
			<td>
				<input type="radio" name="skin[pageres_design_use]" value="Y" <?=checked($_skin['pageres_design_use'], "Y")?>> 예
				<input type="radio" name="skin[pageres_design_use]" value="N" <?=checked($_skin['pageres_design_use'], "N")?>> 아니오
			</td>
		</tr>
		<tr>
			<th>일반 페이지 선택 버튼</th>
			<td>
				폰트 사이즈 : <input type="text" name="skin[pageres_font_size]" size="5" maxlength="5" value="<?=$_skin['pageres_font_size']?>" class="input"> pt &nbsp;
				폰트 색상 : <input type="text" name="skin[pageres_font_color]" size="8" maxlength="7" value="<?=$_skin['pageres_font_color']?>" class="input"> <input type="button" value="색상" onClick="Ccolor(this.form['skin[pageres_font_color]'].value, this.form['skin[pageres_font_color]'], this);" style="background-color:<?=$_skin['pageres_font_color']?>; border:1px solid #DDDDDD; font-size:8pt; height:19px; cursor:hand;">&nbsp;
				앞 뒤 연결 문자 : <input type="text" name="skin[pageres_font_deco1]" size="5" value="<?=inputText($_skin['pageres_font_deco1'])?>" class="input">
				숫자 <input type="text" name="skin[pageres_font_deco2]" size="5" value="<?=inputText($_skin['pageres_font_deco2'])?>" class="input">
			</td>
		</tr>
		<tr>
			<th>선택된 페이지</th>
			<td>
				폰트 사이즈 : <input type="text" name="skin[pageres_this_size]" size="5" maxlength="5" value="<?=$_skin['pageres_this_size']?>" class="input"> pt &nbsp;
				폰트 색상 : <input type="text" name="skin[pageres_this_color]" size="8" maxlength="7" value="<?=$_skin['pageres_this_color']?>" class="input"> <input type="button" value="색상" onClick="Ccolor(this.form['skin[pageres_this_color]'].value, this.form['skin[pageres_this_color]'], this);" style="background-color:<?=$_skin['pageres_this_color']?>; border:1px solid #DDDDDD; font-size:8pt; height:19px; cursor:hand;">&nbsp;
				앞 뒤 연결 문자 : <input type="text" name="skin[pageres_this_deco1]" size="5" value="<?=inputText(stripslashes($_skin['pageres_this_deco1']))?>" class="input">
				숫자 <input type="text" name="skin[pageres_this_deco2]" size="5" value="<?=inputText(stripslashes($_skin['pageres_this_deco2']))?>" class="input">
			</td>
		</tr>
<?
	$_tmp_arr=array("prev"=>"[이전] 블럭", "next"=>"[다음] 블럭", "start"=>"[처음] 선택", "end"=>"[끝] 선택");
	foreach($_tmp_arr as $key=>$val){
		$_skin['pageres_'.$key.'_type']=$_skin['pageres_'.$key.'_type'] ? $_skin['pageres_'.$key.'_type'] : "text";
		$_val_text=@preg_replace("/\[(.*)\](.*)/", "$1", $val);
		$_skin['pageres_'.$key.'_text']=$_skin['pageres_'.$key.'_text'] ? $_skin['pageres_'.$key.'_text'] : "[".$_val_text."]";
?>
		<tr>
			<th><?=$val?> 버튼</th>
			<td>
				<input type="radio" name="skin[pageres_<?=$key?>_type]" value="img" <?=checked($_skin['pageres_'.$key.'_type'], "img")?>> 이미지 경로 : {{$이미지경로}}/<input type="text" name="skin[pageres_<?=$key?>_img]" size="20" value="<?=$_skin['pageres_'.$key.'_img']?>" class="input">
				<font class="help">예) button/button.gif</font><br>
				<input type="radio" name="skin[pageres_<?=$key?>_type]" value="text" <?=checked($_skin['pageres_'.$key.'_type'], "text")?>> 텍스트 구문 : <input type="text" name="skin[pageres_<?=$key?>_text]" size="36" value="<?=$_skin['pageres_'.$key.'_text']?>" class="input">
				<font class="help">예) &lt;strong&gt;[<?=$_val_text?>]&lt;/strong&gt;</font>
			</td>
		</tr>
<?
	}
?>
		<tr>
			<th>이전/다음년도 선택 버튼</th>
			<td>
				<p class='desc2'> 리뷰 및 QNA 를 연도별로 페이지 처리하시면 게시물이 많은 사이트의 경우 사이트 속도가 대폭 상승됩니다.</p>
				<ul class='desc1 square'>
					<li><input type='checkbox' name='skin[rev_year_split]' value='Y' <?=checked($_skin['rev_year_split'],'Y')?> /> 리뷰 리스트를 년도별 페이지 처리</li>
					<li><input type='checkbox' name='skin[qna_year_split]' value='Y' <?=checked($_skin['qna_year_split'],'Y')?> /> QNA 리스트를 년도별 페이지 처리</li>
				</ul>

				폰트 사이즈 : <input type="text" name="skin[pageres_year_size]" size="5" maxlength="5" value="<?=$_skin['pageres_year_size']?>" class="input"> pt &nbsp;
				폰트 색상 : <input type="text" name="skin[pageres_year_color]" size="8" maxlength="7" value="<?=$_skin['pageres_year_color']?>" class="input"> <input type="button" value="색상" onClick="Ccolor(this.form['skin[pageres_font_color]'].value, this.form['skin[pageres_year_color]'], this);" style="background-color:<?=$_skin['pageres_year_color']?>; border:1px solid #DDDDDD; font-size:8pt; height:19px; cursor:hand;">&nbsp;
				앞 뒤 연결 문자 : <input type="text" name="skin[pageres_year_deco1]" size="5" value="<?=$_skin['pageres_year_deco1']?>" class="input">
				<?=date('Y')-1?> <input type="text" name="skin[pageres_year_deco2]" size="5" value="<?=$_skin['pageres_year_deco2']?>" class="input">
			</td>
		</tr>
	</table>

	<div class='footer'>
		<span class='btn blue large'><input type='submit' value='설정완료' /></span>
		<span class='btn gray large'><input type='button' value='창닫기' onclick="window.close();" /></span>
	</div>

</form>
<?
	designValUnset();
?>
<OBJECT id="Cobject" CLASSID="clsid:3050f819-98b5-11cf-bb82-00aa00bdce0b" width="0" height="0"></OBJECT>
<script type="text/javascript">
<!--
function Ccolor(Dcolor, fd, obj){
	if(Dcolor == null) var oTColor = Cobject.ChooseColorDlg("#FFFFFF");
	else var oTColor = Cobject.ChooseColorDlg(Dcolor);
	oTColor = oTColor.toString(16);
	if(oTColor.length < 6){
		var sTempString = "000000".substring(0,6-oTColor.length);
		oTColor = sTempString.concat(oTColor);
	}
	oTColor=oTColor.toUpperCase();
	fd.value="#"+oTColor;
	obj.style.backgroundColor=oTColor;
}

//-->
</script>