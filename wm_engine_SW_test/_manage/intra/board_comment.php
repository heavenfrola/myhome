<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  인트라넷게시판 댓글
	' +----------------------------------------------------------------------------------------------+*/

	$_ctbl=$tbl[intra_comment];

?>
<form name="commentFrm" method="post" target="hidden<?=$now?>" action="<?=$PHP_SELF?>" onsubmit="return comFrmChk(this);">
	<input type="hidden" name="body" value="intra@board.exe">
	<input type="hidden" name="exec" value="comment">
	<input type="hidden" name="db" value="<?=$db?>">
	<input type="hidden" name="no" value="<?=$no?>">
	<input type="hidden" name="cno" value="">
	<?if(authChk("comment")){?>
	<div class="box_full" style="margin:20px 0;">
		<a name="com_write"></a>
		<div class="comment_write">
			<textarea name="ccontent" class="txta" style="width:90%; height:100px;"></textarea>
			<span class="btn"><input type="submit" value="코멘트작성"></span>
		</div>
	</div>
	<?}?>
	<div class="box_full">
		<ul class="list_comment">
			<?
				$csql="select * from `$_ctbl` where `db`='$db' and `ref`='$no' order by `no`";
				$cres = $pdo->iterator($csql);
                foreach ($cres as $cdata) {
					$ccontent=strip_tags($cdata[content]);
					$ccontent=str_replace(chr(13), "<br>", $ccontent);
			?>
			<li>
				<p class="name">
					<b><?=$cdata[name]?></b> (<?=date("Y-m-d H:i", $cdata[reg_date])?>)
					<?if(editAuth($cdata)){?>
					<span class="box_btn_s blue"><input type="button" value="수정" onclick="intraComMod(<?=$cdata[no]?>);"></span>
					<span class="box_btn_s"><input type="button" value="삭제" onclick="intraComDel(<?=$cdata[no]?>);"></span>
					<?}?>
				</p>
				<div>
					<div id="com<?=$cdata[no]?>" style="display:none;"><?=$ccontent?></div>
					<?=$ccontent?>
				</div>
			</li>
			<?}?>
		</ul>
	</div>
</form>