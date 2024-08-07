<?PHP

	include_once $engine_dir.'/_engine/include/wingPos.lib.php';

	$_cname_cache = getCategoriesCache(1);

	$_search_type['name']='상품명';
	if($wp_stat > 2) $_search_type['barcode']='바코드';
	$_search_type['seller']='사입처';
	$_search_type['origin_name']='장기명';

	$search_type = $_GET['search_type'];
	$search_str = addslashes(trim($_GET['search_str']));
	if($_search_type[$search_type] && $search_str) {
		$w .= " and `$search_type` like '%$search_str%'";
	}

	include_once $engine_dir.'/_manage/erp/order_detail_search.inc.php';
	include $engine_dir."/_engine/include/paging.php";

	$NumTotalRec = $pdo->row("select count(*) from wm_product a inner join erp_complex_option b on a.no = b.pno inner join erp_order_dtl c using(complex_no) where a.stat in (2,3,4) and b.del_yn = 'N' and c.order_no = '{$ono}' $w");

	if($page <= 1) $page = 1;
	foreach($_GET as $key=>$val) {
		if($key!="page") $QueryString.="&".$key."=".urlencode($val);
	}
	if(!$QueryString) $QueryString = '&body='.$_GET['body'];
	$PagingInstance = new Paging($NumTotalRec, $page, 15, 10);
	$PagingInstance->addQueryString($QueryString);
	$PagingResult = $PagingInstance->result($pg_dsn);

	$sql .= $PagingResult['LimitQuery'];
	$pageRes = $PagingResult['PageLink'];

	$res = $pdo->iterator($sql);

?>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/shop.js"></script>
<form id="search" name="prdSearchFrm">
	<input type="hidden" name="body" value="<?=$body?>">
	<input type="hidden" name="type">
	<input type="hidden" name="ono" value="<?=$ono?>">
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
	<div class="box_bottom">
		<?=selectArray($_search_type,"search_type",2,"",$search_type)?>
		<input type="text" name="search_str" value="<?=inputText($search_str)?>" class="input" size="40">
		<span class="box_btn gray"><input type="submit" value="검색"></span>
		<span class="box_btn"><input type="button" value="초기화" onclick="location.href='?body=<?=$_GET['body']?>&ono=<?=$ono?>'"></span>
	</div>
</form>
<form method="post" action="<?=$_SERVER['PHP_SELF']?>" target="hidden<?=$now?>" onsubmit="return confirmSubmit(this)">
	<input type="hidden" name="body" value="erp@order_mod.exe">
	<input type="hidden" name="order_no" value="<?=$ono?>">
	<div class="box_title">
		<strong id="total_prd"><?=number_format($NumTotalRec)?></strong>개의 발주상품이 검색되었습니다.
		<span class="box_btn_s btns"><input type="button" value="발주 상세로 이동" onclick="location.href='?body=erp@order_detail&ono=<?=$ono?>'"></span>
	</div>
	<div class="box_middle left">
		<?if($order['order_stat'] < 3) {?>
		<span class="box_btn blue"><input type="button" value="발주추가" onclick="addOrder('<?=$order['order_no']?>')"></span>
		<?}?>
		<p class="p_color2" style="float: right;">발주수량이 입고수량보다 작으면 처리되지 않습니다.</p>
	</div>
	<div class="box_middle">
		<?=$pageRes?>
	</div>
	<table class="tbl_col">
		<caption class="hidden">발주상품 목록</caption>
		<colgroup>
			<col>
			<col>
			<col>
			<col>
			<col>
			<col>
			<col>
			<col style="width:200px">
			<col style="width:60px">
		</colgroup>
		<thead>
			<tr>
				<th scope="col">사입처</th>
				<th scope="col"><a href="<?=$sort3?>">상품명</th>
				<th scope="col">바코드</th>
				<th scope="col">옵션</th>
				<th scope="col">발주수량</th>
				<th scope="col">입고수량</th>
				<th scope="col">발주단가</th>
				<th scope="col">비고</th>
				<th scope="col">삭제</th>
			</tr>
		</thead>
		<tbody>
		<?
			$idx = 0;
			if($res) {
                foreach ($res as $data) {
					if(!$file_dir) $file_dir = getFileDir($data['updir']);
					if($data['upfile3']) {
						$is = setImageSize($data['w3'], $data['h3'], 50, 50);
						$imgstr = "<img src='$file_dir/{$data[updir]}/{$data[upfile3]}' $is[2]>";
					}

					$data['name'] = stripslashes($data['name']);
					$data['origin_name'] = stripslashes($data['origin_name']);
					$data['provider'] = stripslashes($data['provider']);
					$data['remark'] = stripslashes($data['remark']);

					$productname = ($data['wm_sc']) ? $data['name']." <img src='$engine_url/_manage/image/shortcut2.gif' alt='바로가기 상품입니다'>" : $data['name'];
					if($data['origin_name']) $data['origin_name'] = '('.$data['origin_name'].')';
					$category_name = $_cname_cache[$data['big']];
					if($data['mid']) $category_name .= $cate_sprit.$_cname_cache[$data['mid']];
					if($data['small']) $category_name .= $cate_sprit.$_cname_cache[$data['small']];
			?>
			<tr>
				<td class="left"><?=$data['provider']?></td>
				<td class="left">
					<div class="box_setup">
						<div class="thumb"><a href="<?=$root_url?>/shop/detail.php?pno=<?=$data[hash]?>" target="_blank"><?=$imgstr?></a></div>
						<dl>
							<dt class="title"><a href="?body=product@product_register&pno=<?=$data['no']?>" target="_blank"><?=$productname?></a></dt>
							<dd><a href="?body=product@product_register&pno=<?=$data['no']?>" target="_blank"><?=$data['origin_name']?></a></dd>
							<dd class="cstr"><?=$category_name?></dd>
						</dl>
					</div>
				</td>
				<td><?=hideBarcode($data['barcode'])?></td>
				<td><?=getComplexOptionName($data['opts'])?></td>
				<td>
					<input type="hidden" name="order_dtl_no[]" value="<?=$data['order_dtl_no']?>">
					<input type="text" name="order_qty[]" value="<?=$data['order_qty']?>" class="input" size="6">
					<input type="hidden" name="order_qty_org[]" value="<?=$data['order_qty']?>">
				</td>
				<td>
					<?=number_format($data[in_qty])?>
					<input type="hidden" name="in_qty[]" value="<?=$data['in_qty']?>">
				</td>
				<td>
					<input type="text" name="order_price[]" value="<?=$data['order_price']?>" class="input" size="6">
					<input type="hidden" name="order_price_org[]" value="<?=$data['order_price']?>">
				</td>
				<td><input type="text" name="remark[]" value="<?=inputText($data['remark'])?>" class="input" size="20"></td>
				<td>
					<span class="box_btn_s gray"><input type="button" value="삭제" onclick="removeOrder(<?=$data['order_dtl_no']?>)"></span>
				</td>
			</tr>
			<?
				$idx++;
				}
			}
		?>
		</tbody>
	</table>
	<div class="box_bottom">
		<div class="left_area">
			<span class="box_btn blue"><input type="submit" value="발주서 수정"></span>
			<span class="box_btn gray"><input type="button" value="취소" onclick="goM('erp@order_detail&ono=<?=$ono?>')"></span>
		</div>
		<?=$pageRes?>
	</div>
