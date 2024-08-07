<?PHP

	if($_POST['execmode'] == 'ajax') {
		$pno = numberOnly($_POST['pno']);
		$opno = numberOnly($_POST['opno']);
		$ires = $pdo->iterator("select '$pno' as pno, '$opno' as opno, '0' as add_price");
		$opt_item_idx = numberOnly($_POST['is_add']);
	} else {
        $ires = $pdo->iterator("select * from $tbl[product_option_item] where pno='$pno' and opno='$opno' order by sort asc");
        $opt_item_idx = -1;
	}
	$total_res = $ires->rowCount();

	function parseOptionItem($ires) {
		global $tbl, $opt_item_idx, $total_res, $pdo;

		$item = $ires->current();
        $ires->next();
		if ($item == false) return false;

		$item['iname'] = stripslashes($item['iname']);
		$item['add_price'] = parsePrice($item['add_price']);
		$item['hid_checked'] = ($item['hidden'] == 'Y') ? 'checked' : '';

		if($item['complex_no'] > 0) {
			$prd = $pdo->assoc("select p.name, p.updir, p.upfile3, p.w3, p.h3, e.complex_no, e.opts from $tbl[product] p inner join erp_complex_option e on p.no=e.pno where p.wm_sc=0 and e.del_yn='N' and e.complex_no='$item[complex_no]' order by p.reg_date desc");
			$item['pname'] = inputText(strip_tags($prd['name']));
			if($prd['upfile3']) {
				$file_dir = getFileDir($prd['updir']);
				$item['imgstr'] = "<img src='$file_dir/$prd[updir]/$prd[upfile3]' width='40' height='40'>";
			}
			$item['opt_name'] = getComplexOptionName($prd['opts']);
		}

		$opt_item_idx++;

		if(!$item['no'] || $opt_item_idx == 0) $item['btn_hid_up'] = "visibility: hidden;";
		if(!$item['no'] || $opt_item_idx+1 == $total_res) $item['btn_hid_dn'] = "visibility: hidden;";

		return $item;
	}

?>
<?php while($item = parseOptionItem($ires)) {?>
<tr id="option_row_<?=$opt_item_idx?>" class="option_item_row">
	<td class="left">
		<input type="hidden" name="ino[<?=$opt_item_idx?>]" value="<?=$item['no']?>">
		<input type="hidden" name="complex_no[<?=$opt_item_idx?>]" class="complex_no" value="<?=$item['complex_no']?>">
		<input type="text" name="item[<?=$opt_item_idx?>]" value="<?=inputText($item['iname'])?>" class="input block name">
		<div class="box_setup option_product" style="margin-top: 5px;">
			<div class="thumb"><?=$item['imgstr']?></div>
			<dl>
				<dt><strong class="title p_color"><?=$item['pname']?></strong></dt>
				<dd class="opt_name"><?=$item['opt_name']?></dd>
			</dl>
		</div>
	</td>
	<td><input type="text" name="item_price[<?=$opt_item_idx?>]" value="<?=$item['add_price']?>" class="input block right add_price" size="8"></td>
	<td><input type="checkbox" name="item_hidden[<?=$opt_item_idx?>]" value="Y" <?=$item['hid_checked']?>></td>
	<?if($cfg['use_option_product'] == 'Y') {?>
	<td class="option_product"><span class="box_btn_s"><input type="button" value="검색" onclick="psearch.open('&instance=psearch', <?=$opt_item_idx?>)"></span></td>
	<?}?>
	<td>
		<img src="<?=$engine_url?>/_manage/image/arrow_up.gif" alt="위로" onclick="sortOptionItem(-1, <?=$item['no']?>);" class="p_cursor" style="<?=$item['btn_hid_up']?>">
		<img src="<?=$engine_url?>/_manage/image/arrow_down.gif" alt="아래로" onclick="sortOptionItem(1, <?=$item['no']?>);" class="p_cursor" style="<?=$item['btn_hid_dn']?>">
	</td>
	<td><span class="box_btn_s"><input type="button" value="삭제" onclick="cellDelete('<?=$opt_item_idx?>', '<?=$item['no']?>');"></span></td>
</tr>
<?}?>