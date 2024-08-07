<form name="prdFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" style="margin:0px">
<input type="hidden" name="body" value="product@product_option.exe">
<input type="hidden" name="exec" value="copy">
<input type="hidden" name="pno" value="<?=$pno?>">
<input type="hidden" name="stat" value="<?=$stat?>">

<table border=0 cellspacing=2 cellpadding=0 class="frm1" width="400">
	<tr>
		<td class="tcol1">선택</td>
		<td class="tcol1" width="30%">이름</td>
		<td class="tcol1">속성</td>
		<td class="tcol1">미리보기</td>
	</tr>
	<?php
	$res = $pdo->iterator("select * from `".$tbl['product_option_set']."` where `stat`='5' $where order by `sort`");
	$total = $res->rowCount();
	$idx=0;
    foreach ($res as $data) {
		$necessary=($data[necessary]=="Y") ? "필수" : "선택";
		$rclass=($idx%2==0) ? "tcol3" : "tcol2";
		$idx++;
		if($data['sort']!=$idx) {
			$pdo->query("update `$tbl[product_option_set]` set `sort`='$idx' where `no`='$data[no]'");
		}
	?>
	<tr>
		<td class="<?=$rclass?>"><input type="checkbox" name="check_pno[]" id="check_pno" value="<?=$data[no]?>"></td>
		<td class="<?=$rclass?>"><?=stripslashes($data['name'])?></td>
		<td class="<?=$rclass?>"><?=$_otype[$data[otype]]?> (<?=$necessary?>)</td>
		<td class="<?=$rclass?>" align="center">
		<a href="javascript:;" onClick="wisaOpen('./pop.php?body=product@product_option_preview&opno=<?=$data['no']?>','pfldopt1')" class="small">미리보기</a>
		</td>
		</td>
	</tr>
	<?}?>
	<tr>
		<td colspan="20" bgcolor="#FFFFFF">
		<table align="center" height="25" width="100%" border=0 cellspacing=0 cellpadding=0>
			<tr valign="bottom">
				<td>
				<?=btn("전체 선택", "checkAll(document.prdFrm.check_pno,true);")?>
				<?=btn("선택 해제", "checkAll(document.prdFrm.check_pno,false);")?>
				</td>
				<td align="right">
				<?=btn("불러오기", "loadOption(document.prdFrm);")?>
				</td>
			</tr>
		</table>
		</td>
	</tr>
	<tr>
		<td colspan="20" align="center" height="50"><?=$close_btn?></td>
	</tr>
</table>

</form>