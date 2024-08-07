<?PHP

	include_once $engine_dir.'/_engine/include/wingPos.lib.php';

	// 사입처 데이터 캐시
	$_seller = array();
	$prql = $pdo->iterator("select no, provider, arcade, floor from $tbl[provider] order by arcade!='' desc, arcade asc, floor asc, provider asc");
    foreach ($prql as $prdata) {
		$_provider = cutStr(stripslashes($prdata['provider']), 50);
		$_arcade = stripslashes($prdata['arcade']);
		if($prdata['floor']) $_arcade .= ' '.stripslashes($prdata['floor']).'층';
		if($_arcade) $_provider = "[$_arcade] ".$_provider;
		$_seller[$prdata['no']] = $_provider;
	}

	// 상태 검색
	$stat = numberOnly($_GET['stat']);
	if(!is_array($stat)) $stat = array(2, 3);
	foreach($_order_stat as $key=>$val) {
		if($key > 5 && $key != 20) continue;

		$chkd = (is_array($stat) && in_array($key, $stat)) ? 'checked' : '';
		$stat_check_str.="<label class='p_cursor'><input type='checkbox' id='stat' name='stat[]' value='$key' $chkd> $val</label>";
	}

	// 날짜 검색
	$start_date = addslashes($_GET['start_date']);
	$start_time = numberOnly($_GET['start_time']);
	$finish_date = addslashes($_GET['finish_date']);
	$finish_time = numberOnly($_GET['finish_time']);
	$all_date = addslashes($_GET['all_date']);
	$type = ($_GET['type']) ? numberOnly($_GET['type']) : 1;
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
	if(!$date) $date = date('Y-m-d', $now); // 발주일자 지정

	// 발주일자
	if(!$date) $date = date('Y-m-d', $now);

	$seller = numberOnly($_GET['seller']);
	if($seller) {
		if($type == '2') {
			$target = "b.safe_stock_qty - curr_stock(b.complex_no)";
		} else {
			$ckstat = " and x.`stat` in (".implode(',', $stat).")";
			$target = "ifnull((select sum(buy_ea) from wm_order x, wm_order_product y where y.pno = a.no and x.ono = y.ono and y.complex_no = b.complex_no $ckstat), 0)";
		}
		if($cfg['max_cate_depth'] >= 4) {
			$add_field .= ", a.depth4";
		}

		$sql = "select a.no, a.hash, a.name, a.updir, a.upfile3, a.w3, a.h3, a.seller_idx, a.origin_prc, a.big, a.mid, a.small ".
		"		   ,b.barcode, b.complex_no, b.opts, curr_stock(b.complex_no) as current_qty, b.safe_stock_qty " .
		"		   ,d.arcade, d.floor, d.provider $add_field ".
		"          ,{$target} as order_target_qty " .     // 발주대상수량
		"		   ,ifnull((select in_price from erp_inout x where x.complex_no = b.complex_no and x.inout_kind = 'I' limit 1), 0) as order_price" . // 최종단가
		"		   ,ifnull((select sum(order_qty) from erp_order x, erp_order_dtl y where x.order_no = y.order_no and x.order_stat = '1' and b.complex_no = y.complex_no), 0) as exist_qty".
		"		from `$tbl[product]` a inner join erp_complex_option b on a.no = b.pno ".
		"			left join `$tbl[provider]` d on a.seller_idx = d.no ".
		"		where b.del_yn='N' and a.stat in (2,3) and a.seller_idx='$seller' and a.ea_type = 1 ".
		"		having (order_target_qty - exist_qty) > 0";
		$res = $pdo->iterator($sql);
	}

?>
<script type="text/javascript" src="<?=$engine_url?>/_manage/erp/js/jquery.calculation.js"></script>
<!-- 검색폼 -->
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
		</colgroup>
		<tr>
			<th scope="row">발주대상</th>
			<td>
				<label class="p_cursor"><input type="radio" name="type" value="1" <?=checked($type, 1)?>> 주문서 기준</label>
				<label class="p_cursor"><input type="radio" name="type" value="2" <?=checked($type, 2)?>> 안전재고 기준</label>
			</td>
		</tr>
		<tr>
			<th scope="row">사입처</th>
			<td>
				사입처 검색 : <input type="text" class="input" size="20" onkeyup="sellerFinder(this, 'seller')">
				<?=selectArray($_seller, "seller", 2, ":: 사입처 선택 ::", $seller)?>
			</td>
		</tr>
		<tr>
			<th scope="row">발주일자</th>
			<td><input type="text" name="date" value="<?=$date?>" size="10" readonly class="input datepicker"></td>
		</tr>
	</table>
	<div id="type1_detail">
		<div class="box_title">
			<h2 class="title">발주대상 조건 검색</h2>
		</div>
		<table class="tbl_row">
			<caption class="hidden">발주대상 조건 검색</caption>
			<colgroup>
				<col style="width:15%">
			</colgroup>
			<tr>
				<th scope="row">주문기간</th>
				<td>
					<select name="search_date_type">
						<option value="1" <?=checked($search_date_type,1,1)?>>주문일</option>
						<option value="2" <?=checked($search_date_type,2,1)?>>입금일</option>
						<option value="4" <?=checked($search_date_type,4,1)?>>배송일</option>
						<option value="5" <?=checked($search_date_type,5,1)?>>배송완료일</option>
					</select>
					<input type="checkbox" name="all_date" value="Y" <?=checked($all_date,"Y")?> onClick="searchDate(this.form)"> 전체 기간
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
			<tr>
				<th scope="row">주문상태</th>
				<td><?=$stat_check_str?></td>
			</tr>
		</table>
	</div>
	<div class="box_bottom">
		<span class="box_btn blue"><input type="submit" value="발주대상 조회"></span>
	</div>
