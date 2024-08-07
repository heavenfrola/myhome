<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  게시판 추가항목 설정
	' +----------------------------------------------------------------------------------------------+*/

	$no = numberOnly($_GET['no']);
	if(!$no) msg('게시판 생성 후 추가항목을 설정할 수 있습니다.', 'back');
	$data = $pdo->assoc("select * from `mari_config` where `no`='$no'");

	$title = stripslashes($data['title']);
	$tmp_name = unserialize($data['tmp_name'])

?>
<form name="tempFrm" method="post" action="<?=$PHP_SELF?>" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="board@board_temp.exe">
	<input type="hidden" name="no" value="<?=$no?>">
	<table class="tbl_col">
		<caption>추가항목 설정 : <?=$title?> (<?=$data['db']?>)</caption>
		<colgroup>
			<col style="width:150px">
			<col>
		</colgroup>
		<thead>
			<tr>
				<th scope="col">번호</th>
				<th scope="col">추가항목 명</th>
			</tr>
		</thead>
		<tbody>
			<?for($i = 1; $i <= $cfg['board_add_temp']; $i++) {?>
			<tr>
				<th scope="col"><?=$i?></th>
				<td class="left"><input type="text" name="temp<?=$i?>" value="<?=$tmp_name['temp'.$i]?>" class="input" size="60"></td>
			</tr>
			<?}?>
		</tbody>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
		<span class="box_btn gray"><input type="button" onclick="self.close()" value="닫기"></span>
	</div>
</form>