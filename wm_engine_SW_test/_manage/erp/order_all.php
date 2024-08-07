<?PHP

	include_once $engine_dir.'/_engine/include/wingPos.lib.php';

	$w = $h = $l = '';

	// 상태 검색
	$stat = numberOnly($_GET['stat']);
	$type = ($_GET['type']) ? numberOnly($_GET['type']) : 1;
	$search = $_GET['search'];
	if(!$search || !is_array($stat)) $stat = array(2, 3);
	foreach($_order_stat as $key=>$val) {
		if($key > 5 && $key != 20) continue;

		$chkd = (is_array($stat) && in_array($key, $stat)) ? 'checked' : '';
		$stat_check_str.="<label class=\"p_cursor\"><input type='checkbox' id='stat' name='stat[]' value='$key' $chkd> $val&nbsp;&nbsp;</label>";
	}
	if($type == 1 && count($stat) > 0) {
		$w .= " and x.`stat` in (".implode(',', $stat).")";
	}


	// 날짜 검색
	$start_date = addslashes($_GET['start_date']);
	$start_time = numberOnly($_GET['start_time']);
	$finish_date = addslashes($_GET['finish_date']);
	$finish_time = numberOnly($_GET['finish_time']);
	$all_date = addslashes($_GET['all_date']);
	$search_date_type = numberOnly($_GET['search_date_type']);
	$date = addslashes(trim($_GET['date']));

	if(!$start_date && !$finish_date) $all_date = 'Y';
	if(!$start_date) $start_date = date('Y-m-d');
	if(!$start_time) $start_time = 0;
	if(!$finish_date) $finish_date = date('Y-m-d');
	if(!$finish_time) $finish_time = 23;
	if($start_date) $_start_date = strtotime("$start_date $start_time:00:00");
	if($finish_date) $_finish_date = strtotime("$finish_date {$finish_time}:59:59");
	if($all_date != 'Y' && $type == 1) {
		if($start_date && $finish_date) {
			$w  .= " and o.`date{$search_date_type}` between '$_start_date' and '$_finish_date'";
		} else if($start_date) {
			$w  .= " and o.`date{$search_date_type}` >= '$_start_date'";
		} else if($finish_date) {
			$w  .= " and o.`date{$search_date_type}` <= '$_finish_date'";
		}
	}
	if(!$date) $date = date("Y-m-d", $now); // 발주일자 지정


	if($type == 1) { // 주문에 의한 발주대상 조회
		$f .= " , sum(buy_ea) as order_target_qty ";
		$l .= " inner join $tbl[order_product] x on b.complex_no=x.complex_no inner join $tbl[order] o using(ono) ";
	}
	if($type == 2) { // 안전재고에 의한 발주대상 조회
		$f .= " , (safe_stock_qty-curr_stock(b.complex_no)) as order_target_qty";
		$h .= " and safe_stock_qty >= current_qty ";

	}
	$orderd = ($_GET['orderd'] == 'N') ? 'N' : 'Y';
	if($orderd != 'N') $h .= " and order_target_qty-exist_qty > 0 ";

	if($h) $h = ' having 1 '.$h;

	if($cfg['max_cate_depth'] >= 4) {
		$f .= ", a.depth4";
	}

	$prd_stat = numberOnly($_GET['prd_stat']);
	if(!is_array($prd_stat)) $prd_stat = array('2');
	if(count($prd_stat) > 0) {
		$w .= ' and a.stat in ('.implode(',', $prd_stat).')';
	}

	$f_soldout = $_GET['f_soldout'];
	if(!is_array($f_soldout)) $f_soldout = array('N', 'L');
	if(count($f_soldout) > 0) {
		$tmp = '';
		foreach($f_soldout as $val) {
			$val = addslashes($val);
			$tmp .= ",'$val'";
		}
		$tmp = trim($tmp, ',');
		$w .= ' and b.force_soldout in ('.$tmp.')';
	}

	$sql = "select a.no, a.hash, a.name, a.updir, a.upfile3, a.w3, a.h3, a.seller_idx, a.origin_prc, a.origin_name, a.big, a.mid, a.small ".
		   "	, b.barcode, b.complex_no, b.opts, curr_stock(b.complex_no) as current_qty, b.safe_stock_qty, b.del_yn ".
		   "	, d.arcade, d.floor, d.provider ".$f.
		   "	,ifnull((select sum(order_qty) from erp_order x, erp_order_dtl y where x.order_no=y.order_no and x.order_stat='1' and b.complex_no = y.complex_no), 0) as exist_qty ".
		   "from $tbl[product] a inner join erp_complex_option b on a.no=b.pno ".
		   "	inner join $tbl[provider] d on a.seller_idx=d.no ".$l.
		   "where b.del_yn='N' $w group by complex_no $h order by null";

	if($type) $res = $pdo->iterator($sql);
	if(!$type) $type = 1;

