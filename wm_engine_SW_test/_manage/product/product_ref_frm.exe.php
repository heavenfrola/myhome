<?PHP

	printAjaxHeader();

	$f_sql = "";
    if ($scfg->comp('use_partner_shop', 'Y') == true) {
		$f_sql = ", b.partner_no, b.dlv_type";
	}else {
		$admin['partner_no'] = 0;
	}
    if ($scfg->comp('use_prd_perm', 'Y') == true) {
        $f_sql .= ", b.perm_lst, b.perm_dtl, b.perm_sch";
    }
	$res = $pdo->iterator("select a.no, b.no as pno, b.hash, b.name, b.stat, b.sell_prc, b.milage, b.updir, b.upfile3, b.w3, b.h3 $f_sql from `$tbl[product_refprd]` a inner join $tbl[product] b on a.refpno=b.no where `pno`='$pno' and `group`='$refkey' and b.stat!=1 order by `sort` asc");
	$total = $pdo->row("select count(*) from `$tbl[product_refprd]` a inner join $tbl[product] b on a.refpno=b.no where `pno`='$pno' and `group`='$refkey' and b.stat in (2,3,4)");

	// 세트상품 등록
	if($refkey == '99') {
		$_ref_sum = $pdo->assoc("select sum(p.normal_prc) as normal_prc, sum(p.sell_prc) as sell_prc from {$tbl['product_refprd']} r inner join {$tbl['product']} p on r.refpno=p.no where r.pno='$pno' and r.group='$refkey' and p.stat in (2,3)");
		$_ref_sum['normal_prc'] = parsePrice($_ref_sum['normal_prc'], true);
		$_ref_sum['sell_prc'] = parsePrice($_ref_sum['sell_prc'], true);

        foreach ($res as $rdata) {
			if($rdata['upfile3']) {
				$file_dir = getFileDir($rdata['updir']);
				$rdata['imgstr'] = "<img src='$file_dir/{$rdata['updir']}/{$rdata['upfile3']}' style='width:50px;'>";
			}
            if ($rdata['dlv_type'] == '1') $rdata['partner_no'] = '0';
			$view_link = $root_url.'/shop/detail.php?pno='.$rdata['hash'];
			$edit_link = './index.php?body=product@product_register&pno='.$rdata['pno'];

            $checked_perms = '';
            if ($cfg['use_prd_perm'] == 'Y') {
                if ($rdata['perm_lst'] != 'Y' && $rdata['perm_dtl'] != 'Y' && $rdata['perm_sch'] != 'Y') {
                    $checked_perms = 'checked';
                }
            }

			$html .= "
			<tr class='ref_sortable_{$refkey}' data-refno='{$rdata['no']}' data-partner_no='{$rdata['partner_no']}'>
				<td style='padding:10px; width:268px;'>
					<div class='box_setup' style='padding-right:0;'>
						<div class='thumb'><a href='$view_link' target='_blank'>{$rdata['imgstr']}</a></div>
						<p class='title'>
							<strong><a href='$edit_link' target='_blank'>".stripslashes($rdata['name'])."</a></strong>
						</p>
						<p class='cstr'>".parsePrice($rdata['sell_prc'], true).' '.$cfg['currency_type']."</p>
						<p class='explain'>".$_prd_stat[$rdata['stat']]."</p>
					</div>
				</td>
                <td>
                    <input type='checkbox' name='perms' value='Y' data-pno='{$rdata['pno']}' $checked_perms onclick='return setPerms(this)'>
                </td>
				<td style='width:79px;'><span class='box_btn_s gray'><input type='button' value='삭제' onclick='delRefPrd(99, {$rdata['no']});'></span></td>
			</tr>
			";
		}

		if($body == 'product@set_register') {
			echo $html;
			return;
		} else {
			header('Content-type:application/json; charset='._BASE_CHARSET_);
			exit(json_encode(array(
                'result' => 'success',
				'normal_prc' => $_ref_sum['normal_prc'],
				'sell_prc' => $_ref_sum['sell_prc'],
				'html' => $html,
                'partner_no' => $rdata['partner_no'],
			)));
		}
	}

