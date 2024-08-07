<?PHP

	include_once $engine_dir.'/_engine/include/wingPos.lib.php';

	$dlv_no = numberOnly($_GET['dlv_no']);
	$dlv_code = addslashes(trim($_GET['dlv_code']));
	$ext = numberOnly($_GET['ext']);

	$w = "  and a.dlv_no='$dlv_no' and a.dlv_code='$dlv_code' and a.stat in (2, 3, 4, 5)";
	$invoice = $pdo->assoc("
		select ono, stat, dlv_no, b.name as dlv_name, dlv_code, addressee_name, addressee_cell, addressee_zip, addressee_addr1, addressee_addr2, postpone_yn
		from wm_order a inner join wm_delivery_url b on a.dlv_no = b.no where 1 $w
	");

	printAjaxHeader();
	if($invoice) {
		if($invoice['postpone_yn'] == 'Y') {
			exit("({result:500,msg:'배송보류 된 주문입니다.',name:'&nbsp;',cell:'&nbsp;',addr:'&nbsp;',html:''})");
		}

		if($invoice[stat] < $ext) {
			$result = "200";
			$msg = "정상";
			if($cfg['max_cate_depth'] >= 4) {
				$add_field .= ", c.depth4";
			}
			$res = $pdo->iterator("
			select
				c.no, c.seller_idx, c.hash, c.name, c.updir, c.upfile3, c.w3, c.h3, c.prd_type, c.wm_sc, c.big, c.mid, c.small
				, d.complex_no, d.barcode, d.opts, b.buy_ea, b.sell_prc, b.total_prc $add_field
				from wm_order a inner join wm_order_product b using(ono) inner join wm_product c on c.no=b.pno inner join erp_complex_option d on d.complex_no=b.complex_no
				where d.del_yn = 'N' and c.stat in ('2', '3', '4') $w
			");

			ob_start();

			?>
			<tr style="background-color:yellow;">
				<td colspan="6" class="left">
				<input type="hidden" name="idlv_no" value="<?=$dlv_no?>"><input type="hidden" name="idlv_code" value="<?=$dlv_code?>">
				주문번호 : <a href='javascript:;' onclick="viewOrder('<?=$invoice['ono']?>')"><?=$invoice['ono']?></a>
				택배사 : <?=$invoice['dlv_name']?> 운송장번호 : <?=$invoice['dlv_code']?>
				수취인 : <?=$invoice['addressee_name']?>(<?=$invoice['addressee_cell']?>)
				주소 : (<?=$invoice['addressee_zip']?>) <?=$invoice['addressee_addr1'] . " " . $invoice['addressee_addr2']?></td>
			</tr>
			<?php
            foreach ($res as $data) {
				if(!$file_dir) $file_dir = getFileDir($data['updir']);
				if($data['upfile3']) {
					$is = setImageSize($data['w3'], $data['h3'], 50, 50);
					$imgstr = "<img src=\"$file_dir/{$data[updir]}/{$data[upfile3]}\" $is[2]>";
				}

				$productname = ($data['wm_sc']) ? $data['name']." <img src=\"$engine_url/_manage/image/shortcut2.gif\" alt=\"바로가기 상품입니다\">" : $data['name'];
				$category_name = makeCategoryName($data, 1);

			?>
			<tr>
				<td class="left">
					<input type="hidden" name="pno[]" value="<?=$data['no']?>">
					<div class="box_setup">
						<div class="thumb"><a href="<?=$root_url?>/shop/detail.php?pno=<?=$data[hash]?>" target="_blank"><?=$imgstr?></a></div>
						<dl>
							<dt class="title"><a href="./?body=product@product_register&pno=<?=$data[no]?>&listURL=<?=$listURL?>" target='_blank'><?=$productname?></a></dt>
							<dd class="cstr"><?=$category_name?></dd>
						</dl>
					</div>
				</td>
				<td><?=hideBarcode($data['barcode'])?></td>
				<td><?=getComplexOptionName($data['opts'])?></td>
				<td><?=number_format($data['buy_ea'])?></td>
				<td><?=number_format($data['sell_prc'])?></td>
				<td><?=number_format($data['total_prc'])?></td>
			</tr>
			<?
			}
			$html = ob_get_clean();
		} else {
			$result = "300";
			$msg = "이미 처리된 운송장 번호입니다.";
		}

		exit(json_encode(array(
			'result' => $result,
			'msg' => mb_convert_encoding($msg, 'utf8', _BASE_CHARSET_),
			'ono' => $invoice['ono'],
			'name' => mb_convert_encoding($invoice['addressee_name'], 'utf8', _BASE_CHARSET_),
			'cell' => $invoice['addressee_cell'],
			'addr' => mb_convert_encoding($invoice['addressee_zip'].' '.$invoice['addressee_addr1'].' '.$invoice['addressee_addr2'], 'utf8', _BASE_CHARSET_),
			'html' => mb_convert_encoding($html, 'utf8', _BASE_CHARSET_)
		)));
	} else {
		exit(json_encode(array(
			'result' => 400,
			'msg' => mb_convert_encoding('운송장 번호가 없습니다.', 'utf8', _BASE_CHARSET_),
			'ono' => $invoice['ono'],
			'name' => '',
			'cell' => '',
			'addr' => '',
			'html' => '',
		)));
	}

?>