?>
<script type="text/javascript" src="<?=$engine_url?>/_manage/erp/js/jquery.calculation.js"></script>
<form id="search" name="prdSearchFrm">
	<input type="hidden" name="body" value="<?=$body?>">
	<input type="hidden" name="search" value="true">
	<input type="hidden" name="type">
	<div class="box_title first">
		<h2 class="title">발주 정보</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">발주 정보</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">발주대상</th>
			<td>
				<label class="p_cursor"><input type="radio" name="type" value="1" <?=checked($type, 1)?>> 주문서 기준</label>
				<label class="p_cursor"><input type="radio" name="type" value="2" <?=checked($type, 2)?>> 안전재고 기준</label>
			</td>
		</tr>
		<tr>
			<th scope="row">발주일자</th>
			<td><input type="text" name="date" value="<?=$date?>" size="10" readonly class="input datepicker"></td>
		</tr>
		<tr>
			<th scope="row">재발주</th>
			<td>
				<label class="p_cursor"><input type="checkbox" name="orderd" value="N" onclick="setConfig('erp_orderd',this.checked)" <?=checked($_COOKIE['erp_orderd'], 'true')?>> 기발주된 발주서에 상관없이 발주수량을 책정합니다.</label>
				<p class="explain">체크 하지 않을 경우, 발주 시 기발주된 발주서에 있는 상품이 아직 입고가 덜된 경우 해당 수량만큼 발주대상에서 제외합니다.</p>
			</td>
		</tr>
	</table>
	<div class="box_title">
		<h2 class="title">발주대상 조건 검색</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">발주대상 조건 검색</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th>상품상태</th>
			<td>
				 <?PHP for($i = 2; $i <= 4; $i++) {?>
					 <label><input type="checkbox" name="prd_stat[]" value="<?=$i?>" <?=checked(in_array($i, $prd_stat), true)?>> <?=$_prd_stat[$i]?></label>
				 <?}?>
			</td>
		</tr>
		<tr>
			<th>품절방식</th>
			<td>

				 <?PHP foreach($_erp_force_stat as $key => $val) {?>
					 <label><input type="checkbox" name="f_soldout[]" value="<?=$key?>" <?=checked(in_array($key, $f_soldout), true)?>> <?=$val?></label>
				 <?}?>
			</td>
		</tr>
		<tr class="type1_detail">
			<th scope="row">주문기간</th>
			<td>
				<select name="search_date_type">
					<option value="1" <?=checked($search_date_type,1,1)?>>주문일</option>
					<option value="2" <?=checked($search_date_type,2,1)?>>입금일</option>
					<option value="4" <?=checked($search_date_type,4,1)?>>배송일</option>
					<option value="5" <?=checked($search_date_type,5,1)?>>배송완료일</option>
				</select>
				<label class="p_cursor"><input type="checkbox" name="all_date" value="Y" <?=checked($all_date,"Y")?> onClick="searchDate(this.form)"> 전체 기간</label>
				<input type="text" name="start_date" value="<?=$start_date?>" size="10" class="input datepicker">
				<select name="start_time">
					<?for($i=0; $i <=23; $i++){?>
					<option value="<?=$i?>" <?=checked($start_time, $i, 1)?>><?=sprintf('%02d',$i)?>:00</option>
					<?}?>
				</select>
				~
				<input type="text" name="finish_date" value="<?=$finish_date?>" size="10" class="input datepicker">
				<select name="finish_time">
					<?for($i=0; $i <=23; $i++){?>
					<option value="<?=$i?>" <?=checked($finish_time, $i, 1)?>><?=sprintf('%02d',$i)?>:59</option>
					<?}?>
				</select>
			</td>
		</tr>
		<tr class="type1_detail">
			<th scope="row">주문상태</th>
			<td>
				<?=$stat_check_str?>
			</td>
		</tr>
	</table>
	<div class="box_middle2 left">
		사입처가 지정되어있지 않은 상품은 발주상품 목록에 나타나지 않습니다.
		<span class="box_btn_s"><a href="?body=product@product_list&seller_chk=true">사입처 미지정 상품 검색</a></span>
	</div>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="발주대상 조회"></span>
	</div>
