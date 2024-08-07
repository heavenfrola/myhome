<?PHP

	if($_SESSION['partner_login_no'] > 0) {
		msg('입점업체 관리자만 접속할 수 있습니다.', 'back');
	}

	list($birth_y, $birth_m, $birth_d) = explode('-', $admin['birth']);

?>
<form method="post" action='./' target="hidden<?=$now?>">
	<input type="hidden" name="body" value="config@staff_edt.exe">

	<table class="tbl_row">
		<caption>관리자정보 수정</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">아이디</th>
			<td><strong><?=$admin['admin_id']?></strong></td>
		</tr>
		<tr>
			<th scope="row">패스워드</th>
			<td><input type="password" name="pwd" class="input" autocomplete="new-password"></td>
		</tr>
		<tr>
			<th scope="row">패스워드 확인</th>
			<td><input type="password" name="pwd_confirm" class="input" autocomplete="new-password"></td>
		</tr>
		<tr>
			<th scope="row"><strong>성명</strong></th>
			<td><input type="text" name="name" class="input" value="<?=inputText($admin['name'])?>"></td>
		</tr>
		<tr>
			<th scope="row">전화번호</th>
			<td><input type="text" name="phone" class="input" value="<?=inputText($admin['phone'])?>"></td>
		</tr>
		<tr>
			<th scope="row"><strong>휴대폰</strong></th>
			<td><input type="text" name="cell" class="input" value="<?=inputText($admin['cell'])?>"></td>
		</tr>
		<tr>
			<th scope="row"><strong>이메일</strong></th>
			<td><input type="text" name="email" class="input" value="<?=inputText($admin['email'])?>"></td>
		</tr>
		<tr>
			<th scope="row">주소</th>
			<td><input type="text" name="address" class="input input_full" value="<?=inputText($admin['address'])?>"></td>
		</tr>
		<tr>
			<th scope="row">생년월일</th>
			<td>
			<select name="birth[]">
				<option value="">====</option>
				<?
					for($ii=(date("Y")-15); $ii>1950; $ii--){
						$selected=($birth_y == $ii) ? " selected" : "";
						echo "<option value='$ii'$selected>$ii</option>";
					}
				?>
			</select> 년
			<select name="birth[]">
				<option value="">==</option>
				<?
					for($ii=1; $ii<=12; $ii++){
						$ii=($ii<10) ? "0".$ii : $ii;
						$selected=($birth_m == $ii) ? " selected" : "";
						echo "<option value='$ii'$selected>$ii</option>";
					}
				?>
			</select> 월
			<select name="birth[]">
				<option value="">==</option>
				<?
					for($ii=1; $ii<=31; $ii++){
						$ii=($ii<10) ? "0".$ii : $ii;
						$selected=($birth_d == $ii) ? " selected" : "";
						echo "<option value='$ii'$selected>$ii</option>";
					}
				?>
			</select> 일
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>