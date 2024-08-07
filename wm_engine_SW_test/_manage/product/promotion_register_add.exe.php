<?PHP
	
	printAjaxHeader();

	$pgrp_no = ($_GET['pgrp_no'])? numberOnly($_GET['pgrp_no']):numberOnly($pgrp_no);
	if($pgrp_no) {
		$gres = $pdo->iterator("select *, (select count(*) from $tbl[promotion_pgrp_link] where pgrp_no='$pgrp_no' and pno>0) as prd_count from $tbl[promotion_pgrp_list] where no='$pgrp_no'");
        foreach ($gres as $gdata) {
			$name = strip_tags(stripslashes($gdata['pgrp_nm']));
?>
			<tr id="<?=$pgrp_no?>">
				<td class="left"><?=$name?></td>
				<td><?=$gdata['prd_count']?></td>
				<td>
					<span class="box_btn_s"><input type="button" value="수정" onclick="searchGroup(this, '<?=$gdata['no']?>')"></span>
					<span class="box_btn_s"><input type="button" value="삭제" onclick="searchGroupcancel('<?=$gdata['no']?>', '<?=$prno?>')"></span>
				</td>
			</tr>
<?		}
	}

?>