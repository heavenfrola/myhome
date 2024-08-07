<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  회원 - 탈퇴요청 내역
	' +----------------------------------------------------------------------------------------------+*/

	$tmp=explode(":::::",$amember[withdraw_content]);
	$content=stripslashes($tmp[0]);
	$rdate=($amember[withdraw]=='Y') ? date("Y/m/d H;i",$tmp[1]) : "";

	$withdraw_stat=($amember[withdraw]=='Y') ? "탈퇴요청회원" : "일반회원";
	$content=(!$content && $amember[withdraw]=='Y') ? "관리자가 탈퇴요청회원으로 변경하였습니다." : $content;

?>
<table class="tbl_col tbl_col_bottom">
	<caption class="hidden">탈퇴요청 내역</caption>
	<colgroup>
		<col style="width:200px;">
	</colgroup>
	<thead>
		<tr>
			<th scope="col">탈퇴요청 일시</th>
			<th scope="col">상태</th>
			<th scope="col">탈퇴요청 사유</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><?=$rdate?></td>
			<td><?=$withdraw_stat?></td>
			<td><?=$content?></td>
		</tr>
	</tbody>
</table>