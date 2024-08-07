<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  게시판 댓글
	' +----------------------------------------------------------------------------------------------+*/

	$no = numberOnly($_GET['no']);
	$_ctbl="mari_comment";
	$csql="select * from `$_ctbl` where `ref`='$no' order by `no`";
	if(!$pdo->row("select count(*) from `$_ctbl` where `ref`='$no'")) return;

?>
<div class="box_full" style="margin-top:35px;">
	<ul class="list_comment">
		<?
			$cres = $pdo->iterator($csql);
            foreach ($cres as $cdata) {
				$ccontent=strip_tags($cdata[content]);
				$ccontent=str_replace(chr(13), "<br>", $ccontent);
		?>
		<li>
			<p class="name">
				<a href="javascript:;" onClick="viewMember('<?=$cdata[member_no]?>','<?=$cdata[member_id]?>')"><strong><?=$cdata[name]?></strong></a> (<?=date("Y-m-d H:i", $cdata[reg_date])?>) <span class="box_btn_s"><input type="button" value="삭제" onclick="comDel(<?=$cdata[no]?>);"></span>
			</p>
			<div>
				<?=$ccontent?>
				<div id="com<?=$cdata[no]?>" style="display:none;"><?=$ccontent?></div>
			</div>
		</li>
		<?
			}
		?>
	</ul>
</div>