</form>
<?if($res) {?>
<form id="prdFrm" name="prdFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
	<input type="hidden" name="body" value="erp@order_all.exe">
	<input type="hidden" name="exec" value="">
	<input type="hidden" name="order_date" value="">
	<table id="list_order" class="tbl_col">
		<caption>발주상품 목록</caption>
		<colgroup>
			<col span="2" style="width:40px">
			<col span="4">
			<col style="width:75px">
			<?if($type == 1){?>
			<col style="width:65px">
			<?} else {?>
			<col style="width:65px">
			<?}?>
			<col style="width:65px">
			<col style="width:65px">
			<col style="width:50px">
			<col style="width:180px">
			<col style="width:60px">
		</colgroup>
		<thead>
			<tr>
				<th scope="col" colspan="2"><input type="checkbox" onclick="$(':checkbox[name=\'accept[]\']').prop('checked', this.checked)"></th>
				<th scope="col">사입처</th>
				<th scope="col">상품명</th>
				<th scope="col">바코드</th>
				<th scope="col">옵션</th>
				<th scope="col">
					발주대상수량
					<?if($_GET['orderd']!='N'){?><br>(기발주수량)<?}?>
				</th>
				<?if($type == 1){?>
				<th scope="col">현재고</th>
				<?} else {?>
				<th scope="col">현재고<br>(안전재고)</th>
				<?}?>
				<th scope="col">발주수량</th>
				<th scope="col">발주단가</th>
				<th scope="col">발주금액</th>
				<th scope="col">비고</th>
				<th scope="col">삭제</th>
			</tr>
		</thead>
		<tbody>
			<?php
				$idx = 0;
                foreach ($res as $data) {
					$idx++;

					if(!$file_dir) $file_dir = getFileDir($data['updir']);
					if($data['upfile3']) {
						$is = setImageSize($data['w3'], $data['h3'], 50, 50);
						$imgstr = "<img src='$file_dir/{$data[updir]}/{$data[upfile3]}' $is[2]>";
					}

					$data['name'] = stripslashes($data['name']);
					$data['origin_name'] = stripslashes($data['origin_name']);
					$data['provider'] = stripslashes($data['provider']);
					$data['current_qty'] = number_format($data['current_qty']);

					$productname = ($data['wm_sc']) ? $data['name']." <img src='$engine_url/_manage/image/shortcut2.gif' alt='바로가기 상품입니다'>" : $data['name'];
					$category_name = makeCategoryName($data, 1);
					if($data['origin_name']) $data['origin_name'] = '('.$data['origin_name'].')';

					$data['order_price'] = $pdo->row("select in_price from erp_inout where complex_no='$data[complex_no]' and inout_kind='I' order by inout_no desc limit 1");
					if($data['order_price'] < 1) $data['order_price'] = $data['origin_prc'];
					$order_target_qty = ($orderd == 'N') ? $data['order_target_qty'] : $data['order_target_qty']-$data['exist_qty'];
					$order_amt = $order_target_qty*$data['order_price'];
					$total_qty += $order_target_qty;
					$total_amt += $order_amt;
			?>
			<tr>
				<td><input type="checkbox" name="accept[]" value="<?=$data['complex_no']?>"></td>
				<td><?=$idx?></td>
				<td class="left"><?=$data['provider']?></td>
				<td>
					<input type="hidden" name="sno[]" value="<?=$data['seller_idx']?>">
					<input type="hidden" name="pno[]" value="<?=$data['complex_no']?>">
					<input type="hidden" name="order_target_qty[]" value="<?=$order_target_qty?>">
					<div class="box_setup" style="padding-left:10px;">
						<?if($cfg['erp_oimg'] != 'N') {?>
						<div class="thumb"><a id="imgstr" href="<?=$root_url?>/shop/detail.php?pno=<?=$data['hash']?>" target="_blank"><?=$imgstr?></a></div>
						<?}?>
						<dl>
							<dt class="title"><a href="?body=product@product_register&pno=<?=$data['no']?>" target="_blank"><?=$productname?></a></dt>
							<dd><a href="?body=product@product_register&pno=<?=$data['no']?>" target="_blank"><?=$data['origin_name']?></a></dd>
							<dd class="cstr"><?=$category_name?></dd>
						</dl>
					</div>
				</td>
				<td id="barcode"><?=hideBarcode($data['barcode'])?></td>
				<td id="complex_option_name">
					<?=getComplexOptionName($data['opts'])?>
					<?if($data['del_yn'] == 'Y'){?>
					<span class="p_color2">삭제된 옵션</span>
					<?}?>
				</td>
				<td id="order_target_qty">
					<?=number_format($data['order_target_qty'])?>
					<?if($_GET['orderd']!='N'){?>(<?=number_format($data['exist_qty'])?>)<?}?>
				</td>
				<?if($type == 1) {?>
				<td id="current_qty">
					<a href="#" onclick="viewStockDetail(<?=$data['complex_no']?>); return false;"><?=$data['current_qty']?></a>
				</td>
				<?} else {?>
				<td id="current_qty">
					<a href="#" onclick="viewStockDetail(<?=$data['complex_no']?>); return false;"><?=$data['current_qty']?></a>
					(<span class="p_color2"><?=number_format($data['safe_stock_qty'])?></span>)
				</td>
				<?}?>
				<td>
					<input type="text" id="order_qty_<?=++$row?>" name="order_qty[]" value="<?=$order_target_qty?>" class="input number right order_qty" style="ime-mode:disabled;" size="5" onkeydown="keydown();" onfocus="this.select();" onkeyup="calc(<?=$row?>);">
				</td>
				<td>
					<input type="text" id="order_price_<?=$row?>" name="order_price[]" value="<?=parsePrice($data['order_price'])?>" class="input number right order_price" style="ime-mode:disabled;" size="5" onkeydown="keydown();" onfocus="this.select();" onkeyup="calc(<?=$row?>);">
				</td>
				<td id="order_amt_<?=$row?>"><?=parsePrice($order_amt, true)?></td>
				<td><input type="text" name="remark[]" value="" class="input" size="20"></td>
				<td><span class="box_btn_s"><a onclick="delProd(this);">삭제</a></span></td>
			</tr>
			<?}?>
		</tbody>
	</table>
	<div class="box_middle2 right">
		총발주수량 <input type="text" id="total_qty" name="total_qty" class="input right" disabled value="<?=$total_qty == null ? "0" : $total_qty?>" size="3">
		총발주금액 <input type="text" id="total_amt" name="total_amt" class="input right" disabled value="<?=$total_amt?>" size="15">
	</div>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="button" value="선택상품 발주" onclick="order();"></span>
	</div>
</form>
<?}?>

