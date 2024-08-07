<?PHP

	/* +----------------------------------------------------------------------------------------------+
	' |  카테고리 리스트
	' +----------------------------------------------------------------------------------------------+*/

	$ii=1;
	if(!$ctype) $ctype=1;
	if($ctype < 10) $cwhere=" and ctype='$ctype'";

	function totalPrds($_cate) {
		$r=setTotalPrds($_cate);
		return $r;
	}

?>
<ul class="catelist">
	<li><img src="<?=$engine_url?>/_manage/image/icon/ic_folder_c.gif"> 전체</li>
	<?php
	$bc_res = $pdo->iterator("select * from `".$tbl['category']."` where `level`='1' $cwhere order by `sort`");
    foreach ($bc_res as $bc) {
		if($bc['name']=="dummy" && $bc['code']=="dummy") continue;

		$ii++;
		if($ctype < 10) $totalPrds="(".totalPrds($bc).")";
		else $totalPrds="";
		?>
		<li id="cateTd<?=$ii?>">
			<a href="javascript:;" onclick="editCate(<?=$bc['no']?>,<?=$ctype?>);chgCateColor(<?=$ii?>)">
				<img src="<?=$engine_url?>/_manage/image//icon/ic_plus.gif">
				<img src="<?=$engine_url?>/_manage/image/icon/ic_folder_c.gif">
				<?=stripslashes($bc['name'])?> <?=$totalPrds?>
			</a>
		</li>
		<?php
	}
	?>
</ul>
<script language="JavaScript">
	var totalCate=<?=$ii?>;
	var ctype='<?=$ctype?>';
	openAllCate();
</script>