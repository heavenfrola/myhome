<?PHP
	if($cfg['max_cate_depth'] >= 4) {
		$depth4 .= ", depth4";
	}

	$scres = $pdo->iterator("select no, big, mid, small $depth4 from $tbl[product] where wm_sc='$pno' and stat between 2 and 4");
	$scnums = $pdo->row("select count(*) from $tbl[product] where wm_sc='$pno'");

	function parseShortcut($scres) {
		$sc = $scres->current();
        $scres->next();
		if ($sc == false) return false;

		if($sc['big']) $sc['big'] = getCateName($sc['big']);
		if($sc['mid']) $sc['mid'] = "&gt ".getCateName($sc['mid']);
		else $sc['mid'] = '';
		if($sc['small']) $sc['small'] = "&gt ".getCateName($sc['small']);
		else $sc['small'] = '';
		if($sc['depth4']) $sc['depth4'] = "&gt ".getCateName($sc['depth4']);
		else $sc['depth4'] = '';

		return $sc;
	}

?>
<tr>
	<th scope="row">바로가기</th>
	<td>
		<ul class="list_msg">
			<?PHP if($scnums > 0) { while($sc = parseShortcut($scres)) {?>
			<li class='shortcut_<?=$sc['no']?>' style="margin-bottom: 10px;">
				<?=$sc['big']?> <?=$sc['mid']?> <?=$sc['small']?> <?=$sc['depth4']?>
				<a href="javascript:;" onclick="removeShortcut('<?=$sc['no']?>')">[삭제]</a>
			</li>
			<?php }} else { ?>
			<li>등록된 바로가기가 없습니다.</li>
			<?php } ?>
		</ul>
	</td>
</tr>
<script type="text/javascript">
function removeShortcut(scno) {
	if(confirm('선택한 바로가기를 해제하시겠습니까?')) {
		$.post('./index.php', {'body':'product@product_shortcut.exe', 'exec':'remove', 'scno':scno}, function(r) {
			$('.shortcut_'+scno).remove();
		});
	}
}
</script>