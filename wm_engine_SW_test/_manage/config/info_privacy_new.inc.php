<table class="tbl_row">
	<caption class="hidden">개인정보처리방침</caption>
	<colgroup>
		<col style="width:15%">
		<col>
	</colgroup>
	<tr>
		<td colspan="2"><b>기본 정보</b></td>
	</tr>
	<tr>
		<th scope="row">시행일자</th>
		<td><input type="text" name="company_privacy_date2" value="<?=inputText($cfg['company_privacy_date2'])?>" class="input"></td>
	</tr>
	<tr>
		<th scope="row">개인정보수집항목</th>
		<td><input type="text" name="company_privacy_items" value="<?=inputText($cfg['company_privacy_items'])?>" class="input input_full"></td>
	</tr>
	<tr>
		<th scope="row">개인정보수집방법</th>
		<td><input type="text" name="company_privacy_get" value="<?=inputText($cfg['company_privacy_get'])?>" class="input"></td>
	</tr>

	<tr>
		<td colspan="2"><b>고객서비스 담당부서</b></td>
	</tr>
	<tr>
		<th scope="row">고객서비스 담당부서</th>
		<td><input type="text" name="company_privacy1_part" value="<?=inputText($cfg['company_privacy1_part'])?>" class="input"></td>
	</tr>
	<tr>
		<th scope="row">이메일</th>
		<td><input type="text" name="company_privacy1_email" value="<?=inputText($cfg['company_privacy1_email'])?>" class="input"></td>
	</tr>
	<tr>
		<th scope="row">전화번호</th>
		<td><input type="text" name="company_privacy1_phone" value="<?=inputText($cfg['company_privacy1_phone'])?>" class="input"></td>
	</tr>
	<tr>
		<td colspan="2"><b>개인정보보호책임자</b></td>
	</tr>
	<tr>
		<th scope="row">성명</th>
		<td><input type="text" name="company_privacy2_name" value="<?=inputText($cfg['company_privacy2_name'])?>" class="input"></td>
	</tr>
	<tr>
		<th scope="row">이메일</th>
		<td><input type="text" name="company_privacy2_email" value="<?=inputText($cfg['company_privacy2_email'])?>" class="input"></td>
	</tr>
	<tr>
		<th scope="row">전화번호</th>
		<td><input type="text" name="company_privacy2_phone" value="<?=inputText($cfg['company_privacy2_phone'])?>" class="input"></td>
	</tr>
</table>
<div class="box_bottom">
	<span class="box_btn blue"><input type="submit" value="확인"></span>
</div>