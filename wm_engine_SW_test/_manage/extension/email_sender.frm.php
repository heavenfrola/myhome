<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  메일 발송
	' +----------------------------------------------------------------------------------------------+*/

	$email = inputText($_GET['email']);

?>
<script language="javasscript" type="text/javascript" src="<?=$engine_url?>/_manage/R2Na2/R2Na.js"></script>
<form method="post" name="email" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="<?=$_inc[0]?>@email_sender.exe">
	<table class="tbl_row">
		<caption class="hidden">메일발송</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">이메일</th>
			<td><input type="text" name="email" value="<?=$email?>" class="input" size="50"></td>
		</tr>
		<tr>
			<th scope="row">제 목</th>
			<td><input type="text" name="title" value="" class="input" size="80"></td>
		</tr>
		<tr>
			<td colspan="2">
				<textarea id="content1" name="content1" class="txta" cols="100" rows="10"></textarea>
			</td>
		</tr>
	</table>
	<div class="pop_bottom">
		<span class="box_btn_s blue"><input type="submit" value="확인"></span>
		<?=$close_btn?>
	</div>
</form>

<script type="text/javascript">
	window.onload=function (){
		R2Na_Generator("content1");
		document.email.title.focus();
	}
</script>