</form>

<script type="text/javascript">
	var order_qty = $(":input[name='order_qty[]']");
	var order_qty_org = $(":input[name='order_qty_org[]']");
	var in_qty = $(":input[name='in_qty[]']");
	var order_price = $(":input[name='order_price[]']");
	var order_price_org = $(":input[name='order_price_org[]']");

	chg_highlight = function() {
		var idx = -1;
		for(var i = 0; i < order_qty.length; i++) {
			if(order_qty[i] == this || order_price[i] == this) idx = i;
		}

		if(idx > -1) {
			order_qty[idx].style.border = (order_qty[idx].value != order_qty_org[idx].value) ? 'solid 1px red' : 'solid 1px #ddd';
			order_price[idx].style.border = (order_price[idx].value != order_price_org[idx].value) ? 'solid 1px red' : 'solid 1px #ddd';
		}
	}

	order_qty.keyup(chg_highlight).change(chg_highlight);
	order_price.keyup(chg_highlight).change(chg_highlight);

	function confirmSubmit(f) {
		var err = 0;
		var mod = 0;
		in_qty.each(function(idx) {
			if(err > 0) return;
			/*
			if(order_qty[idx].value.toNumber() < 1) {
				window.alert('발주수량을 1개 이상 입력해 주십시오.');
				err++;
				blinkInput(order_qty[idx]);
				return;
			}
			*/
			if(in_qty[idx].value.toNumber() > order_qty[idx].value.toNumber()) {
				window.alert('발주수량이 입고수량보다 작습니다.');
				err++;
				blinkInput(order_qty[idx]);
				return;
			}
			if(order_qty[idx].value != order_qty_org[idx].value || order_price[idx].value != order_price_org[idx].value) {
				mod++;
			}
		});

		if(mod < 1) {
			window.alert('수정할 발주 내역이 없습니다.');
			return false;
		}
		if(err > 0) return false;

		return confirm('입력하신 내용으로 발주서를 수정하시겠습니까?');
		return true;
	}

	function blinkInput(obj) {
		$(obj).css('backgroundColor', '#FFFF66').select();
		setTimeout(function() {
			$(obj).css('backgroundColor', '').select();
		}, 1000);
	}

	function removeOrder(order_dtl_no) {
		if(confirm('선택한 상품을 발주서에서 제거하시겠습니까?')) {
			$.get('?body=erp@order_mod.exe', {'exec':'remove', 'order_dtl_no':order_dtl_no}, function(ret) {
				if(ret == 'OK') location.reload()
				else {
					window.alert(ret);
				}
			});
		}
	}

	$('body').ready(function() {
		$(".paging").find('a').click(function(){
			var mod = 0;
			in_qty.each(function(idx) {
				if(order_qty[idx].value != order_qty_org[idx].value || order_price[idx].value != order_price_org[idx].value) {
					mod++;
				}
			});

			if(mod > 0) return confirm('저장하지 않은 수정사항이 있습니다.\n계속 진행하시겠습니까?');
		});
	});

	var psearch = new layerWindow('erp@order_add.exe');
	function addOrder(order_no) {
		psearch.open('order_no=<?=$ono?>&ono='+order_no);

	}
</script>