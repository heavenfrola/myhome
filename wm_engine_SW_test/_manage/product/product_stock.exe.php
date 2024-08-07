<?PHP

	printAjaxHeader();

	include_once $engine_dir.'/_engine/include/wingPos.lib.php';

	$pno = numberOnly($_POST['pno']);

	$opt_data = array();
	$set_name = array();
	$ores = $pdo->iterator("select no, name from ".$tbl['product_option_set']." where pno='$pno' and necessary in ('Y', 'C') order by sort asc");
    foreach ($ores as $oset) {
		$set_name[] = stripslashes($oset['name']);
		$_temp = $opt_data;
		$res2 = $pdo->iterator("select * from ".$tbl['product_option_item']." where pno='$pno' and opno='$oset[no]' order by sort asc");
        foreach ($res2 as $odata) {
			$iname = stripslashes($odata['iname']);
			if($odata['ori_no']) $odata['no'] = $odata['ori_no'];
			if(count($opt_data) == 0) {
				$_temp[$odata['no']] = $iname;
			} else {
				foreach($opt_data as $key => $val) {
					$_temp[$key.'_'.$odata['no']] = $val.'<ss>'.$iname;
					unset($_temp[$key]);
				}
			}
		}
		$opt_data = $_temp;
	}
	$total_complex = count($opt_data);
	if($total_complex == 0) {
		$set_name[] = '-';
		$opt_data[''] = '옵션없음';
	}

	$page_row = 10;
	include $engine_dir.'/_engine/include/paging.php';

	$page = numberOnly($_POST['page']);
	if($page <= 1) $page = 1;
	$block = 20;
	$QueryString = '';
	foreach($_GET as $key => $val) {
		if($key == 'page') continue;
		$QueryString .= "&$key=".urlencode($val);
	}

	$PagingInstance = new Paging($total_complex, $page, $page_row, $block);
	$PagingInstance->addQueryString($QueryString);
	$PagingResult = $PagingInstance->result($pg_dsn);

	$complex_start = $PagingResult['LimitIndex'];
	$complex_end = $complex_start+$page_row-1;

	$PagingResult['PageLink'] = preg_replace('/\?page=([0-9]+)&.*\"/', '#" onclick="getProductQty('.$pno.', $1); return false;"', $PagingResult['PageLink']);

?>
<div class="box_middle">
	<table class="tbl_inner full">
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
			<?php
				$idx = -1;
				foreach($opt_data as $key => $val) {
					$idx++;
					if($total_complex > $page_row) {
						if($idx < $complex_start) continue;
						if($idx > $complex_end) break;
					}
					$key = makeComplexKey($key);
					$item_name = explode('<ss>', $val);
					$data = $pdo->assoc("select complex_no, barcode, force_soldout, curr_stock(complex_no) as curr from erp_complex_option where pno='$pno' and opts='$key' and del_yn='N'");

			?>
			<tr>
				<?foreach($item_name as $name) {?>
				<td class="left"><?=$name?></td>
				<?}?>
				<td><?=$_erp_force_stat[$data['force_soldout']]?></td>
				<td>
					<a href="#" onclick="viewStockDetail(<?=$data['complex_no']?>); return false;"><?=$data['barcode']?></a>
				</td>
				<td><?=$data['curr']?></td>
			</tr>
			<?}?>
		</tbody>
	</table>
</div>

<div class="box_bottom">
	<div class="right_area">
		<span class="box_btn"><input type="button" value="닫기" onclick="$('#product_stock_list').remove();"></span>
	</div>
	<div class="paging center" style="width: 100%;">
		<?=$PagingResult['PageLink']?>
	</div>
</div>
