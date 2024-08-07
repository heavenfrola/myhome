<?PHP

    header('Content-type:application/json;');

	include_once $engine_dir.'/_engine/include/wingPos.lib.php';

	$dlv_no = trim($_GET['dlv_no']);
	$dlv_code = trim($_GET['dlv_code']);

	$prd_num = 0;
	$_w = " where a.dlv_no='$dlv_no' and a.dlv_code='$dlv_code' and a.stat in (2, 3)";


	// 주문에서 검색
	if($pdo->row("select count(*) from {$tbl['order']} a $_w") > 1) {
		exit(json_encode(array(
			'result' => 'duplication',
			'msg' => '같은 송장번호가 여러 개 존재합니다.'
		)));
	}

	$invoice = $pdo->assoc("
		select
			a.ono, a.stat, a.dlv_no, a.pay_prc, a.total_prc, b.name as dlv_name, a.dlv_code,
			a.addressee_name, a.addressee_cell, a.addressee_zip, a.addressee_addr1, a.addressee_addr2, a.date2
		from {$tbl['order']} a inner join {$tbl['order_product']} c using(ono) inner join {$tbl['delivery_url']} b on c.dlv_no=b.no
		$_w
	");

	if($invoice['stat'] == 4 || $invoice['stat'] == 5) {
		exit(json_encode(array(
			'result' => 'completed',
			'msg' => '주문번호 '.$invoice['ono'].'는 이미 '.$_order_stat[$invoice['stat']].'처리된 주문입니다.'
		)));
	}
	if($invoice['stat'] ==1) {
		exit(json_encode(array(
			'result' => 'notStandbyOrd',
			'msg' => '배송 준비되지 않은 주문입니다.'
		)));
	}
	if($invoice['stat'] > 10) {
		exit(json_encode(array(
			'result' => 'cancel',
			'msg' => '주문번호 '.$invoice['ono'].'는 취소된 주문입니다.'
		)));
	}

	$msg = '존재하지 않는 운송장 번호 또는 상품 바코드 입니다.';
	$result	= 'notFound';


	// 상품에서 체크
	if(!$invoice) {
		$is_prd = $pdo->row("select complex_no from erp_complex_option where barcode='$dlv_code'");
		if($is_prd['complex_no']) {
			$msg = '';
			$result = '500';
		}
	}


	// 주문서가 검색 된 경우 리스트 출력
	if($invoice) {
		if($cfg['max_cate_depth'] >= 4) {
			$add_field .= ", p.depth4";
		}
		$sql = "select p.no, p.seller_idx, p.hash, p.name, p.updir, p.upfile3, p.w3, p.h3, p.prd_type, p.wm_sc, p.big, p.mid, p.small" .
			   "     , d.complex_no, d.barcode, d.del_yn, d.opts" .
			   "     , a.ono, a.buy_ea, a.sell_prc, a.total_prc, a.stat as pstat, a.dlv_hold, a.dlv_code $add_field" .
			   "  from {$tbl['order_product']} a inner join {$tbl['product']} p on p.no=a.pno left join erp_complex_option d using(complex_no) " .
			   $_w;
		$res = $pdo->iterator($sql);

		ob_start();
		?>
		<tr style="display:none;">
			<td colspan="6" class="inv_prd left">
				<input type="hidden" name="idlv_no" value="<?=$dlv_no?>">
				<input type="hidden" name="idlv_code" value="<?=$dlv_code?>">
				주문번호 : <a href="javascript:;" onclick="viewOrder('<?=$invoice['ono']?>')"><?=$invoice['ono']?></a>
				(<?=$_order_stat[$invoice['stat']]?>)
				택배사 : <?=$invoice['dlv_name']?> 운송장번호 : <?=$invoice['dlv_code']?>
				수취인 : <?=$invoice['addressee_name']?>(<?=$invoice['addressee_cell']?>)
				주소 : (<?=$invoice['addressee_zip']?>) <?=$invoice['addressee_addr1'] . " " . $invoice['addressee_addr2']?>
			</td>
			<td></td>
		</tr>
		<?php
            foreach ($res as $data) {
				if(!$file_dir) $file_dir = getFileDir($data['updir']);
				if($data['upfile3']) {
					$is = setImageSize($data['w3'], $data['h3'], 50, 50);
					$imgstr = "<img src=\"$file_dir/{$data[updir]}/{$data[upfile3]}\" $is[2]>";
				}

				$productname = ($data["wm_sc"]) ? $data["name"]." <img src=\"$engine_url/_manage/image/shortcut2.gif\" alt=\"바로가기 상품입니다\">" : $data["name"];
				$category_name = makeCategoryName($data, 1);

				$stat = '&nbsp;';
				$stat_color = '';
				if($data['pstat'] < 4 && $data['dlv_hold'] == 'Y') {
					$stat = '배송보류';
					$hold_num++;
					$stat_color = 'p_color2';
				}
				if($data['pstat'] > 3) {
					$stat = "<span style='color:{$_order_color[$data['pstat']]}'>{$_order_stat[$data['pstat']]}</span>";
				}

				$class = ($data['pstat'] > 3) ? "error" : "";
				if($data['pstat'] < 4) $prd_num++;

				if(!$data['barcode'] || !$data['complex_no']) $data['barcode'] = "<span class='desc3'>바코드 없음</span>";
				?>
				<tr class="prd <?=$class?>" dlvcode='<?=$data['dlv_code']?>' ono='<?=$data['ono']?>' stat='<?=$data['pstat']?>'>
					<td class="left">
						<input type="hidden" name="pno[]" value="<?=$data['no']?>">
						<div class="box_setup">
							<div class="thumb"><a href="<?=$root_url?>/shop/detail.php?pno=<?=$data['hash']?>" target="_blank"><?=$imgstr?></a></div>
							<dl>
								<dt class="title"><a href="./?body=product@product_register&pno=<?=$data['no']?>&listURL=<?=$listURL?>" target='_blank'><?=$productname?></a></dt>
								<dd class="cstr"><?=$category_name?></dd>
							</dl>
						</div>
					</td>
					<td class="barcode"><a href="#" onclick="putBarcode('<?=$data['barcode']?>'); return false;"><?=$data['barcode']?></a></td>
					<td class="complex_option_name"><?=getComplexOptionName($data['opts'])?></td>
					<td class="qty"><?=number_format($data['buy_ea'])?></td>
					<td><?=parsePrice($data['sell_prc'], true)?></td>
					<td><?=parsePrice($data['total_prc'], true)?></td>
					<td><span class="chk <?=$stat_color?>"><?=$stat?></span></td>
				</tr>

		<?php
			}
		$html = ob_get_clean();

		exit(json_encode(array(
			'result' => '200',
			'msg' => '정상',
			'invoice_no' => $dlv_code,
			'order_no' => $invoice['ono'],
			'date2' => date('Y-m-d H:i:s', $invoice['date2']),
			'name' => $invoice['addressee_name'],
			'cell' => $invoice['addressee_cell'],
			'addr' => $invoice['addressee_zip'].' '.$invoice['addressee_addr1'].' '.$invoice['addressee_addr2'],
			'prd_num' => $prd_num,
			'hold_num' => $hold_num,
			'total_prc' => parsePrice($invoice['total_prc']),
			'pay_prc' => parsePrice($invoice['pay_prc']),
			'html' => $html
		)));
	} else {
		exit(json_encode(array(
			'result' => $result,
			'msg' => $msg,
			'invoice_no' => '',
			'name' => '',
			'cell' => '',
			'addr' => '',
			'html' => ''
		)));
	}

?>