<script type="text/javascript">
	var noimg = '<img src=\"<?=$root_url?>/_image/_default/prd/noimg3.gif\" width=\"50\" height=\"50\">';
	var tr;
	function getProdByEnter(obj) {
		tr = $(obj).parent().parent().parent().parent();
		switch (event.keyCode) {
		case 13 :
			if(tr.find("input[name='pno[]']").val() == "") {
				getProd(obj);
			} else {
				tr.find("input[name='order_qty[]']").focus();
			}
			break;
		case 9 :
			break;
		default :
			if(tr.find("input[name='pno[]']").val() != "") {
				setProd({productname:$(obj).val()});
			}
		}
	}
	function getProd(obj) {
		tr = $(obj).parent().parent().parent().parent();
		$.ajax({
			url: '?body=erp@product.exe&word=' + $(obj).val(),
			success: function(data) {
				var json = eval(data);
				if(json.rows == 1) {
					setProd(json.datas[0]);
				} else {
					wisaOpen('./pop.php?body=erp@product_list&search_type=name&search_str=' + $(obj).val(),'chgPrd', 'yes');
				}
			}
		});
	}

	function order() {
		var cnt = 0;
		$(':checkbox[name="accept[]"]').each(function(){
			if(this.checked == true) cnt++;
		});
		if(cnt < 1) {
			window.alert('발주 할 상품을 선택 해 주십시오.');
			return false;
		}


		var order_qty = $("input[name='order_qty[]']");
		var order_amt = $("input[name='order_amt[]']");
		if(order_qty.length == 0 || $("#total_qty").val() == "0") {
			alert(" 발주할 상품이 없습니다. 발주수량을 입력하세요.");
			return;
		}
		var prdFrm = document.getElementById('prdFrm');
		prdFrm.total_qty.disabled = false;
		prdFrm.total_amt.disabled = false;
		prdFrm.order_date.value = prdSearchFrm.date.value;
		prdFrm.submit();
	}
	function delProd(obj) {
		var tr = $(obj).parent().parent().parent();
		tr.remove();
	}
	function keydown(obj) {
		if(event.keyCode == 13) {
			event.keyCode = 9;
		}
		return event.keyCode;
	}
	function calc(row) {
		var qty = $('#order_qty_'+row);
		var prc = $('#order_price_'+row);
		var amt = $('#order_amt_'+row);

		amt.html(qty.val().toNumber() * prc.val().toNumber());

		var total_qty = 0;
		var total_amt = 0;
		var op = $('.order_price');
		$('.order_qty').each(function(idx) {
			total_qty += this.value.toNumber();
			total_amt += (op.eq(idx).val().toNumber() * this.value.toNumber());
		});

		$('#total_qty').val(total_qty);
		$('#total_amt').val(total_amt);
	}
	function setProd(data) {
		data = $.extend({complex_no:'',order_target_qty:0,imgstr:noimg,productname:"",category_name:"",barcode:"",complex_option_name:"",exist_qty:0,current_qty:0,order_qty:0,in_price:0}, data);
		tr.find("#imgstr").html(data.imgstr);
		tr.find("input[name='product_name[]']").val(data.productname);
		tr.find(".cstr").html(data.category_name);
		tr.find("input[name='pno[]']").val(data.complex_no);
		tr.find("input[name='order_target_qty[]']").val(data.order_target_qty);
		tr.find("#barcode").html(data.barcode);
		tr.find("#complex_option_name").html(data.complex_option_name);
		tr.find("#order_target_qty").html(data.order_target_qty);
		tr.find("#exist_qty").html(data.exist_qty);
		tr.find("#current_qty").html(data.current_qty);
		tr.find("input[name='order_qty[]']").val(data.order_qty);
		tr.find("input[name='order_price[]']").val(data.in_price);
		tr.find("#order_amt").html(data.order_qty * data.in_price);
		if(data.complex_no != '') {
			tr.find("input[name='order_qty[]']").focus();
		}
	}
	function insProd(data) {
		var row = $("input[name='order_qty[]']").length + 1;
		var html = "<tr><td><input type='hidden' name='pno[]' value=''>" +
				   "<input type='hidden' name='order_target_qty[]' value=''>" +
				   "<dl class='product_summary'><dt><a id='imgstr' href='' target='_blank'></a></dt>" +
				   "<dd><input type='text' name='product_name[]' class='input' value='' size='20' onfocus='this.select();' onkeyup='getProdByEnter(this);'><img src='<?=$engine_url?>/_manage/image/btn/bt_mod.gif' style='cursor:pointer;' onclick='getProd($(this).prev());'></dd>" +
				   "<dd class='cstr'></dd></dl></td><td id='barcode' class='patt2'></td><td id='complex_option_name'></td>" +
				   "<td id='order_target_qty' class='right patt2 number'></td>"+
				   "<td id='exist_qty' class='right number'></td>"+
				   "<td id='current_qty' class='right patt2 number'></td>"+
				   "<td class='right number'><input type='text' id='order_qty_"+row+"' name='order_qty[]' value='' class='input number right' style='ime-mode:disabled;' size='8' onkeydown='keydown();' onfocus='this.select();' onkeyup='calc();FilterNumOnly(this);'></td>" +
				   "<td class='right patt2 number'><input type='text' id='order_price_"+row+"' name='order_price[]' value='' class='input number right' style='ime-mode:disabled;' size='10' onkeydown='keydown();' onfocus='this.select();' onkeyup='calc();FilterNumOnly(this);'></td>" +
				   "<td id='order_amt' class='right number' id='order_amt_"+row+"'></td>" +
				   "<td class='patt2'><input type='text' name='remark[]' value='' class='input' size='15'></td>" +
				   "<td class='center'><img src='<?=$engine_url?>/_manage/image/btn/bt_del.gif' style='cursor:pointer;' onclick='delProd(this);'></td></tr>";
		$("#list_order").append(html);
		tr = $("#list_order tr:last");
		setProd({});
		tr.find("input[name='product_name[]']").focus();
	}

	function searchDate() { // override
		var f = document.getElementById('search');
		if(f.all_date.checked == true) {
			f.start_date.disabled = true;
			f.finish_date.disabled = true;
			f.start_date.style.background = '#eee';
			f.finish_date.style.background = '#eee';
		} else {
			f.start_date.disabled = false;
			f.finish_date.disabled = false;
			f.start_date.style.background = '';
			f.finish_date.style.background = '';
		}
	}
	searchDate();

	function setType() {
		var val = $('form#search').find(':checked[name=type]').val();
		if(val == '1') {
			$('.type1_detail').show();
		} else {
			$('.type1_detail').hide();
		}
	}
	$('form#search').find(':radio[name=type]').click(function() {
		setType();
	});
	setType();
</script>