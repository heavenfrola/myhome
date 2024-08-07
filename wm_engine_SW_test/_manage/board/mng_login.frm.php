<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  게시판 관리 - 게시판 관리자 로그인
	' +----------------------------------------------------------------------------------------------+*/

	$ssid = addslashes($_GET['ssid']);
	$session = $db_session_handler->parse($ssid);

	if (empty($session['admin_no']) == true) msg('비정상적인 접속이거나 로그인이 해제 되었습니다');

?>
<form id="prdFrm" method="get" target="_blank">
	<input type="hidden" name="body" value="board@mng_login.exe">
	<input type="hidden" name="exec" value="">
	<input type="hidden" name="ssid" value="<?=$ssid?>">
	<?if($member[level]==1){?>
	<div class="list_info tp">
		<p>게시판 관리자 <u><?=$member[name]?>(<?=$member[member_id]?>)</u>님으로 로그인중입니다.</p>
	</div>
	<?}else{?>
	<select name="mng_no">
		<?php
			$ares = $pdo->iterator("select * from `$tbl[member]` where `level`=1 order by `member_id`");
            foreach ($ares as $adata) {
		?>
		<option value="<?=$adata[no]?>"><?=$adata[name]?> (<?=$adata[member_id]?>)</option>
		<?
			}
		?>
	</select>
	<span class="box_btn_s"><input type="submit" value="로그인" class="btn2"></span>
	<div class="list_info fr">
		<p>게시물 관리는 게시판 관리자로 로그인하셔야 가능합니다.</p>
	</div>
	<?}?>
</form>