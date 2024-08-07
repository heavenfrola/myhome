<br><br>
<div id="review_pwd{상품번호}" style="display:none;">
<form name="review_pfrm{상품번호}" target="hidden{NOW}" action="{Rdir}/main/exec.php" method="post" onsubmit="return checkReviewpwdFrm(this);">
<input type="hidden" name="exec_file" value="shop/review_edit.php">
<input type="hidden" name="no" value="{상품번호}">
<input type="hidden" name="exec">
<table border="0" align="center" width="100%" cellpadding="3" cellspacing="0" style="border:3 solid #E3E3E3;">
 <tr>
	<td valign="top" width="100">&nbsp;비밀번호 : </td>
	<td><input type="password" name="pwd" class="input" size="20">
	<input type="submit" value="확인하기" style="border:1 solid #DDDDDD; background-color:#FFFFFF; font-size:9pt; height:20; color:#979A8B; cursor:hand;">
	</td>
 </tr>
</table>
</form>
</div>
<div id="review_modi{상품번호}" style="display:none;">
<form name="review_mfrm{상품번호}" target="hidden{NOW}" action="{Rdir}/main/exec.php" method="post" onsubmit="return checkReviewModiFrm(this)">
<input type="hidden" name="exec_file" value="shop/review_reg.exe.php">
<input type="hidden" name="no" value="{상품번호}">
<table border="0" align="center" width="100%" cellpadding="3" cellspacing="0" style="border:3 solid #E3E3E3;">
 <tr>
	<td valign="top" width="100">&nbsp;평점 : </td>
	<td>{STAR}</td>
 </tr>
 <tr>
	<td colspan="2" height="1" bgcolor="#D8D8D8"></td>
 </tr>
 <tr>
	<td valign="top" width="100">&nbsp;제목 : </td>
	<td>{CATE}<input type="text" name="title" style="width:100%" value="{제목}" class="input"></td>
 </tr>
 <tr>
	<td colspan="2" height="1" bgcolor="#D8D8D8"></td>
 </tr>
 <tr>
	<td valign="top">&nbsp;내용 : </td>
	<td><textarea name="content" style="width:100%" class="txta">{내용}</textarea></td>
 </tr>
 <tr>
	<td colspan="2" align="right"><input type="submit" value="수정하기" style="border:1 solid #DDDDDD; background-color:#FFFFFF; font-size:9pt; height:25; color:#979A8B; cursor:hand;"></td>
 </tr>
</table>
</form>
</div>