<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  게시판 분류 설정
	' +----------------------------------------------------------------------------------------------+*/

	$no = numberOnly($_GET['no']);
	if(!$no) msg('필수값이 없습니다', 'back');
	$board = get_info('mari_config', 'no', $no);
	if($board['use_cate'] != 'Y') msg('카테고리를 사용중이 아닙니다.', 'back');

?>
<form name="listFrm" method="post" action="<?=$PHP_SELF?>" target="hidden<?=$now?>" class="pop_width">
	<input type="hidden" name="body" value="board@board_cate.exe">
	<input type="hidden" name="mng" value="<?=$mng?>">
	<input type="hidden" name="db" value="<?=$board[db]?>">
	<input type="hidden" name="exec" value="">
	<table class="tbl_col">
		<caption>분류 설정 : <?=$board[title]?> (<?=$board[db]?>)</caption>
		<colgroup>
			<col>
			<col style="width:80px">
			<col style="width:80px">
		</colgroup>
		<thead>
			<tr>
				<th scope="col">카테고리명</th>
				<th scope="col">순서</th>
				<th scope="col">삭제선택</th>
			</tr>
		</thead>
		<tbody>
			<?php
				$sql="select * from `mari_cate` where `db`='$board[db]' order by `sort`";
				$res = $pdo->iterator($sql);
                foreach ($res as $data) {
			?>
			<input type="hidden" name="no[]" value="<?=$data[no]?>">
			<tr>
				<td class="left"><input type="text" name="name[]" value="<?=$data[name]?>" class="input" size="60"></td>
				<td><input type="text" name="sort[]" value="<?=$data[sort]?>" class="input" size="2"></td>
				<td><input type="checkbox" name="delete_cate[]" value="<?=$data[no]?>"></td>
			</tr>
			<?}?>
		</tbody>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="button" onclick="document.listFrm.submit();" value="설정적용"></span>
		<span class="box_btn gray"><input type="button" onclick="deleteCate(document.listFrm);" value="선택삭제"></span>
		<span class="explain">(삭제시 선택 분류의 게시물이 함께 삭제됩니다)</span>
	</div>
</form>
<form name="" method="post" action="<?=$PHP_SELF?>" target="hidden<?=$now?>" onSubmit="return checkCreateCate(this)">
	<input type="hidden" name="body" value="board@board_cate.exe">
	<input type="hidden" name="exec" value="new">
	<input type="hidden" name="mng" value="<?=$mng?>">
	<input type="hidden" name="db" value="<?=$board[db]?>">
	<div class="box_title">
		<h2 class="title">분류 추가</h2>
	</div>
	<div class="box_middle">
		분류명 : <input type="text" name="name" value="" class="input" size="50">
		<span class="box_btn_s blue"><input type="submit" value="분류 추가"></span>
	</div>
</form>
<div class="pop_bottom top_line">
	<?=$close_btn?>
</div>