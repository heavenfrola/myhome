<?PHP

	include_once $engine_dir.'/_engine/include/wingPos.lib.php';

	$order_no = addslashes(trim($_GET['order_no']));
	if($order_no) {
		$stat = array(1 => '발주', 2 => '부분입고', 3 => '입고완료', 5 => '발주취소');
		$sql = "select order_date, order_stat from erp_order a where order_no = '{$order_no}'";
		$order = $pdo->assoc($sql);

		if($cfg['max_cate_depth'] >= 4) {
			$add_field .= ", a.depth4";
		}

		$sql = "select a.no, a.hash, a.name, a.updir, a.upfile3, a.w3, a.h3, a.prd_type, a.wm_sc, a.big, a.mid, a.small, e.arcade, e.floor, e.provider, d.sno $add_field " .
			 "      , b.complex_no, b.barcode, b.opts" .
			 "      , d.order_qty, d.order_dtl_no" .
			 "      , ifnull((select sum(qty) from erp_inout x where x.inout_kind='I' and d.order_dtl_no=x.order_dtl_no), 0) as in_qty" . // 기입고수량
			 "      , order_price as in_price" .
			 "  from wm_product a inner join erp_complex_option b on a.no=b.pno inner join erp_order_dtl d using(complex_no) left join wm_provider e on d.sno=e.no " .
			 " where a.stat in ('2', '3', '4') and a.no = b.pno" .
			 "   and b.del_yn = 'N'" .
			 "   and d.order_no   = '{$order_no}'" .
			 " order by d.order_dtl_no";
		$res = $pdo->iterator($sql);
	}

?>
<script type="text/javascript" src="<?=$engine_url?>/_manage/erp/js/jquery.calculation.js"></script>
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
			<th scope="row">발주번호</th>
			<td colspan="3">
				<input type="text" name="order_no" value="<?=$order_no?>" class="input" size="20">
				<span class="box_btn_s blue"><input type="button" value="조회" onclick="sear();"></span>
			</td>
		</tr>
		<tr>
			<th scope="row">발주상태</th>
			<td><?=$order ? $stat[$order['order_stat']] : ""?></td>
			<th scope="row">발주일자</th>
			<td><?=$order['order_date']?></td>
		</tr>
	</table>
