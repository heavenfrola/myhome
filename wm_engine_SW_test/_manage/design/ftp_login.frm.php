<form name="frm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onSubmit="return ckFrm(this)">
<input type="hidden" name="body" value="design@template.exe">
<input type="hidden" name="exec" value="start">
<input type="hidden" name="no" value="<?=$no?>">
	<table class="tbl_row">
		<caption class="hidden">FTP 정보</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">FTP 아이디</th>
			<td><input type="text" name="ftp_id" class="input"></td>
		</tr>
		<tr>
			<th scope="row">FTP 비밀번호</th>
			<td><input type="password" name="ftp_pwd" class="input"></td>
		</tr>
		<tr>
			<th scope="row">접속포트</th>
			<td><input type="text" name="ftp_port" class="input" value="21"></td>
		</tr>
	</table>
	<div class="box_bottom"><?=btn2("접속하기")?></div>
</form>