?>
<table class="tbl_col">
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
            foreach ($res as $rdata) {
				$idx++;
				$rdata=shortCut($rdata);
				if($_use[file_server] == "Y"){
					if(!$file_server_num) fsConFolder($rdata[updir]);
					$file_dir=$file_server[$file_server_num][url]; // 2008-09-17 : 파일서버와 구분 - Han
				} else {
					$file_dir=$root_url;
				}
				$rdata[name]=strip_tags(stripslashes($rdata[name]));
				if($rdata[upfile3] && (!$_use[file_server] && is_file($root_dir."/".$rdata[updir]."/".$rdata[upfile3]) || $_use[file_server] == "Y")) {
					$is=setImageSize($rdata[w3],$rdata[h3],50,50);
					$rdata[imgstr]="<img src=\"$file_dir/$rdata[updir]/$rdata[upfile3]\" $is[2] align=\"middle\">";
				}
				$view_link = '/shop';
		?>
		<tr id="headtr_<?=$rdata['pno']?>" data-idx='<?=$rdata['pno']?>'>
			<td>
				<?php if ($admin['partner_no']==0 || ($cfg['partner_prd_ref']=='Y' && $admin['partner_no']>0)) { ?>
					<?php if ($admin['partner_no']>0 && $cfg['partner_prd_accept']=='Y') {?>
						<?php if ($idx>1){?><a href="#" onclick="refHeadSort(this, <?=$refkey?>, 'up'); return false;"><img src="<?=$engine_url?>/_manage/image/arrow_up.gif" alt="위로"></a><?php } ?>
						<?php if ($idx<=$total){?><a href="#" onclick="refHeadSort(this, <?=$refkey?>, 'down'); return false;"><img src="<?=$engine_url?>/_manage/image/arrow_down.gif" alt="아래로"></a><?php } ?>
					<?php } else { ?>
						<?php if ($idx>1){?><a href="#" onclick="refPrdSort(<?=$refkey?>,<?=$rdata['no']?>, 'up'); return false;"><img src="<?=$engine_url?>/_manage/image/arrow_up.gif" alt="위로"></a><?php } ?>
						<?php if($idx<=$total){?><a href="#" onclick="refPrdSort(<?=$refkey?>,<?=$rdata['no']?>, 'down'); return false;"><img src="<?=$engine_url?>/_manage/image/arrow_down.gif" alt="아래로"></a><?php } ?>
					<?php } ?>
				<?php } else { ?>
				<?=$idx?>
				<?php } ?>
			</td>
			<td class="left">
				<div class="box_setup">
					<div class="thumb"><a href="<?=$root_url?>/<?=$view_link?>/detail.php?pno=<?=$rdata[hash]?>" target="_blank"><?=$rdata['imgstr']?></a></div>
					<p class="title">
						<?php if ($admin['partner_no']==0 || ($admin['partner_no']>0 && $rdata['partner_no']==$admin['partner_no'])) { ?>
							<a href="?body=product@product_register&pno=<?=$rdata['pno']?>" target="_blank"><?=$rdata['name']?></a>
						<?php } else { ?>
							<strong><?=$rdata['name']?></strong>
						<?php } ?>
					</p>
				</div>
			</td>
			<td><?=number_format($rdata['sell_prc'])?> <?=$cfg['currency_type']?></td>
			<td><?=number_format($rdata['milage'])?> <?=$cfg['currency_type']?></td>
			<td><?=$_prd_stat[$rdata['stat']]?></td>
			<td>
				<?php if ($admin['partner_no']==0 || ($cfg['partner_prd_ref']=='Y' && $admin['partner_no']>0)) { ?>
					<?php if($admin['partner_no']==0 || ($admin['partner_no']>0 && $rdata['partner_no']==$admin['partner_no'])) { ?>
						<?php if ($admin['partner_no']>0 && $cfg['partner_prd_accept']=='Y') { ?>
							<span class="box_btn_s"><a href='#' onclick='delHeadRef(<?=$refkey?>,<?=$rdata[pno]?>); return false;'>삭제</a></span>
						<?php } else { ?>
							<span class="box_btn_s"><a href='#' onclick='delRefPrd(<?=$refkey?>,<?=$rdata[no]?>); return false;'>삭제</a></span>
						<?php } ?>
					<?php }else{ ?>
					-
					<?php } ?>
				<?php } else { ?>
					-
				<?php } ?>
			</td>
		</tr>
		<?php
			}
			if(!$idx){
		?>
		<tr><td colspan="6" class="center">관련상품이 등록되어 있지 않습니다</td></tr>
		<?php } ?>
	</tbody>
</table>