</form>
<form id="prdFrm" name="prdFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="erp@order_in.exe">
	<input type="hidden" name="exec" value="">
	<input type="hidden" name="ono" value="<?=$order_no?>">
	<input type="hidden" name="sno" value="<?=$order['sno']?>">
	<div class="box_title">
		<h2 class="title">입고상품 목록</h2>
	</div>
	<table class="tbl_col">
		<caption class="hidden">입고상품 목록</caption>
		<colgroup>
			<col style="width:100px">
			<col>
			<col style="width:120px">
			<col style="width:100px">
			<col style="width:65px">
			<col style="width:65px">
			<col style="width:65px">
			<col style="width:80px">
			<col style="width:80px">
			<col style="width:80px">
			<col style="width:150px">
		</colgroup>
		<thead>
			<tr>
				<th scope="col">사입처</th>
				<th scope="col"><a href="<?=$sort3?>">상품명</th>
				<th scope="col">바코드</th>
				<th scope="col">옵션</th>
				<th scope="col">발주수량</th>
				<th scope="col">기입고수량</th>
				<th scope="col">입고대상<br>수량</th>
				<th scope="col">입고수량</th>
				<th scope="col">입고단가</th>
				<th scope="col">입고금액</th>
				<th scope="col">비고</th>
			</tr>
		</thead>
		<tbody>
			<?php
            if ($res) {
            foreach ($res as $data) {
				if(!$file_dir) $file_dir = getFileDir($data['updir']);
				if($data['upfile3']) {
					$is = setImageSize($data['w3'], $data['h3'], 50, 50);
					$imgstr = "<img src='$file_dir/{$data[updir]}/{$data[upfile3]}' $is[2]>";
				}

				$productname = ($data['wm_sc']) ? $data['name']." <img src='$engine_url/_manage/image/shortcut2.gif' alt='바로가기 상품입니다'>" : $data['name'];
				$category_name = makeCategoryName($data, 1);
				$inqty = $data['order_qty'] - $data['in_qty'];
				$in_amt = $inqty * $data['in_price'];
				$total_qty += $inqty;
				$total_amt += $in_amt;

			?>
			<tr>
				<td><?=$data['provider']?></td>
				<td class="left">
					<?if($inqty > 0) {?>
					<input type="hidden" name="pno[]" value="<?=$data['complex_no']?>">
					<input type="hidden" name="order_dtl_no[]" value="<?=$data['order_dtl_no']?>">
					<input type="hidden" name="order_qty[]" value="<?=$inqty?>">
					<input type="hidden" name="sno2[]" value="<?=$data['sno']?>">
					<?}?>
					<div class="box_setup">
						<div class="thumb"><a href="<?=$root_url?>/shop/detail.php?pno=<?=$data[hash]?>" target="_blank"><?=$imgstr?></a></div>
						<dl>
							<dt class="title"><a href="?body=product@product_register&pno=<?=$data['no']?>" target="_blank"><?=$productname?></a></dt>
							<dd class="cstr"><?=$category_name?></dd>
						</dl>
					</div>
				</td>
				<td><?=hideBarcode($data['barcode'])?></td>
				<td><?=getComplexOptionName($data['opts'])?></td>
				<td><?=number_format($data['order_qty'])?></td>
				<td><?=number_format($data['in_qty'])?></td>
				<td><?=number_format($inqty)?></td>
				<td><input type="text" id="in_qty_<?=++$row?>" name="in_qty[]"<?=$inqty==0?" disabled" : ""?> value="<?=$inqty?>" class="input right" size="6" onkeydown="keydown();" onfocus="this.select();" onkeyup="calc();FilterNumOnly(this);"></td>
				<td><input type="text" id="in_price_<?=$row?>" name="in_price[]"<?=$inqty==0?" disabled" : ""?> value="<?=$data['in_price']?>" class="input right" size="6" onkeydown="keydown();" onfocus="this.select();" onkeyup="calc();FilterNumOnly(this);"></td>
				<td id="in_amt_<?=$row?>"><?=number_format($in_amt)?></td>
				<td><input type="text" name="remark[]"<?=$inqty==0?" disabled" : ""?> value="" class="input" size="15"></td>
			</tr>
			<?}?>
			<?}?>
		</tbody>
	</table>
	<div class="box_middle2 right">
		총입고수량 <input type="text" id="total_qty" name="total_qty" class="input right" disabled value="<?=$total_qty==null?"0":$total_qty?>" size="3">
		총입고금액 <input type="text" id="total_amt" name="total_amt" class="input right" disabled value="<?=$total_amt?>" size="15">
	</div>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="button" value="입고처리" onclick="in1();"></span>
	</div>
</form>

<script type="text/javascript">
	function sear() {
		if(prdSearchFrm.order_no.value == '') {
			alert('발주번호를 입력하세요.');
			prdSearchFrm.order_no.focus();
			return;
		}
		prdSearchFrm.submit();
	}
	function in1() {
		var in_qty = $("input[name='in_qty[]']");
		var in_amt = $("input[name='in_amt[]']");
		if(in_qty.length == 0 || $("#total_qty").val() == "0") {
			alert(" 입고할 상품이 없습니다. 입고수량을 입력하세요.");
			return;
		}
		prdFrm.submit();
	}
	<?if($order_no) {
	if($order['order_stat'] == "3") {
		$msg = "입고완료된 발주건입니다.";
	} else if($order['order_stat'] == "4") {
		$msg = "발주취소된 발주건입니다.";
	}
	if($msg) {
	?>
	alert("<?=$msg?>");
	history.back();
	<?}}?>

	function calc() {
		$("[id^=in_amt]").calc(
			"qty * price",
			{
				qty: $("input[id^=in_qty_]"),
				price: $("input[id^=in_price_]")
			},
			function (s){
				return s.toFixed(0);
			},
			function ($this) {
				var sum = $this.sum();
				$("#total_amt").val(sum.toFixed(0));
				$("#total_qty").val($("input[id^=in_qty_]").sum());
			}
		);
	}
	function keydown(obj) {
		if(event.keyCode == 13) {
			event.keyCode = 9;
		}
		return event.keyCode;
	}
</script>