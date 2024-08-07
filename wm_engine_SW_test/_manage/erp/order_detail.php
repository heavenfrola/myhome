<?PHP

	include_once $engine_dir.'/_engine/include/wingPos.lib.php';
	include_once $engine_dir.'/_manage/erp/order_detail_search.inc.php';
	$res = $pdo->iterator($sql);

	$listURL = getListURL('?body=erp@order_list');

?>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/shop.js"></script>
<form id="search" name="prdSearchFrm">
	<input type="hidden" name="body" value="<?=$body?>">
	<input type="hidden" name="type">
	<div class="box_title first">
		<h2 class="title">발주 정보</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">발주 정보</caption>
		<colgroup>
			<col style="width:15%">
			<col style="width:35%">
			<col style="width:15%">
			<col style="width:35%">
		</colgroup>
		<tr>
			<th scope="row">사입처</th>
			<td><?=$order['provider']?></td>
			<th scope="row">발주번호</th>
			<td><?=$order['order_no']?></td>
		</tr>
		<tr>
			<th scope="row">발주상태</th>
			<td><?=$stat[$order['order_stat']]?></td>
			<th scope="row">발주일자</th>
			<td><?=$order['order_date']?></td>
		</tr>
	</table>
</form>
<form id="prdFrm" name="prdFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="erp@order_close.exe">
	<input type="hidden" name="exec" value="">
	<input type="hidden" name="check_ono[]" value="<?=$ono?>">
	<div class="box_title">
		<strong id="total_prd"><?=number_format($res->rowCount())?></strong>개의 발주상품이 검색되었습니다
		<div class="btns">
			<span class="box_btn_s"><a href="./?body=erp@order_list_dexcel.exe&set=in&search_type=order_no&search_str=<?=$ono?>">입고처리용 엑셀다운</a></span>
			<span class="box_btn_s"><a href="./?body=erp@order_list_dexcel.exe<?=$xls_query?>">엑셀다운</a></span>
		</div>
	</div>
	<div class="box_middle left">
		<span class="box_btn"><input type="button" value="바코드출력" onclick="barcode()"></span>
		<span class="box_btn"><input type="button" value="발주수정" onclick="location.href='?body=erp@order_mod&ono=<?=$ono?>'"></span>
		<span class="box_btn"><input type="button" value="일괄수정" onclick="location.href='?body=erp@order_mod_all&ono=<?=$ono?>'"></span>
		<?if($order['order_stat'] < 3) {?>
		<span class="box_btn blue"><input type="button" value="발주추가" onclick="addOrder('<?=$order['order_no']?>')"></span>
		<?}?>
	</div>
	<table class="tbl_col">
		<caption class="hidden">발주상품 목록</caption>
		<colgroup>
			<col>
			<col>
			<col>
			<col>
			<col style="width:120px">
			<col style="width:60px">
			<col style="width:60px">
			<col style="width:60px">
			<col style="width:60px">
			<col>
		</colgroup>
		<thead>
			<tr>
				<th scope="col">상가</th>
				<th scope="col">층</th>
				<th scope="col">사입처</th>
				<th scope="col"><a href="<?=$sort3?>">상품명</th>
				<th scope="col">바코드</th>
				<th scope="col">발주수량</th>
				<th scope="col">입고수량</th>
				<th scope="col">발주단가</th>
				<th scope="col">발주금액</th>
				<th scope="col">비고</th>
			</tr>
		</thead>
		<tbody>
		<?php
        foreach ($res as $data) {
			if(!$file_dir) $file_dir = getFileDir($data['updir']);
			if($data['upfile3']) {
				$is = setImageSize($data['w3'], $data['h3'], 50, 50);
				$imgstr = "<img src='$file_dir/{$data[updir]}/{$data[upfile3]}' $is[2]>";
			}

			$data['name'] = stripslashes($data['name']);
			$data['origin_name'] = stripslashes($data['origin_name']);
			if($data['origin_name']) $data['origin_name'] = '('.$data['origin_name'].')';
			$data['remark'] = stripslashes($data['remark']);

			$category_name = makeCategoryName($data, 1);

			?>
			<tr>
				<td>
					<?=$data['arcade']?>
					<input type="hidden" name="check_ino[]" value="<?=$data['complex_no']?>">
					<input type="hidden" name="ino[]" value="<?=$data['complex_no']?>">
					<input type="hidden" name="print_qty[]" value="<?=$data['order_qty']?>">
				</td>
				<td><?=$data[floor]?></td>
				<td class="left"><?=$data['provider']?></td>
				<td class="left">
					<div class="box_setup">
						<div class="thumb"><a href="<?=$root_url?>/shop/detail.php?pno=<?=$data[hash]?>" target="_blank"><?=$imgstr?></a></div>
						<dl>
							<dt class="title"><a href="?body=product@product_register&pno=<?=$data['no']?>" target="_blank"><?=$data['name']?></a></dt>
							<dd class="cstr"><?=getComplexOptionName($data['opts'])?></dd>
							<dd><a href="?body=product@product_register&pno=<?=$data['no']?>" target="_blank"><?=$data['origin_name']?></a></dd>
							<dd class="cstr"><?=$category_name?></dd>
						</dl>
					</div>
				</td>
				<td><?=hideBarcode($data['barcode'])?></td>
				<td><?=number_format($data['order_qty'])?></td>
				<td><?=number_format($data['in_qty'])?></td>
				<td><?=number_format($data['order_price'])?></td>
				<td><?=number_format($data['order_qty']*$data['order_price'])?></td>
				<td class="left"><?=$data['remark']?></td>
			</tr>
		<?}?>
		</tbody>
	</table>
	<div class="box_middle2 right">
		<div class="left_area">
			<span class="box_btn"><input type="button" value="바코드출력" onclick="barcode()"></span>
			<span class="box_btn"><input type="button" value="발주수정" onclick="location.href='?body=erp@order_mod&ono=<?=$ono?>'"></span>
			<span class="box_btn"><input type="button" value="일괄수정" onclick="location.href='?body=erp@order_mod_all&ono=<?=$ono?>'"></span>
			<?if($order['order_stat'] < 3) {?>
			<span class="box_btn blue"><input type="button" value="발주추가" onclick="addOrder('<?=$order['order_no']?>')"></span>
			<?}?>
		</div>
		총발주수량
		<input type="text" name="total_qty" class="input right" disabled value="<?=number_format($order[total_qty])?>" size="3">
		총발주금액
		<input type="text" name="total_amt" class="input right" disabled value="<?=number_format($order[total_amt])?>" size="15">
	</div>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="button" value="목록" onclick="location.href='<?=$listURL?>';"></span>
		<?php if($order[order_stat] == "1" || $order[order_stat] == "2") :?>
		<span class="box_btn gray"><input type="button" value="발주취소" onclick="orderClose();"></span>
		<?php endif; ?>
	</div>
</form>

<script type="text/javascript">
	function orderClose() {
		if(!confirm("발주취소처리하시겠습니까?")) return;
		var prdFrm = $('#prdFrm')[0];
		prdFrm.body.value = 'erp@order_close.exe';
		prdFrm.target = hid_frame;
		prdFrm.submit();
	}

	function barcode() {
		window.open('', 'barcode', 'status=no, width=770px, height=680px. scrollbars=yes');

		var prdFrm = $('#prdFrm')[0];
		prdFrm.body.value = 'erp@barcode_print2.exe';
		prdFrm.target = 'barcode';
		prdFrm.submit();
	}

	var psearch = new layerWindow('erp@order_add.exe');
	function addOrder(order_no) {
		psearch.open('order_no=<?=$ono?>&ono='+order_no);

	}
</script>