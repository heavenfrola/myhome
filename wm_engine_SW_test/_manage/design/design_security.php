<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  페이지보안 설정
	' +----------------------------------------------------------------------------------------------+*/
?>
<form method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="printLoading()">
	<input type="hidden" name="body" value="config@config.exe">
	<div class="box_title first">
		<h2 class="title">페이지보안 설정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">페이지보안 설정</caption>
		<colgroup>
			<col style="width:14%">
			<col>
		</colgroup>
		<!--
		<tr>
			<th scope="row">인터넷 주소 출력</th>
			<td>
				<label class="p_cursor"><input type="radio" name="secutiry_url" id="s1" value="1" <?=checked($cfg['secutiry_url'],1)?>> 주소 출력</label>
				<span class="explain">(일반적인 설정으로 현재 페이지의 주소를 보여줍니다)</span><br>
				<label class="p_cursor"><input type="radio" name="secutiry_url" id="s2" value="2" <?=checked($cfg['secutiry_url'],2)?>> 주소 고정</label>
				<span class="explain">(프레임을 사용하여, 첫 접속 도메인만 계속 나타납니다)</span>
				<div class="explain p_color2">BGM을 사용하실때는 반드시 주소고정이어야 합니다</div>
			</td>
		</tr>
		<tr>
			<th scope="row">IE 이미지 도구모음 차단</th>
			<td>
				<div><img src="<?=$engine_url?>/_manage/image/design/security/toolbar.gif"></div>
				<label class="p_cursor"><input type="radio" name="secutiry_imgtoolbar" id="s3" value="Y" <?=checked($cfg['secutiry_imgtoolbar'],"Y")?>> 사용(도구모음이 나타나지 않음)</label><br>
				<label  class="p_cursor"><input type="radio" name="secutiry_imgtoolbar" id="s31" value="N" <?=checked($cfg['secutiry_imgtoolbar'],"N")?>> 사용 안함</label>
			</td>
		</tr>
		-->
		<tr>
			<th scope="row">페이지 드래그 방지</th>
			<td>
				<label class="p_cursor"><input type="radio" name="secutiry_drag" id="s4" value="Y" <?=checked($cfg['secutiry_drag'],"Y")?>> 사용</label> <span class="explain">(텍스트 내용을 긁을 수 없게 합니다, 마우스의 드래그와 클릭을 함께 막습니다)</span><br>
				<label class="p_cursor"><input type="radio" name="secutiry_drag" id="s41" value="N" <?=checked($cfg['secutiry_drag'],"N")?>> 사용 안함</label>
			</td>
		</tr>
		<tr>
			<th scope="row">오른쪽 마우스 차단</th>
			<td>
				<label class="p_cursor"><input type="radio" name="secutiry_click" id="s5" value="Y" <?=checked($cfg['secutiry_click'],"Y")?>> 사용</label> <span class="explain">(오른쪽 마우스 버튼을 클릭하지 못하게 합니다)</span><br>
				<label class="p_cursor"><input type="radio" name="secutiry_click" id="s51" value="N" <?=checked($cfg['secutiry_click'],"N")?>> 사용 안함</label>
			</td>
		</tr>
		<tr>
			<th scope="row">게시판 관리자 차단해제</th>
			<td>
				<label class="p_cursor"><input type="radio" name="mng_secutiry_block" id="s7" value="Y" <?=checked($cfg['mng_secutiry_block'],"Y")?>> 사용</label> <span class="explain">(쇼핑몰 관리자로 로그인 시 페이지 드래그 방지기능과 오른쪽 마우스 버튼기능을 해제합니다.)</span><br>
				<label class="p_cursor"><input type="radio" name="mng_secutiry_block" id="s71" value="N" <?=checked($cfg['mng_secutiry_block'],"N").checked($cfg['mng_secutiry_block'],"")?>> 사용 안함</label>
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>