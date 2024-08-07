<?PHP

	include_once $engine_dir.'/_partner/lib/partner_product.class.php';

	printAjaxHeader();

	if(is_array($opt_data) == false) {
		$pno = numberOnly($_POST['pno']);
		$ori_no = numberOnly($_POST['ori_no']);
		$_prd = $pdo->assoc("select partner_stat from $tbl[product] where no='$pno'");

		$pp = new PartnerProduct();
		$pp->setComplexOption($ori_no, $pno);

		include $engine_dir.'/_manage/product/product_pos.inc.php';
	}

?>
<div class="box_title_reg">
	<h2 class="title">재고 리스트</h2>
</div>
<table class="tbl_col">
	<caption class="hidden">재고 리스트</caption>
	<thead>
		<tr>
			<?foreach($set_name as $name) {?>
			<th scope="col"><?=$name?></th>
			<?}?>
			<th scope="col" style="width:120px">품절방식</th>
			<th scope="col" style="width:220px">
				<?if($cfg['use_dooson'] == 'Y') {?>
				SKU
				<?} else {?>
				바코드
				<?}?>
			</th>
			<th scope="col" style="width:130px">재고수량</th>
		</tr>
	</thead>
	<tbody>
		<?
			$idx = -1;
			foreach($opt_data as $key => $val) {
				$key = makeComplexKey($key);
				$item_name = explode('<ss>', $val);
				$data = $pdo->assoc("select complex_no, barcode, force_soldout, qty from erp_complex_option where pno='$pno' and opts='$key'");
				$idx++;

		?>
		<tr>
			<?foreach($item_name as $name) {?>
			<td class="left"><?=$name?></td>
			<?}?>
			<td>
				<select name="erp_soldout[<?=$idx?>]">
					<option value="L" <?=checked($data['force_soldout'], 'L', true)?>><?=$_erp_force_stat['L']?></option>
					<option value="N" <?=checked($data['force_soldout'], 'N', true)?>><?=$_erp_force_stat['N']?></option>
					<option value="Y" <?=checked($data['force_soldout'], 'Y', true)?>><?=$_erp_force_stat['Y']?></option>
				</select>
			</td>
			<td>
				<?if($data['barcode']){?>
					<?=$data['barcode']?>
				<?}else{?>
					<input type="text" class="input" name="erp_barcode[<?=$idx?>]" value="<?=$data['barcode']?>">
				<?}?>
			</td>
			<td>
				<input type="text" class="input right" name="erp_qty[<?=$idx?>]" size="10" value="<?=$data['qty']?>">
				<input type="hidden" name="erp_no[<?=$idx?>]" value="<?=$data['complex_no']?>">
				<input type="hidden" name="erp_optno[<?=$idx?>]" value="<?=$key?>">
			</td>
		</tr>
		<?}?>
	</tbody>
</table>