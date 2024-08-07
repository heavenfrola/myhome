<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  회원그룹 추가정보
	' +----------------------------------------------------------------------------------------------+*/

	$no = numberOnly($_GET['no']);
	if(!$no || $no>9) msg('잘못된 그룹코드입니다.', 'close');
	$data = $pdo->assoc("select * from $tbl[member_group] where no='$no'");

?>
<form id="groupFrm" method="post" action="./index.php" target="hidden<?=$now?>" onSubmit="return checkReplyCS(this)" enctype="multipart/form-data" class="pop_width">
	<input type="hidden" name="body" value="member@member_group.exe">
	<input type="hidden" name="no" value="<?=$no?>">
	<input type="hidden" name="addInfo" value="Y">
	<table class="tbl_row">
		<caption>회원그룹 추가 정보</caption>
		<colgroup>
			<col style="width:20%;">
		</colgroup>
		<tr>
			<th scope="row">그룹명</th>
			<td><?=$data['name']?></td>
		</tr>
		<tr>
			<th scope="row">로그인 메시지</th>
			<td>
				<textarea name="group_msg" class="txta" cols="50" rows="5"><?=stripslashes($data['group_msg'])?></textarea>
				<label class="p_cursor"><input type="checkbox" name="all" value='Y'> 타그룹에도 같은 메시지를 등록합니다.</label> <span class="box_btn_s"><input type="button" value="메시지 미리보기" onclick="showMsg()"></span>
				<p class="explain">로그인시 메시지창으로 나타납니다.</p>
			</td>
		</tr>
		<tr>
			<th scope="row">그룹별 가격</th>
			<td><label class="p_cursor"><input type="checkbox" name="group_price<?=$no?>" value="Y" <?=checked($cfg['group_price'.$no],'Y')?>> 사용</label></td>
		</tr>
		<tr>
			<th scope="row">그룹 아이콘</th>
			<td><input type="file" name="upfile1" class="input input_full"> <?=delImgStr($data,1)?></td>
		</tr>
		<tr>
			<th scope="row">회원그룹 일괄 변경</th>
			<td>
				<input type="file" name="csv" class="input input_full">
				<p class="explain">'<?=$data['name']?>'으로 등급변경할 회원 아이디를 csv 파일로 업로드 해 주세요.</p>
			</td>
		</tr>
	</table>
	<div class="pop_bottom">
		<span class="box_btn_s blue"><input type="submit" value="확인"></span>
		<?=$close_btn?>
	</div>
</form>

<script language="JavaScript">
	this.focus();
	function showMsg() {
		var f = document.getElementById('groupFrm');
		alert(f.group_msg.value);
	}
</script>