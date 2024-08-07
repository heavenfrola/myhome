<ul class="product_icon_list">
	<?php
		if($_GET['exec'] == 'refresh') {
			$pno = numberOnly($_GET['pno']);
			$data = $pdo->assoc("select icons from $tbl[product] where no='$pno'");
		}

		$iasql = "";
		if($cfg['product_icon_sort']=='Y') {
			$iasql = "order by `sort`, `no` desc";
		}else {
			$iasql = "order by `no` desc";
		}

		if($_GET['itype']) $itype = numberOnly($_GET['itype']);
        if($_store_icon_yn) {
			$itype = 9;
        }

	$icons = explode('@', $data['icons']);
		$icres = $pdo->iterator("select no, upfile from $tbl[product_icon] where itype='$itype' $iasql");
        foreach ($icres as $icdata) {
			$upfile = "$dir[upload]/$dir[icon]/$icdata[upfile]";
			$icon_url = getFileDir($upfile);
			$checked = (in_array($icdata['no'], $icons)) ? 'checked' : '';
	?>
	<li>
		<label class="img" for="icon<?=$icdata['no']?>"><img src="<?=$icon_url?>/<?=$upfile?>" <?=$size[2]?> alt="" style="max-width: 50px; max-height: 50px;"></label>
		<input type="checkbox" name="icons[]" value="<?=$icdata['no']?>" id="icon<?=$icdata['no']?>" <?=$checked?>>
	</li>
	<?php
		}
	?>
</ul>