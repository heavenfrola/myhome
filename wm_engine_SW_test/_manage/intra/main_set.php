<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  인트라넷 설정
	' +----------------------------------------------------------------------------------------------+*/

	adminCheck(2);
	$res = $pdo->iterator("select `no`, `db`, `title` from `$tbl[intra_board_config]` order by `title`");
	$_board=array();
    foreach ($res as $arr) {
		$_board[$arr[no]][db]=$arr[db];
		$_board[$arr[no]][title]=$arr[title];
	}

	$cfg[intra_main_board1]=$cfg[intra_main_board1] ? $cfg[intra_main_board1] : "notice";
	$cfg[intra_main_board2]=$cfg[intra_main_board2] ? $cfg[intra_main_board2] : "community";
	$cfg[intra_main_board3]=$cfg[intra_main_board3] ? $cfg[intra_main_board3] : "_intra_recent_comment_";

?>
<form name="" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="config@config.exe">
	<input type="hidden" name="config_code" value="intra">
	<div class="box_title first">
		<h2 class="title">메인페이지 설정</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">메인페이지 설정</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<?
			$_help[1]="왼쪽에 출력할 게시물을 선택합니다";
			$_help[2]="중앙에 출력할 게시물을 선택합니다";
			$_help[3]="오른쪽에 출력할 게시물을 선택합니다";
			for($ii=1; $ii<=3; $ii++){
		?>
		<tr>
			<th scope="row">게시판 <?=$ii?> 선택</th>
			<td>
				<select name="intra_main_board<?=$ii?>">
					<option value="">======================</option>
					<?
						if($ii == 3) {
					?>
					<option value="_intra_recent_comment_"<?=($cfg['intra_main_board'.$ii] == "_intra_recent_comment_") ? " selected" : ""?>>최근 댓글</option>
					<?
						}
						foreach($_board as $key=>$val){
					?>
					<option value="<?=$_board[$key][db]?>"<?=($cfg['intra_main_board'.$ii] == $_board[$key][db]) ? " selected" : ""?>><?=$_board[$key][title]?></option>
					<?}?>
				</select>
				<span class="explain">(<?=$_help[$ii]?>)</span>
			</td>
		</tr>
		<?}?>
	</table>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="확인"></span>
	</div>
</form>
<?include $engine_dir.'/_manage/intra/att_ck.php';?>