</form>

<?if($res) {?>
<form id="prdFrm" name="prdFrm" method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>">
    <input type="hidden" name="body" value="erp@order.exe">
    <input type="hidden" name="exec" value="">
    <input type="hidden" name="sno" value="<?=$seller?>">
    <input type="hidden" name="order_date" value="">
	<table id="list_order" class="tbl_col">
		<caption>발주상품 목록</caption>
		<colgroup>
			<col style="width:40px">
			<col>
			<col style="width:150px">
			<col style="width:100px">
			<col style="width:80px">
			<col style="width:60px">
			<col style="width:70px">
			<col style="width:70px">
			<col style="width:50px">
			<col style="width:180px">
			<col style="width:60px">
		</colgroup>
		<thead>
			<tr>
				<th scope="col"><input type="checkbox" onclick="$(':checkbox[name=\'accept[]\']').attr('checked', this.checked)"></th>
				<th scope="col"><a href="<?=$sort3?>">상품명</th>
				<th scope="col">바코드</th>
				<th scope="col">옵션</th>
				<th scope="col">발주대상수량(기발주수량)</th>
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
                foreach ($res as $data) {
					if(!$file_dir) $file_dir = getFileDir($data['updir']);
					if($data['upfile3']) {
						$is = setImageSize($data['w3'], $data['h3'], 50, 50);
						$imgstr = "<img src='$file_dir/{$data[updir]}/{$data[upfile3]}' $is[2]>";
					}

					$productname = ($data['wm_sc']) ? $data['name']." <img src='$engine_url/_manage/image/shortcut2.gif' alt='바로가기 상품입니다'>" : $data['name'];
					$category_name = makeCategoryName($data, 1);

					$order_target_qty = $data['order_target_qty'] - $data['exist_qty'] - $cr_qty;
					$order_amt = $order_target_qty * $data['order_price'];
					$total_qty += $order_target_qty;
					$total_amt += $order_amt;
			?>
			<tr>
				<td><input type="checkbox" name="accept[]" value="<?=$data['complex_no']?>"></td>
				<td class="left">
					<input type="hidden" name="pno[]" value="<?=$data['complex_no']?>">
					<input type="hidden" name="order_target_qty[]" value="<?=$data['order_target_qty']?>">
					<div class="box_setup">
						<div class="thumb"><a id="imgstr" href="<?=$root_url?>/shop/detail.php?pno=<?=$data[hash]?>" target="_blank"><?=$imgstr?></a></div>
						<dl>
							<dt class="title">
								<input type="text" name="product_name[]" class="input" value="<?=$productname?>" size="20" onfocus="this.select();" onkeyup="getProdByEnter(this);">
								<!--
								<span class="box_btn_s"><a onclick="getProd($(this).prev());">수정</a></span>
								-->
							</dt>
							<dd class="cstr"><?=$category_name?></dd>
						</dl>
					</div>
				</td>
				<td id="barcode"><?=hideBarcode($data['barcode'])?></td>
				<td id="complex_option_name"><?=getComplexOptionName($data['opts'])?></td>
				<td id="order_target_qty"><?=number_format($data['order_target_qty'])?> (<?=number_format($data['exist_qty'])?>)
				<?if($type == 1) {?>
				<td id="current_qty"><?=number_format($data['current_qty'])?></td>
				<?} else {?>
				<td id="current_qty">
					<?=number_format($data['current_qty'])?>
					(<span class="p_color2"><?=number_format($data['safe_stock_qty'])?></span>)
				</td>
				<?}?>
				<td>
					<input type="text" id="order_qty_<?=++$row?>" name="order_qty[]" value="<?=$order_target_qty?>" class="input right" style="ime-mode:disabled;" size="5" onkeydown="keydown();" onfocus="this.select();" onkeyup="calc();FilterNumOnly(this);">
				</td>
				<td><input type="text" id="order_price_<?=$row?>" name="order_price[]" value="<?=$data['order_price']?>" class="input right" style="ime-mode:disabled;" size="5" onkeydown="keydown();" onfocus="this.select();" onkeyup="calc();FilterNumOnly(this);"></td>
				<td id="order_amt" id="order_amt_<?=$row?>"><?=$order_amt?></td>
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
		<!--
		<span class="box_btn gray"><input type="button" value="상품추가" onclick="insProd({});"></span>
		-->
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
	function order_search(type) {
		if(prdSearchFrm.seller.selectedIndex <= 0) {
			alert('사입처를 선택해주세요.');
			prdSearchFrm.seller.focus();
			return;
		}
		prdSearchFrm.type.value = type;
		prdSearchFrm.submit();
	}
	function order() {
		var cnt = 0;
		var error = 0;
		$(':checkbox[name=\"accept[]\"]').each(function(){
			if(this.checked == true) {
				if(!this.value) {
					error++;
				}
				cnt++;
			}
		});

		if(error > 0) {
			window.alert('상품추가 버튼으로 추가 한 상품이 처리되지 않았습니다.');
			return false;
		}

		if(cnt < 1) {
			window.alert('발주 할 상품을 선택 해 주십시오.');
			return false;
		}

		var sno = $("input[name='sno']");
		if(sno.val() == "") {
			if(prdSearchFrm.seller.selectedIndex <= 0) {
				alert('사입처를 선택해주세요.');
				prdSearchFrm.seller.focus();
				return;
			} else {
			   sno.val(prdSearchFrm.seller.value);
			}
		}
		var order_qty = $("input[name='order_qty[]']");
		var order_amt = $("input[name='order_amt[]']");
		if(order_qty.length == 0 || $("#total_qty").val() == "0") {
			alert(" 발주할 상품이 없습니다. 발주수량을 입력하세요.");
			return;
		}
		prdFrm.total_qty.disabled = false;
		prdFrm.total_amt.disabled = false;
		prdFrm.order_date.value = prdSearchFrm.date.value;
		prdFrm.submit();
	}
	function delProd(obj) {
		var tr = $(obj).parent().parent().parent();
		tr.remove();
		calc();

	}
	function keydown(obj) {
		if(event.keyCode == 13) {
			event.keyCode = 9;
		}
		return event.keyCode;
	}
	function calc() {
		$("[id^=order_amt]").calc(
			"qty * price",
			{
				qty: $("input[id^=order_qty_]"),
				price: $("input[id^=order_price_]")
			},
			function (s){
				return s.toFixed(0);
			},
			function ($this) {
				var sum = $this.sum();
				$("#total_amt").val(sum.toFixed(0));
				$("#total_qty").val($("input[id^=order_qty_]").sum());
			}
		);
	}
	function setProd(data) {
		data = $.extend({complex_no:'',order_target_qty:0,imgstr:noimg,productname:"",category_name:"",barcode:"",complex_option_name:"",exist_qty:0,current_qty:0,order_qty:0,in_price:0}, data);
		tr.find("input[name=\"accept[]\"]").val(data.complex_no);
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
		var html = "<tr><td><input type='checkbox' name='accept[]'></td>"+
				   "<td class='left'><input type='hidden' name='pno[]' value=''>" +
				   "<input type='hidden' name='order_target_qty[]' value=''>" +
				   "<div class='box_setup'><div class='thumb'><a id='imgstr' href='' target='_blank'></a></div>"+
				   "<dl class='product_summary'><dt><input type='text' name='product_name[]' class='input' value='' size='20' onfocus='this.select();' onkeyup='getProdByEnter(this);'><span onclick='getProd($(this).prev());'>수정</span></dt>" +
				   "<dd class='cstr'></dd></dl></div></td><td id='barcode'></td><td id='complex_option_name'></td>" +
				   "<td id='order_target_qty'></td>"+
				   "<td id='current_qty'></td>"+
				   "<td><input type='text' id='order_qty_"+row+"' name='order_qty[]' value='' class='input right' style='ime-mode:disabled;' size='5' onkeydown='keydown();' onfocus='this.select();' onkeyup='calc();FilterNumOnly(this);'></td>" +
				   "<td><input type='text' id='order_price_"+row+"' name='order_price[]' value='' class='input right' style='ime-mode:disabled;' size='5' onkeydown='keydown();' onfocus='this.select();' onkeyup='calc();FilterNumOnly(this);'></td>" +
				   "<td id='order_amt' id='order_amt_"+row+"'></td>" +
				   "<td><input type='text' name='remark[]' value='' class='input' size='20'></td>" +
				   "<td><span class='box_btn_s'><a onclick='delProd(this);'>삭제</a></span></td></tr>";
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
			$('#type1_detail').show();
		} else {
			$('#type1_detail').hide();
		}
	}
	$('form#search').find(':radio[name=type]').click(function() {
		setType();
	});
	setType();
</script>