<?PHP

	printAjaxHeader();

	$refkey = numberOnly($_POST['refkey']);
	$prdArray = implode(',', numberOnly($_POST['prdArray']));
	if(!$prdArray) return;
	$hres = $pdo->iterator("select no, hash, name, stat, sell_prc, milage, updir, upfile3, w3, h3, partner_no from $tbl[product] where `no` in ($prdArray) and stat!=1 order by field(no,".$prdArray.")");
	$total = $pdo->row("select count(*) from wm_product where `no` in ($prdArray) and stat!=1");

?>
<table class="tbl_col" id="headFrm">
	<colgroup>
		<col style="width:50px">
		<col>
		<col style="width:100px">
		<col style="width:100px">
		<col style="width:100px">
		<col style="width:100px">
	</colgroup>
	<thead>
		<tr>
			<th scope="col">순서</th>
			<th scope="col">이름</th>
			<th scope="col">판매금</th>
			<th scope="col">적립금</th>
			<th scope="col">상태</th>
			<th scope="col">삭제</th>
		</tr>
	</thead>
	<tbody>
	<?php
	$idx=0;
    foreach ($hres as $hdata) {
		$idx++;
		$hdata=shortCut($hdata);
		if($_use[file_server] == "Y"){
			if(!$file_server_num) fsConFolder($hdata[updir]);
			$file_dir=$file_server[$file_server_num][url]; // 2008-09-17 : 파일서버와 구분 - Han
		} else {
			$file_dir=$root_url;
		}
		$hdata[name]=strip_tags(stripslashes($hdata[name]));
		if($hdata[upfile3] && (!$_use[file_server] && is_file($root_dir."/".$hdata[updir]."/".$hdata[upfile3]) || $_use[file_server] == "Y")) {
			$is=setImageSize($hdata[w3],$hdata[h3],50,50);
			$hdata[imgstr]="<img src=\"$file_dir/$hdata[updir]/$hdata[upfile3]\" $is[2] align=\"middle\">";
		}
		$view_link = '/shop';
?>
		<tr id="headtr_<?=$hdata['no']?>" data-idx='<?=$hdata['no']?>'>
			<td>
				<?if($admin['partner_no']==0 || ($cfg['partner_prd_ref']=='Y' && $admin['partner_no']>0)) {?>
					<?if($idx>1){?><a onclick="refHeadSort(this, <?=$refkey?>, 'up'); return false;"><img src="<?=$engine_url?>/_manage/image/arrow_up.gif" alt="위로"></a><?}?>
					<?if($idx<=$total){?><a onclick="refHeadSort(this, <?=$refkey?>, 'down'); return false;"><img src="<?=$engine_url?>/_manage/image/arrow_down.gif" alt="아래로"></a><?}?>
				<?}else{?>
				<?=$idx?>
				<?}?>
			</td>
			<td class="left">
				<div class="box_setup">
					<div class="thumb"><a href="<?=$root_url?>/<?=$view_link?>/detail.php?pno=<?=$hdata[hash]?>" target="_blank"><?=$hdata[imgstr]?></a></div>
					<p class="title">
						<?if($admin['partner_no']==0 || ($admin['partner_no']>0 && $hdata['partner_no']==$admin['partner_no'])) {?>
							<a href="?body=product@product_register&pno=<?=$hdata['pno']?>" target="_blank"><?=$hdata['name']?></a>
						<?}else {?>
							<strong><?=$hdata['name']?></strong>
						<?}?>
					</p>
				</div>
			</td>
			<td><?=number_format($hdata[sell_prc])?> 원</td>
			<td><?=number_format($hdata[milage])?> 원</td>
			<td><?=$_prd_stat[$hdata['stat']]?></td>
			<td>
				<?if($admin['partner_no']==0 || ($cfg['partner_prd_ref']=='Y' && $admin['partner_no']>0)) {?>
					<?if($admin['partner_no']==0 || ($admin['partner_no']>0 && $hdata['partner_no']==$admin['partner_no'])) {?>
						<span class="box_btn_s"><a onclick='delHeadRef(<?=$refkey?>,<?=$hdata[no]?>); return false;'>삭제</a></span>
					<?}else{?>
					-
					<?}?>
				<?}else{?>
					-
				<?}?>
			</td>
		</tr>
<?}?>
	</tbody>
</table>