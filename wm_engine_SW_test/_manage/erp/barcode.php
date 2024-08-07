<?PHP

	include_once $engine_dir.'/_engine/include/wingPos.lib.php';

	$_search_type = array(
		'name' => '상품명',
		'barcode' => '바코드',
	);

	// 정렬 및 상품수
	$qs_without_row = '?'.preg_replace('/&row=[^&]+/', '', $_SERVER['QUERY_STRING']);
	$qs_without_sort = '?'.preg_replace('/&sort=[^&]+/', '', $_SERVER['QUERY_STRING']);
	$sort = numberOnly($_GET['sort']);
	if(!$sort) $sort = 0;
	for($i = 1; $i <= 11; $i++) {
		$var1 = ($i-1) * 2;
		$var2 = $var1 + 1;
		${'arrowcolor'.$i} = ($sort == $var1 || $sort == $var2) ? 'blue' : 'gray';
		${'arrowdir'.$i} = ($sort == $var2) ? 'down' : 'up';
		${'sort'.$i} = ($sort == $var1) ? $qs_without_sort.'&sort='.$var2 : $qs_without_sort.'&sort='.$var1;
	}

	// 카테고리 검색
	for($i = 1; $i < $cfg['max_cate_depth']; $i++) {
		$cl = $_cate_colname[1][$i];
		$val = numberOnly($_GET[$cl]);
		if($val) $cw .= " or (`level`='".($i+1)."' and $cl='$val')";
	}
	$sql = $pdo->iterator("select no, name, ctype, level from $tbl[category] where ctype='1' and (level='1' $cw) order by level, sort");
    foreach ($sql as $cate) {
		$cl = $_cate_colname[1][$cate['level']];
		$sel = ($_GET[$cl] == $cate['no']) ? 'selected' : '';
		${'item_'.$cate['ctype']."_".$cate['level']} .= '<option value="'.$cate['no'].'" '.$sel.'>'.stripslashes($cate['name']).'</option>';
	}

	$stat = numberOnly($_GET['stat']);
	if($stat) $w .= ' and b.stat='.$stat;
	$search_type = trim($_GET['search_type']);
	$search_str = addslashes(trim($_GET['search_str']));
	if($search_str && $_search_type[$search_type]) {
		$__search_type = addslashes($_GET['search_type']);
		$w .= " and $__search_type like '%$search_str%'";
	}

	$big = numberOnly($_GET['big']);
	$mid = numberOnly($_GET['mid']);
	$small = numberOnly($_GET['small']);
	$depth4 = numberOnly($_GET['depth4']);
	if($depth4) $w .= " and b.depth4='$depth4'";
	elseif($small) $w .= " and b.small='$small'";
	elseif($mid) $w .= " and b.mid='$mid'";
	elseif($big) $w .= " and b.big='$big'";

	$ext_date = addslashes($_GET['ext_date']);
	if($ext_date) {
		$ext_date1 = strtotime($ext_date);
		$ext_date2 = strtotime($ext_date)+86399;
		$f = ", sum(o.buy_ea) as buy_ea ";
		$join = " inner join $tbl[order_product] o on o.complex_no = a.complex_no ";
		$w .= " and o.stat in (17,19) and o.repay_date between $ext_date1 and $ext_date2 group by o.complex_no ";
	}

	if($admin['level'] == 4) {
		$w .= " and b.partner_no='$admin[partner_no]'";
	}

	if($ext_date) {
		$NumTotalRec = $pdo->rowCount("
            select b.no
                from erp_complex_option a inner join {$tbl['product']} b on a.pno = b.no $join
                where b.stat != 1 and a.del_yn='N' $w
        ");
	} else {
		$NumTotalRec = $pdo->row("select count(*) from erp_complex_option a inner join $tbl[product] b on a.pno = b.no $join where b.stat != 1 and a.del_yn = 'N' $w");
	}

	// 페이징 처리
	include $engine_dir."/_engine/include/paging.php";

	$page = numberOnly($_GET['page']);
	$row = numberOnly($_GET['row']);
	if($page <= 1) $page = 1;
	if(!$row) $row = 20;
	if($row > 200) $row = 200;
	$block = 10;

	$PagingInstance = new Paging($NumTotalRec, $page, $row, $block);
	$PagingInstance->addQueryString(makeQueryString('page'));
	$PagingResult = $PagingInstance->result($pg_dsn);
	$pageRes = $PagingResult['PageLink'];

	$sort = (isset($_GET['sort']) == true) ? numberOnly($_GET['sort']) : 0;
	$sort_str = $cfg['mng_sort'][$sort];

	$res = $pdo->iterator("
		select
			b.*, a.barcode, a.complex_no, a.opts
			, (select provider from wm_provider x where b.seller_idx = x.no) as provider $f
			from erp_complex_option a inner join $tbl[product] b on a.pno = b.no
			$join
			where b.stat != 1 and a.del_yn='N' $w
			order by b.$sort_str ".$PagingResult['LimitQuery']
	);

	$view_link = "shop";
	$edit_link = "product@product_register";

?>
<script type="text/javascript" src="<?=$engine_url?>/_engine/common/shop.js"></script>
<form id="search" name="prdSearchFrm">
	<input type="hidden" name="body" value="<?=$body?>">
	<input type="hidden" name="exec" value="search">
	<input type="hidden" name="sort" value="<?=$sort?>">
	<div class="box_title first">
		<h2 class="title">바코드 관리</h2>
	</div>
	<table class="tbl_row">
		<caption class="hidden">바코드 관리</caption>
		<colgroup>
			<col style="width:15%">
		</colgroup>
		<tr>
			<th scope="row">매장분류</th>
			<td>
				<select name="big" onchange="chgCateInfinite(this, 2, '');">
					<option value="">::대분류::</option>
					<?=$item_1_1?>
				</select>
				<select name="mid" onchange="chgCateInfinite(this, 3, '');">
					<option value="">::중분류::</option>
					<?=$item_1_2?>
				</select>
				<select name="small" onchange="chgCateInfinite(this, 4, '');">
					<option value="">::소분류::</option>
					<?=$item_1_3?>
				</select>
				<?if($cfg['max_cate_depth'] >= 4) {?>
				<select name="depth4">
					<option value="">::세분류::</option>
					<?=$item_1_4?>
				</select>
				<?}?>
				<label class="p_cursor"><input type="checkbox" name="only_cate" value="Y" <?=checked($only_cate,"Y")?>> 하위 분류 상품 제외</label>
			</td>
		</tr>
		<tr>
			<th scope="row">상품상태</th>
			<td>
				<label class="p_cursor"><input type="radio" name="stat" value="" <?=checked($stat, '')?>> 전체</label>
				<label class="p_cursor"><input type="radio" name="stat" value="2" <?=checked($stat, '2')?>> 정상</label>
				<label class="p_cursor"><input type="radio" name="stat" value="3" <?=checked($stat, '3')?>> 품절</label>
				<label class="p_cursor"><input type="radio" name="stat" value="4" <?=checked($stat, '4')?>> 숨김</label>
			</td>
		</tr>
		<tr>
			<th scope="row">반품바코드 재인쇄</th>
			<td>
				<input type="text" name="ext_date" value="<?=$ext_date?>" size="10" readonly class="input datepicker" onfocus="selDate(this)"> 일 반품완료, 교환완료된 주문 검색
			</td>
		</tr>
	</table>
	<div class="box_bottom">
		<?=selectArray($_search_type, 'search_type', 2, '', $search_type)?>
		<input type="text" name="search_str" value="<?=inputText($search_str)?>" class="input" size="40">
		<span class="box_btn gray"><input type="submit" value="검색"></span>
		<span class="box_btn"><input type="button" value="초기화" onclick="location.href='?body=<?=$_GET['body']?>&cpn_mode=<?=$cpn_mode?>'"></span>
	</div>
</form>

<div class="box_title">
	<strong id="total_prd"><?=number_format($NumTotalRec)?></strong>개의 상품이 검색되었습니다.
</div>
<div class="box_sort">
	<dl class="list">
		<dt class="hidden">정렬</dt>
		<dd>
			상품수
			<select name="row" onchange="location.href='<?=$qs_without_row?>&row='+this.value">
				<option value="10" <?=checked($row, 10, 1)?>>10</option>
				<option value="20" <?=checked($row, 20, 1)?>>20</option>
				<option value="30" <?=checked($row, 30, 1)?>>30</option>
				<option value="50" <?=checked($row, 50, 1)?>>50</option>
				<option value="70" <?=checked($row, 70, 1)?>>70</option>
				<option value="100" <?=checked($row, 100, 1)?>>100</option>
				<option value="200" <?=checked($row, 200, 1)?>>200</option>
			</select>
		</dd>
		<dd><a href="<?=$sort1?>">수정일 <img src="<?=$engine_url?>/_manage/image/icon/ic_tr_arrow_<?=$arrowdir1?>.gif" class="arrow <?=$arrowcolor1?>"></a></dd>
		<dd><a href="<?=$sort2?>">등록일 <img src="<?=$engine_url?>/_manage/image/icon/ic_tr_arrow_<?=$arrowdir2?>.gif" class="arrow <?=$arrowcolor2?>"></a></dd>
	</dl>
	<div class="total">
		<a href="javascript:;" onclick="location.reload();" onmouseover="showToolTip(event,'새로고침')" onmouseout="hideToolTip();"><img src="<?=$engine_url?>/_manage/image/btn/bt_reload.gif" alt="새로고침"></a>
	</div>
</div>
<form id="prdFrm" name="prdFrm" method="post">
	<input type="hidden" name="body" value="erp@barcode_print2.exe">
	<table class="tbl_col">
		<caption class="hidden">바코드 관리 리스트</caption>
		<colgroup>
			<col style="width:40px">
			<col style="width:200px">
			<col>
			<col style="width:200px">
			<col style="width:140px">
			<col style="width:100px">
		</colgroup>
		<thead>
			<tr>
				<th scope="col"><input type="checkbox" onclick="checkAll(document.prdFrm.check_ino,this.checked)"></th>
				<th scope="col"><a href="<?=$sort11?>">사입처<img src="<?=$engine_url?>/_manage/image/icon/ic_tr_arrow_<?=$arrowdir11?>.gif" class="arrow <?=$arrowcolor11?>"></a></th>
				<th scope="col"><a href="<?=$sort3?>">상품명 <img src="<?=$engine_url?>/_manage/image/icon/ic_tr_arrow_<?=$arrowdir3?>.gif" class="arrow <?=$arrowcolor3?>"></a></th>
				<th scope="col">옵션</th>
				<th scope="col"><a href="<?=$sort4?>">바코드 <img src="<?=$engine_url?>/_manage/image/icon/ic_tr_arrow_<?=$arrowdir4?>.gif" class="arrow <?=$arrowcolor4?>"></a></th>
				<th scope="col">출력개수</th>
			</tr>
		</thead>
		<tbody>
			<?php
                foreach ($res as $data) {
					$prd = shortcut($data);
					$data['qty'] = $pdo->row("select curr_stock(".$data['complex_no'].") as qty");

					if(!$file_dir) $file_dir = getFileDir($prd['updir']);
					if($prd['upfile3']) {
						$is = setImageSize($prd['w3'], $prd['h3'], 50, 50);
						$imgstr = "<img src='$file_dir/{$prd[updir]}/{$prd[upfile3]}' $is[2]>";
					}

					$productname = ($prd['wm_sc']) ? $prd['name']." <img src='$engine_url/_manage/image/shortcut2.gif' alt='바로가기 상품입니다'>" : $prd['name'];
					$category_name = makeCategoryName($data, 1);

					$qty_now = ($data['prev_qty']+$data['in_qty']-$data['out_qty']);
					$prd['seller'] = stripslashes($prd['seller']);
					switch($qty_now) {
						case 0: $qty_css = 'qty_zero'; break;
						case ($qty_now > 0) : $qty_css = 'qty_over'; break;
						case $qty_now < 0 : $qty_css = 'qty_min'; break;
					}
					$print_no = $data['qty'] > 0 ? $data['qty'] : 0;
					if($ext_date) $print_no = $data['buy_ea'];
			?>
			<tr>
				<td>
					<input type="checkbox" id="check_ino" name="check_ino[]" value="<?=$data['complex_no']?>">
					<input type="hidden" name="ino[]" value="<?=$data['complex_no']?>">
				</td>
				<td><?=$prd['seller']?></td>
				<td class="left">
					<div class="box_setup">
						<div class="thumb"><a href="<?=$root_url?>/<?=$view_link?>/detail.php?pno=<?=$prd['hash']?>" target="_blank"><?=$imgstr?></a></div>
						<dl>
							<dt class="title"><a href="./?body=<?=$edit_link?>&pno=<?=$prd['parent']?>" target="_blank"><?=$productname?></a></dt>
							<dd class="cstr"><?=$category_name?></dd>
							<dd><span class="box_btn_s btnp"><a href="./?body=<?=$edit_link?>&pno=<?=$prd['parent']?>" target="_blank">상세</a></span></dd>
						</dl>
					</div>
				</td>
				<td><?=getComplexOptionName($data['opts'])?></td>
				<td><?=$data['barcode']?></td>
				<td><input type="input" name="print_qty[]" value="<?=$print_no?>" size="7" class="input right"></td>
			</tr>
			<?}?>
		</tbody>
	</table>
	<div class="box_middle2 left">
		<input type="text" name="print_pos" value="0" size="4" class="input"> 번째 위치부터
		<span class="box_btn blue"><input type="button" value="바코드 출력" onclick="barcode()"></span>
	</div>
	<div class="box_bottom">
		<?=$pageRes?>
	</div>
</form>

<script type="text/javascript">
	function barcode() {
		var ino = document.getElementsByName("check_ino[]");
		var no;
		for (var i=0; i<ino.length; i++) {
			if(ino[i].checked) {
				if(no) {
					no += ',';
				} else {
					no = '';
				}
				no += ino[i].value;
			}
		}
		if(no) {
			window.open('', 'barcode', 'status=no, width=770px, height=680px. scrollbars=yes');
			var prdFrm = document.getElementById("prdFrm");
			prdFrm.target = 'barcode';
			prdFrm.submit();
		} else {
			window.alert('선택 된 상품이 없습니다.');
		}
	}

	function selDate(obj) {
		if(obj.value) {
			obj.value = '';
			obj.blur();
		}
	}
</script>