<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  관리자 후기 작성
	' +----------------------------------------------------------------------------------------------+*/

	$no = numberOnly($_GET['cno']);
	if($no) {
		$data = $pdo->assoc("select * from $tbl[often_comment] where `no`='$no'");
		$data = array_map('stripslashes', $data);
	}

?>
<form name="" method="post" action="./" target="hidden<?=$now?>">
	<input type='hidden' name='body' value='member@often_comment_register.exe' />
	<input type='hidden' name='no' value='<?=$no?>' />

	<div class="box_title first">
		<h2 class="title">자주쓰는 댓글 설정</h2>
	</div>
	<table class="tbl_row">
	<colgroup>
		<col style="width:15%">
	</colgroup>
	<tr>
		<th scope="row">분류</th>
		<td>
			<div class="select">
				<?=selectArray($_often_cate_name, "cate", 2, "선택", $data['cate'])?>
			</div>
		</td>
	</tr>
	<tr>
		<th scope="row">제목</th>
		<td><input type="text" name="title" value = "<?=$data['title']?>"class="input" size="90"></td>
	</tr>
	<tr>
		<th scope="row">내용</th>
		<td><textarea name="content" class='txta' style='width:670px; height: 100px;'><?=$data['content']?></textarea></td>
	</tr>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="작성"></span>
		<span class="box_btn gray"><input type="button" value="닫기" onclick="self.close();"></span>
	</div>
</form>